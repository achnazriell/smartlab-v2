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
    $isGuidedMode = ($quiz->quiz_mode === 'guided');
@endphp
<!DOCTYPE html>
<html lang="id" x-data="roomApp()" x-init="init()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $quiz->title }} — Ruang Quiz</title>
    <link rel="icon" type="image/webp" href="{{ asset('image/logo.webp') }}">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Nunito', sans-serif; background: #EFF6FF; min-height: 100vh; }

        /* ===== TOPBAR ===== */
        .topbar {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563EB 100%);
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
            box-shadow: 0 4px 24px rgba(29,78,216,0.4);
        }
        .topbar-left h1 { font-size: 1.15rem; font-weight: 900; letter-spacing: -0.5px; }
        .topbar-left p  { font-size: 0.78rem; opacity: 0.8; }

        /* ===== STATUS BAR ===== */
        .status-bar {
            background: white; border-bottom: 2px solid #DBEAFE;
            padding: 0.75rem 1.5rem;
            display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
        }
        .room-status { display: flex; align-items: center; gap: 0.5rem; font-weight: 700; font-size: 0.875rem; }
        .status-dot { width: 10px; height: 10px; border-radius: 50%; animation: blink 1.5s infinite; }
        @keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }
        .status-dot.closed { background: #9CA3AF; animation: none; }
        .status-dot.open   { background: #10B981; }
        .status-dot.started{ background: #2563EB; }

        /* ===== STATS GRID ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem; padding: 1rem 1.5rem;
        }
        @media (max-width: 768px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }

        .stat-card {
            background: white; border-radius: 16px; padding: 1.1rem;
            display: flex; align-items: center; gap: 1rem;
            box-shadow: 0 2px 12px rgba(37,99,235,0.08);
            border: 1.5px solid #DBEAFE;
        }
        .stat-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
        .stat-icon.blue   { background: #EFF6FF; color: #2563EB; }
        .stat-icon.green  { background: #ECFDF5; color: #059669; }
        .stat-icon.purple { background: #EDE9FE; color: #7C3AED; }
        .stat-icon.yellow { background: #FFFBEB; color: #D97706; }
        .stat-value { font-size: 1.75rem; font-weight: 900; color: #1e3a8a; line-height: 1; }
        .stat-label { font-size: 0.75rem; color: #6B7280; font-weight: 600; margin-top: 2px; }

        /* ===== ACTION BUTTONS ===== */
        .actions-bar {
            display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;
            padding: 1rem 1.5rem;
            background: white; border-radius: 16px;
            margin: 0 1.5rem 1rem;
            border: 1.5px solid #DBEAFE;
            box-shadow: 0 2px 12px rgba(37,99,235,0.06);
        }
        .btn-action {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.65rem 1.25rem; border-radius: 12px;
            font-size: 0.875rem; font-weight: 700;
            border: none; cursor: pointer;
            transition: all 0.2s; text-decoration: none;
        }
        .btn-action:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
        .btn-action:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .btn-open       { background: #2563EB; color: white; }
        .btn-open:hover:not(:disabled) { background: #1d4ed8; }
        .btn-start      { background: #10B981; color: white; }
        .btn-start:hover:not(:disabled) { background: #059669; }
        .btn-stop       { background: #EF4444; color: white; }
        .btn-stop:hover:not(:disabled)  { background: #DC2626; }
        .btn-close-room { background: #F59E0B; color: white; }
        .btn-secondary  { background: #EFF6FF; color: #1d4ed8; border: 1.5px solid #BFDBFE; }
        .btn-secondary:hover:not(:disabled) { background: #DBEAFE; }
        .btn-ready      { background: #10B981; color: white; }
        .btn-join       { background: #2563EB; color: white; }
        .btn-back       { background: #6B7280; color: white; }
        .btn-guided     { background: linear-gradient(135deg, #1d4ed8, #3b82f6); color: white; }

        /* ===== TABS ===== */
        .tabs-container { display: flex; border-bottom: 2px solid #DBEAFE; margin-bottom: 1rem; }
        .tab-btn {
            padding: 0.75rem 1.25rem; font-size: 0.875rem; font-weight: 700;
            color: #6B7280; border: none; background: none; cursor: pointer;
            border-bottom: 3px solid transparent; margin-bottom: -2px; transition: all 0.2s;
        }
        .tab-btn.active { color: #2563EB; border-bottom-color: #2563EB; }

        /* ===== PARTICIPANT GRID ===== */
        .participants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }
        @media (max-width: 640px) { .participants-grid { grid-template-columns: 1fr 1fr; } }

        .p-card {
            background: white; border-radius: 16px; padding: 1rem;
            border: 2px solid #DBEAFE;
            box-shadow: 0 2px 10px rgba(37,99,235,0.05);
            transition: all 0.3s;
        }
        .p-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(37,99,235,0.12); }
        .p-card.violation-card {
            border-color: #EF4444 !important;
            background: linear-gradient(135deg, #FEF2F2, #FEE2E2) !important;
            animation: pulseViolation 2.2s ease-in-out infinite;
        }
        @keyframes pulseViolation {
            0%,100% { box-shadow: 0 0 0 2px rgba(239,68,68,0.2), 0 4px 16px rgba(239,68,68,0.15); }
            50%      { box-shadow: 0 0 0 4px rgba(239,68,68,0.35), 0 8px 32px rgba(239,68,68,0.4); }
        }

        .p-avatar {
            width: 44px; height: 44px; border-radius: 50%;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            color: white; display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; font-weight: 900; flex-shrink: 0;
        }
        .p-status-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .p-status-dot.waiting   { background: #F59E0B; animation: blink 1.5s infinite; }
        .p-status-dot.ready     { background: #10B981; animation: blink 1.5s infinite; }
        .p-status-dot.started   { background: #2563EB; animation: blink 0.8s infinite; }
        .p-status-dot.submitted { background: #2563EB; }

        .violation-badge {
            display: inline-flex; align-items: center; gap: 3px;
            background: #EF4444; color: white;
            font-size: 0.65rem; font-weight: 800;
            padding: 2px 8px; border-radius: 999px;
        }
        .status-pill {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 8px; border-radius: 999px;
            font-size: 0.7rem; font-weight: 700;
        }
        .status-pill.waiting   { background: #FEF3C7; color: #92400E; }
        .status-pill.ready     { background: #D1FAE5; color: #065F46; }
        .status-pill.started   { background: #DBEAFE; color: #1E40AF; }
        .status-pill.submitted { background: #EDE9FE; color: #4C1D95; }
        .p-actions { display: flex; gap: 0.4rem; margin-top: 0.75rem; }
        .p-btn { flex: 1; padding: 0.35rem 0.5rem; border-radius: 8px; font-size: 0.72rem; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s; }
        .p-btn.ready-btn { background: #D1FAE5; color: #065F46; }
        .p-btn.ready-btn:hover { background: #A7F3D0; }
        .p-btn.kick-btn  { background: #FEE2E2; color: #991B1B; }
        .p-btn.kick-btn:hover  { background: #FECACA; }
        .p-btn.warn-btn  { background: #FEF3C7; color: #92400E; }
        .p-btn.warn-btn:hover  { background: #FDE68A; }

        /* ===== VIOLATION CARD — red glow (handled above) ===== */

        /* ===== WARNING POPUP (SISWA) ===== */
        .warning-overlay {
            position: fixed; inset: 0; z-index: 9998;
            background: rgba(239,68,68,0.18);
            display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(3px);
            animation: warnFadeIn 0.3s ease;
        }
        @keyframes warnFadeIn { from { opacity: 0; } to { opacity: 1; } }
        .warning-popup {
            background: white; border-radius: 20px;
            border: 3px solid #EF4444;
            box-shadow: 0 16px 56px rgba(239,68,68,0.35);
            padding: 2rem 2.5rem; text-align: center;
            max-width: 360px; width: 90%;
            animation: warnPop 0.4s cubic-bezier(0.34,1.56,0.64,1);
        }
        @keyframes warnPop { from { opacity: 0; transform: scale(0.7); } to { opacity: 1; transform: scale(1); } }
        .warning-icon-wrap {
            width: 72px; height: 72px; border-radius: 50%;
            background: linear-gradient(135deg, #FEE2E2, #FECACA);
            border: 3px solid #EF4444;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.25rem; font-size: 2.2rem;
            animation: warnShake 0.5s ease 0.1s;
        }
        @keyframes warnShake {
            0%,100%{transform:rotate(0)} 20%{transform:rotate(-8deg)}
            40%{transform:rotate(8deg)} 60%{transform:rotate(-6deg)} 80%{transform:rotate(6deg)}
        }

        /* ===== EMPTY STATE ===== */
        .empty-participants { grid-column: 1 / -1; text-align: center; padding: 4rem 2rem; color: #6B7280; }
        .empty-icon { width: 80px; height: 80px; background: #EFF6FF; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; font-size: 2rem; }

        /* ===== LEADERBOARD ===== */
        .lb-row { display: flex; align-items: center; gap: 1rem; padding: 0.875rem 1rem; border-radius: 12px; margin-bottom: 0.5rem; transition: all 0.2s; }
        .lb-row:hover { transform: translateX(4px); }
        .lb-row.rank-1 { background: linear-gradient(135deg, #FEF3C7, #FDE68A); border: 2px solid #F59E0B; }
        .lb-row.rank-2 { background: linear-gradient(135deg, #F3F4F6, #E5E7EB); border: 2px solid #9CA3AF; }
        .lb-row.rank-3 { background: linear-gradient(135deg, #FEF3C7, #FDE68A); border: 2px solid #D97706; opacity: 0.8; }
        .lb-row.rank-other { background: white; border: 1.5px solid #DBEAFE; }
        .lb-rank { font-size: 1.25rem; width: 36px; text-align: center; font-weight: 900; }

        /* ===== VIOLATION BAR ===== */
        .violation-bar {
            background: linear-gradient(135deg, #FEE2E2, #FECACA);
            border: 1.5px solid #FCA5A5; border-radius: 12px;
            padding: 0.75rem 1rem; margin-bottom: 1rem;
            display: flex; align-items: center; gap: 0.5rem;
            font-size: 0.875rem; font-weight: 700; color: #991B1B;
        }

        /* ===== WAITING ROOM (SISWA) ===== */
        .waiting-room {
            background: white; border-radius: 20px; padding: 2.5rem;
            text-align: center;
            box-shadow: 0 8px 32px rgba(37,99,235,0.12);
            border: 2px solid #DBEAFE; max-width: 500px; margin: 0 auto;
        }
        .pulse-ring {
            width: 80px; height: 80px; border-radius: 50%;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem; position: relative;
        }
        .pulse-ring::after {
            content: ''; position: absolute; inset: -8px; border-radius: 50%;
            border: 3px solid #2563EB; animation: ringPulse 2s infinite;
        }
        @keyframes ringPulse { 0% { opacity: 0.8; transform: scale(1); } 100% { opacity: 0; transform: scale(1.5); } }

        /* ===== COUNTDOWN OVERLAY ===== */
        #countdown-overlay {
            position: fixed; inset: 0;
            background: rgba(29,78,216,0.92);
            z-index: 9999;
            display: none;
            flex-direction: column;
            align-items: center; justify-content: center;
            gap: 1.5rem;
            backdrop-filter: blur(6px);
        }
        #countdown-overlay.active { display: flex; }
        #countdown-number {
            font-size: 9rem; font-weight: 900; color: white;
            line-height: 1; font-family: 'Nunito', sans-serif;
            text-shadow: 0 0 60px rgba(147,197,253,0.8);
            animation: countPulse 1s ease infinite;
        }
        @keyframes countPulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.08); } }
        #countdown-text { font-size: 1.4rem; font-weight: 700; color: rgba(255,255,255,0.85); text-align: center; }
        .countdown-bar { width: 240px; height: 8px; background: rgba(255,255,255,0.25); border-radius: 999px; overflow: hidden; }
        .countdown-bar-fill { height: 100%; background: white; border-radius: 999px; transition: width 1s linear; }

        /* ===== QUIZ STARTED BANNER ===== */
        .quiz-started-banner {
            background: linear-gradient(135deg, #D1FAE5, #A7F3D0);
            border: 2px solid #10B981; border-radius: 16px;
            padding: 1.25rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem;
        }

        /* ===== GUIDED MODE BANNER ===== */
        .guided-banner {
            background: linear-gradient(135deg, #1d4ed8, #2563eb);
            border: 2px solid #3b82f6; border-radius: 16px;
            padding: 1.25rem 1.5rem;
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem;
            color: white;
        }

        /* ===== NOTIFICATION ===== */
        #notif-container {
            position: fixed; top: 1rem; right: 1rem; z-index: 9999;
            display: flex; flex-direction: column; gap: 0.5rem; width: 320px;
        }
        .notif {
            padding: 0.9rem 1.1rem; border-radius: 12px; color: white;
            font-weight: 700; font-size: 0.85rem;
            display: flex; align-items: flex-start; gap: 0.6rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            animation: notifIn 0.3s cubic-bezier(0.34,1.56,0.64,1);
        }
        @keyframes notifIn { from { opacity: 0; transform: translateX(60px); } to { opacity: 1; transform: translateX(0); } }
        .notif.success { background: #10B981; }
        .notif.error   { background: #EF4444; }
        .notif.warning { background: #F59E0B; }
        .notif.info    { background: #2563EB; }

        [x-cloak] { display: none !important; }

        /* ===== DURATION TIMER COLORS ===== */
        .timer-warning { background: rgba(245,158,11,0.5) !important; color: #FEF3C7; }
        .timer-danger  { background: rgba(239,68,68,0.5)  !important; color: #FEE2E2; animation: blink 0.8s infinite; }

        .card { background: white; border-radius: 16px; border: 1.5px solid #DBEAFE; box-shadow: 0 2px 12px rgba(37,99,235,0.06); }
        .progress-bar { height: 6px; background: #DBEAFE; border-radius: 999px; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #2563EB, #60A5FA); border-radius: 999px; transition: width 0.5s ease; }
        .main-content { padding: 0 1.5rem 2rem; }

        /* ===== EXPIRED OVERLAY ===== */
        #expired-overlay {
            display: none;
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(15,23,42,0.88);
            backdrop-filter: blur(6px);
            flex-direction: column; align-items: center; justify-content: center;
            gap: 1.25rem; text-align: center; padding: 2rem;
        }
        #expired-overlay.active { display: flex; }
        #expired-overlay .exp-icon { font-size: 5rem; animation: expPop 0.5s cubic-bezier(0.34,1.56,0.64,1); }
        #expired-overlay h2 { color: white; font-size: 1.75rem; font-weight: 900; }
        #expired-overlay p  { color: #94A3B8; font-size: 1rem; max-width: 420px; }
        #expired-overlay .exp-btn {
            background: linear-gradient(135deg, #2563EB, #3B82F6); color: white;
            border: none; border-radius: 14px; padding: 0.9rem 2.5rem;
            font-size: 1rem; font-weight: 800; cursor: pointer;
            box-shadow: 0 8px 24px rgba(37,99,235,0.4); transition: transform 0.2s;
        }
        #expired-overlay .exp-btn:hover { transform: translateY(-2px); }
        @keyframes expPop { from { opacity:0; transform:scale(0.5); } to { opacity:1; transform:scale(1); } }
    </style>
</head>
<body>

{{-- ===== TOPBAR ===== --}}
<div class="topbar">
    <div class="topbar-left">
        <h1><i class="fas fa-gamepad mr-2" style="color:#FDE68A"></i>{{ Str::limit($quiz->title, 40) }}</h1>
        <p>{{ $quiz->subject->name_subject ?? '' }} • Kelas {{ $quiz->class->name_class ?? '' }}
           • {{ $quiz->questions->count() }} Soal
           @if($quiz->quiz_mode === 'guided') • <span style="color:#FDE68A;font-weight:800">Mode Terpadu</span> @else • {{ $quiz->time_per_question ?? 0 }}s/soal @endif
        </p>
    </div>

    <div style="display:flex;align-items:center;gap:0.75rem">
        {{-- ===== DURATION TIMER — tampil saat quiz berjalan ===== --}}
        @if($quiz->duration > 0)
        <div x-show="quizStarted" x-cloak
            class="text-sm font-bold transition-all"
            :class="{
                'timer-danger': timeRemainingSeconds !== null && timeRemainingSeconds <= 60,
                'timer-warning': timeRemainingSeconds !== null && timeRemainingSeconds > 60 && timeRemainingSeconds <= 300
            }"
            style="padding:0.4rem 0.9rem;border-radius:8px;background:rgba(255,255,255,0.2)">
            <i class="fas fa-hourglass-half mr-1"></i>
            <span x-text="timeRemainingText"></span>
        </div>
        @else
        {{-- Quiz tanpa batas durasi — hanya tampil stopwatch --}}
        <div x-show="quizStarted" x-cloak class="text-sm font-bold"
            style="background:rgba(255,255,255,0.2);padding:0.4rem 0.9rem;border-radius:8px">
            <i class="fas fa-stopwatch mr-1"></i> <span x-text="timeRemainingText"></span>
        </div>
        @endif

        <div style="background:rgba(255,255,255,0.2);padding:0.5rem 1rem;border-radius:10px;font-weight:800;font-size:0.85rem">
            <i class="fas {{ $isGuru ? 'fa-chalkboard-teacher' : 'fa-user-graduate' }} mr-1"></i>{{ $isGuru ? 'Guru' : 'Siswa' }}
        </div>
    </div>
</div>

{{-- ===== STATUS BAR ===== --}}
<div class="status-bar">
    <div class="room-status">
        <div class="status-dot" :class="quizStarted ? 'started' : (roomOpen ? 'open' : 'closed')"></div>
        <span x-text="roomOpen ? (quizStarted ? 'Quiz Sedang Berlangsung' : 'Ruangan Terbuka') : 'Ruangan Tertutup'"></span>
    </div>

    @if($quiz->quiz_mode === 'guided')
    <span class="text-sm font-bold px-3 py-1 rounded-full" style="background:#DBEAFE;color:#1d4ed8;">
        <i class="fas fa-chalkboard mr-1"></i> Mode Terpadu — Guru kendalikan soal
    </span>
    @endif

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

        @if($isGuidedMode)
        {{-- Tombol khusus guided mode untuk guru --}}
        <a href="{{ route('guru.quiz.guided', $quiz->id) }}" x-show="quizStarted" class="btn-action btn-guided">
            <i class="fas fa-chalkboard-teacher"></i> Kendalikan Soal (Terpadu)
        </a>
        @endif

        <button @click="loadRoomData(); showNotif('info', 'Data diperbarui!')" class="btn-action btn-secondary">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>

        <a href="{{ route('guru.quiz.index') }}" class="btn-action btn-back">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>

        @if($quiz->status === 'finished')
        <a href="{{ route('guru.quiz.results', $quiz->id) }}" class="btn-action btn-open">
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

        <div x-show="!roomOpen" class="flex items-center gap-2 text-blue-700 text-sm font-semibold">
            <i class="fas fa-clock animate-pulse"></i> Tunggu guru membuka ruangan...
        </div>

        <div x-show="isJoined && participantStatus === 'ready' && !quizStarted"
            class="flex items-center gap-2 text-green-600 text-sm font-semibold">
            <i class="fas fa-check-circle"></i> Kamu sudah siap! Tunggu quiz dimulai...
        </div>
    @endif
</div>

{{-- ===== GUIDED MODE BANNER UNTUK GURU (setelah quiz dimulai) ===== --}}
@if($isGuru && $isGuidedMode)
<div class="guided-banner mx-6" x-show="quizStarted" x-cloak>
    <div class="flex items-center gap-3">
        <div style="font-size:2rem"><i class="fas fa-chalkboard-teacher"></i></div>
        <div>
            <div class="font-bold text-white text-base">Mode Terpadu Aktif!</div>
            <div class="text-sm text-blue-100">Klik tombol di bawah untuk mengendalikan soal secara langsung di depan siswa.</div>
        </div>
    </div>
    <a href="{{ route('guru.quiz.guided', $quiz->id) }}" class="btn-action btn-start" style="white-space:nowrap;background:white;color:#1d4ed8;">
        <i class="fas fa-chalkboard-teacher"></i> Buka Panel Kontrol Soal
    </a>
</div>
@endif

{{-- ===== QUIZ STARTED BANNER UNTUK SISWA ===== --}}
@if($isMurid)
<div class="quiz-started-banner mx-6" x-show="quizStarted && (participantStatus === 'started' || participantStatus === 'ready')" x-cloak>
    <div class="flex items-center gap-3">
        <div style="font-size:2rem"><i class="fas fa-play-circle" style="color:#065F46"></i></div>
        <div>
            <div class="font-bold text-green-800 text-base">Quiz telah dimulai!</div>
            <div class="text-sm text-green-700">Kamu akan otomatis diarahkan dalam hitungan mundur...</div>
        </div>
    </div>
    <button @click="startCountdown()" class="btn-action btn-start" style="white-space:nowrap">
        <i class="fas fa-play"></i> Mulai Sekarang!
    </button>
</div>
@endif

{{-- ===== PANEL INFO QUIZ UNTUK GURU (sebelum quiz dimulai) ===== --}}
@if($isGuru)
<div class="mx-6 mb-4 p-5" x-show="!quizStarted" x-cloak
     style="background:white;border-radius:16px;border:1.5px solid #DBEAFE;box-shadow:0 2px 12px rgba(37,99,235,0.06);">
    <h3 style="font-size:0.875rem;font-weight:900;color:#1e3a8a;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
        <i class="fas fa-clipboard-list" style="color:#2563EB"></i> Ringkasan Quiz — Ditampilkan ke Siswa Saat Menunggu
    </h3>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0.75rem;margin-bottom:1rem;">
        <div style="background:#EFF6FF;border-radius:12px;padding:0.875rem;text-align:center;">
            <div style="font-size:1.5rem;font-weight:900;color:#1d4ed8;">{{ $quiz->questions->count() }}</div>
            <div style="font-size:0.72rem;color:#6B7280;font-weight:600;margin-top:2px;">Total Soal</div>
        </div>
        <div style="background:#ECFDF5;border-radius:12px;padding:0.875rem;text-align:center;">
            <div style="font-size:1.5rem;font-weight:900;color:#059669;">
                {{ $quiz->time_per_question > 0 ? $quiz->time_per_question . 's' : '∞' }}
            </div>
            <div style="font-size:0.72rem;color:#6B7280;font-weight:600;margin-top:2px;">
                {{ $quiz->time_per_question > 0 ? 'Detik/Soal' : 'Tanpa Limit' }}
            </div>
        </div>
        <div style="background:#F5F3FF;border-radius:12px;padding:0.875rem;text-align:center;">
            <div style="font-size:1.5rem;font-weight:900;color:#7C3AED;">{{ $quiz->duration ?? '∞' }}</div>
            <div style="font-size:0.72rem;color:#6B7280;font-weight:600;margin-top:2px;">Durasi (mnt)</div>
        </div>
        <div style="background:#FFFBEB;border-radius:12px;padding:0.875rem;text-align:center;">
            <div style="font-size:1.5rem;font-weight:900;color:#D97706;">
                {{ $quiz->quiz_mode === 'guided' ? 'Terpadu' : 'Mandiri' }}
            </div>
            <div style="font-size:0.72rem;color:#6B7280;font-weight:600;margin-top:2px;">Mode</div>
        </div>
    </div>
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;font-size:0.8rem;color:#374151;">
        <span style="background:#DBEAFE;color:#1e40af;padding:3px 10px;border-radius:999px;font-weight:700;">
            <i class="fas fa-book mr-1"></i>{{ $quiz->subject->name_subject ?? '—' }}
        </span>
        <span style="background:#DBEAFE;color:#1e40af;padding:3px 10px;border-radius:999px;font-weight:700;">
            <i class="fas fa-users mr-1"></i>Kelas {{ $quiz->class->name_class ?? '—' }}
        </span>
        @if($quiz->instant_feedback)
        <span style="background:#D1FAE5;color:#065F46;padding:3px 10px;border-radius:999px;font-weight:700;">
            <i class="fas fa-bolt mr-1"></i>Feedback Instan
        </span>
        @endif
        @if($quiz->time_per_question > 0)
        <span style="background:#EDE9FE;color:#4C1D95;padding:3px 10px;border-radius:999px;font-weight:700;">
            <i class="fas fa-clock mr-1"></i>Auto Ganti Soal ({{ $quiz->time_per_question }}s)
        </span>
        @else
        <span style="background:#F3F4F6;color:#374151;padding:3px 10px;border-radius:999px;font-weight:700;">
            <i class="fas fa-hand-pointer mr-1"></i>Next Soal Manual
        </span>
        @endif
    </div>
</div>
@endif

{{-- ===== STATS CARDS ===== --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
        <div><div class="stat-value" x-text="stats.joined">0</div><div class="stat-label">Bergabung</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div><div class="stat-value" x-text="stats.ready">0</div><div class="stat-label">Siap</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-pencil-alt"></i></div>
        <div><div class="stat-value" x-text="stats.started">0</div><div class="stat-label">Mengerjakan</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-flag-checkered"></i></div>
        <div><div class="stat-value" x-text="stats.submitted">0</div><div class="stat-label">Selesai</div></div>
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

    {{-- WAITING ROOM UNTUK SISWA --}}
    @if($isMurid)
    <div x-show="!quizStarted" x-cloak class="mb-4 px-4 sm:px-6">

        {{-- Hero Card: Status bergabung + tombol aksi — desain putih bersih --}}
        <div class="rounded-2xl overflow-hidden mb-4"
             style="background:white;border:1.5px solid #E2E8F0;box-shadow:0 4px 24px rgba(15,23,42,0.08);">

            {{-- Top strip tipis berwarna sesuai status --}}
            <div class="h-1.5 w-full"
                 :style="roomOpen ? 'background:linear-gradient(90deg,#10B981,#34D399)' : 'background:#CBD5E1'"></div>

            {{-- Header: judul quiz --}}
            <div class="px-5 pt-4 pb-3 flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="text-slate-400 text-xs font-semibold uppercase tracking-widest mb-1 flex items-center gap-1.5">
                        <i class="fas fa-gamepad text-blue-400" style="font-size:0.65rem"></i> Ruang Quiz
                    </div>
                    <h2 class="text-slate-900 font-black text-lg leading-tight truncate">{{ $quiz->title }}</h2>
                    <div class="text-slate-400 text-xs mt-1">{{ $quiz->subject->name_subject ?? '' }}
                        @if($quiz->class->name_class ?? '')
                            &bull; Kelas {{ $quiz->class->name_class }}
                        @endif
                    </div>
                </div>
                {{-- Status badge --}}
                <div class="flex-shrink-0 flex flex-col items-end gap-1.5">
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold"
                         :style="roomOpen
                            ? 'background:#DCFCE7;color:#15803D;border:1px solid #BBF7D0'
                            : 'background:#F1F5F9;color:#64748B;border:1px solid #E2E8F0'">
                        <div class="w-2 h-2 rounded-full flex-shrink-0"
                             :class="roomOpen ? 'bg-emerald-500' : 'bg-slate-400'"
                             :style="roomOpen ? 'animation:blink 1.5s infinite' : ''"></div>
                        <span x-text="roomOpen ? 'Terbuka' : 'Tertutup'"></span>
                    </div>
                    <span class="text-xs text-slate-400" x-text="stats.joined + ' peserta'"></span>
                </div>
            </div>

            {{-- Divider --}}
            <div style="height:1px;background:#F1F5F9;margin:0 1.25rem;"></div>

            {{-- Stat chips: 4 info sejajar --}}
            <div class="px-5 pt-3 pb-4">
                <div class="grid grid-cols-4 gap-2 mb-4">
                    <div class="rounded-xl text-center py-2.5 px-1" style="background:#F0F9FF;border:1px solid #BAE6FD;">
                        <div class="text-blue-700 font-black text-lg leading-none">{{ $quiz->questions->count() }}</div>
                        <div class="text-blue-400 text-xs mt-0.5 font-semibold">Soal</div>
                    </div>
                    <div class="rounded-xl text-center py-2.5 px-1" style="background:#F0FDF4;border:1px solid #BBF7D0;">
                        <div class="text-emerald-700 font-black text-lg leading-none">{{ $quiz->duration }}</div>
                        <div class="text-emerald-400 text-xs mt-0.5 font-semibold">Menit</div>
                    </div>
                    <div class="rounded-xl text-center py-2.5 px-1" style="background:#FDF4FF;border:1px solid #E9D5FF;">
                        <div class="text-violet-700 font-black text-lg leading-none">
                            {{ $quiz->time_per_question > 0 ? $quiz->time_per_question.'s' : '∞' }}
                        </div>
                        <div class="text-violet-400 text-xs mt-0.5 font-semibold">/Soal</div>
                    </div>
                    <div class="rounded-xl text-center py-2.5 px-1" style="background:#FFFBEB;border:1px solid #FDE68A;">
                        <div class="text-amber-700 font-black text-sm leading-none pt-0.5">
                            {{ $quiz->quiz_mode === 'guided' ? '🎯' : ($quiz->quiz_mode === 'live' ? '⚡' : '📝') }}
                        </div>
                        <div class="text-amber-500 text-xs mt-0.5 font-semibold">
                            {{ $quiz->quiz_mode === 'guided' ? 'Terpadu' : ($quiz->quiz_mode === 'live' ? 'Live' : 'Mandiri') }}
                        </div>
                    </div>
                </div>

                {{-- Status siswa: banner bawah --}}
                <div class="flex items-center gap-3 p-3 rounded-xl"
                     :style="!isJoined
                        ? 'background:#F8FAFC;border:1.5px solid #E2E8F0'
                        : (participantStatus==='ready'
                            ? 'background:#F0FDF4;border:1.5px solid #BBF7D0'
                            : 'background:#EFF6FF;border:1.5px solid #BFDBFE')">
                    {{-- Avatar --}}
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 text-base font-black text-white"
                         :style="isJoined
                            ? (participantStatus==='ready' ? 'background:linear-gradient(135deg,#10B981,#059669)' : 'background:linear-gradient(135deg,#3B82F6,#1d4ed8)')
                            : 'background:#CBD5E1'">
                        <i :class="isJoined ? (participantStatus==='ready' ? 'fas fa-check' : 'fas fa-user') : 'fas fa-user-clock'"></i>
                    </div>
                    {{-- Teks status --}}
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-sm"
                             :class="!isJoined ? 'text-slate-600' : (participantStatus==='ready' ? 'text-emerald-800' : 'text-blue-800')"
                             x-text="!roomOpen ? 'Menunggu guru membuka ruangan' :
                                      (!isJoined ? 'Klik Bergabung untuk masuk' :
                                      (participantStatus==='ready' ? ' Siap! Menunggu quiz dimulai...' : 'Bergabung — klik Saya Siap!'))">
                        </div>
                        <div class="text-slate-400 text-xs mt-0.5 flex items-center gap-2">
                            <span x-text="stats.joined + ' peserta bergabung'"></span>
                            <span x-show="stats.ready > 0">
                                &bull; <span x-text="stats.ready + ' siap'"></span>
                            </span>
                        </div>
                    </div>
                    {{-- Status pill --}}
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full flex-shrink-0"
                          :class="{
                              'bg-emerald-100 text-emerald-700 border border-emerald-200': participantStatus === 'ready',
                              'bg-blue-100 text-blue-700 border border-blue-200': participantStatus === 'waiting',
                              'bg-slate-100 text-slate-500': !isJoined
                          }"
                          x-text="isJoined ? getStatusText(participantStatus) : 'Belum masuk'">
                    </span>
                </div>
            </div>
        </div>

        {{-- Info tambahan: 2 kolom sejajar --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
            {{-- Fitur keamanan --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
                <div class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-3 flex items-center gap-1.5">
                    <i class="fas fa-shield-alt text-blue-500"></i> Pengaturan
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500 flex items-center gap-1.5">
                            <i class="fas fa-bolt text-amber-400 w-3.5 text-center"></i> Feedback
                        </span>
                        <span class="font-bold {{ $quiz->instant_feedback ? 'text-emerald-600' : 'text-slate-400' }}">
                            {{ $quiz->instant_feedback ? 'Instan' : 'Nonaktif' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500 flex items-center gap-1.5">
                            <i class="fas fa-random text-violet-400 w-3.5 text-center"></i> Acak Soal
                        </span>
                        <span class="font-bold {{ $quiz->shuffle_question ? 'text-emerald-600' : 'text-slate-400' }}">
                            {{ $quiz->shuffle_question ? 'Ya' : 'Tidak' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500 flex items-center gap-1.5">
                            <i class="fas fa-eye text-blue-400 w-3.5 text-center"></i> Lihat Skor
                        </span>
                        <span class="font-bold {{ $quiz->show_score ? 'text-emerald-600' : 'text-slate-400' }}">
                            {{ $quiz->show_score ? 'Ya' : 'Tidak' }}
                        </span>
                    </div>
                    @if($quiz->fullscreen_mode || $quiz->block_new_tab)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500 flex items-center gap-1.5">
                            <i class="fas fa-lock text-red-400 w-3.5 text-center"></i> Mode Aman
                        </span>
                        <span class="font-bold text-red-500">Aktif</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Tips --}}
            <div class="rounded-2xl border border-blue-100 p-4"
                 style="background: linear-gradient(135deg, #eff6ff, #dbeafe);">
                <div class="text-xs font-bold text-blue-400 uppercase tracking-wide mb-3 flex items-center gap-1.5">
                    <i class="fas fa-lightbulb text-amber-400"></i> Tips
                </div>
                <ul class="space-y-2 text-sm text-blue-800">
                    @if($quiz->quiz_mode === 'guided')
                        <li class="flex items-start gap-2"><i class="fas fa-dot-circle text-blue-400 mt-0.5 flex-shrink-0"></i> Soal dikontrol guru di layar proyektor</li>
                        <li class="flex items-start gap-2"><i class="fas fa-dot-circle text-blue-400 mt-0.5 flex-shrink-0"></i> Jawab di perangkatmu saat soal ditampilkan</li>
                    @elseif($quiz->quiz_mode === 'live')
                        <li class="flex items-start gap-2"><i class="fas fa-dot-circle text-blue-400 mt-0.5 flex-shrink-0"></i> Kerjakan sendiri setelah quiz dimulai</li>
                        <li class="flex items-start gap-2"><i class="fas fa-dot-circle text-blue-400 mt-0.5 flex-shrink-0"></i> Jangan tutup atau pindah tab browser</li>
                    @endif
                    @if($quiz->time_per_question > 0)
                        <li class="flex items-start gap-2"><i class="fas fa-clock text-blue-400 mt-0.5 flex-shrink-0"></i> Tiap soal punya batas {{ $quiz->time_per_question }} detik</li>
                    @endif
                    <li class="flex items-start gap-2"><i class="fas fa-wifi text-blue-400 mt-0.5 flex-shrink-0"></i> Pastikan koneksi internet stabil</li>
                </ul>
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
                <div class="text-sm font-semibold text-blue-600" x-show="stats.total > 0">
                    <span x-text="stats.joined"></span>/<span x-text="stats.total"></span> siswa bergabung
                </div>
            </div>

            <div class="participants-grid">
                <template x-if="participants.length === 0">
                    <div class="empty-participants">
                        <div class="empty-icon"><i class="fas fa-users" style="color:#BFDBFE"></i></div>
                        <h3 class="font-bold text-gray-700 mb-1">Belum ada peserta</h3>
                        <p class="text-sm">@if($isGuru) Buka ruangan dan siswa akan muncul di sini. @else Bergabung ke ruangan untuk tampil di sini. @endif</p>
                    </div>
                </template>

                <template x-for="p in sortedParticipants" :key="p.id">
                    <div class="p-card"
                         :class="{ 'violation-card': p.has_violation }"
                         :style="p.has_violation
                            ? 'border-color:#EF4444!important;background:linear-gradient(135deg,#FEF2F2,#FEE2E2)!important;box-shadow:0 0 0 2px rgba(239,68,68,0.25),0 6px 24px rgba(239,68,68,0.22)!important'
                            : ''">

                        {{-- TOP BAR: Normal (hijau tipis) vs Violation (merah nyala) --}}
                        <div x-show="!p.has_violation"
                             class="h-1 w-full rounded-full mb-2"
                             :style="p.status==='submitted' ? 'background:#A7F3D0'
                                   : p.status==='started'   ? 'background:#93C5FD'
                                   : p.status==='ready'     ? 'background:#6EE7B7'
                                   :                          'background:#FDE68A'">
                        </div>
                        <div x-show="p.has_violation"
                             class="flex items-center justify-between mb-2 px-2.5 py-1.5 rounded-xl"
                             style="background:rgba(239,68,68,0.14);border:1px solid rgba(239,68,68,0.3)">
                            <span class="violation-badge flex items-center gap-1.5">
                                <i class="fas fa-exclamation-triangle text-red-500" style="font-size:0.7rem"></i>
                                <span class="text-red-700 font-black text-xs" x-text="p.violation_count + ' Pelanggaran'"></span>
                            </span>
                            @if($isGuru)
                            <button @click="showViolationDetail(p.id)"
                                class="flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-bold text-white transition-all hover:scale-105"
                                style="background:linear-gradient(135deg,#EF4444,#DC2626);box-shadow:0 2px 8px rgba(239,68,68,0.35)">
                                <i class="fas fa-shield-virus" style="font-size:0.6rem"></i> Lihat
                            </button>
                            @endif
                        </div>

                        {{-- Avatar + nama + status dot --}}
                        <div class="flex items-center gap-2.5 mb-2">
                            <div class="p-avatar flex-shrink-0"
                                 :style="p.has_violation
                                    ? 'background:linear-gradient(135deg,#EF4444,#DC2626)'
                                    : 'background:linear-gradient(135deg,#1d4ed8,#3b82f6)'"
                                 x-text="p.initial">?</div>
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-sm truncate"
                                     :class="p.has_violation ? 'text-red-900' : 'text-gray-900'"
                                     x-text="p.name"></div>
                                <div class="text-xs truncate"
                                     :class="p.has_violation ? 'text-red-400' : 'text-gray-400'"
                                     x-text="p.email"></div>
                            </div>
                            <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                <div class="p-status-dot" :class="p.status"></div>
                                <span class="status-pill" :class="p.status" x-text="getStatusText(p.status)"></span>
                            </div>
                        </div>

                        {{-- Join time --}}
                        <div class="text-xs mb-2.5 flex items-center gap-1"
                             :class="p.has_violation ? 'text-red-400' : 'text-gray-400'">
                            <i class="fas fa-clock" style="font-size:0.65rem"></i>
                            <span x-text="p.joined_time || '-'"></span>
                        </div>

                        @if($isGuru)
                        {{-- Tombol aksi --}}
                        <div class="flex gap-1 flex-wrap">
                            <button @click="markParticipantAsReady(p.id)"
                                x-show="p.status === 'waiting' || p.status === 'not_joined'"
                                class="p-btn ready-btn flex-1">
                                <i class="fas fa-check mr-1"></i> Siapkan
                            </button>
                            {{-- Tombol DETAIL PELANGGARAN — selalu tampil untuk guru --}}
                            <button @click="showViolationDetail(p.id)"
                                x-show="p.has_violation"
                                class="p-btn flex-1"
                                style="background:linear-gradient(135deg,#FEE2E2,#FECACA);color:#991B1B;border:1px solid #FCA5A5;font-weight:800;">
                                <i class="fas fa-file-shield mr-1"></i> Detail
                            </button>
                            <button @click="warnParticipant(p.id, p.name)"
                                x-show="p.has_violation"
                                class="p-btn warn-btn"
                                style="flex:0 0 auto;min-width:2rem;">
                                <i class="fas fa-bell"></i>
                            </button>
                            <button @click="kickParticipant(p.id)" class="p-btn kick-btn"
                                style="min-width: 2.25rem; flex: 0 0 auto;">
                                <i class="fas fa-times"></i> Keluarkan
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
                        'rank-1': entry.rank === 1, 'rank-2': entry.rank === 2,
                        'rank-3': entry.rank === 3, 'rank-other': entry.rank > 3
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
                            <div class="font-black text-blue-600 text-lg" x-text="(entry.score || 0) + ' pts'"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ===== WARNING POPUP UNTUK SISWA ===== --}}
@if($isMurid)
<div id="warning-overlay" class="warning-overlay" style="display:none;">
    <div class="warning-popup">
        <div class="warning-icon-wrap">⚠️</div>
        <h2 style="font-size:1.2rem;font-weight:900;color:#991B1B;margin-bottom:0.5rem;">Peringatan dari Guru!</h2>
        <p id="warning-message" style="font-size:0.9rem;color:#6B7280;margin-bottom:1.5rem;line-height:1.6;">
            Guru mengingatkanmu untuk tidak melakukan kecurangan selama quiz berlangsung.
        </p>
        <div style="background:#FEF2F2;border:1.5px solid #FECACA;border-radius:12px;padding:0.75rem 1rem;margin-bottom:1.25rem;font-size:0.8rem;color:#991B1B;font-weight:700;">
            <i class="fas fa-shield-alt mr-1"></i> Pelanggaran lebih lanjut dapat mengakibatkan kamu dikeluarkan dari quiz.
        </div>
        <button onclick="closeWarning()" style="width:100%;padding:0.75rem;border-radius:12px;background:linear-gradient(135deg,#EF4444,#DC2626);color:white;font-weight:800;font-size:0.95rem;border:none;cursor:pointer;">
            <i class="fas fa-check mr-2"></i> Saya Mengerti
        </button>
    </div>
</div>
<script>
function showWarning(msg) {
    const overlay = document.getElementById('warning-overlay');
    const msgEl   = document.getElementById('warning-message');
    if (msgEl && msg) msgEl.textContent = msg;
    if (overlay) { overlay.style.display = 'flex'; }
}
function closeWarning() {
    const overlay = document.getElementById('warning-overlay');
    if (overlay) { overlay.style.opacity = '0'; overlay.style.transition = 'opacity 0.3s'; setTimeout(() => { overlay.style.display = 'none'; overlay.style.opacity = '1'; }, 300); }
}
// Poll untuk warning dari guru setiap 4 detik
(function pollWarning() {
    const quizId = {{ $quiz->id }};
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    let lastWarnId = null;
    setInterval(async () => {
        try {
            const r = await fetch(`/quiz/${quizId}/room/check-warning`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } });
            if (!r.ok) return;
            const d = await r.json();
            if (d.warning && d.warning.id && d.warning.id !== lastWarnId) {
                lastWarnId = d.warning.id;
                showWarning(d.warning.message || 'Guru mengingatkanmu untuk berperilaku jujur selama quiz!');
            }
        } catch(e) {}
    }, 4000);
})();
</script>
@endif

{{-- ===== EXPIRED OVERLAY ===== --}}
<div id="expired-overlay">
    <h2>Waktu Quiz Habis!</h2>
    <p id="expired-desc">Durasi quiz telah habis. Semua siswa yang belum selesai akan disubmit otomatis.</p>
    <button class="exp-btn" id="expired-btn">
        <i class="fas fa-home mr-2"></i> Kembali ke Daftar Quiz
    </button>
</div>

{{-- ===== COUNTDOWN OVERLAY ===== --}}
<div id="countdown-overlay">
    <div id="countdown-text">Quiz dimulai dalam</div>
    <div id="countdown-number">3</div>
    <div class="countdown-bar">
        <div class="countdown-bar-fill" id="countdown-bar-fill" style="width:100%"></div>
    </div>
    <p style="color:rgba(255,255,255,0.7);font-size:1rem;font-weight:600">Bersiap mengerjakan...</p>
</div>

<div id="notif-container"></div>

<script>
    // URLs
    @if($isGuru)
    const ROOM_STATUS_URL = '{{ route('guru.quiz.room.status', $quiz->id) }}';
    const OPEN_ROOM_URL   = '{{ route('guru.quiz.room.open',   $quiz->id) }}';
    const CLOSE_ROOM_URL  = '{{ route('guru.quiz.room.close',  $quiz->id) }}';
    const START_QUIZ_URL  = '{{ route('guru.quiz.room.start',  $quiz->id) }}';
    const STOP_QUIZ_URL   = '{{ route('guru.quiz.room.stop',   $quiz->id) }}';
    const KICK_URL        = '{{ route('guru.quiz.room.kick',        [$quiz->id, 'PART_ID']) }}';
    const READY_URL       = '{{ route('guru.quiz.room.mark-ready',  [$quiz->id, 'PART_ID']) }}';
    const WARN_URL        = '{{ route('guru.quiz.room.warn',        [$quiz->id, 'PART_ID']) }}';
    const LEADERBOARD_URL = '{{ route('guru.quiz.leaderboard',  $quiz->id) }}';
    @if($isGuidedMode)
    const GUIDED_URL      = '{{ route('guru.quiz.guided', $quiz->id) }}';
    @endif
    @elseif($isMurid)
    const ROOM_STATUS_URL = '{{ route('quiz.room.status',    $quiz->id) }}';
    const JOIN_ROOM_URL   = '{{ route('quiz.join-room',      $quiz->id) }}';
    const MARK_READY_URL  = '{{ route('quiz.room.mark-ready',$quiz->id) }}';
    @if($isGuidedMode)
    const PLAY_QUIZ_URL   = '{{ route('quiz.play',           $quiz->id) }}';
    @else
    const PLAY_QUIZ_URL   = '{{ route('quiz.play',           $quiz->id) }}';
    @endif
    const LEADERBOARD_URL = '{{ route('quiz.leaderboard',    $quiz->id) }}';
    @endif
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    const IS_GUIDED  = {{ $isGuidedMode ? 'true' : 'false' }};

    function roomApp() {
        return {
            roomOpen:    {{ $quiz->is_room_open    ? 'true' : 'false' }},
            quizStarted: {{ $quiz->is_quiz_started ? 'true' : 'false' }},
            participants: [],
            leaderboard:  [],
            activeTab:    'participants',
            lastUpdated:  null,

            // ── Duration timer ──
            timeRemainingText:    '--:--',
            timeRemainingSeconds: null,   // null = belum diinisialisasi
            _timerTick:           null,   // setInterval handle
            _warnedFiveMin:       false,
            _warnedOneMin:        false,
            // ───────────────────

            stats: {
                total: {{ optional($quiz->class)->students()->count() ?? 0 }},
                joined: 0, ready: 0, started: 0, submitted: 0
            },
            participantStatus: '{{ $participant->status ?? 'not_joined' }}',
            _pollInterval: null,

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

            // ─── Timer: mulai countdown lokal ────────────────────────────────
            // Menggunakan wall-clock (Date.now) agar tidak drift karena setInterval tidak presisi
            startTimer(seconds) {
                this.stopTimer();
                this.timeRemainingSeconds = seconds;
                this.timeRemainingText    = this.formatTime(seconds);
                this._warnedFiveMin       = seconds <= 300;
                this._warnedOneMin        = seconds <= 60;

                // Simpan referensi waktu mulai untuk koreksi wall-clock
                this._timerStartedAt      = Date.now();
                this._timerStartSeconds   = seconds;

                this._timerTick = setInterval(() => {
                    if (!this.quizStarted) { this.stopTimer(); return; }

                    // Hitung dari wall-clock — tidak pernah naik akibat tab sleep/resume
                    const elapsed = Math.floor((Date.now() - this._timerStartedAt) / 1000);
                    this.timeRemainingSeconds = Math.max(0, this._timerStartSeconds - elapsed);
                    this.timeRemainingText    = this.formatTime(this.timeRemainingSeconds);

                    if (!this._warnedFiveMin && this.timeRemainingSeconds <= 300) {
                        this._warnedFiveMin = true;
                        this.showNotif('warning', '⚠️ Waktu quiz tersisa 5 menit!');
                    }
                    if (!this._warnedOneMin && this.timeRemainingSeconds <= 60) {
                        this._warnedOneMin = true;
                        this.showNotif('error', '🚨 Waktu quiz tersisa 1 menit!');
                    }

                    if (this.timeRemainingSeconds <= 0) {
                        this.stopTimer();
                        this.handleExpired();
                    }
                }, 1000);
            },

            stopTimer() {
                if (this._timerTick) { clearInterval(this._timerTick); this._timerTick = null; }
            },

            // Dipanggil saat timer = 0 atau server konfirmasi expired
            handleExpired() {
                this.stopTimer();
                // Hentikan polling SELAMANYA agar tidak restart timer
                if (this._pollInterval) { clearInterval(this._pollInterval); this._pollInterval = null; }
                this.timeRemainingSeconds = 0;
                this.timeRemainingText    = '00:00';

                document.getElementById('expired-overlay')?.classList.add('active');

                @if($isGuru)
                const desc = document.getElementById('expired-desc');
                const btn  = document.getElementById('expired-btn');
                if (desc) desc.textContent = 'Durasi quiz habis. Menutup room dan mengumpulkan jawaban siswa...';
                if (btn)  btn.onclick = () => window.location.href = '{{ route("guru.quiz.index") }}';

                // Otomatis stop quiz via API (tutup room + force submit semua siswa)
                fetch(STOP_QUIZ_URL, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' }
                }).finally(() => {
                    // Redirect ke index setelah 3 detik
                    setTimeout(() => { window.location.href = '{{ route("guru.quiz.index") }}'; }, 3000);
                });
                @else
                const desc = document.getElementById('expired-desc');
                const btn  = document.getElementById('expired-btn');
                if (desc) desc.textContent = 'Durasi quiz habis. Jawabanmu telah dikumpulkan otomatis.';
                if (btn)  { btn.textContent = 'Kembali ke Daftar Quiz'; btn.onclick = () => { window.location.href = '{{ route("quiz.index") }}'; }; }
                // Redirect siswa ke daftar quiz setelah 3 detik
                setTimeout(() => { window.location.href = '{{ route("quiz.index") }}'; }, 3000);
                @endif
            },
            // ─────────────────────────────────────────────────────────────────

            async init() {
                // Load data pertama kali — ini juga akan set timer dari server
                await this.loadRoomData();

                // Poll setiap 3 detik untuk sinkronisasi real-time
                this._pollInterval = setInterval(() => this.loadRoomData(), 3000);

                @if($isMurid)
                if (this.roomOpen && !this.isJoined && !this.quizStarted) {
                    setTimeout(() => this.joinRoom(), 1200);
                }
                if (this.quizStarted && (this.participantStatus === 'started' || this.participantStatus === 'ready')) {
                    this.startCountdown();
                }
                @endif

                @if($isGuru && $isGuidedMode)
                if (this.quizStarted) {
                    setTimeout(() => { window.location.href = GUIDED_URL; }, 800);
                }
                @endif

                @if($isGuru)
                // Jika quiz sudah berjalan saat page load, inisialisasi timer dari durasi
                // (akan langsung disinkron oleh loadRoomData → applyResponse)
                if (this.quizStarted && {{ $quiz->duration ?? 0 }} > 0) {
                    // Timer diinisialisasi oleh applyResponse dari loadRoomData di atas
                    // Tidak perlu startTimer manual — server sudah kirim time_remaining real-time
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
                // Support dua format: flat (is_room_open) dan nested (quiz.is_room_open)
                const quizData      = data.quiz || {};
                const isRoomOpen    = data.is_room_open    ?? quizData.is_room_open;
                const isQuizStarted = data.is_quiz_started ?? quizData.is_quiz_started;
                // time_remaining real-time dari server (dihitung dari quiz_started_at + duration)
                const timeRemote    = data.time_remaining  ?? quizData.quiz_remaining_time ?? null;
                const quizExpired   = Boolean(data.quiz_expired);

                if (isRoomOpen !== undefined) this.roomOpen = Boolean(isRoomOpen);

                if (isQuizStarted !== undefined) {
                    const prevStarted = this.quizStarted;
                    this.quizStarted  = Boolean(isQuizStarted);

                    @if($isGuru && $isGuidedMode)
                    if (!prevStarted && this.quizStarted) {
                        this.showNotif('success', 'Quiz dimulai! Membuka panel kontrol soal...');
                        setTimeout(() => { window.location.href = GUIDED_URL; }, 1200);
                    }
                    @endif

                    // Quiz dihentikan (manual guru ATAU otomatis habis waktu)
                    if (prevStarted && !this.quizStarted) {
                        this.stopTimer();
                        if (quizExpired) { this.handleExpired(); return; }
                    }

                    // Server kirim quiz_expired tanpa is_quiz_started berubah (edge case)
                    if (quizExpired) { this.handleExpired(); return; }
                }

                // ── Sinkronisasi timer durasi dengan server ───────────────────
                if (timeRemote !== null && timeRemote !== undefined) {
                    const srv = parseInt(timeRemote);

                    // Guard: jika expired atau waktu habis → handleExpired sekali, tidak boleh startTimer
                    if (quizExpired || srv <= 0) {
                        if (!document.getElementById('expired-overlay')?.classList.contains('active')) {
                            this.handleExpired();
                        }
                        return;
                    }

                    if (this.quizStarted) {
                        if (this._timerTick === null) {
                            // Timer belum berjalan → mulai dari nilai server
                            this.startTimer(srv);
                        } else if (srv < this.timeRemainingSeconds) {
                            // Server lebih kecil → koreksi (timer terlalu lambat), tidak boleh naik
                            this._timerStartedAt    = Date.now();
                            this._timerStartSeconds = srv;
                            this.timeRemainingSeconds = srv;
                            this.timeRemainingText    = this.formatTime(srv);
                        }
                        // srv > timeRemainingSeconds → abaikan (jangan biarkan timer naik)
                    } else {
                        this.timeRemainingText = this.formatTime(srv);
                    }
                }
                // ─────────────────────────────────────────────────────────────

                if (data.stats) {
                    this.stats = {
                        total:     data.stats.total_students || data.stats.total || this.stats.total,
                        joined:    data.stats.joined    || 0,
                        ready:     data.stats.ready     || 0,
                        started:   data.stats.started   || 0,
                        submitted: data.stats.submitted || 0,
                    };
                }

                if (Array.isArray(data.participants)) {
                    this.participants = data.participants.map(p => {
                        const name = p.student_name || p.name || 'Unknown';
                        const vc   = parseInt(p.violation_count) || 0;
                        // Pakai is_red_card ATAU has_violation dari server, fallback ke vc > 0
                        const hasViolation = !!(p.is_red_card || p.has_violation || vc > 0);

                        // Simpan detail untuk modal pelanggaran
                        window._violationData = window._violationData || {};
                        window._violationData[p.id] = {
                            name,
                            violation_count: vc,
                            violations: p.violations || [],
                        };

                        return {
                            id: p.id, student_id: p.student_id,
                            name, email: p.student_email || p.email || '',
                            status: p.status || 'waiting',
                            joined_time: p.joined_at || p.joined_time || '-',
                            initial: name.charAt(0).toUpperCase(),
                            violation_count: vc,
                            has_violation: hasViolation,
                        };
                    });
                    this.updateViolationBar();
                }

                @if($isMurid)
                if (data.participant) {
                    const prevStatus = this.participantStatus;
                    this.participantStatus = data.participant.status || 'not_joined';
                    if (this.quizStarted && prevStatus !== 'started' && this.participantStatus === 'started') {
                        this.showNotif('success', 'Quiz dimulai! Bersiap...');
                        this.startCountdown();
                    }
                }
                @endif

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
                const map = {
                    not_joined: 'Belum Bergabung', waiting: 'Menunggu',
                    ready: 'Siap', started: 'Mengerjakan',
                    submitted: 'Selesai', disconnected: 'Terputus'
                };
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
                if (this.stats.ready === 0 && !confirm('Belum ada siswa yang siap. Mulai quiz tetap?')) return;
                if (this.stats.ready > 0 && !confirm(`Mulai quiz? ${this.stats.ready} siswa siap.`)) return;
                try {
                    const r = await fetch(START_QUIZ_URL, { method:'POST', headers:{ 'X-CSRF-TOKEN':CSRF_TOKEN, 'Accept':'application/json' }});
                    const data = await r.json();
                    if (data.success) {
                        this.quizStarted = true;
                        this.showNotif('success', data.message || 'Quiz dimulai!');
                        await this.loadRoomData();
                        @if($isGuidedMode)
                        // Guided mode: langsung ke panel kontrol soal
                        setTimeout(() => { window.location.href = GUIDED_URL; }, 1500);
                        @endif
                    }
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
            async warnParticipant(participantId, participantName) {
                const customMsg = prompt(`Kirim peringatan ke ${participantName}:\n(Kosongkan untuk pesan default)`, '');
                if (customMsg === null) return; // user cancel
                const message = customMsg.trim() || `${participantName}, guru mengingatkanmu untuk tidak melakukan kecurangan selama quiz berlangsung!`;
                try {
                    const url = WARN_URL.replace('PART_ID', participantId);
                    const r = await fetch(url, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ message })
                    });
                    const data = await r.json();
                    if (data.success) { this.showNotif('warning', `⚠ Peringatan dikirim ke ${participantName}`); }
                    else this.showNotif('error', data.message || 'Gagal mengirim peringatan');
                } catch(e) { this.showNotif('error', 'Terjadi kesalahan saat mengirim peringatan'); }
            },

            showViolationDetail(participantId) {
                const d = (window._violationData || {})[participantId];
                if (!d) { this.showNotif('info', 'Data pelanggaran belum tersedia'); return; }

                const modal = document.getElementById('violation-modal');
                document.getElementById('vm-name').textContent  = d.name;
                document.getElementById('vm-count').textContent = d.violation_count + ' pelanggaran terdeteksi';

                const list = document.getElementById('vm-list');
                list.innerHTML = '';

                const labelMap = {
                    tab_switch:      '🔀 Pindah Tab / Aplikasi',
                    window_blur:     '👁️ Keluar Window',
                    copy:            '📋 Mencoba Copy',
                    cut:             '✂️ Mencoba Cut',
                    paste:           '📋 Mencoba Paste',
                    right_click:     '🖱️ Klik Kanan',
                    fullscreen_exit: '🖥️ Keluar Fullscreen',
                    devtools:        '🔧 Membuka DevTools',
                    devtools_open:   '🔧 DevTools Terdeteksi',
                    view_source:     '👁️ Mencoba Lihat Sumber',
                    save_page:       '💾 Mencoba Simpan Halaman',
                    print:           '🖨️ Mencoba Print',
                    screenshot:      '📸 Screenshot',
                    blur:            '👁️ Keluar Window / Blur',
                    visibility:      '🫣 Menyembunyikan Tab',
                };

                if (!d.violations || d.violations.length === 0) {
                    list.innerHTML = '<div style="text-align:center;padding:1.5rem;color:#9CA3AF;font-size:0.85rem;"><i class="fas fa-info-circle" style="font-size:1.5rem;display:block;margin-bottom:0.5rem;color:#CBD5E1;"></i>Detail belum tercatat.<br><span style="font-size:0.75rem;">Data masuk saat siswa melanggar selama quiz.</span></div>';
                } else {
                    d.violations.forEach((v, i) => {
                        const label = labelMap[v.type] || ('⚠️ ' + (v.type || 'Tidak diketahui'));
                        const el = document.createElement('div');
                        el.style.cssText = 'background:#FEF2F2;border:1.5px solid #FECACA;border-radius:10px;padding:0.6rem 0.875rem;margin-bottom:0.4rem;';
                        el.innerHTML =
                            `<div style="font-size:0.82rem;font-weight:800;color:#991B1B;">#${i+1} — ${label}</div>` +
                            (v.details || v.message ? `<div style="font-size:0.75rem;color:#6B7280;margin-top:2px;">${v.details || v.message}</div>` : '') +
                            (v.timestamp ? `<div style="font-size:0.7rem;color:#9CA3AF;margin-top:3px;"><i class="fas fa-clock mr-1"></i>${(() => { try { return new Date(v.timestamp).toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit',second:'2-digit'}); } catch(e) { return v.timestamp; } })()}</div>` : '');
                        list.appendChild(el);
                    });
                }

                document.getElementById('vm-warn-btn').onclick = () => {
                    modal.style.display = 'none';
                    this.warnParticipant(participantId, d.name);
                };

                modal.style.display = 'flex';
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

            // ===== COUNTDOWN =====
            startCountdown() {
                @if($isMurid)
                const overlay   = document.getElementById('countdown-overlay');
                const numEl     = document.getElementById('countdown-number');
                const barFill   = document.getElementById('countdown-bar-fill');
                if (!overlay) { window.location.href = PLAY_QUIZ_URL; return; }
                overlay.classList.add('active');
                let count = 3;
                numEl.textContent = count;
                if (barFill) barFill.style.width = '100%';
                const tick = setInterval(() => {
                    count--;
                    if (count <= 0) {
                        clearInterval(tick);
                        numEl.textContent = 'GO!';
                        if (barFill) barFill.style.width = '0%';
                        setTimeout(() => { window.location.href = PLAY_QUIZ_URL; }, 700);
                    } else {
                        numEl.textContent = count;
                        if (barFill) barFill.style.width = (count / 3 * 100) + '%';
                    }
                }, 1000);
                @endif
            },

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
                setTimeout(() => {
                    if (el.parentElement) {
                        el.style.opacity='0'; el.style.transform='translateX(60px)'; el.style.transition='all 0.3s';
                        setTimeout(() => el.remove(), 300);
                    }
                }, 5000);
            }
        }
    }
</script>

@if(session('error'))
<script>
window.addEventListener('load', () => {
    setTimeout(() => {
        const app = document.querySelector('[x-data]');
        if (app && app._x_dataStack) {
            app._x_dataStack[0].showNotif('error', '{{ session('error') }}');
        }
    }, 500);
});
</script>
@endif

{{-- Global store pelanggaran --}}
<script>window._violationData = {};</script>

@if($isGuru)
{{-- ===== MODAL DETAIL PELANGGARAN ===== --}}
<div id="violation-modal"
     style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.6);
            backdrop-filter:blur(4px);align-items:center;justify-content:center;"
     onclick="if(event.target===this)this.style.display='none'">
    <div style="background:white;border-radius:22px;padding:1.75rem;max-width:440px;width:92%;
                box-shadow:0 24px 64px rgba(239,68,68,0.28);border:2px solid #FECACA;
                animation:vmPop .3s cubic-bezier(.34,1.56,.64,1);">
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.25rem;">
            <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#FEE2E2,#FECACA);
                        border:2px solid #EF4444;display:flex;align-items:center;justify-content:center;
                        font-size:1.4rem;flex-shrink:0;">⚠️</div>
            <div style="flex:1;min-width:0;">
                <div id="vm-name" style="font-size:1rem;font-weight:900;color:#991B1B;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></div>
                <div id="vm-count" style="font-size:0.78rem;color:#EF4444;font-weight:700;margin-top:2px;"></div>
            </div>
            <button onclick="document.getElementById('violation-modal').style.display='none'"
                    style="background:#FEE2E2;border:none;border-radius:50%;width:32px;height:32px;cursor:pointer;
                           color:#991B1B;display:flex;align-items:center;justify-content:center;font-size:0.8rem;flex-shrink:0;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div style="border-top:1.5px solid #FECACA;padding-top:1rem;">
            <div style="font-size:0.8rem;font-weight:800;color:#991B1B;margin-bottom:0.6rem;">
                <i class="fas fa-list-ul mr-1"></i> Riwayat Pelanggaran
            </div>
            <div id="vm-list" style="max-height:240px;overflow-y:auto;"></div>
        </div>
        <div style="margin-top:1.25rem;display:flex;gap:0.75rem;">
            <button id="vm-warn-btn"
                    style="flex:1;padding:0.65rem;border-radius:12px;background:linear-gradient(135deg,#F59E0B,#D97706);
                           color:white;font-weight:800;border:none;cursor:pointer;font-size:0.85rem;">
                <i class="fas fa-bell mr-1"></i> Kirim Peringatan
            </button>
            <button onclick="document.getElementById('violation-modal').style.display='none'"
                    style="padding:0.65rem 1.2rem;border-radius:12px;background:#F3F4F6;color:#374151;
                           font-weight:700;border:none;cursor:pointer;font-size:0.85rem;">
                Tutup
            </button>
        </div>
    </div>
</div>
<style>
@keyframes vmPop { from{opacity:0;transform:scale(0.8)} to{opacity:1;transform:scale(1)} }
</style>
@endif

</body>
</html>
