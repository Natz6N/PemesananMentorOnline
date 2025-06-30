<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'role',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function mentorProfile()
    {
        return $this->hasOne(MentorProfile::class);
    }

    public function studentBookings()
    {
        return $this->hasMany(Booking::class, 'student_id');
    }

    public function mentorBookings()
    {
        return $this->hasMany(Booking::class, 'mentor_id');
    }

    public function givenReviews()
    {
        return $this->hasMany(Review::class, 'student_id');
    }

    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'mentor_id');
    }

    // Scopes
    public function scopeMentors($query)
    {
        return $query->where('role', 'mentor');
    }

    public function scopeStudents($query)
    {
        return $query->where('role', 'student');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Helper methods
    public function isMentor()
    {
        return $this->role === 'mentor';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }
}
