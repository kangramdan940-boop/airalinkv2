<?php

namespace App\Http\Controllers;

use App\Models\RekeningValid;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RekeningValidController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->link = 'rekening-valid/';
        $this->title = 'Rekening Valid';
        $this->breadcrumb = ['Rekening Valid' => '#'];
        $this->tableStruct = [
            ['data' => 'num', 'name' => 'num', 'label' => '#', 'orderable' => false, 'searchable' => false, 'className' => 'text-center', 'width' => '40px'],
            ['data' => 'no_rekening', 'name' => 'no_rekening', 'label' => 'No Rekening', 'searchable' => true, 'sortable' => true],
            ['data' => 'atas_nama', 'name' => 'atas_nama', 'label' => 'Atas Nama', 'searchable' => true, 'sortable' => true],
            ['data' => 'kode_bank', 'name' => 'kode_bank', 'label' => 'Kode Bank', 'sortable' => true],
            ['data' => 'nama_bank', 'name' => 'nama_bank', 'label' => 'Nama Bank', 'sortable' => true],
            ['data' => 'total_trx', 'name' => 'total_trx', 'label' => 'Total Trx', 'className' => 'text-center', 'sortable' => true, 'width' => '80px'],
            ['data' => 'action', 'name' => 'action', 'label' => 'Aksi', 'searchable' => false, 'sortable' => false, 'className' => 'text-center', 'width' => '100px'],
        ];
    }

    public function grid(Request $request)
    {
        $records = RekeningValid::query();

        if (!isset(request()->order[0]['column'])) {
            $records->orderBy('total_trx', 'desc');
        }

        return DataTables::of($records)
            ->addColumn('num', fn($r) => $r->id)
            ->addColumn('action', function ($record) {
                $btn = '<div style="display:inline-flex;gap:4px;">';
                $btn .= '<button type="button" class="btn btn-sm btn-info btn-edit" data-id="' . $record->id . '" title="Edit"><i class="bi bi-pencil"></i></button>';
                $btn .= '<button type="button" class="btn btn-sm btn-danger" onclick="deleteData(\'' . url($this->link . $record->id) . '\')" title="Hapus"><i class="bi bi-trash3"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function index()
    {
        return $this->render('modules.rekening-valid.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'no_rekening' => 'required|string',
            'atas_nama' => 'required|string',
            'kode_bank' => 'required|string',
        ]);

        RekeningValid::create([
            'no_rekening' => $request->no_rekening,
            'atas_nama' => strtoupper($request->atas_nama),
            'kode_bank' => $request->kode_bank,
            'nama_bank' => $request->nama_bank,
            'total_trx' => 0,
        ]);

        return response()->json(['status' => true, 'message' => 'Rekening berhasil ditambahkan']);
    }

    public function edit($id)
    {
        $record = RekeningValid::findOrFail($id);
        return response()->json(['record' => $record]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'no_rekening' => 'required|string',
            'atas_nama' => 'required|string',
            'kode_bank' => 'required|string',
        ]);

        $record = RekeningValid::findOrFail($id);
        $record->update([
            'no_rekening' => $request->no_rekening,
            'atas_nama' => strtoupper($request->atas_nama),
            'kode_bank' => $request->kode_bank,
            'nama_bank' => $request->nama_bank,
        ]);

        return response()->json(['status' => true, 'message' => 'Rekening berhasil diupdate']);
    }

    public function destroy($id)
    {
        RekeningValid::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Rekening berhasil dihapus']);
    }

    // API untuk lookup by no_rekening (dipakai di form transfer)
    public function lookup(Request $request)
    {
        $noRek = $request->no_rekening;
        $bank = $request->kode_bank;

        $record = RekeningValid::where('no_rekening', $noRek)
            ->when($bank, fn($q) => $q->where('kode_bank', 'like', "%{$bank}%"))
            ->first();

        if ($record) {
            return response()->json(['found' => true, 'atas_nama' => $record->atas_nama]);
        }

        return response()->json(['found' => false]);
    }

    // API untuk Select2 AJAX search
    public function lookupSearch(Request $request)
    {
        $q = $request->q;

        if (!$q || strlen($q) < 5) {
            return response()->json([]);
        }

        $results = RekeningValid::where('no_rekening', 'like', "%{$q}%")
            ->orWhere('atas_nama', 'like', "%{$q}%")
            ->orderBy('total_trx', 'desc')
            ->limit(10)
            ->get()
            ->map(fn($r) => [
                'id' => $r->no_rekening,
                'text' => $r->no_rekening,
                'atas_nama' => $r->atas_nama,
                'nama' => $r->atas_nama . ' (' . ($r->nama_bank ?? $r->kode_bank) . ')',
            ]);

        return response()->json($results);
    }
}
