<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Services;

use Jegex\LaravelSeo\Schemas\BreadcrumbListSchema;

class BreadcrumbService
{
    /** @var array<int, array{name: string, url: string, image?: string}> */
    protected array $items = [];

    /**
     * Add a breadcrumb item.
     */
    public function add(string $name, string $url, ?string $image = null): self
    {
        $this->items[] = [
            'name' => $name,
            'url' => $url,
            'image' => $image,
        ];

        return $this;
    }

    /**
     * Add a home item.
     */
    public function home(string $name = 'Home', string $url = '/'): self
    {
        return $this->add($name, url($url));
    }

    /**
     * Generate breadcrumb items from current route.
     */
    public function fromRoute(): self
    {
        $route = request()->route();

        if (! $route) {
            return $this;
        }

        $routeName = $route->getName();

        if (! $routeName) {
            return $this;
        }

        $parts = explode('.', $routeName);

        // Add home
        $this->home();

        // Build breadcrumb from route parts
        $currentUrl = '/';
        $currentName = '';

        foreach ($parts as $index => $part) {
            if ($index === count($parts) - 1) {
                // Last part - current page
                $this->add(ucfirst($part), url()->current());
            } else {
                $currentUrl .= $part.'/';
                $this->add(ucfirst($part), url($currentUrl));
            }
        }

        return $this;
    }

    /**
     * Get all breadcrumb items.
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * Render HTML breadcrumb.
     */
    public function renderHtml(?string $class = 'breadcrumb'): string
    {
        if (empty($this->items)) {
            return '';
        }

        $html = '<nav aria-label="breadcrumb"><ol class="'.$class.'">';

        foreach ($this->items as $index => $item) {
            $isLast = $index === count($this->items) - 1;
            $html .= '<li class="breadcrumb-item'.($isLast ? ' active' : '').'"'.($isLast ? ' aria-current="page"' : '').'>';

            if ($isLast) {
                $html .= e($item['name']);
            } else {
                $html .= '<a href="'.e($item['url']).'">'.e($item['name']).'</a>';
            }

            $html .= '</li>';
        }

        $html .= '</ol></nav>';

        return $html;
    }

    /**
     * Generate JSON-LD schema.
     */
    public function generateSchema(): BreadcrumbListSchema
    {
        $schema = new BreadcrumbListSchema;

        foreach ($this->items as $item) {
            $schema->addItem($item['name'], $item['url'], $item['image'] ?? null);
        }

        return $schema;
    }

    /**
     * Render JSON-LD script.
     */
    public function renderSchema(): string
    {
        if (empty($this->items)) {
            return '';
        }

        return $this->generateSchema()->toScript();
    }

    /**
     * Clear all items.
     */
    public function clear(): self
    {
        $this->items = [];

        return $this;
    }

    /**
     * Check if has items.
     */
    public function hasItems(): bool
    {
        return ! empty($this->items);
    }
}
