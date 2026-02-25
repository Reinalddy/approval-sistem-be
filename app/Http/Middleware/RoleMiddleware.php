<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // relasi user()->role()->name
        if (!$request->user() || $request->user()->role->name !== $role) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access.'
            ], 403);
        }

        return $next($request);
    }
}
