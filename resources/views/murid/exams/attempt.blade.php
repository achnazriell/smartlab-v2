@extends('layouts.appSiswa')

@section('content')
    <style>
        /* Tambahkan di bagian atas style */
        .error-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
            z-index: 100000;
            max-width: 400px;
            width: 90%;
        }

        .error-message h3 {
            color: #dc2626;
            margin-bottom: 1rem;
        }

        .error-message p {
            color: #4b5563;
            margin-bottom: 1.5rem;
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
        }

        .retry-button:hover {
            background: #1d4ed8;
        }

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

        .question-text {
            font-weight: 500;
            color: #1e293b;
            line-height: 1.7;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }

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

        .option-radio {
            width: 20px;
            height: 20px;
            border: 2px solid #bfdbfe;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 0.125rem;
            background-color: #ffffff;
        }

        .option-button.selected .option-radio {
            border-color: #2563eb;
            background-color: #2563eb;
        }

        .option-radio::after {
            content: '✓';
            color: white;
            font-size: 0.875rem;
            font-weight: bold;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .option-button.selected .option-radio::after {
            opacity: 1;
        }

        .option-text {
            flex: 1;
            color: #334155;
            line-height: 1.5;
            font-weight: 400;
        }

        .essay-textarea {
            min-height: 150px;
            max-height: 300px;
            resize: vertical;
            font-weight: 400;
            border: 2px solid #e0e7ff;
            padding: 0.75rem;
            border-radius: 8px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .essay-textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .nav-button {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
        }

        .nav-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .nav-button.primary {
            background-color: #2563eb;
            color: white;
        }

        .nav-button.primary:hover:not(:disabled) {
            background-color: #1d4ed8;
        }

        .nav-button.secondary {
            background-color: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }

        .nav-button.secondary:hover:not(:disabled) {
            background-color: #e5e7eb;
        }

        .info-box {
            background-color: #f0f9ff;
            border: 2px solid #bfdbfe;
            border-radius: 10px;
            padding: 1rem;
        }

        @media (max-width: 768px) {
            .question-container {
                max-height: 55vh;
            }

            .question-text {
                font-size: 0.95rem;
            }

            .timer {
                font-size: 1rem;
                min-width: 110px;
                padding: 0.6rem 0.75rem;
            }

            .nav-button {
                padding: 0.6rem 1rem;
                font-size: 0.875rem;
            }
        }

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

        .fullscreen-modal {
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            z-index: 10000;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 2rem;
        }

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
            display: none;
        }

        .fullscreen-content {
            display: none;
        }

        .fullscreen-active .fullscreen-content {
            display: block;
        }

        .fullscreen-inactive {
            display: none;
        }

        .attempt-warning {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #dc2626;
            color: white;
            padding: 1rem;
            text-align: center;
            z-index: 10001;
            font-weight: bold;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-100%);
            }

            to {
                transform: translateY(0);
            }
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.2s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .force-exit-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            padding: 2rem;
        }

        .exam-container {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .exam-container.active {
            opacity: 1;
        }
    </style>
    <!-- Tambahkan ini di dalam section content, sebelum script -->
    <div id="violationCount" data-count="{{ $attempt->violation_count ?? 0 }}"></div>
    <div id="examSettings" data-require-fullscreen="{{ $exam->fullscreen_mode ? 'true' : 'false' }}"
        data-violation-limit="{{ $exam->violation_limit ?? 3 }}" data-exam-id="{{ $exam->id }}"
        data-attempt-id="{{ $attempt->id }}">
    </div>
    <!-- Loading Screen -->
    <div id="loadingScreen" class="loading-screen">
        <div class="text-center">
            <div class="loader"></div>
            <p class="text-slate-700 font-medium" id="loadingText">Memuat soal...</p>
        </div>
    </div>

    <!-- Force Exit Modal -->
    <div id="forceExitModal" class="force-exit-modal" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
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
                    <h2 class="text-3xl font-bold text-red-900 mb-2">UJIAN DITUTUP</h2>
                    <p class="text-gray-600 text-sm">Batas pelanggaran telah tercapai</p>
                </div>

                <!-- Message -->
                <div class="bg-red-50 border-l-4 border-red-600 rounded-lg p-4 text-left">
                    <p class="text-red-900 font-semibold mb-2">Alasan Penutupan:</p>
                    <ul class="space-y-2 text-sm text-red-700">
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Telah melanggar aturan ujian sebanyak 3 kali</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            <span>Jawaban Anda akan dikumpulkan otomatis</span>
                        </li>
                    </ul>
                </div>

                <p class="text-sm text-gray-500">Mengarahkan ke halaman hasil...</p>
            </div>
        </div>
    </div>

    <!-- Attempt Warning -->
    @if (session('attempt_warning'))
        <div class="attempt-warning">
            ⚠️ {{ session('attempt_warning') }}
        </div>
    @endif

    <!-- Fullscreen Modal (Hanya tampil jika fullscreen diwajibkan) -->
    @if ($exam->fullscreen_mode)
        <div id="fullscreenModal" class="fullscreen-modal" style="display: none;">
            <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
                <div class="text-center space-y-6">
                    <!-- Icon -->
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                            fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>

                    <!-- Title -->
                    <div>
                        <h2 class="text-3xl font-bold text-blue-900 mb-2">SIAP MEMULAI?</h2>
                        <p class="text-gray-600 text-sm">Aktifkan mode fullscreen untuk memulai ujian</p>
                    </div>

                    <!-- Rules Card -->
                    <div class="bg-blue-50 border-l-4 border-blue-600 rounded-lg p-4 text-left space-y-3">
                        <p class="text-blue-900 font-semibold text-sm">Persyaratan Ujian:</p>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Fullscreen mode harus aktif</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Jangan buka tab atau window lain</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Screenshot dan copy-paste terlarang</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Button -->
                    <button id="enterFullscreenBtn" type="button"
                        class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-3 px-6 rounded-lg text-base shadow-lg hover:shadow-xl transition-all duration-200 active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3">
                            </path>
                        </svg>
                        MULAI UJIAN
                    </button>

                    <p class="text-xs text-gray-500">
                        Jika tidak bekerja, tekan <kbd
                            class="bg-gray-100 text-gray-700 px-2 py-1 rounded border border-gray-300 text-xs font-mono">F11</kbd>
                        secara manual
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- VIOLATION COUNTER -->
    <div id="violationCounter" class="violation-count">
        <svg class="w-4 h-4 inline mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
        </svg>
        Pelanggaran: <span id="violationCountText">0</span>/3
    </div>

    <!-- Main App Container -->
    <div x-data="quizApp()" x-init="init()"
        :class="{ 'fullscreen-active': isFullscreen || !requireFullscreen }" class="min-h-screen bg-white">

        <!-- Hidden when fullscreen required but not active -->
        <div class="fullscreen-content" :class="{ 'fullscreen-inactive': !isFullscreen && requireFullscreen }">
            <div class="exam-container" :class="{ 'active': examLoaded }">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
                    <!-- Header -->
                    <div class="bg-white rounded-xl p-4 sm:p-6 mb-6 border-2 border-blue-100 shadow-sm">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="flex-1">
                                <h1 class="text-2xl font-semibold text-blue-900">{{ $exam->title }}</h1>
                                <p class="text-blue-700 text-sm mt-1">
                                    {{ $exam->subject->name_subject ?? 'Mata Pelajaran' }}
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
                                            x-html="currentQuestion?.question_text || 'Tidak ada soal'">
                                        </div>

                                        <!-- Question Image -->
                                        <template x-if="currentQuestion?.question_image">
                                            <div class="my-4">
                                                <img :src="currentQuestion.question_image" alt="Question Image"
                                                    class="max-w-full h-auto rounded-xl shadow-md border border-slate-200">
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Answer Options -->
                                    <div class="space-y-2">
                                        <!-- Multiple Choice Questions -->
                                        <template x-if="currentQuestion?.type === 'PG' && currentQuestion?.options">
                                            <template x-for="(optionText, optionKey) in currentQuestion.options"
                                                :key="optionKey">
                                                <button @click="selectAnswer(optionKey)"
                                                    :class="{ 'selected': selectedAnswers[currentQuestion?.id] == optionKey }"
                                                    class="option-button w-full">
                                                    <div class="option-radio">
                                                        <span
                                                            x-text="String.fromCharCode(65 + Object.keys(currentQuestion.options).indexOf(optionKey))"
                                                            class="text-xs font-bold"></span>
                                                    </div>
                                                    <span class="option-text" x-text="optionText"></span>
                                                </button>
                                            </template>
                                        </template>

                                        <!-- Essay Questions -->
                                        <template x-if="currentQuestion?.type === 'IS'">
                                            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                                <p class="text-blue-800 text-sm mb-3 flex items-start gap-2">
                                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"
                                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                        fill="currentColor">
                                                        <path
                                                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z" />
                                                    </svg>
                                                    <span>Soal essay - jawaban akan diperiksa manual oleh guru</span>
                                                </p>
                                                <textarea x-model="essayAnswers[currentQuestion?.id]"
                                                    @input.debounce="saveEssayAnswer(currentQuestion?.id, $event.target.value)"
                                                    class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 essay-textarea"
                                                    rows="4" placeholder="Ketik jawaban Anda di sini..."></textarea>
                                            </div>
                                        </template>

                                        <!-- No Question Found -->
                                        <template x-if="!currentQuestion">
                                            <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                                <p class="text-red-800 flex items-center">
                                                    Soal tidak ditemukan atau terjadi kesalahan.
                                                </p>
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
                                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z">
                                            </path>
                                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                            <polyline points="7 3 7 8 15 8"></polyline>
                                        </svg>
                                        <span
                                            x-text="markedForReview.has(currentQuestion?.id) ? 'Hapus Tanda' : 'Tandai Review'"></span>
                                    </button>
                                    <button @click="clearAnswer()"
                                        class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium flex items-center gap-2">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 4 21 4 23 6 23 20a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V6">
                                            </polyline>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
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
                            <div class="info-box">
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
                                    class="nav-button primary flex-1">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="19" y1="12" x2="5" y2="12"></line>
                                        <polyline points="12 19 5 12 12 5"></polyline>
                                    </svg>
                                    <span class="hidden sm:inline">Sebelumnya</span>
                                </button>
                                <button @click="nextQuestion()" :disabled="currentQuestionIndex === totalQuestions - 1"
                                    class="nav-button primary flex-1">
                                    <span class="hidden sm:inline">Selanjutnya</span>
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </button>
                            </div>

                            <!-- Submit Button -->
                            <button @click="showSubmitModal = true"
                                class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                <span>Kumpulkan Jawaban</span>
                            </button>
                        </div>
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
                            <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
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
                                <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z" />
                                </svg>
                                <span><span class="font-bold" x-text="totalQuestions - answeredCount"></span> soal belum
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
                            <input type="hidden" name="answers" x-bind:value="JSON.stringify(selectedAnswers)">
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-bold py-3 px-4 rounded-lg transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
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
        // FULLSCREEN HANDLER (DIPERBAIKI)
        // ==============================
        class FullscreenHandler {
            constructor() {
                this.requireFullscreen = {{ $exam->fullscreen_mode ? 'true' : 'false' }};
                this.examId = {{ $exam->id }};
                this.isFullscreen = false;
                this.violationCount = {{ $attempt->violation_count ?? 0 }};
                this.MAX_VIOLATIONS = {{ $exam->violation_limit ?? 3 }};
                this.violationLock = false;
                this.examLoaded = false;
                this.isSubmitting = false;
                this.forceExitModalShown = false;

                this.loadingScreen = document.getElementById('loadingScreen');
                this.loadingText = document.getElementById('loadingText');
                this.forceExitModal = document.getElementById('forceExitModal');

                // CSRF Token
                this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                // Cek status awal
                this.checkFullscreen();

                // Jika sudah 3x pelanggaran, langsung exit
                if (this.violationCount >= this.MAX_VIOLATIONS) {
                    this.showForceExitModal();
                    return;
                }

                // Jalankan inisialisasi
                this.init();
            }

            async init() {
                try {
                    this.updateLoadingText('Memuat ujian...');
                    this.setupEventListeners();
                    this.updateViolationDisplay();

                    // Jika tidak butuh fullscreen, langsung mulai
                    if (!this.requireFullscreen) {
                        await this.startExam();
                        return;
                    }

                    // Cek fullscreen status
                    if (this.isFullscreen) {
                        await this.startExam();
                    } else {
                        this.updateLoadingText('Menunggu mode fullscreen...');
                        // Tampilkan modal fullscreen & sembunyikan loader
                        this.showFullscreenModal();
                    }
                } catch (error) {
                    console.error('Error initializing FullscreenHandler:', error);
                    // Fallback: paksa mulai jika error
                    await this.startExam();
                }
            }

            updateLoadingText(text) {
                if (this.loadingText) this.loadingText.textContent = text;
            }

            showFullscreenModal() {
                const modal = document.getElementById('fullscreenModal');
                if (modal) {
                    modal.style.display = 'flex';
                    this.hideLoadingScreen();
                }

                // Setup button event listener
                const btn = document.getElementById('enterFullscreenBtn');
                if (btn) {
                    btn.addEventListener('click', () => {
                        this.requestFullscreen();
                    }, {
                        once: true
                    }); // Hanya sekali
                }
            }

            hideFullscreenModal() {
                const modal = document.getElementById('fullscreenModal');
                if (modal) modal.style.display = 'none';
            }

            hideLoadingScreen() {
                if (this.loadingScreen) {
                    this.loadingScreen.style.display = 'none';
                }
            }

            showErrorScreen(message) {
                this.hideLoadingScreen();
                const errorDiv = document.getElementById('errorMessage');
                const errorText = document.getElementById('errorText');
                if (errorDiv && errorText) {
                    errorText.textContent = message;
                    errorDiv.style.display = 'block';
                }
            }

            checkFullscreen() {
                this.isFullscreen = !!(
                    document.fullscreenElement ||
                    document.webkitFullscreenElement ||
                    document.mozFullScreenElement ||
                    document.msFullscreenElement
                );
                return this.isFullscreen;
            }

            async requestFullscreen() {
                const elem = document.documentElement;
                const requestMethods = [
                    'requestFullscreen',
                    'webkitRequestFullscreen',
                    'mozRequestFullScreen',
                    'msRequestFullscreen'
                ];

                for (const method of requestMethods) {
                    if (elem[method]) {
                        try {
                            this.updateLoadingText('Mengaktifkan fullscreen...');
                            await elem[method]();
                            this.isFullscreen = true;
                            this.hideFullscreenModal();
                            await this.startExam();
                            return true;
                        } catch (err) {
                            console.error('Error enabling fullscreen:', err);
                        }
                    }
                }

                alert('Browser Anda tidak mendukung fullscreen API. Silakan tekan F11 secara manual.');
                await this.startExam();
                return false;
            }

            setupEventListeners() {
                // Fullscreen change listeners
                ['fullscreenchange', 'webkitfullscreenchange', 'mozfullscreenchange', 'MSFullscreenChange']
                .forEach(event => {
                    document.addEventListener(event, () => this.handleFullscreenChange());
                });

                // Proteksi Ujian
                if (!{{ $exam->allow_screenshot ? 'true' : 'false' }}) this.preventScreenshot();
                if ({{ $exam->block_new_tab ? 'true' : 'false' }}) this.detectTabSwitch();
                if (!{{ $exam->allow_copy ? 'true' : 'false' }}) this.preventCopyPaste();
            }

            handleFullscreenChange() {
                const wasFullscreen = this.isFullscreen;
                this.isFullscreen = this.checkFullscreen();

                if (this.requireFullscreen && wasFullscreen && !this.isFullscreen) {
                    this.logViolation('fullscreen_exit');
                    setTimeout(() => {
                        if (!this.isFullscreen) this.showFullscreenModal();
                    }, 500);
                }
            }

            async startExam() {
                console.log('Starting exam...');
                this.hideFullscreenModal();
                this.hideLoadingScreen();

                this.examLoaded = true;

                // Integrasi dengan AlpineJS
                if (window.quizAppInstance) {
                    window.quizAppInstance.examStarted = true;
                    window.quizAppInstance.examLoaded = true;
                    window.quizAppInstance.isFullscreen = this.isFullscreen;
                    window.quizAppInstance.startTimer();
                }

                const examContainer = document.querySelector('.exam-container');
                if (examContainer) {
                    examContainer.classList.add('active');
                }
            }

            // --- Fitur Pelanggaran ---
            preventScreenshot() {
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'PrintScreen' || (e.ctrlKey && e.key === 'p')) {
                        e.preventDefault();
                        this.logViolation('screenshot');
                        this.showViolationWarning('Screenshot tidak diizinkan!');
                    }
                });
                document.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    this.logViolation('right_click');
                });
            }

            detectTabSwitch() {
                let lastSwitch = Date.now();
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden && (Date.now() - lastSwitch > 2000)) {
                        this.logViolation('tab_switch');
                        lastSwitch = Date.now();
                    }
                });
                window.addEventListener('blur', () => {
                    if (Date.now() - lastSwitch > 2000) {
                        this.logViolation('window_switch');
                        lastSwitch = Date.now();
                    }
                });
            }

            preventCopyPaste() {
                ['copy', 'paste', 'cut', 'dragstart', 'selectstart'].forEach(event => {
                    document.addEventListener(event, (e) => {
                        e.preventDefault();
                        if (['copy', 'paste', 'cut'].includes(event)) {
                            this.logViolation(event);
                            this.showViolationWarning('Copy-Paste tidak diizinkan!');
                        }
                    });
                });
            }

            logViolation(type) {
                if (this.violationLock || this.isSubmitting) return;

                this.violationLock = true;
                this.violationCount++;

                this.updateViolationDisplay();
                this.sendViolationToServer(type);

                if (this.violationCount >= this.MAX_VIOLATIONS) {
                    this.handleMaxViolations();
                } else {
                    this.showViolationWarning(
                        `Peringatan ${this.violationCount}/${this.MAX_VIOLATIONS}: ${this.getViolationMessage(type)}`
                    );
                }

                setTimeout(() => this.violationLock = false, 1000);
            }

            updateViolationDisplay() {
                const el = document.getElementById('violationCountText');
                if (el) el.textContent = this.violationCount;
                const counter = document.getElementById('violationCounter');
                if (counter) {
                    counter.style.display = 'flex';
                    counter.style.alignItems = 'center';
                    counter.style.justifyContent = 'center';
                }
            }

            async handleMaxViolations() {
                if (this.forceExitModalShown) return; // Mencegah duplikasi

                this.isSubmitting = true;
                this.forceExitModalShown = true;

                // Tampilkan modal dulu
                this.showForceExitModal();

                // Kirim data pelanggaran ke server
                await this.sendViolationToServer('max_violations_reached');

                // Tunggu 3 detik agar user bisa membaca pesan
                setTimeout(() => {
                    this.forceSubmitExam();
                }, 3000);
            }

            showForceExitModal() {
                if (this.forceExitModal) {
                    this.forceExitModal.style.display = 'flex';
                    this.hideLoadingScreen();

                    // Sembunyikan konten ujian
                    const fullscreenContent = document.querySelector('.fullscreen-content');
                    if (fullscreenContent) {
                        fullscreenContent.style.display = 'none';
                    }

                    // Sembunyikan modal fullscreen jika ada
                    this.hideFullscreenModal();
                }
            }

            async sendViolationToServer(type) {
                if (!this.csrfToken) return;

                try {
                    const response = await fetch(`/soal/{{ $exam->id }}/violation`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            type,
                            count: this.violationCount,
                            timestamp: new Date().toISOString()
                        })
                    });

                    const result = await response.json();

                    // Jika server mengirim redirect URL, gunakan itu
                    if (result.force_submit && result.redirect_url) {
                        console.log('Redirecting to:', result.redirect_url);
                        // Redirect langsung tanpa modal
                        window.location.href = result.redirect_url;
                        return;
                    }

                    return result;
                } catch (e) {
                    console.error('Error sending violation:', e);
                    // Fallback jika API gagal
                    if (this.violationCount >= this.MAX_VIOLATIONS) {
                        this.forceSubmitExam();
                    }
                }
            }

            forceSubmitExam() {
                if (this.isSubmitting && this.submitTriggered) return;

                this.isSubmitting = true;
                this.submitTriggered = true;

                console.log('Force submitting exam...');

                // Collect answers
                let allAnswers = {};
                if (window.quizAppInstance) {
                    // Gabungkan jawaban PG dan Essay
                    allAnswers = {
                        ...window.quizAppInstance.selectedAnswers,
                        ...window.quizAppInstance.essayAnswers
                    };
                }

                // Buat form submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('soal.submit', $exam->id) }}";
                form.style.display = 'none';

                // Tambahkan CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = this.csrfToken;
                form.appendChild(csrfInput);

                // Tambahkan answers
                const answersInput = document.createElement('input');
                answersInput.type = 'hidden';
                answersInput.name = 'answers';
                answersInput.value = JSON.stringify(allAnswers);
                form.appendChild(answersInput);

                // Tambahkan flag violation
                const violationInput = document.createElement('input');
                violationInput.type = 'hidden';
                violationInput.name = 'force_submit_violation';
                violationInput.value = 'true';
                form.appendChild(violationInput);

                // Tambahkan violation count
                const countInput = document.createElement('input');
                countInput.type = 'hidden';
                countInput.name = 'violation_count';
                countInput.value = this.violationCount;
                form.appendChild(countInput);

                document.body.appendChild(form);
                console.log('Submitting form...');
                form.submit();
            }

            showViolationWarning(message) {
                // Hapus warning lama jika ada
                const oldWarning = document.querySelector('.violation-warning');
                if (oldWarning) oldWarning.remove();

                const warning = document.createElement('div');
                warning.className = 'violation-warning';
                warning.innerHTML = `
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.282 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <div>
                    <strong class="text-lg block mb-1">PELANGGARAN!</strong>
                    <p class="text-sm">${message}</p>
                </div>
            </div>
        `;
                document.body.appendChild(warning);
                setTimeout(() => warning.remove(), 4000);
            }

            getViolationMessage(type) {
                const messages = {
                    'fullscreen_exit': 'Keluar dari mode fullscreen!',
                    'tab_switch': 'Beralih ke tab/window lain!',
                    'window_switch': 'Membuka window baru!',
                    'screenshot': 'Mencoba mengambil screenshot!',
                    'copy': 'Mencoba menyalin teks!',
                    'paste': 'Mencoba menempel teks!',
                    'cut': 'Mencoba memotong teks!',
                    'right_click': 'Menggunakan klik kanan!'
                };
                return messages[type] || 'Aktivitas mencurigakan terdeteksi!';
            }
        }

        // ==============================
        // QUIZ APP (ALPINE.JS) - DIPERBAIKI
        // ==============================
        function quizApp() {
            return {
                questions: @json($questions ?? []),
                currentQuestionIndex: 0,
                selectedAnswers: @json($answers ?? []),
                essayAnswers: @json($essayAnswers ?? []),
                markedForReview: new Set(@json($markedForReview ?? [])),
                showSubmitModal: false,
                timeRemaining: {{ $timeRemaining ?? 0 }},
                timerInterval: null,
                examStarted: false,
                examLoaded: false,
                isFullscreen: false,

                get currentQuestion() {
                    return this.questions[this.currentQuestionIndex] || null;
                },
                get totalQuestions() {
                    return this.questions?.length || 0;
                },
                get answeredCount() {
                    let count = 0;
                    // Hitung PG
                    for (let k in this.selectedAnswers) {
                        if (this.selectedAnswers[k] && this.selectedAnswers[k] !== '') count++;
                    }
                    // Hitung Essay
                    for (let k in this.essayAnswers) {
                        if (this.essayAnswers[k] && this.essayAnswers[k].trim() !== '' && !this.selectedAnswers[k])
                            count++;
                    }
                    return count;
                },
                get markedForReviewCount() {
                    return this.markedForReview.size;
                },

                init() {
                    console.log('Initializing Quiz App...');
                    window.quizAppInstance = this;

                    // Perbaiki format questions jika ada masalah
                    this.fixQuestionFormat();

                    // Cek jika soal kosong
                    if (this.totalQuestions === 0) {
                        this.hideLoadingScreen();
                        this.showError('Soal tidak ditemukan. Silakan hubungi guru.');
                        return;
                    }

                    // Load saved data dari localStorage
                    this.loadSavedData();

                    // Inisialisasi Fullscreen Handler
                    setTimeout(() => {
                        if (!window.fullscreenHandler) {
                            window.fullscreenHandler = new FullscreenHandler();
                        }
                    }, 100);

                    // Safety timeout untuk loading screen
                    setTimeout(() => {
                        this.hideLoadingScreen();
                    }, 5000);
                },

                fixQuestionFormat() {
                    // Perbaiki format options jika berupa object
                    this.questions = this.questions.map(q => {
                        if (q.options && typeof q.options === 'object') {
                            // Jika options sudah dalam format {A: "text", B: "text"}
                            return q;
                        }

                        // Jika options kosong atau tidak valid
                        if (q.type === 'PG' || q.type === 'multiple_choice') {
                            q.options = q.options || {
                                A: "Pilihan A",
                                B: "Pilihan B",
                                C: "Pilihan C",
                                D: "Pilihan D"
                            };
                        }

                        return q;
                    });
                },

                hideLoadingScreen() {
                    const ls = document.getElementById('loadingScreen');
                    if (ls) ls.style.display = 'none';

                    const examContainer = document.querySelector('.exam-container');
                    if (examContainer && !examContainer.classList.contains('active')) {
                        examContainer.classList.add('active');
                    }
                },

                showError(message) {
                    const errorDiv = document.getElementById('errorMessage');
                    const errorText = document.getElementById('errorText');
                    if (errorDiv && errorText) {
                        errorText.textContent = message;
                        errorDiv.style.display = 'block';
                    }
                },

                startTimer() {
                    if (this.timerInterval) clearInterval(this.timerInterval);
                    this.examStarted = true;
                    this.timerInterval = setInterval(() => {
                        if (this.timeRemaining > 0) {
                            this.timeRemaining--;

                            // Update timer display
                            this.updateTimerDisplay();

                            // Auto-save setiap 30 detik
                            if (this.timeRemaining % 30 === 0) {
                                this.saveToLocalStorage();
                            }

                            // Auto submit jika waktu habis
                            if (this.timeRemaining <= 0) {
                                this.autoSubmit();
                            }
                        }
                    }, 1000);

                    // Update display awal
                    this.updateTimerDisplay();
                },

                updateTimerDisplay() {
                    const timerElement = document.querySelector('.timer span');
                    if (timerElement) {
                        timerElement.textContent = this.formatTime(this.timeRemaining);

                        // Update class warning/critical
                        const timerContainer = document.querySelector('.timer');
                        if (timerContainer) {
                            timerContainer.classList.remove('warning', 'critical');
                            if (this.timeRemaining < 300 && this.timeRemaining >= 60) {
                                timerContainer.classList.add('warning');
                            } else if (this.timeRemaining < 60) {
                                timerContainer.classList.add('critical');
                            }
                        }
                    }
                },

                formatTime(seconds) {
                    if (isNaN(seconds) || seconds < 0) {
                        return "00:00";
                    }
                    const mins = Math.floor(seconds / 60);
                    const secs = Math.floor(seconds % 60);
                    return `${mins < 10 ? '0' : ''}${mins}:${secs < 10 ? '0' : ''}${secs}`;
                },

                selectAnswer(key) {
                    if (!this.currentQuestion) return;

                    // Pastikan key adalah string (A, B, C, D)
                    const answerKey = String(key).charAt(0).toUpperCase();

                    this.selectedAnswers[this.currentQuestion.id] = answerKey;
                    this.markedForReview.delete(this.currentQuestion.id);
                    this.saveToLocalStorage();

                    // Auto next jika pengaturan memungkinkan
                    if (this.currentQuestion.enable_auto_next) {
                        setTimeout(() => this.nextQuestion(), 300);
                    }
                },

                saveEssayAnswer(id, val) {
                    if (val && val.trim() !== '') {
                        this.essayAnswers[id] = val.trim();
                    } else {
                        delete this.essayAnswers[id];
                    }
                    this.saveToLocalStorage();
                },

                markForReview() {
                    const id = this.currentQuestion?.id;
                    if (!id) return;

                    if (this.markedForReview.has(id)) {
                        this.markedForReview.delete(id);
                    } else {
                        this.markedForReview.add(id);
                    }
                    this.saveToLocalStorage();
                },

                clearAnswer() {
                    const id = this.currentQuestion?.id;
                    if (!id) return;

                    delete this.selectedAnswers[id];
                    delete this.essayAnswers[id];
                    this.markedForReview.delete(id);
                    this.saveToLocalStorage();
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
                    if (this.markedForReview.has(q.id)) cls += ' marked';
                    else if (this.selectedAnswers[q.id] || this.essayAnswers[q.id]) cls += ' answered';
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
                        console.warn('Gagal menyimpan ke localStorage:', e);
                    }
                },

                loadSavedData() {
                    try {
                        const saved = localStorage.getItem('quiz_data_{{ $exam->id }}');
                        if (saved) {
                            const data = JSON.parse(saved);
                            this.selectedAnswers = data.answers || {};
                            this.essayAnswers = data.essayAnswers || {};
                            this.markedForReview = new Set(data.markedForReview || []);
                            this.currentQuestionIndex = data.idx || 0;
                        }
                    } catch (e) {
                        console.warn('Gagal memuat data dari localStorage:', e);
                    }
                },

                autoSubmit() {
                    clearInterval(this.timerInterval);
                    alert('Waktu habis! Jawaban akan otomatis dikumpulkan.');
                    document.getElementById('submitForm').submit();
                }
            };
        }

        // Inisialisasi setelah DOM siap
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing app...');

            // Jika Alpine tersedia, tunggu inisialisasi
            if (typeof Alpine === 'undefined') {
                console.warn('Alpine.js tidak terdeteksi, menggunakan fallback initialization');

                // Manual initialization
                setTimeout(function() {
                    if (window.quizAppInstance) {
                        window.quizAppInstance.init();
                    }
                }, 500);
            }
        });

        // Global error handler
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);

            const loadingScreen = document.getElementById('loadingScreen');
            if (loadingScreen) {
                loadingScreen.style.display = 'none';
            }

            const errorDiv = document.getElementById('errorMessage');
            if (errorDiv) {
                errorDiv.style.display = 'block';
                document.getElementById('errorText').textContent =
                    'Terjadi kesalahan dalam memuat ujian. Silakan refresh halaman.';
            }
        });
    </script>
@endsection
