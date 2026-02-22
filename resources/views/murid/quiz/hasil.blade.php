{{-- resources/views/murid/quiz/result.blade.php --}}
@php
    use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="id" x-data="resultPage()" x-init="init()" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Quiz - {{ $quiz->title }}</title>
    <link rel="icon" type="image/icon" href="{{ asset('image/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: #0066FF;
            --primary-light: #4B9DFF;
            --primary-dark: #0052CC;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --bg-light: #FFFFFF;
            --bg-dark: #0F172A;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #EFF6FF 0%, #F0F9FF 100%);
            color: #111827;
            transition: all 0.3s ease;
        }

        body.dark {
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            color: #F3F4F6;
        }

        .result-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 102, 255, 0.1);
            transition: all 0.3s ease;
        }

        body.dark .result-card {
            background: rgba(30, 41, 59, 0.9);
            backdrop-filter: blur(10px);
            border-color: rgba(99, 102, 241, 0.2);
            box-shadow: 0 20px 40px rgba(99, 102, 241, 0.1);
        }

        .score-circle {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            background: linear-gradient(135deg, #0066FF, #3B82F6);
            color: white;
            font-weight: 700;
            font-size: 2.5rem;
            box-shadow: 0 10px 25px rgba(0, 102, 255, 0.3);
            position: relative;
        }

        body.dark .score-circle {
            background: linear-gradient(135deg, #4B9DFF, #6366F1);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
        }

        .score-circle small {
            font-size: 1rem;
            margin-left: 2px;
            opacity: 0.9;
        }

        .stat-item {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8FBFF 100%);
            border: 1px solid rgba(0, 102, 255, 0.1);
            border-radius: 16px;
            padding: 1rem;
            text-align: center;
            transition: all 0.2s;
        }

        body.dark .stat-item {
            background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
            border-color: rgba(99, 102, 241, 0.2);
        }

        .stat-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0, 102, 255, 0.15);
            border-color: rgba(0, 102, 255, 0.3);
        }

        .leaderboard-entry {
            background: white;
            border-radius: 14px;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.5rem;
            border: 1px solid #E5E7EB;
            transition: all 0.2s;
        }

        body.dark .leaderboard-entry {
            background: #1F2937;
            border-color: #374151;
        }

        .leaderboard-entry:hover {
            transform: translateX(6px);
            border-color: #0066FF;
        }

        .badge-gold {
            background: linear-gradient(135deg, #F59E0B, #FBBF24);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-silver {
            background: linear-gradient(135deg, #6B7280, #9CA3AF);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-bronze {
            background: linear-gradient(135deg, #B45309, #D97706);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.3s ease-out;
            max-width: 400px;
            font-weight: 500;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>

<body class="antialiased">
    <div class="min-h-screen p-4 md:p-6 lg:p-8">
        <div class="max-w-6xl mx-auto">

            <!-- Header -->
            <div class="result-card p-6 md:p-8 mb-8 animate-fadeIn">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white text-2xl shadow-lg">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold">{{ $quiz->title }}</h1>
                            <div class="flex flex-wrap gap-2 mt-2">
                                <span
                                    class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-xs font-semibold flex items-center gap-1">
                                    <i class="fas fa-book"></i> {{ $quiz->subject->name_subject ?? 'Mata Pelajaran' }}
                                </span>
                                <span
                                    class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full text-xs font-semibold flex items-center gap-1">
                                    <i class="fas fa-users"></i> {{ $quiz->class->name_class ?? 'Kelas' }}
                                </span>
                                <span
                                    class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full text-xs font-semibold flex items-center gap-1">
                                    <i class="fas fa-clock"></i>
                                    {{ Carbon::parse($attempt->ended_at)->format('d M Y, H:i') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('quiz.index') }}"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-all flex items-center gap-2 shadow-md hover:shadow-lg">
                            <i class="fas fa-arrow-left"></i> Daftar Quiz
                        </a>
                        <button @click="darkMode = !darkMode; toggleDarkMode()"
                            class="px-4 py-2.5 bg-gray-200 dark:bg-gray-700 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-all">
                            <i class="fas" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Skor -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Nilai dalam Lingkaran -->
                <div class="result-card p-6 flex flex-col items-center justify-center md:col-span-1">
                    <div class="score-circle mb-3">
                        <span>{{ number_format($percentage, 1) }}<small>%</small></span>
                    </div>
                    <h3 class="text-lg font-semibold mt-2">Skor Akhir</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ number_format($attempt->final_score, 2) }} poin
                    </p>
                </div>

                <!-- Statistik Cepat -->
                <div class="md:col-span-2 grid grid-cols-2 gap-4">
                    <div class="stat-item">
                        <div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $correctAnswers }}</div>
                        <div
                            class="text-sm text-gray-600 dark:text-gray-400 flex items-center justify-center gap-1 mt-1">
                            <i class="fas fa-check-circle text-green-500"></i> Benar
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            dari {{ $totalQuestions }} soal
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="text-3xl font-bold text-red-600 dark:text-red-400">{{ $incorrectAnswers }}</div>
                        <div
                            class="text-sm text-gray-600 dark:text-gray-400 flex items-center justify-center gap-1 mt-1">
                            <i class="fas fa-times-circle text-red-500"></i> Salah
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            @if ($totalQuestions > 0)
                                {{ number_format(($incorrectAnswers / $totalQuestions) * 100, 1) }}%
                            @else
                                0%
                            @endif
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $answeredQuestions }}</div>
                        <div
                            class="text-sm text-gray-600 dark:text-gray-400 flex items-center justify-center gap-1 mt-1">
                            <i class="fas fa-pencil-alt text-blue-500"></i> Dijawab
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $answeredQuestions }} / {{ $totalQuestions }} soal
                        </div>
                    </div>
                    <div class="stat-item">
                        @php
                            $timeTaken =
                                $attempt->ended_at && $attempt->started_at
                                    ? $attempt->started_at->diffInSeconds($attempt->ended_at)
                                    : 0;
                            $minutes = floor($timeTaken / 60);
                            $seconds = $timeTaken % 60;
                        @endphp
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                            {{ $minutes }}<span class="text-sm">m</span> {{ $seconds }}<span
                                class="text-sm">s</span>
                        </div>
                        <div
                            class="text-sm text-gray-600 dark:text-gray-400 flex items-center justify-center gap-1 mt-1">
                            <i class="fas fa-hourglass-half text-purple-500"></i> Waktu
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik Tambahan (Streak, Bonus) -->
            @if (!empty($quizStats))
                <div class="result-card p-6 mb-8">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-chart-line text-blue-500"></i> Statistik Tambahan
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div
                            class="bg-gradient-to-br from-orange-50 to-yellow-50 dark:from-orange-900/20 dark:to-yellow-900/20 rounded-xl p-4 border border-yellow-200 dark:border-yellow-800">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/50 flex items-center justify-center text-orange-600 dark:text-orange-400">
                                    <i class="fas fa-fire"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Streak Maks</div>
                                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                        {{ $quizStats['streak_count'] ?? 0 }}x</div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl p-4 border border-green-200 dark:border-green-800">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center text-green-600 dark:text-green-400">
                                    <i class="fas fa-gift"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Bonus Poin</div>
                                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                        {{ $quizStats['bonus_points'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                        <div
                            class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-800">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                    <i class="fas fa-stopwatch"></i>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Waktu per Soal</div>
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                        {{ $quiz->time_per_question ?? 0 }}s</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Leaderboard (jika diaktifkan) -->
            @if ($showLeaderboard)
                <div class="result-card p-6 mb-8">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold flex items-center gap-2">
                            <i class="fas fa-trophy text-yellow-500"></i> Papan Peringkat
                        </h2>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                Posisi Anda:
                                <span
                                    class="font-bold text-indigo-600 dark:text-indigo-400 text-lg ml-1">#{{ $userPosition }}</span>
                            </span>
                            @if ($userPosition == 1)
                                <span class="badge-gold">Juara 1 🥇</span>
                            @elseif($userPosition == 2)
                                <span class="badge-silver">Juara 2 🥈</span>
                            @elseif($userPosition == 3)
                                <span class="badge-bronze">Juara 3 🥉</span>
                            @endif
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Peringkat</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Nama</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Skor</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($leaderboard as $entry)
                                    <tr
                                        class="{{ $entry['student_id'] == auth()->id() ? 'bg-blue-50 dark:bg-blue-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-800' }} transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if ($entry['position'] == 1)
                                                <span class="flex items-center gap-1">🥇 1</span>
                                            @elseif($entry['position'] == 2)
                                                <span class="flex items-center gap-1">🥈 2</span>
                                            @elseif($entry['position'] == 3)
                                                <span class="flex items-center gap-1">🥉 3</span>
                                            @else
                                                {{ $entry['position'] }}
                                            @endif
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $entry['student_name'] }}
                                            @if ($entry['student_id'] == auth()->id())
                                                <span
                                                    class="ml-2 px-2 py-0.5 text-xs bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 rounded-full">Anda</span>
                                            @endif
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                            <span class="font-semibold">{{ number_format($entry['score'], 2) }}</span>
                                            pts
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            @php
                                                $minutes = floor($entry['time_taken'] / 60);
                                                $seconds = $entry['time_taken'] % 60;
                                            @endphp
                                            {{ $minutes }}m {{ $seconds }}s
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-trophy text-4xl mb-2 opacity-30"></i>
                                            <p>Belum ada data leaderboard.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Tombol Aksi -->
            <!-- Tombol Aksi -->
            <div class="flex flex-wrap justify-center gap-4 mt-8">
                @if ($canRetake)
                    <a href="{{ route('quiz.play', $quiz->id) }}"
                        class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transition-all">
                        <i class="fas fa-redo-alt"></i> Kerjakan Ulang
                    </a>
                @endif
                <a href="{{ route('quiz.index') }}"
                    class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-xl font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transition-all">
                    <i class="fas fa-list"></i> Kembali ke Daftar Quiz
                </a>
                <button onclick="window.print()"
                    class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transition-all">
                    <i class="fas fa-print"></i> Cetak Hasil
                </button>
            </div>

        </div>
    </div>

    <!-- Notification Container (untuk pesan session) -->
    <div id="notification-container"></div>

    <script>
        function resultPage() {
            return {
                darkMode: localStorage.getItem('darkMode') === 'true',
                init() {
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                    }
                },
                toggleDarkMode() {
                    localStorage.setItem('darkMode', this.darkMode);
                    document.documentElement.classList.toggle('dark', this.darkMode);
                }
            }
        }

        // Tampilkan notifikasi dari session (jika ada)
        @if (session('success'))
            window.addEventListener('load', function() {
                showNotification('success', '{{ session('success') }}');
            });
        @endif
        @if (session('error'))
            window.addEventListener('load', function() {
                showNotification('error', '{{ session('error') }}');
            });
        @endif

        function showNotification(type, message) {
            const container = document.getElementById('notification-container');
            if (!container) return;

            const notification = document.createElement('div');
            notification.className = `notification ${type === 'success' ? 'bg-green-600' : 'bg-red-600'} text-white`;
            notification.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                        <span>${message}</span>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 hover:opacity-80">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            container.appendChild(notification);
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
    </script>
</body>

</html>
