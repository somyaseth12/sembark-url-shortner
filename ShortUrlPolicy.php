<?php

namespace App\Policies;

use App\Models\ShortUrl;
use App\Models\User;

class ShortUrlPolicy
{
    /**
     * Determine if the user can view the model.
     */
    public function view(User $user, ShortUrl $shortUrl): bool
    {
        // SuperAdmin can view all
        if ($user->role->name === 'SuperAdmin') {
            return true;
        }

        // ClientAdmin and Admin can view all URLs in their company
        if ($user->role->name === 'ClientAdmin' || $user->role->name === 'Admin') {
            return $user->company_id === $shortUrl->company_id;
        }

        // Members can only view their own URLs
        return $user->id === $shortUrl->user_id;
    }

    /**
     * Determine if the user can update the model.
     */
    public function update(User $user, ShortUrl $shortUrl): bool
    {
        // SuperAdmin can update all
        if ($user->role->name === 'SuperAdmin') {
            return true;
        }

        // ClientAdmin and Admin can update URLs in their company
        if ($user->role->name === 'ClientAdmin' || $user->role->name === 'Admin') {
            return $user->company_id === $shortUrl->company_id;
        }

        // Members can only update their own URLs
        return $user->id === $shortUrl->user_id;
    }

    /**
     * Determine if the user can delete the model.
     */
    public function delete(User $user, ShortUrl $shortUrl): bool
    {
        // SuperAdmin can delete all
        if ($user->role->name === 'SuperAdmin') {
            return true;
        }

        // ClientAdmin and Admin can delete URLs in their company
        if ($user->role->name === 'ClientAdmin' || $user->role->name === 'Admin') {
            return $user->company_id === $shortUrl->company_id;
        }

        // Members can only delete their own URLs
        return $user->id === $shortUrl->user_id;
    }
}
