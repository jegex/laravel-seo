<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Services;

use Illuminate\Support\Facades\Config;
use Jegex\LaravelSeo\Contracts\Seoable;
use Illuminate\Support\Str;

class SeoService
{
    protected ?Seoable $model = null;

    /**
     * Pemetaan antara kunci config dan metode di MetaTagService.
     */
    protected array $templateMapping = [
        'title'               => 'setTitle',
        'description'         => 'setDescription',
        'og:title'            => 'setOgTitle',
        'og:description'      => 'setOgDescription',
        'og:image'            => 'setOgImage',
        'twitter:title'       => 'setTwitterTitle',
        'twitter:description' => 'setTwitterDescription',
        'twitter:image'       => 'setTwitterImage',
    ];

    public function __construct(
        protected MetaTagService $metaTags,
        protected TemplateParserService $parser,
        protected SchemaService $schema,
    ) {}

    public function for(?Seoable $model): self
    {
        $this->model = $model;
        $this->metaTags->for($model);
        return $this;
    }

    /**
     * Menggunakan __call untuk mendelegasikan panggilan ke MetaTagService secara otomatis.
     * Ini menghilangkan kebutuhan akan puluhan proxy methods manual.
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (method_exists($this->metaTags, $name)) {
            $result = $this->metaTags->{$name}(...$arguments);
            return $result instanceof MetaTagService ? $this : $result;
        }

        throw new \BadMethodCallException("Method {$name} does not exist on " . static::class);
    }

    /**
     * Menerapkan template dengan cara yang dinamis dan efisien.
     */
    public function applyTemplate(string $type): self
    {
        $templates = Config::get('seo.templates', []);
        $config = $templates[$type] ?? null;

        if ($config) {
            foreach ($this->templateMapping as $key => $method) {
                if (isset($config[$key])) {
                    $this->{$method}($config[$key]);
                }
            }
        }

        return $this;
    }

    public function render(): string
    {
        return implode("\n", array_filter([
            $this->metaTags->render(),
            $this->schema->hasSchemas() ? $this->schema->render() : null,
        ]));
    }

    /**
     * Mendelegasikan pembuatan schema sepenuhnya ke SchemaService.
     */
    public function addSchema(string $type): mixed
    {
        // Biarkan SchemaService yang memutuskan cara membuat object schema
        // agar SeoService tidak perlu tahu detail implementasinya.
        return $this->schema->make($type);
    }

    public function meta(): MetaTagService
    {
        return $this->metaTags;
    }

    public function schema(): SchemaService
    {
        return $this->schema;
    }

    public function parser(): TemplateParserService
    {
        return $this->parser;
    }

    public function reset(): self
    {
        $this->model = null;
        $this->metaTags->reset(); // Pastikan MetaTagService punya method reset sendiri
        $this->schema->clear();

        return $this;
    }
}
