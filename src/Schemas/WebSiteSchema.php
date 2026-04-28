<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Schemas;

class WebSiteSchema extends BaseSchema
{
    public function __construct()
    {
        parent::__construct('WebSite');
    }

    public function name(string $name): self
    {
        return $this->set('name', $name);
    }

    public function url(string $url): self
    {
        return $this->set('url', $url);
    }

    public function description(string $description): self
    {
        return $this->set('description', $description);
    }

    public function publisher(string $name, ?string $logo = null): self
    {
        $publisher = [
            '@type' => 'Organization',
            'name' => $name,
        ];

        if ($logo) {
            $publisher['logo'] = $logo;
        }

        return $this->set('publisher', $publisher);
    }

    public function potentialActionSearch(string $target, string $queryInput = 'search_term_string'): self
    {
        return $this->set('potentialAction', [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => $target,
            ],
            'query-input' => "required name={$queryInput}",
        ]);
    }
}
