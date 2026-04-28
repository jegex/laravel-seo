<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeoEntry extends Model
{
    use HasFactory;

    protected $table = 'seo_entries';

    protected $fillable = [
        'model_type',
        'model_id',
        'title',
        'description',
        'keywords',
        'canonical',
        'robots',
        'og_title',
        'og_description',
        'og_image',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'twitter_card',
        'schema_type',
        'schema_data',
        'focus_keyword',
        'seo_score',
    ];

    protected $casts = [
        'keywords' => 'array',
        'robots' => 'array',
        'schema_data' => 'array',
        'seo_score' => 'integer',
    ];

    /**
     * Get the parent SEO-able model.
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the effective title (fallback to model's default if not set).
     */
    public function getEffectiveTitle(?string $default = null): ?string
    {
        return $this->title ?? $default;
    }

    /**
     * Get the effective description (fallback to model's default if not set).
     */
    public function getEffectiveDescription(?string $default = null): ?string
    {
        return $this->description ?? $default;
    }

    /**
     * Get the effective canonical URL.
     */
    public function getEffectiveCanonical(?string $default = null): ?string
    {
        return $this->canonical ?? $default;
    }

    /**
     * Get the effective OG title.
     */
    public function getEffectiveOgTitle(?string $default = null): ?string
    {
        return $this->og_title ?? $this->title ?? $default;
    }

    /**
     * Get the effective OG description.
     */
    public function getEffectiveOgDescription(?string $default = null): ?string
    {
        return $this->og_description ?? $this->description ?? $default;
    }

    /**
     * Get the effective OG image.
     */
    public function getEffectiveOgImage(?string $default = null): ?string
    {
        return $this->og_image ?? $default;
    }

    /**
     * Get the effective Twitter title.
     */
    public function getEffectiveTwitterTitle(?string $default = null): ?string
    {
        return $this->twitter_title ?? $this->og_title ?? $this->title ?? $default;
    }

    /**
     * Get the effective Twitter description.
     */
    public function getEffectiveTwitterDescription(?string $default = null): ?string
    {
        return $this->twitter_description ?? $this->og_description ?? $this->description ?? $default;
    }

    /**
     * Get the effective Twitter image.
     */
    public function getEffectiveTwitterImage(?string $default = null): ?string
    {
        return $this->twitter_image ?? $this->og_image ?? $default;
    }

    /**
     * Get the robots directive as a comma-separated string.
     */
    public function getRobotsDirective(?array $default = null): ?string
    {
        $robots = $this->robots ?? $default ?? ['index', 'follow'];

        return implode(', ', $robots);
    }

    /**
     * Analyze SEO and return score.
     *
     * @return array<string, mixed>
     */
    public function analyze(): array
    {
        $content = '';

        // Try to get content from the model
        if ($this->model && method_exists($this->model, 'getContent')) {
            $content = $this->model->getContent();
        }

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'focus_keyword' => $this->focus_keyword,
        ];

        return app(\Jegex\LaravelSeo\Services\AnalyzerService::class)
            ->analyze($content, $data);
    }

    /**
     * Calculate and save SEO score.
     */
    public function calculateScore(): void
    {
        $analysis = $this->analyze();
        $this->seo_score = $analysis['score'];
        $this->save();
    }

    /**
     * Get SEO score with visual indicator.
     */
    public function getScoreLabel(): string
    {
        $score = $this->seo_score ?? 0;

        if ($score >= 80) {
            return 'Good';
        }

        if ($score >= 50) {
            return 'Needs Improvement';
        }

        return 'Poor';
    }

    /**
     * Get score color class.
     */
    public function getScoreColor(): string
    {
        $score = $this->seo_score ?? 0;

        if ($score >= 80) {
            return 'success';
        }

        if ($score >= 50) {
            return 'warning';
        }

        return 'danger';
    }
}
