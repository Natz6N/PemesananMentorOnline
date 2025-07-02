<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;
    protected $fillable = [
        'booking_code',
        'student_id',
        'mentor_id',
        'mentor_profile_id',
        'scheduled_at',
        'duration_minutes',
        'total_amount',
        'session_topic',
        'student_notes',
        'mentor_notes',
        'status',
        'meeting_link',
        'cancellation_reason',
        'cancelled_at'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function mentorProfile()
    {
        return $this->belongsTo(MentorProfile::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
            ->whereIn('status', ['confirmed', 'pending']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForMentor($query, $mentorId)
    {
        return $query->where('mentor_id', $mentorId);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    // Helper methods
    public function generateBookingCode()
    {
        $this->booking_code = 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        return $this->booking_code;
    }

    public function canBeCancelled()
    {
        return $this->scheduled_at > now()->addHours(24) &&
            in_array($this->status, ['pending', 'confirmed']);
    }
}
