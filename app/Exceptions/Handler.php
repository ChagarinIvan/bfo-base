<?php

namespace App\Exceptions;

use App\Http\Controllers\Error\Show404ErrorAction;
use App\Mail\ErrorMail;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Routing\Redirector;
use Throwable;

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

    public function render($request, Throwable $e)
    {
        if (!app()->runningInConsole() && !app()->runningUnitTests() && ((bool)env('APP_DEBUG')) === false) {
            app(Mailer::class)->send(new ErrorMail($e));
            return app(Redirector::class)->action(Show404ErrorAction::class);
        }

        return parent::render($request, $e);
    }

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
    }
}
