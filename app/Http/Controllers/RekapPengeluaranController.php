<?php

namespace App\Http\Controllers;

use App\Models\PengeluaranHarian;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RekapPengeluaranController extends Controller
{
    private const PIN = '444444';

    public function __construct()
    {
        $this->middleware('auth');
        $this->title = 'Rekap Pengeluaran';
        $this->breadcrumb = ['Rekap Pengeluaran' => '#'];
    }

    public function index(Request $request)
    {
        $this->authorizeRamdan();

        if (session('rekap_pin') !== self::PIN) {
            return $this->render('modules.rekap.pin');
        }

        $mulai = $request->get('mulai') ? Carbon::parse($request->get('mulai')) : Carbon::today('Asia/Jakarta')->subDays(13);
        $sampai = $request->get('sampai') ? Carbon::parse($request->get('sampai')) : Carbon::today('Asia/Jakarta');

        if ($mulai->gt($sampai)) {
            [$mulai, $sampai] = [$sampai, $mulai];
        }

        $records = PengeluaranHarian::whereBetween('tanggal', [
            $mulai->format('Y-m-d'),
            $sampai->format('Y-m-d'),
        ])->get();

        $total = (float) $records->sum('nominal');
        $jumlahHari = $mulai->diffInDays($sampai) + 1;
        $rataRata = $jumlahHari > 0 ? $total / $jumlahHari : 0;

        $hariTerbesar = PengeluaranHarian::selectRaw('tanggal, SUM(nominal) as total')
            ->whereBetween('tanggal', [$mulai->format('Y-m-d'), $sampai->format('Y-m-d')])
            ->groupBy('tanggal')
            ->orderByDesc('total')
            ->first();

        return $this->render('modules.rekap.index', compact(
            'mulai',
            'sampai',
            'total',
            'rataRata',
            'jumlahHari',
            'hariTerbesar'
        ));
    }

    public function verifyPin(Request $request)
    {
        $this->authorizeRamdan();

        $pin = (string) $request->get('pin', '');

        if ($pin === self::PIN) {
            session(['rekap_pin' => self::PIN]);

            return response()->json(['status' => true]);
        }

        return response()->json([
            'status' => false,
            'message' => 'PIN salah',
        ], 422);
    }

    public function chartData(Request $request)
    {
        $this->authorizeRamdan();

        if (session('rekap_pin') !== self::PIN) {
            abort(403, 'PIN belum diverifikasi');
        }

        $period = $request->get('period', 'daily');
        $mulai = $request->get('mulai');
        $sampai = $request->get('sampai');

        $labels = [];
        $data = [];

        $sumFor = function (Carbon $start, Carbon $end): float {
            return (float) PengeluaranHarian::whereBetween('tanggal', [
                $start->format('Y-m-d'),
                $end->format('Y-m-d'),
            ])->sum('nominal');
        };

        // Range tanggal custom
        if ($mulai && $sampai) {
            $startDate = Carbon::parse($mulai);
            $endDate = Carbon::parse($sampai);

            if ($startDate->gt($endDate)) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }

            if ($startDate->diffInDays($endDate) <= 31) {
                $current = $startDate->copy();
                while ($current->lte($endDate)) {
                    $labels[] = $current->format('d/m');
                    $data[] = $sumFor($current, $current);
                    $current->addDay();
                }
            } else {
                $current = $startDate->copy()->startOfWeek();
                while ($current->lte($endDate)) {
                    $weekEnd = $current->copy()->endOfWeek();
                    if ($weekEnd->gt($endDate)) {
                        $weekEnd = $endDate->copy();
                    }

                    $labels[] = $current->format('d/m') . '-' . $weekEnd->format('d/m');
                    $data[] = $sumFor($current, $weekEnd);
                    $current->addWeek();
                }
            }

            return response()->json(compact('labels', 'data'));
        }

        // Default tanpa filter
        $today = Carbon::today('Asia/Jakarta');

        switch ($period) {
            case 'daily':
                for ($i = 13; $i >= 0; $i--) {
                    $date = $today->copy()->subDays($i);
                    $labels[] = $date->format('d/m');
                    $data[] = $sumFor($date, $date);
                }
                break;

            case 'weekly':
                for ($i = 7; $i >= 0; $i--) {
                    $startOfWeek = $today->copy()->subWeeks($i)->startOfWeek();
                    $endOfWeek = $today->copy()->subWeeks($i)->endOfWeek();
                    $labels[] = $startOfWeek->format('d/m') . '-' . $endOfWeek->format('d/m');
                    $data[] = $sumFor($startOfWeek, $endOfWeek);
                }
                break;

            case 'monthly':
                for ($i = 5; $i >= 0; $i--) {
                    $startOfMonth = $today->copy()->subMonths($i)->startOfMonth();
                    $endOfMonth = $today->copy()->subMonths($i)->endOfMonth();
                    $labels[] = $startOfMonth->translatedFormat('M Y');
                    $data[] = $sumFor($startOfMonth, $endOfMonth);
                }
                break;
        }

        return response()->json(compact('labels', 'data'));
    }

    private function authorizeRamdan(): void
    {
        if (auth()->user()?->username !== 'ramdan') {
            abort(403, 'Khusus user ramdan');
        }
    }
}
