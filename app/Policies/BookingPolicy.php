<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin bisa melihat semua booking
        if ($user->role === 'admin') {
            return $user->status === 'active';
        }

        // Mentor dan student hanya bisa melihat booking terkait mereka
        return $user->status === 'active';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Booking $booking): bool
    {
        // Admin bisa melihat semua booking
        if ($user->role === 'admin') {
            return $user->status === 'active';
        }

        // Mentor hanya bisa melihat booking dimana mereka sebagai mentor
        if ($user->role === 'mentor') {
            return $user->status === 'active' && $user->id === $booking->mentor_id;
        }

        // Student hanya bisa melihat booking mereka sendiri
        if ($user->role === 'student') {
            return $user->status === 'active' && $user->id === $booking->student_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Hanya student yang bisa membuat booking
        return $user->status === 'active' && $user->role === 'student';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Booking $booking): bool
    {
        // Admin bisa mengupdate semua booking
        if ($user->role === 'admin') {
            return $user->status === 'active';
        }

        // Mentor hanya bisa mengupdate booking dimana mereka sebagai mentor
        // dan hanya untuk status dan mentor_notes
        if ($user->role === 'mentor') {
            return $user->status === 'active' && $user->id === $booking->mentor_id;
        }

        // Student hanya bisa mengupdate booking mereka sendiri
        // dan hanya jika status masih pending
        if ($user->role === 'student') {
            return $user->status === 'active' &&
                   $user->id === $booking->student_id &&
                   $booking->status === 'pending';
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Booking $booking): bool
    {
        // Admin bisa menghapus semua booking
        if ($user->role === 'admin') {
            return $user->status === 'active';
        }

        // Student hanya bisa membatalkan booking mereka sendiri
        // dan hanya jika status masih pending atau booking masih jauh (24 jam)
        if ($user->role === 'student') {
            return $user->status === 'active' &&
                   $user->id === $booking->student_id &&
                   $booking->canBeCancelled();
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Booking $booking): bool
    {
        // Hanya admin yang bisa restore
        return $user->status === 'active' && $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Booking $booking): bool
    {
        // Hanya admin yang bisa force delete
        return $user->status === 'active' && $user->role === 'admin';
    }
}
