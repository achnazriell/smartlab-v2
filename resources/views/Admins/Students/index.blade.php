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
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Murid di Kelas</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $studentsInClasses }}</p>
                        <p class="text-xs text-slate-500 mt-1">
                            @if ($students->total() > 0)
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
                        <button onclick="openPrintModal()"
                            class="text-sm flex items-center space-x-1 px-3 py-1.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>Print Absensi</span>
                        </button>
                        <button onclick="openExportModal()"
                            class="inline-flex items-center px-3 py-1.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-100 transition-colors text-sm">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 4v12m0 0l-4-4m4 4l4-4" />
                            </svg>
                            <span>Ekspor</span>
                        </button>

                        <button onclick="showImportModal()"
                            class="text-sm flex items-center space-x-1 px-3 py-1.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                            </svg>
                            <span>Impor</span>
                        </button>
                        <button type="button" onclick="openAddModal()"
                            class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="font-semibold">Tambah Murid</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            @if (session('success'))
                <div class="mx-6 mt-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg"
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
                            class="text-green-600 hover:text-green-800">&times;</button>
                    </div>
                </div>
            @endif
            @if (session('error'))
                <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg" role="alert">
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
                            class="text-red-600 hover:text-red-800">&times;</button>
                    </div>
                </div>
            @endif
            @if (session('info'))
                <div class="mx-6 mt-4 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg"
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
                            class="text-blue-600 hover:text-blue-800">&times;</button>
                    </div>
                </div>
            @endif
            @if ($errors->any())
                <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg" role="alert">
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
                            class="text-red-600 hover:text-red-800">&times;</button>
                    </div>
                </div>
            @endif
            @if (session('import_stats'))
                @include('components.student-import-alert')
            @endif

            <!-- Filter Section -->
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <form method="GET" action="{{ route('students.index') }}"
                    class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search_student" value="{{ request('search_student') }}"
                                placeholder="Cari nama, NIS, email..."
                                class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Tahun Ajaran -->
                    <div class="md:col-span-2">
                        <select name="academic_year"
                            class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Semua Tahun</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}"
                                    {{ request('academic_year') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Angkatan -->
                    <div class="md:col-span-2">
                        <select name="grade"
                            class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Semua Angkatan</option>
                            <option value="X" {{ request('grade') == 'X' ? 'selected' : '' }}>X</option>
                            <option value="XI" {{ request('grade') == 'XI' ? 'selected' : '' }}>XI</option>
                            <option value="XII" {{ request('grade') == 'XII' ? 'selected' : '' }}>XII</option>
                        </select>
                    </div>

                    <!-- Jurusan -->
                    <div class="md:col-span-2">
                        <select name="department_id"
                            class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Semua Jurusan</option>
                            @foreach ($departments ?? [] as $dept)
                                <option value="{{ $dept->id }}"
                                    {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Kelas -->
                    <div class="md:col-span-2">
                        <select name="class_filter"
                            class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Semua Kelas</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}"
                                    {{ request('class_filter') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name_class }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="md:col-span-2">
                        <select name="sort"
                            class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama ↑</option>
                            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nama ↓
                            </option>
                            <option value="nis_asc" {{ request('sort') == 'nis_asc' ? 'selected' : '' }}>NIS ↑</option>
                            <option value="nis_desc" {{ request('sort') == 'nis_desc' ? 'selected' : '' }}>NIS ↓</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="md:col-span-1 flex gap-2">
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center shadow-sm">
                            <span class="text-sm font-medium">Filter</span>
                        </button>
                        <a href="{{ route('students.index') }}"
                            class="px-4 py-2.5 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-100 transition-colors duration-200 flex items-center justify-center"
                            title="Reset Filter">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No
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
                                Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Password</th>
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
                                Kode Siswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @php $offset = ($students->currentPage() - 1) * $students->perPage(); @endphp
                        @foreach ($students as $student)
                            @php
                                $currentAssignment = $student->classAssignments->first();
                                $currentClass = $currentAssignment ? $currentAssignment->class : null;
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors duration-200"
                                id="student-{{ $student->id }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    {{ $loop->iteration + $offset }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-slate-900">{{ $student->user->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $student->user->email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    <span class="font-mono text-slate-400 cursor-help"
                                        title="{{ $student->user->plain_password ?? 'Password default' }}">
                                        {{ str_repeat('•', 8) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-mono">
                                    <span class="bg-slate-100 px-2 py-1 rounded">{{ $student->nis }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-mono">
                                    @if ($student->student_code)
                                        <span
                                            class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">{{ $student->student_code }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($currentClass)
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">{{ $currentClass->name_class }}</span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800">Belum
                                            ditempatkan</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    {{-- Tombol Move Class DIHAPUS dari tabel, akses lewat Quick View --}}
                                    <div class="flex items-center space-x-2">
                                        <button type="button" onclick="quickView({{ $student->id }})"
                                            class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white text-xs font-medium rounded-lg hover:bg-blue-600 transition-colors duration-200"
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
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
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

            @if ($students->isEmpty())
                <div class="text-center py-12">
                    <div class="mx-auto w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900 mb-2">Tidak ada data murid</h3>
                    <p class="text-slate-600 mb-6">Mulai dengan menambahkan murid baru.</p>
                </div>
            @endif

            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $students->withQueryString()->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- QUICK VIEW MODAL                                                  --}}
    {{-- ================================================================ --}}
    <div id="quickViewModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeQuickViewModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-slate-900">Detail Murid</h3>
                            <button onclick="closeQuickViewModal()"
                                class="text-slate-400 hover:text-slate-600 rounded-full p-1">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div id="quickViewContent" class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-16 h-16 bg-blue-100 rounded-xl flex items-center justify-center">
                                    <span class="text-2xl font-bold text-blue-600" id="qvInitial"></span>
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold text-slate-900" id="qvName"></h4>
                                    <p class="text-sm text-slate-600" id="qvEmail"></p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">NIS</label>
                                    <p class="text-lg font-semibold text-slate-900" id="qvNis"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Kode Siswa</label>
                                    <p class="text-sm font-mono text-purple-600 font-medium" id="qvStudentCode"></p>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Kelas Saat Ini</label>
                                <p class="text-sm font-medium" id="qvCurrentClass"></p>
                                {{-- Tombol rolling nomor kelas - hanya muncul dari quick view --}}
                                <button onclick="openMoveClassModalFromQuickView()"
                                    class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Ganti Nomor Kelas
                                </button>
                            </div>
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Riwayat Kelas</label>
                                <div id="qvClassHistory" class="space-y-3"></div>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button onclick="closeQuickViewModal()"
                                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- IMPORT MODAL                                                       --}}
    {{-- ================================================================ --}}
    <div id="importModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeImportModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg">
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
                        <button onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600 rounded-full p-1">
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
                                <p class="text-sm text-slate-600">Struktur kolom: Nama, Email, NIS, Password (opsional),
                                    Kelas (opsional)</p>
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
                                    class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-100">Batal</button>
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Import
                                    Data</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- ADD MODAL                                                          --}}
    {{-- ================================================================ --}}
    <div id="addModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeAddModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg">
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
                        <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600 rounded-full p-1">
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
                                <input type="text" name="name" required placeholder="Masukkan Nama Lengkap Murid"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    value="{{ old('name') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email <span
                                        class="text-red-500">*</span></label>
                                <input type="email" name="email" required placeholder="Masukkan Email"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    value="{{ old('email') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Password <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="password" required placeholder="Masukkan Password"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    value="{{ old('password') }}">
                                <p class="text-xs text-slate-500 mt-1">Password minimal 6 karakter</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">NIS <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="nis" required
                                    placeholder="Masukkan NIS (6-10 digit angka)"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                                    value="{{ old('nis') }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
                                <select name="class_id"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan
                                Murid</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- EDIT MODAL                                                         --}}
    {{-- ================================================================ --}}
    <div id="editModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeEditModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg">
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
                        <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 rounded-full p-1">
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
                                <input type="text" name="name" required placeholder="Masukkan Nama Lengkap Murid"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    id="editName">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email <span
                                        class="text-red-500">*</span></label>
                                <input type="email" name="email" required placeholder="Masukkan Email"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    id="editEmail">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                                <input type="text" name="password" placeholder="Kosongkan jika tidak diubah"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="text-xs text-slate-500 mt-1">Biarkan kosong jika tidak ingin mengubah password
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">NIS <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="nis" required
                                    placeholder="Masukkan NIS (6-10 digit angka)"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                                    id="editNis">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
                                <select name="class_id" id="editClassId"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-amber-600 rounded-lg hover:bg-amber-700">Perbarui
                                Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="exportModal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeExportModal()"></div>
        <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div
                    class="px-6 py-4 border-b border-slate-100 bg-emerald-50 rounded-t-2xl flex items-center justify-between sticky top-0">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 bg-emerald-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900">Export Data Siswa</h3>
                            <p class="text-xs text-slate-500">Pilih filter, kolom, dan batas data</p>
                        </div>
                    </div>
                    <button onclick="closeExportModal()" class="p-1.5 rounded-lg hover:bg-emerald-100 text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="exportForm" action="{{ route('students.export') }}" method="GET">
                    <div class="p-6 space-y-5">
                        <!-- Info -->
                        <div class="flex items-start space-x-3 p-3.5 bg-blue-50 rounded-xl border border-blue-100">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-xs text-blue-700">Kosongkan semua filter untuk mengekspor semua data siswa. File
                                akan diunduh dalam format CSV.</p>
                        </div>

                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Cari Nama / NIS</label>
                            <input type="text" name="search" placeholder="Cari siswa tertentu..."
                                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Tahun Ajaran -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tahun Ajaran</label>
                            <select name="academic_year_id"
                                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <option value="">Semua Tahun</option>
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Grade Filter -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Angkatan</label>
                                <select id="exportGrade" onchange="filterExportClass()"
                                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Semua Angkatan</option>
                                    <option value="X">X</option>
                                    <option value="XI">XI</option>
                                    <option value="XII">XII</option>
                                </select>
                            </div>

                            <!-- Department Filter -->
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jurusan</label>
                                <select id="exportDepartment" onchange="filterExportClass()"
                                    class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                    <option value="">Semua Jurusan</option>
                                    @foreach ($departments ?? [] as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Kelas Multi Select -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kelas (bisa pilih
                                banyak)</label>
                            <select name="class_ids[]" id="exportClass" multiple class="w-full select2-enhanced">
                                <option value="">Semua Kelas</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}"
                                        data-grade="{{ explode(' ', $class->name_class)[0] ?? '' }}"
                                        data-dept="{{ $class->department_id ?? '' }}">
                                        {{ $class->name_class }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500 mt-1">Tekan Ctrl untuk memilih lebih dari satu</p>
                        </div>

                        <!-- Batas Jumlah Record -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Jumlah Record</label>
                            <select name="limit"
                                class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                                <option value="0">Semua</option>
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>

                        <!-- Pilihan Kolom -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Kolom yang Diekspor</label>
                            <div class="grid grid-cols-2 gap-3 p-4 bg-slate-50 rounded-xl border border-slate-200">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="fields[]" value="name" checked
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-slate-700">Nama</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="fields[]" value="email" checked
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-slate-700">Email</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="fields[]" value="nis" checked
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-slate-700">NIS</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="fields[]" value="class" checked
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-slate-700">Kelas</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="fields[]" value="student_code"
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-slate-700">Kode Siswa</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="fields[]" value="password"
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-slate-700">Password (plain)</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="fields[]" value="created_at"
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-slate-700">Tanggal Daftar</span>
                                </label>
                            </div>
                        </div>

                        <!-- Summary display -->
                        <div id="exportSummary" class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                            <p class="text-xs font-semibold text-slate-600 mb-1">Ringkasan Export:</p>
                            <p class="text-xs text-slate-500" id="exportSummaryText">Semua siswa akan diekspor dengan
                                kolom default</p>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 rounded-b-2xl flex justify-end space-x-3">
                        <button type="button" onclick="closeExportModal()"
                            class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center space-x-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v3a1 1 0 001 1h16a1 1 0 001-1v-3" />
                            </svg>
                            <span>Download CSV</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="printModal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closePrintModal()"></div>
        <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div
                    class="px-6 py-4 border-b border-slate-100 bg-blue-50 rounded-t-2xl flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-slate-900">Cetak Absensi</h3>
                            <p class="text-xs text-slate-500">Pilih kelas dan tahun ajaran</p>
                        </div>
                    </div>
                    <button onclick="closePrintModal()" class="p-1.5 rounded-lg hover:bg-blue-100 text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="{{ route('students.print-attendance') }}" method="GET" target="_blank"
                    class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tahun Ajaran <span
                                class="text-red-500">*</span></label>
                        <select name="academic_year_id" required
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Tahun Ajaran</option>
                            @foreach ($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kelas <span
                                class="text-red-500">*</span></label>
                        <select name="class_id" required
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Kelas</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name_class }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Bulan (Opsional)</label>
                        <input type="month" name="month"
                            class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Ukuran Kertas</label>
                        <select name="paper_size" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg text-sm">
                            <option value="A4">A4 (Portrait)</option>
                            <option value="A4 landscape">A4 (Landscape)</option>
                            <option value="A3">A3 (Portrait)</option>
                            <option value="A3 landscape">A3 (Landscape)</option>
                            <option value="Legal">Legal (Portrait)</option>
                            <option value="Legal landscape">Legal (Landscape)</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-slate-200">
                        <button type="button" onclick="closePrintModal()"
                            class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z" />
                            </svg>
                            <span>Cetak</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- MODAL ROLLING NOMOR KELAS                                         --}}
    {{-- FIX: studentId disimpan di data-student-id pada elemen modal,    --}}
    {{-- bukan di variabel JS global yang bisa null jika quick view tutup  --}}
    {{-- ================================================================ --}}
    <div id="moveClassModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" data-student-id="">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closeMoveClassModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md">
                    <div
                        class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-indigo-50 rounded-t-xl">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Ganti Nomor Kelas</h3>
                                <p class="text-sm text-slate-500" id="moveModalSubtitle">Grade & jurusan tetap sama</p>
                            </div>
                        </div>
                        <button onclick="closeMoveClassModal()"
                            class="text-slate-400 hover:text-slate-600 rounded-full p-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 space-y-4">
                        {{-- Info kelas saat ini --}}
                        <div class="bg-slate-50 rounded-lg p-3 border border-slate-200">
                            <p class="text-xs text-slate-500 mb-1">Kelas saat ini</p>
                            <p class="text-sm font-semibold text-slate-800" id="moveCurrentClassInfo">-</p>
                            <p class="text-xs text-slate-400 mt-0.5">Grade & jurusan tidak berubah</p>
                        </div>

                        {{-- Input nomor kelas tujuan --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Nomor Kelas Tujuan <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="move_class_number" min="1" max="20"
                                placeholder="Masukkan Nomor Kelas"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <p class="text-xs text-slate-500 mt-1">
                                Contoh: siswa di <strong>"XII RPL 1"</strong> → masukkan <strong>2</strong> → pindah ke
                                <strong>"XII RPL 2"</strong>
                            </p>
                        </div>

                        {{-- Tahun ajaran (readonly) --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tahun Ajaran</label>
                            <input type="text" readonly value="{{ $activeYear->name ?? 'Tidak ada tahun aktif' }}"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-lg text-slate-600 text-sm">
                        </div>

                        {{-- Error message --}}
                        <div id="moveClassError"
                            class="hidden bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg text-sm"></div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 rounded-b-xl flex justify-end space-x-3">
                        <button type="button" onclick="closeMoveClassModal()"
                            class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 text-sm">Batal</button>
                        <button type="button" id="moveClassSubmitBtn" onclick="submitRollingClass()"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                            Pindahkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- JAVASCRIPT                                                         --}}
    {{-- ================================================================ --}}
    <script>
        // ── State ──────────────────────────────────────────────────────────────
        // Simpan studentId yang sedang dibuka quick view
        // (dipakai untuk meneruskan ke modal rolling kelas)
        let currentQuickViewStudentId = null;

        // ── Helper: CSRF token ─────────────────────────────────────────────────
        function getCsrf() {
            return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        }

        // ── ADD MODAL ──────────────────────────────────────────────────────────
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function openPrintModal() {
            document.getElementById('printModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePrintModal() {
            document.getElementById('printModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Inisialisasi Select2 untuk dropdown kelas di export modal
        function initExportSelect2() {
            $('#exportClass').select2({
                width: '100%',
                placeholder: 'Pilih satu atau lebih kelas',
                allowClear: true,
                dropdownParent: $('#exportModal')
            });
        }

        // Filter opsi kelas berdasarkan grade dan jurusan
        function filterExportClass() {
            const grade = document.getElementById('exportGrade').value;
            const dept = document.getElementById('exportDepartment').value;
            const classSelect = $('#exportClass');

            // Loop semua option dan sembunyikan yang tidak sesuai
            classSelect.find('option').each(function() {
                const opt = $(this);
                if (opt.val() === '') return; // skip placeholder
                const matchGrade = !grade || opt.data('grade') === grade;
                const matchDept = !dept || opt.data('dept') == dept;
                opt.prop('hidden', !(matchGrade && matchDept));
            });

            // Trigger Select2 untuk refresh
            classSelect.trigger('change.select2');

            updateExportSummary();
        }

        // Update ringkasan export
        function updateExportSummary() {
            const search = document.querySelector('#exportForm input[name=search]').value;
            const academicYear = document.querySelector('select[name=academic_year_id] option:checked')?.text || '';
            const grade = document.getElementById('exportGrade').value;
            const dept = document.getElementById('exportDepartment').value;
            const selectedClasses = $('#exportClass').val() || [];
            const limit = document.querySelector('select[name=limit]').value;
            const fields = Array.from(document.querySelectorAll('input[name="fields[]"]:checked')).map(cb => cb.value);

            const parts = [];
            if (search) parts.push(`Pencarian: "${search}"`);
            if (academicYear && academicYear !== 'Semua Tahun') parts.push(`Tahun: ${academicYear}`);
            if (grade) parts.push(`Angkatan: ${grade}`);
            if (dept) {
                const deptText = document.querySelector(`#exportDepartment option[value="${dept}"]`)?.text.split(' (')[0];
                if (deptText) parts.push(`Jurusan: ${deptText}`);
            }
            if (selectedClasses.length > 0) {
                const classNames = selectedClasses.map(id => {
                    return document.querySelector(`#exportClass option[value="${id}"]`)?.text;
                }).filter(Boolean).join(', ');
                parts.push(`Kelas: ${classNames}`);
            }
            if (limit && limit != '0') parts.push(`Maksimal: ${limit} data`);
            parts.push(`Kolom: ${fields.length} dipilih`);

            const summaryText = document.getElementById('exportSummaryText');
            summaryText.textContent = parts.length ? parts.join(' • ') : 'Semua siswa akan diekspor dengan kolom default';
        }

        // Modifikasi fungsi openExportModal untuk menginisialisasi Select2
        function openExportModal() {
            document.getElementById('exportModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Inisialisasi Select2 jika belum
            if (!$.fn.select2) {
                // Select2 sudah diload, tapi pastikan
                setTimeout(() => {
                    initExportSelect2();
                }, 100);
            } else {
                initExportSelect2();
            }

            updateExportSummary();
        }

        // Tutup modal dan destroy Select2 agar tidak mengganggu modal lain
        function closeExportModal() {
            $('#exportClass').select2('destroy');
            document.getElementById('exportModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Event listener untuk update summary
        document.addEventListener('DOMContentLoaded', function() {
            // Untuk form export
            $('#exportForm').on('change',
                'select[name=academic_year_id], select[name=limit], input[name=search], #exportGrade, #exportDepartment, input[name="fields[]"]',
                function() {
                    updateExportSummary();
                });

            $('#exportClass').on('change', function() {
                updateExportSummary();
            });
        });

        // ── EDIT MODAL ─────────────────────────────────────────────────────────
        function openEditModal(id) {
            fetch(`/admin/students/${id}/edit`)
                .then(r => {
                    if (!r.ok) throw new Error('Network error');
                    return r.json();
                })
                .then(data => {
                    document.getElementById('editId').value = data.id;
                    document.getElementById('editName').value = data.user.name;
                    document.getElementById('editEmail').value = data.user.email;
                    document.getElementById('editNis').value = data.nis;
                    document.getElementById('editClassId').value = data.class_id || '';
                    document.getElementById('editForm').action = `/students/${data.id}`;
                    document.getElementById('editModal').classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                })
                .catch(() => alert('Gagal memuat data murid. Silakan coba lagi.'));
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // ── QUICK VIEW ─────────────────────────────────────────────────────────
        function quickView(id) {
            currentQuickViewStudentId = id;

            fetch(`/admin/students/${id}/detail`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('qvInitial').textContent = data.name.charAt(0);
                    document.getElementById('qvName').textContent = data.name;
                    document.getElementById('qvEmail').textContent = data.email;
                    document.getElementById('qvNis').textContent = data.nis;
                    document.getElementById('qvStudentCode').textContent = data.student_code || '-';

                    document.getElementById('qvCurrentClass').innerHTML = data.current_class ?
                        `<span class="text-blue-600">${data.current_class}</span>` :
                        `<span class="text-amber-600">Belum ditempatkan</span>`;

                    const historyDiv = document.getElementById('qvClassHistory');
                    historyDiv.innerHTML = '';
                    if (data.class_history && data.class_history.length > 0) {
                        data.class_history.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'mb-3';
                            div.innerHTML = `
                                <div class="text-xs text-slate-500">${item.academic_year}</div>
                                <div class="text-sm font-medium text-slate-900">${item.class}</div>`;
                            historyDiv.appendChild(div);
                        });
                    } else {
                        historyDiv.innerHTML = '<p class="text-sm text-slate-500">Belum ada riwayat kelas.</p>';
                    }

                    document.getElementById('quickViewModal').classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                })
                .catch(() => alert('Gagal memuat detail murid.'));
        }

        function closeQuickViewModal() {
            document.getElementById('quickViewModal').classList.add('hidden');
            document.body.style.overflow = '';
            // Jangan reset currentQuickViewStudentId di sini!
            // Akan di-reset setelah modal rolling selesai.
        }

        // ── ROLLING NOMOR KELAS ────────────────────────────────────────────────

        /**
         * Buka modal rolling kelas dari dalam quick view.
         * studentId sudah tersimpan di currentQuickViewStudentId.
         */
        function openMoveClassModalFromQuickView() {
            if (!currentQuickViewStudentId) {
                alert('Data siswa tidak ditemukan. Silakan buka quick view terlebih dahulu.');
                return;
            }
            openMoveClassModal(currentQuickViewStudentId);
        }

        /**
         * Buka modal rolling kelas.
         * FIX: studentId disimpan di data-student-id pada elemen modal,
         * sehingga tidak hilang meski quick view sudah ditutup.
         */
        function openMoveClassModal(studentId) {
            const modal = document.getElementById('moveClassModal');

            // Simpan studentId di DOM element (data attribute), bukan hanya di variabel JS
            modal.setAttribute('data-student-id', studentId);

            // Reset form
            document.getElementById('move_class_number').value = '';
            document.getElementById('moveClassError').classList.add('hidden');
            document.getElementById('moveClassError').textContent = '';

            // Ambil info kelas saat ini dari API untuk ditampilkan
            fetch(`/admin/students/${studentId}/detail`)
                .then(r => r.json())
                .then(data => {
                    const cls = data.current_class || 'Belum ditempatkan';
                    document.getElementById('moveCurrentClassInfo').textContent = cls;
                    document.getElementById('moveModalSubtitle').textContent =
                        `${data.name} — grade & jurusan tetap sama`;
                })
                .catch(() => {
                    document.getElementById('moveCurrentClassInfo').textContent = '-';
                });

            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeMoveClassModal() {
            document.getElementById('moveClassModal').classList.add('hidden');
            document.body.style.overflow = '';
            currentQuickViewStudentId = null; // reset setelah modal ditutup
        }

        /**
         * Submit rolling kelas.
         * FIX: Ambil studentId dari data-student-id milik modal (bukan dari variabel global).
         * FIX: CSRF token dikirim lewat header X-CSRF-TOKEN, bukan lewat FormData
         *      karena Laravel lebih andal membaca dari header untuk JSON/fetch.
         */
        function submitRollingClass() {
            const modal = document.getElementById('moveClassModal');
            const studentId = modal.getAttribute('data-student-id');
            const classNumber = document.getElementById('move_class_number').value.trim();
            const activeYearId = '{{ $activeYear->id ?? '' }}';
            const errDiv = document.getElementById('moveClassError');
            const submitBtn = document.getElementById('moveClassSubmitBtn');

            // Validasi sisi klien
            errDiv.classList.add('hidden');
            if (!studentId) {
                errDiv.textContent = 'ID siswa tidak ditemukan. Tutup modal dan coba lagi.';
                errDiv.classList.remove('hidden');
                return;
            }
            if (!classNumber || parseInt(classNumber) < 1) {
                errDiv.textContent = 'Masukkan nomor kelas yang valid (angka ≥ 1).';
                errDiv.classList.remove('hidden');
                return;
            }
            if (!activeYearId) {
                errDiv.textContent = 'Tidak ada tahun ajaran aktif.';
                errDiv.classList.remove('hidden');
                return;
            }

            // Loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'Memproses...';

            const formData = new FormData();
            formData.append('class_number', classNumber);
            formData.append('academic_year_id', activeYearId);
            formData.append('_token', getCsrf());

            fetch(`/admin/students/${studentId}/update-class`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    },
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        closeMoveClassModal();
                        // Tampilkan pesan sukses lalu reload
                        alert('✅ ' + data.message);
                        window.location.reload();
                    } else {
                        errDiv.textContent = data.message || 'Terjadi kesalahan.';
                        errDiv.classList.remove('hidden');
                    }
                })
                .catch(() => {
                    errDiv.textContent = 'Gagal menghubungi server. Periksa koneksi Anda.';
                    errDiv.classList.remove('hidden');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Pindahkan';
                });
        }

        // ── DELETE ─────────────────────────────────────────────────────────────
        function confirmDelete(button) {
            const studentName = button.closest('tr').querySelector('.text-sm.font-medium').textContent.trim();
            if (confirm(`Apakah Anda yakin ingin menghapus murid "${studentName}"?`)) {
                button.closest('form').submit();
            }
        }

        // ── IMPORT MODAL ───────────────────────────────────────────────────────
        function showImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // ── FILTER & SORT ──────────────────────────────────────────────────────
        function applyFilters() {
            const url = new URL(window.location.href);
            const val = document.getElementById('classFilter').value;
            val ? url.searchParams.set('class_filter', val) : url.searchParams.delete('class_filter');
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
            const current = url.searchParams.get('sort');
            let next = 'newest';
            if (column === 'name') {
                next = current === 'name_asc' ? 'name_desc' : current === 'name_desc' ? 'newest' : 'name_asc';
            } else if (column === 'nis') {
                next = current === 'nis_asc' ? 'nis_desc' : current === 'nis_desc' ? 'newest' : 'nis_asc';
            }
            url.searchParams.set('sort', next);
            window.location.href = url.toString();
        }

        // ── CLOSE ALL ──────────────────────────────────────────────────────────
        function closeAllModals() {
            document.querySelectorAll('[id$="Modal"]').forEach(m => m.classList.add('hidden'));
            document.body.style.overflow = '';
        }

        // ── INIT ───────────────────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('importFile').addEventListener('change', function(e) {
                const f = e.target.files[0];
                if (f) document.getElementById('fileName').textContent = 'File: ' + f.name;
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeAllModals();
                if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
                    e.preventDefault();
                    openAddModal();
                }
                if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                    e.preventDefault();
                    document.getElementById('searchInput').focus();
                }
            });
        });
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
