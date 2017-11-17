<?php
// @formatter:off

Route::pattern('id', '[0-9]+');
Route::pattern('model','[a-z0-9\-]+');

/* ------------------------------------------------------------------------------------------------------------------------------ Admin / Login -+- */

if(config('std-admin.plugin.utility_routes', true)){
    Route::group( ['prefix' => 'admin', 'namespace' => 'App\Http\Controllers\Admin', 'as' => 'admin.', 'middleware' => 'web'],function(){
        Route::get('login',     ['as' => 'login',     'uses' => 'AdminController@login']);
        Route::post('login',    ['as' => 'login',     'uses' => 'AdminController@postLogin']);
    });
}

Route::group(['prefix' => 'admin', 'namespace' => 'App\Http\Controllers\Admin', 'as' => 'admin.', 'middleware' => ['web', 'auth']], function () {

    if(config('std-admin.plugin.utility_routes', true)){
        Route::get('logout',   ['as' => 'logout',    'uses' => 'AdminController@logout']);
    }

    /* ---------------------------------------------------------------------------------------------------------------------- Admin / Utilities -+- */
    if(config('std-admin.plugin.utility_routes', true)){
        Route::post('sorting/{model}',   ['as' => 'sorting',   'uses' => 'AjaxController@sorting']);
        Route::post('editable/{model}',  ['as' => 'editable',  'uses' => 'AjaxController@editable']);
    }


    /* ------------------------------------------------------------------------------------------------------------------------ Admin / Modules -+- */
    // here we create routes for our modules based on their config
    foreach(module()->all('*') as $module => $config){

        /* ------------------------------------------------------------------------------------------------------------------ craft method list <-- */
        $only = [];

        $routes = module()->all('routes', null, $module);

        if(array_get($routes, 'index'))
            $only[] = 'index';

        if(array_get($routes, 'create'))
            $only = array_merge($only, ['create', 'store']);

        if(array_get($routes, 'update'))
            $only = array_merge($only, ['edit', 'update']);

        if(array_get($routes, 'delete'))
            $only[] = 'destroy';

        /* -------------------------------------------------------------------------------------------------------------------- generate routes <-- */
        Route::resource($module, "{$config['class-short']}Controller", ['only' => $only]);

        if(module()->all('routes.restore',null,$module)){
            Route::get("$module/trashed",    ["as" => "$module.trashed",    "uses" => "{$config['class-short']}Controller@trashed"]);
            Route::get("$module/{id}",       ["as" => "$module.restore",    "uses" => "{$config['class-short']}Controller@restore"]);
        }
    }

});
