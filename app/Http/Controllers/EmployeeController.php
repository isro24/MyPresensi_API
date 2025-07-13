<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeeRequest;
use App\Http\Requests\UserRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index()
    {
        try {
            $users = User::with('employee')
                ->where('role_id', 2)
                ->get()
                ->map(function ($user) {
                    if ($user->employee && $user->employee->photo) {
                        $user->employee->photo_url = asset('storage/' . $user->employee->photo);
                    }
                    return $user;
                });

            return response()->json([
                'message' => 'User data retrieved successfully',
                'status_code' => 200,
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve user data',
                'status_code' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(EmployeeRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role_id' => 2 
            ]);

            $photoUrl = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photos', 'public');
                $photoUrl = asset(Storage::url($photoPath)); 
            }

            $employee = Employee::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
                'position' => $request->position,
                'department' => $request->department,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'address' => $request->address,
                'photo' => $photoPath,
            ]);

            return response()->json([
                'message' => 'User and employee created successfully',
                'status_code' => 201,
                'data' => [
                    'user' => $user,
                    'employee' => $employee,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'failed to create user',
                'status_code' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(EmployeeRequest $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $employee = $user->employee;

            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
            ]);

            $photoPath = $employee->photo; 

            if ($request->hasFile('photo')) {
                if ($employee->photo && Storage::exists('public/' . $employee->photo)) {
                    Storage::delete('public/' . $employee->photo);
                }

                $photoPath = $request->file('photo')->store('photos', 'public');
            }

            $employee->update([
                'nip' => $request->nip,
                'position' => $request->position,
                'department' => $request->department,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'address' => $request->address,
                'photo' => $photoPath,
            ]);

            return response()->json([
                'message' => 'User and employee updated successfully',
                'status_code' => 200,
                'data' => [
                    'user' => $user,
                    'employee' => $employee,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update user',
                'status_code' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $employee = $user->employee;

            if ($employee && $employee->photo && Storage::exists(str_replace('/storage/', 'public/', $employee->photo))) {
                Storage::delete(str_replace('/storage/', 'public/', $employee->photo));
            }

            if ($employee) {
                $employee->delete();
            }

            $user->delete();

            return response()->json([
                'message' => 'Delete user dan employee successful',
                'status_code' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete user',
                'status_code' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
