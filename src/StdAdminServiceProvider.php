<?php

namespace Umurkaragoz\StdAdmin;

use Illuminate\Database\Eloquent\Relations\Relation;
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

        $this->loadViewsFrom(__DIR__ . '/views', 'std-admin');

        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        $this->setupModuleMorphMap();

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(StdAdminModule::class, function () {
            $module = new StdAdminModule();

            return $module;
        }
        );
    }

    private function setupModuleMorphMap()
    {
        // configure polymorphic "relation type" naming
        // by default, Eloquent uses fully qualified class name in polymorphic relationship adaptor fields (e.g. 'relation_type')
        // this will instruct Elquent to use model slug instead
        // see: https://laravel.com/docs/5.5/eloquent-relationships#polymorphic-relations section: "Custom Polymorphic Types"
        Relation::morphMap(module()->get('class', 'slug'));
    }
}
 