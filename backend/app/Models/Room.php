<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'dorm_id',
        'floor_id',
        'room_number',
        'capacity',
        'status',
    ];

    public function dorm()
    {
        return $this->belongsTo(Dorm::class);
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function assignments()
    {
        return $this->hasMany(RoomAssignment::class);
    }

    public function activeAssignments()
    {
        return $this->hasMany(RoomAssignment::class)->where('active', true);
    }
}
