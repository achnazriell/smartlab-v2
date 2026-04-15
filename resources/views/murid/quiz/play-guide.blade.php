<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Quiz – {{ $quiz->title }}</title>
    <link rel="icon" type="image/webp" href="{{ asset('image/logo.webp') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(22px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes popIn {
            0% {
                opacity: 0;
                transform: scale(.88);
            }

            65% {
                transform: scale(1.03);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes pulseDot {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: .5;
                transform: scale(1.6);
            }
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-9px);
            }
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0);
            }

            60% {
                transform: scale(1.18);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        @keyframes vPop {
            from {
                opacity: 0;
                transform: scale(0.7);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes vShake {

            0%,
            100% {
                transform: rotate(0);
            }

            20% {
                transform: rotate(-8deg);
            }

            40% {
                transform: rotate(8deg);
            }

            60% {
                transform: rotate(-6deg);
            }

            80% {
                transform: rotate(6deg);
            }
        }

        .anim-fade-up {
            animation: fadeUp .38s cubic-bezier(.16, 1, .3, 1) both;
        }

        .anim-pop-in {
            animation: popIn .44s cubic-bezier(.16, 1, .3, 1) both;
        }

        .anim-float {
            animation: float 3.2s ease-in-out infinite;
        }

        .shimmer {
            background: linear-gradient(90deg, #eef2ff 25%, #dbeafe 50%, #eef2ff 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 10px;
        }

        /* Choice button states — dynamic binding via x-bind */
        .choice-btn {
            touch-action: manipulation;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
        }

        .choice-btn:not(:disabled):hover {
            transform: translateY(-2px) scale(1.01);
        }

        .choice-btn:not(:disabled):active {
            transform: scale(.97);
        }

        .choice-btn:disabled {
            cursor: not-allowed;
        }

        .check-badge {
            animation: bounceIn .28s cubic-bezier(.16, 1, .3, 1) both;
        }

        /* Desktop paper overlay */
        @media (min-width:1024px) {
            body {
                background: url('{{ asset('image/play-guide.webp') }}') center center / cover no-repeat fixed !important;
                overflow: hidden !important;
                height: 100dvh !important;
            }

            main.flex-1 {
                display: none !important;
            }

            #dtop-paper {
                position: fixed;
                z-index: 20;
                overflow: hidden;

                /*
                 * ┌─────────────────────────────────────────────────────────┐
                 * │  CARA UBAH TAMPILAN BACKGROUND KERTAS                   │
                 * │                                                          │
                 * │  Pilihan 1 — TRANSPARAN PENUH (lihat gambar tembus):    │
                 * │    background: transparent;                              │
                 * │                                                          │
                 * │  Pilihan 2 — SEMI TRANSPARAN dengan gradient:           │
                 * │    Ubah angka rgba ke-4 (0.0 = bening, 1.0 = putih)    │
                 * │    Contoh: rgba(255,255,255, 0.55) = 55% putih          │
                 * │                                                          │
                 * │  Pilihan 3 — PUTIH SOLID (nilai asli):                  │
                 * │    background: rgba(253,254,255,0.97);                   │
                 * └─────────────────────────────────────────────────────────┘
                 *
                 * Gradient atas transparan → bawah sedikit putih
                 * agar area jawaban lebih mudah dibaca
                 */
                background: linear-gradient(to bottom,
                        rgba(255, 255, 255, 0.70) 100%
                        rgba(255, 255, 255, 0.55) 65%,
                        rgba(255, 255, 255, 0.70) 100%);
                /* Ubah angka-angka di atas untuk atur transparansi tiap bagian */

                /* backdrop-filter: blur(2px);
                -webkit-backdrop-filter: blur(2px);
                filter: drop-shadow(0 4px 16px rgba(15, 40, 100, .18)); */
            }

            #dtop-paper-inner {
                position: relative;
                z-index: 1;
                height: 100%;
                overflow-y: auto;
                overflow-x: hidden;
                display: flex;
                flex-direction: column;
                gap: 10px;
                scrollbar-width: thin;
                scrollbar-color: rgba(148, 163, 184, .35) transparent;
            }

            #dtop-paper-inner::-webkit-scrollbar {
                width: 3px;
            }

            #dtop-paper-inner::-webkit-scrollbar-thumb {
                background: rgba(148, 163, 184, .35);
                border-radius: 99px;
            }

            #dtop-paper .q-card {
                background: transparent;
                border: none;
                border-radius: 0;
                box-shadow: none;
            }

            #dtop-paper .q-header {
                border-radius: 10px;
            }

            #dtop-paper .q-body {
                padding: 11px 0 0 0;
            }

            #dtop-paper .choice-btn {
                background: rgba(248, 250, 255, .88);
                min-height: 54px;
            }

            #dtop-paper .short-input {
                background: rgba(248, 250, 255, .88);
            }

            #dtop-paper .banner-blue {
                background: rgba(239, 246, 255, .82);
            }

            #dtop-paper .banner-orange {
                background: rgba(255, 247, 237, .82);
            }
        }
    </style>
</head>

<body class="bg-[#f0f5ff] min-h-dvh overflow-x-hidden">

    {{-- Background blobs --}}
    <div class="fixed -top-24 -right-24 w-96 h-96 rounded-full pointer-events-none z-0"
        style="background:radial-gradient(circle,rgba(59,130,246,.12) 0%,transparent 70%)"></div>
    <div class="fixed -bottom-20 -left-20 w-72 h-72 rounded-full pointer-events-none z-0"
        style="background:radial-gradient(circle,rgba(29,78,216,.08) 0%,transparent 70%)"></div>

    <div x-data="guidedPlay()" x-init="init()" class="min-h-screen flex flex-col relative z-10">

        {{-- ═══════════ VIOLATION OVERLAY ═══════════ --}}
        <div x-show="violationOverlay" x-transition
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-red-500/20 backdrop-blur-sm">
            <div
                class="bg-white rounded-2xl border-4 border-red-500 shadow-2xl p-8 text-center max-w-sm w-11/12 anim-pop-in">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-red-100 to-red-200 border-4 border-red-500 flex items-center justify-center mx-auto mb-4 text-3xl"
                    style="animation:vShake 0.5s ease">⚠️</div>
                <h3 class="text-lg font-black text-red-900 mb-2">Pelanggaran Terdeteksi!</h3>
                <p x-text="violationMsg" class="text-sm text-slate-500 mb-4 leading-relaxed"></p>
                <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-2.5 text-xs font-bold text-red-900">
                    Setiap pelanggaran dicatat oleh guru!
                </div>
            </div>
        </div>

        {{-- ═══════════ TOP BAR ═══════════ --}}
        <header class="sticky top-0 z-30 bg-white/93 backdrop-blur-lg border-b border-blue-100 shadow-sm">
            <div class="flex items-center justify-between px-5 h-14 gap-2">
                <div class="flex items-center gap-2 flex-shrink-0">
                    <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"
                        style="animation:pulseDot 1.7s ease-in-out infinite;"></span>
                    <span class="text-[10px] font-black tracking-widest text-blue-600 uppercase">Live</span>
                </div>
                <p class="text-[12px] font-semibold text-slate-400 truncate flex-1 text-center">
                    {{ Str::limit($quiz->title, 28) }}
                </p>
                <div class="flex items-center gap-1.5 flex-shrink-0">
                    <span class="text-xs font-black text-blue-700"
                        x-text="currentIndex < 0 ? '—' : (currentIndex + 1) + '/' + totalQ"></span>
                    <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0"
                        :style="'background: conic-gradient(#2563eb ' + (currentIndex < 0 ? 0 : Math.round((currentIndex + 1) /
                            totalQ * 100)) + '%, #dbeafe 0)'">
                        <div class="w-4.5 h-4.5 w-[18px] h-[18px] rounded-full bg-white"></div>
                    </div>
                </div>
            </div>
        </header>

        {{-- ═══════════ TIMER STRIP ═══════════ --}}
        <div x-show="state === 'question' && timePerQuestion > 0"
            class="bg-white border-b border-blue-100 px-5 pt-2 pb-2.5">
            <div class="flex items-center justify-between mb-1.5">
                <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 flex-shrink-0"
                        :class="timeLeft !== null && timeLeft <= 5 ? 'text-red-500' : timeLeft !== null && timeLeft <= 10 ?
                            'text-amber-500' : 'text-blue-500'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-[10px] font-bold uppercase tracking-wider"
                        :class="timeLeft !== null && timeLeft <= 5 ? 'text-red-500' : timeLeft !== null && timeLeft <= 10 ?
                            'text-amber-500' : 'text-blue-500'">
                        Waktu Soal
                    </span>
                </div>
                <span class="text-sm font-black"
                    :class="timeLeft !== null && timeLeft <= 5 ? 'text-red-600' : timeLeft !== null && timeLeft <= 10 ?
                        'text-amber-600' : 'text-blue-700'"
                    x-text="timeLeft !== null ? timeLeft + 's' : '—'"></span>
            </div>
            <div class="h-1.5 rounded-full bg-blue-100 overflow-hidden">
                <div class="h-full rounded-full transition-[width] duration-1000 linear"
                    :class="timeLeft !== null && timeLeft <= 5 ? 'bg-red-500' : timeLeft !== null && timeLeft <= 10 ?
                        'bg-amber-400' : 'bg-blue-500'"
                    :style="'width:' + (timePerQuestion > 0 && timeLeft !== null ? Math.max(0, timeLeft / timePerQuestion *
                        100) : 100) + '%'">
                </div>
            </div>
        </div>

        {{-- ═══════════ MAIN ═══════════ --}}
        <main class="flex-1 flex items-center justify-center px-4 py-5 overflow-y-auto">

            {{-- ─────── WAITING ─────── --}}
            <div x-show="state === 'waiting'" class="w-full max-w-sm anim-fade-up">
                <div class="text-center mb-5">
                    <div
                        class="anim-float inline-flex items-center justify-center w-24 h-24 rounded-3xl mx-auto mb-3 bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-200 shadow-lg">
                        <svg class="w-11 h-11 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-black text-slate-800 mb-1">Menunggu Guru Memulai</h2>
                    <p class="text-sm text-slate-400 leading-relaxed">Soal muncul otomatis saat guru memulai quiz.</p>
                </div>

                <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-md mb-4">
                    <p class="text-[10px] font-black tracking-widest uppercase text-blue-600 mb-3">📋 Info Quiz</p>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div class="rounded-xl bg-blue-50 border border-blue-100 p-3 text-center">
                            <div class="text-2xl font-black text-blue-700">{{ $totalQuestions ?? 0 }}</div>
                            <div class="text-[10px] text-blue-400 font-bold mt-0.5">Total Soal</div>
                        </div>
                        <div
                            class="rounded-xl p-3 text-center {{ $quiz->time_per_question > 0 ? 'bg-blue-50 border border-blue-200' : 'bg-slate-50 border border-blue-100' }}">
                            <div
                                class="text-2xl font-black {{ $quiz->time_per_question > 0 ? 'text-blue-700' : 'text-slate-400' }}">
                                {{ $quiz->time_per_question > 0 ? $quiz->time_per_question . 's' : '∞' }}
                            </div>
                            <div
                                class="text-[10px] font-bold mt-0.5 {{ $quiz->time_per_question > 0 ? 'text-blue-400' : 'text-slate-300' }}">
                                {{ $quiz->time_per_question > 0 ? 'Dtk/Soal' : 'No Limit' }}
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center py-2.5 border-b border-slate-50 text-sm">
                            <span class="text-slate-400">Mode</span>
                            <span
                                class="font-bold text-slate-700">{{ $quiz->quiz_mode === 'guided' ? 'Terpadu' : 'Mandiri' }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2.5 border-b border-slate-50 text-sm">
                            <span class="text-slate-400">Ganti Soal</span>
                            <span
                                class="font-bold {{ $quiz->time_per_question > 0 ? 'text-blue-600' : 'text-slate-400' }}">
                                {{ $quiz->time_per_question > 0 ? 'Otomatis (' . $quiz->time_per_question . 's)' : 'Manual oleh Guru' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-2.5 text-sm">
                            <span class="text-slate-400">Feedback</span>
                            <span
                                class="font-bold {{ $quiz->instant_feedback ? 'text-green-600' : 'text-slate-400' }}">
                                {{ $quiz->instant_feedback ? '✓ Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    class="flex items-center justify-center gap-2.5 bg-blue-50 rounded-2xl px-4 py-3 border border-blue-100">
                    <div class="w-4 h-4 border-2 border-blue-200 border-t-blue-600 rounded-full flex-shrink-0"
                        style="animation:spin .85s linear infinite;"></div>
                    <p class="text-xs font-semibold text-blue-500">Menunggu guru memulai…</p>
                </div>
            </div>

            {{-- ─────── QUESTION ─────── --}}
            <div x-show="state === 'question'" class="w-full max-w-lg anim-fade-up">

                {{-- Question card --}}
                <div class="q-card bg-white border border-blue-100 rounded-3xl shadow-lg overflow-hidden mb-4">

                    {{-- Header --}}
                    <div
                        class="q-header relative bg-gradient-to-br from-blue-700 to-blue-500 px-5 py-5 overflow-hidden">
                        {{-- Deco circles --}}
                        <div class="absolute -top-7 -right-7 w-28 h-28 rounded-full bg-white/8 pointer-events-none">
                        </div>
                        <div class="absolute -bottom-5 left-12 w-18 h-18 rounded-full bg-white/5 pointer-events-none">
                        </div>

                        <div class="flex items-start justify-between gap-3 mb-3 relative z-10">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 bg-white/18">
                                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <span class="text-[10px] font-black text-blue-100 uppercase tracking-widest"
                                    x-text="'Pertanyaan ' + (currentIndex + 1)"></span>
                            </div>
                            <span
                                class="bg-white/20 border border-white/30 text-white text-[11px] font-black px-3 py-0.5 rounded-full flex-shrink-0"
                                x-text="currentQuestion ? currentQuestion.score + ' poin' : '—'"></span>
                        </div>

                        <p class="text-white font-bold text-[15px] leading-snug relative z-10"
                            x-show="currentQuestion" x-text="currentQuestion ? currentQuestion.question : ''"></p>

                        {{-- Shimmer placeholder --}}
                        <div x-show="!currentQuestion" class="space-y-2 relative z-10">
                            <div class="h-4 rounded-lg bg-white/15" style="width:90%"></div>
                            <div class="h-4 rounded-lg bg-white/12" style="width:70%"></div>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="q-body p-4 pb-5">

                        {{-- Shimmer loading --}}
                        <div x-show="!currentQuestion">
                            <div class="grid grid-cols-2 gap-3 mb-2">
                                <div class="shimmer" style="height:88px;"></div>
                                <div class="shimmer" style="height:88px;"></div>
                                <div class="shimmer" style="height:88px;"></div>
                                <div class="shimmer" style="height:88px;"></div>
                            </div>
                        </div>

                        {{-- Banner: lihat layar guru --}}
                        <div x-show="currentQuestion && !timesUp"
                            class="banner banner-blue flex items-center gap-3 rounded-2xl px-3.5 py-3 mb-3 bg-blue-50 border border-blue-200">
                            <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4.5 h-4.5 text-blue-600" style="width:18px;height:18px;" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[13px] font-bold text-blue-800 leading-none mb-0.5">Lihat soal di layar
                                    guru</p>
                                <p class="text-[11px] text-blue-500">Pilih jawaban yang tepat di bawah</p>
                            </div>
                        </div>

                        {{-- Banner: waktu habis --}}
                        <div x-show="currentQuestion && timesUp && !isSubmitted"
                            class="banner banner-orange flex items-center gap-3 rounded-2xl px-3.5 py-3 mb-3 bg-orange-50 border border-orange-200">
                            <div
                                class="w-9 h-9 rounded-xl bg-orange-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4.5 h-4.5 text-orange-500" style="width:18px;height:18px;"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[13px] font-bold text-orange-700 leading-none mb-0.5">Waktu Habis</p>
                                <p class="text-[11px] text-orange-400">Menunggu guru pindah soal…</p>
                            </div>
                        </div>

                        {{-- PG Choices --}}
                        <div x-show="currentQuestion && currentQuestion.type === 'PG'">
                            <div class="grid grid-cols-2 gap-3">
                                <template x-for="(choice, idx) in (currentQuestion ? currentQuestion.choices : [])"
                                    :key="choice.id">
                                    <button @click="selectChoice(choice)" :disabled="isSubmitted || timesUp"
                                        class="choice-btn relative flex flex-col items-center justify-center gap-1.5 min-h-[88px] p-3 rounded-2xl border-2 border-blue-100 bg-[#f8faff] font-semibold text-[13px] text-slate-700 transition-all duration-200"
                                        :class="{
                                            'border-green-500 bg-green-50 shadow-[0_0_0_3px_rgba(22,163,74,.13)]': feedbackMap[choice.id]==='correct',
                                            'border-red-200 bg-red-50/60 opacity-60': feedbackMap[choice.id]==='wrong',
                                            'border-blue-600 bg-blue-50 shadow-[0_0_0_3px_rgba(59,130,246,.16)]': selectedId ===
                                                choice.id && !timesUp && !feedbackMap[choice.id],
                                            'border-slate-200 bg-slate-50 opacity-40 cursor-not-allowed': timesUp && !
                                                feedbackMap[choice.id] && selectedId !== choice.id
                                        }">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-[12px] font-black transition-colors flex-shrink-0"
                                            :class="{
                                                'bg-green-600 text-white': feedbackMap[choice.id]==='correct',
                                                'bg-red-500 text-white': feedbackMap[choice.id]==='wrong',
                                                'bg-blue-700 text-white': selectedId === choice.id && !feedbackMap[
                                                    choice.id],
                                                'bg-blue-100 text-blue-700': !feedbackMap[choice.id] && selectedId !==
                                                    choice.id
                                            }"
                                            x-text="['A','B','C','D','E','F'][idx] || (idx+1)"></div>
                                        <span class="text-[12.5px] leading-snug text-center font-semibold"
                                            x-text="choice.text"></span>

                                        {{-- Selected checkmark --}}
                                        <div x-show="selectedId === choice.id && !feedbackMap[choice.id]"
                                            class="check-badge absolute top-1.5 right-1.5 w-5 h-5 rounded-full bg-blue-600 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        {{-- Correct checkmark --}}
                                        <div x-show="feedbackMap[choice.id]==='correct'"
                                            class="check-badge absolute top-1.5 right-1.5 w-5 h-5 rounded-full bg-green-600 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        {{-- Wrong X --}}
                                        <div x-show="feedbackMap[choice.id]==='wrong'"
                                            class="check-badge absolute top-1.5 right-1.5 w-5 h-5 rounded-full bg-red-500 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        {{-- Isian Singkat --}}
                        <div x-show="currentQuestion && currentQuestion.type === 'IS'" class="flex flex-col gap-3">
                            <input type="text" x-model="shortAnswer" @keydown.enter="submitShortAnswer()"
                                placeholder="Ketik jawaban kamu di sini…" autocomplete="off"
                                :disabled="isSubmitted || timesUp"
                                class="w-full px-4 py-3 rounded-2xl border-2 border-blue-100 bg-[#f8faff] text-[15px] font-semibold text-slate-900 outline-none transition-all focus:border-blue-400 focus:ring-4 focus:ring-blue-400/10 focus:bg-white disabled:opacity-50 disabled:cursor-not-allowed placeholder:text-blue-200 placeholder:font-medium">
                            <button @click="submitShortAnswer()"
                                :disabled="!shortAnswer.trim() || isSubmitted || timesUp"
                                class="w-full py-3 rounded-2xl bg-gradient-to-r from-blue-700 to-blue-500 text-white text-[15px] font-black shadow-lg shadow-blue-700/25 transition-all hover:shadow-xl hover:shadow-blue-700/35 hover:-translate-y-0.5 active:scale-[.98] disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none disabled:translate-y-0 cursor-pointer">
                                ✓ &nbsp;Kirim Jawaban
                            </button>
                        </div>

                        {{-- Instant Feedback --}}
                        <div x-show="feedbackMsg && !timesUp" x-transition
                            class="mt-3 px-4 py-2.5 rounded-xl text-[13px] font-bold text-center"
                            :class="feedbackCorrect ? 'bg-green-50 border border-green-300 text-green-700' :
                                'bg-rose-50 border border-rose-300 text-rose-700'"
                            x-text="feedbackMsg"></div>
                    </div>
                </div>

                {{-- Progress dots --}}
                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                    <template x-for="i in totalQ" :key="i">
                        <div class="rounded-full transition-all duration-300"
                            :class="{
                                'w-2 h-2 bg-blue-600 scale-[1.45]': (i - 1) === currentIndex,
                                'w-1.5 h-1.5 bg-blue-300': (i - 1) < currentIndex,
                                'w-1.5 h-1.5 bg-blue-100': (i - 1) > currentIndex
                            }">
                        </div>
                    </template>
                </div>
            </div>

            {{-- ─────── SUBMITTED ─────── --}}
            <div x-show="state === 'submitted'" class="w-full max-w-sm anim-pop-in">
                <div class="bg-white text-center border border-green-100 rounded-3xl shadow-lg p-7">
                    <div class="anim-float mb-5">
                        <div
                            class="w-24 h-24 rounded-full flex items-center justify-center mx-auto bg-gradient-to-br from-green-100 to-green-200 border-2 border-green-300 shadow-lg">
                            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-black text-slate-800 mb-2">Quiz Selesai! </h2>
                    <p class="text-sm text-slate-400 leading-relaxed mb-5">
                        Semua jawaban sudah terkirim.<br>Tunggu guru menampilkan hasil.
                    </p>
                    <div class="rounded-xl bg-blue-50 border border-blue-100 px-4 py-3">
                        <p class="text-xs text-blue-500 font-semibold">Terima kasih sudah mengikuti quiz dengan baik!
                            </p>
                    </div>
                </div>
            </div>

        </main>

        {{-- ═══════════ TOAST ═══════════ --}}
        <div x-show="showSaved" x-transition:enter="transition duration-200 ease-out"
            x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="opacity-0 scale-90"
            class="fixed top-[68px] left-1/2 -translate-x-1/2 z-[100] flex items-center gap-1.5 bg-white border border-green-300 text-green-700 text-xs font-black px-4 py-1.5 rounded-full shadow-lg whitespace-nowrap">
            <svg class="w-3.5 h-3.5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            Jawaban tersimpan
        </div>

        {{-- ══ DESKTOP paper overlay (positioned by JS) ══ --}}
        <div id="dtop-paper" style="display:none;">
            <div id="dtop-paper-inner">

                {{-- WAITING --}}
                <div x-show="state==='waiting'" class="anim-fade-up flex flex-col gap-3">
                    <div class="text-center">
                        <div
                            class="anim-float inline-flex items-center justify-center w-16 h-16 rounded-2xl mx-auto mb-2 bg-gradient-to-br from-blue-50 to-blue-100 border-2 border-blue-200">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                    d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <h2 class="text-[13px] font-black text-slate-800 mb-0.5">Menunggu Guru Memulai</h2>
                        <p class="text-[10px] text-slate-400">Soal muncul otomatis saat guru memulai.</p>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-xl bg-blue-50 border border-blue-100 p-2 text-center">
                            <div class="text-xl font-black text-blue-700">{{ $totalQuestions ?? 0 }}</div>
                            <div class="text-[9px] text-blue-400 font-bold">Total Soal</div>
                        </div>
                        <div
                            class="rounded-xl p-2 text-center {{ $quiz->time_per_question > 0 ? 'bg-blue-50 border border-blue-200' : 'bg-slate-50 border border-blue-100' }}">
                            <div
                                class="text-xl font-black {{ $quiz->time_per_question > 0 ? 'text-blue-700' : 'text-slate-400' }}">
                                {{ $quiz->time_per_question > 0 ? $quiz->time_per_question . 's' : '∞' }}
                            </div>
                            <div
                                class="text-[9px] font-bold {{ $quiz->time_per_question > 0 ? 'text-blue-400' : 'text-slate-300' }}">
                                {{ $quiz->time_per_question > 0 ? 'Dtk/Soal' : 'No Limit' }}
                            </div>
                        </div>
                    </div>
                    <div
                        class="flex items-center justify-center gap-2 bg-blue-50 rounded-xl px-3 py-2 border border-blue-100">
                        <div class="w-3 h-3 border-2 border-blue-200 border-t-blue-600 rounded-full flex-shrink-0"
                            style="animation:spin .85s linear infinite;"></div>
                        <p class="text-[10px] font-semibold text-blue-500">Menunggu guru memulai…</p>
                    </div>
                </div>

                {{-- QUESTION — hanya pilihan jawaban, tanpa header soal --}}
                <div x-show="state==='question'" class="anim-fade-up flex flex-col gap-1.5">

                    {{-- nomor soal + poin kecil di atas --}}
                    <div class="flex items-center justify-between px-0.5">
                        <span class="text-[9px] font-black text-blue-400 uppercase tracking-widest"
                            x-text="'Soal ' + (currentIndex + 1) + ' / ' + totalQ"></span>
                        <span class="text-[9px] font-black text-blue-400"
                            x-text="currentQuestion ? currentQuestion.score + ' poin' : ''"></span>
                    </div>

                    {{-- Banner waktu habis --}}
                    <div x-show="timesUp && !isSubmitted"
                        class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 bg-orange-50/80 border border-orange-200">
                        <svg class="w-3 h-3 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-[9px] font-bold text-orange-700">Waktu Habis · Tunggu guru pindah soal</p>
                    </div>

                    {{-- PG: 1 kolom vertikal --}}
                    <div x-show="currentQuestion && currentQuestion.type==='PG'" class="flex flex-col gap-1">
                        <template x-for="(choice,idx) in (currentQuestion?currentQuestion.choices:[])"
                            :key="choice.id">
                            <button @click="selectChoice(choice)" :disabled="isSubmitted || timesUp"
                                class="choice-btn relative flex items-center gap-2 w-full px-2.5 py-2 rounded-xl border-2 transition-all duration-200 text-left"
                                :class="{
                                    'border-green-500 bg-green-50/90': feedbackMap[choice.id]==='correct',
                                    'border-red-200 bg-red-50/60 opacity-60': feedbackMap[choice.id]==='wrong',
                                    'border-blue-500 bg-blue-50/90': selectedId === choice.id && !timesUp && !
                                        feedbackMap[choice.id],
                                    'border-white/60 bg-white/55': !feedbackMap[choice.id] && selectedId !== choice
                                        .id && !timesUp,
                                    'border-slate-200/50 bg-white/30 opacity-40 cursor-not-allowed': timesUp && !
                                        feedbackMap[choice.id] && selectedId !== choice.id
                                }">
                                <div class="w-5 h-5 rounded-full flex items-center justify-center text-[8px] font-black flex-shrink-0 transition-colors"
                                    :class="{
                                        'bg-green-600 text-white': feedbackMap[choice.id]==='correct',
                                        'bg-red-400 text-white': feedbackMap[choice.id]==='wrong',
                                        'bg-blue-600 text-white': selectedId === choice.id && !feedbackMap[choice.id],
                                        'bg-blue-100/80 text-blue-700': !feedbackMap[choice.id] && selectedId !== choice
                                            .id
                                    }"
                                    x-text="['A','B','C','D','E','F'][idx]||(idx+1)"></div>
                                <span class="flex-1 text-[10px] font-semibold leading-snug"
                                    :class="{
                                        'text-green-800': feedbackMap[choice.id]==='correct',
                                        'text-red-400': feedbackMap[choice.id]==='wrong',
                                        'text-blue-900': selectedId === choice.id && !feedbackMap[choice.id],
                                        'text-slate-700': !feedbackMap[choice.id] && selectedId !== choice.id
                                    }"
                                    x-text="choice.text"></span>
                                <div x-show="selectedId===choice.id && !feedbackMap[choice.id]"
                                    class="check-badge w-3.5 h-3.5 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div x-show="feedbackMap[choice.id]==='correct'"
                                    class="check-badge w-3.5 h-3.5 rounded-full bg-green-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div x-show="feedbackMap[choice.id]==='wrong'"
                                    class="check-badge w-3.5 h-3.5 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-2 h-2 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                            </button>
                        </template>
                    </div>

                    {{-- IS: isian singkat --}}
                    <div x-show="currentQuestion && currentQuestion.type==='IS'" class="flex flex-col gap-1.5">
                        <input type="text" x-model="shortAnswer" @keydown.enter="submitShortAnswer()"
                            placeholder="Ketik jawaban di sini…" autocomplete="off"
                            :disabled="isSubmitted || timesUp"
                            class="w-full px-2.5 py-2 rounded-xl border-2 border-white/70 bg-white/60 text-[10px] font-semibold text-slate-900 outline-none focus:border-blue-400 focus:bg-white/80 disabled:opacity-50 placeholder:text-slate-400">
                        <button @click="submitShortAnswer()" :disabled="!shortAnswer.trim() || isSubmitted || timesUp"
                            class="w-full py-1.5 rounded-xl bg-blue-600/90 text-white text-[10px] font-black disabled:opacity-40 cursor-pointer hover:bg-blue-700 transition-colors">
                            ✓ Kirim Jawaban
                        </button>
                    </div>

                    {{-- Feedback --}}
                    <div x-show="feedbackMsg && !timesUp" x-transition
                        class="text-center text-[9px] font-bold px-2 py-1.5 rounded-lg"
                        :class="feedbackCorrect ? 'bg-green-100/80 border border-green-300 text-green-800' :
                            'bg-rose-100/80 border border-rose-300 text-rose-700'"
                        x-text="feedbackMsg"></div>
                </div>

                {{-- SUBMITTED --}}
                <div x-show="state==='submitted'"
                    class="anim-pop-in flex flex-col items-center justify-center py-4 text-center">
                    <div class="anim-float mb-3">
                        <div
                            class="w-14 h-14 rounded-full flex items-center justify-center mx-auto bg-gradient-to-br from-green-100 to-green-200 border-2 border-green-300">
                            <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-base font-black text-slate-800 mb-1">Quiz Selesai! </h2>
                    <p class="text-[10px] text-slate-400 leading-relaxed mb-2">Semua jawaban terkirim.<br>Tunggu guru
                        menampilkan hasil.</p>
                    <div class="rounded-xl bg-blue-50 border border-blue-100 px-3 py-2">
                        <p class="text-[9px] text-blue-500 font-semibold">Terima kasih sudah mengikuti quiz! </p>
                    </div>
                </div>

            </div>
        </div>

    </div>{{-- end x-data --}}

    <script>
        function guidedPlay() {
            return {
                state: 'waiting',
                currentIndex: -1,
                totalQ: {{ $totalQuestions ?? 0 }},
                currentQuestion: null,
                selectedId: null,
                shortAnswer: '',
                savedAnswers: {},
                feedbackMap: {},
                feedbackMsg: '',
                feedbackCorrect: false,
                timesUp: false,
                timePerQuestion: {{ (int) ($quiz->time_per_question ?? 0) }},
                timeLeft: null,
                _deadline: null,
                showSaved: false,
                quizId: {{ $quiz->id }},
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                showFeedback: {{ $quiz->instant_feedback ? 'true' : 'false' }},
                isSubmitted: {{ isset($attempt) && in_array($attempt->status ?? '', ['submitted', 'timeout']) ? 'true' : 'false' }},
                violationOverlay: false,
                violationMsg: '',
                violationCount: 0,
                _pollInterval: null,
                _statusInterval: null,
                _localTimer: null,
                _fastPollTimer: null,
                _autoSubmitScheduled: false,

                init() {
                    if (this.isSubmitted) {
                        this.state = 'submitted';
                        return;
                    }
                    this.state = 'waiting';
                    this.startPolling();
                    this.startStatusPolling();
                    this._localTimer = setInterval(() => this.tickTimer(), 1000);
                },

                async reportViolation(type, detail) {
                    const key = `_vThrottle_${type}`;
                    const now = Date.now();
                    if (this[key] && now - this[key] < 3000) return;
                    this[key] = now;
                    this.violationCount++;
                    this.violationMsg = type === 'tab_switch' ?
                        `⚠ Kamu berpindah tab! (${this.violationCount} pelanggaran)` :
                        `⚠ Fokus kamu berpindah! (${this.violationCount} pelanggaran)`;
                    this.violationOverlay = true;
                    setTimeout(() => {
                        this.violationOverlay = false;
                    }, 4000);
                    try {
                        const r = await fetch(`/quiz/${this.quizId}/log-violation`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                type,
                                detail
                            }),
                        });
                        const d = await r.json();
                        if (d.auto_submit) {
                            this.isSubmitted = true;
                            this.state = 'submitted';
                            clearInterval(this._pollInterval);
                            clearInterval(this._statusInterval);
                            clearInterval(this._localTimer);
                        }
                    } catch (e) {}
                },

                tickTimer() {
                    if (!this._deadline || this.timePerQuestion <= 0) return;
                    const now = Math.floor(Date.now() / 1000);
                    const left = Math.max(0, this._deadline - now);
                    this.timeLeft = left;
                    if (left <= 0 && !this.timesUp) {
                        this.timesUp = true;
                        this.feedbackMsg = '';
                        if (this._fastPollTimer) clearTimeout(this._fastPollTimer);
                        this._fastPollTimer = setTimeout(() => this.pollQuestion(), 400);
                    }
                },

                startPolling() {
                    this.pollQuestion();
                    this._pollInterval = setInterval(() => this.pollQuestion(), 2000);
                },

                async pollQuestion() {
                    if (this.isSubmitted) return;
                    try {
                        const r = await fetch(`/quiz/${this.quizId}/room/guided/current`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf
                            }
                        });
                        if (!r.ok) return;
                        const d = await r.json();
                        if (d.waiting) {
                            this.state = 'waiting';
                            return;
                        }
                        if (!d.success || !d.question) return;
                        if (d.time_per_question !== undefined && d.time_per_question !== null)
                            this.timePerQuestion = parseInt(d.time_per_question) || 0;
                        const serverDeadline = d.question_deadline ? parseInt(d.question_deadline) : null;
                        if (serverDeadline !== this._deadline) {
                            this._deadline = serverDeadline;
                            if (serverDeadline) {
                                const now = Math.floor(Date.now() / 1000);
                                this.timeLeft = Math.max(0, serverDeadline - now);
                            } else {
                                this.timeLeft = null;
                            }
                        }
                        if (d.current_index !== this.currentIndex) {
                            this.currentIndex = d.current_index;
                            this.currentQuestion = d.question;
                            this.selectedId = this.savedAnswers[d.question.id] ?? null;
                            this.shortAnswer = '';
                            this.feedbackMap = {};
                            this.feedbackMsg = '';
                            this.timesUp = false;
                            this._deadline = serverDeadline;
                            if (serverDeadline) {
                                const now = Math.floor(Date.now() / 1000);
                                this.timeLeft = Math.max(0, serverDeadline - now);
                            } else {
                                this.timeLeft = this.timePerQuestion > 0 ? this.timePerQuestion : null;
                            }
                            this.state = 'question';
                        }
                        if (serverDeadline && Math.floor(Date.now() / 1000) >= serverDeadline) {
                            if (!this.timesUp) {
                                this.timesUp = true;
                                this.timeLeft = 0;
                                this.feedbackMsg = '';
                            }
                        }

                        // ── Auto-submit saat soal terakhir selesai ditampilkan jawaban ──
                        if (d.is_last_question && d.show_answer && !this.isSubmitted) {
                            // Tunggu sebentar agar siswa bisa lihat jawaban sebelum submit
                            if (!this._autoSubmitScheduled) {
                                this._autoSubmitScheduled = true;
                                setTimeout(() => this.submitQuiz(), 3000);
                            }
                        }
                        // ─────────────────────────────────────────────────────────────────

                    } catch (e) {
                        console.warn('pollQuestion error:', e);
                    }
                },

                startStatusPolling() {
                    this._statusInterval = setInterval(() => this.pollStatus(), 8000);
                },

                async pollStatus() {
                    if (this.isSubmitted) return;
                    try {
                        const r = await fetch(`/quiz/${this.quizId}/room/status`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrf
                            }
                        });
                        if (!r.ok) return;
                        const d = await r.json();
                        if (d.participant && d.participant.status === 'submitted') {
                            this.isSubmitted = true;
                            this.state = 'submitted';
                            clearInterval(this._pollInterval);
                            clearInterval(this._statusInterval);
                            clearInterval(this._localTimer);
                        }
                    } catch (e) {
                        console.warn('pollStatus error:', e);
                    }
                },

                async selectChoice(choice) {
                    if (this.isSubmitted || this.timesUp || !this.currentQuestion) return;
                    this.selectedId = choice.id;
                    this.savedAnswers[this.currentQuestion.id] = choice.id;
                    try {
                        const r = await fetch(`/quiz/${this.quizId}/save-progress`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                question_id: this.currentQuestion.id,
                                answer: choice.label,
                                choice_id: choice.id
                            }),
                        });
                        const d = await r.json();
                        if (d.success) {
                            this.flashSaved();
                            if (this.showFeedback && d.is_correct !== undefined) {
                                this.feedbackMap[choice.id] = d.is_correct ? 'correct' : 'wrong';
                                this.feedbackCorrect = d.is_correct;
                                this.feedbackMsg = d.is_correct ? '✓ Jawaban Benar!' :
                                '✗ Kurang tepat, coba yang lain!';
                            }
                        }
                    } catch (e) {
                        this.flashSaved();
                    }
                },

                async submitShortAnswer() {
                    const val = this.shortAnswer.trim();
                    if (!val || this.isSubmitted || this.timesUp || !this.currentQuestion) return;
                    this.savedAnswers[this.currentQuestion.id] = val;
                    try {
                        const r = await fetch(`/quiz/${this.quizId}/save-progress`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                question_id: this.currentQuestion.id,
                                answer: val
                            }),
                        });
                        const d = await r.json();
                        if (d.success) {
                            this.flashSaved();
                            this.feedbackMsg = '✓ Jawaban dikirim!';
                            this.feedbackCorrect = true;
                        }
                    } catch (e) {
                        this.flashSaved();
                    }
                },

                // ── Submit semua jawaban ke server ─────────────────────────────
                async submitQuiz() {
                    if (this.isSubmitted) return;
                    this.isSubmitted = true;
                    this.state = 'submitted';
                    clearInterval(this._pollInterval);
                    clearInterval(this._statusInterval);
                    clearInterval(this._localTimer);

                    // Bangun payload answers dari savedAnswers
                    const answers = Object.entries(this.savedAnswers).map(([questionId, val]) => {
                        // val bisa berupa choice.id (number) atau string teks (IS)
                        if (typeof val === 'number') {
                            return { question_id: parseInt(questionId), choice_id: val, text_answer: null };
                        } else {
                            return { question_id: parseInt(questionId), choice_id: null, text_answer: val };
                        }
                    });

                    try {
                        await fetch(`/quiz/${this.quizId}/submit`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.csrf,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ answers, total_score: 0, time_spent: 0 })
                        });
                        // Halaman submitted sudah tampil — tidak perlu redirect paksa,
                        // biarkan siswa nunggu guru umumkan hasil
                    } catch(e) {
                        console.warn('submitQuiz error:', e);
                    }
                },
                // ──────────────────────────────────────────────────────────────

                flashSaved() {
                    this.showSaved = true;
                    setTimeout(() => {
                        this.showSaved = false;
                    }, 1800);
                },
            };
        }
    </script>

    <script>
        /*
         * Desktop paper overlay — posisi mengikuti outline biru di play-guide.jpg
         * Koordinat dideteksi dari gambar asli 1033×581 px
         */
        (function() {
            // Ukuran gambar play-guide.jpg: 1033 × 581 px
            const IMG = {
                w: 1033,
                h: 581
            };

            /*
             * ┌──────────────────────────────────────────────────────┐
             * │  CARA UBAH POSISI/UKURAN SECTION KERTAS              │
             * │  Semua nilai dalam pixel gambar asli (1033 × 581)    │
             * │                                                       │
             * │  tlx/tly = sudut KIRI ATAS                           │
             * │  trx/try = sudut KANAN ATAS                          │
             * │  blx/bly = sudut KIRI BAWAH                          │
             * │  brx/bry = sudut KANAN BAWAH                         │
             * │                                                       │
             * │  Geser kanan  → tambah  tlx, trx, blx, brx          │
             * │  Geser kiri   → kurangi tlx, trx, blx, brx          │
             * │  Perlebar     → kurangi tlx/blx, tambah trx/brx     │
             * │  Perpanjang   → tambah  bly, bry                    │
             * └──────────────────────────────────────────────────────┘
             */
            const C = {
                tlx: 413, // kiri atas   — X
                tly: 150, // kiri atas   — Y
                trx: 620, // kanan atas  — X
                try: 150, // kanan atas  — Y
                blx: 408, // kiri bawah  — X
                bly: 500, // kiri bawah  — Y
                brx: 629, // kanan bawah — X
                bry: 500 // kanan bawah — Y
            };

            function place() {
                const vw = window.innerWidth,
                    vh = window.innerHeight;
                if (vw < 1024) {
                    const el = document.getElementById('dtop-paper');
                    if (el) el.style.display = 'none';
                    return;
                }
                const ir = IMG.w / IMG.h,
                    vr = vw / vh;
                let sc, ox = 0,
                    oy = 0;
                if (vr > ir) {
                    sc = vw / IMG.w;
                    oy = (vh - IMG.h * sc) / 2;
                } else {
                    sc = vh / IMG.h;
                    ox = (vw - IMG.w * sc) / 2;
                }
                const tl = {
                    x: ox + C.tlx * sc,
                    y: oy + C.tly * sc
                };
                const tr = {
                    x: ox + C.trx * sc,
                    y: oy + C.try * sc
                };
                const bl = {
                    x: ox + C.blx * sc,
                    y: oy + C.bly * sc
                };
                const br = {
                    x: ox + C.brx * sc,
                    y: oy + C.bry * sc
                };
                const L = Math.min(tl.x, bl.x),
                    T = Math.min(tl.y, tr.y),
                    R = Math.max(tr.x, br.x),
                    B = Math.max(bl.y, br.y);
                const W = R - L,
                    H = B - T;

                function pct(v, tot) {
                    return (v / tot * 100).toFixed(3) + '%';
                }
                const clip =
                    `polygon(${pct(tl.x-L,W)} ${pct(tl.y-T,H)},${pct(tr.x-L,W)} ${pct(tr.y-T,H)},${pct(br.x-L,W)} ${pct(br.y-T,H)},${pct(bl.x-L,W)} ${pct(bl.y-T,H)})`;
                const el = document.getElementById('dtop-paper');
                if (!el) return;
                el.style.left = L + 'px';
                el.style.top = T + 'px';
                el.style.width = W + 'px';
                el.style.height = H + 'px';
                el.style.clipPath = clip;
                el.style.display = 'block';
                const topInset = tl.x - L,
                    hPad = Math.ceil(topInset + 12);
                const inn = document.getElementById('dtop-paper-inner');
                if (inn) {
                    inn.style.paddingLeft = hPad + 'px';
                    inn.style.paddingRight = hPad + 'px';
                    inn.style.paddingTop = '10px';
                    inn.style.paddingBottom = '10px';
                    inn.style.gap = '6px';
                }
            }
            window.addEventListener('load', place);
            window.addEventListener('resize', place);
        })();
    </script>

</body>

</html>
