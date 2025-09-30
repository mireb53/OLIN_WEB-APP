<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminReportsController extends Controller
{
    /**
     * Display reports and logs dashboard.
     */
    public function index(Request $request)
    {
        // allow selecting by instructor id and course id from modal
        $instructorId = $request->input('instructor_id');
        $courseFilterId = $request->input('course_id');
        // date range based on filter
        $range = $request->input('range', '7d');
        $end = Carbon::now();

        // Filters from request
        $instructorName = $request->input('instructor');
        $instructorEmail = $request->input('instructor_email');
        $department = $request->input('department');
        $programFilter = $request->input('program_id');
        $courseName = $request->input('course_name');

        // If instructor_email provided (from modal), resolve to instructor id
        if ($instructorEmail && empty($instructorId)) {
            $instr = DB::table('users')->where('role', 'instructor')->where('email', $instructorEmail)->first();
            if ($instr) $instructorId = $instr->id;
        }

        // determine if any filters are applied (used in multiple places)
        $filtersApplied = (bool)($instructorId || $instructorName || $department || $programFilter || $courseName || $courseFilterId || ($range && $range !== '7d'));

        switch ($range) {
            case 'today':
                $start = $end->copy()->startOfDay();
                break;
            case 'yesterday':
                $start = $end->copy()->subDay()->startOfDay();
                $end = $start->copy()->endOfDay();
                break;
            case '30d':
                $start = $end->copy()->subDays(30);
                break;
            case '365d':
                $start = $end->copy()->subDays(365);
                break;
            case '7d':
            default:
                $start = $end->copy()->subDays(6)->startOfDay();
                break;
        }

        // Users registered in range grouped by day
        // Build course filter set based on course/program/instructor filters
        $courseQuery = DB::table('courses');
        if ($programFilter) {
            $courseQuery->where('program_id', $programFilter);
        }
        if ($courseName) {
            $courseQuery->where('title', 'like', "%{$courseName}%");
        }
        if ($instructorId) {
            // instructor selected by id (modal)
            $instIds = [$instructorId];
            $courseQuery->whereIn('instructor_id', $instIds);
        } elseif ($instructorName) {
            // find instructor ids matching name (search)
            $instIds = DB::table('users')
                ->where('role', 'instructor')
                ->where('name', 'like', "%{$instructorName}%")
                ->when($department, function ($q) use ($department) {
                    return $q->where('department', $department);
                })
                ->pluck('id')
                ->toArray();
            if (count($instIds) > 0) {
                $courseQuery->whereIn('instructor_id', $instIds);
            } else {
                // no matching instructor -> ensure no courses
                $courseQuery->whereRaw('0 = 1');
            }
        }
        // if specific course was chosen from modal, restrict to it
        if ($courseFilterId) {
            $courseQuery->where('id', $courseFilterId);
        }
        $filteredCourseIds = $courseQuery->pluck('id')->toArray();

        // Determine student subset based on filtered courses or department/program
        $studentIds = [];
        if (count($filteredCourseIds) > 0) {
            $studentIds = DB::table('enrollments')
                ->whereIn('course_id', $filteredCourseIds)
                ->pluck('student_id')
                ->unique()
                ->toArray();
        }

        // Base user query for registrations accounting for filters
        $usersBase = DB::table('users');
        if (count($studentIds) > 0) {
            $usersBase->whereIn('id', $studentIds);
        } else {
            if ($department) {
                $usersBase->where('department', $department);
            }
            if ($programFilter) {
                $usersBase->where('program_id', $programFilter);
            }
        }

        $registrations = (clone $usersBase)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Account-active users based on status column (accounts marked active)
        $accountActiveUsersQuery = DB::table('users')->where('status', 'active');
        if (count($studentIds) > 0) {
            $accountActiveUsersQuery->whereIn('id', $studentIds);
        } else {
            if ($department) {
                $accountActiveUsersQuery->where('department', $department);
            }
            if ($programFilter) {
                $accountActiveUsersQuery->where('program_id', $programFilter);
            }
        }
        $accountActiveUsers = $accountActiveUsersQuery
            ->select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->get();

        // Currently online users: sessions whose last_activity is within the last N minutes
        // Sessions may not have user_id populated; try to extract user id from payload when missing.
        $onlineThresholdMinutes = 15; // consider users active if they've had activity in the last 15 minutes
        $thresholdTimestamp = Carbon::now()->subMinutes($onlineThresholdMinutes)->timestamp;

        // fetch recent session rows within threshold
        $sessionRows = DB::table('sessions')
            ->where('last_activity', '>=', $thresholdTimestamp)
            ->orderBy('last_activity', 'desc')
            ->get();

        $sessionUserIds = [];
        foreach ($sessionRows as $sr) {
            if (!empty($sr->user_id)) {
                $sessionUserIds[] = (int)$sr->user_id;
                continue;
            }
            // attempt to extract user id from payload using common serialized patterns
            $payload = $sr->payload ?? '';
            $found = null;
            // pattern: user_id";i:123;  (serialized)
            if (preg_match('/user_id";i:(\d+);/i', $payload, $m)) {
                $found = (int)$m[1];
            }
            // fallback: look for \"id\":123 or "id";i:123
            if (!$found && preg_match('/"id";i:(\d+);/i', $payload, $m2)) {
                $found = (int)$m2[1];
            }
            if (!$found) {
                if (preg_match('/"id"\s*:\s*(\d+)/', $payload, $m3)) {
                    $found = (int)$m3[1];
                }
            }
            if ($found) $sessionUserIds[] = $found;
        }

        $sessionUserIds = array_values(array_unique(array_filter($sessionUserIds)));

        // Now build onlineUsers grouped by role using resolved user ids
        // Determine allowed IDs when filters applied (students + instructor)
        $allowedOnlineIds = [];
        if (count($studentIds) > 0) $allowedOnlineIds = array_merge($allowedOnlineIds, $studentIds);
        if ($instructorId) $allowedOnlineIds[] = (int)$instructorId;
        $allowedOnlineIds = array_values(array_unique(array_filter($allowedOnlineIds)));

        // Compute effective online ids as intersection of session-derived ids and allowed ids when filter is applied.
        $effectiveOnlineIds = $sessionUserIds;
        if (count($allowedOnlineIds) > 0) {
            $effectiveOnlineIds = array_values(array_intersect($sessionUserIds, $allowedOnlineIds));
        }

        if (count($effectiveOnlineIds) > 0) {
            // Query users that are both in sessions and allowed by filters
            $onlineUsers = DB::table('users')
                ->whereIn('id', $effectiveOnlineIds)
                ->select('role', DB::raw('count(*) as total'))
                ->groupBy('role')
                ->get();
        } else {
            // No effective ids; if no explicit allowed list provided, fall back to department/program filtering on all session users
            if (count($allowedOnlineIds) === 0) {
                $onlineUsersQuery = DB::table('users');
                if (count($sessionUserIds) > 0) $onlineUsersQuery->whereIn('id', $sessionUserIds);
                if ($department) $onlineUsersQuery->where('department', $department);
                if ($programFilter) $onlineUsersQuery->where('program_id', $programFilter);
                $onlineUsers = $onlineUsersQuery->select('role', DB::raw('count(*) as total'))->groupBy('role')->get();
            } else {
                // We had an allowed list but none of them are currently in session -> return empty collection
                $onlineUsers = collect();
            }
        }

        // Recent authentication sessions (from sessions table)
        // Build recentSessions with resolved user id and name where possible
        $recentSessionRows = DB::table('sessions')
            ->select('id','user_id','payload','ip_address','last_activity')
            ->orderBy('last_activity', 'desc')
            ->limit(200)
            ->get();
        $recentSessions = [];
        $recentUserIds = [];

        // build allowed user ids when filters applied (students + instructor)
        $allowedUserIds = [];
        if (count($studentIds) > 0) $allowedUserIds = array_merge($allowedUserIds, $studentIds);
        if ($instructorId) $allowedUserIds[] = (int)$instructorId;
        $allowedUserIds = array_values(array_unique(array_filter($allowedUserIds)));

        foreach ($recentSessionRows as $rsr) {
            $uid = $rsr->user_id;
            if (empty($uid)) {
                $payload = $rsr->payload ?? '';
                $found = null;
                if (preg_match('/user_id";i:(\d+);/i', $payload, $m)) {
                    $found = (int)$m[1];
                }
                if (!$found && preg_match('/"id";i:(\d+);/i', $payload, $m2)) {
                    $found = (int)$m2[1];
                }
                if (!$found && preg_match('/"id"\s*:\s*(\d+)/', $payload, $m3)) {
                    $found = (int)$m3[1];
                }
                if ($found) $uid = $found;
            }

            // if filters applied, only include sessions for allowed user ids (students + instructor)
            if ($filtersApplied) {
                if (empty($uid) || !in_array((int)$uid, $allowedUserIds, true)) {
                    continue;
                }
            }

            $recentSessions[] = (object)[
                'user_id' => $uid,
                'ip_address' => $rsr->ip_address,
                'last_activity' => $rsr->last_activity,
            ];
            if ($uid) $recentUserIds[] = $uid;
        }
        $recentUserIds = array_values(array_unique($recentUserIds));
        $recentUsers = [];
        if (count($recentUserIds) > 0) {
            $users = DB::table('users')->whereIn('id', $recentUserIds)->select('id','name')->get();
            foreach ($users as $u) $recentUsers[$u->id] = $u->name;
        }
        // attach name where available
        foreach ($recentSessions as $k => $rs) {
            $recentSessions[$k]->name = $recentUsers[$rs->user_id] ?? null;
        }

        // --- DEFAULT, unfiltered datasets (should NOT change when filters applied) ---
        // Account status by role (global, unfiltered)
        $accountActiveUsersDefault = DB::table('users')
            ->select('role', DB::raw('count(*) as total'))
            ->where('status', 'active')
            ->groupBy('role')
            ->get();

        // Online users default (last 15 minutes, unfiltered) - resolve ids from payloads as above
        $sessionRowsDef = DB::table('sessions')
            ->where('last_activity', '>=', Carbon::now()->subMinutes(15)->timestamp)
            ->get();
        $sessionUserIdsDef = [];
        foreach ($sessionRowsDef as $sr) {
            if (!empty($sr->user_id)) { $sessionUserIdsDef[] = (int)$sr->user_id; continue; }
            $payload = $sr->payload ?? '';
            if (preg_match('/user_id";i:(\d+);/i', $payload, $m)) $sessionUserIdsDef[] = (int)$m[1];
            elseif (preg_match('/"id";i:(\d+);/i', $payload, $m2)) $sessionUserIdsDef[] = (int)$m2[1];
            elseif (preg_match('/"id"\s*:\s*(\d+)/', $payload, $m3)) $sessionUserIdsDef[] = (int)$m3[1];
        }
        $sessionUserIdsDef = array_values(array_unique(array_filter($sessionUserIdsDef)));
        $onlineUsersDefault = collect();
        if (count($sessionUserIdsDef) > 0) {
            $onlineUsersDefault = DB::table('users')
                ->whereIn('id', $sessionUserIdsDef)
                ->select('role', DB::raw('count(*) as total'))
                ->groupBy('role')
                ->get();
        }

        // Recent sessions default (global, unfiltered) - build with resolved names
        $recentSessionRowsDef = DB::table('sessions')
            ->select('id','user_id','payload','ip_address','last_activity')
            ->orderBy('last_activity', 'desc')
            ->limit(50)
            ->get();
        $recentSessionsDefault = [];
        $recentUserIdsDef = [];
        foreach ($recentSessionRowsDef as $rsd) {
            $uid = $rsd->user_id;
            if (empty($uid)) {
                $payload = $rsd->payload ?? '';
                $found = null;
                if (preg_match('/user_id";i:(\d+);/i', $payload, $m)) $found = (int)$m[1];
                if (!$found && preg_match('/"id";i:(\d+);/i', $payload, $m2)) $found = (int)$m2[1];
                if (!$found && preg_match('/"id"\s*:\s*(\d+)/', $payload, $m3)) $found = (int)$m3[1];
                $uid = $found;
            }
            $recentSessionsDefault[] = (object)[
                'user_id' => $uid,
                'ip_address' => $rsd->ip_address,
                'last_activity' => $rsd->last_activity,
            ];
            if ($uid) $recentUserIdsDef[] = $uid;
        }
        $recentUserIdsDef = array_values(array_unique($recentUserIdsDef));
        $recentUsersDef = [];
        if (count($recentUserIdsDef) > 0) {
            $usersDef = DB::table('users')->whereIn('id', $recentUserIdsDef)->select('id','name')->get();
            foreach ($usersDef as $u) $recentUsersDef[$u->id] = $u->name;
        }
        foreach ($recentSessionsDefault as $k => $rs) {
            $recentSessionsDefault[$k]->name = $recentUsersDef[$rs->user_id] ?? null;
        }

        // Programs and departments for filter dropdowns
        $programs = DB::table('programs')->select('id', 'name')->orderBy('name')->get();
        $departments = DB::table('users')->whereNotNull('department')->distinct()->pluck('department');

    // DEFAULT CHART (unfiltered) - last 30 days
        $chartEnd = Carbon::now();
        $chartStart = $chartEnd->copy()->subDays(29)->startOfDay();
        $chartLabels = [];
        $chartRegistrations = [];
        $chartActiveAccounts = [];
        $chartOnline = [];
        $chartCoursesCreated = [];

        $cur = $chartStart->copy();
        while ($cur->lte($chartEnd)) {
            $chartLabels[] = $cur->toDateString();

            // registrations on that day
            $chartRegistrations[] = (int) DB::table('users')->whereDate('created_at', $cur->toDateString())->count();

            // active accounts created on that day
            $chartActiveAccounts[] = (int) DB::table('users')->where('status', 'active')->whereDate('created_at', $cur->toDateString())->count();

            // online users on that day via sessions
            $chartOnline[] = (int) DB::table('sessions')
                ->whereRaw('DATE(FROM_UNIXTIME(last_activity)) = ?', [$cur->toDateString()])
                ->distinct('user_id')
                ->count('user_id');

            // courses created on that day
            $chartCoursesCreated[] = (int) DB::table('courses')->whereDate('created_at', $cur->toDateString())->count();

            $cur->addDay();
        }

        // --- GLOBAL (unfiltered) counts ---
        $coursesCountGlobal = DB::table('courses')->count();
        $materialsCountGlobal = DB::table('materials')->count();
        $assessmentsCountGlobal = DB::table('assessments')->count();

        $totalEnrollmentsGlobal = DB::table('enrollments')->count();
        $activeEnrollmentsGlobal = DB::table('enrollments')->where('status', 'active')->count();

        $topCoursesGlobal = DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->select('courses.id', 'courses.title', DB::raw('count(enrollments.student_id) as students'))
            ->groupBy('courses.id', 'courses.title')
            ->orderByDesc('students')
            ->limit(3)
            ->get();

        $avgGradeOverallGlobal = DB::table('enrollments')->whereNotNull('grade')->avg('grade');
        $avgGradeByCourseGlobal = DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->whereNotNull('enrollments.grade')
            ->select('courses.id', 'courses.title', DB::raw('avg(enrollments.grade) as avg_grade'))
            ->groupBy('courses.id', 'courses.title')
            ->orderByDesc('avg_grade')
            ->limit(3)
            ->get();

        $totalSubmissionsGlobal = DB::table('submitted_assessments')->count();

        $topicsCountGlobal = DB::table('topics')->count();

        $totalUsersGlobal = DB::table('users')->count();
        $verifiedUsersGlobal = DB::table('users')->whereNotNull('email_verified_at')->count();

        $usersByProgramGlobal = DB::table('users')
            ->select('program_id', DB::raw('count(*) as total'))
            ->groupBy('program_id')
            ->limit(10)
            ->get();

        // --- FILTERED counts (respecting modal filters / request params). These will be used when filtersApplied === true ---
        // Counts for key entities (filtered)
        $coursesCountQuery = DB::table('courses');
        if (count($filteredCourseIds) > 0) {
            $coursesCountQuery->whereIn('id', $filteredCourseIds);
        } else {
            if ($programFilter) {
                $coursesCountQuery->where('program_id', $programFilter);
            }
        }
        $coursesCountFiltered = $coursesCountQuery->count();

        $materialsCountQuery = DB::table('materials');
        if (count($filteredCourseIds) > 0) {
            $materialsCountQuery->whereIn('course_id', $filteredCourseIds);
        } elseif ($programFilter) {
            $materialsCountQuery->join('courses', 'materials.course_id', '=', 'courses.id')->where('courses.program_id', $programFilter);
        }
        $materialsCountFiltered = $materialsCountQuery->count();

        $assessmentsCountQuery = DB::table('assessments');
        if (count($filteredCourseIds) > 0) {
            $assessmentsCountQuery->whereIn('course_id', $filteredCourseIds);
        } elseif ($programFilter) {
            $assessmentsCountQuery->where('program_id', $programFilter);
        }
        $assessmentsCountFiltered = $assessmentsCountQuery->count();

        // Enrollment metrics filtered
        $totalEnrollmentsFiltered = DB::table('enrollments')
            ->when(count($filteredCourseIds) > 0, fn($q) => $q->whereIn('course_id', $filteredCourseIds))
            ->when($programFilter && count($filteredCourseIds)===0, fn($q) => $q->join('courses','enrollments.course_id','=','courses.id')->where('courses.program_id',$programFilter))
            ->count();
        $activeEnrollmentsFiltered = DB::table('enrollments')
            ->when(count($filteredCourseIds) > 0, fn($q) => $q->whereIn('course_id', $filteredCourseIds))
            ->when($programFilter && count($filteredCourseIds)===0, fn($q) => $q->join('courses','enrollments.course_id','=','courses.id')->where('courses.program_id',$programFilter))
            ->where('status','active')
            ->count();

        // Top courses by enrollment (filtered)
        $topCoursesQuery = DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id');
        if (count($filteredCourseIds) > 0) {
            $topCoursesQuery->whereIn('courses.id', $filteredCourseIds);
        } elseif ($programFilter) {
            $topCoursesQuery->where('courses.program_id', $programFilter);
        }
        $topCoursesFiltered = $topCoursesQuery
            ->select('courses.id', 'courses.title', DB::raw('count(enrollments.student_id) as students'))
            ->groupBy('courses.id', 'courses.title')
            ->orderByDesc('students')
            ->limit(3)
            ->get();

        // Average grades overall and top courses by average grade (filtered)
        $avgGradeOverallFiltered = DB::table('enrollments')
            ->when(count($filteredCourseIds) > 0, fn($q) => $q->whereIn('course_id', $filteredCourseIds))
            ->when($programFilter && count($filteredCourseIds)===0, fn($q) => $q->join('courses','enrollments.course_id','=','courses.id')->where('courses.program_id',$programFilter))
            ->whereNotNull('grade')
            ->avg('grade');

        $avgGradeByCourseFiltered = DB::table('enrollments')
            ->join('courses', 'enrollments.course_id', '=', 'courses.id')
            ->when(count($filteredCourseIds) > 0, fn($q) => $q->whereIn('courses.id', $filteredCourseIds))
            ->when($programFilter && count($filteredCourseIds)===0, fn($q) => $q->where('courses.program_id',$programFilter))
            ->whereNotNull('enrollments.grade')
            ->select('courses.id', 'courses.title', DB::raw('avg(enrollments.grade) as avg_grade'))
            ->groupBy('courses.id', 'courses.title')
            ->orderByDesc('avg_grade')
            ->limit(3)
            ->get();

        // Submitted assessments metrics (filtered)
        $totalSubmissionsFiltered = DB::table('submitted_assessments')
            ->when(count($studentIds) > 0, fn($q) => $q->whereIn('student_id', $studentIds))
            ->when(count($filteredCourseIds) > 0 && count($studentIds)===0, fn($q) => $q->join('assessments as a2','submitted_assessments.assessment_id','=','a2.id')->whereIn('a2.course_id',$filteredCourseIds))
            ->count();
        $recentSubmissionsQuery = DB::table('submitted_assessments')
            ->join('users', 'submitted_assessments.student_id', '=', 'users.id')
            ->join('assessments', 'submitted_assessments.assessment_id', '=', 'assessments.id')
            ->select('submitted_assessments.id', 'users.name as student', 'assessments.title as assessment', 'submitted_assessments.score', 'submitted_assessments.submitted_at');
        if (count($studentIds) > 0) {
            $recentSubmissionsQuery->whereIn('submitted_assessments.student_id', $studentIds);
        } elseif (count($filteredCourseIds) > 0) {
            $recentSubmissionsQuery->join('assessments as a2', 'submitted_assessments.assessment_id', '=', 'a2.id')
                ->whereIn('a2.course_id', $filteredCourseIds);
        }
        $recentSubmissionsFiltered = $recentSubmissionsQuery->orderBy('submitted_assessments.submitted_at', 'desc')->limit(10)->get();

        // Materials (filtered)
        $materialsUploadedInRangeFiltered = DB::table('materials')
            ->when(count($filteredCourseIds) > 0, fn($q) => $q->whereIn('course_id', $filteredCourseIds))
            ->when($programFilter && count($filteredCourseIds)===0, fn($q) => $q->join('courses','materials.course_id','=','courses.id')->where('courses.program_id',$programFilter))
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->count();

        $recentMaterialsQuery = DB::table('materials')
            ->join('courses', 'materials.course_id', '=', 'courses.id')
            ->select('materials.id', 'materials.title as material', 'courses.title as course', 'materials.created_at');
        if (count($filteredCourseIds) > 0) {
            $recentMaterialsQuery->whereIn('materials.course_id', $filteredCourseIds);
        } elseif ($programFilter) {
            $recentMaterialsQuery->where('courses.program_id', $programFilter);
        }
        $recentMaterialsFiltered = $recentMaterialsQuery->orderBy('materials.created_at', 'desc')->limit(8)->get();

        // Topics count (filtered)
        $topicsCountFiltered = DB::table('topics')
            ->when(count($filteredCourseIds) > 0, fn($q) => $q->whereIn('course_id', $filteredCourseIds))
            ->when($programFilter && count($filteredCourseIds)===0, fn($q) => $q->join('courses','topics.course_id','=','courses.id')->where('courses.program_id',$programFilter))
            ->count();

        // Email verification counts for filtered users (students + instructor)
        $userIdsForVerification = [];
        if (count($studentIds) > 0) $userIdsForVerification = array_merge($userIdsForVerification, $studentIds);
        if ($instructorId) $userIdsForVerification[] = (int)$instructorId;
        $userIdsForVerification = array_values(array_unique(array_filter($userIdsForVerification)));

        if (count($userIdsForVerification) > 0) {
            $totalUsersFiltered = DB::table('users')->whereIn('id', $userIdsForVerification)->count();
            $verifiedUsersFiltered = DB::table('users')->whereIn('id', $userIdsForVerification)->whereNotNull('email_verified_at')->count();
        } elseif ($programFilter) {
            $totalUsersFiltered = DB::table('users')->where('program_id', $programFilter)->count();
            $verifiedUsersFiltered = DB::table('users')->where('program_id', $programFilter)->whereNotNull('email_verified_at')->count();
        } else {
            $totalUsersFiltered = 0;
            $verifiedUsersFiltered = 0;
        }

        // prepare chart data: global chart already computed above as $chartLabels/$chartRegistrations etc.
        // Prepare filtered chart (when filtersApplied) - here chart will show Submissions (count) and Avg Score per day
        $filteredLabels = [];
        $submissionsSeries = [];
        $avgScoreSeries = [];
        $cur = $start->copy();
        while ($cur->lte($end)) {
            $d = $cur->toDateString();
            $filteredLabels[] = $d;

            // submissions on that day (respecting filtered students/courses)
            $subsQ = DB::table('submitted_assessments')->whereDate('submitted_at', $d);
            if (count($studentIds) > 0) $subsQ->whereIn('student_id', $studentIds);
            elseif (count($filteredCourseIds) > 0) $subsQ->join('assessments as a2','submitted_assessments.assessment_id','=','a2.id')->whereIn('a2.course_id',$filteredCourseIds);
            $subsCount = (int) $subsQ->count();
            $submissionsSeries[] = $subsCount;

            // average score on that day
            $scoreQ = DB::table('submitted_assessments')->whereDate('submitted_at', $d)->whereNotNull('score');
            if (count($studentIds) > 0) $scoreQ->whereIn('student_id', $studentIds);
            elseif (count($filteredCourseIds) > 0) $scoreQ->join('assessments as a3','submitted_assessments.assessment_id','=','a3.id')->whereIn('a3.course_id',$filteredCourseIds);
            $avgScore = $scoreQ->avg('score');
            $avgScoreSeries[] = $avgScore ? round((float)$avgScore,2) : 0;

            $cur->addDay();
        }

        // prepare selected filter display names for header
        $selectedInstructorName = null;
        $selectedProgramName = null;
        $selectedCourseName = null;
        if ($instructorId) {
            $inst = DB::table('users')->where('id', $instructorId)->select('name')->first();
            if ($inst) $selectedInstructorName = $inst->name;
        } elseif ($instructorName) {
            $selectedInstructorName = $instructorName;
        }
        if ($programFilter) {
            $prog = DB::table('programs')->where('id', $programFilter)->select('name')->first();
            if ($prog) $selectedProgramName = $prog->name;
        }
        if ($courseFilterId) {
            $c = DB::table('courses')->where('id', $courseFilterId)->select('title')->first();
            if ($c) $selectedCourseName = $c->title;
        } elseif ($courseName) {
            $selectedCourseName = $courseName;
        }

        return view('admin.reports.reports_logs', [
            // Use global chart data so the graph doesn't change with filters
            'labels' => $chartLabels,
            'data' => $chartRegistrations,
            'registrationsData' => $chartRegistrations,
            'activeAccountsData' => $chartActiveAccounts,
            'onlineData' => $chartOnline,
            'coursesCreatedData' => $chartCoursesCreated,
            // filtered datasets for other sections
            'accountActiveUsers' => $accountActiveUsers,
            'onlineUsers' => $onlineUsers,
            'recentSessions' => $recentSessions,
            // global/unfiltered datasets
            'accountActiveUsersDefault' => $accountActiveUsersDefault,
            'onlineUsersDefault' => $onlineUsersDefault,
            'recentSessionsDefault' => $recentSessionsDefault,
            'programs' => $programs,
            'departments' => $departments,
            // Global (default)
            'coursesCountGlobal' => $coursesCountGlobal,
            'materialsCountGlobal' => $materialsCountGlobal,
            'assessmentsCountGlobal' => $assessmentsCountGlobal,
            'totalEnrollmentsGlobal' => $totalEnrollmentsGlobal,
            'activeEnrollmentsGlobal' => $activeEnrollmentsGlobal,
            'topCoursesGlobal' => $topCoursesGlobal,
            'avgGradeOverallGlobal' => $avgGradeOverallGlobal,
            'avgGradeByCourseGlobal' => $avgGradeByCourseGlobal,
            'totalSubmissionsGlobal' => $totalSubmissionsGlobal,
            'topicsCountGlobal' => $topicsCountGlobal,
            'totalUsersGlobal' => $totalUsersGlobal,
            'verifiedUsersGlobal' => $verifiedUsersGlobal,
            'usersByProgramGlobal' => $usersByProgramGlobal,
            // Filtered (when modal applied)
            'coursesCountFiltered' => $coursesCountFiltered,
            'materialsCountFiltered' => $materialsCountFiltered,
            'assessmentsCountFiltered' => $assessmentsCountFiltered,
            'totalEnrollmentsFiltered' => $totalEnrollmentsFiltered,
            'activeEnrollmentsFiltered' => $activeEnrollmentsFiltered,
            'topCoursesFiltered' => $topCoursesFiltered,
            'avgGradeOverallFiltered' => $avgGradeOverallFiltered,
            'avgGradeByCourseFiltered' => $avgGradeByCourseFiltered,
            'totalSubmissionsFiltered' => $totalSubmissionsFiltered,
            'recentSubmissionsFiltered' => $recentSubmissionsFiltered,
            'materialsUploadedInRangeFiltered' => $materialsUploadedInRangeFiltered,
            'recentMaterialsFiltered' => $recentMaterialsFiltered,
            'topicsCountFiltered' => $topicsCountFiltered,
            'usersByProgram' => $usersByProgramGlobal,
            'totalUsersFiltered' => $totalUsersFiltered ?? 0,
            'verifiedUsersFiltered' => $verifiedUsersFiltered ?? 0,
            'range' => $range,
            'selectedInstructorName' => $selectedInstructorName,
            'selectedProgramName' => $selectedProgramName,
            'selectedCourseName' => $selectedCourseName,
            'filtersApplied' => $filtersApplied,
        ]);
    }

    /**
     * AJAX lookup for instructor by email used in the reports filter modal.
     */
    public function lookupInstructor(Request $request)
    {
        $email = $request->input('email');
        if (!$email) return response()->json(['success' => false, 'message' => 'Email required'], 400);

        $instructor = DB::table('users')->where('role', 'instructor')->where('email', $email)->first();
        if (!$instructor) return response()->json(['success' => false, 'message' => 'Instructor not found']);

        // gather programs and courses taught by this instructor
        $programs = DB::table('programs')
            ->join('courses', 'programs.id', '=', 'courses.program_id')
            ->where('courses.instructor_id', $instructor->id)
            ->select('programs.id','programs.name')
            ->distinct()
            ->get();

        $courses = DB::table('courses')
            ->where('instructor_id', $instructor->id)
            ->select('id','title')
            ->orderBy('title')
            ->get();

        return response()->json(['success' => true, 'instructor' => $instructor, 'programs' => $programs, 'courses' => $courses]);
    }

    /**
     * Return chart data (AJAX) for the registrations/metrics chart based on filters and date selection.
     */
    public function chartData(Request $request)
    {
        $range = $request->input('range', '7d');
        $startDate = $request->input('start');
        $endDate = $request->input('end');

        $instructorId = $request->input('instructor_id');
        $programFilter = $request->input('program_id');
        $courseFilterId = $request->input('course_id');

        $end = $endDate ? Carbon::parse($endDate) : Carbon::now();
        switch ($range) {
            case 'today':
                $start = $end->copy()->startOfDay();
                break;
            case 'yesterday':
                $start = $end->copy()->subDay()->startOfDay();
                $end = $start->copy()->endOfDay();
                break;
            case '30d':
                $start = $end->copy()->subDays(30);
                break;
            case '365d':
                $start = $end->copy()->subDays(365);
                break;
            case '7d':
            default:
                $start = $end->copy()->subDays(6)->startOfDay();
                break;
        }
        if ($startDate) {
            $start = Carbon::parse($startDate);
        }

        // Build course filter set similar to index
        $courseQuery = DB::table('courses');
        if ($programFilter) $courseQuery->where('program_id', $programFilter);
        if ($courseFilterId) $courseQuery->where('id', $courseFilterId);
        if ($instructorId) $courseQuery->where('instructor_id', $instructorId);
        $filteredCourseIds = $courseQuery->pluck('id')->toArray();

        $labels = [];
        $registrations = [];
        $activeAccounts = [];
        $onlineCounts = [];
        $coursesCreated = [];

        $cur = $start->copy();
        while ($cur->lte($end)) {
            $d = $cur->toDateString();
            $labels[] = $d;

            // registrations
            $usersQ = DB::table('users')->whereDate('created_at', $d);
            if (count($filteredCourseIds) > 0) {
                // registrations for students in those courses
                $studentIds = DB::table('enrollments')->whereIn('course_id', $filteredCourseIds)->pluck('student_id')->unique()->toArray();
                if (count($studentIds) > 0) $usersQ->whereIn('id', $studentIds);
            }
            $registrations[] = (int)$usersQ->count();

            // active accounts created that day
            $aaQ = DB::table('users')->where('status','active')->whereDate('created_at',$d);
            if (count($filteredCourseIds) > 0) {
                $studentIds = DB::table('enrollments')->whereIn('course_id', $filteredCourseIds)->pluck('student_id')->unique()->toArray();
                if (count($studentIds) > 0) $aaQ->whereIn('id', $studentIds);
            }
            $activeAccounts[] = (int)$aaQ->count();

            // online - count distinct sessions where last_activity date matches
            $online = DB::table('sessions')
                ->whereRaw('DATE(FROM_UNIXTIME(last_activity)) = ?', [$d]);
            $sessionIds = [];
            $sessionRows = $online->get();
            foreach ($sessionRows as $sr) {
                if (!empty($sr->user_id)) $sessionIds[] = (int)$sr->user_id;
                else {
                    $payload = $sr->payload ?? '';
                    if (preg_match('/user_id";i:(\d+);/i', $payload, $m)) $sessionIds[] = (int)$m[1];
                    elseif (preg_match('/"id";i:(\d+);/i', $payload, $m2)) $sessionIds[] = (int)$m2[1];
                    elseif (preg_match('/"id"\s*:\s*(\d+)/', $payload, $m3)) $sessionIds[] = (int)$m3[1];
                }
            }
            $sessionIds = array_values(array_unique(array_filter($sessionIds)));
            if (count($filteredCourseIds) > 0) {
                $studentIds = DB::table('enrollments')->whereIn('course_id', $filteredCourseIds)->pluck('student_id')->unique()->toArray();
                $sessionIds = array_values(array_intersect($sessionIds, $studentIds));
            }
            $onlineCounts[] = count($sessionIds);

            // courses created
            $coursesCreated[] = (int) DB::table('courses')->whereDate('created_at', $d)->count();

            $cur->addDay();
        }

        return response()->json([
            'labels' => $labels,
            'registrations' => $registrations,
            'activeAccounts' => $activeAccounts,
            'onlineCounts' => $onlineCounts,
            'coursesCreated' => $coursesCreated,
        ]);
    }
}
