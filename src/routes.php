<?php
// @formatter:off

foreach(module()->all('*') as $module => $config){
    Route::pattern($config['slug'], '[0-9]+');
}

Route::pattern('model','[a-z0-9\-]+');

/* ------------------------------------------------------------------------------------------------------------------------------ Admin / Login -+- */

if(config('std-admin.plugin.utility_routes', true)){
    Route::group( ['prefix' => 'admin', 'namespace' => 'App\Http\Controllers\Admin', 'as' => 'admin.', 'middleware' => 'web'],function(){
        Route::get('login',     ['as' => 'login',     'uses' => 'AdminController@login']);
        Route::post('login',    ['as' => 'login',     'uses' => 'AdminController@postLogin']);
    });
}

Route::group(['prefix' => 'admin', 'namespace' => 'App\Http\Controllers\Admin', 'as' => 'admin.'], function () {

    if(config('std-admin.plugin.utility_routes', true)){
        Route::get('logout',   ['as' => 'logout',    'uses' => 'AdminController@logout'])
            ->middleware(config('std-admin.plugin.utility_routes.middleware'));
    }

    /* ---------------------------------------------------------------------------------------------------------------------- Admin / Utilities -+- */
    if(config('std-admin.plugin.utility_routes', true)){
        Route::post('sorting/{model}',   ['as' => 'sorting',   'uses' => 'AjaxController@sorting'])
            ->middleware(config('std-admin.plugin.utility_routes.middleware'));

        Route::post('editable/{model}',  ['as' => 'editable',  'uses' => 'AjaxController@editable'])
            ->middleware(config('std-admin.plugin.utility_routes.middleware'));
    }


    /* ------------------------------------------------------------------------------------------------------------------------ Admin / Modules -+- */
    // here we create routes for our modules based on their config
    foreach(module()->all('*') as $module => $config){

        /* ------------------------------------------------------------------------------------------------------------------ craft method list <-- */
        $only = [];
        $middleware = array_get($config, 'routes.middleware');

        $routes = module()->all('routes', null, $module);

        if(array_get($routes, 'index'))
            $only[] = 'index';

        if(array_get($routes, 'show'))
            $only[] = 'show';

        if(array_get($routes, 'create'))
            $only = array_merge($only, ['create', 'store']);

        if(array_get($routes, 'update'))
            $only = array_merge($only, ['edit', 'update']);

        if(array_get($routes, 'delete'))
            $only[] = 'destroy';

        /* -------------------------------------------------------------------------------------------------------------------- generate routes <-- */
        Route::resource($module, "{$config['class-short']}Controller", ['only' => $only, 'middleware' => $middleware]);

        if(module()->all('routes.restore', null, $module)){
            Route::get("$module/trashed",    ["as" => "$module.trashed",    "uses" => "{$config['class-short']}Controller@trashed"])
                ->middleware($middleware);

            Route::get("$module/{id}",       ["as" => "$module.restore",    "uses" => "{$config['class-short']}Controller@restore"])
                ->middleware($middleware);
        }
    }
});
