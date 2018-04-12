<?php

namespace Umurkaragoz\StdAdmin;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait ManagesEdit
 * Supplies common data to both `create` and `edit` operations.
 *
 * @package Umurkaragoz\StdAdmin
 */
trait ManagesEditing
{
    /* ----------------------------------------------------------------------------------------------------------------------- supply Edit Data -+- */
    /**
     * Parses edit data and supplies it to view.
     *
     * @return mixed
     */
    private function supplyEditData()
    {
        $variables = $this->editData();

        foreach ($variables as $variable => $data) {
            app('view')->share($variable, $data);
        }
    }

    /* ------------------------------------------------------------------------------------------------------------------------------ edit Data -+- */
    /**
     * Override this method and supply common data for both 'create' and 'edit' operations.
     *
     * @return array
     */
    public function editData()
    {
        return [];
    }
}