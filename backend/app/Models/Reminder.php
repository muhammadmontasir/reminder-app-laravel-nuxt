<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'email',
        'remind_at',
        'status',
        'sent_at',
        'error_message'
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'sent_at' => 'datetime'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}