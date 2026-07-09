<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // SMTP delivery failures (e.g. 550 mailbox not found) should never
        // surface as a 500 to the user.  Log them and redirect back with a
        // friendly message so the user knows to use a valid email address.
        $exceptions->renderable(function (
            \Symfony\Component\Mailer\Exception\UnexpectedResponseException $e,
            \Illuminate\Http\Request $request
        ) {
            \Illuminate\Support\Facades\Log::warning('Mail delivery failed during request', [
                'url'   => $request->fullUrl(),
                'error' => $e->getMessage(),
            ]);

            $message = 'We could not send a verification email to that address. '
                . 'Please check that you entered a valid email address and try again.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return back()->withErrors(['email' => $message])->withInput();
        });
    })->create();
