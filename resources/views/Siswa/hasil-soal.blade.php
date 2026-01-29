@extends('layouts.appSiswa')

@section('content')
    <style>
        .score-card {
            background: #2563eb;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 2px solid #1d4ed8;
        }

        .score-display {
            font-size: clamp(2rem, 8vw, 4rem);
            font-weight: 700;
            color: white;
        }

        .score-label {
            font-size: clamp(0.875rem, 2vw, 1.25rem);
            color: rgba(255, 255, 255, 0.9);
        }

        .result-item {
            border-left: 4px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        .result-item.correct {
            border-left-color: #22c55e;
            background-color: rgba(34, 197, 94, 0.05);
        }

        .result-item.incorrect {
            border-left-color: #ef4444;
            background-color: rgba(239, 68, 68, 0.05);
        }

        .result-item:hover {
            transform: translateX(4px);
        }

        .answer-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .answer-badge.correct {
            background-color: #dcfce7;
            color: #15803d;
        }

        .answer-badge.incorrect {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .answer-badge.skipped {
            background-color: #f3f4f6;
            color: #6b7280;
        }
    </style>

    <div class="min-h-screen bg-white">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="loader border-t-4 border-blue-600 rounded-full w-16 h-16 animate-spin"></div>
        </div>

        <div x-data="quizApp()" x-init="init()" class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Back Navigation -->
            <a href="{{ route('soal.index') }}"
                class="inline-flex items-center gap-2 text-slate-600 hover:text-blue-600 font-medium transition-colors duration-200 mb-8">
                <i class="fas fa-arrow-left text-lg"></i>
                <span>Kembali ke Daftar Soal</span>
            </a>

            <!-- Header -->
            <div class="bg-white shadow-lg rounded-2xl p-6 sm:p-8 mb-8 border border-slate-100">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex-1">
                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Hasil Ujian Anda</h1>
                        <p class="text-slate-600 mt-2">{{ $quiz->title }} • {{ $quiz->subject->name_subject ?? 'N/A' }}</p>
                    </div>

                    <div class="text-center">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Selesai Pada</p>
                        <p class="text-lg font-semibold text-slate-900">{{ now()->format('d F Y H:i') }}</p>
                    </div>
                </div>
            </div>

        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-800 mb-2">Hasil Kuis Anda</h1>
                <p class="text-gray-600">{{ $quiz->title }} • {{ $quiz->subject->name_subject ?? 'N/A' }}</p>
            </div>

            <!-- Score Summary Card -->
            <div class="score-card mb-8 shadow-lg">
                <div class="p-8 sm:p-12 text-center">
                    <div class="mb-4">
                        <p class="score-label">Skor Akhir Anda</p>
                    </div>
                    <div class="flex items-baseline justify-center gap-2 mb-6">
                        <span class="score-display">{{ $score }}</span>
                        <span class="text-2xl sm:text-3xl text-white opacity-80">/{{ $totalQuestions }}</span>
                    </div>
                    <div class="text-lg sm:text-xl font-semibold text-white mb-6">
                        Persentase: <span class="text-3xl">{{ round(($score / $totalQuestions) * 100, 1) }}%</span>
                    </div>

                    <!-- Performance Badge -->
                    @php
                        $percentage = ($score / $totalQuestions) * 100;
                        $grade = match(true) {
                            $percentage >= 90 => ['Sempurna', 'bg-green-500'],
                            $percentage >= 75 => ['Sangat Baik', 'bg-blue-500'],
                            $percentage >= 60 => ['Baik', 'bg-yellow-500'],
                            default => ['Perlu Ditingkatkan', 'bg-red-500'],
                        };
                    @endphp

                    <div class="inline-block {{ $grade[1] }} text-white px-6 py-2 rounded-full font-semibold">
                        {{ $grade[0] }}
                    </div>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg p-6 shadow-sm border-2 border-blue-100 text-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-blue-100 mx-auto mb-3">
                        <i class="fas fa-check text-blue-600"></i>
                    </div>
                    <p class="text-slate-600 text-sm mb-2 font-semibold uppercase tracking-wider">Benar</p>
                    <p class="text-4xl font-bold text-blue-600">{{ $correct }}</p>
                </div>
                <div class="bg-white rounded-lg p-6 shadow-sm border-2 border-blue-100 text-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-blue-100 mx-auto mb-3">
                        <i class="fas fa-xmark text-blue-600"></i>
                    </div>
                    <p class="text-slate-600 text-sm mb-2 font-semibold uppercase tracking-wider">Salah</p>
                    <p class="text-4xl font-bold text-blue-600">{{ $incorrect }}</p>
                </div>
                <div class="bg-white rounded-lg p-6 shadow-sm border-2 border-blue-100 text-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-blue-100 mx-auto mb-3">
                        <i class="fas fa-dash text-blue-600"></i>
                    </div>
                    <p class="text-slate-600 text-sm mb-2 font-semibold uppercase tracking-wider">Terlewat</p>
                    <p class="text-4xl font-bold text-blue-600">{{ $skipped }}</p>
                </div>
                <div class="bg-white rounded-lg p-6 shadow-sm border-2 border-blue-100 text-center">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-blue-100 mx-auto mb-3">
                        <i class="fas fa-hourglass text-blue-600"></i>
                    </div>
                    <p class="text-slate-600 text-sm mb-2 font-semibold uppercase tracking-wider">Waktu</p>
                    <p class="text-4xl font-bold text-blue-600">{{ $timeSpent ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Detail Per Soal -->
            <div class="bg-white rounded-lg shadow-sm p-8 mb-8 border-2 border-blue-100">
                <h2 class="text-2xl font-bold text-slate-900 mb-8 flex items-center gap-3">
                    <i class="fas fa-clipboard-list text-blue-600"></i>
                    Detail Per Soal
                </h2>

                <div class="space-y-6">
                    @forelse($answers as $index => $answer)
                        <div x-data="{ expanded: false }" class="result-item p-6 rounded-2xl {{ $answer['is_correct'] ? 'correct border-l-4 border-l-green-500 bg-green-50' : 'incorrect border-l-4 border-l-red-500 bg-red-50' }}">
                            <!-- Question Header -->
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-lg text-sm font-semibold">
                                            Soal {{ $index + 1 }}
                                        </span>
                                        @if ($answer['is_correct'])
                                            <span class="answer-badge correct">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    viewBox="0 0 24 24" fill="currentColor" class="inline mr-1">
                                                    <path
                                                        d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" />
                                                </svg>
                                                Benar
                                            </span>
                                        @else
                                            <span class="answer-badge incorrect">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    viewBox="0 0 24 24" fill="currentColor" class="inline mr-1">
                                                    <path
                                                        d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" />
                                                </svg>
                                                Salah
                                            </span>
                                        @endif
                                        @if (!$answer['selected_answer'])
                                            <span class="answer-badge skipped">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                    viewBox="0 0 24 24" fill="currentColor" class="inline mr-1">
                                                    <path d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm0 12a.75.75 0 0 1 0-1.5.75.75 0 0 1 0 1.5Z" />
                                                </svg>
                                                Terlewat
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-gray-800 font-semibold" x-html="'{{ addslashes($answer['question_text']) }}'"></h3>
                                </div>
                                <button @click="expanded = !expanded"
                                    class="flex-shrink-0 px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors text-sm font-semibold">
                                    <span x-show="!expanded">Lihat Detail</span>
                                    <span x-show="expanded">Sembunyikan</span>
                                </button>
                            </div>

                            <!-- Question Content (Hidden by default) -->
                            <template x-if="expanded">
                                <div class="mt-4 space-y-4 border-t pt-4">
                                    <!-- Question Image -->
                                    @if ($answer['question_image'])
                                        <div>
                                            <img src="{{ $answer['question_image'] }}" alt="Question Image"
                                                class="max-w-full h-auto rounded-lg shadow-sm">
                                        </div>
                                    @endif

                                    <!-- Answer Comparison -->
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <!-- Your Answer -->
                                        <div class="bg-red-50 rounded-lg p-4">
                                            <p class="text-sm font-semibold text-gray-700 mb-2">Jawaban Anda:</p>
                                            @if ($answer['selected_answer'])
                                                <p class="text-lg font-bold text-red-600">
                                                    {{ $answer['selected_answer'] }}. {{ $answer['selected_text'] ?? 'N/A' }}
                                                </p>
                                            @else
                                                <p class="text-lg font-semibold text-gray-500 italic">Tidak dijawab</p>
                                            @endif
                                        </div>

                                        <!-- Correct Answer -->
                                        <div class="bg-green-50 rounded-lg p-4">
                                            <p class="text-sm font-semibold text-gray-700 mb-2">Jawaban Benar:</p>
                                            <p class="text-lg font-bold text-green-600">
                                                {{ $answer['correct_answer'] }}. {{ $answer['correct_text'] ?? 'N/A' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Explanation -->
                                    @if ($answer['explanation'])
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                            <p class="text-sm font-semibold text-blue-900 mb-2">Pembahasan:</p>
                                            <p class="text-gray-700 leading-relaxed" x-html="'{{ addslashes($answer['explanation']) }}'"></p>
                                        </div>
                                    @endif
                                </div>
                            </template>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <p class="text-gray-600 text-lg">Tidak ada data jawaban</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                <a href="{{ route('soal.index') }}"
                    class="flex items-center justify-center gap-2 px-8 py-3 bg-slate-600 hover:bg-slate-700 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Daftar Soal
                </a>

                @if ($allow_retake)
                    <a href="{{ route('soal.show', $quiz->id) }}"
                        class="flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg">
                        <i class="fas fa-rotate-right"></i>
                        Coba Lagi
                    </a>
                @endif
            </div>

            <!-- Additional Stats (Optional) -->
            <div class="bg-white rounded-lg shadow-sm p-8 border-2 border-blue-100">
                <h3 class="text-xl font-bold text-blue-900 mb-8 flex items-center gap-3">
                    <i class="fas fa-chart-bar text-blue-600"></i>
                    Statistik Tambahan
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                    <div class="p-6 bg-white rounded-lg border-2 border-blue-100">
                        <p class="text-slate-600 text-sm mb-3 font-semibold uppercase tracking-wider">Rata-rata Waktu per Soal</p>
                        <p class="text-3xl font-bold text-blue-600">
                            {{ $timeSpent ? round($timeSpent / count($answers), 0) : 'N/A' }} <span class="text-lg">detik</span>
                        </p>
                    </div>
                    <div class="p-6 bg-white rounded-lg border-2 border-blue-100">
                        <p class="text-slate-600 text-sm mb-3 font-semibold uppercase tracking-wider">Tingkat Akurasi</p>
                        <p class="text-3xl font-bold text-blue-600">{{ round(($correct / count($answers)) * 100, 1) }}<span class="text-lg">%</span></p>
                    </div>
                </div>
            </div>
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
