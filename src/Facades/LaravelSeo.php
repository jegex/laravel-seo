<?php

namespace Jegex\LaravelSeo\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jegex\LaravelSeo\LaravelSeo
 */
class LaravelSeo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Jegex\LaravelSeo\LaravelSeo::class;
    }
}
