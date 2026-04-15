@extends('layouts.appTeacher')
@section('title', 'Quiz Interaktif - SmartLab')
@section('breadcrumb', 'Quiz Interaktif')

@section('content')
<div class="space-y-6">

    {{-- ===== PAGE HEADER ===== --}}
    <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800 rounded-2xl p-6 md:p-8 text-white shadow-lg relative overflow-hidden">
        {{-- Decorative circles --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/4"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/4"></div>

        <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-blue-200 text-sm font-medium">Manajemen Quiz</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-white font-jakarta">Quiz Interaktif</h1>
                <p class="text-blue-200 mt-1 text-sm md:text-base">Buat, kelola, dan pantau quiz untuk siswa Anda</p>
            </div>
            <a href="{{ route('guru.quiz.create') }}"
                class="flex-shrink-0 flex items-center gap-2 px-5 py-3 bg-white text-blue-700 font-semibold text-sm rounded-xl hover:bg-blue-50 transition-all shadow-md hover:shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Quiz Baru
            </a>
        </div>

        {{-- Search + Filters --}}
        <div class="relative mt-6 flex flex-col sm:flex-row gap-3">
            {{-- Search --}}
            <form action="{{ route('guru.quiz.index') }}" method="GET" id="searchForm" class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-300 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" placeholder="Cari judul quiz, mapel, atau kelas..."
                        value="{{ request('search') }}"
                        class="w-full pl-10 pr-4 py-2.5 bg-white/15 border border-white/25 text-white placeholder-blue-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-white/40 focus:bg-white/20 transition-all"
                        onchange="document.getElementById('searchForm').submit()">
                    @if(request('search'))
                        <button type="button" onclick="window.location='{{ route('guru.quiz.index') }}'"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-blue-300 hover:text-white transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </form>

            {{-- Filter Form --}}
            <form action="{{ route('guru.quiz.index') }}" method="GET" id="filterForm" class="flex gap-2 flex-wrap sm:flex-nowrap">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif

                <select name="status" onchange="document.getElementById('filterForm').submit()"
                    class="px-3 py-2.5 bg-white/15 border border-white/25 text-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-white/40 transition-all">
                    <option value="" class="text-slate-800">Semua Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }} class="text-slate-800">Draft</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }} class="text-slate-800">Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }} class="text-slate-800">Nonaktif</option>
                    <option value="finished" {{ request('status') == 'finished' ? 'selected' : '' }} class="text-slate-800">Selesai</option>
                </select>

                @if($classes->count() > 0)
                <select name="class_id" onchange="document.getElementById('filterForm').submit()"
                    class="px-3 py-2.5 bg-white/15 border border-white/25 text-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-white/40 transition-all">
                    <option value="" class="text-slate-800">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }} class="text-slate-800">
                            {{ $class->name_class }}
                        </option>
                    @endforeach
                </select>
                @endif

                @if($subjects->count() > 0)
                <select name="subject_id" onchange="document.getElementById('filterForm').submit()"
                    class="px-3 py-2.5 bg-white/15 border border-white/25 text-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-white/40 transition-all">
                    <option value="" class="text-slate-800">Semua Mapel</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }} class="text-slate-800">
                            {{ $subject->name_subject }}
                        </option>
                    @endforeach
                </select>
                @endif

                <select name="quiz_mode" onchange="document.getElementById('filterForm').submit()"
                    class="px-3 py-2.5 bg-white/15 border border-white/25 text-white rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-white/40 transition-all">
                    <option value="" class="text-slate-800">Semua Mode</option>
                    <option value="live" {{ request('quiz_mode') == 'live' ? 'selected' : '' }} class="text-slate-800">Live Quiz</option>
                    <option value="homework" {{ request('quiz_mode') == 'homework' ? 'selected' : '' }} class="text-slate-800">Quiz Mandiri</option>
                    <option value="guided" {{ request('quiz_mode') == 'guided' ? 'selected' : '' }} class="text-slate-800">Quiz Terpandu</option>
                </select>

                @if(request('status') || request('class_id') || request('subject_id') || request('search') || request('quiz_mode'))
                    <button type="button" onclick="resetFilters()"
                        class="px-3 py-2.5 bg-white/20 hover:bg-white/30 text-white border border-white/30 rounded-xl text-sm transition-colors flex items-center gap-1.5 whitespace-nowrap">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Reset
                    </button>
                @endif
            </form>
        </div>
    </div>

    {{-- ===== STAT CARDS ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Quiz --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 transition-colors">
                <svg class="w-6 h-6 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Total Quiz</p>
                <p class="text-2xl font-bold text-slate-800 mt-0.5">{{ $quizzes->total() ?? 0 }}</p>
            </div>
        </div>

        {{-- Aktif --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-500 transition-colors">
                <svg class="w-6 h-6 text-emerald-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Aktif</p>
                <p class="text-2xl font-bold text-slate-800 mt-0.5">{{ $quizzes->where('status', 'active')->count() }}</p>
            </div>
        </div>

        {{-- Draft --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-amber-500 transition-colors">
                <svg class="w-6 h-6 text-amber-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Draft</p>
                <p class="text-2xl font-bold text-slate-800 mt-0.5">{{ $quizzes->where('status', 'draft')->count() }}</p>
            </div>
        </div>

        {{-- Live Berlangsung --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 hover:shadow-md transition-shadow group">
            <div class="w-12 h-12 bg-violet-50 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-violet-600 transition-colors">
                <svg class="w-6 h-6 text-violet-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Live Aktif</p>
                <p class="text-2xl font-bold text-slate-800 mt-0.5">{{ $quizzes->where('is_quiz_started', true)->count() }}</p>
            </div>
        </div>
    </div>

    {{-- ===== ALERTS ===== --}}
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center gap-3 text-sm" id="successAlert">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
            <div class="flex items-center gap-2 mb-1 font-semibold text-sm">
                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                Terjadi Kesalahan
            </div>
            <ul class="ml-6 list-disc text-xs space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ===== TABLE CARD ===== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

        {{-- Table Header --}}
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gradient-to-r from-slate-50 to-blue-50/30">
            <div>
                <h3 class="text-base font-semibold text-slate-800">Daftar Quiz Interaktif</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Menampilkan <span class="font-semibold text-blue-600">{{ $quizzes->count() }}</span>
                    dari <span class="font-semibold text-slate-700">{{ $quizzes->total() }}</span> quiz
                    @if(request()->hasAny(['status','class_id','subject_id','search','quiz_mode']))
                        <span class="ml-1 text-blue-600">(difilter)</span>
                    @endif
                </p>
            </div>

            {{-- Active filter badges --}}
            <div class="flex flex-wrap gap-1.5">
                @if(request('status'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="4"/></svg>
                        Status: {{ ucfirst(request('status')) }}
                    </span>
                @endif
                @if(request('quiz_mode'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-violet-100 text-violet-700 rounded-full text-xs font-medium">
                        Mode: {{ request('quiz_mode') == 'live' ? 'Live' : (request('quiz_mode') == 'homework' ? 'Mandiri' : 'Terpandu') }}
                    </span>
                @endif
                @if(request('class_id') && $classes->where('id', request('class_id'))->first())
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-medium">
                        Kelas: {{ $classes->where('id', request('class_id'))->first()->name_class }}
                    </span>
                @endif
                @if(request('subject_id') && $subjects->where('id', request('subject_id'))->first())
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-medium">
                        Mapel: {{ $subjects->where('id', request('subject_id'))->first()->name_subject }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="pl-6 pr-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider w-12">#</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Judul & Detail</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Mapel / Kelas</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Mode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider pr-6">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($quizzes as $index => $quiz)
                        <tr class="hover:bg-blue-50/30 transition-colors group">
                            {{-- No --}}
                            <td class="pl-6 pr-3 py-4 text-sm text-slate-400 font-medium">
                                {{ $index + 1 + (($quizzes->currentPage() - 1) * $quizzes->perPage()) }}
                            </td>

                            {{-- Title --}}
                            <td class="px-4 py-4">
                                <div class="font-semibold text-slate-800 text-sm leading-snug">{{ $quiz->title }}</div>
                                <div class="flex items-center gap-3 mt-1 text-xs text-slate-400">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $quiz->time_per_question > 0 ? $quiz->time_per_question.' dtk/soal' : 'Tanpa limit' }}
                                    </span>
                                    <span class="text-slate-200">|</span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        {{ $quiz->questions_count ?? 0 }} soal
                                    </span>
                                    @if($quiz->is_quiz_started)
                                        <span class="flex items-center gap-1 text-red-500">
                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>
                                            Berlangsung
                                        </span>
                                    @elseif($quiz->is_room_open)
                                        <span class="flex items-center gap-1 text-emerald-600">
                                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                            Room terbuka
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Mapel/Kelas --}}
                            <td class="px-4 py-4">
                                <div class="text-sm font-medium text-slate-700">{{ $quiz->subject?->name_subject ?? '-' }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $quiz->class?->name_class ?? '-' }}</div>
                            </td>

                            {{-- Mode --}}
                            <td class="px-4 py-4">
                                @php
                                    $modeConfig = [
                                        'live' => ['bg-blue-100 text-blue-700', 'Live Quiz'],
                                        'homework' => ['bg-slate-100 text-slate-600', 'Mandiri'],
                                        'guided' => ['bg-violet-100 text-violet-700', 'Terpandu'],
                                    ];
                                    $mode = $modeConfig[$quiz->quiz_mode] ?? $modeConfig['homework'];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium {{ $mode[0] }}">
                                    {{ $mode[1] }}
                                </span>
                            </td>

                            {{-- Waktu --}}
                            <td class="px-4 py-4">
                                <div class="text-xs text-slate-600">
                                    @if($quiz->quiz_mode === 'homework' && $quiz->start_at)
                                        <div class="flex items-center gap-1 text-slate-500">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $quiz->start_at->format('d M Y') }}
                                        </div>
                                        <div class="text-slate-400 mt-0.5">s/d {{ $quiz->end_at?->format('d M Y') ?? '-' }}</div>
                                    @else
                                        <span class="text-slate-400">{{ $quiz->duration }} menit</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-4">
                                @php
                                    $statusMap = [
                                        'active'   => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'draft'    => 'bg-amber-100 text-amber-800 border-amber-200',
                                        'inactive' => 'bg-slate-100 text-slate-600 border-slate-200',
                                        'finished' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    ];
                                    $dotMap = [
                                        'active' => 'bg-emerald-500',
                                        'draft' => 'bg-amber-500',
                                        'inactive' => 'bg-slate-400',
                                        'finished' => 'bg-blue-500',
                                    ];
                                    $statusClass = $statusMap[$quiz->status] ?? $statusMap['inactive'];
                                    $dotClass = $dotMap[$quiz->status] ?? $dotMap['inactive'];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusClass }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }} {{ $quiz->status === 'active' ? 'animate-pulse' : '' }}"></span>
                                    {{ ucfirst($quiz->status) }}
                                </span>
                            </td>

                            <td class="px-4 py-4 pr-6">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Preview — selalu tersedia --}}
                                    <a href="{{ route('guru.quiz.preview', $quiz->id) }}"
                                        class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Preview">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    @if ($quiz->status !== 'finished')
                                        {{-- Kelola Soal — hanya jika belum selesai --}}
                                        <a href="{{ route('guru.quiz.questions', $quiz->id) }}"
                                            class="p-2 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Kelola Soal">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </a>
                                        {{-- Edit — hanya jika belum selesai --}}
                                        <a href="{{ route('guru.quiz.edit', $quiz->id) }}"
                                            class="p-2 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    @endif

                                    {{-- Hasil — selalu tersedia (lebih berguna saat finished) --}}
                                    <a href="{{ route('guru.quiz.results', $quiz->id) }}"
                                        class="p-2 rounded-lg transition-colors
                                            {{ $quiz->status === 'finished'
                                                ? 'text-violet-600 bg-violet-50 hover:bg-violet-100'
                                                : 'text-slate-400 hover:text-violet-600 hover:bg-violet-50' }}"
                                        title="Lihat Hasil">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                    </a>

                                    {{-- Hapus — selalu tersedia --}}
                                    <button onclick="openDeleteModal('{{ $quiz->id }}', '{{ addslashes($quiz->title) }}')"
                                        class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-base font-semibold text-slate-700">
                                            @if(request()->hasAny(['search','status','class_id','subject_id','quiz_mode']))
                                                Tidak ada quiz yang cocok
                                            @else
                                                Belum ada quiz
                                            @endif
                                        </p>
                                        <p class="text-sm text-slate-400 mt-1">
                                            @if(request()->hasAny(['search','status','class_id','subject_id','quiz_mode']))
                                                Coba ubah filter atau kata kunci pencarian
                                            @else
                                                Mulai dengan membuat quiz interaktif pertama Anda
                                            @endif
                                        </p>
                                    </div>
                                    @if(!request()->hasAny(['search','status','class_id','subject_id','quiz_mode']))
                                        <a href="{{ route('guru.quiz.create') }}"
                                            class="mt-2 flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Buat Quiz Pertama
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($quizzes->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $quizzes->links('vendor.pagination.tailwind') }}
            </div>
        @endif
    </div>

</div>

{{-- ===== DELETE MODAL ===== --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden">
        <div class="p-6">
            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-900 text-center">Hapus Quiz</h3>
            <p class="text-sm text-slate-500 text-center mt-2">
                Yakin ingin menghapus quiz <strong class="text-slate-800" id="deleteExamTitle"></strong>?
                Tindakan ini tidak dapat dibatalkan.
            </p>
        </div>
        <div class="flex gap-3 px-6 pb-6">
            <button type="button" onclick="closeDeleteModal()"
                class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-colors">
                Batal
            </button>
            <form id="deleteForm" method="POST" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="w-full px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl text-sm transition-colors">
                    Hapus
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(quizId, quizTitle) {
        document.getElementById('deleteExamTitle').textContent = quizTitle;
        document.getElementById('deleteForm').action = `/guru/quiz/${quizId}`;
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function resetFilters() {
        const search = new URL(window.location.href).searchParams.get('search');
        window.location.href = '{{ route("guru.quiz.index") }}' + (search ? '?search=' + search : '');
    }

    document.getElementById('deleteModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });

    // Auto-dismiss success alert
    const successAlert = document.getElementById('successAlert');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.transition = 'opacity 0.3s';
            successAlert.style.opacity = '0';
            setTimeout(() => successAlert.remove(), 300);
        }, 4000);
    }
</script>
@endsection
