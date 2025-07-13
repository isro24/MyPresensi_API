<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id', 'schedule_id', 'location_id', 'clock_in', 'latitude_clock_in', 'longitude_clock_in', 'photo_clock_in',
        'clock_out', 'latitude_clock_out', 'longitude_clock_out', 'photo_clock_out',
        'status', 'note'
    ];
    protected $table = 'attendance';

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
