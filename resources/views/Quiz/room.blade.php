@php
    $isGuru = auth()->user()->hasRole('Guru');
    $isMurid = auth()->user()->hasRole('Murid');
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
            --primary: #4F46E5;
            --primary-dark: #4338CA;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .room-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .participant-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .participant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.15);
        }

        .action-button {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .action-button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.3);
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

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-waiting {
            background-color: #FBBF24;
        }

        .status-ready {
            background-color: #10B981;
        }

        .status-started {
            background-color: #3B82F6;
        }

        .status-submitted {
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

        .animate-fadeIn {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .animate-pulse {
            animation: pulse 2s infinite;
        }
    </style>
</head>

<body>
    <div class="min-h-screen p-4 md:p-6">
        <div class="max-w-7xl mx-auto">

            <!-- Header Card -->
            <div class="room-container p-6 mb-6 animate-fadeIn">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-6">
                    <div class="flex-1">
                        <div class="flex items-center space-x-4 mb-4">
                            <div
                                class="w-16 h-16 bg-gradient-to-r from-purple-500 to-blue-500 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-graduation-cap text-white text-2xl"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-1">{{ $quiz->title }}</h1>
                                <div class="flex flex-wrap gap-2">
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                        <i class="fas fa-book mr-1"></i> {{ $quiz->subject->name_subject }}
                                    </span>
                                    <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm">
                                        <i class="fas fa-users mr-1"></i> {{ $quiz->class->name_class }}
                                    </span>
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                                        <i class="fas fa-question-circle mr-1"></i> {{ $quiz->questions()->count() }}
                                        Soal
                                    </span>
                                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm">
                                        <i class="fas fa-clock mr-1"></i> {{ $quiz->duration }} Menit
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
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex flex-wrap gap-3">
                        @if ($isGuru)
                            <!-- Teacher Actions -->
                            <button @click="openRoom()" x-show="!roomOpen" class="action-button">
                                <i class="fas fa-door-open mr-2"></i> Buka Ruangan
                            </button>

                            <button @click="startQuiz()" x-show="roomOpen && !quizStarted" :disabled="!canStartQuiz"
                                :class="canStartQuiz ? 'action-button success' : 'action-button secondary'"
                                :title="canStartQuiz ? 'Klik untuk memulai quiz' : 'Tunggu minimal 1 siswa siap'">
                                <i class="fas fa-play mr-2"></i>
                                <span
                                    x-text="canStartQuiz ? 'Mulai Quiz (' + stats.ready + ' Siap)' : 'Tunggu Siswa Siap'"></span>
                            </button>

                            <button @click="stopQuiz()" x-show="quizStarted" class="action-button warning">
                                <i class="fas fa-stop mr-2"></i> Hentikan Quiz
                            </button>

                            <button @click="closeRoom()" x-show="roomOpen && !quizStarted" class="action-button danger">
                                <i class="fas fa-door-closed mr-2"></i> Tutup Ruangan
                            </button>

                            <button @click="refreshData()" class="action-button secondary">
                                <i class="fas fa-sync-alt mr-2"></i> Refresh
                            </button>

                            <a href="{{ route('guru.quiz.index') }}" class="action-button secondary">
                                <i class="fas fa-arrow-left mr-2"></i> Kembali
                            </a>
                        @else
                            <!-- Student Actions -->
                            <button @click="joinRoom()" x-show="roomOpen && !isJoined && !quizStarted"
                                class="action-button">
                                <i class="fas fa-sign-in-alt mr-2"></i> Bergabung ke Ruangan
                            </button>

                            <button @click="markAsReady()"
                                x-show="isJoined && participantStatus === 'waiting' && !quizStarted"
                                class="action-button success">
                                <i class="fas fa-check-circle mr-2"></i> Saya Sudah Siap
                            </button>

                            <button @click="refreshData()" class="action-button secondary">
                                <i class="fas fa-sync-alt mr-2"></i> Refresh
                            </button>

                            <a href="{{ route('quiz.index') }}" class="action-button secondary">
                                <i class="fas fa-arrow-left mr-2"></i> Kembali
                            </a>

                            <!-- Status Messages -->
                            <div x-show="!roomOpen" class="flex items-center text-yellow-600 ml-2">
                                <i class="fas fa-clock mr-2"></i>
                                <span>Menunggu guru membuka ruangan...</span>
                            </div>

                            <div x-show="roomOpen && isJoined && participantStatus === 'ready' && !quizStarted"
                                class="flex items-center text-green-600 ml-2">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span>Siap! Tunggu guru memulai quiz...</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Auto Redirect Notice for Students -->
                @if ($isMurid)
                    <div x-show="shouldRedirect"
                        class="mt-4 p-4 bg-gradient-to-r from-blue-100 to-blue-200 rounded-xl border border-blue-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-rocket text-blue-600 text-xl"></i>
                                <div>
                                    <div class="font-bold text-blue-800">Quiz dimulai!</div>
                                    <div class="text-sm text-blue-600" x-show="redirectCountdown > 0">
                                        Anda akan diarahkan dalam <span x-text="redirectCountdown"></span> detik
                                    </div>
                                </div>
                            </div>
                            <button @click="redirectNow()" class="action-button px-4 py-2">
                                <i class="fas fa-external-link-alt mr-2"></i> Masuk Sekarang
                            </button>
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
                            <div class="text-2xl font-bold" x-text="stats.joined"></div>
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
                            <div class="text-2xl font-bold" x-text="stats.ready"></div>
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
                            <div class="text-2xl font-bold" x-text="stats.started"></div>
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
                            <div class="text-2xl font-bold" x-text="stats.total"></div>
                            <div class="text-sm text-gray-600">Total Siswa</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Participants Section -->
            <div class="room-container p-6 animate-fadeIn">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">
                            <i class="fas fa-users mr-2 text-purple-600"></i>
                            Daftar Peserta
                            <span class="text-sm font-normal text-gray-500 ml-2">
                                (<span x-text="stats.joined"></span> dari <span x-text="stats.total"></span> siswa)
                            </span>
                        </h2>
                        <p class="text-gray-600 text-sm">Update otomatis setiap 3 detik</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-500" x-text="lastUpdatedText"></div>
                    </div>
                </div>

                <!-- Participants Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-if="participants.length === 0">
                        <div class="col-span-full text-center py-12">
                            <div
                                class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                                <i class="fas fa-users text-gray-400 text-2xl"></i>
                            </div>
                            <p class="text-gray-500">Belum ada peserta yang bergabung</p>
                        </div>
                    </template>

                    <template x-for="participant in participants" :key="participant.id">
                        <div class="participant-card p-4">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                                        <!-- Gunakan initial yang sudah dikirim dari backend -->
                                        <span
                                            x-text="participant.initial || (participant.name ? participant.name.charAt(0).toUpperCase() : '?')"></span>
                                    </div>
                                    <div>
                                        <!-- Pastikan nama ditampilkan -->
                                        <div class="font-semibold" x-text="participant.name || 'Unknown'"></div>
                                        <div class="text-xs text-gray-500" x-text="participant.email || ''"></div>
                                    </div>
                                </div>
                                <div :class="{
                                    'status-waiting': participant.status === 'waiting',
                                    'status-ready': participant.status === 'ready',
                                    'status-started': participant.status === 'started',
                                    'status-submitted': participant.status === 'submitted'
                                }"
                                    class="status-indicator"></div>
                            </div>

                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">
                                    <i class="fas fa-clock mr-1"></i>
                                    <span x-text="participant.joined_time || '-'"></span>
                                </span>
                                <span
                                    :class="{
                                        'text-yellow-600': participant.status === 'waiting',
                                        'text-green-600': participant.status === 'ready',
                                        'text-blue-600': participant.status === 'started',
                                        'text-purple-600': participant.status === 'submitted'
                                    }"
                                    class="font-medium capitalize" x-text="getStatusText(participant.status)"></span>
                            </div>

                            @if ($isGuru)
                                <div class="mt-3 pt-3 border-t border-gray-200 flex gap-2">
                                    <button @click="markParticipantAsReady(participant.id)"
                                        x-show="participant.status === 'waiting'"
                                        class="flex-1 text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200">
                                        <i class="fas fa-check mr-1"></i> Siapkan
                                    </button>
                                    <button @click="kickParticipant(participant.id)"
                                        class="flex-1 text-xs bg-red-100 text-red-700 px-2 py-1 rounded hover:bg-red-200">
                                        <i class="fas fa-times mr-1"></i> Keluarkan
                                    </button>
                                </div>
                            @endif
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        // PENTING: Route URL berbeda untuk Guru dan Murid
        @if ($isGuru)
            const ROOM_STATUS_URL = '{{ route('guru.quiz.room.status', $quiz->id) }}';
            const OPEN_ROOM_URL = '{{ route('guru.quiz.room.open', $quiz->id) }}';
            const CLOSE_ROOM_URL = '{{ route('guru.quiz.room.close', $quiz->id) }}';
            const START_QUIZ_URL = '{{ route('guru.quiz.room.start', $quiz->id) }}';
            const STOP_QUIZ_URL = '{{ route('guru.quiz.room.stop', $quiz->id) }}';
            const KICK_PARTICIPANT_URL = '{{ route('guru.quiz.room.kick', [$quiz->id, '']) }}';
            const MARK_PARTICIPANT_READY_URL = '{{ route('guru.quiz.room.mark-ready', [$quiz->id, '']) }}';
        @else
            const ROOM_STATUS_URL = '{{ route('quiz.room.status', $quiz->id) }}';
            const JOIN_ROOM_URL = '{{ route('quiz.join-room', $quiz->id) }}';
            const MARK_READY_URL = '{{ route('quiz.room.mark-ready', $quiz->id) }}';
        @endif

        function roomData() {
            return {
                isDarkMode: false,
                roomOpen: {{ $quiz->is_room_open ? 'true' : 'false' }},
                quizStarted: {{ $quiz->is_quiz_started ? 'true' : 'false' }},
                participantStatus: '{{ $participant->status ?? 'waiting' }}',
                canStartQuiz: false,
                lastUpdated: null,
                timeRemainingText: '--:--',
                redirectCountdown: 5,
                redirectTimer: null,
                redirected: false,
                autoRefreshTimer: null,
                participants: [],
                stats: {
                    total: 0,
                    joined: 0,
                    ready: 0,
                    started: 0,
                    submitted: 0
                },

                get isJoined() {
                    return this.participantStatus !== 'waiting' ||
                        {{ isset($participant) && $participant ? 'true' : 'false' }};
                },

                get roomStatusText() {
                    if (this.quizStarted) return 'Quiz Sedang Berlangsung';
                    if (this.roomOpen) return 'Ruangan Terbuka';
                    return 'Ruangan Tertutup';
                },

                get lastUpdatedText() {
                    if (!this.lastUpdated) return '';
                    const now = new Date();
                    const diffMs = now - this.lastUpdated;
                    const diffSecs = Math.floor(diffMs / 1000);
                    if (diffSecs < 60) return `${diffSecs} detik lalu`;
                    return `${Math.floor(diffSecs / 60)} menit lalu`;
                },

                get shouldRedirect() {
                    @if ($isMurid)
                        // Hanya redirect jika quiz dimulai DAN participant status adalah 'started'
                        return this.quizStarted &&
                            (this.participantStatus === 'started') &&
                            !this.redirected;
                    @else
                        return false;
                    @endif
                },

                init() {
                    console.log('Room initialized');
                    this.loadRoomData();
                    this.setupAutoRefresh();

                    @if ($isMurid)
                        // Auto join jika room terbuka
                        if (this.roomOpen && !this.isJoined && !this.quizStarted) {
                            setTimeout(() => this.joinRoom(), 1000);
                        }

                        // Check if already should redirect
                        if (this.shouldRedirect) {
                            this.startRedirectCountdown();
                        }
                    @endif
                },

                async loadRoomData() {
                    try {
                        const response = await fetch(ROOM_STATUS_URL, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!response.ok) throw new Error('Network error');
                        const data = await response.json();

                        if (data.success) {
                            this.updateFromResponse(data);
                        }
                    } catch (error) {
                        console.error('Error loading room data:', error);
                    }
                },

                updateFromResponse(data) {
                    this.roomOpen = data.is_room_open || false;
                    this.quizStarted = data.is_quiz_started || false;

                    // Pastikan participants diupdate dengan data dari backend
                    this.participants = data.participants || [];

                    // Update stats
                    if (data.stats) {
                        this.stats.total = data.stats.total_students || 0;
                        this.stats.joined = data.stats.joined || 0;
                        this.stats.ready = data.stats.ready || 0;
                        this.stats.started = data.stats.started || 0;
                        this.stats.submitted = data.stats.submitted || 0;
                    }

                    // Update participant status
                    if (data.participant) {
                        this.participantStatus = data.participant.status || 'waiting';
                    }

                    // Update waktu
                    this.timeRemainingText = data.time_remaining ? this.formatTime(data.time_remaining) : '--:--';

                    // Cek apakah bisa start quiz (untuk guru)
                    this.canStartQuiz = this.stats.ready > 0;

                    this.lastUpdated = new Date();

                    // Hanya redirect jika diperlukan dan belum redirect sebelumnya
                    if (data.should_redirect && !this.redirected) {
                        this.startRedirectCountdown();
                    }
                }

                setupAutoRefresh() {
                    if (this.autoRefreshTimer) clearInterval(this.autoRefreshTimer);
                    this.autoRefreshTimer = setInterval(() => {
                        if (!this.redirected) {
                            this.loadRoomData();
                        }
                    }, 3000);
                },

                formatTime(seconds) {
                    if (!seconds) return '00:00';
                    const minutes = Math.floor(seconds / 60);
                    const secs = seconds % 60;
                    return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                },

                getStatusText(status) {
                    const texts = {
                        waiting: 'Menunggu',
                        ready: 'Siap',
                        started: 'Mengerjakan',
                        submitted: 'Selesai'
                    };
                    return texts[status] || 'Tidak diketahui';
                },

                @if ($isGuru)
                    async openRoom() {
                            if (confirm('Buka ruangan quiz? Siswa akan bisa bergabung.')) {
                                try {
                                    const response = await fetch(OPEN_ROOM_URL, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        }
                                    });
                                    const data = await response.json();
                                    if (data.success) {
                                        this.roomOpen = true;
                                        this.showNotification('success', data.message);
                                        this.loadRoomData();
                                    } else {
                                        this.showNotification('error', data.message);
                                    }
                                } catch (error) {
                                    console.error('Error:', error);
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
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Accept': 'application/json'
                                            }
                                        });
                                        const data = await response.json();
                                        if (data.success) {
                                            this.roomOpen = false;
                                            this.showNotification('success', data.message);
                                            this.loadRoomData();
                                        }
                                    } catch (error) {
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
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                }
                                            });
                                            const data = await response.json();
                                            if (data.success) {
                                                this.quizStarted = true;
                                                this.showNotification('success', data.message);
                                                this.loadRoomData();
                                            } else {
                                                this.showNotification('error', data.message);
                                            }
                                        } catch (error) {
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
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                        'Accept': 'application/json'
                                                    }
                                                });
                                                const data = await response.json();
                                                if (data.success) {
                                                    this.quizStarted = false;
                                                    this.showNotification('success', data.message);
                                                    this.loadRoomData();
                                                }
                                            } catch (error) {
                                                this.showNotification('error', 'Terjadi kesalahan');
                                            }
                                        }
                                    },

                                    async kickParticipant(participantId) {
                                            if (confirm('Keluarkan peserta ini dari ruangan?')) {
                                                try {
                                                    const response = await fetch(
                                                        `${KICK_PARTICIPANT_URL}${participantId}`, {
                                                            method: 'POST',
                                                            headers: {
                                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                                'Accept': 'application/json'
                                                            }
                                                        });
                                                    const data = await response.json();
                                                    if (data.success) {
                                                        this.showNotification('success', data.message);
                                                        this.loadRoomData();
                                                    }
                                                } catch (error) {
                                                    this.showNotification('error', 'Terjadi kesalahan');
                                                }
                                            }
                                        },

                                        async markParticipantAsReady(participantId) {
                                                try {
                                                    const response = await fetch(
                                                        `${MARK_PARTICIPANT_READY_URL}${participantId}`, {
                                                            method: 'POST',
                                                            headers: {
                                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                                'Accept': 'application/json'
                                                            }
                                                        });
                                                    const data = await response.json();
                                                    if (data.success) {
                                                        this.showNotification('success', data.message);
                                                        this.loadRoomData();
                                                    }
                                                } catch (error) {
                                                    this.showNotification('error', 'Terjadi kesalahan');
                                                }
                                            },
                @endif

                @if ($isMurid)
                    async joinRoom() {
                            try {
                                const response = await fetch(JOIN_ROOM_URL, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                });
                                const data = await response.json();
                                if (data.success) {
                                    this.participantStatus = 'waiting';
                                    this.showNotification('success', data.message);
                                    this.loadRoomData();
                                } else {
                                    this.showNotification('error', data.message);
                                }
                            } catch (error) {
                                this.showNotification('error', 'Terjadi kesalahan');
                            }
                        },

                        async markAsReady() {
                                try {
                                    const response = await fetch(MARK_READY_URL, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        }
                                    });
                                    const data = await response.json();
                                    if (data.success) {
                                        this.participantStatus = 'ready';
                                        this.showNotification('success', data.message);
                                        this.loadRoomData();
                                    }
                                } catch (error) {
                                    this.showNotification('error', 'Terjadi kesalahan');
                                }
                            },

                            startRedirectCountdown() {
                                if (this.redirectTimer) clearInterval(this.redirectTimer);

                                // Hanya start countdown jika benar-benar harus redirect
                                if (this.shouldRedirect) {
                                    this.redirectCountdown = 3; // Kurangi waktu menjadi 3 detik
                                    this.redirectTimer = setInterval(() => {
                                        this.redirectCountdown--;
                                        if (this.redirectCountdown <= 0) {
                                            clearInterval(this.redirectTimer);
                                            this.redirectToQuiz();
                                        }
                                    }, 1000);
                                }
                            },

                            redirectToQuiz() {
                                if (!this.redirected && this.shouldRedirect) {
                                    this.redirected = true;
                                    clearInterval(this.redirectTimer);

                                    // Gunakan URL yang benar
                                    window.location.href = '{{ route('quiz.play', $quiz->id) }}';
                                }
                            },

                            redirectNow() {
                                this.redirectToQuiz();
                            },
                @endif

                refreshData() {
                    this.loadRoomData();
                    this.showNotification('info', 'Memperbarui data...');
                },

                showNotification(type, message) {
                    const notification = document.createElement('div');
                    notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg ${
                        type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' :
                        type === 'error' ? 'bg-red-100 text-red-800 border border-red-200' :
                        type === 'warning' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' :
                        'bg-blue-100 text-blue-800 border border-blue-200'
                    }`;
                    notification.innerHTML = `
                        <div class="flex items-center space-x-2">
                            <i class="fas ${
                                type === 'success' ? 'fa-check-circle' :
                                type === 'error' ? 'fa-exclamation-circle' :
                                type === 'warning' ? 'fa-exclamation-triangle' :
                                'fa-info-circle'
                            }"></i>
                            <span>${message}</span>
                        </div>
                    `;
                    document.body.appendChild(notification);
                    setTimeout(() => {
                        notification.style.opacity = '0';
                        setTimeout(() => notification.remove(), 300);
                    }, 3000);
                }
            };
        }
    </script>
</body>

</html>
