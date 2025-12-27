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

        // Normalize template newlines
        $template = str_replace(["\r\n", "\r"], "\n", $template);

        // Max iterations to prevent infinite loops (though Anki templates are finite)
        $maxIterations = 10;
        $iteration = 0;

        // Handle Conditionals iteratively to support nesting
        do {
            $replaced = false;
            $iteration++;

            // 1. Handle Conditional Blocks: {{#Field}}...{{/Field}}
            $template = preg_replace_callback('/\{\{#\s*(.+?)\s*\}\}([\s\S]*?)\{\{\/\s*\1\s*\}\}/', function ($matches) use ($fields, &$replaced) {
                $key = $matches[1];
                $content = $matches[2];
                $value = $this->getFieldValue($fields, $key);
                $replaced = true;

                return !empty($value) ? $content : '';
            }, $template, -1, $count);

            if ($count > 0)
                $replaced = true;

            // 2. Handle Inverted Conditional Blocks: {{^Field}}...{{/Field}}
            $template = preg_replace_callback('/\{\{\^\s*(.+?)\s*\}\}([\s\S]*?)\{\{\/\s*\1\s*\}\}/', function ($matches) use ($fields, &$replaced) {
                $key = $matches[1];
                $content = $matches[2];
                $value = $this->getFieldValue($fields, $key);
                $replaced = true; // Mark as replaced to continue loop

                return empty($value) ? $content : '';
            }, $template, -1, $countInv);

            if ($countInv > 0)
                $replaced = true;

        } while ($replaced && $iteration < $maxIterations);

        // 3. Handle Variable Replacement: {{Field}} and {{filter:Field}}
        return preg_replace_callback('/\{\{(.*?)\}\}/', function ($matches) use ($fields) {
            $rawKey = trim($matches[1]);

            // Ignore control tags if any left
            if (str_starts_with($rawKey, '#') || str_starts_with($rawKey, '^') || str_starts_with($rawKey, '/')) {
                return $matches[0];
            }

            // Handle filters like "furigana:FieldName" or "hint:FieldName"
            $parts = explode(':', $rawKey);
            $key = end($parts);
            $filter = count($parts) > 1 ? reset($parts) : null;

            if ($key === 'FrontSide') {
                return $this->front_html;
            }

            $value = $this->getFieldValue($fields, $key);

            if ($value === null) {
                return '';
            }

            // Apply filters
            if ($filter === 'furigana') {
                // Convert "Kanji[Kana]" to ruby tags
                return preg_replace('/ ?([^ >]+?)\[(.+?)\]/', '<ruby>$1<rt>$2</rt></ruby>', $value);
            }
            if ($filter === 'hint') {
                return $value;
            }

            return $value;
        }, $template);
    }

    private function getFieldValue(array $fields, string $key): ?string
    {
        // Direct match
        if (isset($fields[$key]))
            return $fields[$key];

        // Case-insensitive match
        foreach ($fields as $k => $v) {
            if (strcasecmp($k, $key) === 0) {
                return $v;
            }
        }

        return null;
    }
}
