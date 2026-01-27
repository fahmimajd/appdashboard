<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WilayahKecamatan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use DatabaseTransactions;

    // No setup needed for this simple test

    public function test_admin_can_filter_users_by_role_petugas()
    {
        $admin = User::create([
            'nik' => '1111111111111111',
            'nama' => 'Admin Test',
            'akses' => 'Admin',
            'status_aktif' => 'Aktif',
            'password' => bcrypt('password')
        ]);

        $petugas = User::create([
            'nik' => '2222222222222222',
            'nama' => 'Petugas AAA',
            'akses' => 'Petugas',
            'status_aktif' => 'Aktif',
            'password' => bcrypt('password')
        ]);

        $pendamping = User::create([
            'nik' => '3333333333333333',
            'nama' => 'Pendamping BBB',
            'akses' => 'Pendamping',
            'status_aktif' => 'Aktif',
            'password' => bcrypt('password')
        ]);

        // Verify user exists in DB
        $this->assertDatabaseHas('users', ['nik' => '2222222222222222', 'akses' => 'Petugas']);

        $response = $this->actingAs($admin)->get(route('users.index', ['akses' => 'Petugas', 'search' => 'Petugas AAA']));

        $response->assertStatus(200);
        $response->assertSee('Petugas AAA');
        $response->assertDontSee('Pendamping BBB');
        
        // Verify filter dropdown has Petugas selected
        $response->assertSee('value="Petugas" selected', false);
    }
    public function test_petugas_shows_desa_name_in_user_management()
    {
        // Require Desa data
        \App\Models\WilayahDesa::create([
            'kode_desa' => '3301012001', 
            'nama_desa' => 'Desa Test View', 
            'kode_kecamatan' => 'K1'
        ]);

        $admin = User::create([
            'nik' => '9999999999999999',
            'nama' => 'Admin Test',
            'akses' => 'Admin',
            'status_aktif' => 'Aktif',
            'password' => bcrypt('password')
        ]);

        $petugas = User::create([
            'nik' => '8888888888888888',
            'nama' => 'Petugas Desa View',
            'akses' => 'Petugas',
            'status_aktif' => 'Aktif',
            'kode_desa' => '3301012001',
            'password' => bcrypt('password')
        ]);

        $response = $this->actingAs($admin)->get(route('users.index', ['search' => 'Petugas Desa View']));

        $response->assertStatus(200);
        $response->assertSee('Petugas Desa View');
        $response->assertSee('Desa Test View');
    }
}
