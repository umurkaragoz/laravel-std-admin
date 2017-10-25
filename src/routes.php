<?php
// @formatter:off


Route::pattern('id', '[0-9]+');

/* ------------------------------------------------------------------------------------------------------------------------------ Admin / Login -+- */

Route::group( ['prefix' => 'admin', 'namespace' => 'App\Http\Controllers\Admin', 'as' => 'admin.', 'middleware' => 'web'],function(){
    Route::get('/',         ['as' => 'login',     'uses' => 'AdminController@login']);
    Route::get('login',     ['as' => 'login',     'uses' => 'AdminController@login']);
    Route::post('login',    ['as' => 'login',     'uses' => 'AdminController@postLogin']);
});

Route::group(['prefix' => 'admin', 'namespace' => 'App\Http\Controllers\Admin', 'as' => 'admin.', 'middleware' => ['web', 'auth']], function () {

    Route::get('logout',   ['as' => 'logout',    'uses' => 'AdminController@logout']);

    /* ---------------------------------------------------------------------------------------------------------------------- Admin / Utilities -+- */
    Route::post('sorting/{model}',   ['as' => 'sorting',   'uses' => 'AjaxController@sorting']);
    Route::post('editable/{model}',  ['as' => 'editable',  'uses' => 'AjaxController@editable']);


    /* ------------------------------------------------------------------------------------------------------------------------ Admin / Modules -+- */
    // here we create routes for our modules based on their config
    foreach(module()->get('*') as $module => $config){


        /* ------------------------------------------------------------------------------------------------------------------ craft method list <-- */
        $only = [];

        $functions = module()->get('functions', null, $module);

        if(array_get($functions, 'index'))
            $only[] = 'index';

        if(array_get($functions, 'create'))
            $only = array_merge($only, ['create', 'store']);

        if(array_get($functions, 'update'))
            $only = array_merge($only, ['edit', 'update']);

        if(array_get($functions, 'delete'))
            $only[] = 'destroy';

        /* -------------------------------------------------------------------------------------------------------------------- generate routes <-- */
        Route::resource($module, "{$config['class-short']}Controller", ['only' => $only]);

        if(module()->get('functions.restore',null,$module)){
            Route::get("$module/trashed",    ["as" => "$module.trashed",    "uses" => "{$config['class-short']}Controller@trashed"]);
            Route::get("$module/{id}",       ["as" => "$module.restore",    "uses" => "{$config['class-short']}Controller@restore"]);
        }
    }

});
