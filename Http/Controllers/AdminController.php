<?php

namespace GeekCms\PackagesManager\Http\Controllers;

use GeekCms\PackagesManager\Facades\Packages;
use Illuminate\Routing\Controller;

/**
 * Class AdminController.
 */
class AdminController extends Controller
{
    /**
     * Main route with installed packages.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Nwidart\Modules\Exceptions\ModuleNotFoundException
     */
    public function index()
    {
        $main = Packages::setRemote();
        $list = $main->getOfficialPackages();

        return view('packagesmanager::admin/index', [
            'list' => $list
        ]);
    }

    /**
     * Route for show page with available modules list.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('packagesmanager::admin/list', [
            'list' => []
        ]);
    }
}
