<?php

namespace App\Http\Controllers\Konfigurasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Konfigurasi\RolesRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->link = 'konfigurasi/roles/';
        $this->title = 'Hak Akses';
        $this->modalSize = 'mini';
        $this->breadcrumb = ['Konfigurasi' => '#', 'Hak Akses' => '#'];
        $this->tableStruct = [
            ['data' => 'num', 'name' => 'num', 'label' => '#', 'orderable' => false, 'searchable' => false, 'className' => 'text-center', 'width' => '40px'],
            ['data' => 'name', 'name' => 'name', 'label' => 'Nama Role', 'sortable' => true],
            ['data' => 'users_count', 'name' => 'users_count', 'label' => 'Jumlah User', 'className' => 'text-center', 'sortable' => true, 'width' => '120px'],
            ['data' => 'permissions_count', 'name' => 'permissions_count', 'label' => 'Jumlah Akses', 'className' => 'text-center', 'sortable' => true, 'width' => '120px'],
            ['data' => 'created_at', 'name' => 'created_at', 'label' => 'Dibuat', 'sortable' => true, 'width' => '120px'],
            ['data' => 'action', 'name' => 'action', 'label' => 'Aksi', 'searchable' => false, 'sortable' => false, 'className' => 'text-center', 'width' => '150px'],
        ];
    }

    public function grid(Request $request)
    {
        $records = Role::withCount(['users', 'permissions']);

        if (!isset(request()->order[0]['column'])) {
            $records->orderBy('created_at', 'desc');
        }

        if ($name = $request->name) {
            $records->where('name', 'like', "%{$name}%");
        }

        return DataTables::of($records)
            ->addColumn('num', fn() => '')
            ->editColumn('created_at', fn($r) => $r->created_at?->diffForHumans())
            ->addColumn('action', function ($record) {
                $btn = $this->makeButton(['type' => 'detail', 'id' => $record->id, 'url' => url($this->link . $record->id)]);
                $btn .= $this->makeButton(['type' => 'modal', 'id' => $record->id, 'datas' => ['id' => $record->id]]);
                $btn .= $this->makeButton(['type' => 'delete', 'id' => $record->id, 'url' => url($this->link . $record->id)]);
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function index()
    {
        return $this->render('modules.konfigurasi.roles.index');
    }

    public function create()
    {
        return $this->render('modules.konfigurasi.roles.create');
    }

    public function store(RolesRequest $request)
    {
        Role::create(['name' => $request->name, 'guard_name' => 'web']);
        return response()->json(['status' => true, 'message' => 'Role berhasil dibuat']);
    }

    public function show($id)
    {
        $record = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all()->groupBy(function ($perm) {
            $parts = explode('-', $perm->name);
            array_pop($parts); // remove action (view/add/edit/delete)
            return implode('-', $parts);
        });

        return $this->render('modules.konfigurasi.roles.detail', compact('record', 'permissions'));
    }

    public function edit($id)
    {
        $record = Role::findOrFail($id);
        return $this->render('modules.konfigurasi.roles.edit', compact('record'));
    }

    public function update(RolesRequest $request, $id)
    {
        $record = Role::findOrFail($id);
        $record->update(['name' => $request->name]);

        return response()->json(['status' => true, 'message' => 'Role berhasil diupdate']);
    }

    public function grant(Request $request, $id)
    {
        $record = Role::findOrFail($id);
        $record->syncPermissions($request->check ?? []);

        return response()->json(['status' => true, 'message' => 'Permissions berhasil diupdate']);
    }

    public function destroy($id)
    {
        Role::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'Role berhasil dihapus']);
    }
}
