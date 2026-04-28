<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface Seoable
{
    /**
     * Get the SEO entry associated with this model.
     */
    public function seoEntry(): MorphOne;

    /**
     * Get the default SEO title for this model.
     */
    public function getSeoTitle(): ?string;

    /**
     * Get the default SEO description for this model.
     */
    public function getSeoDescription(): ?string;

    /**
     * Get the default canonical URL for this model.
     */
    public function getSeoCanonical(): ?string;

    /**
     * Get the default OG image for this model.
     */
    public function getSeoOgImage(): ?string;

    /**
     * Get the model data for template variable parsing.
     *
     * @return array<string, mixed>
     */
    public function getSeoData(): array;
}
