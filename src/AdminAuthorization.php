<?php

namespace Modules\Admin\Authorization;

use Illuminate\Support\Facades\Facade;

/**
 * @method  static bool can($permission)
 */
class AdminAuthorization extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'admin_authorization';
    }
}
