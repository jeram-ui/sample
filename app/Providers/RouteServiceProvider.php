<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
        $this->mapApiBusinessRoutes();
        $this->mapApiTreasuryRoutes();
        $this->mapApiSchedulerRoutes();
        $this->mapApiTrackerRoutes();
        $this->mapApiLegalRoutes();
        $this->mapApiBACRoutes();
        $this->mapApidashboardRoutes();
        $this->mapApiInspectorateRoutes();
        $this->mapApiMayorsRoutes();
        $this->mapApiCenroRoutes();
        $this->mapApiHRRoutes();
        $this->mapApiPerformanceRoutes();
        $this->mapApiITRoutes();
        


        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    protected function mapApiBusinessRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/business.api.php'));
    }
    protected function mapApiTreasuryRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/treasury.api.php'));
    }
    protected function mapApiSchedulerRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/scheduler.api.php'));
    }
    protected function mapApiTrackerRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/tracker.api.php'));
    }
    protected function mapApiLegalRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/legal.api.php'));
    }
    protected function mapApiBACRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/BAC.api.php'));
    }
    protected function mapApidashboardRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/dashboard.api.php'));
    }
    protected function mapApiInspectorateRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/inspectorate.api.php'));
    }
    protected function mapApiMayorsRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/mayors.api.php'));
    }
    protected function mapApiCenroRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/cenro.api.php'));
    }
    protected function mapApiHRRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/hr.api.php'));
    }
    protected function mapApiPerformanceRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/performance.php'));
    }
    protected function mapApiITRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/it.api.php'));
    }
}
