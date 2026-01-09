<?php

namespace App\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
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
        // HTTPSを強制
        URL::forceScheme('https');

        // 文字エンコーディングをUTF-8に設定
        mb_internal_encoding('UTF-8');
        mb_http_output('UTF-8');
    }
}
