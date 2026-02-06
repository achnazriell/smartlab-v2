@extends('layouts.appTeacher')

@section('content')
    <style>
        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            animation: fadeIn 0.2s ease-out;
        }

        .modal-overlay.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e0e7ff;
        }

        .modal-icon {
            width: 48px;
            height: 48px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e3a8a;
            margin: 0;
        }

        .modal-body {
            color: #475569;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .modal-footer {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .modal-btn-cancel {
            background: #e5e7eb;
            color: #374151;
        }

        .modal-btn-cancel:hover {
            background: #d1d5db;
        }

        .modal-btn-danger {
            background: #dc2626;
            color: white;
        }

        .modal-btn-danger:hover {
            background: #b91c1c;
        }
    </style>

    <div class="max-w-6xl mx-auto space-y-6">
        <!-- Header -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 font-poppins">{{ $exam->title }}</h1>
                    <div class="flex flex-wrap items-center gap-3 mt-2">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 text-sm font-medium rounded-full">
                            {{ $exam->type }}
                        </span>
                        <span class="text-slate-600">
                            {{ $exam->subject->name_subject ?? 'Tidak ada mapel' }}
                        </span>
                        <span class="text-slate-600">•</span>
                        <span class="text-slate-600">
                            {{ $exam->class->name_class ?? 'Tidak ada kelas' }}
                        </span>
                        <span class="text-slate-600">•</span>
                        <span
                            class="px-3 py-1 {{ $exam->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }} text-sm font-medium rounded-full">
                            {{ $exam->status === 'active' ? 'Aktif' : ($exam->status === 'draft' ? 'Draft' : ucfirst($exam->status)) }}
                        </span>
                    </div>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('guru.exams.edit', $exam->id) }}"
                        class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        Edit
                    </a>
                    <a href="{{ route('guru.exams.soal', $exam->id) }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Kelola Soal
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total Soal</p>
                        <p class="text-2xl font-bold text-slate-800">{{ $totalQuestions }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total Skor</p>
                        <p class="text-2xl font-bold text-slate-800">{{ $totalScore }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Durasi</p>
                        <p class="text-2xl font-bold text-slate-800">
                            @if ($exam->type === 'QUIZ')
                                {{ $exam->time_per_question }} detik/soal
                            @else
                                {{ $exam->duration }} menit
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exam Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Exam Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Waktu Ujian -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        Waktu Ujian
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-slate-500">Mulai</p>
                            <p class="font-medium text-slate-800">
                                {{ $exam->start_at ? $exam->start_at->translatedFormat('l, d F Y H:i') : 'Belum ditentukan' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Selesai</p>
                            <p class="font-medium text-slate-800">
                                {{ $exam->end_at ? $exam->end_at->translatedFormat('l, d F Y H:i') : 'Belum ditentukan' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pengaturan -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Pengaturan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-slate-500">Acak Soal</p>
                            <p class="font-medium text-slate-800">
                                {{ $exam->shuffle_question ? 'Ya' : 'Tidak' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Acak Jawaban</p>
                            <p class="font-medium text-slate-800">
                                {{ $exam->shuffle_answer ? 'Ya' : 'Tidak' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Tampilkan Skor</p>
                            <p class="font-medium text-slate-800">
                                {{ $exam->show_score ? 'Ya' : 'Tidak' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Maksimal Percobaan</p>
                            <p class="font-medium text-slate-800">
                                {{ $exam->limit_attempts ?? 1 }} kali
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Soal Preview -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center justify-between">
                        <span class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-slate-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Preview Soal ({{ $totalQuestions }} soal)
                        </span>
                        <a href="{{ route('guru.exams.soal', $exam->id) }}"
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Lihat semua →
                        </a>
                    </h3>

                    @if ($exam->questions->count() > 0)
                        <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                            @foreach ($exam->questions->take(5) as $index => $question)
                                <div class="border border-slate-200 rounded-lg p-4 hover:bg-slate-50 transition-colors">
                                    <div class="flex justify-between items-start mb-2">
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded
                                {{ $question->type === 'PG' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $question->type === 'PG' ? 'Pilihan Ganda' : 'Isian Singkat' }}
                                        </span>
                                        <span class="text-sm text-slate-600">Skor: {{ $question->score }}</span>
                                    </div>
                                    <p class="text-slate-800 mb-3">{{ $question->question }}</p>

                                    @if ($question->type === 'PG')
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach ($question->choices as $choice)
                                                <div
                                                    class="text-sm flex items-center {{ $choice->is_correct ? 'text-emerald-700 font-medium' : 'text-slate-600' }}">
                                                    <span class="mr-2">{{ $choice->label }}.</span>
                                                    <span>{{ $choice->text }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="text-sm">
                                            <p class="text-slate-600 mb-1">Jawaban diterima:</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($question->short_answers ?? [] as $answer)
                                                    <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs">
                                                        {{ $answer }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            @if ($exam->questions->count() > 5)
                                <div class="text-center py-3 text-slate-500 text-sm">
                                    + {{ $exam->questions->count() - 5 }} soal lainnya
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-8 border-2 border-dashed border-slate-200 rounded-lg">
                            <svg class="w-12 h-12 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <p class="text-slate-500 mb-4">Belum ada soal</p>
                            <a href="{{ route('guru.exams.soal', $exam->id) }}"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                Tambah Soal
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Actions & Info -->
            <div class="space-y-6">
                <!-- Status & Actions -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Status & Tindakan</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Status:</span>
                            <span
                                class="px-3 py-1 {{ $exam->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }} text-sm font-medium rounded-full">
                                {{ $exam->status === 'active' ? 'Aktif' : ($exam->status === 'draft' ? 'Draft' : ucfirst($exam->status)) }}
                            </span>
                        </div>

                        <!-- TAMBAHKAN INI: Informasi Finalisasi -->
                        @if ($exam->status === 'draft')
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-3">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-amber-600 mr-2 mt-0.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.995-.833-2.732 0L4.12 16.5c-.77.833.192 2.5 1.732 2.5z">
                                        </path>
                                    </svg>
                                    <div>
                                        <p class="text-amber-800 font-medium mb-1">Ujian masih dalam status Draft</p>
                                        <p class="text-amber-700 text-sm">Ujian ini belum tersedia untuk siswa.
                                            Publikasikan untuk membuatnya tersedia.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="pt-3 border-t border-slate-100">
                            <!-- TAMBAHKAN INI: Tombol Finalisasi/Publikasi -->
                            @if ($exam->status === 'draft')
                                <div class="mb-4">
                                    <form action="{{ route('guru.exams.finalize', $exam->id) }}" method="POST"
                                        id="finalizeForm">
                                        @csrf
                                        <button type="submit"
                                            class="w-full text-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors shadow-sm flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Publikasikan Ujian
                                        </button>
                                        <p class="text-xs text-slate-500 mt-2 text-center">
                                            Setelah dipublikasikan, ujian akan tersedia untuk siswa
                                        </p>
                                    </form>
                                </div>
                            @endif

                            <form action="{{ route('guru.exams.update-status', $exam->id) }}" method="POST"
                                class="mb-3">
                                @csrf
                                @method('PUT')
                                <select name="status"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm mb-3"
                                    onchange="this.form.submit()">
                                    <option value="draft" {{ $exam->status === 'draft' ? 'selected' : '' }}>Draft
                                    </option>
                                    <option value="active" {{ $exam->status === 'active' ? 'selected' : '' }}>Aktif
                                    </option>
                                    <option value="inactive" {{ $exam->status === 'inactive' ? 'selected' : '' }}>Nonaktif
                                    </option>
                                </select>
                            </form>

                            <div class="space-y-2">
                                @if ($exam->status === 'active')
                                    <a href="{{ route('guru.exams.results.index', $exam->id) }}"
                                        class="block w-full text-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors">
                                        Lihat Hasil
                                    </a>
                                @endif

                                <form action="{{ route('guru.exams.destroy', $exam->id) }}" method="POST"
                                    id="deleteExamForm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="openDeleteModal()"
                                        class="block w-full text-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                                        Hapus Ujian
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Aksi Cepat</h3>
                    <div class="space-y-3">
                        <a href="{{ route('guru.exams.soal', $exam->id) }}"
                            class="flex items-center p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                            <svg class="w-5 h-5 mr-3 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            <div>
                                <p class="font-medium text-slate-800">Kelola Soal</p>
                                <p class="text-xs text-slate-500">Tambah, edit, atau hapus soal</p>
                            </div>
                        </a>

                        <a href="{{ route('guru.exams.edit', $exam->id) }}"
                            class="flex items-center p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                            <svg class="w-5 h-5 mr-3 text-amber-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            <div>
                                <p class="font-medium text-slate-800">Edit Pengaturan</p>
                                <p class="text-xs text-slate-500">Ubah detail ujian</p>
                            </div>
                        </a>

                        @if ($exam->status === 'active')
                            <a href="{{ route('guru.exams.results.index', $exam->id) }}"
                                class="flex items-center p-3 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                                <svg class="w-5 h-5 mr-3 text-emerald-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                                <div>
                                    <p class="font-medium text-slate-800">Lihat Hasil</p>
                                    <p class="text-xs text-slate-500">Lihat nilai siswa</p>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Share Info -->
                <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Info Ujian</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-slate-500">ID Ujian</p>
                            <p class="font-mono text-slate-800">{{ $exam->id }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Dibuat</p>
                            <p class="text-slate-800">{{ $exam->created_at->translatedFormat('d F Y') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Terakhir Diupdate</p>
                            <p class="text-slate-800">{{ $exam->updated_at->translatedFormat('d F Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between pt-6 border-t border-slate-200">
            <a href="{{ route('guru.exams.index') }}"
                class="inline-flex items-center px-4 py-2 text-slate-600 font-medium hover:text-slate-800">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar
            </a>
            <div class="flex space-x-3">
                <button onclick="window.print()"
                    class="inline-flex items-center px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    Cetak
                </button>
                <a href="{{ route('guru.exams.soal', $exam->id) }}"
                    class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Kelola Soal
                </a>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">⚠️</div>
                <div>
                    <h2 class="modal-title">Hapus Ujian</h2>
                </div>
            </div>
            <div class="modal-body">
                <p><strong>Ujian:</strong> {{ $exam->title }}</p>
                <p class="mt-3" style="color: #dc2626; font-weight: 600;">Apakah Anda yakin ingin menghapus ujian ini?
                    Tindakan ini tidak dapat dibatalkan dan semua data terkait ujian akan dihapus.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeDeleteModal()">
                    Batal
                </button>
                <button type="button" class="modal-btn modal-btn-danger" onclick="submitDeleteForm()">
                    Ya, Hapus Ujian
                </button>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal() {
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        function submitDeleteForm() {
            document.getElementById('deleteExamForm').submit();
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
@endsection

@push('styles')
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
@endpush
