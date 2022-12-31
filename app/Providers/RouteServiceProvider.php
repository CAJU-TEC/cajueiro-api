<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // clients
            Route::middleware(['api', 'auth:sanctum'])
                ->prefix('api')
                ->name('clients.')
                ->group(base_path('routes/clients.php'));

            // images
            Route::middleware(['api', 'auth:sanctum'])
                ->prefix('api')
                ->name('images.')
                ->group(base_path('routes/images.php'));

            // impacts
            Route::middleware(['api', 'auth:sanctum'])
                ->prefix('api')
                ->name('impacts.')
                ->group(base_path('routes/impacts.php'));

            // collaborators
            Route::middleware(['api', 'auth:sanctum'])
                ->prefix('api')
                ->name('collaborators.')
                ->group(base_path('routes/collaborators.php'));

            // job plans
            Route::middleware(['api', 'auth:sanctum'])
                ->prefix('api')
                ->name('jobPlans.')
                ->group(base_path('routes/jobPlans.php'));

            // corporates
            Route::middleware(['api', 'auth:sanctum'])
                ->prefix('api')
                ->name('corporates.')
                ->group(base_path('routes/corporates.php'));

            // tickets
            Route::middleware(['api', 'auth:sanctum'])
                ->prefix('api')
                ->name('tickets.')
                ->group(base_path('routes/tickets.php'));

            // users
            Route::middleware('api')
                ->prefix('api')
                ->name('users.')
                ->group(base_path('routes/users.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });
    }
}
