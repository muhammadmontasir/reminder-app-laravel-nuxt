<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'event_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'status',
        'metadata',
        'last_synced_at',
        'is_online',
        'reminder_time',
        'participants',
    ];

    protected $casts = [
        'participants' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'reminder_time' => 'datetime',
        'metadata' => 'array',
        'last_synced_at' => 'datetime',
    ];

    protected $attributes = [
        'participants' => '[]',
    ];

    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}