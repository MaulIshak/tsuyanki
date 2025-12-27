<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\ReviewLog;
use App\Models\ReviewState;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function due(Request $request)
    {
        $user = $request->user();
        $limit = $request->input('limit', 20);
        $deckId = $request->input('deck_id');

        // 1. Get Daily Goal from Preferences
        $dailyGoal = $user->preferences['daily_goal'] ?? 20;

        $ignoreLimits = $request->boolean('ignore_limits');

        // 2. Count "True New" Cards introduced today
        // A "True New" card introduction is defined as a card receiving its FIRST EVER review log today.
        // This is robust against lapses/relearning logic which generates logs but aren't "introductions".
        $newCardsIntroducedToday = DB::table('review_logs')
            ->where('user_id', $user->id)
            ->select('card_id')
            ->groupBy('card_id')
            ->havingRaw('MIN(reviewed_at) >= ?', [Carbon::today()->toDateTimeString()])
            ->get()
            ->count();

        if ($ignoreLimits) {
            $remainingNewLimit = 1000; // Allow studying well beyond the daily limit
        } else {
            $remainingNewLimit = max(0, $dailyGoal - $newCardsIntroducedToday);
        }

        // 3. Query Due Review Cards (Priority 1)
        // Definition:
        // - Normal Reviews: repetition > 0
        // - Lapsed/Relearning: repetition = 0 AND interval > 0 (failed reviews reset rep to 0 but usually keep interval >= 1 or custom logic)
        //   Note: Import service sets interval=0 for fresh cards.
        $reviewsQuery = Card::query()
            ->with(['note.noteType', 'template'])
            ->whereHas('reviewState', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('due_at', '<=', now())
                    ->where(function ($sq) {
                        $sq->where('repetition', '>', 0)
                            ->orWhere('interval', '>', 0);
                    });
            });

        if ($deckId) {
            $reviewsQuery->whereHas('note', fn($q) => $q->where('deck_id', $deckId));
        }

        $reviews = $reviewsQuery->limit($limit)->get();

        // 4. Query True New Cards (Priority 2)
        // Definition:
        // - No State (created manually)
        // - OR State exists but repetition=0 AND interval=0 (Imported fresh cards)
        $newCards = collect();
        if ($reviews->count() < $limit && $remainingNewLimit > 0) {
            $slotsAvailable = $limit - $reviews->count();
            $fetchCount = min($slotsAvailable, $remainingNewLimit);

            $newQuery = Card::query()
                ->with(['note.noteType', 'template'])
                ->whereHas('note.deck', function ($q) use ($user) {
                    $q->where('owner_user_id', $user->id);
                })
                ->where(function ($q) use ($user) {
                    $q->whereDoesntHave('reviewState', function ($sq) use ($user) {
                        $sq->where('user_id', $user->id);
                    })
                        ->orWhereHas('reviewState', function ($sq) use ($user) {
                            $sq->where('user_id', $user->id)
                                ->where('repetition', 0)
                                ->where('interval', 0);
                        });
                });

            if ($deckId) {
                $newQuery->whereHas('note', fn($q) => $q->where('deck_id', $deckId));
            }

            $newCards = $newQuery->inRandomOrder()->limit($fetchCount)->get();
        }

        // 5. Query Review Ahead (Priority 3 - Cram Mode)
        // Only if ignore_limits is TRUE and we still have space.
        $reviewAheadCards = collect();
        if ($ignoreLimits && ($reviews->count() + $newCards->count()) < $limit) {
            $slotsAvailable = $limit - ($reviews->count() + $newCards->count());

            // Use robust INNER JOIN for Review Ahead / Cram Mode
            // This avoids potential subquery issues and ensures only cards with valid state logic are pulled.
            $aheadQuery = Card::query()
                ->with(['note.noteType', 'template'])
                ->join('review_states', 'cards.id', '=', 'review_states.card_id')
                ->where('review_states.user_id', $user->id)
                ->where('review_states.due_at', '>', now());

            if ($deckId) {
                $aheadQuery->whereHas('note', fn($q) => $q->where('deck_id', $deckId));
            }

            // Get cards reviewed LEAST recently first (Cycling behavior)
            $reviewAheadCards = $aheadQuery
                ->select('cards.*') // Ensure we return Card models structure
                ->orderBy('review_states.last_reviewed_at', 'asc')
                ->orderBy('review_states.due_at', 'asc')
                ->limit($slotsAvailable)
                ->get();
        }

        // 6. Merge
        $cards = $reviews->merge($newCards)->merge($reviewAheadCards);

        return response()->json([
            'cards' => $cards,
            'summary' => [
                'total' => $cards->count(),
                'reviews_due' => $reviews->count(),
                'new_cards' => $newCards->count(),
                'daily_limit_remaining' => $remainingNewLimit
            ]
        ]);
    }

    public function submit(Request $request, Card $card)
    {
        // Simple SM-2 Implementation
        $request->validate(['quality' => 'required|integer|min:0|max:5']);
        $quality = $request->input('quality');
        $user = $request->user();

        // Get or Create State
        $state = ReviewState::firstOrCreate(
            ['user_id' => $user->id, 'card_id' => $card->id],
            ['ease_factor' => 2.5, 'interval' => 0, 'repetition' => 0, 'due_at' => now()]
        );

        // Calculate new state (SM-2 simplified)
        if ($quality >= 3) {
            if ($state->repetition == 0) {
                $state->interval = 1;
            } elseif ($state->repetition == 1) {
                $state->interval = 6;
            } else {
                $state->interval = round($state->interval * $state->ease_factor);
            }
            $state->repetition += 1;
            $state->ease_factor = $state->ease_factor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
            if ($state->ease_factor < 1.3)
                $state->ease_factor = 1.3;
        } else {
            $state->repetition = 0;
            $state->interval = 1;
        }

        $state->due_at = Carbon::now()->addDays($state->interval);
        $state->last_reviewed_at = Carbon::now();
        $state->save();

        // Log
        ReviewLog::create([
            'user_id' => $user->id,
            'card_id' => $card->id,
            'quality' => $quality,
            'ease_factor' => $state->ease_factor,
            'interval' => $state->interval,
            'repetition' => $state->repetition,
            'reviewed_at' => now(),
        ]);

        return response()->json([
            'card_id' => $card->id,
            'next_due_at' => $state->due_at,
            'interval' => $state->interval
        ]);
    }

    public function stats(Request $request)
    {
        $user = $request->user();

        // 1. Today's Reviews
        $todayReviews = ReviewLog::where('user_id', $user->id)
            ->whereDate('reviewed_at', Carbon::today())
            ->count();

        // 2. Recent Activity (Last 7 Days)
        // Group by date
        $startDate = Carbon::today()->subDays(6);
        $activityData = ReviewLog::where('user_id', $user->id)
            ->whereDate('reviewed_at', '>=', $startDate)
            ->select(DB::raw('DATE(reviewed_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $recentActivity = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->format('Y-m-d');
            $recentActivity[] = $activityData->get($date)->count ?? 0;
        }

        // 3. Current Streak
        // Consecutive days with at least 1 review, looking backwards from today
        $streak = 0;
        $checkDate = Carbon::today();

        // Optimize: verify today first
        $hasReviewedToday = $todayReviews > 0;
        if ($hasReviewedToday) {
            $streak++;
            $checkDate->subDay();
        } else {
            // If not reviewed today, check yesterday. If yesterday has reviews, streak is alive (but today is 0 so far)
            // Actually, usually streak includes today if done, or up to yesterday.
            // Let's count backwards. 
            $checkDate->subDay();
        }

        while (true) {
            $count = ReviewLog::where('user_id', $user->id)
                ->whereDate('reviewed_at', $checkDate)
                ->exists();

            if ($count) {
                $streak++;
                $checkDate->subDay();
            } else {
                break;
            }
        }

        // 4. Total Mastery (Mature Cards / Total Learning Cards)
        // Mature = Interval > 21 days
        $matureCount = ReviewState::where('user_id', $user->id)->where('interval', '>', 21)->count();
        $totalLearning = ReviewState::where('user_id', $user->id)->count();
        $mastery = $totalLearning > 0 ? round(($matureCount / $totalLearning) * 100) : 0;

        return response()->json([
            'period' => 'today',
            'reviews_completed' => $todayReviews,
            'recent_activity' => $recentActivity,
            'streak' => $streak,
            'total_mastery' => $mastery
        ]);
    }
}
