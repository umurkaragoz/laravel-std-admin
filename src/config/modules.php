<?php

return [
    /* ------------------------------------------------------------------------------------------------------------------------------- defaults -+- */
    '_defaults' => [
        'class'     => \Illuminate\Database\Eloquent\Model::class,
        'enabled'   => true,
        'level'     => 0,
        'name-attr' => 'name',
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - routes
        'routes'    => [
            'middleware' => ['web', 'auth'],
            'index'      => true,
            'create'     => true,
            'update'     => true,
            'delete'     => true,
            'restore'    => true,
        ],
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - functions
        'functions' => [
            'index'   => [
                'columns' => [
                    'id'         => 'sorting',
                    'name'       => 'string',
                    'created_at' => 'string',
                ],
            ],
            'create'  => true,
            'update'  => true,
            'delete'  => true,
            'restore' => [
                'columns' => [
                    'id'         => 'string',
                    ':name-attr' => 'string',
                    'deleted_at' => 'string',
                ],
            ],
        ],
    ],
];
