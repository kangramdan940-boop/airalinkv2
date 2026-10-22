<?php

namespace App\Http\Controllers;

use App\Models\PengeluaranHarian;
use Illuminate\Http\Request;

class PengeluaranHarianController extends Controller
{
    private const PIN_INPUT = '222222';
    private const PIN_ADMIN = '444444';
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 60;

    public function index()
    {
        return view('pengeluaran-harian.index', [
            'role' => session('ph_role'),
        ]);
    }

    public function verifyPin(Request $request)
    {
        if ($this->isLockedOut()) {
            return response()->json([
                'status' => false,
                'locked' => true,
                'message' => 'Terlalu banyak percobaan. Coba lagi ' . self::LOCK_SECONDS . ' detik lagi.',
            ], 429);
        }

        $pin = (string) $request->get('pin', '');

        if ($pin === self::PIN_INPUT) {
            $this->login($request, 'input');

            return response()->json(['status' => true, 'role' => 'input']);
        }

        if ($pin === self::PIN_ADMIN) {
            $this->login($request, 'admin');

            return response()->json(['status' => true, 'role' => 'admin']);
        }

        $attempts = (int) session('ph_attempts', 0) + 1;
        session(['ph_attempts' => $attempts, 'ph_last_attempt' => now()->timestamp]);

        return response()->json([
            'status' => false,
            'message' => 'PIN salah. Sisa percobaan: ' . max(0, self::MAX_ATTEMPTS - $attempts),
        ], 422);
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['ph_role', 'ph_attempts', 'ph_last_attempt']);

        return response()->json(['status' => true]);
    }

    public function data(Request $request)
    {
        $this->requireAuth();

        $tanggal = $this->today();
        if (session('ph_role') === 'admin' && $request->filled('tanggal')) {
            $tanggal = $request->get('tanggal');
        }

        $items = PengeluaranHarian::where('tanggal', $tanggal)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status' => true,
            'tanggal' => $tanggal,
            'total' => (float) $items->sum('nominal'),
            'data' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $this->requireAuth();

        $rules = [
            'nama' => 'required|string|max:100',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string|max:255',
        ];

        if (session('ph_role') === 'admin') {
            $rules['tanggal'] = 'required|date';
        }

        $data = $request->validate($rules);
        $data['tanggal'] = session('ph_role') === 'admin' ? $data['tanggal'] : $this->today();

        $item = PengeluaranHarian::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Pengeluaran berhasil dicatat',
            'data' => $item,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->requireAdmin();

        $item = PengeluaranHarian::findOrFail($id);

        $data = $request->validate([
            'nama' => 'required|string|max:100',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $item->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Pengeluaran berhasil diupdate',
            'data' => $item->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $this->requireAdmin();

        PengeluaranHarian::findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Pengeluaran berhasil dihapus']);
    }

    private function login(Request $request, string $role): void
    {
        $request->session()->forget(['ph_attempts', 'ph_last_attempt']);
        session(['ph_role' => $role]);
    }

    private function today(): string
    {
        return now('Asia/Jakarta')->format('Y-m-d');
    }

    private function requireAuth(): void
    {
        if (!session('ph_role')) {
            abort(401, 'Belum masuk PIN');
        }
    }

    private function requireAdmin(): void
    {
        $this->requireAuth();

        if (session('ph_role') !== 'admin') {
            abort(403, 'Akses khusus mode admin');
        }
    }

    private function isLockedOut(): bool
    {
        $attempts = (int) session('ph_attempts', 0);
        $last = (int) session('ph_last_attempt', 0);

        return $attempts >= self::MAX_ATTEMPTS
            && $last > 0
            && (now()->timestamp - $last) < self::LOCK_SECONDS;
    }
}
