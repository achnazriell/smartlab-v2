@extends('layouts.appSiswa')

@section('content')
    <style>
        /* Loading Screen */
        .loading-screen {
            position: fixed;
            inset: 0;
            background: white;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Question Number Buttons */
        .question-number-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #e5e7eb;
            font-size: 0.875rem;
        }

        .question-number-btn.unanswered {
            background-color: #f8fafc;
            color: #64748b;
            border-color: #e2e8f0;
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
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
            border-color: #3b82f6;
            transform: scale(1.05);
        }

        /* Timer */
        .timer {
            font-size: 1.25rem;
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            min-width: 120px;
            text-align: center;
        }

        .timer.warning {
            background-color: #fef3c7;
            color: #92400e;
            border-color: #f59e0b;
        }

        .timer.critical {
            background-color: #fee2e2;
            color: #991b1b;
            border-color: #ef4444;
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.8; }
            100% { opacity: 1; }
        }

        /* Question Container */
        .question-container {
            max-height: 60vh;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .question-container::-webkit-scrollbar {
            width: 6px;
        }

        .question-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .question-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* Question Text */
        .question-text {
            font-weight: 500;
            color: #1e293b;
            line-height: 1.7;
            font-size: 1.125rem;
            margin-bottom: 1.5rem;
        }

        .question-text img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            margin: 1rem 0;
            border: 1px solid #e2e8f0;
        }

        /* Options */
        .option-button {
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
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
            border-color: #3b82f6;
            background-color: #f8fafc;
            transform: translateY(-1px);
        }

        .option-button.selected {
            border-color: #3b82f6;
            background-color: #eff6ff;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);
        }

        .option-radio {
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 0.125rem;
        }

        .option-button.selected .option-radio {
            border-color: #3b82f6;
            background-color: #8ab7ff;
        }


        .option-text {
            flex: 1;
            color: #475569;
            line-height: 1.5;
        }

        /* Essay Textarea */
        .essay-textarea {
            min-height: 150px;
            max-height: 300px;
            resize: vertical;
        }

        /* Navigation Buttons */
        .nav-button {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .nav-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .question-container {
                max-height: 50vh;
            }

            .question-text {
                font-size: 1rem;
            }

            .timer {
                font-size: 1rem;
                min-width: 100px;
                padding: 0.5rem 0.75rem;
            }

            .nav-button {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }
        }
    </style>

    <!-- Loading Screen -->
    <div id="loadingScreen" class="loading-screen">
        <div class="text-center">
            <div class="loader"></div>
            <p class="text-gray-600 font-medium">Memuat soal...</p>
        </div>
    </div>

    <!-- Main App Container -->
    <div x-data="quizApp()" x-init="init()" class="min-h-screen bg-gradient-to-b from-slate-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
            <!-- Header -->
            <div class="bg-white shadow-lg rounded-2xl p-4 sm:p-8 mb-6 border border-slate-100">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex-1">
                        <h1 class="text-xl sm:text-2xl font-semibold text-slate-900">{{ $exam->title }}</h1>
                        <p class="text-slate-600 text-sm sm:text-base mt-1">
                            {{ $exam->subject->name_subject ?? 'Mata Pelajaran' }}
                        </p>
                    </div>

                    <!-- Progress -->
                    <div class="flex-1 md:text-center">
                        <p class="text-xs font-semibold text-slate-600 mb-2 uppercase tracking-wider">Progres Pengerjaan</p>
                        <div class="w-full bg-slate-200 rounded-full h-2">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all"
                                :style="`width: ${(answeredCount / totalQuestions) * 100}%`"></div>
                        </div>
                        <p class="text-xs text-slate-600 mt-2 font-medium">
                            <span x-text="answeredCount"></span>/<span x-text="totalQuestions"></span> soal dijawab
                        </p>
                    </div>

                    <!-- Timer -->
                    <div class="flex items-center">
                        <div class="timer"
                            :class="{
                                'warning': timeRemaining < 300 && timeRemaining >= 60,
                                'critical': timeRemaining < 60
                            }">
                            <div class="text-xs text-slate-600 uppercase tracking-wider font-semibold mb-1">Waktu</div>
                            <span x-text="formatTime(timeRemaining)" class="text-lg font-mono font-bold text-slate-900"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Question Area -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow-lg rounded-2xl p-4 sm:p-8 border border-slate-100">
                        <!-- Question Number -->
                        <div class="mb-4 pb-4 border-b border-slate-200">
                            <p class="text-sm font-semibold text-blue-600 uppercase tracking-wider">
                                Soal <span x-text="currentQuestionIndex + 1"></span> dari <span x-text="totalQuestions"></span>
                            </p>
                        </div>

                        <!-- Question Container -->
                        <div class="question-container mb-6">
                            <!-- Question Text -->
                            <div class="mb-6">
                                <div class="question-text" x-html="currentQuestion?.question_text || 'Tidak ada soal'"></div>

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
                                    <template x-for="(optionText, optionKey) in currentQuestion.options" :key="optionKey">
                                        <button @click="selectAnswer(optionKey)"
                                            :class="{ 'selected': selectedAnswers[currentQuestion?.id] == optionKey }"
                                            class="option-button w-full">
                                            <div class="option-radio">
                                                <span x-text="String.fromCharCode(65 + Object.keys(currentQuestion.options).indexOf(optionKey))"
                                                    class="text-xs font-bold"></span>
                                            </div>
                                            <span class="option-text" x-text="optionText"></span>
                                        </button>
                                    </template>
                                </template>

                                <!-- Essay Questions -->
                                <template x-if="currentQuestion?.type === 'IS'">
                                    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <p class="text-yellow-800 text-sm mb-3">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Soal essay - jawaban akan diperiksa manual oleh guru
                                        </p>
                                        <textarea x-model="essayAnswers[currentQuestion?.id]"
                                            @input.debounce="saveEssayAnswer(currentQuestion?.id, $event.target.value)"
                                            class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 essay-textarea"
                                            rows="4"
                                            placeholder="Ketik jawaban Anda di sini..."></textarea>
                                    </div>
                                </template>

                                <!-- No Question Found -->
                                <template x-if="!currentQuestion">
                                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                                        <p class="text-red-800 flex items-center">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            Soal tidak ditemukan atau terjadi kesalahan.
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2 flex-wrap">
                            <button @click="markForReview()"
                                class="px-3 py-2 border border-yellow-400 text-yellow-700 rounded-lg hover:bg-yellow-50 transition-colors text-sm font-medium">
                                <i :class="markedForReview.has(currentQuestion?.id) ? 'fas fa-bookmark' : 'far fa-bookmark'" class="mr-1"></i>
                                <span x-text="markedForReview.has(currentQuestion?.id) ? 'Hapus Tanda' : 'Tandai Review'"></span>
                            </button>
                            <button @click="clearAnswer()"
                                class="px-3 py-2 border border-red-400 text-red-700 rounded-lg hover:bg-red-50 transition-colors text-sm font-medium">
                                <i class="fas fa-eraser mr-1"></i>
                                Hapus Jawaban
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Question Navigator -->
                    <div class="bg-white shadow-lg rounded-2xl p-4 border border-slate-100">
                        <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                            <i class="fas fa-th text-blue-600"></i>
                            Navigasi Soal
                        </h3>
                        <div class="grid grid-cols-5 gap-2">
                            <template x-for="(q, index) in questions" :key="q.id">
                                <button @click="goToQuestion(index)"
                                        :class="getQuestionButtonClass(index)"
                                        class="question-number-btn"
                                        x-text="index + 1"
                                        :title="'Soal ' + (index + 1)"></button>
                            </template>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-2xl p-4">
                        <h4 class="font-semibold text-blue-900 mb-3 text-sm flex items-center gap-2">
                            <i class="fas fa-info-circle"></i>
                            Status Soal
                        </h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-slate-300 border border-slate-400 rounded"></div>
                                <span class="text-slate-700">Belum dijawab</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-green-500 border border-green-600 rounded"></div>
                                <span class="text-slate-700">Sudah dijawab</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-yellow-500 border border-yellow-600 rounded"></div>
                                <span class="text-slate-700">Ditandai review</span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button @click="showSubmitModal = true"
                        class="w-full bg-gradient-to-r from-green-600 to-emerald-500 hover:from-green-700 hover:to-emerald-600 text-white font-bold py-3 px-4 rounded-xl transition-all shadow hover:shadow-lg flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                        Kumpulkan Jawaban
                    </button>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex gap-3 mt-6">
                <button @click="previousQuestion()"
                        :disabled="currentQuestionIndex === 0"
                        class="nav-button flex-1 bg-slate-600 hover:bg-slate-700 disabled:bg-slate-400 text-white">
                    <i class="fas fa-arrow-left"></i>
                    <span class="hidden sm:inline">Sebelumnya</span>
                </button>
                <button @click="nextQuestion()"
                        :disabled="currentQuestionIndex === totalQuestions - 1"
                        class="nav-button flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white">
                    <span class="hidden sm:inline">Selanjutnya</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- Submit Modal -->
        <template x-if="showSubmitModal">
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 animate-fadeIn">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-800">Konfirmasi Pengumpulan</h3>
                        <button @click="showSubmitModal = false" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="text-center">
                                <p class="text-xs text-gray-600 mb-1">Soal Dijawab</p>
                                <p class="text-2xl font-bold text-blue-700" x-text="answeredCount"></p>
                                <p class="text-xs text-gray-500">dari <span x-text="totalQuestions"></span> soal</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-600 mb-1">Ditandai Review</p>
                                <p class="text-2xl font-bold text-yellow-600" x-text="markedForReviewCount"></p>
                                <p class="text-xs text-gray-500">perlu diperiksa</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <p class="text-gray-700 mb-4 text-sm">
                            Apakah Anda yakin ingin mengumpulkan jawaban Anda sekarang?
                            <span class="font-semibold">Anda tidak dapat mengubah jawaban setelah dikumpulkan.</span>
                        </p>

                        <div x-show="answeredCount < totalQuestions"
                            class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg mb-4 animate-pulse">
                            <div class="flex items-center gap-2 text-yellow-800 text-sm">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span><span class="font-semibold" x-text="totalQuestions - answeredCount"></span> soal belum terjawab</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button @click="showSubmitModal = false"
                            class="flex-1 bg-slate-500 hover:bg-slate-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                            Lanjutkan
                        </button>
                        <form action="{{ route('soal.submit', $exam->id) }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="answers" :value="JSON.stringify(selectedAnswers)">
                            <button type="submit"
                                class="w-full bg-gradient-to-r from-green-600 to-emerald-500 hover:from-green-700 hover:to-emerald-600 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                                Kumpulkan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <script>
        function quizApp() {
            return {
                questions: @json($questions),
                currentQuestionIndex: 0,
                selectedAnswers: @json($answers ?? []),
                essayAnswers: @json($essayAnswers ?? []),
                markedForReview: new Set(@json($markedForReview ?? [])),
                showSubmitModal: false,
                timeRemaining: {{ $timeRemaining ?? 0 }},
                timerInterval: null,
                lastSaveTime: null,

                get currentQuestion() {
                    return this.questions[this.currentQuestionIndex] || null;
                },

                get totalQuestions() {
                    return this.questions.length;
                },

                get answeredCount() {
                    const mcAnswers = Object.keys(this.selectedAnswers).filter(key =>
                        this.selectedAnswers[key] && this.selectedAnswers[key].trim() !== ''
                    ).length;

                    const essayAnswers = Object.keys(this.essayAnswers).filter(key =>
                        this.essayAnswers[key] && this.essayAnswers[key].trim() !== ''
                    ).length;

                    return mcAnswers + essayAnswers;
                },

                get markedForReviewCount() {
                    return this.markedForReview.size;
                },

                init() {
                    console.log('Quiz initialized with', this.totalQuestions, 'questions');

                    // Hide loading screen
                    setTimeout(() => {
                        const loadingScreen = document.getElementById('loadingScreen');
                        if (loadingScreen) loadingScreen.style.display = 'none';
                    }, 500);

                    // Start timer
                    this.startTimer();

                    // Load saved data
                    this.loadSavedData();

                    // Auto-save interval
                    setInterval(() => this.autoSave(), 15000);

                    // Save before unload
                    window.addEventListener('beforeunload', () => this.saveToLocalStorage());

                    // Handle timer format
                    this.formatTime(this.timeRemaining);
                },

                formatTime(seconds) {
                    const hours = Math.floor(seconds / 3600);
                    const minutes = Math.floor((seconds % 3600) / 60);
                    const secs = seconds % 60;

                    return `${hours.toString().padStart(2, '0')}:${minutes
                        .toString()
                        .padStart(2, '0')}:${secs
                        .toString()
                        .padStart(2, '0')}`;
                },

                selectAnswer(answerKey) {
                    if (!this.currentQuestion) return;

                    this.selectedAnswers[this.currentQuestion.id] = answerKey;
                    this.markedForReview.delete(this.currentQuestion.id);
                    this.saveToLocalStorage();
                },

                saveEssayAnswer(questionId, answer) {
                    if (!questionId) return;

                    if (answer.trim()) {
                        this.essayAnswers[questionId] = answer;
                    } else {
                        delete this.essayAnswers[questionId];
                    }

                    this.saveToLocalStorage();
                },

                markForReview() {
                    if (!this.currentQuestion) return;

                    const questionId = this.currentQuestion.id;
                    if (this.markedForReview.has(questionId)) {
                        this.markedForReview.delete(questionId);
                    } else {
                        this.markedForReview.add(questionId);
                    }

                    this.saveToLocalStorage();
                },

                clearAnswer() {
                    if (!this.currentQuestion) return;

                    const questionId = this.currentQuestion.id;
                    delete this.selectedAnswers[questionId];
                    delete this.essayAnswers[questionId];
                    this.markedForReview.delete(questionId);

                    this.saveToLocalStorage();
                },

                goToQuestion(index) {
                    if (index >= 0 && index < this.totalQuestions) {
                        this.currentQuestionIndex = index;
                        this.saveToLocalStorage();
                    }
                },

                nextQuestion() {
                    if (this.currentQuestionIndex < this.totalQuestions - 1) {
                        this.currentQuestionIndex++;
                        this.saveToLocalStorage();
                    }
                },

                previousQuestion() {
                    if (this.currentQuestionIndex > 0) {
                        this.currentQuestionIndex--;
                        this.saveToLocalStorage();
                    }
                },

                getQuestionButtonClass(index) {
                    const question = this.questions[index];
                    if (!question) return 'unanswered';

                    let classes = '';

                    // Active state
                    if (index === this.currentQuestionIndex) {
                        classes += ' active';
                    }

                    // Status
                    if (this.markedForReview.has(question.id)) {
                        classes += ' marked';
                    } else if (this.selectedAnswers[question.id] || this.essayAnswers[question.id]) {
                        classes += ' answered';
                    } else {
                        classes += ' unanswered';
                    }

                    return classes.trim();
                },

                startTimer() {
                    if (this.timerInterval) {
                        clearInterval(this.timerInterval);
                    }

                    this.timerInterval = setInterval(() => {
                        if (this.timeRemaining > 0) {
                            this.timeRemaining--;

                            // Update every 30 seconds
                            if (this.timeRemaining % 30 === 0) {
                                this.saveTimeToStorage();
                            }

                            // Auto-submit when time is up
                            if (this.timeRemaining === 0) {
                                this.autoSubmit();
                            }
                        }
                    }, 1000);
                },

                saveTimeToStorage() {
                    localStorage.setItem('quiz_time_remaining_{{ $exam->id }}', this.timeRemaining);
                },

                saveToLocalStorage() {
                    try {
                        const saveData = {
                            answers: this.selectedAnswers,
                            essayAnswers: this.essayAnswers,
                            markedForReview: Array.from(this.markedForReview),
                            currentQuestionIndex: this.currentQuestionIndex,
                            timestamp: new Date().toISOString()
                        };

                        localStorage.setItem('quiz_data_{{ $exam->id }}', JSON.stringify(saveData));
                        this.lastSaveTime = Date.now();
                    } catch (error) {
                        console.error('Error saving to localStorage:', error);
                    }
                },

                loadSavedData() {
                    try {
                        const savedData = localStorage.getItem('quiz_data_{{ $exam->id }}');
                        const savedTime = localStorage.getItem('quiz_time_remaining_{{ $exam->id }}');

                        if (savedData) {
                            const data = JSON.parse(savedData);

                            if (data.answers) {
                                Object.assign(this.selectedAnswers, data.answers);
                            }

                            if (data.essayAnswers) {
                                Object.assign(this.essayAnswers, data.essayAnswers);
                            }

                            if (data.markedForReview) {
                                this.markedForReview = new Set(data.markedForReview);
                            }

                            if (data.currentQuestionIndex !== undefined) {
                                this.currentQuestionIndex = data.currentQuestionIndex;
                            }
                        }

                        if (savedTime) {
                            const savedTimeInt = parseInt(savedTime);
                            if (!isNaN(savedTimeInt) && savedTimeInt > 0) {
                                this.timeRemaining = savedTimeInt;
                            }
                        }
                    } catch (error) {
                        console.error('Error loading from localStorage:', error);
                    }
                },

                autoSave() {
                    if (Date.now() - (this.lastSaveTime || 0) > 10000) {
                        this.saveToLocalStorage();
                    }
                },

                autoSubmit() {
                    clearInterval(this.timerInterval);

                    // Show notification
                    if (Notification.permission === 'granted') {
                        new Notification('Waktu Habis!', {
                            body: 'Jawaban akan dikumpulkan otomatis.',
                            icon: '/favicon.ico'
                        });
                    }

                    // Submit form
                    const submitForm = document.querySelector('form[action*="submit"]');
                    if (submitForm) {
                        const answersInput = submitForm.querySelector('input[name="answers"]');
                        if (answersInput) {
                            answersInput.value = JSON.stringify({
                                ...this.selectedAnswers,
                                ...this.essayAnswers
                            });
                        }
                        submitForm.submit();
                    }
                }
            };
        }

        // Handle page visibility change
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                // Page became visible again, refresh timer display if needed
                if (typeof Alpine !== 'undefined') {
                    const quizApp = Alpine.$data(document.querySelector('[x-data="quizApp()"]'));
                    if (quizApp) {
                        quizApp.formatTime(quizApp.timeRemaining);
                    }
                }
            }
        });
    </script>
@endsection
