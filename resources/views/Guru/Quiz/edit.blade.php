@extends('layouts.appTeacher')
@section('title', 'Edit Quiz - SmartLab')
@section('breadcrumb', 'Quiz Interaktif / Edit')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- ===== STEP INDICATOR ===== --}}
    <div class="flex items-center justify-center gap-3">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold shadow-md shadow-blue-200">1</div>
            <span class="text-sm font-semibold text-blue-700">Edit Pengaturan Quiz</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-8 h-px bg-slate-300"></div>
            <div class="w-1.5 h-1.5 bg-slate-300 rounded-full"></div>
            <div class="w-8 h-px bg-slate-300"></div>
        </div>
        <a href="{{ route('guru.quiz.questions', $quiz->id) }}" class="flex items-center gap-2 opacity-60 hover:opacity-100 transition-opacity">
            <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-sm font-bold">2</div>
            <span class="text-sm font-medium text-slate-500">Kelola Soal</span>
        </a>
    </div>

    {{-- ===== FLASH ERRORS ===== --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl">
            <div class="flex items-center gap-2 font-semibold text-sm mb-2">
                <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.282 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                Terjadi Kesalahan!
            </div>
            <ul class="ml-6 list-disc text-xs space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- ===== MAIN FORM CARD ===== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

        {{-- Card Header --}}
        <div class="p-6 bg-gradient-to-r from-blue-600 to-blue-700">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white font-jakarta">Edit Quiz</h2>
                        <p class="text-blue-200 text-sm truncate max-w-xs">{{ $quiz->title }}</p>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    @if($quiz->is_quiz_started)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-yellow-400/20 text-yellow-200 border border-yellow-300/30">
                            <span class="w-2 h-2 bg-yellow-300 rounded-full animate-pulse"></span>
                            Sedang Berlangsung
                        </span>
                    @elseif($quiz->is_room_open)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-400/20 text-emerald-200 border border-emerald-300/30">
                            <span class="w-2 h-2 bg-emerald-300 rounded-full"></span>
                            Room Terbuka
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-white/10 text-blue-100 border border-white/20">
                            <span class="w-2 h-2 bg-blue-200 rounded-full"></span>
                            {{ ucfirst($quiz->status) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <form action="{{ route('guru.quiz.update', $quiz->id) }}" method="POST" id="quizForm" class="divide-y divide-slate-100">
            @csrf
            @method('PUT')
            <input type="hidden" name="type" value="QUIZ">

            {{-- ===== SECTION 1: INFO DASAR ===== --}}
            <div class="p-6 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                    <div class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center">
                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    Informasi Dasar Quiz
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Judul Quiz <span class="text-red-500">*</span></label>
                        <input type="text" name="title" placeholder="Contoh: Quiz Matematika Bab 3"
                            value="{{ old('title', $quiz->title) }}"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                        <select name="subject_id" id="subject_id" required
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach ($mapels as $mapel)
                                <option value="{{ $mapel->id }}" {{ old('subject_id', $quiz->subject_id) == $mapel->id ? 'selected' : '' }}>
                                    {{ $mapel->name_subject }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Kelas Target - Chip Multi-select --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Kelas Target <span class="text-red-500">*</span>
                        <span class="font-normal text-slate-400 text-xs ml-1">(boleh pilih lebih dari satu)</span>
                    </label>

                    {{-- Loading --}}
                    <div id="classLoading" class="hidden flex items-center gap-2 text-sm text-blue-600 py-1.5">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Memuat kelas...
                    </div>

                    {{-- Chip trigger button --}}
                    <div id="classSelectWrapper" class="relative">
                        <button type="button" id="classDropdownBtn"
                            class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white text-left flex justify-between items-center hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                            <div id="chipContainer" class="flex flex-wrap gap-1.5 flex-1 items-center min-h-[20px]">
                                <span id="chipPlaceholder" class="text-slate-400 text-xs">Pilih mata pelajaran terlebih dahulu</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-400 flex-shrink-0 transition-transform" id="dropdownChevron"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Dropdown panel --}}
                        <div id="classDropdown" class="hidden absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg max-h-72 flex flex-col">
                            <div class="p-2 border-b border-slate-100">
                                <input type="text" id="classSearch" placeholder="Cari kelas..."
                                    class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div id="classListContainer" class="overflow-y-auto flex-1 p-1.5"></div>
                        </div>
                    </div>

                    {{-- Hidden inputs for class_ids[] - populated by JS --}}
                    <div id="classHiddenInputs"></div>
                    <p class="text-xs text-slate-400 mt-1.5">Pilih satu atau lebih kelas yang akan mengerjakan quiz ini</p>
                </div>
            </div>

            {{-- ===== SECTION 2: MODE QUIZ ===== --}}
            <div class="p-6 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                    <div class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center">
                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
                        </svg>
                    </div>
                    Mode Quiz
                </h3>

                @if($quiz->is_quiz_started)
                    <div class="p-3.5 bg-yellow-50 border border-yellow-200 rounded-xl flex items-center gap-2 text-sm text-yellow-800">
                        <svg class="w-4 h-4 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.282 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        Quiz sedang berlangsung. Beberapa pengaturan tidak dapat diubah.
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    {{-- Mode Homework --}}
                    <label class="relative cursor-pointer">
                        <input type="radio" name="quiz_mode" value="homework" id="mode_homework" class="sr-only peer"
                            {{ old('quiz_mode', $quiz->quiz_mode) == 'homework' ? 'checked' : '' }}
                            {{ $quiz->is_quiz_started ? 'disabled' : '' }}>
                        <div class="border-2 rounded-xl p-4 h-full transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 border-slate-200 hover:border-blue-300 hover:bg-slate-50 {{ $quiz->is_quiz_started ? 'opacity-60 cursor-not-allowed' : '' }}">
                            <div class="flex items-center gap-2.5 mb-3">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm text-slate-800">Quiz Mandiri</p>
                                    <p class="text-xs text-slate-500">Tanpa pemantauan guru</p>
                                </div>
                            </div>
                            <ul class="text-xs text-slate-500 space-y-1">
                                <li class="flex items-center gap-1.5"><svg class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Dikerjakan kapan saja</li>
                                <li class="flex items-center gap-1.5"><svg class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Batas waktu mulai & akhir</li>
                                <li class="flex items-center gap-1.5"><svg class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Auto-submit jika over batas pelanggaran</li>
                            </ul>
                        </div>
                    </label>

                    {{-- Grup Dipantau Guru: Live + Terpandu --}}
                    <div class="border-2 rounded-xl overflow-hidden transition-all border-slate-200
                        [&:has(input[value=live]:checked)]:border-blue-500
                        [&:has(input[value=guided]:checked)]:border-blue-500
                        {{ $quiz->is_quiz_started ? 'opacity-60' : '' }}">
                        <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 border-b border-slate-200">
                            <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span class="text-xs font-semibold text-blue-600 uppercase tracking-wide">Dipantau Guru (Real-time)</span>
                        </div>
                        <div class="grid grid-cols-2 divide-x divide-slate-200">
                            {{-- Mode Live --}}
                            <label class="relative cursor-pointer">
                                <input type="radio" name="quiz_mode" value="live" id="mode_live" class="sr-only peer"
                                    {{ old('quiz_mode', $quiz->quiz_mode) == 'live' ? 'checked' : '' }}
                                    {{ $quiz->is_quiz_started ? 'disabled' : '' }}>
                                <div class="p-4 h-full transition-all peer-checked:bg-blue-50 hover:bg-slate-50 {{ $quiz->is_quiz_started ? 'cursor-not-allowed' : '' }}">
                                    <div class="flex items-center gap-2.5 mb-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-sm text-slate-800">Live Quiz</p>
                                            <p class="text-xs text-slate-500">Dipantau langsung</p>
                                        </div>
                                    </div>
                                    <ul class="text-xs text-slate-500 space-y-1">
                                        <li class="flex items-center gap-1.5"><svg class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Kode room siswa</li>
                                        <li class="flex items-center gap-1.5"><svg class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Monitor real-time</li>
                                        <li class="flex items-center gap-1.5"><svg class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Kartu merah pelanggaran</li>
                                    </ul>
                                </div>
                            </label>
                            {{-- Mode Guided --}}
                            <label class="relative cursor-pointer">
                                <input type="radio" name="quiz_mode" value="guided" id="mode_guided" class="sr-only peer"
                                    {{ old('quiz_mode', $quiz->quiz_mode) == 'guided' ? 'checked' : '' }}
                                    {{ $quiz->is_quiz_started ? 'disabled' : '' }}>
                                <div class="p-4 h-full transition-all peer-checked:bg-blue-50 hover:bg-slate-50 {{ $quiz->is_quiz_started ? 'cursor-not-allowed' : '' }}">
                                    <div class="flex items-center gap-2.5 mb-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-sm text-slate-800">Quiz Terpandu</p>
                                            <p class="text-xs text-slate-500">Soal di layar guru</p>
                                        </div>
                                    </div>
                                    <ul class="text-xs text-slate-500 space-y-1">
                                        <li class="flex items-center gap-1.5"><svg class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Soal tampil di proyektor</li>
                                        <li class="flex items-center gap-1.5"><svg class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Guru kontrol pergantian soal</li>
                                        <li class="flex items-center gap-1.5"><svg class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Siswa jawab di perangkat</li>
                                    </ul>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== SECTION 3: WAKTU ===== --}}
            <div class="p-6 space-y-5">
                <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                    <div class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center">
                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    Pengaturan Waktu
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Waktu Per Soal (detik)</label>
                        <input type="number" name="time_per_question" id="time_per_question" min="0" max="600"
                            value="{{ old('time_per_question', $quiz->time_per_question ?? 0) }}"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        <p class="text-xs text-slate-400 mt-1">0 = tanpa batas waktu per soal</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Durasi Total (menit) <span class="text-red-500">*</span></label>
                        <input type="number" name="duration" min="1" max="480"
                            value="{{ old('duration', $quiz->duration) }}"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                            required>
                    </div>
                    <div id="start_at_wrapper" style="{{ old('quiz_mode', $quiz->quiz_mode) == 'homework' ? '' : 'display:none' }}">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Waktu Mulai <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="start_at" id="start_at"
                            value="{{ old('start_at', $quiz->start_at ? $quiz->start_at->format('Y-m-d\TH:i') : '') }}"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>
                    <div id="end_at_wrapper" style="{{ old('quiz_mode', $quiz->quiz_mode) == 'homework' ? '' : 'display:none' }}">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Waktu Berakhir <span class="text-red-500">*</span></label>
                        <input type="datetime-local" name="end_at" id="end_at"
                            value="{{ old('end_at', $quiz->end_at ? $quiz->end_at->format('Y-m-d\TH:i') : '') }}"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>
                </div>
            </div>

            {{-- ===== SECTION 4: PENGATURAN SOAL ===== --}}
            <div class="p-6 space-y-4">
                <h3 class="flex items-center justify-between gap-2 text-sm font-semibold text-slate-800">
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center">
                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        Pengaturan Soal & Hasil
                    </div>
                    <label class="flex items-center gap-1.5 cursor-pointer text-xs text-slate-500 font-normal">
                        <input type="checkbox" id="selectAllSoal" class="w-3.5 h-3.5 text-blue-600 rounded border-slate-300">
                        Pilih Semua
                    </label>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @php
                    $checkboxes = [
                        ['shuffle_question', old('shuffle_question', $quiz->shuffle_question), 'Acak Urutan Soal', 'Setiap siswa mendapat urutan soal berbeda'],
                        ['shuffle_answer', old('shuffle_answer', $quiz->shuffle_answer), 'Acak Pilihan Jawaban', 'Urutan A/B/C/D diacak per siswa'],
                        ['show_score', old('show_score', $quiz->show_score), 'Tampilkan Skor', 'Siswa dapat melihat nilainya setelah selesai'],
                        ['show_correct_answer', old('show_correct_answer', $quiz->show_correct_answer), 'Tampilkan Jawaban Benar', 'Siswa bisa melihat jawaban benar setelah quiz'],
                    ];
                    @endphp
                    @foreach($checkboxes as [$name, $checked, $label, $desc])
                    <div class="flex items-start gap-3 p-3.5 bg-slate-50 border border-slate-200 rounded-xl hover:bg-blue-50/50 hover:border-blue-100 transition-colors">
                        <input type="checkbox" name="{{ $name }}" value="1" id="{{ $name }}" data-section="soal"
                            {{ $checked ? 'checked' : '' }}
                            class="w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 flex-shrink-0">
                        <label for="{{ $name }}" class="text-sm text-slate-700 cursor-pointer">
                            <span class="font-medium">{{ $label }}</span>
                            <span class="block text-xs text-slate-400 mt-0.5">{{ $desc }}</span>
                        </label>
                    </div>
                    @endforeach

                    {{-- enable_retake - hanya untuk homework --}}
                    <div id="retakeWrapper" style="{{ old('quiz_mode', $quiz->quiz_mode) == 'homework' ? '' : 'display:none' }}"
                        class="flex items-start gap-3 p-3.5 bg-slate-50 border border-slate-200 rounded-xl hover:bg-blue-50/50 hover:border-blue-100 transition-colors">
                        <input type="checkbox" name="enable_retake" value="1" id="enable_retake" data-section="soal"
                            {{ old('enable_retake', $quiz->enable_retake) ? 'checked' : '' }}
                            class="w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 flex-shrink-0">
                        <label for="enable_retake" class="text-sm text-slate-700 cursor-pointer">
                            <span class="font-medium">Izinkan Pengulangan Quiz</span>
                            <span class="block text-xs text-slate-400 mt-0.5">Siswa boleh mengulang quiz (hanya Quiz Mandiri)</span>
                        </label>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Tampilkan Hasil Setelah</label>
                        <select name="show_result_after"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                            <option value="immediately" {{ old('show_result_after', $quiz->show_result_after) == 'immediately' ? 'selected' : '' }}>Selesai Mengerjakan</option>
                            <option value="after_exam" {{ old('show_result_after', $quiz->show_result_after) == 'after_exam' ? 'selected' : '' }}>Setelah Semua Selesai</option>
                            <option value="after_submit" {{ old('show_result_after', $quiz->show_result_after) == 'after_submit' ? 'selected' : '' }}>Setelah Submit</option>
                            <option value="never" {{ old('show_result_after', $quiz->show_result_after) == 'never' ? 'selected' : '' }}>Tidak Ditampilkan</option>
                        </select>
                    </div>

                    <div id="limitAttemptsWrapper" style="{{ old('quiz_mode', $quiz->quiz_mode) == 'homework' ? '' : 'display:none' }}" class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Maks. Percobaan</label>
                        <input type="number" name="limit_attempts" min="1" max="10"
                            value="{{ old('limit_attempts', $quiz->limit_attempts ?? 3) }}"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Nilai Kelulusan Minimal</label>
                        <div class="relative">
                            <input type="number" name="min_pass_grade" min="0" max="100" step="1"
                                value="{{ old('min_pass_grade', $quiz->min_pass_grade ?? 0) }}"
                                class="w-full px-3.5 py-2.5 pr-8 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== SECTION 5: FITUR INTERAKTIF ===== --}}
            <div id="fiturSection" class="p-6 space-y-4" style="{{ old('quiz_mode', $quiz->quiz_mode) == 'guided' ? 'display:none' : '' }}">
                <h3 class="flex items-center justify-between gap-2 text-sm font-semibold text-slate-800">
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center">
                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        Fitur Interaktif
                        <span class="ml-1 text-xs text-slate-400 font-normal">(opsional)</span>
                    </div>
                    <label class="flex items-center gap-1.5 cursor-pointer text-xs text-slate-500 font-normal">
                        <input type="checkbox" id="selectAllFitur" class="w-3.5 h-3.5 text-blue-600 rounded border-slate-300">
                        Pilih Semua
                    </label>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @php
                    $features = [
                        ['show_leaderboard', old('show_leaderboard', $quiz->show_leaderboard), 'Tampilkan Leaderboard', 'Papan peringkat siswa real-time'],
                        ['instant_feedback', old('instant_feedback', $quiz->instant_feedback), 'Feedback Instan', 'Tampilkan benar/salah setelah menjawab'],
                        ['enable_music', old('enable_music', $quiz->enable_music), 'Musik Latar', 'Putar musik selama quiz berlangsung'],
                        ['enable_memes', old('enable_memes', $quiz->enable_memes), 'Tampilkan Meme', 'Meme muncul setelah siswa menjawab'],
                        ['enable_powerups', old('enable_powerups', $quiz->enable_powerups), 'Power-ups', 'Aktifkan power-up seperti 50:50'],
                        ['streak_bonus', old('streak_bonus', $quiz->streak_bonus), 'Bonus Streak', 'Bonus poin untuk jawaban benar beruntun'],
                        ['time_bonus', old('time_bonus', $quiz->time_bonus), 'Bonus Kecepatan', 'Poin tambahan untuk jawaban cepat'],
                    ];
                    @endphp
                    @foreach($features as [$name, $checked, $label, $desc])
                    <div class="flex items-start gap-3 p-3.5 bg-slate-50 border border-slate-200 rounded-xl hover:bg-violet-50/40 hover:border-violet-100 transition-colors">
                        <input type="checkbox" name="{{ $name }}" value="1" id="{{ $name }}" data-section="fitur"
                            {{ $checked ? 'checked' : '' }}
                            class="w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 flex-shrink-0">
                        <label for="{{ $name }}" class="text-sm text-slate-700 cursor-pointer">
                            <span class="font-medium">{{ $label }}</span>
                            <span class="block text-xs text-slate-400 mt-0.5">{{ $desc }}</span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- ===== SECTION 6: KEAMANAN ===== --}}
            <div class="p-6 space-y-4">
                <h3 class="flex items-center justify-between gap-2 text-sm font-semibold text-slate-800">
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 bg-red-100 rounded flex items-center justify-center">
                            <svg class="w-3 h-3 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        Keamanan Quiz
                    </div>
                    <label class="flex items-center gap-1.5 cursor-pointer text-xs text-slate-500 font-normal">
                        <input type="checkbox" id="selectAllKeamanan" class="w-3.5 h-3.5 text-red-600 rounded border-slate-300">
                        Pilih Semua
                    </label>
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @php
                    $security = [
                        ['fullscreen_mode', old('fullscreen_mode', $quiz->fullscreen_mode), 'Mode Layar Penuh', 'Wajib fullscreen selama quiz'],
                        ['block_new_tab', old('block_new_tab', $quiz->block_new_tab), 'Blokir Tab Baru', 'Mencegah siswa buka tab/jendela baru'],
                        ['prevent_copy_paste', old('prevent_copy_paste', $quiz->prevent_copy_paste), 'Cegah Copy-Paste', 'Nonaktifkan fungsi copy, cut, paste'],
                    ];
                    @endphp
                    @foreach($security as [$name, $checked, $label, $desc])
                    <div class="flex items-start gap-3 p-3.5 bg-red-50 border border-red-100 rounded-xl">
                        <input type="checkbox" name="{{ $name }}" value="1" id="{{ $name }}" data-section="keamanan"
                            {{ $checked ? 'checked' : '' }}
                            class="w-4 h-4 mt-0.5 text-red-600 rounded border-slate-300 focus:ring-red-500 flex-shrink-0">
                        <label for="{{ $name }}" class="text-sm text-slate-700 cursor-pointer">
                            <span class="font-medium">{{ $label }}</span>
                            <span class="block text-xs text-slate-500 mt-0.5">{{ $desc }}</span>
                        </label>
                    </div>
                    @endforeach

                    <div id="violationWrapper" style="{{ old('quiz_mode', $quiz->quiz_mode) == 'homework' ? '' : 'display:none' }}" class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Batas Maksimal Pelanggaran</label>
                        <input type="number" name="violation_limit" min="1" max="20"
                            value="{{ old('violation_limit', $quiz->violation_limit ?? 3) }}"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        <p class="text-xs text-slate-400">Jawaban otomatis dikumpulkan jika melebihi batas ini</p>
                    </div>

                    <div id="violationInfoLive" class="md:col-span-2 p-3.5 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-2 text-xs text-slate-600"
                        style="{{ old('quiz_mode', $quiz->quiz_mode) == 'homework' ? 'display:none' : '' }}">
                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span id="violationInfoText">Pada mode Live Quiz / Quiz Terpandu, pelanggaran tidak menyebabkan auto-submit. Kartu siswa berubah merah di panel guru.</span>
                    </div>
                </div>
            </div>

            {{-- ===== SECTION 7: STATUS QUIZ ===== --}}
            <div class="p-6 space-y-4">
                <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                    <div class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center">
                        <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    Status Quiz
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Status Publikasi</label>
                        <select name="status"
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                            <option value="draft" {{ old('status', $quiz->status) == 'draft' ? 'selected' : '' }}>Draft — Belum Dipublikasikan</option>
                            <option value="active" {{ old('status', $quiz->status) == 'active' ? 'selected' : '' }}>Aktif — Siswa Bisa Mengakses</option>
                            <option value="inactive" {{ old('status', $quiz->status) == 'inactive' ? 'selected' : '' }}>Nonaktif — Dihentikan Sementara</option>
                            <option value="finished" {{ old('status', $quiz->status) == 'finished' ? 'selected' : '' }}>Selesai — Quiz Telah Berakhir</option>
                        </select>
                        <p class="text-xs text-slate-400">Status menentukan apakah siswa bisa mengakses quiz ini</p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-sm font-semibold text-slate-700">Status Ruangan</label>
                        <div class="flex items-center gap-3 p-3.5 rounded-xl border
                            {{ $quiz->is_quiz_started ? 'bg-yellow-50 border-yellow-200' : ($quiz->is_room_open ? 'bg-emerald-50 border-emerald-200' : 'bg-slate-50 border-slate-200') }}">
                            @if($quiz->is_quiz_started)
                                <span class="w-3 h-3 rounded-full bg-yellow-500 animate-pulse flex-shrink-0"></span>
                                <div>
                                    <p class="text-sm font-semibold text-yellow-700">Quiz Sedang Berlangsung</p>
                                    <p class="text-xs text-yellow-600 mt-0.5">Dimulai: {{ $quiz->quiz_started_at ? $quiz->quiz_started_at->format('d/m/Y H:i') : '-' }}</p>
                                </div>
                            @elseif($quiz->is_room_open)
                                <span class="w-3 h-3 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                <div>
                                    <p class="text-sm font-semibold text-emerald-700">Ruangan Terbuka</p>
                                    <p class="text-xs text-emerald-600 mt-0.5">Siswa dapat masuk ke ruangan</p>
                                </div>
                            @else
                                <span class="w-3 h-3 rounded-full bg-slate-400 flex-shrink-0"></span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-600">Ruangan Tertutup</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Buka ruangan dari halaman preview</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== SUBMIT ===== --}}
            <div class="p-6 flex flex-wrap items-center justify-between gap-3 bg-slate-50 border-t border-slate-200">
                <a href="{{ route('guru.quiz.index') }}"
                    class="flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold rounded-xl text-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>
                <div class="flex items-center gap-3">
                    <a href="{{ route('guru.quiz.preview', $quiz->id) }}"
                        class="flex items-center gap-2 px-5 py-2.5 bg-slate-700 hover:bg-slate-800 text-white font-semibold rounded-xl text-sm shadow-sm transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Preview & Room
                    </a>
                    <a href="{{ route('guru.quiz.questions', $quiz->id) }}"
                        class="flex items-center gap-2 px-5 py-2.5 bg-blue-100 hover:bg-blue-200 text-blue-700 font-semibold rounded-xl text-sm transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Edit Soal
                    </a>
                    <button type="submit" id="submitBtn"
                        class="flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-sm hover:shadow-md transition-all cursor-pointer">
                        <svg id="submitIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span id="submitText">Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes slideIn { from { opacity:0; transform:translateX(40px); } to { opacity:1; transform:translateX(0); } }
.chip { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; background:#dbeafe; color:#1d4ed8; border-radius:6px; font-size:12px; font-weight:500; }
.chip button { display:flex; align-items:center; background:none; border:none; cursor:pointer; color:inherit; padding:0; margin-left:2px; }
.chip button:hover { color:#1e3a8a; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Data dari server ──────────────────────────────────────────
    let selectedClassIds = @json(array_map('strval', $assignedClassIds ?? []));
    let classesList      = [];
    const initialSubjectId = '{{ old('subject_id', $quiz->subject_id) }}';
    const quizIndexUrl     = '{{ route('guru.quiz.index') }}';
    const csrfToken        = document.querySelector('meta[name="csrf-token"]')?.content
                             || document.querySelector('input[name="_token"]')?.value || '';

    // ── Elemen DOM ────────────────────────────────────────────────
    const form              = document.getElementById('quizForm');
    const subjectSelect     = document.getElementById('subject_id');
    const classLoading      = document.getElementById('classLoading');
    const classDropdownBtn  = document.getElementById('classDropdownBtn');
    const classDropdown     = document.getElementById('classDropdown');
    const chipContainer     = document.getElementById('chipContainer');
    const chipPlaceholder   = document.getElementById('chipPlaceholder');
    const classListContainer= document.getElementById('classListContainer');
    const classHiddenInputs = document.getElementById('classHiddenInputs');
    const classSearch       = document.getElementById('classSearch');
    const dropdownChevron   = document.getElementById('dropdownChevron');
    const submitBtn         = document.getElementById('submitBtn');
    const submitText        = document.getElementById('submitText');
    const submitIcon        = document.getElementById('submitIcon');

    // Mode-dependent elements handled by updateModeUI()
    const modeRadios        = document.querySelectorAll('input[name="quiz_mode"]');

    // ── Helpers ───────────────────────────────────────────────────
    function getMode() {
        const checked = document.querySelector('input[name="quiz_mode"]:checked');
        return checked ? checked.value : 'live';
    }

    function showToast(message, type = 'info') {
        const old = document.getElementById('quiz-toast');
        if (old) old.remove();
        const colors = { success: '#059669', error: '#dc2626', info: '#2563eb' };
        const toast = document.createElement('div');
        toast.id = 'quiz-toast';
        Object.assign(toast.style, {
            position:'fixed', top:'20px', right:'20px', zIndex:'9999',
            display:'flex', alignItems:'flex-start', gap:'12px',
            padding:'14px 18px', borderRadius:'12px', boxShadow:'0 8px 30px rgba(0,0,0,0.15)',
            background: colors[type] || colors.info, color:'#fff',
            fontSize:'14px', maxWidth:'360px', animation:'slideIn 0.3s ease',
        });
        toast.innerHTML = `<div style="flex:1;white-space:pre-line">${message}</div>
            <button onclick="document.getElementById('quiz-toast').remove()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:18px;line-height:1;opacity:.8;margin-left:4px">&times;</button>`;
        document.body.appendChild(toast);
        if (type !== 'error') setTimeout(() => toast?.remove(), 4000);
    }

    // ── Kelas: render chips ───────────────────────────────────────
    function renderChips() {
        // Clear existing chips (keep placeholder span structure)
        Array.from(chipContainer.children).forEach(el => {
            if (el.id !== 'chipPlaceholder') el.remove();
        });

        if (selectedClassIds.length === 0) {
            chipPlaceholder.style.display = '';
            chipPlaceholder.textContent = classesList.length === 0
                ? 'Pilih mata pelajaran terlebih dahulu'
                : 'Pilih kelas...';
        } else {
            chipPlaceholder.style.display = 'none';
            selectedClassIds.forEach(id => {
                const cls = classesList.find(c => String(c.id) === String(id));
                const chip = document.createElement('span');
                chip.className = 'chip';
                chip.dataset.id = id;
                chip.innerHTML = `${cls ? cls.name : id}
                    <button type="button" aria-label="Hapus">
                        <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>`;
                chip.querySelector('button').addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectedClassIds = selectedClassIds.filter(v => String(v) !== String(id));
                    renderChips();
                    renderClassList();
                    updateHiddenInputs();
                });
                chipContainer.insertBefore(chip, chipPlaceholder);
            });
        }
    }

    function updateHiddenInputs() {
        classHiddenInputs.innerHTML = '';
        selectedClassIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'class_ids[]';
            input.value = id;
            classHiddenInputs.appendChild(input);
        });
    }

    // ── Kelas: render dropdown list ───────────────────────────────
    function renderClassList() {
        const q = (classSearch?.value || '').toLowerCase();
        const filtered = classesList.filter(c => c.name.toLowerCase().includes(q));

        // Group by prefix
        const groups = {};
        filtered.forEach(cls => {
            const prefix = cls.name.trim().split(/\s+/)[0] || 'Lainnya';
            if (!groups[prefix]) groups[prefix] = [];
            groups[prefix].push(cls);
        });

        const sortedKeys = Object.keys(groups).sort((a,b) => a.localeCompare(b, undefined, {numeric:true}));

        if (sortedKeys.length === 0) {
            classListContainer.innerHTML = '<div style="text-align:center;padding:16px;font-size:12px;color:#94a3b8">Kelas tidak ditemukan</div>';
            return;
        }

        classListContainer.innerHTML = '';
        sortedKeys.forEach(label => {
            const group = groups[label];
            const allSelected = group.every(c => selectedClassIds.includes(String(c.id)));

            const groupDiv = document.createElement('div');
            groupDiv.style.marginBottom = '4px';
            groupDiv.innerHTML = `
                <div style="display:flex;align-items:center;justify-content:space-between;padding:4px 8px 2px">
                    <span style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em">${label}</span>
                    <button type="button" data-group="${label}" class="group-toggle-btn"
                        style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:6px;border:none;cursor:pointer;background:${allSelected?'#dbeafe':'#f1f5f9'};color:${allSelected?'#1d4ed8':'#64748b'}">
                        ${allSelected ? 'Batal Semua' : 'Pilih Semua ('+group.length+')'}
                    </button>
                </div>`;

            group.forEach(cls => {
                const isChecked = selectedClassIds.includes(String(cls.id));
                const label = document.createElement('label');
                label.style.cssText = 'display:flex;align-items:center;gap:8px;padding:8px;border-radius:8px;cursor:pointer;transition:background .15s';
                label.innerHTML = `
                    <input type="checkbox" value="${cls.id}" ${isChecked ? 'checked' : ''}
                        style="width:14px;height:14px;accent-color:#3b82f6;flex-shrink:0">
                    <span style="font-size:14px;color:#374151">${cls.name}</span>`;
                label.addEventListener('mouseenter', () => label.style.background = '#eff6ff');
                label.addEventListener('mouseleave', () => label.style.background = '');
                const checkbox = label.querySelector('input');
                checkbox.addEventListener('change', () => {
                    if (checkbox.checked) {
                        if (!selectedClassIds.includes(String(cls.id))) {
                            selectedClassIds.push(String(cls.id));
                        }
                    } else {
                        selectedClassIds = selectedClassIds.filter(v => String(v) !== String(cls.id));
                    }
                    renderChips();
                    updateHiddenInputs();
                    // Re-render only the group header button
                    renderClassList();
                });
                groupDiv.appendChild(label);
            });

            classListContainer.appendChild(groupDiv);
        });

        // Attach group toggle handlers
        classListContainer.querySelectorAll('.group-toggle-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const groupLabel = btn.dataset.group;
                const groupClasses = groups[groupLabel] || [];
                const allSel = groupClasses.every(c => selectedClassIds.includes(String(c.id)));
                if (allSel) {
                    selectedClassIds = selectedClassIds.filter(id => !groupClasses.some(c => String(c.id) === String(id)));
                } else {
                    groupClasses.forEach(c => {
                        if (!selectedClassIds.includes(String(c.id))) selectedClassIds.push(String(c.id));
                    });
                }
                renderChips();
                updateHiddenInputs();
                renderClassList();
            });
        });
    }

    // ── Kelas: load via API ───────────────────────────────────────
    function loadClasses(subjectId, keepSelected = false) {
        if (!subjectId) {
            classesList = [];
            if (!keepSelected) selectedClassIds = [];
            renderChips();
            renderClassList();
            updateHiddenInputs();
            return;
        }

        classLoading.classList.remove('hidden');
        classDropdownBtn.style.display = 'none';

        const prevSelected = keepSelected ? [...selectedClassIds] : [];

        fetch(`/guru/quiz/get-classes-by-subject/${subjectId}`)
            .then(r => r.json())
            .then(data => {
                classesList = (data.success && data.classes) ? data.classes : [];
                const validIds = classesList.map(c => String(c.id));
                selectedClassIds = keepSelected
                    ? prevSelected.filter(id => validIds.includes(String(id)))
                    : [];
                renderChips();
                renderClassList();
                updateHiddenInputs();
            })
            .catch(() => {
                classesList = [];
                selectedClassIds = [];
                renderChips();
                classListContainer.innerHTML = '<div style="text-align:center;padding:16px;font-size:12px;color:#ef4444">Gagal memuat kelas</div>';
            })
            .finally(() => {
                classLoading.classList.add('hidden');
                classDropdownBtn.style.display = '';
            });
    }

    // ── Mode quiz: toggle elemen ──────────────────────────────────
    function setWrapperState(wrapperId, visible) {
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return;
        wrapper.style.display = visible ? '' : 'none';
        // Disable inputs ketika hidden agar tidak divalidasi HTML5
        wrapper.querySelectorAll('input, select, textarea').forEach(el => {
            el.disabled = !visible;
        });
    }

    function updateModeUI() {
        const mode = getMode();
        const isHomework = mode === 'homework';
        const isGuided   = mode === 'guided';

        setWrapperState('start_at_wrapper', isHomework);
        setWrapperState('end_at_wrapper', isHomework);
        setWrapperState('retakeWrapper', isHomework);
        setWrapperState('limitAttemptsWrapper', isHomework);
        setWrapperState('violationWrapper', isHomework);

        const violationInfoLive = document.getElementById('violationInfoLive');
        if (violationInfoLive) violationInfoLive.style.display = isHomework ? 'none' : '';

        const fiturSection = document.getElementById('fiturSection');
        if (fiturSection) fiturSection.style.display = isGuided ? 'none' : '';
    }

    // ── Pilih Semua per section ───────────────────────────────────
    function setupSelectAll(masterId, section) {
        const master = document.getElementById(masterId);
        if (!master) return;
        master.addEventListener('change', () => {
            document.querySelectorAll(`input[data-section="${section}"]`).forEach(cb => {
                cb.checked = master.checked;
            });
        });
    }
    setupSelectAll('selectAllSoal', 'soal');
    setupSelectAll('selectAllFitur', 'fitur');
    setupSelectAll('selectAllKeamanan', 'keamanan');

    // ── Dropdown toggle ───────────────────────────────────────────
    classDropdownBtn.addEventListener('click', () => {
        const open = classDropdown.classList.toggle('hidden');
        dropdownChevron.style.transform = open ? '' : 'rotate(180deg)';
        if (!open) {  // just opened
            dropdownChevron.style.transform = 'rotate(180deg)';
            renderClassList();
            setTimeout(() => classSearch?.focus(), 50);
        } else {
            dropdownChevron.style.transform = '';
        }
    });

    document.addEventListener('click', (e) => {
        if (!document.getElementById('classSelectWrapper').contains(e.target)) {
            classDropdown.classList.add('hidden');
            dropdownChevron.style.transform = '';
        }
    });

    classSearch?.addEventListener('input', renderClassList);

    // ── Radio mode change ─────────────────────────────────────────
    modeRadios.forEach(r => r.addEventListener('change', updateModeUI));

    // ── Submit ────────────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (selectedClassIds.length === 0) {
            showToast('Silakan pilih minimal satu kelas terlebih dahulu.', 'error');
            return;
        }

        const mode = getMode();
        if (mode === 'homework') {
            const startAt = document.getElementById('start_at')?.value;
            const endAt   = document.getElementById('end_at')?.value;
            if (!startAt || !endAt) {
                showToast('Mode Quiz Mandiri memerlukan waktu mulai dan waktu berakhir.', 'error');
                return;
            }
            if (new Date(endAt) <= new Date(startAt)) {
                showToast('Waktu berakhir harus lebih dari waktu mulai.', 'error');
                return;
            }
        }

        // Loading state
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
        submitIcon.outerHTML = `<svg id="submitIcon" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>`;
        submitText.textContent = 'Menyimpan...';

        const formData = new FormData(form);
        formData.set('_method', 'PUT');

        // Pastikan class_ids[] dari state JS (bukan hidden inputs yang mungkin duplikat)
        formData.delete('class_ids[]');
        selectedClassIds.forEach(id => formData.append('class_ids[]', id));

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            }
        })
        .then(response => {
            const ct = response.headers.get('content-type') || '';
            if (!ct.includes('application/json')) {
                // Non-JSON = redirect HTML = sukses
                showToast('Quiz berhasil disimpan!', 'success');
                setTimeout(() => window.location.href = quizIndexUrl, 800);
                return;
            }
            return response.json().then(data => {
                if (response.ok && data.success) {
                    showToast(data.message || 'Quiz berhasil disimpan!', 'success');
                    setTimeout(() => window.location.href = data.redirect || quizIndexUrl, 800);
                } else {
                    const msg = data.errors
                        ? Object.values(data.errors).flat().join('\n')
                        : (data.message || 'Gagal menyimpan perubahan.');
                    showToast(msg, 'error');
                    resetSubmitBtn();
                }
            });
        })
        .catch(err => {
            showToast('Terjadi kesalahan koneksi: ' + err.message, 'error');
            resetSubmitBtn();
        });
    });

    function resetSubmitBtn() {
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
        document.getElementById('submitIcon').outerHTML = `<svg id="submitIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`;
        submitText.textContent = 'Simpan Perubahan';
    }

    // ── Init ──────────────────────────────────────────────────────
    updateModeUI();
    if (initialSubjectId) {
        loadClasses(initialSubjectId, true);  // preserve assignedClassIds
    }

    subjectSelect.addEventListener('change', function () {
        loadClasses(this.value, false);  // ganti mapel → reset kelas
    });

});
</script>
@endsection
