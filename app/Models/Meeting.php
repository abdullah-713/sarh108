<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'meeting_date',
        'start_time',
        'end_time',
        'location',
        'meeting_link',
        'status',
        'organizer_id',
        'created_by',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'meeting_attendees', 'meeting_id', 'user_id')
            ->withTimestamps();
    }
}
