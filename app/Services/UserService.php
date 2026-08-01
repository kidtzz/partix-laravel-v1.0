<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;

class UserService
{
    public function getSemuaUser()
    {
        return User::with('roles')->get()->map(function($u) {
            return [
                'username' => $u->username,
                'nama_lengkap' => $u->name,
                'role' => $u->roles->first()->name ?? 'Guest',
                'status' => str_replace('Non Aktif', 'Nonaktif', $u->status)
            ];
        })->toArray();
    }

    public function tambahUser($payload)
    {
        if (User::where('username', $payload['username'])->exists()) {
            throw new Exception("Username sudah dipakai.");
        }

        DB::beginTransaction();
        try {
            $u = User::create([
                'username' => $payload['username'],
                'name' => $payload['nama_lengkap'],
                'password' => Hash::make($payload['password']),
                'status' => 'Aktif'
            ]);
            $u->assignRole($payload['role'] ?? 'Kasir');
            LogService::log('CREATE', 'Master User', "Tambah user: " . $u->username);
            DB::commit();
            return true;
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateUser($username, $payload)
    {
        $u = User::where('username', $username)->first();
        if (!$u) throw new Exception("User tidak ditemukan.");

        DB::beginTransaction();
        try {
            $updateData = [];
            if (isset($payload['nama_lengkap'])) $updateData['name'] = $payload['nama_lengkap'];
            if (!empty($payload['password'])) $updateData['password'] = Hash::make($payload['password']);
            if (isset($payload['status_user'])) {
                $updateData['status'] = str_replace('Nonaktif', 'Non Aktif', $payload['status_user']);
            } elseif (isset($payload['status'])) {
                $updateData['status'] = str_replace('Nonaktif', 'Non Aktif', $payload['status']);
            }

            $u->update($updateData);

            if (isset($payload['role'])) {
                $u->syncRoles([$payload['role']]);
            }
            LogService::log('UPDATE', 'Master User', "Update user: " . $u->username);
            DB::commit();
            return true;
        } catch(Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function hapusUser($username)
    {
        $u = User::where('username', $username)->first();
        if (!$u) throw new Exception("User tidak ditemukan.");
        if ($u->id === \Illuminate\Support\Facades\Auth::id()) {
            throw new Exception("Tidak bisa menghapus user diri sendiri.");
        }
        $u->delete();
        LogService::log('DELETE', 'Master User', "Hapus permanen user: $username");
        return true;
    }
}
