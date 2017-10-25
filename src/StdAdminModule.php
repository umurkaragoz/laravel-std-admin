<?php

namespace Umurkaragoz\StdAdmin;

use ReflectionClass;

class StdAdminModule
{

    private $config = [];

    private function loadConfig()
    {
        $this->config = config('std-admin.modules');

        foreach ($this->config as $key => &$options) {
            if (!$options['enabled']) {
                unset($this->config[$key]);
                continue;
            }

            $reflection = new ReflectionClass($options['class']);

            $options['class-short'] = $reflection->getShortName();
            $options['slug'] = strtolower($options['class-short']);
            $options['name'] = $key;
        }
    }

    /**
     * Get module properties
     *
     * @param string      $value
     * @param null|string $key
     * @param bool|array  $filter
     *
     * @return array|mixed returns the requested value without outer array IF $key is not specified and $filter is given as string.
     */
    public function get($value, $key = null, $filter = false)
    {
        // compose config values if not done within this request
        if (!$this->config) $this->loadConfig();

        if ($value == '*') return $this->config;

        // get the config values
        $config = collect($this->config);

        // filter the modules if filter provided
        if ($filter) $config = $config->only($filter);

        $config = $config->pluck($value, $key);

        // returns single value without outer array IF $key is not specified and $filter is given as string.
        if ($key == null && is_string($filter)) {
            $config = $config->first();
        } else {
            $config = $config->toArray();
        }

        return $config;
    }

}