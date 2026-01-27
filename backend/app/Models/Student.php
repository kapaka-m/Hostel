<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'dorm_id',
        'full_name',
        'student_no',
        'phone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dorm()
    {
        return $this->belongsTo(Dorm::class);
    }

    public function roomAssignments()
    {
        return $this->hasMany(RoomAssignment::class);
    }

    public function activeAssignment()
    {
        return $this->hasOne(RoomAssignment::class)->where('active', true);
    }
}
