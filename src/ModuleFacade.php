<?php

namespace Umurkaragoz\StdAdmin;

class ModuleFacade extends \Illuminate\Support\Facades\Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return Module::class;
    }
}