@extends('layouts.appSiswa')

@section('content')
<style>
    .quiz-hero {
        background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 60%, #60a5fa 100%);
        color: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(37, 99, 235, 0.3);
    }

    .feature-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .feature-card:hover {
        transform: translateY(-3px);
        border-color: #3b82f6;
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.12);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .badge-easy   { background: #10b981; color: white; }
    .badge-medium { background: #f59e0b; color: white; }
    .badge-hard   { background: #ef4444; color: white; }

    .progress-ring { transform: rotate(-90deg); }
    .progress-ring__circle { transition: stroke-dashoffset 0.5s ease; transform-origin: 50% 50%; }

    .leaderboard-item { transition: all 0.2s ease; }
    .leaderboard-item:hover { background-color: #eff6ff; transform: scale(1.01); }

    .pulse-animation { animation: pulse 2s infinite; }
    @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:.7; } }

    .floating-button {
        box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
        transition: all 0.3s ease;
    }
    .floating-button:hover {
        box-shadow: 0 15px 35px rgba(37, 99, 235, 0.4);
        transform: translateY(-2px);
    }
    .floating-button:disabled { box-shadow: none; transform: none; }

    .section-icon {
        width: 28px; height: 28px;
        background: #dbeafe;
        color: #2563eb;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .info-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.875rem;
    }
    .info-row:last-child { border-bottom: none; }

    .stat-tile {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 12px;
        padding: 14px;
        text-align: center;
    }

    /* Countdown overlay */
    #start-countdown {
        display: none;
        position: fixed; inset: 0;
        background: rgba(0,0,0,.9);
        z-index: 9999;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }
    #start-countdown.active { display: flex; }
    #cdn-number {
        font-size: 9rem; font-weight: 900; color: white; line-height: 1;
        text-shadow: 0 0 50px rgba(59,130,246,.8);
        animation: cdnPulse 1s ease infinite;
    }
    @keyframes cdnPulse { 0%,100%{transform:scale(1);} 50%{transform:scale(1.06);} }
    #cdn-label { font-size: 1.3rem; font-weight: 700; color: rgba(255,255,255,.8); text-align: center; }
    .cdn-bar { width: 220px; height: 6px; background: rgba(255,255,255,.2); border-radius: 999px; overflow: hidden; }
    .cdn-bar-fill { height: 100%; background: linear-gradient(90deg, #3B82F6, #60A5FA); border-radius: 999px; transition: width 1s linear; }

    /* Status indicators */
    .status-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .status-active { background: #22c55e; }
    .status-open   { background: #3b82f6; }
    .status-closed { background: #9ca3af; }

    /* Attempt status badge */
    .attempt-status {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
    }
    .attempt-submitted { background: #d1fae5; color: #065f46; }
    .attempt-timeout   { background: #fee2e2; color: #991b1b; }
    .attempt-progress  { background: #fef3c7; color: #78350f; }
</style>

{{-- Countdown overlay --}}
<div id="start-countdown">
    <div id="cdn-label">Quiz dimulai dalam</div>
    <div id="cdn-number">3</div>
    <div class="cdn-bar"><div class="cdn-bar-fill" id="cdn-fill" style="width:100%"></div></div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Alerts --}}
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            <span><strong>Error:</strong> {{ session('error') }}</span>
        </div>
    @endif
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Quiz Hero --}}
    <div class="quiz-hero p-7 mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex-1">
                <div class="flex flex-wrap items-center gap-2.5 mb-4">
                    <span class="badge {{ $quiz->difficulty_level == 'easy' ? 'badge-easy' : ($quiz->difficulty_level == 'medium' ? 'badge-medium' : 'badge-hard') }}">
                        {{ $quiz->difficulty_level == 'easy' ? 'Mudah' : ($quiz->difficulty_level == 'medium' ? 'Sedang' : 'Sulit') }}
                    </span>
                    <span class="badge bg-white/20 text-white">
                        {{ $quiz->quiz_mode === 'homework' ? 'Mandiri' : ($quiz->quiz_mode === 'guided' ? 'Terpandu' : 'Live') }}
                    </span>
                    <span class="badge bg-white/20 text-white">
                        {{ $quiz->questions_count }} Soal
                    </span>
                    @if($quiz->quiz_mode !== 'homework' && $quiz->is_quiz_started)
                        <span class="badge bg-green-500 pulse-animation flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-white inline-block"></span>
                            Sedang Berlangsung
                        </span>
                    @elseif($quiz->quiz_mode !== 'homework' && $quiz->is_room_open)
                        <span class="badge bg-white/30 text-white">Ruangan Terbuka</span>
                    @endif
                </div>

                <h1 class="text-3xl md:text-4xl font-bold mb-3">{{ $quiz->title }}</h1>

                <div class="flex flex-wrap items-center gap-5 text-white/90 text-sm">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $duration }} Menit
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                        {{ $quiz->subject->name_subject }}
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        {{ $quiz->class->name_class }}
                    </div>
                </div>
            </div>

            <div class="w-28 h-28 relative flex-shrink-0">
                <svg class="w-28 h-28 progress-ring" viewBox="0 0 100 100">
                    <circle class="text-white/20" stroke-width="8" stroke="currentColor" fill="transparent" r="42" cx="50" cy="50"/>
                    <circle class="text-white progress-ring__circle" stroke-width="8" stroke-linecap="round" stroke="currentColor" fill="transparent" r="42" cx="50" cy="50"
                            stroke-dasharray="264"
                            style="stroke-dashoffset: {{ 264 - ($quiz->questions_count / 50 * 264) }}"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center text-center">
                    <div>
                        <div class="text-3xl font-bold">{{ $quiz->questions_count }}</div>
                        <div class="text-xs opacity-75 mt-0.5">Soal</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Left Column --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Informasi Quiz --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-base font-bold text-gray-800 mb-5 flex items-center gap-2.5">
                    <span class="section-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    Informasi Quiz
                </h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
                    <div class="stat-tile">
                        <div class="text-xl font-bold text-blue-700">{{ $duration }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Menit</div>
                    </div>
                    <div class="stat-tile">
                        <div class="text-xl font-bold text-blue-700">{{ $timePerQuestion > 0 ? $timePerQuestion : '-' }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Detik / Soal</div>
                    </div>
                    <div class="stat-tile">
                        <div class="text-xl font-bold text-blue-700">{{ $quiz->questions_count }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Soal</div>
                    </div>
                    <div class="stat-tile">
                        <div class="text-xl font-bold text-blue-700">{{ $quiz->total_score ?? $quiz->questions->sum('score') }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Total Poin</div>
                    </div>
                </div>

                <div class="border-t pt-4 mb-4">
                    <h3 class="font-semibold text-gray-700 text-sm mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        Tujuan Quiz
                    </h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Uji pemahaman Anda tentang materi <strong>{{ $quiz->subject->name_subject }}</strong> melalui quiz interaktif ini. Jawab semua soal dengan tepat untuk mendapatkan nilai terbaik.</p>
                </div>

                <div class="border-t pt-4">
                    <h3 class="font-semibold text-gray-700 text-sm mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 12h6m-6 4h4"/></svg>
                        Instruksi Pengerjaan
                    </h3>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                            Pastikan koneksi internet stabil sebelum memulai
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                            Baca setiap soal dengan teliti sebelum menjawab
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                            Manfaatkan waktu yang tersedia dengan baik
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                            Submit jawaban sebelum waktu habis
                        </li>
                        @if($quiz->quiz_mode === 'homework')
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span><strong class="text-green-700">Mode mandiri:</strong> Dapat dikerjakan kapan saja tanpa menunggu guru membuka ruangan</span>
                        </li>
                        @elseif($quiz->quiz_mode === 'guided')
                        <li class="flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span><strong class="text-blue-700">Mode terpadu:</strong> Soal ditampilkan di layar guru — pilih jawaban di perangkat Anda saat guru memperlihatkan soal</span>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            {{-- Fitur Quiz --}}
            @if(count(array_filter($quizFeatures)) > 0)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-base font-bold text-gray-800 mb-5 flex items-center gap-2.5">
                    <span class="section-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    </span>
                    Fitur Quiz
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @if($quizFeatures['enable_music'])
                    <div class="feature-card bg-blue-50 border border-blue-100 p-4 rounded-xl">
                        <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                        </div>
                        <div class="font-semibold text-gray-800 text-sm">Musik Latar</div>
                        <div class="text-xs text-gray-500 mt-0.5">Suasana quiz lebih menyenangkan</div>
                    </div>
                    @endif
                    @if($quizFeatures['enable_memes'])
                    <div class="feature-card bg-green-50 border border-green-100 p-4 rounded-xl">
                        <div class="w-9 h-9 bg-green-100 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="font-semibold text-gray-800 text-sm">Meme</div>
                        <div class="text-xs text-gray-500 mt-0.5">Hiburan di sela-sela soal</div>
                    </div>
                    @endif
                    @if($quizFeatures['enable_powerups'])
                    <div class="feature-card bg-yellow-50 border border-yellow-100 p-4 rounded-xl col-span-2 md:col-span-3">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-800 text-sm">Power-ups Aktif</div>
                                <div class="text-xs text-gray-500 mt-0.5">3 powerup acak tersedia · Maks 1 per soal · Hilang setelah dipakai · 1 powerup baru tiap soal berikutnya</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            {{-- Supersonic --}}
                            <div style="background:#FEF3C7;border:1.5px solid #F59E0B;border-radius:12px;padding:10px 12px;display:flex;align-items:flex-start;gap:10px">
                                <div style="width:32px;height:32px;background:#F59E0B;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="fas fa-rocket" style="color:white;font-size:0.85rem"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.8rem;color:#92400E">Supersonic</div>
                                    <div style="font-size:0.7rem;color:#B45309;margin-top:1px">1.5× skor selama <strong>20 detik</strong></div>
                                </div>
                            </div>
                            {{-- Streak Booster --}}
                            <div style="background:#D1FAE5;border:1.5px solid #10B981;border-radius:12px;padding:10px 12px;display:flex;align-items:flex-start;gap:10px">
                                <div style="width:32px;height:32px;background:#10B981;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="fas fa-fire" style="color:white;font-size:0.85rem"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.8rem;color:#065F46">Streak +1</div>
                                    <div style="font-size:0.7rem;color:#059669;margin-top:1px">Tambah streak +1 sekarang</div>
                                </div>
                            </div>
                            {{-- Gift --}}
                            <div style="background:#FCE7F3;border:1.5px solid #EC4899;border-radius:12px;padding:10px 12px;display:flex;align-items:flex-start;gap:10px">
                                <div style="width:32px;height:32px;background:#EC4899;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="fas fa-gift" style="color:white;font-size:0.85rem"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.8rem;color:#831843">Gift</div>
                                    <div style="font-size:0.7rem;color:#BE185D;margin-top:1px">Kirim 800 poin ke pemain acak</div>
                                </div>
                            </div>
                            {{-- Double Jeopardy --}}
                            <div style="background:#FEE2E2;border:1.5px solid #EF4444;border-radius:12px;padding:10px 12px;display:flex;align-items:flex-start;gap:10px">
                                <div style="width:32px;height:32px;background:#EF4444;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="fas fa-exclamation-triangle" style="color:white;font-size:0.85rem"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.8rem;color:#991B1B">Dbl Jeopardy</div>
                                    <div style="font-size:0.7rem;color:#DC2626;margin-top:1px">Benar=2× poin, Salah=0 poin</div>
                                </div>
                            </div>
                            {{-- 2X --}}
                            <div style="background:#EDE9FE;border:1.5px solid #8B5CF6;border-radius:12px;padding:10px 12px;display:flex;align-items:flex-start;gap:10px">
                                <div style="width:32px;height:32px;background:#8B5CF6;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="fas fa-times" style="color:white;font-size:0.85rem"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.8rem;color:#4C1D95">2X</div>
                                    <div style="font-size:0.7rem;color:#7C3AED;margin-top:1px">2× poin soal berikutnya</div>
                                </div>
                            </div>
                            {{-- 50-50 --}}
                            <div style="background:#CFFAFE;border:1.5px solid #06B6D4;border-radius:12px;padding:10px 12px;display:flex;align-items:flex-start;gap:10px">
                                <div style="width:32px;height:32px;background:#06B6D4;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="fas fa-cut" style="color:white;font-size:0.85rem"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.8rem;color:#164E63">50-50</div>
                                    <div style="font-size:0.7rem;color:#0891B2;margin-top:1px">Hapus ½ pilihan yang salah</div>
                                </div>
                            </div>
                            {{-- Eraser --}}
                            <div style="background:#E0E7FF;border:1.5px solid #6366F1;border-radius:12px;padding:10px 12px;display:flex;align-items:flex-start;gap:10px">
                                <div style="width:32px;height:32px;background:#6366F1;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="fas fa-eraser" style="color:white;font-size:0.85rem"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.8rem;color:#312E81">Eraser</div>
                                    <div style="font-size:0.7rem;color:#4F46E5;margin-top:1px">Hapus 1 pilihan pasti salah</div>
                                </div>
                            </div>
                            {{-- Immunity --}}
                            <div style="background:#CCFBF1;border:1.5px solid #14B8A6;border-radius:12px;padding:10px 12px;display:flex;align-items:flex-start;gap:10px">
                                <div style="width:32px;height:32px;background:#14B8A6;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="fas fa-shield-alt" style="color:white;font-size:0.85rem"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.8rem;color:#134E4A">Immunity</div>
                                    <div style="font-size:0.7rem;color:#0D9488;margin-top:1px">Jawaban salah pertama dimaafkan</div>
                                </div>
                            </div>
                            {{-- Time Freeze --}}
                            <div style="background:#DBEAFE;border:1.5px solid #3B82F6;border-radius:12px;padding:10px 12px;display:flex;align-items:flex-start;gap:10px">
                                <div style="width:32px;height:32px;background:#3B82F6;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="fas fa-snowflake" style="color:white;font-size:0.85rem"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.8rem;color:#1E3A8A">Time Freeze</div>
                                    <div style="font-size:0.7rem;color:#2563EB;margin-top:1px">Hentikan timer soal ini</div>
                                </div>
                            </div>
                            {{-- Power Play --}}
                            <div style="background:#FFEDD5;border:1.5px solid #F97316;border-radius:12px;padding:10px 12px;display:flex;align-items:flex-start;gap:10px">
                                <div style="width:32px;height:32px;background:#F97316;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="fas fa-users" style="color:white;font-size:0.85rem"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.8rem;color:#7C2D12">Power Play</div>
                                    <div style="font-size:0.7rem;color:#EA580C;margin-top:1px">Semua +50% skor selama <strong>20 detik</strong></div>
                                </div>
                            </div>
                            {{-- Streak Saver --}}
                            <div style="background:#DCFCE7;border:1.5px solid #22C55E;border-radius:12px;padding:10px 12px;display:flex;align-items:flex-start;gap:10px">
                                <div style="width:32px;height:32px;background:#22C55E;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="fas fa-life-ring" style="color:white;font-size:0.85rem"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.8rem;color:#14532D">Streak Saver</div>
                                    <div style="font-size:0.7rem;color:#16A34A;margin-top:1px">Streak tidak hilang jika salah</div>
                                </div>
                            </div>
                            {{-- Glitch --}}
                            <div style="background:#F3E8FF;border:1.5px solid #A855F7;border-radius:12px;padding:10px 12px;display:flex;align-items:flex-start;gap:10px">
                                <div style="width:32px;height:32px;background:#A855F7;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                                    <i class="fas fa-bug" style="color:white;font-size:0.85rem"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.8rem;color:#4A1D96">Glitch</div>
                                    <div style="font-size:0.7rem;color:#9333EA;margin-top:1px">Layar lawan glitch <strong>10 detik</strong></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if($quizFeatures['instant_feedback'])
                    <div class="feature-card bg-amber-50 border border-amber-100 p-4 rounded-xl">
                        <div class="w-9 h-9 bg-amber-100 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="font-semibold text-gray-800 text-sm">Feedback Instan</div>
                        <div class="text-xs text-gray-500 mt-0.5">Koreksi jawaban langsung</div>
                    </div>
                    @endif
                    @if($quizFeatures['streak_bonus'])
                    <div class="feature-card bg-red-50 border border-red-100 p-4 rounded-xl">
                        <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/></svg>
                        </div>
                        <div class="font-semibold text-gray-800 text-sm">Streak Bonus</div>
                        <div class="text-xs text-gray-500 mt-0.5">Poin ekstra jawaban beruntun</div>
                    </div>
                    @endif
                    @if($quizFeatures['time_bonus'])
                    <div class="feature-card bg-indigo-50 border border-indigo-100 p-4 rounded-xl">
                        <div class="w-9 h-9 bg-indigo-100 rounded-lg flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="font-semibold text-gray-800 text-sm">Bonus Waktu</div>
                        <div class="text-xs text-gray-500 mt-0.5">Poin tambahan untuk jawaban cepat</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Riwayat Percobaan --}}
            @if($lastAttempt || $attemptCount > 0)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-base font-bold text-gray-800 mb-5 flex items-center gap-2.5">
                    <span class="section-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </span>
                    Riwayat Percobaan
                </h2>

                @if($lastAttempt)
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-4">
                    <div class="flex justify-between items-center mb-3">
                        <div class="font-semibold text-gray-700 text-sm">Percobaan Terakhir</div>
                        <div class="text-xs text-gray-400">{{ $lastAttempt->created_at->format('d M Y, H:i') }}</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs text-gray-500 mb-1">Status</div>
                            @if($lastAttempt->status == 'submitted')
                                <span class="attempt-status attempt-submitted">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    Selesai
                                </span>
                            @elseif($lastAttempt->status == 'timeout')
                                <span class="attempt-status attempt-timeout">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Timeout
                                </span>
                            @else
                                <span class="attempt-status attempt-progress">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                    Dalam Proses
                                </span>
                            @endif
                        </div>
                        @if(in_array($lastAttempt->status, ['submitted', 'timeout']))
                        <div class="text-right">
                            <div class="text-xs text-gray-500 mb-1">Nilai</div>
                            <div class="text-lg font-bold text-blue-600">{{ number_format($lastAttempt->final_score, 1) }}<span class="text-sm font-normal text-gray-400">/100</span></div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="stat-tile">
                        <div class="text-xl font-bold text-blue-600">{{ $attemptCount }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Total Percobaan</div>
                    </div>
                    <div class="stat-tile" style="background:#f0fdf4;border-color:#bbf7d0">
                        <div class="text-xl font-bold text-green-600">
                            @if($quiz->limit_attempts > 0)
                                {{ max(0, $quiz->limit_attempts - $attemptCount) }}
                            @else
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">Sisa Percobaan</div>
                    </div>
                    <div class="stat-tile">
                        <div class="text-xl font-bold {{ $canRetake ? 'text-green-600' : 'text-gray-400' }}">
                            @if($canRetake)
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">Bisa Mengulang</div>
                    </div>
                    <div class="stat-tile" style="background:#fffbeb;border-color:#fde68a">
                        <div class="text-xl font-bold text-amber-600">{{ $quiz->min_pass_grade ?? 0 }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">Nilai Lulus</div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">

            {{-- Action Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sticky top-6">
                <h2 class="text-base font-bold text-gray-800 mb-5 flex items-center gap-2.5">
                    <span class="section-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    Mulai Quiz
                </h2>

                {{-- MODE MANDIRI --}}
                @if($quiz->quiz_mode === 'homework')
                    <div class="flex items-center gap-3 p-3 bg-blue-50 border border-blue-200 rounded-lg mb-5">
                        <div class="status-dot status-open flex-shrink-0"></div>
                        <div>
                            <div class="font-semibold text-blue-800 text-sm">Mode Mandiri</div>
                            <div class="text-xs text-blue-600 mt-0.5">Kerjakan kapan saja tanpa menunggu ruangan</div>
                        </div>
                    </div>

                    @if($lastAttempt && $lastAttempt->status === 'in_progress')
                        <button onclick="startCountdown()" class="w-full py-3.5 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold rounded-xl floating-button flex items-center justify-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Lanjutkan Quiz
                        </button>
                    @elseif($canRetake || !$lastAttempt)
                        <button onclick="startCountdown()" class="w-full py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-xl floating-button flex items-center justify-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Mulai Quiz Sekarang
                        </button>
                    @else
                        @if($lastAttempt)
                        <a href="{{ route('quiz.result', [$quiz->id, $lastAttempt->id]) }}"
                            class="w-full py-3.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold rounded-xl floating-button flex items-center justify-center gap-2 text-sm mb-3">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Lihat Hasil
                        </a>
                        @endif
                        <button disabled class="w-full py-3 bg-gray-100 text-gray-400 font-semibold rounded-xl cursor-not-allowed text-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            Batas percobaan telah habis
                        </button>
                    @endif

                {{-- MODE LIVE / GUIDED --}}
                @else
                    <div class="mb-5">
                        @if($quiz->is_quiz_started)
                        <div class="flex items-center gap-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="status-dot status-active pulse-animation flex-shrink-0"></div>
                            <div>
                                <div class="font-semibold text-green-800 text-sm">Quiz Sedang Berlangsung</div>
                                <div class="text-xs text-green-600 mt-0.5">
                                    @if($quiz->quiz_mode === 'guided')
                                        Ikuti soal yang ditampilkan guru di layar
                                    @else
                                        Bergabung sekarang sebelum terlambat
                                    @endif
                                </div>
                            </div>
                        </div>
                        @elseif($quiz->is_room_open)
                        <div class="flex items-center gap-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="status-dot status-open flex-shrink-0"></div>
                            <div>
                                <div class="font-semibold text-blue-800 text-sm">Ruangan Terbuka</div>
                                <div class="text-xs text-blue-600 mt-0.5">
                                    @if($quiz->quiz_mode === 'guided')
                                        Tunggu guru menampilkan soal di layar
                                    @else
                                        Menunggu guru memulai quiz
                                    @endif
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-lg">
                            <div class="status-dot status-closed flex-shrink-0"></div>
                            <div>
                                <div class="font-semibold text-gray-700 text-sm">Ruangan Tertutup</div>
                                <div class="text-xs text-gray-500 mt-0.5">Menunggu guru membuka ruangan</div>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($quiz->is_quiz_started)
                        @if($lastAttempt && $lastAttempt->status === 'in_progress')
                        <a href="{{ route('quiz.play', $quiz->id) }}" class="w-full py-3.5 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold rounded-xl floating-button flex items-center justify-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Lanjutkan Quiz
                        </a>
                        @else
                        <form id="startQuizForm" action="{{ route('quiz.start', $quiz->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold rounded-xl floating-button flex items-center justify-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Mulai Quiz
                            </button>
                        </form>
                        @endif
                    @elseif($quiz->is_room_open)
                        <a href="{{ route('quiz.room', $quiz->id) }}" class="w-full py-3.5 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold rounded-xl floating-button flex items-center justify-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Masuk Ruangan
                        </a>
                    @else
                        <button disabled class="w-full py-3.5 bg-gray-100 text-gray-400 font-semibold rounded-xl cursor-not-allowed text-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Menunggu Ruangan Dibuka
                        </button>
                    @endif
                @endif

                {{-- Info tambahan --}}
                <div class="mt-5 pt-5 border-t border-gray-100 space-y-0">
                    <div class="info-row">
                        <span class="text-gray-500">Mode Quiz</span>
                        <span class="font-semibold text-gray-800">{{ $quiz->quiz_mode === 'homework' ? 'Mandiri' : ($quiz->quiz_mode === 'guided' ? 'Terpandu' : 'Live') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="text-gray-500">Acak Soal</span>
                        <span class="font-semibold {{ $quiz->shuffle_question ? 'text-green-600' : 'text-gray-400' }}">{{ $quiz->shuffle_question ? 'Aktif' : 'Tidak' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="text-gray-500">Acak Jawaban</span>
                        <span class="font-semibold {{ $quiz->shuffle_answer ? 'text-green-600' : 'text-gray-400' }}">{{ $quiz->shuffle_answer ? 'Aktif' : 'Tidak' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="text-gray-500">Tampilkan Nilai</span>
                        <span class="font-semibold {{ $quiz->show_score ? 'text-green-600' : 'text-gray-400' }}">{{ $quiz->show_score ? 'Ya' : 'Tidak' }}</span>
                    </div>
                </div>
            </div>

            {{-- Leaderboard --}}
            @if($showLeaderboard && $leaderboard && count($leaderboard) > 0)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2.5">
                    <span class="section-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    </span>
                    Peringkat Teratas
                </h2>
                <div class="space-y-2">
                    @foreach($leaderboard->take(3) as $index => $entry)
                    <div class="leaderboard-item flex items-center gap-3 p-3 rounded-lg {{ $index == 0 ? 'bg-amber-50 border border-amber-100' : ($index == 1 ? 'bg-slate-50 border border-slate-100' : 'bg-orange-50 border border-orange-100') }}">
                        <div class="flex-shrink-0 w-7 h-7 flex items-center justify-center rounded-full text-xs font-bold
                            {{ $index == 0 ? 'bg-amber-200 text-amber-800' : ($index == 1 ? 'bg-gray-200 text-gray-700' : 'bg-orange-200 text-orange-800') }}">
                            {{ $index + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-800 text-sm truncate">{{ $entry['student_name'] }}</div>
                        </div>
                        <div class="font-bold text-blue-600 text-sm flex-shrink-0">{{ number_format($entry['score'], 1) }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Statistik Ruangan (live/guided) --}}
            @if($quiz->quiz_mode !== 'homework')
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2.5">
                    <span class="section-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </span>
                    Statistik Ruangan
                </h2>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1.5">
                            <span class="text-gray-500">Peserta Bergabung</span>
                            <span class="font-semibold text-gray-800">{{ $stats['joined'] }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500 rounded-full" style="width: {{ $stats['total'] > 0 ? min(100, ($stats['joined'] / $stats['total']) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1.5">
                            <span class="text-gray-500">Peserta Selesai</span>
                            <span class="font-semibold text-gray-800">{{ $stats['submitted'] }}</span>
                        </div>
                        <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500 rounded-full" style="width: {{ $stats['total'] > 0 ? min(100, ($stats['submitted'] / $stats['total']) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                    <div class="pt-1">
                        <a href="{{ route('quiz.room', $quiz->id) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Lihat Ruangan Quiz
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- In-progress warning modal (live/guided mode) --}}
    @if($lastAttempt && $lastAttempt->status === 'in_progress' && $quiz->quiz_mode !== 'homework')
    <div x-data="{ open: true }" x-show="open" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-11 h-11 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-gray-800">Quiz Belum Diselesaikan</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Anda memiliki sesi yang masih berjalan.</p>
                </div>
            </div>
            <div class="bg-amber-50 border border-amber-200 p-3 rounded-lg mb-5 text-sm text-amber-700 flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Lanjutkan quiz Anda yang belum selesai atau mulai yang baru.
            </div>
            <div class="flex justify-end gap-3">
                <button @click="open = false" class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 text-sm font-medium">Tutup</button>
                <a href="{{ route('quiz.play', $quiz->id) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold">Lanjutkan Quiz</a>
            </div>
        </div>
    </div>
    @endif

</div>

<script>
    const PLAY_URL = '{{ route('quiz.play', $quiz->id) }}';

    function startCountdown() {
        const overlay = document.getElementById('start-countdown');
        const numEl   = document.getElementById('cdn-number');
        const fill    = document.getElementById('cdn-fill');
        overlay.classList.add('active');
        let n = 3;
        numEl.textContent = n;
        fill.style.width = '100%';

        const tick = setInterval(() => {
            n--;
            if (n <= 0) {
                clearInterval(tick);
                numEl.textContent = 'GO!';
                fill.style.width = '0%';
                setTimeout(() => { window.location.href = PLAY_URL; }, 600);
            } else {
                numEl.textContent = n;
                fill.style.width = (n / 3 * 100) + '%';
            }
        }, 1000);
    }

    document.getElementById('startQuizForm')?.addEventListener('submit', function(e) {
        if (!confirm('Mulai quiz sekarang? Pastikan Anda sudah siap.')) {
            e.preventDefault(); return false;
        }
        const btn = this.querySelector('button[type="submit"]');
        if (btn) {
            btn.innerHTML = '<svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Memulai...';
            btn.disabled = true;
        }
    });

    @if($quiz->quiz_mode !== 'homework' && $quiz->is_room_open && !$quiz->is_quiz_started)
    setInterval(() => {
        fetch('{{ route("quiz.room.status", $quiz->id) }}')
            .then(r => r.json())
            .then(d => { if (d.success && d.is_quiz_started) location.reload(); })
            .catch(() => {});
    }, 5000);
    @endif

    @if($quiz->quiz_mode === 'homework')
    if (window.innerWidth < 1024) {
        setTimeout(() => {
            document.querySelector('.sticky')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 500);
    }
    @endif
</script>

@endsection
