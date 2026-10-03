<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ClosingController extends Controller
{
    private string $apiBase = 'https://api.tokokangramdan.com/api';

    public function __construct()
    {
        $this->title = 'Closing Brilink';
        $this->breadcrumb = ['Home' => url('/home'), 'Closing' => '#'];
    }

    public function index()
    {
        return $this->render('modules.closing.index');
    }

    public function apiAccounts()
    {
        $response = Http::get("{$this->apiBase}/brilink-accounts");
        return response()->json($response->json(), $response->status());
    }

    public function apiShops()
    {
        $response = Http::get("{$this->apiBase}/brilink-shops");
        return response()->json($response->json(), $response->status());
    }

    public function apiByDate($date)
    {
        $response = Http::get("{$this->apiBase}/brilink-closings/by-date/{$date}");
        return response()->json($response->json(), $response->status());
    }

    public function apiStore(Request $request)
    {
        $response = Http::post("{$this->apiBase}/brilink-closings", $request->all());
        return response()->json($response->json(), $response->status());
    }

    public function apiList(Request $request)
    {
        $month = $request->query('month', date('n'));
        $year = $request->query('year', date('Y'));
        $response = Http::get("{$this->apiBase}/brilink-closings", [
            'month' => $month,
            'year' => $year,
        ]);
        return response()->json($response->json(), $response->status());
    }

    public function apiShow($id)
    {
        $response = Http::get("{$this->apiBase}/brilink-closings/{$id}");
        return response()->json($response->json(), $response->status());
    }
}
