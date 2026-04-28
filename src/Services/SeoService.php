<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Services;

use Illuminate\Support\Facades\Config;
use Jegex\LaravelSeo\Contracts\Seoable;

class SeoService
{
    protected ?Seoable $model = null;

    protected ?array $templateConfig = null;

    public function __construct(
        protected MetaTagService $metaTags,
        protected TemplateParserService $parser,
        protected SchemaService $schema,
    ) {}

    /**
     * Set the SEO-able model for context.
     */
    public function for(?Seoable $model): self
    {
        $this->model = $model;
        $this->metaTags->for($model);

        return $this;
    }

    /**
     * Set the title.
     */
    public function setTitle(?string $title, bool $parseTemplate = true): self
    {
        $this->metaTags->setTitle($title, $parseTemplate);

        return $this;
    }

    /**
     * Set the description.
     */
    public function setDescription(?string $description, bool $parseTemplate = true): self
    {
        $this->metaTags->setDescription($description, $parseTemplate);

        return $this;
    }

    /**
     * Set the canonical URL.
     */
    public function setCanonical(?string $canonical): self
    {
        $this->metaTags->setCanonical($canonical);

        return $this;
    }

    /**
     * Set robots directives.
     */
    public function setRobots(array $robots): self
    {
        $this->metaTags->setRobots($robots);

        return $this;
    }

    /**
     * Set Open Graph title.
     */
    public function setOgTitle(?string $title, bool $parseTemplate = true): self
    {
        $this->metaTags->setOgTitle($title, $parseTemplate);

        return $this;
    }

    /**
     * Set Open Graph description.
     */
    public function setOgDescription(?string $description, bool $parseTemplate = true): self
    {
        $this->metaTags->setOgDescription($description, $parseTemplate);

        return $this;
    }

    /**
     * Set Open Graph image.
     */
    public function setOgImage(?string $image): self
    {
        $this->metaTags->setOgImage($image);

        return $this;
    }

    /**
     * Set Open Graph type.
     */
    public function setOgType(?string $type): self
    {
        $this->metaTags->setOgType($type);

        return $this;
    }

    /**
     * Set Twitter title.
     */
    public function setTwitterTitle(?string $title, bool $parseTemplate = true): self
    {
        $this->metaTags->setTwitterTitle($title, $parseTemplate);

        return $this;
    }

    /**
     * Set Twitter description.
     */
    public function setTwitterDescription(?string $description, bool $parseTemplate = true): self
    {
        $this->metaTags->setTwitterDescription($description, $parseTemplate);

        return $this;
    }

    /**
     * Set Twitter image.
     */
    public function setTwitterImage(?string $image): self
    {
        $this->metaTags->setTwitterImage($image);

        return $this;
    }

    /**
     * Set Twitter card type.
     */
    public function setTwitterCard(?string $card): self
    {
        $this->metaTags->setTwitterCard($card);

        return $this;
    }

    /**
     * Apply template configuration for a given type.
     */
    public function applyTemplate(string $type): self
    {
        $templates = Config::get('seo.templates', []);
        $template = $templates[$type] ?? null;

        if ($template) {
            if (isset($template['title'])) {
                $this->setTitle($template['title']);
            }

            if (isset($template['description'])) {
                $this->setDescription($template['description']);
            }

            if (isset($template['og:title'])) {
                $this->setOgTitle($template['og:title']);
            }

            if (isset($template['og:description'])) {
                $this->setOgDescription($template['og:description']);
            }

            if (isset($template['og:image'])) {
                $this->setOgImage($template['og:image']);
            }

            if (isset($template['twitter:title'])) {
                $this->setTwitterTitle($template['twitter:title']);
            }

            if (isset($template['twitter:description'])) {
                $this->setTwitterDescription($template['twitter:description']);
            }

            if (isset($template['twitter:image'])) {
                $this->setTwitterImage($template['twitter:image']);
            }
        }

        return $this;
    }

    /**
     * Render all SEO meta tags and schemas.
     */
    public function render(): string
    {
        $output = [];
        $output[] = $this->metaTags->render();

        if ($this->schema->hasSchemas()) {
            $output[] = $this->schema->render();
        }

        return implode("\n", array_filter($output));
    }

    /**
     * Get the SchemaService instance.
     */
    public function schema(): SchemaService
    {
        return $this->schema;
    }

    /**
     * Add a JSON-LD schema.
     *
     * @param  string  $type  Schema type (article, website, organization, breadcrumbs)
     */
    public function addSchema(string $type): Schemas\BaseSchema
    {
        return match ($type) {
            'article' => $this->schema->article(),
            'website' => $this->schema->website(),
            'organization' => $this->schema->organization(),
            'breadcrumbs' => $this->schema->breadcrumbs(),
            default => throw new \InvalidArgumentException("Unknown schema type: {$type}"),
        };
    }

    /**
     * Get the MetaTagService instance.
     */
    public function meta(): MetaTagService
    {
        return $this->metaTags;
    }

    /**
     * Get the TemplateParserService instance.
     */
    public function parser(): TemplateParserService
    {
        return $this->parser;
    }

    /**
     * Reset the service state.
     */
    public function reset(): self
    {
        $this->model = null;
        $this->metaTags = app(MetaTagService::class);
        $this->schema->clear();

        return $this;
    }
}
