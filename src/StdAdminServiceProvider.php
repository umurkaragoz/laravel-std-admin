<?php

namespace Umurkaragoz\StdAdmin;

use Illuminate\Support\ServiceProvider;

class StdAdminServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/views', 'timezones');
        
        $this->publishes([
            __DIR__ . '/views' => base_path('resources/views/umurkaragoz/std-admin'),
        ]);
    }
    
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
