<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DormRequest;
use App\Models\Dorm;

class UniversityDormController extends Controller
{
    public function index()
    {
        $this->authorizeIfEnabled('viewAny', Dorm::class);

        $dorms = Dorm::orderBy('name')->get();

        return view('admin.university.dorms.index', [
            'dorms' => $dorms,
        ]);
    }

    public function create()
    {
        $this->authorizeIfEnabled('create', Dorm::class);

        return view('admin.university.dorms.form', [
            'dorm' => new Dorm(),
        ]);
    }

    public function store(DormRequest $request)
    {
        $this->authorizeIfEnabled('create', Dorm::class);

        Dorm::create($request->validated());

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
