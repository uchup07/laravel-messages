<?php

namespace Uchup07\Messages;

use Illuminate\Support\Facades\Facade;

class LaravelMessageFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-messages';
    }
}