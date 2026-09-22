<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create()
    // Timeweb shared hosting on this account fixes the web root to
    // "public_html" with no way to point it at "public" instead, so the
    // deployed public/ directory is renamed to public_html/ on the server.
    // Detect whichever one actually exists so storage:link, asset helpers,
    // etc. resolve correctly both there and in every other environment
    // (local dev, CI) where the folder is still named "public".
    ->usePublicPath(is_dir(__DIR__.'/../public_html') ? __DIR__.'/../public_html' : __DIR__.'/../public');
