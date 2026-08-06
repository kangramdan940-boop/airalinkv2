<?php

namespace App\Http\Controllers;

use App\Models\Investor;
use App\Models\Pengeluaran;
use App\Models\User;
use App\Models\Transaksi\Transfer;
use App\Models\Transaksi\Tunai;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvestorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->title = 'Investor';
        $this->breadcrumb = ['Investor' => '#'];
    }

    public function index(Request $request)
    {
        $bulan = $request->get('bulan', Carbon::now()->format('Y-m'));
        $startOfMonth = Carbon::parse($bulan)->startOfMonth();
        $endOfMonth = Carbon::parse($bulan)->endOfMonth();

        $investors = Investor::with('user')->orderBy('persentase', 'desc')->get();
        $users = User::role(['admin', 'user'])->orderBy('username')->get();

        // Hitung keuntungan per user (investor)
        $totalKeuntungan = 0;
        foreach ($investors as $inv) {
            $adminTransfer = Transfer::where('user_id', $inv->user_id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('admin');
            $adminTunai = Tunai::where('user_id', $inv->user_id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->get()->sum(function ($item) {
                    return is_numeric($item->keterangan) ? (double) $item->keterangan : 0;
                });
            $inv->keuntungan_user = $adminTransfer + $adminTunai;

            // Pengeluaran user (tetap per tahun, ambil data terbaru)
            $pengeluaranUser = Pengeluaran::where('user_id', $inv->user_id)
                ->latest('bulan')
                ->get()
                ->unique('nama');
            $inv->pengeluaran_user = $pengeluaranUser->sum(fn($i) => $i->nominal_bulanan);

            // Keuntungan bersih = keuntungan - pengeluaran
            $inv->keuntungan_bersih = $inv->keuntungan_user - $inv->pengeluaran_user;

            $totalKeuntungan += $inv->keuntungan_user;
        }

        return $this->render('modules.investor.index', compact('investors', 'users', 'bulan', 'totalKeuntungan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:sys_users,id|unique:investors,user_id,NULL,id,deleted_at,NULL',
            'persentase' => 'required|numeric|min:0|max:100',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'user_id.unique' => 'User ini sudah terdaftar sebagai investor.',
        ]);

        Investor::create([
            'user_id' => $request->user_id,
            'persentase' => $request->persentase,
            'keterangan' => $request->keterangan,
            'status' => 1,
        ]);

        return response()->json(['status' => true, 'message' => 'Investor berhasil ditambahkan']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'persentase' => 'required|numeric|min:0|max:100',
            'keterangan' => 'nullable|string|max:255',
            'status' => 'required|boolean',
        ]);

        $record = Investor::findOrFail($id);
        $record->update($request->only('persentase', 'keterangan', 'status'));

        return response()->json(['status' => true, 'message' => 'Data berhasil diupdate']);
    }

    public function destroy($id)
    {
        Investor::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Data berhasil dihapus']);
    }
}
