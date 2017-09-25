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
        $this->publishes([
            __DIR__ . '/views' => base_path('resources/views/vendor/umurkaragoz/std-admin'),
        ]);
        
        $this->loadViewsFrom(__DIR__ . '/views','std-admin');
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
