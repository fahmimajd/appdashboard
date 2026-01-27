<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\WilayahDesa;
use App\Models\BelumRekam;
use App\Models\BelumAkte;
use App\Models\Pendamping;

class SasaranTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create desas
        WilayahDesa::create(['kode_desa' => 'D1', 'nama_desa' => 'Desa Satu', 'kode_kecamatan' => 'K1']);
        WilayahDesa::create(['kode_desa' => 'D2', 'nama_desa' => 'Desa Dua', 'kode_kecamatan' => 'K1']);
        WilayahDesa::create(['kode_desa' => 'D3', 'nama_desa' => 'Desa Tiga', 'kode_kecamatan' => 'K1']);
        
        // Create sasaran data (Belum Rekam)
        BelumRekam::create(['NIK' => '111', 'NAMA_LGKP' => 'A', 'KODE_DESA' => 'D1', 'WKTP_KET' => 'WKTP', 'KODE_KECAMATAN' => 'K1']);
        BelumRekam::create(['NIK' => '333', 'NAMA_LGKP' => 'C', 'KODE_DESA' => 'D2', 'WKTP_KET' => 'WKTP', 'KODE_KECAMATAN' => 'K1']);
        BelumRekam::create(['NIK' => '444', 'NAMA_LGKP' => 'D', 'KODE_DESA' => 'D3', 'WKTP_KET' => 'WKTP', 'KODE_KECAMATAN' => 'K1']);
        
        // Create sasaran data (Belum Akte)
        BelumAkte::create(['nik' => '222', 'nama_lgkp' => 'B', 'kode_desa' => 'D1', 'kode_kecamatan' => 'K1']);
    }

    public function test_admin_sees_all_summary()
    {
        $admin = \App\Models\User::create([
            'nik' => 'admin',
            'nama' => 'Admin',
            'akses' => 'Admin',
            'status_aktif' => 'Aktif',
            'kode_desa' => 'D1',
            'kode_kecamatan' => 'K1',
            'password' => bcrypt('password')
        ]);
        
        $response = $this->actingAs($admin)->get(route('sasaran.rekapitulasi'));
        
        $response->assertStatus(200);
        $response->assertViewHas('summaryData');
        
        $summary = $response->viewData('summaryData');
        $this->assertNotNull($summary->firstWhere('kode_desa', 'D1'));
        $this->assertNotNull($summary->firstWhere('kode_desa', 'D2'));
        $this->assertNotNull($summary->firstWhere('kode_desa', 'D3'));
        
        $d1 = $summary->firstWhere('kode_desa', 'D1');
        $this->assertEquals(1, $d1->belum_rekam_count);
        $this->assertEquals(1, $d1->belum_akte_count);
        
        $d2 = $summary->firstWhere('kode_desa', 'D2');
        $this->assertEquals(1, $d2->belum_rekam_count);
        $this->assertEquals(0, $d2->belum_akte_count);
    }
    
    public function test_admin_can_filter_by_kecamatan()
    {
        WilayahDesa::create(['kode_desa' => 'D4', 'nama_desa' => 'Desa Empat', 'kode_kecamatan' => 'K2']);
        
        $admin = \App\Models\User::create([
            'nik' => 'admin',
            'nama' => 'Admin',
            'akses' => 'Admin',
            'status_aktif' => 'Aktif',
            'kode_desa' => 'D1',
            'kode_kecamatan' => 'K1',
            'password' => bcrypt('password')
        ]);
        
        $response = $this->actingAs($admin)->get(route('sasaran.rekapitulasi'));
        $response->assertStatus(200);
        $summary = $response->viewData('summaryData');
        $this->assertNotNull($summary->firstWhere('kode_desa', 'D1'));
        $this->assertNotNull($summary->firstWhere('kode_desa', 'D4'));
        
        $response = $this->actingAs($admin)->get(route('sasaran.rekapitulasi', ['kode_kecamatan' => 'K1']));
        $summary = $response->viewData('summaryData');
        $this->assertNotNull($summary->firstWhere('kode_desa', 'D1'));
        $this->assertNotNull($summary->firstWhere('kode_desa', 'D2'));
        $this->assertNotNull($summary->firstWhere('kode_desa', 'D3'));
        $this->assertNull($summary->firstWhere('kode_desa', 'D4'));
        
        $response = $this->actingAs($admin)->get(route('sasaran.rekapitulasi', ['kode_kecamatan' => 'K2']));
        $summary = $response->viewData('summaryData');
        $this->assertNotNull($summary->firstWhere('kode_desa', 'D4'));
        $this->assertNull($summary->firstWhere('kode_desa', 'D1'));
    }

    public function test_pendamping_sees_only_assigned_summary()
    {
        $user = \App\Models\User::create([
            'nik' => '999',
            'nama' => 'Pendamping1',
            'akses' => 'Pendamping',
            'status_aktif' => 'Aktif',
            'kode_desa' => 'D1', 
            'kode_kecamatan' => 'K1',
            'password' => bcrypt('password')
        ]);

        // Assign desa to pendamping (via Pendamping model as before, assuming relationship logic still holds or controller uses Pendamping model to check assignments)
        // Controller uses: \App\Models\Pendamping::where('nik', $user->nik)...
        Pendamping::create([
            'id' => 2,
            'nik' => '999',
            'nama' => 'Pendamping1',
            'akses' => 'Pendamping',
            'status_aktif' => 'Aktif',
            'kode_desa' => 'D1',
            'kode_kecamatan' => 'K1',
            'password' => bcrypt('password') // this field might be unused now but required by table
        ]);
        
        Pendamping::create([
             'id' => 3,
             'nik' => '999',
             'nama' => 'Pendamping1',
             'akses' => 'Pendamping',
             'status_aktif' => 'Aktif',
             'kode_desa' => 'D2',
             'kode_kecamatan' => 'K1',
             'password' => bcrypt('password')
        ]);
        
        
        $response = $this->actingAs($user)->get(route('sasaran.rekapitulasi'));
        
        $response->assertStatus(200);
        $summary = $response->viewData('summaryData');
        
        $this->assertNotNull($summary->firstWhere('kode_desa', 'D1'));
        $this->assertNotNull($summary->firstWhere('kode_desa', 'D2'));
        $this->assertNull($summary->firstWhere('kode_desa', 'D3'));
    }

    public function test_petugas_sees_only_assigned_desa_summary()
    {
        $user = \App\Models\User::create([
            'nik' => '4444444444444444',
            'nama' => 'Petugas1',
            'akses' => 'Petugas',
            'status_aktif' => 'Aktif',
            'kode_desa' => trim('D1'),
            'kode_kecamatan' => 'K1',
            'password' => bcrypt('password')
        ]);
        
        $response = $this->actingAs($user)->get(route('sasaran.rekapitulasi'));
        
        $response->assertStatus(200);
        $summary = $response->viewData('summaryData');
        
        // Should only see D1
        $this->assertNotNull($summary->firstWhere('kode_desa', 'D1'));
        $this->assertNull($summary->firstWhere('kode_desa', 'D2'));
        $this->assertNull($summary->firstWhere('kode_desa', 'D3'));
    }
}
