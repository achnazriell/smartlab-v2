@extends('layouts.app')

@section('content')
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <div class="p-6 space-y-6">
        <!-- Hero Section -->
        <div class="relative bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 sm:p-8 mb-6 overflow-hidden border border-blue-100">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 font-poppins">Data Murid</h1>
                    <nav class="flex mt-2 text-sm text-slate-500 font-medium" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-2">
                            <li class="inline-flex items-center">
                                <a href="#" class="hover:text-blue-600 transition-colors">Dashboard</a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <span class="mx-2 text-slate-400">•</span>
                                    <span class="text-slate-900 font-semibold">Murid</span>
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
        <!-- Page Actions header -->
        <div class="flex flex-row items-center justify-between gap-4 ">
            <h2 class="text-xl font-bold text-slate-800">Data Murid</h2>
            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button" id="btnImportMurid"
                    class="flex items-center space-x-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 shadow-md">

                    <span>Import Murid</span>
                </button>
                <button type="button" data-modal-target="modalTambahMurid" data-modal-toggle="modalTambahMurid"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-all shadow-md shadow-blue-200">
                    <span>Tambah</span>
                </button>
            </div>
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
                    <h2 class="text-lg font-semibold text-slate-900 font-poppins">Daftar Murid</h2>
                    <p class="text-sm text-slate-600 mt-1">Total: {{ $students->total() }} Murid</p>
                </div>
                <div class="flex space-x-1">
                    @if (request('search_student'))
                        <a href="{{ route('students.index') }}"
                            class="w-8 h-8 p-3 mt-1 rounded-lg flex items-center justify-center bg-slate-100 text-slate-600 hover:bg-slate-200transition-colors duration-200 text-sm">
                            X
                        </a>
                    @endif
                    {{-- Search form --}}
                    <form id="searchForm" action="{{ route('students.index') }}" method="GET" class="flex items-center ">
                        <div class="relative flex items-center">
                            <input type="text" name="search_student" id="searchInput" placeholder="Cari Murid..."
                                value="{{ request('search_student') }}"
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Password</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">nis
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @php
                            $offset = ($students->currentPage() - 1) * $students->perPage();
                        @endphp
                        @forelse ($students as $student)
                            <tr class="hover:bg-slate-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    {{ $loop->iteration + $offset }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            <span
                                                class="text-blue-600 font-medium text-sm">{{ strtoupper(substr($student->user->name, 0, 1)) }}</span>
                                        </div>
                                        <div class="text-sm font-medium text-slate-900">{{ $student->user->name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $student->user->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    <span
                                        class="font-mono text-slate-400">{{ $student->user->plain_password ?? '••••••••' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm bg-slate-100 px-2 py-1 rounded">
                                        {{ $student->nis }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if ($student->class)
                                        <span
                                            class="inline-flex px-3 py-1 w-20 text-center rounded-full text-xs bg-blue-100 text-blue-800">
                                            {{ $student->class->name_class }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex px-2.5 py-0.5 rounded-full text-xs bg-amber-100 text-amber-800">
                                            Belum ditempatkan
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center space-x-2">
                                        <button type="button" onclick="openEditModal({{ $student->id }})"
                                            class="inline-flex items-center px-3 py-1.5 bg-amber-500 text-white text-xs font-medium rounded-lg hover:bg-amber-600 transition-colors duration-200">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                            Edit
                                        </button>
                                        <button type="button" onclick="openDeleteModal({{ $student->id }})"
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

                            {{-- Modal Edit --}}
                            <div id="editModal{{ $student->id }}" class="fixed inset-0 z-50 hidden" role="dialog"
                                aria-modal="true">
                                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
                                    onclick="closeEditModal({{ $student->id }})"></div>
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
                                                        <h3 class="text-lg font-semibold text-slate-900">Edit Murid</h3>
                                                        <p class="text-sm text-slate-500">{{ $student->name }}</p>
                                                    </div>
                                                </div>
                                                <button type="button" onclick="closeEditModal({{ $student->id }})"
                                                    class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full p-1 transition-colors duration-200">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            <form action="{{ route('students.update', $student->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="p-6 space-y-4">
                                                    <div>
                                                        <label class="block text-sm font-medium text-slate-700 mb-1">Nama
                                                            Murid</label>
                                                        <input type="text" name="name"
                                                            value="{{ $student->name }}" required
                                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors duration-200">
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                                                        <input type="email" name="email"
                                                            value="{{ $student->email }}" required
                                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors duration-200">
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                                                        <input type="text" name="password"
                                                            placeholder="Kosongkan jika tidak ingin mengubah password"
                                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors duration-200">
                                                        <p class="text-xs text-slate-500 mt-1">Biarkan kosong jika tidak
                                                            ingin mengubah password</p>
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-slate-700 mb-1">nis</label>
                                                        <input type="text" name="nis" value="{{ $student->nis }}"
                                                            required
                                                            class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-colors duration-200 font-mono">
                                                    </div>
                                                    <div>
                                                        <label
                                                            class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
                                                        {{-- Simplified select structure and updated placeholder text for single selection --}}
                                                        <select name="class_id" required
                                                            class="edit-class-select-{{ $student->id }} w-full"
                                                            id="editClassSelect{{ $student->id }}">
                                                            <option value="">-- Pilih Kelas --</option>
                                                            @php
                                                                $selectedClass = $student->class?->id;
                                                            @endphp
                                                            @foreach ($classes as $class)
                                                                <option value="{{ $class->id }}"
                                                                    {{ $class->id == $selectedClass ? 'selected' : '' }}>
                                                                    {{ $class->name_class }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <p class="text-xs text-slate-500 mt-1">Pilih kelas murid
                                                        </p>
                                                    </div>
                                                </div>
                                                <div
                                                    class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-end space-x-3">
                                                    <button type="button" onclick="closeEditModal({{ $student->id }})"
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
                            <div id="deleteModal{{ $student->id }}" class="fixed inset-0 z-50 hidden" role="dialog"
                                aria-modal="true">
                                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity"
                                    onclick="closeDeleteModal({{ $student->id }})"></div>
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
                                                        <h3 class="text-lg font-semibold text-slate-900">Hapus Murid</h3>
                                                        <p class="text-sm text-slate-500">Konfirmasi penghapusan</p>
                                                    </div>
                                                </div>
                                                <button type="button" onclick="closeDeleteModal({{ $student->id }})"
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
                                                    <p class="text-slate-600 mb-2">Anda yakin ingin menghapus murid:</p>
                                                    <p class="text-lg font-semibold text-slate-900 mb-4">
                                                        {{ $student->name }}</p>
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
                                            <form action="{{ route('students.destroy', $student->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div
                                                    class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-end space-x-3">
                                                    <button type="button"
                                                        onclick="closeDeleteModal({{ $student->id }})"
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
                                                        Hapus Murid
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-300 mb-4"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        </svg>
                                        <p class="text-slate-500 font-medium">Tidak ada data murid ditemukan</p>
                                        <p class="text-slate-400 text-sm mt-1">Coba ubah kata kunci pencarian</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $students->withQueryString()->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>

    {{-- Modal Import Murid --}}
    <div id="importMuridModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" id="importMuridBackdrop"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all">
                    <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600"
                                    viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 16l4-5h-3V4h-2v7H8l4 5zm8 2H4v2h16v-2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Import Data Murid</h3>
                                <p class="text-sm text-slate-500">Upload file Excel untuk import data</p>
                            </div>
                        </div>
                        <button type="button" id="closeImportMuridModal"
                            class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full p-1 transition-colors duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="p-6 space-y-5">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-slate-700">Upload File Excel</label>
                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-lg hover:border-green-400 transition-colors duration-200 cursor-pointer"
                                    id="dropzoneMurid">
                                    <div class="space-y-2 text-center">
                                        <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor"
                                            fill="none" viewBox="0 0 48 48">
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-slate-600 justify-center">
                                            <label for="file-upload-murid"
                                                class="relative cursor-pointer bg-white rounded-md font-medium text-green-600 hover:text-green-500 focus-within:outline-none">
                                                <span>Pilih file</span>
                                                <input id="file-upload-murid" name="file" type="file"
                                                    accept=".xlsx,.xls" required class="sr-only">
                                            </label>
                                            <p class="pl-1">atau drag and drop</p>
                                        </div>
                                        <p class="text-xs text-slate-500">Excel (.xlsx, .xls)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-end items-center">
                            <div class="flex space-x-3">
                                <button type="button" id="cancelImportMuridBtn"
                                    class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors duration-200">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                    </svg>
                                    Import
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah Murid --}}
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
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Tambah Murid Baru</h3>
                                <p class="text-sm text-slate-500">Isi data murid secara manual</p>
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
                    <form action="{{ route('students.store') }}" method="POST">
                        @csrf
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Murid <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="name" required placeholder="Masukkan nama lengkap murid"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email <span
                                        class="text-red-500">*</span></label>
                                <input type="email" name="email" required placeholder="contoh@email.com"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Password <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="password" required placeholder="Masukkan password"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">nis <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="nis" required placeholder="Masukkan nis"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 font-mono">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Kelas <span
                                        class="text-red-500">*</span></label>
                                {{-- Updated select name and class to be consistent with single selection logic --}}
                                <select name="class_id" id="addClassSelect" required class="add-class-select w-full">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name_class }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-slate-500 mt-1">Pilih kelas murid</p>
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
                                Simpan Murid
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
            setTimeout(function() {
                $('#editClassSelect' + id).select2({
                    placeholder: '-- Pilih Kelas --',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#editModal' + id)
                });
            }, 100);
        }

        function closeEditModal(id) {
            document.getElementById('editModal' + id).classList.add('hidden');
            document.body.style.overflow = '';
            if ($('#editClassSelect' + id).hasClass('select2-hidden-accessible')) {
                $('#editClassSelect' + id).select2('destroy');
            }
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
            setTimeout(function() {
                $('#addClassSelect').select2({
                    placeholder: '-- Pilih Kelas --',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#addModal')
                });
            }, 100);
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            if ($('#addClassSelect').hasClass('select2-hidden-accessible')) {
                $('#addClassSelect').val(null).trigger('change');
                $('#addClassSelect').select2('destroy');
            }
        }

        $(document).ready(function() {
            // Search functionality
            const searchToggle = document.getElementById('searchToggle');
            const searchInput = document.getElementById('searchInput');
            const searchForm = document.getElementById('searchForm');
            let isSearchOpen = {{ request('search_student') ? 'true' : 'false' }};

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

            // Import modal
            const importModal = document.getElementById('importMuridModal');
            const btnImport = document.getElementById('btnImportMurid');
            const closeImportModal = document.getElementById('closeImportMuridModal');
            const cancelImportBtn = document.getElementById('cancelImportMuridBtn');
            const importBackdrop = document.getElementById('importMuridBackdrop');

            function openImportModal() {
                importModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeImportModalFn() {
                importModal.classList.add('hidden');
                document.body.style.overflow = '';
            }

            btnImport.addEventListener('click', openImportModal);
            closeImportModal.addEventListener('click', closeImportModalFn);
            cancelImportBtn.addEventListener('click', closeImportModalFn);
            importBackdrop.addEventListener('click', closeImportModalFn);

            // Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    if (!importModal.classList.contains('hidden')) {
                        closeImportModalFn();
                    }
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
