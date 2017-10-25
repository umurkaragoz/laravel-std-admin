<?php

namespace Umurkaragoz\StdAdmin;

use ReflectionClass;
use Route;

class StdAdminModule
{

    private $app;

    // all modules configs together
    private $configAll = [];
    // module config
    private $config = [];
    // trans
    private $trans = [];

    public $action;

    public $supersection;
    public $name;

    public $editing;
    public $creating;

    public $formMethod;
    public $formAction;
    // id of current item (taken from the url)
    public $id;


    public function __construct($app = false)
    {
        if (!$app) {
            $app = app();
        }
        $this->app = $app;
    }


    /* ------------------------------------------------------------------------------------------------------------------------ PRIVATE METHODS -+- */

    /* ---------------------------------------------------------------------------------------------------------------------------- load Config -+- */
    /**
     * Load and parse module configs
     */
    private function loadConfig()
    {
        $this->configAll = config('std-admin.modules');

        foreach ($this->configAll as $key => &$options) {
            if (!$options['enabled']) {
                unset($this->configAll[$key]);
                continue;
            }

            $reflection = new ReflectionClass($options['class']);

            // add more information about each config
            $options['class-short'] = $reflection->getShortName();
            $options['slug'] = strtolower($options['class-short']);
            $options['name'] = $key;
        }
    }

    /* ------------------------------------------------------------------------------------------------------------------------- PUBLIC METHODS -+- */

    /* -------------------------------------------------------------------------------------------------------------------- parse Current Route -+- */
    /**
     * Deduce current module by the cues from current route
     * - set module variables
     * - set module config
     *
     * @param int $level
     */
    public function parseCurrentRoute($level = 1)
    {
        // check the action
        $editing = request()->segment($level + 4) == 'edit';
        $creating = request()->segment($level + 3) == 'create';

        // generate supersection path for the route
        $supersection = '';
        for ($i = 0; $i < $level + 1; $i++) {
            $supersection .= request()->segment($i + 1) . '.';
        }

        $module = request()->segment($level + 2);

        $formMethod = $editing ? 'put' : 'post';
        $formAction = $editing ? 'update' : 'store';

        // on get the id of the current model
        $id = $editing ? request()->segment($level + 3) : false;

        // generate links to be used in create and edit forms
        if ($editing) {
            $formAction = route("$supersection$module.$formAction", $id);
        } else if ($creating) {
            $formAction = route("$supersection$module.$formAction");
        }

        $this->supersection = $supersection;
        $this->name = $module;
        $this->editing = $editing;
        $this->creating = $creating;
        $this->formMethod = $formMethod;
        $this->formAction = $formAction;
        $this->id = $id;

        $this->slug = $this->config('slug');

        $this->config = array_get($this->configAll, $this->name);

        // legacy support
        // TODO: think about a cleaner way to use utilities in views
        view()->share([
            'action'   => $formAction,
            'method'   => $formMethod,
            'editing'  => $editing,
            'creating' => $creating,
        ]);
    }

    /* --------------------------------------------------------------------------------------------------------------------------------- config -+- */
    /**
     *
     * @param string $key
     * @param bool   $default
     *
     * @return mixed|null
     */
    public function config($key, $default = false)
    {
        return array_get($this->config, $key, $default);
    }

    /* ---------------------------------------------------------------------------------------------------------------------------------- trans -+- */

    /**
     * Processces trans files and provides access to translations for current module
     *
     * @param string $key
     * @param bool   $default
     *
     * @return mixed|null
     */
    public function trans($key, $default = false)
    {
        // process trans. Add and overwrite in order;
        // 1) std-admin/modules.{module}
        // 2) validation.attributes
        // 3) std-admin/modules._common.attributes

        if (!$this->trans) {
            /* -------------------------------------------------------------------------------------------------------------------------------- -1- */
            if (is_array(trans('std-admin/modules.' . module('name'))))
                $this->trans = trans('std-admin/modules.' . module('name'));

            /* -------------------------------------------------------------------------------------------------------------------------------- -2- */
            if (is_array(trans('validation.attributes')))
                $this->trans['attributes'] = array_merge(array_get($this->trans, 'attributes', []), trans('validation.attributes'));

            /* -------------------------------------------------------------------------------------------------------------------------------- -3- */
            if (is_array(trans('std-admin/modules._common.attributes')))
                $this->trans['attributes'] = array_merge($this->trans['attributes'], trans('std-admin/modules._common.attributes'));

        }

        return array_get($this->trans, $key, $default);
    }

    /* ---------------------------------------------------------------------------------------------------------------------------------- route -+- */
    /**
     * Provides a module-agnostic way to
     *
     * @param       $action
     * @param array $parameters
     *
     * @return bool|string
     */
    public function route($action, $parameters = [])
    {
        // build full route name
        $routeName = $this->supersection . $this->name . '.' . $action;

        // also convenienly handle special cases like editable and sorting.
        // make calls to generate those routes module agnostic.
        if (!$parameters) {
            if ($action == 'editable') {
                $routeName = 'admin.editable';
                $parameters[] = module('slug');

            } else if ($action == 'sorting') {
                $routeName = 'admin.sorting';
                $parameters[] = module('slug');
            }
        }

        // check if route name exists and generate the route
        if (Route::has($routeName)) {
            return route($routeName, $parameters);
        }

        return false;
    }

    /* ------------------------------------------------------------------------------------------------------------------------------------ all -+- */
    /**
     * Get module properties
     *
     * @param string      $value
     * @param null|string $key
     * @param bool|array  $filter
     *
     * @return array|mixed returns the requested value without outer array IF $key is not specified and $filter is given as string.
     */
    public function all($value, $key = null, $filter = false)
    {
        // compose config values if not done within this request
        if (!$this->configAll) $this->loadConfig();

        if ($value == '*') return $this->configAll;

        // get the config values
        $configAll = collect($this->configAll);

        // filter the modules if filter provided
        if ($filter) $configAll = $configAll->only($filter);

        $configAll = $configAll->pluck($value, $key);

        // returns single value without outer array IF $key is not specified and $filter is given as string.
        if ($key == null && is_string($filter)) {
            $configAll = $configAll->first();
        } else {
            $configAll = $configAll->toArray();
        }

        return $configAll;
    }

}