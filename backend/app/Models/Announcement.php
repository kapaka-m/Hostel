<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Announcement extends Model
{
    use HasFactory;

    public const STATUSES = ['DRAFT', 'SCHEDULED', 'PUBLISHED', 'EXPIRED'];

    public const AUDIENCES = ['UNIVERSITY', 'DORM'];

    protected $fillable = [
        'university_id',
        'dorm_id',
        'created_by',
        'title',
        'body',
        'audience',
        'status',
        'publish_at',
        'expire_at',
    ];

    protected $casts = [
        'publish_at' => 'datetime',
        'expire_at' => 'datetime',
    ];

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function dorm()
    {
        return $this->belongsTo(Dorm::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function currentStatus(): string
    {
        if ($this->status === 'DRAFT') {
            return 'DRAFT';
        }

        $now = Carbon::now();

        if ($this->publish_at && $this->publish_at->isFuture()) {
            return 'SCHEDULED';
        }

        if ($this->expire_at && $this->expire_at->isPast()) {
            return 'EXPIRED';
        }

        return 'PUBLISHED';
    }
}
