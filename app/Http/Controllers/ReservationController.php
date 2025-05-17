<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Resources\ReservationCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use App\Models\Service;

class ReservationController extends Controller
{
    public function store(StoreReservationRequest $request): ReservationResource|JsonResponse
    {
        $data = $request->validated();

        $service = Service::find($data['service_id']);

        if (!$service || !$service->available) {
            return response()->json([
                'message' => 'The selected service is not available.'
            ], 400);
        }

        $exists = Reservation::where('service_id', $data['service_id'])
            ->where('reservation_datetime', $data['reservation_datetime'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'This service is already reserved at the selected time.'
            ], 400);
        }

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

        $pastReservations = Reservation::with('service')
            ->where('user_id', $userId)
            ->where('reservation_datetime', '<', $now)
            ->orderBy('reservation_datetime', 'desc')
            ->get();

        $upcomingReservations = Reservation::with('service')
            ->where('user_id', $userId)
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

        if ($reservation->reservation_datetime < Carbon::now()) {
            return response()->json(['message' => 'Cannot cancel past reservations'], 400);
        }

        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json(['message' => 'Reservation cancelled successfully']);
    }

    public function confirm(int $id): JsonResponse
    {
        $reservation = Reservation::findOrFail($id);

    
        if ($reservation->status !== 'pending') 
        {
            return response()->json(['message' => 'Reservation cannot be confirmed'], 400);
        }

        $reservation->status = 'confirmed';
        $reservation->save();

        return response()->json(['message' => 'Reservation confirmed successfully']);
    }

    public function updateReservationTime(UpdateReservationRequest $request, int $id): ReservationResource | JsonResponse
    {
        $reservation = Reservation::findOrFail($id);

        $data = $request->validated();

        if ($reservation->status !== 'pending') {
            return response()->json(['message' => 'Reservation cannot be updated'], 400);
        }

        $conflict = Reservation::where('service_id', $reservation->service_id)
            ->where('reservation_datetime', $data['reservation_datetime'])
            ->where('id', '!=', $reservation->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($conflict) {
            return response()->json(['message' => 'This time slot is already reserved'], 400);
        }

        $reservation->reservation_datetime = $data['reservation_datetime'];
        $reservation->save();

        return new ReservationResource($reservation);
    }

}
