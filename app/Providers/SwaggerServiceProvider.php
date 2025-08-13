<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;

class SwaggerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Set dynamic Swagger base URL based on current request
        if (app()->environment('local')) {
            $request = request();
            if ($request) {
                $baseUrl = $request->getSchemeAndHttpHost();
                config(['l5-swagger.defaults.paths.base' => $baseUrl]);
            }
        }
    }
}
