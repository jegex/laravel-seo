<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Jegex\LaravelSeo\Contracts\TemplateParser as TemplateParserContract;

class TemplateParserService implements TemplateParserContract
{
    /**
     * Custom variable callbacks.
     *
     * @var array<string, callable>
     */
    protected array $customVariables = [];

    /**
     * Cache untuk menyimpan gabungan semua variabel agar tidak dihitung berulang kali.
     */
    protected ?array $resolvedVariables = null;

    /**
     * Get localized config value.
     * Supports both single language (string) and multilanguage (array) formats.
     *
     * @param  string  $key  Config key (e.g., 'seo.site_name')
     * @param  mixed  $default  Default value if config not found
     */
    protected function getLocalizedConfig(string $key, mixed $default = null): mixed
    {
        $value = Config::get($key, $default);

        // If value is array, treat as multilanguage and return based on current locale
        if (is_array($value)) {
            $locale = App::getLocale();
            $fallback = Config::get('app.fallback_locale', 'en');

            return $value[$locale] ?? $value[$fallback] ?? reset($value) ?? $default;
        }

        // Return as-is for single language (string) format
        return $value;
    }

    /**
     * Parse a template string and replace variables with actual values.
     *
     * @param  string  $template  The template string (e.g., "%title% %sep% %sitename%")
     * @param  array<string, mixed>  $data  Data to use for variable replacement
     */
    public function parse(string $template, array $data = []): string
    {
        $definitions = $this->getAllDefinitions();
        $replacements = [];

        preg_match_all('/%[a-z0-9_]+%/', $template, $matches);
        $placeholdersInTemplate = array_unique($matches[0] ?? []);

        foreach ($placeholdersInTemplate as $placeholder) {
            if (isset($definitions[$placeholder]) && is_callable($definitions[$placeholder])) {
                $replacements[$placeholder] = $definitions[$placeholder]($data);
            }
        }

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );
    }

    /**
     * Menggabungkan built-in, config, dan runtime variables sekali saja.
     */
    protected function getAllDefinitions(): array
    {
        if ($this->resolvedVariables === null) {
            $this->resolvedVariables = array_merge(
                $this->getVariables(),
                Config::get('seo.custom_variables', []),
                $this->customVariables
            );
        }

        return $this->resolvedVariables;
    }

    /**
     * Register a custom variable parser.
     *
     * @param  string  $variable  The variable name (e.g., "%custom%")
     * @param  callable  $callback  Callback that receives data and returns string
     */
    public function registerVariable(string $variable, callable $callback): void
    {
        $this->customVariables[$variable] = $callback;
        $this->resolvedVariables = null;
    }

    /**
     * Get all registered built-in variables.
     *
     * @return array<string, callable>
     */
    public function getVariables(): array
    {
        return [
            '%title%'           => fn ($data) => $data['title'] ?? '',
            '%ptitle%'          => fn ($data) => $data['title'] ?? '',
            '%sep%'             => fn () => Config::get('seo.separator', ' - '),
            '%sitename%'        => fn () => $this->getLocalizedConfig('seo.site_name', config('app.name')),
            '%sitedesc%'        => fn () => $this->getLocalizedConfig('seo.site_description', ''),
            '%currentdate%'     => fn () => now()->format('F j, Y'),
            '%currentday%'      => fn () => now()->format('j'),
            '%currentmonth%'    => fn () => now()->format('F'),
            '%currentyear%'     => fn () => now()->format('Y'),
            '%currenttime%'     => fn () => now()->format('g:i a'),
            '%description%'     => fn ($data) => $data['description'] ?? '',
            '%excerpt%'         => fn ($data) => $this->excerpt($data['description'] ?? ''),
            '%excerpt_only%'    => fn ($data) => $this->excerpt($data['description'] ?? ''),
            '%author%'          => fn ($data) => $data['author'] ?? '',
            '%authorfirstname%' => function ($data) {
                return Str::before($data['author'] ?? '', ' ');
            },
            '%authorlastname%'  => function ($data) {
                return Str::afterLast($data['author'] ?? '', ' ');
            },
            '%categories%'      => fn ($data) => is_array($data['categories'] ?? null)
                ? implode(', ', $data['categories'])
                : ($data['categories'] ?? ''),
            '%tags%'            => fn ($data) => is_array($data['tags'] ?? null)
                ? implode(', ', $data['tags'])
                : ($data['tags'] ?? ''),
            '%id%'              => fn ($data) => $data['id'] ?? '',
            '%modified%'        => fn ($data) => $data['modified'] ?? '',
        ];
    }

    /**
     * Create an excerpt from text using Laravel Str helper.
     */
    protected function excerpt(string $text, int $length = 160): string
    {
        return Str::limit(strip_tags($text), $length);
    }
}
