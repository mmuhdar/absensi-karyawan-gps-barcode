<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeSchedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'shift_id',
        'date'
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendance()
    {
        return $this->hasOne(Attendance::class);
    }
}
