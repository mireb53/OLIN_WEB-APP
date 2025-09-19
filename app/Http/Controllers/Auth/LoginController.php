<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 

class LoginController extends Controller
{
    // Shows the single login form
    public function showLoginForm()
    {
        return view('auth.login'); // This loads resources/views/auth/login.blade.php
    }

    // Handles the login form submission
    public function login(Request $request)
    {
        // Validate the input (email and password must be present)
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt to log the user in
        if (Auth::attempt($credentials)) {
            // Regenerate session to prevent session fixation attacks
            $request->session()->regenerate();

            $user = Auth::user(); // Get the currently logged-in user

            // Update last login timestamp
            $user->update(['last_login_at' => now()]);

            if ($user->role === 'admin') {
                return redirect()->intended('/admin/dashboard'); 
            } elseif ($user->role === 'instructor') {
                return redirect()->intended('/instructor/dashboard'); 
            } else {
                // Default redirect for other roles (e.g., 'student' if you had one)
                return redirect()->intended('/dashboard'); // Generic user dashboard
            }
        }

        // If authentication fails, redirect back with an error message
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Handles user logout
    public function logout(Request $request)
    {
        Auth::logout(); // Log out the user

        $request->session()->invalidate(); // Invalidate the current session
        $request->session()->regenerateToken(); // Regenerate CSRF token for future requests

        return redirect('/login'); // Redirect to login page after logout
    }
}