<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DormRequest;
use App\Models\Dorm;
use Illuminate\Http\Request;

class UniversityDormController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeIfEnabled('viewAny', Dorm::class);

        $universityId = $this->requireUniversityId($request);

        $query = Dorm::query()
            ->where('university_id', $universityId)
            ->withCount(['rooms', 'students']);

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(function ($builder) use ($term) {
                $builder->where('name', 'like', '%' . $term . '%')
                    ->orWhere('code', 'like', '%' . $term . '%')
                    ->orWhere('address', 'like', '%' . $term . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'capacity', 'status', 'created_at'];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        $dorms = $query->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return view('admin.university.dorms.index', [
            'dorms' => $dorms,
            'filters' => [
                'q' => $request->input('q', ''),
                'status' => $request->input('status', ''),
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeIfEnabled('create', Dorm::class);

        $this->requireUniversityId($request);

        return view('admin.university.dorms.form', [
            'dorm' => new Dorm,
        ]);
    }

    public function store(DormRequest $request)
    {
        $this->authorizeIfEnabled('create', Dorm::class);

        $universityId = $this->requireUniversityId($request);

        Dorm::create(array_merge($request->validated(), [
            'university_id' => $universityId,
        ]));

        return redirect()->route('admin.university.dorms.index')
            ->with('success', 'Dorm created successfully.');
    }

    public function edit(Dorm $dorm)
    {
        $this->authorizeIfEnabled('update', $dorm);

        return view('admin.university.dorms.form', [
            'dorm' => $dorm,
        ]);
    }

    public function update(DormRequest $request, Dorm $dorm)
    {
        $this->authorizeIfEnabled('update', $dorm);

        $dorm->update($request->validated());

        return redirect()->route('admin.university.dorms.index')
            ->with('success', 'Dorm updated successfully.');
    }

    public function destroy(Dorm $dorm)
    {
        $this->authorizeIfEnabled('delete', $dorm);

        $dorm->delete();

        return redirect()->route('admin.university.dorms.index')
            ->with('success', 'Dorm deleted successfully.');
    }
}
