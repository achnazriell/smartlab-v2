@extends('layouts.app')

@section('content')
    <div class="p-6 space-y-6">
        <!-- Hero Section -->
        <div
            class="relative bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 sm:p-8 mb-6 overflow-hidden border border-blue-100">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 font-poppins">Data Kelas</h1>
                    <nav class="flex mt-2 text-sm text-slate-500 font-medium" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-2">
                            <li class="inline-flex items-center">
                                <a href="#" class="hover:text-blue-600 transition-colors">Dashboard</a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <span class="mx-2 text-slate-400">•</span>
                                    <span class="text-slate-900 font-semibold">Kelas</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                </div>
                <div class="hidden md:block">
                    <img src="https://pkl.hummatech.com/assets-user/dist/images/breadcrumb/ChatBc.png" alt="Illustration"
                        class="w-36 h-w-36 object-contain drop-shadow-xl transform hover:scale-105 transition-transform duration-300">
                </div>
            </div>
            <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-blue-100/50 rounded-full blur-3xl"></div>
        </div>

        <div class="flex flex-row items-center justify-between gap-4 ">
            <h2 class="text-xl font-bold text-slate-800">Data Kelas</h2>
            <button type="button" onclick="openAddModal()"
                class="flex items-center space-x-2 px-4 py-2 bg-blue-200 text-blue-600 rounded-lg hover:bg-blue-300 transition-colors duration-200 shadow-md">
                <span class="font-semibold">Tambah</span>
            </button>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg relative" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <strong class="font-medium">{{ session('success') }}</strong>
                </div>
                <button onclick="this.parentElement.style.display='none'"
                    class="absolute top-0 bottom-0 right-0 px-4 py-3 text-green-600 hover:text-green-800">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg relative" role="alert">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <strong class="font-medium">Kesalahan Validasi:</strong>
                        <ul class="list-disc ml-5 mt-2 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button onclick="this.parentElement.style.display='none'"
                    class="absolute top-0 bottom-0 right-0 px-4 py-3 text-red-600 hover:text-red-800">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden">
            <div class="flex justify-between px-6 py-4 border-b border-slate-200">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 font-poppins">Daftar Kelas</h2>
                    <p class="text-sm text-slate-600 mt-1">Total: {{ $classes->total() }} kelas</p>
                </div>
                <div class="flex space-x-1">
                    @if (request('search_class'))
                        <a href="{{ route('classes.index') }}"
                            class="w-8 h-8 p-3 mt-1 rounded-lg flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-slate-200transition-colors duration-200 text-sm">
                            X
                        </a>
                    @endif
                    {{-- Search form --}}
                    <form id="searchForm" action="{{ route('classes.index') }}" method="GET" class="flex items-center ">
                        <div class="relative flex items-center">
                            <input type="text" name="search_class" id="searchInput" placeholder="Cari kelas..."
                                value="{{ request('search_class') }}"
                                class="search-input w-0 px-0 py-2 border-0 bg-transparent focus:outline-none focus:ring-0 transition-all duration-300 ease-in-out text-sm">
                            <button type="button" id="searchToggle"
                                class="flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="m21 21-4.35-4.35"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Nama
                                Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Deskripsi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @php
                            $offset = ($classes->currentPage() - 1) * $classes->perPage();
                        @endphp
                        @foreach ($classes as $class)
                            <tr class="hover:bg-slate-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    {{ $loop->iteration + $offset }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-slate-900">{{ $class->name_class }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ $class->description ?? 'Tidak ada deskripsi' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center space-x-2">
                                        <button type="button" onclick="openEditModal({{ $class->id }})"
                                            class="inline-flex items-center px-3 py-1.5 bg-amber-500 text-white text-xs font-medium rounded-lg hover:bg-amber-600 transition-colors duration-200">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                            Edit
                                        </button>
                                        <button type="button" onclick="openDeleteModal({{ $class->id }})"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-500 text-white text-xs font-medium rounded-lg hover:bg-red-600 transition-colors duration-200">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            @php
                                $nameClass = $class->name_class ?? '';
                                $classParts = is_string($nameClass) ? explode('-', $nameClass) : [];
                                $classGrade = $classParts[0] ?? '';
                                $className = $classParts[1] ?? '';
                            @endphp

                            {{-- Modal Edit --}}
                            <div id="editModal{{ $class->id }}" class="fixed inset-0 z-50 hidden" role="dialog"
                                aria-modal="true">
                                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
                                    onclick="closeEditModal({{ $class->id }})"></div>
                                <div class="fixed inset-0 z-10 overflow-y-auto">
                                    <div class="flex min-h-full items-center justify-center p-4">
                                        <div
                                            class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-all">
                                            <div
                                                class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-amber-50 rounded-t-xl">
                                                <div class="flex items-center space-x-3">
                                                    <div
                                                        class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-5 w-5 text-amber-600" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h3 class="text-lg font-semibold text-slate-900">Edit Kelas</h3>
                                                        <p class="text-sm text-slate-500">{{ $class->name_class }}</p>
                                                    </div>
                                                </div>
                                                <button type="button" onclick="closeEditModal({{ $class->id }})"
                                                    class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full p-1 transition-colors duration-200">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <form action="{{ route('classes.update', $class->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="p-6 space-y-4">
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-slate-700 mb-1">Angkatan
                                                            Kelas</label>
                                                        <select name="name_class[0]"
                                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors duration-200">
                                                            <option value="" disabled
                                                                {{ $classGrade === '' ? 'selected' : '' }}>Pilih Angkatan
                                                            </option>
                                                            <option value="10"
                                                                {{ $classGrade == 10 ? 'selected' : '' }}>10</option>
                                                            <option value="11"
                                                                {{ $classGrade == 11 ? 'selected' : '' }}>11</option>
                                                            <option value="12"
                                                                {{ $classGrade == 12 ? 'selected' : '' }}>12</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama
                                                            Kelas</label>
                                                        <input type="text" name="name_class[1]"
                                                            value="{{ $className }}"
                                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors duration-200">
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
                                                        <textarea name="description" rows="3"
                                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors duration-200">{{ $class->description }}</textarea>
                                                    </div>
                                                </div>
                                                <div
                                                    class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-end space-x-3">
                                                    <button type="button" onclick="closeEditModal({{ $class->id }})"
                                                        class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors duration-200">
                                                        Batal
                                                    </button>
                                                    <button type="submit"
                                                        class="px-4 py-2 text-sm font-medium text-white bg-amber-500 rounded-lg hover:bg-amber-600 transition-colors duration-200 flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        Simpan Perubahan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Delete --}}
                            <div id="deleteModal{{ $class->id }}" class="fixed inset-0 z-50 hidden" role="dialog"
                                aria-modal="true">
                                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
                                    onclick="closeDeleteModal({{ $class->id }})"></div>
                                <div class="fixed inset-0 z-10 overflow-y-auto">
                                    <div class="flex min-h-full items-center justify-center p-4">
                                        <div
                                            class="relative bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all">
                                            <div
                                                class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-red-50 rounded-t-xl">
                                                <div class="flex items-center space-x-3">
                                                    <div
                                                        class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-5 w-5 text-red-600" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <h3 class="text-lg font-semibold text-slate-900">Hapus Kelas</h3>
                                                        <p class="text-sm text-slate-500">Konfirmasi penghapusan</p>
                                                    </div>
                                                </div>
                                                <button type="button" onclick="closeDeleteModal({{ $class->id }})"
                                                    class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full p-1 transition-colors duration-200">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="p-6">
                                                <div class="text-center">
                                                    <div
                                                        class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                                                        <svg class="h-8 w-8 text-red-600" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                    <p class="text-slate-600 mb-2">Anda yakin ingin menghapus kelas:</p>
                                                    <p class="text-lg font-semibold text-slate-900 mb-4">
                                                        {{ $class->name_class }}</p>
                                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                                        <p class="text-sm text-red-600 flex items-center justify-center">
                                                            <svg class="w-4 h-4 mr-1" fill="currentColor"
                                                                viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd"
                                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                                    clip-rule="evenodd"></path>
                                                            </svg>
                                                            Aksi ini tidak bisa dibatalkan!
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <form action="{{ route('classes.destroy', $class->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div
                                                    class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-end space-x-3">
                                                    <button type="button"
                                                        onclick="closeDeleteModal({{ $class->id }})"
                                                        class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors duration-200">
                                                        Batal
                                                    </button>
                                                    <button type="submit"
                                                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors duration-200 flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                        Hapus Kelas
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $classes->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>

    {{-- Modal Tambah Kelas --}}
    <div id="addModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeAddModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-all">
                    <div
                        class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-blue-50 rounded-t-xl">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Tambah Kelas Baru</h3>
                                <p class="text-sm text-slate-500">Isi data kelas</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeAddModal()"
                            class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full p-1 transition-colors duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <form action="{{ route('classes.store') }}" method="POST">
                        @csrf
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Angkatan <span
                                        class="text-red-500">*</span></label>
                                <select name="name_class[0]" required
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                    <option value="" disabled selected>Pilih Angkatan</option>
                                    <option value="10" {{ old('name_class.0') == '10' ? 'selected' : '' }}>10</option>
                                    <option value="11" {{ old('name_class.0') == '11' ? 'selected' : '' }}>11</option>
                                    <option value="12" {{ old('name_class.0') == '12' ? 'selected' : '' }}>12</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Kelas <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="name_class[1]" value="{{ old('name_class.1') }}" required
                                    placeholder="Contoh: IPA 1"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
                                <textarea name="description" rows="3" placeholder="Deskripsi kelas (opsional)"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">{{ old('description') }}</textarea>
                            </div>
                        </div>
                        <div
                            class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-3 rounded-b-xl">
                            <button type="button" onclick="closeAddModal()"
                                class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-100 transition-colors duration-200">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                Tambah Kelas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openEditModal(id) {
            document.getElementById('editModal' + id).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal(id) {
            document.getElementById('editModal' + id).classList.add('hidden');
            document.body.style.overflow = '';
        }

        function openDeleteModal(id) {
            document.getElementById('deleteModal' + id).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal(id) {
            document.getElementById('deleteModal' + id).classList.add('hidden');
            document.body.style.overflow = '';
        }

        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchToggle = document.getElementById('searchToggle');
            const searchInput = document.getElementById('searchInput');
            const searchForm = document.getElementById('searchForm');
            let isSearchOpen = {{ request('search_class') ? 'true' : 'false' }};

            function setSearchOpenStyle() {
                if (window.matchMedia('(min-width: 1024px)').matches) {
                    searchInput.style.width = '200px';
                } else {
                    searchInput.style.width = '100px';
                }
                searchInput.style.paddingLeft = '12px';
                searchInput.style.paddingRight = '12px';
                searchInput.style.border = '1px solid #cbd5e1';
                searchInput.style.borderRadius = '9999px';
                searchInput.style.marginRight = '8px';
            }

            function setSearchClosedStyle() {
                searchInput.style.width = '0';
                searchInput.style.paddingLeft = '0';
                searchInput.style.paddingRight = '0';
                searchInput.style.border = '0';
                searchInput.style.marginRight = '0';
            }

            function toggleSearch() {
                isSearchOpen = !isSearchOpen;
                if (isSearchOpen) {
                    setSearchOpenStyle();
                    searchInput.focus();
                } else {
                    setSearchClosedStyle();
                }
            }

            if (isSearchOpen) {
                setSearchOpenStyle();
            }

            searchToggle.addEventListener('click', function(e) {
                e.preventDefault();
                if (isSearchOpen && searchInput.value.trim() !== '') {
                    searchForm.submit();
                } else if (!isSearchOpen) {
                    toggleSearch();
                } else {
                    searchForm.submit();
                }
            });

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchForm.submit();
                }
            });

            document.addEventListener('click', function(e) {
                if (!searchForm.contains(e.target) && isSearchOpen && searchInput.value.trim() === '') {
                    toggleSearch();
                }
            });

            // Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.querySelectorAll('[id^="editModal"], [id^="deleteModal"]').forEach(function(
                        modal) {
                        if (!modal.classList.contains('hidden')) {
                            modal.classList.add('hidden');
                            document.body.style.overflow = '';
                        }
                    });
                    if (!document.getElementById('addModal').classList.contains('hidden')) {
                        closeAddModal();
                    }
                }
            });
        });
    </script>
@endsection
