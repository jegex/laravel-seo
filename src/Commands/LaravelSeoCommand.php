<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo\Commands;

use Illuminate\Console\Command;

class LaravelSeoCommand extends Command
{
    public $signature = 'seo:info';

    public $description = 'Display SEO package information and status';

    public function handle(): int
    {
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║        Jegex Laravel SEO Package        ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();

        $this->info('Features:');
        $this->line('  ✓ Meta tags generation (title, description, robots)');
        $this->line('  ✓ Open Graph tags (Facebook)');
        $this->line('  ✓ Twitter Cards');
        $this->line('  ✓ Canonical URLs');
        $this->line('  ✓ Template variables (%title%, %sep%, %sitename%, etc.)');
        $this->line('  ✓ Webmaster verification (Google, Bing, Pinterest, etc.)');
        $this->line('  ✓ Redirect management (301, 302, 410)');
        $this->line('  ✓ 404 error tracking');
        $this->newLine();

        $this->info('Quick Start:');
        $this->line('  1. Add HasSeo trait to your models');
        $this->line('  2. Use {{ seo()->render() }} in your blade layout');
        $this->line('  3. Configure templates in config/seo.php');
        $this->newLine();

        return self::SUCCESS;
    }
}
