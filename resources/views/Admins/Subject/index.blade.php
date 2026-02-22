@extends('layouts.app')

@section('content')
    <div class="p-6 space-y-6">
        <!-- Hero Section -->
        <div
            class="relative bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 sm:p-8 mb-6 overflow-hidden border border-blue-100">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 font-poppins">Data Mata Pelajaran</h1>
                    <nav class="flex mt-2 text-sm text-slate-500 font-medium" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-2">
                            <li class="inline-flex items-center">
                                <a href="#" class="hover:text-blue-600 transition-colors">Dashboard</a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <span class="mx-2 text-slate-400">•</span>
                                    <span class="text-slate-900 font-semibold">Mata Pelajaran</span>
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Total Mapel</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalSubjects }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Total Guru</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalTeachers }}</p>
                        <p class="text-xs text-slate-500">pengajar aktif</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Populer</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $popularSubjects }}</p>
                        <p class="text-xs text-slate-500">≥ 3 guru</p>
                    </div>
                    <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Rasio Guru/Mapel</p>
                        <p class="text-2xl font-bold text-slate-900 mt-1">{{ $avgTeachersPerSubject }}</p>
                        <p class="text-xs text-slate-500">per mapel</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Control Panel -->
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-200">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <h2 class="text-xl font-bold text-slate-800">Data Mata Pelajaran</h2>

                    <div class="flex flex-wrap gap-3">
                        <!-- Add Button -->
                        <button type="button" onclick="openAddModal()"
                            class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="font-semibold">Tambah Mapel</span>
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
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
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

            <!-- Filter Section (seperti tahun ajaran) -->
            <div class="p-6 border-b border-slate-200 bg-slate-50">
                <form method="GET" action="{{ route('subject.index') }}"
                    class="grid grid-cols-1 md:grid-cols-12 gap-4 ">
                    <!-- Search -->
                    <div class="md:col-span-3">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari mata pelajaran..."
                                class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <select name="min_teachers"
                            class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Min. Guru</option>
                            <option value="1" {{ request('min_teachers') == '1' ? 'selected' : '' }}>≥ 1</option>
                            <option value="2" {{ request('min_teachers') == '2' ? 'selected' : '' }}>≥ 2</option>
                            <option value="3" {{ request('min_teachers') == '3' ? 'selected' : '' }}>≥ 3</option>
                            <option value="5" {{ request('min_teachers') == '5' ? 'selected' : '' }}>≥ 5</option>
                        </select>
                    </div>

                    <!-- Filter Max Guru -->
                    <div class="md:col-span-2">
                        <select name="max_teachers"
                            class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Max. Guru</option>
                            <option value="1" {{ request('max_teachers') == '1' ? 'selected' : '' }}>≤ 1</option>
                            <option value="2" {{ request('max_teachers') == '2' ? 'selected' : '' }}>≤ 2</option>
                            <option value="3" {{ request('max_teachers') == '3' ? 'selected' : '' }}>≤ 3</option>
                            <option value="5" {{ request('max_teachers') == '5' ? 'selected' : '' }}>≤ 5</option>
                        </select>
                    </div>

                    <!-- Sort -->
                    <div class="md:col-span-2">
                        <select name="sort"
                            class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama
                            </option>
                            <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama A-Z
                            </option>
                            <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nama Z-A
                            </option>
                            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Paling Banyak
                                Guru</option>
                        </select>
                    </div>

                    <!-- Per Page -->
                    <div class="md:col-span-1">
                        <select name="per_page"
                            class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center shadow-sm">
                            <span class="text-sm font-medium">Filter</span>
                        </button>
                        <a href="{{ route('subject.index') }}"
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
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                No
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider cursor-pointer"
                                onclick="sortColumn('name')">
                                <div class="flex items-center">
                                    Nama Mata Pelajaran
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
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider cursor-pointer"
                                onclick="sortColumn('teachers_count')">
                                <div class="flex items-center">
                                    Jumlah Guru
                                    @if (request('sort') == 'popular')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider cursor-pointer"
                                onclick="sortColumn('created_at')">
                                <div class="flex items-center">
                                    Dibuat
                                    @if (request('sort') == 'oldest')
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7" />
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    @endif
                                </div>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @php
                            $offset = ($subjects->currentPage() - 1) * $subjects->perPage();
                        @endphp
                        @foreach ($subjects as $subject)
                            @php
                                $teacherCount = $subject->teachers_count ?? 0;
                                $isPopular = $teacherCount >= 3;
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors duration-200"
                                id="subject-{{ $subject->id }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    {{ $loop->iteration + $offset }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-slate-900">{{ $subject->name_subject }}
                                            </div>
                                            @if ($isPopular)
                                                <div class="text-xs text-blue-600 flex items-center">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                    Populer
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-slate-900">{{ $teacherCount }}</div>
                                        <div class="ml-2 text-xs text-slate-500">guru</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                    {{ $subject->created_at->format('d/m/Y') }}
                                    <div class="text-xs text-slate-400">{{ $subject->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center space-x-2">
                                        <button type="button" onclick="openEditModal({{ $subject->id }})"
                                            class="inline-flex items-center px-3 py-1.5 bg-amber-500 text-white text-xs font-medium rounded-lg hover:bg-amber-600 transition-colors duration-200 tooltip"
                                            title="Edit Data">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                </path>
                                            </svg>
                                        </button>
                                        <button type="button"
                                            onclick="openDeleteModal({{ $subject->id }}, '{{ addslashes($subject->name_subject) }}')"
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
            @if ($subjects->isEmpty())
                <div class="text-center py-12">
                    <div class="mx-auto w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900 mb-2">Tidak ada data mata pelajaran</h3>
                    <p class="text-slate-600 mb-6">Mulai dengan menambahkan mata pelajaran baru.</p>
                </div>
            @endif

            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                <div>
                    {{ $subjects->withQueryString()->links('vendor.pagination.tailwind') }}
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
                            <h3 class="text-lg font-semibold text-slate-900">Detail Mata Pelajaran</h3>
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
                                <h3 class="text-lg font-semibold text-slate-900">Import Mata Pelajaran</h3>
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

                    <form action="{{ route('subject.import') }}" method="POST" enctype="multipart/form-data"
                        id="importForm">
                        @csrf
                        <div class="p-6">
                            <div class="mb-4">
                                <p class="text-sm text-slate-600 mb-2">Format file yang didukung: .csv, .xlsx, .xls</p>
                                <p class="text-sm text-slate-600">Struktur kolom yang diperlukan: Nama Mata Pelajaran</p>
                            </div>

                            <div class="border-2 border-dashed border-slate-300 rounded-xl p-8 text-center mb-4">
                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                                <p class="mt-2 text-sm text-slate-600">Drag & drop file di sini atau</p>
                                <input type="file" id="importFile" name="file" accept=".csv,.xlsx,.xls"
                                    class="hidden" required>
                                <button type="button" onclick="document.getElementById('importFile').click()"
                                    class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                    Pilih File
                                </button>
                                <p id="fileName" class="mt-2 text-sm text-slate-500"></p>
                                <p id="fileError" class="mt-2 text-sm text-red-500 hidden"></p>
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
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Tambah Mata Pelajaran</h3>
                                <p class="text-sm text-slate-500">Isi data mata pelajaran baru</p>
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

                    <form action="{{ route('subject.store') }}" method="POST" id="addForm">
                        @csrf
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Mata Pelajaran <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="name_subject" required
                                    placeholder="Masukkan nama mata pelajaran"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                    value="{{ old('name_subject') }}">
                                <p class="mt-1 text-sm text-red-500 hidden" id="addNameError"></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi (Opsional)</label>
                                <textarea name="description" rows="3" placeholder="Deskripsi mata pelajaran..."
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">{{ old('description') }}</textarea>
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
                                Simpan Mata Pelajaran
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
                                <h3 class="text-lg font-semibold text-slate-900">Edit Mata Pelajaran</h3>
                                <p class="text-sm text-slate-500">Perbarui data mata pelajaran</p>
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
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Mata Pelajaran <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="name_subject" id="editNameSubject" required
                                    placeholder="Masukkan nama mata pelajaran"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                                <p class="mt-1 text-sm text-red-500 hidden" id="editNameError"></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi (Opsional)</label>
                                <textarea name="description" id="editDescription" rows="3" placeholder="Deskripsi mata pelajaran..."
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"></textarea>
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
                                Perbarui Mata Pelajaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeDeleteModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all">
                    <div
                        class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-red-50 rounded-t-xl">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">Hapus Mata Pelajaran</h3>
                                <p class="text-sm text-slate-500">Konfirmasi penghapusan data</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeDeleteModal()"
                            class="text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full p-1 transition-colors duration-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="p-6">
                        <div class="text-center mb-6">
                            <div class="mx-auto w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.898-.833-2.668 0L4.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-semibold text-slate-900 mb-2" id="deleteSubjectName"></h4>
                            <p class="text-slate-600">Anda yakin ingin menghapus mata pelajaran ini? Data yang dihapus
                                tidak dapat dikembalikan.</p>
                        </div>

                        <form id="deleteForm" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeDeleteModal()"
                                    class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-100 transition-colors duration-200">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200">
                                    Ya, Hapus
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            // Reset form
            document.getElementById('addForm').reset();
            document.getElementById('addNameError').classList.add('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        async function openEditModal(id) {
            try {
                const response = await fetch(`/admin/subject/${id}/edit`);
                if (!response.ok) throw new Error('Failed to fetch subject data');

                const subject = await response.json();

                // Populate edit modal with data
                document.getElementById('editNameSubject').value = subject.name_subject || '';
                document.getElementById('editDescription').value = subject.description || '';
                document.getElementById('editForm').action = `/admin/subject/${id}`;
                document.getElementById('editNameError').classList.add('hidden');

                document.getElementById('editModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } catch (error) {
                console.error('Error loading edit modal:', error);
                alert('Gagal memuat data mata pelajaran');
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function openDeleteModal(id, name) {
            document.getElementById('deleteSubjectName').textContent = `Hapus "${name}"?`;
            document.getElementById('deleteForm').action = `/admin/subject/${id}`;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function showImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            // Reset file input
            document.getElementById('importFile').value = '';
            document.getElementById('fileName').textContent = '';
            document.getElementById('fileError').classList.add('hidden');
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        async function quickView(id) {
            try {
                const response = await fetch(`/admin/subject/${id}/detail`);
                if (!response.ok) throw new Error('Failed to fetch subject details');

                const data = await response.json();
                const content = document.getElementById('quickViewContent');

                content.innerHTML = `
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-16 h-16 ${data.teachersCount >= 3 ? 'bg-blue-100' : 'bg-slate-100'} rounded-xl flex items-center justify-center">
                                <span class="text-2xl font-bold ${data.teachersCount >= 3 ? 'text-blue-600' : 'text-slate-600'}">${data.name.charAt(0)}</span>
                            </div>
                            <div>
                                <h4 class="text-lg font-semibold text-slate-900">${data.name}</h4>
                                ${data.teachersCount >= 3 ? '<div class="text-xs text-blue-600 flex items-center"><svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> Mata Pelajaran Populer</div>' : ''}
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Jumlah Guru</label>
                                <p class="text-lg font-semibold text-slate-900">${data.teachersCount} <span class="text-sm font-normal text-slate-600">guru</span></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                                <p class="text-sm text-green-600 font-medium">Aktif</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Dibuat</label>
                            <p class="text-sm text-slate-900">${data.createdAt}</p>
                        </div>

                        ${data.description ? `
                                                            <div>
                                                                <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
                                                                <p class="text-sm text-slate-900">${data.description}</p>
                                                            </div>
                                                            ` : ''}

                        <div class="pt-4 border-t border-slate-200">
                            <div class="flex space-x-2">
                                <button type="button" onclick="openEditModal(${data.id})"
                                    class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors duration-200">
                                    Edit Data
                                </button>
                                <button type="button" onclick="openDeleteModal(${data.id}, '${data.name.replace(/'/g, "\\'")}')"
                                    class="flex-1 inline-flex justify-center items-center px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors duration-200">
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('quickViewModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } catch (error) {
                console.error('Error loading quick view:', error);
                alert('Gagal memuat detail mata pelajaran');
            }
        }

        function closeQuickViewModal() {
            document.getElementById('quickViewModal').classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Filter functions
        function changePerPage(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', value);
            url.searchParams.set('page', '1'); // Reset to first page
            window.location.href = url.toString();
        }

        function applySort(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', value);
            url.searchParams.set('page', '1'); // Reset to first page
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
            } else if (column === 'teachers_count') {
                newSort = currentSort === 'popular' ? 'newest' : 'popular';
            } else if (column === 'created_at') {
                newSort = currentSort === 'oldest' ? 'newest' : 'oldest';
            }

            url.searchParams.set('sort', newSort);
            url.searchParams.set('page', '1'); // Reset to first page
            window.location.href = url.toString();
        }

        // Form validation and submission
        document.addEventListener('DOMContentLoaded', function() {
            // File name preview for import
            document.getElementById('importFile').addEventListener('change', function(e) {
                const file = e.target.files[0];
                const fileError = document.getElementById('fileError');

                if (file) {
                    // Validate file size (max 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        fileError.textContent = 'Ukuran file maksimal 5MB';
                        fileError.classList.remove('hidden');
                        this.value = '';
                        document.getElementById('fileName').textContent = '';
                    } else {
                        // Validate file extension
                        const validExtensions = ['.csv', '.xlsx', '.xls'];
                        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

                        if (!validExtensions.includes(fileExtension)) {
                            fileError.textContent = 'Format file harus .csv, .xlsx, atau .xls';
                            fileError.classList.remove('hidden');
                            this.value = '';
                            document.getElementById('fileName').textContent = '';
                        } else {
                            document.getElementById('fileName').textContent = `File: ${file.name}`;
                            fileError.classList.add('hidden');
                        }
                    }
                }
            });

            // Handle form submissions with validation
            const addForm = document.getElementById('addForm');
            if (addForm) {
                addForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const nameInput = this.querySelector('input[name="name_subject"]');
                    const errorElement = document.getElementById('addNameError');

                    // Basic validation
                    if (!nameInput.value.trim()) {
                        errorElement.textContent = 'Nama mata pelajaran wajib diisi';
                        errorElement.classList.remove('hidden');
                        return;
                    }

                    // Submit form
                    try {
                        const response = await fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const result = await response.json();

                        if (response.ok) {
                            // Success - reload page
                            window.location.reload();
                        } else {
                            // Show error
                            if (result.errors && result.errors.name_subject) {
                                errorElement.textContent = result.errors.name_subject[0];
                                errorElement.classList.remove('hidden');
                            }
                        }
                    } catch (error) {
                        console.error('Error submitting form:', error);
                        alert('Terjadi kesalahan saat menyimpan data');
                    }
                });
            }

            // Handle edit form submission
            const editForm = document.getElementById('editForm');
            if (editForm) {
                editForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const nameInput = this.querySelector('input[name="name_subject"]');
                    const errorElement = document.getElementById('editNameError');

                    // Basic validation
                    if (!nameInput.value.trim()) {
                        errorElement.textContent = 'Nama mata pelajaran wajib diisi';
                        errorElement.classList.remove('hidden');
                        return;
                    }

                    // Submit form
                    try {
                        const response = await fetch(this.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        const result = await response.json();

                        if (response.ok) {
                            // Success - reload page
                            window.location.reload();
                        } else {
                            // Show error
                            if (result.errors && result.errors.name_subject) {
                                errorElement.textContent = result.errors.name_subject[0];
                                errorElement.classList.remove('hidden');
                            }
                        }
                    } catch (error) {
                        console.error('Error submitting form:', error);
                        alert('Terjadi kesalahan saat memperbarui data');
                    }
                });
            }

            // Handle delete form submission
            const deleteForm = document.getElementById('deleteForm');
            if (deleteForm) {
                deleteForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    try {
                        const response = await fetch(this.action, {
                            method: 'POST',
                            body: new FormData(this),
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (response.ok) {
                            // Success - reload page
                            window.location.reload();
                        } else {
                            alert('Gagal menghapus mata pelajaran');
                        }
                    } catch (error) {
                        console.error('Error deleting subject:', error);
                        alert('Terjadi kesalahan saat menghapus data');
                    }
                });
            }

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Escape to close modals
                if (e.key === 'Escape') {
                    closeAllModals();
                }

                // Ctrl/Cmd + N for new subject
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

            // Initialize tooltips
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
