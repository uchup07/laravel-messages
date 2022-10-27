<?php

return [

    'paginate' => 10,

    /*
    |--------------------------------------------------------------------------
    | Messages Route Group Config
    |--------------------------------------------------------------------------
    |
    |
    */

    'route' => [
        'prefix' => 'messages',
        'middleware' => ['web', 'auth'],
        'name' => null
    ],

    /*
    |--------------------------------------------------------------------------
    | Messages Tables Name
    |--------------------------------------------------------------------------
    |
    | ..
    |
    */

    'tables' => [
        'threads' => 'threads',
        'messages' => 'messages',
        'participants' => 'participants',
    ],

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | If you want to overwrite any model you should change it here as well.
    |
    */

    'models' => [
        'thread' => Uchup07\Messages\Models\Thread::class,
        'message' => Uchup07\Messages\Models\Message::class,
        'participant' => Uchup07\Messages\Models\Participant::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Inbox Notification
    |--------------------------------------------------------------------------
    |
    | Via Supported: "mail", "database", "array"
    |
    */

    'notifications' => [
        'via' => [
            'mail',
        ],
    ],
];