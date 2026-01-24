<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'changes_json',
        'actor_id',
        'created_at',
    ];

    protected $casts = [
        'changes_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
