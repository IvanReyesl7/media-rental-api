<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;

use App\Http\Requests\MediaStoreRequest;
use App\Http\Requests\MediaUpdateRequest;
use App\Http\Resources\MediaResource;

class MediaController extends Controller
{

    public function index(Request $request)
    {
        $media = Media::query()
            ->when($request->type, fn ($q, $v) => $q->where('type', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->search, fn ($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('name', 'like', "%{$v}%")->orWhere('location', 'like', "%{$v}%");
            }))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();

        return MediaResource::collection($media);
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

    public function store(MediaStoreRequest $request)
    {
        return (new MediaResource(Media::create($request->validated())))
            ->response()->setStatusCode(201);
    }

    public function show(Media $media)
    {
        return new MediaResource($media);
    }

    public function update(MediaUpdateRequest $request, Media $media)
    {
        $media->update($request->validated());
        return new MediaResource($media);
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