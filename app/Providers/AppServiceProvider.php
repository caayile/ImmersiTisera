<?php

namespace App\Providers;

use App\Mail\SmtpCaMailManager;
use App\Models\PersonalAccessToken;
use App\Support\NeonMigrationConnection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend('mail.manager', function ($manager, $app) {
            return new SmtpCaMailManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        if (config('database.default') !== 'neon' || ! $this->app->runningInConsole()) {
            return;
        }

        config([
            'database.connections.neon' => NeonMigrationConnection::forConsole(
                config('database.connections.neon'),
                $_SERVER['argv'][1] ?? null,
            ),
        ]);
    }
}
