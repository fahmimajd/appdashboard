@extends('layouts.app')

@section('title', 'Edit Kinerja')
@section('subtitle', 'Form edit kinerja petugas lapangan')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg border border-gray-100 p-8">
    <div class="mb-6 pb-6 border-b border-gray-100">
        <a href="{{ route('kinerja.index') }}" class="text-sm text-gray-500 hover:text-blue-600 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Daftar
        </a>
        <h2 class="text-xl font-bold text-gray-800">Edit Kinerja: {{ $kinerja->petugas->nama ?? 'Petugas' }}</h2>
        <p class="text-sm text-gray-500">Periode: {{ \Carbon\Carbon::create()->month($kinerja->bulan)->translatedFormat('F') }} {{ $kinerja->tahun }}</p>
    </div>

    @if(auth()->user()->isPetugas())
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <p class="font-medium text-blue-800">Perubahan memerlukan approval</p>
                <p class="text-sm text-blue-600">Perubahan yang Anda lakukan akan menunggu approval dari Pendamping sebelum diterapkan.</p>
            </div>
        </div>
    </div>
    @endif

    @if($kinerja->hasPendingApproval())
    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <p class="font-medium text-yellow-800">Ada {{ count($kinerja->getPendingFields()) }} field menunggu approval</p>
                <p class="text-sm text-yellow-600">Field dengan tanda (⏳) memiliki nilai yang sedang menunggu approval. Nilai baru yang Anda masukkan akan menggantikan pengajuan sebelumnya.</p>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('kinerja.update', $kinerja->id) }}" method="POST" x-data="kinerjaForm()">
        @csrf
        @method('PUT')
        <input type="hidden" name="petugas_id" value="{{ $kinerja->nik_petugas }}">
        <input type="hidden" name="desa_id" value="{{ $kinerja->kode_desa }}">
        <input type="hidden" name="bulan" value="{{ $kinerja->bulan }}">
        <input type="hidden" name="tahun" value="{{ $kinerja->tahun }}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
             <!-- Petugas & Periode Readonly -->
            <div class="md:col-span-2 grid grid-cols-3 gap-4 bg-gray-50 p-4 rounded border border-gray-100">
                 <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase">Petugas</label>
                    <span class="font-medium">{{ $kinerja->petugas->nama ?? '-' }}</span>
                 </div>
                 <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase">Bulan</label>
                    <span class="font-medium">{{ \Carbon\Carbon::create()->month($kinerja->bulan)->translatedFormat('F') }}</span>
                 </div>
                 <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase">Tahun</label>
                    <span class="font-medium">{{ $kinerja->tahun }}</span>
                 </div>
            </div>
        </div>
        
        <div class="bg-blue-50 p-6 rounded-xl border border-blue-100 mb-6">
            <h3 class="text-blue-800 font-semibold mb-4 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Rincian Pelayanan
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Aktivasi IKD</label>
                    <input type="number" min="0" x-model.number="aktivasi_ikd" name="aktivasi_ikd" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-600 uppercase mb-1">Total IKD Desa</span>
                    <input type="number" min="0" x-model.number="ikd_desa" name="ikd_desa" class="w-full rounded border-gray-300">
                </div>
                 <div class="bg-blue-50 p-2 rounded border border-blue-100">
                    <label class="block text-xs font-semibold text-blue-800 uppercase mb-1">Total Aktivasi IKD</label>
                    <input type="number" min="0" name="total_aktivasi_ikd" value="{{ old('total_aktivasi_ikd', $kinerja->total_aktivasi_ikd ?? 0) }}" class="w-full rounded border-blue-300 font-bold text-blue-800">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Akta Kelahiran</label>
                    <input type="number" min="0" x-model.number="akta_kelahiran" name="akta_kelahiran" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Akta Kematian</label>
                    <input type="number" min="0" x-model.number="akta_kematian" name="akta_kematian" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Pengajuan KK</label>
                    <input type="number" min="0" x-model.number="pengajuan_kk" name="pengajuan_kk" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Pengajuan Pindah</label>
                    <input type="number" min="0" x-model.number="pengajuan_pindah" name="pengajuan_pindah" class="w-full rounded border-gray-300">
                </div>
                 <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Pengajuan KIA</label>
                    <input type="number" min="0" x-model.number="pengajuan_kia" name="pengajuan_kia" class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Jumlah Login Apps</label>
                   <input type="number" min="0" x-model.number="jumlah_login" name="jumlah_login" class="w-full rounded border-gray-300">
                </div>
            </div>
            
            <div class="mt-6 pt-4 border-t border-blue-200 flex justify-between items-center bg-blue-100 p-4 rounded-lg">
                <span class="font-bold text-blue-800">Total Pelayanan:</span>
                <span class="text-2xl font-bold text-blue-900" x-text="total">0</span>
                <input type="hidden" name="total_pelayanan" :value="total">
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('kinerja.index') }}" class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                Batal
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 font-medium tracking-wide shadow-md">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
    function kinerjaForm() {
        return {
            aktivasi_ikd: {{ old('aktivasi_ikd', $kinerja->aktivasi_ikd ?? 0) }},
            ikd_desa: {{ old('ikd_desa', $kinerja->ikd_desa ?? 0) }},
            akta_kelahiran: {{ old('akta_kelahiran', $kinerja->akta_kelahiran ?? 0) }},
            akta_kematian: {{ old('akta_kematian', $kinerja->akta_kematian ?? 0) }},
            pengajuan_kk: {{ old('pengajuan_kk', $kinerja->pengajuan_kk ?? 0) }},
            pengajuan_pindah: {{ old('pengajuan_pindah', $kinerja->pengajuan_pindah ?? 0) }},
            pengajuan_kia: {{ old('pengajuan_kia', $kinerja->pengajuan_kia ?? 0) }},
            jumlah_login: {{ old('jumlah_login', $kinerja->jumlah_login ?? 0) }},
            
            get total() {
                // Rule: exclude ikd_desa and jumlah_login
                return this.aktivasi_ikd + 
                       this.akta_kelahiran + this.akta_kematian + 
                       this.pengajuan_kk + this.pengajuan_pindah + this.pengajuan_kia;
            },
            
            get total() {
                // Rule: exclude ikd_desa and jumlah_login
                return this.aktivasi_ikd + 
                       this.akta_kelahiran + this.akta_kematian + 
                       this.pengajuan_kk + this.pengajuan_pindah + this.pengajuan_kia;
            }
        }
    }
</script>
@endsection
