{{-- resources/views/murid/exams/attempt.blade.php --}}
@extends('layouts.appSiswa')

@section('content')
    <style>
        /* Copy semua style dari mengerjakan-soal.blade.php */
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
            background-color: #f8fafc;
            border: 2px solid #e5e7eb;
        }

        .timer.warning {
            background-color: #fef3c7;
            color: #92400e;
            border-color: #fcd34d;
        }

        .timer.critical {
            background-color: #fee2e2;
            color: #991b1b;
            border-color: #fca5a5;
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

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 p-4 sm:p-6 lg:p-8">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="loader border-t-4 border-blue-600 rounded-full w-16 h-16 animate-spin"></div>
        </div>

        <div x-data="examApp()" x-init="init()" class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="bg-white shadow-md rounded-xl p-4 sm:p-6 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Exam Title & Subject -->
                    <div class="col-span-1">
                        <h1 class="text-lg sm:text-2xl font-bold text-gray-800">{{ $exam->title }}</h1>
                        <p class="text-sm text-gray-600 mt-1">{{ $exam->subject->name_subject ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">Pengajar: {{ $exam->teacher->name ?? 'N/A' }}</p>
                    </div>

                    <!-- Progress -->
                    <div class="col-span-1">
                        <p class="text-sm text-gray-600 mb-2">Progres Pengerjaan</p>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all"
                                :style="`width: ${(answeredCount / {{ $questions->count() }}) * 100}%`"></div>
                        </div>
                        <p class="text-xs text-gray-600 mt-1">
                            <span>{{ $answeredCount }}</span>/<span>{{ $questions->count() }}</span> soal dijawab
                        </p>
                    </div>

                    <!-- Timer -->
                    <div class="col-span-1 flex items-center justify-end">
                        <div class="timer" :class="{ 'warning': timeRemaining < 300, 'critical': timeRemaining < 60 }">
                            <span x-text="formatTime(timeRemaining)"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Question Area (Left/Main) -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow-md rounded-xl p-6 sm:p-8">
                        <!-- Question Number -->
                        <div class="mb-4">
                            <p class="text-sm font-semibold text-blue-600">
                                Soal <span x-text="currentQuestionIndex + 1"></span> dari
                                <span>{{ $questions->count() }}</span>
                            </p>
                        </div>

                        <!-- Question Text -->
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-4" x-html="currentQuestion?.question || ''"></h2>

                            <!-- Question Image (if exists) -->
                            <template x-if="currentQuestion?.image_path">
                                <div class="mb-6">
                                    <img :src="currentQuestion.image_path" alt="Question Image"
                                        class="max-w-full h-auto rounded-lg shadow-sm">
                                </div>
                            </template>
                        </div>

                        <!-- Answer Options -->
                        <div class="space-y-3 mb-8">
                            <template x-if="currentQuestion?.type === 'PG'">
                                <template x-for="(choice, index) in currentQuestion?.choices || []" :key="choice.id">
                                    <button @click="selectAnswer(choice.id)"
                                        :class="{ 'selected': selectedAnswers[currentQuestion?.id] == choice.id }"
                                        class="option-button w-full text-left">
                                        <div class="option-radio">
                                            <span x-text="getChoiceLabel(index)" class="text-sm font-bold"></span>
                                        </div>
                                        <span class="flex-1 text-gray-800" x-text="choice.text"></span>
                                    </button>
                                </template>
                            </template>

                            <template x-if="currentQuestion?.type === 'IS'">
                                <div>
                                    <textarea x-model="essayAnswers[currentQuestion?.id]" placeholder="Ketik jawaban Anda di sini..."
                                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        rows="5"></textarea>
                                    <button @click="saveEssayAnswer()"
                                        class="mt-3 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                                        Simpan Jawaban
                                    </button>
                                </div>
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
                                    <path
                                        d="M19 4v20a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4m3 0V3a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1">
                                    </path>
                                    <line x1="10" y1="12" x2="10" y2="17"></line>
                                    <line x1="14" y1="12" x2="14" y2="17"></line>
                                </svg>
                                Hapus Jawaban
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar (Right) -->
                <div class="lg:col-span-1">
                    <!-- Question Navigator -->
                    <div class="bg-white shadow-md rounded-xl p-6 mb-6">
                        <h3 class="text-sm font-bold text-gray-800 mb-4">Navigasi Soal</h3>
                        <div class="grid grid-cols-5 gap-2">
                            <template x-for="(q, index) in questions" :key="q.id">
                                <button @click="goToQuestion(index)" :class="getQuestionButtonClass(index)"
                                    class="question-number-btn" x-text="index + 1"></button>
                            </template>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                        <h4 class="font-semibold text-blue-900 mb-3 text-sm">Legenda</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-blue-300 border-2 border-blue-400 rounded"></div>
                                <span class="text-gray-700">Belum dijawab</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-green-300 border-2 border-green-400 rounded"></div>
                                <span class="text-gray-700">Sudah dijawab</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 bg-yellow-300 border-2 border-yellow-400 rounded"></div>
                                <span class="text-gray-700">Ditandai review</span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button @click="showSubmitModal = true"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-colors">
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
                <button @click="nextQuestion()" :disabled="currentQuestionIndex === questions.length - 1"
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
                <button @click="nextQuestion()" :disabled="currentQuestionIndex === questions.length - 1"
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
                                <span class="font-semibold">Soal yang dijawab:</span> <span x-text="answeredCount"></span>
                                dari <span>{{ $questions->count() }}</span>
                            </p>
                            <p class="text-sm text-gray-700">
                                <span class="font-semibold">Ditandai review:</span> <span
                                    x-text="markedForReviewCount"></span>
                            </p>
                        </div>

                        <p class="text-gray-700 mb-6">
                            Apakah Anda yakin ingin mengumpulkan jawaban Anda sekarang? Anda tidak dapat mengubahnya setelah
                            dikumpulkan.
                        </p>

                        <div class="flex gap-3">
                            <button @click="showSubmitModal = false"
                                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                Lanjutkan Mengerjakan
                            </button>
                            <form action="{{ route('soal.submit', $exam->id) }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="attempt_id" value="{{ $attempt->id }}">
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
        function examApp() {
            return {
                questions: @json($questions),
                currentQuestionIndex: 0,
                selectedAnswers: @json($answers),
                essayAnswers: {},
                markedForReview: new Set(),
                showSubmitModal: false,
                timeRemaining: {{ $timeRemaining }},
                attemptId: {{ $attempt->id }},
                examId: {{ $exam->id }},

                get currentQuestion() {
                    return this.questions[this.currentQuestionIndex] || null;
                },

                get answeredCount() {
                    const multipleChoice = Object.keys(this.selectedAnswers).length;
                    const essay = Object.keys(this.essayAnswers).length;
                    return multipleChoice + essay;
                },

                get markedForReviewCount() {
                    return this.markedForReview.size;
                },

                init() {
                    this.startTimer();
                    this.setupAutoSave();
                },

                getChoiceLabel(index) {
                    const labels = ['A', 'B', 'C', 'D', 'E', 'F'];
                    return labels[index] || String.fromCharCode(65 + index);
                },

                selectAnswer(choiceId) {
                    if (this.currentQuestion?.type === 'PG') {
                        this.$set(this.selectedAnswers, this.currentQuestion.id, choiceId);
                        this.markedForReview.delete(this.currentQuestion.id);
                        this.saveAnswerToServer(choiceId);
                    }
                },

                saveEssayAnswer() {
                    if (this.currentQuestion?.type === 'IS' && this.essayAnswers[this.currentQuestion.id]) {
                        this.saveEssayToServer(this.essayAnswers[this.currentQuestion.id]);
                    }
                },

                async saveAnswerToServer(choiceId) {
                    try {
                        const response = await fetch(`/murid/exams/${this.examId}/save-answer`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify({
                                attempt_id: this.attemptId,
                                question_id: this.currentQuestion.id,
                                choice_id: choiceId
                            })
                        });

                        if (!response.ok) {
                            console.error('Failed to save answer');
                        }
                    } catch (error) {
                        console.error('Error saving answer:', error);
                    }
                },

                async saveEssayToServer(answerText) {
                    try {
                        const response = await fetch(`/murid/exams/${this.examId}/save-answer`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            },
                            body: JSON.stringify({
                                attempt_id: this.attemptId,
                                question_id: this.currentQuestion.id,
                                answer_text: answerText
                            })
                        });

                        if (!response.ok) {
                            console.error('Failed to save essay answer');
                        }
                    } catch (error) {
                        console.error('Error saving essay answer:', error);
                    }
                },

                markForReview() {
                    if (this.markedForReview.has(this.currentQuestion.id)) {
                        this.markedForReview.delete(this.currentQuestion.id);
                    } else {
                        this.markedForReview.add(this.currentQuestion.id);
                    }
                },

                clearAnswer() {
                    if (this.currentQuestion?.type === 'PG') {
                        delete this.selectedAnswers[this.currentQuestion.id];
                    } else if (this.currentQuestion?.type === 'IS') {
                        delete this.essayAnswers[this.currentQuestion.id];
                    }
                    this.markedForReview.delete(this.currentQuestion.id);
                },

                goToQuestion(index) {
                    this.currentQuestionIndex = index;
                },

                nextQuestion() {
                    if (this.currentQuestionIndex < this.questions.length - 1) {
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
                    else if (this.selectedAnswers[qId] || this.essayAnswers[qId]) classes += ' answered';
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
                    const timerInterval = setInterval(() => {
                        if (this.timeRemaining > 0) {
                            this.timeRemaining--;
                        } else {
                            clearInterval(timerInterval);
                            this.autoSubmit();
                        }
                    }, 1000);
                },

                setupAutoSave() {
                    // Auto-save setiap 30 detik
                    setInterval(() => {
                        console.log('[Auto-save] Menyimpan jawaban...');
                    }, 30000);
                },

                autoSubmit() {
                    alert('Waktu habis! Jawaban akan dikumpulkan otomatis.');
                    document.querySelector('form[action*="submit"]').submit();
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
