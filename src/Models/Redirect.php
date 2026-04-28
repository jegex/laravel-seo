<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Redirect extends Model
{
    use HasFactory;

    protected $table = 'seo_redirects';

    protected $fillable = [
        'from_url',
        'to_url',
        'type',
        'hits',
        'last_accessed_at',
        'is_regex',
        'is_active',
    ];

    protected $casts = [
        'hits' => 'integer',
        'last_accessed_at' => 'datetime',
        'is_regex' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get active redirects.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Record a hit on this redirect.
     */
    public function recordHit(): void
    {
        $this->increment('hits');
        $this->update(['last_accessed_at' => now()]);
    }

    /**
     * Check if the given URL matches this redirect rule.
     */
    public function matches(string $url): bool
    {
        if ($this->is_regex) {
            return (bool) preg_match($this->from_url, $url);
        }

        return $this->from_url === $url;
    }

    /**
     * Get the redirect destination URL.
     * For regex redirects, applies the replacement.
     */
    public function getDestination(string $url): ?string
    {
        if ($this->type === 410) {
            return null; // 410 Gone has no destination
        }

        if ($this->is_regex) {
            return preg_replace($this->from_url, $this->to_url, $url);
        }

        return $this->to_url;
    }

    /**
     * Get the HTTP status code for this redirect.
     */
    public function getStatusCode(): int
    {
        return (int) $this->type;
    }
}
