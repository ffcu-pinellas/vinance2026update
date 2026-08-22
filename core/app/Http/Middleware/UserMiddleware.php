<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Constants\Status;

class UserMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return to_route('user.login');
        }

        if (auth()->user()->status != Status::USER_ACTIVE) {
            return to_route('home');
        }

        return $next($request);
    }
}