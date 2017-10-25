<?php

namespace Umurkaragoz\StdAdmin;

/**
 * Trait ManagesDeletables
 * Manages deletable operations of the module.
 *
 * @package Umurkaragoz\StdAdmin
 */
trait GeneratesIndexes
{
    /* ---------------------------------------------------------------------------------------------------------------------------------- index -+- */
    public function index()
    {
        $query = module('class')::query();

        if (method_exists(module('class'), 'scopeOrder')) {
            $query = $query->order();
        }

        $rows = $query->filter(app('request')->input('filters'))->get();

        $columns = module('functions.index.columns');

        $headers = $this->generateHeaders(array_keys($columns));

        return app('view')->make('std-admin::index')
            ->with('columns', $columns)
            ->with('headers', $headers)
            ->with('rows', $rows);
    }
}