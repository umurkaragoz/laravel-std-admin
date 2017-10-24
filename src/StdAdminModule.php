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
     * @return array
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

        return $config->pluck($value, $key)->toArray();
    }

}