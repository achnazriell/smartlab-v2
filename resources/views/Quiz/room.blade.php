@php
    $isGuru = auth()->user()->hasRole('Guru');
    $isMurid = auth()->user()->hasRole('Murid');
    $participant = null;
    if ($isMurid) {
        $quiz->load(['activeSession.participants' => function ($query) {
            $query->where('student_id', auth()->id());
        }]);
        if ($quiz->activeSession) {
            $participant = $quiz->activeSession->participants->first();
        }
    }
@endphp
<!DOCTYPE html>
<html lang="id" x-data="roomApp()" x-init="init()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quiz->title }} — Ruang Quiz</title>
    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Nunito', sans-serif;
            background: #F4F4FE;
            min-height: 100vh;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            background: linear-gradient(135deg, #6C3DE5 0%, #8B5CF6 100%);
            color: white;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 4px 20px rgba(108,61,229,0.35);
        }

        .topbar-left h1 {
            font-size: 1.15rem;
            font-weight: 900;
            letter-spacing: -0.5px;
        }

        .topbar-left p {
            font-size: 0.78rem;
            opacity: 0.85;
        }

        .room-code-badge {
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 14px;
            padding: 0.5rem 1.25rem;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .room-code-badge .code {
            font-size: 1.6rem;
            font-weight: 900;
            letter-spacing: 4px;
            color: #FDE68A;
            display: block;
            font-family: 'Courier New', monospace;
        }

        .room-code-badge .label {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.8;
        }

        /* ===== STATUS INDICATOR ===== */
        .status-bar {
            background: white;
            border-bottom: 2px solid #F3F4F6;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .room-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            animation: blink 1.5s infinite;
        }

        @keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }

        .status-dot.closed { background: #9CA3AF; animation: none; }
        .status-dot.open { background: #10B981; }
        .status-dot.started { background: #EF4444; }

        /* ===== STAT CARDS ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            padding: 1rem 1.5rem;
        }

        @media (max-width: 768px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            border: 1.5px solid #F3F4F6;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .stat-icon.blue { background: #EFF6FF; color: #2563EB; }
        .stat-icon.green { background: #ECFDF5; color: #059669; }
        .stat-icon.purple { background: #F5F3FF; color: #7C3AED; }
        .stat-icon.yellow { background: #FFFBEB; color: #D97706; }

        .stat-value { font-size: 1.75rem; font-weight: 900; color: #111827; line-height: 1; }
        .stat-label { font-size: 0.75rem; color: #6B7280; font-weight: 600; margin-top: 2px; }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            padding: 0 1.5rem 2rem;
        }

        /* ===== ACTION BUTTONS ===== */
        .actions-bar {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
            padding: 1rem 1.5rem;
            background: white;
            border-radius: 16px;
            margin: 0 1.5rem 1rem;
            border: 1.5px solid #F3F4F6;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.65rem 1.25rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-action:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
        .btn-action:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        .btn-open { background: #6C3DE5; color: white; }
        .btn-open:hover:not(:disabled) { background: #5B21B6; }
        .btn-start { background: #10B981; color: white; }
        .btn-start:hover:not(:disabled) { background: #059669; }
        .btn-stop { background: #EF4444; color: white; }
        .btn-stop:hover:not(:disabled) { background: #DC2626; }
        .btn-close-room { background: #F59E0B; color: white; }
        .btn-secondary { background: #F3F4F6; color: #374151; }
        .btn-secondary:hover:not(:disabled) { background: #E5E7EB; }
        .btn-ready { background: #10B981; color: white; }
        .btn-join { background: #6C3DE5; color: white; }
        .btn-back { background: #6B7280; color: white; }

        /* ===== TABS ===== */
        .tabs-container {
            display: flex;
            border-bottom: 2px solid #E5E7EB;
            margin-bottom: 1rem;
        }

        .tab-btn {
            padding: 0.75rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 700;
            color: #6B7280;
            border: none;
            background: none;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all 0.2s;
        }

        .tab-btn.active { color: #6C3DE5; border-bottom-color: #6C3DE5; }

        /* ===== PARTICIPANT GRID ===== */
        .participants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }

        @media (max-width: 640px) {
            .participants-grid { grid-template-columns: 1fr 1fr; }
        }

        .p-card {
            background: white;
            border-radius: 16px;
            padding: 1rem;
            border: 2px solid #F3F4F6;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            position: relative;
        }

        .p-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }

        .p-card.violation-card {
            border-color: #FCA5A5;
            background: linear-gradient(135deg, #FEF2F2, #FECACA);
            animation: pulseViolation 2s infinite;
        }

        @keyframes pulseViolation {
            0%,100% { box-shadow: 0 2px 10px rgba(239,68,68,0.2); }
            50% { box-shadow: 0 4px 20px rgba(239,68,68,0.5); }
        }

        .p-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6C3DE5, #8B5CF6);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 900;
            flex-shrink: 0;
        }

        .p-status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .p-status-dot.waiting { background: #F59E0B; animation: blink 1.5s infinite; }
        .p-status-dot.ready { background: #10B981; animation: blink 1.5s infinite; }
        .p-status-dot.started { background: #3B82F6; animation: blink 0.8s infinite; }
        .p-status-dot.submitted { background: #8B5CF6; }

        .violation-badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            background: #EF4444;
            color: white;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 999px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .status-pill.waiting { background: #FEF3C7; color: #92400E; }
        .status-pill.ready { background: #D1FAE5; color: #065F46; }
        .status-pill.started { background: #DBEAFE; color: #1E40AF; }
        .status-pill.submitted { background: #EDE9FE; color: #5B21B6; }

        .p-actions { display: flex; gap: 0.4rem; margin-top: 0.75rem; }

        .p-btn {
            flex: 1;
            padding: 0.35rem 0.5rem;
            border-radius: 8px;
            font-size: 0.72rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .p-btn.ready-btn { background: #D1FAE5; color: #065F46; }
        .p-btn.ready-btn:hover { background: #A7F3D0; }
        .p-btn.kick-btn { background: #FEE2E2; color: #991B1B; }
        .p-btn.kick-btn:hover { background: #FECACA; }

        /* ===== EMPTY STATE ===== */
        .empty-participants {
            grid-column: 1 / -1;
            text-align: center;
            padding: 4rem 2rem;
            color: #6B7280;
        }

        .empty-participants .empty-icon {
            width: 80px;
            height: 80px;
            background: #F3F4F6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
        }

        /* ===== LEADERBOARD ===== */
        .lb-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.875rem 1rem;
            border-radius: 12px;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
        }

        .lb-row:hover { transform: translateX(4px); }
        .lb-row.rank-1 { background: linear-gradient(135deg, #FEF3C7, #FDE68A); border: 2px solid #F59E0B; }
        .lb-row.rank-2 { background: linear-gradient(135deg, #F3F4F6, #E5E7EB); border: 2px solid #9CA3AF; }
        .lb-row.rank-3 { background: linear-gradient(135deg, #FEF3C7, #FDE68A); border: 2px solid #D97706; opacity: 0.8; }
        .lb-row.rank-other { background: white; border: 1.5px solid #F3F4F6; }

        .lb-rank {
            font-size: 1.25rem;
            width: 36px;
            text-align: center;
            font-weight: 900;
        }

        /* ===== VIOLATION BAR ===== */
        .violation-bar {
            background: linear-gradient(135deg, #FEE2E2, #FECACA);
            border: 1.5px solid #FCA5A5;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 700;
            color: #991B1B;
        }

        /* ===== WAITING ROOM (SISWA) ===== */
        .waiting-room {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(108,61,229,0.1);
            border: 2px solid #EDE9FE;
            max-width: 500px;
            margin: 0 auto;
        }

        .pulse-ring {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6C3DE5, #8B5CF6);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            position: relative;
            font-size: 2rem;
        }

        .pulse-ring::after {
            content: '';
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            border: 3px solid #6C3DE5;
            animation: ringPulse 2s infinite;
        }

        @keyframes ringPulse {
            0% { opacity: 0.8; transform: scale(1); }
            100% { opacity: 0; transform: scale(1.5); }
        }

        /* ===== QUIZ STARTED BANNER (SISWA) ===== */
        .quiz-started-banner {
            background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
            border: 2px solid #10B981;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        /* ===== NOTIFICATION ===== */
        #notif-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            width: 320px;
        }

        .notif {
            padding: 0.9rem 1.1rem;
            border-radius: 12px;
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            animation: notifIn 0.3s cubic-bezier(0.34,1.56,0.64,1);
        }

        @keyframes notifIn {
            from { opacity: 0; transform: translateX(60px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .notif.success { background: #10B981; }
        .notif.error { background: #EF4444; }
        .notif.warning { background: #F59E0B; }
        .notif.info { background: #6C3DE5; }

        [x-cloak] { display: none !important; }

        .card {
            background: white;
            border-radius: 16px;
            border: 1.5px solid #F3F4F6;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        }

        .progress-bar {
            height: 6px;
            background: #EDE9FE;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #6C3DE5, #A78BFA);
            border-radius: 999px;
            transition: width 0.5s ease;
        }
    </style>
</head>
<body>

{{-- ===== TOPBAR ===== --}}
<div class="topbar">
    <div class="topbar-left">
        <h1><i class="fas fa-gamepad mr-2" style="color:#FDE68A"></i>{{ Str::limit($quiz->title, 40) }}</h1>
        <p>{{ $quiz->subject->name_subject ?? '' }} • Kelas {{ $quiz->class->name_class ?? '' }}
           • {{ $quiz->questions->count() }} Soal • {{ $quiz->time_per_question ?? 0 }}s/soal</p>
    </div>

    {{-- Room Code --}}
    @if($quiz->activeSession && $quiz->is_room_open)
    <div class="room-code-badge">
        <span class="label">Kode Ruangan</span>
        <span class="code">{{ $quiz->activeSession->session_code ?? '------' }}</span>
    </div>
    @endif

    {{-- User badge --}}
    <div style="display:flex;align-items:center;gap:0.75rem">
        <div x-show="quizStarted" class="text-sm font-bold" style="background:rgba(255,255,255,0.2);padding:0.4rem 0.9rem;border-radius:8px">
            <i class="fas fa-stopwatch mr-1"></i> <span x-text="timeRemainingText"></span>
        </div>
        <div style="background:rgba(255,255,255,0.2);padding:0.5rem 1rem;border-radius:10px;font-weight:800;font-size:0.85rem">
            <i class="fas {{ $isGuru ? 'fa-chalkboard-teacher' : 'fa-user-graduate' }} mr-1"></i>{{ $isGuru ? 'Guru' : 'Siswa' }}
        </div>
        <button onclick="document.querySelector('#notif-container').innerHTML=''"
            style="background:rgba(255,255,255,0.15);border:none;color:white;padding:0.5rem 0.75rem;border-radius:8px;cursor:pointer;font-size:0.8rem">
            <i class="fas fa-bell"></i>
        </button>
    </div>
</div>

{{-- ===== STATUS BAR ===== --}}
<div class="status-bar">
    <div class="room-status">
        <div class="status-dot"
            :class="quizStarted ? 'started' : (roomOpen ? 'open' : 'closed')"></div>
        <span x-text="roomOpen ? (quizStarted ? 'Quiz Sedang Berlangsung' : 'Ruangan Terbuka') : 'Ruangan Tertutup'"></span>
    </div>

    <div class="progress-bar" style="flex:1;max-width:250px" x-show="quizStarted">
        <div class="progress-fill" :style="`width: ${Math.min((stats.submitted / Math.max(stats.joined,1)) * 100, 100)}%`"></div>
    </div>
    <span class="text-sm text-gray-500" x-show="quizStarted">
        <span x-text="stats.submitted"></span>/<span x-text="stats.joined"></span> selesai
    </span>

    <div class="ml-auto text-sm text-gray-400" x-text="lastUpdatedText ? 'Update: ' + lastUpdatedText : ''"></div>
</div>

{{-- ===== ACTION BUTTONS ===== --}}
<div class="actions-bar">
    @if($isGuru)
        <button @click="openRoom()" x-show="!roomOpen" class="btn-action btn-open">
            <i class="fas fa-door-open"></i> Buka Ruangan
        </button>

        <button @click="startQuiz()" x-show="roomOpen && !quizStarted"
            :disabled="stats.ready === 0"
            :class="stats.ready > 0 ? 'btn-start' : 'btn-secondary'"
            class="btn-action">
            <i class="fas fa-play"></i>
            <span x-text="stats.ready > 0 ? 'Mulai Quiz (' + stats.ready + ' siap)' : 'Tunggu Siswa Siap'"></span>
        </button>

        <button @click="closeRoom()" x-show="roomOpen && !quizStarted" class="btn-action btn-close-room">
            <i class="fas fa-door-closed"></i> Tutup Ruangan
        </button>

        <button @click="stopQuiz()" x-show="quizStarted" class="btn-action btn-stop">
            <i class="fas fa-stop-circle"></i> Hentikan Quiz
        </button>

        <button @click="loadRoomData(); showNotif('info', 'Data diperbarui!')" class="btn-action btn-secondary">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>

        <a href="{{ route('guru.quiz.index') }}" class="btn-action btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>

        @if($quiz->status === 'finished')
        <a href="{{ route('guru.quiz.results', $quiz->id) }}" class="btn-action" style="background:#8B5CF6;color:white">
            <i class="fas fa-chart-bar"></i> Hasil Quiz
        </a>
        @endif
    @else
        {{-- SISWA ACTIONS --}}
        <button @click="joinRoom()" x-show="!isJoined && roomOpen && !quizStarted" class="btn-action btn-join">
            <i class="fas fa-sign-in-alt"></i> Bergabung ke Ruangan
        </button>

        <button @click="markAsReady()" x-show="isJoined && participantStatus === 'waiting' && !quizStarted"
            class="btn-action btn-ready">
            <i class="fas fa-check-circle"></i> Saya Siap!
        </button>

        <button @click="loadRoomData()" class="btn-action btn-secondary">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>

        <a href="{{ route('quiz.index') }}" class="btn-action btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>

        {{-- Status messages siswa --}}
        <div x-show="!roomOpen" class="flex items-center gap-2 text-yellow-600 text-sm font-semibold">
            <i class="fas fa-clock animate-pulse"></i> Tunggu guru membuka ruangan...
        </div>

        <div x-show="isJoined && participantStatus === 'ready' && !quizStarted"
            class="flex items-center gap-2 text-green-600 text-sm font-semibold">
            <i class="fas fa-check-circle"></i> Kamu sudah siap! Tunggu quiz dimulai...
        </div>
    @endif
</div>

{{-- ===== QUIZ STARTED BANNER UNTUK SISWA ===== --}}
@if($isMurid)
<div class="quiz-started-banner mx-6" x-show="quizStarted && (participantStatus === 'started' || participantStatus === 'ready')" x-cloak>
    <div class="flex items-center gap-3">
        <div style="font-size:2rem"><i class="fas fa-play-circle" style="color:#065F46"></i></div>
        <div>
            <div class="font-bold text-green-800 text-base">Quiz telah dimulai!</div>
            <div class="text-sm text-green-700">Klik tombol untuk mulai mengerjakan soal.</div>
        </div>
    </div>
    <a :href="PLAY_QUIZ_URL" class="btn-action btn-start" style="white-space:nowrap">
        <i class="fas fa-play"></i> Mulai Sekarang!
    </a>
</div>
@endif

{{-- ===== STATS CARDS ===== --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div>
            <div class="stat-value" x-text="stats.joined">0</div>
            <div class="stat-label">Bergabung</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div>
            <div class="stat-value" x-text="stats.ready">0</div>
            <div class="stat-label">Siap</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-pencil-alt"></i></div>
        <div>
            <div class="stat-value" x-text="stats.started">0</div>
            <div class="stat-label">Mengerjakan</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-flag-checkered"></i></div>
        <div>
            <div class="stat-value" x-text="stats.submitted">0</div>
            <div class="stat-label">Selesai</div>
        </div>
    </div>
</div>

{{-- ===== MAIN CONTENT ===== --}}
<div class="main-content">

    {{-- VIOLATION BAR --}}
    <div id="violation-bar" class="violation-bar hidden">
        <i class="fas fa-exclamation-triangle"></i>
        Total <span id="viol-count" class="text-red-900">0</span> pelanggaran dari
        <span id="viol-users" class="text-red-900">0</span> peserta terdeteksi
    </div>

    {{-- WAITING ROOM UNTUK SISWA (jika belum ada yang mulai) --}}
    @if($isMurid)
    <div x-show="!quizStarted" x-cloak class="mb-6">
        <div class="waiting-room">
            <div class="pulse-ring"><i class="fas fa-gamepad" style="color:white;font-size:2rem"></i></div>
            <h2 class="text-xl font-black text-gray-900 mb-2" x-text="roomOpen ? 'Ruangan Terbuka!' : 'Menunggu Guru...'"></h2>
            <p class="text-gray-500 text-sm mb-4"
                x-text="roomOpen ? (isJoined ? 'Kamu sudah bergabung. ' + (participantStatus === \'ready\' ? \'Tunggu quiz dimulai!\' : \'Klik Saya Siap saat kamu siap!\') : \'Klik Bergabung untuk masuk ruangan.\'): \'Guru akan segera membuka ruangan quiz.\'"
            ></p>

            @if($quiz->activeSession)
            <div style="background:#F5F3FF;border-radius:12px;padding:1rem;margin-bottom:1rem">
                <p class="text-xs text-purple-600 font-bold mb-1">KODE BERGABUNG</p>
                <p class="text-2xl font-black text-purple-700 letter-spacing-4" style="letter-spacing:4px;font-family:'Courier New'">
                    {{ $quiz->activeSession->session_code ?? '' }}
                </p>
            </div>
            @endif

            <div class="flex justify-center gap-4 text-sm text-gray-500">
                <div class="flex items-center gap-1.5">
                    <div class="p-status-dot" :class="isJoined ? 'ready' : 'waiting'"></div>
                    <span x-text="isJoined ? 'Bergabung' : 'Belum bergabung'"></span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span>Status:</span>
                    <span class="status-pill" :class="participantStatus" x-text="getStatusText(participantStatus)"></span>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TABS --}}
    <div class="card p-4">
        <div class="tabs-container">
            <button @click="activeTab = 'participants'" class="tab-btn" :class="{ 'active': activeTab === 'participants' }">
                <i class="fas fa-users mr-2"></i> Peserta (<span x-text="participants.length"></span>)
            </button>
            @if($quiz->show_leaderboard ?? true)
            <button @click="activeTab = 'leaderboard'; loadLeaderboard()" class="tab-btn" :class="{ 'active': activeTab === 'leaderboard' }">
                <i class="fas fa-trophy mr-2 text-yellow-500"></i> Leaderboard
            </button>
            @endif
        </div>

        {{-- PARTICIPANTS TAB --}}
        <div x-show="activeTab === 'participants'">
            <div class="flex justify-between items-center mb-4">
                <p class="text-sm text-gray-500">Update otomatis setiap 3 detik</p>
                <div class="text-sm font-semibold text-purple-600" x-show="stats.total > 0">
                    <span x-text="stats.joined"></span>/<span x-text="stats.total"></span> siswa bergabung
                </div>
            </div>

            <div class="participants-grid">
                {{-- EMPTY STATE --}}
                <template x-if="participants.length === 0">
                    <div class="empty-participants">
                        <div class="empty-icon"><i class="fas fa-users" style="color:#D1D5DB"></i></div>
                        <h3 class="font-bold text-gray-700 mb-1">Belum ada peserta</h3>
                        <p class="text-sm">@if($isGuru) Buka ruangan dan siswa akan muncul di sini. @else Bergabung ke ruangan untuk tampil di sini. @endif</p>
                    </div>
                </template>

                <template x-for="p in sortedParticipants" :key="p.id">
                    <div class="p-card" :class="{ 'violation-card': p.has_violation }">
                        {{-- Violation badge --}}
                        <div x-show="p.has_violation" class="mb-2">
                            <span class="violation-badge">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span x-text="p.violation_count + ' Pelanggaran'"></span>
                            </span>
                        </div>

                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2.5 flex-1 min-w-0">
                                <div class="p-avatar" x-text="p.initial">?</div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-sm truncate text-gray-900" x-text="p.name"></div>
                                    <div class="text-xs text-gray-400 truncate" x-text="p.email"></div>
                                </div>
                            </div>
                            <div class="p-status-dot" :class="p.status"></div>
                        </div>

                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-gray-400">
                                <i class="fas fa-clock mr-1"></i>
                                <span x-text="p.joined_time || '-'"></span>
                            </span>
                            <span class="status-pill" :class="p.status" x-text="getStatusText(p.status)"></span>
                        </div>

                        @if($isGuru)
                        <div class="p-actions">
                            <button @click="markParticipantAsReady(p.id)"
                                x-show="p.status === 'waiting' || p.status === 'not_joined'"
                                class="p-btn ready-btn">
                                <i class="fas fa-check mr-1"></i> Siapkan
                            </button>
                            <button @click="kickParticipant(p.id)" class="p-btn kick-btn">
                                <i class="fas fa-times mr-1"></i> Keluarkan
                            </button>
                        </div>
                        @endif
                    </div>
                </template>
            </div>
        </div>

        {{-- LEADERBOARD TAB --}}
        @if($quiz->show_leaderboard ?? true)
        <div x-show="activeTab === 'leaderboard'" x-cloak>
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-gray-900"><i class="fas fa-trophy mr-2" style="color:#D97706"></i> Leaderboard</h3>
                <button @click="loadLeaderboard()" class="btn-action btn-secondary text-sm px-3 py-2">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>

            <div x-show="leaderboard.length === 0" class="text-center py-8 text-gray-500">
                <i class="fas fa-trophy text-4xl text-gray-300 mb-3 block"></i>
                <p>Belum ada data. Mulai quiz untuk melihat leaderboard.</p>
            </div>

            <div class="space-y-2" x-show="leaderboard.length > 0">
                <template x-for="(entry, idx) in leaderboard" :key="idx">
                    <div class="lb-row" :class="{
                        'rank-1': entry.rank === 1,
                        'rank-2': entry.rank === 2,
                        'rank-3': entry.rank === 3,
                        'rank-other': entry.rank > 3
                    }">
                        <div class="lb-rank">
                            <span x-show="entry.rank === 1"><i class="fas fa-medal" style="color:#D97706"></i></span>
                            <span x-show="entry.rank === 2"><i class="fas fa-medal" style="color:#9CA3AF"></i></span>
                            <span x-show="entry.rank === 3"><i class="fas fa-medal" style="color:#B45309"></i></span>
                            <span x-show="entry.rank > 3" x-text="'#' + entry.rank" class="text-gray-500 text-base"></span>
                        </div>
                        <div class="p-avatar" x-text="(entry.student_name || entry.name || 'U').charAt(0).toUpperCase()"></div>
                        <div class="flex-1">
                            <div class="font-bold text-sm" x-text="entry.student_name || entry.name || 'Peserta'"></div>
                            <div class="text-xs text-gray-500" x-text="'Waktu: ' + formatTime(entry.time_taken || 0)"></div>
                        </div>
                        <div class="text-right">
                            <div class="font-black text-purple-600 text-lg" x-text="(entry.score || 0) + ' pts'"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ===== NOTIFICATION CONTAINER ===== --}}
<div id="notif-container"></div>

<script>
    // URLs
    @if($isGuru)
    const ROOM_STATUS_URL = '{{ route('guru.quiz.room.status', $quiz->id) }}';
    const OPEN_ROOM_URL   = '{{ route('guru.quiz.room.open', $quiz->id) }}';
    const CLOSE_ROOM_URL  = '{{ route('guru.quiz.room.close', $quiz->id) }}';
    const START_QUIZ_URL  = '{{ route('guru.quiz.room.start', $quiz->id) }}';
    const STOP_QUIZ_URL   = '{{ route('guru.quiz.room.stop', $quiz->id) }}';
    const KICK_URL        = '{{ route('guru.quiz.room.kick', [$quiz->id, 'PART_ID']) }}';
    const READY_URL       = '{{ route('guru.quiz.room.mark-ready', [$quiz->id, 'PART_ID']) }}';
    const LEADERBOARD_URL = '{{ route('guru.quiz.leaderboard', $quiz->id) }}';
    @elseif($isMurid)
    const ROOM_STATUS_URL = '{{ route('quiz.room.status', $quiz->id) }}';
    const JOIN_ROOM_URL   = '{{ route('quiz.join-room', $quiz->id) }}';
    const MARK_READY_URL  = '{{ route('quiz.room.mark-ready', $quiz->id) }}';
    const PLAY_QUIZ_URL   = '{{ route('quiz.play', $quiz->id) }}';
    const LEADERBOARD_URL = '{{ route('quiz.leaderboard', $quiz->id) }}';
    @endif
    const CSRF_TOKEN = '{{ csrf_token() }}';

    function roomApp() {
        return {
            roomOpen: {{ $quiz->is_room_open ? 'true' : 'false' }},
            quizStarted: {{ $quiz->is_quiz_started ? 'true' : 'false' }},
            participants: [],
            leaderboard: [],
            activeTab: 'participants',
            lastUpdated: null,
            timeRemainingText: '--:--',
            stats: {
                total: {{ optional($quiz->class)->students()->count() ?? 0 }},
                joined: 0, ready: 0, started: 0, submitted: 0
            },
            participantStatus: '{{ $participant->status ?? 'not_joined' }}',
            _pollInterval: null,
            _lbInterval: null,

            get isJoined() {
                return this.participantStatus !== 'not_joined' && this.participantStatus !== '';
            },
            get sortedParticipants() {
                return [...this.participants].sort((a, b) => {
                    if (a.has_violation && !b.has_violation) return -1;
                    if (!a.has_violation && b.has_violation) return 1;
                    if (a.has_violation && b.has_violation) return (b.violation_count||0) - (a.violation_count||0);
                    const order = ['started','ready','waiting','submitted'];
                    return order.indexOf(a.status) - order.indexOf(b.status);
                });
            },
            get lastUpdatedText() {
                if (!this.lastUpdated) return '';
                const s = Math.floor((Date.now() - this.lastUpdated) / 1000);
                return s < 60 ? `${s}d lalu` : `${Math.floor(s/60)}m lalu`;
            },

            async init() {
                await this.loadRoomData();

                this._pollInterval = setInterval(() => this.loadRoomData(), 3000);
                this._lbInterval = setInterval(() => {
                    if (this.activeTab === 'leaderboard') this.loadLeaderboard();
                }, 5000);

                @if($isMurid)
                if (this.roomOpen && !this.isJoined && !this.quizStarted) {
                    setTimeout(() => this.joinRoom(), 1200);
                }
                // Auto-redirect jika quiz sudah dimulai dan status started
                if (this.quizStarted && (this.participantStatus === 'started')) {
                    // Don't auto-redirect, let user click
                }
                @endif
            },

            async loadRoomData() {
                try {
                    const r = await fetch(ROOM_STATUS_URL, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
                    });
                    if (!r.ok) return;
                    const data = await r.json();
                    if (data.success) this.applyResponse(data);
                } catch(e) {}
            },

            applyResponse(data) {
                if (data.is_room_open !== undefined) this.roomOpen = Boolean(data.is_room_open);
                if (data.is_quiz_started !== undefined) this.quizStarted = Boolean(data.is_quiz_started);

                if (data.stats) {
                    this.stats = {
                        total: data.stats.total_students || data.stats.total || this.stats.total,
                        joined: data.stats.joined || 0,
                        ready: data.stats.ready || 0,
                        started: data.stats.started || 0,
                        submitted: data.stats.submitted || 0,
                    };
                }

                if (Array.isArray(data.participants)) {
                    this.participants = data.participants.map(p => {
                        const name = p.student_name || p.name || 'Unknown';
                        const vc = parseInt(p.violation_count) || 0;
                        return {
                            id: p.id,
                            student_id: p.student_id,
                            name, email: p.student_email || p.email || '',
                            status: p.status || 'waiting',
                            joined_time: p.joined_at || p.joined_time || '-',
                            initial: name.charAt(0).toUpperCase(),
                            violation_count: vc,
                            has_violation: vc > 0 || p.has_violation,
                        };
                    });
                    this.updateViolationBar();
                }

                @if($isMurid)
                if (data.participant) {
                    const prevStatus = this.participantStatus;
                    this.participantStatus = data.participant.status || 'not_joined';
                    // Auto redirect when quiz starts and user is ready/started
                    if (this.quizStarted && prevStatus !== 'started' && this.participantStatus === 'started') {
                        this.showNotif('success', 'Quiz dimulai! Klik tombol untuk mengerjakan.');
                    }
                }
                @endif

                if (data.time_remaining !== undefined && data.time_remaining !== null) {
                    this.timeRemainingText = this.formatTime(data.time_remaining);
                }

                this.lastUpdated = Date.now();
            },

            updateViolationBar() {
                const bar = document.getElementById('violation-bar');
                if (!bar) return;
                const violators = this.participants.filter(p => p.violation_count > 0);
                const total = this.participants.reduce((s,p) => s + (p.violation_count||0), 0);
                if (violators.length > 0) {
                    bar.classList.remove('hidden');
                    document.getElementById('viol-count').textContent = total;
                    document.getElementById('viol-users').textContent = violators.length;
                } else {
                    bar.classList.add('hidden');
                }
            },

            formatTime(secs) {
                const s = parseInt(secs);
                if (isNaN(s) || s <= 0) return '00:00';
                return `${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`;
            },

            getStatusText(status) {
                const map = { not_joined:'Belum Bergabung', waiting:'Menunggu', ready:'Siap', started:'Mengerjakan', submitted:'Selesai', disconnected:'Terputus' };
                return map[status] || status;
            },

            // ===== GURU ACTIONS =====
            @if($isGuru)
            async openRoom() {
                if (!confirm('Buka ruangan quiz sekarang? Siswa akan bisa bergabung.')) return;
                try {
                    const r = await fetch(OPEN_ROOM_URL, { method:'POST', headers:{ 'X-CSRF-TOKEN':CSRF_TOKEN, 'Accept':'application/json' }});
                    const data = await r.json();
                    if (data.success) { this.roomOpen = true; this.showNotif('success', data.message); await this.loadRoomData(); }
                    else this.showNotif('error', data.message || 'Gagal membuka ruangan');
                } catch(e) { this.showNotif('error', 'Terjadi kesalahan'); }
            },
            async closeRoom() {
                if (!confirm('Tutup ruangan? Semua siswa akan dikeluarkan.')) return;
                try {
                    const r = await fetch(CLOSE_ROOM_URL, { method:'POST', headers:{ 'X-CSRF-TOKEN':CSRF_TOKEN, 'Accept':'application/json' }});
                    const data = await r.json();
                    if (data.success) { this.roomOpen = false; this.showNotif('success', data.message); await this.loadRoomData(); }
                    else this.showNotif('error', data.message || 'Gagal menutup ruangan');
                } catch(e) { this.showNotif('error', 'Terjadi kesalahan'); }
            },
            async startQuiz() {
                if (this.stats.ready === 0) { this.showNotif('warning', 'Belum ada siswa yang siap!'); return; }
                if (!confirm(`Mulai quiz? ${this.stats.ready} siswa siap.`)) return;
                try {
                    const r = await fetch(START_QUIZ_URL, { method:'POST', headers:{ 'X-CSRF-TOKEN':CSRF_TOKEN, 'Accept':'application/json' }});
                    const data = await r.json();
                    if (data.success) { this.quizStarted = true; this.showNotif('success', data.message || 'Quiz dimulai!'); await this.loadRoomData(); }
                    else this.showNotif('error', data.message || 'Gagal memulai quiz');
                } catch(e) { this.showNotif('error', 'Terjadi kesalahan'); }
            },
            async stopQuiz() {
                if (!confirm('Hentikan quiz? Semua siswa yang belum selesai akan dipaksa submit.')) return;
                try {
                    const r = await fetch(STOP_QUIZ_URL, { method:'POST', headers:{ 'X-CSRF-TOKEN':CSRF_TOKEN, 'Accept':'application/json' }});
                    const data = await r.json();
                    if (data.success) { this.quizStarted = false; this.roomOpen = false; this.showNotif('success', data.message); await this.loadRoomData(); }
                    else this.showNotif('error', data.message || 'Gagal menghentikan quiz');
                } catch(e) { this.showNotif('error', 'Terjadi kesalahan'); }
            },
            async kickParticipant(participantId) {
                if (!confirm('Keluarkan peserta ini?')) return;
                try {
                    const url = KICK_URL.replace('PART_ID', participantId);
                    const r = await fetch(url, { method:'POST', headers:{ 'X-CSRF-TOKEN':CSRF_TOKEN, 'Accept':'application/json' }});
                    const data = await r.json();
                    if (data.success) { this.showNotif('success', data.message); await this.loadRoomData(); }
                    else this.showNotif('error', data.message || 'Gagal');
                } catch(e) { this.showNotif('error', 'Terjadi kesalahan'); }
            },
            async markParticipantAsReady(participantId) {
                try {
                    const url = READY_URL.replace('PART_ID', participantId);
                    const r = await fetch(url, { method:'POST', headers:{ 'X-CSRF-TOKEN':CSRF_TOKEN, 'Accept':'application/json' }});
                    const data = await r.json();
                    if (data.success) { this.showNotif('success', data.message || 'Status diubah!'); await this.loadRoomData(); }
                    else this.showNotif('error', data.message || 'Gagal');
                } catch(e) { this.showNotif('error', 'Terjadi kesalahan'); }
            },
            @endif

            // ===== SISWA ACTIONS =====
            @if($isMurid)
            async joinRoom() {
                try {
                    const r = await fetch(JOIN_ROOM_URL, { method:'POST', headers:{ 'X-CSRF-TOKEN':CSRF_TOKEN, 'Accept':'application/json' }});
                    const data = await r.json();
                    if (data.success) { this.participantStatus = data.participant_status || 'waiting'; this.showNotif('success', data.message); await this.loadRoomData(); }
                    else this.showNotif('error', data.message || 'Gagal bergabung');
                } catch(e) { this.showNotif('error', 'Terjadi kesalahan'); }
            },
            async markAsReady() {
                try {
                    const r = await fetch(MARK_READY_URL, { method:'POST', headers:{ 'X-CSRF-TOKEN':CSRF_TOKEN, 'Accept':'application/json' }});
                    const data = await r.json();
                    if (data.success) { this.participantStatus = 'ready'; this.showNotif('success', data.message || 'Kamu siap!'); await this.loadRoomData(); }
                    else this.showNotif('error', data.message || 'Gagal');
                } catch(e) { this.showNotif('error', 'Terjadi kesalahan'); }
            },
            @endif

            // ===== LEADERBOARD =====
            async loadLeaderboard() {
                try {
                    const r = await fetch(LEADERBOARD_URL, { headers: { 'Accept':'application/json', 'X-CSRF-TOKEN':CSRF_TOKEN }});
                    const data = await r.json();
                    if (data.success) this.leaderboard = data.leaderboard || [];
                    else this.leaderboard = [];
                } catch(e) { this.leaderboard = []; }
            },

            // ===== NOTIFICATION =====
            showNotif(type, msg) {
                const icons = { success:'fa-check-circle', error:'fa-exclamation-circle', warning:'fa-exclamation-triangle', info:'fa-info-circle' };
                const el = document.createElement('div');
                el.className = `notif ${type}`;
                el.innerHTML = `<i class="fas ${icons[type]||'fa-info-circle'}"></i><span style="flex:1">${msg}</span><button onclick="this.parentElement.remove()" style="background:none;border:none;color:white;cursor:pointer;font-size:0.875rem;flex-shrink:0"><i class="fas fa-times"></i></button>`;
                document.getElementById('notif-container').appendChild(el);
                setTimeout(() => { if (el.parentElement) { el.style.opacity='0'; el.style.transform='translateX(60px)'; el.style.transition='all 0.3s'; setTimeout(() => el.remove(), 300); } }, 5000);
            }
        }
    }
</script>

@if(session('error'))
<script>
window.addEventListener('load', () => {
    const app = document.querySelector('[x-data]').__x;
    if (app) app.$data.showNotif('error', '{{ session('error') }}');
});
</script>
@endif

</body>
</html>
