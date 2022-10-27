<?php

namespace Uchup07\Messages\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        view()->composer('laravel-messages::*', function ($view) {
            $view->with([
                'recipients' => config('auth.providers.users.model')::where('id', '!=', auth()->id())->get(),
            ]);
        });
    }
}