<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission): Response
    {
        if (!$request->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }

        if (!$request->user()->hasPermissionTo($permission)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to perform this action'
            ], 403);
        }

        return $next($request);
    }
}