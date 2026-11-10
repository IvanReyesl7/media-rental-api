<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index(Request $request)
    {
    return Media::query()
        ->when($request->type, fn ($q, $v) => $q->where('type', $v))
        ->when($request->status, fn ($q, $v) => $q->where('status', $v))
        ->when($request->search, fn ($q, $v) => $q->where(function ($q) use ($v) {
            $q->where('name', 'like', "%{$v}%")
              ->orWhere('location', 'like', "%{$v}%");
        }))
        ->orderBy('name')
        ->paginate($request->integer('per_page', 10))
        ->withQueryString();
    }

    public function availability(Request $request, Media $media)
    {
        $data = $request->validate([
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after_or_equal:starts_at',
        ]);

        $conflicts = $media->bookings()
            ->where('status', '!=', 'cancelled')
            ->where('starts_at', '<=', $data['ends_at'])
            ->where('ends_at', '>=', $data['starts_at'])
            ->get(['id', 'starts_at', 'ends_at', 'status']);

        return [
            'media_id' => $media->id,
            'available' => $media->status === 'active' && $conflicts->isEmpty(),
            'conflicts' => $conflicts,
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'location' => 'required|string|max:255',
            'dimensions' => 'required|string|max:50',
            'price_per_day' => 'required|numeric|min:0',
            'status' => 'in:active,inactive',
        ]);

        return response()->json(Media::create($data), 201);
    }

    public function show(Media $media)
    {
        return $media;
    }

    public function update(Request $request, Media $media)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|max:50',
            'location' => 'sometimes|string|max:255',
            'dimensions' => 'sometimes|string|max:50',
            'price_per_day' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $media->update($data);

        return $media;
    }

    public function destroy(Media $media)
    {
        if ($media->bookings()->exists()) {
            return response()->json(['message' => 'This medium has bookings and cannot be deleted.'], 409);
        }

        $media->delete();

        return response()->noContent();
    }
}