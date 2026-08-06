<?php

namespace App\Http\Controllers;

use App\Models\Transaksi\Transfer;
use App\Models\Transaksi\Tunai;
use Carbon\Carbon;
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

    public function chartData(Request $request)
    {
        $period = $request->get('period', 'daily');
        $userIds = $request->get('users', []);
        $mulai = $request->get('mulai');
        $sampai = $request->get('sampai');

        $labels = [];
        $data = [];

        // Jika ada filter tanggal custom, tampilkan data per hari dalam range tersebut
        if ($mulai && $sampai) {
            $startDate = Carbon::parse($mulai);
            $endDate = Carbon::parse($sampai);
            $diffDays = $startDate->diffInDays($endDate);

            if ($diffDays <= 31) {
                // Per hari
                $current = $startDate->copy();
                while ($current->lte($endDate)) {
                    $labels[] = $current->format('d/m');

                    $transferQuery = Transfer::whereDate('created_at', $current);
                    $tunaiQuery = Tunai::whereDate('created_at', $current);

                    if (!empty($userIds)) {
                        $transferQuery->whereIn('user_id', $userIds);
                        $tunaiQuery->whereIn('user_id', $userIds);
                    }

                    $adminTransfer = $transferQuery->sum('admin');
                    $adminTunai = $tunaiQuery->get()->sum(function ($item) {
                        return is_numeric($item->keterangan) ? (double) $item->keterangan : 0;
                    });

                    $data[] = $adminTransfer + $adminTunai;
                    $current->addDay();
                }
            } else {
                // Per minggu jika range > 31 hari
                $current = $startDate->copy()->startOfWeek();
                while ($current->lte($endDate)) {
                    $weekEnd = $current->copy()->endOfWeek();
                    if ($weekEnd->gt($endDate)) $weekEnd = $endDate->copy();

                    $labels[] = $current->format('d/m') . '-' . $weekEnd->format('d/m');

                    $transferQuery = Transfer::whereBetween('created_at', [$current, $weekEnd->endOfDay()]);
                    $tunaiQuery = Tunai::whereBetween('created_at', [$current, $weekEnd->endOfDay()]);

                    if (!empty($userIds)) {
                        $transferQuery->whereIn('user_id', $userIds);
                        $tunaiQuery->whereIn('user_id', $userIds);
                    }

                    $adminTransfer = $transferQuery->sum('admin');
                    $adminTunai = $tunaiQuery->get()->sum(function ($item) {
                        return is_numeric($item->keterangan) ? (double) $item->keterangan : 0;
                    });

                    $data[] = $adminTransfer + $adminTunai;
                    $current->addWeek();
                }
            }

            return response()->json(['labels' => $labels, 'data' => $data]);
        }

        // Default: tanpa filter tanggal
        switch ($period) {
            case 'daily':
                for ($i = 13; $i >= 0; $i--) {
                    $date = Carbon::today()->subDays($i);
                    $labels[] = $date->format('d/m');

                    $transferQuery = Transfer::whereDate('created_at', $date);
                    $tunaiQuery = Tunai::whereDate('created_at', $date);

                    if (!empty($userIds)) {
                        $transferQuery->whereIn('user_id', $userIds);
                        $tunaiQuery->whereIn('user_id', $userIds);
                    }

                    $adminTransfer = $transferQuery->sum('admin');
                    $adminTunai = $tunaiQuery->get()->sum(function ($item) {
                        return is_numeric($item->keterangan) ? (double) $item->keterangan : 0;
                    });

                    $data[] = $adminTransfer + $adminTunai;
                }
                break;

            case 'weekly':
                for ($i = 7; $i >= 0; $i--) {
                    $startOfWeek = Carbon::today()->subWeeks($i)->startOfWeek();
                    $endOfWeek = Carbon::today()->subWeeks($i)->endOfWeek();
                    $labels[] = $startOfWeek->format('d/m') . '-' . $endOfWeek->format('d/m');

                    $transferQuery = Transfer::whereBetween('created_at', [$startOfWeek, $endOfWeek]);
                    $tunaiQuery = Tunai::whereBetween('created_at', [$startOfWeek, $endOfWeek]);

                    if (!empty($userIds)) {
                        $transferQuery->whereIn('user_id', $userIds);
                        $tunaiQuery->whereIn('user_id', $userIds);
                    }

                    $adminTransfer = $transferQuery->sum('admin');
                    $adminTunai = $tunaiQuery->get()->sum(function ($item) {
                        return is_numeric($item->keterangan) ? (double) $item->keterangan : 0;
                    });

                    $data[] = $adminTransfer + $adminTunai;
                }
                break;

            case 'monthly':
                for ($i = 5; $i >= 0; $i--) {
                    $startOfMonth = Carbon::today()->subMonths($i)->startOfMonth();
                    $endOfMonth = Carbon::today()->subMonths($i)->endOfMonth();
                    $labels[] = $startOfMonth->translatedFormat('M Y');

                    $transferQuery = Transfer::whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                    $tunaiQuery = Tunai::whereBetween('created_at', [$startOfMonth, $endOfMonth]);

                    if (!empty($userIds)) {
                        $transferQuery->whereIn('user_id', $userIds);
                        $tunaiQuery->whereIn('user_id', $userIds);
                    }

                    $adminTransfer = $transferQuery->sum('admin');
                    $adminTunai = $tunaiQuery->get()->sum(function ($item) {
                        return is_numeric($item->keterangan) ? (double) $item->keterangan : 0;
                    });

                    $data[] = $adminTransfer + $adminTunai;
                }
                break;
        }

        return response()->json([
            'labels' => $labels,
            'data' => $data,
        ]);
    }
}
