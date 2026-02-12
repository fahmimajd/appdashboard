@extends('layouts.app')

@section('title', 'Input Kinerja Harian')
@section('subtitle', 'Tambah laporan kinerja harian kecamatan')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
        
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('kinerja-kecamatan.store') }}" method="POST">
            @csrf

            <!-- Identity Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 pb-6 border-b border-gray-100">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Laporan</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', $today) }}" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200" required>
                    @error('tanggal') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kecamatan</label>
                    <select name="kode_kecamatan" id="kode_kecamatan" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200 bg-gray-50" required
                        {{ auth()->user()->kode_kecamatan ? 'readonly' : '' }}>
                        @if(!auth()->user()->kode_kecamatan)
                            <option value="">-- Pilih Kecamatan --</option>
                        @endif
                        @foreach($kecamatans as $k)
                            <option value="{{ $k->kode_kecamatan }}" 
                                {{ (old('kode_kecamatan') == $k->kode_kecamatan || auth()->user()->kode_kecamatan == $k->kode_kecamatan) ? 'selected' : '' }}>
                                {{ $k->nama_kecamatan }}
                            </option>
                        @endforeach
                    </select>
                    @if(auth()->user()->kode_kecamatan)
                        <!-- If readonly, select verification might fail if disabled, but here it's just styled bg-gray-50 and controlled by logic. 
                             Better: if explicit readonly attribute used, need hidden input. 
                             But 'readonly' on select doesn't work in standard HTML. 
                             So we use pointer-events-none or hidden input logic. -->
                         @if(auth()->user()->kode_kecamatan)
                            <input type="hidden" name="kode_kecamatan" value="{{ auth()->user()->kode_kecamatan }}">
                            <script>document.getElementById('kode_kecamatan').style.pointerEvents = 'none';</script>
                         @endif
                    @endif
                    @error('kode_kecamatan') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Petugas</label>
                    <select name="petugas_id" id="petugas_id" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200" required>
                        <option value="">-- Pilih Petugas --</option>
                        @foreach($daft_petugas as $p)
                            <option value="{{ $p->nik }}" data-kecamatan="{{ $p->kode_kecamatan }}"
                                {{ (old('petugas_id') == $p->nik || auth()->user()->nik == $p->nik) ? 'selected' : '' }}>
                                {{ $p->nama }}
                            </option>
                        @endforeach
                    </select>
                    @if(auth()->user()->isPetugas())
                        <input type="hidden" name="petugas_id" value="{{ auth()->user()->nik }}">
                        <script>document.getElementById('petugas_id').style.pointerEvents = 'none'; document.getElementById('petugas_id').style.backgroundColor = '#f9fafb';</script>
                    @endif
                    @error('petugas_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Pelayanan Section -->
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Pelayanan Kependudukan
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Rekam KTP-EL</label>
                    <input type="number" name="rekam_ktp_el" value="{{ old('rekam_ktp_el', 0) }}" min="0" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-right" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cetak KTP-EL</label>
                    <input type="number" name="cetak_ktp_el" value="{{ old('cetak_ktp_el', 0) }}" min="0" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-right" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kartu Keluarga</label>
                    <input type="number" name="kartu_keluarga" value="{{ old('kartu_keluarga', 0) }}" min="0" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-right" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">KIA</label>
                    <input type="number" name="kia" value="{{ old('kia', 0) }}" min="0" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-right" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pindah</label>
                    <input type="number" name="pindah" value="{{ old('pindah', 0) }}" min="0" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-right" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Kedatangan</label>
                    <input type="number" name="kedatangan" value="{{ old('kedatangan', 0) }}" min="0" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-right" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Akta Kelahiran</label>
                    <input type="number" name="akta_kelahiran" value="{{ old('akta_kelahiran', 0) }}" min="0" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-right" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Akta Kematian</label>
                    <input type="number" name="akta_kematian" value="{{ old('akta_kematian', 0) }}" min="0" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-right" required>
                </div>
            </div>

            <!-- IKD & Stok Section -->
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                IKD & Stok Logistik
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                 <div class="col-span-2 md:col-span-1 bg-green-50 p-2 rounded-lg border border-green-100">
                    <label class="block text-xs font-bold text-green-800 mb-1">IKD Hari Ini</label>
                    <input type="number" name="ikd_hari_ini" value="{{ old('ikd_hari_ini', 0) }}" min="0" class="w-full rounded-lg border-green-300 focus:border-green-500 focus:ring focus:ring-green-200 text-right" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Stok Blangko KTP</label>
                    <input type="number" name="stok_blangko_ktp" value="{{ old('stok_blangko_ktp', 0) }}" min="0" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-right" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Stok Blangko KIA</label>
                    <input type="number" name="stok_blangko_kia" value="{{ old('stok_blangko_kia', 0) }}" min="0" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-right" required>
                </div>
                 <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Persentase Ribbon (%)</label>
                    <input type="number" name="persentase_ribbon" value="{{ old('persentase_ribbon', 0) }}" step="0.01" min="0" max="100" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-right" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Persentase Film (%)</label>
                    <input type="number" name="persentase_film" value="{{ old('persentase_film', 0) }}" step="0.01" min="0" max="100" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 text-right" required>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-100">
                <a href="{{ route('kinerja-kecamatan.index') }}" class="px-6 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200 font-medium">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-md font-medium flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan Laporan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const kecamatanSelect = document.getElementById('kode_kecamatan');
    const petugasSelect = document.getElementById('petugas_id');
    const allPetugasOptions = Array.from(petugasSelect.querySelectorAll('option'));

    function filterPetugas() {
        const selectedKecamatan = kecamatanSelect.value;
        const currentPetugasValue = petugasSelect.value;
        
        // Clear options except first
        petugasSelect.innerHTML = '<option value="">-- Pilih Petugas --</option>';

        if (selectedKecamatan) {
            allPetugasOptions.forEach(option => {
                if (option.value === "") return; // Skip placeholder
                if (option.dataset.kecamatan === selectedKecamatan) {
                    petugasSelect.appendChild(option);
                }
            });
        } else {
             // If no kecamatan selected (e.g. admin view all), maybe show all? Or wait for selection?
             // Usually wait for selection. But let's show all if "Semua Kecamatan" logic applies, but here value is empty string for placeholder.
             // If Admin has no filter, database query returns all active.
             // Let's just append all if no kecamatan is forced.
             allPetugasOptions.forEach(option => {
                 if (option.value !== "") petugasSelect.appendChild(option);
             });
        }
        
        // Restore selection if valid
        if (currentPetugasValue) {
            petugasSelect.value = currentPetugasValue;
        }
    }

    kecamatanSelect.addEventListener('change', filterPetugas);
    
    // Initial filter on load
    filterPetugas();
});
</script>
@endsection
