<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use App\Models\Transaksi\Transfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->title = 'Pengeluaran Bulanan';
        $this->breadcrumb = ['Pengeluaran' => '#'];
    }

    public function index(Request $request)
    {
        $bulan = $request->get('bulan', Carbon::now()->format('Y-m'));
        $userId = $request->get('user_id');

        $users = User::role(['admin', 'user'])->orderBy('username')->get();

        $query = Pengeluaran::where('bulan', $bulan);
        if ($userId) {
            $query->where('user_id', $userId);
        }
        $pengeluaran = $query->orderBy('user_id')->orderBy('nama')->get();

        // Jika filter per user dan belum ada data, generate preset
        if ($userId && $pengeluaran->isEmpty()) {
            foreach (Pengeluaran::PRESETS as $preset) {
                Pengeluaran::create([
                    'user_id' => $userId,
                    'nama' => $preset['nama'],
                    'nominal' => $preset['nominal'],
                    'tipe' => $preset['tipe'],
                    'bulan' => $bulan,
                ]);
            }
            $pengeluaran = Pengeluaran::where('bulan', $bulan)
                ->where('user_id', $userId)
                ->orderBy('nama')
                ->get();
        }

        $totalBulanan = $pengeluaran->sum(fn($i) => $i->nominal_bulanan);

        // Group by user
        $pengeluaranPerUser = $pengeluaran->groupBy('user_id');

        return $this->render('modules.pengeluaran.index', compact('pengeluaran', 'pengeluaranPerUser', 'bulan', 'totalBulanan', 'users', 'userId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:sys_users,id',
            'nama' => 'required|string|max:100',
            'nominal' => 'required|numeric|min:0',
            'tipe' => 'required|in:bulanan,harian',
            'bulan' => 'required|string',
            'keterangan' => 'nullable|string|max:255',
        ]);

        Pengeluaran::create($request->only('user_id', 'nama', 'nominal', 'tipe', 'bulan', 'keterangan'));

        return response()->json(['status' => true, 'message' => 'Pengeluaran berhasil ditambahkan']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $record = Pengeluaran::findOrFail($id);
        $record->update($request->only('nominal', 'keterangan'));

        return response()->json(['status' => true, 'message' => 'Data berhasil diupdate']);
    }

    public function destroy($id)
    {
        Pengeluaran::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Data berhasil dihapus']);
    }

    public function hitungAdmin(Request $request)
    {
        $request->validate([
            'pengeluaran_id' => 'required|exists:pengeluaran,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'excluded_banks' => 'nullable|array',
        ]);

        $pengeluaran = Pengeluaran::findOrFail($request->pengeluaran_id);
        $userId = $pengeluaran->user_id;

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // Excluded banks dari checklist user
        $excludedBanks = $request->get('excluded_banks', []);

        // Hitung transfer selain BCA dan selain bank yang di-exclude
        $query = Transfer::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('kode_bank', '!=', '014-BCA');

        if (!empty($excludedBanks)) {
            $query->whereNotIn('kode_bank', $excludedBanks);
        }

        $jumlahTransfer = $query->count();
        $biayaAdmin = $jumlahTransfer * 2500;

        // Update nominal pengeluaran
        $pengeluaran->update([
            'nominal' => $biayaAdmin,
            'tipe' => 'bulanan',
            'keterangan' => $jumlahTransfer . ' trx × Rp 2.500 (' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y') . ')',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Biaya admin berhasil dihitung',
            'jumlah_transfer' => $jumlahTransfer,
            'biaya_admin' => $biayaAdmin,
            'keterangan' => $pengeluaran->keterangan,
        ]);
    }

    /**
     * Get list of banks with transaction count for the period (for exclude checklist)
     */
    public function getBanks(Request $request)
    {
        $request->validate([
            'pengeluaran_id' => 'required|exists:pengeluaran,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $pengeluaran = Pengeluaran::findOrFail($request->pengeluaran_id);
        $userId = $pengeluaran->user_id;

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // Get semua bank yang ada transaksi (selain BCA)
        $banks = Transfer::where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('kode_bank', '!=', '014-BCA')
            ->selectRaw('kode_bank, COUNT(*) as total_trx')
            ->groupBy('kode_bank')
            ->orderBy('total_trx', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'banks' => $banks,
            'total_trx' => $banks->sum('total_trx'),
        ]);
    }
}
