<?php

namespace LaravelSklearnBot;

use Illuminate\Support\ServiceProvider;

use LaravelSklearnBot\Models\Helpbot as BaseHelpbot;
use LaravelSklearnBot\Models\Search as BaseSearch;

class SklearnbotServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('sklearnbot', function ($app) {
            return new \LaravelSklearnBot\SklearnbotService();
        });

        $this->app->bind('Helpbot', function () {
            if (class_exists(\App\Models\Helpbot::class)) {
                if (method_exists(\App\Models\Helpbot::class, 'getOwnerModel')) {
                    return new \App\Models\Helpbot();
                }
            }
            return new BaseHelpbot();
        });

        $this->app->bind('Search', function () {
            if (class_exists(\App\Models\Search::class)) {
                if (method_exists(\App\Models\Search::class, 'getOwnerModel')) {
                    return new \App\Models\Search();
                }
            }
            return new BaseSearch();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        
        // Merge package config with application's published config
        $this->mergeConfigFrom(__DIR__.'/../config/sklearnbot.php', 'sklearnbot');

        // Publish the configuration file
        $this->publishes([
            __DIR__.'/../config/sklearnbot.php' => config_path('sklearnbot.php'),
        ], 'config');

        // Publish the migration files
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'migrations');

        // Publish the language files
        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/sklearnbot'),
        ], 'lang');

        $this->registerMiddlewares();
        $this->registerCommands();
    }

    protected function registerCommands()
    {
        $this->commands([
            \LaravelSklearnBot\Console\InstallHelpBot::class,
            \LaravelSklearnBot\Console\CreateHelpBotModel::class,
        ]);
    }

     /**
     * Register the middlewares automatically.
     *
     * @return void
     */
    protected function registerMiddlewares()
    {
        /*if (! $this->app['config']->get('sklearnbot.middleware.register')) {
            return;
        }

        $router = $this->app['router'];

        if (method_exists($router, 'middleware')) {
            $registerMethod = 'middleware';
        } elseif (method_exists($router, 'aliasMiddleware')) {
            $registerMethod = 'aliasMiddleware';
        } else {
            return;
        }

        $middlewares = [
        ];

        foreach ($middlewares as $key => $class) {
            $router->$registerMethod($key, $class);
        }*/
    }


    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        // If the provider is deferred, this method can return the services it provides.
        return [];
    }
}