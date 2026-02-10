<!DOCTYPE html>
<html lang="id" x-data="quizPlayer()" x-init="init()" :class="{'dark': isDarkMode}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quiz->title }} - Pengerjaan Quiz</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('logo.png') }}">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --primary: #6366F1;
            --primary-dark: #4F46E5;
            --secondary: #10B981;
            --accent: #F59E0B;
            --danger: #EF4444;
            --light: #F9FAFB;
            --dark: #1F2937;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .dark body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }

        /* Animated Background */
        .bg-animated {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .bg-animated::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: bgMove 20s linear infinite;
        }

        @keyframes bgMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        /* Modern Card */
        .quiz-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .dark .quiz-container {
            background: rgba(31, 41, 55, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Header dengan Glassmorphism */
        .quiz-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 20px 20px 0 0;
            padding: 1.5rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .quiz-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: headerShine 3s ease-in-out infinite;
        }

        @keyframes headerShine {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(10%, 10%); }
        }

        /* Timer dengan animasi */
        .timer-display {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            border-radius: 16px;
            padding: 1rem 1.5rem;
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3);
            animation: timerPulse 2s ease-in-out infinite;
        }

        @keyframes timerPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .timer-display.warning {
            animation: timerWarning 1s ease-in-out infinite;
        }

        @keyframes timerWarning {
            0%, 100% { box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3); }
            50% { box-shadow: 0 8px 32px rgba(239, 68, 68, 0.6); }
        }

        /* Question Card Modern */
        .question-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            border: 2px solid rgba(99, 102, 241, 0.1);
            transition: all 0.3s ease;
        }

        .dark .question-card {
            background: #374151;
            border-color: rgba(99, 102, 241, 0.2);
        }

        .question-card:hover {
            box-shadow: 0 15px 50px rgba(99, 102, 241, 0.15);
            transform: translateY(-2px);
        }

        /* Answer Options dengan Hover Effect */
        .answer-option {
            background: white;
            border: 3px solid #E5E7EB;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .dark .answer-option {
            background: #1F2937;
            border-color: #374151;
        }

        .answer-option::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.1), transparent);
            transition: left 0.5s;
        }

        .answer-option:hover::before {
            left: 100%;
        }

        .answer-option:hover {
            border-color: var(--primary);
            transform: translateX(8px);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.2);
        }

        .answer-option.selected {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(79, 70, 229, 0.05) 100%);
            transform: translateX(8px);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.2);
        }

        .dark .answer-option.selected {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(79, 70, 229, 0.1) 100%);
        }

        .answer-option.correct {
            border-color: var(--secondary);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);
            animation: correctAnswer 0.6s ease-in-out;
        }

        @keyframes correctAnswer {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .answer-option.incorrect {
            border-color: var(--danger);
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.05) 100%);
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        /* Option Label dengan Gradient */
        .option-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            margin-right: 1rem;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .answer-option.selected .option-label {
            animation: labelPulse 0.6s ease-in-out;
        }

        @keyframes labelPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* Question Navigation */
        .question-nav {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(48px, 1fr));
            gap: 0.75rem;
        }

        .question-nav-item {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            border: 2px solid #E5E7EB;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .dark .question-nav-item {
            background: #374151;
            border-color: #4B5563;
        }

        .question-nav-item:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        .question-nav-item.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.3);
        }

        .question-nav-item.answered {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
            border-color: #10B981;
        }

        /* Action Buttons */
        .action-button {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 14px;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3);
            position: relative;
            overflow: hidden;
        }

        .action-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .action-button:hover::before {
            width: 300px;
            height: 300px;
        }

        .action-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.4);
        }

        .action-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .action-button.danger {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.3);
        }

        .action-button.danger:hover {
            box-shadow: 0 10px 30px rgba(239, 68, 68, 0.4);
        }

        .action-button.success {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
        }

        .action-button.success:hover {
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
        }

        /* Progress Bar */
        .progress-container {
            background: #E5E7EB;
            border-radius: 12px;
            height: 12px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #6366F1 0%, #10B981 100%);
            border-radius: 12px;
            transition: width 0.5s ease;
            position: relative;
            overflow: hidden;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: progressShine 2s infinite;
        }

        @keyframes progressShine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Stats Card */
        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 2px solid rgba(99, 102, 241, 0.1);
            transition: all 0.3s ease;
        }

        .dark .stats-card {
            background: #374151;
            border-color: rgba(99, 102, 241, 0.2);
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.15);
        }

        /* Modal Overlay */
        .modal-overlay {
            backdrop-filter: blur(8px);
            background: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
            animation: modalSlideIn 0.3s ease-out;
        }

        .dark .modal-content {
            background: #1F2937;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Loading Animation */
        .loading-spinner {
            border: 4px solid #E5E7EB;
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .quiz-container {
                margin: 1rem;
                border-radius: 16px;
            }

            .question-card {
                padding: 1.5rem;
            }

            .quiz-header {
                padding: 1rem;
            }

            .answer-option {
                padding: 1rem;
            }

            .option-label {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }

            .question-nav {
                grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
                gap: 0.5rem;
            }

            .question-nav-item {
                width: 40px;
                height: 40px;
            }
        }

        /* Smooth Transitions */
        * {
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animated"></div>

    <!-- Main Container -->
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="quiz-container mb-6">
                <div class="quiz-header">
                    <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-4">
                        <!-- Quiz Info -->
                        <div class="text-center md:text-left">
                            <h1 class="text-2xl md:text-3xl font-bold text-white mb-1">
                                {{ $quiz->title }}
                            </h1>
                            <p class="text-indigo-100 text-sm">
                                <i class="fas fa-book mr-2"></i>{{ $quiz->subject->name_subject ?? 'Mata Pelajaran' }}
                            </p>
                        </div>

                        <!-- Timer & Progress -->
                        <div class="flex flex-col items-center gap-3">
                            <div class="timer-display" x-bind:class="{ 'warning': timeRemaining < 300 }">
                                <i class="fas fa-clock mr-2"></i>
                                <span x-text="formatTime(timeRemaining)">00:00</span>
                            </div>
                            <div class="text-white text-sm font-medium">
                                Soal <span x-text="currentQuestion + 1">1</span> dari <span x-text="totalQuestions">{{ count($questions) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800">
                    <div class="progress-container">
                        <div class="progress-bar"
                             x-bind:style="`width: ${(answeredCount / totalQuestions) * 100}%`">
                        </div>
                    </div>
                    <div class="mt-2 flex justify-between text-sm text-gray-600 dark:text-gray-400">
                        <span><i class="fas fa-check-circle mr-1 text-green-500"></i><span x-text="answeredCount">0</span> Terjawab</span>
                        <span><i class="fas fa-circle-notch mr-1 text-gray-400"></i><span x-text="totalQuestions - answeredCount">{{ count($questions) }}</span> Belum Dijawab</span>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Question Section -->
                <div class="lg:col-span-3">
                    <div class="question-card">
                        <!-- Question Number & Text -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <span class="inline-flex items-center px-4 py-2 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded-full text-sm font-semibold">
                                    <i class="fas fa-question-circle mr-2"></i>
                                    Pertanyaan <span x-text="currentQuestion + 1">1</span>
                                </span>
                                <button @click="flagQuestion()"
                                        class="text-gray-400 hover:text-yellow-500 transition-colors"
                                        x-bind:class="{ 'text-yellow-500': questions[currentQuestion]?.flagged }">
                                    <i class="fas fa-flag text-xl"></i>
                                </button>
                            </div>
                            <h2 class="text-xl md:text-2xl font-semibold text-gray-800 dark:text-white leading-relaxed"
                                x-html="questions[currentQuestion]?.question || 'Memuat soal...'">
                            </h2>
                        </div>

                        <!-- Answer Options -->
                        <div class="space-y-4">
                            <template x-for="(choice, index) in questions[currentQuestion]?.choices || []" :key="index">
                                <div class="answer-option"
                                     @click="selectAnswer(index)"
                                     x-bind:class="{
                                         'selected': questions[currentQuestion]?.selectedAnswer === index
                                     }">
                                    <div class="flex items-center relative z-10">
                                        <span class="option-label" x-text="String.fromCharCode(65 + index)">A</span>
                                        <span class="text-gray-700 dark:text-gray-200 font-medium flex-1"
                                              x-text="choice.choice_text">
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex flex-col md:flex-row items-center justify-between gap-4 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <button @click="previousQuestion()"
                                    x-bind:disabled="currentQuestion === 0"
                                    class="action-button w-full md:w-auto">
                                <i class="fas fa-arrow-left mr-2"></i>
                                <span class="relative z-10">Sebelumnya</span>
                            </button>

                            <div class="text-center text-gray-600 dark:text-gray-400 text-sm">
                                <i class="fas fa-info-circle mr-1"></i>
                                Klik soal untuk berpindah
                            </div>

                            <button @click="nextQuestion()"
                                    x-show="currentQuestion < totalQuestions - 1"
                                    class="action-button w-full md:w-auto">
                                <span class="relative z-10">Selanjutnya</span>
                                <i class="fas fa-arrow-right ml-2"></i>
                            </button>

                            <button @click="confirmSubmit()"
                                    x-show="currentQuestion === totalQuestions - 1"
                                    class="action-button success w-full md:w-auto">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span class="relative z-10">Selesai & Submit</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Stats -->
                    <div class="stats-card mb-6">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">
                            <i class="fas fa-chart-line mr-2 text-indigo-600"></i>
                            Statistik
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400 text-sm">Total Soal</span>
                                <span class="font-bold text-gray-800 dark:text-white" x-text="totalQuestions">{{ count($questions) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400 text-sm">Terjawab</span>
                                <span class="font-bold text-green-600" x-text="answeredCount">0</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400 text-sm">Ditandai</span>
                                <span class="font-bold text-yellow-600" x-text="flaggedCount">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Question Navigator -->
                    <div class="stats-card">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">
                            <i class="fas fa-th mr-2 text-indigo-600"></i>
                            Navigasi Soal
                        </h3>
                        <div class="question-nav">
                            <template x-for="(question, index) in questions" :key="index">
                                <button @click="goToQuestion(index)"
                                        class="question-nav-item"
                                        x-bind:class="{
                                            'active': currentQuestion === index,
                                            'answered': question.selectedAnswer !== null
                                        }"
                                        x-text="index + 1">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Submit Button (Mobile) -->
                    <button @click="confirmSubmit()"
                            class="action-button success w-full mt-6 lg:hidden">
                        <i class="fas fa-paper-plane mr-2"></i>
                        <span class="relative z-10">Submit Quiz</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div x-show="showSubmitModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center modal-overlay"
         style="display: none;">
        <div class="modal-content max-w-md w-full mx-4 p-8">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-yellow-100 dark:bg-yellow-900 mb-4">
                    <i class="fas fa-exclamation-triangle text-3xl text-yellow-600 dark:text-yellow-400"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    Konfirmasi Submit
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Anda telah menjawab <span x-text="answeredCount" class="font-bold text-indigo-600">0</span> dari <span x-text="totalQuestions" class="font-bold">{{ count($questions) }}</span> soal.
                </p>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mb-6">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                        Pastikan Anda sudah memeriksa semua jawaban sebelum submit.
                    </p>
                </div>
                <div class="flex gap-3">
                    <button @click="showSubmitModal = false"
                            class="action-button secondary flex-1">
                        <i class="fas fa-times mr-2"></i>
                        <span class="relative z-10">Batal</span>
                    </button>
                    <button @click="submitQuiz()"
                            class="action-button success flex-1">
                        <i class="fas fa-check mr-2"></i>
                        <span class="relative z-10">Ya, Submit</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function quizPlayer() {
            return {
                isDarkMode: false,
                currentQuestion: 0,
                totalQuestions: {{ count($questions) }},
                timeRemaining: {{ $quiz->duration_minutes * 60 }},
                showSubmitModal: false,
                questions: @json($questions->map(function($q) {
                    return [
                        'id' => $q->id,
                        'question' => $q->question_text,
                        'choices' => $q->choices->map(function($c) {
                            return [
                                'id' => $c->id,
                                'choice_text' => $c->choice_text
                            ];
                        }),
                        'selectedAnswer' => null,
                        'flagged' => false
                    ];
                })),

                init() {
                    this.startTimer();
                    this.preventCheating();

                    // Auto-save progress setiap 30 detik
                    setInterval(() => {
                        this.saveProgress();
                    }, 30000);
                },

                get answeredCount() {
                    return this.questions.filter(q => q.selectedAnswer !== null).length;
                },

                get flaggedCount() {
                    return this.questions.filter(q => q.flagged).length;
                },

                selectAnswer(index) {
                    this.questions[this.currentQuestion].selectedAnswer = index;
                },

                nextQuestion() {
                    if (this.currentQuestion < this.totalQuestions - 1) {
                        this.currentQuestion++;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                previousQuestion() {
                    if (this.currentQuestion > 0) {
                        this.currentQuestion--;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                goToQuestion(index) {
                    this.currentQuestion = index;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                flagQuestion() {
                    this.questions[this.currentQuestion].flagged = !this.questions[this.currentQuestion].flagged;
                },

                startTimer() {
                    const timer = setInterval(() => {
                        if (this.timeRemaining > 0) {
                            this.timeRemaining--;
                        } else {
                            clearInterval(timer);
                            this.submitQuiz();
                        }
                    }, 1000);
                },

                formatTime(seconds) {
                    const hours = Math.floor(seconds / 3600);
                    const minutes = Math.floor((seconds % 3600) / 60);
                    const secs = seconds % 60;

                    if (hours > 0) {
                        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                    }
                    return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                },

                confirmSubmit() {
                    this.showSubmitModal = true;
                },

                async submitQuiz() {
                    try {
                        const answers = this.questions.map(q => ({
                            question_id: q.id,
                            choice_id: q.choices[q.selectedAnswer]?.id || null
                        }));

                        const response = await fetch('{{ route("quiz.submit", $quiz->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                answers: answers,
                                time_taken: {{ $quiz->duration_minutes * 60 }} - this.timeRemaining
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            window.location.href = data.redirect_url;
                        } else {
                            alert('Gagal submit quiz: ' + data.message);
                        }
                    } catch (error) {
                        console.error('Error submitting quiz:', error);
                        alert('Terjadi kesalahan saat submit quiz');
                    }
                },

                async saveProgress() {
                    try {
                        const answers = this.questions.map(q => ({
                            question_id: q.id,
                            choice_id: q.choices[q.selectedAnswer]?.id || null
                        }));

                        await fetch('{{ route("quiz.save-progress", $quiz->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                answers: answers,
                                current_question: this.currentQuestion
                            })
                        });
                    } catch (error) {
                        console.error('Error saving progress:', error);
                    }
                },

                preventCheating() {
                    // Prevent right click
                    document.addEventListener('contextmenu', e => e.preventDefault());

                    // Prevent text selection
                    document.addEventListener('selectstart', e => {
                        if (e.target.closest('.question-card')) {
                            e.preventDefault();
                        }
                    });

                    // Detect tab switch
                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden) {
                            console.warn('Tab switch detected');
                        }
                    });
                }
            }
        }
    </script>
</body>
</html>
