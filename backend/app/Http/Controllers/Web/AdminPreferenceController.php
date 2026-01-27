<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminPreferenceController extends Controller
{
    public function updateTheme(Request $request)
    {
        $data = $request->validate([
            'theme' => ['required', 'in:light,dark'],
        ]);

        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        $user->ui_theme = $data['theme'];
        $user->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Theme updated.',
                'theme' => $user->ui_theme,
            ]);
        }

        return back()->with('success', 'Theme updated.');
    }
}
