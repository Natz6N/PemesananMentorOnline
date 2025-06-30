<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MentorProfile extends Model
{
    /** @use HasFactory<\Database\Factories\MentorProfileFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id', 'bio', 'expertise', 'experience_years', 'education',
        'current_position', 'company', 'achievements', 'hourly_rate',
        'timezone', 'languages', 'status', 'rejection_reason',
        'rating_average', 'total_reviews', 'total_sessions', 'is_available'
    ];

    protected $casts = [
        'expertise' => 'array',
        'languages' => 'array',
        'hourly_rate' => 'decimal:2',
        'rating_average' => 'decimal:2',
        'is_available' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'mentor_categories');
    }

    public function availabilities()
    {
        return $this->hasMany(MentorAvailabilitie::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId);
        });
    }

    // Helper methods
    public function updateRating()
    {
        $reviews = $this->user->receivedReviews;
        $this->total_reviews = $reviews->count();
        $this->rating_average = $reviews->avg('rating') ?: 0;
        $this->save();
    }
}
