@php
    $isGuru = auth()->user()->hasRole('Guru');
    $isMurid = auth()->user()->hasRole('Murid');

    // Pastikan $participant tersedia untuk siswa
    $participant = null;
    if ($isMurid) {
        $quiz->load([
            'activeSession.participants' => function ($query) {
                $query->where('student_id', auth()->id());
            },
        ]);

        if ($quiz->activeSession) {
            $participant = $quiz->activeSession->participants->first();
        }
    }
@endphp

<!DOCTYPE html>
<html lang="id" x-data="roomData()" x-init="init()" :class="{ 'dark': isDarkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quiz->title }} - Ruangan Quiz</title>
    <link rel="icon" type="image/icon" href="{{ asset('image/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --primary-light: #3B82F6;
            --primary-dark: #6366F1;
            --bg-light: #FFFFFF;
            --bg-dark: #1F2937;
            --text-light: #111827;
            --text-dark: #F3F4F6;
            --accent-light: #EFF6FF;
            --accent-dark: #1F2937;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body {
            background: linear-gradient(135deg, #EFF6FF 0%, #F0F9FF 100%);
            color: var(--text-light);
        }

        body.dark {
            background: linear-gradient(135deg, #1F2937 0%, #111827 100%);
            color: var(--text-dark);
        }

        .room-container {
            background: var(--bg-light);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.08);
            border: 1px solid rgba(59, 130, 246, 0.1);
            transition: all 0.3s ease;
        }

        body.dark .room-container {
            background: var(--bg-dark);
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .participant-card {
            background: var(--bg-light);
            border-radius: 12px;
            border: 1px solid rgba(59, 130, 246, 0.1);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.05);
            transition: all 0.3s ease;
        }

        body.dark .participant-card {
            background: #374151;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .participant-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
        }

        /* ✅ FIX: Card merah saat ada pelanggaran */
        .participant-card.has-violation {
            border: 3px solid #EF4444 !important;
            background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%) !important;
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3) !important;
            animation: pulseRed 2s ease-in-out infinite;
        }

        body.dark .participant-card.has-violation {
            border: 3px solid #DC2626 !important;
            background: linear-gradient(135deg, #7F1D1D 0%, #991B1B 100%) !important;
            box-shadow: 0 4px 16px rgba(220, 38, 38, 0.3) !important;
        }

        @keyframes pulseRed {

            0%,
            100% {
                box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3);
            }

            50% {
                box-shadow: 0 4px 24px rgba(239, 68, 68, 0.6);
            }
        }

        /* ✅ FIX: Badge pelanggaran merah */
        .violation-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #EF4444;
            color: white;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 9999px;
        }

        /* ✅ FIX: Summary bar total pelanggaran */
        .violation-summary-bar {
            background: linear-gradient(135deg, #FEF2F2, #FEE2E2);
            border: 1px solid #FECACA;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        body.dark .violation-summary-bar {
            background: linear-gradient(135deg, rgba(127, 29, 29, 0.3), rgba(153, 27, 27, 0.2));
            border-color: rgba(239, 68, 68, 0.4);
        }

        .action-button {
            background: linear-gradient(135deg, var(--primary-light) 0%, #1D4ED8 100%);
            color: white;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            text-decoration: none;
            gap: 0.5rem;
        }

        body.dark .action-button {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #818CF8 100%);
        }

        .action-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
        }

        .action-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .action-button.success {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        }

        .action-button.danger {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
        }

        .action-button.warning {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        }

        .action-button.secondary {
            background: linear-gradient(135deg, #6B7280 0%, #4B5563 100%);
        }

        body.dark .action-button.secondary {
            background: linear-gradient(135deg, #4B5563 0%, #1F2937 100%);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.875rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-badge.waiting {
            background: #FEF3C7;
            color: #92400E;
        }

        .status-badge.ready {
            background: #DCFCE7;
            color: #166534;
        }

        .status-badge.started {
            background: #DBEAFE;
            color: #0C4A6E;
        }

        .status-badge.submitted {
            background: #EDE9FE;
            color: #5B21B6;
        }

        body.dark .status-badge {
            opacity: 0.9;
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            animation: pulse-dot 2s infinite;
        }

        .status-indicator.waiting {
            background-color: #FBBF24;
        }

        .status-indicator.ready {
            background-color: #10B981;
        }

        .status-indicator.started {
            background-color: #3B82F6;
        }

        .status-indicator.submitted {
            background-color: #8B5CF6;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.6s ease-out;
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

        .notification.success {
            background: #10B981;
            color: white;
            border-left: 4px solid #059669;
        }

        .notification.error {
            background: #EF4444;
            color: white;
            border-left: 4px solid #DC2626;
        }

        .notification.warning {
            background: #F59E0B;
            color: white;
            border-left: 4px solid #D97706;
        }

        .notification.info {
            background: #3B82F6;
            color: white;
            border-left: 4px solid #1D4ED8;
        }

        .section-title {
            color: var(--text-light);
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 1.5rem;
        }

        body.dark .section-title {
            color: var(--text-dark);
        }

        .header-gradient {
            background: linear-gradient(135deg, var(--primary-light) 0%, #1D4ED8 100%);
        }

        body.dark .header-gradient {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #818CF8 100%);
        }

        .stat-card {
            background: var(--accent-light);
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        body.dark .stat-card {
            background: #374151;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="antialiased">
    <div class="min-h-screen p-4 md:p-8">
        <div class="max-w-7xl mx-auto">

            <!-- Header Card -->
            <div class="room-container p-6 md:p-8 mb-8 animate-fadeIn">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
                    <div class="flex-1">
                        <div class="flex items-center gap-4 mb-6">
                            <div
                                class="header-gradient w-20 h-20 rounded-2xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-graduation-cap text-white text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <h1 class="text-3xl md:text-4xl font-bold mb-3">{{ $quiz->title }}</h1>
                                <div class="flex flex-wrap gap-2">
                                    <span class="status-badge ready">
                                        <i class="fas fa-book"></i> {{ $quiz->subject->name_subject }}
                                    </span>
                                    <span class="status-badge started">
                                        <i class="fas fa-users"></i> {{ $quiz->class->name_class }}
                                    </span>
                                    <span class="status-badge submitted">
                                        <i class="fas fa-question-circle"></i> {{ $quiz->questions()->count() }} Soal
                                    </span>
                                    <span class="status-badge waiting">
                                        <i class="fas fa-clock"></i> {{ $quiz->duration }} Menit
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Room Status -->
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="flex items-center space-x-2">
                                <div :class="{
                                    'bg-green-500 animate-pulse': roomOpen && !quizStarted,
                                    'bg-blue-500 animate-pulse': quizStarted,
                                    'bg-red-500': !roomOpen
                                }"
                                    class="w-3 h-3 rounded-full"></div>
                                <span class="font-medium" x-text="roomStatusText"></span>
                            </div>

                            <div class="text-sm text-gray-600" x-show="quizStarted">
                                <i class="fas fa-clock mr-1"></i>
                                Sisa waktu: <span class="font-bold" x-text="timeRemainingText"></span>
                            </div>
                        </div>
                    </div>

                    <!-- User Role Badge -->
                    <div class="flex items-center">
                        <div
                            class="{{ $isGuru ? 'bg-yellow-500' : 'bg-blue-500' }} text-white px-4 py-2 rounded-full font-bold flex items-center space-x-2">
                            <i class="fas {{ $isGuru ? 'fa-chalkboard-teacher' : 'fa-user-graduate' }}"></i>
                            <span>{{ $isGuru ? 'Guru' : 'Siswa' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex flex-wrap gap-2 md:gap-3 items-center">
                        @if ($isGuru)
                            <!-- Teacher Actions -->
                            <button @click="openRoom()" x-show="!roomOpen"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 text-white rounded-lg font-medium text-sm transition-all">
                                <i class="fas fa-door-open"></i> Buka Ruangan
                            </button>

                            <template x-if="roomOpen">
                                <div class="flex flex-wrap gap-2 md:gap-3">
                                    <button @click="startQuiz()" x-show="!quizStarted" :disabled="!canStartQuiz"
                                        :class="canStartQuiz ?
                                            'bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600' :
                                            'bg-gray-400 cursor-not-allowed'"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 text-white rounded-lg font-medium text-sm transition-all"
                                        :title="canStartQuiz ? 'Klik untuk memulai quiz' : 'Tunggu minimal 1 siswa siap'">
                                        <i class="fas fa-play"></i>
                                        <span
                                            x-text="canStartQuiz ? 'Mulai Quiz (' + stats.ready + ')' : 'Tunggu Siswa (' + stats.ready + ')'"></span>
                                    </button>

                                    <button @click="closeRoom()" x-show="!quizStarted"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600 text-white rounded-lg font-medium text-sm transition-all">
                                        <i class="fas fa-door-closed"></i> Tutup Ruangan
                                    </button>
                                </div>
                            </template>

                            <button @click="stopQuiz()" x-show="quizStarted"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-orange-500 hover:bg-orange-600 dark:bg-orange-600 dark:hover:bg-orange-500 text-white rounded-lg font-medium text-sm transition-all">
                                <i class="fas fa-stop"></i> Hentikan Quiz
                            </button>

                            <button @click="refreshData()"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-500 hover:bg-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 text-white rounded-lg font-medium text-sm transition-all">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>

                            <a href="{{ route('guru.quiz.index') }}"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-700 dark:hover:bg-indigo-600 text-white rounded-lg font-medium text-sm transition-all">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        @else
                            <!-- Student Actions -->
                            <template x-if="!isJoined && roomOpen && !quizStarted">
                                <button @click="joinRoom()"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 text-white rounded-lg font-medium text-sm transition-all">
                                    <i class="fas fa-sign-in-alt"></i> Bergabung
                                </button>
                            </template>

                            <template x-if="isJoined && participantStatus === 'waiting' && !quizStarted">
                                <button @click="markAsReady()"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white rounded-lg font-medium text-sm transition-all">
                                    <i class="fas fa-check-circle"></i> Saya Sudah Siap
                                </button>
                            </template>

                            <button @click="refreshData()"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-500 hover:bg-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600 text-white rounded-lg font-medium text-sm transition-all">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>

                            <a href="{{ route('quiz.index') }}"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-700 dark:hover:bg-indigo-600 text-white rounded-lg font-medium text-sm transition-all">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>

                            <!-- Status Messages -->
                            <template x-if="!roomOpen">
                                <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 ml-2 text-sm">
                                    <i class="fas fa-clock"></i>
                                    <span>Tunggu guru membuka ruangan...</span>
                                </div>
                            </template>

                            <template x-if="roomOpen && isJoined && participantStatus === 'ready' && !quizStarted">
                                <div
                                    class="flex items-center gap-2 text-green-600 dark:text-green-400 ml-2 text-sm animate-pulse">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Siap! Tunggu guru mulai quiz...</span>
                                </div>
                            </template>
                        @endif
                    </div>
                </div>

                <!-- Start Now Button for Students (not auto redirect) -->
                @if ($isMurid)
                    <div x-show="quizStarted && participantStatus === 'started'" x-cloak
                        class="mt-4 p-4 bg-gradient-to-r from-green-100 to-green-200 dark:from-green-900/30 dark:to-green-800/30 rounded-xl border border-green-200 dark:border-green-700">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-play-circle text-green-600 dark:text-green-400 text-xl"></i>
                                <div>
                                    <div class="font-bold text-green-800 dark:text-green-200">Quiz telah dimulai!</div>
                                    <div class="text-sm text-green-600 dark:text-green-300">Klik tombol di samping untuk
                                        mulai mengerjakan.</div>
                                </div>
                            </div>
                            <a :href="PLAY_QUIZ_URL"
                                class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition-all shadow-lg hover:shadow-xl">
                                <i class="fas fa-external-link-alt mr-2"></i> Mulai Sekarang
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="room-container p-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold" id="stat-joined" x-text="stats.joined"></div>
                            <div class="text-sm text-gray-600">Bergabung</div>
                        </div>
                    </div>
                </div>
                <div class="room-container p-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold" id="stat-ready" x-text="stats.ready"></div>
                            <div class="text-sm text-gray-600">Siap</div>
                        </div>
                    </div>
                </div>
                <div class="room-container p-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-pencil-alt text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold" id="stat-started" x-text="stats.started"></div>
                            <div class="text-sm text-gray-600">Mengerjakan</div>
                        </div>
                    </div>
                </div>
                <div class="room-container p-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-user-check text-yellow-600 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold" id="stat-submitted" x-text="stats.submitted"></div>
                            <div class="text-sm text-gray-600">Selesai</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ✅ FIX: Violation Summary Bar -->
            <div id="violation-summary-bar" class="violation-summary-bar hidden mb-4">
                <i class="fas fa-exclamation-triangle text-red-500"></i>
                <span class="text-red-700 dark:text-red-400 font-semibold text-sm">
                    Total Pelanggaran: <span id="total-violation-count" class="font-bold">0</span>
                    dari <span id="total-violators" class="font-bold">0</span> peserta
                </span>
            </div>

            <!-- Tab: Participants / Leaderboard -->
            <div class="room-container p-6 animate-fadeIn">
                <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6">
                    <button @click="activeTab = 'participants'"
                        :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'participants' }"
                        class="px-4 py-2 font-medium text-sm border-b-2 border-transparent transition-colors">
                        <i class="fas fa-users mr-2"></i> Peserta
                    </button>
                    @if ($quiz->show_leaderboard)
                        <button @click="activeTab = 'leaderboard'; loadLeaderboard()"
                            :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'leaderboard' }"
                            class="px-4 py-2 font-medium text-sm border-b-2 border-transparent transition-colors">
                            <i class="fas fa-trophy mr-2"></i> Leaderboard
                        </button>
                    @endif
                </div>

                <!-- Participants Grid (untuk semua role) -->
                <div x-show="activeTab === 'participants'">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                Daftar Peserta
                                <span class="text-sm font-normal text-gray-500 dark:text-gray-400 ml-2">
                                    (<span x-text="stats.joined"></span> dari <span x-text="stats.total"></span>
                                    siswa)
                                </span>
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400 text-sm">Update otomatis setiap 3 detik</p>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400" x-text="lastUpdatedText"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-if="participants.length === 0">
                            <div class="col-span-full flex flex-col items-center justify-center py-12 px-4">
                                <div
                                    class="flex items-center justify-center w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full mb-4">
                                    <i class="fas fa-users text-blue-400 text-2xl"></i>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 text-center">Belum ada peserta yang
                                    bergabung</p>
                            </div>
                        </template>

                        <template x-for="participant in sortedParticipants" :key="participant.id">
                            <!-- ✅ FIX: Participant Card dengan violation badge -->
                            <div class="participant-card p-4 md:p-5"
                                :class="{ 'has-violation': participant.has_violation }"
                                :data-student-id="participant.student_id">

                                <!-- ✅ Violation badge -->
                                <div x-show="participant.has_violation" class="mb-2">
                                    <span class="violation-badge">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span x-text="participant.violation_count + ' ⚠️'"></span>
                                    </span>
                                </div>

                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <div
                                            class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm md:text-base flex-shrink-0">
                                            <span
                                                x-text="participant.initial || (participant.name ? participant.name.charAt(0).toUpperCase() : '?')"></span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="font-semibold text-sm md:text-base truncate"
                                                x-text="participant.name || 'Unknown'"></div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate"
                                                x-text="participant.email || ''"></div>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 ml-2">
                                        <div :class="{
                                            'bg-yellow-400': participant.status === 'waiting',
                                            'bg-green-500': participant.status === 'ready',
                                            'bg-blue-500': participant.status === 'started',
                                            'bg-purple-600': participant.status === 'submitted'
                                        }"
                                            class="w-3 h-3 rounded-full animate-pulse"></div>
                                    </div>
                                </div>

                                <div
                                    class="flex items-center justify-between text-xs md:text-sm mb-3 pb-3 border-b border-gray-200 dark:border-gray-600">
                                    <span class="text-gray-600 dark:text-gray-400 flex items-center gap-1">
                                        <i class="fas fa-clock text-blue-500"></i>
                                        <span x-text="participant.joined_time || '-'"></span>
                                    </span>
                                    <span
                                        :class="{
                                            'text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/30': participant
                                                .status === 'waiting',
                                            'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30': participant
                                                .status === 'ready',
                                            'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30': participant
                                                .status === 'started',
                                            'text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-900/30': participant
                                                .status === 'submitted'
                                        }"
                                        class="px-2 py-1 rounded font-medium text-xs capitalize"
                                        x-text="getStatusText(participant.status)"></span>
                                </div>

                                @if ($isGuru)
                                    <div class="flex gap-2">
                                        <button @click="markParticipantAsReady(participant.id)"
                                            x-show="participant.status === 'waiting'"
                                            class="flex-1 text-xs py-1.5 px-2 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 rounded-md hover:bg-green-200 dark:hover:bg-green-900/60 transition-colors font-medium">
                                            <i class="fas fa-check mr-1"></i> Siapkan
                                        </button>
                                        <button @click="kickParticipant(participant.id)"
                                            class="flex-1 text-xs py-1.5 px-2 bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 rounded-md hover:bg-red-200 dark:hover:bg-red-900/60 transition-colors font-medium">
                                            <i class="fas fa-times mr-1"></i> Keluarkan
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Leaderboard (hanya jika diaktifkan) -->
                @if ($quiz->show_leaderboard)
                    <div x-show="activeTab === 'leaderboard'" x-cloak>
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                <i class="fas fa-trophy mr-2 text-yellow-500"></i> Leaderboard
                            </h2>
                            <button @click="loadLeaderboard()"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600">
                                <i class="fas fa-sync-alt mr-1"></i> Refresh
                            </button>
                        </div>

                        <!-- ✅ FIX: Leaderboard container -->
                        <div id="leaderboard-container"
                            class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Rank</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Nama</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Skor</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Waktu</th>
                                    </tr>
                                </thead>
                                <tbody id="leaderboard-table-body"
                                    class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-if="leaderboard.length === 0">
                                        <tr>
                                            <td colspan="4"
                                                class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Belum
                                                ada data leaderboard.</td>
                                        </tr>
                                    </template>
                                    <template x-for="(entry, index) in leaderboard" :key="index">
                                        <tr :class="{ 'bg-yellow-50 dark:bg-yellow-900/20': entry.rank === 1 }">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span x-show="entry.rank === 1">🥇</span>
                                                <span x-show="entry.rank === 2">🥈</span>
                                                <span x-show="entry.rank === 3">🥉</span>
                                                <span x-show="entry.rank > 3" x-text="entry.rank"></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                                x-text="entry.student_name || entry.name || 'Unknown'"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                                x-text="entry.score != null ? parseFloat(entry.score).toFixed(2) : '0.00'">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm"
                                                x-text="entry.time_taken ? formatTime(entry.time_taken) : '--:--'">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notification-container"></div>

    <script>
        // Route URLs
        @if ($isGuru)
            const BASE_URL = '{{ url('/') }}';
            const ROOM_STATUS_URL = '{{ route('guru.quiz.room.status', $quiz->id) }}';
            const OPEN_ROOM_URL = '{{ route('guru.quiz.room.open', $quiz->id) }}';
            const CLOSE_ROOM_URL = '{{ route('guru.quiz.room.close', $quiz->id) }}';
            const START_QUIZ_URL = '{{ route('guru.quiz.room.start', $quiz->id) }}';
            const STOP_QUIZ_URL = '{{ route('guru.quiz.room.stop', $quiz->id) }}';
            const KICK_PARTICIPANT_URL = '{{ route('guru.quiz.room.kick', [$quiz->id, 'PARTICIPANT_ID']) }}';
            const MARK_PARTICIPANT_READY_URL = '{{ route('guru.quiz.room.mark-ready', [$quiz->id, 'PARTICIPANT_ID']) }}';
            const LEADERBOARD_URL = '{{ route('guru.quiz.leaderboard', $quiz->id) }}';
            const CSRF_TOKEN = '{{ csrf_token() }}';
        @elseif ($isMurid)
            const BASE_URL = '{{ url('/') }}';
            const ROOM_STATUS_URL = '{{ route('quiz.room.status', $quiz->id) }}';
            const JOIN_ROOM_URL = '{{ route('quiz.join-room', $quiz->id) }}';
            const MARK_READY_URL = '{{ route('quiz.room.mark-ready', $quiz->id) }}';
            const PLAY_QUIZ_URL = '{{ route('quiz.play', $quiz->id) }}';
            const LEADERBOARD_URL = '{{ route('quiz.leaderboard', $quiz->id) }}';
            const CSRF_TOKEN = '{{ csrf_token() }}';
        @endif

        function roomData() {
            return {
                isDarkMode: localStorage.getItem('darkMode') === 'true',
                roomOpen: {{ $quiz->is_room_open ? 'true' : 'false' }},
                quizStarted: {{ $quiz->is_quiz_started ? 'true' : 'false' }},
                participantStatus: '{{ $participant->status ?? 'not_joined' }}',
                canStartQuiz: false,
                lastUpdated: null,
                timeRemainingText: '--:--',
                participants: [],
                stats: {
                    total: {{ $quiz->class->students()->count() ?? 0 }},
                    joined: 0,
                    ready: 0,
                    started: 0,
                    submitted: 0
                },
                activeTab: 'participants',
                leaderboard: [],

                get isJoined() {
                    return this.participantStatus !== 'not_joined' && this.participantStatus !== '';
                },

                get roomStatusText() {
                    if (this.quizStarted) return 'Quiz Sedang Berlangsung';
                    if (this.roomOpen) return 'Ruangan Terbuka';
                    return 'Ruangan Tertutup';
                },

                get sortedParticipants() {
                    return [...this.participants].sort((a, b) => {
                        if (a.has_violation && !b.has_violation) return -1;
                        if (!a.has_violation && b.has_violation) return 1;
                        if (a.has_violation && b.has_violation) {
                            return (b.violation_count || 0) - (a.violation_count || 0);
                        }
                        return 0;
                    });
                },

                get lastUpdatedText() {
                    if (!this.lastUpdated) return '';
                    const now = new Date();
                    const diffMs = now - this.lastUpdated;
                    const diffSecs = Math.floor(diffMs / 1000);
                    if (diffSecs < 60) return `${diffSecs} detik lalu`;
                    return `${Math.floor(diffSecs / 60)} menit lalu`;
                },

                async init() {
                    console.log('[INIT] Room initialized for:', '{{ $isGuru ? 'Guru' : 'Siswa' }}');

                    await this.loadRoomData();

                    setInterval(() => {
                        this.loadRoomData();
                    }, 3000);

                    setInterval(() => {
                        if (this.activeTab === 'leaderboard' && typeof this.loadLeaderboard === 'function') {
                            this.loadLeaderboard();
                        }
                    }, 5000);

                    if (this.isDarkMode) {
                        document.body.classList.add('dark');
                    }

                    @if ($isMurid)
                        if (this.roomOpen && !this.isJoined && !this.quizStarted) {
                            console.log('[AUTO-JOIN] Attempting to auto-join...');
                            setTimeout(() => {
                                this.joinRoom();
                            }, 1000);
                        }
                    @endif
                },

                // ✅ FIX: updateFromResponse dengan violation_count
                async loadRoomData() {
                    try {
                        const response = await fetch(ROOM_STATUS_URL, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            cache: 'no-cache'
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}`);
                        }

                        const data = await response.json();
                        if (data.success) {
                            this.updateFromResponse(data);
                        }
                    } catch (error) {
                        console.error('[LOAD] Error:', error);
                    }
                },

                updateFromResponse(data) {
                    if (data.is_room_open !== undefined) this.roomOpen = Boolean(data.is_room_open);
                    if (data.is_quiz_started !== undefined) this.quizStarted = Boolean(data.is_quiz_started);

                    if (data.stats) {
                        this.stats = {
                            total: data.stats.total_students || data.stats.total || 0,
                            joined: data.stats.joined || 0,
                            ready: data.stats.ready || 0,
                            started: data.stats.started || 0,
                            submitted: data.stats.submitted || 0
                        };
                    }

                    if (Array.isArray(data.participants)) {
                        this.participants = data.participants.map(p => {
                            const name = p.student_name || p.name || 'Unknown';
                            const email = p.student_email || p.email || '';
                            const violationCount = parseInt(p.violation_count) || 0;

                            // Debug violation
                            if (violationCount > 0) {
                                console.log('⚠️ Participant with violation:', name, '-', violationCount,
                                    'violations');
                            }

                            return {
                                id: p.id,
                                student_id: p.student_id,
                                name: name,
                                email: email,
                                status: p.status || 'waiting',
                                joined_time: p.joined_at || p.joined_time || '-',
                                initial: name.charAt(0).toUpperCase(),
                                violation_count: violationCount,
                                has_violation: p.has_violation || violationCount > 0
                            };
                        });

                        // ✅ Update violation summary
                        this.updateViolationSummary(this.participants);
                    }

                    @if ($isMurid)
                        if (data.participant) {
                            this.participantStatus = data.participant.status || 'not_joined';
                            console.log('Participant status updated to:', this.participantStatus);
                        }
                    @endif

                    if (data.time_remaining !== undefined && data.time_remaining !== null) {
                        this.timeRemainingText = this.formatTime(data.time_remaining);
                    }

                    this.canStartQuiz = this.stats.ready > 0 && this.roomOpen && !this.quizStarted;
                    this.lastUpdated = new Date();
                },

                // ✅ FIX: Update violation summary bar
                updateViolationSummary(participants) {
                    const bar = document.getElementById('violation-summary-bar');
                    if (!bar) return;

                    const violators = participants.filter(p => (p.violation_count || 0) > 0);
                    const totalViolations = participants.reduce((sum, p) => sum + (p.violation_count || 0), 0);

                    if (violators.length > 0) {
                        bar.classList.remove('hidden');
                        const countEl = document.getElementById('total-violation-count');
                        const violatorsEl = document.getElementById('total-violators');
                        if (countEl) countEl.textContent = totalViolations;
                        if (violatorsEl) violatorsEl.textContent = violators.length;
                    } else {
                        bar.classList.add('hidden');
                    }
                },

                formatTime(seconds) {
                    const s = parseInt(seconds);
                    if (isNaN(s) || s <= 0) return '00:00';
                    const minutes = Math.floor(s / 60);
                    const secs = s % 60;
                    return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                },

                getStatusText(status) {
                    const texts = {
                        'not_joined': 'Belum Bergabung',
                        'waiting': 'Menunggu',
                        'ready': 'Siap',
                        'started': 'Mengerjakan',
                        'submitted': 'Selesai',
                        'disconnected': 'Terputus'
                    };
                    return texts[status] || 'Tidak diketahui';
                },

                // ✅ FIX: loadLeaderboard untuk guru
                @if ($isGuru)
                    async openRoom() {
                            if (confirm('Buka ruangan quiz? Siswa akan bisa bergabung.')) {
                                try {
                                    const response = await fetch(OPEN_ROOM_URL, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': CSRF_TOKEN,
                                            'Accept': 'application/json'
                                        }
                                    });

                                    const data = await response.json();

                                    if (data.success) {
                                        this.roomOpen = true;
                                        this.showNotification('success', data.message);
                                        await this.loadRoomData();
                                    } else {
                                        this.showNotification('error', data.message || 'Gagal membuka ruangan');
                                    }
                                } catch (error) {
                                    console.error('Error opening room:', error);
                                    this.showNotification('error', 'Terjadi kesalahan');
                                }
                            }
                        },

                        async closeRoom() {
                                if (confirm('Tutup ruangan quiz? Semua peserta akan dikeluarkan.')) {
                                    try {
                                        const response = await fetch(CLOSE_ROOM_URL, {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                                'Accept': 'application/json'
                                            }
                                        });

                                        const data = await response.json();

                                        if (data.success) {
                                            this.roomOpen = false;
                                            this.showNotification('success', data.message);
                                            await this.loadRoomData();
                                        }
                                    } catch (error) {
                                        console.error('Error closing room:', error);
                                        this.showNotification('error', 'Terjadi kesalahan');
                                    }
                                }
                            },

                            async startQuiz() {
                                    if (!this.canStartQuiz) {
                                        this.showNotification('warning', 'Belum ada siswa yang siap');
                                        return;
                                    }

                                    if (confirm(
                                            `Mulai quiz sekarang? ${this.stats.ready} siswa yang siap akan mulai mengerjakan.`
                                            )) {
                                        try {
                                            const response = await fetch(START_QUIZ_URL, {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': CSRF_TOKEN,
                                                    'Accept': 'application/json'
                                                }
                                            });

                                            const data = await response.json();

                                            if (data.success) {
                                                this.quizStarted = true;
                                                this.showNotification('success', data.message);
                                                await this.loadRoomData();
                                            } else {
                                                this.showNotification('error', data.message);
                                            }
                                        } catch (error) {
                                            console.error('Error starting quiz:', error);
                                            this.showNotification('error', 'Terjadi kesalahan');
                                        }
                                    }
                                },

                                async stopQuiz() {
                                        if (confirm(
                                                'Hentikan quiz sekarang? Semua siswa yang belum selesai akan dipaksa submit.'
                                                )) {
                                            try {
                                                const response = await fetch(STOP_QUIZ_URL, {
                                                    method: 'POST',
                                                    headers: {
                                                        'X-CSRF-TOKEN': CSRF_TOKEN,
                                                        'Accept': 'application/json'
                                                    }
                                                });

                                                const data = await response.json();

                                                if (data.success) {
                                                    this.quizStarted = false;
                                                    this.roomOpen = false;
                                                    this.showNotification('success', data.message);
                                                    await this.loadRoomData();
                                                } else {
                                                    this.showNotification('error', data.message ||
                                                        'Gagal menghentikan quiz');
                                                }
                                            } catch (error) {
                                                console.error('Error stopping quiz:', error);
                                                this.showNotification('error', 'Terjadi kesalahan');
                                            }
                                        }
                                    },

                                    async kickParticipant(participantId) {
                                            if (confirm('Keluarkan peserta ini dari ruangan?')) {
                                                try {
                                                    const url = KICK_PARTICIPANT_URL.replace('PARTICIPANT_ID',
                                                        participantId);
                                                    const response = await fetch(url, {
                                                        method: 'POST',
                                                        headers: {
                                                            'X-CSRF-TOKEN': CSRF_TOKEN,
                                                            'Accept': 'application/json'
                                                        }
                                                    });

                                                    const data = await response.json();

                                                    if (data.success) {
                                                        this.showNotification('success', data.message);
                                                        await this.loadRoomData();
                                                    }
                                                } catch (error) {
                                                    console.error('Error kicking participant:', error);
                                                    this.showNotification('error', 'Terjadi kesalahan');
                                                }
                                            }
                                        },

                                        async markParticipantAsReady(participantId) {
                                                try {
                                                    const url = MARK_PARTICIPANT_READY_URL.replace('PARTICIPANT_ID',
                                                        participantId);
                                                    const response = await fetch(url, {
                                                        method: 'POST',
                                                        headers: {
                                                            'X-CSRF-TOKEN': CSRF_TOKEN,
                                                            'Accept': 'application/json'
                                                        }
                                                    });

                                                    const data = await response.json();

                                                    if (data.success) {
                                                        this.showNotification('success', data.message);
                                                        await this.loadRoomData();
                                                    }
                                                } catch (error) {
                                                    console.error('Error marking participant as ready:', error);
                                                    this.showNotification('error', 'Terjadi kesalahan');
                                                }
                                            },

                                            // ✅ FIX: Load leaderboard dari endpoint guru yang sudah diperbaiki
                                            async loadLeaderboard() {
                                                    try {
                                                        const response = await fetch(LEADERBOARD_URL, {
                                                            headers: {
                                                                'Accept': 'application/json',
                                                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                                                'X-Requested-With': 'XMLHttpRequest'
                                                            }
                                                        });
                                                        const data = await response.json();
                                                        console.log('🏆 [GURU] Leaderboard API Response:', data);
                                                        if (data.success) {
                                                            this.leaderboard = data.leaderboard;
                                                            console.log('✅ [GURU] Leaderboard loaded:', this.leaderboard
                                                                .length, 'entries');
                                                            this.renderLeaderboardTable(this.leaderboard);
                                                        } else {
                                                            console.warn('⚠️ [GURU] Leaderboard not successful:', data
                                                                .message);
                                                            this.leaderboard = [];
                                                        }
                                                    } catch (error) {
                                                        console.error('❌ [GURU] Error loading leaderboard:', error);
                                                        this.leaderboard = [];
                                                    }
                                                },

                                                renderLeaderboardTable(leaderboard) {
                                                    const tbody = document.getElementById('leaderboard-table-body');
                                                    if (!tbody) return;

                                                    if (!leaderboard || leaderboard.length === 0) {
                                                        tbody.innerHTML =
                                                            '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Belum ada data leaderboard.</td></tr>';
                                                        return;
                                                    }

                                                    let html = '';
                                                    leaderboard.forEach((entry, index) => {
                                                        const rankClass = entry.rank === 1 ?
                                                            'bg-yellow-50 dark:bg-yellow-900/20' : '';
                                                        html += `<tr class="${rankClass}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    ${entry.rank === 1 ? '🥇' : entry.rank === 2 ? '🥈' : entry.rank === 3 ? '🥉' : entry.rank}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">${entry.student_name || entry.name || 'Unknown'}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">${(entry.score || 0).toFixed(2)}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">${this.formatTime(entry.time_taken || 0)}</td>
                            </tr>`;
                                                    });
                                                    tbody.innerHTML = html;
                                                },
                @endif

                @if ($isMurid)
                    async loadLeaderboard() {
                            try {
                                const response = await fetch(LEADERBOARD_URL, {
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': CSRF_TOKEN
                                    }
                                });
                                const data = await response.json();
                                console.log('🏆 [MURID] Leaderboard API Response:', data);
                                if (data.success) {
                                    this.leaderboard = data.leaderboard;
                                    console.log('✅ [MURID] Leaderboard loaded:', this.leaderboard.length, 'entries');
                                } else {
                                    console.warn('⚠️ [MURID] Leaderboard not successful:', data.message);
                                    this.leaderboard = [];
                                }
                            } catch (error) {
                                console.error('❌ [MURID] Error loading leaderboard:', error);
                                this.leaderboard = [];
                            }
                        },

                        async joinRoom() {
                                try {
                                    const response = await fetch(JOIN_ROOM_URL, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': CSRF_TOKEN,
                                            'Accept': 'application/json'
                                        }
                                    });

                                    const data = await response.json();

                                    if (data.success) {
                                        this.participantStatus = data.participant_status || 'waiting';
                                        this.showNotification('success', data.message);
                                        await this.loadRoomData();
                                    } else {
                                        this.showNotification('error', data.message || 'Gagal bergabung');
                                    }
                                } catch (error) {
                                    console.error('[JOIN] Error:', error);
                                    this.showNotification('error', 'Terjadi kesalahan saat bergabung');
                                }
                            },

                            async markAsReady() {
                                    try {
                                        const response = await fetch(MARK_READY_URL, {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                                'Accept': 'application/json'
                                            }
                                        });

                                        const data = await response.json();

                                        if (data.success) {
                                            this.participantStatus = 'ready';
                                            this.showNotification('success', data.message);
                                            await this.loadRoomData();
                                        } else {
                                            this.showNotification('error', data.message || 'Gagal mengubah status');
                                        }
                                    } catch (error) {
                                        console.error('[READY] Error:', error);
                                        this.showNotification('error', 'Terjadi kesalahan');
                                    }
                                },
                @endif

                refreshData() {
                    this.loadRoomData();
                    this.showNotification('info', 'Memperbarui data...');
                },

                showNotification(type, message) {
                    const container = document.getElementById('notification-container');
                    if (!container) return;

                    const oldNotifications = container.querySelectorAll('.notification');
                    oldNotifications.forEach(notif => notif.remove());

                    const notification = document.createElement('div');
                    notification.className = `notification ${type}`;
                    notification.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <i class="fas ${
                                    type === 'success' ? 'fa-check-circle' :
                                    type === 'error' ? 'fa-exclamation-circle' :
                                    type === 'warning' ? 'fa-exclamation-triangle' :
                                    'fa-info-circle'
                                }"></i>
                                <span>${message}</span>
                            </div>
                            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 hover:opacity-80">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;

                    container.appendChild(notification);
                    setTimeout(() => {
                        if (notification.parentElement) {
                            notification.style.opacity = '0';
                            notification.style.transform = 'translateX(100%)';
                            setTimeout(() => notification.remove(), 300);
                        }
                    }, 5000);
                }
            };
        }
    </script>
    @if (session('error'))
        <script>
            window.addEventListener('load', function() {
                const container = document.getElementById('notification-container');
                if (container) {
                    const notification = document.createElement('div');
                    notification.className = 'notification error';
                    notification.innerHTML = `
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ session('error') }}</span>
                            </div>
                            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 hover:opacity-80">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    container.appendChild(notification);
                    setTimeout(() => notification.remove(), 5000);
                }
            });
        </script>
    @endif
</body>

</html>
