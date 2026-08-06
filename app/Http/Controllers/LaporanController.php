<?php

namespace App\Http\Controllers;

use App\Models\Investor;
use App\Models\Pengeluaran;
use App\Models\User;
use App\Models\Transaksi\Transfer;
use App\Models\Transaksi\Tunai;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->title = 'Laporan';
        $this->breadcrumb = ['Laporan' => '#'];
    }

    public function index()
    {
        $users = User::role(['admin', 'user'])->orderBy('username')->get();

        return $this->render('modules.laporan.index', compact('users'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'bulan_mulai' => 'required|string',
            'bulan_akhir' => 'required|string',
            'user_id' => 'nullable|exists:sys_users,id',
        ]);

        $bulanMulai = $request->bulan_mulai;
        $bulanAkhir = $request->bulan_akhir;
        $filterUserId = $request->user_id;

        $startDate = Carbon::parse($bulanMulai . '-01')->startOfMonth();
        $endDate = Carbon::parse($bulanAkhir . '-01')->endOfMonth();

        // Generate list bulan
        $months = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $months[] = $current->format('Y-m');
            $current->addMonth();
        }

        // Users to report
        if ($filterUserId) {
            $users = User::where('id', $filterUserId)->get();
        } else {
            $users = User::role(['admin', 'user'])->get();
        }

        $filterUsername = $filterUserId ? $users->first()?->username : 'Semua Karyawan';

        // Data per bulan
        $laporanBulanan = [];
        $grandTotalKeuntungan = 0;
        $grandTotalPengeluaran = 0;

        foreach ($months as $bulan) {
            $monthStart = Carbon::parse($bulan . '-01')->startOfMonth();
            $monthEnd = Carbon::parse($bulan . '-01')->endOfMonth();

            $bulanData = [
                'label' => Carbon::parse($bulan . '-01')->translatedFormat('F Y'),
                'users' => [],
                'total_keuntungan' => 0,
                'total_pengeluaran' => 0,
            ];

            foreach ($users as $user) {
                $adminTransfer = Transfer::where('user_id', $user->id)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->sum('admin');
                $adminTunai = Tunai::where('user_id', $user->id)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->get()->sum(function ($item) {
                        return is_numeric($item->keterangan) ? (double) $item->keterangan : 0;
                    });

                $keuntungan = $adminTransfer + $adminTunai;
                if ($keuntungan > 0) {
                    $bulanData['users'][] = [
                        'username' => $user->username,
                        'keuntungan' => $keuntungan,
                    ];
                    $bulanData['total_keuntungan'] += $keuntungan;
                }
            }

            // Pengeluaran
            $pengeluaranQuery = Pengeluaran::query();
            if ($filterUserId) {
                $pengeluaranQuery->where('user_id', $filterUserId);
            }
            $pengeluaranBulan = $pengeluaranQuery->latest('bulan')->get()->unique('nama');
            $bulanData['total_pengeluaran'] = $pengeluaranBulan->sum(fn($i) => $i->nominal_bulanan);

            $bulanData['total_bersih'] = $bulanData['total_keuntungan'] - $bulanData['total_pengeluaran'];

            $grandTotalKeuntungan += $bulanData['total_keuntungan'];
            $grandTotalPengeluaran += $bulanData['total_pengeluaran'];

            $laporanBulanan[] = $bulanData;
        }

        $grandTotalBersih = $grandTotalKeuntungan - $grandTotalPengeluaran;

        // Bagi hasil investor
        $investorReport = [];
        $investors = Investor::with('user')->active()->get();
        if ($filterUserId) {
            $investors = $investors->where('user_id', $filterUserId);
        }

        foreach ($investors as $inv) {
            $totalKeuntunganInv = 0;
            foreach ($months as $bulan) {
                $monthStart = Carbon::parse($bulan . '-01')->startOfMonth();
                $monthEnd = Carbon::parse($bulan . '-01')->endOfMonth();

                $adminTransfer = Transfer::where('user_id', $inv->user_id)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->sum('admin');
                $adminTunai = Tunai::where('user_id', $inv->user_id)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->get()->sum(fn($item) => is_numeric($item->keterangan) ? (double) $item->keterangan : 0);

                $totalKeuntunganInv += ($adminTransfer + $adminTunai);
            }

            $pengeluaranInv = Pengeluaran::where('user_id', $inv->user_id)
                ->latest('bulan')->get()->unique('nama');
            $totalPengeluaranInv = $pengeluaranInv->sum(fn($i) => $i->nominal_bulanan) * count($months);

            $bersih = $totalKeuntunganInv - $totalPengeluaranInv;
            $bagiHasil = $totalKeuntunganInv > 0 ? $bersih * ($inv->persentase / 100) : 0;

            $investorReport[] = [
                'username' => $inv->user?->username ?? '-',
                'persentase' => $inv->persentase,
                'keuntungan' => $totalKeuntunganInv,
                'pengeluaran' => $totalPengeluaranInv,
                'bersih' => $bersih,
                'bagi_hasil' => $bagiHasil,
            ];
        }

        $data = [
            'filterUsername' => $filterUsername,
            'bulanMulai' => Carbon::parse($bulanMulai . '-01')->translatedFormat('F Y'),
            'bulanAkhir' => Carbon::parse($bulanAkhir . '-01')->translatedFormat('F Y'),
            'laporanBulanan' => $laporanBulanan,
            'investorReport' => $investorReport,
            'grandTotalKeuntungan' => $grandTotalKeuntungan,
            'grandTotalPengeluaran' => $grandTotalPengeluaran,
            'grandTotalBersih' => $grandTotalBersih,
            'tanggalCetak' => Carbon::now()->translatedFormat('d F Y, H:i'),
        ];

        $pdf = Pdf::loadView('modules.laporan.pdf', $data)->setPaper('a4', 'portrait');
        $filename = 'Laporan_' . $bulanMulai . '_sd_' . $bulanAkhir . '.pdf';

        return $pdf->download($filename);
    }
}
