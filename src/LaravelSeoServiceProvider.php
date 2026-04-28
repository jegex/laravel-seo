<?php

declare(strict_types=1);

namespace Jegex\LaravelSeo;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Jegex\LaravelSeo\Commands\LaravelSeoCommand;
use Jegex\LaravelSeo\Commands\AnalyzeSeoCommand;
use Jegex\LaravelSeo\Services\SeoService;
use Jegex\LaravelSeo\Services\MetaTagService;
use Jegex\LaravelSeo\Services\TemplateParserService;
use Jegex\LaravelSeo\Services\SchemaService;
use Jegex\LaravelSeo\Services\SitemapService;
use Jegex\LaravelSeo\Services\RobotsService;
use Jegex\LaravelSeo\Services\AnalyzerService;
use Jegex\LaravelSeo\Services\BreadcrumbService;
use Jegex\LaravelSeo\View\Components\MetaTags;
use Jegex\LaravelSeo\View\Components\JsonLd;
use Jegex\LaravelSeo\View\Components\Breadcrumbs;

class LaravelSeoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-seo')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigrations([
                'create_seo_entries_table',
                'create_seo_redirects_table',
                'create_seo_404_logs_table',
            ])
            ->hasCommand(LaravelSeoCommand::class);
    }

    public function registeringPackage(): void
    {
        // Register services
        $this->app->singleton(TemplateParserService::class);
        $this->app->singleton(MetaTagService::class);
        $this->app->singleton(SchemaService::class);
        $this->app->singleton(SitemapService::class);
        $this->app->singleton(RobotsService::class);

        $this->app->singleton(SeoService::class, function ($app) {
            return new SeoService(
                $app->make(MetaTagService::class),
                $app->make(TemplateParserService::class),
                $app->make(SchemaService::class),
            );
        });
    }

    public function bootingPackage(): void
    {
        // Register blade components
        Blade::componentNamespace('Jegex\LaravelSeo\View\Components', 'seo');

        // Register blade directives
        Blade::directive('seo', function () {
            return '<?php echo seo()->render(); ?>';
        });

        // Register routes
        $this->registerRoutes();
    }

    protected function registerRoutes(): void
    {
        Route::middleware('web')
            ->group(function () {
                // Sitemap route
                if (config('seo.sitemap.enabled', true)) {
                    Route::get(config('seo.sitemap.path', 'sitemap.xml'), function (SitemapService $sitemap) {
                        return response($sitemap->generate(), 200)
                            ->header('Content-Type', 'application/xml');
                    })->name('seo.sitemap');
                }

                // robots.txt route
                if (config('seo.robots.enabled', true)) {
                    Route::get('robots.txt', function (RobotsService $robots) {
                        return response($robots->generate(), 200)
                            ->header('Content-Type', 'text/plain');
                    })->name('seo.robots');
                }
            });
    }
}

