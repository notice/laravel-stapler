<?php namespace Codesleeve\LaravelStapler\Providers;

use Codesleeve\LaravelStapler\IlluminateConfig;
use Codesleeve\Stapler\Stapler;
use Codesleeve\LaravelStapler\Commands\FastenCommand;
use Config;

class L5ServiceProvider extends ServiceProvider 
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $packageRoot = dirname(__DIR__);

        // config
        $this->publishes([
            $packageRoot . '/config/filesystem.php' => config_path('laravel-stapler/filesystem.php'),
            $packageRoot . '/config/s3.php' => config_path('laravel-stapler/s3.php'),
            $packageRoot . '/config/stapler.php' => config_path('laravel-stapler/stapler.php')
        ]);

        $this->mergeConfigFrom($packageRoot . '/config/filesystem.php', 'filesystem');
        $this->mergeConfigFrom($packageRoot . '/config/s3.php', 's3');
        $this->mergeConfigFrom($packageRoot . '/config/stapler.php', 'stapler');

        // views
        $this->loadViewsFrom($packageRoot . '/views', 'laravel-stapler');

        $this->bootstrapStapler();
    }

    /**
     * Bootstrap up the stapler package:
     * - Boot stapler.
     * - Set the config driver.
     * - Set public_path config using laravel's public_path() method (if necessary).
     * - Set base_path config using laravel's base_path() method (if necessary).
     * 
     * @return void
     */
    protected function bootstrapStapler()
    {
        Stapler::boot();

        $config = new IlluminateConfig(Config::getFacadeRoot());
        Stapler::setConfigInstance($config);

        if (!$config->get('stapler.public_path')) {
            $config->set('stapler.public_path', realpath(public_path()));
        }

        if (!$config->get('stapler.base_path')) {
            $config->set('stapler.base_path', realpath(base_path()));
        }
    }

    /**
     * Register the stapler fasten command with the container.
     *
     * @return void
     */
    protected function registerStaplerFastenCommand()
    {
        $this->app->bind('stapler.fasten', function($app)
        {
            $migrationsFolderPath = base_path() . '/database/migrations';

            return new FastenCommand($app['view'], $app['files'], $migrationsFolderPath);
        });
    }
}
