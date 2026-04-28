<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
use Jegex\LaravelSeo\Contracts\Seoable;

class MetaTagService
{
    protected ?Seoable $model = null;

    protected array $data = [];

    protected ?string $title = null;

    protected ?string $description = null;

    protected ?string $canonical = null;

    protected array $robots = [];

    protected ?string $ogTitle = null;

    protected ?string $ogDescription = null;

    protected ?string $ogImage = null;

    protected ?string $ogType = null;

    protected ?string $twitterTitle = null;

    protected ?string $twitterDescription = null;

    protected ?string $twitterImage = null;

    protected ?string $twitterCard = null;

    protected array $webmasterTags = [];

    public function __construct(
        protected TemplateParserService $parser
    ) {
        $this->initializeDefaults();
    }

    protected function initializeDefaults(): void
    {
        $this->robots = Config::get('seo.default_robots', ['index', 'follow']);
        $this->ogType = Config::get('seo.default_og_type', 'website');
        $this->twitterCard = Config::get('seo.default_twitter_card', 'summary_large_image');
        $this->webmasterTags = Config::get('seo.webmaster_verification', []);
    }

    public function for(?Seoable $model): self
    {
        $this->model = $model;

        if ($model) {
            $this->data = $model->getSeoData();
            $this->applyModelDefaults($model);
        }

        return $this;
    }

    protected function applyModelDefaults(Seoable $model): void
    {
        // Get model's default values
        $defaultTitle = $model->getSeoTitle();
        $defaultDesc = $model->getSeoDescription();
        $defaultCanonical = $model->getSeoCanonical();
        $defaultImage = $model->getSeoOgImage();

        // Get SEO entry if exists
        if ($model->seoEntry && $model->seoEntry()->exists()) {
            $entry = $model->seoEntry;
            $this->title = $entry->getEffectiveTitle($defaultTitle);
            $this->description = $entry->getEffectiveDescription($defaultDesc);
            $this->canonical = $entry->getEffectiveCanonical($defaultCanonical);
            $this->ogTitle = $entry->getEffectiveOgTitle($defaultTitle);
            $this->ogDescription = $entry->getEffectiveOgDescription($defaultDesc);
            $this->ogImage = $entry->getEffectiveOgImage($defaultImage);
            $this->twitterTitle = $entry->getEffectiveTwitterTitle($defaultTitle);
            $this->twitterDescription = $entry->getEffectiveTwitterDescription($defaultDesc);
            $this->twitterImage = $entry->getEffectiveTwitterImage($defaultImage);
            $this->twitterCard = $entry->twitter_card ?? $this->twitterCard;
            $this->robots = $entry->robots ?? $this->robots;
        } else {
            // Use model defaults directly
            $this->title = $defaultTitle;
            $this->description = $defaultDesc;
            $this->canonical = $defaultCanonical;
            $this->ogTitle = $defaultTitle;
            $this->ogDescription = $defaultDesc;
            $this->ogImage = $defaultImage;
            $this->twitterTitle = $defaultTitle;
            $this->twitterDescription = $defaultDesc;
            $this->twitterImage = $defaultImage;
        }
    }

    public function setTitle(?string $title, bool $parseTemplate = true): self
    {
        if ($parseTemplate && $title) {
            $title = $this->parser->parse($title, $this->data);
        }
        $this->title = $title;

        return $this;
    }

    public function setDescription(?string $description, bool $parseTemplate = true): self
    {
        if ($parseTemplate && $description) {
            $description = $this->parser->parse($description, $this->data);
        }
        $this->description = $description;

        return $this;
    }

    public function setCanonical(?string $canonical): self
    {
        $this->canonical = $canonical;

        return $this;
    }

    public function setRobots(array $robots): self
    {
        $this->robots = $robots;

        return $this;
    }

    public function setOgTitle(?string $title, bool $parseTemplate = true): self
    {
        if ($parseTemplate && $title) {
            $title = $this->parser->parse($title, $this->data);
        }
        $this->ogTitle = $title;

        return $this;
    }

    public function setOgDescription(?string $description, bool $parseTemplate = true): self
    {
        if ($parseTemplate && $description) {
            $description = $this->parser->parse($description, $this->data);
        }
        $this->ogDescription = $description;

        return $this;
    }

    public function setOgImage(?string $image): self
    {
        $this->ogImage = $image ? url($image) : null;

        return $this;
    }

    public function setOgType(?string $type): self
    {
        $this->ogType = $type;

        return $this;
    }

    public function setTwitterTitle(?string $title, bool $parseTemplate = true): self
    {
        if ($parseTemplate && $title) {
            $title = $this->parser->parse($title, $this->data);
        }
        $this->twitterTitle = $title;

        return $this;
    }

    public function setTwitterDescription(?string $description, bool $parseTemplate = true): self
    {
        if ($parseTemplate && $description) {
            $description = $this->parser->parse($description, $this->data);
        }
        $this->twitterDescription = $description;

        return $this;
    }

    public function setTwitterImage(?string $image): self
    {
        $this->twitterImage = $image ? url($image) : null;

        return $this;
    }

    public function setTwitterCard(?string $card): self
    {
        $this->twitterCard = $card;

        return $this;
    }

    public function render(): string
    {
        $tags = [];

        // Basic meta tags
        if ($this->title) {
            $tags[] = sprintf('<title>%s</title>', e($this->title));
        }

        if ($this->description) {
            $tags[] = sprintf('<meta name="description" content="%s">', e($this->description));
        }

        // Robots
        if (! empty($this->robots)) {
            $tags[] = sprintf('<meta name="robots" content="%s">', implode(', ', $this->robots));
        }

        // Canonical
        $canonical = $this->canonical ?? URL::current();
        if ($canonical) {
            $tags[] = sprintf('<link rel="canonical" href="%s">', e($canonical));
        }

        // Open Graph
        $tags[] = sprintf('<meta property="og:site_name" content="%s">', e(Config::get('seo.site_name', config('app.name'))));

        if ($this->ogTitle) {
            $tags[] = sprintf('<meta property="og:title" content="%s">', e($this->ogTitle));
        }

        if ($this->ogDescription) {
            $tags[] = sprintf('<meta property="og:description" content="%s">', e($this->ogDescription));
        }

        if ($this->ogImage) {
            $tags[] = sprintf('<meta property="og:image" content="%s">', e($this->ogImage));
        }

        if ($this->ogType) {
            $tags[] = sprintf('<meta property="og:type" content="%s">', e($this->ogType));
        }

        $tags[] = sprintf('<meta property="og:url" content="%s">', e(URL::current()));

        // Twitter Cards
        if ($this->twitterCard) {
            $tags[] = sprintf('<meta name="twitter:card" content="%s">', e($this->twitterCard));
        }

        $twitterSite = Config::get('seo.twitter_site');
        if ($twitterSite) {
            $tags[] = sprintf('<meta name="twitter:site" content="%s">', e($twitterSite));
        }

        $twitterCreator = Config::get('seo.twitter_creator');
        if ($twitterCreator) {
            $tags[] = sprintf('<meta name="twitter:creator" content="%s">', e($twitterCreator));
        }

        if ($this->twitterTitle) {
            $tags[] = sprintf('<meta name="twitter:title" content="%s">', e($this->twitterTitle));
        }

        if ($this->twitterDescription) {
            $tags[] = sprintf('<meta name="twitter:description" content="%s">', e($this->twitterDescription));
        }

        if ($this->twitterImage) {
            $tags[] = sprintf('<meta name="twitter:image" content="%s">', e($this->twitterImage));
        }

        // Webmaster verification
        $this->renderWebmasterTags($tags);

        return implode("\n", $tags);
    }

    protected function renderWebmasterTags(array &$tags): void
    {
        $webmasterTags = [
            'google' => 'google-site-verification',
            'bing' => 'msvalidate.01',
            'pinterest' => 'p:domain_verify',
            'yandex' => 'yandex-verification',
            'baidu' => 'baidu-site-verification',
            'norton' => 'norton-safeweb-site-verification',
        ];

        foreach ($webmasterTags as $key => $metaName) {
            $code = $this->webmasterTags[$key] ?? null;
            if ($code) {
                $tags[] = sprintf('<meta name="%s" content="%s">', $metaName, e($code));
            }
        }

        // Custom verification tags
        $customTags = $this->webmasterTags['custom'] ?? [];
        foreach ($customTags as $customTag) {
            if (is_string($customTag)) {
                $tags[] = $customTag;
            }
        }
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCanonical(): ?string
    {
        return $this->canonical;
    }

    public function getRobots(): array
    {
        return $this->robots;
    }

    public function getOgTitle(): ?string
    {
        return $this->ogTitle ?? $this->title;
    }

    public function getOgDescription(): ?string
    {
        return $this->ogDescription ?? $this->description;
    }

    public function getOgImage(): ?string
    {
        return $this->ogImage;
    }
}
