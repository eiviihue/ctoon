<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $profile = auth()->user()->profile;
        return view('profile.edit', compact('profile'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:4096',
        ]);

        $user = auth()->user();
        $profile = $user->profile ?: $user->profile()->create([]);

        if ($request->hasFile('avatar')) {
            if ($profile->avatar_path)
                Storage::disk('public')->delete($profile->avatar_path);
            $profile->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->bio = $data['bio'] ?? $profile->bio;
        $profile->save();

        return back()->with('success', 'Profile updated');
    }
}

