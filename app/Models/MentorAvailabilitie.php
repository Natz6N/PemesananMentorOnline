<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentorAvailabilitie extends Model
{
    /** @use HasFactory<\Database\Factories\MentorAvailabilitieFactory> */
    use HasFactory;
    protected $fillable = [
        'mentor_profile_id', 'day_of_week', 'start_time', 'end_time', 'is_active'
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function mentorProfile()
    {
        return $this->belongsTo(MentorProfile::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }
}
