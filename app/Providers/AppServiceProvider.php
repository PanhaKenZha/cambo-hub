<?php

namespace App\Providers;

use App\Enums\PlatformModule;
use App\Models\User;
use App\Services\PlatformSettings;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PlatformSettings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * Show content only while a module is turned on:
         * @module('news') ... @endmodule
         */
        Blade::if('module', fn (string $module): bool => PlatformModule::from($module)->isEnabled());

        /*
         * Password reset emails send administrators to the admin panel's reset page
         * and everyone else to the public one.
         */
        ResetPassword::createUrlUsing(fn (User $user, string $token): string => route(
            $user->isAdmin() ? 'admin.password.reset' : 'password.reset',
            ['token' => $token, 'email' => $user->getEmailForPasswordReset()],
        ));
    }
}
