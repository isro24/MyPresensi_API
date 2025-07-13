<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocationRequest;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function index()
    {
        try {
            $adminId = Auth::user()->admin->id;

            $locations = Location::all();

            return response()->json([
                'message' => 'Location data retrieved successfully',
                'status_code' => 200,
                'data' => $locations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve location data',
                'status_code' => 500,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function store(LocationRequest $request)
    {
        $adminId = Auth::user()->admin->id;

        try {
            $request->validate([
                'name' => 'required|string',
                'latitude' => 'required',
                'longitude' => 'required',
                'radius' => 'required|numeric',
            ]);

            $location = Location::create([
                'admin_id' => $adminId,
                'name' => $request->name,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'radius' => $request->radius,
            ]);

            return response()->json([
                'message' => 'Lokasi Berhasil Ditambahkan',
                'status_code' => 201,
                'data' => $location
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal Menambah Lokasi',
                'status_code' => 500,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update(LocationRequest $request, $id)
    {
        try {
            $location = Location::findOrFail($id);

            $location->update($request->only(['name', 'latitude', 'longitude', 'radius']));

            return response()->json([
                'message' => 'Lokasi Berhasil Diubah',
                'status_code' => 200,
                'data' => $location
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal Mengubah Lokasi',
                'status_code' => 500,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $location = Location::findOrFail($id);
            $location->delete();

            return response()->json([
                'message' => 'Lokasi Berhasil Dihapus',
                'status_code' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete location',
                'status_code' => 500,
                'error' => $e->getMessage()
            ]);
        }
    }
}
