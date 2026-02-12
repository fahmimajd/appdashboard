@extends('layouts.app')

@section('title', 'Master Barang')
@section('subtitle', 'Kelola daftar barang dan konfigurasi stok')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <a href="{{ route('management-barang.dashboard') }}" class="text-blue-600 hover:text-blue-800 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali
        </a>
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200 shadow-md flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Barang
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Barang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satuan</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Auto Kurang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mapping Field</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($barangs as $barang)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $barang->kode }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $barang->nama }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $barang->kategori == 'blangko' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst($barang->kategori) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $barang->satuan }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                            @if($barang->auto_kurang)
                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            @if($barang->field_kinerja)
                                <div class="text-xs">Kinerja: <span class="font-mono bg-gray-100 px-1">{{ $barang->field_kinerja }}</span></div>
                            @endif
                            @if($barang->field_stok_laporan)
                                <div class="text-xs mt-1">Lap: <span class="font-mono bg-gray-100 px-1">{{ $barang->field_stok_laporan }}</span></div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button onclick="editModal({{ $barang }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                            <form action="{{ route('management-barang.master.destroy', $barang->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus barang ini? Data stok dan mutasi akan hilang!')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Tambah Barang Baru</h3>
            <form action="{{ route('management-barang.master.store') }}" method="POST" class="mt-4">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kode (Unik)</label>
                        <input type="text" name="kode" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Barang</label>
                        <input type="text" name="nama" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700">Satuan</label>
                        <input type="text" name="satuan" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="pcs, lembar, roll">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select name="kategori" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="blangko">Blangko</option>
                            <option value="logistik">Logistik</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="hidden" name="auto_kurang" value="0">
                        <input type="checkbox" name="auto_kurang" value="1" id="add_auto_kurang" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="add_auto_kurang" class="ml-2 block text-sm text-gray-900">Otomatis Kurang dari Kinerja</label>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Field Kinerja (Pengurang)</label>
                        <input type="text" name="field_kinerja" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 placeholder-gray-300" placeholder="cth: cetak_ktp_el">
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700">Field Stok (Rekonsiliasi)</label>
                        <input type="text" name="field_stok_laporan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 placeholder-gray-300" placeholder="cth: stok_blangko_ktp">
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal Template (populated by JS) -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
     <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <form id="editForm" method="POST" class="mt-3">
             @csrf
             @method('PUT')
             <h3 class="text-lg font-medium text-gray-900">Edit Barang</h3>
             <div class="space-y-4 mt-4">
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Barang</label>
                    <input type="text" name="nama" id="edit_nama" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Satuan</label>
                    <input type="text" name="satuan" id="edit_satuan" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Kategori</label>
                    <select name="kategori" id="edit_kategori" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="blangko">Blangko</option>
                        <option value="logistik">Logistik</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="flex items-center">
                    <input type="hidden" name="auto_kurang" value="0">
                    <input type="checkbox" name="auto_kurang" value="1" id="edit_auto_kurang" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="edit_auto_kurang" class="ml-2 block text-sm text-gray-900">Otomatis Kurang</label>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Field Kinerja</label>
                    <input type="text" name="field_kinerja" id="edit_field_kinerja" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Field Stok Laporan</label>
                    <input type="text" name="field_stok_laporan" id="edit_field_stok_laporan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
             </div>
             <div class="mt-5 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Simpan Perubahan</button>
            </div>
        </form>
     </div>
</div>

<script>
function editModal(data) {
    const modal = document.getElementById('editModal');
    const form = document.getElementById('editForm');
    
    // Set Action URL
    form.action = "{{ route('management-barang.master.update', ':id') }}".replace(':id', data.id);
    
    // Populate Fields
    document.getElementById('edit_nama').value = data.nama;
    document.getElementById('edit_satuan').value = data.satuan;
    document.getElementById('edit_kategori').value = data.kategori;
    document.getElementById('edit_auto_kurang').checked = data.auto_kurang;
    document.getElementById('edit_field_kinerja').value = data.field_kinerja || '';
    document.getElementById('edit_field_stok_laporan').value = data.field_stok_laporan || '';

    modal.classList.remove('hidden');
}
</script>
@endsection
