<?php

namespace App\Http\Controllers;

use App\Models\Transaksi\UangCashMonitoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class UangCashMonitoringController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->link = 'uang-cash-monitoring/';
        $this->title = 'Uang Cash Monitoring';
        $this->breadcrumb = ['Uang Cash Monitoring' => '#'];
        $this->tableStruct = [
            ['data' => 'num', 'name' => 'num', 'label' => '#', 'orderable' => false, 'searchable' => false, 'className' => 'text-center', 'width' => '40px'],
            ['data' => 'username', 'name' => 'username', 'label' => 'Karyawan', 'sortable' => true],
            ['data' => 'nominal', 'name' => 'nominal', 'label' => 'Nominal', 'className' => 'text-end', 'sortable' => true],
            ['data' => 'cabang_toko', 'name' => 'cabang_toko', 'label' => 'Cabang', 'sortable' => true],
            ['data' => 'whatsapp', 'name' => 'whatsapp', 'label' => 'WhatsApp', 'sortable' => true],
            ['data' => 'total', 'name' => 'total', 'label' => 'Total', 'className' => 'text-end', 'sortable' => true],
            ['data' => 'keterangan', 'name' => 'keterangan', 'label' => 'Keterangan', 'sortable' => true],
            ['data' => 'created_at', 'name' => 'created_at', 'label' => 'Dibuat', 'sortable' => true, 'width' => '120px'],
            ['data' => 'action', 'name' => 'action', 'label' => 'Aksi', 'searchable' => false, 'sortable' => false, 'className' => 'text-center', 'width' => '100px'],
        ];
    }

    public function grid(Request $request)
    {
        $records = UangCashMonitoring::with('user');

        if (!isset(request()->order[0]['column'])) {
            $records->orderBy('created_at', 'desc');
        }

        return DataTables::of($records)
            ->addColumn('num', fn() => '')
            ->addColumn('username', fn($r) => $r->user?->username ?? '-')
            ->editColumn('nominal', fn($r) => 'Rp ' . number_format($r->nominal, 0, ',', '.'))
            ->editColumn('total', fn($r) => 'Rp ' . number_format($r->total, 0, ',', '.'))
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
        return $this->render('modules.uang-cash-monitoring.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:sys_users,id',
            'nominal' => 'required|numeric|min:1',
            'cabang_toko' => 'required|string|max:100',
            'total' => 'required|integer|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $record = new UangCashMonitoring;
        $record->user_id = $request->user_id;
        $record->nominal = $request->nominal;
        $record->cabang_toko = $request->cabang_toko;
        $record->total = $request->total;
        $record->keterangan = $request->keterangan;
        $record->save();

        return response()->json(['status' => true, 'message' => 'Data monitoring berhasil disimpan']);
    }

    public function destroy($id)
    {
        UangCashMonitoring::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Data berhasil dihapus']);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'total' => 'required|integer|min:0',
        ]);

        $record = UangCashMonitoring::findOrFail($id);
        $record->total = $request->total;
        $record->save();

        return response()->json(['status' => true, 'message' => 'Data berhasil diupdate']);
    }
}
