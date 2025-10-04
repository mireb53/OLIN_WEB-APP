<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserManagementController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the user management page with school-filtered users
     */
    public function index(Request $request)
    {
        $role = $request->input('role', 'instructor');
        $status = $request->input('status', '');
        $search = $request->input('search', '');
        $schoolFilter = $request->input('school_id', ''); // For SuperAdmin school filtering

        $actor = Auth::user();
        
        // Authorization check
        if (!$actor || (!$actor->isSuperAdmin() && !$actor->isSchoolAdmin())) {
            abort(403, 'Unauthorized access to user management');
        }

        $query = User::query();
        $activeSchool = null;
        $schools = collect();
        
        if ($actor->isSuperAdmin()) {
            // Super Admin: Can see all schools and filter by school
            $schools = School::orderBy('name')->get();
            
            // Determine active school for filtering
            $activeSchoolId = $schoolFilter ?: Session::get('active_school');
            if ($activeSchoolId) {
                $activeSchool = School::find($activeSchoolId);
                if ($activeSchool) {
                    $query->where('school_id', $activeSchoolId);
                    // Update session to remember school selection
                    Session::put('active_school', $activeSchoolId);
                }
            }

            // If no school is selected, and schools exist, require manual selection via Settings
            if (!$activeSchool && $schools->isNotEmpty()) {
                return redirect()->route('admin.settings')
                    ->with('info', 'Please select a school to manage users.');
            }
            
        } elseif ($actor->isSchoolAdmin()) {
            // School Admin: Only see users from their assigned school
            if (!$actor->school_id) {
                abort(422, 'No school assigned to your admin account. Please contact Super Admin.');
            }
            
            $activeSchool = $actor->school;
            $schools = collect([$activeSchool]); // Only their school
            $query->where('school_id', $actor->school_id);
            
            // School admins cannot see super admins
            $query->where('role', '!=', User::ROLE_SUPER_ADMIN);
        }
        
        // Apply role filter
        if ($role && $role !== 'all') {
            if ($role === 'admin') {
                if ($actor->isSuperAdmin()) {
                    $query->whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_SCHOOL_ADMIN]);
                } else {
                    // School admins can only see other school admins from their school
                    $query->where('role', User::ROLE_SCHOOL_ADMIN);
                }
            } else {
                $query->where('role', $role);
            }
        }
        
        // Apply status filter
        if ($status && $status !== 'all' && $status !== '') {
            $query->where('status', $status);
        }
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }
        
        $users = $query->with('school')
                      ->orderBy('created_at', 'desc')
                      ->paginate(8)
                      ->appends($request->except('page'));

        // Get online user IDs based on session activity (last 15 minutes)
        $onlineThresholdMinutes = 15;
        $thresholdTimestamp = Carbon::now()->subMinutes($onlineThresholdMinutes)->timestamp;
        
        $sessionRows = DB::table('sessions')
            ->where('last_activity', '>=', $thresholdTimestamp)
            ->get();

        $onlineUserIds = [];
        foreach ($sessionRows as $sr) {
            if (!empty($sr->user_id)) {
                $onlineUserIds[] = (int)$sr->user_id;
                continue;
            }
            // Extract user id from payload for guest sessions that became authenticated
            $payload = $sr->payload ?? '';
            if (preg_match('/user_id";i:(\d+);/i', $payload, $m)) {
                $onlineUserIds[] = (int)$m[1];
            } elseif (preg_match('/"id";i:(\d+);/i', $payload, $m2)) {
                $onlineUserIds[] = (int)$m2[1];
            } elseif (preg_match('/"id"\s*:\s*(\d+)/', $payload, $m3)) {
                $onlineUserIds[] = (int)$m3[1];
            }
        }
        
        $onlineUserIds = array_values(array_unique(array_filter($onlineUserIds)));

        return view('admin.user_management', compact(
            'users', 
            'role', 
            'status', 
            'search', 
            'schools', 
            'activeSchool',
            'schoolFilter',
            'onlineUserIds'
        ));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $this->authorize('create', User::class);
        $schools = School::orderBy('name')->get();
        return view('admin.users.create', compact('schools'));
    }

    /**
     * Store a newly created user in storage
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $actor = Auth::user();

        $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
            // Only allow creating Student or Instructor here; admin promotions happen via Edit
            'role' => 'required|in:instructor,student',
            'program_id' => 'nullable|exists:programs,id',
            'section_id' => 'nullable|exists:sections,id',
            'school_id' => 'nullable|exists:schools,id',
        ]);

        // NEW: Role assignment validation
        if ($request->role === User::ROLE_SUPER_ADMIN && !$actor->isSuperAdmin()) {
            return redirect()->back()->withInput()->with('error', 'Only a Super Admin can create another Super Admin.');
        }

        // Determine school assignment
        $schoolId = null;
        if ($actor && $actor->isSuperAdmin()) {
            // Super Admin: Use active_school session
            $schoolId = Session::get('active_school') ?: $request->school_id;
        } elseif ($actor && $actor->isSchoolAdmin()) {
            // School Admin: Force their own school
            $schoolId = $actor->school_id;
        }

        // For student/instructor, no password required at creation (Google SSO in most cases)
        $user = User::create([
            'name' => $request->input('name') ?: explode('@', $request->email)[0],
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make(Str::random(32)), // placeholder strong random; can be reset later
            'program_id' => $request->program_id,
            'section_id' => $request->section_id,
            'email_verified_at' => now(), // Auto-verify for admin created users
            'status' => 'active',
            'school_id' => $schoolId,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        // Eager-load relations used by role-specific profile pages
        $user->load(['program', 'section', 'school', 'taughtCourses', 'courses']);

        // Lightweight stats for display
        $taughtCount = method_exists($user, 'taughtCourses') ? $user->taughtCourses->count() : 0;
        $enrolledCount = method_exists($user, 'courses') ? $user->courses->count() : 0;

        return view('admin.users.show', compact('user', 'taughtCount', 'enrolledCount'));
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
        $this->authorize('update', $user);

        $actor = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:super_admin,school_admin,instructor,student',
            'program_id' => 'nullable|exists:programs,id',
            'section_id' => 'nullable|exists:sections,id',
            'status' => 'required|in:active,inactive,suspended',
            'school_id' => 'nullable|exists:schools,id',
            // Require admin password confirmation for sensitive action
            'admin_password' => 'required|string',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            // When role is admin, enforce password fields
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Verify admin password
        if (!$actor || !Hash::check($request->input('admin_password'), $actor->getAuthPassword())) {
            return redirect()->back()->withInput()->with('error', 'Admin password verification failed.');
        }

        // NEW: Role assignment validation
        if ($request->role === User::ROLE_SUPER_ADMIN && !$actor->isSuperAdmin()) {
            return redirect()->back()->withInput()->with('error', 'Only a Super Admin can assign the Super Admin role.');
        }
        // Prevent non-super admins from editing Super Admin accounts
        if ($user->role === User::ROLE_SUPER_ADMIN && !$actor->isSuperAdmin()) {
            return redirect()->back()->withInput()->with('error', 'You cannot edit a Super Admin.');
        }

        // School assignment logic
        $schoolId = null;
        if ($actor && $actor->isSuperAdmin()) {
            // Super Admin: Use active_school session or allow override
            $schoolId = $request->school_id ?: Session::get('active_school');
        } elseif ($actor && $actor->isSchoolAdmin()) {
            // School Admin: Keep user in same school
            $schoolId = $user->school_id;
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'program_id' => $request->program_id,
            'section_id' => $request->section_id,
            'status' => $request->status,
            'school_id' => $schoolId,
        ]);

        // If promoting to admin (school_admin or super_admin), require setting/resetting password
        $isPromotingToAdmin = in_array($request->role, [User::ROLE_SCHOOL_ADMIN, User::ROLE_SUPER_ADMIN]);
        if ($isPromotingToAdmin) {
            if (!$request->filled('password')) {
                return redirect()->back()->withInput()->with('error', 'Please set a password when assigning an Admin role.');
            }
        }

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Reset the specified user's password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $this->authorize('resetPassword', $user);

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
            'admin_password' => 'required|string',
        ]);

        // Verify admin password
        $actor = Auth::user();
        if (!$actor || !Hash::check($request->input('admin_password'), $actor->getAuthPassword())) {
            return redirect()->route('admin.user_management')
                ->with('error', 'Admin password verification failed.');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.user_management')
            ->with('success', 'Password reset successfully.');
    }

    /**
     * Bulk import students from CSV/Excel file
     */
   public function bulkImport(Request $request)
{
    $actor = Auth::user();
    $this->authorize('create', User::class);

    $request->validate([
        'file' => 'required|file|mimes:csv,xlsx,xls|max:2048'
    ]);

    try {
        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        $data = [];

        // ✅ Handle CSV manually
        if ($extension === 'csv') {
            $csvData = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_map('trim', array_shift($csvData));
            foreach ($csvData as $row) {
                if (count($header) === count($row)) {
                    $data[] = array_combine($header, $row);
                }
            }
        } else {
            // ✅ Handle Excel via Laravel Excel
            $data = Excel::toArray([], $file)[0]; // first sheet
            $header = array_map('trim', array_shift($data));
            $data = array_map(fn($row) => array_combine($header, $row), $data);
        }

        $imported = 0;
        $errors = [];

        foreach ($data as $row) {
            try {
                if (empty($row['Name']) || empty($row['Email']) || empty($row['Password'])) {
                    $errors[] = "Missing required fields for row: " . json_encode($row);
                    continue;
                }

                if (User::where('email', $row['Email'])->exists()) {
                    $errors[] = "Email {$row['Email']} already exists";
                    continue;
                }

                // Default values (normalize role values)
                $roleRaw = $row['Role']   ?? 'student';
                $map = [
                    'superadmin' => User::ROLE_SUPER_ADMIN,
                    'super_admin' => User::ROLE_SUPER_ADMIN,
                    'schooladmin' => User::ROLE_SCHOOL_ADMIN,
                    'school_admin' => User::ROLE_SCHOOL_ADMIN,
                    'instructor' => User::ROLE_INSTRUCTOR,
                    'student' => User::ROLE_STUDENT,
                    'admin' => User::ROLE_SCHOOL_ADMIN, // treat generic admin as school_admin
                ];
                $key = strtolower(trim((string)$roleRaw));
                $role = $map[$key] ?? User::ROLE_STUDENT;
                $status = $row['Status'] ?? 'active';

                // ✅ Validate role & status against your system
                $validRoles  = ['student', 'instructor', 'schoolAdmin', 'superAdmin'];
                $validStatus = ['active', 'inactive', 'suspended'];

                if (!in_array($role, $validRoles)) {
                    $errors[] = "Invalid role '{$row['Role']}' for email {$row['Email']}";
                    continue;
                }

                if (!in_array($status, $validStatus)) {
                    $errors[] = "Invalid status '{$row['Status']}' for email {$row['Email']}";
                    continue;
                }

                // Determine school assignment
                $schoolId = null;
                if ($actor && $actor->isSuperAdmin()) {
                    $schoolId = Session::get('active_school') ?: $request->school_id;
                } elseif ($actor && $actor->isSchoolAdmin()) {
                    $schoolId = $actor->school_id;
                }

                // Enforce privilege: only Super Admin can import Super Admins
                if ($role === User::ROLE_SUPER_ADMIN && !($actor && $actor->isSuperAdmin())) {
                    $errors[] = "Only a Super Admin can import Super Admin accounts ({$row['Email']}).";
                    continue;
                }

                // ✅ Create user
                User::create([
                    'name'              => $row['Name'],
                    'email'             => $row['Email'],
                    'password'          => Hash::make($row['Password']),
                    'role'              => $role,
                    'status'            => $status,
                    'email_verified_at' => now(),
                    'school_id'         => $schoolId,
                ]);

                $imported++;
            } catch (Exception $e) {
                $errors[] = "Error importing {$row['Email']}: " . $e->getMessage();
            }
        }

        $message = "Successfully imported {$imported} users.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return redirect()->route('admin.user_management', ['role' => 'student'])
            ->with('success', $message);

    } catch (Exception $e) {
        return redirect()->route('admin.user_management')
            ->with('error', 'Error processing file: ' . $e->getMessage());
    }
}


    /**
     * Remove the specified user from storage
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        // Prevent deleting the currently logged-in admin
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        request()->validate([
            // Require typed confirmation (either the literal word DELETE or the user's email)
            'confirm' => 'required|string',
        ]);

        $confirm = trim((string) request('confirm'));
        if (!($confirm === 'DELETE' || strcasecmp($confirm, $user->email) === 0)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Deletion not confirmed. Type DELETE or the user\'s exact email to confirm.');
        }

        // Check if the user is an instructor with courses
        $coursesCount = DB::table('courses')->where('instructor_id', $user->id)->count();
        if ($coursesCount > 0) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cannot delete this user because they are assigned as an instructor to one or more courses. Please reassign these courses to another instructor first.');
        }

        // Check for other possible relationships (customize based on your database structure)
        // Add more checks here if needed for other relationships

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user status (activate/deactivate)
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('update', $user);
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
        $role = $request->input('role', 'instructor');
        $status = $request->input('status', '');
        $search = $request->input('search', '');

        $query = User::query();
        if ($role && $role !== 'all') {
            if ($role === 'admin') {
                $query->whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_SCHOOL_ADMIN]);
            } else {
                $query->where('role', $role);
            }
        }
        if ($status && $status !== 'all' && $status !== '') {
            $query->where('status', $status);
        }
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(15)->appends($request->except('page'));

        return view('admin.user_management', compact('users', 'role', 'status', 'search'));
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