<?php

namespace Jegex\LaravelSeo;

use Jegex\LaravelSeo\Commands\AnalyzeSeoCommand;
use Jegex\LaravelSeo\Commands\LaravelSeoCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

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
            ->discoversMigrations()
            ->hasCommands([
                LaravelSeoCommand::class,
                AnalyzeSeoCommand::class,
            ]);
    }
}
