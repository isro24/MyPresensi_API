<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePermission extends Model
{
    protected $table = 'employee_permissions';

    protected $fillable = [
        'employee_id',
        'type',
        'start_date',
        'end_date',
        'status',
        'reason',
        'attachment',
        'approved_at',
        'rejected_at',
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'approved_at',
        'rejected_at',
        'created_at',
        'updated_at',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
