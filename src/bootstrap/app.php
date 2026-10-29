<?php

use App\Exceptions\Api\ApiException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(App\Http\Middleware\CorsMiddleware::class);

        // Exclude public API from CSRF protection
        // but do not exclude private API endpoints
        $middleware->validateCsrfTokens(except: [
            'api/v*/action/*',
            'api/v*/data/*',
        ]);

        $middleware->alias([
            'api.auth' => App\Http\Middleware\ApiMiddleware::class,
            'admin' => App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if (env('APP_DEBUG') == true) {
                return null;
            }

            // Render nice error pages if debug is off
            if ($e instanceof NotFoundHttpException) {
                // Handle 404 exceptions
                if (env('SETTING_REDIRECT_404')) {
                    // Redirect 404s to SETTING_INDEX_REDIRECT
                    return redirect()->to(env('SETTING_INDEX_REDIRECT'));
                }
                // Otherwise, show a nice error page
                return response(view('errors.404'), 404);
            }
            if ($e instanceof HttpException) {
                // Handle HTTP exceptions thrown by public-facing controllers
                $status_code = $e->getStatusCode();
                $status_message = $e->getMessage();

                if ($status_code == 500) {
                    // Render a nice error page for 500s
                    return response(view('errors.500'), 500);
                }

                // If not 500, render generic page
                return response(
                    view('errors.generic', [
                        'status_code' => $status_code,
                        'status_message' => $status_message
                    ]), $status_code);
            }
            if ($e instanceof ApiException) {
                // Handle HTTP exceptions thrown by API controllers
                $status_code = $e->getCode();
                $encoded_status_message = $e->getEncodedErrorMessage();
                if ($e->response_type == 'json') {
                    return response($encoded_status_message, $status_code)
                        ->header('Content-Type', 'application/json')
                        ->header('Access-Control-Allow-Origin', '*');
                }

                return response($encoded_status_message, $status_code)
                    ->header('Content-Type', 'text/plain')
                    ->header('Access-Control-Allow-Origin', '*');
            }

            return null;
        });
    })->create();
