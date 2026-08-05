<?php

namespace App\Http\Controllers\Konfigurasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Konfigurasi\UsersRequest;
use App\Models\User;
use App\Models\Transaksi\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->link = 'konfigurasi/users/';
        $this->perms = 'konfigurasi-users';
        $this->title = 'Manajemen Pengguna';
        $this->modalSize = 'small';
        $this->breadcrumb = ['Konfigurasi' => '#', 'Manajemen Pengguna' => '#'];
        $this->tableStruct = [
            ['data' => 'num', 'name' => 'num', 'label' => '#', 'orderable' => false, 'searchable' => false, 'className' => 'text-center', 'width' => '40px'],
            ['data' => 'username', 'name' => 'username', 'label' => 'Username', 'sortable' => true],
            ['data' => 'email', 'name' => 'email', 'label' => 'Email', 'sortable' => true],
            ['data' => 'roles', 'name' => 'roles', 'label' => 'Hak Akses', 'sortable' => false],
            ['data' => 'last_login', 'name' => 'last_login', 'label' => 'Login Terakhir', 'sortable' => true],
            ['data' => 'created_at', 'name' => 'created_at', 'label' => 'Dibuat', 'sortable' => true],
            ['data' => 'action', 'name' => 'action', 'label' => 'Aksi', 'searchable' => false, 'sortable' => false, 'className' => 'text-center', 'width' => '120px'],
        ];
    }

    public function grid(Request $request)
    {
        $records = User::with('roles');

        if (!isset(request()->order[0]['column'])) {
            $records->orderBy('created_at', 'desc');
        }

        if ($username = $request->username) {
            $records->where('username', 'like', "%{$username}%");
        }

        return DataTables::of($records)
            ->addColumn('num', fn() => '')
            ->addColumn('roles', fn($r) => $r->roles->pluck('name')->implode(', '))
            ->editColumn('last_login', fn($r) => $r->last_login?->diffForHumans() ?? '-')
            ->editColumn('created_at', fn($r) => $r->created_at?->diffForHumans())
            ->addColumn('action', function ($record) {
                $btn = $this->makeButton(['type' => 'modal', 'id' => $record->id, 'datas' => ['id' => $record->id]]);
                $btn .= '<button type="button" onclick="resetPassword(' . $record->id . ')" class="btn btn-sm btn-outline-primary" title="Reset Password"><i class="bi bi-key"></i></button> ';
                $btn .= $this->makeButton(['type' => 'delete', 'id' => $record->id, 'url' => url($this->link . $record->id)]);
                return $btn;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function index()
    {
        return $this->render('modules.konfigurasi.users.index');
    }

    public function create()
    {
        $roles = Role::all();
        return $this->render('modules.konfigurasi.users.create', compact('roles'));
    }

    public function store(UsersRequest $request)
    {
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'last_login' => Carbon::now(),
            'status' => 1,
        ]);

        if ($request->roles) {
            $user->syncRoles($request->roles);
        }

        return response()->json(['status' => true, 'message' => 'User berhasil dibuat']);
    }

    public function edit($id)
    {
        $record = User::with('roles')->findOrFail($id);
        $limit = Limit::where('user_id', $id)->first();

        if (request()->ajax()) {
            return response()->json(['record' => $record, 'limit' => $limit]);
        }

        $roles = Role::all();
        return $this->render('modules.konfigurasi.users.edit', compact('record', 'roles', 'limit'));
    }

    public function update(UsersRequest $request, $id)
    {
        $record = User::findOrFail($id);
        $record->username = $request->username;
        $record->email = $request->email;

        // Password change logic
        if ($request->filled('password_lama')) {
            if (!Hash::check($request->password_lama, $record->password)) {
                return response()->json([
                    'message' => 'Password lama tidak sesuai',
                    'errors' => ['password_lama' => ['Password lama tidak sesuai']],
                ], 422);
            }
        }

        if ($request->filled('password') && $request->password === $request->confirm_password) {
            $record->password = $request->password;
        }

        $record->save();

        if ($request->roles) {
            $record->syncRoles($request->roles);
        }

        // Update limit harian
        if ($request->filled('limit')) {
            Limit::updateOrCreate(
                ['user_id' => $id],
                ['limit' => $request->limit]
            );
        }

        return response()->json(['status' => true, 'message' => 'User berhasil diupdate']);
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return response()->json(['status' => true, 'message' => 'User berhasil dihapus']);
    }

    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|string|min:6',
        ]);

        $record = User::findOrFail($id);
        $record->password = $request->new_password;
        $record->save();

        return response()->json(['status' => true, 'message' => 'Password ' . $record->username . ' berhasil direset']);
    }
}
