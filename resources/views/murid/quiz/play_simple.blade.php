<!DOCTYPE html>
<html lang="id" x-data="quizPlayer()" x-init="init()">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quiz->title }} — Quiz</title>
    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --primary: #6C3DE5;
            --primary-light: #8B5CF6;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --bg: #F4F4FE;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Nunito', sans-serif;
            background: var(--bg);
            color: #111827;
            min-height: 100vh;
        }

        body.dark {
            background: #0F172A;
            color: #F1F5F9;
        }

        /* ===== SECURITY OVERLAYS ===== */
        #security-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.97);
            z-index: 99999;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.25rem;
            text-align: center;
            padding: 2rem;
        }

        #security-overlay.active { display: flex; }

        #security-overlay .big-icon { font-size: 4rem; color: #EF4444; animation: shake 0.5s ease; }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%,60% { transform: translateX(-12px); }
            40%,80% { transform: translateX(12px); }
        }

        #security-overlay h2 { color: white; font-size: 1.5rem; font-weight: 900; }
        #security-overlay p { color: #94A3B8; font-size: 0.9rem; max-width: 380px; }

        #security-overlay .resume-btn {
            background: linear-gradient(135deg, #6C3DE5, #8B5CF6);
            color: white; border: none; border-radius: 14px;
            padding: 0.9rem 2.5rem; font-size: 1rem;
            font-weight: 800; cursor: pointer;
            box-shadow: 0 8px 24px rgba(108,61,229,0.4);
            transition: transform 0.2s;
        }

        #security-overlay .resume-btn:hover { transform: translateY(-2px); }

        #violation-toast {
            display: none;
            position: fixed;
            top: 5rem; left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #EF4444, #DC2626);
            color: white;
            padding: 0.9rem 2rem;
            border-radius: 14px;
            font-weight: 700;
            font-size: 0.9rem;
            z-index: 99998;
            box-shadow: 0 8px 32px rgba(239,68,68,0.4);
            animation: toastDrop 0.3s ease;
            text-align: center;
        }

        #violation-toast.show { display: block; }

        @keyframes toastDrop {
            from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
            to { opacity: 1; transform: translateX(-50%) translateY(0); }
        }

        #fullscreen-prompt {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.99);
            z-index: 99997;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            text-align: center;
            padding: 2rem;
        }

        #fullscreen-prompt.active { display: flex; }
        #fullscreen-prompt h2 { color: white; font-size: 2rem; font-weight: 900; }
        #fullscreen-prompt p { color: #CBD5E1; max-width: 380px; font-size: 0.9rem; }

        #fullscreen-prompt .fs-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, #6C3DE5, #8B5CF6);
            color: white;
            border: none;
            padding: 1.1rem 2.5rem;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(108,61,229,0.4);
            transition: transform 0.2s;
        }

        #fullscreen-prompt .fs-btn:hover { transform: translateY(-2px); }

        /* ===== TOPBAR ===== */
        .quiz-topbar {
            background: linear-gradient(135deg, #6C3DE5, #8B5CF6);
            padding: 0.875rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            position: sticky;
            top: 0;
            z-index: 40;
            box-shadow: 0 4px 20px rgba(108,61,229,0.3);
        }

        .quiz-topbar-title {
            font-weight: 900;
            font-size: 1rem;
            color: white;
            letter-spacing: -0.3px;
        }

        .quiz-topbar-meta { font-size: 0.75rem; color: rgba(255,255,255,0.8); }

        /* ===== PROGRESS BAR ===== */
        .progress-bar {
            height: 5px;
            background: rgba(255,255,255,0.2);
            border-radius: 999px;
            overflow: hidden;
            flex: 1;
            max-width: 300px;
        }

        .progress-fill {
            height: 100%;
            background: white;
            border-radius: 999px;
            transition: width 0.5s ease;
        }

        /* ===== TIMER ===== */
        .timer-ring-container {
            position: relative;
            width: 72px;
            height: 72px;
            flex-shrink: 0;
        }

        .timer-ring-container svg { transform: rotate(-90deg); }

        .timer-ring-bg { fill: none; stroke: rgba(255,255,255,0.2); stroke-width: 6; }
        .timer-ring-fg {
            fill: none;
            stroke: white;
            stroke-width: 6;
            stroke-linecap: round;
            stroke-dasharray: 188;
            transition: stroke-dashoffset 1s linear, stroke 0.3s;
        }

        .timer-ring-text {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 900;
            color: white;
        }

        .timer-ring-fg.warning { stroke: #FDE68A; }
        .timer-ring-fg.danger { stroke: #FCA5A5; animation: timerPulse 0.5s infinite; }

        @keyframes timerPulse { 0%,100% { opacity: 1; } 50% { opacity: 0.6; } }

        /* ===== QUESTION CARD ===== */
        .question-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(108,61,229,0.1);
            border: 2px solid #EDE9FE;
        }

        body.dark .question-card {
            background: #1E293B;
            border-color: #334155;
        }

        .question-number {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: linear-gradient(135deg, #6C3DE5, #8B5CF6);
            color: white;
            padding: 0.3rem 0.9rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .question-text {
            font-size: 1.25rem;
            font-weight: 800;
            color: #111827;
            line-height: 1.55;
            margin-bottom: 1.5rem;
        }

        body.dark .question-text { color: #F1F5F9; }

        /* ===== ANSWER OPTIONS ===== */
        .answers-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.875rem;
        }

        @media (max-width: 600px) {
            .answers-grid { grid-template-columns: 1fr; }
        }

        .answer-btn {
            position: relative;
            border: 3px solid #E5E7EB;
            border-radius: 18px;
            padding: 1.1rem 1.25rem;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            display: flex;
            align-items: center;
            gap: 0.875rem;
            text-align: left;
            background: white;
            font-family: 'Nunito', sans-serif;
            font-size: 0.925rem;
            font-weight: 700;
            color: #374151;
            overflow: hidden;
        }

        body.dark .answer-btn {
            background: #1E293B;
            border-color: #334155;
            color: #E2E8F0;
        }

        .answer-btn:hover:not(.revealed):not(.disabled) {
            transform: scale(1.02) translateY(-2px);
            box-shadow: 0 8px 24px rgba(108,61,229,0.2);
            border-color: #8B5CF6;
        }

        .answer-btn .choice-label {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #EDE9FE;
            color: #6C3DE5;
            font-weight: 900;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.25s;
        }

        /* Selected */
        .answer-btn.selected {
            border-color: #6C3DE5;
            background: #EDE9FE;
            box-shadow: 0 6px 20px rgba(108,61,229,0.25);
        }

        .answer-btn.selected .choice-label { background: #6C3DE5; color: white; }

        /* Correct */
        .answer-btn.correct {
            border-color: #10B981;
            background: #ECFDF5;
            animation: correctPop 0.35s ease;
        }

        .answer-btn.correct .choice-label { background: #10B981; color: white; }

        @keyframes correctPop {
            0% { transform: scale(0.96); }
            60% { transform: scale(1.04); }
            100% { transform: scale(1); }
        }

        /* Incorrect */
        .answer-btn.incorrect {
            border-color: #EF4444;
            background: #FEF2F2;
            animation: incorrectShake 0.4s ease;
        }

        .answer-btn.incorrect .choice-label { background: #EF4444; color: white; }

        @keyframes incorrectShake {
            0%,100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }

        .answer-btn.hidden { opacity: 0.2; pointer-events: none; }
        .answer-btn.disabled { opacity: 0.5; cursor: not-allowed; pointer-events: none; }
        .answer-btn.revealed { cursor: default; }

        /* ===== FEEDBACK BAR ===== */
        .feedback-bar {
            border-radius: 14px;
            padding: 1rem 1.25rem;
            text-align: center;
            font-weight: 800;
            font-size: 1rem;
            margin-top: 1rem;
        }

        .feedback-bar.correct { background: #D1FAE5; color: #065F46; }
        .feedback-bar.incorrect { background: #FEE2E2; color: #991B1B; }
        .feedback-bar.timeout { background: #FEF3C7; color: #78350F; }

        /* ===== SCORE & STREAK DISPLAY ===== */
        .score-streak-bar {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .score-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.1rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 800;
        }

        .score-chip.score { background: #EDE9FE; color: #6C3DE5; }
        .score-chip.streak { background: #FEF3C7; color: #B45309; }
        .score-chip.question { background: #ECFDF5; color: #065F46; }

        /* ===== POWER-UPS ===== */
        .powerups-bar {
            display: flex;
            gap: 0.6rem;
            justify-content: center;
            flex-wrap: wrap;
            padding: 0.875rem;
            background: white;
            border-radius: 16px;
            border: 1.5px solid #EDE9FE;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        body.dark .powerups-bar {
            background: #1E293B;
            border-color: #334155;
        }

        .powerup-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            padding: 0.6rem 0.9rem;
            border-radius: 12px;
            border: 2px solid transparent;
            cursor: pointer;
            font-size: 0.72rem;
            font-weight: 800;
            min-width: 72px;
            transition: all 0.2s;
            background: #F5F3FF;
            color: #6C3DE5;
        }

        .powerup-btn:hover:not(:disabled) { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(108,61,229,0.25); }
        .powerup-btn:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }
        .powerup-btn.active { background: #6C3DE5; color: white; }

        /* ===== BONUS POPUP ===== */
        @keyframes bonusFloat {
            0% { transform: translateY(0) scale(1); opacity: 1; }
            100% { transform: translateY(-70px) scale(1.4); opacity: 0; }
        }

        /* ===== LEADERBOARD MODAL ===== */
        .lb-modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 9990;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .lb-modal {
            background: white;
            border-radius: 24px;
            padding: 1.75rem;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.3);
            animation: modalIn 0.3s ease;
        }

        body.dark .lb-modal { background: #1E293B; }

        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .lb-entry {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            margin-bottom: 0.5rem;
        }

        .lb-entry.rank-1 { background: linear-gradient(135deg, #FEF3C7, #FDE68A); }
        .lb-entry.rank-2 { background: linear-gradient(135deg, #F3F4F6, #E5E7EB); }
        .lb-entry.rank-3 { background: #FEF3C7; opacity: 0.8; }
        .lb-entry.rank-other { background: #F9FAFB; }

        body.dark .lb-entry.rank-other { background: #0F172A; }

        /* ===== SETTINGS PANEL ===== */
        .settings-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9991;
        }

        .settings-panel {
            position: fixed;
            top: 0; right: -380px;
            width: 340px;
            height: 100%;
            background: white;
            z-index: 9992;
            padding: 1.5rem;
            overflow-y: auto;
            box-shadow: -8px 0 32px rgba(0,0,0,0.15);
            transition: right 0.3s ease;
        }

        .settings-panel.open { right: 0; }
        body.dark .settings-panel { background: #1E293B; color: white; }

        /* ===== NOTIFICATIONS ===== */
        #notif-container {
            position: fixed;
            top: 1rem; right: 1rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            width: 280px;
        }

        .notif-toast {
            padding: 0.8rem 1rem;
            border-radius: 12px;
            color: white;
            font-weight: 700;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
            animation: notifIn 0.3s cubic-bezier(0.34,1.56,0.64,1);
        }

        @keyframes notifIn {
            from { opacity: 0; transform: translateX(50px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .notif-toast.success { background: #10B981; }
        .notif-toast.error { background: #EF4444; }
        .notif-toast.warning { background: #F59E0B; }
        .notif-toast.info { background: #6C3DE5; }

        /* ===== QUIZ FINISHED ===== */
        .quiz-finished-screen {
            text-align: center;
            padding: 3rem 2rem;
            background: white;
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(108,61,229,0.12);
        }

        body.dark .quiz-finished-screen { background: #1E293B; }

        .finish-icon { font-size: 4rem; margin-bottom: 1rem; animation: bounce 0.6s ease; }

        @keyframes bounce {
            0% { transform: scale(0.8); }
            60% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }

        [x-cloak] { display: none !important; }

        /* Toggle switch */
        .toggle-switch {
            position: relative;
            width: 44px;
            height: 24px;
            border-radius: 999px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .toggle-switch input { display: none; }

        .toggle-knob {
            position: absolute;
            top: 3px; left: 3px;
            width: 18px; height: 18px;
            border-radius: 50%;
            background: white;
            transition: transform 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        input:checked + .toggle-knob { transform: translateX(20px); }
    </style>
</head>

<body>
{{-- ===== SECURITY OVERLAY ===== --}}
<div id="security-overlay">
    <div class="big-icon"><i class="fas fa-exclamation-triangle"></i></div>
    <h2 id="security-title">Pelanggaran Terdeteksi!</h2>
    <p id="security-msg">Anda keluar dari halaman quiz. Pelanggaran ini telah dicatat.</p>
    <button class="resume-btn" @click="resumeFromViolation()">
        <i class="fas fa-play mr-2"></i> Lanjutkan Quiz
    </button>
</div>

<div id="violation-toast"></div>

{{-- FULLSCREEN PROMPT --}}
@if($quiz->fullscreen_mode)
<div id="fullscreen-prompt">
    <div style="font-size:3rem"><i class="fas fa-lock" style="color:white"></i></div>
    <h2>Mode Layar Penuh Diperlukan</h2>
    <p>Quiz ini memerlukan mode layar penuh untuk keamanan dan pengalaman terbaik.</p>
    <button class="fs-btn" @click="enterFullscreen()">
        <i class="fas fa-expand"></i> Masuk Fullscreen
    </button>
</div>
@endif

{{-- ===== TOPBAR ===== --}}
<div class="quiz-topbar">
    <div>
        <div class="quiz-topbar-title">{{ Str::limit($quiz->title, 40) }}</div>
        <div class="quiz-topbar-meta">{{ $quiz->subject->name_subject ?? '' }}</div>
    </div>

    <div class="progress-bar" style="margin-top: 0.2rem">
        <div class="progress-fill" :style="`width: ${((currentQuestion + 1) / totalQuestions) * 100}%`"></div>
    </div>

    <div style="display:flex;align-items:center;gap:0.75rem;flex-shrink:0">
        @if($quiz->show_score)
        <div class="score-chip score" style="background:rgba(255,255,255,0.15);color:white">
            <i class="fas fa-star text-yellow-300"></i>
            <span x-text="Math.floor(totalScore) + ' pts'"></span>
        </div>
        @endif

        <div x-show="streakCount >= 2" class="score-chip streak" style="background:rgba(255,255,255,0.15);color:white">
            <i class="fas fa-fire"></i> <span x-text="streakCount"></span>
        </div>

        @if($quiz->show_leaderboard)
        <button @click="showLeaderboardModal = true; loadLeaderboard()"
            style="background:rgba(255,255,255,0.15);border:none;color:white;padding:0.5rem;border-radius:10px;cursor:pointer;font-size:1rem">
            <i class="fas fa-trophy"></i>
        </button>
        @endif

        <button @click="settingsPanelOpen = true"
            style="background:rgba(255,255,255,0.15);border:none;color:white;padding:0.5rem;border-radius:10px;cursor:pointer;font-size:1rem">
            <i class="fas fa-cog"></i>
        </button>
    </div>
</div>

{{-- ===== MAIN CONTENT ===== --}}
<div style="max-width: 820px; margin: 0 auto; padding: 1.5rem 1rem">

    {{-- QUIZ FINISHED --}}
    <div x-show="quizFinished" x-cloak class="quiz-finished-screen">
        <div class="finish-icon"><i class="fas fa-check-circle" style="color:#10B981"></i></div>
        <h2 style="font-size:1.75rem;font-weight:900;margin-bottom:0.5rem">Quiz Selesai!</h2>
        <p style="color:#6B7280;margin-bottom:1.5rem">Jawabanmu telah dikirim. Hasil akan segera tersedia.</p>
        @if($quiz->show_score)
        <div style="background:#EDE9FE;border-radius:16px;padding:1.25rem;display:inline-block;margin-bottom:1.5rem">
            <div style="font-size:2.5rem;font-weight:900;color:#6C3DE5" x-text="Math.floor(totalScore) + ' pts'"></div>
            <div style="font-size:0.85rem;color:#7C3AED;font-weight:700">Total Skor</div>
        </div>
        @endif
        <div>
            <div style="display:flex;align-items:center;justify-content:center;gap:0.5rem;color:#10B981;font-weight:700;font-size:0.9rem">
                <svg class="w-4 h-4 animate-spin" style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Mengalihkan ke halaman hasil...
            </div>
        </div>
    </div>

    {{-- SCORE + PROGRESS BAR --}}
    <div class="score-streak-bar" x-show="!quizFinished">
        <div class="score-chip question">
            <i class="fas fa-list-ol"></i>
            Soal <span x-text="currentQuestion + 1"></span> dari <span x-text="totalQuestions"></span>
        </div>
        @if($quiz->show_score)
        <div class="score-chip score">
            <i class="fas fa-star"></i> <span x-text="Math.floor(totalScore) + ' pts'"></span>
        </div>
        @endif
        <div class="score-chip streak" x-show="streakCount >= 2">
            <i class="fas fa-fire"></i> Streak <span x-text="streakCount"></span>
        </div>
    </div>

    {{-- QUESTION CARD --}}
    <div class="question-card mb-4" x-show="!quizFinished"
        :class="{ 'opacity-0 -translate-y-2': animatingOut, 'opacity-100': !animatingOut }"
        style="transition: opacity 0.25s, transform 0.25s">

        <template x-if="questions[currentQuestion]">
            <div>
                {{-- Question header --}}
                <div class="flex items-start justify-between gap-3 mb-4">
                    <span class="question-number">
                        <i class="fas fa-question-circle" style="font-size:0.9rem"></i>
                        Soal <span x-text="currentQuestion + 1"></span>
                    </span>

                    {{-- Timer ring --}}
                    @if($quiz->time_per_question > 0)
                    <div class="timer-ring-container" x-show="!isAnswerRevealed">
                        <svg width="72" height="72" viewBox="0 0 72 72">
                            <circle class="timer-ring-bg" cx="36" cy="36" r="30"/>
                            <circle class="timer-ring-fg"
                                :class="questionTimeRemaining <= 5 ? 'danger' : (questionTimeRemaining <= 10 ? 'warning' : '')"
                                cx="36" cy="36" r="30"
                                :style="'stroke-dashoffset: ' + (188 - (188 * questionTimeRemaining / {{ $quiz->time_per_question }}))"/>
                        </svg>
                        <div class="timer-ring-text"
                            :class="questionTimeRemaining <= 5 ? 'text-red-400' : (questionTimeRemaining <= 10 ? 'text-yellow-300' : 'text-white')"
                            x-text="questionTimeRemaining">
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Question text --}}
                <p class="question-text" x-text="questions[currentQuestion].question"></p>

                {{-- MULTIPLE CHOICE --}}
                <template x-if="questions[currentQuestion].type === 'PG'">
                    <div class="answers-grid">
                        <template x-for="(choice, idx) in questions[currentQuestion].choices" :key="idx">
                            <button class="answer-btn"
                                :class="{
                                    'selected': questions[currentQuestion].selectedAnswer === idx && !isAnswerRevealed,
                                    'correct': isAnswerRevealed && choice.is_correct,
                                    'incorrect': isAnswerRevealed && questions[currentQuestion].selectedAnswer === idx && !choice.is_correct,
                                    'hidden': choice.disabled,
                                    'revealed': isAnswerRevealed
                                }"
                                @click="!isAnswerRevealed && !choice.disabled && questions[currentQuestion].selectedAnswer === null ? selectAnswer(idx) : null">
                                <span class="choice-label" x-text="String.fromCharCode(65+idx)"></span>
                                <span style="flex:1;word-break:break-word" x-text="choice.choice_text || choice.text"></span>
                                <span x-show="isAnswerRevealed && choice.is_correct" style="flex-shrink:0"><i class="fas fa-check-circle text-green-500"></i></span>
                                <span x-show="isAnswerRevealed && questions[currentQuestion].selectedAnswer === idx && !choice.is_correct" style="flex-shrink:0"><i class="fas fa-times-circle text-red-500"></i></span>
                            </button>
                        </template>
                    </div>
                </template>

                {{-- SHORT ANSWER --}}
                <template x-if="questions[currentQuestion].type === 'IS'">
                    <div>
                        <input type="text"
                            x-model="questions[currentQuestion].textAnswer"
                            :disabled="isAnswerRevealed"
                            @keyup.enter="!isAnswerRevealed && questions[currentQuestion].textAnswer?.trim() ? submitTextAnswer() : null"
                            style="width:100%;padding:1rem;border:2.5px solid #E5E7EB;border-radius:14px;font-size:1rem;font-family:'Nunito',sans-serif;font-weight:600;outline:none;transition:border-color 0.2s"
                            :style="isAnswerRevealed ? '' : ''"
                            placeholder="Ketik jawaban Anda dan tekan Enter..."
                            @focus="$el.style.borderColor='#6C3DE5';"
                            @blur="$el.style.borderColor='#E5E7EB';">

                        <button x-show="!isAnswerRevealed"
                            @click="submitTextAnswer()"
                            :disabled="!questions[currentQuestion].textAnswer?.trim()"
                            style="margin-top:0.875rem;width:100%;padding:0.875rem;background:linear-gradient(135deg,#6C3DE5,#8B5CF6);color:white;border:none;border-radius:14px;font-weight:800;font-size:0.95rem;cursor:pointer;transition:opacity 0.2s"
                            :style="!questions[currentQuestion].textAnswer?.trim() ? 'opacity:0.5;cursor:not-allowed' : ''">
                            <i class="fas fa-paper-plane mr-2"></i> Kirim Jawaban
                        </button>
                    </div>
                </template>

                {{-- FEEDBACK --}}
                <div x-show="feedbackMessage" class="feedback-bar" :class="feedbackType" x-text="feedbackMessage"></div>

                {{-- EXPLANATION (if shown after answer) --}}
                <div x-show="isAnswerRevealed && questions[currentQuestion].explanation"
                    style="margin-top:1rem;padding:1rem;background:#F0FDF4;border-left:4px solid #10B981;border-radius:0 12px 12px 0"
                    x-cloak>
                    <p style="font-size:0.85rem;color:#065F46;font-weight:700">
                        <i class="fas fa-lightbulb mr-1 text-yellow-500"></i> Penjelasan:
                    </p>
                    <p style="font-size:0.85rem;color:#065F46;margin-top:0.25rem" x-text="questions[currentQuestion].explanation"></p>
                </div>
            </div>
        </template>
    </div>

    {{-- POWER-UPS --}}
    @if($quiz->enable_powerups)
    <div class="powerups-bar mb-4" x-show="!quizFinished">
        <div style="font-size:0.75rem;font-weight:800;color:#6C3DE5;width:100%;text-align:center;margin-bottom:0.25rem">
            <i class="fas fa-bolt"></i> Power-ups
        </div>
        <template x-for="(pu, key) in powerupsRandom" :key="key">
            <button class="powerup-btn" @click="activatePowerup(key)"
                :disabled="pu.cooldown > 0 || isAnswerRevealed"
                :class="{ 'active': pu.cooldown === 0 && !isAnswerRevealed }">
                <i :class="pu.icon" style="font-size:1.2rem"></i>
                <span x-text="pu.cooldown > 0 ? pu.cooldown + 's' : pu.name"></span>
            </button>
        </template>
    </div>
    @endif

</div>

{{-- ===== LEADERBOARD MODAL ===== --}}
@if($quiz->show_leaderboard)
<div x-show="showLeaderboardModal" class="lb-modal-backdrop" @click.self="showLeaderboardModal = false" x-cloak>
    <div class="lb-modal">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
            <h3 style="font-size:1.1rem;font-weight:900"><i class="fas fa-trophy mr-2" style="color:#D97706"></i> Leaderboard</h3>
            <button @click="showLeaderboardModal = false"
                style="background:#F3F4F6;border:none;border-radius:8px;padding:0.4rem 0.7rem;cursor:pointer;font-weight:700"><i class="fas fa-times"></i></button>
        </div>

        <div x-show="leaderboard.length === 0" style="text-align:center;padding:2rem;color:#6B7280">
            <i class="fas fa-spinner fa-spin text-2xl mb-2 block"></i>
            Memuat leaderboard...
        </div>

        <div x-show="leaderboard.length > 0" class="space-y-2">
            <template x-for="(entry, i) in leaderboard" :key="i">
                <div class="lb-entry" :class="{
                    'rank-1': entry.rank === 1,
                    'rank-2': entry.rank === 2,
                    'rank-3': entry.rank === 3,
                    'rank-other': entry.rank > 3
                }">
                    <div style="font-size:1.25rem;width:32px;text-align:center;font-weight:900">
                        <span x-show="entry.rank === 1"><i class="fas fa-medal" style="color:#D97706"></i></span>
                        <span x-show="entry.rank === 2"><i class="fas fa-medal" style="color:#9CA3AF"></i></span>
                        <span x-show="entry.rank === 3"><i class="fas fa-medal" style="color:#B45309"></i></span>
                        <span x-show="entry.rank > 3" x-text="'#' + entry.rank" style="color:#6B7280;font-size:1rem"></span>
                    </div>
                    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#6C3DE5,#8B5CF6);color:white;display:flex;align-items:center;justify-content:center;font-weight:900;flex-shrink:0"
                        x-text="(entry.student_name || entry.name || 'U').charAt(0).toUpperCase()"></div>
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:800;font-size:0.875rem" x-text="entry.student_name || entry.name || 'Peserta'"></div>
                        <div style="font-size:0.72rem;color:#6B7280" x-text="'Waktu: ' + formatTime(entry.time_taken || 0)"></div>
                    </div>
                    <div style="font-weight:900;color:#6C3DE5;font-size:1rem" x-text="(entry.score || 0) + ' pts'"></div>
                </div>
            </template>

            @if($quiz->show_score)
            <div style="border-top:2px solid #F3F4F6;margin-top:0.75rem;padding-top:0.75rem;display:flex;justify-content:space-between;align-items:center">
                <span style="font-weight:700;font-size:0.875rem">Skor Kamu:</span>
                <span style="font-weight:900;color:#6C3DE5;font-size:1.1rem" x-text="Math.floor(totalScore) + ' pts'"></span>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ===== SETTINGS PANEL ===== --}}
<div x-show="settingsPanelOpen" class="settings-backdrop" @click="settingsPanelOpen = false" x-cloak></div>
<div class="settings-panel" :class="{ 'open': settingsPanelOpen }">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
        <h3 style="font-size:1.05rem;font-weight:900"><i class="fas fa-cog mr-2"></i> Pengaturan</h3>
        <button @click="settingsPanelOpen = false" style="background:#F3F4F6;border:none;border-radius:8px;padding:0.4rem 0.7rem;cursor:pointer;font-weight:700"><i class="fas fa-times"></i></button>
    </div>

    <div style="display:flex;flex-direction:column;gap:1.25rem">
        {{-- Dark Mode --}}
        <div style="display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-weight:700;font-size:0.875rem">Mode Gelap</div>
                <div style="font-size:0.75rem;color:#6B7280">Hemat mata di malam hari</div>
            </div>
            <div class="toggle-switch" style="background: settings.darkMode ? '#6C3DE5' : '#E5E7EB'"
                :style="'background:' + (settings.darkMode ? '#6C3DE5' : '#E5E7EB')"
                @click="settings.darkMode = !settings.darkMode; toggleDarkMode()">
                <div class="toggle-knob" :style="settings.darkMode ? 'transform:translateX(20px)' : ''"></div>
            </div>
        </div>

        {{-- Sound --}}
        <div style="display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-weight:700;font-size:0.875rem">Efek Suara</div>
                <div style="font-size:0.75rem;color:#6B7280">Suara saat menjawab</div>
            </div>
            <div class="toggle-switch"
                :style="'background:' + (settings.soundEnabled ? '#6C3DE5' : '#E5E7EB')"
                @click="toggleSound()">
                <div class="toggle-knob" :style="settings.soundEnabled ? 'transform:translateX(20px)' : ''"></div>
            </div>
        </div>

        {{-- Text Size --}}
        <div>
            <div style="font-weight:700;font-size:0.875rem;margin-bottom:0.5rem">Ukuran Teks</div>
            <div style="display:flex;gap:0.5rem">
                <button @click="settings.textSize='small';localStorage.setItem('quiz_textSize','small')"
                    :style="settings.textSize==='small' ? 'background:#6C3DE5;color:white' : 'background:#F3F4F6;color:#374151'"
                    style="flex:1;padding:0.5rem;border-radius:8px;border:none;cursor:pointer;font-weight:700;font-size:0.8rem">Kecil</button>
                <button @click="settings.textSize='normal';localStorage.setItem('quiz_textSize','normal')"
                    :style="settings.textSize==='normal' ? 'background:#6C3DE5;color:white' : 'background:#F3F4F6;color:#374151'"
                    style="flex:1;padding:0.5rem;border-radius:8px;border:none;cursor:pointer;font-weight:700;font-size:0.8rem">Normal</button>
                <button @click="settings.textSize='large';localStorage.setItem('quiz_textSize','large')"
                    :style="settings.textSize==='large' ? 'background:#6C3DE5;color:white' : 'background:#F3F4F6;color:#374151'"
                    style="flex:1;padding:0.5rem;border-radius:8px;border:none;cursor:pointer;font-weight:700;font-size:0.8rem">Besar</button>
            </div>
        </div>
    </div>
</div>

{{-- NOTIFICATION CONTAINER --}}
<div id="notif-container"></div>

{{-- ===== ALPINE JS ===== --}}
<script>
    window.quizData = {
        totalQuestions: {{ $quiz->questions->count() }},
        quizDuration: {{ $quiz->duration }},
        timePerQuestion: {{ $quiz->time_per_question }},
        timeRemaining: {{ $quiz->duration * 60 ?? ($quiz->duration) }},
        instantFeedback: {{ $quiz->instant_feedback ? 'true' : 'false' }},
        showCorrectAnswer: {{ $quiz->show_correct_answer ? 'true' : 'false' }},
        enablePowerups: {{ $quiz->enable_powerups ? 'true' : 'false' }},
        streakBonus: {{ $quiz->streak_bonus ? 'true' : 'false' }},
        timeBonus: {{ $quiz->time_bonus ? 'true' : 'false' }},
        showLeaderboard: {{ $quiz->show_leaderboard ? 'true' : 'false' }},
        fullscreenMode: {{ $quiz->fullscreen_mode ? 'true' : 'false' }},
        blockNewTab: {{ $quiz->block_new_tab ? 'true' : 'false' }},
        preventCopyPaste: {{ $quiz->prevent_copy_paste ? 'true' : 'false' }},
        enableMusic: {{ ($quiz->enable_music ?? false) ? 'true' : 'false' }},
        csrfToken: '{{ csrf_token() }}',
        submitUrl: '{{ route('quiz.submit', $quiz->id) }}',
        saveProgressUrl: '{{ route('quiz.save-progress', $quiz->id) }}',
        leaderboardTop5Url: '{{ route('quiz.leaderboard-top5', $quiz->id) }}',
        violationUrl: '{{ route('quiz.report-violation', $quiz->id) }}',
        powerupUrl: '{{ route('quiz.powerups', $quiz->id) }}',
        questions: {!! json_encode(
            $quiz->questions->map(function ($q) {
                return [
                    'id' => $q->id,
                    'question' => $q->question,
                    'type' => $q->type,
                    'score' => $q->score,
                    'explanation' => $q->show_correct_answer ?? false ? ($q->explanation ?? '') : '',
                    'choices' => $q->type === 'PG'
                        ? $q->choices->map(function ($c) {
                            return [
                                'id' => $c->id,
                                'choice_text' => $c->text,
                                'is_correct' => $c->is_correct,
                                'disabled' => false,
                            ];
                        })->toArray()
                        : [],
                    'selectedAnswer' => null,
                    'textAnswer' => '',
                ];
            })
        ) !!}
    };

    function quizPlayer() {
        return {
            // ===== STATE =====
            currentQuestion: 0,
            totalQuestions: window.quizData.totalQuestions,
            timeRemaining: window.quizData.timeRemaining,
            questionTimeRemaining: window.quizData.timePerQuestion,
            perQuestionTimer: null,
            questions: window.quizData.questions,
            isAnswerRevealed: false,
            animatingOut: false,
            quizFinished: false,
            showLeaderboardModal: false,
            feedbackMessage: '',
            feedbackType: 'correct',
            totalScore: 0,
            streakCount: 0,
            bonusPoints: 0,
            leaderboard: [],
            settingsPanelOpen: false,
            violationCount: 0,
            _securityBlocked: false,
            powerupsRandom: {},
            nextQuestionMultiplier: 1,
            activeMultiplier: 1,
            multiplierExpiresAt: null,
            immunityActive: false,
            streakSaverActive: false,
            supersonicActive: false,
            bgMusicAudio: null,
            bgMusicPlaying: false,

            settings: {
                soundEnabled: localStorage.getItem('quiz_sound') !== 'false',
                darkMode: localStorage.getItem('quiz_darkMode') === 'true',
                textSize: localStorage.getItem('quiz_textSize') || 'normal',
            },

            powerups: {
                double_up: { name: 'Double Up', icon: 'fas fa-times-circle', cooldown: 0, multiplier: 2 },
                triple_up: { name: 'Triple Up', icon: 'fas fa-cubes', cooldown: 0, multiplier: 3 },
                immunity: { name: 'Immunity', icon: 'fas fa-shield-alt', cooldown: 0 },
                eraser: { name: 'Eraser', icon: 'fas fa-eraser', cooldown: 0 },
                freeze: { name: 'Freeze', icon: 'fas fa-snowflake', cooldown: 0 },
            },

            get answeredCount() {
                return this.questions.filter(q => q.selectedAnswer !== null || q.textAnswer?.trim()).length;
            },

            // ===== INIT =====
            init() {
                window.quizPlayerInstance = this;
                document.documentElement.classList.toggle('dark', this.settings.darkMode);
                document.body.classList.toggle('dark', this.settings.darkMode);

                this.selectRandomPowerups();
                this.startPerQuestionTimer();
                this.loadProgress();
                this.preloadSounds();
                this.initSecurityListeners();

                if (window.quizData.showLeaderboard) {
                    this.loadLeaderboard().catch(() => {});
                }

                if (window.quizData.fullscreenMode && !document.fullscreenElement) {
                    document.getElementById('fullscreen-prompt')?.classList.add('active');
                }

                if (window.quizData.enableMusic) this.initBgMusic();

                // Powerup cooldown interval
                setInterval(() => {
                    for (let k in this.powerupsRandom) {
                        if (this.powerupsRandom[k].cooldown > 0) this.powerupsRandom[k].cooldown--;
                    }
                }, 1000);
            },

            // ===== FULLSCREEN =====
            enterFullscreen() {
                const el = document.documentElement;
                const fn = el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen;
                if (fn) fn.call(el).then(() => {
                    document.getElementById('fullscreen-prompt')?.classList.remove('active');
                }).catch(() => {});
            },

            // ===== SECURITY =====
            initSecurityListeners() {
                if (window.quizData.blockNewTab) {
                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden && !this.quizFinished) this.handleViolation('tab_switch', 'Keluar dari tab quiz');
                    });
                    window.addEventListener('blur', () => {
                        if (!this.quizFinished) this.handleViolation('window_blur', 'Window kehilangan fokus');
                    });
                }

                if (window.quizData.preventCopyPaste) {
                    document.addEventListener('copy', (e) => { e.preventDefault(); this.handleViolation('copy', 'Mencoba copy'); });
                    document.addEventListener('paste', (e) => { e.preventDefault(); this.handleViolation('paste', 'Mencoba paste'); });
                    document.addEventListener('contextmenu', (e) => { e.preventDefault(); this.handleViolation('right_click', 'Right click'); });
                }

                if (window.quizData.fullscreenMode) {
                    document.addEventListener('fullscreenchange', () => {
                        if (!document.fullscreenElement && !this.quizFinished) {
                            const overlay = document.getElementById('fullscreen-prompt');
                            if (overlay) overlay.classList.add('active');
                            this.handleViolation('fullscreen_exit', 'Keluar dari fullscreen');
                        }
                    });
                }
            },

            showViolationToast(msg) {
                const t = document.getElementById('violation-toast');
                if (!t) return;
                t.textContent = msg;
                t.classList.add('show');
                setTimeout(() => t.classList.remove('show'), 4000);
            },

            resumeFromViolation() {
                this._securityBlocked = false;
                document.getElementById('security-overlay')?.classList.remove('active');
                if (window.quizData.fullscreenMode) this.enterFullscreen();
            },

            async handleViolation(type, details = null) {
                if (this.quizFinished) return;
                this.violationCount++;
                this.showViolationToast(`Pelanggaran #${this.violationCount}: ${details || type}`);
                try {
                    const r = await fetch(window.quizData.violationUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': window.quizData.csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ type, details })
                    });
                    const data = await r.json().catch(() => ({}));
                    if (data.violation_count) this.violationCount = data.violation_count;
                    if (data.auto_submit) {
                        this.showNotification('Batas pelanggaran tercapai! Disubmit otomatis.', 'error');
                        setTimeout(() => this.submitQuiz(), 1500);
                    }
                } catch(e) {}
            },

            // ===== TIMER =====
            startPerQuestionTimer() {
                if (this.perQuestionTimer) clearInterval(this.perQuestionTimer);
                this.questionTimeRemaining = window.quizData.timePerQuestion;
                this.perQuestionTimer = setInterval(() => {
                    if (this.quizFinished || this._securityBlocked) return;
                    if (!this.isAnswerRevealed && this.questionTimeRemaining > 0) {
                        this.questionTimeRemaining--;
                        if (this.questionTimeRemaining === 10) this.playSound('countdown');
                    } else if (this.questionTimeRemaining <= 0 && !this.isAnswerRevealed) {
                        clearInterval(this.perQuestionTimer);
                        this.playSound('timeout');
                        this.feedbackMessage = 'Waktu habis!';
                        this.feedbackType = 'timeout';
                        this.streakCount = 0;
                        this.isAnswerRevealed = true;
                        setTimeout(() => this.nextQuestion(), 1500);
                    }
                }, 1000);
            },

            // ===== ANSWER SELECT =====
            async selectAnswer(choiceIndex) {
                if (this._securityBlocked) return;
                const q = this.questions[this.currentQuestion];
                if (this.isAnswerRevealed || q.selectedAnswer !== null) return;

                q.selectedAnswer = choiceIndex;
                this.playSound('click');

                const choice = q.choices[choiceIndex];
                const isCorrect = choice?.is_correct || false;
                let earned = 0;

                if (isCorrect) {
                    earned = q.score || 1;
                    if (this.activeMultiplier > 1) earned *= this.activeMultiplier;
                    if (this.nextQuestionMultiplier > 1) { earned *= this.nextQuestionMultiplier; this.nextQuestionMultiplier = 1; }
                    if (this.supersonicActive) { earned *= 2; this.supersonicActive = false; }
                    this.totalScore += earned;
                    this.streakCount++;

                    if (window.quizData.timeBonus && this.questionTimeRemaining > 0) {
                        const tb = Math.floor(this.questionTimeRemaining * 0.5);
                        this.totalScore += tb;
                        if (tb > 0) this.showBonusPopup('+' + tb + ' Time!');
                    }

                    if (window.quizData.streakBonus && this.streakCount > 0 && this.streakCount % 3 === 0) {
                        const sb = Math.floor(earned * 0.5);
                        this.totalScore += sb;
                        this.showBonusPopup('Streak! +' + sb);
                    }

                    if (earned > 0) this.showBonusPopup('+' + Math.floor(earned) + ' pts');
                } else {
                    if (this.immunityActive) {
                        this.immunityActive = false;
                        this.showNotification('Immunity aktif! Jawaban salah diabaikan.', 'success');
                    } else if (this.streakSaverActive) {
                        this.streakSaverActive = false;
                        this.streakCount = Math.max(0, this.streakCount - 1);
                    } else {
                        this.streakCount = 0;
                    }
                }

                await this.saveProgress();

                this.feedbackType = isCorrect ? 'correct' : 'incorrect';
                this.feedbackMessage = isCorrect ? 'Benar!' : 'Salah!';
                this.playSound(isCorrect ? 'correct' : 'incorrect');
                this.isAnswerRevealed = true;

                clearInterval(this.perQuestionTimer);

                if (this.currentQuestion < this.totalQuestions - 1) {
                    setTimeout(() => this.nextQuestion(), isCorrect ? 700 : 1000);
                } else {
                    setTimeout(() => this.submitQuiz(), 1200);
                }
            },

            async submitTextAnswer() {
                const q = this.questions[this.currentQuestion];
                if (!q.textAnswer?.trim()) return;
                q.selectedAnswer = 0;
                this.playSound('click');
                await this.saveProgress();
                this.feedbackMessage = 'Jawaban disimpan!';
                this.feedbackType = 'correct';
                this.isAnswerRevealed = true;
                clearInterval(this.perQuestionTimer);
                if (this.currentQuestion < this.totalQuestions - 1) {
                    setTimeout(() => this.nextQuestion(), 800);
                } else {
                    setTimeout(() => this.submitQuiz(), 1000);
                }
            },

            nextQuestion() {
                if (this.currentQuestion >= this.totalQuestions - 1) { this.submitQuiz(); return; }
                this.animatingOut = true;
                setTimeout(() => {
                    this.currentQuestion++;
                    this.isAnswerRevealed = false;
                    this.feedbackMessage = '';
                    this.animatingOut = false;
                    this.startPerQuestionTimer();
                }, 250);
            },

            // ===== POWER-UPS =====
            selectRandomPowerups() {
                const keys = Object.keys(this.powerups);
                const shuffled = keys.sort(() => 0.5 - Math.random()).slice(0, 3);
                this.powerupsRandom = {};
                shuffled.forEach(k => { this.powerupsRandom[k] = { ...this.powerups[k] }; });
            },

            async activatePowerup(type) {
                if (!window.quizData.enablePowerups) return;
                const pu = this.powerupsRandom[type];
                if (!pu || pu.cooldown > 0 || this.isAnswerRevealed) return;

                pu.cooldown = 30;
                this.playSound('powerup');

                switch(type) {
                    case 'double_up':
                        this.nextQuestionMultiplier = 2;
                        this.showNotification('2x poin soal berikutnya!', 'success'); break;
                    case 'triple_up':
                        this.nextQuestionMultiplier = 3;
                        this.showNotification('3x poin soal berikutnya!', 'success'); break;
                    case 'immunity':
                        this.immunityActive = true;
                        this.showNotification('Immunity aktif!', 'success'); break;
                    case 'eraser': {
                        const q = this.questions[this.currentQuestion];
                        const wrong = q.choices.filter(c => !c.is_correct);
                        const toHide = wrong.sort(() => Math.random() - 0.5).slice(0, Math.floor(wrong.length/2));
                        toHide.forEach(c => c.disabled = true);
                        this.showNotification('2 pilihan salah disembunyikan!', 'info'); break;
                    }
                    case 'freeze':
                        this.questionTimeRemaining = Math.min(this.questionTimeRemaining + 15, window.quizData.timePerQuestion);
                        this.showNotification('+15 detik tambahan!', 'info'); break;
                }

                try {
                    await fetch(window.quizData.powerupUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': window.quizData.csrfToken, 'Content-Type': 'application/json' },
                        body: JSON.stringify({ type, question_id: this.questions[this.currentQuestion].id })
                    });
                } catch(e) {}
            },

            // ===== SUBMIT =====
            async submitQuiz() {
                if (this.quizFinished) return;
                this.quizFinished = true;
                clearInterval(this.perQuestionTimer);

                try {
                    const answers = this.questions.map(q => ({
                        question_id: q.id,
                        choice_id: q.choices?.[q.selectedAnswer]?.id || null,
                        text_answer: q.textAnswer || null,
                    }));

                    const r = await fetch(window.quizData.submitUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.quizData.csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify({ answers, total_score: this.totalScore, time_spent: window.quizData.quizDuration - this.timeRemaining })
                    });

                    const data = await r.json();
                    if (data.success) {
                        this.playSound('victory');
                        if (window.quizData.showLeaderboard) {
                            await this.loadLeaderboard().catch(() => {});
                            if (this.leaderboard.length > 0) {
                                this.showLeaderboardModal = true;
                                setTimeout(() => { window.location.href = data.redirect; }, 4000);
                                return;
                            }
                        }
                        setTimeout(() => { window.location.href = data.redirect; }, 2000);
                    } else {
                        this.quizFinished = false;
                        this.showNotification('Gagal submit: ' + (data.message || 'Error'), 'error');
                    }
                } catch(e) {
                    this.quizFinished = false;
                    this.showNotification('Terjadi kesalahan. Coba lagi.', 'error');
                }
            },

            // ===== SAVE/LOAD PROGRESS =====
            async saveProgress() {
                try {
                    const answers = this.questions.map(q => ({
                        question_id: q.id,
                        choice_id: q.choices?.[q.selectedAnswer]?.id || null,
                        text_answer: q.textAnswer || null,
                    }));
                    await fetch(window.quizData.saveProgressUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.quizData.csrfToken },
                        body: JSON.stringify({ answers, current_question: this.currentQuestion, time_remaining: this.timeRemaining, total_score: this.totalScore, streak_count: this.streakCount })
                    });
                } catch(e) {}
            },

            async loadProgress() {
                try {
                    const r = await fetch(window.quizData.saveProgressUrl, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.quizData.csrfToken } });
                    if (!r.ok) return;
                    const data = await r.json();
                    if (data.success && data.progress) {
                        const p = data.progress;
                        this.currentQuestion = p.current_question || 0;
                        this.timeRemaining = p.time_remaining || this.timeRemaining;
                        this.totalScore = p.total_score || 0;
                        this.streakCount = p.streak_count || 0;
                        if (p.answers) {
                            p.answers.forEach(saved => {
                                const q = this.questions.find(q => q.id == saved.question_id);
                                if (q && saved.choice_id) {
                                    const idx = q.choices?.findIndex(c => c.id == saved.choice_id);
                                    if (idx !== -1) q.selectedAnswer = idx;
                                }
                                if (q && saved.text_answer) q.textAnswer = saved.text_answer;
                            });
                        }
                    }
                } catch(e) {}
            },

            // ===== LEADERBOARD =====
            async loadLeaderboard() {
                if (!window.quizData.showLeaderboard) return;
                try {
                    const r = await fetch(window.quizData.leaderboardTop5Url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.quizData.csrfToken } });
                    const data = await r.json();
                    this.leaderboard = data.success ? (data.leaderboard || []) : [];
                } catch(e) { this.leaderboard = []; }
            },

            // ===== MUSIC =====
            initBgMusic() {
                const tracks = ['/sounds/bg_music_1.mp3', '/sounds/bg_music_2.mp3'];
                this.bgMusicAudio = new Audio(tracks[Math.floor(Math.random() * tracks.length)]);
                this.bgMusicAudio.loop = true;
                this.bgMusicAudio.volume = 0.2;
            },

            // ===== SOUNDS =====
            preloadSounds() {
                if (!this.settings.soundEnabled) return;
                ['click','correct','incorrect','powerup','victory','countdown','timeout'].forEach(t => new Audio(`/sounds/${t}.mp3`).load());
            },

            playSound(type = 'click') {
                if (!this.settings.soundEnabled) return;
                try { new Audio(`/sounds/${type}.mp3`).play().catch(() => {}); } catch(e) {}
            },

            toggleSound() {
                this.settings.soundEnabled = !this.settings.soundEnabled;
                localStorage.setItem('quiz_sound', this.settings.soundEnabled);
                if (this.settings.soundEnabled) this.playSound('click');
            },

            toggleDarkMode() {
                localStorage.setItem('quiz_darkMode', this.settings.darkMode);
                document.documentElement.classList.toggle('dark', this.settings.darkMode);
                document.body.classList.toggle('dark', this.settings.darkMode);
            },

            // ===== BONUS POPUP =====
            showBonusPopup(msg) {
                const div = document.createElement('div');
                div.textContent = msg;
                div.style.cssText = `
                    position: fixed;
                    left: ${45 + Math.random()*10}%;
                    top: 40%;
                    pointer-events: none;
                    z-index: 9999;
                    font-size: 1.35rem;
                    font-weight: 900;
                    color: #F59E0B;
                    text-shadow: 0 2px 8px rgba(0,0,0,.3);
                    font-family: 'Nunito', sans-serif;
                    animation: bonusFloat 1.4s ease-out forwards;
                `;
                document.body.appendChild(div);
                setTimeout(() => div.remove(), 1500);
            },

            // ===== NOTIFICATIONS =====
            showNotification(msg, type = 'info') {
                const icons = {
                    success: '<i class="fas fa-check-circle"></i>',
                    error: '<i class="fas fa-times-circle"></i>',
                    warning: '<i class="fas fa-exclamation-triangle"></i>',
                    info: '<i class="fas fa-info-circle"></i>'
                };
                const el = document.createElement('div');
                el.className = `notif-toast ${type}`;
                el.innerHTML = `<span style="flex-shrink:0">${icons[type]||icons['info']}</span><span style="flex:1">${msg}</span>`;
                const c = document.getElementById('notif-container');
                c.appendChild(el);
                setTimeout(() => {
                    el.style.opacity = '0';
                    el.style.transform = 'translateX(50px)';
                    el.style.transition = 'all 0.3s';
                    setTimeout(() => el.remove(), 300);
                }, 3500);
            },

            formatTime(s) {
                s = parseInt(s);
                if (isNaN(s)||s<=0) return '00:00';
                return `${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`;
            }
        };
    }
</script>
</body>
</html>
