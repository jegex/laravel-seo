<?php

namespace Jegex\LaravelSeo;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Jegex\LaravelSeo\Commands\LaravelSeoCommand;

class LaravelSeoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-seo')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_seo_table')
            ->hasCommand(LaravelSeoCommand::class);
    }
}
