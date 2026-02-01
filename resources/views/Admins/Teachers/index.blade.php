@extends('layouts.app')

@section('content')
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <div class="p-6 space-y-6">
        <!-- Hero Section -->
        <div
            class="relative bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 sm:p-8 mb-6 overflow-hidden border border-blue-100">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 font-poppins">Data Guru</h1>
                    <nav class="flex mt-2 text-sm text-slate-500 font-medium" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-2">
                            <li class="inline-flex items-center">
                                <a href="#" class="hover:text-blue-600 transition-colors">Dashboard</a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <span class="mx-2 text-slate-400">•</span>
                                    <span class="text-slate-900 font-semibold">Guru</span>
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

        <!-- Stats Cards - Diperbaiki -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Total Guru</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $teachers->total() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Guru Aktif</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">
                            {{ $teachers->where('teacher.teacherClasses', '!=', null)->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Belum Aktif</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">
                            {{ $teachers->where('teacher.teacherClasses', null)->count() }}
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Total Mapel</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $subjects->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Control Panel -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <h2 class="text-xl font-bold text-slate-800">Data Guru</h2>

                    <div class="flex flex-wrap gap-3">
                        <!-- Import Button -->
                        <button onclick="showImportModal()"
                            class="text-sm flex items-center space-x-1 px-3 py-1.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                            </svg>
                            <span>Import</span>
                        </button>

                        <!-- Add Button -->
                        <button type="button" onclick="openAddModal()"
                            class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="font-semibold">Tambah Guru</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @if (session('success'))
                <div class="mx-6 mt-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg relative"
                    role="alert">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <strong class="font-medium">{{ session('success') }}</strong>
                        </div>
                        <button onclick="this.parentElement.parentElement.style.display='none'"
                            class="text-green-600 hover:text-green-800">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg relative"
                    role="alert">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <strong class="font-medium">{{ session('error') }}</strong>
                        </div>
                        <button onclick="this.parentElement.parentElement.style.display='none'"
                            class="text-red-600 hover:text-red-800">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg relative"
                    role="alert">
                    <div class="flex items-start justify-between">
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
                        <button onclick="this.parentElement.parentElement.style.display='none'"
                            class="text-red-600 hover:text-red-800">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Table Controls -->
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center space-x-4">
                        <!-- Items Per Page -->
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-slate-600">Tampilkan:</span>
                            <select onchange="changePerPage(this.value)"
                                class="text-sm border border-slate-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="5" {{ request('per_page', 10) == 5 ? 'selected' : '' }}>5</option>
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                            <span class="text-sm text-slate-600">entri</span>
                        </div>

                        <!-- Sort Options -->
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-slate-600">Urutkan:</span>
                            <select onchange="applySort(this.value)"
                                class="text-sm border border-slate-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>
                                    Terbaru</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama
                                </option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama A-Z
                                </option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nama Z-A
                                </option>
                            </select>
                        </div>

                        <!-- Class Filter -->
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-slate-600">Filter Kelas:</span>
                            <select id="classFilter" onchange="applyFilters()"
                                class="text-sm border border-slate-300 rounded-lg px-3 py-1.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Kelas</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}"
                                        {{ request('class_filter') == $class->id ? 'selected' : '' }}>
                                        {{ $class->name_class }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex space-x-2">
                        <!-- Reset Filter -->
                        @if (request('search_teacher') || request('class_filter') || request('sort') || request('per_page') != 10)
                            <a href="{{ route('teachers.index') }}"
                                class="flex items-center px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition-colors duration-200 text-sm">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Reset Filter
                            </a>
                        @endif

                        <!-- Search form -->
                        <form id="searchForm" action="{{ route('teachers.index') }}" method="GET"
                            class="flex items-center">
                            <div class="relative flex items-center">
                                <input type="text" name="search_teacher" id="searchInput"
                                    placeholder="Cari nama/email/NIP..." value="{{ request('search_teacher') }}"
                                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                <button type="submit"
                                    class="absolute right-0 flex items-center justify-center w-10 h-10 text-blue-600 hover:text-blue-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.35-4.35"></path>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                No
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider cursor-pointer"
                                onclick="sortColumn('name')">
                                <div class="flex items-center">
                                    Nama Guru
                                    @if (request('sort') == 'name_asc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                    @elseif(request('sort') == 'name_desc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Email
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider cursor-pointer"
                                onclick="sortColumn('nip')">
                                <div class="flex items-center">
                                    NIP
                                    @if (request('sort') == 'nip_asc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                    @elseif(request('sort') == 'nip_desc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Kelas & Mapel
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @php
                            $offset = ($teachers->currentPage() - 1) * $teachers->perPage();
                        @endphp
                        @foreach ($teachers as $teacher)
                            <tr class="hover:bg-slate-50 transition-colors duration-200"
                                id="teacher-{{ $teacher->id }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    {{ $loop->iteration + $offset }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            <span
                                                class="font-bold text-blue-600">{{ substr($teacher->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-slate-900">{{ $teacher->name }}</div>
                                            <div class="text-xs text-slate-500">
                                                {{ $teacher->teacher->sapaan ?? 'Guru' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ $teacher->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-mono">
                                    @if ($teacher->teacher && $teacher->teacher->nip)
                                        <span
                                            class="bg-blue-50 px-2 py-1 rounded text-xs">{{ $teacher->teacher->nip }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    @if ($teacher->teacher && $teacher->teacher->teacherClasses->isNotEmpty())
                                        <div class="space-y-1">
                                            @foreach ($teacher->teacher->teacherClasses as $tc)
                                                <div class="flex items-start">
                                                    <span
                                                        class="inline-flex px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-800 mr-2">
                                                        {{ $tc->classes->name_class ?? '-' }}
                                                    </span>
                                                    <span class="text-xs text-slate-500 truncate max-w-xs">
                                                        {{ $tc->subjects->pluck('name_subject')->join(', ') }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800">
                                            Belum ditempatkan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($teacher->teacher && $teacher->teacher->teacherClasses->isNotEmpty())
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-800">
                                            Aktif
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800">
                                            Belum Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center space-x-2">
                                        <button type="button"
                                            onclick="quickView({{ $teacher->teacher ? $teacher->teacher->id : $teacher->id }})"
                                            class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white text-xs font-medium rounded-lg hover:bg-blue-600 transition-colors duration-200 tooltip"
                                            title="Lihat Detail">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button type="button" onclick="openEditModal({{ $teacher->id }})"
                                            class="inline-flex items-center px-3 py-1.5 bg-amber-500 text-white text-xs font-medium rounded-lg hover:bg-amber-600 transition-colors duration-200 tooltip"
                                            title="Edit Data">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="openDeleteModal({{ $teacher->id }})"
                                            class="inline-flex items-center px-3 py-1.5 bg-red-500 text-white text-xs font-medium rounded-lg hover:bg-red-600 transition-colors duration-200 tooltip"
                                            title="Hapus Data">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            @if ($teachers->isEmpty())
                <div class="text-center py-12">
                    <div class="mx-auto w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900 mb-2">Tidak ada data guru</h3>
                    <p class="text-slate-600 mb-6">Mulai dengan menambahkan guru baru.</p>
                
                </div>
            @endif

            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="text-sm text-slate-600">
                        Menampilkan {{ $teachers->firstItem() ?? 0 }} - {{ $teachers->lastItem() ?? 0 }} dari
                        {{ $teachers->total() }} guru
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-slate-600">
                            Halaman {{ $teachers->currentPage() }} dari {{ $teachers->lastPage() }}
                        </div>
                        <div>
                            {{ $teachers->withQueryString()->links('vendor.pagination.tailwind') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick View Modal --}}
    <div id="quickViewModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeQuickViewModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-slate-900">Detail Guru</h3>
                            <button type="button" onclick="closeQuickViewModal()"
                                class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full p-1 transition-colors duration-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div id="quickViewContent" class="space-y-4">
                            <!-- Content will be loaded here -->
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" onclick="closeQuickViewModal()"
                                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors duration-200">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Import Modal --}}
    <div id="importModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeImportModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-all">
                    <div
                        class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-blue-50 rounded-t-xl">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Import Guru</h3>
                                <p class="text-sm text-slate-500">Unggah file CSV atau Excel</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeImportModal()"
                            class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full p-1 transition-colors duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form action="{{ route('teachers.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="p-6">
                            <div class="mb-4">
                                <p class="text-sm text-slate-600 mb-2">Format file yang didukung: .csv, .xlsx, .xls</p>
                                <p class="text-sm text-slate-600">Struktur kolom yang diperlukan: Nama, Email, Password,
                                    NIP (opsional)</p>
                            </div>

                            <div class="border-2 border-dashed border-slate-300 rounded-xl p-8 text-center mb-4">
                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                                <p class="mt-2 text-sm text-slate-600">Drag & drop file di sini atau</p>
                                <input type="file" id="importFile" name="file" accept=".csv,.xlsx,.xls"
                                    class="hidden">
                                <button type="button" onclick="document.getElementById('importFile').click()"
                                    class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                    Pilih File
                                </button>
                                <p id="fileName" class="mt-2 text-sm text-slate-500"></p>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeImportModal()"
                                    class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-100 transition-colors duration-200">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                    Import Data
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Modal --}}
    <div id="addModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeAddModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-all">
                    <div
                        class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-blue-50 rounded-t-xl">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Tambah Guru Baru</h3>
                                <p class="text-sm text-slate-500">Isi data guru baru</p>
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

                    <form action="{{ route('teachers.store') }}" method="POST">
                        @csrf
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Guru <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="name" required placeholder="Masukkan nama lengkap guru"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                    value="{{ old('name') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email <span
                                        class="text-red-500">*</span></label>
                                <input type="email" name="email" required placeholder="contoh@email.com"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                    value="{{ old('email') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Password <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="password" required placeholder="Masukkan password"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                    value="{{ old('password') }}">
                                <p class="text-xs text-slate-500 mt-1">Password minimal 6 karakter, tidak boleh mengandung
                                    spasi</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">NIP (Opsional)</label>
                                <input type="text" name="nip" placeholder="Masukkan NIP"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 font-mono"
                                    value="{{ old('nip') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Sapaan (Opsional)</label>
                                <input type="text" name="sapaan" placeholder="Contoh: Bapak, Ibu, Pak, Bu"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                    value="{{ old('sapaan') }}">
                            </div>

                            <!-- Kelas dan Mapel -->
                            <div class="border-t border-slate-200 pt-4 mt-4">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Penempatan Kelas &
                                    Mapel</label>
                                <div id="classContainer">
                                    <!-- Container untuk dinamis kelas -->
                                </div>
                                <button type="button" onclick="addClassField()"
                                    class="mt-2 text-sm text-blue-600 hover:text-blue-800 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Kelas
                                </button>
                            </div>
                        </div>

                        <div
                            class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-end space-x-3">
                            <button type="button" onclick="closeAddModal()"
                                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors duration-200">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                Simpan Guru
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="editModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <!-- Structure will be populated by JavaScript -->
    </div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <!-- Structure will be populated by JavaScript -->
    </div>

    <script>
        // Initialize classes data
        const allClasses = @json($classes);
        const allSubjects = @json($subjects);

        // Modal functions
        function openAddModal() {
            // Reset class container
            const container = document.getElementById('classContainer');
            container.innerHTML = '';
            addClassField(); // Add one initial class field

            document.getElementById('addModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function addClassField() {
            const container = document.getElementById('classContainer');
            const classCount = container.children.length;
            const classId = `class_${classCount}`;

            const classField = document.createElement('div');
            classField.className = 'mb-4 p-3 border border-slate-200 rounded-lg';
            classField.innerHTML = `
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-slate-700">Kelas ${classCount + 1}</span>
                    <button type="button" onclick="removeClassField(this)" class="text-red-500 hover:text-red-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mb-2">
                    <label class="block text-xs text-slate-600 mb-1">Pilih Kelas</label>
                    <select name="class_id[]" required class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm class-select">
                        <option value="">Pilih Kelas</option>
                        ${allClasses.map(cls => `<option value="${cls.id}">${cls.name_class}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-600 mb-1">Pilih Mata Pelajaran</label>
                    <div class="space-y-1" id="subjects_${classId}">
                        ${allSubjects.map(subject => `
                                <label class="inline-flex items-center mr-3 mb-1">
                                    <input type="checkbox" name="subjects[${classId}][]" value="${subject.id}" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-1 text-sm text-slate-700">${subject.name_subject}</span>
                                </label>
                            `).join('')}
                    </div>
                </div>
            `;

            container.appendChild(classField);

            // Initialize select2 for the new select
            $(classField.querySelector('.class-select')).select2({
                width: '100%',
                placeholder: 'Pilih Kelas'
            });
        }

        function removeClassField(button) {
            const container = document.getElementById('classContainer');
            if (container.children.length > 1) {
                button.closest('.mb-4').remove();
                // Renumber classes
                const classFields = container.querySelectorAll('.mb-4');
                classFields.forEach((field, index) => {
                    field.querySelector('span').textContent = `Kelas ${index + 1}`;
                });
            }
        }

        function openEditModal(id) {
            fetch(`/teachers/${id}/edit`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    const modal = document.getElementById('editModal');

                    // Build class and subjects HTML
                    let classFieldsHTML = '';
                    if (data.teacherClasses && data.teacherClasses.length > 0) {
                        data.teacherClasses.forEach((tc, index) => {
                            const classId = `class_${index}`;
                            const subjectsArray = tc.subjects ? tc.subjects.map(s => s.id) : [];

                            classFieldsHTML += `
                            <div class="mb-4 p-3 border border-slate-200 rounded-lg">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-slate-700">Kelas ${index + 1}</span>
                                    ${index > 0 ? '<button type="button" onclick="removeClassField(this)" class="text-red-500 hover:text-red-700"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>' : ''}
                                </div>
                                <div class="mb-2">
                                    <label class="block text-xs text-slate-600 mb-1">Pilih Kelas</label>
                                    <select name="class_id[]" required class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm class-select">
                                        <option value="">Pilih Kelas</option>
                                        ${allClasses.map(cls => `<option value="${cls.id}" ${cls.id == tc.classes_id ? 'selected' : ''}>${cls.name_class}</option>`).join('')}
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs text-slate-600 mb-1">Pilih Mata Pelajaran</label>
                                    <div class="space-y-1">
                                        ${allSubjects.map(subject => `
                                                <label class="inline-flex items-center mr-3 mb-1">
                                                    <input type="checkbox" name="subjects[${classId}][]" value="${subject.id}"
                                                        ${subjectsArray.includes(subject.id) ? 'checked' : ''}
                                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                                    <span class="ml-1 text-sm text-slate-700">${subject.name_subject}</span>
                                                </label>
                                            `).join('')}
                                    </div>
                                </div>
                            </div>
                        `;
                        });
                    } else {
                        classFieldsHTML = `
                        <div class="mb-4 p-3 border border-slate-200 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-slate-700">Kelas 1</span>
                            </div>
                            <div class="mb-2">
                                <label class="block text-xs text-slate-600 mb-1">Pilih Kelas</label>
                                <select name="class_id[]" required class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm class-select">
                                    <option value="">Pilih Kelas</option>
                                    ${allClasses.map(cls => `<option value="${cls.id}">${cls.name_class}</option>`).join('')}
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-600 mb-1">Pilih Mata Pelajaran</label>
                                <div class="space-y-1">
                                    ${allSubjects.map(subject => `
                                            <label class="inline-flex items-center mr-3 mb-1">
                                                <input type="checkbox" name="subjects[class_0][]" value="${subject.id}" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                                <span class="ml-1 text-sm text-slate-700">${subject.name_subject}</span>
                                            </label>
                                        `).join('')}
                                </div>
                            </div>
                        </div>
                    `;
                    }

                    modal.innerHTML = `
                    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeEditModal()"></div>
                    <div class="fixed inset-0 z-10 overflow-y-auto">
                        <div class="flex min-h-full items-center justify-center p-4">
                            <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-all">
                                <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-blue-50 rounded-t-xl">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-slate-900">Edit Guru</h3>
                                            <p class="text-sm text-slate-500">Ubah data guru</p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="closeEditModal()"
                                        class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full p-1 transition-colors duration-200">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>

                                <form action="/teachers/${id}" method="POST" id="editForm">
                                    @csrf
                                    @method('PUT')
                                    <div class="p-6 space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Guru <span class="text-red-500">*</span></label>
                                            <input type="text" name="name" required placeholder="Masukkan nama lengkap guru"
                                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                                value="${data.name || ''}">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Email <span class="text-red-500">*</span></label>
                                            <input type="email" name="email" required placeholder="contoh@email.com"
                                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                                value="${data.email || ''}">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Password (Kosongkan jika tidak diubah)</label>
                                            <input type="text" name="password" placeholder="Masukkan password baru"
                                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                            <p class="text-xs text-slate-500 mt-1">Password minimal 6 karakter, tidak boleh mengandung spasi</p>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">NIP (Opsional)</label>
                                            <input type="text" name="nip" placeholder="Masukkan NIP"
                                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 font-mono"
                                                value="${data.teacher?.nip || ''}">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Sapaan (Opsional)</label>
                                            <input type="text" name="sapaan" placeholder="Contoh: Bapak, Ibu, Pak, Bu"
                                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                                value="${data.teacher?.sapaan || ''}">
                                        </div>

                                        <!-- Kelas dan Mapel -->
                                        <div class="border-t border-slate-200 pt-4 mt-4">
                                            <label class="block text-sm font-medium text-slate-700 mb-2">Penempatan Kelas & Mapel</label>
                                            <div id="editClassContainer">
                                                ${classFieldsHTML}
                                            </div>
                                            <button type="button" onclick="addEditClassField()" class="mt-2 text-sm text-blue-600 hover:text-blue-800 flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Tambah Kelas
                                            </button>
                                        </div>
                                    </div>

                                    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-end space-x-3">
                                        <button type="button" onclick="closeEditModal()"
                                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors duration-200">
                                            Batal
                                        </button>
                                        <button type="submit"
                                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                            Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                `;

                    modal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';

                    // Initialize select2 for edit modal
                    $('.class-select').select2({
                        width: '100%',
                        placeholder: 'Pilih Kelas'
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Gagal memuat data guru');
                });
        }

        function addEditClassField() {
            const container = document.getElementById('editClassContainer');
            const classCount = container.children.length;
            const classId = `class_${classCount}`;

            const classField = document.createElement('div');
            classField.className = 'mb-4 p-3 border border-slate-200 rounded-lg';
            classField.innerHTML = `
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-slate-700">Kelas ${classCount + 1}</span>
                    <button type="button" onclick="removeEditClassField(this)" class="text-red-500 hover:text-red-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="mb-2">
                    <label class="block text-xs text-slate-600 mb-1">Pilih Kelas</label>
                    <select name="class_id[]" required class="w-full px-3 py-1.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm class-select">
                        <option value="">Pilih Kelas</option>
                        ${allClasses.map(cls => `<option value="${cls.id}">${cls.name_class}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-slate-600 mb-1">Pilih Mata Pelajaran</label>
                    <div class="space-y-1">
                        ${allSubjects.map(subject => `
                                <label class="inline-flex items-center mr-3 mb-1">
                                    <input type="checkbox" name="subjects[${classId}][]" value="${subject.id}" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-1 text-sm text-slate-700">${subject.name_subject}</span>
                                </label>
                            `).join('')}
                    </div>
                </div>
            `;

            container.appendChild(classField);

            // Initialize select2 for the new select
            $(classField.querySelector('.class-select')).select2({
                width: '100%',
                placeholder: 'Pilih Kelas'
            });
        }

        function removeEditClassField(button) {
            const container = document.getElementById('editClassContainer');
            if (container.children.length > 1) {
                button.closest('.mb-4').remove();
                // Renumber classes
                const classFields = container.querySelectorAll('.mb-4');
                classFields.forEach((field, index) => {
                    field.querySelector('span').textContent = `Kelas ${index + 1}`;
                });
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function openDeleteModal(id) {
            const modal = document.getElementById('deleteModal');
            modal.innerHTML = `
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all">
                            <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-red-50 rounded-t-xl">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-slate-900">Hapus Guru</h3>
                                        <p class="text-sm text-slate-500">Konfirmasi penghapusan data</p>
                                    </div>
                                </div>
                                <button type="button" onclick="closeDeleteModal()"
                                    class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full p-1 transition-colors duration-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="p-6">
                                <div class="flex items-center justify-center mb-4">
                                    <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                    </div>
                                </div>

                                <p class="text-center text-slate-700 mb-2">Apakah Anda yakin ingin menghapus guru ini?</p>
                                <p class="text-center text-sm text-slate-500 mb-6">Data yang dihapus tidak dapat dikembalikan</p>

                                <form action="/teachers/${id}" method="POST" id="deleteForm">
                                    @csrf
                                    @method('DELETE')
                                    <div class="flex justify-center space-x-3">
                                        <button type="button" onclick="closeDeleteModal()"
                                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors duration-200">
                                            Batal
                                        </button>
                                        <button type="submit"
                                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors duration-200">
                                            Hapus
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function showImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        async function quickView(id) {
            try {
                const response = await fetch(`/teachers/${id}/detail`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();

                const content = document.getElementById('quickViewContent');
                let kelasHtml = '';

                if (data && data.length > 0) {
                    data.forEach(item => {
                        const subjects = item.subjects && item.subjects.length > 0 ?
                            item.subjects.join(', ') :
                            'Belum ada mapel';

                        kelasHtml += `
                            <div class="border border-slate-200 rounded-lg p-3 mb-2">
                                <div class="font-semibold text-slate-900 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    Kelas: ${item.class || '-'}
                                </div>
                                <div class="text-sm text-slate-600 mt-2 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                    Mata Pelajaran: ${subjects}
                                </div>
                            </div>
                        `;
                    });
                } else {
                    kelasHtml = `
                        <div class="text-center py-4">
                            <svg class="w-12 h-12 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-slate-500 font-medium mt-2">Belum ada data mengajar</p>
                            <p class="text-slate-400 text-sm mt-1">Guru ini belum ditempatkan di kelas manapun</p>
                        </div>
                    `;
                }

                // Get teacher name from table row
                const teacherRow = document.querySelector(`#teacher-${id}`);
                const teacherName = teacherRow ? teacherRow.querySelector('td:nth-child(2) .text-sm.font-medium')
                    .textContent : 'Guru';
                const teacherEmail = teacherRow ? teacherRow.querySelector('td:nth-child(3)').textContent : '';
                const teacherNip = teacherRow ? teacherRow.querySelector('td:nth-child(4) span').textContent : '-';
                const teacherStatus = data && data.length > 0 ? 'Aktif' : 'Belum Aktif';
                const statusColor = data && data.length > 0 ? 'text-emerald-600' : 'text-amber-600';

                content.innerHTML = `
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center">
                                <span class="text-2xl font-bold text-blue-600">${teacherName.charAt(0)}</span>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-slate-900">${teacherName}</h4>
                                <p class="text-sm text-slate-600">${teacherEmail}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">NIP</label>
                                <p class="text-sm font-medium text-slate-900">${teacherNip}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                                <p class="text-sm ${statusColor} font-medium">
                                    ${teacherStatus}
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kelas & Mata Pelajaran</label>
                            <div class="space-y-2">
                                ${kelasHtml}
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('quickViewModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';

            } catch (error) {
                console.error('Error:', error);
                alert('Gagal memuat data detail guru');
            }
        }

        function closeQuickViewModal() {
            document.getElementById('quickViewModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Filter functions
        function applyFilters() {
            const classFilter = document.getElementById('classFilter').value;
            const url = new URL(window.location.href);

            if (classFilter) {
                url.searchParams.set('class_filter', classFilter);
            } else {
                url.searchParams.delete('class_filter');
            }

            window.location.href = url.toString();
        }

        function changePerPage(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', value);
            window.location.href = url.toString();
        }

        function applySort(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', value);
            window.location.href = url.toString();
        }

        function sortColumn(column) {
            const url = new URL(window.location.href);
            const currentSort = url.searchParams.get('sort');

            let newSort = 'newest';
            if (column === 'name') {
                if (currentSort === 'name_asc') {
                    newSort = 'name_desc';
                } else if (currentSort === 'name_desc') {
                    newSort = 'newest';
                } else {
                    newSort = 'name_asc';
                }
            } else if (column === 'nip') {
                if (currentSort === 'nip_asc') {
                    newSort = 'nip_desc';
                } else if (currentSort === 'nip_desc') {
                    newSort = 'newest';
                } else {
                    newSort = 'nip_asc';
                }
            }

            url.searchParams.set('sort', newSort);
            window.location.href = url.toString();
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // File name preview for import
            document.getElementById('importFile')?.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name;
                if (fileName) {
                    document.getElementById('fileName').textContent = `File: ${fileName}`;
                }
            });

            // Initialize Select2 for class filter
            $('#classFilter').select2({
                placeholder: 'Semua Kelas',
                allowClear: true,
                width: '150px'
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Escape to close modals
                if (e.key === 'Escape') {
                    closeAllModals();
                }

                // Ctrl/Cmd + N for new teacher
                if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                    e.preventDefault();
                    openAddModal();
                }

                // Ctrl/Cmd + F for search
                if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                    e.preventDefault();
                    document.getElementById('searchInput').focus();
                }
            });

            // Add tooltip functionality
            const tooltips = document.querySelectorAll('.tooltip');
            tooltips.forEach(tooltip => {
                tooltip.addEventListener('mouseenter', function(e) {
                    const tooltipText = this.getAttribute('title');
                    const tooltipEl = document.createElement('div');
                    tooltipEl.className =
                        'fixed z-50 px-2 py-1 text-xs font-medium text-white bg-slate-900 rounded shadow-lg';
                    tooltipEl.textContent = tooltipText;
                    tooltipEl.style.top = (e.clientY - 30) + 'px';
                    tooltipEl.style.left = (e.clientX + 10) + 'px';
                    tooltipEl.id = 'tooltip-' + Date.now();
                    document.body.appendChild(tooltipEl);

                    this.setAttribute('data-tooltip-id', tooltipEl.id);
                });

                tooltip.addEventListener('mouseleave', function() {
                    const tooltipId = this.getAttribute('data-tooltip-id');
                    if (tooltipId) {
                        const tooltipEl = document.getElementById(tooltipId);
                        if (tooltipEl) {
                            document.body.removeChild(tooltipEl);
                        }
                    }
                });
            });
        });

        function closeAllModals() {
            const modals = ['addModal', 'editModal', 'deleteModal', 'importModal', 'quickViewModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('hidden');
                }
            });
            document.body.style.overflow = '';
        }
    </script>

    <style>
        .tooltip {
            position: relative;
        }

        .tooltip:hover::after {
            content: attr(title);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #1e293b;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 50;
            margin-bottom: 4px;
        }

        .tooltip:hover::before {
            content: '';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 4px;
            border-style: solid;
            border-color: #1e293b transparent transparent transparent;
            z-index: 50;
        }

        .select2-container {
            z-index: 10000;
        }

        .select2-selection {
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            height: 2.5rem !important;
        }

        .select2-selection:focus {
            outline: 2px solid #3b82f6 !important;
            outline-offset: 2px !important;
        }
    </style>
@endsection
