<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Services;

use Jegex\LaravelSeo\Schemas\BaseSchema;
use Jegex\LaravelSeo\Schemas\ArticleSchema;
use Jegex\LaravelSeo\Schemas\WebSiteSchema;
use Jegex\LaravelSeo\Schemas\OrganizationSchema;
use Jegex\LaravelSeo\Schemas\BreadcrumbListSchema;

class SchemaService
{
    /** @var array<int, BaseSchema> */
    protected array $schemas = [];

    /**
     * Add a schema to the collection.
     */
    public function add(BaseSchema $schema): self
    {
        $this->schemas[] = $schema;

        return $this;
    }

    /**
     * Create and add an Article schema.
     */
    public function article(): ArticleSchema
    {
        $schema = new ArticleSchema();
        $this->add($schema);

        return $schema;
    }

    /**
     * Create and add a WebSite schema.
     */
    public function website(): WebSiteSchema
    {
        $schema = new WebSiteSchema();
        $this->add($schema);

        return $schema;
    }

    /**
     * Create and add an Organization schema.
     */
    public function organization(): OrganizationSchema
    {
        $schema = new OrganizationSchema();
        $this->add($schema);

        return $schema;
    }

    /**
     * Create and add a BreadcrumbList schema.
     */
    public function breadcrumbs(): BreadcrumbListSchema
    {
        $schema = new BreadcrumbListSchema();
        $this->add($schema);

        return $schema;
    }

    /**
     * Render all schemas as script tags.
     */
    public function render(): string
    {
        if (empty($this->schemas)) {
            return '';
        }

        $output = [];

        foreach ($this->schemas as $schema) {
            $output[] = $schema->toScript();
        }

        return implode("\n", $output);
    }

    /**
     * Get all schemas as array.
     *
     * @return array<int, array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(fn (BaseSchema $schema) => $schema->toArray(), $this->schemas);
    }

    /**
     * Clear all schemas.
     */
    public function clear(): self
    {
        $this->schemas = [];

        return $this;
    }

    /**
     * Check if there are any schemas.
     */
    public function hasSchemas(): bool
    {
        return ! empty($this->schemas);
    }
}
