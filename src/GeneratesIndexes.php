<?php

namespace Umurkaragoz\StdAdmin;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait ManagesDeletables
 * Manages deletable operations of the module.
 *
 * @package Umurkaragoz\StdAdmin
 */
trait GeneratesIndexes
{
    /* ---------------------------------------------------------------------------------------------------------------------------------- index -+- */
    public function defaultIndex()
    {
        $columns = module('functions.index.columns');

        $headers = $this->generateHeaders(array_keys($columns));

        return app('view')->make('std-admin::index')
            ->with('columns', $columns)
            ->with('headers', $headers)
            ->with('rows', $this->indexRows());
    }

    /* ----------------------------------------------------------------------------------------------------------------------------- index Rows -+- */
    /**
     * Queries and returns $rows for the index view. This can be called from custom controllers to be used in custom views.
     *
     * @return array
     */
    protected function indexRows(Builder $query = null)
    {
        $query = $query ?: module('class')::query();

        if (method_exists(module('class'), 'scopeOrder')) {
            $query = $query->order();
        }

        $rows = $query->filter(app('request')->input('filters'))->get();

        return $rows;
    }
}