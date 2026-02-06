@extends('layouts.appSiswa')

@section('content')
    <style>
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: slideUp 0.3s ease-out;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            z-index: 100000;
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
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e0e7ff;
        }

        .modal-icon {
            width: 48px;
            height: 48px;
            background: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
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
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 120px;
        }

        .modal-btn-cancel {
            background: #e5e7eb;
            color: #374151;
        }

        .modal-btn-cancel:hover {
            background: #d1d5db;
        }

        .modal-btn-confirm {
            background: #3b82f6;
            color: white;
        }

        .modal-btn-confirm:hover:not(:disabled) {
            background: #2563eb;
        }

        .modal-btn-confirm:disabled {
            background: #93c5fd;
            cursor: not-allowed;
        }
    </style>

    <div class="max-w-5xl mx-auto px-4 md:px-8 py-8 md:py-12">
        <!-- Header dengan Gradient Biru SMART LAB -->
        <div
            class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-xl p-10 md:p-12 mb-10 relative overflow-hidden">
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" viewBox="0 0 1440 320" preserveAspectRatio="none">
                    <path fill="currentColor"
                        d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z">
                    </path>
                </svg>
            </div>

            <div class="relative z-10">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6 mb-8">
                    <div class="flex-1">
                        <h1 class="text-4xl md:text-5xl font-bold text-white mb-3 drop-shadow-lg leading-tight">
                            {{ $exam->title ?? 'Detail Ujian' }}
                        </h1>
                        @if ($exam->type)
                            <p class="text-blue-100 text-lg font-medium flex items-center gap-2">
                                <i class="fas fa-file-alt text-blue-200"></i>
                                <span>{{ $exam->getDisplayType() }}</span>
                            </p>
                        @endif
                    </div>

                    <!-- Status Badge -->
                    @php
                        $statusClass = 'bg-emerald-500/25 text-white';
                        $statusText = 'Aktif';
                        $statusIcon = 'fa-check-circle';

                        // Gunakan method dari model
                        $timeStatus = $exam->getTimeStatus();

                        if ($exam->status !== 'active') {
                            $statusClass = 'bg-gray-500/25 text-white';
                            $statusText = ucfirst($exam->status);
                            $statusIcon = 'fa-times-circle';
                        } elseif ($timeStatus === 'upcoming') {
                            $statusClass = 'bg-blue-400/25 text-white';
                            $statusText = 'Akan Datang';
                            $statusIcon = 'fa-clock';
                        } elseif ($timeStatus === 'ongoing') {
                            $statusClass = 'bg-emerald-500/25 text-white';
                            $statusText = 'Sedang Berlangsung';
                            $statusIcon = 'fa-hourglass-half';
                        } elseif ($timeStatus === 'finished') {
                            $statusClass = 'bg-gray-500/25 text-white';
                            $statusText = 'Telah Berakhir';
                            $statusIcon = 'fa-times-circle';
                        } elseif ($timeStatus === 'inactive') {
                            $statusClass = 'bg-red-500/25 text-white';
                            $statusText = 'Tidak Aktif';
                            $statusIcon = 'fa-ban';
                        }
                    @endphp
                    <span
                        class="px-5 py-3 rounded-lg font-semibold text-sm {{ $statusClass }} inline-flex items-center gap-2 backdrop-blur-md border border-white/20 shadow-lg">
                        <i class="fas {{ $statusIcon }}"></i> {{ $statusText }}
                    </span>
                </div>

                <!-- Meta Information -->
                <div class="flex flex-wrap gap-6 mt-6 pt-6 border-t border-white/20">
                    @if ($exam->subject)
                        <div class="flex items-center gap-3">
                            <i class="fas fa-book text-blue-200 text-lg"></i>
                            <div class="text-sm opacity-80">
                                <span class="block text-blue-200 opacity-70">Mata Pelajaran</span>
                                <span
                                    class="block text-blue-200 text-base font-semibold">{{ $exam->subject->name_subject ?? 'Tidak ada' }}</span>
                            </div>
                        </div>
                    @endif

                    @if ($exam->teacher)
                        <div class="flex items-center gap-3">
                            <i class="fas fa-user-tie text-blue-200 text-lg"></i>
                            <div class="text-sm opacity-80">
                                <span class="block text-blue-200 opacity-70">Guru</span>
                                <span
                                    class="block text-blue-200 text-base font-semibold">{{ $exam->teacher->user->name ?? $exam->teacher->name }}</span>
                            </div>
                        </div>
                    @endif

                    @if ($exam->class)
                        <div class="flex items-center gap-3">
                            <i class="fas fa-users text-blue-200 text-lg"></i>
                            <div class="text-sm opacity-80">
                                <span class="block text-blue-200 opacity-70">Kelas</span>
                                <span
                                    class="block text-blue-200 text-base font-semibold">{{ $exam->class->name_class ?? 'Tidak ada' }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Info Cards - Exam Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
            <div
                class="bg-white rounded-xl p-7 border border-gray-300 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all">
                <h3 class="flex items-center gap-3 text-xl font-bold text-gray-900 mb-5 pb-4 border-b border-gray-200">
                    <i class="fas fa-list-check text-blue-500"></i>
                    Informasi Ujian
                </h3>
                <div class="space-y-3">
                    @php
                        $questionsCount = $exam->questions_count ?? $exam->questions->count();
                    @endphp
                    @if ($questionsCount > 0)
                        <div
                            class="flex flex-col gap-1 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500 hover:bg-blue-100 transition-colors">
                            <span class="text-xs text-blue-600 font-semibold uppercase tracking-wider">Total Soal</span>
                            <span class="text-2xl font-bold text-blue-900">{{ $questionsCount }}</span>
                        </div>
                    @endif

                    @if ($exam->duration)
                        <div
                            class="flex flex-col gap-1 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500 hover:bg-blue-100 transition-colors">
                            <span class="text-xs text-blue-600 font-semibold uppercase tracking-wider">Durasi Ujian</span>
                            <span class="text-2xl font-bold text-blue-900">{{ $exam->duration }} Menit</span>
                        </div>
                    @endif

                    @if ($exam->min_pass_grade)
                        <div
                            class="flex flex-col gap-1 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500 hover:bg-blue-100 transition-colors">
                            <span class="text-xs text-blue-600 font-semibold uppercase tracking-wider">Nilai Kelulusan
                                Minimal</span>
                            <span class="text-2xl font-bold text-blue-900">{{ $exam->min_pass_grade }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div
                class="bg-white rounded-xl p-7 border border-gray-300 shadow-md hover:shadow-xl hover:-translate-y-1 transition-all">
                <h3 class="flex items-center gap-3 text-xl font-bold text-gray-900 mb-5 pb-4 border-b border-gray-200">
                    <i class="fas fa-sliders-h text-blue-500"></i>
                    Pengaturan Ujian
                </h3>
                <div class="space-y-3">
                    <div
                        class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <span class="text-gray-700 font-medium">Acak Soal</span>
                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-white rounded-lg font-bold">
                            <i
                                class="fas {{ $exam->shuffle_question ? 'fa-check text-emerald-600' : 'fa-times text-gray-400' }}"></i>
                            <span
                                class="{{ $exam->shuffle_question ? 'text-emerald-700' : 'text-gray-600' }}">{{ $exam->shuffle_question ? 'Ya' : 'Tidak' }}</span>
                        </span>
                    </div>

                    <div
                        class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <span class="text-gray-700 font-medium">Acak Jawaban</span>
                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-white rounded-lg font-bold">
                            <i
                                class="fas {{ $exam->shuffle_answer ? 'fa-check text-emerald-600' : 'fa-times text-gray-400' }}"></i>
                            <span
                                class="{{ $exam->shuffle_answer ? 'text-emerald-700' : 'text-gray-600' }}">{{ $exam->shuffle_answer ? 'Ya' : 'Tidak' }}</span>
                        </span>
                    </div>

                    <div
                        class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <span class="text-gray-700 font-medium">Tampilkan Nilai</span>
                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-white rounded-lg font-bold">
                            <i
                                class="fas {{ $exam->show_score ? 'fa-check text-emerald-600' : 'fa-times text-gray-400' }}"></i>
                            <span
                                class="{{ $exam->show_score ? 'text-emerald-700' : 'text-gray-600' }}">{{ $exam->show_score ? 'Ya' : 'Tidak' }}</span>
                        </span>
                    </div>

                    <div
                        class="flex justify-between items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <span class="text-gray-700 font-medium">Tampilkan Jawaban Benar</span>
                        <span class="inline-flex items-center gap-2 px-3 py-1 bg-white rounded-lg font-bold">
                            <i
                                class="fas {{ $exam->show_correct_answer ? 'fa-check text-emerald-600' : 'fa-times text-gray-400' }}"></i>
                            <span
                                class="{{ $exam->show_correct_answer ? 'text-emerald-700' : 'text-gray-600' }}">{{ $exam->show_correct_answer ? 'Ya' : 'Tidak' }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        @if ($exam->prevent_copy_paste || $exam->fullscreen_mode || $exam->block_new_tab)
            <div class="bg-gradient-to-r from-red-50 to-red-100 border-2 border-red-500 rounded-xl p-7 mb-10 shadow-lg">
                <h4 class="flex items-center gap-3 text-xl font-bold text-red-900 mb-5">
                    <i class="fas fa-lock text-red-600 text-2xl"></i>
                    Pengaturan Keamanan Ujian
                </h4>
                <div class="space-y-3">
                    @if ($exam->prevent_copy_paste)
                        <div
                            class="flex items-center gap-3 p-4 bg-white/60 rounded-lg font-semibold text-red-900 border-l-4 border-red-500">
                            <i class="fas fa-ban text-red-600 text-lg"></i>
                            <span>Copy, Cut, dan Paste Dinonaktifkan</span>
                        </div>
                    @endif

                    @if ($exam->fullscreen_mode)
                        <div
                            class="flex items-center gap-3 p-4 bg-white/60 rounded-lg font-semibold text-red-900 border-l-4 border-red-500">
                            <i class="fas fa-expand text-red-600 text-lg"></i>
                            <span>Mode Fullscreen Wajib Diaktifkan</span>
                        </div>
                    @endif

                    @if ($exam->block_new_tab)
                        <div
                            class="flex items-center gap-3 p-4 bg-white/60 rounded-lg font-semibold text-red-900 border-l-4 border-red-500">
                            <i class="fas fa-ban text-red-600 text-lg"></i>
                            <span>Tidak Boleh Membuka Tab/Window Baru</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Attempt Status -->
        @php
            // Pastikan menggunakan variabel yang sudah di-pass dari controller
            $latestAttempt = $lastAttempt ?? null;
            $attemptCount = $attemptCount ?? 0;
        @endphp

        @if ($latestAttempt)
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-500 rounded-xl p-8 mb-10 shadow-lg">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6 mb-8">
                    <h2 class="flex items-center gap-3 text-3xl font-bold text-gray-900">
                        <i class="fas fa-history text-blue-600 text-3xl"></i>
                        Status Pengerjaan Terbaru
                    </h2>
                    @if ($latestAttempt->status == 'in_progress')
                        <span
                            class="px-6 py-3 bg-amber-500 text-white rounded-lg font-bold text-sm inline-flex items-center gap-2 shadow-lg">
                            <i class="fas fa-hourglass-half animate-spin"></i> Sedang Berlangsung
                        </span>
                    @else
                        <span
                            class="px-6 py-3 bg-emerald-600 text-white rounded-lg font-bold text-sm inline-flex items-center gap-2 shadow-lg">
                            <i class="fas fa-check-circle"></i> Selesai
                        </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-5 bg-white rounded-lg shadow-md border border-gray-200">
                        <p class="text-xs text-gray-600 font-bold uppercase tracking-wider mb-2">Percobaan Ke</p>
                        <p class="text-3xl font-bold text-blue-600">
                            #{{ $attemptCount + ($latestAttempt->status == 'in_progress' ? 0 : 1) }}</p>
                    </div>

                    @if ($latestAttempt->status == 'submitted' || $latestAttempt->status == 'timeout')
                        <div class="text-center p-5 bg-white rounded-lg shadow-md border border-gray-200">
                            <p class="text-xs text-gray-600 font-bold uppercase tracking-wider mb-2">Nilai Akhir</p>
                            <p class="text-3xl font-bold text-blue-600">{{ $latestAttempt->final_score ?? 0 }}</p>
                        </div>

                        <div class="text-center p-5 bg-white rounded-lg shadow-md border border-gray-200">
                            <p class="text-xs text-gray-600 font-bold uppercase tracking-wider mb-2">Status Kelulusan</p>
                            @php
                                $isPassed = $exam->min_pass_grade
                                    ? $latestAttempt->final_score >= $exam->min_pass_grade
                                    : true;
                            @endphp
                            <p class="text-2xl font-bold {{ $isPassed ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $isPassed ? 'LULUS' : 'TIDAK LULUS' }}
                            </p>
                        </div>
                    @endif

                    @if ($latestAttempt->violation_count > 0)
                        <div class="text-center p-5 bg-white rounded-lg shadow-md border border-gray-200">
                            <p class="text-xs text-gray-600 font-bold uppercase tracking-wider mb-2">Pelanggaran</p>
                            <p class="text-3xl font-bold text-red-600">{{ $latestAttempt->violation_count }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="mt-12 flex flex-col sm:flex-row gap-4 justify-center items-stretch">
            @php
                // Cek apakah ujian bisa diakses
                $canAccess = $exam->isAccessibleForStudent();
                $hasOngoingAttempt = $latestAttempt && $latestAttempt->status == 'in_progress';
                $hasCompletedAttempt = $latestAttempt && in_array($latestAttempt->status, ['submitted', 'timeout']);
                $canStartNewAttempt = $canAccess && !$hasOngoingAttempt && (!$hasCompletedAttempt || ($canRetake ?? false));
            @endphp

            @if ($canStartNewAttempt)
                <!-- Tombol Mulai Ujian - Tampilkan tombol dengan modal -->
                <button type="button" id="btnStartExam"
                    class="px-10 py-4 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold rounded-xl shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 inline-flex items-center justify-center gap-3 min-w-64 text-lg">
                    <i class="fas fa-play-circle text-xl"></i>
                    Mulai Ujian
                </button>
            @elseif($hasOngoingAttempt)
                <!-- Tombol Lanjutkan Ujian -->
                <a href="{{ route('soal.kerjakan', $exam->id) }}"
                    class="px-10 py-4 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold rounded-xl shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 inline-flex items-center justify-center gap-3 min-w-64 text-lg">
                    <i class="fas fa-arrow-right-long text-xl"></i>
                    Lanjutkan Ujian
                </a>
            @else
                <!-- Tombol Ujian Tidak Tersedia -->
                <button disabled
                    class="px-10 py-4 bg-gray-400 text-white font-bold rounded-xl shadow-xl inline-flex items-center justify-center gap-3 min-w-64 text-lg cursor-not-allowed">
                    <i class="fas fa-lock text-xl"></i>
                    Ujian Tidak Tersedia
                </button>
            @endif

            @if ($hasCompletedAttempt)
                <!-- Tombol Lihat Hasil -->
                <a href="{{ route('soal.hasil', ['exam' => $exam->id, 'attempt' => $latestAttempt->id]) }}"
                    class="px-10 py-4 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-bold rounded-xl shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 inline-flex items-center justify-center gap-3 min-w-64 text-lg">
                    <i class="fas fa-chart-line text-xl"></i>
                    Lihat Hasil
                </a>
            @endif

            <!-- Tombol Kembali -->
            <a href="{{ route('soal.index') }}"
                class="px-10 py-4 bg-gray-300 hover:bg-gray-400 text-gray-900 font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 inline-flex items-center justify-center gap-3 min-w-64 text-lg">
                <i class="fas fa-chevron-left text-xl"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Modal Konfirmasi Mulai Ujian -->
    <div id="startExamModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h2 class="modal-title">Konfirmasi Mulai Ujian</h2>
                </div>
            </div>
            <div class="modal-body">
                <!-- Tampilkan peraturan keamanan -->
                <div class="space-y-4">
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4">
                        <h4 class="font-bold text-blue-900 mb-2 flex items-center gap-2">
                            <i class="fas fa-info-circle text-blue-600"></i>
                            Informasi Ujian
                        </h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Judul Ujian:</span>
                                <span class="font-semibold">{{ $exam->title }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Durasi:</span>
                                <span class="font-semibold">{{ $exam->duration }} menit</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Jumlah Soal:</span>
                                <span class="font-semibold">{{ $exam->questions_count ?? $exam->questions->count() }}
                                    soal</span>
                            </div>
                            @if ($exam->limit_attempts > 1)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Maksimal Percobaan:</span>
                                    <span class="font-semibold">{{ $exam->limit_attempts }} kali</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Peraturan Keamanan -->
                    @if ($exam->fullscreen_mode || $exam->block_new_tab || $exam->prevent_copy_paste)
                        <div class="bg-red-50 border-l-4 border-red-500 p-4">
                            <h4 class="font-bold text-red-900 mb-2 flex items-center gap-2">
                                <i class="fas fa-shield-alt text-red-600"></i>
                                Peraturan Keamanan
                            </h4>
                            <div class="space-y-2 text-sm">
                                @if ($exam->fullscreen_mode)
                                    <div class="flex items-start gap-2">
                                        <i class="fas fa-check-circle text-red-600 mt-0.5"></i>
                                        <span>Wajib menggunakan mode <strong>fullscreen</strong></span>
                                    </div>
                                @endif
                                @if ($exam->block_new_tab)
                                    <div class="flex items-start gap-2">
                                        <i class="fas fa-check-circle text-red-600 mt-0.5"></i>
                                        <span><strong>Dilarang</strong> membuka tab/window baru</span>
                                    </div>
                                @endif
                                @if ($exam->prevent_copy_paste)
                                    <div class="flex items-start gap-2">
                                        <i class="fas fa-check-circle text-red-600 mt-0.5"></i>
                                        <span><strong>Dilarang</strong> copy, cut, paste</span>
                                    </div>
                                @endif
                                @if ($exam->violation_limit)
                                    <div class="flex items-start gap-2">
                                        <i class="fas fa-check-circle text-red-600 mt-0.5"></i>
                                        <span>Batas pelanggaran: <strong>{{ $exam->violation_limit }}
                                                kali</strong></span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Warning Message -->
                    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-lg mt-0.5"></i>
                            <div>
                                <p class="text-yellow-900 font-medium mb-1">Peringatan Penting!</p>
                                <p class="text-yellow-800 text-sm">
                                    Pastikan Anda siap mengerjakan ujian. Setelah dimulai, waktu akan terus berjalan dan
                                    tidak bisa dihentikan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Checkbox Konfirmasi -->
                    <div class="mt-4">
                        <label class="flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" id="confirmRules"
                                class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="text-sm text-gray-700">
                                Saya telah membaca dan memahami semua peraturan di atas. Saya siap mengerjakan ujian
                                dengan jujur dan bertanggung jawab.
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-cancel" id="btnCancelModal">
                    <i class="fas fa-times mr-2"></i> Batal
                </button>
                <button type="button" id="confirmStartBtn" class="modal-btn modal-btn-confirm" disabled>
                    <i class="fas fa-play-circle mr-2"></i> Mulai Ujian
                </button>
            </div>
        </div>
    </div>

    <!-- Form untuk Mulai Ujian (tersembunyi) -->
    <form id="startExamForm" action="{{ route('soal.start', $exam->id) }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get elements
            const modal = document.getElementById('startExamModal');
            const btnStartExam = document.getElementById('btnStartExam');
            const btnCancelModal = document.getElementById('btnCancelModal');
            const confirmRules = document.getElementById('confirmRules');
            const confirmStartBtn = document.getElementById('confirmStartBtn');
            const startExamForm = document.getElementById('startExamForm');

            // Debug logging
            console.log('Modal element:', modal);
            console.log('Start button element:', btnStartExam);

            // Check if modal and button exist
            if (!modal) {
                console.error('Modal element not found!');
                return;
            }

            // Open modal function
            function openModal() {
                console.log('Opening modal...');
                modal.classList.add('active');
                confirmRules.checked = false;
                confirmStartBtn.disabled = true;
            }

            // Close modal function
            function closeModal() {
                console.log('Closing modal...');
                modal.classList.remove('active');
                confirmRules.checked = false;
                confirmStartBtn.disabled = true;
            }

            // Submit form function
            function submitForm() {
                console.log('Submitting form...');
                if (confirmRules.checked) {
                    startExamForm.submit();
                }
            }

            // Event Listeners
            if (btnStartExam) {
                btnStartExam.addEventListener('click', openModal);
            } else {
                console.warn('Start exam button not found!');
            }

            if (btnCancelModal) {
                btnCancelModal.addEventListener('click', closeModal);
            }

            if (confirmRules) {
                confirmRules.addEventListener('change', function() {
                    confirmStartBtn.disabled = !this.checked;
                });
            }

            if (confirmStartBtn) {
                confirmStartBtn.addEventListener('click', submitForm);
            }

            // Close modal when clicking outside
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal();
                    }
                });
            }

            // Handle Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modal.classList.contains('active')) {
                    closeModal();
                }
            });
        });
    </script>
@endsection
