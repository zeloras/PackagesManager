<?php

namespace GeekCms\PackagesManager\Events;

use GeekCms\PackagesManager\Models\Modules;
use App\Events\Event;
use App\Models\User;

/**
 * Class ModulesEvent.
 */
class ModulesEvent extends Event
{
    /**
     * ModulesEvent constructor.
     *
     * @param Modules   $model
     * @param User        $user
     * @param null|string $type
     */
    public function __construct(Modules $model, User $user, $type = null)
    {
        parent::__construct($model, $user, $type);
    }
}
