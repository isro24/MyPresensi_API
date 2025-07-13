<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeePermission;
use App\Models\Location;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {

    }

    public function getEmployeeDashboard()
    {
        try {
            $user = Auth::guard('api')->user();

            $userWithEmployee = User::with('employee')->find($user->id);

            if (!$userWithEmployee || !$userWithEmployee->employee) {
                return response()->json([
                    'message' => 'Data not found',
                    'status_code' => 404,
                    'data' => null,
                ], 404);
            }

            $photo = $userWithEmployee->employee->photo;
            $image = $photo 
                ? (str_starts_with($photo, 'http') ? $photo : asset('storage/' . $photo)) 
                : null;

            $schedule = Schedule::first();

            $scheduleData = $schedule ? [
                'id' => $schedule->id,
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
            ] : null;

            $attendance = Attendance::where('employee_id', $userWithEmployee->employee->id)
                ->latest()
                ->first();

            $attendanceData = $attendance ? [
                'clock_in' => $attendance->clock_in,
                'clock_out' => $attendance->clock_out,
            ] : null;

            return response()->json([
                'message' => 'Data found',
                'status_code' => 200,
                'data' => [
                    'id' => $userWithEmployee->id,
                    'name' => $userWithEmployee->name,
                    'role' => $userWithEmployee->role,
                    'photo' => $image,
                    'position' => $userWithEmployee->employee->position,
                    'schedule' => $scheduleData,
                    'attendance' => $attendanceData,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Internal Server Error: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

   public function getAdminDashboard()
    {
        $user = Auth::guard('api')->user();
        $userWithAdmin = User::with('admin')->find($user->id);

        if (!$userWithAdmin || !$userWithAdmin->admin) {
            return response()->json([
                'message' => 'Data not found',
                'status_code' => 404,
                'data' => null,
            ], 404);
        }

        $admin = $userWithAdmin->admin;

        $photo = $admin->photo 
            ? (str_starts_with($admin->photo, 'http') ? $admin->photo : asset('storage/' . $admin->photo)) 
            : null;

        $schedules = Schedule::where('admin_id', $admin->id)
            ->select('id', 'start_time', 'end_time')
            ->get();

        $locations = Location::where('admin_id', $admin->id)
            ->select('id', 'name', 'latitude', 'longitude', 'radius')
            ->get();

        $today = now()->toDateString();
        $attendancesToday = Attendance::with(['employee', 'location', 'schedule'])
            ->whereDate('clock_in', $today)
            ->get();

        $totalEmployees = Employee::count();
        $presentCount = $attendancesToday->count();
        $absentCount = $totalEmployees - $presentCount;

        $lateAttendances = $attendancesToday->filter(function ($att) {
            return optional($att->schedule)->start_time && $att->clock_in > $att->schedule->start_time;
        });

        $lateCount = $lateAttendances->count();

        $lateEmployees = $lateAttendances->map(function ($att) {
            return [
                'name' => $att->employee->name ?? '-',
                'clock_in' => $att->clock_in,
                'photo' => $att->employee->photo 
                    ? asset('storage/' . $att->employee->photo) 
                    : null,
            ];
        })->values();

        $absentEmployeeIds = $attendancesToday->pluck('employee_id')->toArray();

        $absentEmployees = Employee::with('user') 
            ->whereNotIn('id', $absentEmployeeIds)
            ->get()
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->user->name ?? '-',
                    'position' => $emp->position,
                    'photo' => $emp->photo 
                        ? asset('storage/' . $emp->photo) 
                        : null,
                ];
            })->values();

        $recentAttendances = $attendancesToday->sortByDesc('clock_in')->take(5)->map(function ($att) {
            return [
                'name' => $att->employee->name ?? '-',
                'clock_in' => $att->clock_in,
                'clock_out' => $att->clock_out,
                'photo' => $att->employee->photo 
                    ? asset('storage/' . $att->employee->photo) 
                    : null,
            ];
        })->values();

        $attendancesTodayData = $attendancesToday->map(function ($att) {
            return [
                'clock_in' => $att->clock_in,
                'clock_out' => $att->clock_out,
                'status' => $att->status,
                'note' => $att->note,
                'employee' => [
                    'name' => $att->employee->name ?? '-',
                    'position' => $att->employee->position ?? '-',
                    'photo' => $att->employee->photo 
                        ? asset('storage/' . $att->employee->photo) 
                        : null,
                ],
            ];
        });

        $weeklyAttendance = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $count = Attendance::whereDate('clock_in', $date)->count();
            $weeklyAttendance[] = [
                'date' => $date,
                'count' => $count,
            ];
        }

        $activeLocations = $attendancesToday
            ->pluck('location')
            ->unique('id')
            ->map(function ($loc) {
                return [
                    'id' => $loc->id,
                    'name' => $loc->name,
                ];
            })->values();

        $today = Carbon::today();

        $pendingPermissions = EmployeePermission::with('employee.user')
            ->where('status', 'menunggu')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get()
            ->map(function ($perm) {
            return [
                'name' => $perm->employee->user->name ?? '-',
                'position' => $perm->employee->position ?? '-',
                'type' => $perm->type,
                'start_date' => Carbon::parse($perm->start_date)->toDateString(),
                'end_date' => Carbon::parse($perm->end_date)->toDateString(),
                'photo' => $perm->employee->photo 
                    ? asset('storage/' . $perm->employee->photo) 
                    : null,
            ];
        })->values();

        return response()->json([
            'message' => 'Success',
            'status_code' => 200,
            'data' => [
                'admin' => [
                    'id' => $admin->id,
                    'name' => $userWithAdmin->name,
                    'photo' => $photo,
                ],
                'schedules' => $schedules,
                'locations' => $locations,
                'attendance_today_count' => $presentCount,
                'absent_count' => $absentCount,
                'late_count' => $lateCount,
                'attendances_today' => $attendancesTodayData,
                'recent_attendances' => $recentAttendances,
                'late_employees' => $lateEmployees,
                'absent_employees' => $absentEmployees,
                'weekly_attendance' => $weeklyAttendance,
                'active_locations' => $activeLocations,
                'pending_permissions' => $pendingPermissions,
            ],
        ]);
    }

}
