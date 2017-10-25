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

    /* ----------------------------------------------------------------------------------------------------------------------------------- boot -+- */
    final protected function bootManagesDeletablesTrait()
    {
        $this->setManagesDeletablesDefaultOptions();
    }

    /* -------------------------------------------------------------------------------------------------------------------- set default options -+- */
    private function setManagesDeletablesDefaultOptions()
    {
        $this->opts_set([
            'deletable' => [
                'model'             => ':model',
                'model-name'        => ':model-name',
                'model-name-plural' => ':model-name-plural',
                // > info messages to return
                'messages'          => [
                    'success' => 'Operation successfully completed.',
                    'error'   => 'An error occurred during the operation.',
                    // - info messages to return related to 'destroy' process.
                    'destroy' => [
                        'success' => ':deletable.messages.success',
                        'error'   => ':deletable.messages.error',
                    ],
                    // - info messages to return related to 'restore' process.
                    'restore' => [
                        'success' => ':deletable.messages.success',
                        'error'   => ':deletable.messages.error',
                    ],
                ]
                // ^ info messages to return
            ]
        ]);
    }

    /* -------------------------------------------------------------------------------------------------------------------------------- destroy -+- */
    public function destroy($id)
    {
        /** @var Model $row */
        $row = module('class')::findOrFail($id);
        $this->item = $row;

        if ($row->delete()) {
            $success = true;
            $message = $this->opts_get('deletable.messages.destroy.success');
        } else {
            $success = false;
            $message = $this->opts_get('deletable.messages.destroy.error');
        }

        return $this->jsonResponse($success, $message);
    }

    /* -------------------------------------------------------------------------------------------------------------------------------- restore -+- */
    public function restore($id)
    {
        /** @var Model $row */
        $row = module('class')::onlyTrashed()->findOrFail($id);

        $this->item = $row;

        if ($row->restore()) {
            $success = true;
            $message = $this->opts_get('deletable.messages.restore.success');
        } else {
            $success = false;
            $message = $this->opts_get('deletable.messages.restore.error');
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