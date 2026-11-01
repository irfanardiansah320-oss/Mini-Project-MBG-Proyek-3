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
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
    $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
        return response(
            '<p>Ukuran file melebihi batas. Bukti pendukung maksimal 2 MB.</p>'
            . '<p><a href="javascript:history.back()">Kembali ke form</a></p>',
            413
        );
    });
    })->create();
