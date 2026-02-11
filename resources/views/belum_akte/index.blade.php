@extends('layouts.app')

@section('title', 'Data Belum Akte')
@section('subtitle', 'Daftar penduduk yang belum memiliki Akte Kelahiran')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6 h-full flex flex-col">
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if(session('info'))
        <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
            {{ session('info') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif
    <!-- Header Tools - Fixed -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 flex-shrink-0">
        <!-- Filter & Search -->
        <form action="{{ route('belum_akte.index') }}" method="GET" class="flex flex-col md:flex-row gap-4 flex-1">
            <div class="w-full md:w-64">
                <select name="kode_kecamatan" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="">Semua Kecamatan</option>
                    @foreach($kecamatans as $kec)
                        <option value="{{ $kec->kode_kecamatan }}" {{ request('kode_kecamatan') == $kec->kode_kecamatan ? 'selected' : '' }}>
                            {{ $kec->nama_kecamatan }}
                        </option>
                    @endforeach
                </select>
            </div>
             @if(!empty($desas) || request('kode_desa'))
            <div class="w-full md:w-64">
                <select name="kode_desa" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="">Semua Desa</option>
                    @foreach($desas as $desa)
                        <option value="{{ $desa->kode_desa }}" {{ request('kode_desa') == $desa->kode_desa ? 'selected' : '' }}>
                            {{ $desa->nama_desa }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="w-full md:w-48">
                <select name="sort_tahun" onchange="this.form.submit()" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                    <option value="">Urut Tahun</option>
                    <option value="desc" {{ request('sort_tahun') == 'desc' ? 'selected' : '' }}>Tahun Terbesar</option>
                    <option value="asc" {{ request('sort_tahun') == 'asc' ? 'selected' : '' }}>Tahun Terkecil</option>
                </select>
            </div>

            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIK atau Nama..." 
                       class="w-full pl-10 pr-4 rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition duration-200">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </form>

        <!-- Actions -->
        <div class="flex gap-2">
            @if(!auth()->user()->isPetugas())
                @php
                    $pendingCount = \App\Http\Controllers\BelumAkteController::getPendingCount();
                @endphp
                @if($pendingCount > 0)
                    <a href="{{ route('belum_akte.pending') }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition duration-200 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Pending ({{ $pendingCount }})
                    </a>
                @endif
                <a href="{{ route('belum_akte.export', request()->query()) }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export Excel
                </a>
            @endif
            @if(request('search') || request('kode_kecamatan') || request('kode_desa') || request('sort_tahun'))
                <a href="{{ route('belum_akte.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition duration-200">
                    Reset
                </a>
            @endif
        </div>
    </div>

    <!-- Table Container - Scrollable -->
    <div class="flex-1 overflow-auto min-h-0">
        <table class="w-full text-left border-collapse">
            <thead class="sticky top-0 bg-gray-50 z-10">
                <tr class="border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <th class="px-4 py-3 w-16">No</th>
                    <th class="px-4 py-3">NIK</th>
                    <th class="px-4 py-3">Nama Lengkap</th>
                    <th class="px-4 py-3">L/P</th>
                    <th class="px-4 py-3">Tgl Lahir</th>
                    <th class="px-4 py-3">Desa</th>
                    <th class="px-4 py-3">Kecamatan</th>
                    <th class="px-4 py-3">Keterangan</th>
                    <th class="px-4 py-3">No Akta Kelahiran</th>
                    <th class="px-4 py-3 text-center">Dokumen</th>
                    @if(!auth()->user()->isSupervisor())
                        <th class="px-4 py-3 text-center">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($data as $item)
                    <tr class="hover:bg-gray-50 transition duration-150 {{ $item->hasPendingApproval() ? 'bg-yellow-50' : '' }}">
                        <td class="px-4 py-3 text-sm text-gray-500 text-center">{{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $item->nik }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            {{ $item->nama_lgkp }}
                            @if($item->hasPendingApproval())
                                <span class="ml-2 px-2 py-0.5 text-xs font-medium bg-yellow-200 text-yellow-800 rounded-full">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->jenis_klmin }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->tgl_lhr }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->desa->nama_desa ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->kecamatan->nama_kecamatan ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->keterangan }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $item->no_akta_kelahiran ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($item->dokumen_path)
                                {{-- Has document: show preview & download buttons --}}
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="openPreview('{{ asset('storage/' . $item->dokumen_path) }}', '{{ $item->nama_lgkp }}')"
                                        class="inline-flex items-center px-2 py-1 bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 transition duration-200 text-xs" title="Preview">
                                        <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Lihat
                                    </button>
                                    <a href="{{ route('belum_akte.download-dokumen', trim($item->nik)) }}"
                                        class="inline-flex items-center px-2 py-1 bg-green-100 text-green-700 rounded hover:bg-green-200 transition duration-200 text-xs" title="Download">
                                        <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        Unduh
                                    </a>
                                    @if(!auth()->user()->isSupervisor())
                                        <form action="{{ route('belum_akte.delete-dokumen', trim($item->nik)) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-2 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 transition duration-200 text-xs" title="Hapus Dokumen">
                                                <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                {{-- No document: show upload button --}}
                                @if(!auth()->user()->isSupervisor())
                                    <button type="button" onclick="openUploadModal('{{ trim($item->nik) }}', '{{ $item->nama_lgkp }}')"
                                        class="inline-flex items-center px-2.5 py-1 bg-purple-100 text-purple-700 rounded hover:bg-purple-200 transition duration-200 text-xs">
                                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>
                                        Upload
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">Belum ada</span>
                                @endif
                            @endif
                        </td>
                        @if(!auth()->user()->isSupervisor())
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('belum_akte.edit', $item->nik) }}" 
                                    class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition duration-200 text-sm">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit
                                </a>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-gray-500">
                            Tidak ada data belum akte.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination - Fixed at bottom -->
    <div class="mt-4 flex-shrink-0">
        {{ $data->links() }}
    </div>
</div>

@endsection

@push('modals')
{{-- Upload Modal --}}
<div id="uploadModal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50" style="display:none">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Upload Dokumen</h3>
            <button type="button" onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <p class="text-sm text-gray-600 mb-4">Upload dokumen untuk: <strong id="uploadNamaLgkp"></strong></p>
        <form id="uploadForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File (JPG/JPEG, max 1MB)</label>
                <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-400 transition duration-200 cursor-pointer">
                    <svg class="w-10 h-10 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <p class="text-sm text-gray-500">Klik atau seret file ke sini</p>
                    <p class="text-xs text-gray-400 mt-1">Format: JPG/JPEG • Maks: 1MB</p>
                    <input type="file" name="dokumen" id="dokumenInput" accept=".jpg,.jpeg" class="hidden">
                </div>
                <div id="filePreviewArea" class="hidden mt-3">
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <img id="filePreviewImg" src="" alt="Preview" class="w-16 h-16 object-cover rounded">
                        <div class="flex-1 min-w-0">
                            <p id="fileName" class="text-sm font-medium text-gray-700 truncate"></p>
                            <p id="fileSize" class="text-xs text-gray-500"></p>
                        </div>
                        <button type="button" onclick="clearFileInput()" class="text-red-500 hover:text-red-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeUploadModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200">
                    Batal
                </button>
                <button type="submit" id="uploadSubmitBtn" disabled class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Preview Modal --}}
<div id="previewModal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-75" style="display:none">
    <div class="relative max-w-3xl w-full mx-4">
        <button type="button" onclick="closePreview()" class="absolute -top-10 right-0 text-white hover:text-gray-300 transition">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <p id="previewTitle" class="text-white text-center mb-3 text-sm font-medium"></p>
        <img id="previewImage" src="" alt="Preview" class="max-w-full max-h-[80vh] mx-auto rounded-lg shadow-2xl">
    </div>
</div>
@endpush

@push('scripts')
<script>
    var _dropZone, _dokumenInput;

    // Upload Modal
    function openUploadModal(nik, namaLgkp) {
        var modal = document.getElementById('uploadModal');
        var form = document.getElementById('uploadForm');
        document.getElementById('uploadNamaLgkp').textContent = namaLgkp;
        form.action = '/belum-akte/' + nik + '/upload-dokumen';
        clearFileInput();
        modal.style.display = 'flex';
    }

    function closeUploadModal() {
        var modal = document.getElementById('uploadModal');
        modal.style.display = 'none';
        clearFileInput();
    }

    function handleFileSelect(file) {
        var allowed = ['image/jpeg'];
        if (allowed.indexOf(file.type) === -1) {
            alert('Format file harus JPG/JPEG.');
            clearFileInput();
            return;
        }
        if (file.size > 1024 * 1024) {
            alert('Ukuran file maksimal 1MB.');
            clearFileInput();
            return;
        }

        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = (file.size / 1024).toFixed(1) + ' KB';

        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('filePreviewImg').src = e.target.result;
        };
        reader.readAsDataURL(file);

        document.getElementById('filePreviewArea').style.display = 'block';
        if (_dropZone) _dropZone.style.display = 'none';
        document.getElementById('uploadSubmitBtn').disabled = false;
    }

    function clearFileInput() {
        if (_dokumenInput) _dokumenInput.value = '';
        document.getElementById('filePreviewArea').style.display = 'none';
        if (_dropZone) _dropZone.style.display = 'block';
        document.getElementById('uploadSubmitBtn').disabled = true;
    }

    // Preview Modal
    function openPreview(imageUrl, namaLgkp) {
        var modal = document.getElementById('previewModal');
        document.getElementById('previewImage').src = imageUrl;
        document.getElementById('previewTitle').textContent = 'Dokumen: ' + namaLgkp;
        modal.style.display = 'flex';
    }

    function closePreview() {
        var modal = document.getElementById('previewModal');
        modal.style.display = 'none';
        document.getElementById('previewImage').src = '';
    }

    // Initialize after DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        _dropZone = document.getElementById('dropZone');
        _dokumenInput = document.getElementById('dokumenInput');

        if (_dropZone && _dokumenInput) {
            _dropZone.addEventListener('click', function() { _dokumenInput.click(); });
            _dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                _dropZone.classList.add('border-purple-500', 'bg-purple-50');
            });
            _dropZone.addEventListener('dragleave', function() {
                _dropZone.classList.remove('border-purple-500', 'bg-purple-50');
            });
            _dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                _dropZone.classList.remove('border-purple-500', 'bg-purple-50');
                if (e.dataTransfer.files.length) {
                    _dokumenInput.files = e.dataTransfer.files;
                    handleFileSelect(_dokumenInput.files[0]);
                }
            });

            _dokumenInput.addEventListener('change', function(e) {
                if (e.target.files.length) {
                    handleFileSelect(e.target.files[0]);
                }
            });
        }

        // Close modals on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeUploadModal();
                closePreview();
            }
        });

        // Close modals on backdrop click
        var uploadModal = document.getElementById('uploadModal');
        var previewModal = document.getElementById('previewModal');
        if (uploadModal) {
            uploadModal.addEventListener('click', function(e) {
                if (e.target === e.currentTarget) closeUploadModal();
            });
        }
        if (previewModal) {
            previewModal.addEventListener('click', function(e) {
                if (e.target === e.currentTarget) closePreview();
            });
        }
    });
</script>
@endpush

