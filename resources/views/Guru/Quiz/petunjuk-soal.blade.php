<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrol Soal – {{ $quiz->title }}</title>
    <link rel="icon" type="image/webp" href="{{ asset('image/logo.webp') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }

        @keyframes live-pulse {
            0%,100% { opacity:1; box-shadow:0 0 0 0 rgba(59,130,246,.4) }
            50%      { opacity:.7; box-shadow:0 0 0 6px rgba(59,130,246,0) }
        }
        @keyframes spin { to { transform:rotate(360deg) } }
        @keyframes answer-reveal {
            from { opacity:0; transform:translateY(8px) scale(0.97) }
            to   { opacity:1; transform:translateY(0) scale(1) }
        }
        @keyframes timer-pulse {
            0%,100% { transform:scale(1) }
            50%     { transform:scale(1.1) }
        }
        @keyframes slide-in-up {
            from { opacity:0; transform:translateY(16px) }
            to   { opacity:1; transform:translateY(0) }
        }

        .answer-reveal  { animation: answer-reveal  .4s  cubic-bezier(.16,1,.3,1) both; }
        .timer-danger   { animation: timer-pulse     .6s  ease-in-out infinite; }
        .slide-up       { animation: slide-in-up     .35s cubic-bezier(.16,1,.3,1) both; }
        .live-pulse-dot { animation: live-pulse       1.6s ease-in-out infinite; }

        /* Desktop scene — complex layout needs raw CSS */
        @media (min-width:1024px) {
            html,body { height:100%; overflow:hidden; }
            body {
                background: url('{{ asset('image/petunjuk-soal.webp') }}') center top / cover no-repeat;
                background-color: #0d1b3e;
            }
            .mobile-main  { display:none !important; }
            .desktop-scene {
                position:fixed; top:52px; left:0; right:0; bottom:0; z-index:20; pointer-events:none;
            }
            .scene-frame {
                position:absolute; top:50%; left:50%;
                transform:translate(-50%,-50%);
                width:min(100vw, calc((100vh - 52px) * 1408 / 768));
                height:min(calc(100vh - 52px), calc(100vw * 768 / 1408));
            }
            /*
             * ┌─────────────────────────────────────────────────────┐
             * │  PANDUAN UBAH POSISI BOARD OVERLAY                  │
             * │  left   → geser kiri/kanan (% dari lebar frame)     │
             * │  top    → geser atas/bawah (% dari tinggi frame)    │
             * │  width  → lebar kotak soal                          │
             * │  height → tinggi kotak soal                         │
             * └─────────────────────────────────────────────────────┘
             */

            /* === MODE NORMAL (bukan fullscreen) === */
            .board-overlay {
                position:absolute; left:20.5%; top:12.5%; width:60.5%; height:67%;
                pointer-events:all;
                transform:perspective(2400px) rotateY(-0.6deg) rotateX(0.2deg);
                transform-origin:50% 50%; overflow:hidden; border-radius:3px;
                transition: left .3s, top .3s, width .3s, height .3s;
            }

            /* === MODE FULLSCREEN === */
            body.is-fullscreen .board-overlay {
                left:24%; top:10%; width:53%; height:60%;
            }
            .board-overlay::before {
                content:''; position:absolute; inset:0;
                background:rgba(248,252,255,.90);
                background-image:linear-gradient(135deg,rgba(255,255,255,.6) 0%,rgba(235,247,255,.86) 50%,rgba(244,251,255,.90) 100%);
                box-shadow:inset 0 0 50px rgba(180,215,255,.12),inset 3px 3px 20px rgba(255,255,255,.55),inset -2px -2px 10px rgba(180,210,240,.1);
                border-radius:3px;
            }
            .board-content {
                position:relative; z-index:2; height:100%; overflow-y:auto;
                padding:2% 3% 2%; scrollbar-width:none;
            }
            .board-content::-webkit-scrollbar { display:none; }
            .board-content .question-card { background:transparent; border:none; border-radius:0; box-shadow:none; }
            .board-content .question-header {
                background:rgba(29,78,216,.83); border-radius:10px;
                padding:13px 17px 11px; margin-bottom:13px; backdrop-filter:blur(6px);
            }
            .board-content .question-header::after { display:none; }
            .board-content .choice-btn {
                background:rgba(248,250,255,.80); border-color:rgba(219,234,254,.72);
                backdrop-filter:blur(3px); padding:9px 13px; border-radius:10px; gap:10px;
            }
            .board-content .choice-btn.correct { background:rgba(240,253,244,.86); }
            .board-content .choice-label { width:32px; height:32px; font-size:11px; }
            .board-content .timer-badge {
                background:rgba(239,246,255,.80); backdrop-filter:blur(8px);
                border-radius:10px; padding:7px 13px; margin-bottom:11px;
            }
            .board-content .timer-badge.answered { background:rgba(240,253,244,.86); }
            .board-content .timer-badge.danger   { background:rgba(254,242,242,.86); }
            .board-content .timer-badge.warning  { background:rgba(255,251,235,.86); }
            .board-nav {
                display:flex; align-items:center; justify-content:space-between; gap:10px;
                background:rgba(255,255,255,.65); backdrop-filter:blur(10px);
                border-radius:10px; padding:8px 12px;
                border:1px solid rgba(219,234,254,.5); margin-top:10px;
            }
            .board-nav .nav-btn-secondary { background:rgba(255,255,255,.80); font-size:12px; padding:6px 13px; }
            .board-nav .nav-btn-primary   { font-size:12px; padding:6px 13px; }
            .board-nav .dot-nav           { width:26px; height:26px; font-size:10px; }
            .board-content .kbd { background:rgba(241,245,249,.80); }
            .top-nav {
                background:rgba(10,20,55,.82) !important;
                backdrop-filter:blur(24px); -webkit-backdrop-filter:blur(24px);
                border-bottom:1px solid rgba(59,130,246,.2) !important;
            }
            .top-nav .text-slate-800 { color:#dde8f8 !important; }
            .top-nav .text-blue-600  { color:#93c5fd !important; }
            .top-nav .text-blue-500  { color:#7cb8ff !important; }
            .top-nav .bg-blue-50     { background:rgba(59,130,246,.15) !important; }
            .top-nav .border-blue-200 { border-color:rgba(59,130,246,.25) !important; }
            .stat-chip { background:rgba(59,130,246,.15) !important; border-color:rgba(59,130,246,.25) !important; }
            .stat-chip .text-slate-800 { color:#dde8f8; }
            .stat-chip .text-blue-500  { color:#93c5fd; }
        }
        @media (max-width:1023px) {
            .desktop-scene { display:none; }
            .mobile-main   { display:flex; }
        }

        /* Shared component styles */
        .question-header::after {
            content:''; position:absolute; bottom:0; left:0; right:0;
            height:1px; background:rgba(255,255,255,.15);
        }
        .timer-ring { transform:rotate(-90deg); }
        .timer-ring-track    { fill:none; stroke:#dbeafe; stroke-width:6; }
        .timer-ring-progress { fill:none; stroke-width:6; stroke-linecap:round; transition:stroke-dashoffset 1s linear, stroke .3s; }
    </style>
</head>

<body class="text-slate-900 min-h-screen overflow-x-hidden bg-[#f0f5ff]">

    <div x-data="guidedControl()" x-init="init()" class="min-h-screen flex flex-col" x-cloak>

        {{-- TOP NAV --}}
        <nav class="top-nav bg-white border-b border-blue-100 shadow-sm sticky top-0 z-50" style="height:52px;">
            <div class="max-w-5xl mx-auto px-5 h-full flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex items-center gap-1.5 flex-shrink-0">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 live-pulse-dot"></span>
                        <span class="text-[10px] font-black tracking-widest uppercase text-blue-600">LIVE</span>
                    </div>
                    <div class="w-px h-5 bg-blue-100"></div>
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <div class="min-w-0">
                        <p class="text-[13px] font-bold text-slate-800 truncate max-w-[180px] sm:max-w-xs">{{ $quiz->title }}</p>
                        <p class="text-[10px] text-blue-500 font-semibold">Kontrol Soal Terpadu</p>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-2">
                    <span class="text-xs font-bold text-blue-600"
                        x-text="(currentIndex + 1) + ' / ' + totalQ">{{ ($quiz->guided_current_index ?? 0) + 1 }} / {{ $totalQuestions }}</span>
                    <div class="w-32 h-2 bg-blue-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full transition-all duration-500"
                            :style="'width:' + ((currentIndex + 1) / totalQ * 100) + '%'"></div>
                    </div>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0">
                    <div class="hidden sm:flex items-center gap-3">
                        <div class="stat-chip bg-blue-50 border border-blue-200 rounded-xl px-3.5 py-2 text-center">
                            <div class="text-base font-black text-slate-800 leading-none" x-text="stats.joined">{{ $stats['joined'] ?? 0 }}</div>
                            <div class="text-[9px] text-blue-500 font-bold mt-0.5">Peserta</div>
                        </div>
                    </div>
                    <button @click="toggleFullscreen()"
                        class="flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg px-3 py-2"
                        :title="isFullscreen ? 'Keluar Fullscreen' : 'Fullscreen'">
                        {{-- Icon Enter Fullscreen --}}
                        <svg x-show="!isFullscreen" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 8V6a2 2 0 012-2h2M4 16v2a2 2 0 002 2h2M16 4h2a2 2 0 012 2v2M16 20h2a2 2 0 002-2v-2" />
                        </svg>
                        {{-- Icon Exit Fullscreen --}}
                        <svg x-show="isFullscreen" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 9V5m0 4H5m0 0l4-4M15 9h4m-4 0V5m0 4l4-4M9 15v4m0-4H5m4 0l-4 4M15 15h4m-4 0v4m4-4l-4 4" />
                        </svg>
                        <span x-text="isFullscreen ? 'Exit' : 'Fullscreen'" class="hidden sm:inline"></span>
                    </button>
                    <a href="{{ route('guru.quiz.preview', $quiz->id) }}"
                        class="flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg px-3 py-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>
        </nav>

        {{-- DESKTOP: Overlay on whiteboard --}}
        <div class="desktop-scene">
            <div class="scene-frame">
                <div class="board-overlay">
                    <div class="board-content">

                        <div x-show="timePerQuestion > 0">
                            {{-- Timer running --}}
                            <div x-show="!showAnswer && timeLeft !== null"
                                class="timer-badge slide-up flex items-center justify-between bg-blue-50 border border-blue-200 rounded-xl px-4 py-2.5 mb-2"
                                :class="timeLeft !== null && timeLeft <= 5 ? 'danger !bg-red-50 !border-red-300' : timeLeft !== null && timeLeft <= 10 ? 'warning !bg-amber-50 !border-amber-300' : ''">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4"
                                        :class="timeLeft <= 5 ? 'text-red-500' : timeLeft <= 10 ? 'text-amber-500' : 'text-blue-500'"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-sm font-semibold"
                                        :class="timeLeft <= 5 ? 'text-red-600' : timeLeft <= 10 ? 'text-amber-600' : 'text-slate-600'">Waktu Per Soal</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-24 h-1.5 bg-white rounded-full overflow-hidden border border-slate-200">
                                        <div class="h-full rounded-full transition-all duration-1000"
                                            :class="timeLeft <= 5 ? 'bg-red-500' : timeLeft <= 10 ? 'bg-amber-400' : 'bg-blue-500'"
                                            :style="'width:' + (timePerQuestion > 0 ? Math.max(0, timeLeft / timePerQuestion * 100) : 100) + '%'"></div>
                                    </div>
                                    <span class="text-xl font-black w-8 text-right"
                                        :class="timeLeft <= 5 ? 'text-red-600 timer-danger' : timeLeft <= 10 ? 'text-amber-600' : 'text-blue-700'"
                                        x-text="timeLeft !== null ? timeLeft : '—'"></span>
                                    <span class="text-[10px] text-slate-400 font-bold">dtk</span>
                                </div>
                            </div>
                            {{-- Answer revealed --}}
                            <div x-show="showAnswer"
                                class="timer-badge answered answer-reveal flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-4 py-2.5 mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-green-800">Jawaban Benar Ditampilkan</p>
                                        <p class="text-[10px] text-green-600">
                                            <template x-if="timePerQuestion > 0"><span>Auto-next: <span class="font-black" x-text="autoNextIn"></span>s</span></template>
                                            <template x-if="timePerQuestion <= 0"><span>Klik "Berikutnya" untuk lanjut</span></template>
                                        </p>
                                    </div>
                                </div>
                                <button @click="navigate('next')" :disabled="currentIndex >= totalQ - 1 || isNavigating"
                                    class="nav-btn nav-btn-primary flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold disabled:opacity-35 cursor-pointer"
                                    style="padding:5px 11px;font-size:11px;">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    Berikutnya
                                </button>
                            </div>
                        </div>

                        {{-- Question Card --}}
                        <div class="question-card bg-white border border-blue-100 rounded-[20px] shadow-md overflow-hidden transition-all duration-200"
                            :class="isNavigating ? 'opacity-60 scale-[0.99]' : ''">
                            <div class="question-header relative bg-gradient-to-br from-blue-700 via-blue-600 to-blue-400 px-6 py-5">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[9px] font-black tracking-widest uppercase text-blue-200"
                                        x-text="'Soal ' + (currentIndex + 1) + ' dari ' + totalQ">Soal 1 dari {{ $totalQuestions }}</span>
                                    <span class="text-[10px] font-black text-white bg-white/20 rounded-full px-3 py-0.5"
                                        x-text="question ? question.score + ' Poin' : '—'">—</span>
                                </div>
                                <h2 class="text-[0.9rem] font-bold text-white leading-snug"
                                    x-text="question ? question.question : 'Memuat soal...'">Memuat soal...</h2>
                            </div>

                            <div class="p-3 flex flex-col gap-2">
                                <div x-show="!question" class="py-6 text-center">
                                    <div class="w-6 h-6 border-4 border-blue-100 border-t-blue-500 rounded-full mx-auto"
                                        style="animation:spin 0.8s linear infinite;"></div>
                                    <p class="text-slate-400 text-xs mt-2">Memuat...</p>
                                </div>
                                <template x-if="question && question.type === 'PG'">
                                    <div class="flex flex-col gap-1.5">
                                        <template x-for="(choice, idx) in question.choices" :key="choice.id">
                                            <div class="choice-btn flex items-center gap-3.5 px-4 py-3.5 rounded-2xl border border-blue-100 bg-[#f8faff] transition-all duration-300 cursor-default"
                                                :class="showAnswer && choice.is_correct ? 'correct !border-green-400 !bg-green-50 answer-reveal' : showAnswer && !choice.is_correct ? 'wrong !border-slate-200 !bg-slate-50 opacity-50' : ''">
                                                <div class="choice-label w-10 h-10 rounded-full flex items-center justify-center text-sm font-black flex-shrink-0 text-white transition-colors"
                                                    :class="showAnswer && choice.is_correct ? '!bg-green-500' : showAnswer && !choice.is_correct ? '!bg-slate-300' : 'bg-blue-700'"
                                                    x-text="['A','B','C','D','E','F'][idx] || (idx+1)"></div>
                                                <span class="flex-1 font-semibold text-[12px] text-slate-700"
                                                    :class="showAnswer && choice.is_correct ? '!font-bold !text-green-800' : showAnswer && !choice.is_correct ? '!text-slate-400' : ''"
                                                    x-text="choice.text"></span>
                                                <div x-show="showAnswer && choice.is_correct"
                                                    class="answer-reveal w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="question && question.type === 'IS'">
                                    <div class="rounded-xl border border-blue-200 bg-blue-50/80 px-4 py-3 flex items-center gap-3">
                                        <svg class="w-4 h-4 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <div>
                                            <p class="text-xs font-bold text-blue-800">Soal Isian Singkat</p>
                                            <p class="text-[10px] text-blue-500">Siswa mengetik jawaban di perangkat.</p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Board Nav --}}
                        <div class="board-nav">
                            <button @click="navigate('prev')" :disabled="currentIndex === 0 || isNavigating"
                                class="nav-btn nav-btn-secondary flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm border border-blue-100 bg-white text-blue-900 hover:bg-blue-50 disabled:opacity-35 cursor-pointer transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Sebelumnya
                            </button>
                            <div class="flex flex-wrap gap-1 justify-center max-w-[160px]">
                                <template x-for="i in totalQ" :key="i">
                                    <button @click="navigate('goto', i - 1)"
                                        class="dot-nav w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-black border-2 cursor-pointer transition-all"
                                        :class="(i - 1) === currentIndex ? 'active bg-blue-700 border-blue-700 text-white' : 'inactive bg-white border-blue-100 text-slate-400 hover:border-blue-400 hover:text-blue-700'"
                                        x-text="i"></button>
                                </template>
                            </div>
                            <button @click="navigate('next')" :disabled="currentIndex >= totalQ - 1 || isNavigating"
                                class="nav-btn nav-btn-primary flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-blue-700 hover:bg-blue-800 text-white disabled:opacity-35 cursor-pointer transition-all">
                                <span x-text="currentIndex === totalQ - 1 ? 'Terakhir' : 'Berikutnya'">Berikutnya</span>
                                <svg x-show="currentIndex < totalQ - 1" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-center text-[10px] text-slate-400 mt-1.5">
                            <kbd class="kbd bg-slate-100 border border-slate-200 rounded px-1.5 py-0.5 text-[11px] font-mono text-slate-500">←</kbd>
                            Sebelumnya &nbsp;
                            <kbd class="kbd bg-slate-100 border border-slate-200 rounded px-1.5 py-0.5 text-[11px] font-mono text-slate-500">→</kbd>
                            Berikutnya
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- MOBILE: normal layout --}}
        <div class="mobile-main flex-1 max-w-5xl mx-auto w-full px-4 py-6 flex flex-col gap-5">

            {{-- Timer --}}
            <div x-show="timePerQuestion > 0">
                <div x-show="!showAnswer && timeLeft !== null"
                    class="slide-up flex items-center justify-between bg-blue-50 border border-blue-200 rounded-xl px-4 py-2.5 transition-all duration-300"
                    :class="timeLeft !== null && timeLeft <= 5 ? '!bg-red-50 !border-red-300' : timeLeft !== null && timeLeft <= 10 ? '!bg-amber-50 !border-amber-300' : ''">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-blue-500"
                            :class="timeLeft <= 5 ? '!text-red-500' : timeLeft <= 10 ? '!text-amber-500' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-semibold text-slate-600">Waktu Per Soal</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-36 h-2 bg-white rounded-full overflow-hidden border border-slate-200">
                            <div class="h-full rounded-full transition-all duration-1000"
                                :class="timeLeft <= 5 ? 'bg-red-500' : timeLeft <= 10 ? 'bg-amber-400' : 'bg-blue-500'"
                                :style="'width:' + (timePerQuestion > 0 ? Math.max(0, timeLeft / timePerQuestion * 100) : 100) + '%'"></div>
                        </div>
                        <span class="text-2xl font-black w-10 text-right"
                            :class="timeLeft <= 5 ? 'text-red-600 timer-danger' : timeLeft <= 10 ? 'text-amber-600' : 'text-blue-700'"
                            x-text="timeLeft !== null ? timeLeft : '—'"></span>
                        <span class="text-xs text-slate-400 font-bold">dtk</span>
                    </div>
                </div>
                <div x-show="showAnswer"
                    class="answer-reveal flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-4 py-2.5">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-green-800">Jawaban Benar Ditampilkan</p>
                            <p class="text-xs text-green-600">
                                <template x-if="timePerQuestion > 0 && currentIndex < totalQ - 1"><span>Auto-next: <span class="font-black" x-text="autoNextIn"></span>s</span></template>
                                <template x-if="timePerQuestion > 0 && currentIndex >= totalQ - 1"><span>Selesaikan quiz dalam: <span class="font-black" x-text="autoNextIn"></span>s</span></template>
                                <template x-if="timePerQuestion <= 0 && currentIndex < totalQ - 1"><span>Klik "Soal Berikutnya"</span></template>
                                <template x-if="timePerQuestion <= 0 && currentIndex >= totalQ - 1"><span>Klik "Selesaikan Quiz"</span></template>
                            </p>
                        </div>
                    </div>

                    {{-- Soal bukan terakhir → Soal Berikutnya --}}
                    <button x-show="currentIndex < totalQ - 1"
                        @click="navigate('next')" :disabled="isNavigating"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-blue-700 hover:bg-blue-800 text-white disabled:opacity-35 cursor-pointer transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        Soal Berikutnya
                    </button>

                    {{-- Soal terakhir → Selesaikan Quiz --}}
                    <button x-show="currentIndex >= totalQ - 1"
                        @click="stopQuizAfterLastQuestion()" :disabled="_quizStopped"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-emerald-600 hover:bg-emerald-700 text-white disabled:opacity-35 cursor-pointer transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span x-text="_quizStopped ? 'Menyelesaikan...' : 'Selesaikan Quiz'"></span>
                    </button>
                </div>
            </div>

            {{-- Question Card --}}
            <div class="bg-white border border-blue-100 rounded-[20px] shadow-md overflow-hidden transition-all duration-200"
                :class="isNavigating ? 'opacity-60 scale-[0.99]' : ''">
                <div class="question-header relative bg-gradient-to-br from-blue-700 via-blue-600 to-blue-400 px-7 py-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-black tracking-widest uppercase text-blue-200"
                            x-text="'Soal ' + (currentIndex + 1) + ' dari ' + totalQ">Soal 1 dari {{ $totalQuestions }}</span>
                        <span class="text-[13px] font-black text-white bg-white/20 rounded-full px-4 py-1.5"
                            x-text="question ? question.score + ' Poin' : '—'">—</span>
                    </div>
                    <h2 class="text-[1.2rem] font-bold text-white leading-snug"
                        x-text="question ? question.question : 'Memuat soal...'">Memuat soal...</h2>
                </div>
                <div class="p-6 flex flex-col gap-3">
                    <div x-show="!question" class="py-12 text-center">
                        <div class="w-8 h-8 border-4 border-blue-100 border-t-blue-500 rounded-full mx-auto"
                            style="animation:spin 0.8s linear infinite;"></div>
                        <p class="text-slate-400 text-sm mt-3">Memuat pilihan jawaban...</p>
                    </div>
                    <template x-if="question && question.type === 'PG'">
                        <div class="flex flex-col gap-3">
                            <template x-for="(choice, idx) in question.choices" :key="choice.id">
                                <div class="flex items-center gap-3.5 px-4 py-3.5 rounded-2xl border border-blue-100 bg-[#f8faff] transition-all duration-300 cursor-default"
                                    :class="showAnswer && choice.is_correct ? '!border-green-400 !bg-green-50 answer-reveal' : showAnswer && !choice.is_correct ? '!border-slate-200 !bg-slate-50 opacity-50' : ''">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-black flex-shrink-0 text-white transition-colors"
                                        :class="showAnswer && choice.is_correct ? 'bg-green-500' : showAnswer && !choice.is_correct ? 'bg-slate-300' : 'bg-blue-700'"
                                        x-text="['A','B','C','D','E','F'][idx] || (idx+1)"></div>
                                    <span class="flex-1 font-semibold text-[15px] text-slate-700"
                                        :class="showAnswer && choice.is_correct ? '!font-bold !text-green-800' : showAnswer && !choice.is_correct ? '!text-slate-400' : ''"
                                        x-text="choice.text"></span>
                                    <div x-show="showAnswer && choice.is_correct"
                                        class="answer-reveal w-7 h-7 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="question && question.type === 'IS'">
                        <div class="rounded-xl border border-blue-200 bg-blue-50 px-5 py-4 flex items-center gap-3">
                            <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <div>
                                <p class="text-sm font-bold text-blue-800">Soal Isian Singkat</p>
                                <p class="text-xs text-blue-500">Siswa mengetik jawaban langsung di perangkat masing-masing.</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Navigation --}}
            <div class="flex items-center justify-between gap-3">
                <button @click="navigate('prev')" :disabled="currentIndex === 0 || isNavigating"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm border border-blue-100 bg-white text-blue-900 hover:bg-blue-50 disabled:opacity-35 cursor-pointer transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Sebelumnya
                </button>
                <div class="flex flex-wrap gap-1.5 justify-center max-w-xs">
                    <template x-for="i in totalQ" :key="i">
                        <button @click="navigate('goto', i - 1)"
                            class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-black border-2 cursor-pointer transition-all"
                            :class="(i - 1) === currentIndex ? 'bg-blue-700 border-blue-700 text-white' : 'bg-white border-blue-100 text-slate-400 hover:border-blue-400 hover:text-blue-700'"
                            x-text="i"></button>
                    </template>
                </div>
                <button @click="navigate('next')" :disabled="currentIndex >= totalQ - 1 || isNavigating"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-blue-700 hover:bg-blue-800 text-white disabled:opacity-35 cursor-pointer transition-all">
                    <span x-text="currentIndex === totalQ - 1 ? 'Soal Terakhir' : 'Berikutnya'">Berikutnya</span>
                    <svg x-show="currentIndex < totalQ - 1" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
            <p class="text-center text-[11px] text-slate-400 -mt-2">
                Keyboard:
                <kbd class="bg-slate-100 border border-slate-200 rounded px-1.5 py-0.5 text-[11px] font-mono text-slate-500">←</kbd> Sebelumnya &nbsp;
                <kbd class="bg-slate-100 border border-slate-200 rounded px-1.5 py-0.5 text-[11px] font-mono text-slate-500">→</kbd> Berikutnya
            </p>
        </div>

        {{-- SIDEBAR TOGGLE --}}
        <button @click="panelOpen = !panelOpen"
            class="fixed right-0 top-1/2 -translate-y-1/2 bg-blue-700 hover:bg-blue-800 text-white rounded-l-xl px-2.5 py-3.5 cursor-pointer z-40 shadow-lg transition-colors"
            style="writing-mode:vertical-rl;text-orientation:mixed;">
            <div class="flex flex-col items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <span class="text-[9px] font-black tracking-widest uppercase">PANEL</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="panelOpen ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </button>

        {{-- Panel Overlay --}}
        <div x-show="panelOpen" x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition duration-150"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-slate-900/35 backdrop-blur-sm" @click="panelOpen = false">
        </div>

        {{-- Panel Drawer --}}
        <div x-show="panelOpen" x-transition:enter="transition duration-300 ease-out"
            x-transition:enter-start="opacity-0 translate-x-full" x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="opacity-0 translate-x-full"
            class="fixed right-0 top-0 h-full z-50 w-80 overflow-y-auto bg-white border-l border-blue-100 shadow-2xl">
            <div class="sticky top-0 bg-white border-b border-blue-100 px-5 py-4 flex items-center justify-between z-10">
                <div>
                    <h3 class="text-sm font-bold text-blue-900">Panel Pengaturan</h3>
                    <p class="text-xs text-blue-400">{{ $quiz->title }}</p>
                </div>
                <button @click="panelOpen = false"
                    class="w-8 h-8 rounded-lg bg-blue-50 hover:bg-blue-100 flex items-center justify-center transition-colors cursor-pointer">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-5 flex flex-col gap-4">

                {{-- Timer Status --}}
                <div class="bg-white border border-blue-100 rounded-2xl p-4">
                    <p class="text-[10px] font-black tracking-widest uppercase text-blue-500 mb-3">⏱ Status Timer</p>
                    <div x-show="timePerQuestion <= 0" class="text-center py-3">
                        <div class="text-3xl mb-1">∞</div>
                        <div class="text-sm font-bold text-slate-500">Mode Manual</div>
                        <div class="text-xs text-slate-400 mt-1">Guru next soal sendiri</div>
                    </div>
                    <div x-show="timePerQuestion > 0">
                        <div x-show="!showAnswer" class="flex flex-col items-center py-2">
                            <div class="relative w-20 h-20 mb-2">
                                <svg class="w-20 h-20 timer-ring" viewBox="0 0 68 68">
                                    <circle class="timer-ring-track" cx="34" cy="34" r="28" />
                                    <circle class="timer-ring-progress"
                                        :stroke="timeLeft !== null && timeLeft <= 5 ? '#ef4444' : timeLeft !== null && timeLeft <= 10 ? '#f59e0b' : '#2563eb'"
                                        cx="34" cy="34" r="28" :stroke-dasharray="175.9"
                                        :stroke-dashoffset="175.9 - (timePerQuestion > 0 && timeLeft !== null ? Math.max(0, timeLeft / timePerQuestion) : 1) * 175.9" />
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="text-2xl font-black"
                                        :class="timeLeft !== null && timeLeft <= 5 ? 'text-red-600 timer-danger' : timeLeft !== null && timeLeft <= 10 ? 'text-amber-600' : 'text-blue-700'"
                                        x-text="timeLeft !== null ? timeLeft : '—'"></span>
                                </div>
                            </div>
                            <div class="text-xs text-slate-400 font-semibold">detik tersisa</div>
                        </div>
                        <div x-show="showAnswer" class="text-center py-2">
                            <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-2">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="text-sm font-bold text-green-700">Jawaban Benar Tampil</div>
                            <div class="text-xs text-green-500 mt-1">Auto-next: <span class="font-black" x-text="autoNextIn"></span>s</div>
                        </div>
                    </div>
                </div>

                {{-- Status Peserta --}}
                <div class="bg-white border border-blue-100 rounded-2xl p-4">
                    <p class="text-[10px] font-black tracking-widest uppercase text-blue-500 mb-3">👥 Status Peserta</p>
                    <div class="grid grid-cols-3 gap-2 mb-3">
                        <div class="text-center p-2 rounded-lg bg-blue-50 border border-blue-100">
                            <div class="text-lg font-black text-blue-700" x-text="stats.joined">{{ $stats['joined'] ?? 0 }}</div>
                            <div class="text-[9px] text-blue-400 font-bold">Bergabung</div>
                        </div>
                        <div class="text-center p-2 rounded-lg bg-amber-50 border border-amber-100">
                            <div class="text-lg font-black text-amber-600" x-text="Math.max(0, stats.joined - stats.submitted)">—</div>
                            <div class="text-[9px] text-amber-400 font-bold">Mengerjakan</div>
                        </div>
                    </div>
                    <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-green-400 rounded-full transition-all duration-500"
                            :style="'width:' + (stats.joined > 0 ? (stats.submitted / stats.joined * 100) : 0) + '%'"></div>
                    </div>
                </div>

                {{-- Info Quiz --}}
                <div class="bg-white border border-blue-100 rounded-2xl p-4">
                    <p class="text-[10px] font-black tracking-widest uppercase text-blue-500 mb-3">📋 Info Quiz</p>
                    <div class="space-y-2 text-[13px]">
                        <div class="flex justify-between items-center py-1.5 border-b border-blue-50">
                            <span class="text-slate-500">Total Soal</span>
                            <span class="font-bold text-slate-800">{{ $totalQuestions }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-blue-50">
                            <span class="text-slate-500">Soal Aktif</span>
                            <span class="font-bold text-blue-600" x-text="(currentIndex + 1) + ' dari ' + totalQ">—</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-blue-50">
                            <span class="text-slate-500">Durasi Total</span>
                            <span class="font-bold text-slate-800">{{ $quiz->duration ?? '—' }} mnt</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-blue-50">
                            <span class="text-slate-500">Per Soal</span>
                            <span class="font-bold" :class="timePerQuestion > 0 ? 'text-blue-600' : 'text-slate-400'">
                                {{ $quiz->time_per_question > 0 ? $quiz->time_per_question . ' dtk' : 'Manual' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-1.5">
                            <span class="text-slate-500">Instant Feedback</span>
                            <span class="font-bold {{ $quiz->instant_feedback ? 'text-green-600' : 'text-slate-400' }}">
                                {{ $quiz->instant_feedback ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Atur Waktu --}}
                <div class="bg-white border border-blue-100 rounded-2xl p-4">
                    <p class="text-[10px] font-black tracking-widest uppercase text-blue-500 mb-1">⏱ Atur Waktu Per Soal</p>
                    <p class="text-[11px] text-slate-400 mb-3 leading-relaxed">
                        Masukkan <span class="font-bold text-slate-600">0</span> untuk mode manual, atau isi detik untuk ganti otomatis.
                    </p>
                    <div class="flex items-center gap-2 mb-3">
                        <input type="number" min="0" max="300" x-model.number="editTimePerQuestion"
                            class="flex-1 px-3 py-2.5 rounded-xl border-2 border-blue-200 text-blue-900 font-black text-center text-lg focus:outline-none focus:ring-2 focus:ring-blue-400 transition-all bg-blue-50"
                            placeholder="0">
                        <span class="text-sm text-slate-400 font-bold flex-shrink-0">dtk</span>
                    </div>
                    <button @click="updateTimePerQuestion()" :disabled="isUpdatingTime"
                        class="w-full py-2.5 rounded-xl font-bold text-[13px] transition-all disabled:opacity-40 cursor-pointer"
                        :class="isUpdatingTime ? 'bg-slate-100 text-slate-400' : 'bg-blue-600 hover:bg-blue-700 text-white'">
                        <span x-show="!isUpdatingTime" x-text="editTimePerQuestion > 0 ? '✓ Terapkan (' + editTimePerQuestion + 's/soal)' : '✓ Aktifkan Mode Manual'"></span>
                        <span x-show="isUpdatingTime">Menyimpan...</span>
                    </button>
                    <div x-show="timeUpdateMsg" x-transition
                        class="mt-2 text-center text-[11px] font-semibold px-3 py-2 rounded-lg"
                        :class="timeUpdateSuccess ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-600 border border-red-200'"
                        x-text="timeUpdateMsg"></div>
                </div>

                {{-- Aksi Cepat --}}
                <div class="bg-white border border-blue-100 rounded-2xl p-4">
                    <p class="text-[10px] font-black tracking-widest uppercase text-blue-500 mb-3">🔗 Aksi Cepat</p>
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('guru.quiz.room', $quiz->id) }}"
                            class="flex items-center gap-2 px-4 py-3 rounded-xl text-[13px] font-semibold text-slate-700 bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 hover:text-blue-700 transition-all">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Lihat Ruangan
                        </a>
                        <a href="{{ route('guru.quiz.results', $quiz->id) }}"
                            class="flex items-center gap-2 px-4 py-3 rounded-xl text-[13px] font-semibold text-slate-700 bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 hover:text-blue-700 transition-all">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Lihat Hasil Quiz
                        </a>
                        <a href="{{ route('guru.quiz.leaderboard', $quiz->id) }}"
                            class="flex items-center gap-2 px-4 py-3 rounded-xl text-[13px] font-semibold text-slate-700 bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-200 hover:text-blue-700 transition-all">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                            Leaderboard
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        function guidedControl() {
            return {
                quizId: {{ $quiz->id }},
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                totalQ: {{ $totalQuestions }},
                currentIndex: {{ $quiz->guided_current_index ?? 0 }},
                question: @json($initialQuestion),
                isNavigating: false,
                showAnswer: {{ $quiz->guided_show_answer ? 'true' : 'false' }},
                timeLeft: null,
                timePerQuestion: {{ (int) ($quiz->time_per_question ?? 0) }},
                editTimePerQuestion: {{ (int) ($quiz->time_per_question ?? 0) }},
                isUpdatingTime: false,
                timeUpdateMsg: '',
                timeUpdateSuccess: false,
                autoNextIn: 5,
                stats: {
                    joined: {{ $stats['joined'] ?? 0 }},
                    submitted: {{ $stats['submitted'] ?? 0 }}
                },
                panelOpen: false,
                isFullscreen: false,
                isLastAndRevealed: false,
                _quizStopped: false,
                _stateInterval: null,
                _localTimer: null,
                _autoNextTimer: null,
                _deadline: @json($quiz->guided_question_deadline ?? null),
                init() {
                    @if ($quiz->guided_question_deadline)
                        const now = Math.floor(Date.now() / 1000);
                        this.timeLeft = Math.max(0, {{ $quiz->guided_question_deadline }} - now);
                    @endif
                    this._stateInterval = setInterval(() => this.pollState(), 2000);
                    this._localTimer = setInterval(() => this.tickTimer(), 1000);
                    window.addEventListener('keydown', (e) => {
                        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { e.preventDefault(); this.navigate('next'); }
                        if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') { e.preventDefault(); this.navigate('prev'); }
                        if (e.key === 'F11') { e.preventDefault(); this.toggleFullscreen(); }
                    });
                    // Deteksi perubahan fullscreen dari browser (tombol Esc, F11 native, dll)
                    document.addEventListener('fullscreenchange', () => {
                        this.isFullscreen = !!document.fullscreenElement;
                        document.body.classList.toggle('is-fullscreen', this.isFullscreen);
                    });
                },
                toggleFullscreen() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(() => {});
                    } else {
                        document.exitFullscreen().catch(() => {});
                    }
                },
                async updateTimePerQuestion() {
                    if (this.isUpdatingTime) return;
                    this.isUpdatingTime = true;
                    this.timeUpdateMsg = '';
                    try {
                        const r = await fetch(`/guru/quiz/${this.quizId}/room/guided/set-time`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': this.csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ time_per_question: this.editTimePerQuestion })
                        });
                        const d = await r.json();
                        if (d.success) {
                            this.timePerQuestion = this.editTimePerQuestion;
                            this.timeLeft = this.timePerQuestion > 0 ? this.timePerQuestion : null;
                            this._deadline = null;
                            this.showAnswer = false;
                            this.timeUpdateMsg = this.timePerQuestion > 0 ? `✓ Timer ${this.timePerQuestion}s/soal aktif` : '✓ Mode manual aktif';
                            this.timeUpdateSuccess = true;
                        } else {
                            this.timeUpdateMsg = d.message || 'Gagal menyimpan';
                            this.timeUpdateSuccess = false;
                        }
                    } catch {
                        this.timeUpdateMsg = 'Terjadi kesalahan';
                        this.timeUpdateSuccess = false;
                    } finally {
                        this.isUpdatingTime = false;
                        setTimeout(() => { this.timeUpdateMsg = ''; }, 4000);
                    }
                },
                tickTimer() {
                    if (!this._deadline || this.timePerQuestion <= 0) return;
                    const now = Math.floor(Date.now() / 1000), left = Math.max(0, this._deadline - now);
                    this.timeLeft = left;
                    if (left <= 0 && !this.showAnswer) this.revealAnswer();
                },
                async revealAnswer() {
                    if (this.showAnswer) return;
                    this.showAnswer = true;
                    try {
                        await fetch(`/guru/quiz/${this.quizId}/room/guided/reveal`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': this.csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({})
                        });
                    } catch (e) {}

                    const isLast = this.currentIndex >= this.totalQ - 1;

                    if (this.timePerQuestion <= 0) {
                        // Mode manual: jika soal terakhir, tampilkan tombol selesaikan
                        if (isLast) this.isLastAndRevealed = true;
                        return;
                    }

                    this.autoNextIn = 5;
                    if (this._autoNextTimer) clearInterval(this._autoNextTimer);
                    this._autoNextTimer = setInterval(() => {
                        this.autoNextIn--;
                        if (this.autoNextIn <= 0) {
                            clearInterval(this._autoNextTimer);
                            this._autoNextTimer = null;
                            if (!isLast) {
                                this.navigate('next');
                            } else {
                                // Soal terakhir habis → stop quiz otomatis
                                this.stopQuizAfterLastQuestion();
                            }
                        }
                    }, 1000);
                },

                async stopQuizAfterLastQuestion() {
                    if (this._quizStopped) return;
                    this._quizStopped = true;
                    try {
                        await fetch(`/guru/quiz/${this.quizId}/room/stop`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' }
                        });
                    } catch(e) {}
                    // Redirect ke halaman room/hasil
                    setTimeout(() => { window.location.href = `/guru/quiz/${this.quizId}/room`; }, 1500);
                },
                async navigate(dir, idx = null) {
                    if (this.isNavigating) return;
                    if (dir === 'next' && this.currentIndex >= this.totalQ - 1) return;
                    if (dir === 'prev' && this.currentIndex <= 0) return;
                    this.isNavigating = true;
                    if (this._autoNextTimer) { clearInterval(this._autoNextTimer); this._autoNextTimer = null; }
                    try {
                        const r = await fetch(`/guru/quiz/${this.quizId}/room/guided/${dir}`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': this.csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: dir === 'goto' ? JSON.stringify({ index: idx }) : JSON.stringify({})
                        });
                        const d = await r.json();
                        if (d.success) {
                            this.currentIndex = d.current_index;
                            this.question = d.question;
                            this.showAnswer = d.show_answer || false;
                            this._deadline = d.question_deadline ?? null;
                            if (this._deadline && this.timePerQuestion > 0) {
                                const now = Math.floor(Date.now() / 1000);
                                this.timeLeft = Math.max(0, this._deadline - now);
                            } else {
                                this.timeLeft = this.timePerQuestion > 0 ? this.timePerQuestion : null;
                            }
                            this.autoNextIn = 5;
                        }
                    } catch (e) {} finally { this.isNavigating = false; }
                },
                async pollState() {
                    try {
                        const r = await fetch(`/guru/quiz/${this.quizId}/room/guided/state`, {
                            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf }
                        });
                        if (!r.ok) return;
                        const d = await r.json();
                        if (!d.success) return;
                        this.stats.joined = d.stats?.joined ?? this.stats.joined;
                        this.stats.submitted = d.stats?.submitted ?? this.stats.submitted;
                        if (d.time_per_question !== undefined && d.time_per_question !== this.timePerQuestion) {
                            this.timePerQuestion = d.time_per_question;
                            this.editTimePerQuestion = d.time_per_question;
                        }
                        this._deadline = d.question_deadline ?? this._deadline;
                        if (d.show_answer !== undefined) this.showAnswer = d.show_answer;
                        if (d.current_index !== this.currentIndex && !this.isNavigating) {
                            this.currentIndex = d.current_index;
                            this.question = d.question;
                            if (d.show_answer !== undefined) this.showAnswer = d.show_answer;
                            if (d.question_deadline && this.timePerQuestion > 0) {
                                const now = Math.floor(Date.now() / 1000);
                                this.timeLeft = Math.max(0, d.question_deadline - now);
                            }
                        }
                        if (this._deadline && !this.showAnswer && this.timePerQuestion > 0) {
                            const now = Math.floor(Date.now() / 1000), left = Math.max(0, this._deadline - now);
                            this.timeLeft = left;
                            if (left <= 0 && !this.showAnswer) this.revealAnswer();
                        }
                    } catch (e) {}
                },
            };
        }
    </script>
</body>

</html>
