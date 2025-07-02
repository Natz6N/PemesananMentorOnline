<?php

namespace App\Policies;

use App\Models\MentorAvailabilitie;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MentorAvailabilitiePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admin dan student dapat melihat semua ketersediaan mentor
        return $user->status === 'active';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MentorAvailabilitie $mentorAvailabilitie): bool
    {
        // Semua user aktif dapat melihat ketersediaan mentor
        return $user->status === 'active';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Hanya mentor dan admin yang dapat membuat ketersediaan
        return $user->status === 'active' &&
              ($user->role === 'mentor' || $user->role === 'admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MentorAvailabilitie $mentorAvailabilitie): bool
    {
        // Admin dapat mengupdate semua ketersediaan
        if ($user->role === 'admin') {
            return $user->status === 'active';
        }

        // Mentor hanya dapat mengupdate ketersediaannya sendiri
        if ($user->role === 'mentor') {
            $mentorProfile = $mentorAvailabilitie->mentorProfile;
            return $user->status === 'active' && $user->id === $mentorProfile->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MentorAvailabilitie $mentorAvailabilitie): bool
    {
        // Admin dapat menghapus semua ketersediaan
        if ($user->role === 'admin') {
            return $user->status === 'active';
        }

        // Mentor hanya dapat menghapus ketersediaannya sendiri
        if ($user->role === 'mentor') {
            $mentorProfile = $mentorAvailabilitie->mentorProfile;
            return $user->status === 'active' && $user->id === $mentorProfile->user_id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MentorAvailabilitie $mentorAvailabilitie): bool
    {
        // Hanya admin yang dapat restore
        return $user->status === 'active' && $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MentorAvailabilitie $mentorAvailabilitie): bool
    {
        // Hanya admin yang dapat force delete
        return $user->status === 'active' && $user->role === 'admin';
    }
}
