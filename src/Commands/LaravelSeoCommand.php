<?php

namespace Jegex\LaravelSeo\Commands;

use Illuminate\Console\Command;

class LaravelSeoCommand extends Command
{
    public $signature = 'laravel-seo';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
