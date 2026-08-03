<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__.'/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectTo(function (Request $request) {
            if ($request->is('api/*')) {
                return null;
            }

            return '/login';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (ValidationException $e, Request $request) {
            Log::warning('Falha de validação na API', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'dados_enviados' => $request->except(['password', 'password_confirmation']),
                'erros' => $e->errors(),
            ]);

            return response()->json([
                'status' => 'fail',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            Log::channel('auth')->warning('Acesso não autorizado', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'token_fornecido' => $request->bearerToken() ? 'Sim' : 'Não',
                'erros' => $e->errors(),

            ]);

            return response()->json([
                'status' => 'fail',
                'message' => 'Acesso negado. Usuário não autenticado ou token inválido/expirado.',
            ], 401);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            Log::warning('Recurso não encontrado!', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'token_fornecido' => $request->bearerToken() ? 'Sim' : 'Não',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'fail',
                'message' => 'O recurso solicitado não foi encontrado em nosso sistema.',
            ], 404);
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            Log::warning('Acesso não autorizado', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'token_fornecido' => $request->bearerToken() ? 'Sim' : 'Não',

            ]);

            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
            ], 403);
        });
    })->create();
