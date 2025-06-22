<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Console\Commands\DDD\MakeContextCommand;
use App\Console\Commands\DDD\MakeEntityCommand;
use App\Console\Commands\DDD\MakeValueObjectCommand;
use App\Console\Commands\DDD\MakeRepositoryCommand;
use App\Console\Commands\DDD\MakeEventCommand;
use App\Console\Commands\DDD\MakeCommandCommand;
use App\Console\Commands\DDD\MakeQueryCommand;
use App\Console\Commands\DDD\MakeMiddlewareCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeContextCommand::class,
                MakeEntityCommand::class,
                MakeValueObjectCommand::class,
                MakeRepositoryCommand::class,
                MakeEventCommand::class,
                MakeCommandCommand::class,
                MakeQueryCommand::class,
                MakeMiddlewareCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Shared module provider
        $this->app->register(\Modules\Shared\Infrastructure\Providers\SharedServiceProvider::class);
    }
}
