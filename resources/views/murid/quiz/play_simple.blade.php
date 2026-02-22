<!DOCTYPE html>
<html lang="id" x-data="quizPlayer()" x-init="init()" :class="{ 'dark': settings.darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $quiz->title }} - Quiz</title>
    <link rel="icon" type="image/icon" href="{{ asset('image/logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --primary: #0066FF;
            --primary-light: #4B9DFF;
            --primary-dark: #0052CC;
            --secondary: #E8F0FF;
            --bg-light: #FFFFFF;
            --bg-dark: #0F172A;
            --text-light: #111827;
            --text-dark: #F3F4F6;
            --success: #10B981;
            --danger: #EF4444;
            --sl-blue: #0066FF;
            --sl-blue-light: #E8F0FF;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #FFFFFF 0%, #E8F0FF 100%);
            color: var(--text-light);
            transition: background-color 0.3s ease;
            overflow-x: hidden;
        }

        body.dark {
            background: linear-gradient(135deg, #0F172A 0%, #1a1f3a 100%);
            color: var(--text-dark);
        }

        #security-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.97);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 1.5rem;
        }

        #security-overlay.active {
            display: flex;
        }

        #security-overlay .overlay-icon {
            font-size: 4rem;
            color: #EF4444;
        }

        #security-overlay h2 {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            text-align: center;
        }

        #security-overlay p {
            color: #94A3B8;
            text-align: center;
            max-width: 400px;
        }

        #security-overlay .resume-btn {
            background: #3B82F6;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: background 0.2s;
            margin-top: 0.5rem;
        }

        #security-overlay .resume-btn:hover {
            background: #2563EB;
        }

        #violation-toast {
            display: none;
            position: fixed;
            top: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #EF4444, #DC2626);
            color: white;
            padding: 1rem 2rem;
            border-radius: 14px;
            font-weight: 600;
            font-size: 0.95rem;
            z-index: 99998;
            box-shadow: 0 8px 32px rgba(239, 68, 68, 0.4);
            animation: slideDown 0.3s ease;
            text-align: center;
        }

        #violation-toast.show {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        #fullscreen-prompt {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.98);
            z-index: 99997;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 1.5rem;
        }

        #fullscreen-prompt.active {
            display: flex;
        }

        #fullscreen-prompt h2 {
            color: white;
            font-size: 1.75rem;
            font-weight: 700;
        }

        #fullscreen-prompt p {
            color: #CBD5E1;
            text-align: center;
            max-width: 400px;
        }

        #fullscreen-prompt .enter-fs-btn {
            background: linear-gradient(135deg, #0066FF, #3B82F6);
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
            box-shadow: 0 8px 24px rgba(0, 102, 255, 0.4);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        #fullscreen-prompt .enter-fs-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0, 102, 255, 0.5);
        }

        .quiz-container {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8FBFF 100%);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(0, 102, 255, 0.15);
            border: 2px solid rgba(0, 102, 255, 0.2);
            transition: all 0.3s ease;
        }

        body.dark .quiz-container {
            background: linear-gradient(135deg, #1a2744 0%, #0f172a 100%);
            box-shadow: 0 10px 40px rgba(75, 157, 255, 0.15);
            border: 2px solid rgba(75, 157, 255, 0.3);
        }

        .timer-display {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            border-radius: 14px;
            padding: 1rem 1.75rem;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 140px;
            justify-content: center;
        }

        .timer-display.warning {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.3);
            animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .per-question-timer {
            background: linear-gradient(135deg, #0066FF 0%, #4B9DFF 100%);
            border-radius: 12px;
            padding: 0.65rem 1.2rem;
            color: white;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 102, 255, 0.25);
        }

        .per-question-timer.critical {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
            animation: pulse 0.7s ease infinite;
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

        .answer-option {
            background: linear-gradient(135deg, #FFFFFF 0%, #F8FBFF 100%);
            border: 2px solid #E8F0FF;
            border-radius: 18px;
            padding: 1.75rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            font-size: 0.95rem;
            font-weight: 500;
            line-height: 1.5;
            text-align: left;
            word-wrap: break-word;
            white-space: normal;
        }

        body.dark .answer-option {
            background: linear-gradient(135deg, #1a2744 0%, #162140 100%);
            border-color: rgba(75, 157, 255, 0.3);
        }

        .answer-option:hover:not(.disabled):not(.revealed) {
            border-color: var(--primary-light);
            transform: scale(1.02) translateY(-4px);
            box-shadow: 0 14px 28px rgba(0, 102, 255, 0.25);
        }

        .answer-option.selected:not(.revealed) {
            border-color: #0066FF;
            background: linear-gradient(135deg, #E8F0FF 0%, #D4E4FF 100%);
            box-shadow: 0 10px 25px rgba(0, 102, 255, 0.3);
        }

        .answer-option.correct {
            border-color: var(--success);
            background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);
            animation: slideInCorrect 0.4s ease-out;
        }

        .answer-option.incorrect {
            border-color: var(--danger);
            background: linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.25);
            animation: slideInIncorrect 0.4s ease-out;
        }

        .answer-option.hidden {
            opacity: 0.3;
            pointer-events: none;
        }

        .answer-option.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        @keyframes slideInCorrect {
            from {
                transform: scale(0.95);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        @keyframes slideInIncorrect {
            from {
                transform: translateX(-10px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes bonusFloat {
            0% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }

            100% {
                transform: translateY(-60px) scale(1.3);
                opacity: 0;
            }
        }

        /* Settings Panel */
        .settings-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 9998;
            display: none;
        }

        .settings-overlay.open {
            display: block;
        }

        .settings-panel {
            position: fixed;
            top: 0;
            right: -400px;
            width: 350px;
            height: 100%;
            background: white;
            box-shadow: -5px 0 20px rgba(0, 0, 0, 0.1);
            z-index: 9999;
            transition: right 0.3s ease;
            padding: 1.5rem;
            overflow-y: auto;
        }

        body.dark .settings-panel {
            background: #1F2937;
            color: white;
        }

        .settings-panel.open {
            right: 0;
        }
    </style>
</head>

<body>
    <!-- Security Overlays -->
    <div id="security-overlay">
        <div class="overlay-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h2 id="security-overlay-title">⚠️ Peringatan!</h2>
        <p id="security-overlay-msg">Anda melakukan pelanggaran.</p>
        <button class="resume-btn" @click="resumeFromViolation()">
            <i class="fas fa-play mr-2"></i> Lanjutkan
        </button>
    </div>

    <div id="violation-toast"></div>
    <div id="fullscreen-prompt">
        <h2>🔒 Mode Layar Penuh Diperlukan</h2>
        <p>Quiz ini memerlukan mode layar penuh untuk memastikan pengalaman yang optimal dan aman.</p>
        <button class="enter-fs-btn" @click="enterFullscreen()">
            <i class="fas fa-expand"></i> Masuk Fullscreen
        </button>
    </div>

    <!-- Main Content -->
    <div class="min-h-screen p-4 md:p-6 lg:p-8">
        <div class="max-w-5xl mx-auto">
            <!-- Header -->
            <div class="quiz-container p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold mb-2">{{ $quiz->title }}</h1>
                        <div class="flex flex-wrap gap-3 text-sm">
                            <span class="flex items-center gap-1">
                                <i class="fas fa-book text-blue-500"></i>
                                <span x-text="'Soal ' + (currentQuestion + 1) + ' dari ' + totalQuestions"></span>
                            </span>
                            @if ($quiz->show_score)
                                <span class="flex items-center gap-1">
                                    <i class="fas fa-star text-yellow-500"></i>
                                    <span x-text="'Skor: ' + totalScore.toFixed(0)"></span>
                                </span>
                            @endif
                            <!-- Streak Counter -->
                            <span class="flex items-center gap-1" x-show="streakCount > 0">
                                <i class="fas fa-fire text-orange-500"></i>
                                <span x-text="'Streak: ' + streakCount"></span>
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @if ($quiz->time_per_question > 0)
                            <div class="per-question-timer" :class="{ 'critical': questionTimeRemaining <= 5 }">
                                <i class="fas fa-stopwatch"></i>
                                <span x-text="questionTimeRemaining + 's'"></span>
                            </div>
                        @endif
                        <!-- Leaderboard Button -->
                        @if ($quiz->show_leaderboard)
                            <button @click="showLeaderboardModal = true; loadLeaderboard()"
                                class="p-3 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg">
                                <i class="fas fa-trophy"></i>
                            </button>
                        @endif
                        <!-- Settings Button -->
                        <button @click="toggleSettingsPanel()"
                            class="p-3 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                            <i class="fas fa-cog"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Question -->
            <div class="quiz-container p-6 md:p-8 mb-6"
                :class="{ 'animate-fadeOut': animatingOut, 'animate-fadeIn': animatingIn }">
                <template x-if="questions[currentQuestion]">
                    <div>
                        <div class="mb-6">
                            <h2 class="text-xl md:text-2xl font-semibold mb-4"
                                x-text="questions[currentQuestion].question"></h2>

                            <!-- Multiple Choice -->
                            <template x-if="questions[currentQuestion].type === 'PG'">
                                <div class="space-y-3">
                                    <template x-for="(choice, index) in questions[currentQuestion].choices"
                                        :key="index">
                                        <div class="answer-option"
                                            :class="{
                                                'selected': questions[currentQuestion].selectedAnswer === index && !
                                                    isAnswerRevealed,
                                                'correct': isAnswerRevealed && choice.is_correct,
                                                'incorrect': isAnswerRevealed && questions[currentQuestion]
                                                    .selectedAnswer === index && !choice.is_correct,
                                                'disabled': choice.disabled,
                                                'revealed': isAnswerRevealed
                                            }"
                                            @click="!isAnswerRevealed && !choice.disabled ? selectAnswer(index) : null">
                                            <span class="flex-1" x-text="choice.choice_text"></span>
                                            <template x-if="isAnswerRevealed && choice.is_correct">
                                                <i class="fas fa-check-circle text-green-600 text-xl ml-2"></i>
                                            </template>
                                            <template
                                                x-if="isAnswerRevealed && questions[currentQuestion].selectedAnswer === index && !choice.is_correct">
                                                <i class="fas fa-times-circle text-red-600 text-xl ml-2"></i>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Short Answer -->
                            <template x-if="questions[currentQuestion].type === 'IS'">
                                <div>
                                    <input type="text" x-model="questions[currentQuestion].textAnswer"
                                        :disabled="isAnswerRevealed"
                                        @keyup.enter="!isAnswerRevealed ? submitTextAnswer() : null"
                                        class="w-full p-4 border-2 border-gray-200 dark:border-gray-600 rounded-xl focus:border-blue-500 focus:outline-none"
                                        placeholder="Ketik jawaban Anda...">

                                    <button @click="submitTextAnswer()" x-show="!isAnswerRevealed"
                                        class="mt-4 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">
                                        <i class="fas fa-paper-plane mr-2"></i> Submit Jawaban
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Feedback Message -->
                        <div x-show="feedbackMessage" class="mt-4 p-4 rounded-lg text-center font-semibold"
                            :class="{
                                'bg-green-100 text-green-800': feedbackType === 'correct',
                                'bg-red-100 text-red-800': feedbackType === 'incorrect',
                                'bg-orange-100 text-orange-800': feedbackType === 'timeout'
                            }"
                            x-text="feedbackMessage">
                        </div>
                    </div>
                </template>
            </div>

            <!-- Power-ups (jika enabled) -->
            @if ($quiz->enable_powerups)
                <div class="quiz-container p-4 mb-6">
                    <div class="flex gap-2 overflow-x-auto">
                        <template x-for="(powerup, key) in powerupsRandom" :key="key">
                            <button @click="activatePowerup(key)" :disabled="powerup.cooldown > 0 || isAnswerRevealed"
                                class="flex-shrink-0 px-4 py-2 rounded-lg transition-all"
                                :class="powerup.cooldown > 0 ? 'bg-gray-300 dark:bg-gray-600 cursor-not-allowed' :
                                    'bg-purple-600 hover:bg-purple-700 text-white'">
                                <i :class="powerup.icon" class="mr-2"></i>
                                <span x-text="powerup.cooldown > 0 ? powerup.cooldown + 's' : powerup.name"></span>
                            </button>
                        </template>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Leaderboard Modal -->
    @if ($quiz->show_leaderboard)
        <div x-show="showLeaderboardModal" @click.away="showLeaderboardModal = false"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-xl max-w-2xl w-full p-6" @click.stop>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-2xl font-bold"><i class="fas fa-trophy text-yellow-500 mr-2"></i>Leaderboard Top 5
                    </h3>
                    <button @click="showLeaderboardModal = false"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Loading state -->
                <div x-show="leaderboard.length === 0" class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-500 mb-3"></i>
                    <p class="text-gray-500 dark:text-gray-400">Memuat leaderboard...</p>
                </div>

                <div x-show="leaderboard.length > 0" class="space-y-3">
                    <template x-for="(entry, index) in leaderboard" :key="index">
                        <div class="flex items-center gap-4 p-4 rounded-lg"
                            :class="{
                                'bg-yellow-50 dark:bg-yellow-900 border-2 border-yellow-400': entry.rank === 1,
                                'bg-gray-50 dark:bg-gray-700': entry.rank !== 1
                            }">
                            <div class="text-2xl font-bold w-8 text-center">
                                <span x-show="entry.rank === 1">🥇</span>
                                <span x-show="entry.rank === 2">🥈</span>
                                <span x-show="entry.rank === 3">🥉</span>
                                <span x-show="entry.rank > 3" x-text="'#' + entry.rank"></span>
                            </div>
                            <div class="flex-1">
                                <!-- ✅ FIX: Support student_name, name, dan fallback -->
                                <div class="font-semibold"
                                    x-text="entry.student_name || entry.name || ('Peserta ' + entry.student_id) || 'Unknown'">
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    Skor: <span class="font-bold text-blue-600 dark:text-blue-400"
                                        x-text="(entry.score || 0) + ' pts'"></span>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 text-right">
                                <div x-text="formatTime(entry.time_taken || 0)"></div>
                            </div>
                        </div>
                    </template>

                    @if ($quiz->show_score)
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                            <div class="flex justify-between items-center">
                                <span class="font-medium">Skor Anda:</span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400 text-xl"
                                    x-text="totalScore.toFixed(0) + ' pts'"></span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Settings Panel -->
    <div class="settings-overlay" @click="closeSettingsPanel()" :class="{ 'open': settingsPanelOpen }"></div>
    <div class="settings-panel" :class="{ 'open': settingsPanelOpen }">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold">Pengaturan</h3>
            <button @click="closeSettingsPanel()"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="space-y-4">
            <!-- Dark Mode Toggle -->
            <div class="flex items-center justify-between">
                <span class="font-medium">Mode Gelap</span>
                <button @click="settings.darkMode = !settings.darkMode; toggleDarkMode()"
                    class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none"
                    :class="settings.darkMode ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'">
                    <span class="inline-block w-4 h-4 transform transition-transform bg-white rounded-full"
                        :class="settings.darkMode ? 'translate-x-6' : 'translate-x-1'"></span>
                </button>
            </div>
            <!-- Sound Toggle -->
            <div class="flex items-center justify-between">
                <span class="font-medium">Suara</span>
                <button @click="toggleSound()"
                    class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none"
                    :class="settings.soundEnabled ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600'">
                    <span class="inline-block w-4 h-4 transform transition-transform bg-white rounded-full"
                        :class="settings.soundEnabled ? 'translate-x-6' : 'translate-x-1'"></span>
                </button>
            </div>
            <!-- Text Size -->
            <div class="space-y-2">
                <span class="font-medium">Ukuran Teks</span>
                <div class="flex gap-2">
                    <button @click="settings.textSize = 'small'; localStorage.setItem('quiz_textSize', 'small')"
                        class="px-3 py-1 rounded border"
                        :class="settings.textSize === 'small' ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700'">
                        Kecil
                    </button>
                    <button @click="settings.textSize = 'normal'; localStorage.setItem('quiz_textSize', 'normal')"
                        class="px-3 py-1 rounded border"
                        :class="settings.textSize === 'normal' ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700'">
                        Normal
                    </button>
                    <button @click="settings.textSize = 'large'; localStorage.setItem('quiz_textSize', 'large')"
                        class="px-3 py-1 rounded border"
                        :class="settings.textSize === 'large' ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700'">
                        Besar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Container -->
    <div id="notification-container" class="fixed top-4 right-4 z-[9999] space-y-2"></div>

    <!-- Alpine JS Component -->
    <script>
        window.quizData = {
            totalQuestions: {{ $quiz->questions->count() }},
            quizDuration: {{ $quiz->duration }},
            timePerQuestion: {{ $quiz->time_per_question }},
            timeRemaining: {{ $quiz->duration }},
            instantFeedback: {{ $quiz->instant_feedback ? 'true' : 'false' }},
            showCorrectAnswer: {{ $quiz->show_correct_answer ? 'true' : 'false' }},
            enablePowerups: {{ $quiz->enable_powerups ? 'true' : 'false' }},
            streakBonus: {{ $quiz->streak_bonus ? 'true' : 'false' }},
            timeBonus: {{ $quiz->time_bonus ? 'true' : 'false' }},
            showLeaderboard: {{ $quiz->show_leaderboard ? 'true' : 'false' }},
            fullscreenMode: {{ $quiz->fullscreen_mode ? 'true' : 'false' }},
            blockNewTab: {{ $quiz->block_new_tab ? 'true' : 'false' }},
            preventCopyPaste: {{ $quiz->prevent_copy_paste ? 'true' : 'false' }},
            disableViolations: {{ $quiz->disable_violations ? 'true' : 'false' }},
            enableMusic: false,
            csrfToken: '{{ csrf_token() }}',
            submitUrl: '{{ route('quiz.submit', $quiz->id) }}',
            saveProgressUrl: '{{ route('quiz.save-progress', $quiz->id) }}',
            leaderboardTop5Url: '{{ route('quiz.leaderboard-top5', $quiz->id) }}',
            // ✅ FIX: Gunakan route report-violation (bukan string kosong)
            violationUrl: '{{ route('quiz.report-violation', $quiz->id) }}',
            powerupUrl: '{{ route('quiz.powerups', $quiz->id) }}',
            questions: {!! json_encode(
                $quiz->questions->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'question' => $q->question,
                        'type' => $q->type,
                        'score' => $q->score,
                        'choices' =>
                            $q->type === 'PG'
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
                }),
            ) !!}
        };

        function quizPlayer() {
            return {
                // ============ STATE ============
                currentQuestion: 0,
                totalQuestions: window.quizData.totalQuestions,
                timeRemaining: window.quizData.timeRemaining,
                questionTimeRemaining: window.quizData.timePerQuestion,
                perQuestionTimer: null,
                showLeaderboardModal: false,
                feedbackMessage: '',
                feedbackType: 'success',
                questions: window.quizData.questions,
                isAnswerRevealed: false,
                animatingOut: false,
                animatingIn: false,
                quizFinished: false,
                settingsPanelOpen: false,
                bgMusicPlaying: false,
                bgMusicAudio: null,
                leaderboard: [],

                // ============ SETTINGS ============
                settings: {
                    soundEnabled: localStorage.getItem('quiz_sound') !== 'false',
                    darkMode: localStorage.getItem('quiz_darkMode') === 'true',
                    textSize: localStorage.getItem('quiz_textSize') || 'normal',
                },

                // ============ SCORE & STREAK ============
                totalScore: 0,
                streakCount: 0,
                bonusPoints: 0,
                instantFeedback: window.quizData.instantFeedback,
                showCorrectAnswer: window.quizData.showCorrectAnswer,

                // ============ POWER-UPS ============
                powerups: {
                    double_up: {
                        name: 'Double Up',
                        icon: 'fas fa-times-circle',
                        cooldown: 0,
                        multiplier: 2
                    },
                    triple_up: {
                        name: 'Triple Up',
                        icon: 'fas fa-cubes',
                        cooldown: 0,
                        multiplier: 3
                    },
                    multiplier_2x: {
                        name: '2x Multiplier',
                        icon: 'fas fa-bolt',
                        cooldown: 0
                    },
                    immunity: {
                        name: 'Immunity',
                        icon: 'fas fa-shield-alt',
                        cooldown: 0
                    },
                    streak_saver: {
                        name: 'Streak Saver',
                        icon: 'fas fa-sync-alt',
                        cooldown: 0
                    },
                    eraser: {
                        name: 'Eraser',
                        icon: 'fas fa-eraser',
                        cooldown: 0
                    },
                    supersonic: {
                        name: 'Supersonic',
                        icon: 'fas fa-rocket',
                        cooldown: 0
                    },
                    gift: {
                        name: 'Gift',
                        icon: 'fas fa-gift',
                        cooldown: 0
                    },
                    freeze: {
                        name: 'Freeze',
                        icon: 'fas fa-snowflake',
                        cooldown: 0
                    },
                    glitch: {
                        name: 'Glitch',
                        icon: 'fas fa-eye-slash',
                        cooldown: 0
                    },
                },
                powerupsRandom: {},
                nextQuestionMultiplier: 1,
                activeMultiplier: 1,
                multiplierExpiresAt: null,
                multiplierCountdown: 0,
                immunityActive: false,
                streakSaverActive: false,
                supersonicActive: false,

                // ============ SECURITY ============
                violationCount: 0,
                _securityBlocked: false,

                // ============ GETTERS ============
                get answeredCount() {
                    return this.questions.filter(q =>
                        q.selectedAnswer !== null || (q.textAnswer && q.textAnswer.trim())
                    ).length;
                },

                // ========================================================
                // INIT
                // ========================================================
                init() {
                    window.quizPlayerInstance = this;

                    this.selectRandomPowerups();
                    document.documentElement.classList.toggle('dark', this.settings.darkMode);

                    this.startPerQuestionTimer();
                    this.loadProgress();
                    this.preloadSounds();
                    this.initSecurityListeners();

                    if (window.quizData.showLeaderboard) {
                        this.loadLeaderboard().catch(e => console.warn('Leaderboard init error:', e));
                    }

                    if (window.quizData.fullscreenMode) {
                        if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                            document.getElementById('fullscreen-prompt').classList.add('active');
                        }
                    }

                    if (window.quizData.enableMusic) {
                        this.initBgMusic();
                    }

                    setInterval(() => {
                        for (let key in this.powerupsRandom) {
                            if (this.powerupsRandom[key].cooldown > 0) this.powerupsRandom[key].cooldown--;
                        }
                    }, 1000);

                    setInterval(() => {
                        if (this.multiplierExpiresAt) {
                            const remaining = Math.max(0, Math.ceil((this.multiplierExpiresAt - Date.now()) /
                            1000));
                            this.multiplierCountdown = remaining;
                            if (remaining <= 0) {
                                this.activeMultiplier = 1;
                                this.multiplierExpiresAt = null;
                            }
                        }
                    }, 500);
                },

                // ========================================================
                // FULLSCREEN
                // ========================================================
                enterFullscreen() {
                    const elem = document.documentElement;
                    const promise = elem.requestFullscreen?.() ||
                        elem.webkitRequestFullscreen?.() ||
                        elem.msRequestFullscreen?.();
                    if (promise) {
                        promise.then(() => {
                            document.getElementById('fullscreen-prompt').classList.remove('active');
                            this._securityBlocked = false;
                        }).catch(() => {
                            this.showNotification('Gagal masuk fullscreen. Coba tekan F11.', 'warning');
                        });
                    } else {
                        document.getElementById('fullscreen-prompt').classList.remove('active');
                    }
                },

                exitFullscreenDetected() {
                    if (this.quizFinished || !window.quizData.fullscreenMode) return;
                    this._securityBlocked = true;
                    this.showSecurityOverlay(
                        '⚠️ Keluar dari Layar Penuh!',
                        'Anda keluar dari mode layar penuh. Kembali untuk melanjutkan quiz.'
                    );
                    this.reportViolation('fullscreen_exit');
                },

                resumeFromViolation() {
                    const overlay = document.getElementById('security-overlay');
                    overlay.classList.remove('active');
                    this._securityBlocked = false;
                    if (window.quizData.fullscreenMode) this.enterFullscreen();
                },

                showSecurityOverlay(title, msg) {
                    document.getElementById('security-overlay-title').textContent = title;
                    document.getElementById('security-overlay-msg').textContent = msg;
                    document.getElementById('security-overlay').classList.add('active');
                },

                // ========================================================
                // SECURITY LISTENERS
                // ========================================================
                initSecurityListeners() {
                    if (window.quizData.disableViolations) return;

                    if (window.quizData.fullscreenMode) {
                        const fsEvents = ['fullscreenchange', 'webkitfullscreenchange', 'mozfullscreenchange',
                            'MSFullscreenChange'
                        ];
                        fsEvents.forEach(ev => {
                            document.addEventListener(ev, () => {
                                if (!document.fullscreenElement && !document.webkitFullscreenElement) {
                                    if (!this.quizFinished) this.exitFullscreenDetected();
                                } else {
                                    document.getElementById('fullscreen-prompt').classList.remove('active');
                                }
                            });
                        });
                        document.addEventListener('keydown', (e) => {
                            if (this.quizFinished) return;
                            if (e.key === 'Escape') {
                                e.preventDefault();
                                e.stopPropagation();
                            }
                        }, true);
                    }

                    if (window.quizData.blockNewTab) {
                        document.addEventListener('visibilitychange', () => {
                            if (document.hidden && !this.quizFinished) this.handleViolation('tab_switch',
                                'Berpindah ke tab lain');
                        });
                        window.addEventListener('blur', () => {
                            if (!this.quizFinished && !this._securityBlocked) this.handleViolation('window_blur',
                                'Jendela tidak fokus');
                        });
                        document.addEventListener('keydown', (e) => {
                            if (this.quizFinished) return;
                            if ((e.ctrlKey || e.metaKey) && ['n', 't', 'w'].includes(e.key.toLowerCase())) {
                                e.preventDefault();
                                this.showViolationToast('⛔ Membuka tab/jendela baru tidak diizinkan!');
                                this.handleViolation('new_tab_attempt', 'Mencoba buka tab baru');
                            }
                            if (e.key === 'F5' || ((e.ctrlKey || e.metaKey) && e.key === 'r')) {
                                e.preventDefault();
                                this.showViolationToast('⛔ Refresh halaman tidak diizinkan!');
                            }
                        });
                        document.addEventListener('contextmenu', (e) => {
                            if (!this.quizFinished) e.preventDefault();
                        });
                    }

                    if (window.quizData.preventCopyPaste) {
                        document.addEventListener('copy', (e) => {
                            e.preventDefault();
                            this.handleViolation('copy', 'Mencoba copy');
                        });
                        document.addEventListener('cut', (e) => {
                            e.preventDefault();
                            this.handleViolation('cut', 'Mencoba cut');
                        });
                        document.addEventListener('paste', (e) => {
                            e.preventDefault();
                            this.handleViolation('paste', 'Mencoba paste');
                        });
                        document.addEventListener('keydown', (e) => {
                            if (this.quizFinished) return;
                            if ((e.ctrlKey || e.metaKey) && ['c', 'v', 'x', 'a', 'u'].includes(e.key
                            .toLowerCase())) {
                                e.preventDefault();
                                this.showViolationToast('⛔ Copy/paste tidak diizinkan!');
                                if (['c', 'v', 'x'].includes(e.key.toLowerCase())) this.handleViolation(
                                    'copy_paste_shortcut', 'Shortcut copy/paste');
                            }
                        });
                    }

                    document.addEventListener('keydown', (e) => {
                        if (this.quizFinished) return;
                        if (e.key === 'F12' || ((e.ctrlKey || e.metaKey) && e.shiftKey && ['i', 'j', 'c'].includes(e
                                .key.toLowerCase()))) {
                            e.preventDefault();
                            this.showViolationToast('⛔ Developer tools diblokir!');
                        }
                    });
                },

                showViolationToast(msg) {
                    const toast = document.getElementById('violation-toast');
                    toast.textContent = msg;
                    toast.classList.add('show');
                    clearTimeout(this._toastTimeout);
                    this._toastTimeout = setTimeout(() => toast.classList.remove('show'), 2500);
                },

                async reportViolation(type, details = null) {
                    if (this.quizFinished) return;
                    try {
                        const res = await fetch(window.quizData.violationUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': window.quizData.csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                type,
                                details
                            })
                        });
                        const data = await res.json().catch(() => ({}));
                        // Jika auto_submit diminta dari server, submit sekarang
                        if (data.auto_submit) {
                            this.showNotification('⛔ Batas pelanggaran tercapai! Quiz disubmit otomatis.', 'error');
                            setTimeout(() => this.submitQuiz(), 1500);
                        }
                    } catch (e) {}
                },

                // ✅ FIX: handleViolation menggunakan violationUrl yang sudah mengarah ke report-violation
                async handleViolation(type, details = null) {
                    if (this.quizFinished) return;
                    this.violationCount++;
                    this.showViolationToast(`⚠️ Pelanggaran #${this.violationCount}: ${details || type}`);
                    try {
                        // ✅ FIX: Gunakan violationUrl yang mengarah ke report-violation (sync ke room guru)
                        const res = await fetch(window.quizData.violationUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': window.quizData.csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                type,
                                details
                            })
                        });
                        const data = await res.json().catch(() => ({}));
                        if (data.violation_count) this.violationCount = data.violation_count;
                        // Jika auto_submit diminta dari server
                        if (data.auto_submit) {
                            this.showNotification('⛔ Batas pelanggaran tercapai! Quiz disubmit otomatis.', 'error');
                            setTimeout(() => this.submitQuiz(), 1500);
                        }
                    } catch (e) {
                        console.warn('Violation report error:', e);
                    }
                },

                // ========================================================
                // TIMER PER SOAL
                // ========================================================
                startPerQuestionTimer() {
                    if (this.perQuestionTimer) clearInterval(this.perQuestionTimer);
                    this.questionTimeRemaining = window.quizData.timePerQuestion;
                    this.perQuestionTimer = setInterval(() => {
                        if (this.quizFinished || this._securityBlocked) return;
                        if (!this.isAnswerRevealed && this.questionTimeRemaining > 0) {
                            this.questionTimeRemaining--;
                            if (this.questionTimeRemaining === 10) this.playSound('countdown');
                        } else if (this.questionTimeRemaining <= 0 && !this.isAnswerRevealed) {
                            this.playSound('timeout');
                            this.autoSubmitWrong();
                        }
                    }, 1000);
                },

                autoSubmitWrong() {
                    clearInterval(this.perQuestionTimer);
                    this.feedbackMessage = '⏰ Waktu habis!';
                    this.feedbackType = 'timeout';
                    this.streakCount = 0;
                    this.isAnswerRevealed = true;
                    setTimeout(() => this.nextQuestion(), 1500);
                },

                // ========================================================
                // JAWABAN - DENGAN AUTO SUBMIT DAN AUTO NEXT
                // ========================================================
                async selectAnswer(choiceIndex) {
                    if (this._securityBlocked) return;
                    const question = this.questions[this.currentQuestion];
                    if (this.isAnswerRevealed || question.selectedAnswer !== null) return;

                    question.selectedAnswer = choiceIndex;
                    this.playSound('click');

                    const choice = question.choices[choiceIndex];
                    const isCorrect = choice?.is_correct || false;
                    let earnedScore = 0;

                    if (isCorrect) {
                        earnedScore = question.score || 1;
                        if (this.activeMultiplier > 1) earnedScore *= this.activeMultiplier;
                        if (this.nextQuestionMultiplier > 1) {
                            earnedScore *= this.nextQuestionMultiplier;
                            this.nextQuestionMultiplier = 1;
                        }
                        if (this.supersonicActive) {
                            earnedScore *= 2;
                            this.supersonicActive = false;
                        }
                        this.totalScore += earnedScore;
                        this.streakCount++;

                        if (window.quizData.timeBonus && this.questionTimeRemaining > 0) {
                            const tb = Math.floor(this.questionTimeRemaining * 0.5);
                            this.totalScore += tb;
                            this.bonusPoints += tb;
                            if (tb > 0) this.showBonusPopup('+' + tb + ' ⚡ Time Bonus!');
                        }

                        if (window.quizData.streakBonus && this.streakCount > 0 && this.streakCount % 3 === 0) {
                            const sb = Math.floor(earnedScore * 0.5);
                            this.totalScore += sb;
                            this.bonusPoints += sb;
                            this.showBonusPopup('🔥 Streak Bonus +' + sb + '!');
                        }
                        if (earnedScore > 0) this.showBonusPopup('+' + Math.floor(earnedScore) + ' pts');
                    } else {
                        if (this.immunityActive) {
                            this.immunityActive = false;
                            this.playSound('immunity');
                            this.showNotification('🛡️ Immunity melindungimu!', 'success');
                        } else if (this.streakSaverActive) {
                            this.streakSaverActive = false;
                            this.streakCount = Math.max(0, this.streakCount - 1);
                            this.playSound('streak_saver');
                            this.showNotification('🔄 Streak Saver aktif! Streak hanya berkurang 1.', 'info');
                        } else {
                            this.streakCount = 0;
                        }
                    }

                    await this.saveProgress();

                    this.feedbackType = isCorrect ? 'correct' : 'incorrect';
                    this.playSound(isCorrect ? 'correct' : 'incorrect');

                    this.isAnswerRevealed = true;

                    // ✅ AUTO NEXT: Langsung ke soal berikutnya setelah delay singkat
                    // ✅ Jika ini soal terakhir, langsung submit
                    if (this.currentQuestion < this.totalQuestions - 1) {
                        setTimeout(() => this.nextQuestion(), 800);
                    } else {
                        // ✅ Soal terakhir - AUTO SUBMIT
                        setTimeout(() => this.submitQuiz(), 1000);
                    }
                },

                async submitTextAnswer() {
                    const question = this.questions[this.currentQuestion];
                    if (!question.textAnswer || !question.textAnswer.trim()) return;
                    question.selectedAnswer = 0;
                    this.playSound('click');
                    await this.saveProgress();
                    this.feedbackMessage = '📝 Jawaban disimpan';
                    this.feedbackType = 'correct';
                    this.isAnswerRevealed = true;

                    // ✅ AUTO NEXT untuk short answer
                    if (this.currentQuestion < this.totalQuestions - 1) {
                        setTimeout(() => this.nextQuestion(), 800);
                    } else {
                        // ✅ Soal terakhir - AUTO SUBMIT
                        setTimeout(() => this.submitQuiz(), 1000);
                    }
                },

                // ========================================================
                // POWER-UPS
                // ========================================================
                selectRandomPowerups() {
                    const powerupKeys = Object.keys(this.powerups);
                    const shuffled = powerupKeys.sort(() => 0.5 - Math.random()).slice(0, 3);
                    this.powerupsRandom = {};
                    shuffled.forEach(key => {
                        this.powerupsRandom[key] = {
                            ...this.powerups[key]
                        };
                    });
                },

                async activatePowerup(type) {
                    if (!window.quizData.enablePowerups) return;
                    if (!this.powerupsRandom.hasOwnProperty(type)) {
                        this.showNotification('⚠️ Power-up tidak tersedia', 'warning');
                        return;
                    }
                    if (this.powerupsRandom[type].cooldown > 0) {
                        this.showNotification(`⏳ Cooldown ${this.powerupsRandom[type].cooldown}s`, 'warning');
                        return;
                    }
                    if (this.isAnswerRevealed || this.quizFinished) return;

                    this.playSound('powerup');

                    try {
                        const response = await fetch(window.quizData.powerupUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.quizData.csrfToken,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                powerup_type: type,
                                question_id: this.questions[this.currentQuestion]?.id ?? null
                            })
                        });

                        if (!response.ok) {
                            const errData = await response.json().catch(() => ({}));
                            this.showNotification(errData.message || `Error ${response.status}`, 'error');
                            return;
                        }

                        const data = await response.json();

                        if (data.success) {
                            this.powerupsRandom[type].cooldown = data.cooldown || 10;

                            if (data.eraser_choice_index !== undefined) {
                                this.questions[this.currentQuestion].choices[data.eraser_choice_index].disabled = true;
                            }
                            if (data.bonus) {
                                this.totalScore += data.bonus;
                                this.bonusPoints += data.bonus;
                                this.showBonusPopup('🎁 +' + data.bonus + ' Gift!');
                            }
                            if (data.next_question_multiplier) {
                                this.nextQuestionMultiplier = data.next_question_multiplier;
                            }
                            if (data.active_multiplier && data.duration) {
                                this.activeMultiplier = data.active_multiplier;
                                this.multiplierExpiresAt = Date.now() + (data.duration * 1000);
                            }
                            if (data.immunity_active) this.immunityActive = true;
                            if (data.streak_saver_active) this.streakSaverActive = true;
                            if (data.supersonic_active) this.supersonicActive = true;
                            if (data.freeze_timer) {
                                this.questionTimeRemaining = window.quizData.timePerQuestion;
                                this.playSound('freeze');
                            }
                            if (data.glitch) {
                                this.playSound('glitch');
                                const q = this.questions[this.currentQuestion];
                                if (q && q.choices) {
                                    q.choices = [...q.choices].sort(() => Math.random() - 0.5);
                                }
                            }

                            this.showNotification('⚡ ' + (data.message || 'Power-up aktif!'), 'success');
                        } else {
                            this.showNotification(data.message || 'Gagal mengaktifkan', 'error');
                        }
                    } catch (e) {
                        console.error('Powerup error:', e);
                        this.showNotification('Gagal mengaktifkan power-up', 'error');
                    }
                },

                // ========================================================
                // NAVIGASI & SUBMIT OTOMATIS
                // ========================================================
                nextQuestion() {
                    if (this.currentQuestion < this.totalQuestions - 1) {
                        this.animatingOut = true;
                        setTimeout(() => {
                            this.currentQuestion++;
                            this.isAnswerRevealed = false;
                            this.feedbackMessage = '';
                            this.animatingOut = false;
                            this.animatingIn = true;
                            setTimeout(() => {
                                this.animatingIn = false;
                            }, 400);
                            this.startPerQuestionTimer();
                        }, 400);
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        });
                    } else {
                        this.submitQuiz();
                    }
                },

                // ========================================================
                // SUBMIT
                // ========================================================
                async submitQuiz() {
                    this.quizFinished = true;
                    if (this.perQuestionTimer) clearInterval(this.perQuestionTimer);

                    document.getElementById('security-overlay').classList.remove('active');

                    if (document.fullscreenElement) {
                        document.exitFullscreen?.().catch(() => {});
                    }

                    const answers = {};
                    this.questions.forEach((q) => {
                        if (q.type === 'IS' && q.textAnswer) {
                            answers[q.id] = q.textAnswer;
                        } else if (q.selectedAnswer !== null && q.choices) {
                            const choice = q.choices[q.selectedAnswer];
                            if (choice) answers[q.id] = choice.id;
                        }
                    });

                    try {
                        this.showNotification('📤 Menyimpan jawaban...', 'info');
                        const response = await fetch(window.quizData.submitUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.quizData.csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                answers: JSON.stringify(answers),
                                time_spent: window.quizData.quizDuration - this.timeRemaining,
                                streak_count: this.streakCount,
                                bonus_points: this.bonusPoints,
                                total_score: this.totalScore
                            })
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.playSound('victory');

                            // ✅ FIX: Load leaderboard setelah submit berhasil sebelum redirect
                            if (window.quizData.showLeaderboard) {
                                try {
                                    await this.loadLeaderboard();
                                    if (this.leaderboard.length > 0) {
                                        this.showLeaderboardModal = true;
                                        // ✅ Redirect setelah 4 detik tampil leaderboard
                                        setTimeout(() => {
                                            window.location.href = data.redirect;
                                        }, 4000);
                                        return;
                                    }
                                } catch (e) {
                                    /* langsung redirect jika error */ }
                            }

                            window.location.href = data.redirect;
                        } else {
                            this.quizFinished = false;
                            this.showNotification('Gagal submit: ' + (data.message || 'Error'), 'error');
                        }

                        if (typeof window.roomDataInstance !== 'undefined' && window.roomDataInstance.loadRoomData) {
                            window.roomDataInstance.loadRoomData();
                        }

                    } catch (error) {
                        this.quizFinished = false;
                        console.error('Submit error:', error);
                        this.showNotification('Terjadi kesalahan saat submit. Coba lagi.', 'error');
                    }
                },

                // ========================================================
                // SAVE & LOAD PROGRESS
                // ========================================================
                async saveProgress() {
                    try {
                        const answers = this.questions.map(q => ({
                            question_id: q.id,
                            choice_id: q.choices?.[q.selectedAnswer]?.id || null,
                            text_answer: q.textAnswer || null,
                        }));
                        await fetch(window.quizData.saveProgressUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.quizData.csrfToken
                            },
                            body: JSON.stringify({
                                answers: answers,
                                current_question: this.currentQuestion,
                                time_remaining: this.timeRemaining,
                                total_score: this.totalScore,
                                streak_count: this.streakCount,
                                bonus_points: this.bonusPoints
                            })
                        });
                    } catch (e) {
                        console.warn('Save progress error:', e);
                    }
                },

                async loadProgress() {
                    try {
                        const response = await fetch(window.quizData.saveProgressUrl, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': window.quizData.csrfToken
                            }
                        });
                        if (response.ok) {
                            const data = await response.json();
                            if (data.success && data.progress) {
                                const p = data.progress;
                                this.currentQuestion = p.current_question || 0;
                                this.timeRemaining = p.time_remaining || this.timeRemaining;
                                this.totalScore = p.total_score || 0;
                                this.streakCount = p.streak_count || 0;
                                this.bonusPoints = p.bonus_points || 0;
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
                        }
                    } catch (e) {
                        console.warn('Load progress error:', e);
                    }
                },

                // ========================================================
                // LEADERBOARD
                // ========================================================
                async loadLeaderboard() {
                    if (!window.quizData.showLeaderboard) return;
                    try {
                        const res = await fetch(window.quizData.leaderboardTop5Url, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': window.quizData.csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });
                        if (!res.ok) throw new Error(`HTTP ${res.status}`);
                        const data = await res.json();
                        console.log('🏆 Leaderboard API Response:', data);
                        if (data.success && Array.isArray(data.leaderboard)) {
                            this.leaderboard = data.leaderboard;
                            console.log('✅ Leaderboard loaded:', this.leaderboard.length, 'entries');
                        } else {
                            console.warn('⚠️ Leaderboard data kosong atau tidak valid:', data);
                            this.leaderboard = [];
                        }
                    } catch (e) {
                        console.warn('❌ Leaderboard error:', e);
                        this.leaderboard = [];
                    }
                },

                // ========================================================
                // MUSIK
                // ========================================================
                initBgMusic() {
                    const tracks = ['/sounds/bg_music_1.mp3', '/sounds/bg_music_2.mp3'];
                    const track = tracks[Math.floor(Math.random() * tracks.length)];
                    this.bgMusicAudio = new Audio(track);
                    this.bgMusicAudio.loop = true;
                    this.bgMusicAudio.volume = 0.25;
                },
                toggleBgMusic() {
                    if (!this.bgMusicAudio) return;
                    if (this.bgMusicPlaying) {
                        this.bgMusicAudio.pause();
                        this.bgMusicPlaying = false;
                    } else {
                        this.bgMusicAudio.play().catch(() => {});
                        this.bgMusicPlaying = true;
                    }
                },

                // ========================================================
                // UTILITAS
                // ========================================================
                formatTime(seconds) {
                    if (seconds < 0) seconds = 0;
                    const mins = Math.floor(seconds / 60);
                    const secs = seconds % 60;
                    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                },

                toggleSound() {
                    this.settings.soundEnabled = !this.settings.soundEnabled;
                    localStorage.setItem('quiz_sound', this.settings.soundEnabled);
                    if (this.settings.soundEnabled) this.playSound('click');
                },

                playSound(type = 'click') {
                    if (!this.settings.soundEnabled) return;
                    const soundMap = {
                        click: '/sounds/click.mp3',
                        correct: '/sounds/correct.mp3',
                        incorrect: '/sounds/incorrect.mp3',
                        powerup: '/sounds/powerup.mp3',
                        victory: '/sounds/victory.mp3',
                        countdown: '/sounds/countdown.mp3',
                        freeze: '/sounds/freeze.mp3',
                        glitch: '/sounds/glitch.mp3',
                        gift: '/sounds/gift.mp3',
                        immunity: '/sounds/immunity.mp3',
                        streak_saver: '/sounds/streak_saver.mp3',
                        timeout: '/sounds/timeout.mp3',
                    };
                    if (soundMap[type]) {
                        try {
                            new Audio(soundMap[type]).play().catch(() => {});
                        } catch (e) {}
                    }
                },

                preloadSounds() {
                    if (!this.settings.soundEnabled) return;
                    ['click', 'correct', 'incorrect', 'powerup', 'victory', 'countdown', 'timeout']
                    .forEach(t => new Audio(`/sounds/${t}.mp3`).load());
                },

                toggleDarkMode() {
                    localStorage.setItem('quiz_darkMode', this.settings.darkMode);
                    document.documentElement.classList.toggle('dark', this.settings.darkMode);
                },

                toggleSettingsPanel() {
                    this.settingsPanelOpen = !this.settingsPanelOpen;
                },
                closeSettingsPanel() {
                    this.settingsPanelOpen = false;
                },

                showBonusPopup(msg) {
                    const div = document.createElement('div');
                    div.className = 'bonus-popup';
                    div.textContent = msg;
                    div.style.cssText = `
                        position: fixed;
                        left: ${40 + Math.random() * 20}%;
                        top: 50%;
                        transform: translateY(-50%);
                        pointer-events: none;
                        z-index: 9999;
                        font-size: 1.4rem;
                        font-weight: 800;
                        color: #F59E0B;
                        text-shadow: 0 2px 8px rgba(0,0,0,.4);
                        animation: bonusFloat 1.5s ease-out forwards;
                    `;
                    document.body.appendChild(div);
                    setTimeout(() => div.remove(), 1600);
                },

                showNotification(message, type = 'info') {
                    const container = document.getElementById('notification-container');
                    const toast = document.createElement('div');
                    const bg = type === 'success' ? 'bg-green-600' :
                        type === 'error' ? 'bg-red-600' :
                        type === 'warning' ? 'bg-orange-500' :
                        'bg-blue-600';
                    toast.className =
                        `px-4 py-3 rounded-xl shadow-xl text-white font-semibold text-sm ${bg} transform transition-all duration-300 translate-x-0`;
                    toast.textContent = message;
                    container.appendChild(toast);
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        toast.style.transform = 'translateX(100%)';
                        setTimeout(() => toast.remove(), 300);
                    }, 3000);
                },
            };
        }
    </script>
</body>

</html>
