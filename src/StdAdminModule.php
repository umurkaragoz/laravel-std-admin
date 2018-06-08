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
    // default module config. Specified in std-admin.modules._defaults
    private $configDefaults = [];
    // trans
    private $trans = [];

    public $action;

    public $supersection;
    public $supersectionParameters;
    public $name;

    public $method;
    public $editing;
    public $creating;
    public $formMethod;
    public $formAction;


    public function __construct($app = false)
    {
        if (!$app) {
            $app = app();
        }
        $this->app = $app;
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

        // get route parameters
        $this->supersectionParameters = request()->route() ? request()->route()->parameters() : [];

        // parameters are for supersection parameters. So remove any section parameter which might exist.
        if (is_numeric(request()->segment($level + 3))) {
            array_pop($this->supersectionParameters);
        }

        // generate supersection path for the route
        $supersection = '';
        for ($i = 0; $i < $level + 1; $i++) {
            $supersection .= request()->segment($i + 1) . '.';
        }

        // remove parameters from the supersection for it is the route name
        foreach ($this->supersectionParameters as $parameter) {
            $supersection = str_replace("$parameter.", '', $supersection);
        }

        $module = request()->segment($level + 2);

        $formMethod = $editing ? 'put' : 'post';
        $formAction = $editing ? 'update' : 'store';

        $this->supersection = $supersection;
        $this->name = $module;

        $this->editing = $editing;
        $this->creating = $creating;
        $this->formMethod = $formMethod;
        $this->formAction = $formAction;


        if ($this->editing) {
            $this->method = 'edit';
        } else if ($this->creating) {
            $this->method = 'create';
        } else if (!request()->segment($level + 3)) {
            $this->method = 'index';
        } else if (!request()->segment($level + 4) == 'trashed') {
            $this->method = 'trashed';
        }

        // on get the id of the current model
        $id = $editing ? request()->segment($level + 3) : false;

        // generate links to be used in create and edit forms
        if ($editing) {
            $formAction = $this->route($formAction, $id);
        } else if ($creating) {
            $formAction = $this->route($formAction);
        }

        // legacy support
        // TODO: think about a cleaner way to use utilities in views
        view()->share([
            'action'   => $formAction,
            'method'   => $formMethod,
            'editing'  => $editing,
            'creating' => $creating,
        ]);

        // set config config
        $this->config = array_get($this->configAll, $this->name);
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
        if (!$this->trans) $this->loadTrans();

        return $this->resolveConfigLinks(array_get($this->trans, $key, $default), 'trans');
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

        // also conveniently handle special cases like editable and sorting.
        // make module agnostic calls to generate those routes.
        if (!$parameters) {
            if ($action == 'editable') {
                $routeName = 'admin.editable';
                $parameters[] = module('slug');

            } else if ($action == 'sorting') {
                $routeName = 'admin.sorting';
                $parameters[] = module('slug');
            }
        }

        // if a single parameter is given, put it into an array.
        if ($parameters && !is_array($parameters)) $parameters = [$parameters];

        $parameters = array_merge($this->supersectionParameters, $parameters);

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

    /* -------------------------------------------------------------------------------------------------------------------------- extend Config -+- */
    /**
     * Extend the module config with given values
     */
    public function extendConfig($config)
    {
        $this->config = $this->config ?: [];

        // we want these keys to directly overwritten. For example, when we set index columns, we do not want those to be merged with default values.
        $keysToDirectlyOverwrite = [
            'functions.index.columns',
            'functions.restore.columns',
        ];

        foreach ($keysToDirectlyOverwrite as $key) {
            if (array_has($config, $key))
                array_set($this->config, $key, array_get($config, $key));
        }

        $this->config = array_replace_recursive($this->config, $config);

        // update base general config
        array_set($this->configAll, $this->config('name'), $this->config);
    }

    /* ------------------------------------------------------------------------------------------------------------------------ PRIVATE METHODS -+- */

    /* ---------------------------------------------------------------------------------------------------------------------------- load Config -+- */
    /**
     * Load and parse module configs
     */
    private function loadConfig()
    {
        $this->configAll = config('std-admin.modules');
        $this->configDefaults = array_pull($this->configAll, '_defaults');

        foreach ($this->configAll as $key => &$options) {
            if (!array_get($options, 'class')) {
                throw new \InvalidArgumentException("Required config parameter 'class' is not specified for module '$key'");
            }

            $reflection = new ReflectionClass($options['class']);

            // add more information about each config
            $options['class-short'] = $reflection->getShortName();
            $options['slug'] = strtolower($options['class-short']);
            $options['name'] = $key;
        }

        $this->fillConfigDefaults();
        $this->removeDisabledModuleConfig();
    }

    /* ------------------------------------------------------------------------------------------------------------------- fill Config Defaults -+- */
    /**
     * This fills unspecified config values with defaults by extending the defauls using each module specification
     */
    private function fillConfigDefaults()
    {
        // we want these keys to directly overwritten. For example, when we set module middleware, we do not want those to be merged with default values.
        $keysToPreserve = [
            'routes.middleware',
        ];

        foreach ($this->configAll as $key => &$options) {

            # $key here is the module name. e.g. 'user'

            $preservedItems = [];

            // array_replace_recursive() ahead will 'merge' the module options with defaults.
            // However we want some keys to be preserved, if they are spceficied.
            // Therefore we cache preserved values here and later replace them back.
            foreach ($keysToPreserve as $optionKey) {
                if (array_has($options, $optionKey)) {
                    $preservedItems[$optionKey] = array_get($options, $optionKey);
                }
            }

            // extend defaults with this module, save the result to this module's config.
            $options = array_replace_recursive($this->configDefaults, $options);

            # now all the data is replaced and merged in a way we do not want for some keys.
            // revert preserved data into initial status
            foreach ($preservedItems as $itemKey => $preservedItem) {
                array_set($options, $itemKey, $preservedItem);
            }
        }
    }

    /* ----------------------------------------------------------------------------------------------------------------------------- load Trans -+- */
    /**
     * Load and parse module trans
     */
    private function loadTrans()
    {
        // process trans. Add and overwrite in order;

        // 1) std-admin/modules.{module}
        if (is_array(trans('std-admin::modules.' . module('name'))))
            $this->trans = trans('std-admin::modules.' . module('name'));

        // 2) validation.attributes
        if (is_array(trans('validation.attributes')))
            $this->trans['attributes'] = array_merge(array_get($this->trans, 'attributes', []), trans('validation.attributes'));

        // 3) std-admin/modules._default.attributes
        if (is_array(trans('std-admin::modules._default.attributes')))
            $this->trans['attributes'] = array_merge($this->trans['attributes'], trans('std-admin::modules._defaults.attributes'));

        $this->fillTransDefaults();
    }

    /* ------------------------------------------------------------------------------------------------------------------- fill Trans Defaults -+- */
    /**
     * This fills unspecified trans values with defaults by extending the defauls using each module specification
     */
    private function fillTransDefaults()
    {
        // extend defaults with this module, save the result to this module's trans.
        $this->trans = array_replace_recursive($this->trans, trans("std-admin::modules._defaults"));
    }

    /* ------------------------------------------------------------------------------------------------------------------- resolve Config Links -+- */
    private function resolveConfigLinks($value, $type = 'config')
    {
        // replace variables/inner links.
        $newValue = preg_replace_callback('|:([A-z:._-]*)|', function($matches) use ($type) {
            $raw = $matches[0];
            $key = $matches[1];

            $source = $type == 'config' ? $this->config : $this->trans;

            // retrieve the value from opts.
            $value = array_get($source, $key, $raw);

            return $value;
        }, $value);

        // re-process the value if it has changed, return it if it has not.
        return $value == $newValue ? $value : $this->resolveConfigLinks($newValue);
    }

    /* ---------------------------------------------------------------------------------------------------------- remove Disabled Module Config -+- */
    /**
     * This removes disabled modules from 'configAll'
     */
    private function removeDisabledModuleConfig()
    {
        foreach ($this->configAll as $key => &$options) {

            if (!array_get($options, 'enabled')) {
                unset($this->configAll[$key]);
                continue;
            }
        }
    }

}