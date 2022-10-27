<?php

namespace Uchup07\Messages;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MessageServiceProvider extends ServiceProvider
{
    use EventMap;

    public function boot()
    {
        $this->registerEvents();
        $this->registerMigrations();
        $this->registerRoutes();
        $this->registerResources();
        $this->registerTranslations();
    }

    /**
     * Register the message events
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     */
    protected function registerEvents()
    {
        $events = $this->app->make(Dispatcher::class);

        foreach ($this->events as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }
    }

    /**
     * Register the message routes
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group([
            'prefix' => config('laravel-messages.route.prefix', 'message'),
            'namespace' => 'Uchup07\Messages\Http\Controllers',
            'middleware' => config('laravel-messages.route.middleware', ['web', 'auth']),
            'as' => config('laravel-messages.route.name')
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    protected function registerResources()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-messages');
    }

    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function registerTranslations()
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-messages');
    }

    public function register()
    {
        $this->configure();
        $this->offerPublishing();
    }

    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-messages.php', 'laravel-messages'
        );
    }

    protected function offerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/laravel-messages.php' => config_path('laravel-messages.php'),
            ], 'messages-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'messages-migrations');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-messages'),
            ], 'messages-views');

            $this->publishes([
                __DIR__.'/../resources/lang' => $this->app->langPath('vendor/laravel-messages'),
            ], 'messages-translations');
        }
    }
}