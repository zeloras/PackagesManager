<?php

namespace GeekCms\PackagesManager\Events;

use App\Events\Event;
use App\Models\User;
use Gcms;
use GeekCms\PackagesManager\Models\Modules;
use Throwable;

/**
 * Class ModulesEvent.
 */
class ModulesEvent extends Event
{
    /**
     * ModulesEvent constructor.
     *
     * @param Modules $model
     * @param User $user
     * @param null $type
     *
     * @throws Throwable
     */
    public function __construct(Modules $model, User $user, $type = null)
    {
        Gcms::syncPermissionsList();
        Gcms::syncPermissionForUser($user);
        parent::__construct($model, $user, $type);
    }
}
