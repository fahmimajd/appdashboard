<?php

namespace Tests\Feature;

use App\Models\KinerjaPetugas;
use App\Models\Petugas;
use App\Models\User;
use App\Models\WilayahDesa;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KinerjaUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_kinerja_update_fails_without_hidden_fields()
    {
        // 1. Setup Data
        $desa = WilayahDesa::first();
        if (!$desa) {
            $this->markTestSkipped('No WilayahDesa found.');
        }

        // Create a Petugas if needed, or pick one.
        // We need a user to login as Admin to bypass some permission checks for simpler testing
        $admin = User::where('akses', 'Admin')->first();
        if (!$admin) {
            // fallback create
            $admin = User::forceCreate([
                'nik' => '9999999999999999', // Add nik as it might be required or part of table
                'nama' => 'Test Admin',
                'password' => bcrypt('password'),
                'akses' => 'Admin',
                'status_aktif' => 'Aktif'
            ]);
        }

        $petugas = Petugas::first();
        if (!$petugas) {
             $petugas = Petugas::forceCreate([
                'nik' => '1234567890123456',
                'nama' => 'Test Petugas',
                'kode_desa' => $desa->kode_desa,
                'status_aktif' => 'Aktif',
                'no_hp' => '08123456789'
             ]);
        }

        $kinerja = KinerjaPetugas::create([
            'nik_petugas' => $petugas->nik,
            'kode_desa' => $desa->kode_desa,
            'bulan' => 1,
            'tahun' => 2024,
            'aktivasi_ikd' => 10,
            'total_aktivasi_ikd' => 10
        ]);

        // 2. Act: Attempt Update WITHOUT hidden fields (simulating current bug)
        $response = $this->actingAs($admin)
                         ->put(route('kinerja.update', $kinerja->id), [
                             'aktivasi_ikd' => 20, // Changing a value
                             // Missing: petugas_id, desa_id, bulan, tahun
                         ]);

        // 3. Assert: Should have validation errors
        $response->assertSessionHasErrors(['petugas_id', 'desa_id', 'bulan', 'tahun']);
    }

    public function test_kinerja_update_succeeds_with_hidden_fields()
    {
         // 1. Setup Data
         $desa = WilayahDesa::first();
         if (!$desa) {
             $this->markTestSkipped('No WilayahDesa found.');
         }
 
         $admin = User::where('akses', 'Admin')->first();
         if (!$admin) {
             $admin = User::forceCreate([
                'nik' => '9999999999999999',
                'nama' => 'Test Admin',
                'password' => bcrypt('password'),
                'akses' => 'Admin',
                'status_aktif' => 'Aktif'
             ]);
         }
 
         $petugas = Petugas::first();
         if (!$petugas) {
            $petugas = Petugas::forceCreate([
               'nik' => '1234567890123456',
               'nama' => 'Test Petugas',
               'kode_desa' => $desa->kode_desa,
               'status_aktif' => 'Aktif',
               'no_hp' => '08123456789'
            ]);
         }
 
         $kinerja = KinerjaPetugas::create([
             'nik_petugas' => $petugas->nik,
             'kode_desa' => $desa->kode_desa,
             'bulan' => 2, // Different month to avoid collision with previous test if check doesn't cleanup
             'tahun' => 2024,
             'aktivasi_ikd' => 10,
             'total_aktivasi_ikd' => 10
         ]);
 
         // 2. Act: Attempt Update WITH hidden fields (the fix)
         $response = $this->actingAs($admin)
                          ->put(route('kinerja.update', $kinerja->id), [
                              'petugas_id' => $petugas->nik,
                              'desa_id' => $desa->kode_desa,
                              'bulan' => 2,
                              'tahun' => 2024,
                              'aktivasi_ikd' => 25,
                              'total_aktivasi_ikd' => 25
                          ]);
 
         // 3. Assert: Success
         $response->assertSessionHasNoErrors();
         $response->assertRedirect(route('kinerja.index'));
         
         $this->assertDatabaseHas('kinerja_petugas', [
             'id' => $kinerja->id,
             'aktivasi_ikd' => 25
         ]);
    }
}
