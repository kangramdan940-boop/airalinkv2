<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaksi\TransferRequest;
use App\Models\Transaksi\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class TransferController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->link = 'transaksi/transfer/';
        $this->title = 'Transfer Bank';
        $this->modalSize = 'small';
        $this->breadcrumb = ['Transaksi' => '#', 'Transfer' => '#'];
        $this->tableStruct = [
            ['data' => 'num', 'name' => 'num', 'label' => '#', 'orderable' => false, 'searchable' => false, 'className' => 'text-center', 'width' => '40px'],
            ['data' => 'username', 'name' => 'username', 'label' => 'Karyawan', 'searchable' => true, 'sortable' => true],
            ['data' => 'kode_bank', 'name' => 'kode_bank', 'label' => 'Bank', 'searchable' => true, 'sortable' => true],
            ['data' => 'no_rekening', 'name' => 'no_rekening', 'label' => 'No Rek', 'sortable' => true],
            ['data' => 'atas_nama', 'name' => 'atas_nama', 'label' => 'Atas Nama', 'sortable' => true],
            ['data' => 'nominal', 'name' => 'nominal', 'label' => 'Nominal', 'className' => 'text-end', 'sortable' => true],
            ['data' => 'cabang_toko', 'name' => 'cabang_toko', 'label' => 'Cabang', 'className' => 'text-center', 'sortable' => true],
            ['data' => 'status', 'name' => 'status', 'label' => 'Status', 'className' => 'text-center', 'sortable' => true],
            ['data' => 'created_at', 'name' => 'created_at', 'label' => 'Dibuat', 'sortable' => true, 'width' => '120px'],
            ['data' => 'action', 'name' => 'action', 'label' => 'Aksi', 'searchable' => false, 'sortable' => false, 'className' => 'text-center', 'width' => '150px'],
        ];
    }

    public function grid(Request $request)
    {
        $records = Transfer::with('user');

        if (!isset(request()->order[0]['column'])) {
            $records->orderBy('created_at', 'desc');
        }

        // Date filter
        if ($request->start_date) {
            $records->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $records->whereDate('created_at', '<=', $request->end_date);
        }

        if ($atas_nama = $request->atas_nama) {
            $records->where('atas_nama', 'like', "%{$atas_nama}%");
        }

        return DataTables::of($records)
            ->addColumn('num', fn($r) => $r->id)
            ->addColumn('username', fn($r) => $r->user?->username ?? '-')
            ->editColumn('nominal', fn($r) => 'Rp ' . number_format($r->nominal, 0, ',', '.'))
            ->editColumn('status', fn($r) => $r->status_label)
            ->editColumn('created_at', fn($r) => $r->created_at?->diffForHumans())
            ->addColumn('action', function ($record) {
                $btn = $this->makeButton(['type' => 'confirm', 'id' => $record->id, 'url' => url($this->link . $record->id)]);
                $btn .= $this->makeButton(['type' => 'modal', 'id' => $record->id, 'datas' => ['id' => $record->id]]);
                $btn .= $this->makeButton(['type' => 'delete', 'id' => $record->id, 'url' => url($this->link . $record->id)]);
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function index()
    {
        return $this->render('modules.transaksi.transfer.index');
    }

    public function create()
    {
        return $this->render('modules.transaksi.transfer.create');
    }

    public function store(TransferRequest $request)
    {
        $record = new Transfer;
        $record->fill($request->validated());
        $record->user_id = Auth::id();
        $record->status = Transfer::STATUS_REQUEST;
        $record->save();

        return response()->json(['status' => true, 'message' => 'Transfer berhasil dibuat']);
    }

    public function edit($id)
    {
        $record = Transfer::findOrFail($id);
        return $this->render('modules.transaksi.transfer.edit', ['record' => $record]);
    }

    public function update(TransferRequest $request, $id)
    {
        $record = Transfer::findOrFail($id);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/transfer/' . date('Y-m')), $filename);
            $record->file = 'uploads/transfer/' . date('Y-m') . '/' . $filename;
        }

        $record->fill($request->validated());
        $record->save();

        return response()->json(['status' => true, 'message' => 'Transfer berhasil diupdate']);
    }

    public function destroy($id)
    {
        Transfer::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Data berhasil dihapus']);
    }
}
