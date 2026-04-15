@extends('layouts.appTeacher')
@section('title', 'Buat Quiz Baru - SmartLab')
@section('breadcrumb', 'Quiz Interaktif / Buat Baru')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">

        {{-- ===== STEP INDICATOR ===== --}}
        <div class="flex items-center justify-center gap-3">
            <div class="flex items-center gap-2">
                <div
                    class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-bold shadow-md shadow-blue-200">
                    1</div>
                <span class="text-sm font-semibold text-blue-700">Pengaturan Quiz</span>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-8 h-px bg-slate-300"></div>
                <div class="w-1.5 h-1.5 bg-slate-300 rounded-full"></div>
                <div class="w-8 h-px bg-slate-300"></div>
            </div>
            <div class="flex items-center gap-2 opacity-50">
                <div
                    class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-sm font-bold">
                    2</div>
                <span class="text-sm font-medium text-slate-500">Buat Soal</span>
            </div>
        </div>

        {{-- ===== ERRORS ===== --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-xl">
                <div class="flex items-center gap-2 font-semibold text-sm mb-2">
                    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.282 16.5c-.77.833.192 2.5 1.732 2.5z" />
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

        {{-- ===== MAIN FORM CARD ===== --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="quizForm()"
            x-init="init()">

            {{-- Card Header --}}
            <div class="p-6 bg-gradient-to-r from-blue-600 to-blue-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white font-jakarta">Buat Quiz Interaktif Baru</h2>
                        <p class="text-blue-200 text-sm">Atur pengaturan quiz yang efektif dan menyenangkan untuk siswa.</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('guru.quiz.store') }}" method="POST" id="quizForm" @submit.prevent="handleSubmit"
                class="divide-y divide-slate-100">
                @csrf
                <input type="hidden" name="type" value="QUIZ">

                {{-- ===== SECTION 1: INFO DASAR ===== --}}
                <div class="p-6 space-y-5">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                        <div class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center">
                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        Informasi Dasar Quiz
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Judul Quiz <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="title" placeholder="Contoh: Quiz Matematika Bab 3"
                                value="{{ old('title') }}"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                required x-model="title">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Mata Pelajaran <span
                                    class="text-red-500">*</span></label>
                            <select name="subject_id" required
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
                                x-on:change="getClassesBySubject($event.target.value)" x-model="subjectId">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach ($mapels as $mapel)
                                    <option value="{{ $mapel->id }}"
                                        {{ old('subject_id') == $mapel->id ? 'selected' : '' }}>
                                        {{ $mapel->name_subject }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Kelas Target - Multi-select chips --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Kelas Target <span class="text-red-500">*</span>
                            <span class="font-normal text-slate-400 text-xs ml-1">(boleh pilih lebih dari satu)</span>
                        </label>

                        <div x-show="loadingClasses" class="flex items-center gap-2 text-sm text-blue-600 py-1.5">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Memuat kelas...
                        </div>

                        <div x-data="{ open: false }" @click.away="open = false" class="relative" x-show="!loadingClasses">
                            <button type="button" @click="open = !open"
                                class="w-full px-3.5 py-2.5 border border-slate-300 rounded-lg text-sm bg-white text-left flex justify-between items-center hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                                <div class="flex flex-wrap gap-1.5 flex-1 items-center min-h-[20px]">
                                    <template x-for="id in selectedClassIds" :key="id">
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-md text-xs font-medium">
                                            <span x-text="classesList.find(c => c.id == id)?.name ?? id"></span>
                                            <button type="button"
                                                @click.stop="selectedClassIds = selectedClassIds.filter(v => v != id); updateHiddenSelect()"
                                                class="hover:text-blue-900 ml-0.5">
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>
                                    <span x-show="selectedClassIds.length === 0 && classesList.length === 0"
                                        class="text-slate-400 text-xs">
                                        Pilih mata pelajaran terlebih dahulu
                                    </span>
                                    <span x-show="selectedClassIds.length === 0 && classesList.length > 0"
                                        class="text-slate-400 text-xs">
                                        Pilih kelas...
                                    </span>
                                </div>
                                <svg class="w-4 h-4 text-slate-400 flex-shrink-0 transition-transform"
                                    :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <div x-show="open && classesList.length > 0" x-cloak
                                class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg max-h-72 flex flex-col">
                                {{-- Search --}}
                                <div class="p-2 border-b border-slate-100">
                                    <input type="text" x-model="classSearch" placeholder="Cari kelas..."
                                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="overflow-y-auto flex-1 p-1.5">
                                    {{-- Kelompok per kategori --}}
                                    <template x-for="group in filteredClassGroups()" :key="group.label">
                                        <div class="mb-1">
                                            {{-- Header kategori + tombol Pilih Semua kategori --}}
                                            <div class="flex items-center justify-between px-2 py-1 mb-0.5">
                                                <span class="text-xs font-bold text-slate-500 uppercase tracking-wide" x-text="group.label"></span>
                                                <button type="button"
                                                    @click.prevent="toggleGroupClasses(group)"
                                                    class="text-xs font-semibold px-2 py-0.5 rounded-md transition-colors"
                                                    :class="isGroupAllSelected(group)
                                                        ? 'bg-blue-100 text-blue-700 hover:bg-blue-200'
                                                        : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
                                                    x-text="isGroupAllSelected(group) ? 'Batal Semua' : 'Pilih Semua (' + group.classes.length + ')'">
                                                </button>
                                            </div>
                                            {{-- Item kelas dalam kategori --}}
                                            <template x-for="cls in group.classes" :key="cls.id">
                                                <label class="flex items-center gap-2 px-2 py-2 hover:bg-blue-50 rounded-lg cursor-pointer transition-colors">
                                                    <input type="checkbox" :value="cls.id" x-model="selectedClassIds"
                                                        @change="updateHiddenSelect()"
                                                        class="w-3.5 h-3.5 text-blue-600 rounded border-slate-300">
                                                    <span class="text-sm text-slate-700" x-text="cls.name"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </template>
                                    <div x-show="filteredClassGroups().every(g => g.classes.length === 0)"
                                        class="text-center py-4 text-xs text-slate-400">Kelas tidak ditemukan</div>
                                </div>
                            </div>
                        </div>

                        <select id="class_id" name="class_ids[]" multiple class="hidden" required></select>
                        <p class="text-xs text-slate-400 mt-1.5">Pilih satu atau lebih kelas yang akan mengerjakan quiz ini
                        </p>
                    </div>
                </div>

                {{-- ===== SECTION 2: MODE QUIZ ===== --}}
                <div class="p-6 space-y-5">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                        <div class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center">
                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7" />
                            </svg>
                        </div>
                        Mode Quiz
                    </h3>

                    {{-- Layout: Homework di kiri, grup Live+Terpandu di kanan --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                        {{-- Mode Homework --}}
                        <label class="relative cursor-pointer">
                            <input type="radio" name="quiz_mode" value="homework" x-model="quizMode"
                                class="sr-only peer" {{ old('quiz_mode', 'live') == 'homework' ? 'checked' : '' }}>
                            <div
                                class="border-2 rounded-xl p-4 h-full transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50 border-slate-200 hover:border-blue-300 hover:bg-slate-50">
                                <div class="flex items-center gap-2.5 mb-3">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-slate-100 peer-checked:bg-blue-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-sm text-slate-800">Quiz Mandiri</p>
                                        <p class="text-xs text-slate-500">Tanpa pemantauan guru</p>
                                    </div>
                                </div>
                                <ul class="text-xs text-slate-500 space-y-1">
                                    <li class="flex items-center gap-1.5"><svg
                                            class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>Dikerjakan kapan saja</li>
                                    <li class="flex items-center gap-1.5"><svg
                                            class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>Batas waktu mulai & akhir</li>
                                    <li class="flex items-center gap-1.5"><svg
                                            class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>Auto-submit jika over batas pelanggaran</li>
                                </ul>
                            </div>
                        </label>

                        {{-- Grup Dipantau Guru: Live + Terpandu --}}
                        <div class="border-2 rounded-xl overflow-hidden transition-all
                            border-slate-200
                            [&:has(input[value=live]:checked)]:border-blue-500
                            [&:has(input[value=guided]:checked)]:border-blue-500">
                            {{-- Label grup --}}
                            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 border-b border-slate-200">
                                <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <span class="text-xs font-semibold text-blue-600 uppercase tracking-wide">Dipantau Guru (Real-time)</span>
                            </div>
                            <div class="grid grid-cols-2 divide-x divide-slate-200">

                        {{-- Mode Live --}}
                        <label class="relative cursor-pointer">
                            <input type="radio" name="quiz_mode" value="live" x-model="quizMode"
                                class="sr-only peer" {{ old('quiz_mode', 'live') == 'live' ? 'checked' : '' }}>
                            <div
                                class="p-4 h-full transition-all peer-checked:bg-blue-50 hover:bg-slate-50">
                                <div class="flex items-center gap-2.5 mb-3">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 10l4.553-2.069A1 1 0 0121 8.868v6.264a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-sm text-slate-800">Live Quiz</p>
                                        <p class="text-xs text-slate-500">Dipantau langsung</p>
                                    </div>
                                </div>
                                <ul class="text-xs text-slate-500 space-y-1">
                                    <li class="flex items-center gap-1.5"><svg
                                            class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>Kode room siswa</li>
                                    <li class="flex items-center gap-1.5"><svg
                                            class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>Monitor real-time</li>
                                    <li class="flex items-center gap-1.5"><svg
                                            class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>Kartu merah pelanggaran</li>
                                </ul>
                            </div>
                        </label>

                        {{-- Mode Guided --}}
                        <label class="relative cursor-pointer">
                            <input type="radio" name="quiz_mode" value="guided" x-model="quizMode"
                                class="sr-only peer" {{ old('quiz_mode') == 'guided' ? 'checked' : '' }}>
                            <div
                                class="p-4 h-full transition-all peer-checked:bg-blue-50 hover:bg-slate-50">
                                <div class="flex items-center gap-2.5 mb-3">
                                    <div
                                        class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-sm text-slate-800">Quiz Terpandu</p>
                                        <p class="text-xs text-slate-500">Soal di layar guru</p>
                                    </div>
                                </div>
                                <ul class="text-xs text-slate-500 space-y-1">
                                    <li class="flex items-center gap-1.5"><svg
                                            class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>Soal tampil proyektor</li>
                                    <li class="flex items-center gap-1.5"><svg
                                            class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>Siswa jawab di perangkat</li>
                                    <li class="flex items-center gap-1.5"><svg
                                            class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>Guru kontrol tempo soal</li>
                                </ul>
                            </div>
                        </label>

                            </div>{{-- end grid inner --}}
                        </div>{{-- end grup dipantau --}}

                    </div>{{-- end grid outer --}}

                    <div
                        class="flex items-start gap-2.5 p-3.5 bg-blue-50 border border-blue-100 rounded-xl text-xs text-slate-600">
                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>
                            <span x-show="quizMode === 'homework'"><strong>Quiz Mandiri:</strong> Siswa mengerjakan dalam
                                rentang waktu yang ditentukan. Pelanggaran melebihi batas akan otomatis submit.</span>
                            <span x-show="quizMode === 'live'" x-cloak><strong>Live Quiz:</strong> Guru buka ruangan, siswa
                                masuk dengan kode. Guru memantau langsung — pelanggaran ditandai tapi tidak
                                auto-submit.</span>
                            <span x-show="quizMode === 'guided'" x-cloak><strong>Quiz Terpandu:</strong> Soal hanya tampil
                                di layar guru (proyektor). Siswa cukup melihat pilihan di perangkat. Guru mengontrol
                                perpindahan soal.</span>
                        </span>
                    </div>
                </div>

                {{-- ===== SECTION 3: WAKTU ===== --}}
                <div class="p-6 space-y-5">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                        <div class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center">
                            <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        Pengaturan Waktu
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Waktu Per Soal</label>
                            <div class="flex gap-2">
                                {{-- Select hanya pengontrol mode, tanpa name --}}
                                <select x-model="timePerQuestionMode"
                                    class="px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                                    <option value="timed">Dengan Batas</option>
                                    <option value="unlimited">Tanpa Batas</option>
                                </select>

                                {{-- Input number untuk mode timed --}}
                                <input type="number" name="time_per_question" min="5" max="600"
                                    value="{{ old('time_per_question', 30) }}"
                                    class="flex-1 px-3 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                                    x-show="timePerQuestionMode === 'timed'"
                                    :disabled="timePerQuestionMode !== 'timed'"
                                    :required="timePerQuestionMode === 'timed'">

                                {{-- Input hidden untuk mode unlimited --}}
                                <input type="hidden" name="time_per_question" value="0"
                                    x-show="timePerQuestionMode === 'unlimited'"
                                    :disabled="timePerQuestionMode !== 'unlimited'">
                            </div>
                            <p class="text-xs text-slate-400 mt-1">Dalam detik. 0 = tanpa batas waktu per soal.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Durasi Total (menit) <span
                                    class="text-red-500">*</span></label>
                            <input type="number" name="duration" min="1" max="480"
                                value="{{ old('duration', 30) }}"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                                required>
                            <p class="text-xs text-slate-400 mt-1">Total waktu maksimal pengerjaan quiz</p>
                        </div>

                        <div x-show="quizMode === 'homework'" x-transition>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Waktu Mulai <span
                                    class="text-red-500">*</span></label>
                            <input type="datetime-local" name="start_at" value="{{ old('start_at') }}"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                                :required="quizMode === 'homework'">
                        </div>

                        <div x-show="quizMode === 'homework'" x-transition>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Waktu Berakhir <span
                                    class="text-red-500">*</span></label>
                            <input type="datetime-local" name="end_at" value="{{ old('end_at') }}"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                                :required="quizMode === 'homework'">
                        </div>
                    </div>
                </div>

                {{-- ===== SECTION 4: PENGATURAN SOAL ===== --}}
                <div class="p-6 space-y-4">
                    <h3 class="flex items-center justify-between gap-2 text-sm font-semibold text-slate-800">
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center">
                                <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            Pengaturan Soal & Hasil
                        </div>
                        <label class="flex items-center gap-1.5 cursor-pointer text-xs text-slate-500 font-normal">
                            <input type="checkbox"
                                :checked="isSectionAllSelected('soal')"
                                @change="toggleSectionAll('soal')"
                                class="w-3.5 h-3.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                            Pilih Semua
                        </label>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @php
                            $checkboxes = [
                                [
                                    'shuffle_question',
                                    old('shuffle_question', 1),
                                    'Acak Urutan Soal',
                                    'Setiap siswa mendapat urutan soal berbeda',
                                    'blue',
                                ],
                                [
                                    'shuffle_answer',
                                    old('shuffle_answer', 1),
                                    'Acak Pilihan Jawaban',
                                    'Urutan A/B/C/D diacak per siswa',
                                    'blue',
                                ],
                                [
                                    'show_score',
                                    old('show_score', 1),
                                    'Tampilkan Skor',
                                    'Siswa dapat melihat nilainya setelah selesai',
                                    'blue',
                                ],
                                [
                                    'show_correct_answer',
                                    old('show_correct_answer'),
                                    'Tampilkan Jawaban Benar',
                                    'Siswa bisa melihat jawaban benar setelah quiz',
                                    'blue',
                                ],
                                [
                                    'enable_retake',
                                    old('enable_retake'),
                                    'Izinkan Pengulangan Quiz',
                                    'Siswa boleh mengulang quiz (hanya Quiz Mandiri)',
                                    'blue',
                                ],
                            ];
                        @endphp

                        @foreach ($checkboxes as [$name, $checked, $label, $desc, $color])
                            @if ($name === 'enable_retake')
                            {{-- Hanya tampilkan enable_retake untuk mode homework --}}
                            <div x-show="quizMode === 'homework'" x-transition
                                class="flex items-start gap-3 p-3.5 bg-slate-50 border border-slate-200 rounded-xl hover:bg-blue-50/50 hover:border-blue-100 transition-colors">
                                <input type="checkbox" name="{{ $name }}" value="1"
                                    id="{{ $name }}" {{ $checked ? 'checked' : '' }}
                                    x-model="enableRetake"
                                    class="w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 flex-shrink-0">
                                <label for="{{ $name }}" class="text-sm text-slate-700 cursor-pointer">
                                    <span class="font-medium">{{ $label }}</span>
                                    <span class="block text-xs text-slate-400 mt-0.5">{{ $desc }}</span>
                                </label>
                            </div>
                            @else
                            <div
                                class="flex items-start gap-3 p-3.5 bg-slate-50 border border-slate-200 rounded-xl hover:bg-blue-50/50 hover:border-blue-100 transition-colors">
                                <input type="checkbox" name="{{ $name }}" value="1"
                                    id="{{ $name }}" {{ $checked ? 'checked' : '' }}
                                    class="w-4 h-4 mt-0.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500 flex-shrink-0">
                                <label for="{{ $name }}" class="text-sm text-slate-700 cursor-pointer">
                                    <span class="font-medium">{{ $label }}</span>
                                    <span class="block text-xs text-slate-400 mt-0.5">{{ $desc }}</span>
                                </label>
                            </div>
                            @endif
                        @endforeach

                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-700">Tampilkan Hasil Setelah</label>
                            <select name="show_result_after"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                                <option value="immediately"
                                    {{ old('show_result_after', 'immediately') == 'immediately' ? 'selected' : '' }}>
                                    Selesai Mengerjakan</option>
                                <option value="after_exam"
                                    {{ old('show_result_after') == 'after_exam' ? 'selected' : '' }}>Setelah Semua Selesai
                                </option>
                                <option value="after_submit"
                                    {{ old('show_result_after') == 'after_submit' ? 'selected' : '' }}>Setelah Submit
                                </option>
                                <option value="never" {{ old('show_result_after') == 'never' ? 'selected' : '' }}>Tidak
                                    Ditampilkan</option>
                            </select>
                        </div>

                        <div x-show="enableRetake" x-transition class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-700">Maks. Percobaan</label>
                            <input type="number" name="limit_attempts" min="1" max="10"
                                value="{{ old('limit_attempts', 3) }}"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-700">Nilai Kelulusan Minimal</label>
                            <div class="relative">
                                <input type="number" name="min_pass_grade" min="0" max="100"
                                    step="1" value="{{ old('min_pass_grade', 0) }}"
                                    class="w-full px-3.5 py-2.5 pr-8 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                                <span
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== SECTION 5: FITUR INTERAKTIF ===== --}}
                {{-- Disembunyikan untuk mode Guided karena tidak didukung --}}
                <div class="p-6 space-y-4" x-show="quizMode !== 'guided'" x-transition>
                    <h3 class="flex items-center justify-between gap-2 text-sm font-semibold text-slate-800">
                        <div class="flex items-center gap-2">
                            <div class="w-5 h-5 bg-blue-100 rounded flex items-center justify-center">
                                <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </div>
                            Fitur Interaktif
                            <span class="ml-1 text-xs text-slate-400 font-normal">(opsional)</span>
                        </div>
                        <label class="flex items-center gap-1.5 cursor-pointer text-xs text-slate-500 font-normal">
                            <input type="checkbox"
                                :checked="isSectionAllSelected('fitur')"
                                @change="toggleSectionAll('fitur')"
                                class="w-3.5 h-3.5 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                            Pilih Semua
                        </label>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @php
                            $features = [
                                ['show_leaderboard', 'Tampilkan Leaderboard', 'Papan peringkat siswa real-time'],
                                ['instant_feedback', 'Feedback Instan', 'Tampilkan benar/salah setelah menjawab'],
                                ['enable_music', 'Musik Latar', 'Putar musik selama quiz berlangsung'],
                                ['enable_memes', 'Tampilkan Meme', 'Meme muncul setelah siswa menjawab'],
                                ['enable_powerups', 'Power-ups', 'Aktifkan power-up seperti 50:50'],
                                ['streak_bonus', 'Bonus Streak', 'Bonus poin untuk jawaban benar beruntun'],
                                ['time_bonus', 'Bonus Kecepatan', 'Poin tambahan untuk jawaban cepat'],
                            ];
                        @endphp
                        @foreach ($features as [$name, $label, $desc])
                            <div
                                class="flex items-start gap-3 p-3.5 bg-slate-50 border border-slate-200 rounded-xl hover:bg-violet-50/40 hover:border-violet-100 transition-colors">
                                <input type="checkbox" name="{{ $name }}" value="1"
                                    id="{{ $name }}" {{ old($name) ? 'checked' : '' }}
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            Keamanan Quiz
                        </div>
                        <label class="flex items-center gap-1.5 cursor-pointer text-xs text-slate-500 font-normal">
                            <input type="checkbox"
                                :checked="isSectionAllSelected('keamanan')"
                                @change="toggleSectionAll('keamanan')"
                                class="w-3.5 h-3.5 text-red-600 rounded border-slate-300 focus:ring-red-500">
                            Pilih Semua
                        </label>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @php
                            $security = [
                                [
                                    'fullscreen_mode',
                                    old('fullscreen_mode', 1),
                                    'Mode Layar Penuh',
                                    'Wajib fullscreen selama quiz',
                                ],
                                [
                                    'block_new_tab',
                                    old('block_new_tab', 1),
                                    'Blokir Tab Baru',
                                    'Mencegah siswa buka tab/jendela baru',
                                ],
                                [
                                    'prevent_copy_paste',
                                    old('prevent_copy_paste', 1),
                                    'Cegah Copy-Paste',
                                    'Nonaktifkan fungsi copy, cut, paste',
                                ],
                            ];
                        @endphp
                        @foreach ($security as [$name, $checked, $label, $desc])
                            <div class="flex items-start gap-3 p-3.5 bg-red-50 border border-red-100 rounded-xl">
                                <input type="checkbox" name="{{ $name }}" value="1"
                                    id="{{ $name }}" {{ $checked ? 'checked' : '' }}
                                    class="w-4 h-4 mt-0.5 text-red-600 rounded border-slate-300 focus:ring-red-500 flex-shrink-0">
                                <label for="{{ $name }}" class="text-sm text-slate-700 cursor-pointer">
                                    <span class="font-medium">{{ $label }}</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">{{ $desc }}</span>
                                </label>
                            </div>
                        @endforeach

                        <div x-show="quizMode === 'homework'" x-transition class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-700">Batas Maksimal Pelanggaran</label>
                            <input type="number" name="violation_limit" min="1" max="20"
                                value="{{ old('violation_limit', 3) }}"
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                            <p class="text-xs text-slate-400">Jawaban otomatis dikumpulkan jika melebihi batas ini</p>
                        </div>

                        <div class="md:col-span-2 p-3.5 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-2 text-xs text-slate-600"
                            x-show="quizMode !== 'homework'" x-transition>
                            <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Pada mode <strong x-text="quizMode === 'live' ? 'Live Quiz' : 'Quiz Terpandu'"
                                class="mx-0.5"></strong>, pelanggaran tidak menyebabkan auto-submit. Kartu siswa berubah
                            merah di panel guru.
                        </div>
                    </div>
                </div>

                {{-- ===== HIDDEN VALUES UNTUK GUIDED MODE ===== --}}
                {{-- Saat guided, kirim semua fitur interaktif sebagai 0 agar tidak tersimpan --}}
                <template x-if="quizMode === 'guided'">
                    <div class="hidden">
                        <input type="hidden" name="show_leaderboard" value="0">
                        <input type="hidden" name="instant_feedback" value="0">
                        <input type="hidden" name="enable_music" value="0">
                        <input type="hidden" name="enable_memes" value="0">
                        <input type="hidden" name="enable_powerups" value="0">
                        <input type="hidden" name="streak_bonus" value="0">
                        <input type="hidden" name="time_bonus" value="0">
                    </div>
                </template>

                {{-- ===== SUBMIT ===== --}}
                <div class="p-6 flex items-center justify-between bg-slate-50 border-t border-slate-200">
                    <a href="{{ route('guru.quiz.index') }}"
                        class="flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold rounded-xl text-sm transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </a>
                    <button type="submit"
                        class="flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow-sm hover:shadow-md transition-all"
                        :disabled="isSubmitting">
                        <svg x-show="isSubmitting" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span x-text="isSubmitting ? 'Menyimpan...' : 'Lanjut Buat Soal'"></span>
                        <svg x-show="!isSubmitting" class="w-4 h-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function quizForm() {
            return {
                quizMode: '{{ old('quiz_mode', 'live') }}',
                selectedSubjectId: {{ old('subject_id') ?? 'null' }},
                loadingClasses: false,
                isSubmitting: false,
                title: '{{ old('title', '') }}',
                subjectId: '{{ old('subject_id', '') }}',
                enableRetake: {{ old('enable_retake') ? 'true' : 'false' }},
                timePerQuestionMode: '{{ old('time_per_question', 30) == 0 ? 'unlimited' : 'timed' }}',
                classesList: [],
                selectedClassIds: @json(old('class_ids', [])),
                classSearch: '',

                init() {
                    if (this.selectedSubjectId) {
                        this.getClassesBySubject(this.selectedSubjectId);
                    }
                    this.$watch('selectedClassIds', () => this.updateHiddenSelect());
                    this.updateHiddenSelect();
                },

                async getClassesBySubject(subjectId) {
                    if (!subjectId) {
                        this.resetClassSelect();
                        return;
                    }
                    this.selectedSubjectId = subjectId;
                    this.loadingClasses = true;
                    try {
                        const response = await fetch(`/guru/exams/get-classes-by-subject/${subjectId}`);
                        const data = await response.json();
                        if (data.success && data.classes) {
                            this.classesList = data.classes;
                            const validIds = data.classes.map(c => c.id);
                            this.selectedClassIds = this.selectedClassIds.filter(id => validIds.includes(parseInt(id)));
                        } else {
                            this.classesList = [];
                            this.selectedClassIds = [];
                        }
                    } catch (e) {
                        this.classesList = [];
                        this.selectedClassIds = [];
                    } finally {
                        this.loadingClasses = false;
                        this.updateHiddenSelect();
                    }
                },

                resetClassSelect() {
                    this.classesList = [];
                    this.selectedClassIds = [];
                    this.updateHiddenSelect();
                },

                toggleAllClasses(checked) {
                    this.selectedClassIds = checked ? this.classesList.map(c => c.id) : [];
                    this.updateHiddenSelect();
                },

                // Nama checkbox per section
                _sectionFields: {
                    soal:     ['shuffle_question','shuffle_answer','show_score','show_correct_answer','enable_retake'],
                    fitur:    ['show_leaderboard','instant_feedback','enable_music','enable_memes','enable_powerups','streak_bonus','time_bonus'],
                    keamanan: ['fullscreen_mode','block_new_tab','prevent_copy_paste'],
                },

                isSectionAllSelected(section) {
                    const fields = this._sectionFields[section] || [];
                    return fields.every(name => {
                        const el = document.querySelector(`input[name="${name}"][type="checkbox"]`);
                        return el && el.checked;
                    });
                },

                toggleSectionAll(section) {
                    const fields = this._sectionFields[section] || [];
                    const allChecked = this.isSectionAllSelected(section);
                    fields.forEach(name => {
                        const el = document.querySelector(`input[name="${name}"][type="checkbox"]`);
                        if (el) {
                            el.checked = !allChecked;
                            el.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    });
                },

                // Kelompokkan kelas berdasarkan prefix nama (X, XI, XII, dst)
                getClassGroups() {
                    const groups = {};
                    this.classesList.forEach(cls => {
                        // Ambil kata pertama sebagai kategori (misal: "X", "XI", "XII", "10", dst)
                        const prefix = cls.name.trim().split(/\s+/)[0] || 'Lainnya';
                        if (!groups[prefix]) groups[prefix] = [];
                        groups[prefix].push(cls);
                    });
                    // Urutkan key secara alami
                    return Object.keys(groups).sort((a, b) => a.localeCompare(b, undefined, { numeric: true }))
                        .map(label => ({ label, classes: groups[label] }));
                },

                filteredClassGroups() {
                    const q = this.classSearch.toLowerCase();
                    return this.getClassGroups()
                        .map(g => ({ ...g, classes: g.classes.filter(c => c.name.toLowerCase().includes(q)) }))
                        .filter(g => g.classes.length > 0);
                },

                toggleGroupClasses(group) {
                    const allIds = group.classes.map(c => c.id);
                    const allSelected = allIds.every(id => this.selectedClassIds.includes(id));
                    if (allSelected) {
                        this.selectedClassIds = this.selectedClassIds.filter(id => !allIds.includes(id));
                    } else {
                        const merged = [...new Set([...this.selectedClassIds, ...allIds])];
                        this.selectedClassIds = merged;
                    }
                    this.updateHiddenSelect();
                },

                isGroupAllSelected(group) {
                    return group.classes.length > 0 && group.classes.every(c => this.selectedClassIds.includes(c.id));
                },

                updateHiddenSelect() {
                    const el = document.getElementById('class_id');
                    if (!el) return;
                    el.innerHTML = '';
                    this.selectedClassIds.forEach(id => {
                        const opt = document.createElement('option');
                        opt.value = id;
                        opt.selected = true;
                        el.appendChild(opt);
                    });
                },

                async handleSubmit(e) {
                    const form = e.target;
                    if (this.selectedClassIds.length === 0) {
                        alert('Silakan pilih minimal satu kelas terlebih dahulu.');
                        return;
                    }
                    if (this.quizMode === 'homework') {
                        const startAt = form.querySelector('[name="start_at"]')?.value;
                        const endAt = form.querySelector('[name="end_at"]')?.value;
                        if (!startAt || !endAt) {
                            alert('Mode Quiz Mandiri memerlukan waktu mulai dan waktu berakhir.');
                            return;
                        }
                        if (new Date(endAt) <= new Date(startAt)) {
                            alert('Waktu berakhir harus lebih dari waktu mulai.');
                            return;
                        }
                    }

                    this.isSubmitting = true;
                    try {
                        const formData = new FormData(form);
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        const data = await response.json();

                        if (data.success) {
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            } else if (data.exam_id) {
                                window.location.href = '{{ route('guru.quiz.questions', ':id') }}'.replace(':id', data
                                    .exam_id);
                            } else {
                                window.location.href = '{{ route('guru.quiz.index') }}';
                            }
                        } else {
                            let msg = data.message || 'Gagal menyimpan pengaturan';
                            if (data.errors) msg += '\n' + Object.values(data.errors).flat().join('\n');
                            alert(msg);
                            this.isSubmitting = false;
                        }
                    } catch (err) {
                        alert('Terjadi kesalahan: ' + err.message);
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection
