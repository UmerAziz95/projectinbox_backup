<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {   
        // If user is not logged in, redirect to login page
        if (!Auth::check()) {
            // Create a log
            Log::info('Unauthorized access attempt to ' . $request->path() . ' at ' . now());
            return redirect('/')->withErrors(['errors' => 'You must be logged in to access this page.']);
        }

        // If user is logged in but does not have the required role, redirect
        //(Auth::user()->role->id !==  (int) $role) { 
        if (!in_array((int) Auth::user()->role->id, array_map('intval', $roles))){// check multiple roles 
            // Create a log
            Log::info('Unauthorized access attempt by user ' . Auth::user()->id . ' to ' . $request->path() . ' at ' . now());
            // Log the user ID and the requested path
            return redirect('/')->withErrors(['errors' => 'Unauthorized access.']);
        }
        // If user has the required role, allow the request to proceed
        // Log the user ID and the requested path
        Log::info('User ' . Auth::user()->id . ' accessed ' . $request->path() . ' at ' . now());
        return $next($request);
    }
}
