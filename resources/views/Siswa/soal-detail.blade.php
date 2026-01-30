@extends('layouts.appSiswa')

@section('content')
    <style>
        .exam-detail-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .exam-header {
            background: #ffffff;
            border-radius: 12px;
            color: #1e40af;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 2px solid #dbeafe;
        }

        .exam-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e0e7ff;
        }

        .info-card {
            background: #f0f9ff;
            border-radius: 12px;
            padding: 1.5rem;
            border-left: 4px solid #2563eb;
            margin-bottom: 1.5rem;
            border: 1px solid #bfdbfe;
        }

        .btn-start {
            background: #2563eb;
            color: white;
            font-weight: 600;
            padding: 1rem 2rem;
            border-radius: 10px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
        }

        .btn-start:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .btn-continue {
            background: #0ea5e9;
            color: white;
            font-weight: 600;
            padding: 1rem 2rem;
            border-radius: 10px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
        }

        .btn-continue:hover {
            background: #0284c7;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.2);
        }

        .btn-result {
            background: #1e40af;
            color: white;
            font-weight: 600;
            padding: 1rem 2rem;
            border-radius: 10px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
        }

        .btn-result:hover {
            background: #1e3a8a;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.2);
        }

        /* Modal Styles */
        .exam-start-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.2s ease-out;
            padding: 1rem;
        }

        .exam-start-modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
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
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0e7ff;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e40af;
            margin: 0;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #6b7280;
            cursor: pointer;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .close-modal:hover {
            background-color: #f3f4f6;
            color: #374151;
        }

        .modal-body {
            color: #475569;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .info-label {
            font-weight: 600;
            color: #4b5563;
        }

        .info-value {
            color: #1f2937;
            font-weight: 500;
        }

        .modal-footer {
            display: flex;
            gap: 1rem;
        }

        .modal-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-btn-cancel {
            background: #e5e7eb;
            color: #374151;
        }

        .modal-btn-cancel:hover {
            background: #d1d5db;
        }

        .modal-btn-confirm {
            background: #2563eb;
            color: white;
        }

        .modal-btn-confirm:hover {
            background: #1d4ed8;
        }

        .badge-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-available {
            background-color: rgba(96, 165, 250, 0.1);
            color: #1e40af;
            border: 1px solid #93c5fd;
        }

        .status-ongoing {
            background-color: rgba(251, 191, 36, 0.1);
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        .status-completed {
            background-color: rgba(34, 197, 94, 0.1);
            color: #15803d;
            border: 1px solid #86efac;
        }

        .status-expired {
            background-color: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .security-warning {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .security-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            color: #92400e;
        }

        .security-item i {
            color: #f59e0b;
        }

        .fullscreen-warning {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }

        .fullscreen-warning p {
            margin: 0;
            color: #92400e;
            font-size: 0.875rem;
        }

        @media (min-width: 640px) {

            .btn-start,
            .btn-continue,
            .btn-result {
                width: auto;
            }

            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

    <div class="p-4 sm:p-6 lg:p-8 exam-detail-container">
        <!-- Exam Header -->
        <div class="exam-header">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold mb-2">{{ $exam->title }}</h1>
                    <p class="text-blue-700">Detail Ujian / Kuis</p>
                </div>
                @php
                    $statusClass = 'status-available';
                    if ($ongoingAttempt) {
                        $status = 'ongoing';
                        $statusClass = 'status-ongoing';
                    } elseif ($completedAttempt) {
                        $status = 'completed';
                        $statusClass = 'status-completed';
                    } elseif ($exam->end_at && now() > $exam->end_at) {
                        $status = 'expired';
                        $statusClass = 'status-expired';
                    } else {
                        $status = 'available';
                    }
                @endphp
                <span class="badge-status {{ $statusClass }}">
                    @if ($status === 'ongoing')
                        <i class="fas fa-clock mr-2"></i>Sedang Dikerjakan
                    @elseif($status === 'completed')
                        <i class="fas fa-check-circle mr-2"></i>Sudah Dikerjakan
                    @elseif($status === 'expired')
                        <i class="fas fa-times-circle mr-2"></i>Kadaluarsa
                    @else
                        <i class="fas fa-play-circle mr-2"></i>Belum Dikerjakan
                    @endif
                </span>
            </div>
        </div>

        <!-- Exam Content -->
        <div class="exam-content">
            <!-- Informasi Ujian -->
            <div class="info-card">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Ujian</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="info-item">
                        <span class="info-label">Mata Pelajaran:</span>
                        <span class="info-value">{{ $exam->subject->name_subject ?? 'N/A' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Guru Pengampu:</span>
                        <span class="info-value">{{ $exam->teacher->user->name ?? 'N/A' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Kelas:</span>
                        <span class="info-value">{{ $exam->class->name_class ?? 'N/A' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Jumlah Soal:</span>
                        <span class="info-value">{{ $exam->questions->count() }} soal</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Durasi:</span>
                        <span class="info-value">{{ $exam->duration }} menit</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Waktu Mulai:</span>
                        <span class="info-value">
                            {{ $exam->start_at ? $exam->start_at->format('d M Y H:i') : 'Sekarang' }}
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Waktu Selesai:</span>
                        <span class="info-value">
                            {{ $exam->end_at ? $exam->end_at->format('d M Y H:i') : 'Tidak ditentukan' }}
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Tipe Ujian:</span>
                        <span class="info-value">{{ $exam->type === 'QUIZ' ? 'Kuis' : 'Ujian' }}</span>
                    </div>
                </div>
            </div>

            <!-- Pengaturan Ujian -->
            <div class="info-card">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Pengaturan Ujian</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="info-item">
                        <span class="info-label">Acak Soal:</span>
                        <span class="info-value">{{ $exam->shuffle_question ? 'Ya' : 'Tidak' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Acak Jawaban:</span>
                        <span class="info-value">{{ $exam->shuffle_answer ? 'Ya' : 'Tidak' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Tampilkan Skor:</span>
                        <span class="info-value">{{ $exam->show_score ? 'Ya' : 'Tidak' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Tampilkan Jawaban:</span>
                        <span class="info-value">{{ $exam->show_correct_answer ? 'Ya' : 'Tidak' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Batas Percobaan:</span>
                        <span class="info-value">{{ $exam->limit_attempts ?? 1 }} kali</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Nilai Minimal Lulus:</span>
                        <span class="info-value">{{ $exam->min_pass_grade ?? 0 }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Mode Fullscreen:</span>
                        <span class="info-value">{{ $exam->fullscreen_mode ? 'Wajib' : 'Opsional' }}</span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Blokir Tab Baru:</span>
                        <span class="info-value">{{ $exam->block_new_tab ? 'Ya' : 'Tidak' }}</span>
                    </div>
                </div>
            </div>

            <!-- Security Settings Warning -->
            @if ($exam->fullscreen_mode || $exam->block_new_tab || $exam->prevent_copy_paste)
                <div class="security-warning">
                    <h4 class="font-semibold text-amber-800 mb-3">⚠️ Pengaturan Keamanan Ujian</h4>
                    <div class="space-y-2">
                        @if ($exam->fullscreen_mode)
                            <div class="security-item">
                                <i class="fas fa-expand-alt"></i>
                                <span>Mode Fullscreen wajib diaktifkan</span>
                            </div>
                        @endif
                        @if ($exam->block_new_tab)
                            <div class="security-item">
                                <i class="fas fa-ban"></i>
                                <span>Pindah tab/window tidak diizinkan</span>
                            </div>
                        @endif
                        @if ($exam->prevent_copy_paste)
                            <div class="security-item">
                                <i class="fas fa-copy"></i>
                                <span>Copy-paste dan screenshot diblokir</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Tombol Aksi -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center items-center">
                @if ($ongoingAttempt)
                    <!-- Tombol Lanjutkan -->
                    <div class="w-full sm:w-auto">
                        <form action="{{ route('soal.kerjakan', $exam->id) }}" method="GET">
                            <button type="submit" class="btn-continue">
                                <i class="fas fa-play-circle mr-2"></i>
                                Lanjutkan Ujian
                            </button>
                        </form>
                        <p class="text-sm text-gray-600 mt-2 text-center">
                            Anda memiliki ujian yang belum diselesaikan
                        </p>
                    </div>
                @elseif($completedAttempt)
                    <!-- Tombol Lihat Hasil -->
                    <div class="w-full sm:w-auto">
                        <a href="{{ route('soal.hasil', ['exam' => $exam->id, 'attempt' => $completedAttempt->id]) }}"
                            class="btn-result">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Lihat Hasil Ujian
                        </a>
                    </div>
                @elseif($exam->end_at && now() > $exam->end_at)
                    <!-- Ujian Kadaluarsa -->
                    <div class="w-full sm:w-auto">
                        <button disabled
                            class="w-full bg-gray-400 text-white font-semibold py-3 px-8 rounded-lg cursor-not-allowed flex items-center justify-center">
                            <i class="fas fa-clock mr-2"></i>
                            Ujian Telah Berakhir
                        </button>
                    </div>
                @elseif($exam->start_at && now() < $exam->start_at)
                    <!-- Ujian Belum Dimulai -->
                    <div class="w-full sm:w-auto">
                        <button disabled
                            class="w-full bg-yellow-400 text-white font-semibold py-3 px-8 rounded-lg cursor-not-allowed flex flex-col items-center">
                            <div class="flex items-center">
                                <i class="fas fa-clock mr-2"></i>
                                Ujian Belum Dimulai
                            </div>
                            <p class="text-sm mt-1">Mulai: {{ $exam->start_at->format('d M Y H:i') }}</p>
                        </button>
                    </div>
                @else
                    <!-- Tombol Mulai Ujian -->
                    <div class="w-full sm:w-auto">
                        <button type="button" class="btn-start" onclick="openExamModal()">
                            <i class="fas fa-play mr-2"></i>
                            Mulai Ujian Sekarang
                        </button>
                    </div>
                @endif

                <!-- Tombol Kembali -->
                <div class="w-full sm:w-auto">
                    <a href="{{ route('soal.index') }}"
                        class="w-full sm:w-auto bg-slate-300 hover:bg-slate-400 text-slate-800 font-semibold py-3 px-8 rounded-lg transition-colors text-center inline-flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-left"></i>
                        Kembali ke Daftar Soal
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Start Modal -->
    <div id="examStartModal" class="exam-start-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Persiapan Ujian</h3>
                <button type="button" class="close-modal" onclick="closeExamModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Judul Ujian:</span>
                        <span class="info-value">{{ $exam->title }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Durasi:</span>
                        <span class="info-value">{{ $exam->duration }} menit</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Jumlah Soal:</span>
                        <span class="info-value">{{ $exam->questions->count() }} soal</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tipe Soal:</span>
                        <span class="info-value">{{ $exam->type === 'QUIZ' ? 'Kuis' : 'Ujian' }}</span>
                    </div>
                </div>

                @if ($exam->fullscreen_mode)
                    <div class="fullscreen-warning">
                        <p><strong>⚠️ PENTING:</strong> Ujian ini memerlukan mode fullscreen. Pastikan Anda:</p>
                        <ul class="mt-2 space-y-1 text-sm">
                            <li>• Siap mengerjakan tanpa gangguan</li>
                            <li>• Tidak membuka tab atau aplikasi lain</li>
                            <li>• Mematikan notifikasi yang mengganggu</li>
                        </ul>
                    </div>
                @endif

                <p class="text-gray-600 text-sm mt-4">
                    Setelah memulai, waktu ujian akan langsung berjalan. Pastikan Anda sudah siap sebelum memulai.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeExamModal()">
                    Batal
                </button>
                <form action="{{ route('soal.start', $exam->id) }}" method="POST" id="startExamForm">
                    @csrf
                    <button type="button" class="modal-btn modal-btn-confirm">
                        <i class="fas fa-play mr-2"></i>
                        Mulai Ujian
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openExamModal() {
            document.getElementById('examStartModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeExamModal() {
            document.getElementById('examStartModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        document.getElementById('startExamForm')?.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memulai...';

            submitBtn.classList.add('opacity-75');
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeExamModal();
            }
        });

        document.getElementById('examStartModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeExamModal();
            }
        });
    </script>
@endsection
