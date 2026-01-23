@extends('layouts.appSiswa')

@section('content')
    <style>
        .exam-detail-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .exam-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 16px;
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.15);
        }

        .exam-content {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
        }

        .info-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            border-left: 4px solid #3b82f6;
            margin-bottom: 1.5rem;
        }

        .btn-start {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            font-weight: 600;
            padding: 1rem 2rem;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .btn-start:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }

        .btn-continue {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            font-weight: 600;
            padding: 1rem 2rem;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .btn-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.3);
        }

        .btn-result {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            font-weight: 600;
            padding: 1rem 2rem;
            border-radius: 12px;
            transition: all 0.3s;
        }

        .btn-result:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
        }

        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .info-label {
            width: 120px;
            font-weight: 600;
            color: #4b5563;
        }

        .info-value {
            color: #1f2937;
        }

        .badge-status {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-available {
            background-color: rgba(96, 165, 250, 0.1);
            color: #1e40af;
        }

        .status-ongoing {
            background-color: rgba(251, 191, 36, 0.1);
            color: #92400e;
        }

        .status-completed {
            background-color: rgba(34, 197, 94, 0.1);
            color: #15803d;
        }

        .status-expired {
            background-color: rgba(239, 68, 68, 0.1);
            color: #991b1b;
        }
    </style>

    <div class="p-4 sm:p-6 lg:p-8 exam-detail-container">
        <!-- Loading Screen -->
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="loader border-t-4 border-blue-600 rounded-full w-16 h-16 animate-spin"></div>
        </div>

        <!-- Exam Header -->
        <div class="exam-header">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold mb-2">{{ $exam->title }}</h1>
                    <p class="text-blue-100">Detail Ujian / Kuis</p>
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
                    @if($status === 'ongoing')
                        Sedang Dikerjakan
                    @elseif($status === 'completed')
                        Sudah Dikerjakan
                    @elseif($status === 'expired')
                        Kadaluarsa
                    @else
                        Belum Dikerjakan
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
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                @if($ongoingAttempt)
                    <!-- Tombol Lanjutkan -->
                    <form action="{{ route('soal.kerjakan', $exam->id) }}" method="GET" class="w-full sm:w-auto">
                        <button type="submit" class="btn-continue w-full">
                            <i class="fas fa-play-circle mr-2"></i>
                            Lanjutkan Ujian
                        </button>
                        <p class="text-sm text-gray-600 mt-2 text-center">
                            Anda memiliki ujian yang belum diselesaikan
                        </p>
                    </form>
                @elseif($completedAttempt)
                    <!-- Tombol Lihat Hasil -->
                    <a href="{{ route('soal.hasil', ['exam' => $exam->id, 'attempt' => $completedAttempt->id]) }}"
                       class="btn-result w-full sm:w-auto text-center">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Lihat Hasil Ujian
                    </a>
                @elseif($exam->end_at && now() > $exam->end_at)
                    <!-- Ujian Kadaluarsa -->
                    <button disabled class="w-full sm:w-auto bg-gray-400 text-white font-semibold py-3 px-8 rounded-lg cursor-not-allowed">
                        <i class="fas fa-clock mr-2"></i>
                        Ujian Telah Berakhir
                    </button>
                @elseif($exam->start_at && now() < $exam->start_at)
                    <!-- Ujian Belum Dimulai -->
                    <button disabled class="w-full sm:w-auto bg-yellow-400 text-white font-semibold py-3 px-8 rounded-lg cursor-not-allowed">
                        <i class="fas fa-clock mr-2"></i>
                        Ujian Belum Dimulai
                        <p class="text-sm mt-1">Mulai: {{ $exam->start_at->format('d M Y H:i') }}</p>
                    </button>
                @else
                    <!-- Tombol Mulai Ujian -->
                    <form action="{{ route('soal.start', $exam->id) }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="btn-start w-full" onclick="return confirm('Apakah Anda siap memulai ujian?')">
                            <i class="fas fa-play mr-2"></i>
                            Mulai Ujian Sekarang
                        </button>
                    </form>
                @endif

                <!-- Tombol Kembali -->
                <a href="{{ route('soal.index') }}"
                   class="w-full sm:w-auto bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-8 rounded-lg transition-colors text-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Daftar Soal
                </a>
            </div>

            <!-- Instruksi -->
            @if(!$completedAttempt && !$ongoingAttempt && (! $exam->end_at || now() <= $exam->end_at) && (! $exam->start_at || now() >= $exam->start_at))
            <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="font-semibold text-blue-800 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>Instruksi Sebelum Memulai:
                </h4>
                <ul class="list-disc list-inside text-sm text-blue-700 space-y-1">
                    <li>Pastikan koneksi internet stabil</li>
                    <li>Waktu ujian {{ $exam->duration }} menit</li>
                    <li>Jumlah soal: {{ $exam->questions->count() }}</li>
                    @if($exam->shuffle_question)
                        <li>Soal akan diacak untuk setiap peserta</li>
                    @endif
                    @if($exam->shuffle_answer)
                        <li>Pilihan jawaban akan diacak</li>
                    @endif
                    @if($exam->prevent_copy_paste)
                        <li>Fitur copy-paste dinonaktifkan</li>
                    @endif
                </ul>
            </div>
            @endif
        </div>
    </div>

    <!-- Scripts -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadingScreen = document.getElementById('loadingScreen');
            if (loadingScreen) {
                loadingScreen.classList.add('hidden');
            }
        });
    </script>
@endsection
