<?php

namespace GeekCms\PackagesManager\Http\Controllers;

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
     */
    public function index()
    {
        return view('packages::admin/index');
    }

    /**
     * Route for show page with available modules list.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list()
    {
        return view('packages::admin/list');
    }
}
