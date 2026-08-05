<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\PegawaiRequest;
use App\Models\Master\Pegawai;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PegawaiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->link = 'master/pegawai/';
        $this->title = 'Pegawai';
        $this->modalSize = 'mini';
        $this->breadcrumb = ['Data Master' => '#', 'Pegawai' => '#'];
        $this->tableStruct = [
            ['data' => 'num', 'name' => 'num', 'label' => '#', 'orderable' => false, 'searchable' => false, 'className' => 'text-center', 'width' => '40px'],
            ['data' => 'nama', 'name' => 'nama', 'label' => 'Nama Pegawai', 'searchable' => true, 'sortable' => true],
            ['data' => 'telepon', 'name' => 'telepon', 'label' => 'Telepon', 'searchable' => false, 'sortable' => true],
            ['data' => 'status', 'name' => 'status', 'label' => 'Status', 'searchable' => false, 'sortable' => true, 'className' => 'text-center'],
            ['data' => 'keterangan', 'name' => 'keterangan', 'label' => 'Keterangan', 'searchable' => false, 'sortable' => true],
            ['data' => 'created_at', 'name' => 'created_at', 'label' => 'Dibuat', 'searchable' => false, 'sortable' => true, 'width' => '120px'],
            ['data' => 'action', 'name' => 'action', 'label' => 'Aksi', 'searchable' => false, 'sortable' => false, 'className' => 'text-center', 'width' => '100px'],
        ];
    }

    public function grid(Request $request)
    {
        $records = Pegawai::query();

        if (!isset(request()->order[0]['column'])) {
            $records->orderBy('created_at', 'desc');
        }

        if ($nama = $request->nama) {
            $records->where('nama', 'like', "%{$nama}%");
        }

        return DataTables::of($records)
            ->addColumn('num', fn() => '')
            ->editColumn('created_at', fn($r) => $r->created_at?->diffForHumans())
            ->editColumn('status', fn($r) => $r->status == 1
                ? '<span class="badge bg-primary">Aktif</span>'
                : '<span class="badge bg-warning">Probation</span>')
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
        return $this->render('modules.master.pegawai.index');
    }

    public function create()
    {
        return $this->render('modules.master.pegawai.create');
    }

    public function store(PegawaiRequest $request)
    {
        $record = new Pegawai;
        $record->fill($request->validated());
        $record->save();

        return response()->json(['status' => true, 'message' => 'Data berhasil disimpan']);
    }

    public function edit($id)
    {
        $record = Pegawai::findOrFail($id);

        if (request()->ajax()) {
            return response()->json(['record' => $record]);
        }

        return $this->render('modules.master.pegawai.edit', ['record' => $record]);
    }

    public function update(PegawaiRequest $request, $id)
    {
        $record = Pegawai::findOrFail($id);
        $record->fill($request->validated());
        $record->save();

        return response()->json(['status' => true, 'message' => 'Data berhasil diupdate']);
    }

    public function destroy($id)
    {
        Pegawai::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Data berhasil dihapus']);
    }
}
