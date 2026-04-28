<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Services;

use Illuminate\Support\Facades\Config;
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
     * Parse a template string and replace variables with actual values.
     *
     * @param  string  $template  The template string (e.g., "%title% %sep% %sitename%")
     * @param  array<string, mixed>  $data  Data to use for variable replacement
     */
    public function parse(string $template, array $data = []): string
    {
        $variables = $this->getVariables();
        $siteName = Config::get('seo.site_name', config('app.name'));
        $siteDesc = Config::get('seo.site_description', '');
        $separator = Config::get('seo.separator', ' - ');

        // Built-in replacements that don't depend on model data
        $replacements = [
            '%sep%' => $separator,
            '%sitename%' => $siteName,
            '%sitedesc%' => $siteDesc,
            '%currentdate%' => now()->format('F j, Y'),
            '%currentday%' => now()->format('j'),
            '%currentmonth%' => now()->format('F'),
            '%currentyear%' => now()->format('Y'),
            '%currenttime%' => now()->format('g:i a'),
        ];

        // Model data replacements
        if (isset($data['title'])) {
            $replacements['%title%'] = $data['title'];
            $replacements['%ptitle%'] = $data['title']; // Parent title (same as title by default)
        }

        if (isset($data['description'])) {
            $replacements['%description%'] = $data['description'];
            $replacements['%excerpt%'] = $this->excerpt($data['description'], 160);
            $replacements['%excerpt_only%'] = $this->excerpt($data['description'], 160);
        }

        if (isset($data['id'])) {
            $replacements['%id%'] = $data['id'];
        }

        if (isset($data['modified'])) {
            $replacements['%modified%'] = $data['modified'];
        }

        if (isset($data['categories'])) {
            $replacements['%categories%'] = is_array($data['categories'])
                ? implode(', ', $data['categories'])
                : $data['categories'];
        }

        if (isset($data['tags'])) {
            $replacements['%tags%'] = is_array($data['tags'])
                ? implode(', ', $data['tags'])
                : $data['tags'];
        }

        if (isset($data['author'])) {
            $author = $data['author'];
            $replacements['%author%'] = $author;

            // Try to split name into first/last
            $nameParts = explode(' ', $author);
            $replacements['%authorfirstname%'] = $nameParts[0] ?? '';
            $replacements['%authorlastname%'] = $nameParts[count($nameParts) - 1] ?? '';
        }

        // Custom variables from config
        $customVars = Config::get('seo.custom_variables', []);
        foreach ($customVars as $variable => $callback) {
            if (is_callable($callback)) {
                $replacements[$variable] = $callback($data);
            }
        }

        // Runtime custom variables
        foreach ($this->customVariables as $variable => $callback) {
            $replacements[$variable] = $callback($data);
        }

        // Perform replacements
        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );
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
    }

    /**
     * Get all registered built-in variables.
     *
     * @return array<string, callable>
     */
    public function getVariables(): array
    {
        return [
            '%title%' => fn ($data) => $data['title'] ?? '',
            '%sep%' => fn () => Config::get('seo.separator', ' - '),
            '%sitename%' => fn () => Config::get('seo.site_name', config('app.name')),
            '%sitedesc%' => fn () => Config::get('seo.site_description', ''),
            '%currentdate%' => fn () => now()->format('F j, Y'),
            '%currentday%' => fn () => now()->format('j'),
            '%currentmonth%' => fn () => now()->format('F'),
            '%currentyear%' => fn () => now()->format('Y'),
            '%currenttime%' => fn () => now()->format('g:i a'),
            '%excerpt%' => fn ($data) => $this->excerpt($data['description'] ?? '', 160),
            '%excerpt_only%' => fn ($data) => $this->excerpt($data['description'] ?? '', 160),
            '%author%' => fn ($data) => $data['author'] ?? '',
            '%authorfirstname%' => function ($data) {
                $name = $data['author'] ?? '';
                $parts = explode(' ', $name);

                return $parts[0] ?? '';
            },
            '%authorlastname%' => function ($data) {
                $name = $data['author'] ?? '';
                $parts = explode(' ', $name);

                return $parts[count($parts) - 1] ?? '';
            },
            '%categories%' => fn ($data) => is_array($data['categories'] ?? null)
                ? implode(', ', $data['categories'])
                : ($data['categories'] ?? ''),
            '%tags%' => fn ($data) => is_array($data['tags'] ?? null)
                ? implode(', ', $data['tags'])
                : ($data['tags'] ?? ''),
            '%id%' => fn ($data) => $data['id'] ?? '',
            '%modified%' => fn ($data) => $data['modified'] ?? '',
        ];
    }

    /**
     * Create an excerpt from text.
     */
    protected function excerpt(string $text, int $length = 160): string
    {
        $text = strip_tags($text);

        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - 3).'...';
    }
}
