<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFactory> */
    use HasFactory;
    protected $fillable = [
        'booking_id', 'student_id', 'mentor_id', 'rating', 'comment',
        'rating_aspects', 'is_anonymous'
    ];

    protected $casts = [
        'rating_aspects' => 'array',
        'is_anonymous' => 'boolean',
    ];

    // Relationships
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    // Scopes
    public function scopeForMentor($query, $mentorId)
    {
        return $query->where('mentor_id', $mentorId);
    }

    public function scopeHighRating($query, $minRating = 4)
    {
        return $query->where('rating', '>=', $minRating);
    }
}
