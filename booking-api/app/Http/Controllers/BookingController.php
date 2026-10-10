<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use Illuminate\Http\Request;

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

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'booking' => $booking
        ], 201);
    }
}
