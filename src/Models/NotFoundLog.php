<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotFoundLog extends Model
{
    use HasFactory;

    protected $table = 'seo_404_logs';

    protected $fillable = [
        'url',
        'referer',
        'user_agent',
        'ip_address',
        'hit_count',
        'last_hit_at',
    ];

    protected $casts = [
        'hit_count' => 'integer',
        'last_hit_at' => 'datetime',
    ];

    /**
     * Record or increment a 404 hit.
     */
    public static function record(string $url, ?string $referer = null, ?string $userAgent = null, ?string $ipAddress = null): self
    {
        $log = self::where('url', $url)->first();

        if ($log) {
            $log->increment('hit_count');
            $log->update([
                'referer' => $referer,
                'user_agent' => $userAgent,
                'ip_address' => $ipAddress,
                'last_hit_at' => now(),
            ]);

            return $log;
        }

        return self::create([
            'url' => $url,
            'referer' => $referer,
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress,
            'hit_count' => 1,
            'last_hit_at' => now(),
        ]);
    }

    /**
     * Get popular 404 errors (most hits).
     */
    public function scopePopular($query, int $limit = 10)
    {
        return $query->orderByDesc('hit_count')->limit($limit);
    }

    /**
     * Get recent 404 errors.
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderByDesc('last_hit_at')->limit($limit);
    }
}
