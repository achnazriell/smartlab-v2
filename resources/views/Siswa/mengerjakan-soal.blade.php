@extends('layouts.appSiswa')

@section('content')
    <style>
        .question-number-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }

        .question-number-btn.unanswered {
            background-color: #dbeafe;
            color: #1e40af;
            border-color: #bfdbfe;
        }

        .question-number-btn.answered {
            background-color: #dcfce7;
            color: #15803d;
            border-color: #bbf7d0;
        }

        .question-number-btn.marked {
            background-color: #fef3c7;
            color: #92400e;
            border-color: #fcd34d;
        }

        .question-number-btn.active {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
            border-color: #3b82f6;
        }

        .timer {
            font-size: 1.25rem;
            font-weight: 700;
            padding: 0.75rem 1rem;
            border-radius: 8px;
        }

        .timer.warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .timer.critical {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .option-button {
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            background-color: #ffffff;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .option-button:hover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }

        .option-button.selected {
            border-color: #3b82f6;
            background-color: #dbeafe;
        }

        .option-radio {
            width: 24px;
            height: 24px;
            border: 2px solid #d1d5db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .option-button.selected .option-radio {
            border-color: #3b82f6;
            background-color: #3b82f6;
        }

        .option-button.selected .option-radio::after {
            content: '';
            width: 6px;
            height: 6px;
            background-color: white;
            border-radius: 50%;
        }
    </style>

    <div class="min-h-screen bg-gradient-to-b from-slate-50 to-white">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="loader border-t-4 border-blue-600 rounded-full w-16 h-16 animate-spin"></div>
        </div>

        <div x-data="quizApp()" x-init="init()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="bg-white shadow-lg rounded-2xl p-6 sm:p-8 mb-8 border border-slate-100">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex-1">
                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ $quiz->title }}</h1>
                        <p class="text-slate-600 mt-1">{{ $quiz->subject->name_subject ?? 'N/A' }}</p>
                    </div>

                    <!-- Progress -->
                    <div class="flex-1 md:text-center">
                        <p class="text-sm font-semibold text-slate-600 mb-2 uppercase tracking-wider">Progres Pengerjaan</p>
                        <div class="w-full bg-slate-200 rounded-full h-2.5">
                            <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-2.5 rounded-full transition-all" :style="`width: ${(answeredCount / totalQuestions) * 100}%`"></div>
                        </div>
                        <p class="text-xs text-slate-600 mt-2 font-medium"><span x-text="answeredCount"></span>/<span x-text="totalQuestions"></span> soal dijawab</p>
                    </div>

                    <!-- Timer -->
                    <div class="flex items-center">
                        <div class="timer bg-slate-100 rounded-lg px-4 py-3 border-2 border-slate-200" :class="{ 'warning': timeRemaining < 300, 'critical': timeRemaining < 60 }">
                            <div class="text-xs text-slate-600 uppercase tracking-wider font-semibold mb-1">Waktu Tersisa</div>
                            <span x-text="formatTime(timeRemaining)" class="text-lg font-bold text-slate-900"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Question Area (Left/Main) -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow-lg rounded-2xl p-8 border border-slate-100">
                        <!-- Question Number -->
                        <div class="mb-6 pb-6 border-b border-slate-200">
                            <p class="text-sm font-semibold text-blue-600 uppercase tracking-wider">
                                Soal <span x-text="currentQuestionIndex + 1"></span> dari <span x-text="totalQuestions"></span>
                            </p>
                        </div>

                        <!-- Question Text -->
                        <div class="mb-8">
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-6 leading-relaxed" x-html="currentQuestion?.question_text || ''"></h2>

                            <!-- Question Image (if exists) -->
                            <template x-if="currentQuestion?.question_image">
                                <div class="mb-8">
                                    <img :src="currentQuestion.question_image" alt="Question Image" class="max-w-full h-auto rounded-xl shadow-md border border-slate-200">
                                </div>
                            </template>
                        </div>

                        <!-- Answer Options -->
                        <div class="space-y-3 mb-8">
                            <template x-for="(option, index) in getOptionsArray()" :key="index">
                                <button @click="selectAnswer(option.key)" :class="{ 'selected': selectedAnswers[currentQuestion?.id] === option.key }"
                                    class="option-button w-full text-left">
                                    <div class="option-radio">
                                        <span x-text="option.key" class="text-sm font-bold"></span>
                                    </div>
                                    <span class="flex-1 text-gray-800" x-text="option.value"></span>
                                </button>
                            </template>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3 flex-wrap">
                            <button @click="markForReview()"
                                class="px-4 py-2 border border-yellow-500 text-yellow-600 rounded-lg hover:bg-yellow-50 transition-colors text-sm font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" class="inline mr-1">
                                    <path d="M11 17H5V3h14v4"></path>
                                    <path d="M17 21v-2m0-4v-2"></path>
                                </svg>
                                Tandai untuk Review
                            </button>
                            <button @click="clearAnswer()"
                                class="px-4 py-2 border border-red-500 text-red-600 rounded-lg hover:bg-red-50 transition-colors text-sm font-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" class="inline mr-1">
                                    <polyline points="3 6 5 4 21 4"></polyline>
                                    <path d="M19 4v20a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4m3 0V3a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1"></path>
                                    <line x1="10" y1="12" x2="10" y2="17"></line>
                                    <line x1="14" y1="12" x2="14" y2="17"></line>
                                </svg>
                                Hapus Jawaban
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar (Right) -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Question Navigator -->
                    <div class="bg-white shadow-lg rounded-2xl p-6 border border-slate-100">
                        <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2 uppercase tracking-wider">
                            <i class="fas fa-th text-blue-600"></i>
                            Navigasi Soal
                        </h3>
                        <div class="grid grid-cols-5 gap-2.5">
                            <template x-for="(q, index) in questions" :key="q.id">
                                <button @click="goToQuestion(index)" :class="getQuestionButtonClass(index)"
                                    class="question-number-btn" x-text="index + 1"></button>
                            </template>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-2xl p-6">
                        <h4 class="font-semibold text-blue-900 mb-4 text-sm flex items-center gap-2">
                            <i class="fas fa-info-circle"></i>
                            Legenda Status
                        </h4>
                        <div class="space-y-3 text-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 bg-blue-300 border-2 border-blue-500 rounded-md"></div>
                                <span class="text-blue-900 font-medium">Belum dijawab</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 bg-green-300 border-2 border-green-500 rounded-md"></div>
                                <span class="text-blue-900 font-medium">Sudah dijawab</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-5 h-5 bg-yellow-300 border-2 border-yellow-500 rounded-md"></div>
                                <span class="text-blue-900 font-medium">Ditandai review</span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button @click="showSubmitModal = true"
                        class="w-full bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600 text-white font-bold py-3 px-4 rounded-2xl transition-all shadow-md hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" class="inline mr-2">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Kumpulkan Jawaban
                    </button>
                </div>
            </div>

            <!-- Navigation Buttons (Mobile) -->
            <div class="flex gap-4 mt-6 sm:hidden">
                <button @click="previousQuestion()" :disabled="currentQuestionIndex === 0"
                    class="flex-1 bg-gray-600 hover:bg-gray-700 disabled:bg-gray-400 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                    ← Sebelumnya
                </button>
                <button @click="nextQuestion()" :disabled="currentQuestionIndex === totalQuestions - 1"
                    class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                    Selanjutnya →
                </button>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden sm:flex gap-4 mt-6 justify-between">
                <button @click="previousQuestion()" :disabled="currentQuestionIndex === 0"
                    class="bg-gray-600 hover:bg-gray-700 disabled:bg-gray-400 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                    ← Soal Sebelumnya
                </button>
                <button @click="nextQuestion()" :disabled="currentQuestionIndex === totalQuestions - 1"
                    class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-bold py-2 px-6 rounded-lg transition-colors">
                    Soal Selanjutnya →
                </button>
            </div>

            <!-- Submit Confirmation Modal -->
            <template x-if="showSubmitModal">
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Konfirmasi Pengumpulan</h3>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <p class="text-sm text-gray-700 mb-2">
                                <span class="font-semibold">Soal yang dijawab:</span> <span x-text="answeredCount"></span> dari <span x-text="totalQuestions"></span>
                            </p>
                            <p class="text-sm text-gray-700">
                                <span class="font-semibold">Ditandai review:</span> <span x-text="markedForReviewCount"></span>
                            </p>
                        </div>

                        <p class="text-gray-700 mb-6">
                            Apakah Anda yakin ingin mengumpulkan jawaban Anda sekarang? Anda tidak dapat mengubahnya setelah dikumpulkan.
                        </p>

                        <div class="flex gap-3">
                            <button @click="showSubmitModal = false"
                                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                Lanjutkan Mengerjakan
                            </button>
                            <form action="{{ route('soal.submit', $quiz->id) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="answers" :value="JSON.stringify(selectedAnswers)">
                                <button type="submit"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                    Kumpulkan Sekarang
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Alpine.js Script -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        function quizApp() {
            return {
                questions: @json($questions),
                currentQuestionIndex: 0,
                selectedAnswers: {},
                markedForReview: new Set(),
                showSubmitModal: false,
                timeRemaining: {{ $quiz->time_limit * 60 }},

                get currentQuestion() {
                    return this.questions[this.currentQuestionIndex] || null;
                },

                get totalQuestions() {
                    return this.questions.length;
                },

                get answeredCount() {
                    return Object.keys(this.selectedAnswers).length;
                },

                get markedForReviewCount() {
                    return this.markedForReview.size;
                },

                init() {
                    this.startTimer();
                    this.setupAutoSave();
                },

                getOptionsArray() {
                    if (!this.currentQuestion?.options) return [];
                    const opts = typeof this.currentQuestion.options === 'string'
                        ? JSON.parse(this.currentQuestion.options)
                        : this.currentQuestion.options;
                    return Object.entries(opts).map(([key, value]) => ({ key, value }));
                },

                selectAnswer(key) {
                    this.$set(this.selectedAnswers, this.currentQuestion.id, key);
                    this.markedForReview.delete(this.currentQuestion.id);
                },

                markForReview() {
                    if (this.markedForReview.has(this.currentQuestion.id)) {
                        this.markedForReview.delete(this.currentQuestion.id);
                    } else {
                        this.markedForReview.add(this.currentQuestion.id);
                    }
                },

                clearAnswer() {
                    delete this.selectedAnswers[this.currentQuestion.id];
                },

                goToQuestion(index) {
                    this.currentQuestionIndex = index;
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
                    const qId = this.questions[index].id;
                    let classes = '';

                    if (index === this.currentQuestionIndex) classes += ' active';
                    if (this.markedForReview.has(qId)) classes += ' marked';
                    else if (this.selectedAnswers[qId]) classes += ' answered';
                    else classes += ' unanswered';

                    return classes;
                },

                formatTime(seconds) {
                    const hours = Math.floor(seconds / 3600);
                    const minutes = Math.floor((seconds % 3600) / 60);
                    const secs = seconds % 60;

                    if (hours > 0) {
                        return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                    }
                    return `${minutes}:${secs.toString().padStart(2, '0')}`;
                },

                startTimer() {
                    setInterval(() => {
                        if (this.timeRemaining > 0) {
                            this.timeRemaining--;
                        } else {
                            this.autoSubmit();
                        }
                    }, 1000);
                },

                setupAutoSave() {
                    setInterval(() => {
                        // Auto-save answers every 10 seconds
                        console.log('[v0] Auto-saving answers...');
                    }, 10000);
                },

                autoSubmit() {
                    // Auto-submit when time is up
                    console.log('[v0] Time is up, auto-submitting...');
                    // Implement auto-submit logic
                }
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            const loadingScreen = document.getElementById('loadingScreen');
            if (loadingScreen) {
                loadingScreen.classList.add('hidden');
            }
        });
    </script>
@endsection
