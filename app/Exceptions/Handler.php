<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
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
