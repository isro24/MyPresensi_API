<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminEmployeePermissionRequest;
use App\Http\Requests\EmployeePermissionRequest;
use App\Models\EmployeePermission;
use App\Models\Employee;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployeePermissionController extends Controller
{
    public function adminIndex()
    {
        $permissions = EmployeePermission::with('employee.user')
            ->orderBy('created_at', 'desc')
            ->get();

        $permissions->transform(function ($item) {
            if ($item->attachment) {
                $item->attachment_url = asset('storage/' . $item->attachment);
            }

            if (
                $item->employee &&
                $item->employee->photo &&
                !str_starts_with($item->employee->photo, 'http')
            ) {
                $item->employee->photo_url = asset('storage/' . $item->employee->photo);
            } elseif ($item->employee && $item->employee->photo) {
                $item->employee->photo_url = $item->employee->photo;
            }

            return $item;
        });

        return response()->json([
            'message' => 'List semua pengajuan izin karyawan',
            'status_code' => 200,
            'data' => $permissions,
        ]);
    }

    public function employeeIndex()
    {
        $user = Auth::guard('api')->user();
        $employee = Employee::where('user_id', $user->id)->firstOrFail();

        $permissions = EmployeePermission::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'List pengajuan izin Anda',
            'status_code' => 200,
            'data' => $permissions,
        ]);
    }

    public function store(EmployeePermissionRequest $request)
    {
        $user = Auth::guard('api')->user();
        $employee = Employee::where('user_id', $user->id)->firstOrFail();

        $data = $request->validated();
        $data['employee_id'] = $employee->id;

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('attachments', 'public');
        }

        $permission = EmployeePermission::create($data);

        return response()->json([
            'message' => 'Pengajuan izin berhasil dibuat',
            'status_code' => 201,
            'data' => $permission,
        ]);
    }

    public function show($id)
    {
        $permission = EmployeePermission::with('employee.user')->findOrFail($id);

        return response()->json([
            'message' => 'Detail pengajuan izin',
            'status_code' => 200,
            'data' => $permission,
        ]);
    }

    public function update(EmployeePermissionRequest $request, $id)
    {
        $user = Auth::guard('api')->user();
        $employee = Employee::where('user_id', $user->id)->firstOrFail();

        $permission = EmployeePermission::where('id', $id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            if ($permission->attachment && Storage::disk('public')->exists($permission->attachment)) {
                Storage::disk('public')->delete($permission->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('attachments', 'public');
        }

        $permission->update($data);

        return response()->json([
            'message' => 'Pengajuan izin berhasil diperbarui',
            'status_code' => 200,
            'data' => $permission,
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::guard('api')->user();
        $employee = Employee::where('user_id', $user->id)->firstOrFail();

        $permission = EmployeePermission::where('id', $id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        if ($permission->attachment && Storage::disk('public')->exists($permission->attachment)) {
            Storage::disk('public')->delete($permission->attachment);
        }

        $permission->delete();

        return response()->json([
            'message' => 'Pengajuan izin berhasil dihapus',
            'status_code' => 200,
        ]);
    }

    public function updateStatus(AdminEmployeePermissionRequest $request, $id)
    {
        $permission = EmployeePermission::findOrFail($id);
        $status = $request->validated()['status'];

        $updateData = ['status' => $status];

        if ($status === 'disetujui') {
            $updateData['approved_at'] = now();
            $updateData['rejected_at'] = null;
        } elseif ($status === 'ditolak') {
            $updateData['rejected_at'] = now();
            $updateData['approved_at'] = null;
        } elseif ($status === 'menunggu') {
            $updateData['approved_at'] = null;
            $updateData['rejected_at'] = null;
        }

        $permission->update($updateData);

        return response()->json([
            'message' => "Status izin berhasil diubah : ($status)",
            'status_code' => 200,
            'data' => $permission,
        ]);
    }

    public function downloadAttachment($filename)
    {
        $filePath = storage_path("app/public/attachments/$filename");

        if (!file_exists($filePath)) {
            return response()->json([
                'message' => 'File tidak ditemukan',
            ], HttpResponse::HTTP_NOT_FOUND);
        }

        return response()->file($filePath, [
            'Content-Type' => mime_content_type($filePath),
        ]);
    }
}
