<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

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
}
