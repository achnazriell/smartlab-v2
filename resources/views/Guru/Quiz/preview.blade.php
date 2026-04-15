@extends('layouts.appTeacher')

@section('content')
    <style>
        .preview-card {
            transition: all 0.3s ease;
            border: 2px solid rgba(37, 99, 235, 0.1);
        }

        .preview-card:hover {
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.15);
            border-color: rgba(37, 99, 235, 0.3);
        }

        .badge-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }

        .stat-box {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 2px solid #93c5fd;
        }

        /* Feature Badges */
        .feature-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .feature-badge.active {
            background-color: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #93c5fd;
        }

        .feature-badge.inactive {
            background-color: #f3f4f6;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }

        /* Custom Modal Styles */
        .custom-modal-backdrop {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 9998;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease-out;
        }

        .custom-modal-backdrop.active {
            display: flex;
            opacity: 1;
        }

        .custom-modal {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
            margin: auto;
            transform: translateY(-20px);
            opacity: 0;
            transition: all 0.3s ease-out;
        }

        .custom-modal.active {
            transform: translateY(0);
            opacity: 1;
        }

        .custom-modal-header {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .custom-modal-body {
            padding: 1rem 1.5rem;
        }

        .custom-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        /* Custom Alert Styles */
        .custom-alert {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 9999;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            max-width: 400px;
        }

        .custom-alert.show {
            transform: translateX(0);
            opacity: 1;
        }

        .custom-alert.success {
            border-left: 4px solid #10b981;
        }

        .custom-alert.error {
            border-left: 4px solid #ef4444;
        }

        .custom-alert.warning {
            border-left: 4px solid #f59e0b;
        }

        .custom-alert.info {
            border-left: 4px solid #3b82f6;
        }

        .alert-icon {
            flex-shrink: 0;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert-icon.success {
            background-color: #d1fae5;
            color: #047857;
        }

        .alert-icon.error {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .alert-icon.warning {
            background-color: #fef3c7;
            color: #d97706;
        }

        .alert-icon.info {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-weight: 600;
            color: #111827;
            font-size: 0.875rem;
            margin-bottom: 0.125rem;
        }

        .alert-message {
            color: #6b7280;
            font-size: 0.75rem;
        }

        .alert-close {
            color: #9ca3af;
            cursor: pointer;
            padding: 0.25rem;
            transition: color 0.2s;
        }

        .alert-close:hover {
            color: #374151;
        }

        /* Room Status */
        .room-status {
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
        }

        .room-status-open {
            background-color: #d1fae5;
            border: 2px solid #10b981;
            color: #065f46;
        }

        .room-status-closed {
            background-color: #f3f4f6;
            border: 2px solid #9ca3af;
            color: #4b5563;
        }

        .room-status-active {
            background-color: #dbeafe;
            border: 2px solid #3b82f6;
            color: #1e40af;
        }

        /* Status Option Buttons */
        .status-option {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 0.625rem;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
            background-color: #f9fafb;
        }

        .status-option:hover {
            background-color: #f3f4f6;
            border-color: #d1d5db;
        }

        .status-option.selected {
            border-color: currentColor;
            background-color: var(--status-bg, #f0fdf4);
        }

        .status-option.opt-draft        { color: #6b7280; --status-bg: #f9fafb; }
        .status-option.opt-active       { color: #059669; --status-bg: #ecfdf5; }
        .status-option.opt-inactive     { color: #d97706; --status-bg: #fffbeb; }
        .status-option.opt-finished     { color: #dc2626; --status-bg: #fef2f2; }

        .status-option.opt-draft.selected    { border-color: #9ca3af; background-color: #f3f4f6; }
        .status-option.opt-active.selected   { border-color: #10b981; background-color: #d1fae5; }
        .status-option.opt-inactive.selected { border-color: #f59e0b; background-color: #fef3c7; }
        .status-option.opt-finished.selected { border-color: #ef4444; background-color: #fee2e2; }
    </style>

    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <a href="{{ route('guru.quiz.index') }}"
                    class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-2 transition-colors">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali
                </a>
                <h1 class="text-3xl font-bold text-gray-800">Preview Quiz</h1>
            </div>
        </div>

        @if (session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showAlert('success', 'Berhasil!', '{{ session('success') }}');
                });
            </script>
        @endif

        @if (session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showAlert('error', 'Error!', '{{ session('error') }}');
                });
            </script>
        @endif


        {{-- Room Status --}}
        @if ($quiz->is_room_open && $quiz->activeSession)
            @php
                $sessionStatus = 'open';
                if ($quiz->activeSession->status === 'active') {
                    $sessionStatus = 'active';
                }
            @endphp

            @if ($sessionStatus === 'active')
                <div class="room-status room-status-active">
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z" />
                        </svg>
                        <div class="flex-1">
                            <div class="font-bold text-lg">Quiz Sedang Berlangsung</div>
                            <div class="text-sm opacity-90">
                                {{ $stats['joined'] ?? 0 }} peserta bergabung •
                                {{ $stats['submitted'] ?? 0 }} selesai
                            </div>
                        </div>
                        <a href="{{ route('guru.quiz.room', $quiz->id) }}"
                            class="bg-white text-blue-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                            Monitor Room
                        </a>
                    </div>
                </div>
            @else
                <div class="room-status room-status-open">
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                        </svg>
                        <div class="flex-1">
                            <div class="font-bold text-lg">Ruangan Terbuka</div>
                            <div class="text-sm opacity-90">
                                Siswa dapat bergabung • {{ $stats['joined'] ?? 0 }} peserta menunggu
                            </div>
                        </div>
                        <a href="{{ route('guru.quiz.room', $quiz->id) }}"
                            class="bg-white text-green-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
                            Lihat Room
                        </a>
                    </div>
                </div>
            @endif
        @endif

        {{-- Main Content --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Quiz Info --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Quiz Header Card --}}
                <div class="preview-card bg-gradient-to-br from-blue-500 to-blue-700 rounded-2xl p-6 text-white">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold mb-2">{{ $quiz->title }}</h2>
                            <div class="flex items-center gap-4 text-sm">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    {{ $quiz->subject->name_subject ?? '-' }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    {{ $quiz->class->class_name ?? '-' }}
                                    @if ($quiz->class_ids && is_array($quiz->class_ids) && count($quiz->class_ids) > 1)
                                        + {{ count($quiz->class_ids) - 1 }} kelas
                                    @endif
                                </span>
                            </div>
                        </div>
                        <span class="badge-blue px-4 py-2 rounded-full text-xs font-bold uppercase tracking-wider">
                            {{ $modeLabel }}
                        </span>
                    </div>

                    {{-- Stats Grid --}}
                    <div class="grid grid-cols-3 gap-4">
                        <div class="stat-box text-center p-4 rounded-xl">
                            <div class="text-3xl font-bold text-blue-700">{{ $totalQuestions }}</div>
                            <div class="text-sm text-blue-600 font-medium">Soal</div>
                        </div>
                        <div class="stat-box text-center p-4 rounded-xl">
                            <div class="text-3xl font-bold text-blue-700">{{ $quiz->duration }}</div>
                            <div class="text-sm text-blue-600 font-medium">Menit</div>
                        </div>
                        <div class="stat-box text-center p-4 rounded-xl">
                            <div class="text-3xl font-bold text-blue-700">{{ $quiz->total_score }}</div>
                            <div class="text-sm text-blue-600 font-medium">Total Poin</div>
                        </div>
                    </div>
                </div>

                {{-- Features --}}
                <div class="preview-card bg-white p-6 rounded-2xl">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Fitur Aktif</h3>
                    <div class="flex flex-wrap gap-2">
                        <span class="feature-badge {{ $quiz->shuffle_question ? 'active' : 'inactive' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                            </svg>
                            Acak Soal
                        </span>
                        <span class="feature-badge {{ $quiz->shuffle_answer ? 'active' : 'inactive' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            Acak Jawaban
                        </span>
                        <span class="feature-badge {{ $quiz->show_score ? 'active' : 'inactive' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Tampilkan Hasil
                        </span>
                        <span class="feature-badge {{ $quiz->instant_feedback ? 'active' : 'inactive' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Feedback Langsung
                        </span>
                        @if ($quiz->quiz_mode === 'homework')
                            <span class="feature-badge {{ $quiz->violation_limit > 0 ? 'active' : 'inactive' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Batas Pelanggaran ({{ $quiz->violation_limit }})
                            </span>
                        @endif
                        @if ($quiz->time_per_question > 0)
                            <span class="feature-badge active">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $quiz->time_per_question }}s per soal
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Questions List --}}
                <div class="preview-card bg-white p-6 rounded-2xl">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Daftar Soal</h3>
                        @if ($quiz->status !== 'finished')
                        <a href="{{ route('guru.quiz.edit', $quiz->id) }}"
                            class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Soal
                        </a>
                        @else
                        <span class="text-xs text-slate-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Hanya baca
                        </span>
                        @endif
                    </div>

                    <div class="space-y-4">
                        @foreach ($quiz->questions as $index => $question)
                            <div class="border-2 border-gray-100 hover:border-blue-200 rounded-xl p-4 transition-all">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold">{{ $index + 1 }}</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span
                                                class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded">
                                                {{ $question->type }}
                                            </span>
                                            <span class="text-blue-600 text-sm font-semibold">{{ $question->score }}
                                                poin</span>
                                        </div>
                                        <p class="text-gray-800 font-medium mb-2">{{ $question->question }}</p>

                                        @if ($question->type === 'PG' && $question->choices->isNotEmpty())
                                            <div class="space-y-2 mt-3">
                                                @foreach ($question->choices as $choice)
                                                    <div
                                                        class="flex items-center gap-2 text-sm p-2 rounded-lg {{ $choice->is_correct ? 'bg-green-50 border border-green-200' : 'bg-gray-50' }}">
                                                        <span
                                                            class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $choice->is_correct ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-700' }}">
                                                            {{ $choice->label }}
                                                        </span>
                                                        <span
                                                            class="{{ $choice->is_correct ? 'text-green-800 font-semibold' : 'text-gray-600' }}">
                                                            {{ $choice->text }}
                                                        </span>
                                                        @if ($choice->is_correct)
                                                            <svg class="w-4 h-4 text-green-600 ml-auto" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif($question->type === 'IS')
                                            <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                                <p class="text-sm text-blue-700 font-semibold">Jawaban Isian Singkat</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions --}}
            <div class="space-y-6">
                {{-- Status Card --}}
                <div class="preview-card bg-white p-6 rounded-2xl">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Status Quiz</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 text-sm">Status</span>
                            @if ($quiz->status === 'draft')
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-full">DRAFT</span>
                            @elseif($quiz->status === 'active')
                                <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">AKTIF</span>
                            @elseif($quiz->status === 'inactive')
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-bold rounded-full">NONAKTIF</span>
                            @elseif($quiz->status === 'finished')
                                <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">SELESAI</span>
                            @else
                                <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-full">{{ strtoupper($quiz->status) }}</span>
                            @endif
                        </div>

                        {{-- Tombol ubah status — dikunci jika sudah selesai --}}
                        @if ($quiz->status === 'finished')
                            <div class="w-full mt-1 flex items-center justify-center gap-2 px-3 py-2 bg-slate-100 border border-slate-200 text-slate-400 rounded-lg text-xs font-semibold cursor-not-allowed select-none">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Status Terkunci (Selesai)
                            </div>
                        @else
                            <button onclick="showStatusModal()"
                                class="w-full mt-1 flex items-center justify-center gap-2 px-3 py-2 border border-slate-300 hover:border-blue-400 hover:bg-blue-50 text-slate-600 hover:text-blue-700 rounded-lg text-xs font-semibold transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Ubah Status
                            </button>
                        @endif

                        @if ($quiz->quiz_mode === 'homework')
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 text-sm">Mulai</span>
                                <span class="text-gray-800 font-semibold text-sm">
                                    {{ $quiz->start_at ? \Carbon\Carbon::parse($quiz->start_at)->format('d M Y H:i') : '-' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 text-sm">Selesai</span>
                                <span class="text-gray-800 font-semibold text-sm">
                                    {{ $quiz->end_at ? \Carbon\Carbon::parse($quiz->end_at)->format('d M Y H:i') : '-' }}
                                </span>
                            </div>
                        @endif

                        @if ($quiz->quiz_mode === 'live' || $quiz->quiz_mode === 'guided')
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 text-sm">Ruangan</span>
                                <span class="font-semibold text-sm">
                                    @if ($quiz->is_room_open)
                                        <span class="text-green-600">Terbuka</span>
                                    @else
                                        <span class="text-gray-600">Tertutup</span>
                                    @endif
                                </span>
                            </div>
                        @endif

                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 text-sm">Durasi</span>
                            <span class="text-gray-800 font-semibold text-sm">{{ $quiz->duration }} menit</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 text-sm">Total Soal</span>
                            <span class="text-gray-800 font-semibold text-sm">{{ $totalQuestions }}</span>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="preview-card bg-white p-6 rounded-2xl space-y-3">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Aksi</h3>

                    @if ($quiz->status === 'draft')
                        <button onclick="showPublishModal()"
                            class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-4 rounded-xl transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Publikasikan Quiz
                        </button>
                    @endif

                    {{-- Tombol Ruangan: tampil untuk mode live/guided HANYA saat status active (bukan finished) --}}
                    @if (in_array($quiz->quiz_mode, ['live', 'guided']) && in_array($quiz->status, ['published', 'active']))

                        @if (!$quiz->is_room_open)
                            {{-- Ruangan belum dibuka --}}
                            <button onclick="openRoom({{ $quiz->id }})"
                                class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-3 px-4 rounded-xl transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                                Buka Ruangan
                            </button>

                            {{-- Tombol langsung masuk room (jika sebelumnya sudah ada sesi) --}}
                            <a href="{{ route('guru.quiz.room', $quiz->id) }}"
                                class="w-full bg-white border-2 border-green-500 text-green-600 hover:bg-green-50 font-bold py-3 px-4 rounded-xl transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Masuk Room
                            </a>

                        @else
                            {{-- Ruangan sudah dibuka --}}

                            {{-- Tombol masuk room selalu tersedia --}}
                            <a href="{{ route('guru.quiz.room', $quiz->id) }}"
                                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-4 rounded-xl transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Masuk Room
                                @if (!$quiz->is_quiz_started)
                                    <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full">
                                        {{ $stats['joined'] ?? 0 }} menunggu
                                    </span>
                                @else
                                    <span class="text-xs bg-white/20 px-2 py-0.5 rounded-full">Sedang berlangsung</span>
                                @endif
                            </a>

                            @if (!$quiz->is_quiz_started)
                                {{-- Quiz belum dimulai: tampilkan tombol mulai --}}
                                @if ($quiz->quiz_mode === 'guided')
                                    <button onclick="startGuidedQuiz({{ $quiz->id }})"
                                        class="w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-bold py-3 px-4 rounded-xl transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Mulai Quiz Terpadu
                                    </button>
                                @else
                                    <button onclick="startLiveQuiz({{ $quiz->id }})"
                                        class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-3 px-4 rounded-xl transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Mulai Quiz
                                    </button>
                                @endif
                            @else
                                {{-- Quiz sedang berjalan: tampilkan tombol ke panel kontrol (guided) --}}
                                @if ($quiz->quiz_mode === 'guided')
                                    <a href="{{ route('guru.quiz.guided', $quiz->id) }}"
                                        class="w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-bold py-3 px-4 rounded-xl transition-all flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        Panel Kontrol Soal
                                    </a>
                                @endif
                            @endif

                            <button onclick="closeRoom({{ $quiz->id }})"
                                class="w-full bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-4 rounded-xl transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Tutup Ruangan
                            </button>
                        @endif

                    @endif

                    {{-- Lihat Hasil — selalu ditampilkan untuk quiz yang sudah ada data --}}
                    <a href="{{ route('guru.quiz.results', $quiz->id) }}"
                        class="w-full bg-violet-600 hover:bg-violet-700 text-white font-bold py-3 px-4 rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Lihat Hasil Quiz
                    </a>

                    @if ($quiz->status !== 'finished')
                    {{-- Edit — hanya jika belum selesai --}}
                    <a href="{{ route('guru.quiz.edit', $quiz->id) }}"
                        class="w-full bg-white border-2 border-blue-600 text-blue-600 hover:bg-blue-50 font-bold py-3 px-4 rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Quiz
                    </a>
                    @endif

                    <form action="{{ route('guru.quiz.destroy', $quiz->id) }}" method="POST"
                        onsubmit="return confirm('Hapus quiz ini? Tindakan tidak dapat dibatalkan!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full bg-white border-2 border-red-500 text-red-500 hover:bg-red-50 font-bold py-3 px-4 rounded-xl transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus Quiz
                        </button>
                    </form>
                </div>

                {{-- Info Card --}}
                <div class="preview-card bg-blue-50 border-2 border-blue-200 p-6 rounded-2xl">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-1" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Informasi</p>
                            <ul class="space-y-1 text-blue-700">
                                @if ($quiz->quiz_mode === 'homework')
                                    <li>• Siswa dapat mengerjakan dalam rentang waktu yang ditentukan</li>
                                @elseif($quiz->quiz_mode === 'live')
                                    <li>• Buka ruangan untuk memulai sesi live quiz</li>
                                    <li>• Pantau siswa secara real-time</li>
                                @else
                                    <li>• Soal akan ditampilkan di layar guru/proyektor</li>
                                    <li>• Siswa hanya menjawab di perangkat masing-masing</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Publish Modal --}}
    <div class="custom-modal-backdrop" id="publishModalBackdrop">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <h3 class="text-xl font-bold text-gray-800">Publikasikan Quiz</h3>
            </div>
            <div class="custom-modal-body">
                <p class="text-gray-600">
                    Setelah dipublikasikan, quiz akan tersedia untuk siswa.
                    @if ($quiz->quiz_mode === 'homework')
                        Quiz akan aktif sesuai jadwal yang telah ditentukan.
                    @else
                        Anda dapat membuka ruangan untuk memulai sesi.
                    @endif
                </p>
                <p class="text-gray-600 mt-2">
                    Yakin ingin melanjutkan?
                </p>
            </div>
            <div class="custom-modal-footer">
                <button onclick="hidePublishModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-medium transition-colors">
                    Batal
                </button>
                <button onclick="submitPublish()"
                    class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-medium transition-colors">
                    Ya, Publikasikan
                </button>
            </div>
        </div>
    </div>

    {{-- Status Change Modal --}}
    <div class="custom-modal-backdrop" id="statusModalBackdrop">
        <div class="custom-modal" style="max-width: 420px;">
            <div class="custom-modal-header">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Ubah Status Quiz</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Status saat ini: <strong id="currentStatusLabel">—</strong></p>
                    </div>
                </div>
            </div>
            <div class="custom-modal-body space-y-2" style="padding-top: 1.25rem; padding-bottom: 1.25rem;">
                <p class="text-xs text-gray-500 mb-3">Pilih status baru untuk quiz ini:</p>

                {{-- Draft --}}
                <button type="button" onclick="selectStatus('draft')"
                    id="statusOpt-draft"
                    class="status-option opt-draft w-full text-left {{ $quiz->status === 'draft' ? 'selected' : '' }}">
                    <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <span class="font-semibold text-sm">Draft</span>
                        <p class="text-xs opacity-75 mt-0.5">Quiz belum dipublikasikan, tidak terlihat siswa</p>
                    </div>
                    <svg id="check-draft" class="w-5 h-5 flex-shrink-0 {{ $quiz->status === 'draft' ? '' : 'hidden' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </button>

                {{-- Aktif --}}
                <button type="button" onclick="selectStatus('active')"
                    id="statusOpt-active"
                    class="status-option opt-active w-full text-left {{ $quiz->status === 'active' ? 'selected' : '' }}">
                    <div class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <span class="font-semibold text-sm">Aktif</span>
                        <p class="text-xs opacity-75 mt-0.5">Quiz dipublikasikan, siswa dapat mengakses</p>
                    </div>
                    <svg id="check-active" class="w-5 h-5 flex-shrink-0 {{ $quiz->status === 'active' ? '' : 'hidden' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </button>

                {{-- Nonaktif --}}
                <button type="button" onclick="selectStatus('inactive')"
                    id="statusOpt-inactive"
                    class="status-option opt-inactive w-full text-left {{ $quiz->status === 'inactive' ? 'selected' : '' }}">
                    <div class="w-8 h-8 rounded-lg bg-yellow-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <span class="font-semibold text-sm">Nonaktif</span>
                        <p class="text-xs opacity-75 mt-0.5">Quiz dihentikan sementara, tidak bisa diakses</p>
                    </div>
                    <svg id="check-inactive" class="w-5 h-5 flex-shrink-0 {{ $quiz->status === 'inactive' ? '' : 'hidden' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </button>

                {{-- Selesai — hanya informasi, tidak bisa dipilih manual --}}
                <div class="flex items-center gap-3 px-3 py-3 rounded-xl bg-slate-50 border-2 border-dashed border-slate-200 text-slate-400 cursor-not-allowed select-none opacity-60">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <span class="font-semibold text-sm">Selesai</span>
                        <p class="text-xs mt-0.5">Status ini ditetapkan otomatis — tidak dapat dipilih manual</p>
                    </div>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button onclick="hideStatusModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-medium transition-colors text-sm">
                    Batal
                </button>
                <button onclick="submitStatusChange()"
                    id="btnSimpanStatus"
                    disabled
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-lg font-medium transition-colors text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Status
                </button>
            </div>
        </div>
    </div>

    <script>
        // ===== STATUS CHANGE (FIXED) =====
        const CURRENT_STATUS  = '{{ $quiz->status }}';
        const STATUS_CHANGE_URL = '{{ route("guru.quiz.update", $quiz->id) }}';
        const STATUS_LABELS   = { draft: 'Draft', active: 'Aktif', inactive: 'Nonaktif', finished: 'Selesai' };
        let selectedStatus    = null;

        function showStatusModal() {
            selectedStatus = null;
            // Reset pilihan ke status saat ini
            ['draft', 'active', 'inactive', 'finished'].forEach(s => {
                const opt = document.getElementById('statusOpt-' + s);
                const chk = document.getElementById('check-' + s);
                if (opt && chk) {
                    opt.classList.toggle('selected', s === CURRENT_STATUS);
                    chk.classList.toggle('hidden', s !== CURRENT_STATUS);
                }
            });
            document.getElementById('currentStatusLabel').textContent = STATUS_LABELS[CURRENT_STATUS] || CURRENT_STATUS;
            document.getElementById('btnSimpanStatus').disabled = true;

            const backdrop = document.getElementById('statusModalBackdrop');
            backdrop.classList.add('active');
            setTimeout(() => {
                const modal = backdrop.querySelector('.custom-modal');
                if (modal) modal.classList.add('active');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function hideStatusModal() {
            const backdrop = document.getElementById('statusModalBackdrop');
            const modal = backdrop.querySelector('.custom-modal');
            if (modal) modal.classList.remove('active');
            setTimeout(() => {
                backdrop.classList.remove('active');
                document.body.style.overflow = '';
            }, 300);
        }

        function selectStatus(status) {
            selectedStatus = status;
            ['draft', 'active', 'inactive', 'finished'].forEach(s => {
                const opt = document.getElementById('statusOpt-' + s);
                const chk = document.getElementById('check-' + s);
                if (opt && chk) {
                    opt.classList.toggle('selected', s === status);
                    chk.classList.toggle('hidden', s !== status);
                }
            });
            const btnSimpan = document.getElementById('btnSimpanStatus');
            if (btnSimpan) {
                btnSimpan.disabled = (status === CURRENT_STATUS);
            }
        }

        async function submitStatusChange() {
            if (!selectedStatus || selectedStatus === CURRENT_STATUS) {
                showAlert('warning', 'Peringatan', 'Tidak ada perubahan status yang dipilih');
                return;
            }

            hideStatusModal();
            showAlert('info', 'Menyimpan...', 'Sedang mengubah status quiz...', 2000);

            try {
                const classIds = @json($assignedClassIds ?? []);

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PUT');
                formData.append('status', selectedStatus);

                // Kirim field wajib yang ada di validator controller
                formData.append('title', '{{ addslashes($quiz->title) }}');
                formData.append('subject_id', '{{ $quiz->subject_id }}');
                formData.append('quiz_mode', '{{ $quiz->quiz_mode }}');
                formData.append('duration', '{{ $quiz->duration }}');

                // Kirim class_ids (wajib di validator)
                if (classIds && classIds.length > 0) {
                    classIds.forEach(cid => {
                        formData.append('class_ids[]', cid);
                    });
                } else {
                    formData.append('class_ids[]', '{{ $quiz->class_id }}');
                }

                // Kirim field opsional
                formData.append('shuffle_question', '{{ $quiz->shuffle_question ? 1 : 0 }}');
                formData.append('shuffle_answer', '{{ $quiz->shuffle_answer ? 1 : 0 }}');
                formData.append('show_score', '{{ $quiz->show_score ? 1 : 0 }}');
                formData.append('show_correct_answer', '{{ $quiz->show_correct_answer ? 1 : 0 }}');
                formData.append('show_result_after', '{{ $quiz->show_result_after ?? "immediately" }}');
                formData.append('limit_attempts', '{{ $quiz->limit_attempts ?? 1 }}');
                formData.append('min_pass_grade', '{{ $quiz->min_pass_grade ?? 0 }}');
                formData.append('enable_retake', '{{ $quiz->enable_retake ? 1 : 0 }}');
                formData.append('show_leaderboard', '{{ $quiz->show_leaderboard ? 1 : 0 }}');
                formData.append('enable_music', '{{ $quiz->enable_music ? 1 : 0 }}');
                formData.append('enable_memes', '{{ $quiz->enable_memes ? 1 : 0 }}');
                formData.append('enable_powerups', '{{ $quiz->enable_powerups ? 1 : 0 }}');
                formData.append('instant_feedback', '{{ $quiz->instant_feedback ? 1 : 0 }}');
                formData.append('streak_bonus', '{{ $quiz->streak_bonus ? 1 : 0 }}');
                formData.append('time_bonus', '{{ $quiz->time_bonus ? 1 : 0 }}');
                formData.append('fullscreen_mode', '{{ $quiz->fullscreen_mode ? 1 : 0 }}');
                formData.append('block_new_tab', '{{ $quiz->block_new_tab ? 1 : 0 }}');
                formData.append('prevent_copy_paste', '{{ $quiz->prevent_copy_paste ? 1 : 0 }}');

                @if($quiz->quiz_mode === 'homework')
                formData.append('start_at', '{{ $quiz->start_at }}');
                formData.append('end_at', '{{ $quiz->end_at }}');
                formData.append('violation_limit', '{{ $quiz->violation_limit ?? 3 }}');
                @endif

                formData.append('time_per_question', '{{ $quiz->time_per_question ?? 0 }}');

                const response = await fetch(STATUS_CHANGE_URL, {
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
                    showAlert('success', 'Status diperbarui!',
                        'Status quiz berhasil diubah ke ' + (STATUS_LABELS[selectedStatus] || selectedStatus), 4000);
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    let errorMsg = data.message || 'Gagal mengubah status';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join(', ');
                    }
                    showAlert('error', 'Gagal!', errorMsg);
                }
            } catch (err) {
                console.error('Error:', err);
                showAlert('error', 'Error!', 'Terjadi kesalahan: ' + err.message);
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const statusBackdrop = document.getElementById('statusModalBackdrop');
                const publishBackdrop = document.getElementById('publishModalBackdrop');
                if (statusBackdrop && statusBackdrop.classList.contains('active')) {
                    hideStatusModal();
                }
                if (publishBackdrop && publishBackdrop.classList.contains('active')) {
                    hidePublishModal();
                }
            }
        });

        // Backdrop click untuk status modal
        const statusBackdrop = document.getElementById('statusModalBackdrop');
        if (statusBackdrop) {
            statusBackdrop.addEventListener('click', function(e) {
                if (e.target === this) hideStatusModal();
            });
        }

        // ===== PUBLISH MODAL =====
        function showPublishModal() {
            const backdrop = document.getElementById('publishModalBackdrop');
            const modal = backdrop.querySelector('.custom-modal');

            backdrop.classList.add('active');
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
            document.body.style.overflow = 'hidden';
        }

        function hidePublishModal() {
            const backdrop = document.getElementById('publishModalBackdrop');
            const modal = backdrop.querySelector('.custom-modal');

            modal.classList.remove('active');
            setTimeout(() => {
                backdrop.classList.remove('active');
                document.body.style.overflow = '';
            }, 300);
        }

        function submitPublish() {
            hidePublishModal();
            showAlert('info', 'Memproses...', 'Sedang mempublikasikan quiz...', 2000);

            fetch('{{ route('guru.quiz.finalize', $quiz->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ action: 'publish' })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', '✅ Berhasil Dipublikasikan!', data.message || 'Quiz berhasil dipublikasikan dan sudah aktif!', 4000);
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    showAlert('error', 'Gagal!', data.message || 'Gagal mempublikasikan quiz.');
                }
            })
            .catch(() => showAlert('error', 'Error!', 'Terjadi kesalahan saat mempublikasikan quiz.'));
        }

        // Backdrop click untuk publish modal
        const publishBackdrop = document.getElementById('publishModalBackdrop');
        if (publishBackdrop) {
            publishBackdrop.addEventListener('click', function(e) {
                if (e.target === this) {
                    hidePublishModal();
                }
            });
        }

        // Route URLs (menggunakan Laravel named routes)
        const OPEN_ROOM_URL  = '{{ route('guru.quiz.room.open',  $quiz->id) }}';
        const CLOSE_ROOM_URL = '{{ route('guru.quiz.room.close', $quiz->id) }}';
        const START_QUIZ_URL = '{{ route('guru.quiz.room.start', $quiz->id) }}';
        const ROOM_URL       = '{{ route('guru.quiz.room',       $quiz->id) }}';
        @if ($quiz->quiz_mode === 'guided')
        const GUIDED_URL     = '{{ route('guru.quiz.guided',     $quiz->id) }}';
        @endif
        const CSRF = '{{ csrf_token() }}';

        // Room Functions
        function openRoom(quizId) {
            if (!confirm('Buka ruangan quiz? Siswa akan dapat masuk dan menunggu.')) return;
            showAlert('info', 'Membuka ruangan...', 'Sedang membuka ruangan quiz...', 2000);

            fetch(OPEN_ROOM_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Berhasil!', data.message || 'Ruangan berhasil dibuka!', 3000);
                    setTimeout(() => {
                        window.location.href = data.redirect || ROOM_URL;
                    }, 1200);
                } else {
                    showAlert('error', 'Gagal!', data.message || 'Gagal membuka ruangan');
                }
            })
            .catch(() => showAlert('error', 'Error!', 'Terjadi kesalahan saat membuka ruangan'));
        }

        function closeRoom(quizId) {
            if (!confirm('Tutup ruangan quiz? Semua siswa akan dikeluarkan dari ruangan.')) return;
            showAlert('info', 'Menutup ruangan...', 'Sedang menutup ruangan quiz...', 2000);

            fetch(CLOSE_ROOM_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Berhasil!', data.message || 'Ruangan ditutup.', 3000);
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    showAlert('error', 'Gagal!', data.message || 'Gagal menutup ruangan');
                }
            })
            .catch(() => showAlert('error', 'Error!', 'Terjadi kesalahan saat menutup ruangan'));
        }

        // Guided: mulai → redirect ke panel kontrol soal
        function startGuidedQuiz(quizId) {
            if (!confirm('Mulai Quiz Terpadu sekarang?\n\nAnda akan langsung masuk ke panel kontrol soal dan siswa akan mendapat countdown 3-2-1 GO!')) return;
            showAlert('info', 'Memulai Quiz Terpadu...', 'Mohon tunggu...', 3000);

            fetch(START_QUIZ_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Quiz dimulai!', 'Membuka panel kontrol soal...', 2000);
                    setTimeout(() => { window.location.href = GUIDED_URL; }, 1000);
                } else {
                    showAlert('error', 'Gagal!', data.message || 'Gagal memulai quiz');
                }
            })
            .catch(() => showAlert('error', 'Error!', 'Terjadi kesalahan saat memulai quiz'));
        }

        // Live: mulai → redirect ke room monitoring
        function startLiveQuiz(quizId) {
            if (!confirm('Mulai quiz sekarang?\n\nSemua siswa yang sudah siap akan langsung mendapat countdown 3-2-1 GO!')) return;
            showAlert('info', 'Memulai quiz...', 'Sedang memulai quiz untuk semua peserta...', 2000);

            fetch(START_QUIZ_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', 'Quiz dimulai!', 'Menuju halaman monitoring...', 2000);
                    setTimeout(() => { window.location.href = ROOM_URL; }, 1000);
                } else {
                    showAlert('error', 'Gagal!', data.message || 'Gagal memulai quiz');
                }
            })
            .catch(() => showAlert('error', 'Error!', 'Terjadi kesalahan saat memulai quiz'));
        }

        // Custom Alert Function
        function showAlert(type, title, message, duration = 5000) {
            const alertTypes = {
                success: {
                    icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>',
                    color: 'success'
                },
                error: {
                    icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/></svg>',
                    color: 'error'
                },
                warning: {
                    icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/></svg>',
                    color: 'warning'
                },
                info: {
                    icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/></svg>',
                    color: 'info'
                }
            };

            const alertType = alertTypes[type] || alertTypes.info;

            const alertDiv = document.createElement('div');
            alertDiv.className = `custom-alert ${alertType.color}`;
            alertDiv.innerHTML = `
                <div class="alert-icon ${alertType.color}">
                    ${alertType.icon}
                </div>
                <div class="alert-content">
                    <div class="alert-title">${title}</div>
                    <div class="alert-message">${message}</div>
                </div>
                <div class="alert-close" onclick="this.parentElement.remove()">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                    </svg>
                </div>
            `;

            document.body.appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.classList.add('show');
            }, 10);

            setTimeout(() => {
                if (alertDiv.parentElement) {
                    alertDiv.classList.remove('show');
                    setTimeout(() => {
                        if (alertDiv.parentElement) {
                            alertDiv.remove();
                        }
                    }, 400);
                }
            }, duration);
        }
    </script>
@endsection
