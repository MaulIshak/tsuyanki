<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Card extends Model
{
    use HasFactory;

    protected $fillable = [
        'note_id',
        'card_template_id',
    ];

    protected $appends = ['front_html', 'back_html'];

    public function note(): BelongsTo
    {
        return $this->belongsTo(Note::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(CardTemplate::class, 'card_template_id');
    }

    public function reviewState(): HasOne
    {
        return $this->hasOne(ReviewState::class);
    }

    public function reviewLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ReviewLog::class);
    }

    public function getFrontHtmlAttribute(): string
    {
        if (!$this->relationLoaded('note') || !$this->relationLoaded('template')) {
            // Attempt to load if not loaded? Or return empty?
            // Safer to return empty or try to lazy load if critical, but performance-wise better to eager load.
            // For now, let's allow lazy loading if it helps debugging, but ideally eager load.
            if ($this->note && $this->template) {
                return $this->renderTemplate($this->template->front_template, $this->note->fields);
            }
            return '';
        }

        return $this->renderTemplate($this->template->front_template, $this->note->fields);
    }

    public function getBackHtmlAttribute(): string
    {
        if (!$this->relationLoaded('note') || !$this->relationLoaded('template')) {
            if ($this->note && $this->template) {
                return $this->renderTemplate($this->template->back_template, $this->note->fields);
            }
            return '';
        }

        return $this->renderTemplate($this->template->back_template, $this->note->fields);
    }

    private function renderTemplate(string $template, ?array $fields): string
    {
        if (!$fields)
            return $template;

        return preg_replace_callback('/\{\{(.*?)\}\}/', function ($matches) use ($fields) {
            $key = $matches[1];
            if ($key === 'FrontSide') {
                return $this->front_html;
            }
            // Check for both casing just in case
            return $fields[$key] ?? $fields[ucfirst($key)] ?? $fields[strtolower($key)] ?? $matches[0];
        }, $template);
    }
}
