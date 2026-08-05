<?php

namespace App\Http\Controllers;

use App\Models\Transaksi\Tunai;
use App\Models\Transaksi\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TunaiFormController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->link = 'tunai';
        $this->title = 'Tunai';
        $this->breadcrumb = ['Tunai' => '#'];
    }

    public function index()
    {
        $userId = Auth::id();
        $records = Tunai::where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get();

        $limitData = Limit::where('user_id', $userId)->first();
        $total = $records->sum('nominal');
        $limitHarian = $limitData ? (double) $limitData->limit : 0;
        $sisaLimit = $limitHarian - $total;

        return $this->render('modules.tunai-form', compact('records', 'total', 'limitHarian', 'sisaLimit'));
    }

    public function store(Request $request)
    {
        // Strip format koma
        $request->merge([
            'nominal' => str_replace(',', '', $request->nominal),
            'admin' => str_replace(',', '', $request->admin),
        ]);

        $request->validate([
            'kode_bank' => 'required|string',
            'atas_nama' => 'nullable|string',
            'nominal' => 'required|numeric|min:1',
            'admin' => 'required|numeric|min:0',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $userId = Auth::id();

        $record = new Tunai;
        $record->user_id = $userId;
        $record->kode_bank = $request->kode_bank;
        $record->no_rekening = $request->kode_bank;
        $record->atas_nama = strtoupper($request->atas_nama ?? '');
        $record->nominal = $request->nominal;
        $record->admin = $request->admin;
        $record->cabang_toko = Auth::user()->username;
        $record->status = 3;
        $record->keterangan = $request->admin; // legacy: keterangan = admin fee

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/tunai/' . date('Y-m')), $filename);
            $record->file = 'uploads/tunai/' . date('Y-m') . '/' . $filename;
        }

        $record->save();

        return response()->json(['status' => true, 'message' => 'Tarik tunai berhasil disimpan']);
    }
}
