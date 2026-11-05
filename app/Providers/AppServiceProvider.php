<?php

namespace App\Providers;

use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->preserveWindowsServerEnvironment();
    }

    /**
     * Fix `php artisan serve` on Windows.
     *
     * Laravel's ServeCommand strips almost every environment variable from the
     * child `php -S` process so it can watch the .env file. It keeps only a
     * small whitelist (ServeCommand::$passthroughVariables), and it matches the
     * whitelist with a CASE-SENSITIVE in_array().
     *
     * On Windows the OS exposes the variable as "SystemRoot" (mixed case), but
     * the whitelist only lists "SYSTEMROOT" (upper case). The case-sensitive
     * check therefore strips "SystemRoot", and without it the built-in PHP
     * server cannot initialise Winsock, so it fails with:
     *     Failed to listen on 127.0.0.1:8000 (reason: ?)
     *
     * We re-add the correctly-cased Windows variables so the dev server can
     * bind its socket while .env watching still works (no --no-reload needed).
     */
    protected function preserveWindowsServerEnvironment(): void
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            return; // Windows only.
        }

        ServeCommand::$passthroughVariables = array_merge(
            ServeCommand::$passthroughVariables,
            [
                'SystemRoot',
                'windir',
                'SystemDrive',
                'ComSpec',
                'TEMP',
                'TMP',
                'NUMBER_OF_PROCESSORS',
                'PROCESSOR_ARCHITECTURE',
                'PROCESSOR_IDENTIFIER',
                'APPDATA',
                'LOCALAPPDATA',
                'USERPROFILE',
                'HOMEDRIVE',
                'HOMEPATH',
                'OS',
                'PATHEXT',
            ],
        );
    }
}

