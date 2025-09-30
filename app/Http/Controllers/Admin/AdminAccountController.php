<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class AdminAccountController extends Controller
{
    public function index()
    {
        $admin = Auth::user();
        return view('admin.admin_account', compact('admin'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Allow editing of name (via first_name/last_name), phone, address
        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:2000'],
        ]);

        // Compute name from first/last if provided, else keep existing name
        $first = trim((string)($validated['first_name'] ?? ''));
        $last = trim((string)($validated['last_name'] ?? ''));
        if ($first !== '' || $last !== '') {
            $user->name = trim($first.' '.$last);
        }

        if (array_key_exists('phone', $validated)) {
            $user->phone = $validated['phone'];
        }
        if (array_key_exists('address', $validated)) {
            $user->address = $validated['address'];
        }

        $user->save();

        return back()->with('status', 'Profile updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.'])->with('error', 'Password change failed.');
        }

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return back()->with('status', 'Password updated successfully.');
    }

    public function uploadProfileImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $user = Auth::user();

        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        $imagePath = $request->file('profile_image')->store('profile_images', 'public');
        $user->profile_image = $imagePath;
        $user->save();

        // For simplicity, redirect back. Could return JSON if using fetch.
        return back()->with('status', 'Profile image updated successfully!');
    }

    public function deleteProfileImage()
    {
        $user = Auth::user();
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
            $user->profile_image = null;
            $user->save();
            return back()->with('status', 'Profile image deleted successfully!');
        }
        return back()->with('status', 'No profile image to delete.');
    }
}
