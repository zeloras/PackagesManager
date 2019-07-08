<?php

namespace GeekCms\PackagesManager\Repository;

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

    /**
     * Set statuses for modules, e.g installed, enabled
     */
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

    /**
     * Get all installed modules
     *
     * @return array
     */
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

    /**
     * Get all available modules for install/remove
     *
     * @param bool $sort_desc
     * @return array
     */
    public function available($sort_desc = false)
    {
        uasort($this->modules, function ($a, $b) use ($sort_desc) {
            $weight = ($sort_desc) ? 1 : -1;
            if ($a['installed'] && $b['installed']) {
                $weight = ($a['enabled']) ? 1 : -1;
            } elseif ($a['installed']) {
                $weight = 1;
            }

            return (!$sort_desc) ? $weight * -1 : $weight;
        });

        return $this->modules;
    }

    /**
     * Get all disabled modules
     *
     * @return array
     */
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

    /**
     * Get all enabled modules
     *
     * @return array
     */
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

    /**
     * Get all available for install modules
     *
     * @return array
     */
    public function forInstall()
    {
        $list = [];
        foreach ($this->modules as $module) {
            if (!$module['installed']) {
                $list[] = $module;
            }
        }

        return $list;
    }
}
