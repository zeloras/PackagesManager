<?php

namespace GeekCms\PackagesManager\Repository;

use Illuminate\Support\Arr;

class LocalPackage
{
    protected $modules = [];
    protected $modules_system = [];

    public function __construct($data = [])
    {
        $this->modules = $data;
        $this->modules_system = \Module::all();
        $this->setStatuses();
    }

    protected function setStatuses()
    {
        foreach ($this->modules_system as $module) {
            $module_fetch_name = $module->get('name', null);
            $modules_where = array_where($this->modules, function ($v) use ($module) {
                return !empty($v['module_info']['name']) && $v['module_info']['name'] === $module->get('name', null);
            });

            if (count($modules_where) && !empty($module_fetch_name)) {
                $first_key = array_key_first($modules_where);
                $this->modules[$first_key]['installed'] = true;
                $this->modules[$first_key]['enabled'] = ($module->isStatus(1));
            }
        }
    }

    public function installed()
    {
        $list = [];
        foreach ($this->modules as $module) {
            if ($module['installed']) {
                $list[] = $module;
            }
        }

        return $list;
    }

    public function disabled()
    {
        $list = [];
        foreach ($this->modules as $module) {
            if (!$module['enabled']) {
                $list[] = $module;
            }
        }

        return $list;
    }

    public function enabled()
    {
        $list = [];
        foreach ($this->modules as $module) {
            if ($module['enabled']) {
                $list[] = $module;
            }
        }

        return $list;
    }
}
