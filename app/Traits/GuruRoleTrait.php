<?php

namespace App\Traits;

use App\Models\Guru;
use App\Models\Role;
use App\Models\User;

trait GuruRoleTrait
{
    /**
     * Build a query that filters Guru records by a given role name,
     * checking both the polymorphic `roles` relationship and the
     * legacy single `role` relationship.
     *
     * @param  string  $roleName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function queryGuruByRole(string $roleName)
    {
        return Guru::with('user')->whereHas('user', function ($query) use ($roleName) {
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('role_name', $roleName);
            })->orWhereHas('role', function ($q) use ($roleName) {
                $q->where('role_name', $roleName);
            });
        });
    }

    /**
     * Return the IDs of all Guru records that currently hold the given role,
     * optionally excluding a specific Guru ID.
     *
     * @param  string    $roleName
     * @param  int|null  $excludeId
     * @return \Illuminate\Support\Collection
     */
    protected function getGuruIdsByRole(string $roleName, ?int $excludeId = null)
    {
        $query = Guru::whereHas('user', function ($query) use ($roleName) {
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('role_name', $roleName);
            })->orWhereHas('role', function ($q) use ($roleName) {
                $q->where('role_name', $roleName);
            });
        });

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->pluck('id');
    }

    /**
     * Convert the `status` field ("Aktif" / "Tidak Aktif") into the
     * integer `is_active` field and remove `status` from the array.
     *
     * @param  array  $validated
     * @return array
     */
    protected function convertStatusToIsActive(array $validated): array
    {
        $validated['is_active'] = $validated['status'] === 'Aktif' ? 1 : 0;
        unset($validated['status']);

        return $validated;
    }

    /**
     * Extract and clean a subset of Guru-related fields from a validated
     * data array, filtering out null and empty-string values.
     *
     * @param  array  $validated
     * @param  array  $extraFields  Additional field names beyond the default set.
     * @return array
     */
    protected function filterGuruUpdateData(array $validated, array $extraFields = []): array
    {
        $baseFields = ['nama', 'nip', 'alamat', 'telepon', 'email', 'foto', 'is_active'];
        $fields = array_unique(array_merge($baseFields, $extraFields));

        $guruData = array_intersect_key($validated, array_flip($fields));

        return array_filter($guruData, function ($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * Attach the given role to a User without removing existing roles.
     *
     * @param  \App\Models\User  $user
     * @param  string            $roleName
     * @return void
     */
    protected function syncUserRole(User $user, string $roleName): void
    {
        $role = Role::where('role_name', $roleName)->first();
        if ($role) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }

    /**
     * Detach the given role from a User.
     *
     * @param  \App\Models\User  $user
     * @param  string            $roleName
     * @return void
     */
    protected function detachUserRole(User $user, string $roleName): void
    {
        $role = Role::where('role_name', $roleName)->first();
        if ($role) {
            $user->roles()->detach($role->id);
        }
    }
}
