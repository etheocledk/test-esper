<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => "Non autorisé. L'accès administrateur est requis"
            ], 403);
        }

        $user = Auth::user(); 
        if (!($user instanceof \App\Models\Admin)) {
            return response()->json([
                'success' => false,
                'message' => "Non autorisé. L'accès administrateur est requis"
            ], 403);
        }

        return $next($request);
    }
}
