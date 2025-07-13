<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminProfileRequest;
use App\Http\Requests\ProfileRequest;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\Attendence;
use App\Models\Employee;
use App\Models\EmployeePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {

    }

    // Employee

    public function getEmployeeProfile()
    {
        try {
            $user = Auth::guard('api')->user();

            $employeeWithUser = Employee::where('user_id', $user->id)->first();

            if (!$employeeWithUser || !$employeeWithUser->user) {
                return response()->json([
                    'message' => 'Profile not found',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }

            $attendance = Attendance::selectRaw("
                SUM(status = 'Hadir') as hadir,
                SUM(status = 'Telat') as telat,
                SUM(status = 'Izin') as izin,
                SUM(status = 'Sakit') as sakit,
                SUM(status = 'Alfa') as alfa,
                SUM(status = 'Cuti') as cuti            ")
            ->where('employee_id', $employeeWithUser->id)
                ->orderBy('created_at', 'desc')
                ->first();

            $image = $employeeWithUser->photo;

            if ($employeeWithUser->photo && !str_starts_with($employeeWithUser->photo, 'http')) {
                $image = asset('storage/' . $employeeWithUser->photo);
            } else {
                $image = $employeeWithUser->photo;
            }

            $izinSummary = EmployeePermission::selectRaw("
                SUM(type = 'izin') as izin,
                SUM(type = 'sakit') as sakit,
                SUM(type = 'cuti') as cuti
            ")
            ->where('employee_id', $employeeWithUser->id)
            ->where('status', 'disetujui') 
            ->first();


            return response()->json([
                'message' => 'Profile found',
                'status_code' => 200,
                'data' => [
                    'id' => $employeeWithUser->id,
                    'user_id' => $employeeWithUser->user_id,
                    'name' => $employeeWithUser->user->name,
                    'email' => $employeeWithUser->user->email,
                    'nip' => $employeeWithUser->nip,
                    'position' => $employeeWithUser->position,
                    'department' => $employeeWithUser->department,
                    'gender' => $employeeWithUser->gender,
                    'phone' => $employeeWithUser->phone,
                    'address' => $employeeWithUser->address,
                    'photo' => $image,
                    'summary' => [
                        'hadir' => (int) ($attendance->hadir ?? 0),
                        'telat' => (int) ($attendance->telat ?? 0),
                        'alfa' => (int) ($attendance->alfa ?? 0),
                    ],
                    'izin_summary' => [
                        'izin' => (int) ($izinSummary->izin ?? 0),
                        'sakit' => (int) ($izinSummary->sakit ?? 0),
                        'cuti' => (int) ($izinSummary->cuti ?? 0),
                    ],
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Internal Server Error: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     *
     * @bodyParam _method string required Example: PUT
     * @bodyParam phone string required Example: 08123456789
     * @bodyParam address string required Example: Jalan Jakarta
     * @bodyParam photo file The profile image
     */
    public function updateEmployeeProfile($id, ProfileRequest $request)
    {
        try {
            $employee = Employee::findOrFail($id);

            // Update basic fields
            $employee->update([
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

            // Handle photo update
            if ($request->hasFile('photo')) {
                if ($employee->photo && Storage::exists($employee->photo)) {
                    Storage::delete($employee->photo);
                }

                // Upload foto baru
                $photoPath = $request->file('photo')->store('photos', 'public');
                $employee->photo = $photoPath; 
                $employee->save();
            }

            return response()->json([
                'message' => 'Profile updated successfully',
                'status_code' => 200,
                'data' => [
                    'id' => $employee->id,
                    'phone' => $employee->phone,
                    'address' => $employee->address,
                    'photo' => asset('storage/' . $employee->photo),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update profile',
                'status_code' => 500,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Admin
    public function getAdminProfile()
    {
        try {
            $user = Auth::guard('api')->user();

            $adminWithUser = Admin::where('user_id', $user->id)->first();

            if (!$adminWithUser || !$adminWithUser->user) {
                return response()->json([
                    'message' => 'Profile not found',
                    'status_code' => 404,
                    'data' => null
                ], 404);
            }

            $image = $adminWithUser->photo;

            if ($adminWithUser->photo && !str_starts_with($adminWithUser->photo, 'http')) {
                $image = asset('storage/' . $adminWithUser->photo);
            } else {
                $image = $adminWithUser->photo;
            }

            return response()->json([
                'message' => 'Profile found',
                'status_code' => 200,
                'data' => [
                    'id' => $adminWithUser->id,
                    'user_id' => $adminWithUser->user_id,
                    'name' => $adminWithUser->user->name,
                    'email' => $adminWithUser->user->email,
                    'position' => $adminWithUser->position,
                    'phone' => $adminWithUser->phone,
                    'photo' => $image,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Internal Server Error: ' . $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

     /**
     *
     * @bodyParam _method string required Example: PUT
     * @bodyParam phone string required Example: 08123456789
     * @bodyParam address string required Example: Jalan Jakarta
     * @bodyParam photo file The profile image
     */
    public function updateAdminProfile($id, AdminProfileRequest $request)
    {
        try {
            $admin = Admin::findOrFail($id);

            // Update basic fields
            $admin->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            $admin->update([
                'phone' => $request->phone,
                'position' => $request->position,            
            ]);

            // Handle photo update
            if ($request->hasFile('photo')) {
                if ($admin->photo && Storage::exists($admin->photo)) {
                    Storage::delete($admin->photo);
                }

                // Upload foto baru
                $photoPath = $request->file('photo')->store('photos', 'public');
                $admin->photo = $photoPath; 
                $admin->save();
            }

            return response()->json([
                'message' => 'Profile updated successfully',
                'status_code' => 200,
                'data' => [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'position' => $admin->position,
                    'phone' => $admin->phone,
                    'photo' => asset('storage/' . $admin->photo),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update profile',
                'status_code' => 500,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
