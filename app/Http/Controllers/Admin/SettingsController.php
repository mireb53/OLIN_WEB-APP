<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\School;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SettingsController extends Controller
{
    use AuthorizesRequests;

    /**
     * Show the settings edit form with school selection and role-based permissions.
     */
    public function edit(Request $request)
    {
        $user = Auth::user();

        // Authorization check
        if (!$user || (!$user->isSuperAdmin() && !$user->isSchoolAdmin())) {
            abort(403, 'Unauthorized access to settings');
        }

        if ($user->isSuperAdmin()) {
            // Super Admin: Can select and manage any school
            $schools = School::with('users')->orderBy('name')->get();
            
            // Handle school selection - priority: request > session > first school (if any)
            $selectedSchoolId = $request->get('school_id') ?? Session::get('active_school');
            $activeSchool = null;
            
            if ($selectedSchoolId) {
                $activeSchool = School::find($selectedSchoolId);
            }
            
            if (!$activeSchool && $schools->isNotEmpty()) {
                $activeSchool = $schools->first();
            }
            
            // Store active school in session for other pages (only if school exists)
            if ($activeSchool) {
                Session::put('active_school', $activeSchool->id);
            } else {
                // Clear session if no schools exist
                Session::forget('active_school');
            }
            
            // Load global settings and school-specific settings
            $globalSettings = Setting::whereNull('school_id')->first() ?? new Setting();
            $schoolSettings = $activeSchool ? 
                ($activeSchool->settings ?? new Setting(['school_id' => $activeSchool->id])) : 
                new Setting();
            
            // For Super Admin, all permissions are enabled
            $canEditSchoolInfo = true;
            $canManageAdmins = true;
            $canEditGlobalSettings = true;

        } elseif ($user->isSchoolAdmin()) {
            // School Admin: Limited to their assigned school only
            $activeSchool = $user->school;
            
            if (!$activeSchool) {
                abort(422, 'No school assigned to this admin account. Please contact Super Admin.');
            }
            
            $schools = collect([$activeSchool]); // Only their school
            $globalSettings = new Setting(); // No access to global settings
            $schoolSettings = $activeSchool->settings ?? new Setting(['school_id' => $activeSchool->id]);
            
            // Determine permissions based on admin hierarchy
            $isHeadAdmin = $this->isHeadAdmin($user, $activeSchool);
            $canEditSchoolInfo = $isHeadAdmin;
            $canManageAdmins = false; // School admins cannot manage other admins
            $canEditGlobalSettings = false; // School admins never edit global settings
        }

        // Get school administrators for management section (Super Admin only)
        $schoolAdmins = ($user->isSuperAdmin() && $activeSchool) ? 
            User::where('school_id', $activeSchool->id)
                ->where('role', 'school_admin')
                ->orderBy('name')
                ->get() : 
            collect();

        return view('admin.settings', compact(
            'schools',
            'activeSchool', 
            'globalSettings',
            'schoolSettings',
            'schoolAdmins',
            'canEditSchoolInfo',
            'canManageAdmins', 
            'canEditGlobalSettings'
        ));
    }

    /**
     * Update settings based on user role and permissions.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user || (!$user->isSuperAdmin() && !$user->isSchoolAdmin())) {
            abort(403, 'Unauthorized to update settings');
        }

        // Determine permissions and active school
        if ($user->isSuperAdmin()) {
            $activeSchool = Session::get('active_school') ? School::find(Session::get('active_school')) : null;
            $canEditSchoolInfo = true;
            $canEditGlobalSettings = true;
        } else {
            $activeSchool = $user->school;
            if (!$activeSchool) {
                return redirect()->back()->withErrors(['error' => 'No school assigned to your account.']);
            }
            $canEditSchoolInfo = $this->isHeadAdmin($user, $activeSchool);
            $canEditGlobalSettings = false;
        }

        // Validate based on permissions
        $rules = $this->getValidationRules($user, $canEditSchoolInfo);
        $validated = $request->validate($rules);

        try {
            // Update global settings (Super Admin only)
            if ($canEditGlobalSettings && $user->isSuperAdmin()) {
                $globalSettings = Setting::firstOrCreate(['school_id' => null]);
                $this->fillGlobalSettings($globalSettings, $validated);
                $globalSettings->save();
            }

            // Update school information and settings (only if school exists)
            if ($activeSchool) {
                // Update school info if permitted
                if ($canEditSchoolInfo) {
                    $schoolUpdateData = [];
                    if (isset($validated['school_name'])) {
                        $schoolUpdateData['name'] = $validated['school_name'];
                    }
                    if (isset($validated['school_address'])) {
                        $schoolUpdateData['address'] = $validated['school_address'];
                    }
                    if (isset($validated['school_contact'])) {
                        $schoolUpdateData['email'] = $validated['school_contact'];
                    }
                    
                    if (!empty($schoolUpdateData)) {
                        $activeSchool->update($schoolUpdateData);
                    }
                }

                // Update school-specific settings
                $schoolSettings = Setting::firstOrCreate(['school_id' => $activeSchool->id]);
                $this->fillSchoolSettings($schoolSettings, $validated);
                $schoolSettings->save();
            }

            return redirect()->back()->with('success', 'Settings have been updated successfully!');
            
        } catch (\Exception $e) {
            \Log::error('Settings update failed:', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update settings: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Fill global settings with validated data.
     */
    private function fillGlobalSettings(Setting $settings, array $validated)
    {
        $globalFields = [
            'platform_name',
            'default_language', 
            'timezone',
            'max_file_size',
            'allowed_file_types'
        ];

        foreach ($globalFields as $field) {
            if (isset($validated[$field])) {
                $settings->$field = $validated[$field];
            }
        }
    }

    /**
     * Fill school-specific settings with validated data.
     */
    private function fillSchoolSettings(Setting $settings, array $validated)
    {
        $schoolFields = [
            'current_semester',
            'academic_year',
            'start_date',
            'end_date',
            'max_file_size',
            'allowed_file_types'
        ];

        foreach ($schoolFields as $field) {
            if (isset($validated[$field])) {
                $settings->$field = $validated[$field];
            }
        }
    }

    /**
     * Handle school selection for Super Admin.
     */
    public function selectSchool(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Only Super Admin can select schools');
        }
        
        $request->validate([
            'school_id' => ['required', 'integer', 'exists:schools,id']
        ]);
        
        // Store selected school in session
        Session::put('active_school', $request->school_id);
        
        return redirect()->route('admin.settings')->with('success', 'School context updated successfully.');
    }

    /**
     * Create a new school (Super Admin only) and set it active.
     */
    public function createSchool(Request $request)
    {
        // Enhanced logging at the very beginning
        \Log::info('=== CREATE SCHOOL METHOD CALLED ===');
        \Log::info('Request method: ' . $request->method());
        \Log::info('Request URL: ' . $request->url());
        \Log::info('All request data:', $request->all());

        $user = Auth::user();
        if (!$user || !$user->isSuperAdmin()) {
            \Log::error('Unauthorized access to createSchool', [
                'user' => $user ? $user->toArray() : null,
                'is_super_admin' => $user ? $user->isSuperAdmin() : false
            ]);
            abort(403, 'Only Super Admin can create schools');
        }

        \Log::info('User authorized:', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role' => $user->role
        ]);

        // Validate the request
        try {
            $validated = $request->validate([
                'new_school_name' => ['required','string','max:255'],
                'new_school_address' => ['nullable','string','max:500'],
                'new_school_email' => ['nullable','email','max:255'],
            ]);
            \Log::info('Validation passed:', $validated);
        } catch (\Exception $e) {
            \Log::error('Validation failed:', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            throw $e;
        }

        try {
            // Generate a short unique code based on name (e.g., OLIN, MAIN1, etc.)
            $baseCode = strtoupper(preg_replace('/[^A-Z0-9]/', '', substr($validated['new_school_name'], 0, 8)));
            if ($baseCode === '') {
                $baseCode = 'SCH';
            }
            $code = $baseCode;
            $counter = 1;
            while (School::where('code', $code)->exists()) {
                $code = $baseCode . $counter;
                $counter++;
            }

            \Log::info('Generated school code:', [
                'base_code' => $baseCode,
                'final_code' => $code,
                'counter' => $counter - 1
            ]);

            $schoolData = [
                'name' => $validated['new_school_name'],
                'code' => $code,
                'address' => $validated['new_school_address'] ?? null,
                'email' => $validated['new_school_email'] ?? null,
            ];

            \Log::info('About to create school with data:', $schoolData);

            $school = School::create($schoolData);

            \Log::info('School created successfully:', [
                'school_id' => $school->id,
                'school' => $school->toArray(),
                'schools_count_after' => School::count()
            ]);

            // Set session active school
            Session::put('active_school', $school->id);
            \Log::info('Session updated with active school ID: ' . $school->id);

            \Log::info('About to redirect to admin.settings with success message');
            
            return redirect()->route('admin.settings')->with('success', 'New school created and set as active! (ID: ' . $school->id . ', Count: ' . School::count() . ')');
            
        } catch (\Exception $e) {
            \Log::error('Failed to create school:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to create school: '.$e->getMessage()])->withInput();
        }
    }
    
    /**
     * Determine if user is Head Admin of the school.
     * Head Admin is the first school_admin assigned to a school or explicitly designated.
     */
    private function isHeadAdmin($user, $school)
    {
        if (!$user || !$school || !$user->isSchoolAdmin()) {
            return false;
        }
        
        // Check if user has a 'head_admin' flag (if you add this field to users table)
        if (isset($user->admin_type) && $user->admin_type === 'head_admin') {
            return true;
        }
        
        // Fallback: First school admin assigned to school is considered Head Admin
        $firstAdmin = User::where('school_id', $school->id)
            ->where('role', 'school_admin')
            ->orderBy('created_at')
            ->first();
            
        return $firstAdmin && $firstAdmin->id === $user->id;
    }

    /**
     * Get validation rules based on user role and permissions.
     */
    private function getValidationRules($user, $canEditSchoolInfo = false)
    {
        $baseRules = [
            'current_semester' => ['nullable', 'string', 'max:50'],
            'academic_year' => ['nullable', 'string', 'max:20', 'regex:/^\d{4}-\d{4}$/'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'max_file_size' => ['nullable', 'numeric', 'min:1', 'max:1000'],
            'allowed_file_types' => ['nullable', 'string', 'max:255', 'regex:/^[\.\w,\s]+$/'],
        ];

        // Add school info rules if user can edit them
        if ($canEditSchoolInfo) {
            $baseRules = array_merge($baseRules, [
                'school_name' => ['nullable', 'string', 'max:255'],
                'school_address' => ['nullable', 'string', 'max:500'],
                'school_contact' => ['nullable', 'email', 'max:255'],
            ]);
        }

        // Super Admin can update global system settings
        if ($user->isSuperAdmin()) {
            $baseRules = array_merge($baseRules, [
                'platform_name' => ['nullable', 'string', 'max:255'],
                'default_language' => ['nullable', 'string', 'max:50'],
                'timezone' => ['nullable', 'string', 'max:100'],
            ]);
        }

        return $baseRules;
    }

    /**
     * Display the admin account and settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin.admin_account');
    }
}
