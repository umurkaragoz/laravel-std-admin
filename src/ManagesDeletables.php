<?php

namespace Umurkaragoz\StdAdmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Trait ManagesDeletables
 * Manages deletable operations of the module.
 *
 * @package Umurkaragoz\StdAdmin
 */
trait ManagesDeletables
{
    /* -------------------------------------------------------------------------------------------------------------------------------- destroy -+- */
    public function destroy($id)
    {
        /** @var Model $row */
        $row = module('class')::findOrFail($id);

        if ($row->delete()) {
            $success = true;
            $message = module()->trans('messages.delete.success');
        } else {
            $success = false;
            $message = module()->trans('messages.delete.error');
        }

        return $this->jsonResponse($success, $message);
    }

    /* -------------------------------------------------------------------------------------------------------------------------------- restore -+- */
    public function restore($id)
    {
        /** @var Model $row */
        $row = module('class')::onlyTrashed()->findOrFail($id);

        if ($row->restore()) {
            $success = true;
            $message = module()->trans('messages.restore.success');
        } else {
            $success = false;
            $message = module()->trans('messages.restore.error');
        }

        return $this->jsonResponse($success, $message);
    }

    /* -------------------------------------------------------------------------------------------------------------------------------- trashed -+- */
    public function trashed(Request $request)
    {
        $columns = [
            'id',
            module('name-attr'),
            'deleted_at'
        ];

        /** @var Model $rows */
        $rows = module('class')::select($columns)->onlyTrashed()->filter($request->query('filters'))->get()->toArray();

        $headers = $this->generateHeaders($columns);

        return app('view')->make('std-admin::trashed')
            ->with('headers', $headers)
            ->with('rows', $rows);
    }
}