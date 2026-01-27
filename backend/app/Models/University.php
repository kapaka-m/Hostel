<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'contact_email',
        'contact_phone',
        'is_active',
    ];

    public function dorms()
    {
        return $this->hasMany(Dorm::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
