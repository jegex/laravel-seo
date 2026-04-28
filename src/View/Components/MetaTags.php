<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;
use Jegex\LaravelSeo\Services\SeoService;

class MetaTags extends Component
{
    public function __construct(
        protected SeoService $seoService,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $canonical = null,
    ) {
        // Apply any passed attributes to the service
        if ($title) {
            $seoService->setTitle($title);
        }

        if ($description) {
            $seoService->setDescription($description);
        }

        if ($canonical) {
            $seoService->setCanonical($canonical);
        }
    }

    public function render(): View
    {
        return view('laravel-seo::components.meta-tags');
    }
}
