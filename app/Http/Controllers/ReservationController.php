<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Http\Resources\ReservationCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class ReservationController extends Controller
{
    public function store(StoreReservationRequest $request): ReservationResource
    {
        $data = $request->validated();

        $reservation = Reservation::create([
            'user_id' => Auth::id(),
            'service_id' => $data['service_id'],
            'reservation_datetime' => $data['reservation_datetime'],
            'status' => 'pending',
        ]);

        return new ReservationResource($reservation);
    }

    public function index(Request $request): JsonResponse
    {
        $now = Carbon::now();

        $userId = Auth::id();

        $pastReservations = Reservation::where('user_id', $userId)
            ->where('reservation_datetime', '<', $now)
            ->orderBy('reservation_datetime', 'desc')
            ->get();

        $upcomingReservations = Reservation::where('user_id', $userId)
            ->where('reservation_datetime', '>=', $now)
            ->orderBy('reservation_datetime', 'asc')
            ->get();

        return response()->json([
            'past' => ReservationResource::collection($pastReservations),
            'upcoming' => ReservationResource::collection($upcomingReservations),
        ]);
    }

    public function cancel(int $id): JsonResponse
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($reservation->reservation_datetime < Carbon::now()) {
            return response()->json(['message' => 'Cannot cancel past reservations'], 400);
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json(['message' => 'Reservation cancelled successfully']);
    }
    
}
