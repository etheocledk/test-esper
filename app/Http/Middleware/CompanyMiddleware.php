<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CompanyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé. L\'authentification est requise.'
            ], 403);
        }

        $user = Auth::user();
        if (!($user instanceof \App\Models\Company)) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé. Type d\'utilisateur invalide.'
            ], 403);
        }

        return $next($request);
    }
}
