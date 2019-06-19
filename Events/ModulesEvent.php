<?php

namespace GeekCms\PackagesManager\Events;

use App\Events\Event;
use App\Models\User;
use GeekCms\PackagesManager\Models\Modules;

/**
 * Class ModulesEvent.
 */
class ModulesEvent extends Event
{
    /**
     * ModulesEvent constructor.
     *
     * @param Modules     $model
     * @param User        $user
     * @param null|string $type
     */
    public function __construct(Modules $model, User $user, $type = null)
    {
        parent::__construct($model, $user, $type);
    }
}
