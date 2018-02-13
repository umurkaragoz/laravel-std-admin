<?php

/* ------------------------------------------------------------------------------------------------------------------------------------- module -+- */
/**
 * Get the Module instance
 *
 * @return \Umurkaragoz\StdAdmin\ModuleFacade|array
 */
function module($key = false, $default = false)
{
    $instance = app(\Umurkaragoz\StdAdmin\StdAdminModule::class);

    if($key){
        return $instance->config($key, $default);
    } else {
        return $instance;
    }
}

/* ------------------------------------------------------------------------------------------------------------------------------- module Route -+- */
/**
 * Get the Module instance
 *
 * @return string|bool
 */
function mRoute($action, $parameters = [])
{
    return module()->route($action, $parameters);
}