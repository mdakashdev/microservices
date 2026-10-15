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

        $idempotencyKey = $request->header('Idempotency-Key');

        if (!$idempotencyKey || strlen($idempotencyKey) > 255) {
            return response()->json([
                'success' => false,
                'message' => 'A valid Idempotency-Key header is required',
            ], 400);
        }

        // 1. Check whether this booking request already exists
        $existingBooking = Booking::where(
            'idempotency_key',
            $idempotencyKey
        )->first();

        if ($existingBooking) {
            // Same key cannot be reused for different booking data
            if (
                $existingBooking->customer_name !== $data['customer_name'] ||
                $existingBooking->service !== $data['service']
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'This Idempotency-Key was already used for a different booking',
                ], 409);
            }

            return response()->json([
                'success' => true,
                'message' => 'Booking already exists',
                'booking' => $existingBooking,
                'duplicate' => true,
            ], 200);
        }

        // 2. Create the booking in MySQL
        $booking = Booking::create([
            'customer_name' => $data['customer_name'],
            'service' => $data['service'],
            'status' => 'created',
            'idempotency_key' => $idempotencyKey,
        ]);

        // 3. Send notification to Node.js
        $notificationSent = false;

        try {
            $response = Http::retry(
                3,
                500,
                 function (\Exception $exception, $request) {
                    logger()->warning('Notification request retrying', [
                        'error' => $exception->getMessage(),
                    ]);

                    return true;
                 })
                ->timeout(3)
                ->post(
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

        // 4. Return the result
        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'booking' => $booking,
            'notification' => [
                'sent' => $notificationSent,
            ],
            'duplicate' => false,
        ], 201);
    }

}
