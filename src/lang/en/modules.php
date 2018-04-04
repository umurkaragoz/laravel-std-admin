<?php

return [
    /* ------------------------------------------------------------------------------------------------------------------------------- DEFAULTS -+- */
    '_defaults'         => [
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - attributes
        'attributes' => [
            'id'           => 'ID',
        ],
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - messages
        'messages'   => [
            'success' => 'Success.',
            'error'   => 'An error occured!',
            // delete action messages
            'delete'  => [
                'success' => ':name-singular has deleted.',
                'error'   => ':messages.error',
            ],
            // trash action messages
            'trash'   => [
                'success' => ':name-singular has moved to trash.',
                'error'   => ':messages.delete.error',
            ],
            // restore action messages
            'restore' => [
                'success' => ':name-singular has recovered.',
                'error'   => ':messages.error',
            ],
        ],
    ],

    /* -------------------------------------------------------------------------------------------------------------------------------- MODULES -+- */

    /* ---------------------------------------------------------------------------------------------------------------------------------- users -+- */
    'users'             => [
        'name'          => 'Users',
        'name-singular' => 'User',
    ],

];





