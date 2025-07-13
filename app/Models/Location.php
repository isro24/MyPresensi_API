<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'admin_id','name', 'latitude', 'longitude', 'radius'
    ];
    protected $table = 'locations';

    public function attendances()
    {
        return $this->hasMany(Location::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
    