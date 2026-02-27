<!DOCTYPE html>
<html lang="id" x-data="quizApp()" x-init="init()">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/webp" href="{{ asset('image/logo.webp') }}">
    <title>Ujian: {{ $exam->title }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Custom styles - SEMUA STYLE ASLI TETAP ADA */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: #ffffff !important;
            overflow-x: hidden !important;
        }

        /* Error Message */
        .error-message {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 100000;
            padding: 2rem;
        }

        .error-content {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        .error-icon {
            font-size: 3rem;
            color: #dc2626;
            margin-bottom: 1rem;
        }

        .error-message h3 {
            color: #dc2626;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .error-message p {
            color: #4b5563;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .retry-button {
            background: #2563eb;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }

        .retry-button:hover {
            background: #1d4ed8;
        }

        /* Loading Screen */
        .loading-screen {
            position: fixed;
            inset: 0;
            background: white;
            z-index: 99999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .loader {
            border: 4px solid #e8eef7;
            border-top: 4px solid #2563eb;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Question Navigation */
        .question-number-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #dbeafe;
            font-size: 0.875rem;
            background-color: #f0f9ff;
            color: #1e40af;
        }

        .question-number-btn.answered {
            background-color: #10b981;
            color: white;
            border-color: #059669;
        }

        .question-number-btn.marked {
            background-color: #f59e0b;
            color: white;
            border-color: #d97706;
        }

        .question-number-btn.active {
            background-color: #2563eb;
            color: white;
            border-color: #1e40af;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
            transform: scale(1.08);
        }

        /* Timer */
        .timer {
            font-size: 1.125rem;
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            background-color: #eff6ff;
            border: 2px solid #bfdbfe;
            min-width: 130px;
            text-align: center;
            color: #1e40af;
        }

        .timer.warning {
            background-color: #fef3c7;
            color: #92400e;
            border-color: #fcd34d;
        }

        .timer.critical {
            background-color: #fee2e2;
            color: #7f1d1d;
            border-color: #fca5a5;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.8;
            }
        }

        /* Question Container */
        .question-container {
            max-height: 70vh;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .question-container::-webkit-scrollbar {
            width: 6px;
        }

        .question-container::-webkit-scrollbar-track {
            background: #f0f9ff;
        }

        .question-container::-webkit-scrollbar-thumb {
            background: #93c5fd;
            border-radius: 10px;
        }

        /* Options */
        .option-button {
            padding: 1rem;
            border: 2px solid #e0e7ff;
            border-radius: 10px;
            background-color: #ffffff;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            text-align: left;
            margin-bottom: 0.75rem;
        }

        .option-button:hover {
            border-color: #2563eb;
            background-color: #f0f9ff;
            transform: translateY(-2px);
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.05);
        }

        .option-button.selected {
            border-color: #2563eb;
            background-color: #eff6ff;
            box-shadow: 0 4px 8px rgba(37, 99, 235, 0.1);
        }

        /* Violation Warning */
        .violation-warning {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #ef4444;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            z-index: 9999;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
            animation: slideIn 0.3s ease-out;
            border-left: 4px solid #991b1b;
            max-width: 350px;
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

        /* Fullscreen Modal */
        .fullscreen-modal {
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            z-index: 10000;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 1rem;
        }

        .rules-container {
            background: white;
            border-radius: 12px;
            max-width: 800px;
            width: 95%;
            max-height: 85vh;
            overflow-y: auto;
            margin: 1rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .rules-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            padding: 2rem;
        }

        .rules-column {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .rule-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
        }

        .rule-item.warning {
            border-left-color: #ef4444;
            background: #fef2f2;
        }

        .rule-item.warning .rule-icon {
            background: #fecaca;
            color: #dc2626;
        }

        .rule-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            background: #dbeafe;
            color: #1e40af;
        }

        .rule-text h4 {
            margin: 0 0 0.25rem 0;
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
        }

        .rule-text p {
            margin: 0;
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.4;
        }

        /* Violation Counter */
        .violation-count {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #dc2626;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: bold;
            z-index: 9998;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }

        /* Force Exit Modal */
        .force-exit-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 2rem;
        }

        .force-exit-content {
            background: white;
            border-radius: 12px;
            max-width: 400px;
            width: 100%;
            padding: 2rem;
            color: #1e293b;
        }

        /* ============================================================
           SECURITY OVERLAYS (pola dari play_simple_blade)
           ============================================================ */

        #security-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.97);
            z-index: 99998;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 1.5rem;
            text-align: center;
            padding: 2rem;
        }
        #security-overlay.active { display: flex; }
        #security-overlay .overlay-icon { font-size: 4rem; color: #ef4444; }
        #security-overlay h2 { color: white; font-size: 1.5rem; font-weight: 700; }
        #security-overlay p { color: #94a3b8; max-width: 420px; line-height: 1.6; }
        #security-overlay .resume-btn {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
            padding: 0.85rem 2.25rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            box-shadow: 0 8px 24px rgba(37,99,235,0.4);
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 0.5rem;
        }
        #security-overlay .resume-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(37,99,235,0.5);
        }
        #security-overlay .violation-badge {
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.4);
            color: #fca5a5;
            padding: 0.4rem 1rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        #fullscreen-reenter-prompt {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.98);
            z-index: 99997;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 1.5rem;
            text-align: center;
            padding: 2rem;
        }
        #fullscreen-reenter-prompt.active { display: flex; }
        #fullscreen-reenter-prompt h2 { color: white; font-size: 1.75rem; font-weight: 700; }
        #fullscreen-reenter-prompt p { color: #cbd5e1; max-width: 420px; line-height: 1.6; }
        #fullscreen-reenter-prompt .enter-fs-btn {
            background: linear-gradient(135deg, #0066ff, #3b82f6);
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 14px;
            font-size: 1.1rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 8px 24px rgba(0,102,255,0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        #fullscreen-reenter-prompt .enter-fs-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0,102,255,0.5);
        }

        #violation-toast {
            display: none;
            position: fixed;
            top: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 0.9rem 2rem;
            border-radius: 14px;
            font-weight: 600;
            font-size: 0.95rem;
            z-index: 99996;
            box-shadow: 0 8px 32px rgba(239,68,68,0.4);
            animation: toastSlideDown 0.3s ease;
            text-align: center;
        }
        #violation-toast.show { display: block; }

        @keyframes toastSlideDown {
            from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
            to   { opacity: 1; transform: translateX(-50%) translateY(0); }
        }

        /* Modal Overlay */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .timer span {
            font-family: 'Courier New', monospace;
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.05em;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .rules-content {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 1rem;
            }

            .question-container {
                max-height: 55vh;
            }

            .timer {
                font-size: 1rem;
                min-width: 110px;
                padding: 0.6rem 0.75rem;
            }

            .rules-container {
                margin: 0.5rem;
                max-height: 90vh;
            }
        }

        @media (max-width: 480px) {
            .question-number-btn {
                width: 36px;
                height: 36px;
                font-size: 0.75rem;
            }

            .option-button {
                padding: 0.75rem;
                gap: 0.75rem;
            }
        }
    </style>

    <!-- INI YANG PENTING: Definisikan window.quizData SEBELUM Alpine.js dijalankan -->
    <script>
        window.quizData = {
            exam: @json($exam),
            questions: @json($questionsFormatted ?? $questions),
            attempt: @json($attempt),
            securitySettings: @json($securitySettings ?? []),
            markedForReview: @json($markedForReview ?? [])
        };
        console.log('Quiz Data Loaded:', window.quizData);
    </script>
</head>

<body class="exam-mode min-h-screen bg-white"
    @if ($exam->prevent_copy_paste) oncopy="return false" oncut="return false" onpaste="return false" @endif
    @if ($exam->fullscreen_mode) onkeydown="if(event.key === 'F11') event.preventDefault();" @endif>
    <!-- Error Message Container -->
    <div id="errorMessage" class="error-message" style="display: none;">
        <div class="error-content">
            <div class="error-icon">⚠️</div>
            <h3>Terjadi Kesalahan</h3>
            <p id="errorText">Gagal memuat ujian. Silakan refresh halaman.</p>
            <button class="retry-button" onclick="location.reload()">Coba Lagi</button>
        </div>
    </div>

    <!-- ============================================================
         SECURITY OVERLAYS (fullscreen exit / tab switch)
         ============================================================ -->

    <!-- Violation Toast (slide from top) -->
    <div id="violation-toast"></div>

    <!-- Security Overlay (keluar fullscreen / pindah tab) -->
    <div id="security-overlay">
        <div class="overlay-icon">⚠️</div>
        <h2 id="security-overlay-title">Peringatan Keamanan!</h2>
        <p id="security-overlay-msg">Tindakan Anda terdeteksi sebagai pelanggaran.</p>
        <div id="security-violation-badge" class="violation-badge">
            Pelanggaran <span id="security-violation-count">0</span>/<span id="security-violation-limit">{{ $exam->violation_limit ?? 3 }}</span>
        </div>
        <button class="resume-btn" onclick="window.fullscreenHandler && window.fullscreenHandler.resumeFromOverlay()">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"/>
            </svg>
            Kembali & Lanjutkan Ujian
        </button>
    </div>

    <!-- Fullscreen Re-enter Prompt (muncul saat keluar fullscreen — jika fullscreen_mode aktif) -->
    @if ($exam->fullscreen_mode)
    <div id="fullscreen-reenter-prompt">
        <div style="font-size:4rem;">🔒</div>
        <h2>Mode Layar Penuh Diperlukan</h2>
        <p>Anda keluar dari mode layar penuh. Ujian ini memerlukan fullscreen agar dapat dilanjutkan.</p>
        @if (!$exam->disable_violations)
        <div class="violation-badge" id="fs-violation-badge" style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.4);color:#fca5a5;padding:0.4rem 1rem;border-radius:999px;font-size:0.85rem;font-weight:600;">
            Pelanggaran dicatat
        </div>
        @endif
        <button class="enter-fs-btn" onclick="window.fullscreenHandler && window.fullscreenHandler.reEnterFullscreen()">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"/>
            </svg>
            Masuk Kembali ke Fullscreen
        </button>
        <p style="color:#64748b;font-size:0.8rem;margin-top:0.5rem;">Atau tekan <kbd style="background:#1e293b;color:#94a3b8;padding:0.2rem 0.5rem;border-radius:4px;font-family:monospace;">F11</kbd></p>
    </div>
    @endif

    <!-- Violation Counter -->
    <div id="violationCounter" class="violation-count" style="display: none;">
        <svg class="w-4 h-4 inline mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <path
                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
        </svg>
        Pelanggaran: <span
            id="violationCountText">{{ $attempt->violation_count ?? 0 }}</span>/{{ $exam->violation_limit ?? 3 }}
    </div>

    <!-- Loading Screen dengan Tailwind CSS yang benar -->
    <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex items-center justify-center">
        <div class="text-center">
            <!-- Loader dengan Tailwind Animation -->
            <div
                class="w-16 h-16 border-4 border-blue-100 border-t-4 border-t-blue-600 rounded-full animate-spin mx-auto mb-4">
            </div>

            <!-- Text Loading -->
            <p class="text-gray-700 font-medium text-lg mb-2">Memuat soal...</p>
            <p class="text-gray-500 text-sm">
                @if ($exam->fullscreen_mode)
                    Menyiapkan sistem pengawasan fullscreen...
                @else
                    Menyiapkan ujian...
                @endif
            </p>

            <!-- Progress Indicator -->
            <div class="mt-4 w-64 h-1 bg-gray-200 rounded-full overflow-hidden mx-auto">
                <div id="loadingProgress" class="h-full bg-blue-600 rounded-full transition-all duration-300 ease-out"
                    style="width: 0%"></div>
            </div>
        </div>
    </div>

    <!-- Force Exit Modal -->
    <div id="forceExitModal" class="force-exit-modal">
        <div class="force-exit-content">
            <div class="text-center space-y-6">
                <!-- Icon -->
                <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-full">
                    <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.282 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>

                <!-- Title -->
                <div>
                    <h2 class="text-2xl font-bold text-red-900 mb-2">UJIAN DIHENTIKAN</h2>
                    <p class="text-gray-600 text-sm">Batas pelanggaran telah tercapai</p>
                </div>

                <!-- Countdown Timer -->
                <div id="forceSubmitCountdown" class="text-lg font-bold text-red-600">
                    Mengarahkan dalam <span id="countdownTimer">3</span> detik...
                </div>

                <!-- Manual Submit Button -->
                <button id="manualSubmitBtn" onclick="manualForceSubmit()"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                        <polyline points="17 21 17 13 7 13 7 21" />
                        <polyline points="7 3 7 8 15 8" />
                    </svg>
                    Kumpulkan Sekarang
                </button>
            </div>
        </div>
    </div>

    <!-- Fullscreen Modal -->
    @if ($exam->fullscreen_mode)
        <div id="fullscreenModal" class="fullscreen-modal">
            <div class="rules-container">
                <div class="rules-content">
                    <!-- Kolom 1: Peraturan Wajib -->
                    <div class="rules-column">
                        <div class="text-center mb-4">
                            <h2 class="text-2xl font-bold text-blue-900">PERATURAN UJIAN</h2>
                            <p class="text-gray-600 text-sm">Aktifkan mode fullscreen untuk memulai ujian</p>
                        </div>

                        <div class="rule-item">
                            <div class="rule-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                                </svg>
                            </div>
                            <div class="rule-text">
                                <h4>WAJIB FULLSCREEN</h4>
                                <p>Mode layar penuh harus aktif selama ujian</p>
                            </div>
                        </div>

                        <div class="rule-item warning">
                            <div class="rule-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.282 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                            </div>
                            <div class="rule-text">
                                <h4>BATAS PELANGGARAN</h4>
                                <p>Maksimal {{ $exam->violation_limit ?? 3 }} pelanggaran</p>
                            </div>
                        </div>

                        <div class="rule-item">
                            <div class="rule-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="rule-text">
                                <h4>WAKTU UJIAN</h4>
                                <p>Durasi: {{ $exam->duration }} menit</p>
                            </div>
                        </div>

                        @if (!$exam->disable_violations)
                            <div class="rule-item warning">
                                <div class="rule-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                        <polyline points="17 21 17 13 7 13 7 21" />
                                        <polyline points="7 3 7 8 15 8" />
                                    </svg>
                                </div>
                                <div class="rule-text">
                                    <h4>AUTO-SUBMIT</h4>
                                    <p>Ujian akan otomatis dikumpulkan jika melanggar</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Kolom 2: Larangan -->
                    <div class="rules-column">
                        <h3 class="text-lg font-bold text-red-800 mb-3">LARANGAN KETAT</h3>

                        @if ($exam->block_new_tab)
                            <div class="rule-item warning">
                                <div class="rule-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </div>
                                <div class="rule-text">
                                    <h4>PINDAH TAB/WINDOW</h4>
                                    <p>Dilarang membuka tab atau aplikasi lain</p>
                                </div>
                            </div>
                        @endif

                        @if ($exam->prevent_copy_paste)
                            <div class="rule-item warning">
                                <div class="rule-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div class="rule-text">
                                    <h4>COPY-PASTE</h4>
                                    <p>Dilarang menyalin atau menempel teks</p>
                                </div>
                            </div>
                        @endif

                        @if (in_array($exam->security_level, ['strict', 'basic']))
                            <div class="rule-item warning">
                                <div class="rule-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </div>
                                <div class="rule-text">
                                    <h4>KLIK KANAN</h4>
                                    <p>Menu klik kanan dinonaktifkan</p>
                                </div>
                            </div>
                        @endif

                        @if ($exam->require_camera)
                            <div class="rule-item warning">
                                <div class="rule-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="rule-text">
                                    <h4>KAMERA WAJIB</h4>
                                    <p>Kamera harus aktif selama ujian</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="relative px-7 pb-6">
                    <!-- Button Fullscreen -->
                    <button id="enterFullscreenBtn" type="button"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors flex items-center justify-center gap-2 mt-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                        </svg>
                        SETUJU & MULAI UJIAN
                    </button>

                    <p class="text-xs text-gray-500 text-center mt-2">
                        Jika tombol tidak bekerja, tekan <kbd
                            class="bg-gray-100 text-gray-700 px-2 py-1 rounded border border-gray-300 text-xs font-mono">F11</kbd>
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Exam Container -->
    <div class="min-h-screen bg-white">
        <!-- Exam Content -->
        <div class="exam-container" :class="{ 'active': examLoaded }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
                <!-- Header -->
                <div class="bg-white rounded-xl p-4 sm:p-6 mb-6 border-2 border-blue-100 shadow-sm">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex-1">
                            <h1 class="text-2xl font-semibold text-blue-900">{{ $exam->title }}</h1>
                            <p class="text-blue-700 text-sm mt-1">
                                {{ $exam->subject->name_subject ?? 'Mata Pelajaran' }}
                                <span class="text-slate-500">|</span>
                                {{ $exam->getDisplayType() }}
                            </p>
                        </div>

                        <!-- Progress -->
                        <div class="flex-1 md:text-center">
                            <p class="text-xs font-semibold text-blue-700 mb-2 uppercase">Progres</p>
                            <div class="w-full bg-blue-100 rounded-full h-3">
                                <div class="bg-blue-600 h-3 rounded-full transition-all"
                                    :style="`width: ${(answeredCount / totalQuestions) * 100}%`"></div>
                            </div>
                            <p class="text-xs text-blue-700 mt-2 font-medium">
                                <span x-text="answeredCount"></span>/<span x-text="totalQuestions"></span> terjawab
                            </p>
                        </div>

                        <!-- Timer -->
                        <div class="timer"
                            :class="{
                                'warning': timeRemaining < 300 && timeRemaining >= 60,
                                'critical': timeRemaining < 60
                            }">
                            <div class="text-xs font-semibold mb-1">Sisa Waktu</div>
                            <span x-text="formatTime(timeRemaining)" class="text-lg font-mono font-bold"></span>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Question Area -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl p-4 sm:p-8 border-2 border-blue-100 shadow-sm">
                            <!-- Question Number -->
                            <div class="mb-6 pb-4 border-b-2 border-blue-100">
                                <p class="text-sm font-semibold text-blue-700 uppercase">
                                    Soal <span x-text="currentQuestionIndex + 1"></span> dari <span
                                        x-text="totalQuestions"></span>
                                </p>
                            </div>

                            <!-- Question Container -->
                            <div class="question-container mb-6">
                                <!-- Question Text -->
                                <div class="mb-6">
                                    <div class="question-text"
                                        x-html="currentQuestion?.question_text || currentQuestion?.question || 'Tidak ada soal'">
                                    </div>
                                    @if (isset($questions[0]['question_image']))
                                        <template x-if="currentQuestion?.question_image">
                                            <img :src="'/storage/' + currentQuestion.question_image"
                                                class="mt-4 max-w-full rounded-lg border border-gray-200">
                                        </template>
                                    @endif
                                </div>

                                <!-- Answer Options - Support semua 8 tipe soal -->
                                <div class="space-y-2">

                                    <!-- PG & DD: Pilihan Ganda Tunggal -->
                                    <template x-if="(currentQuestion?.type === 'PG' || currentQuestion?.type === 'DD') && currentQuestion?.options">
                                        <div class="space-y-2">
                                            <template x-for="(optionText, optionKey) in currentQuestion.options" :key="optionKey">
                                                <button @click="selectAnswer(optionKey)"
                                                    :class="{
                                                        'border-blue-500 bg-blue-50 shadow-sm': selectedAnswers[currentQuestion?.id] == optionKey,
                                                        'border-gray-200 hover:border-blue-300 hover:bg-slate-50': selectedAnswers[currentQuestion?.id] != optionKey
                                                    }"
                                                    class="w-full text-left px-4 py-3 border-2 rounded-xl transition-all duration-200 flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-full border-2 flex-shrink-0 flex items-center justify-center text-sm font-bold transition-all"
                                                        :class="{
                                                            'border-blue-500 bg-blue-500 text-white': selectedAnswers[currentQuestion?.id] == optionKey,
                                                            'border-gray-300 text-gray-500 bg-gray-50': selectedAnswers[currentQuestion?.id] != optionKey
                                                        }"
                                                        x-text="optionKey">
                                                    </div>
                                                    <span class="flex-1 text-slate-800 text-sm leading-relaxed" x-html="optionText"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- PGK: Pilihan Ganda Kompleks (multi-checkbox) -->
                                    <template x-if="currentQuestion?.type === 'PGK'">
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2 text-xs text-indigo-700 font-semibold bg-indigo-50 border border-indigo-200 px-3 py-2 rounded-lg mb-3">
                                                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                                Pilih semua jawaban yang benar (bisa lebih dari satu)
                                            </div>
                                            <template x-for="(optionText, optionKey) in currentQuestion.options" :key="optionKey">
                                                <button @click="togglePGKAnswer(optionKey)"
                                                    :class="{
                                                        'border-indigo-500 bg-indigo-50 shadow-sm': isPGKSelected(optionKey),
                                                        'border-gray-200 hover:border-indigo-300 hover:bg-indigo-50/40': !isPGKSelected(optionKey)
                                                    }"
                                                    class="w-full text-left px-4 py-3 border-2 rounded-xl transition-all duration-200 flex items-center gap-3">
                                                    <div class="w-6 h-6 rounded border-2 flex-shrink-0 flex items-center justify-center transition-all"
                                                        :class="{
                                                            'border-indigo-500 bg-indigo-500': isPGKSelected(optionKey),
                                                            'border-gray-300 bg-white': !isPGKSelected(optionKey)
                                                        }">
                                                        <svg x-show="isPGKSelected(optionKey)" class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                    </div>
                                                    <span class="font-semibold text-indigo-600 text-sm w-5 flex-shrink-0" x-text="optionKey"></span>
                                                    <span class="flex-1 text-slate-800 text-sm leading-relaxed" x-html="optionText"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- BS: Benar / Salah -->
                                    <template x-if="currentQuestion?.type === 'BS'">
                                        <div class="grid grid-cols-2 gap-4 pt-2">
                                            <button @click="selectAnswer('benar')"
                                                :class="{
                                                    'border-emerald-500 bg-emerald-50 text-emerald-700 shadow-md': selectedAnswers[currentQuestion?.id] === 'benar',
                                                    'border-gray-200 hover:border-emerald-400 hover:bg-emerald-50/50 text-slate-600': selectedAnswers[currentQuestion?.id] !== 'benar'
                                                }"
                                                class="py-8 flex flex-col items-center gap-3 border-2 rounded-2xl font-bold transition-all duration-200">
                                                <div class="w-14 h-14 rounded-full flex items-center justify-center transition-all"
                                                    :class="{
                                                        'bg-emerald-500': selectedAnswers[currentQuestion?.id] === 'benar',
                                                        'bg-gray-100': selectedAnswers[currentQuestion?.id] !== 'benar'
                                                    }">
                                                    <svg class="w-7 h-7 transition-all" :class="{'text-white': selectedAnswers[currentQuestion?.id] === 'benar', 'text-gray-400': selectedAnswers[currentQuestion?.id] !== 'benar'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                </div>
                                                <span class="text-base">Benar</span>
                                            </button>
                                            <button @click="selectAnswer('salah')"
                                                :class="{
                                                    'border-red-500 bg-red-50 text-red-700 shadow-md': selectedAnswers[currentQuestion?.id] === 'salah',
                                                    'border-gray-200 hover:border-red-400 hover:bg-red-50/50 text-slate-600': selectedAnswers[currentQuestion?.id] !== 'salah'
                                                }"
                                                class="py-8 flex flex-col items-center gap-3 border-2 rounded-2xl font-bold transition-all duration-200">
                                                <div class="w-14 h-14 rounded-full flex items-center justify-center transition-all"
                                                    :class="{
                                                        'bg-red-500': selectedAnswers[currentQuestion?.id] === 'salah',
                                                        'bg-gray-100': selectedAnswers[currentQuestion?.id] !== 'salah'
                                                    }">
                                                    <svg class="w-7 h-7 transition-all" :class="{'text-white': selectedAnswers[currentQuestion?.id] === 'salah', 'text-gray-400': selectedAnswers[currentQuestion?.id] !== 'salah'}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </div>
                                                <span class="text-base">Salah</span>
                                            </button>
                                        </div>
                                    </template>

                                    <!-- IS: Isian Singkat -->
                                    <template x-if="currentQuestion?.type === 'IS'">
                                        <div class="p-5 bg-amber-50 border border-amber-200 rounded-xl">
                                            <p class="text-amber-800 text-sm mb-3 font-semibold flex items-center gap-2">
                                                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                                Ketik jawaban dengan tepat
                                            </p>
                                            <input type="text"
                                                :value="selectedAnswers[currentQuestion?.id] || ''"
                                                @input.debounce.300ms="selectAnswer($event.target.value)"
                                                class="w-full px-4 py-3 border-2 border-amber-300 rounded-xl focus:ring-2 focus:ring-amber-400 focus:border-amber-400 text-base bg-white placeholder-gray-400 transition-all"
                                                placeholder="Tulis jawaban di sini...">
                                        </div>
                                    </template>

                                    <!-- ES: Esai -->
                                    <template x-if="currentQuestion?.type === 'ES'">
                                        <div class="p-5 bg-rose-50 border border-rose-200 rounded-xl">
                                            <p class="text-rose-800 text-sm mb-3 font-semibold flex items-center gap-2">
                                                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                                Soal esai — jawaban diperiksa manual oleh guru
                                            </p>
                                            <textarea x-model="essayAnswers[currentQuestion?.id]"
                                                @input.debounce.500ms="saveEssayAnswer(currentQuestion?.id, $event.target.value)"
                                                class="w-full px-4 py-3 border-2 border-rose-300 rounded-xl focus:ring-2 focus:ring-rose-400 focus:border-rose-400 bg-white resize-y min-h-32 text-sm placeholder-gray-400 transition-all"
                                                rows="6" placeholder="Tulis jawaban esai kamu di sini..."></textarea>
                                            <p class="text-xs text-rose-500 mt-1 text-right" x-text="(essayAnswers[currentQuestion?.id] || '').length + ' karakter'"></p>
                                        </div>
                                    </template>

                                    <!-- SK: Skala Linear -->
                                    <template x-if="currentQuestion?.type === 'SK'">
                                        <div class="p-5 bg-teal-50 border border-teal-200 rounded-xl">
                                            <p class="text-teal-800 text-sm mb-4 font-semibold">Pilih nilai pada skala berikut:</p>
                                            <div class="flex items-center justify-center gap-2 flex-wrap py-2">
                                                <template x-for="n in getScaleRange(currentQuestion)" :key="n">
                                                    <button @click="selectAnswer(String(n))"
                                                        :class="{
                                                            'bg-teal-600 text-white border-teal-600 shadow-lg scale-110': selectedAnswers[currentQuestion?.id] == String(n),
                                                            'bg-white border-teal-300 text-teal-700 hover:bg-teal-100': selectedAnswers[currentQuestion?.id] != String(n)
                                                        }"
                                                        class="w-12 h-12 rounded-xl border-2 font-bold text-lg transition-all duration-200"
                                                        x-text="n">
                                                    </button>
                                                </template>
                                            </div>
                                            <div class="flex justify-between mt-3 px-1 text-xs text-teal-600 font-medium">
                                                <span x-text="(currentQuestion?.scale_min || 1) + (currentQuestion?.scale_min_label ? ' — ' + currentQuestion.scale_min_label : '')"></span>
                                                <span x-text="(currentQuestion?.scale_max || 5) + (currentQuestion?.scale_max_label ? ' — ' + currentQuestion.scale_max_label : '')"></span>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- MJ: Menjodohkan -->
                                    <template x-if="currentQuestion?.type === 'MJ'">
                                        <div class="p-5 bg-orange-50 border border-orange-200 rounded-xl">
                                            <p class="text-orange-800 text-sm mb-4 font-semibold flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                                Pasangkan item kiri dengan item kanan yang sesuai
                                            </p>
                                            <div class="space-y-3">
                                                <template x-for="(pair, idx) in (currentQuestion?.pairs || [])" :key="idx">
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex-1 px-3 py-2.5 bg-white border-2 border-orange-200 rounded-lg text-sm font-medium text-slate-700 min-w-0" x-text="pair.left"></div>
                                                        <svg class="w-5 h-5 text-orange-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                        <select
                                                            :value="getMJAnswer(idx)"
                                                            @change="setMJAnswer(idx, $event.target.value)"
                                                            class="flex-1 px-3 py-2.5 border-2 border-orange-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-400 focus:border-orange-400 bg-white transition-all min-w-0">
                                                            <option value="">— pilih —</option>
                                                            <template x-for="opt in (currentQuestion?.mj_options || [])" :key="opt">
                                                                <option :value="opt" x-text="opt"></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- No Question Found -->
                                    <template x-if="!currentQuestion">
                                        <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                            <p class="text-red-800">Soal tidak ditemukan atau terjadi kesalahan.</p>
                                        </div>
                                    </template>

                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2 flex-wrap pt-4">
                                <button @click="markForReview()"
                                    class="px-4 py-2 border-2 border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50 transition-colors text-sm font-medium flex items-center gap-2">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                        <polyline points="17 21 17 13 7 13 7 21" />
                                        <polyline points="7 3 7 8 15 8" />
                                    </svg>
                                    <span
                                        x-text="markedForReview.has(currentQuestion?.id) ? 'Hapus Tanda' : 'Tandai Review'"></span>
                                </button>
                                <button @click="clearAnswer()"
                                    class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium flex items-center gap-2">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 4 21 4 23 6 23 20a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V6" />
                                        <line x1="10" y1="11" x2="10" y2="17" />
                                        <line x1="14" y1="11" x2="14" y2="17" />
                                    </svg>
                                    Hapus Jawaban
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="lg:col-span-1 space-y-6">
                        <!-- Question Navigator -->
                        <div class="bg-white rounded-xl p-4 border-2 border-blue-100 shadow-sm">
                            <h3 class="text-sm font-semibold text-blue-900 mb-4 flex items-center gap-2">
                                Navigasi Soal
                            </h3>
                            <div class="grid grid-cols-5 gap-2">
                                <template x-for="(q, index) in questions" :key="q.id">
                                    <button @click="goToQuestion(index)" :class="getQuestionButtonClass(index)"
                                        class="question-number-btn" x-text="index + 1"
                                        :title="'Soal ' + (index + 1)"></button>
                                </template>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-white rounded-xl p-4 border-2 border-blue-100 shadow-sm">
                            <h4 class="font-semibold text-blue-900 mb-3 text-sm flex items-center gap-2">
                                Keterangan Status
                            </h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 bg-blue-100 border-2 border-blue-300 rounded"></div>
                                    <span class="text-slate-700">Belum dijawab</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 bg-emerald-500 border-2 border-emerald-600 rounded"></div>
                                    <span class="text-slate-700">Sudah dijawab</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 bg-amber-500 border-2 border-amber-600 rounded"></div>
                                    <span class="text-slate-700">Ditandai review</span>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex gap-3">
                            <button @click="previousQuestion()" :disabled="currentQuestionIndex === 0"
                                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="19" y1="12" x2="5" y2="12" />
                                    <polyline points="12 19 5 12 12 5" />
                                </svg>
                                <span class="hidden sm:inline">Sebelumnya</span>
                            </button>
                            <button @click="nextQuestion()" :disabled="currentQuestionIndex === totalQuestions - 1"
                                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                                <span class="hidden sm:inline">Selanjutnya</span>
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                    <polyline points="12 5 19 12 12 19" />
                                </svg>
                            </button>
                        </div>

                        <!-- Submit Button -->
                        <button @click="showSubmitModal = true"
                            class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                <polyline points="17 21 17 13 7 13 7 21" />
                                <polyline points="7 3 7 8 15 8" />
                            </svg>
                            <span>Kumpulkan Jawaban</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Modal -->
        <template x-if="showSubmitModal">
            <div class="modal-overlay">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-blue-900">Konfirmasi Pengumpulan</h3>
                        <button @click="showSubmitModal = false"
                            class="text-gray-400 hover:text-gray-600 text-2xl font-light">
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>

                    <div class="bg-blue-50 border-2 border-blue-100 rounded-lg p-4 mb-6">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="text-center">
                                <p class="text-xs text-blue-700 font-semibold mb-1">Soal Dijawab</p>
                                <p class="text-2xl font-bold text-blue-900" x-text="answeredCount"></p>
                                <p class="text-xs text-blue-600">dari <span x-text="totalQuestions"></span> soal</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-blue-700 font-semibold mb-1">Ditandai Review</p>
                                <p class="text-2xl font-bold text-amber-600" x-text="markedForReviewCount"></p>
                                <p class="text-xs text-blue-600">perlu diperiksa</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <p class="text-slate-700 mb-4 text-sm">
                            Apakah Anda yakin ingin mengumpulkan jawaban Anda sekarang?
                            <span class="font-semibold text-slate-800">Anda tidak dapat mengubah jawaban setelah
                                dikumpulkan.</span>
                        </p>

                        <div x-show="answeredCount < totalQuestions"
                            class="p-3 bg-orange-50 border-l-4 border-orange-500 rounded-lg">
                            <div class="flex items-center gap-2 text-orange-800 text-sm font-medium">
                                <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z" />
                                </svg>
                                <span><span class="font-bold" x-text="totalQuestions - answeredCount"></span> soal
                                    belum
                                    terjawab</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button @click="showSubmitModal = false"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded-lg transition-colors">
                            Lanjutkan
                        </button>
                        <form action="{{ route('soal.submit', $exam->id) }}" method="POST" class="flex-1"
                            id="submitForm">
                            @csrf
                            <input type="hidden" name="answers"
                                x-bind:value="JSON.stringify({ ...selectedAnswers, ...essayAnswers })">
                            @if ($exam->show_result_after === 'immediately' || $exam->show_result_after === 'after_submit')
                                <input type="hidden" name="show_result" value="1">
                            @endif
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                    <polyline points="17 21 17 13 7 13 7 21" />
                                    <polyline points="7 3 7 8 15 8" />
                                </svg>
                                Kumpulkan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
        // ==============================
        // GLOBAL HELPER FUNCTIONS - DIPERBAIKI
        // ==============================
        function showError(message) {
            console.error('[Error]', message);

            const errorDiv = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');

            if (errorDiv && errorText) {
                errorText.textContent = message;
                errorDiv.style.display = 'block';
            }

            hideLoadingScreen();
        }

        function hideLoadingScreen() {
            const ls = document.getElementById('loadingScreen');
            if (ls) {
                ls.style.opacity = '0';
                setTimeout(() => {
                    ls.style.display = 'none';
                }, 300);
            }
        }

        function manualForceSubmit() {
            console.log('[Manual Submit] Button clicked');
            if (window.fullscreenHandler && typeof window.fullscreenHandler.forceSubmitExam === 'function') {
                // Clear any existing countdown
                if (window.fullscreenHandler.countdownInterval) {
                    clearInterval(window.fullscreenHandler.countdownInterval);
                }
                window.fullscreenHandler.forceSubmitExam();
            } else {
                console.error('[Manual Submit] FullscreenHandler not available, trying form submit');
                // Fallback: try to submit form directly
                const form = document.getElementById('submitForm');
                if (form) {
                    form.submit();
                } else {
                    // Last resort: redirect to exam index
                    window.location.href = "{{ route('soal.index') }}";
                }
            }
        }

        function safeInit() {
            try {
                // Cek data exam - SEKARANG MENGGUNAKAN window.quizData
                if (!window.quizData || !window.quizData.exam) {
                    throw new Error('Data ujian tidak ditemukan');
                }

                // Cek pertanyaan
                if (!Array.isArray(window.quizData.questions) || window.quizData.questions.length === 0) {
                    console.warn('Tidak ada pertanyaan ditemukan atau array kosong');
                }

                return true;
            } catch (error) {
                console.error('[SafeInit] Error:', error);
                showError(error.message);
                return false;
            }
        }

        // ==============================
        // SECURITY: COPY-PASTE PREVENTION
        // ==============================
        @if ($exam->prevent_copy_paste)
            document.addEventListener('copy', function(e) {
                e.preventDefault();
                return false;
            });

            document.addEventListener('cut', function(e) {
                e.preventDefault();
                return false;
            });

            document.addEventListener('paste', function(e) {
                e.preventDefault();
                return false;
            });

            document.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                return false;
            });

            // Disable right-click
            document.oncontextmenu = function() {
                return false;
            };

            // Disable keyboard shortcuts for copy/cut
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'x' || e.key === 'v')) {
                    e.preventDefault();
                    return false;
                }
            });
        @endif

        // ==============================
        // FULLSCREEN HANDLER - DIPERBAIKI
        // ==============================
        class FullscreenHandler {
            constructor() {
                console.log('[FullscreenHandler] Initializing...');

                // Gunakan data dari window.quizData
                const quizData = window.quizData || {};
                const exam = quizData.exam || {};

                this.settings = {
                    requireFullscreen: {{ $exam->fullscreen_mode ? 'true' : 'false' }},
                    securityLevel: '{{ $exam->security_level ?? 'none' }}',
                    violationLimit: parseInt("{{ $exam->violation_limit ?? 3 }}"),
                    disableViolations: {{ $exam->disable_violations ? 'true' : 'false' }},
                    blockNewTab: {{ $exam->block_new_tab ? 'true' : 'false' }},
                    preventCopyPaste: {{ $exam->prevent_copy_paste ? 'true' : 'false' }},
                    autoSubmitOnViolation: {{ !$exam->disable_violations ? 'true' : 'false' }}
                };

                this.examId = {{ $exam->id }};
                this.attemptId = {{ $attempt->id ?? 0 }};
                this.isFullscreen = false;
                this.violationCount = parseInt("{{ $attempt->violation_count ?? 0 }}");
                this.isSubmitting = false;
                this.forceExitModalShown = false;
                this.examStarted = false;
                this.countdownInterval = null;
                this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                // Setup listeners immediately
                this.setupBasicListeners();

                // Start exam based on settings
                if (!this.settings.requireFullscreen) {
                    this.startExam();
                } else {
                    // Show rules modal
                    setTimeout(() => {
                        this.showRulesModal();
                    }, 500);
                }
            }

            showRulesModal() {
                const modal = document.getElementById('fullscreenModal');
                if (modal) {
                    modal.style.display = 'flex';

                    const btn = document.getElementById('enterFullscreenBtn');
                    if (btn) {
                        btn.onclick = () => this.requestFullscreen();
                    }
                }
            }

            hideRulesModal() {
                const modal = document.getElementById('fullscreenModal');
                if (modal) modal.style.display = 'none';
            }

            hideLoadingScreen() {
                const ls = document.getElementById('loadingScreen');
                if (ls) {
                    ls.style.opacity = '0';
                    setTimeout(() => {
                        ls.style.display = 'none';
                    }, 300);
                }
            }

            async requestFullscreen() {
                console.log('[FullscreenHandler] Requesting fullscreen...');
                const elem = document.documentElement;

                try {
                    if (elem.requestFullscreen) {
                        await elem.requestFullscreen();
                    } else if (elem.webkitRequestFullscreen) {
                        await elem.webkitRequestFullscreen();
                    } else if (elem.mozRequestFullScreen) {
                        await elem.mozRequestFullScreen();
                    } else if (elem.msRequestFullscreen) {
                        await elem.msRequestFullscreen();
                    }

                    this.isFullscreen = true;
                    this.hideRulesModal();
                    this.startExam();
                    return true;
                } catch (err) {
                    console.error('[FullscreenHandler] Error enabling fullscreen:', err);
                    // Fallback if fullscreen fails
                    alert('Fullscreen gagal diaktifkan. Anda dapat melanjutkan ujian tanpa fullscreen.');
                    this.startExam();
                    return false;
                }
            }

            // ================================================================
            // SETUP LISTENERS
            // ================================================================
            setupBasicListeners() {
                // Listen fullscreen change dari semua browser
                const fullscreenEvents = [
                    'fullscreenchange',
                    'webkitfullscreenchange',
                    'mozfullscreenchange',
                    'MSFullscreenChange'
                ];
                fullscreenEvents.forEach(event => {
                    document.addEventListener(event, () => this.handleFullscreenChange());
                });

                // Cegah ESC menutup fullscreen saat ujian berlangsung
                if (this.settings.requireFullscreen) {
                    document.addEventListener('keydown', (e) => {
                        if (this.examStarted && !this.isSubmitting && e.key === 'Escape') {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                    }, true);
                }

                this.setupViolationListeners();
            }

            setupViolationListeners() {
                if (this.settings.disableViolations) return;

                // ── Tab / window switch (blockNewTab) ──────────────────────────
                if (this.settings.blockNewTab) {
                    document.addEventListener('visibilitychange', () => {
                        if (!this.examStarted || this.isSubmitting) return;
                        if (document.hidden) {
                            this.handleTabSwitch('Pindah ke tab/window lain');
                        } else {
                            // Kembali ke tab → tutup overlay
                            this.hideSecurityOverlay();
                        }
                    });

                    window.addEventListener('blur', () => {
                        if (!this.examStarted || this.isSubmitting) return;
                        // blur bisa terpicu saat dialog browser — debounce 300ms
                        clearTimeout(this._blurTimeout);
                        this._blurTimeout = setTimeout(() => {
                            if (!document.hidden) {
                                this.handleTabSwitch('Beralih ke aplikasi/window lain');
                            }
                        }, 300);
                    });

                    window.addEventListener('focus', () => {
                        clearTimeout(this._blurTimeout);
                        // Jika security overlay masih muncul karena blur, tutup
                        if (!document.hidden) this.hideSecurityOverlay();
                    });

                    // Blokir Ctrl+T/N/W
                    document.addEventListener('keydown', (e) => {
                        if (!this.examStarted || this.isSubmitting) return;
                        if ((e.ctrlKey || e.metaKey) && ['n', 't', 'w'].includes(e.key.toLowerCase())) {
                            e.preventDefault();
                            this.showViolationToast('⛔ Membuka tab/jendela baru tidak diizinkan!');
                            this.logViolation('Mencoba buka tab/window baru');
                        }
                        if (e.key === 'F5' || ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'r')) {
                            e.preventDefault();
                            this.showViolationToast('⛔ Refresh halaman tidak diizinkan!');
                        }
                    });

                    document.addEventListener('contextmenu', (e) => {
                        if (this.examStarted && !this.isSubmitting) e.preventDefault();
                    });
                }

                // ── Copy-paste prevention ──────────────────────────────────────
                if (this.settings.preventCopyPaste) {
                    ['copy', 'paste', 'cut'].forEach(event => {
                        document.addEventListener(event, (e) => {
                            if (!this.examStarted || this.isSubmitting) return;
                            e.preventDefault();
                            this.logViolation(`Mencoba ${event} teks`);
                            this.showViolationToast(`⛔ ${event.charAt(0).toUpperCase()+event.slice(1)} tidak diizinkan!`);
                        });
                    });
                    document.addEventListener('keydown', (e) => {
                        if (!this.examStarted || this.isSubmitting) return;
                        if ((e.ctrlKey || e.metaKey) && ['c', 'v', 'x', 'a'].includes(e.key.toLowerCase())) {
                            e.preventDefault();
                            this.showViolationToast('⛔ Copy/paste tidak diizinkan!');
                            if (['c', 'v', 'x'].includes(e.key.toLowerCase()))
                                this.logViolation('Shortcut copy/paste');
                        }
                    });
                }

                // ── Right click (strict/basic security) ──────────────────────
                if (this.settings.securityLevel === 'strict' || this.settings.securityLevel === 'basic') {
                    document.addEventListener('contextmenu', (e) => {
                        if (!this.examStarted || this.isSubmitting) return;
                        e.preventDefault();
                        this.logViolation('Menggunakan klik kanan');
                    });
                }

                // ── DevTools block ─────────────────────────────────────────────
                document.addEventListener('keydown', (e) => {
                    if (!this.examStarted || this.isSubmitting) return;
                    if (e.key === 'F12' || ((e.ctrlKey || e.metaKey) && e.shiftKey && ['i','j','c'].includes(e.key.toLowerCase()))) {
                        e.preventDefault();
                        this.showViolationToast('⛔ Developer tools diblokir!');
                    }
                });
            }

            // ================================================================
            // FULLSCREEN CHANGE HANDLER — inti logika overlay baru
            // ================================================================
            handleFullscreenChange() {
                if (!this.examStarted || this.isSubmitting) return;

                const wasFullscreen = this.isFullscreen;
                this.isFullscreen = !!(
                    document.fullscreenElement ||
                    document.webkitFullscreenElement ||
                    document.mozFullScreenElement ||
                    document.msFullscreenElement
                );

                if (this.settings.requireFullscreen) {
                    if (wasFullscreen && !this.isFullscreen) {
                        // Keluar dari fullscreen → tampilkan fullscreen re-enter prompt
                        this.onFullscreenExited();
                    } else if (!wasFullscreen && this.isFullscreen) {
                        // Kembali ke fullscreen → tutup prompt
                        this.hideFullscreenReenterPrompt();
                        this.hideSecurityOverlay();
                    }
                }
            }

            // Dipanggil saat pengguna keluar fullscreen
            onFullscreenExited() {
                console.log('[FullscreenHandler] User exited fullscreen');
                if (!this.settings.disableViolations) {
                    this.logViolation('Keluar dari mode fullscreen', false); // false = jangan tampilkan security overlay umum
                }
                this.showFullscreenReenterPrompt();
            }

            // Dipanggil saat pengguna pindah tab / window blur
            handleTabSwitch(reason) {
                if (!this.examStarted || this.isSubmitting) return;
                this.logViolation(reason, true); // true = tampilkan security overlay
            }

            // ================================================================
            // OVERLAY CONTROLS
            // ================================================================

            /** Tampilkan overlay security (tab switch / pelanggaran umum) */
            showSecurityOverlay(title, msg) {
                document.getElementById('security-overlay-title').textContent = title;
                document.getElementById('security-overlay-msg').textContent   = msg;

                const badge = document.getElementById('security-violation-badge');
                const countEl = document.getElementById('security-violation-count');
                if (countEl) countEl.textContent = this.violationCount;
                if (badge) badge.style.display = 'block';

                document.getElementById('security-overlay').classList.add('active');
            }

            hideSecurityOverlay() {
                const el = document.getElementById('security-overlay');
                if (el) el.classList.remove('active');
            }

            /** Tampilkan prompt re-enter fullscreen */
            showFullscreenReenterPrompt() {
                const el = document.getElementById('fullscreen-reenter-prompt');
                if (el) el.classList.add('active');
            }

            hideFullscreenReenterPrompt() {
                const el = document.getElementById('fullscreen-reenter-prompt');
                if (el) el.classList.remove('active');
            }

            /** Tombol "Kembali & Lanjutkan" pada #security-overlay */
            resumeFromOverlay() {
                this.hideSecurityOverlay();
                // Jika fullscreen mode aktif dan saat ini tidak fullscreen, minta lagi
                if (this.settings.requireFullscreen && !this.isFullscreen) {
                    this.reEnterFullscreen();
                }
            }

            /** Tombol "Masuk Kembali ke Fullscreen" pada #fullscreen-reenter-prompt */
            async reEnterFullscreen() {
                console.log('[FullscreenHandler] Re-entering fullscreen...');
                const elem = document.documentElement;
                try {
                    const promise = elem.requestFullscreen?.() ||
                        elem.webkitRequestFullscreen?.() ||
                        elem.mozRequestFullScreen?.() ||
                        elem.msRequestFullscreen?.();
                    if (promise) await promise;
                    this.isFullscreen = true;
                    this.hideFullscreenReenterPrompt();
                    this.hideSecurityOverlay();
                } catch (err) {
                    console.warn('[FullscreenHandler] Re-enter fullscreen failed:', err);
                    this.showViolationToast('⚠️ Gagal masuk fullscreen. Coba tekan F11.');
                }
            }

            /** Violation toast — slide from top */
            showViolationToast(msg) {
                const toast = document.getElementById('violation-toast');
                if (!toast) return;
                toast.textContent = msg;
                toast.classList.add('show');
                clearTimeout(this._toastTimeout);
                this._toastTimeout = setTimeout(() => toast.classList.remove('show'), 3000);
            }

            // ================================================================
            // START EXAM
            // ================================================================
            startExam() {
                console.log('[FullscreenHandler] Starting exam...');
                this.examStarted = true;
                this.hideRulesModal();
                this.hideLoadingScreen();

                // Start AlpineJS timer
                if (window.quizAppInstance) {
                    window.quizAppInstance.examLoaded = true;
                    if (window.quizAppInstance.startTimer) {
                        window.quizAppInstance.startTimer();
                    }
                }

                // Show exam container
                const examContainer = document.querySelector('.exam-container');
                if (examContainer) examContainer.classList.add('active');

                // Show violation counter if there are existing violations
                if (this.violationCount > 0) this.updateViolationDisplay();

                console.log('[FullscreenHandler] Exam started successfully');
            }

            // ================================================================
            // VIOLATION LOGGING
            // ================================================================
            /**
             * @param {string} reason       - pesan pelanggaran
             * @param {boolean} showOverlay - tampilkan security overlay (default: true)
             */
            logViolation(reason, showOverlay = true) {
                if (this.isSubmitting || !this.examStarted || this.settings.disableViolations) return;

                this.violationCount++;
                this.updateViolationDisplay();
                console.log(`[Violation] ${reason} (count: ${this.violationCount}/${this.settings.violationLimit})`);

                // Send to server
                this.sendViolationToServer(reason);

                // Tampilkan toast ringkas
                this.showViolationToast(`⚠️ Pelanggaran ${this.violationCount}/${this.settings.violationLimit}: ${reason}`);

                // Tampilkan security overlay jika diminta
                if (showOverlay) {
                    this.showSecurityOverlay(
                        `⚠️ Pelanggaran #${this.violationCount}!`,
                        `${reason}. Tekan tombol di bawah untuk kembali melanjutkan ujian.`
                    );
                }

                // Cek batas pelanggaran
                if (this.violationCount >= this.settings.violationLimit && this.settings.autoSubmitOnViolation) {
                    this.handleMaxViolations();
                }
            }

            updateViolationDisplay() {
                const el = document.getElementById('violationCountText');
                if (el) el.textContent = this.violationCount;

                const secCount = document.getElementById('security-violation-count');
                if (secCount) secCount.textContent = this.violationCount;

                // Show violation counter badge
                const violationCounter = document.getElementById('violationCounter');
                if (violationCounter) violationCounter.style.display = 'flex';
            }

            showViolationWarning(message) {
                // Digantikan oleh showViolationToast + showSecurityOverlay
                this.showViolationToast(message);
            }

            showForceExitModal() {
                const modal = document.getElementById('forceExitModal');
                if (modal) {
                    modal.style.display = 'flex';

                    // Start countdown
                    let countdown = 3;
                    const countdownElement = document.getElementById('countdownTimer');
                    const countdownInterval = setInterval(() => {
                        countdown--;
                        if (countdownElement) {
                            countdownElement.textContent = countdown;
                        }
                        if (countdown <= 0) {
                            clearInterval(countdownInterval);
                            this.forceSubmitExam();
                        }
                    }, 1000);

                    // Store interval for cleanup
                    this.countdownInterval = countdownInterval;
                } else {
                    // If modal doesn't exist, submit immediately
                    console.log('[FullscreenHandler] Modal not found, submitting immediately');
                    this.forceSubmitExam();
                }
            }

            async sendViolationToServer(type) {
                if (!this.csrfToken || !this.examStarted || this.settings.disableViolations) return;

                try {
                    const response = await fetch(`/soal/{{ $exam->id }}/violation`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            type: type,
                            count: this.violationCount,
                            timestamp: new Date().toISOString(),
                            attempt_id: this.attemptId
                        })
                    });

                    const data = await response.json();
                    console.log('Violation sent to server:', data);

                    return data;
                } catch (e) {
                    console.error('Error sending violation:', e);
                    return null;
                }
            }

            async forceSubmitExam() {
                if (this.isSubmitting && this.submitAttempted) {
                    console.log('[FullscreenHandler] Already submitting, preventing duplicate');
                    return;
                }

                this.isSubmitting = true;
                this.submitAttempted = true;
                console.log('[FullscreenHandler] Force submitting exam...');

                try {
                    // Collect answers from quiz app
                    let allAnswers = {};
                    if (window.quizAppInstance) {
                        allAnswers = {
                            ...window.quizAppInstance.selectedAnswers,
                            ...window.quizAppInstance.essayAnswers
                        };
                        console.log('[FullscreenHandler] Collected answers:', Object.keys(allAnswers).length);
                    }

                    // Method 1: Try existing submit form first
                    const existingForm = document.getElementById('submitForm');
                    if (existingForm) {
                        console.log('[FullscreenHandler] Using existing submit form');

                        // Add violation data to existing form
                        const violationInput = document.createElement('input');
                        violationInput.type = 'hidden';
                        violationInput.name = 'force_submit_violation';
                        violationInput.value = 'true';
                        existingForm.appendChild(violationInput);

                        const countInput = document.createElement('input');
                        countInput.type = 'hidden';
                        countInput.name = 'violation_count';
                        countInput.value = this.violationCount;
                        existingForm.appendChild(countInput);

                        // Add attempt_id if not already present
                        const attemptInput = document.createElement('input');
                        attemptInput.type = 'hidden';
                        attemptInput.name = 'attempt_id';
                        attemptInput.value = this.attemptId;
                        existingForm.appendChild(attemptInput);

                        existingForm.submit();
                        return;
                    }

                    // Method 2: Create new form for submission
                    console.log('[FullscreenHandler] Creating new submit form');
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ route('soal.submit', $exam->id) }}`;
                    form.style.display = 'none';

                    // Add CSRF token
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = this.csrfToken;
                    form.appendChild(csrfInput);

                    // Add answers
                    const answersInput = document.createElement('input');
                    answersInput.type = 'hidden';
                    answersInput.name = 'answers';
                    answersInput.value = JSON.stringify(allAnswers);
                    form.appendChild(answersInput);

                    // Add violation flag
                    const violationInput = document.createElement('input');
                    violationInput.type = 'hidden';
                    violationInput.name = 'force_submit_violation';
                    violationInput.value = 'true';
                    form.appendChild(violationInput);

                    // Add violation count
                    const countInput = document.createElement('input');
                    countInput.type = 'hidden';
                    countInput.name = 'violation_count';
                    countInput.value = this.violationCount;
                    form.appendChild(countInput);

                    // Add attempt_id
                    const attemptInput = document.createElement('input');
                    attemptInput.type = 'hidden';
                    attemptInput.name = 'attempt_id';
                    attemptInput.value = this.attemptId;
                    form.appendChild(attemptInput);

                    // Add show_result flag based on exam settings
                    const showResultInput = document.createElement('input');
                    showResultInput.type = 'hidden';
                    showResultInput.name = 'show_result';
                    showResultInput.value =
                        "{{ $exam->show_result_after === 'immediately' || $exam->show_result_after === 'after_submit' ? '1' : '0' }}";
                    form.appendChild(showResultInput);

                    document.body.appendChild(form);
                    console.log('[FullscreenHandler] Submitting form...');
                    form.submit();

                } catch (error) {
                    console.error('[FullscreenHandler] Error in forceSubmitExam:', error);

                    // Method 3: Fallback - try fetch API
                    try {
                        console.log('[FullscreenHandler] Trying fetch API as fallback');
                        let allAnswers = {};
                        if (window.quizAppInstance) {
                            allAnswers = {
                                ...window.quizAppInstance.selectedAnswers,
                                ...window.quizAppInstance.essayAnswers
                            };
                        }

                        const response = await fetch(`{{ route('soal.force-submit-violation', $exam->id) }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                answers: allAnswers,
                                violation_count: this.violationCount,
                                attempt_id: this.attemptId
                            })
                        });

                        if (response.ok) {
                            const data = await response.json();
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                window.location.href = "{{ route('soal.index') }}";
                            }
                        } else {
                            throw new Error('Fetch submission failed');
                        }
                    } catch (fetchError) {
                        console.error('[FullscreenHandler] Fetch API also failed:', fetchError);

                        // Method 4: Final fallback - redirect to index
                        console.log('[FullscreenHandler] Final fallback - redirecting to index');
                        setTimeout(() => {
                            window.location.href = "{{ route('soal.index') }}";
                        }, 1000);
                    }
                }
            }

            handleMaxViolations() {
                if (this.forceExitModalShown || !this.examStarted || this.settings.disableViolations) return;

                console.log('[FullscreenHandler] Handling max violations...');
                this.isSubmitting = true;
                this.forceExitModalShown = true;

                // Check if auto-submit is enabled
                if (this.settings.autoSubmitOnViolation) {
                    this.showForceExitModal();
                } else {
                    // Just show warning if auto-submit is disabled
                    this.showViolationWarning(
                        `Batas pelanggaran tercapai (${this.violationCount}/${this.settings.violationLimit}). Ujian akan tetap berjalan.`
                    );
                }
            }
        }

        // ==============================
        // ENHANCED QUIZ APP WITH SHUFFLING - DIPERBAIKI
        // ==============================
        function quizApp() {
            return {
                questions: window.quizData.questions || [],
                currentQuestionIndex: 0,
                selectedAnswers: {},
                essayAnswers: {},
                markedForReview: new Set(),
                showSubmitModal: false,
                timeRemaining: Math.floor({{ $timeRemaining ?? ($attempt->getTimeRemaining() ?? 0) }}),
                timerInterval: null,
                examLoaded: false,

                init() {
                    console.log('[QuizApp] Initializing...');
                    console.log('[QuizApp] Questions loaded:', this.questions.length);

                    try {
                        // Validasi dulu
                        if (!safeInit()) {
                            return;
                        }

                        window.quizAppInstance = this;

                        // ========================================
                        // SHUFFLE QUESTIONS IF ENABLED
                        // ========================================
                        @if ($exam->shuffle_question)
                            console.log('[QuizApp] Shuffling questions...');
                            this.questions = this.shuffleArray(this.questions);
                        @endif

                        // ========================================
                        // SHUFFLE ANSWERS IF ENABLED (for PG only)
                        // ========================================
                        @if ($exam->shuffle_answer)
                            console.log('[QuizApp] Shuffling answers...');
                            this.questions = this.questions.map(q => {
                                if (['PG', 'DD', 'PGK'].includes(q.type) && q.options && typeof q.options === 'object') {
                                    const entries = Object.entries(q.options);
                                    const shuffled = this.shuffleArray(entries);
                                    q.options = Object.fromEntries(shuffled);
                                }
                                return q;
                            });
                        @endif

                        // Load saved data from localStorage
                        this.loadSavedData();

                        // Initialize FullscreenHandler
                        setTimeout(() => {
                            try {
                                window.fullscreenHandler = new FullscreenHandler();
                            } catch (error) {
                                console.error('[QuizApp] Error initializing FullscreenHandler:', error);
                                this.handleCriticalError(error);
                            }
                        }, 1000);

                        // Hide loading screen after timeout
                        setTimeout(() => {
                            this.hideLoadingScreen();
                        }, 2000);

                        // Start timer
                        this.startTimer();
                    } catch (error) {
                        console.error('[QuizApp] Critical error in init:', error);
                        this.handleCriticalError(error);
                    }
                },

                handleCriticalError(error) {
                    const errorMsg = `Gagal memuat ujian: ${error.message}`;
                    console.error('[QuizApp] Critical error:', errorMsg);

                    // Show error secara langsung
                    const errorDiv = document.getElementById('errorMessage');
                    const errorText = document.getElementById('errorText');

                    if (errorDiv && errorText) {
                        errorText.textContent = errorMsg;
                        errorDiv.style.display = 'block';
                    } else {
                        // Fallback jika elemen error tidak ditemukan
                        document.body.innerHTML = `
                            <div class="error-message" style="display: block;">
                                <div class="error-content">
                                    <div class="error-icon">⚠️</div>
                                    <h3>Gagal Memuat Ujian</h3>
                                    <p>${errorMsg}</p>
                                    <p class="text-sm mt-2">Silakan hubungi pengawas atau coba refresh halaman.</p>
                                    <button class="retry-button mt-4" onclick="window.location.reload()">
                                        Refresh Halaman
                                    </button>
                                </div>
                            </div>
                        `;
                    }

                    hideLoadingScreen();
                },

                /**
                 * Shuffle array using Fisher-Yates algorithm
                 */
                shuffleArray(array) {
                    if (!Array.isArray(array) || array.length === 0) {
                        return array;
                    }

                    const shuffled = [...array];
                    for (let i = shuffled.length - 1; i > 0; i--) {
                        const j = Math.floor(Math.random() * (i + 1));
                        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
                    }
                    return shuffled;
                },

                formatTime(seconds) {
                    // Handle float/decimal values
                    const secs = Math.max(0, Math.floor(seconds));

                    const hours = Math.floor(secs / 3600);
                    const minutes = Math.floor((secs % 3600) / 60);
                    const remainingSeconds = secs % 60;

                    if (hours > 0) {
                        return `${hours}:${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
                    }
                    return `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
                },

                selectAnswer(optionKey) {
                    if (!this.currentQuestion) return;
                    this.selectedAnswers[this.currentQuestion.id] = optionKey;
                    this.saveToLocalStorage();
                },

                // PGK: toggle checkbox
                togglePGKAnswer(optionKey) {
                    if (!this.currentQuestion) return;
                    const id = this.currentQuestion.id;
                    const current = this.selectedAnswers[id];
                    let arr = current ? String(current).split(',').filter(Boolean) : [];
                    const idx = arr.indexOf(String(optionKey));
                    if (idx > -1) arr.splice(idx, 1);
                    else arr.push(String(optionKey));
                    this.selectedAnswers[id] = arr.join(',');
                    this.saveToLocalStorage();
                },

                isPGKSelected(optionKey) {
                    if (!this.currentQuestion) return false;
                    const current = this.selectedAnswers[this.currentQuestion.id];
                    if (!current) return false;
                    return String(current).split(',').includes(String(optionKey));
                },

                getScaleRange(question) {
                    const min = parseInt(question?.scale_min ?? 1);
                    const max = parseInt(question?.scale_max ?? 5);
                    const range = [];
                    for (let i = min; i <= max; i++) range.push(i);
                    return range;
                },

                getMJAnswer(idx) {
                    if (!this.currentQuestion) return '';
                    const current = this.selectedAnswers[this.currentQuestion.id];
                    if (!current) return '';
                    try { return JSON.parse(current)[idx]?.right || ''; } catch(e) { return ''; }
                },

                setMJAnswer(idx, value) {
                    if (!this.currentQuestion) return;
                    const id = this.currentQuestion.id;
                    const pairs = this.currentQuestion.pairs || [];
                    let current = [];
                    try {
                        const saved = this.selectedAnswers[id];
                        current = saved ? JSON.parse(saved) : pairs.map(p => ({ left: p.left, right: '' }));
                    } catch(e) {
                        current = pairs.map(p => ({ left: p.left, right: '' }));
                    }
                    if (!current[idx]) current[idx] = { left: pairs[idx]?.left || '', right: '' };
                    current[idx].right = value;
                    this.selectedAnswers[id] = JSON.stringify(current);
                    this.saveToLocalStorage();
                },

                saveEssayAnswer(questionId, value) {
                    if (!questionId) return;
                    this.essayAnswers[questionId] = value;
                    this.saveToLocalStorage();
                },

                markForReview() {
                    if (!this.currentQuestion) return;
                    const id = this.currentQuestion.id;
                    if (this.markedForReview.has(id)) {
                        this.markedForReview.delete(id);
                    } else {
                        this.markedForReview.add(id);
                    }
                    this.saveToLocalStorage();
                },

                clearAnswer() {
                    if (!this.currentQuestion) return;
                    const id = this.currentQuestion.id;
                    delete this.selectedAnswers[id];
                    delete this.essayAnswers[id];
                    this.markedForReview.delete(id);
                    this.saveToLocalStorage();
                },

                startTimer() {
                    // Pastikan timeRemaining integer
                    this.timeRemaining = Math.floor(this.timeRemaining);

                    if (this.timeRemaining <= 0) return;

                    this.timerInterval = setInterval(() => {
                        if (this.timeRemaining > 0) {
                            this.timeRemaining--;

                            // Update tampilan langsung untuk memastikan konsistensi
                            this.updateTimerDisplay();

                            // Save to localStorage every 10 seconds
                            if (this.timeRemaining % 10 === 0) {
                                this.saveToLocalStorage();
                            }
                        } else {
                            this.autoSubmit();
                        }
                    }, 1000);
                },

                updateTimerDisplay() {
                    // Pastikan timeRemaining selalu integer
                    this.timeRemaining = Math.floor(this.timeRemaining);

                    // Timer sudah diupdate otomatis melalui Alpine binding
                    // Tapi kita bisa update elemen timer langsung jika perlu
                    const timerElement = document.querySelector('.timer span');
                    if (timerElement) {
                        timerElement.textContent = this.formatTime(this.timeRemaining);
                    }
                },


                hideLoadingScreen() {
                    const loadingScreen = document.getElementById('loadingScreen');
                    if (loadingScreen) {
                        loadingScreen.style.opacity = '0';
                        setTimeout(() => {
                            loadingScreen.style.display = 'none';
                            this.examLoaded = true;
                        }, 300);
                    }
                },

                goToQuestion(idx) {
                    if (idx >= 0 && idx < this.totalQuestions) {
                        this.currentQuestionIndex = idx;
                    }
                },

                nextQuestion() {
                    if (this.currentQuestionIndex < this.totalQuestions - 1) {
                        this.currentQuestionIndex++;
                    }
                },

                previousQuestion() {
                    if (this.currentQuestionIndex > 0) {
                        this.currentQuestionIndex--;
                    }
                },

                getQuestionButtonClass(index) {
                    const q = this.questions[index];
                    if (!q) return 'question-number-btn';
                    let cls = 'question-number-btn';
                    if (index === this.currentQuestionIndex) cls += ' active';
                    if (this.markedForReview.has(q.id)) {
                        cls += ' marked';
                    } else {
                        const sel = this.selectedAnswers[q.id];
                        const essay = this.essayAnswers[q.id];
                        let answered = !!(essay && String(essay).trim());
                        if (!answered && sel) {
                            const s = String(sel).trim();
                            if (s && s !== '{}' && s !== '[]') {
                                if (s.startsWith('[')) { try { answered = JSON.parse(s).some(p => p.right); } catch(e) {} }
                                else answered = true;
                            }
                        }
                        if (answered) cls += ' answered';
                    }
                    return cls;
                },

                saveToLocalStorage() {
                    try {
                        const data = {
                            answers: this.selectedAnswers,
                            essayAnswers: this.essayAnswers,
                            markedForReview: Array.from(this.markedForReview),
                            idx: this.currentQuestionIndex,
                            timestamp: Date.now()
                        };
                        localStorage.setItem('quiz_data_{{ $exam->id }}', JSON.stringify(data));
                    } catch (e) {
                        console.warn('[QuizApp] Failed to save to localStorage:', e);
                    }
                },

                loadSavedData() {
                    try {
                        const saved = localStorage.getItem('quiz_data_{{ $exam->id }}');
                        if (saved) {
                            const data = JSON.parse(saved);

                            // INI YANG DIPERBAIKI - cek timestamp dulu
                            const currentTime = Date.now();
                            const sessionStartTime = currentTime - (30 * 60 * 1000); // 30 menit lalu

                            if (data.timestamp && data.timestamp >= sessionStartTime) {
                                // Hanya load jika data masih fresh (dari sesi ini)
                                this.selectedAnswers = data.answers || {}; // <-- Langsung dari localStorage
                                this.essayAnswers = data.essayAnswers || {}; // <-- Langsung dari localStorage
                                this.markedForReview = new Set(data.markedForReview || []);
                                this.currentQuestionIndex = data.idx || 0;

                                console.log('[QuizApp] Loaded from localStorage:', {
                                    answersCount: Object.keys(this.selectedAnswers).length,
                                    essayCount: Object.keys(this.essayAnswers).length,
                                    markedCount: this.markedForReview.size,
                                    idx: this.currentQuestionIndex
                                });
                            } else {
                                console.log('[QuizApp] localStorage data expired, starting fresh');
                                // Mulai dari kosong jika data sudah kadaluarsa
                                this.selectedAnswers = {};
                                this.essayAnswers = {};
                                this.markedForReview = new Set();
                                this.currentQuestionIndex = 0;
                            }
                        } else {
                            console.log('[QuizApp] No saved data in localStorage, starting fresh');
                            // Mulai dari kosong jika tidak ada data tersimpan
                            this.selectedAnswers = {};
                            this.essayAnswers = {};
                            this.markedForReview = new Set();
                        }
                    } catch (e) {
                        console.warn('[QuizApp] Failed to load from localStorage:', e);
                        // Jika error, mulai dari kosong
                        this.selectedAnswers = {};
                        this.essayAnswers = {};
                        this.markedForReview = new Set();
                    }
                },

                autoSubmit() {
                    if (this.timerInterval) {
                        clearInterval(this.timerInterval);
                    }

                    alert('Waktu habis! Jawaban akan otomatis dikumpulkan.');
                    document.getElementById('submitForm').submit();
                },

                get totalQuestions() {
                    return this.questions.length;
                },

                get currentQuestion() {
                    return this.questions[this.currentQuestionIndex];
                },

                get answeredCount() {
                    const selCount = Object.keys(this.selectedAnswers).filter(key => {
                        const v = this.selectedAnswers[key];
                        if (!v) return false;
                        const s = String(v).trim();
                        if (!s || s === '{}' || s === '[]') return false;
                        if (s.startsWith('[')) {
                            try { return JSON.parse(s).some(p => p.right && p.right.trim()); } catch(e) { return false; }
                        }
                        return true;
                    }).length;
                    const essayCount = Object.keys(this.essayAnswers).filter(key =>
                        this.essayAnswers[key] && String(this.essayAnswers[key]).trim() !== ''
                    ).length;
                    return selCount + essayCount;
                },

                get markedForReviewCount() {
                    return this.markedForReview.size;
                }
            };
        }

        // ==============================
        // INITIALIZATION
        // ==============================
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[App] DOM loaded, initializing...');

            // Alpine.js auto-starts via defer CDN - no need to call Alpine.start()
            // Just set timeout untuk memastikan loading screen hilang jika ada error

            // Set timeout untuk memastikan loading screen hilang
            setTimeout(function() {
                hideLoadingScreen();

                // Jika masih ada error, tampilkan pesan
                const examContainer = document.querySelector('.exam-container');
                if (!examContainer || !examContainer.classList.contains('active')) {
                    const errorDiv = document.getElementById('errorMessage');
                    if (errorDiv && errorDiv.style.display !== 'block') {
                        showError('Gagal memuat ujian. Silakan refresh halaman atau hubungi pengawas.');
                    }
                }
            }, 5000);
        });

        // Global error handlers
        window.addEventListener('error', function(e) {
            console.error('[Global Error]', e.error);

            // Don't show error if already showing error modal
            const errorDiv = document.getElementById('errorMessage');
            if (errorDiv && errorDiv.style.display !== 'block') {
                showError('Terjadi kesalahan sistem. Silakan hubungi pengawas ujian.');
            }
        });

        window.addEventListener('unhandledrejection', function(event) {
            console.error('[Unhandled Promise]', event.reason);

            // Handle fullscreen errors gracefully
            if (event.reason && event.reason.message &&
                (event.reason.message.includes('fullscreen') ||
                    event.reason.message.includes('Fullscreen'))) {
                console.log('Fullscreen error, continuing without fullscreen');

                if (window.fullscreenHandler && typeof window.fullscreenHandler.startExam === 'function') {
                    window.fullscreenHandler.startExam();
                }
            }
        });
    </script>
</body>

</html>
