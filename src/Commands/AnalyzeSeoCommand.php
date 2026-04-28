<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Commands;

use Illuminate\Console\Command;
use Jegex\LaravelSeo\Models\SeoEntry;

class AnalyzeSeoCommand extends Command
{
    protected $signature = 'seo:analyze
                            {--model= : Analyze specific model class}
                            {--id= : Analyze specific model ID}
                            {--calculate-scores : Calculate and save SEO scores}';

    protected $description = 'Analyze SEO entries and display scores';

    public function handle(): int
    {
        $query = SeoEntry::query();

        if ($this->option('model')) {
            $query->where('model_type', $this->option('model'));
        }

        if ($this->option('id')) {
            $query->where('model_id', $this->option('id'));
        }

        $entries = $query->get();

        if ($entries->isEmpty()) {
            $this->warn('No SEO entries found.');

            return self::SUCCESS;
        }

        $this->info("Analyzing {$entries->count()} SEO entries...\n");

        $tableData = [];

        foreach ($entries as $entry) {
            if ($this->option('calculate-scores')) {
                $entry->calculateScore();
            }

            $analysis = $entry->analyze();
            $score = $analysis['score'];

            $color = $score >= 80 ? 'green' : ($score >= 50 ? 'yellow' : 'red');
            $scoreStr = "<fg={$color}>{$score}</>";

            $tableData[] = [
                $entry->id,
                class_basename($entry->model_type),
                $entry->model_id,
                $entry->title ? str_limit($entry->title, 40) : '-',
                $scoreStr,
                count($analysis['alerts']),
            ];
        }

        $this->table(
            ['ID', 'Model', 'Model ID', 'Title', 'Score', 'Alerts'],
            $tableData
        );

        // Show details for entries with alerts
        $entriesWithAlerts = $entries->filter(fn ($e) => count($e->analyze()['alerts']) > 0);

        if ($entriesWithAlerts->isNotEmpty()) {
            $this->newLine();
            $this->warn('⚠️  Entries with issues:');

            foreach ($entriesWithAlerts->take(5) as $entry) {
                $analysis = $entry->analyze();
                $this->line("\n  <fg=cyan>#{$entry->id}</> {$entry->title}");

                foreach ($analysis['alerts'] as $alert) {
                    $this->line("    <fg=red>-</> {$alert}");
                }
            }

            if ($entriesWithAlerts->count() > 5) {
                $this->line("\n  ... and ".($entriesWithAlerts->count() - 5).' more entries with issues');
            }
        }

        $this->newLine();
        $avgScore = round($entries->avg(fn ($e) => $e->analyze()['score']));
        $this->info("Average SEO Score: {$avgScore}/100");

        return self::SUCCESS;
    }
}
