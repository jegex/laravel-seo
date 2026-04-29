<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Traits;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Jegex\LaravelSeo\Contracts\Seoable;
use Jegex\LaravelSeo\Models\SeoEntry;

trait HasSeo
{
    /**
     * Boot the HasSeo trait.
     */
    public static function bootHasSeo(): void
    {
        static::deleting(function (Seoable $model) {
            $model->seoEntry()->delete();
        });
    }

    /**
     * Get the SEO entry associated with this model.
     */
    public function seoEntry(): MorphOne
    {
        return $this->morphOne(SeoEntry::class, 'model');
    }

    /**
     * Get or create the SEO entry for this model.
     */
    public function getOrCreateSeoEntry(): SeoEntry
    {
        return $this->seoEntry()->firstOrCreate([]);
    }

    /**
     * Get the default SEO title for this model.
     * Override this method in your model to customize.
     */
    public function getSeoTitle(): ?string
    {
        if (property_exists($this, 'seoTitleAttribute') && $this->seoTitleAttribute) {
            return $this->getAttribute($this->seoTitleAttribute);
        }

        if ($this->hasAttribute('title')) {
            return $this->getAttribute('title');
        }

        if ($this->hasAttribute('name')) {
            return $this->getAttribute('name');
        }

        return null;
    }

    /**
     * Get the default SEO description for this model.
     * Override this method in your model to customize.
     */
    public function getSeoDescription(): ?string
    {
        if (property_exists($this, 'seoDescriptionAttribute') && $this->seoDescriptionAttribute) {
            return $this->getAttribute($this->seoDescriptionAttribute);
        }

        if ($this->hasAttribute('description')) {
            return $this->getAttribute('description');
        }

        if ($this->hasAttribute('excerpt')) {
            return $this->getAttribute('excerpt');
        }

        if ($this->hasAttribute('summary')) {
            return $this->getAttribute('summary');
        }

        if ($this->hasAttribute('content')) {
            $content = strip_tags($this->getAttribute('content'));

            return substr($content, 0, 160);
        }

        return null;
    }

    /**
     * Get the default canonical URL for this model.
     * Override this method in your model to customize.
     */
    public function getSeoCanonical(): ?string
    {
        if (method_exists($this, 'getRouteKey')) {
            return url($this->getRouteKey());
        }

        return null;
    }

    /**
     * Get the default OG image for this model.
     * Override this method in your model to customize.
     */
    public function getSeoOgImage(): ?string
    {
        if (property_exists($this, 'seoImageAttribute') && $this->seoImageAttribute) {
            $image = $this->getAttribute($this->seoImageAttribute);

            return $image ? asset($image) : null;
        }

        if ($this->hasAttribute('image')) {
            $image = $this->getAttribute('image');

            return $image ? asset($image) : null;
        }

        if ($this->hasAttribute('featured_image')) {
            $image = $this->getAttribute('featured_image');

            return $image ? asset($image) : null;
        }

        return null;
    }

    /**
     * Get the model data for template variable parsing.
     *
     * @return array<string, mixed>
     */
    public function getSeoData(): array
    {
        return [
            'id' => $this->getKey(),
            'title' => $this->getSeoTitle(),
            'description' => $this->getSeoDescription(),
            'excerpt' => $this->getSeoDescription(),
            'excerpt_only' => $this->getSeoDescription(),
            'canonical' => $this->getSeoCanonical(),
            'created_at' => $this->getAttribute('created_at')?->toIso8601String(),
            'updated_at' => $this->getAttribute('updated_at')?->toIso8601String(),
            'modified' => $this->getAttribute('updated_at')?->toIso8601String(),
        ];
    }

    /**
     * Check if model has the given attribute.
     */
    public function hasAttribute(string $attribute): bool
    {
        return array_key_exists($attribute, $this->getAttributes());
    }

    /**
     * Set the SEO entry attributes.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function setSeo(array $attributes): SeoEntry
    {
        $entry = $this->getOrCreateSeoEntry();
        $entry->fill($attributes);
        $entry->save();

        return $entry;
    }

    /**
     * Clear the SEO entry for this model.
     */
    public function clearSeo(): void
    {
        $this->seoEntry()->delete();
    }
}
