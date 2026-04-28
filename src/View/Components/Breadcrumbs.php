<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Breadcrumbs extends Component
{
    /**
     * @param array<int, array{name: string, url: string}> $items
     */
    public function __construct(
        public array $items = [],
        public bool $jsonLd = true,
    ) {
    }

    public function render(): View
    {
        return view('laravel-seo::components.breadcrumbs');
    }

    /**
     * Generate JSON-LD structured data for breadcrumbs.
     *
     * @return array<string, mixed>
     */
    public function getJsonLdSchema(): array
    {
        $itemListElements = [];

        foreach ($this->items as $index => $item) {
            $itemListElements[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElements,
        ];
    }
}
