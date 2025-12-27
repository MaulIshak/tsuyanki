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

        // Get cards due
        // Logic: cards that belong to decks visible to user (or just user's decks for study? usually user studies his own progress on any deck)
        // A user studies a card -> creates a review state for THAT user.
        // So we query ReviewState for this user.

        // However, new cards don't have review state yet.
        // New cards: Cards in decks user owns/follow that don't have review state.

        // Simplified: Study owned decks or specific deck.
        // If deck_id provided

        $limit = $request->input('limit', 20);
        $deckId = $request->input('deck_id');

        $query = Card::query();

        if ($deckId) {
            $query->whereHas('note', function ($q) use ($deckId) {
                $q->where('deck_id', $deckId);
            });
        }

        // Join with ReviewState
        // We want cards where review_state is null (new) OR review_state.due_at <= now
        // And user_id = current user

        $cards = $query->where(function ($q) use ($user) {
            $q->whereDoesntHave('reviewState', function ($sq) use ($user) {
                $sq->where('user_id', $user->id);
            })
                ->orWhereHas('reviewState', function ($sq) use ($user) {
                    $sq->where('user_id', $user->id)
                        ->where('due_at', '<=', now());
                });
        })
            ->with([
                'reviewState' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                },
                'note',
                'template'
            ])
            ->limit($limit)
            ->get();

        return response()->json([
            'cards' => $cards,
            'summary' => [
                'total' => $cards->count()
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

        return response()->json([
            'period' => 'today',
            'reviews_completed' => $todayReviews,
            'recent_activity' => $recentActivity,
            'streak' => $streak,
            'total_mastery' => 0 // Placeholder, or calc based on Mature cards (interval > 21 days)
        ]);
    }
}
