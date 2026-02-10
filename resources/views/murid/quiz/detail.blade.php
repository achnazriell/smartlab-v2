<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quiz->title }} - Detail Quiz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .quiz-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .quiz-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
        }

        .feature-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .feature-card:hover {
            transform: translateY(-3px);
            border-color: #3b82f6;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.1);
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-easy {
            background-color: #10b981;
            color: white;
        }

        .badge-medium {
            background-color: #f59e0b;
            color: white;
        }

        .badge-hard {
            background-color: #ef4444;
            color: white;
        }

        .progress-ring {
            transform: rotate(-90deg);
        }

        .progress-ring__circle {
            transition: stroke-dashoffset 0.5s ease;
            transform-origin: 50% 50%;
        }

        .leaderboard-item {
            transition: all 0.2s ease;
        }

        .leaderboard-item:hover {
            background-color: #f8fafc;
            transform: scale(1.01);
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .floating-button {
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
            transition: all 0.3s ease;
        }

        .floating-button:hover {
            box-shadow: 0 15px 35px rgba(59, 130, 246, 0.4);
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('quiz.index') }}" class="flex items-center text-gray-700 hover:text-gray-900">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali ke Daftar Quiz
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Quiz Header -->
        <div class="quiz-card p-8 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        <span class="badge {{ $quiz->difficulty_level == 'easy' ? 'badge-easy' : ($quiz->difficulty_level == 'medium' ? 'badge-medium' : 'badge-hard') }}">
                            {{ $quiz->difficulty_level == 'easy' ? 'Mudah' : ($quiz->difficulty_level == 'medium' ? 'Sedang' : 'Sulit') }}
                        </span>
                        <span class="badge bg-white text-purple-600">
                            {{ $quiz->quiz_mode == 'live' ? 'Quiz Langsung' : 'Quiz Rumah' }}
                        </span>
                        <span class="badge bg-white/30">
                            {{ $quiz->questions_count }} Soal
                        </span>
                        @if($quiz->is_quiz_started)
                            <span class="badge bg-green-500 pulse-animation">
                                🔴 Sedang Berlangsung
                            </span>
                        @elseif($quiz->is_room_open)
                            <span class="badge bg-blue-500">
                                Ruangan Terbuka
                            </span>
                        @endif
                    </div>

                    <h1 class="text-3xl md:text-4xl font-bold mb-3">{{ $quiz->title }}</h1>

                    <div class="flex items-center space-x-6 text-white/90">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>{{ $duration }} Menit</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <span>{{ $quiz->subject->name_subject }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                            </svg>
                            <span>{{ $quiz->class->name_class }}</span>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="w-32 h-32 relative">
                        <svg class="w-32 h-32 progress-ring" viewBox="0 0 100 100">
                            <circle class="text-white/20" stroke-width="8" stroke="currentColor" fill="transparent" r="42" cx="50" cy="50"/>
                            <circle class="text-white progress-ring__circle" stroke-width="8" stroke-linecap="round" stroke="currentColor" fill="transparent" r="42" cx="50" cy="50"
                                    stroke-dasharray="264"
                                    stroke-dashoffset="264"
                                    style="stroke-dashoffset: {{ 264 - ($quiz->questions_count / 50 * 264) }}"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-3xl font-bold">{{ $quiz->questions_count }}</div>
                                <div class="text-sm opacity-80">Soal</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Quiz Info & Instructions -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">📋 Informasi Quiz</h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-500 mb-1">Waktu Per Soal</div>
                                <div class="text-lg font-semibold text-gray-800">{{ $timePerQuestion }} Detik</div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm text-gray-500 mb-1">Total Poin</div>
                                <div class="text-lg font-semibold text-gray-800">{{ $quiz->total_score }} Poin</div>
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <h3 class="font-semibold text-gray-700 mb-2">🎯 Tujuan Quiz</h3>
                            <p class="text-gray-600">Uji pemahaman Anda tentang materi {{ $quiz->subject->name_subject }} melalui quiz interaktif ini. Jawab semua soal dengan tepat untuk mendapatkan nilai terbaik!</p>
                        </div>

                        <div class="border-t pt-4">
                            <h3 class="font-semibold text-gray-700 mb-2">📝 Instruksi Pengerjaan</h3>
                            <ul class="list-disc pl-5 space-y-2 text-gray-600">
                                <li>Pastikan koneksi internet stabil</li>
                                <li>Baca soal dengan teliti sebelum menjawab</li>
                                <li>Manfaatkan waktu dengan baik</li>
                                <li>Gunakan fitur "Tandai Soal" jika ragu</li>
                                <li>Submit jawaban sebelum waktu habis</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Quiz Features -->
                @if(count(array_filter($quizFeatures)) > 0)
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">✨ Fitur Quiz</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        @if($quizFeatures['enable_music'])
                        <div class="feature-card bg-blue-50 p-4 rounded-lg text-center">
                            <div class="text-2xl mb-2">🎵</div>
                            <div class="font-medium text-gray-800">Musik</div>
                            <div class="text-xs text-gray-600">Latar musik</div>
                        </div>
                        @endif

                        @if($quizFeatures['enable_memes'])
                        <div class="feature-card bg-green-50 p-4 rounded-lg text-center">
                            <div class="text-2xl mb-2">😂</div>
                            <div class="font-medium text-gray-800">Meme</div>
                            <div class="text-xs text-gray-600">Hiburan saat quiz</div>
                        </div>
                        @endif

                        @if($quizFeatures['enable_powerups'])
                        <div class="feature-card bg-purple-50 p-4 rounded-lg text-center">
                            <div class="text-2xl mb-2">⚡</div>
                            <div class="font-medium text-gray-800">Power-ups</div>
                            <div class="text-xs text-gray-600">Bonus kemampuan</div>
                        </div>
                        @endif

                        @if($quizFeatures['instant_feedback'])
                        <div class="feature-card bg-yellow-50 p-4 rounded-lg text-center">
                            <div class="text-2xl mb-2">💡</div>
                            <div class="font-medium text-gray-800">Feedback Instan</div>
                            <div class="text-xs text-gray-600">Koreksi langsung</div>
                        </div>
                        @endif

                        @if($quizFeatures['streak_bonus'])
                        <div class="feature-card bg-red-50 p-4 rounded-lg text-center">
                            <div class="text-2xl mb-2">🔥</div>
                            <div class="font-medium text-gray-800">Streak Bonus</div>
                            <div class="text-xs text-gray-600">Poin beruntun</div>
                        </div>
                        @endif

                        @if($quizFeatures['time_bonus'])
                        <div class="feature-card bg-indigo-50 p-4 rounded-lg text-center">
                            <div class="text-2xl mb-2">⏱️</div>
                            <div class="font-medium text-gray-800">Bonus Waktu</div>
                            <div class="text-xs text-gray-600">Poin cepat</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Previous Attempts -->
                @if($lastAttempt || $attemptCount > 0)
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">📊 Riwayat Percobaan</h2>
                    <div class="space-y-4">
                        @if($lastAttempt)
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex justify-between items-center mb-2">
                                <div class="font-medium text-gray-800">Percobaan Terakhir</div>
                                <div class="text-sm text-gray-500">{{ $lastAttempt->created_at->format('d M Y H:i') }}</div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <div class="text-sm text-gray-500">Status</div>
                                    <div class="font-semibold {{ $lastAttempt->status == 'submitted' ? 'text-green-600' : 'text-yellow-600' }}">
                                        {{ $lastAttempt->status == 'submitted' ? 'Selesai' : 'Dalam Proses' }}
                                    </div>
                                </div>
                                @if($lastAttempt->status == 'submitted')
                                <div>
                                    <div class="text-sm text-gray-500">Nilai</div>
                                    <div class="font-semibold text-blue-600">{{ number_format($lastAttempt->final_score, 1) }}/100</div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $attemptCount }}</div>
                                <div class="text-sm text-gray-600">Total Percobaan</div>
                            </div>

                            <div class="bg-green-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $quiz->limit_attempts > 0 ? $quiz->limit_attempts - $attemptCount : '∞' }}</div>
                                <div class="text-sm text-gray-600">Sisa Percobaan</div>
                            </div>

                            <div class="bg-purple-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ $canRetake ? 'Ya' : 'Tidak' }}</div>
                                <div class="text-sm text-gray-600">Bisa Mengulang</div>
                            </div>

                            <div class="bg-yellow-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-yellow-600">{{ $quiz->min_pass_grade ?? 0 }}</div>
                                <div class="text-sm text-gray-600">Nilai Minimal Lulus</div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column -->
            <div class="space-y-8">
                <!-- Action Card -->
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-6">🚀 Mulai Quiz</h2>

                    <!-- Status Indicators -->
                    <div class="space-y-4 mb-6">
                        @if($quiz->is_quiz_started)
                        <div class="flex items-center p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="w-3 h-3 rounded-full bg-green-500 mr-3 pulse-animation"></div>
                            <div>
                                <div class="font-medium text-green-800">Quiz Sedang Berlangsung</div>
                                <div class="text-sm text-green-600">Bergabung sekarang!</div>
                            </div>
                        </div>
                        @elseif($quiz->is_room_open)
                        <div class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="w-3 h-3 rounded-full bg-blue-500 mr-3"></div>
                            <div>
                                <div class="font-medium text-blue-800">Ruangan Terbuka</div>
                                <div class="text-sm text-blue-600">Menunggu guru memulai</div>
                            </div>
                        </div>
                        @else
                        <div class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg">
                            <div class="w-3 h-3 rounded-full bg-gray-400 mr-3"></div>
                            <div>
                                <div class="font-medium text-gray-800">Ruangan Tertutup</div>
                                <div class="text-sm text-gray-600">Menunggu guru membuka ruangan</div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Start Button -->
                    @if($quiz->is_quiz_started)
                        <!-- Jika quiz sudah dimulai, tampilkan tombol mulai -->
                        @if(!$lastAttempt || $lastAttempt->status != 'in_progress')
                        <form id="startQuizForm" action="{{ route('quiz.start', $quiz->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-4 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold rounded-xl shadow-lg floating-button flex items-center justify-center text-lg">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Mulai Quiz
                            </button>
                        </form>
                        @else
                        <a href="{{ route('quiz.play', $quiz->id) }}" class="block w-full py-4 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg floating-button text-center text-lg">
                            Lanjutkan Quiz
                        </a>
                        @endif
                    @elseif($quiz->is_room_open)
                        <!-- Jika ruangan terbuka tapi quiz belum dimulai -->
                        <a href="{{ route('quiz.room', $quiz->id) }}" class="block w-full py-4 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg floating-button text-center text-lg">
                            <div class="flex items-center justify-center">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Masuk Ruangan
                            </div>
                            <div class="text-sm mt-1 opacity-90">Bergabung dengan peserta lain</div>
                        </a>
                    @else
                        <!-- Jika ruangan tertutup -->
                        <button disabled class="w-full py-4 bg-gray-300 text-gray-500 font-bold rounded-xl cursor-not-allowed text-lg">
                            Menunggu Ruangan Dibuka
                        </button>
                    @endif

                    <!-- Additional Info -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Mode Quiz</span>
                                <span class="font-medium text-gray-800">{{ $quiz->quiz_mode == 'live' ? 'Live' : 'Homework' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Acak Soal</span>
                                <span class="font-medium {{ $quiz->shuffle_question ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $quiz->shuffle_question ? 'Ya' : 'Tidak' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Acak Jawaban</span>
                                <span class="font-medium {{ $quiz->shuffle_answer ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $quiz->shuffle_answer ? 'Ya' : 'Tidak' }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Tampilkan Nilai</span>
                                <span class="font-medium {{ $quiz->show_score ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $quiz->show_score ? 'Ya' : 'Tidak' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leaderboard Preview -->
                @if($showLeaderboard && $leaderboard && count($leaderboard) > 0)
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">🏆 Peringkat Teratas</h2>
                    <div class="space-y-3">
                        @foreach($leaderboard->take(3) as $index => $entry)
                        <div class="leaderboard-item flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100">
                            <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full
                                {{ $index == 0 ? 'bg-yellow-100 text-yellow-800' :
                                  ($index == 1 ? 'bg-gray-100 text-gray-800' : 'bg-orange-100 text-orange-800') }}">
                                {{ $index + 1 }}
                            </div>
                            <div class="ml-3 flex-1">
                                <div class="font-medium text-gray-800 truncate">{{ $entry['student_name'] }}</div>
                                <div class="text-sm text-gray-600">{{ number_format($entry['score'], 1) }} poin</div>
                            </div>
                        </div>
                        @endforeach

                        @if(count($leaderboard) > 3)
                        <div class="text-center pt-2">
                            <a href="{{ route('quiz.leaderboard', $quiz->id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Lihat Semua Peringkat →
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Quick Stats -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-800 mb-4">📈 Statistik Cepat</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-600">Peserta Bergabung</span>
                                <span class="font-medium text-gray-800">{{ $stats['joined'] ?? 0 }}</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500" style="width: {{ min(100, (($stats['joined'] ?? 0) / ($stats['total'] ?? 1)) * 100) }}%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-600">Peserta Selesai</span>
                                <span class="font-medium text-gray-800">{{ $stats['submitted'] ?? 0 }}</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500" style="width: {{ min(100, (($stats['submitted'] ?? 0) / ($stats['total'] ?? 1)) * 100) }}%"></div>
                            </div>
                        </div>

                        <div class="text-center pt-2">
                            <a href="{{ route('quiz.room', $quiz->id) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Lihat Ruangan Quiz
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Warning Modal -->
        <div x-data="{ showWarning: false }" x-init="
            @if($lastAttempt && $lastAttempt->status == 'in_progress')
                showWarning = true;
            @endif
        ">
            <!-- Modal -->
            <div x-show="showWarning" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Quiz Sedang Berjalan</h3>
                            <p class="text-gray-600">Anda memiliki quiz yang belum diselesaikan.</p>
                        </div>
                    </div>

                    <div class="bg-yellow-50 p-4 rounded-lg mb-6">
                        <p class="text-sm text-yellow-700">
                            ⚠️ Quiz Anda sebelumnya masih dalam proses. Anda dapat melanjutkan atau memulai yang baru.
                        </p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button @click="showWarning = false" class="px-5 py-2.5 border border-gray-300 text-gray-700 hover:text-gray-900 rounded-lg hover:bg-gray-50 transition-colors">
                            Tutup
                        </button>
                        <a href="{{ route('quiz.play', $quiz->id) }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                            Lanjutkan Quiz
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Handle form submission with confirmation
        document.getElementById('startQuizForm')?.addEventListener('submit', function(e) {
            if (!confirm('Mulai quiz sekarang? Pastikan Anda sudah siap.')) {
                e.preventDefault();
                return false;
            }

            // Show loading
            const button = this.querySelector('button[type="submit"]');
            if (button) {
                button.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memulai...
                `;
                button.disabled = true;
            }
        });

        // Auto refresh room status
        @if($quiz->is_room_open && !$quiz->is_quiz_started)
        setInterval(() => {
            fetch('{{ route("quiz.room.status", $quiz->id) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.quiz_started) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Error checking room status:', error));
        }, 5000); // Check every 5 seconds
        @endif
    </script>
</body>
</html>
