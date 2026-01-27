<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dorm extends Model
{
    use HasFactory;

    protected $fillable = [
        'university_id',
        'code',
        'name',
        'address',
        'capacity',
        'status',
        'contact_name',
        'contact_email',
        'contact_phone',
        'notes',
    ];

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function floors()
    {
        return $this->hasMany(Floor::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function dormAdmins()
    {
        return $this->hasMany(DormAdmin::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
