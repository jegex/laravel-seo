<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Schemas;

class ArticleSchema extends BaseSchema
{
    public function __construct()
    {
        parent::__construct('Article');
    }

    public function headline(string $headline): self
    {
        return $this->set('headline', $headline);
    }

    public function description(string $description): self
    {
        return $this->set('description', $description);
    }

    public function image(string $url): self
    {
        return $this->set('image', $url);
    }

    public function author(string $name, ?string $url = null): self
    {
        $author = [
            '@type' => 'Person',
            'name' => $name,
        ];

        if ($url) {
            $author['url'] = $url;
        }

        return $this->set('author', $author);
    }

    public function publisher(string $name, string $logoUrl): self
    {
        return $this->set('publisher', [
            '@type' => 'Organization',
            'name' => $name,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $logoUrl,
            ],
        ]);
    }

    public function datePublished(string $date): self
    {
        return $this->set('datePublished', $date);
    }

    public function dateModified(string $date): self
    {
        return $this->set('dateModified', $date);
    }

    public function url(string $url): self
    {
        return $this->set('url', $url);
    }

    public function keywords(array $keywords): self
    {
        return $this->set('keywords', implode(',', $keywords));
    }

    public function articleBody(string $body): self
    {
        return $this->set('articleBody', $body);
    }

    public function wordCount(int $count): self
    {
        return $this->set('wordCount', $count);
    }
}
