<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\FloorRequest;
use App\Models\Floor;
use Illuminate\Http\Request;

class DormFloorController extends Controller
{
    protected function dormId(Request $request): int
    {
        $dormId = $request->user()?->dormAdmin?->dorm_id;

        if (!$dormId) {
            abort(403);
        }

        return $dormId;
    }

    public function index(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Floor::class);

        $dormId = $this->dormId($request);

        $floors = Floor::where('dorm_id', $dormId)->orderBy('number')->get();

        return view('admin.dorm.floors.index', [
            'floors' => $floors,
        ]);
    }

    public function create()
    {
        $this->authorizeIfEnabled('create', Floor::class);

        return view('admin.dorm.floors.form', [
            'floor' => new Floor(),
        ]);
    }

    public function store(FloorRequest $request)
    {
        $dormId = $this->dormId($request);

        $this->authorizeIfEnabled('create', [Floor::class, $dormId]);

        Floor::create([
            'dorm_id' => $dormId,
            'number' => $request->validated()['number'],
            'bathrooms' => $request->validated()['bathrooms'] ?? 4,
            'kitchens' => $request->validated()['kitchens'] ?? 2,
            'showers' => $request->validated()['showers'] ?? 2,
        ]);

        return redirect()->route('admin.dorm.floors.index')
            ->with('success', 'Floor created successfully.');
    }

    public function edit(Request $request, Floor $floor)
    {
        $dormId = $this->dormId($request);

        if ($floor->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('view', $floor);

        return view('admin.dorm.floors.form', [
            'floor' => $floor,
        ]);
    }

    public function update(FloorRequest $request, Floor $floor)
    {
        $dormId = $this->dormId($request);

        if ($floor->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('update', $floor);

        $floor->update($request->validated());

        return redirect()->route('admin.dorm.floors.index')
            ->with('success', 'Floor updated successfully.');
    }

    public function destroy(Request $request, Floor $floor)
    {
        $dormId = $this->dormId($request);

        if ($floor->dorm_id !== $dormId) {
            abort(403);
        }

        $this->authorizeIfEnabled('delete', $floor);

        if ($floor->rooms()->exists()) {
            return redirect()->route('admin.dorm.floors.index')
                ->with('error', 'Cannot delete a floor with rooms.');
        }

        $floor->delete();

        return redirect()->route('admin.dorm.floors.index')
            ->with('success', 'Floor deleted successfully.');
    }
}
