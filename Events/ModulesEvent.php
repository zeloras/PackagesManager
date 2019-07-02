<?php

namespace GeekCms\PackagesManager\Events;

use App\Events\Event;
use App\Models\User;
use Carbon\Carbon;
use GeekCms\PackagesManager\Models\Modules;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

/**
 * Class ModulesEvent.
 */
class ModulesEvent extends Event
{
    /**
     * ModulesEvent constructor.
     *
     * @param Modules $model
     * @param User    $user
     * @param null    $type
     *
     * @throws \Throwable
     */
    public function __construct(Modules $model, User $user, $type = null)
    {
        self::checkAndUpdatePermissions();
        parent::__construct($model, $user, $type);
    }

    /**
     * Method for check new permission roles and update permission table.
     *
     * @throws \Throwable
     */
    public static function checkAndUpdatePermissions()
    {
        if (!Schema::hasTable('permissions')) {
            return false;
        }

        $main_user = User::first();
        $permissions = \Packages::getPermissionsList();
        $permissions_list = $prepared_insert = [];
        $current_time = Carbon::now();
        array_map(function ($arr) use (&$permissions_list, &$prepared_insert, $current_time) {
            $columns = array_column($arr, 'name');
            $permissions_list = array_merge($permissions_list, $columns);

            foreach ($columns as $col) {
                $prepared_insert[$col] = [
                    'name' => $col,
                    'guard_name' => 'web',
                    'created_at' => $current_time,
                    'updated_at' => $current_time,
                ];
            }

            return null;
        }, $permissions);

        \DB::transaction(function () use ($permissions_list, $prepared_insert, $main_user) {
            $permissions_list = array_unique($permissions_list);
            $permissions_old = Permission::whereIn('name', $permissions_list)->get();

            if (!\count($permissions_old)) {
                Permission::query()->insert($prepared_insert);
                if ($main_user->checkPermissionTo('admin_access')) {
                    $permissions_list = array_keys($prepared_insert);
                    $main_user->syncPermissions($permissions_list);
                }
            } elseif (\count($permissions_old) !== \count($permissions_list)) {
                foreach ($permissions_old as $arr_old) {
                    unset($prepared_insert[$arr_old->name]);
                }

                Permission::query()->insert($prepared_insert);
            }
        });
    }
}
