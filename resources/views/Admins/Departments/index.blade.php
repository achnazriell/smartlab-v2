@extends('layouts.app')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <div class="p-6 space-y-6">
        <!-- Hero Section -->
        <div class="relative bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 sm:p-8 mb-6 overflow-hidden border border-blue-100">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 font-poppins">Data Jurusan</h1>
                    <nav class="flex mt-2 text-sm text-slate-500 font-medium" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-2">
                            <li class="inline-flex items-center">
                                <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors">Dashboard</a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <span class="mx-2 text-slate-400">•</span>
                                    <span class="text-slate-900 font-semibold">Jurusan</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                </div>
                <div class="hidden md:block">
                    <img src="https://pkl.hummatech.com/assets-user/dist/images/breadcrumb/ChatBc.png" alt="Illustration"
                        class="w-36 h-36 object-contain drop-shadow-xl transform hover:scale-105 transition-transform duration-300">
                </div>
            </div>
            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-blue-100/50 rounded-full blur-3xl"></div>
        </div>

        <!-- Stats Card -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Total Jurusan</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalDepartments }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Control Panel -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <h2 class="text-xl font-bold text-slate-800">Data Jurusan</h2>
                    <div class="flex flex-wrap gap-3">
                        <!-- Add Button -->
                        <button type="button" onclick="openAddModal()"
                            class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="font-semibold">Tambah Jurusan</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alerts SweetAlert -->
            @if (session('success'))
                <script>Swal.fire({ icon: 'success', title: 'Berhasil', text: '{{ session('success') }}', timer: 3000 });</script>
            @endif
            @if (session('error'))
                <script>Swal.fire({ icon: 'error', title: 'Gagal', text: '{{ session('error') }}' });</script>
            @endif
            @if ($errors->any())
                <script>Swal.fire({ icon: 'error', title: 'Kesalahan Validasi', html: '{!! implode('<br>', $errors->all()) !!}' });</script>
            @endif

            <!-- Filter Section (seperti tahun ajaran) -->
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <form method="GET" action="{{ route('departments.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari nama jurusan, kode..."
                                class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Sort -->
                    <div class="md:col-span-3">
                        <select name="sort"
                            class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama A-Z</option>
                            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nama Z-A</option>
                            <option value="code_asc" {{ request('sort') == 'code_asc' ? 'selected' : '' }}>Kode A-Z</option>
                            <option value="code_desc" {{ request('sort') == 'code_desc' ? 'selected' : '' }}>Kode Z-A</option>
                        </select>
                    </div>

                    <!-- Per Page -->
                    <div class="md:col-span-2">
                        <select name="per_page"
                            class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10 per halaman</option>
                            <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 per halaman</option>
                            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 per halaman</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="md:col-span-3 flex gap-2">
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center shadow-sm">
                            <span class="text-sm font-medium">Filter</span>
                        </button>
                        <a href="{{ route('departments.index') }}"
                            class="px-4 py-2.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-100 transition-colors duration-200 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama Jurusan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Deskripsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Jumlah Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse ($departments as $index => $department)
                            <tr class="hover:bg-slate-50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    {{ ($departments->currentPage() - 1) * $departments->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $department->code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                    {{ $department->name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ Str::limit($department->description, 50) ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ $department->class_count }} kelas
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <!-- Quick View -->
                                        <button onclick="showDetail({{ $department->id }})"
                                            class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white text-xs font-medium rounded-lg hover:bg-blue-600 transition-colors duration-200"
                                            title="Lihat Detail">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>

                                        <!-- Edit -->
                                        <button onclick="editDepartment({{ $department->id }})"
                                            class="inline-flex items-center px-3 py-1.5 bg-amber-500 text-white text-xs font-medium rounded-lg hover:bg-amber-600 transition-colors duration-200"
                                            title="Edit Data">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        <!-- Delete -->
                                        <button onclick="deleteDepartment({{ $department->id }}, '{{ addslashes($department->name) }}')"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-500 text-white text-xs font-medium rounded-lg hover:bg-red-600 transition-colors duration-200"
                                            title="Hapus Data">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 text-slate-300 mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <p class="text-slate-500 text-lg font-medium">Tidak ada data jurusan</p>
                                        <p class="text-slate-400 text-sm mt-1">Silakan tambahkan jurusan baru</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $departments->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Add -->
    <div id="addModal" class="fixed inset-0 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform transition-all">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-2xl">
                <h3 class="text-xl font-bold text-white">Tambah Jurusan Baru</h3>
                <button onclick="closeAddModal()" class="text-white hover:text-slate-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('departments.store') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <div>
                    <label for="add_name" class="block text-sm font-semibold text-slate-700 mb-2">Nama Jurusan <span class="text-red-500">*</span></label>
                    <input type="text" id="add_name" name="name" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Contoh: Rekayasa Perangkat Lunak">
                </div>

                <div>
                    <label for="add_code" class="block text-sm font-semibold text-slate-700 mb-2">Kode Jurusan <span class="text-red-500">*</span></label>
                    <input type="text" id="add_code" name="code" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors uppercase"
                        placeholder="Contoh: RPL" maxlength="10">
                    <p class="mt-1 text-xs text-slate-500">Kode akan otomatis diubah menjadi huruf kapital</p>
                </div>

                <div>
                    <label for="add_description" class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi</label>
                    <textarea id="add_description" name="description" rows="3"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Deskripsi singkat tentang jurusan..."></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200">
                    <button type="button" onclick="closeAddModal()"
                        class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors font-medium">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-lg">
                        Simpan Jurusan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="editModal" class="fixed inset-0 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform transition-all">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-gradient-to-r from-amber-600 to-amber-700 rounded-t-2xl">
                <h3 class="text-xl font-bold text-white">Edit Jurusan</h3>
                <button onclick="closeEditModal()" class="text-white hover:text-slate-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="editForm" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="edit_name" class="block text-sm font-semibold text-slate-700 mb-2">Nama Jurusan <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_name" name="name" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors">
                </div>

                <div>
                    <label for="edit_code" class="block text-sm font-semibold text-slate-700 mb-2">Kode Jurusan <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_code" name="code" required
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors uppercase"
                        maxlength="10">
                </div>

                <div>
                    <label for="edit_description" class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi</label>
                    <textarea id="edit_description" name="description" rows="3"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-200">
                    <button type="button" onclick="closeEditModal()"
                        class="px-5 py-2.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors font-medium">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors font-medium shadow-lg">
                        Update Jurusan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Detail -->
    <div id="detailModal" class="fixed inset-0 bg-slate-900 bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform transition-all">
            <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between bg-gradient-to-r from-slate-600 to-slate-700 rounded-t-2xl">
                <h3 class="text-xl font-bold text-white">Detail Jurusan</h3>
                <button onclick="closeDetailModal()" class="text-white hover:text-slate-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6 space-y-4" id="detailContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="px-6 py-4 border-t border-slate-200 flex justify-end">
                <button onclick="closeDetailModal()" class="px-5 py-2.5 bg-slate-600 text-white rounded-lg hover:bg-slate-700">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        // Modal Add
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
            document.getElementById('addModal').classList.add('flex');
        }
        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.getElementById('addModal').classList.remove('flex');
        }

        // Edit
        function editDepartment(id) {
            fetch(`/admin/departments/${id}/edit`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('editForm').action = `/admin/departments/${id}`;
                        document.getElementById('edit_name').value = data.data.name;
                        document.getElementById('edit_code').value = data.data.code;
                        document.getElementById('edit_description').value = data.data.description || '';
                        document.getElementById('editModal').classList.remove('hidden');
                        document.getElementById('editModal').classList.add('flex');
                    }
                })
                .catch(() => Swal.fire('Error!', 'Gagal memuat data jurusan', 'error'));
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.getElementById('editModal').classList.remove('flex');
        }

        // Detail
        function showDetail(id) {
            fetch(`/admin/departments/${id}/detail`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const content = `
                            <div class="space-y-3">
                                <div class="flex justify-between items-start">
                                    <span class="text-sm font-semibold text-slate-600">Kode:</span>
                                    <span class="text-sm font-medium text-slate-900 bg-blue-100 px-3 py-1 rounded-full">${data.data.code}</span>
                                </div>
                                <div class="flex justify-between items-start">
                                    <span class="text-sm font-semibold text-slate-600">Nama Jurusan:</span>
                                    <span class="text-sm font-medium text-slate-900">${data.data.name}</span>
                                </div>
                                <div class="flex justify-between items-start">
                                    <span class="text-sm font-semibold text-slate-600">Deskripsi:</span>
                                    <span class="text-sm text-slate-900 text-right max-w-xs">${data.data.description || '-'}</span>
                                </div>
                                <div class="flex justify-between items-start">
                                    <span class="text-sm font-semibold text-slate-600">Jumlah Kelas:</span>
                                    <span class="text-sm font-medium text-slate-900">${data.data.class_count} kelas</span>
                                </div>
                                <div class="pt-3 border-t border-slate-200">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-slate-500">Dibuat:</span>
                                        <span class="text-slate-600 font-medium">${data.data.createdAt}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                        document.getElementById('detailContent').innerHTML = content;
                        document.getElementById('detailModal').classList.remove('hidden');
                        document.getElementById('detailModal').classList.add('flex');
                    }
                })
                .catch(() => Swal.fire('Error!', 'Gagal memuat detail jurusan', 'error'));
        }
        function closeDetailModal() {
            document.getElementById('detailModal').classList.add('hidden');
            document.getElementById('detailModal').classList.remove('flex');
        }

        // Delete
        function deleteDepartment(id, name) {
            Swal.fire({
                title: 'Hapus Jurusan?',
                html: `Jurusan <strong>${name}</strong> akan dihapus permanen!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/departments/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Terhapus!', data.message, 'success').then(() => window.location.reload());
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error!', 'Gagal menghapus jurusan', 'error'));
                }
            });
        }

        // Auto uppercase code input
        document.getElementById('add_code')?.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
        document.getElementById('edit_code')?.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Close modals on Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAddModal();
                closeEditModal();
                closeDetailModal();
            }
        });
    </script>
@endsection
