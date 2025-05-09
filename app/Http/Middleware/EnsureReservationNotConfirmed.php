<?php

namespace App\Http\Middleware;

use Closure;
use \App\Models\Reservation;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureReservationNotConfirmed
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
       $id = $request->route('id');
       $reservation = Reservation::findOrFail($id);
    
       if ($reservation->status === 'confirmed') 
       {
            return response()->json(['message' => 'Reservation is already confirmed'], 400);
       }
    
       return $next($request);
    }
}
