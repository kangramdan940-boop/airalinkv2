<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KalkulatorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->title = 'Kalkulator';
        $this->breadcrumb = ['Kalkulator' => '#'];
    }

    public function index()
    {
        return $this->render('modules.kalkulator.index');
    }
}
