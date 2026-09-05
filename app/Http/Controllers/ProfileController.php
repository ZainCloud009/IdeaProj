<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('Pages.edit_profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,

            // always required
            'current_password' => ['required', 'current_password'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);
        // $user->name = $request->name;
        // $user->email = $request->email;

        // $user->save();

        return redirect()->route('ideas')->with('success', 'Profile updated successfully');
    }
}
