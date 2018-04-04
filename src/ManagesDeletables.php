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
        $row = $this->getDeletableModelBuilder()->findOrFail($id);

        $action = module('functions.restore') ? 'trash' : 'delete';

        if ($row->delete()) {
            $success = true;
            $message = module()->trans("messages.$action.success");
        } else {
            $success = false;
            $message = module()->trans("messages.$action.error");
        }

        return $this->jsonResponse($success, $message);
    }

    /* ------------------------------------------------------------------------------------------------------------ get Deletable Model Builder -+- */
    /**
     * Get deletable model instance to be deleted or restored.
     * This is exists to allow the user to modify the builder without being have to override destroy or restore method.
     *
     * @return Model
     */
    protected function getDeletableModelBuilder()
    {
        /** @var Model $row */
        $row = module('class')::query();

        return $row;
    }

    /* -------------------------------------------------------------------------------------------------------------------------------- restore -+- */
    public function restore($id)
    {
        /** @var Model $row */
        $row = $this->getDeletableModelBuilder()->onlyTrashed()->findOrFail($id);

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
        $columns = module('functions.restore.columns');

        /** @var Model $rows */
        $rows = module('class')::select(array_keys($columns))->onlyTrashed()->filter($request->query('filters'))->get()->toArray();

        $headers = $this->generateHeaders($columns);

        return app('view')->make('std-admin::trashed')
            ->with('headers', $headers)
            ->with('rows', $rows);
    }
}