@extends('layouts.app')

@section('title', 'Input Pelayanan / Kinerja')
@section('subtitle', 'Input data kinerja petugas bulanan')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <form action="{{ route('kinerja.store') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6" x-data="petugasSearch()">
                    <!-- Search & Selection -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari Petugas / Desa</label>
                        <input 
                            type="text" 
                            x-model="search" 
                            placeholder="Ketik Nama Petugas atau Nama Desa..." 
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200"
                            @focus="open = true"
                            @click.away="open = false"
                        >
                        
                        <!-- Dropdown List -->
                        <div x-show="open && filteredPetugas.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                            <template x-for="p in filteredPetugas" :key="p.nik">
                                <div 
                                    @click="selectPetugas(p)"
                                    class="px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-50 last:border-b-0"
                                >
                                    <div class="font-medium text-gray-800" x-text="p.nama"></div>
                                    <div class="text-xs text-gray-500" x-text="p.desa?.nama_desa || 'Desa Tidak Diketahui'"></div>
                                </div>
                            </template>
                        </div>
                        
                        <div x-show="open && filteredPetugas.length === 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-4 text-center text-gray-500">
                            Tidak ditemukan.
                        </div>
                        
                        <!-- Hidden Inputs -->
                        <input type="hidden" name="petugas_id" :value="selectedPetugas?.nik">
                        <input type="hidden" name="desa_id" :value="selectedPetugas?.kode_desa">
                    </div>

                    <!-- Selected Detail (Locked) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Detail Terpilih</label>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                            <div x-show="selectedPetugas">
                                <p class="text-xs text-gray-500 uppercase">Petugas</p>
                                <p class="font-bold text-gray-800 mb-2" x-text="selectedPetugas.nama"></p>
                                
                                <p class="text-xs text-gray-500 uppercase">NIK</p>
                                <p class="font-mono text-gray-800 mb-2" x-text="selectedPetugas.nik"></p>
                                
                                <p class="text-xs text-gray-500 uppercase">Desa</p>
                                <p class="font-bold text-gray-800" x-text="selectedPetugas.desa?.nama_desa || '-'"></p>
                            </div>
                            <div x-show="!selectedPetugas" class="text-gray-400 italic">
                                Silakan cari dan pilih petugas terlebih dahulu.
                            </div>
                        </div>
                        @error('petugas_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        @error('desa_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <!-- Scripts for Data -->
                    <script>
                        function petugasSearch() {
                            return {
                                open: false,
                                search: '',
                                allPetugas: @json($petugas),
                                selectedPetugas: null,
                                
                                get filteredPetugas() {
                                    if (this.search === '') return this.allPetugas;
                                    return this.allPetugas.filter(p => {
                                        const searchLower = this.search.toLowerCase();
                                        const namaMatch = p.nama.toLowerCase().includes(searchLower);
                                        const desaMatch = p.desa && p.desa.nama_desa.toLowerCase().includes(searchLower);
                                        return namaMatch || desaMatch;
                                    });
                                },
                                
                                selectPetugas(p) {
                                    this.selectedPetugas = p;
                                    this.search = p.nama; // Set input to name
                                    this.open = false;
                                },

                                init() {
                                    // Pre-select if old input exists (validation fail)
                                    const oldPetugasId = "{{ old('petugas_id') }}";
                                    if(oldPetugasId) {
                                        this.selectedPetugas = this.allPetugas.find(p => p.nik == oldPetugasId);
                                        if(this.selectedPetugas) this.search = this.selectedPetugas.nama;
                                    }
                                }
                            }
                        }
                    </script>

                    <!-- Periode -->
                    <div>
                        <label for="bulan" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                        <select name="bulan" id="bulan" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ old('bulan', date('n')) == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endforeach
                        </select>
                        @error('bulan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                        <select name="tahun" id="tahun" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" required>
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ old('tahun', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        @error('tahun')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Rincian Pelayanan</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Service Inputs -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="aktivasi_ikd" class="block text-sm font-medium text-gray-700 mb-1">Aktivasi IKD (bulan ini)</label>
                            <input type="number" name="aktivasi_ikd" id="aktivasi_ikd" value="{{ old('aktivasi_ikd', 0) }}" min="0" class="service-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" oninput="calculateTotal()">
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="ikd_desa" class="block text-sm font-medium text-gray-700 mb-1">Total IKD Desa (saat ini)</label>
                            <input type="number" name="ikd_desa" id="ikd_desa" value="{{ old('ikd_desa', 0) }}" min="0" class="service-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" oninput="calculateTotal()">
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="akta_kelahiran" class="block text-sm font-medium text-gray-700 mb-1">Akta Kelahiran</label>
                            <input type="number" name="akta_kelahiran" id="akta_kelahiran" value="{{ old('akta_kelahiran', 0) }}" min="0" class="service-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" oninput="calculateTotal()">
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="akta_kematian" class="block text-sm font-medium text-gray-700 mb-1">Akta Kematian</label>
                            <input type="number" name="akta_kematian" id="akta_kematian" value="{{ old('akta_kematian', 0) }}" min="0" class="service-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" oninput="calculateTotal()">
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="pengajuan_kk" class="block text-sm font-medium text-gray-700 mb-1">Pengajuan KK</label>
                            <input type="number" name="pengajuan_kk" id="pengajuan_kk" value="{{ old('pengajuan_kk', 0) }}" min="0" class="service-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" oninput="calculateTotal()">
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="pengajuan_pindah" class="block text-sm font-medium text-gray-700 mb-1">Pengajuan Pindah</label>
                            <input type="number" name="pengajuan_pindah" id="pengajuan_pindah" value="{{ old('pengajuan_pindah', 0) }}" min="0" class="service-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" oninput="calculateTotal()">
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="pengajuan_kia" class="block text-sm font-medium text-gray-700 mb-1">Pengajuan KIA</label>
                            <input type="number" name="pengajuan_kia" id="pengajuan_kia" value="{{ old('pengajuan_kia', 0) }}" min="0" class="service-input w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200" oninput="calculateTotal()">
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <label for="jumlah_login" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Login</label>
                            <input type="number" name="jumlah_login" id="jumlah_login" value="{{ old('jumlah_login', 0) }}" min="0" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200">
                            <p class="text-xs text-gray-500 mt-1">Tidak dihitung dalam total pelayanan</p>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                            <label for="total_aktivasi_ikd" class="block text-sm font-medium text-blue-800 mb-1">Total Aktivasi IKD (sampai saat ini)</label>
                            <input type="number" name="total_aktivasi_ikd" id="total_aktivasi_ikd" value="{{ old('total_aktivasi_ikd', 0) }}" min="0" class="w-full rounded-lg border-blue-300 focus:border-blue-500 focus:ring focus:ring-blue-200 font-bold text-blue-800">
                        </div>
                    </div>
                </div>

                <!-- Total Readonly -->
                <div class="mb-6 p-4 bg-blue-50 rounded-xl border border-blue-100 flex items-center justify-between">
                    <span class="text-blue-800 font-semibold">Total Pelayanan:</span>
                    <span id="total_display" class="text-3xl font-bold text-blue-800">0</span>
                    <input type="hidden" name="total_pelayanan" id="total_pelayanan" value="0">
                </div>

                <div class="flex justify-end gap-4">
                    <a href="{{ route('kinerja.index') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200">Batal</a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-md">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function calculateTotal() {
    let inputs = document.querySelectorAll('.service-input');
    let total = 0;
    inputs.forEach(input => {
        // Exclude ikd_desa from total calculation based on new requirement
        if(input.id !== 'ikd_desa') {
            total += parseInt(input.value) || 0;
        }
    });
    
    document.getElementById('total_display').innerText = total;
    document.getElementById('total_pelayanan').value = total;
}

// Initial calculation on load
document.addEventListener('DOMContentLoaded', calculateTotal);
</script>
@endsection
