<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FloorRequest;
use App\Http\Resources\FloorResource;
use App\Models\Floor;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    protected function dormId(Request $request): int
    {
        $dormId = $request->user()?->dormAdmin?->dorm_id;

        if (!$dormId) {
            abort(403, 'Dorm admin profile missing.');
        }

        return $dormId;
    }

    public function index(Request $request)
    {
        $dormId = $this->dormId($request);

        $floors = Floor::where('dorm_id', $dormId)->orderBy('number')->get();

        return FloorResource::collection($floors);
    }

    public function store(FloorRequest $request)
    {
        $dormId = $this->dormId($request);

        $floor = Floor::create([
            'dorm_id' => $dormId,
            'number' => $request->validated()['number'],
            'bathrooms' => $request->validated()['bathrooms'] ?? 4,
            'kitchens' => $request->validated()['kitchens'] ?? 2,
            'showers' => $request->validated()['showers'] ?? 2,
        ]);

        return new FloorResource($floor);
    }

    public function show(Request $request, Floor $floor)
    {
        $dormId = $this->dormId($request);

        if ($floor->dorm_id !== $dormId) {
            abort(403);
        }

        return new FloorResource($floor);
    }

    public function update(FloorRequest $request, Floor $floor)
    {
        $dormId = $this->dormId($request);

        if ($floor->dorm_id !== $dormId) {
            abort(403);
        }

        $floor->update($request->validated());

        return new FloorResource($floor);
    }

    public function destroy(Request $request, Floor $floor)
    {
        $dormId = $this->dormId($request);

        if ($floor->dorm_id !== $dormId) {
            abort(403);
        }

        if ($floor->rooms()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a floor with rooms.',
                'errors' => [],
            ], 422);
        }

        $floor->delete();

        return response()->json([
            'message' => 'Floor deleted successfully.',
            'errors' => [],
        ]);
    }
}
