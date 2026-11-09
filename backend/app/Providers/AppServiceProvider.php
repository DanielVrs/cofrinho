<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        // Log de cada consulta SQL, pra enxergar o cache funcionando. Liga com DB_LOG_QUERIES=true
        if (config('database.log_queries')) {
            DB::listen(function (QueryExecuted $query) {
                Log::debug('SQL', ['sql' => $query->sql, 'ms' => $query->time]);
            });
        }
    }
}
