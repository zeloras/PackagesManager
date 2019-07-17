<?php

namespace GeekCms\PackagesManager\Events;

use App\Events\Event;
use App\Models\User;
use Gcms;
use Throwable;

/**
 * Class ModulesEvent.
 */
class ModulesEvent extends Event
{
    /**
     * ModulesEvent constructor.
     *
     * @param null $type
     *
     * @throws Throwable
     */
    public function __construct($type = null)
    {
        Gcms::syncPermissionsList();
        Gcms::syncPermissionForUser();
        parent::__construct($type);
    }
}
