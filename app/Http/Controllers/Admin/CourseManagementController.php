<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use App\Models\Assessment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminVerificationCode;
use ZipArchive;

class CourseManagementController extends Controller
{
    public function index()
    {
        $activeSchoolId = $this->getActiveSchoolId();
        // Server-side filtering and pagination
        $query = Course::with(['instructor','program'])
            ->when($activeSchoolId, function($q) use ($activeSchoolId) {
                $q->whereHas('instructor', function($iq) use ($activeSchoolId){
                    $iq->where('school_id', $activeSchoolId);
                });
            })
            ->withCount('students');
        // Build departments list dynamically for the active school; fallback to defaults if empty
        $departmentsList = Course::when($activeSchoolId, function($q) use ($activeSchoolId){
                $q->whereHas('instructor', function($iq) use ($activeSchoolId){ $iq->where('school_id', $activeSchoolId); });
            })
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->filter()
            ->values()
            ->toArray();
        if (empty($departmentsList)) {
            $departmentsList = ['CCS','CAS','CHS','CEA','CTDE','CTHBM'];
        }

        // search q: course title, id, instructor name, program name
        $q = request()->input('q');
        if ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('id', 'like', "%{$q}%")
                    ->orWhereHas('instructor', function($iq) use ($q) { $iq->where('name','like',"%{$q}%"); })
                    ->orWhereHas('program', function($pq) use ($q) { $pq->where('name','like',"%{$q}%"); });
            });
        }

        // filter by program (accept numeric id or program name/code)
        $programFilter = request()->input('program');
        if ($programFilter) {
            if (ctype_digit((string)$programFilter)) {
                $query->where('program_id', $programFilter);
            } else {
                $query->whereHas('program', function($pq) use ($programFilter) {
                    $pq->where('name', $programFilter);
                });
            }
        }

        // filter by status
        $statusFilter = request()->input('status');
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        // filter by course department
        $departmentFilter = request()->input('department');
        if ($departmentFilter && in_array($departmentFilter, $departmentsList, true)) {
            $query->where('department', $departmentFilter);
        }

    $courses = $query->orderBy('created_at','desc')->paginate(10)->withQueryString();

        // Handle AJAX requests for filtering
        if (request()->ajax() || request()->wantsJson()) {
            $html = '';
            if ($courses->count() > 0) {
                foreach ($courses as $course) {
                    $html .= view('admin.courses.partials.course-row', compact('course'))->render();
                }
            } else {
                $html = '<tr><td colspan="6" class="px-6 py-8 text-center text-gray-500">No courses found matching your criteria.</td></tr>';
            }
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => method_exists($courses, 'links') ? $courses->links()->render() : '',
                'total' => $courses->total()
            ]);
        }

        // load programs for the create modal program select (scoped to active school if set)
        $programs = Program::orderBy('name')
            ->when($activeSchoolId, function($q) use ($activeSchoolId) {
                $q->whereExists(function($sq) use ($activeSchoolId){
                    $sq->selectRaw('1')
                       ->from('courses as pc')
                       ->join('users as pi', 'pc.instructor_id', '=', 'pi.id')
                       ->whereColumn('pc.program_id', 'programs.id')
                       ->where('pi.school_id', $activeSchoolId);
                });
            })
            ->get();
        return view('admin.course_management', compact('courses','programs'))
            ->with('departments', $departmentsList)
            ->with('selectedDepartment', $departmentFilter);
    }

    // GET /admin/courses/search -> reuse index filters
    public function search(Request $request)
    {
        return $this->index();
    }

    // GET /admin/courses/create -> no separate page; redirect to management
    public function create(Request $request)
    {
        return redirect()->route('admin.course_management');
    }

    /**
     * AJAX: find instructor by email (returns id and name) for the create modal
     */
    public function findInstructor(Request $request)
    {
        $activeSchoolId = $this->getActiveSchoolId();
        $request->validate(['email' => 'required|email']);
        $email = $request->input('email');
        $user = User::where('email', $email)
            ->where('role', 'instructor')
            ->when($activeSchoolId, function($q) use ($activeSchoolId){ $q->where('school_id', $activeSchoolId); })
            ->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Instructor not found.'], 404);
        }
        return response()->json(['success' => true, 'instructor' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email]]);
    }

    /**
     * Store a new course as admin (associates to instructor)
     */
    public function store(Request $request)
    {
        $activeSchoolId = $this->getActiveSchoolId();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'course_code' => 'required|string|max:50|unique:courses,course_code',
            'status' => 'required|in:published,draft,archived',
            'program_name' => 'required|string|max:50',
            'department' => 'required|string|max:10',
            'description' => 'nullable|string',
            'credits' => 'nullable|numeric|min:0',
            'instructor_id' => 'required|exists:users,id',
        ]);

        // Ensure instructor belongs to active school when set
        if ($activeSchoolId) {
            $ok = User::where('id', $validated['instructor_id'])
                ->where('role','instructor')
                ->where('school_id', $activeSchoolId)
                ->exists();
            if (!$ok) {
                return response()->json(['success' => false, 'message' => 'Instructor must belong to the active school.'], 422);
            }
        }

        // Find or create program by name (acronym)
        $program = Program::firstOrCreate(['name' => $validated['program_name']]);

        $course = Course::create([
            'title' => $validated['title'],
            'course_code' => $validated['course_code'],
            'status' => $validated['status'],
            'program_id' => $program->id,
            'department' => $validated['department'],
            'instructor_id' => $validated['instructor_id'],
            'description' => $validated['description'] ?? null,
            'credits' => $validated['credits'] ?? null,
        ]);

        // load relations for client-side append
        $course->load('instructor','program');

        return response()->json([
            'success' => true,
            'course' => $course
        ]);
    }

    public function show($courseId)
    {
        $activeSchoolId = $this->getActiveSchoolId();
        $course = \App\Models\Course::with([
            'instructor',
            'program',
            'topics',
            'materials',
            'assessments',
            'students'
        ])
        ->when($activeSchoolId, function($q) use ($activeSchoolId){
            $q->whereHas('instructor', function($iq) use ($activeSchoolId){ $iq->where('school_id', $activeSchoolId); });
        })
        ->findOrFail($courseId);

        // If the request expects JSON (AJAX/modal), return JSON payload
        if (request()->wantsJson() || request()->ajax()) {
            // Format students for modal (only id, name, email)
            $course->students = $course->students->map(function($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'email' => $s->email
                ];
            })->values();
            return response()->json(['success' => true, 'course' => $course]);
        }
        // For direct page access, redirect back to course management (we now use modals)
    return redirect()->route('admin.course_management');
    }

    /**
     * Show a full page view of the course details (same content as modal).
     */
    public function showDetails($courseId)
    {
        $activeSchoolId = $this->getActiveSchoolId();
        $course = \App\Models\Course::with([
            'instructor',
            'program',
            'topics',
            'materials',
            'assessments',
            'students'
        ])
        ->when($activeSchoolId, function($q) use ($activeSchoolId){
            $q->whereHas('instructor', function($iq) use ($activeSchoolId){ $iq->where('school_id', $activeSchoolId); });
        })
        ->findOrFail($courseId);

        return view('admin.courses.course-details', compact('course'));
    }

    public function edit($courseId)
    {
        $activeSchoolId = $this->getActiveSchoolId();
        $course = Course::with(['instructor','program'])
            ->when($activeSchoolId, function($q) use ($activeSchoolId){
                $q->whereHas('instructor', function($iq) use ($activeSchoolId){ $iq->where('school_id', $activeSchoolId); });
            })
            ->findOrFail($courseId);
        // Return JSON for modal edit; redirect to management if accessed directly
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'course' => $course]);
        }
    return redirect()->route('admin.course_management');
    }

    public function update(Request $request, $courseId)
    {
        $activeSchoolId = $this->getActiveSchoolId();
        $course = Course::when($activeSchoolId, function($q) use ($activeSchoolId){
                $q->whereHas('instructor', function($iq) use ($activeSchoolId){ $iq->where('school_id', $activeSchoolId); });
            })
            ->findOrFail($courseId);

        // Require course-specific OTP verification before allowing updates
        $verifiedKey = 'course_2fa_verified_'.$course->id;
        $verifiedAtKey = 'course_2fa_verified_at_'.$course->id;
        $verified = (bool) session($verifiedKey);
        $verifiedAt = session($verifiedAtKey);
        if (!$verified || ($verifiedAt && now()->diffInMinutes($verifiedAt) > 10)) { // 10-minute window
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Verification required. Please request and enter the code sent to the instructor.'], 403);
            }
            return redirect()->back()->with('error', 'Verification required.');
        }
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'course_code' => 'required|string|max:50|unique:courses,course_code,' . $course->id,
            'status' => 'required|in:published,draft,archived',
            'program_name' => 'nullable|string|max:50',
            'department' => 'required|string|max:10',
            'description' => 'nullable|string',
            'credits' => 'nullable|numeric|min:0'
        ]);

        // Handle program creation/finding like in store method
        if (!empty($validated['program_name'])) {
            $program = Program::firstOrCreate(['name' => $validated['program_name']]);
            $validated['program_id'] = $program->id;
        } else {
            $validated['program_id'] = null;
        }

        // Do not persist program_name string column
        unset($validated['program_name']);

        $course->fill($validated);
        $course->save();

        // Clear course OTP verification after successful update
        session()->forget([$verifiedKey, $verifiedAtKey]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'course' => $course->fresh(['program','instructor'])]);
        }
        return redirect()->route('admin.courseManagement')->with('success', 'Course updated successfully.');
    }

    public function destroy($courseId)
    {
        $activeSchoolId = $this->getActiveSchoolId();
        $course = Course::when($activeSchoolId, function($q) use ($activeSchoolId){
                $q->whereHas('instructor', function($iq) use ($activeSchoolId){ $iq->where('school_id', $activeSchoolId); });
            })
            ->findOrFail($courseId);

        // Require course-specific OTP verification before allowing deletion
        $verifiedKey = 'course_2fa_verified_'.$course->id;
        $verifiedAtKey = 'course_2fa_verified_at_'.$course->id;
        $verified = (bool) session($verifiedKey);
        $verifiedAt = session($verifiedAtKey);
        if (!$verified || ($verifiedAt && now()->diffInMinutes($verifiedAt) > 10)) {
            return response()->json(['success' => false, 'message' => 'Verification required. Please request and enter the code sent to the instructor.'], 403);
        }
        $course->delete();
        // Clear verification after destructive action
        session()->forget([$verifiedKey, $verifiedAtKey]);
        return response()->json(['success' => true]);
    }

    /**
     * Send a one-time verification code to the course instructor's email for sensitive actions.
     */
    public function requestCourseOtp(Request $request, $courseId)
    {
        $activeSchoolId = $this->getActiveSchoolId();
        $course = Course::with('instructor')
            ->when($activeSchoolId, function($q) use ($activeSchoolId){
                $q->whereHas('instructor', function($iq) use ($activeSchoolId){ $iq->where('school_id', $activeSchoolId); });
            })
            ->findOrFail($courseId);

        $instructor = $course->instructor;
        if (!$instructor || empty($instructor->email)) {
            return response()->json(['success' => false, 'message' => 'Instructor email not available.'], 422);
        }

        $code = rand(100000, 999999);
        // Store in session with expiry and scope to this course
        session([
            'course_2fa_code_'.$course->id => (string) $code,
            'course_2fa_expires_'.$course->id => now()->addMinutes(5)->addSeconds(5),
        ]);

        // Persist to instructor record to allow DB verification fallback
        $instructor->forceFill([
            'email_verification_code' => (string) $code,
            'email_verification_code_expires_at' => now()->addMinutes(5)->addSeconds(5),
        ])->save();

        try {
            Mail::to($instructor->email)->send(new AdminVerificationCode($code, $instructor));
        } catch (\Throwable $e) {
            \Log::error('Failed to send course OTP mail: '.$e->getMessage());
            // Still return success in dev if mail fails? Better return error so UI can inform user.
            return response()->json(['success' => false, 'message' => 'Unable to send verification email. Please try again.'], 500);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Verify the course-specific OTP code entered by the admin.
     */
    public function verifyCourseOtp(Request $request, $courseId)
    {
        $request->validate(['code' => 'required|string']);
        $activeSchoolId = $this->getActiveSchoolId();
        $course = Course::with('instructor')
            ->when($activeSchoolId, function($q) use ($activeSchoolId){
                $q->whereHas('instructor', function($iq) use ($activeSchoolId){ $iq->where('school_id', $activeSchoolId); });
            })
            ->findOrFail($courseId);

        $inputCode = (string) $request->input('code');
        $sessionCode = (string) session('course_2fa_code_'.$course->id);
        $sessionExpires = session('course_2fa_expires_'.$course->id);
        $instructor = $course->instructor;

        $sessionValid = $sessionCode && $sessionExpires && now()->lt($sessionExpires) && hash_equals($sessionCode, $inputCode);
        $dbValid = false;
        if ($instructor) {
            $dbCode = (string) ($instructor->email_verification_code ?? '');
            $dbExpires = $instructor->email_verification_code_expires_at;
            $dbValid = $dbCode !== '' && $dbExpires && now()->lt($dbExpires) && hash_equals($dbCode, $inputCode);
        }

        if ($sessionValid || $dbValid) {
            // clear session code and mark verified for this course
            session()->forget(['course_2fa_code_'.$course->id, 'course_2fa_expires_'.$course->id]);
            session(['course_2fa_verified_'.$course->id => true, 'course_2fa_verified_at_'.$course->id => now()]);
            // clear instructor DB code to prevent reuse
            if ($instructor) {
                $instructor->forceFill([
                    'email_verification_code' => null,
                    'email_verification_code_expires_at' => null,
                ])->save();
            }
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid or expired code.'], 422);
    }

    /**
     * Resolve the active school id for the current admin context.
     */
    private function getActiveSchoolId()
    {
        $user = Auth::user();
        if (!$user) return null;
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return Session::get('active_school');
        }
        if (method_exists($user, 'isSchoolAdmin') && $user->isSchoolAdmin()) {
            return $user->school_id;
        }
        return null;
    }

    /**
     * Stream / view a material (primarily PDF) in a new browser tab.
     * Falls back to download if file type not directly displayable.
     */
    public function viewMaterial(Material $material)
    {
        // Security: ensure relationship to a course (already implicit) - additional checks could be added.
        if (!$material->file_path || !\Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'Material file not found');
        }
        $path = $material->file_path;
        $mime = \Storage::disk('public')->mimeType($path);
        $stream = \Storage::disk('public')->readStream($path);
        $disposition = in_array($mime, ['application/pdf','image/png','image/jpeg','image/gif','text/plain']) ? 'inline' : 'attachment';
        return response()->stream(function() use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition.'; filename="'.basename($path).'"'
        ]);
    }

    /**
     * Provide assessment full details including questions & options for modal display (AJAX JSON).
     */
    public function assessmentDetails(Assessment $assessment)
    {
        $assessment->load(['questions.options']);
        $payload = [
            'id' => $assessment->id,
            'title' => $assessment->title,
            'type' => $assessment->type,
            'description' => $assessment->description,
            'duration_minutes' => $assessment->duration_minutes,
            'available_at' => $assessment->available_at?->toDateTimeString(),
            'unavailable_at' => $assessment->unavailable_at?->toDateTimeString(),
            'has_file' => (bool)$assessment->assessment_file_path,
            'questions' => $assessment->questions->map(function($q){
                return [
                    'id' => $q->id,
                    'text' => $q->question_text,
                    'type' => $q->question_type,
                    'points' => $q->points,
                    'order' => $q->order,
                    'correct_answer' => $q->correct_answer,
                    'options' => $q->options->map(function($o){
                        return [
                            'id' => $o->id,
                            'text' => $o->option_text,
                            'order' => $o->option_order,
                            // expose correctness flag if schema supports it
                            'is_correct' => (bool)($o->is_correct ?? false),
                        ];
                    })->values(),
                ];
            })->values(),
        ];
        return response()->json(['success' => true, 'assessment' => $payload]);
    }

    /**
     * Stream / view attached assessment file if present.
     */
    public function viewAssessmentFile(Assessment $assessment)
    {
        if (!$assessment->assessment_file_path || !\Storage::disk('public')->exists($assessment->assessment_file_path)) {
            abort(404, 'Assessment file not found');
        }
        $path = $assessment->assessment_file_path;
        $mime = \Storage::disk('public')->mimeType($path);
        $stream = \Storage::disk('public')->readStream($path);
        $disposition = in_array($mime, ['application/pdf','image/png','image/jpeg','image/gif','text/plain']) ? 'inline' : 'attachment';
        return response()->stream(function() use ($stream) { fpassthru($stream); }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition.'; filename="'.basename($path).'"'
        ]);
    }

    /**
     * Export courses (all or single) as JSON suitable for re-import.
     * Query param: course_id (optional) to export a single course.
     */
    public function export(Request $request)
    {
        $courseId = $request->query('course_id');
        $q = Course::with(['program','instructor'])->orderBy('id');
        if ($courseId) { $q->where('id', $courseId); }
        $courses = $q->get();
        $payload = $courses->map(function($c){
            return [
                'title' => $c->title,
                'course_code' => $c->course_code,
                'status' => $c->status,
                'program_name' => $c->program?->name,
                'department' => $c->department,
                'description' => $c->description,
                'credits' => $c->credits,
                'instructor_email' => $c->instructor?->email,
            ];
        })->values();
        $single = (bool)$courseId && $payload->count() === 1;
        $json = json_encode([
            'meta' => [
                'exported_at' => now()->toIso8601String(),
                'count' => $payload->count(),
                'single' => $single,
                'version' => 1
            ],
            'courses' => $payload
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($single) {
            $titleSlug = Str::slug($payload->first()['title'] ?: 'course');
            $filename = $titleSlug . '-export-' . now()->format('Ymd-His') . '.json';
        } else {
            $filename = 'courses-export-' . now()->format('Ymd-His') . '.json';
        }
        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ]);
    }

    /**
     * Export all courses (ignores filters) to an Excel (.xlsx) file with columns matching the management table.
     */
    public function exportExcel()
    {
        $courses = Course::with(['instructor','program'])->withCount('students')->orderBy('created_at','desc')->get();

        // Build minimal SpreadsheetML structure for a single-sheet XLSX
        $headers = [
            'ID','Title','Course Code','Instructor','Department','Program','Students','Status','Updated At','Credits','Description'
        ];

        $rowsXml = '';
        $cell = function($v){
            $val = htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            return '<c t="inlineStr"><is><t>'.$val.'</t></is></c>';
        };
        $rowsXml .= '<row>'.implode('', array_map($cell,$headers)).'</row>';
        foreach($courses as $c){
            $rowsXml .= '<row>'
                .$cell($c->id)
                .$cell($c->title)
                .$cell($c->course_code)
                .$cell($c->instructor?->name ?: 'N/A')
                .$cell($c->department)
                .$cell($c->program?->name)
                .$cell($c->students_count)
                .$cell($c->status)
                .$cell(optional($c->updated_at)->format('Y-m-d H:i'))
                .$cell($c->credits)
                .$cell($c->description)
                .'</row>';
        }

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetData>'.$rowsXml.'</sheetData>'
            .'</worksheet>';
        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Courses" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
        $relsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'</Relationships>';
        $contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'</Types>';
        $rootRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $contentTypesXml);
        $zip->addEmptyDir('_rels');
        $zip->addFromString('_rels/.rels', $rootRelsXml);
        $zip->addEmptyDir('xl');
        $zip->addEmptyDir('xl/_rels');
        $zip->addEmptyDir('xl/worksheets');
        $zip->addFromString('xl/workbook.xml', $workbookXml);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $relsXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $filename = 'courses-export-'.now()->format('Ymd-His').'.xlsx';
        return response()->download($tmp, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ])->deleteFileAfterSend(true);
    }

    /**
     * Import courses from previously exported JSON.
     * Strategy: match/update by course_code; create if missing; associate program by name; link instructor by email if exists.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file'
        ]);
        $contents = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($contents, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid JSON: '.json_last_error_msg()
            ], 422);
        }
        if (isset($data[0]) && is_array($data[0]) && !isset($data['courses'])) {
            $data = [ 'meta' => [ 'inferred' => true, 'single' => count($data) === 1, 'version' => 1 ], 'courses' => $data ];
        }
        if (isset($data['course']) && !isset($data['courses'])) {
            $data['courses'] = [ $data['course'] ];
            $data['meta']['single'] = true;
        }
        if (!$data || !isset($data['courses']) || !is_array($data['courses'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid file structure. Expecting object with a "courses" array.',
                'received_keys' => array_keys(is_array($data)? $data : [])
            ], 422);
        }
        $isSingle = false;
        if (isset($data['meta']['single'])) {
            $isSingle = (bool)$data['meta']['single'];
        } elseif (count($data['courses']) === 1) {
            $isSingle = true;
        }
        $created = 0; $updated = 0; $skipped = 0; $errors = [];
        foreach ($data['courses'] as $idx => $c) {
            if (empty($c['title']) || empty($c['course_code'])) { $skipped++; continue; }
            try {
                $existing = Course::where('course_code', $c['course_code'])->first();
                if ($isSingle) {
                    if ($existing) { $skipped++; continue; }
                } else {
                    if ($existing) { $skipped++; continue; }
                }
                $programId = null;
                if (!empty($c['program_name'])) {
                    $program = Program::firstOrCreate(['name' => $c['program_name']]);
                    $programId = $program->id;
                }
                $instructorId = null;
                if (!empty($c['instructor_email'])) {
                    $user = User::where('email', $c['instructor_email'])->where('role', 'instructor')->first();
                    if ($user) { $instructorId = $user->id; }
                }
                $payload = [
                    'title' => $c['title'],
                    'course_code' => $c['course_code'],
                    'status' => $c['status'] ?? 'draft',
                    'program_id' => $programId,
                    'department' => $c['department'] ?? null,
                    'description' => $c['description'] ?? null,
                    'credits' => $c['credits'] ?? null,
                ];
                if ($instructorId) { $payload['instructor_id'] = $instructorId; }
                Course::create($payload);
                $created++;
            } catch (\Throwable $e) {
                $errors[] = 'Row '.($idx+1).': '.$e->getMessage();
            }
        }
        return response()->json([
            'success' => true,
            'mode' => $isSingle ? 'single' : 'bulk',
            'summary' => [
                'created' => $created,
                'updated' => $updated,
                'skipped_existing' => $skipped,
                'errors' => $errors
            ]
        ]);
    }
}
