<?php

namespace SchenkeIo\LaravelGa4Marketing;

use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Client\Factory;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use SchenkeIo\LaravelGa4Marketing\Console\VerifyGA4Command;
use SchenkeIo\LaravelGa4Marketing\Http\Controllers\EventController;
use SchenkeIo\LaravelGa4Marketing\Middleware\CaptureAdParameters;
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

class Ga4MarketingServiceProvider extends PackageServiceProvider
{
    public static function getPackageRoot(): string
    {
        return dirname(__DIR__);
    }

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
                VerifyGA4Command::class,
            ]);
    }

    public function packageBooted(): void
    {
        Blade::directive('G4MarketingScript', function () {
            return "<?php echo view('ga4-marketing::components.ga4-marketing')->render(); ?>";
        });

        /** @var Router $router */
        $router = $this->app->make('router');
        $router->aliasMiddleware('capture-ad-parameters', CaptureAdParameters::class);
        $router->aliasMiddleware('track-page-view', TrackPageView::class);
        $router->aliasMiddleware('track-outbound-link', TrackOutboundLink::class);

        Route::post('ga4-marketing/event', [EventController::class, 'store'])
            ->middleware('web')
            ->name('ga4-marketing.event');

        if (class_exists(Livewire::class)) {
            Livewire::listen('ga4-event', function ($eventName, $eventParams = []) {
                // In Livewire 3, the 'ga4-event' dispatched from the component
                // is already a browser event that the JS tracker picks up.
                // Bridging to 'ga4-event-triggered' is not needed and
                // Livewire::dispatch() is not available globally in v3.
            });
        }
    }
}
