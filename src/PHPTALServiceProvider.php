<?php

namespace MarcosHoo\LaravelPHPTAL;

use Illuminate\Support\ServiceProvider;
use MarcosHoo\LaravelPHPTAL\Engines\PHPTALEngine;

class PHPTALServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        // This is only for the template system.
        $templateIdString = '';
        $templateIdsArray = $this->app['config']->get('template.templateIds');
        if (is_array($templateIdsArray) && !empty($templateIdsArray) && !empty($_SERVER['HTTP_HOST'])) {
            $templateIdString = array_search($_SERVER['HTTP_HOST'], $templateIdsArray);
            $templateIdString .= '/';
        }
        define('TEMPLATE_ID', $templateIdString);

        // Config
        $configPath = __DIR__ . '/../config/config.php';
        $this->mergeConfigFrom($configPath, 'phptal');
        $this->publishes([
            $configPath => config_path('phptal.php')
        ]);

        // Set extensions
        $extensions = trim($app['config']['phptal.extensions']);
        $templateRepositories = trim($app['config']['phptal.templateRepositories']);

        // Set template paths
        $paths = $app['config']['view.paths'];
        $repositories = [];
        if ($templateRepositories) {
            foreach (is_array($templateRepositories) ? $templateRepositories : explode(',', $templateRepositories) as $repo) {
                $repositories[] = $repo;
                if (!in_array($repo, $paths)) {
                    $app['config']['view.paths'] = array_merge($app['config']['view.paths'], array(
                        $repo
                    ));
                }
            }
        }
        $app['config']['phptal.templateRepositories'] = $repositories;

        $factory = $app->make('view');

        foreach (is_array($extensions) ? $extensions : explode(',', $extensions) as $extension) {
            $factory->addExtension(trim($extension), 'tal');
        }

        $this->app->extend('view.engine.resolver', function () use($factory) {
            $resolver = $factory->getEngineResolver();
            $this->registerPHPTALEngine($resolver);
            return $resolver;
        });
    }

    /**
     * Register the PHPTAL engine implementation.
     *
     * @param \Illuminate\View\Engines\EngineResolver $resolver
     * @return void
     */
    public function registerPHPTALEngine($resolver)
    {
        $resolver->register('tal', function () {
            return new PHPTALEngine($this->app);
        });
    }
}
