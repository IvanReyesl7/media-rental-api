<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Media;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
    return Booking::with(['media', 'customer'])
        ->when($request->status, fn ($q, $v) => $q->where('status', $v))
        ->when($request->media_id, fn ($q, $v) => $q->where('media_id', $v))
        ->when($request->customer_id, fn ($q, $v) => $q->where('customer_id', $v))
        ->when($request->from, fn ($q, $v) => $q->where('ends_at', '>=', $v))
        ->when($request->to, fn ($q, $v) => $q->where('starts_at', '<=', $v))
        ->latest()
        ->paginate($request->integer('per_page', 10))
        ->withQueryString();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'media_id' => 'required|exists:media,id',
            'customer_id' => 'required|exists:customers,id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
        ]);

        $media = Media::findOrFail($data['media_id']);

        if ($media->status !== 'active') {
            return response()->json(['message' => 'This medium is not available.'], 422);
        }

        $overlaps = Booking::where('media_id', $media->id)
            ->where('status', '!=', 'cancelled')
            ->where('starts_at', '<=', $data['ends_at'])
            ->where('ends_at', '>=', $data['starts_at'])
            ->exists();

        if ($overlaps) {
            return response()->json(['message' => 'This medium is already booked for those dates.'], 422);
        }

        $days = (int) Carbon::parse($data['starts_at'])->diffInDays(Carbon::parse($data['ends_at'])) + 1;

        $booking = Booking::create([
            ...$data,
            'total_price' => $days * $media->price_per_day,
            'status' => 'pending',
        ]);

        return response()->json($booking->load(['media', 'customer']), 201);
    }

    public function show(Booking $booking)
    {
        return $booking->load(['media', 'customer']);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
    $data = $request->validate([
        'status' => 'required|in:pending,confirmed,cancelled',
    ]);

    $booking->update($data);

    return $booking->load(['media', 'customer']);
    }
}