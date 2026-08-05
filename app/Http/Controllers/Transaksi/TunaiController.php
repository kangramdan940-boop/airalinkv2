<?php

namespace App\Http\Controllers\Transaksi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaksi\TunaiRequest;
use App\Models\Transaksi\Tunai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class TunaiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->link = 'transaksi/tarik-tunai/';
        $this->title = 'Tarik Tunai';
        $this->modalSize = 'small';
        $this->breadcrumb = ['Transaksi' => '#', 'Tarik Tunai' => '#'];
        $this->tableStruct = [
            ['data' => 'num', 'name' => 'num', 'label' => '#', 'orderable' => false, 'searchable' => false, 'className' => 'text-center', 'width' => '40px'],
            ['data' => 'username', 'name' => 'username', 'label' => 'Karyawan', 'searchable' => true, 'sortable' => true],
            ['data' => 'kode_bank', 'name' => 'kode_bank', 'label' => 'Bank', 'searchable' => true, 'sortable' => true],
            ['data' => 'no_rekening', 'name' => 'no_rekening', 'label' => 'No Rek', 'sortable' => true],
            ['data' => 'atas_nama', 'name' => 'atas_nama', 'label' => 'Atas Nama', 'sortable' => true],
            ['data' => 'nominal', 'name' => 'nominal', 'label' => 'Nominal', 'className' => 'text-end', 'sortable' => true],
            ['data' => 'cabang_toko', 'name' => 'cabang_toko', 'label' => 'Cabang', 'className' => 'text-center', 'sortable' => true],
            ['data' => 'status', 'name' => 'status', 'label' => 'Status / Bukti', 'className' => 'text-center', 'sortable' => true],
            ['data' => 'created_at', 'name' => 'created_at', 'label' => 'Dibuat', 'sortable' => true, 'width' => '120px'],
            ['data' => 'action', 'name' => 'action', 'label' => 'Aksi', 'searchable' => false, 'sortable' => false, 'className' => 'text-center', 'width' => '150px'],
        ];
    }

    public function grid(Request $request)
    {
        $records = Tunai::with('user');

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
            ->editColumn('status', function ($r) {
                if ($r->status == 1 && $r->file) {
                    $url = asset($r->file);
                    return '<a href="' . $url . '" target="_blank" class="badge bg-info text-decoration-none">Bukti Tf</a>';
                }
                return '<span class="badge bg-warning">-</span>';
            })
            ->editColumn('created_at', fn($r) => $r->created_at?->diffForHumans())
            ->addColumn('action', function ($record) {
                $btn = $this->makeButton(['type' => 'modal', 'id' => $record->id, 'datas' => ['id' => $record->id]]);
                $btn .= $this->makeButton(['type' => 'delete', 'id' => $record->id, 'url' => url($this->link . $record->id)]);
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function index()
    {
        return $this->render('modules.transaksi.tunai.index');
    }

    public function create()
    {
        return $this->render('modules.transaksi.tunai.create');
    }

    public function store(TunaiRequest $request)
    {
        $record = new Tunai;
        $record->fill($request->validated());
        $record->user_id = Auth::id();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/tunai/' . date('Y-m')), $filename);
            $record->file = 'uploads/tunai/' . date('Y-m') . '/' . $filename;
            $record->status = 1;
        }

        $record->save();

        return response()->json(['status' => true, 'message' => 'Tarik tunai berhasil dibuat']);
    }

    public function edit($id)
    {
        $record = Tunai::findOrFail($id);
        return $this->render('modules.transaksi.tunai.edit', ['record' => $record]);
    }

    public function update(TunaiRequest $request, $id)
    {
        $record = Tunai::findOrFail($id);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/tunai/' . date('Y-m')), $filename);
            $record->file = 'uploads/tunai/' . date('Y-m') . '/' . $filename;
            $record->status = 1;
        }

        $record->fill($request->validated());
        $record->save();

        return response()->json(['status' => true, 'message' => 'Data berhasil diupdate']);
    }

    public function destroy($id)
    {
        Tunai::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Data berhasil dihapus']);
    }
}
