<?php

use App\Exceptions\CategoryInUseException;
use App\Exceptions\InsufficientBalanceException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // requisições vindas do frontend (SANCTUM_STATEFUL_DOMAINS) usam sessão + cookie + CSRF
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // erro de domínio vira 409 Conflict
        $exceptions->render(fn (CategoryInUseException $e) => response()->json(
            ['message' => $e->getMessage()],
            409,
        ));

        // saldo insuficiente vira 422, no mesmo formato dos erros de validação
        $exceptions->render(fn (InsufficientBalanceException $e) => response()->json(
            ['message' => $e->getMessage(), 'errors' => ['amount_cents' => [$e->getMessage()]]],
            422,
        ));
    })->create();
