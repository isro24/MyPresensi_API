<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index()
    {
        try {
            $adminId = Auth::user()->admin->id;

            $schedules = Schedule::where('admin_id', $adminId)->get();

            return response()->json([
                'message' => 'Schedule data retrieved successfully',
                'status_code' => 200,
                'data' => $schedules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve schedules',
                'status_code' => 500,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'start_time' => 'required|date_format:H:i:s',
                'end_time' => 'required|date_format:H:i:s|after:start_time',
            ]);

            $schedule = Schedule::create([
                'admin_id' => Auth::user()->admin->id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            return response()->json([
                'message' => 'Schedule created successfully',
                'status_code' => 201,
                'data' => $schedule
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create schedule',
                'status_code' => 500,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $schedule = Schedule::findOrFail($id);

            $schedule->update($request->only(['start_time', 'end_time']));

            return response()->json([
                'message' => 'Schedule updated successfully',
                'status_code' => 200,
                'data' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update schedule',
                'status_code' => 500,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $schedule = Schedule::findOrFail($id);
            $schedule->delete();

            return response()->json([
                'message' => 'Schedule deleted successfully',
                'status_code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete schedule',
                'status_code' => 500,
                'error' => $e->getMessage()
            ]);
        }
    }
}
