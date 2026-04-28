<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Schemas;

class BreadcrumbListSchema extends BaseSchema
{
    /** @var array<int, array<string, mixed>> */
    protected array $items = [];

    public function __construct()
    {
        parent::__construct('BreadcrumbList');
    }

    public function addItem(string $name, string $url, ?string $image = null): self
    {
        $item = [
            '@type' => 'ListItem',
            'position' => count($this->items) + 1,
            'name' => $name,
            'item' => [
                '@type' => 'WebPage',
                '@id' => $url,
                'url' => $url,
                'name' => $name,
            ],
        ];

        if ($image) {
            $item['item']['image'] = $image;
        }

        $this->items[] = $item;

        return $this;
    }

    public function toArray(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => $this->type,
            'itemListElement' => $this->items,
        ];
    }
}
