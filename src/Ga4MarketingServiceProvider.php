<?php

namespace SchenkeIo\LaravelGa4Marketing;

use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Client\Factory;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use SchenkeIo\LaravelGa4Marketing\Console\VerifyGa4Command;
use SchenkeIo\LaravelGa4Marketing\Http\Controllers\EventController;
use SchenkeIo\LaravelGa4Marketing\Middleware\CaptureAdParameters;
use SchenkeIo\LaravelGa4Marketing\Middleware\HandleVisitorCookie;
use SchenkeIo\LaravelGa4Marketing\Middleware\TrackOutboundLink;
use SchenkeIo\LaravelGa4Marketing\Middleware\TrackPageView;
use SchenkeIo\LaravelGa4Marketing\Services\AnalyticsService;
use SchenkeIo\LaravelGa4Marketing\Services\BotDetector;
use SchenkeIo\LaravelGa4Marketing\Services\ClientIdGenerator;
use SchenkeIo\LaravelGa4Marketing\Services\EventMapper;
use SchenkeIo\LaravelGa4Marketing\Services\EventValidator;
use SchenkeIo\LaravelGa4Marketing\Services\PayloadBuilder;
use SchenkeIo\LaravelGa4Marketing\Services\SessionManager;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

/**
 * Service provider for the GA4 Marketing package.
 *
 * This class handles package registration, configuration, routing,
 * and Blade directive setup using Spatie's Laravel Package Tools.
 */
class Ga4MarketingServiceProvider extends PackageServiceProvider
{
    public static function getPackageRoot(): string
    {
        return dirname(__DIR__);
    }

    /**
     * Actions to perform when the package is registered.
     */
    public function packageRegistered(): void
    {
        $this->app->singleton(ClientIdGenerator::class, function ($app) {
            return new ClientIdGenerator(
                $app->make('request'),
                config('ga4-marketing.ga4.client_id_salt', '')
            );
        });

        $this->app->singleton(BotDetector::class, function ($app) {
            return new BotDetector(
                config('ga4-marketing.extra_bots', [])
            );
        });

        $this->app->singleton(AnalyticsService::class, function ($app) {
            return new AnalyticsService(
                $app->make(ClientIdGenerator::class),
                $app->make(BotDetector::class),
                $app->make(EventValidator::class),
                $app->make(EventMapper::class),
                $app->make(SessionManager::class),
                $app->make(PayloadBuilder::class),
                config('ga4-marketing', []),
                $app->make(Factory::class),
                $app->make(RateLimiter::class),
                $app->make('request'),
                $app->make(ExceptionHandler::class),
                $app->make(Dispatcher::class)
            );
        });
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('ga4-marketing')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommands([
                VerifyGa4Command::class,
            ]);
    }

    /**
     * Actions to perform when the package is booted.
     */
    public function packageBooted(): void
    {
        Blade::directive('Ga4MarketingScript', function () {
            return "<?php echo view('ga4-marketing::components.ga4-marketing')->render(); ?>";
        });

        Blade::directive('Ga4MarketingConfig', function () {
            return "<?php echo view('ga4-marketing::components.config')->render(); ?>";
        });

        /** @var Router $router */
        $router = $this->app->make('router');
        $router->aliasMiddleware('capture-ad-parameters', CaptureAdParameters::class);
        $router->aliasMiddleware('track-page-view', TrackPageView::class);
        $router->aliasMiddleware('track-outbound-link', TrackOutboundLink::class);
        $router->aliasMiddleware('handle-visitor-cookie', HandleVisitorCookie::class);

        $router->pushMiddlewareToGroup('web', HandleVisitorCookie::class);

        Route::post('ga4-marketing/event', [EventController::class, 'store'])
            ->middleware('web')
            ->name('ga4-marketing.event');

    }
}
