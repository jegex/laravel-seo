<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Schemas;

abstract class BaseSchema
{
    protected string $type;

    protected array $properties = [];

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Set a schema property.
     */
    public function set(string $key, mixed $value): self
    {
        $this->properties[$key] = $value;

        return $this;
    }

    /**
     * Get the schema output as array.
     */
    public function toArray(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => $this->type,
            ...$this->properties,
        ];
    }

    /**
     * Get the schema output as JSON string.
     */
    public function toJson(int $flags = JSON_UNESCAPED_SLASHES): string
    {
        return json_encode($this->toArray(), $flags);
    }

    /**
     * Render as script tag.
     */
    public function toScript(): string
    {
        return sprintf('<script type="application/ld+json">%s</script>', $this->toJson());
    }
}
