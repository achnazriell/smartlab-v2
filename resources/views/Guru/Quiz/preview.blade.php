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
    </style>

    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-blue-900 mb-2">Preview Quiz: {{ $quiz->title }}</h1>
            <p class="text-gray-600">
                {{ $quiz->subject->name_subject }} • Kelas {{ $quiz->class->name_class }}
            </p>
            <div class="flex justify-center space-x-4 mt-4">
                <span class="badge-blue px-3 py-1 rounded-full text-sm font-medium">
                    {{ $quiz->difficulty_level == 'easy' ? 'Mudah' : ($quiz->difficulty_level == 'medium' ? 'Sedang' : 'Sulit') }}
                </span>
                <span class="bg-blue-50 text-blue-800 border-2 border-blue-200 px-3 py-1 rounded-full text-sm font-medium">
                    {{ $quiz->quiz_mode == 'live' ? 'Live Mode' : 'Homework Mode' }}
                </span>
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                    {{ $questions->count() }} Soal
                </span>
            </div>
        </div>

        <!-- Room Status & Actions -->
        <div class="preview-card bg-white rounded-xl shadow-sm border p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-blue-900 mb-2">Status Ruangan Quiz</h2>

                    <!-- Room Status Display -->
                    <div
                        class="room-status
                    @if ($quiz->is_quiz_started) room-status-active
                    @elseif($quiz->is_room_open) room-status-open
                    @else room-status-closed @endif">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                @if ($quiz->is_quiz_started)
                                    <span class="w-3 h-3 rounded-full bg-yellow-500 animate-pulse"></span>
                                    <span class="text-sm font-medium">Quiz Sedang Berlangsung</span>
                                @elseif($quiz->is_room_open)
                                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                                    <span class="text-sm font-medium">Ruangan Terbuka</span>
                                @else
                                    <span class="w-3 h-3 rounded-full bg-gray-400"></span>
                                    <span class="text-sm font-medium">Ruangan Tertutup</span>
                                @endif
                            </div>
                            <div class="text-sm">
                                @if ($quiz->is_quiz_started)
                                    Dimulai: {{ $quiz->quiz_started_at ? $quiz->quiz_started_at->format('H:i') : '-' }}
                                @elseif($quiz->is_room_open)
                                    Dibuka: {{ $quiz->room_opened_at ? $quiz->room_opened_at->format('H:i') : '-' }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Room Actions -->
                <!-- Perbaiki bagian Room Actions di preview.blade.php -->
                <div class="flex flex-wrap gap-2">
                    @if ($quiz->status === 'active')
                        @if (!$quiz->is_room_open)
                            <button onclick="openRoom('{{ $quiz->id }}')"
                                class="inline-flex items-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                <span>Buka Ruangan</span>
                            </button>
                        @elseif(!$quiz->is_quiz_started)
                            <a href="{{ route('guru.quiz.room', $quiz->id) }}"
                                class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                                <span>Masuk Ruangan</span>
                            </a>
                        @else
                            <a href="{{ route('guru.quiz.room', $quiz->id) }}"
                                class="inline-flex items-center px-5 py-2.5 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                                <span>Pantau Quiz</span>
                            </a>
                        @endif
                    @endif

                    @if ($quiz->is_room_open)
                        <button onclick="closeRoom('{{ $quiz->id }}')"
                            class="inline-flex items-center px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span>Tutup Ruangan</span>
                        </button>
                    @endif

                    @if ($quiz->is_room_open && !$quiz->is_quiz_started)
                        <button onclick="startQuiz('{{ $quiz->id }}')"
                            class="inline-flex items-center px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Mulai Quiz</span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Pengaturan Aktif -->
            <div class="mt-4">
                <h3 class="text-md font-semibold text-gray-700 mb-3">Pengaturan Aktif:</h3>
                <div class="flex flex-wrap gap-2">
                    @if ($quiz->show_leaderboard)
                        <span class="feature-badge active">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z"
                                    clip-rule="evenodd" />
                            </svg>
                            Leaderboard
                        </span>
                    @endif

                    @if ($quiz->instant_feedback)
                        <span class="feature-badge active">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            Feedback Instan
                        </span>
                    @endif

                    @if ($quiz->enable_music)
                        <span class="feature-badge active">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a3 3 0 000 6h.28l1.771 5.316A1 1 0 008 18h1a1 1 0 001-1v-4.382l6.553 3.276A1 1 0 0018 15V3z"
                                    clip-rule="evenodd" />
                            </svg>
                            Background Music
                        </span>
                    @endif

                    @if ($quiz->enable_memes)
                        <span class="feature-badge active">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zm-.464 5.535a1 1 0 10-1.415-1.414 3 3 0 01-4.242 0 1 1 0 00-1.415 1.414 5 5 0 007.072 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Memes
                        </span>
                    @endif

                    @if ($quiz->enable_powerups)
                        <span class="feature-badge active">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                                    clip-rule="evenodd" />
                            </svg>
                            Power-ups
                        </span>
                    @endif

                    @if ($quiz->streak_bonus)
                        <span class="feature-badge active">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                                    clip-rule="evenodd" />
                            </svg>
                            Bonus Streak
                        </span>
                    @endif

                    @if ($quiz->time_bonus)
                        <span class="feature-badge active">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                    clip-rule="evenodd" />
                            </svg>
                            Bonus Waktu
                        </span>
                    @endif

                    @if ($quiz->enable_retake)
                        <span class="feature-badge active">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                                    clip-rule="evenodd" />
                            </svg>
                            Izinkan Ulang
                        </span>
                    @endif

                    @if ($quiz->shuffle_question)
                        <span class="feature-badge active">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M5 4a1 1 0 00-2 0v7.268a2 2 0 000 3.464V16a1 1 0 102 0v-1.268a2 2 0 000-3.464V4zM11 4a1 1 0 10-2 0v1.268a2 2 0 000 3.464V16a1 1 0 102 0V8.732a2 2 0 000-3.464V4zM16 3a1 1 0 011 1v7.268a2 2 0 010 3.464V16a1 1 0 11-2 0v-1.268a2 2 0 010-3.464V4a1 1 0 011-1z" />
                            </svg>
                            Acak Soal
                        </span>
                    @endif

                    @if ($quiz->shuffle_answer)
                        <span class="feature-badge active">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Acak Jawaban
                        </span>
                    @endif

                    @if ($quiz->fullscreen_mode)
                        <span class="feature-badge active">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 11-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15 13.586V12a1 1 0 011-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            Fullscreen
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quiz Info -->
        <div class="preview-card bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="text-lg font-semibold text-blue-900 mb-4">Informasi Quiz</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 stat-box rounded-lg">
                    <div class="text-2xl font-bold text-blue-700">{{ $questions->count() }}</div>
                    <div class="text-sm text-gray-700">Total Soal</div>
                </div>
                <div class="text-center p-4 stat-box rounded-lg">
                    <div class="text-2xl font-bold text-blue-700">{{ $quiz->duration }}</div>
                    <div class="text-sm text-gray-700">Menit</div>
                </div>
                <div class="text-center p-4 stat-box rounded-lg">
                    <div class="text-2xl font-bold text-blue-700">{{ $quiz->time_per_question ?? 'N/A' }}</div>
                    <div class="text-sm text-gray-700">Detik/Soal</div>
                </div>
                <div class="text-center p-4 stat-box rounded-lg">
                    <div class="text-2xl font-bold text-blue-700">{{ $questions->sum('score') }}</div>
                    <div class="text-sm text-gray-700">Total Poin</div>
                </div>
            </div>
        </div>

        <!-- Questions Preview -->
        <div class="space-y-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-blue-900">Preview Soal</h2>
                <span class="text-sm text-gray-600">{{ $questions->count() }} soal</span>
            </div>

            @foreach ($questions as $index => $question)
                <div class="preview-card bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="badge-blue text-sm font-medium px-3 py-1 rounded-full">
                                    Soal {{ $index + 1 }}
                                </span>
                                <span
                                    class="{{ $question->type === 'PG' ? 'bg-blue-50 text-blue-800 border-2 border-blue-200' : 'bg-blue-100 text-blue-800' }} text-sm font-medium px-3 py-1 rounded-full">
                                    {{ $question->type === 'PG' ? 'Pilihan Ganda' : 'Isian Singkat' }}
                                </span>
                                <span
                                    class="bg-yellow-50 text-yellow-800 border-2 border-yellow-200 text-sm font-medium px-3 py-1 rounded-full">
                                    {{ $question->score }} Poin
                                </span>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $question->question }}</h3>
                        </div>
                    </div>

                    <!-- Multiple Choice Answers -->
                    @if ($question->type === 'PG')
                        <div class="space-y-3 mt-4">
                            @foreach ($question->choices as $choice)
                                <div
                                    class="flex items-center space-x-3 p-3 {{ $choice->is_correct ? 'bg-blue-50 border-blue-300' : 'bg-gray-50 border-gray-200' }} border-2 rounded-lg">
                                    <div
                                        class="{{ $choice->is_correct ? 'badge-blue' : 'bg-gray-100 text-gray-800' }} w-8 h-8 flex items-center justify-center rounded-full font-bold">
                                        {{ chr(65 + $loop->index) }}
                                    </div>
                                    <span
                                        class="{{ $choice->is_correct ? 'text-blue-800 font-medium' : 'text-gray-700' }} flex-1">
                                        {{ $choice->text }}
                                    </span>
                                    @if ($choice->is_correct)
                                        <span class="text-blue-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Short Answers -->
                    @if ($question->type === 'IS')
                        <div class="mt-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Jawaban yang Diterima:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach (json_decode($question->short_answers) as $answer)
                                    <span
                                        class="bg-blue-100 text-blue-800 border-2 border-blue-200 text-sm px-3 py-1.5 rounded-full">
                                        {{ $answer }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Explanation -->
                    @if ($question->explanation)
                        <div class="mt-4 pt-4 border-t border-blue-100">
                            <p class="text-sm font-medium text-blue-900 mb-1">Penjelasan:</p>
                            <p class="text-gray-600">{{ $question->explanation }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Actions Footer -->
        <div class="mt-8 preview-card bg-white rounded-xl shadow-sm p-6">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-gray-600">Status Quiz:
                        <span
                            class="font-medium {{ $quiz->status === 'draft' ? 'text-yellow-600' : ($quiz->status === 'active' ? 'text-green-600' : 'text-red-600') }}">
                            {{ $quiz->status === 'draft' ? 'Draft' : ($quiz->status === 'active' ? 'Active' : 'Inactive') }}
                        </span>
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        Dibuat: {{ $quiz->created_at->format('d M Y') }}
                        @if ($quiz->published_at)
                            • Dipublikasikan: {{ $quiz->published_at->format('d M Y') }}
                        @endif
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('guru.quiz.edit', $quiz->id) }}"
                        class="px-4 py-2 border-2 border-blue-600 text-blue-600 hover:bg-blue-50 rounded-xl transition-all hover:shadow-md">
                        Edit Pengaturan
                    </a>
                    <a href="{{ route('guru.quiz.questions', $quiz->id) }}"
                        class="px-4 py-2 border-2 border-purple-600 text-purple-600 hover:bg-purple-50 rounded-xl transition-all hover:shadow-md">
                        Edit Soal
                    </a>
                    @if ($quiz->status === 'draft')
                        <button onclick="showPublishModal()"
                            class="px-4 py-2 badge-blue hover:opacity-90 rounded-xl transition-all shadow-md hover:shadow-lg hover:-translate-y-0.5">
                            Publikasikan Quiz
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Publish Modal -->
    <div class="custom-modal-backdrop" id="publishModalBackdrop">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div
                            class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Publikasikan Quiz</h2>
                        <p class="text-sm text-gray-600 mt-1">Quiz akan dapat diakses oleh siswa setelah dipublikasikan.
                        </p>
                    </div>
                </div>
            </div>
            <div class="custom-modal-body">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-blue-700">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <p class="text-sm">Pastikan semua soal sudah benar sebelum dipublikasikan.</p>
                    </div>
                    <div class="mt-3 space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Total Soal:</span>
                            <span class="font-medium">{{ $questions->count() }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Total Poin:</span>
                            <span class="font-medium">{{ $questions->sum('score') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Durasi:</span>
                            <span class="font-medium">{{ $quiz->duration }} menit</span>
                        </div>
                    </div>
                </div>
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

    <script>
        // Modal Functions
        function showPublishModal() {
            const backdrop = document.getElementById('publishModalBackdrop');
            const modal = backdrop.querySelector('.custom-modal');

            backdrop.classList.add('active');
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);

            // Prevent body scroll
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

            // Show loading alert
            showAlert('info', 'Memproses...', 'Sedang mempublikasikan quiz...', 2000);

            setTimeout(() => {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('guru.quiz.finalize', $quiz->id) }}';
                form.innerHTML = `
            @csrf
            <input type="hidden" name="action" value="publish">
        `;
                document.body.appendChild(form);
                form.submit();
            }, 500);
        }

        // Room Functions
        function openRoom(quizId) {
            if (confirm('Buka ruangan quiz? Siswa akan dapat masuk dan menunggu.')) {
                showAlert('info', 'Membuka ruangan...', 'Sedang membuka ruangan quiz...', 2000);

                fetch(`/guru/quiz/${quizId}/open-room`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', 'Berhasil!', data.message, 3000);
                            setTimeout(() => {
                                window.location.href = data.redirect || window.location.href;
                            }, 1500);
                        } else {
                            showAlert('error', 'Gagal!', data.message || 'Gagal membuka ruangan');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('error', 'Error!', 'Terjadi kesalahan saat membuka ruangan');
                    });
            }
        }

        function closeRoom(quizId) {
            if (confirm('Tutup ruangan quiz? Semua siswa akan dikeluarkan dari ruangan.')) {
                showAlert('info', 'Menutup ruangan...', 'Sedang menutup ruangan quiz...', 2000);

                fetch(`/guru/quiz/${quizId}/close-room`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', 'Berhasil!', data.message, 3000);
                            setTimeout(() => {
                                window.location.href = window.location.href;
                            }, 1500);
                        } else {
                            showAlert('error', 'Gagal!', data.message || 'Gagal menutup ruangan');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('error', 'Error!', 'Terjadi kesalahan saat menutup ruangan');
                    });
            }
        }

        function startQuiz(quizId) {
            if (confirm('Mulai quiz sekarang? Semua siswa yang sudah bergabung akan memulai quiz.')) {
                showAlert('info', 'Memulai quiz...', 'Sedang memulai quiz untuk semua peserta...', 2000);

                fetch(`/guru/quiz/${quizId}/start-quiz`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('success', 'Berhasil!', data.message, 3000);
                            setTimeout(() => {
                                window.location.href = '{{ route('guru.quiz.room', $quiz->id) }}';
                            }, 1500);
                        } else {
                            showAlert('error', 'Gagal!', data.message || 'Gagal memulai quiz');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showAlert('error', 'Error!', 'Terjadi kesalahan saat memulai quiz');
                    });
            }
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

            // Animate in
            setTimeout(() => {
                alertDiv.classList.add('show');
            }, 10);

            // Auto remove after duration
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

        // Close modal on backdrop click
        document.getElementById('publishModalBackdrop').addEventListener('click', function(e) {
            if (e.target === this) {
                hidePublishModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hidePublishModal();
            }
        });
    </script>
@endsection
