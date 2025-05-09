<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ReservationCollection;
use App\Http\Resources\AdminReservationResource;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;


class AdminReservationController extends Controller
{
    public function list(Request $request): ReservationCollection
    {
        $query = Reservation::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->has(['from', 'to'])) {
            $query->whereBetween('reservation_datetime', [
                Carbon::parse($request->from),
                Carbon::parse($request->to),
            ]);
        }

        $reservations = $query->orderBy('reservation_datetime', 'desc')->get();

        return new ReservationCollection($reservations);
    }

    public function show(int $id): AdminReservationResource
    {
        $reservation = Reservation::with(['user', 'service'])->findOrFail($id);
        return new AdminReservationResource($reservation);

    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $reservation = Reservation::findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        $reservation->status = $validated['status'];
        $reservation->save();

        return response()->json(['message' => 'Status updated successfully']);
    }


    public function cancel(int $id): JsonResponse
    {
        $reservation = Reservation::findOrFail($id);
        if($reservation->status == 'cancelled') {
            return response()->json(['message' => 'Reservation already cancelled'], 400);
        }
        $reservation->status = 'cancelled';
        $reservation->save();

        return response()->json(['message' => 'Reservation cancelled successfully']);
    }

    public function export(Request $request)
    {
        $reservations = Reservation::with(['user', 'service'])->get();

        $csv = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="reservations.csv"');

        fputcsv($csv, ['ID', 'User', 'Service', 'DateTime', 'Status']);

        foreach ($reservations as $r) {
            fputcsv($csv, [
                $r->id,
                $r->user->name,
                $r->service->name,
                $r->reservation_datetime,
                $r->status,
            ]);
        }

        fclose($csv);
        exit;
    }

}
