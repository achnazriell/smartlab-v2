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
            --primary: #2563EB;
            --primary-light: #3B82F6;
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
            background: linear-gradient(135deg, #2563EB, #3B82F6);
            color: white; border: none; border-radius: 14px;
            padding: 0.9rem 2.5rem; font-size: 1rem;
            font-weight: 800; cursor: pointer;
            box-shadow: 0 8px 24px rgba(37,99,235,0.4);
            transition: transform 0.2s;
        }

        #security-overlay .resume-btn:hover { transform: translateY(-2px); }

        /* ===== AUTO-SUBMIT OVERLAY ===== */
        #autosubmit-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.97);
            z-index: 999999;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            text-align: center;
            padding: 2rem;
        }
        #autosubmit-overlay.active { display: flex; }
        #autosubmit-overlay .as-icon { font-size: 4rem; color: #EF4444; animation: shake 0.6s ease; }
        #autosubmit-overlay h2 { color: white; font-size: 1.6rem; font-weight: 900; margin: 0; }
        #autosubmit-overlay p  { color: #94A3B8; font-size: 0.95rem; max-width: 400px; margin: 0; }
        #autosubmit-overlay .as-spinner {
            width: 48px; height: 48px;
            border: 5px solid rgba(255,255,255,0.1);
            border-top-color: #3B82F6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        #autosubmit-overlay .as-label { color: #60A5FA; font-size: 0.9rem; font-weight: 600; }
        @keyframes spin { to { transform: rotate(360deg); } }

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

        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
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
            background: linear-gradient(135deg, #2563EB, #3B82F6);
            color: white;
            border: none;
            padding: 1.1rem 2.5rem;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(37,99,235,0.4);
            transition: transform 0.2s;
        }

        #fullscreen-prompt .fs-btn:hover { transform: translateY(-2px); }

        /* ===== TOPBAR ===== */
        .quiz-topbar {
            background: linear-gradient(135deg, #2563EB, #3B82F6);
            padding: 0.875rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            position: sticky;
            top: 0;
            z-index: 40;
            box-shadow: 0 4px 20px rgba(37,99,235,0.3);
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
            box-shadow: 0 8px 32px rgba(37,99,235,0.1);
            border: 2px solid #DBEAFE;
        }

        body.dark .question-card {
            background: #1E293B;
            border-color: #334155;
        }

        .question-number {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: linear-gradient(135deg, #2563EB, #3B82F6);
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
            box-shadow: 0 8px 24px rgba(37,99,235,0.2);
            border-color: #3B82F6;
        }

        .answer-btn .choice-label {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #DBEAFE;
            color: #2563EB;
            font-weight: 900;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.25s;
        }

        .answer-btn.selected {
            border-color: #2563EB;
            background: #DBEAFE;
            box-shadow: 0 6px 20px rgba(37,99,235,0.25);
        }

        .answer-btn.selected .choice-label { background: #2563EB; color: white; }

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

        .score-chip.score { background: #DBEAFE; color: #2563EB; }
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
            border: 1.5px solid #DBEAFE;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        body.dark .powerups-bar {
            background: #1E293B;
            border-color: #334155;
        }

        .powerup-slot {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
        }

        .powerup-detail-btn {
            position: absolute;
            top: -6px;
            right: -6px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #6366F1;
            color: white;
            border: none;
            font-size: 0.55rem;
            font-weight: 900;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
            transition: transform 0.15s;
        }
        .powerup-detail-btn:hover { transform: scale(1.2); background: #4F46E5; }

        .powerup-duration-badge {
            font-size: 0.6rem;
            font-weight: 900;
            padding: 1px 6px;
            border-radius: 999px;
            background: rgba(0,0,0,0.12);
            color: inherit;
            white-space: nowrap;
        }

        /* Powerup detail modal */
        .pu-modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 99995;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .pu-modal {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            width: 100%;
            max-width: 340px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.3);
            animation: modalIn 0.3s ease;
        }
        body.dark .pu-modal { background: #1E293B; color: #F1F5F9; }
        .pu-modal-icon {
            width: 60px; height: 60px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.75rem;
            margin: 0 auto 1rem;
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
            background: #EFF6FF;
            color: #2563EB;
        }

        .powerup-btn:hover:not(:disabled) { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(0,0,0,0.15); }
        .powerup-btn:disabled { opacity: 0.3; cursor: not-allowed; transform: none; filter: grayscale(0.6); }
        .powerup-btn.used-up  { opacity: 0.2; cursor: not-allowed; transform: none; filter: grayscale(1); }

        @keyframes powerupRotateIn {
            0%   { transform: rotateY(90deg) scale(0.5); opacity: 0; }
            60%  { transform: rotateY(-10deg) scale(1.05); }
            100% { transform: rotateY(0deg) scale(1); opacity: 1; }
        }
        .powerup-btn.rotating-in { animation: powerupRotateIn 0.45s ease forwards; }

        /* Powerup warna per tipe — bg & border */
        .powerup-btn[data-type="supersonic"]      { background:#FEF3C7; border-color:#F59E0B; color:#92400E; }
        .powerup-btn[data-type="streak_booster"]  { background:#D1FAE5; border-color:#10B981; color:#065F46; }
        .powerup-btn[data-type="gift"]            { background:#FCE7F3; border-color:#EC4899; color:#831843; }
        .powerup-btn[data-type="double_jeopardy"] { background:#FEE2E2; border-color:#EF4444; color:#991B1B; }
        .powerup-btn[data-type="2x"]              { background:#EDE9FE; border-color:#8B5CF6; color:#4C1D95; }
        .powerup-btn[data-type="fifty_fifty"]     { background:#CFFAFE; border-color:#06B6D4; color:#164E63; }
        .powerup-btn[data-type="eraser"]          { background:#E0E7FF; border-color:#6366F1; color:#312E81; }
        .powerup-btn[data-type="immunity"]        { background:#CCFBF1; border-color:#14B8A6; color:#134E4A; }
        .powerup-btn[data-type="time_freeze"]     { background:#DBEAFE; border-color:#3B82F6; color:#1E3A8A; }
        .powerup-btn[data-type="power_play"]      { background:#FFEDD5; border-color:#F97316; color:#7C2D12; }
        .powerup-btn[data-type="streak_saver"]    { background:#DCFCE7; border-color:#22C55E; color:#14532D; }
        .powerup-btn[data-type="glitch"]          { background:#F3E8FF; border-color:#A855F7; color:#4A1D96; }

        /* Active state: lebih tebal border */
        .powerup-btn[data-type="supersonic"].pu-active      { background:#F59E0B; color:white; }
        .powerup-btn[data-type="streak_booster"].pu-active  { background:#10B981; color:white; }
        .powerup-btn[data-type="gift"].pu-active            { background:#EC4899; color:white; }
        .powerup-btn[data-type="double_jeopardy"].pu-active { background:#EF4444; color:white; }
        .powerup-btn[data-type="2x"].pu-active              { background:#8B5CF6; color:white; }
        .powerup-btn[data-type="fifty_fifty"].pu-active     { background:#06B6D4; color:white; }
        .powerup-btn[data-type="eraser"].pu-active          { background:#6366F1; color:white; }
        .powerup-btn[data-type="immunity"].pu-active        { background:#14B8A6; color:white; }
        .powerup-btn[data-type="time_freeze"].pu-active     { background:#3B82F6; color:white; }
        .powerup-btn[data-type="power_play"].pu-active      { background:#F97316; color:white; }
        .powerup-btn[data-type="streak_saver"].pu-active    { background:#22C55E; color:white; }
        .powerup-btn[data-type="glitch"].pu-active          { background:#A855F7; color:white; }

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

        /* Glitch Effect */
        @keyframes glitch {
            0%,100% { transform: translate(0); filter: none; }
            20%      { transform: translate(-3px, 2px); filter: hue-rotate(90deg); }
            40%      { transform: translate(3px, -2px); filter: invert(0.1); }
            60%      { transform: translate(-2px, -1px); filter: hue-rotate(-90deg); }
            80%      { transform: translate(2px, 1px); filter: brightness(1.2); }
        }
        body.glitch-effect {
            animation: glitch 0.3s infinite;
        }

        [x-cloak] { display: none !important; }

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
<div id="security-overlay">
    <div class="big-icon"><i class="fas fa-exclamation-triangle"></i></div>
    <h2 id="security-title">Pelanggaran Terdeteksi!</h2>
    <p id="security-msg">Anda keluar dari halaman quiz. Pelanggaran ini telah dicatat.</p>
    <button class="resume-btn" @click="resumeFromViolation()">
        <i class="fas fa-play mr-2"></i> Lanjutkan Quiz
    </button>
</div>

<div id="violation-toast"></div>

{{-- ===== AUTO-SUBMIT OVERLAY ===== --}}
<div id="autosubmit-overlay">
    <div class="as-icon"><i class="fas fa-ban"></i></div>
    <h2>Batas Pelanggaran Tercapai</h2>
    <p>Quiz dikumpulkan otomatis karena terlalu banyak pelanggaran.</p>
    <div class="as-spinner"></div>
    <div class="as-label">Mengumpulkan jawaban...</div>
</div>


{{-- ===== PERINGATAN GURU ===== --}}
<div id="teacher-warning-overlay"
    style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.92);z-index:99996;flex-direction:column;align-items:center;justify-content:center;gap:1.25rem;text-align:center;padding:2rem;animation:fadeIn 0.3s ease">
    <div style="font-size:3.5rem;animation:shake 0.5s ease"><i class="fas fa-exclamation-circle" style="color:#F59E0B"></i></div>
    <h2 style="color:white;font-size:1.5rem;font-weight:900;margin:0">⚠️ Peringatan dari Guru</h2>
    <div id="teacher-warning-msg"
        style="color:#FDE68A;font-size:1rem;font-weight:700;max-width:420px;line-height:1.6;background:rgba(245,158,11,0.15);border:2px solid rgba(245,158,11,0.4);border-radius:16px;padding:1rem 1.5rem">
    </div>
    <p style="color:#94A3B8;font-size:0.85rem;max-width:360px">Pesan ini dikirim oleh guru yang memantau quiz kamu. Pastikan kamu mengerjakan quiz dengan jujur.</p>
    <button onclick="document.getElementById('teacher-warning-overlay').style.display='none'; window.quizPlayerInstance && window.quizPlayerInstance.startPerQuestionTimer()"
        style="background:linear-gradient(135deg,#F59E0B,#D97706);color:white;border:none;border-radius:14px;padding:0.9rem 2.5rem;font-size:1rem;font-weight:800;cursor:pointer;box-shadow:0 8px 24px rgba(245,158,11,0.4);transition:transform 0.2s"
        onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
        <i class="fas fa-check mr-2"></i> Mengerti, Lanjutkan Quiz
    </button>
</div>

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

<div class="quiz-topbar">
    <div>
        <div class="quiz-topbar-title">{{ Str::limit($quiz->title, 40) }}</div>
        <div class="quiz-topbar-meta">{{ $quiz->subject->name_subject ?? '' }}</div>
    </div>

    <div class="progress-bar" style="margin-top: 0.2rem">
        <div class="progress-fill" :style="`width: ${((currentQuestion + 1) / totalQuestions) * 100}%`"></div>
    </div>

    <div style="display:flex;align-items:center;gap:0.75rem;flex-shrink:0">
        @if($quiz->duration > 0)
        <div x-show="!quizFinished"
            :style="timeRemaining > 0
                ? (timeRemaining <= 60
                    ? 'background:rgba(239,68,68,0.4);color:white;padding:0.4rem 0.85rem;border-radius:10px;font-weight:800;font-size:0.85rem;display:flex;align-items:center;gap:0.4rem;animation:timerPulse 0.5s infinite;'
                    : (timeRemaining <= 300
                        ? 'background:rgba(245,158,11,0.4);color:white;padding:0.4rem 0.85rem;border-radius:10px;font-weight:800;font-size:0.85rem;display:flex;align-items:center;gap:0.4rem;'
                        : 'background:rgba(255,255,255,0.15);color:white;padding:0.4rem 0.85rem;border-radius:10px;font-weight:800;font-size:0.85rem;display:flex;align-items:center;gap:0.4rem;'))
                : 'background:rgba(239,68,68,0.6);color:white;padding:0.4rem 0.85rem;border-radius:10px;font-weight:800;font-size:0.85rem;display:flex;align-items:center;gap:0.4rem;'">
            <i class="fas fa-hourglass-half"></i>
            <span x-text="timeRemaining > 0 ? (String(Math.floor(timeRemaining/60)).padStart(2,'0') + ':' + String(Math.floor(timeRemaining%60)).padStart(2,'0')) : '00:00'"></span>
        </div>
        @endif

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

<div style="max-width: 820px; margin: 0 auto; padding: 1.5rem 1rem">

    <div x-show="quizFinished" x-cloak class="quiz-finished-screen">
        <div class="finish-icon"><i class="fas fa-check-circle" style="color:#10B981"></i></div>
        <h2 style="font-size:1.75rem;font-weight:900;margin-bottom:0.5rem">Quiz Selesai!</h2>
        <p style="color:#6B7280;margin-bottom:1.5rem">Jawabanmu telah dikirim. Hasil akan segera tersedia.</p>
        @if($quiz->show_score)
        <div style="background:#DBEAFE;border-radius:16px;padding:1.25rem;display:inline-block;margin-bottom:1.5rem">
            <div style="font-size:2.5rem;font-weight:900;color:#2563EB" x-text="Math.floor(totalScore) + ' pts'"></div>
            <div style="font-size:0.85rem;color:#2563EB;font-weight:700">Total Skor</div>
        </div>
        @endif
        <div>
            <div style="display:flex;align-items:center;justify-content:center;gap:0.5rem;color:#10B981;font-weight:700;font-size:0.9rem">
                <svg class="w-4 h-4 animate-spin" style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Mengalihkan ke halaman hasil...
            </div>
        </div>
    </div>

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

    <div class="question-card mb-4" x-show="!quizFinished"
        :class="{ 'opacity-0 -translate-y-2': animatingOut, 'opacity-100': !animatingOut }"
        style="transition: opacity 0.25s, transform 0.25s">

        <template x-if="questions[currentQuestion]">
            <div>
                <div class="flex items-start justify-between gap-3 mb-4">
                    <span class="question-number">
                        <i class="fas fa-question-circle" style="font-size:0.9rem"></i>
                        Soal <span x-text="currentQuestion + 1"></span>
                    </span>

                    @if($quiz->time_per_question > 0)
                    <div class="timer-ring-container" x-show="!isAnswerRevealed && !timeFreezeActive">
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

                <p class="question-text" x-text="questions[currentQuestion].question"></p>

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

                <template x-if="questions[currentQuestion].type === 'IS'">
                    <div>
                        <input type="text"
                            x-model="questions[currentQuestion].textAnswer"
                            :disabled="isAnswerRevealed"
                            @keyup.enter="!isAnswerRevealed && questions[currentQuestion].textAnswer?.trim() ? submitTextAnswer() : null"
                            style="width:100%;padding:1rem;border:2.5px solid #E5E7EB;border-radius:14px;font-size:1rem;font-family:'Nunito',sans-serif;font-weight:600;outline:none;transition:border-color 0.2s"
                            placeholder="Ketik jawaban Anda dan tekan Enter..."
                            @focus="$el.style.borderColor='#2563EB';"
                            @blur="$el.style.borderColor='#E5E7EB';">

                        <button x-show="!isAnswerRevealed"
                            @click="submitTextAnswer()"
                            :disabled="!questions[currentQuestion].textAnswer?.trim()"
                            style="margin-top:0.875rem;width:100%;padding:0.875rem;background:linear-gradient(135deg,#2563EB,#3B82F6);color:white;border:none;border-radius:14px;font-weight:800;font-size:0.95rem;cursor:pointer;transition:opacity 0.2s"
                            :style="!questions[currentQuestion].textAnswer?.trim() ? 'opacity:0.5;cursor:not-allowed' : ''">
                            <i class="fas fa-paper-plane mr-2"></i> Kirim Jawaban
                        </button>
                    </div>
                </template>

                <div x-show="feedbackMessage" class="feedback-bar" :class="feedbackType" x-text="feedbackMessage"></div>

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

    @if($quiz->enable_powerups)
    {{-- Powerup Detail Modal --}}
    <div x-show="showPowerupDetail" class="pu-modal-backdrop" @click.self="showPowerupDetail = false" x-cloak>
        <div class="pu-modal">
            <div class="pu-modal-icon"
                :style="'background:' + (activePowerupDetail?.bgColor || '#EFF6FF')">
                <i :class="activePowerupDetail?.icon" :style="'color:' + (activePowerupDetail?.accentColor || '#2563EB')"></i>
            </div>
            <h3 style="font-size:1.1rem;font-weight:900;text-align:center;margin-bottom:0.5rem" x-text="activePowerupDetail?.name"></h3>
            <p style="font-size:0.875rem;text-align:center;color:#6B7280;margin-bottom:0.5rem" x-text="activePowerupDetail?.description"></p>
            <template x-if="activePowerupDetail?.duration">
                <p style="font-size:0.8rem;text-align:center;font-weight:700;color:#F97316">
                    <i class="fas fa-clock mr-1"></i> Aktif selama <span x-text="activePowerupDetail?.duration"></span> detik
                </p>
            </template>
            <template x-if="activePowerupDetail?.scope === 'question'">
                <p style="font-size:0.8rem;text-align:center;font-weight:700;color:#3B82F6">
                    <i class="fas fa-question-circle mr-1"></i> Berlaku untuk soal ini saja
                </p>
            </template>
            <button @click="showPowerupDetail = false"
                style="margin-top:1.1rem;width:100%;padding:0.75rem;background:#2563EB;color:white;border:none;border-radius:12px;font-weight:800;cursor:pointer;font-size:0.9rem">
                Mengerti
            </button>
        </div>
    </div>

    <div class="powerups-bar mb-4" x-show="!quizFinished && powerupsRandom && Object.keys(powerupsRandom).length > 0">
        <div style="font-size:0.75rem;font-weight:800;color:#2563EB;width:100%;text-align:center;margin-bottom:0.4rem;display:flex;align-items:center;justify-content:center;gap:0.5rem">
            <i class="fas fa-bolt"></i> Power-ups
            <span style="font-weight:600;color:#6B7280;font-size:0.68rem">
                (<span x-text="Object.values(powerupsRandom).filter(p => !p.used).length"></span>/<span x-text="Object.keys(powerupsRandom).length"></span> tersisa)
            </span>
            <template x-if="_lastPowerupUseQuestion === currentQuestion">
                <span style="font-size:0.65rem;background:#FEF3C7;color:#B45309;padding:2px 7px;border-radius:999px;font-weight:700">
                    <i class="fas fa-lock"></i> 1 per soal
                </span>
            </template>
        </div>
        <template x-for="(pu, key) in powerupsRandom" :key="key">
            <div class="powerup-slot">
                {{-- Detail button --}}
                <button class="powerup-detail-btn"
                    @click.stop="openPowerupDetail(key)"
                    title="Info powerup">
                    <i class="fas fa-info"></i>
                </button>

                <button class="powerup-btn"
                    :data-type="key"
                    @click="activatePowerup(key)"
                    :disabled="isAnswerRevealed || (_lastPowerupUseQuestion === currentQuestion && !pu.used)"
                    :class="{
                        'pu-active': !pu.used && _lastPowerupUseQuestion !== currentQuestion && !isAnswerRevealed,
                        'used-up': pu.used,
                        'rotating-in': pu.rotatingIn
                    }"
                    :title="pu.description">
                    <i :class="pu.icon" style="font-size:1.2rem"></i>
                    <span x-text="pu.name" style="font-size:0.65rem;text-align:center;max-width:70px;line-height:1.2"></span>
                    {{-- Durasi badge untuk powerup yang punya waktu aktif --}}
                    <template x-if="pu.duration && !pu.used">
                        <span class="powerup-duration-badge" x-text="pu.duration + 's'"></span>
                    </template>
                    {{-- Sudah dipakai --}}
                    <template x-if="pu.used">
                        <span class="powerup-duration-badge" style="background:rgba(0,0,0,0.15)">✓ dipakai</span>
                    </template>
                </button>
            </div>
        </template>
    </div>
    @endif

</div>

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
                    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#2563EB,#3B82F6);color:white;display:flex;align-items:center;justify-content:center;font-weight:900;flex-shrink:0"
                        x-text="(entry.student_name || entry.name || 'U').charAt(0).toUpperCase()"></div>
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:800;font-size:0.875rem" x-text="entry.student_name || entry.name || 'Peserta'"></div>
                        <div style="font-size:0.72rem;color:#6B7280" x-text="'Waktu: ' + formatTime(entry.time_taken || 0)"></div>
                    </div>
                    <div style="font-weight:900;color:#2563EB;font-size:1rem" x-text="(entry.score || 0) + ' pts'"></div>
                </div>
            </template>

            @if($quiz->show_score)
            <div style="border-top:2px solid #F3F4F6;margin-top:0.75rem;padding-top:0.75rem;display:flex;justify-content:space-between;align-items:center">
                <span style="font-weight:700;font-size:0.875rem">Skor Kamu:</span>
                <span style="font-weight:900;color:#2563EB;font-size:1.1rem" x-text="Math.floor(totalScore) + ' pts'"></span>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

<div x-show="settingsPanelOpen" class="settings-backdrop" @click="settingsPanelOpen = false" x-cloak></div>
<div class="settings-panel" :class="{ 'open': settingsPanelOpen }">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
        <h3 style="font-size:1.05rem;font-weight:900"><i class="fas fa-cog mr-2"></i> Pengaturan</h3>
        <button @click="settingsPanelOpen = false" style="background:#F3F4F6;border:none;border-radius:8px;padding:0.4rem 0.7rem;cursor:pointer;font-weight:700"><i class="fas fa-times"></i></button>
    </div>

    <div style="display:flex;flex-direction:column;gap:1.25rem">
        <div style="display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-weight:700;font-size:0.875rem">Mode Gelap</div>
                <div style="font-size:0.75rem;color:#6B7280">Hemat mata di malam hari</div>
            </div>
            <div class="toggle-switch" style="background: settings.darkMode ? '#2563EB' : '#E5E7EB'"
                :style="'background:' + (settings.darkMode ? '#2563EB' : '#E5E7EB')"
                @click="settings.darkMode = !settings.darkMode; toggleDarkMode()">
                <div class="toggle-knob" :style="settings.darkMode ? 'transform:translateX(20px)' : ''"></div>
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between">
            <div>
                <div style="font-weight:700;font-size:0.875rem">Efek Suara</div>
                <div style="font-size:0.75rem;color:#6B7280">Suara saat menjawab</div>
            </div>
            <div class="toggle-switch"
                :style="'background:' + (settings.soundEnabled ? '#2563EB' : '#E5E7EB')"
                @click="toggleSound()">
                <div class="toggle-knob" :style="settings.soundEnabled ? 'transform:translateX(20px)' : ''"></div>
            </div>
        </div>

        <div>
            <div style="font-weight:700;font-size:0.875rem;margin-bottom:0.5rem">Ukuran Teks</div>
            <div style="display:flex;gap:0.5rem">
                <button @click="settings.textSize='small';localStorage.setItem('quiz_textSize','small')"
                    :style="settings.textSize==='small' ? 'background:#2563EB;color:white' : 'background:#F3F4F6;color:#374151'"
                    style="flex:1;padding:0.5rem;border-radius:8px;border:none;cursor:pointer;font-weight:700;font-size:0.8rem">Kecil</button>
                <button @click="settings.textSize='normal';localStorage.setItem('quiz_textSize','normal')"
                    :style="settings.textSize==='normal' ? 'background:#2563EB;color:white' : 'background:#F3F4F6;color:#374151'"
                    style="flex:1;padding:0.5rem;border-radius:8px;border:none;cursor:pointer;font-weight:700;font-size:0.8rem">Normal</button>
                <button @click="settings.textSize='large';localStorage.setItem('quiz_textSize','large')"
                    :style="settings.textSize==='large' ? 'background:#2563EB;color:white' : 'background:#F3F4F6;color:#374151'"
                    style="flex:1;padding:0.5rem;border-radius:8px;border:none;cursor:pointer;font-weight:700;font-size:0.8rem">Besar</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.quizData = {
        totalQuestions: {{ $quiz->questions->count() }},
        quizDuration: {{ $quiz->duration }},
        timePerQuestion: {{ $quiz->time_per_question }},
        @php
            $quizStartedAt = $quiz->quiz_started_at;
            if ($quizStartedAt && $quiz->duration > 0 && $quiz->is_quiz_started) {
                $elapsed = now()->diffInSeconds($quizStartedAt);
                $computed = max(0, ($quiz->duration * 60) - $elapsed);
            } else {
                $computed = $quizTimeRemaining ?? ($quiz->duration * 60);
            }

            // Shuffle soal jika diaktifkan (dilakukan di server agar konsisten per attempt)
            // Seed dari attempt_id agar urutan sama walau refresh
            $questionsCollection = $quiz->questions->load('choices');
            if ($quiz->shuffle_question) {
                // Gunakan seed dari user_id + quiz_id agar per-siswa unik tapi konsisten saat refresh
                $seed = crc32(auth()->id() . '-' . $quiz->id);
                mt_srand($seed);
                $questionsArr = $questionsCollection->all();
                for ($i = count($questionsArr) - 1; $i > 0; $i--) {
                    $j = mt_rand(0, $i);
                    [$questionsArr[$i], $questionsArr[$j]] = [$questionsArr[$j], $questionsArr[$i]];
                }
                $questionsCollection = collect($questionsArr);
            }

            $questionsJson = $questionsCollection->map(function ($q) use ($quiz) {
                $choices = $q->type === 'PG'
                    ? $q->choices->map(function ($c) {
                        return [
                            'id'          => $c->id,
                            'choice_text' => $c->text,
                            'is_correct'  => $c->is_correct,
                            'disabled'    => false,
                        ];
                    })->values()->all()
                    : [];

                // Shuffle pilihan jawaban jika diaktifkan
                if ($quiz->shuffle_answer && count($choices) > 0) {
                    // Shuffle tapi pertahankan is_correct melekat pada teks
                    shuffle($choices);
                }

                return [
                    'id'             => $q->id,
                    'question'       => $q->question,
                    'type'           => $q->type,
                    'score'          => $q->score,
                    'explanation'    => ($quiz->show_correct_answer ?? false) ? ($q->explanation ?? '') : '',
                    'choices'        => $choices,
                    'selectedAnswer' => null,
                    'textAnswer'     => '',
                ];
            })->values()->all();
        @endphp
        timeRemaining: parseInt({{ $computed }}) || 0,
        serverStartedAt: {{ $quiz->is_quiz_started && $quiz->quiz_started_at ? $quiz->quiz_started_at->timestamp : 'null' }},
        serverDurationSec: {{ $quiz->duration * 60 }},
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
        quizStatusUrl: '{{ route('quiz.status', $quiz->id) }}',
        checkWarningUrl: '{{ route('quiz.room.check-warning', $quiz->id) }}',
        maxViolations: {{ (int) ($quiz->violation_limit ?? 3) }},
        disableViolations: {{ $quiz->disable_violations ? 'true' : 'false' }},
        quizMode: '{{ $quiz->quiz_mode }}',
        questions: {!! json_encode($questionsJson) !!}
    };

    function quizPlayer() {
        return {
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
            _isAutoSubmit: false,
            nextQuestionMultiplier: 1,
            activeMultiplier: 1,
            multiplierExpiresAt: null,
            immunityActive: false,
            streakSaverActive: false,
            supersonicActive: false,
            supersonicMultiplier: 1.5,
            doubleJeopardyActive: false,
            timeFreezeActive: false,
            powerPlayActive: false,
            powerPlayExpiresAt: null,
            bgMusicAudio: null,
            bgMusicPlaying: false,
            _durationTimer: null,
            _syncTimer: null,
            _warnedFiveMin: false,
            _warnedOneMin: false,
            _lastPowerupUseQuestion: -1,
            showPowerupDetail: false,
            activePowerupDetail: null,
            _warningPollTimer: null,

            settings: {
                soundEnabled: localStorage.getItem('quiz_sound') !== 'false',
                darkMode: localStorage.getItem('quiz_darkMode') === 'true',
                textSize: localStorage.getItem('quiz_textSize') || 'normal',
            },

            powerups: {
                supersonic:      { name: 'Supersonic',   icon: 'fas fa-rocket',              description: '1.5× skor selama 20 detik',               duration: 20, scope: 'timed',    bgColor: '#FEF3C7', accentColor: '#D97706' },
                streak_booster:  { name: 'Streak +1',    icon: 'fas fa-fire',                description: 'Tambah streak +1 sekarang juga',           duration: null, scope: 'instant', bgColor: '#D1FAE5', accentColor: '#059669' },
                gift:            { name: 'Gift',          icon: 'fas fa-gift',                description: 'Kirim 800 poin ke pemain acak',            duration: null, scope: 'instant', bgColor: '#FCE7F3', accentColor: '#DB2777' },
                double_jeopardy: { name: 'Dbl Jeopardy', icon: 'fas fa-exclamation-triangle', description: 'Benar = 2× poin, Salah = 0 poin',          duration: null, scope: 'question', bgColor: '#FEE2E2', accentColor: '#DC2626' },
                '2x':            { name: '2X',            icon: 'fas fa-times',               description: '2× poin untuk soal berikutnya',           duration: null, scope: 'question', bgColor: '#EDE9FE', accentColor: '#7C3AED' },
                fifty_fifty:     { name: '50-50',         icon: 'fas fa-cut',                 description: 'Hapus setengah pilihan yang salah',        duration: null, scope: 'question', bgColor: '#CFFAFE', accentColor: '#0891B2' },
                eraser:          { name: 'Eraser',        icon: 'fas fa-eraser',              description: 'Hapus 1 pilihan yang pasti salah',         duration: null, scope: 'question', bgColor: '#E0E7FF', accentColor: '#4F46E5' },
                immunity:        { name: 'Immunity',      icon: 'fas fa-shield-alt',          description: 'Jawaban salah pertama diabaikan',         duration: null, scope: 'question', bgColor: '#CCFBF1', accentColor: '#0D9488' },
                time_freeze:     { name: 'Time Freeze',   icon: 'fas fa-snowflake',           description: 'Hentikan timer soal ini sepenuhnya',      duration: null, scope: 'question', bgColor: '#DBEAFE', accentColor: '#2563EB' },
                power_play:      { name: 'Power Play',    icon: 'fas fa-users',               description: 'Semua pemain mendapat +50% skor selama 20 detik', duration: 20, scope: 'timed', bgColor: '#FFEDD5', accentColor: '#EA580C' },
                streak_saver:    { name: 'Streak Save',   icon: 'fas fa-life-ring',           description: 'Streak tidak hilang jika menjawab salah', duration: null, scope: 'question', bgColor: '#DCFCE7', accentColor: '#16A34A' },
                glitch:          { name: 'Glitch',        icon: 'fas fa-bug',                 description: 'Layar lawan glitch selama 10 detik',       duration: 10, scope: 'timed',    bgColor: '#F3E8FF', accentColor: '#9333EA' },
            },
            powerupsRandom: {},

            get answeredCount() {
                return this.questions.filter(q => q.selectedAnswer !== null || q.textAnswer?.trim()).length;
            },

            async init() {
                window.quizPlayerInstance = this;
                document.documentElement.classList.toggle('dark', this.settings.darkMode);
                document.body.classList.toggle('dark', this.settings.darkMode);

                if (window.quizData.enablePowerups) {
                    this.selectRandomPowerups();
                    // No cooldown countdown needed
                }

                this.preloadSounds();
                this.initSecurityListeners();

                if (window.quizData.showLeaderboard) {
                    this.loadLeaderboard().catch(() => {});
                }

                if (window.quizData.fullscreenMode && !document.fullscreenElement) {
                    // Tunda sedikit agar DOM selesai render sebelum prompt muncul
                    setTimeout(() => {
                        document.getElementById('fullscreen-prompt')?.classList.add('active');
                    }, 300);
                }

                if (window.quizData.enableMusic) this.initBgMusic();

                // Hitung waktu langsung dari serverStartedAt (unix timestamp dari server)
                // agar timer akurat sejak pertama kali dibuka, tidak bergantung nilai PHP render
                if (window.quizData.serverStartedAt && window.quizData.serverDurationSec > 0) {
                    const nowSec     = Math.floor(Date.now() / 1000);
                    const elapsed    = nowSec - window.quizData.serverStartedAt;
                    const remaining  = Math.max(0, window.quizData.serverDurationSec - elapsed);
                    this.timeRemaining = remaining;
                }

                await this.loadProgress();
                this.startPerQuestionTimer();
                this.startDurationTimer();
                this.startWarningPolling();

                // Deteksi tutup tab / navigasi paksa — kirim lewat sendBeacon (tidak butuh response)
                const sendLeaveBeacon = () => {
                    if (this.quizFinished) return;
                    const payload = JSON.stringify({ type: 'page_leave', details: 'Tab ditutup atau navigasi paksa' });
                    const blob    = new Blob([payload], { type: 'application/json' });
                    navigator.sendBeacon(
                        window.quizData.violationUrl + '?_token=' + window.quizData.csrfToken,
                        blob
                    );
                };
                window.addEventListener('beforeunload', sendLeaveBeacon);
                window.addEventListener('pagehide',     sendLeaveBeacon);
            },

            selectRandomPowerups() {
                const keys = Object.keys(this.powerups);
                const shuffled = keys.sort(() => 0.5 - Math.random()).slice(0, 3);
                this.powerupsRandom = {};
                shuffled.forEach(k => {
                    this.powerupsRandom[k] = { ...this.powerups[k], used: false, rotatingIn: false };
                });
            },

            resetPowerupsForNewQuestion() {
                if (!window.quizData.enablePowerups) return;
                // Each new question: replace ONE used powerup with a new random one
                const usedKeys = Object.keys(this.powerupsRandom).filter(k => this.powerupsRandom[k].used);
                if (usedKeys.length > 0) {
                    // Pick one used slot to refresh
                    const toReplace = usedKeys[Math.floor(Math.random() * usedKeys.length)];
                    // Pick a new powerup not currently in the bar
                    const currentKeys = Object.keys(this.powerupsRandom);
                    const available = Object.keys(this.powerups).filter(k => !currentKeys.includes(k) || k === toReplace);
                    if (available.length > 0) {
                        const newKey = available[Math.floor(Math.random() * available.length)];
                        // Remove old, add new
                        delete this.powerupsRandom[toReplace];
                        this.powerupsRandom[newKey] = { ...this.powerups[newKey], used: false, rotatingIn: true };
                        setTimeout(() => {
                            if (this.powerupsRandom[newKey]) this.powerupsRandom[newKey].rotatingIn = false;
                        }, 500);
                    } else {
                        // Fallback: just reset the used powerup
                        this.powerupsRandom[toReplace].used = false;
                    }
                }
                this._lastPowerupUseQuestion = -1;
            },

            openPowerupDetail(key) {
                this.activePowerupDetail = this.powerupsRandom[key] || null;
                this.showPowerupDetail = true;
            },

            startWarningPolling() {
                if (!window.quizData.checkWarningUrl) return;
                // Poll setiap 6 detik — tidak terlalu sering agar tidak boros
                this._warningPollTimer = setInterval(() => this.checkTeacherWarning(), 6000);
            },

            async checkTeacherWarning() {
                if (this.quizFinished) {
                    clearInterval(this._warningPollTimer);
                    return;
                }
                try {
                    const r = await fetch(window.quizData.checkWarningUrl, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.quizData.csrfToken }
                    });
                    const data = await r.json();
                    if (data.warning && data.warning.message) {
                        this.showTeacherWarning(data.warning.message);
                    }
                } catch(e) {}
            },

            showTeacherWarning(message) {
                this.playSound('countdown');
                const overlay = document.getElementById('teacher-warning-overlay');
                const msgEl   = document.getElementById('teacher-warning-msg');
                if (!overlay || !msgEl) return;
                msgEl.textContent = message;
                overlay.style.display = 'flex';
                // Jeda timer per soal selama overlay tampil (jika ada)
                clearInterval(this.perQuestionTimer);
            },

            enterFullscreen() {
                const el = document.documentElement;
                const fn = el.requestFullscreen || el.webkitRequestFullscreen || el.mozRequestFullScreen;
                if (fn) fn.call(el).then(() => {
                    document.getElementById('fullscreen-prompt')?.classList.remove('active');
                }).catch(() => {});
            },

            initSecurityListeners() {
                // ============================================================
                // DETEKSI SELALU AKTIF — tidak bergantung setting quiz apapun
                // ============================================================

                // 1. Keluar tab / minimize / pindah window (paling andal)
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden && !this.quizFinished) {
                        this.handleViolation('tab_switch', 'Keluar dari tab / minimize');
                    }
                });

                // 2. Window kehilangan fokus (klik di luar browser, alt-tab, dll)
                window.addEventListener('blur', () => {
                    if (!this.quizFinished) {
                        this.handleViolation('window_blur', 'Pindah ke aplikasi lain');
                    }
                });

                // 3. Cegah + catat copy/paste/right-click
                document.addEventListener('copy', (e) => {
                    e.preventDefault();
                    if (!this.quizFinished) this.handleViolation('copy', 'Mencoba menyalin teks');
                });
                document.addEventListener('cut', (e) => {
                    e.preventDefault();
                    if (!this.quizFinished) this.handleViolation('cut', 'Mencoba cut teks');
                });
                document.addEventListener('paste', (e) => {
                    e.preventDefault();
                    if (!this.quizFinished) this.handleViolation('paste', 'Mencoba paste');
                });
                document.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    if (!this.quizFinished) this.handleViolation('right_click', 'Klik kanan');
                });

                // 4. Shortcut keyboard berbahaya (F12, Ctrl+U, Ctrl+S, PrintScreen, dll)
                document.addEventListener('keydown', (e) => {
                    if (this.quizFinished) return;
                    const ctrl = e.ctrlKey || e.metaKey;
                    // PrintScreen
                    if (e.key === 'PrintScreen') {
                        e.preventDefault();
                        this.handleViolation('screenshot', 'Mencoba screenshot (PrintScreen)');
                        return;
                    }
                    // F12 DevTools
                    if (e.key === 'F12') {
                        e.preventDefault();
                        this.handleViolation('devtools', 'Membuka DevTools (F12)');
                        return;
                    }
                    if (ctrl) {
                        // Ctrl+U view source
                        if (e.key === 'u' || e.key === 'U') {
                            e.preventDefault();
                            this.handleViolation('view_source', 'Mencoba melihat kode sumber');
                            return;
                        }
                        // Ctrl+Shift+I / Ctrl+Shift+J DevTools
                        if (e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j')) {
                            e.preventDefault();
                            this.handleViolation('devtools', 'Membuka DevTools');
                            return;
                        }
                        // Ctrl+C/X/V sudah ditangani event copy/cut/paste di atas
                        // Ctrl+A (select all) — blokir saja, tidak catat sebagai pelanggaran besar
                        if (e.key === 'a' || e.key === 'A') {
                            e.preventDefault();
                            return;
                        }
                        // Ctrl+S save
                        if (e.key === 's' || e.key === 'S') {
                            e.preventDefault();
                            this.handleViolation('save_page', 'Mencoba menyimpan halaman');
                            return;
                        }
                        // Ctrl+P print
                        if (e.key === 'p' || e.key === 'P') {
                            e.preventDefault();
                            this.handleViolation('print', 'Mencoba mencetak halaman');
                            return;
                        }
                    }
                });

                // 5. Deteksi DevTools terbuka lewat ukuran window (threshold 160px)
                this._devtoolsCheckInterval = setInterval(() => {
                    if (this.quizFinished) { clearInterval(this._devtoolsCheckInterval); return; }
                    const threshold = 160;
                    const widthDiff  = window.outerWidth  - window.innerWidth;
                    const heightDiff = window.outerHeight - window.innerHeight;
                    if (widthDiff > threshold || heightDiff > threshold) {
                        this.handleViolation('devtools_open', 'DevTools terdeteksi terbuka');
                    }
                }, 3000);

                // 6. Fullscreen exit — selalu pantau, bukan hanya jika fullscreenMode aktif
                document.addEventListener('fullscreenchange', () => {
                    if (!this.quizFinished && window.quizData.fullscreenMode && !document.fullscreenElement) {
                        const overlay = document.getElementById('fullscreen-prompt');
                        if (overlay) overlay.classList.add('active');
                        this.handleViolation('fullscreen_exit', 'Keluar dari mode layar penuh');
                    }
                });
            },

            showViolationToast(msg) {
                const t = document.getElementById('violation-toast');
                if (!t) return;
                t.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>${msg}`;
                t.classList.add('show');
                clearTimeout(this._toastTimer);
                this._toastTimer = setTimeout(() => t.classList.remove('show'), 4000);
            },

            resumeFromViolation() {
                this._securityBlocked = false;
                document.getElementById('security-overlay')?.classList.remove('active');
                if (window.quizData.fullscreenMode) this.enterFullscreen();
            },

            showAutoSubmitOverlay(msg = null) {
                // Tampilkan overlay auto-submit dan kunci semua interaksi
                this._isAutoSubmit = true;
                document.getElementById('security-overlay')?.classList.remove('active');
                const overlay = document.getElementById('autosubmit-overlay');
                if (overlay) overlay.classList.add('active');
                if (msg) {
                    const lbl = overlay?.querySelector('.as-label');
                    if (lbl) lbl.textContent = msg;
                }
                // Keluar dari fullscreen agar redirect tidak tertahan browser
                if (document.fullscreenElement) {
                    document.exitFullscreen().catch(() => {});
                }
            },

            async handleViolation(type, details = null) {
                if (this.quizFinished) return;

                // Debounce: abaikan event sejenis dalam 2 detik terakhir
                const now = Date.now();
                const lastKey = `_vLast_${type}`;
                if (this[lastKey] && (now - this[lastKey]) < 2000) return;
                this[lastKey] = now;

                this.violationCount++;

                // Jika pelanggaran dinonaktifkan, hanya tampilkan toast tanpa auto-submit
                if (window.quizData.disableViolations) {
                    this.showViolationToast('Pelanggaran: ' + (details || type));
                    return;
                }

                const max    = window.quizData.maxViolations || 3;
                const remain = max - this.violationCount;

                // Tampilkan toast dengan sisa peluang
                if (remain > 0) {
                    this.showViolationToast('Pelanggaran #' + this.violationCount + '/' + max + ': ' + (details || type) + ' -- Sisa ' + remain + ' peluang!');
                } else {
                    this.showViolationToast('Batas pelanggaran tercapai! Quiz dikumpulkan otomatis.');
                }

                // Client-side auto-submit untuk mode mandiri (fallback jika server tidak merespons)
                const isHomework  = window.quizData.quizMode === 'homework';
                const clientLimit = isHomework && this.violationCount >= max;

                const payload = JSON.stringify({ type, details });
                const url     = window.quizData.violationUrl;
                const headers = { 'X-CSRF-TOKEN': window.quizData.csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' };
                const beaconPayload = new Blob([payload], { type: 'application/json' });

                // Tampilkan overlay autosubmit langsung jika sudah di batas
                if (clientLimit) this.showAutoSubmitOverlay();

                try {
                    const r = await fetch(url, { method: 'POST', headers, body: payload });
                    const data = await r.json().catch(() => ({}));
                    if (data.violation_count) this.violationCount = data.violation_count;
                    if (data.max_violations)  window.quizData.maxViolations = data.max_violations;
                    if (data.auto_submit || clientLimit) {
                        this.showAutoSubmitOverlay();
                        this.submitQuiz();
                    }
                } catch(e) {
                    // fetch gagal - coba sendBeacon, dan tetap auto-submit jika limit tercapai
                    try { navigator.sendBeacon(url + '?_token=' + window.quizData.csrfToken, beaconPayload); } catch(_) {}
                    if (clientLimit) this.submitQuiz();
                }
            },

            startDurationTimer() {
                const dur = window.quizData.quizDuration;
                if (!dur || dur <= 0) return;

                // Jika server menyediakan quiz_started_at, hitung langsung dari situ
                // agar timer selalu akurat meski halaman di-refresh berkali-kali
                if (window.quizData.serverStartedAt) {
                    const nowSec      = Math.floor(Date.now() / 1000);
                    const elapsedSec  = nowSec - window.quizData.serverStartedAt;
                    const remaining   = Math.max(0, window.quizData.serverDurationSec - elapsedSec);
                    this.timeRemaining = remaining;
                }

                if (this.timeRemaining <= 0) {
                    if (!this.quizFinished) {
                        setTimeout(() => this.submitQuiz(), 1200);
                    }
                    return;
                }

                if (this._durationTimer) clearInterval(this._durationTimer);
                this._warnedFiveMin = this.timeRemaining <= 300;
                this._warnedOneMin  = this.timeRemaining <= 60;

                // Anchor ke wall-clock agar tidak drift
                this._timerStartedAt    = Date.now();
                this._timerStartSeconds = this.timeRemaining;

                this._durationTimer = setInterval(() => {
                    if (this.quizFinished) { clearInterval(this._durationTimer); return; }

                    // Hitung ulang dari serverStartedAt jika tersedia (paling akurat)
                    if (window.quizData.serverStartedAt) {
                        const nowSec     = Math.floor(Date.now() / 1000);
                        const elapsed    = nowSec - window.quizData.serverStartedAt;
                        this.timeRemaining = Math.max(0, window.quizData.serverDurationSec - elapsed);
                    } else {
                        const elapsed = Math.floor((Date.now() - this._timerStartedAt) / 1000);
                        this.timeRemaining = Math.max(0, this._timerStartSeconds - elapsed);
                    }

                    if (!this._warnedFiveMin && this.timeRemaining <= 300) {
                        this._warnedFiveMin = true;
                        this.playSound('countdown');
                    }
                    if (!this._warnedOneMin && this.timeRemaining <= 60) {
                        this._warnedOneMin = true;
                        this.playSound('countdown');
                    }

                    if (this.timeRemaining <= 0) {
                        clearInterval(this._durationTimer);
                        if (!this.quizFinished) {
                            setTimeout(() => this.submitQuiz(), 1200);
                        }
                    }
                }, 1000);

                // Sync dengan server setiap 5 detik (lebih sering dari 20 detik sebelumnya)
                if (this._syncTimer) clearInterval(this._syncTimer);
                this._syncTimer = setInterval(() => this.syncTimeWithServer(), 5000);
            },

            async syncTimeWithServer() {
                if (this.quizFinished || !window.quizData.quizStatusUrl) return;
                try {
                    const r = await fetch(window.quizData.quizStatusUrl, {
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.quizData.csrfToken }
                    });
                    const d = await r.json();
                    if (!d.success) return;

                    if (d.quiz_expired || (!d.is_quiz_started && window.quizData.quizMode !== 'homework')) {
                        if (!this.quizFinished) {
                            clearInterval(this._durationTimer);
                            this.timeRemaining = 0;
                            setTimeout(() => this.submitQuiz(), 1200);
                        }
                        return;
                    }

                    // Update serverStartedAt dari response jika ada — ini sumber paling akurat
                    if (d.quiz_started_at) {
                        window.quizData.serverStartedAt = Math.floor(new Date(d.quiz_started_at).getTime() / 1000);
                    }

                    if (d.time_remaining !== null && d.time_remaining !== undefined) {
                        const srv = parseInt(d.time_remaining);
                        if (srv <= 0 && !this.quizFinished) {
                            clearInterval(this._durationTimer);
                            this.timeRemaining = 0;
                            setTimeout(() => this.submitQuiz(), 1200);
                        } else {
                            // Koreksi ke server jika selisih lebih dari 3 detik
                            const diff = Math.abs(srv - this.timeRemaining);
                            if (diff > 3) {
                                this.timeRemaining      = srv;
                                this._timerStartedAt    = Date.now();
                                this._timerStartSeconds = srv;
                            }
                        }
                    }
                } catch(e) {}
            },

            startPerQuestionTimer() {
                if (this.perQuestionTimer) clearInterval(this.perQuestionTimer);
                if (this.timeFreezeActive) return;
                this.questionTimeRemaining = window.quizData.timePerQuestion;
                if (this.questionTimeRemaining <= 0) return;
                this.perQuestionTimer = setInterval(() => {
                    if (this.quizFinished || this._securityBlocked) return;
                    if (this.timeFreezeActive) return;
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

                    if (this.doubleJeopardyActive) {
                        earned *= 2;
                        this.doubleJeopardyActive = false;
                    } else if (this.nextQuestionMultiplier > 1) {
                        earned *= this.nextQuestionMultiplier;
                        this.nextQuestionMultiplier = 1;
                    }

                    if (this.supersonicActive) {
                        earned *= (this.supersonicMultiplier || 1.5);
                    }

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
                    if (this.doubleJeopardyActive) {
                        this.doubleJeopardyActive = false;
                        earned = 0;
                    }

                    if (this.immunityActive) {
                        this.immunityActive = false;
                        this.isAnswerRevealed = false;
                        q.selectedAnswer = null;
                        return;
                    } else if (this.streakSaverActive) {
                        this.streakSaverActive = false;
                    } else {
                        this.streakCount = 0;
                    }
                }

                // Suara & feedback langsung — tidak tunggu saveProgress
                this.feedbackType    = isCorrect ? 'correct' : 'incorrect';
                this.feedbackMessage = isCorrect ? 'Benar!' : 'Salah!';
                this.playSound(isCorrect ? 'correct' : 'incorrect');
                this.isAnswerRevealed = true;
                clearInterval(this.perQuestionTimer);

                // Simpan progress ke server di background (tidak blokir UI/suara)
                this.saveProgress().catch(() => {});

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
                    this.timeFreezeActive = false;
                    this.animatingOut = false;
                    this.startPerQuestionTimer();
                    this.resetPowerupsForNewQuestion();
                }, 250);
            },

            async activatePowerup(type) {
                if (!window.quizData.enablePowerups) return;
                const pu = this.powerupsRandom[type];
                if (!pu || this.isAnswerRevealed || pu.used) return;

                // Maks 1 powerup per soal
                if (this._lastPowerupUseQuestion === this.currentQuestion) return;

                pu.used = true; // Hilang setelah dipakai, tidak bisa dipakai lagi
                this._lastPowerupUseQuestion = this.currentQuestion;
                this.playSound('powerup');

                const q = this.questions[this.currentQuestion];

                switch(type) {
                    case 'supersonic':
                        this.supersonicActive = true;
                        this.supersonicMultiplier = 1.5;
                        setTimeout(() => {
                            this.supersonicActive = false;
                            this.supersonicMultiplier = 1;
                        }, 20000);
                        break;

                    case 'streak_booster':
                        this.streakCount++;
                        this.showBonusPopup('Streak +1!');
                        break;

                    case 'gift':
                        break;

                    case 'double_jeopardy':
                        this.doubleJeopardyActive = true;
                        break;

                    case '2x':
                        this.nextQuestionMultiplier = 2;
                        break;

                    case 'fifty_fifty': {
                        const wrong50 = q.choices.filter(c => !c.is_correct && !c.disabled);
                        const half = Math.ceil(wrong50.length / 2);
                        wrong50.sort(() => Math.random() - 0.5).slice(0, half).forEach(c => c.disabled = true);
                        break;
                    }

                    case 'eraser': {
                        const wrongE = q.choices.filter(c => !c.is_correct && !c.disabled);
                        if (wrongE.length > 0) {
                            wrongE[Math.floor(Math.random() * wrongE.length)].disabled = true;
                        }
                        break;
                    }

                    case 'immunity':
                        this.immunityActive = true;
                        break;

                    case 'time_freeze':
                        this.timeFreezeActive = true;
                        clearInterval(this.perQuestionTimer);
                        break;

                    case 'power_play':
                        this.powerPlayActive = true;
                        this.activeMultiplier = Math.max(this.activeMultiplier, 1.5);
                        this.powerPlayExpiresAt = Date.now() + 20000;
                        setTimeout(() => {
                            this.powerPlayActive = false;
                            if (this.activeMultiplier === 1.5) this.activeMultiplier = 1;
                        }, 20000);
                        break;

                    case 'streak_saver':
                        this.streakSaverActive = true;
                        break;

                    case 'glitch':
                        break;
                }

                try {
                    await fetch(window.quizData.powerupUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': window.quizData.csrfToken, 'Content-Type': 'application/json' },
                        body: JSON.stringify({ type, question_id: q.id })
                    });
                } catch(e) {}
            },

            async submitQuiz() {
                if (this.quizFinished) return;
                this.quizFinished = true;         // kunci permanen - tidak pernah di-reset
                clearInterval(this.perQuestionTimer);
                clearInterval(this._durationTimer);

                // Tampilkan overlay "mengumpulkan" jika belum tampil
                this.showAutoSubmitOverlay();

                const answers = this.questions.map(q => ({
                    question_id: q.id,
                    choice_id: q.choices?.[q.selectedAnswer]?.id || null,
                    text_answer: q.textAnswer || null,
                }));

                // Retry helper: coba submit sampai 3x jika gagal
                let data = null;
                for (let attempt = 0; attempt < 3; attempt++) {
                    try {
                        const r = await fetch(window.quizData.submitUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.quizData.csrfToken, 'Accept': 'application/json' },
                            body: JSON.stringify({ answers, total_score: this.totalScore, time_spent: window.quizData.quizDuration - this.timeRemaining })
                        });
                        data = await r.json();
                        if (data.success) break;
                    } catch(e) {
                        if (attempt === 2) {
                            // Semua retry gagal - update label overlay
                            const lbl = document.querySelector('#autosubmit-overlay .as-label');
                            if (lbl) lbl.textContent = 'Koneksi bermasalah, mencoba lagi...';
                            await new Promise(r => setTimeout(r, 1500));
                        }
                    }
                }

                if (data && data.success) {
                    // Auto-submit: jangan play victory sound, langsung redirect
                    if (!this._isAutoSubmit) this.playSound('victory');
                    const lbl = document.querySelector('#autosubmit-overlay .as-label');
                    if (lbl) lbl.textContent = 'Berhasil! Mengalihkan ke halaman hasil...';

                    // Leaderboard hanya untuk submit manual, bukan auto-submit
                    if (!this._isAutoSubmit && window.quizData.showLeaderboard) {
                        await this.loadLeaderboard().catch(() => {});
                        if (this.leaderboard.length > 0) {
                            this.showLeaderboardModal = true;
                            setTimeout(() => { window.location.href = data.redirect; }, 3000);
                            return;
                        }
                    }
                    // Langsung redirect tanpa delay ekstra
                    window.location.href = data.redirect;
                } else if (data && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    // Gagal total - redirect ke halaman quiz
                    const lbl = document.querySelector('#autosubmit-overlay .as-label');
                    if (lbl) lbl.textContent = 'Gagal mengumpulkan, mengarahkan ulang...';
                    setTimeout(() => { window.location.href = window.quizData.quizUrl || '/'; }, 2000);
                }
            },

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
                        this.totalScore  = p.total_score  || 0;
                        this.streakCount = p.streak_count || 0;

                        // Restore jawaban yang sudah tersimpan di DB
                        let lastAnsweredIndex = -1;
                        if (p.answers && p.answers.length > 0) {
                            p.answers.forEach(saved => {
                                const q = this.questions.find(q => q.id == saved.question_id);
                                if (!q) return;
                                if (saved.choice_id) {
                                    const idx = q.choices?.findIndex(c => c.id == saved.choice_id);
                                    if (idx !== -1 && idx !== undefined) {
                                        q.selectedAnswer = idx;
                                        // Temukan index soal ini dalam array
                                        const qIdx = this.questions.indexOf(q);
                                        if (qIdx > lastAnsweredIndex) lastAnsweredIndex = qIdx;
                                    }
                                }
                                if (saved.text_answer) {
                                    q.textAnswer = saved.text_answer;
                                    const qIdx = this.questions.indexOf(q);
                                    if (qIdx > lastAnsweredIndex) lastAnsweredIndex = qIdx;
                                }
                            });
                        }

                        // Tentukan posisi soal: gunakan current_question dari server
                        // jika valid, atau cari soal pertama yang belum dijawab
                        const serverQ = typeof p.current_question === 'number' ? p.current_question : -1;
                        if (serverQ >= 0 && serverQ < this.totalQuestions) {
                            this.currentQuestion = serverQ;
                        } else {
                            // Fallback: soal pertama yang belum dijawab
                            const firstUnanswered = this.questions.findIndex(q =>
                                q.selectedAnswer === null && !q.textAnswer?.trim()
                            );
                            this.currentQuestion = firstUnanswered !== -1
                                ? firstUnanswered
                                : (lastAnsweredIndex >= 0 ? lastAnsweredIndex : 0);
                        }

                        this.isAnswerRevealed = false;
                        this.feedbackMessage  = '';
                    }
                } catch(e) {}
                this.questionTimeRemaining = window.quizData.timePerQuestion;
            },

            async loadLeaderboard() {
                if (!window.quizData.showLeaderboard) return;
                try {
                    const r = await fetch(window.quizData.leaderboardTop5Url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': window.quizData.csrfToken } });
                    const data = await r.json();
                    this.leaderboard = data.success ? (data.leaderboard || []) : [];
                } catch(e) { this.leaderboard = []; }
            },

            initBgMusic() {
                const tracks = ['/sounds/bg_music_1.mp3', '/sounds/bg_music_2.mp3'];
                this.bgMusicAudio = new Audio(tracks[Math.floor(Math.random() * tracks.length)]);
                this.bgMusicAudio.loop = true;
                this.bgMusicAudio.volume = 0.2;
            },

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
