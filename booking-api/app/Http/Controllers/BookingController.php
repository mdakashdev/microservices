<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class BookingController extends Controller
{
    public function index()
    {
        $booking = Booking::latest()->get();

        return response()->json([
            'success' => true,
            'bookings' => $booking
        ]);
    }

    public function store(BookingRequest $request)
    {
        $data = $request->validated();

        $booking = Booking::create($data);

        // Step 2: Send notification to Node.js service
        $notificationSent = false;

        try {
            $response = Http::timeout(3)->post(
                'http://127.0.0.1:3001/notifications',
                [
                    'bookingId' => $booking->id,
                    'message' => "New booking created for {$booking->customer_name}",
                ]
            );

            $notificationSent = $response->successful();
        } catch (ConnectionException $e) {
            report($e);
        }


        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'booking' => $booking,
            'notification' => [
                'sent' => $notificationSent
            ]
        ], 201);
    }
}
