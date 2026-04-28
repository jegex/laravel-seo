<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Contracts;

interface TemplateParser
{
    /**
     * Parse a template string and replace variables with actual values.
     *
     * @param string $template The template string (e.g., "%title% %sep% %sitename%")
     * @param array<string, mixed> $data Data to use for variable replacement
     * @return string The parsed string
     */
    public function parse(string $template, array $data = []): string;

    /**
     * Register a custom variable parser.
     *
     * @param string $variable The variable name (e.g., "%custom%")
     * @param callable $callback Callback that receives data and returns string
     */
    public function registerVariable(string $variable, callable $callback): void;

    /**
     * Get all registered built-in variables.
     *
     * @return array<string, callable>
     */
    public function getVariables(): array;
}
