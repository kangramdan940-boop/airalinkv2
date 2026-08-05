<?php

namespace App\Http\Controllers;

use App\Models\Transaksi\Transfer;
use App\Models\Transaksi\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TransferFormController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->link = 'transfer';
        $this->title = 'Transfer';
        $this->breadcrumb = ['Transfer' => '#'];
    }

    public function index()
    {
        $userId = Auth::id();
        $records = Transfer::where('user_id', $userId)
            ->whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get();

        $limitData = Limit::where('user_id', $userId)->first();
        $total = $records->sum('nominal');
        $limitHarian = $limitData ? (double) $limitData->limit : 0;
        $sisaLimit = $limitHarian - $total;

        return $this->render('modules.transfer-form', compact('records', 'total', 'limitHarian', 'sisaLimit'));
    }

    public function store(Request $request)
    {
        // Strip format koma dari nominal dan admin
        $request->merge([
            'nominal' => str_replace(',', '', $request->nominal),
            'admin' => str_replace(',', '', $request->admin),
        ]);

        $request->validate([
            'kode_bank' => 'required|string',
            'no_rekening' => 'required|string',
            'atas_nama' => 'required|string',
            'nominal' => 'required|numeric|min:1',
            'admin' => 'required|numeric|min:0',
            'keterangan' => 'required|string',
        ]);

        $userId = Auth::id();

        $record = new Transfer;
        $record->user_id = $userId;
        $record->kode_bank = $request->kode_bank;
        $record->no_rekening = $request->no_rekening;
        $record->atas_nama = strtoupper($request->atas_nama);
        $record->nominal = $request->nominal;
        $record->admin = $request->admin;
        $record->cabang_toko = Auth::user()->username;
        $record->status = 1;
        $record->keterangan = $request->keterangan;
        $record->whatsapp = $request->whatsapp;
        $record->save();

        return response()->json(['status' => true, 'message' => 'Transfer berhasil dibuat']);
    }
}
