<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        ValidationException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (CustomException $e, $request) {
            $this->logError($e, $e->getLevel(), $request, $e->getField());

            return response([
                $e->getLevel() => true,
                'message' => $e->getMessage(),
                'field' => $e->getField(),
            ]);
        });

        $this->renderable(function (ValidationException $e, $request) {
            if (!$request->expectsJson() && !$request->ajax()) {
                return null;
            }

            $errors = $e->errors();
            $first = reset($errors);

            return response()->json([
                'error' => true,
                'message' => is_array($first) ? reset($first) : $e->getMessage(),
                'errors' => $errors,
            ], 422);
        });

        // Permission/role middleware deny access with abort(403, '...').
        // Surface the real message to the AJAX form handler instead of the
        // generic "Something went wrong" envelope.
        $this->renderable(function (HttpExceptionInterface $e, $request) {
            if (!$request->expectsJson() && !$request->ajax()) {
                return null;
            }

            $this->logError($e, 'warning', $request);

            return response([
                'error' => true,
                'message' => $this->httpExceptionMessage($e),
                'status' => $e->getStatusCode(),
            ]);
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            if (!$request->expectsJson() && !$request->ajax()) {
                return null;
            }

            $this->logError($e, 'warning', $request);

            return response([
                'error' => true,
                'message' => $e->getMessage() ?: 'You are not authorized to perform this action.',
            ]);
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if (!$request->expectsJson() && !$request->ajax()) {
                return null;
            }

            return response([
                'error' => true,
                'message' => 'Your session has expired. Please log in again.',
                'status' => 401,
            ], 401);
        });

        $this->renderable(function (Throwable $e, $request) {
            if (!$request->expectsJson() && !$request->ajax()) {
                return null;
            }

            $this->logError($e, 'error', $request);

            return response([
                'error' => true,
                'message' => 'Something went wrong',
                'console' => $e->getMessage(),
            ]);
        });
    }

    /**
     * Human readable message for an HTTP exception, falling back to the
     * status text when no explicit message was provided.
     *
     * @param  \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface  $e
     * @return string
     */
    protected function httpExceptionMessage(HttpExceptionInterface $e)
    {
        $message = trim((string) $e->getMessage());

        if ($message !== '' && $e->getStatusCode() !== 404) {
            return $message;
        }

        $messages = [
            400 => 'The request could not be understood.',
            401 => 'Your session has expired. Please log in again.',
            403 => 'You are not authorized to perform this action.',
            404 => 'The requested resource was not found.',
            419 => 'Your session has expired. Please refresh the page and try again.',
            429 => 'Too many requests. Please slow down and try again.',
        ];

        return $messages[$e->getStatusCode()] ?? 'Something went wrong';
    }

    /**
     * Write the error to the dated "errors" log channel.
     *
     * @param  \Throwable  $e
     * @param  string  $level
     * @param  \Illuminate\Http\Request|null  $request
     * @param  string|null  $field
     * @return void
     */
    protected function logError(Throwable $e, $level = 'error', $request = null, $field = null)
    {
        $level = in_array($level, ['emergency', 'alert', 'critical', 'error', 'warning', 'notice', 'info', 'debug'], true)
            ? $level
            : 'error';

        try {
            Log::channel('errors')->log($level, $e->getMessage() !== '' ? $e->getMessage() : get_class($e), array_filter([
                'type' => get_class($e),
                'field' => $field,
                'url' => $request ? $request->fullUrl() : null,
                'method' => $request ? $request->method() : null,
                'ip' => $request ? $request->ip() : null,
                'user_id' => optional(auth()->user())->id,
                'file' => $e->getFile() . ':' . $e->getLine(),
            ], function ($value) {
                return !is_null($value);
            }));
        } catch (Throwable $loggingException) {
            //
        }
    }
}
