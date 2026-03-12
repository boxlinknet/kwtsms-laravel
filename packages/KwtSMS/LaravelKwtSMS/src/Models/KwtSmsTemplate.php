<?php

namespace KwtSMS\Laravel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $event_type
 * @property string $locale
 * @property string $body
 * @property bool $is_active
 *
 * @method static Builder active()
 * @method static Builder forEvent(string $eventType, string $locale = 'en')
 */
class KwtSmsTemplate extends Model
{
    protected $table = 'kwtsms_templates';

    protected $fillable = [
        'name',
        'event_type',
        'locale',
        'body',
        'is_active',
    ];

    public function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope to only active templates.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to templates for a specific event type and locale.
     */
    public function scopeForEvent(Builder $query, string $eventType, string $locale = 'en'): Builder
    {
        return $query->where('event_type', $eventType)->where('locale', $locale);
    }

    /**
     * Replace template placeholders with actual values.
     *
     * @param  array<string, string>  $data
     */
    public function render(array $data = []): string
    {
        $body = $this->body;

        foreach ($data as $key => $value) {
            $body = str_replace('{'.$key.'}', (string) $value, $body);
        }

        return $body;
    }
}
