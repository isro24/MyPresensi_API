<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'admin_id', 'start_time', 'end_time',
    ];
    protected $table = 'schedules';

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
