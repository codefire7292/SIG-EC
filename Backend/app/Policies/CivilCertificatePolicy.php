<?php

namespace App\Policies;

use App\Models\CivilCertificate;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CivilCertificatePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view-registries', 'full-access']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CivilCertificate $civilCertificate): bool
    {
        return $user->hasAnyPermission(['view-registries', 'full-access']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['create-drafts', 'full-access']);
    }

    /**
     * Determine whether the user can update the model (Drafts and Corrections only).
     */
    public function update(User $user, CivilCertificate $civilCertificate): bool
    {
        if ($civilCertificate->is_locked || in_array($civilCertificate->status, ['signe', 'valide_hierarchie'])) {
            return false;
        }

        if ($civilCertificate->status === 'a_corriger') {
            return $user->hasAnyPermission(['create-drafts', 'manage-corrections', 'full-access']);
        }

        return $user->hasAnyPermission(['create-drafts', 'full-access']);
    }

    public function observe(User $user, CivilCertificate $civilCertificate): bool
    {
        if ($civilCertificate->is_locked || in_array($civilCertificate->status, ['signe', 'brouillon'])) {
            return false;
        }

        return $user->hasAnyPermission(['validate-intermediate', 'validate-final', 'full-access']);
    }

    /**
     * Determine whether the user can approve (Hierarchy Validation).
     */
    public function approve(User $user, CivilCertificate $civilCertificate): bool
    {
        if ($civilCertificate->is_locked || in_array($civilCertificate->status, ['signe', 'valide_hierarchie'])) {
            return false;
        }

        return $user->hasAnyPermission(['validate-intermediate', 'full-access']);
    }

    /**
     * Determine whether the user can sign legally (Final Validation).
     */
    public function sign(User $user, CivilCertificate $civilCertificate): bool
    {
        if ($civilCertificate->is_locked || $civilCertificate->status !== 'valide_hierarchie') {
            return false;
        }

        return $user->hasPermissionTo('sign-legally');
    }

    /**
     * Determine whether the user can rectify a signed act (Create legal version).
     */
    public function rectify(User $user, CivilCertificate $civilCertificate): bool
    {
        if ($civilCertificate->status !== 'signe') {
            return false;
        }

        return $user->hasAnyPermission(['manage-corrections', 'full-access']);
    }
}
