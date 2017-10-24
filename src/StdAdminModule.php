<?php

namespace Umurkaragoz\StdAdmin;

use ReflectionClass;

class StdAdminModule
{

    private static $config = [];

    private static function loadConfig()
    {
        self::$config = config('std-admin.modules');

        foreach (self::$config as $key => &$options) {
            $reflection = new ReflectionClass($options['class']);

            $options['class-short'] = $reflection->getShortName();
            $options['slug'] = strtolower($options['class-short']);
            $options['name'] = $key;
        }
    }

    /**
     * Get module information
     */
    public static function get($value, $key = null, $filter = false)
    {
        // compose config values if not done within this request
        if (!self::$config) self::loadConfig();

        if($value == '*') return self::$config;

        // get the config values
        $config = collect(self::$config);

        // filter the modules if filter provided
        if ($filter) $config = $config->only($filter);

        return $config->pluck($value, $key)->toArray();
    }

}