<?php

namespace App\Http\Controllers;

use App\Models\Transaksi\UangCash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class UangCashController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->link = 'uang-cash/';
        $this->title = 'Uang Cash';
        $this->breadcrumb = ['Uang Cash' => '#'];
        $this->tableStruct = [
            ['data' => 'num', 'name' => 'num', 'label' => '#', 'orderable' => false, 'searchable' => false, 'className' => 'text-center', 'width' => '40px'],
            ['data' => 'username', 'name' => 'username', 'label' => 'Karyawan', 'sortable' => true],
            ['data' => 'nominal', 'name' => 'nominal', 'label' => 'Nominal', 'className' => 'text-end', 'sortable' => true],
            ['data' => 'cabang_toko', 'name' => 'cabang_toko', 'label' => 'Cabang', 'sortable' => true],
            ['data' => 'whatsapp', 'name' => 'whatsapp', 'label' => 'WhatsApp', 'sortable' => true],
            ['data' => 'keterangan', 'name' => 'keterangan', 'label' => 'Keterangan', 'sortable' => true],
            ['data' => 'created_at', 'name' => 'created_at', 'label' => 'Dibuat', 'sortable' => true, 'width' => '120px'],
            ['data' => 'action', 'name' => 'action', 'label' => 'Aksi', 'searchable' => false, 'sortable' => false, 'className' => 'text-center', 'width' => '100px'],
        ];
    }

    public function grid(Request $request)
    {
        $records = UangCash::with('user');

        if (!isset(request()->order[0]['column'])) {
            $records->orderBy('created_at', 'desc');
        }

        return DataTables::of($records)
            ->addColumn('num', fn() => '')
            ->addColumn('username', fn($r) => $r->user?->username ?? '-')
            ->editColumn('nominal', fn($r) => 'Rp ' . number_format($r->nominal, 0, ',', '.'))
            ->editColumn('created_at', fn($r) => $r->created_at?->diffForHumans())
            ->addColumn('action', function ($record) {
                $btn = $this->makeButton(['type' => 'modal', 'id' => $record->id, 'datas' => ['id' => $record->id]]);
                $btn .= $this->makeButton(['type' => 'delete', 'id' => $record->id, 'url' => url($this->link . $record->id)]);
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function index()
    {
        return $this->render('modules.uang-cash.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:1',
            'total' => 'required|integer|min:0',
            'cabang_toko' => 'nullable|string|max:100',
        ]);

        $record = new UangCash;
        $record->user_id = Auth::id();
        $record->nominal = $request->nominal;
        $record->total = $request->total;
        $record->cabang_toko = $request->cabang_toko;
        $record->save();

        return response()->json(['status' => true, 'message' => 'Data cash berhasil disimpan']);
    }

    public function destroy($id)
    {
        UangCash::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Data berhasil dihapus']);
    }
}
