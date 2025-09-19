<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;

class UserManagementController extends Controller
{
    /**
     * Display the user management page with all users
     */
    public function index()
    {
        $users = User::with('program', 'section')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $usersByRole = [
            'admin' => User::where('role', 'admin')->count(),
            'instructor' => User::where('role', 'instructor')->count(),
            'student' => User::where('role', 'student')->count(),
        ];

        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $inactiveUsers = User::where('status', 'inactive')->count();

        return view('admin.user_management', compact(
            'users', 
            'usersByRole', 
            'totalUsers', 
            'activeUsers', 
            'inactiveUsers'
        ));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|in:admin,instructor,student',
            'password' => 'required|string|min:8|confirmed',
            'program_id' => 'nullable|exists:programs,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'program_id' => $request->program_id,
            'section_id' => $request->section_id,
            'email_verified_at' => now(), // Auto-verify for admin created users
            'status' => 'active',
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load('program', 'section');
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $user->load('program', 'section');
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,instructor,student',
            'program_id' => 'nullable|exists:programs,id',
            'section_id' => 'nullable|exists:sections,id',
            'status' => 'required|in:active,inactive',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'program_id' => $request->program_id,
            'section_id' => $request->section_id,
            'status' => $request->status,
        ]);

        // Update password if provided
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy(User $user)
    {
        // Prevent deleting the currently logged-in admin
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user status (activate/deactivate)
     */
    public function toggleStatus(User $user)
    {
        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        $statusText = $user->status === 'active' ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.users.index')
            ->with('success', "User {$statusText} successfully.");
    }

    /**
     * Search users based on criteria
     */
    public function search(Request $request)
    {
        $query = User::with('program', 'section');

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('created_at', 'desc')->get();
        
        $usersByRole = [
            'admin' => User::where('role', 'admin')->count(),
            'instructor' => User::where('role', 'instructor')->count(),
            'student' => User::where('role', 'student')->count(),
        ];

        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $inactiveUsers = User::where('status', 'inactive')->count();

        return view('admin.user_management', compact(
            'users', 
            'usersByRole', 
            'totalUsers', 
            'activeUsers', 
            'inactiveUsers'
        ));
    }

    /**
     * Request 2FA code for sensitive user management actions
     */
    public function request2FACode(Request $request)
    {
        $user = Auth::user();
        $code = rand(100000, 999999);

        // Store code with expiration (5 min + 5 sec)
        session([
            'user_management_2fa_code' => $code,
            'user_management_2fa_expires' => now()->addMinutes(5)->addSeconds(5),
        ]);

        // Send code via email
        Mail::raw("Your user management verification code is: $code", function ($message) use ($user) {
            $message->to($user->email)
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('User Management Verification Code');
        });

        return response()->json(['success' => true]);
    }

    /**
     * Verify 2FA code for user management actions
     */
    public function verify2FACode(Request $request)
    {
        $inputCode = $request->input('code');
        $storedCode = session('user_management_2fa_code');
        $expiresAt = session('user_management_2fa_expires');

        if (!$storedCode || !$expiresAt || now()->isAfter($expiresAt)) {
            return response()->json(['success' => false, 'message' => 'Code expired or invalid']);
        }

        if ($inputCode == $storedCode) {
            // Clear the code from session
            session()->forget(['user_management_2fa_code', 'user_management_2fa_expires']);
            
            // Set verification flag
            session(['user_management_2fa_verified' => true, 'user_management_2fa_verified_at' => now()]);
            
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid code']);
    }

    /**
     * Show the user permissions page
     */
    public function permissions(User $user)
    {
        if ($user->role !== 'admin') {
            return redirect()->route('admin.users.index')
                ->with('error', 'Only admin users have permission settings');
        }

        return view('admin.users.permissions', compact('user'));
    }
}