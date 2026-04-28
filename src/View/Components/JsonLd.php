<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class JsonLd extends Component
{
    /**
     * @param  array<string, mixed>  $schema
     */
    public function __construct(
        public array $schema = [],
        public ?string $type = null,
    ) {}

    public function render(): View
    {
        return view('laravel-seo::components.json-ld');
    }

    /**
     * Generate the JSON-LD script tag.
     */
    public function toScript(): string
    {
        $data = $this->getSchemaData();

        return sprintf(
            '<script type="application/ld+json">%s</script>',
            json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
        );
    }

    /**
     * Get the schema data.
     *
     * @return array<string, mixed>
     */
    public function getSchemaData(): array
    {
        $schema = $this->schema;

        // If type is provided, wrap the schema
        if ($this->type) {
            $schema['@context'] = 'https://schema.org';
            $schema['@type'] = $this->type;
        }

        return $schema;
    }
}
