<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Services;

use Illuminate\Support\Str;
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
        $this->robots = config('seo.default_robots', ['index', 'follow']);
        $this->ogType = config('seo.default_og_type', 'website');
        $this->twitterCard = config('seo.default_twitter_card', 'summary_large_image');
        $this->webmasterTags = config('seo.webmaster_verification', []);
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

        // Get SEO entry if exists - menggunakan eager loading check untuk menghindari N+1
        $seoEntry = $model->seoEntry;

        if ($seoEntry) {
            $this->title = $seoEntry->getEffectiveTitle($defaultTitle);
            $this->description = $seoEntry->getEffectiveDescription($defaultDesc);
            $this->canonical = $seoEntry->getEffectiveCanonical($defaultCanonical);
            $this->ogTitle = $seoEntry->getEffectiveOgTitle($defaultTitle);
            $this->ogDescription = $seoEntry->getEffectiveOgDescription($defaultDesc);
            $this->ogImage = $seoEntry->getEffectiveOgImage($defaultImage);
            $this->twitterTitle = $seoEntry->getEffectiveTwitterTitle($defaultTitle);
            $this->twitterDescription = $seoEntry->getEffectiveTwitterDescription($defaultDesc);
            $this->twitterImage = $seoEntry->getEffectiveTwitterImage($defaultImage);
            $this->twitterCard = $seoEntry->twitter_card ?? $this->twitterCard;
            $this->robots = $seoEntry->robots ?? $this->robots;
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

    /**
     * Set robots directive.
     *
     * @param  array<int, string>|string  $robots
     */
    public function setRobots(array|string $robots): self
    {
        if (is_string($robots)) {
            $this->robots = array_map('trim', explode(',', $robots));
        } else {
            $this->robots = $robots;
        }

        return $this;
    }

    public function noIndex(): self
    {
        $this->removeRobotDirective('index');
        $this->addRobotDirective('noindex');

        return $this;
    }

    public function noFollow(): self
    {
        $this->removeRobotDirective('follow');
        $this->addRobotDirective('nofollow');

        return $this;
    }

    protected function addRobotDirective(string $directive): void
    {
        if (! in_array($directive, $this->robots, true)) {
            $this->robots[] = $directive;
        }
    }

    protected function removeRobotDirective(string $directive): void
    {
        $this->robots = array_filter(
            $this->robots,
            fn ($robot) => $robot !== $directive
        );
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

    /**
     * Render meta tags dengan output buffering untuk performa lebih baik.
     */
    public function render(): string
    {
        return Str::implode($this->buildTags(), "\n");
    }

    /**
     * Build array of meta tags.
     *
     * @return array<int, string>
     */
    protected function buildTags(): array
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
        $canonical = $this->canonical ?? url()->current();
        if ($canonical) {
            $tags[] = sprintf('<link rel="canonical" href="%s">', e($canonical));
        }

        // Open Graph tags
        $this->buildOpenGraphTags($tags);

        // Twitter Card tags
        $this->buildTwitterTags($tags);

        // Webmaster verification tags
        $this->renderWebmasterTags($tags);

        return $tags;
    }

    /**
     * Build Open Graph meta tags.
     *
     * @param  array<int, string>  $tags
     */
    protected function buildOpenGraphTags(array &$tags): void
    {
        $siteName = config('seo.site_name', config('app.name'));
        if ($siteName) {
            $tags[] = sprintf('<meta property="og:site_name" content="%s">', e($siteName));
        }

        $this->addMetaTag($tags, 'og:title', $this->ogTitle);
        $this->addMetaTag($tags, 'og:description', $this->ogDescription);
        $this->addMetaTag($tags, 'og:image', $this->ogImage);
        $this->addMetaTag($tags, 'og:type', $this->ogType);
        $this->addMetaTag($tags, 'og:url', url()->current());
    }

    /**
     * Build Twitter Card meta tags.
     *
     * @param  array<int, string>  $tags
     */
    protected function buildTwitterTags(array &$tags): void
    {
        $this->addMetaTag($tags, 'twitter:card', $this->twitterCard);

        $twitterSite = config('seo.twitter_site');
        if ($twitterSite) {
            $tags[] = sprintf('<meta name="twitter:site" content="%s">', e($twitterSite));
        }

        $twitterCreator = config('seo.twitter_creator');
        if ($twitterCreator) {
            $tags[] = sprintf('<meta name="twitter:creator" content="%s">', e($twitterCreator));
        }

        $this->addMetaTag($tags, 'twitter:title', $this->twitterTitle);
        $this->addMetaTag($tags, 'twitter:description', $this->twitterDescription);
        $this->addMetaTag($tags, 'twitter:image', $this->twitterImage);
    }

    /**
     * Helper method to add a meta tag if value exists.
     *
     * @param  array<int, string>  $tags
     */
    protected function addMetaTag(array &$tags, string $name, ?string $content): void
    {
        if ($content) {
            $tags[] = sprintf('<meta %s content="%s">',
                str_starts_with($name, 'og:') ? 'property' : 'name',
                e($content)
            );
        }
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

    /**
     * Reset all values to defaults.
     */
    public function reset(): self
    {
        $this->model = null;
        $this->data = [];
        $this->title = null;
        $this->description = null;
        $this->canonical = null;
        $this->ogTitle = null;
        $this->ogDescription = null;
        $this->ogImage = null;
        $this->twitterTitle = null;
        $this->twitterDescription = null;
        $this->twitterImage = null;
        $this->initializeDefaults();

        return $this;
    }
}
