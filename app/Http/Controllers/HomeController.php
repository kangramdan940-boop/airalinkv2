<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Jika user tidak punya akses dashboard, redirect ke transfer
        if (!auth()->user()->can('dashboard-view')) {
            return redirect('/transfer');
        }

        $this->title = 'Dashboard';
        $this->breadcrumb = ['Dashboard' => '#'];

        return $this->render('modules.home');
    }
}
