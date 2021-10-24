<?php

namespace App\Exceptions;

use App\Mail\ErrorMail;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Mail\Mailer;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
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
        $this->renderable(function (\Exception $e, $request) {
            if (!app()->runningInConsole() && !app()->runningUnitTests() && ((bool)env('APP_DEBUG')) === false) {
                app(Mailer::class)->send(new ErrorMail($e));
                return response()->view('errors.error', [], 404);
            }
            return false;
        });
    }
}
