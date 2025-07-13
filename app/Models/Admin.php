<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $fillable = [
        'position', 'phone', 'photo', 
    ];
    protected $table = 'admin';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function location()
    {
        return $this->hasMany(Location::class);
    }
}
