<?php

namespace StackNerds\MtnOpenAPI\ServiceProviders;

use Illuminate\Support\ServiceProvider;
use StackNerds\MtnOpenAPI\Contracts\OpenAPIInterface;
use StackNerds\MtnOpenAPI\Facades\OpenAPIFacadeAccessor;
use StackNerds\MtnOpenAPI\OpenAPI;

/**
 * Class MtnOpenApiServiceProvider
 *
 * @author  Fenn-CS@StackNerds <normad@stacknerds.com>
 */
class MtnOpenApiServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the package.
     */
    public function boot()
    {
        /*
        |--------------------------------------------------------------------------
        | Publish the Config file from the Package to the App directory
        |--------------------------------------------------------------------------
        */
        $this->configPublisher();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        /*
        |--------------------------------------------------------------------------
        | Implementation Bindings
        |--------------------------------------------------------------------------
        */
        $this->implementationBindings();

        /*
        |--------------------------------------------------------------------------
        | Facade Bindings
        |--------------------------------------------------------------------------
        */
        $this->facadeBindings();

        /*
        |--------------------------------------------------------------------------
        | Registering Service Providers
        |--------------------------------------------------------------------------
        */
        $this->serviceProviders();
    }

    /**
     * Implementation Bindings
     */
    private function implementationBindings()
    {
        $this->app->bind(
            OpenAPIInterface::class,
            OpenAPI::class
        );
    }

    /**
     * Publish the Config file from the Package to the App directory
     */
    private function configPublisher()
    {
        // When users execute Laravel's vendor:publish command, the config file will be copied to the specified location
        $this->publishes([
            __DIR__ . '/Config/mtnopenapi.php' => config_path('mtnopenapi.php'),
        ]);
    }

    /**
     * Facades Binding
     */
    private function facadeBindings()
    {
        // Register 'mtnopenapi.say' instance container
        $this->app['mtnopenapi.OpenAPI'] = $this->app->share(function ($app) {
            return $app->make(OpenAPI::class);
        });

        // Register 'OpenAPI' Alias, So users don't have to add the Alias to the 'app/config/app.php'
        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('OpenAPI', OpenAPIFacadeAccessor::class);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Registering Other Custom Service Providers (if you have)
     */
    private function serviceProviders()
    {
        // $this->app->register('...\...\...');
    }

}
