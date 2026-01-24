<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\DormRequest;
use App\Models\Dorm;

class UniversityDormController extends Controller
{
    public function index()
    {
        $dorms = Dorm::orderBy('name')->get();

        return view('admin.university.dorms.index', [
            'dorms' => $dorms,
        ]);
    }

    public function create()
    {
        return view('admin.university.dorms.form', [
            'dorm' => new Dorm(),
        ]);
    }

    public function store(DormRequest $request)
    {
        Dorm::create($request->validated());

        return redirect()->route('admin.university.dorms.index')
            ->with('success', 'Dorm created successfully.');
    }

    public function edit(Dorm $dorm)
    {
        return view('admin.university.dorms.form', [
            'dorm' => $dorm,
        ]);
    }

    public function update(DormRequest $request, Dorm $dorm)
    {
        $dorm->update($request->validated());

        return redirect()->route('admin.university.dorms.index')
            ->with('success', 'Dorm updated successfully.');
    }

    public function destroy(Dorm $dorm)
    {
        $dorm->delete();

        return redirect()->route('admin.university.dorms.index')
            ->with('success', 'Dorm deleted successfully.');
    }
}
