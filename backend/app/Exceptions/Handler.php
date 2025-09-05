<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        AuthenticationException::class,
        ValidationException::class,
        TokenExpiredException::class,
        TokenInvalidException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'biometric_data',
        'token',
        'refresh_token',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log crítico para erros de segurança
            if ($this->isSecurityException($e)) {
                \Log::critical('Security Exception', [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'user_id' => auth()->id(),
                ]);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // API responses
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions and return JSON responses.
     */
    protected function handleApiException(Request $request, Throwable $e)
    {
        $status = 500;
        $message = 'Internal Server Error';
        $errors = null;

        // Authentication exceptions
        if ($e instanceof AuthenticationException) {
            $status = 401;
            $message = 'Unauthenticated';
        }
        // JWT exceptions
        elseif ($e instanceof TokenExpiredException) {
            $status = 401;
            $message = 'Token expired';
        }
        elseif ($e instanceof TokenInvalidException) {
            $status = 401;
            $message = 'Token invalid';
        }
        elseif ($e instanceof JWTException) {
            $status = 401;
            $message = 'Token error';
        }
        // Validation exceptions
        elseif ($e instanceof ValidationException) {
            $status = 422;
            $message = 'Validation failed';
            $errors = $e->errors();
        }
        // Not found exceptions
        elseif ($e instanceof NotFoundHttpException) {
            $status = 404;
            $message = 'Resource not found';
        }
        // Method not allowed exceptions
        elseif ($e instanceof MethodNotAllowedHttpException) {
            $status = 405;
            $message = 'Method not allowed';
        }
        // HTTP exceptions
        elseif ($e instanceof HttpException) {
            $status = $e->getStatusCode();
            $message = $e->getMessage() ?: Response::$statusTexts[$status];
        }
        // Development environment - show detailed errors
        elseif (config('app.debug')) {
            $message = $e->getMessage();
            $errors = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ];
        }

        $response = [
            'success' => false,
            'message' => $message,
            'status_code' => $status,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        // Add request ID for tracking
        if ($request->hasHeader('X-Request-ID')) {
            $response['request_id'] = $request->header('X-Request-ID');
        }

        return response()->json($response, $status);
    }

    /**
     * Check if the exception is security-related.
     */
    protected function isSecurityException(Throwable $e): bool
    {
        $securityExceptions = [
            'Illuminate\\Auth\\Access\\AuthorizationException',
            'Symfony\\Component\\HttpKernel\\Exception\\AccessDeniedHttpException',
            'Illuminate\\Auth\\AuthenticationException',
        ];

        foreach ($securityExceptions as $exception) {
            if ($e instanceof $exception) {
                return true;
            }
        }

        // Check for suspicious patterns in the message
        $suspiciousPatterns = [
            'sql injection',
            'xss',
            'csrf',
            'unauthorized access',
            'permission denied',
        ];

        $message = strtolower($e->getMessage());
        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }
}