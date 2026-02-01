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

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Total Murid</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $students->total() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0c-.181.028-.362.056-.543.084a14.944 14.944 0 01-2.457 1.667C14.743 11.67 13.46 12 12 12c-1.46 0-2.743-.33-3.957-.919a14.944 14.944 0 01-2.457-1.667 14.702 14.702 0 01-.543-.084" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Murid di Kelas</p>
                        @php
                            $studentsInClasses = $students->where('class_id', '!=', null)->count();
                        @endphp
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $studentsInClasses }}</p>
                        <p class="text-xs text-slate-500 mt-1">
                            @if($students->total() > 0)
                                {{ round(($studentsInClasses / $students->total()) * 100, 1) }}% dari total
                            @else
                                0%
                            @endif
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Rata-rata per Kelas</p>
                        @php
                            $totalClasses = $classes->count();
                            $avgPerClass = $totalClasses > 0 ? round($students->total() / $totalClasses, 1) : 0;
                        @endphp
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $avgPerClass }}</p>
                        <p class="text-xs text-slate-500 mt-1">Dari {{ $totalClasses }} kelas</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Control Panel -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <h2 class="text-xl font-bold text-slate-800">Data Murid</h2>

                    <div class="flex flex-wrap gap-3">
                        <!-- Quick Actions -->
                        <div class="flex items-center space-x-2">

                            <button onclick="showImportModal()"
                                class="text-sm flex items-center space-x-1 px-3 py-1.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-100 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                                <span>Import</span>
                            </button>
                        </div>

                        <!-- Add Button -->
                        <button type="button" onclick="openAddModal()"
                            class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="font-semibold">Tambah Murid</span>
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

            @if (session('info'))
                <div class="mx-6 mt-4 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg relative"
                    role="alert">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <strong class="font-medium">{{ session('info') }}</strong>
                        </div>
                        <button onclick="this.parentElement.parentElement.style.display='none'"
                            class="text-blue-600 hover:text-blue-800">
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
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru
                                </option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama
                                </option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama A-Z
                                </option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nama
                                    Z-A</option>
                                <option value="nis_asc" {{ request('sort') == 'nis_asc' ? 'selected' : '' }}>NIS
                                    Terkecil</option>
                                <option value="nis_desc" {{ request('sort') == 'nis_desc' ? 'selected' : '' }}>NIS
                                    Terbesar</option>
                            </select>
                        </div>

                        <!-- Class Filter -->
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-slate-600">Kelas:</span>
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
                        @if (request('search_student') || request('class_filter') || request('sort') || request('per_page') != 10)
                            <a href="{{ route('students.index') }}"
                                class="flex items-center px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition-colors duration-200 text-sm">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Reset Filter
                            </a>
                        @endif

                        <!-- Search form -->
                        <form id="searchForm" action="{{ route('students.index') }}" method="GET"
                            class="flex items-center">
                            <div class="relative flex items-center">
                                <input type="text" name="search_student" id="searchInput" placeholder="Cari murid..."
                                    value="{{ request('search_student') }}"
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
                                    Nama Murid
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Password
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider cursor-pointer"
                                onclick="sortColumn('nis')">
                                <div class="flex items-center">
                                    NIS
                                    @if (request('sort') == 'nis_asc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                    @elseif(request('sort') == 'nis_desc')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Kelas
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @php
                            $offset = ($students->currentPage() - 1) * $students->perPage();
                        @endphp
                        @foreach ($students as $student)
                            <tr class="hover:bg-slate-50 transition-colors duration-200"
                                id="student-{{ $student->id }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    {{ $loop->iteration + $offset }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            <span
                                                class="font-bold text-blue-600">{{ substr($student->user->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-slate-900">{{ $student->user->name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    {{ $student->user->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    <div class="group relative">
                                        <span class="font-mono text-slate-400 cursor-help"
                                            title="{{ $student->user->plain_password ?? 'Password default' }}">
                                            {{ str_repeat('•', 8) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-mono">
                                    <span class="bg-slate-100 px-2 py-1 rounded">{{ $student->nis }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($student->class)
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                            {{ $student->class->name_class }}
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800">
                                            Belum ditempatkan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center space-x-2">
                                        <button type="button" onclick="quickView({{ $student->id }})"
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
                                        <button type="button" onclick="openEditModal({{ $student->id }})"
                                            class="inline-flex items-center px-3 py-1.5 bg-amber-500 text-white text-xs font-medium rounded-lg hover:bg-amber-600 transition-colors duration-200 tooltip"
                                            title="Edit Data">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </button>
                                        <form action="{{ route('students.destroy', $student->id) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete(this)"
                                                class="inline-flex items-center px-3 py-1.5 bg-red-500 text-white text-xs font-medium rounded-lg hover:bg-red-600 transition-colors duration-200 tooltip"
                                                title="Hapus Data">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            @if ($students->isEmpty())
                <div class="text-center py-12">
                    <div class="mx-auto w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0c-.181.028-.362.056-.543.084a14.944 14.944 0 01-2.457 1.667C14.743 11.67 13.46 12 12 12c-1.46 0-2.743-.33-3.957-.919a14.944 14.944 0 01-2.457-1.667 14.702 14.702 0 01-.543-.084" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900 mb-2">Tidak ada data murid</h3>
                    <p class="text-slate-600 mb-6">Mulai dengan menambahkan murid baru.</p>
                </div>
            @endif

            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                <div>
                    {{ $students->withQueryString()->links('vendor.pagination.tailwind') }}
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
                            <h3 class="text-lg font-semibold text-slate-900">Detail Murid</h3>
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
                                <h3 class="text-lg font-semibold text-slate-900">Import Murid</h3>
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

                    <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="p-6">
                            <div class="mb-4">
                                <p class="text-sm text-slate-600 mb-2">Format file yang didukung: .csv, .xlsx, .xls</p>
                                <p class="text-sm text-slate-600">Struktur kolom yang diperlukan: Nama, Email, NIS,
                                    Password (opsional), Kelas (opsional)</p>
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
                                <p class="text-xs text-slate-500 mt-1">Password minimal 6 karakter</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">NIS <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="nis" required
                                    placeholder="Masukkan NIS (6-10 digit angka)"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 font-mono"
                                    value="{{ old('nis') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
                                <select name="class_id"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                    <option value="">-- Pilih Kelas (Opsional) --</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}"
                                            {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name_class }}
                                        </option>
                                    @endforeach
                                </select>
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
                                Simpan Murid
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="editModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeEditModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-all">
                    <div
                        class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-amber-50 rounded-t-xl">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                                <svg class="h-5 w-5 text-amber-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Edit Data Murid</h3>
                                <p class="text-sm text-slate-500">Perbarui data murid</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeEditModal()"
                            class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full p-1 transition-colors duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form id="editForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="p-6 space-y-4">
                            <input type="hidden" name="id" id="editId">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Murid <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="name" required placeholder="Masukkan nama lengkap murid"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                    id="editName">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email <span
                                        class="text-red-500">*</span></label>
                                <input type="email" name="email" required placeholder="contoh@email.com"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                    id="editEmail">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                                <input type="text" name="password" placeholder="Kosongkan jika tidak diubah"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                <p class="text-xs text-slate-500 mt-1">Biarkan kosong jika tidak ingin mengubah password
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">NIS <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="nis" required
                                    placeholder="Masukkan NIS (6-10 digit angka)"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 font-mono"
                                    id="editNis">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
                                <select name="class_id"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                    id="editClassId">
                                    <option value="">-- Pilih Kelas (Opsional) --</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name_class }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div
                            class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-end space-x-3">
                            <button type="button" onclick="closeEditModal()"
                                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors duration-200">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition-colors duration-200">
                                Perbarui Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function openEditModal(id) {
            fetch(`/students/${id}/edit`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('editId').value = data.id;
                    document.getElementById('editName').value = data.user.name;
                    document.getElementById('editEmail').value = data.user.email;
                    document.getElementById('editNis').value = data.nis;
                    document.getElementById('editClassId').value = data.class_id || '';

                    const form = document.getElementById('editForm');
                    form.action = `/students/${data.id}`;

                    document.getElementById('editModal').classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                })
                .catch(error => {
                    console.error('Error loading student data:', error);
                    alert('Gagal memuat data murid. Silakan coba lagi.');
                });
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function confirmDelete(button) {
            const studentRow = button.closest('tr');
            const studentName = studentRow.querySelector('.text-sm.font-medium').textContent;

            if (confirm(`Apakah Anda yakin ingin menghapus murid "${studentName}"?`)) {
                button.closest('form').submit();
            }
        }

        function showImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function quickView(id) {
            fetch(`/students/${id}/detail`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const content = document.getElementById('quickViewContent');
                    content.innerHTML = `
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center">
                                    <span class="text-2xl font-bold text-blue-600">${data.name.charAt(0)}</span>
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold text-slate-900">${data.name}</h4>
                                    <p class="text-sm text-slate-600">${data.email}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">NIS</label>
                                    <p class="text-lg font-semibold text-slate-900">${data.nis}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
                                    <p class="text-sm ${data.class ? 'text-blue-600' : 'text-amber-600'} font-medium">
                                        ${data.class || 'Belum ditempatkan'}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Password Default</label>
                                <p class="text-sm font-mono text-slate-600">${data.password || 'Tidak ada'}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Dibuat</label>
                                <p class="text-sm text-slate-600">${data.created_at}</p>
                            </div>
                        </div>
                    `;

                    document.getElementById('quickViewModal').classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                })
                .catch(error => {
                    console.error('Error loading student details:', error);
                    alert('Gagal memuat detail murid. Silakan coba lagi.');
                });
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
            } else if (column === 'nis') {
                if (currentSort === 'nis_asc') {
                    newSort = 'nis_desc';
                } else if (currentSort === 'nis_desc') {
                    newSort = 'newest';
                } else {
                    newSort = 'nis_asc';
                }
            }

            url.searchParams.set('sort', newSort);
            window.location.href = url.toString();
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // File name preview for import
            document.getElementById('importFile').addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name;
                if (fileName) {
                    document.getElementById('fileName').textContent = `File: ${fileName}`;
                }
            });

            // Initialize select2
            $('select').select2({
                width: '100%',
                placeholder: 'Pilih opsi',
                allowClear: true
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Escape to close modals
                if (e.key === 'Escape') {
                    closeAllModals();
                }

                // Ctrl/Cmd + N for new student
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
        });

        function closeAllModals() {
            document.querySelectorAll('[id$="Modal"]').forEach(modal => {
                modal.classList.add('hidden');
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
    </style>
@endsection
