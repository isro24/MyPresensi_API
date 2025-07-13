<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceClockInRequest;
use App\Http\Requests\AttendanceClockOutRequest;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Services\LocationValidator as ServicesLocationValidator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    public function index()
    {

    }

    public function getAttendance()
    {
        try {
            $user = Auth::guard('api')->user();

            if ($user->role === 'admin') {
                $attendances = Attendance::with(['employee.user', 'schedule', 'location'])->latest()->get();
            } else {
                $employee = $user->employee;
                $attendances = Attendance::with(['employee.user', 'schedule', 'location'])
                    ->where('employee_id', $employee->id)
                    ->latest()
                    ->get();
            }

            return response()->json([
                'status_code' => 200,
                'message' => 'Attendance data fetched successfully',
                'data' => $attendances
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Internal Server Error: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }


    public function clockIn(AttendanceClockInRequest $request)
    {
        try {

            $user = Auth::guard('api')->user();
            $employee = $user->employee;

            $existing = Attendance::where('employee_id', $employee->id)
                ->whereDate('clock_in', now()->toDateString())
                ->exists();

            if ($existing) {
                return response()->json([
                    'status_code' => 409,
                    'message' => 'You have already clocked in today.',
                ], 409);
            }

            $validator = new ServicesLocationValidator();
            $location = $validator->validate($request->latitude_clock_in, $request->longitude_clock_in);      
            if (!$location) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'You are outside the allowed location radius.',
                    'data' => null,
                ], 404);
            }

            $schedule = Schedule::first();
            if (!$schedule) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Schedule not found',
                    'data' => null,
                ], 404);
            }

            $startTime = Carbon::createFromFormat('H:i:s', $schedule->start_time);
            $now = Carbon::now();

            $tolerance = 10; 
            $startTimeWithTolerance = $startTime->copy()->addMinutes($tolerance);
            $status = $now->lte($startTimeWithTolerance) ? 'Hadir' : 'Telat';

            $photo = null;
            if ($request->hasFile('photo_clock_in')) {
                $photoPath = $request->file('photo_clock_in')->store('attendance_photos', 'local');
                $photo = basename($photoPath);
            }

            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'schedule_id' => $schedule->id,
                'location_id' => $location->id,
                'clock_in' => now(),
                'latitude_clock_in' => $request->latitude_clock_in,
                'longitude_clock_in' => $request->longitude_clock_in,
                'photo_clock_in' => $photo,
                'status' => $status,
                'note' => $request->note,
            ]);

            return response()->json([
                'status_code' => 201,
                'message' => 'Clock-in successful',
                'data' => $attendance,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Internal Server Error: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function clockOut(AttendanceClockOutRequest $request)
    {
        try {
            $user = Auth::guard('api')->user();
            $employee = $user->employee;

            $attendance = Attendance::where('employee_id', $employee->id)
                ->whereNull('clock_out')
                ->latest()
                ->first();

            if (!$attendance) {
                return response()->json([
                    'status_code' => 404,
                    'message' => 'Clock-in record not found',
                    'data' => null,
                ], 404);
            }

            $validator = new ServicesLocationValidator();
            $location = $validator->validate($request->latitude_clock_out, $request->longitude_clock_out);

            if (!$location) {
                return response()->json([
                    'status_code' => 403,
                    'message' => 'You are outside the allowed location radius.',
                    'data' => null,
                ], 403);
            }

            $photo = null;
            if ($request->hasFile('photo_clock_out')) {
                $photoPath = $request->file('photo_clock_out')->store('attendance_photos', 'local');
                $photo = basename($photoPath);
            }

            $attendance->update([
                'clock_out' => now(),
                'latitude_clock_out' => $request->latitude_clock_out,
                'longitude_clock_out' => $request->longitude_clock_out,
                'photo_clock_out' => $photo,
            ]);

            return response()->json([
                'status_code' => 200,
                'message' => 'Clock-out successful',
                'data' => $attendance,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Internal Server Error: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getPrivatePhoto($filename)
    {
        $user = Auth::guard('api')->user(); 
        $path = storage_path('app/private/attendance_photos/' . $filename);

        if (!file_exists($path)) {
            abort(404, 'Photo not found');
        }

        $attendance = Attendance::where('photo_clock_in', $filename)
            ->orWhere('photo_clock_out', $filename)
            ->first();

        if (!$attendance) {
            abort(404, 'Photo not linked to any attendance record');
        }

    if ($user->role === 'employee' && $attendance->employee_id !== optional($user->employee)->id) {
        abort(403, 'Unauthorized access to photo');
    }


        return response()->streamDownload(function () use ($path) {
            echo file_get_contents($path);
        }, $filename, [
            'Content-Type' => mime_content_type($path),
        ]);
    }

    public function getAttendanceByEmployee()
    {
        try {
            $user = Auth::guard('api')->user();
            $employee = $user->employee;

            $attendances = Attendance::with(['employee.user', 'schedule', 'location'])
                ->where('employee_id', $employee->id)
                ->latest()
                ->get()
                ->map(function ($attendance) {
                    return [
                        'id' => $attendance->id,
                        'nip' => $attendance->employee->nip,
                        'name' => $attendance->employee->user->name,
                        'schedule_id' => $attendance->schedule_id,
                        'location' => $attendance->location->name,
                        'clock_in' => $attendance->clock_in,
                        'latitude_clock_in' => $attendance->latitude_clock_in,
                        'longitude_clock_in' => $attendance->longitude_clock_in,
                        'clock_out' => $attendance->clock_out,
                        'latitude_clock_out' => $attendance->latitude_clock_out,
                        'longitude_clock_out' => $attendance->longitude_clock_out,
                        'photo_clock_in' => $attendance->photo_clock_in,
                        'photo_clock_out' => $attendance->photo_clock_out,
                        'status' => $attendance->status,
                        'note' => $attendance->note,
                    ];
                });

            return response()->json([
                'status_code' => 200,
                'message' => 'Employee attendance fetched successfully',
                'data' => $attendances
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Internal Server Error: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function getAttendanceByAdmin()
    {
        try {
            $attendances = Attendance::with(['employee.user', 'schedule', 'location'])
                ->latest()
                ->get()
                ->map(function ($attendance) {
                    return [
                        'id' => $attendance->id,
                        'nip' => $attendance->employee->nip,
                        'name' => $attendance->employee->user->name,
                        'schedule_id' => $attendance->schedule_id,
                        'location' => $attendance->location->name,
                        'clock_in' => $attendance->clock_in,
                        'latitude_clock_in' => $attendance->latitude_clock_in,
                        'longitude_clock_in' => $attendance->longitude_clock_in,
                        'clock_out' => $attendance->clock_out,
                        'latitude_clock_out' => $attendance->latitude_clock_out,
                        'longitude_clock_out' => $attendance->longitude_clock_out,
                        'photo_clock_in' => $attendance->photo_clock_in,
                        'photo_clock_out' => $attendance->photo_clock_out,
                        'status' => $attendance->status,
                        'note' => $attendance->note,
                    ];
                });

            return response()->json([
                'status_code' => 200,
                'message' => 'All attendance data fetched successfully',
                'data' => $attendances
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Internal Server Error: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
