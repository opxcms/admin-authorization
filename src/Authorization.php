<?php

namespace Modules\Admin\Authorization;

use Core\Foundation\Module\BaseModule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Managers\Models\Manager;

class Authorization extends BaseModule
{
    /** @var string  Module name */
    protected $name = 'admin_authorization';

    /** @var string  Module path */
    protected $path = __DIR__;

    /** @var array  Store for permissions */
    protected $permissions;

    /**
     * Check if currently authenticated manager has the permission.
     *
     * @param $permission
     *
     * @return  bool
     */
    public function can($permission): bool
    {
        // If manager is admin he can do anything.
        if (empty($permission) || $this->isAdmin()) {
            return true;
        }

        $manager = $this->getManager();

        // If manager was not logged in he can do nothing.
        if ($manager === null) {
            return false;
        }

        $permissions = $this->getManagerPermissions($manager);

        return in_array($permission, $permissions, true);
    }

    /**
     * Check if manager is admin.
     *
     * @return  bool
     */
    protected function isAdmin(): bool
    {
        return Auth::guard('admin')->check();
    }

    /**
     * Get currently logged in manager.
     *
     * @return  Manager|null
     */
    protected function getManager(): ?Manager
    {
        return Auth::guard('manager')->user();
    }

    /**
     * Get available permissions foe manager.
     *
     * @param Manager $manager
     *
     * @return  array
     */
    protected function getManagerPermissions(Manager $manager): array
    {
        if (isset($this->permissions)) {
            return $this->permissions;
        }

        // get managers permission groups
        $managerGroups = DB::table('manager_has_permission_group')
            ->where('manager_id', $manager->getAttribute('id'))
            ->pluck('group_id')
            ->toArray();

        // get default groups
        $defaultGroups = DB::table('permission_groups')
            ->where('default', true)
            ->pluck('id')
            ->toArray();

        $groups = array_merge($managerGroups, $defaultGroups);

        $permissions = DB::table('permission_group_has_permission')
            ->whereIn('group_id', $groups)
            ->groupBy('permission')
            ->pluck('permission')
            ->toArray();

        $this->permissions = $permissions;

        return $this->permissions;
    }
}
