@extends('layouts.appTeacher')

@section('content')
    <style>
        /* Base Styles */
        .question-card {
            transition: all 0.3s ease;
            border: 2px solid rgba(37, 99, 235, 0.1);
        }

        .question-card:hover {
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.15);
            border-color: rgba(37, 99, 235, 0.3);
        }

        .badge-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }

        .tab-blue-active {
            border-color: #2563eb;
            color: #2563eb;
        }

        .btn-blue {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            transition: all 0.3s ease;
        }

        .btn-blue:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .stat-box-blue {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 2px solid #93c5fd;
        }

        /* Fixed Progress Bar Styles */
        .progress-bar-container {
            width: 100%;
            background-color: #e5e7eb;
            border-radius: 9999px;
            overflow: hidden;
            height: 0.5rem;
            position: relative;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            border-radius: 9999px;
            transition: width 0.3s ease;
            min-width: 0.5rem;
            max-width: 100%;
        }

        .progress-bar-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }

        .progress-percentage {
            font-weight: 600;
            color: #1d4ed8;
        }

        /* Responsive Grid */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        @media (min-width: 1024px) {
            .main-grid {
                grid-template-columns: 2fr 1fr;
            }
        }

        /* Fixed Header */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 40;
            background: linear-gradient(135deg, #ffffff 0%, #ffffff 100%);
            backdrop-filter: blur(8px);
            padding-top: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
            padding: 20px;
            border-radius: 20px;
        }

        /* Question List Container */
        .question-list-container {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .question-list-container::-webkit-scrollbar {
            width: 6px;
        }

        .question-list-container::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .question-list-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .question-list-container::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Custom Modal Styles - Optimized */
        .custom-modal-backdrop {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 9998;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease-out;
            padding: 1rem;
            align-items: center;
            justify-content: center;
        }

        .custom-modal-backdrop.active {
            display: flex;
            opacity: 1;
        }

        .custom-modal {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            max-height: 90vh;
            overflow-y: auto;
            transform: translateY(-20px);
            opacity: 0;
            transition: all 0.3s ease-out;
        }

        .custom-modal.active {
            transform: translateY(0);
            opacity: 1;
        }

        .custom-modal-header {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .custom-modal-content {
            max-height: calc(90vh - 120px);
            overflow-y: auto;
            padding: 1rem 1.5rem;
        }

        .custom-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        /* Alert Styles */
        .custom-alert {
            position: fixed;
            top: 1rem;
            right: 1rem;
            left: 1rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 9999;
            transform: translateY(-100%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        @media (min-width: 640px) {
            .custom-alert {
                left: auto;
                max-width: 400px;
                transform: translateX(100%);
            }
        }

        .custom-alert.show {
            transform: translateY(0) translateX(0);
            opacity: 1;
        }

        .custom-alert.success {
            border-left: 4px solid #10b981;
        }

        .custom-alert.error {
            border-left: 4px solid #ef4444;
        }

        .custom-alert.warning {
            border-left: 4px solid #f59e0b;
        }

        .custom-alert.info {
            border-left: 4px solid #3b82f6;
        }

        /* Form Field Improvements */
        .form-field {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-field:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Responsive Button Group */
        .btn-group-responsive {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        @media (min-width: 640px) {
            .btn-group-responsive {
                flex-direction: row;
                align-items: center;
            }
        }

        /* Choice Item */
        .choice-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background-color: #f9fafb;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }

        .choice-item:hover {
            background-color: #f3f4f6;
        }

        .choice-item.correct {
            background-color: #d1fae5;
            border-color: #a7f3d0;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        @media (min-width: 768px) {
            .action-buttons {
                flex-wrap: nowrap;
            }
        }

        /* Import Questions Styles */
        .import-section {
            border: 2px dashed #cbd5e1;
            border-radius: 0.75rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .import-section:hover {
            border-color: #3b82f6;
            background-color: #f8fafc;
        }

        .import-section.dragover {
            border-color: #3b82f6;
            background-color: #eff6ff;
            transform: scale(1.02);
        }

        .import-preview {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            background-color: #f9fafb;
        }

        .import-preview-item {
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
            transition: all 0.2s;
        }

        .import-preview-item:last-child {
            border-bottom: none;
        }

        .import-preview-item:hover {
            background-color: #f3f4f6;
        }

        .import-preview-item.selected {
            background-color: #dbeafe;
            border-left: 4px solid #3b82f6;
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="quizQuestionCreator()" x-init="init()">
        <!-- Fixed Header -->
        <div class="sticky-header shadow-md">
            <div class="flex flex-col gap-4">
                <!-- Title and Info -->
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <h1 class="text-2xl font-bold text-gray-900 truncate">Buat Soal Quiz: {{ $quiz->title }}</h1>
                        <p class="text-gray-600 mt-1 text-sm">
                            {{ $quiz->subject->name_subject }} • Kelas {{ $quiz->class->name_class }} •
                            <span class="font-medium">
                                {{ $quiz->difficulty_level == 'easy' ? 'Mudah' : ($quiz->difficulty_level == 'medium' ? 'Sedang' : 'Sulit') }}
                            </span>
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-50 text-blue-700 px-4 py-2 rounded-lg whitespace-nowrap">
                            <span class="font-bold text-lg">{{ $questionCount }}</span> Soal •
                            <span class="font-bold text-lg">{{ $totalScore }}</span> Poin
                        </div>
                        <button @click="showImportModal = true"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg flex items-center gap-2 whitespace-nowrap">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="hidden sm:inline">Import Soal</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="main-grid">
            <!-- Left Column: Question Form and List -->
            <div class="space-y-6">
                <!-- Question Form -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Tambah Soal Baru</h2>

                    <!-- Question Type Tabs -->
                    <div class="flex border-b border-gray-200 mb-4">
                        <button @click="newQuestion.type = 'PG'"
                            :class="newQuestion.type === 'PG' ?
                                'border-b-2 border-blue-500 text-blue-600' :
                                'text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-2 text-center font-medium text-sm sm:text-base">
                            Pilihan Ganda
                        </button>
                        <button @click="newQuestion.type = 'IS'"
                            :class="newQuestion.type === 'IS' ?
                                'border-b-2 border-blue-500 text-blue-600' :
                                'text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-2 text-center font-medium text-sm sm:text-base">
                            Isian Singkat
                        </button>
                    </div>

                    <!-- Question Input -->
                    <div class="space-y-4">
                        <!-- Question Text -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pertanyaan</label>
                            <textarea x-model="newQuestion.question" rows="3" placeholder="Masukkan pertanyaan..." class="form-field"></textarea>
                        </div>

                        <!-- Score and Explanation -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Poin</label>
                                <input type="number" x-model="newQuestion.score" min="1" max="100"
                                    class="form-field">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Penjelasan (Opsional)</label>
                                <input type="text" x-model="newQuestion.explanation" placeholder="Penjelasan jawaban..."
                                    class="form-field">
                            </div>
                        </div>

                        <!-- Multiple Choice Section -->
                        <div x-show="newQuestion.type === 'PG'" x-transition class="space-y-4">
                            <div class="flex justify-between items-center">
                                <label class="block text-sm font-medium text-gray-700">Pilihan Jawaban</label>
                                <button type="button" @click="addChoice()"
                                    class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>Tambah Pilihan</span>
                                </button>
                            </div>

                            <!-- Choices List -->
                            <div class="space-y-3">
                                <template x-for="(choice, index) in newQuestion.choices" :key="choice.id">
                                    <div class="choice-item" :class="{ 'correct': choice.is_correct }">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <!-- Correct Answer Radio -->
                                            <input type="radio" :name="'correct_choice_' + newQuestion.id"
                                                :checked="choice.is_correct" @change="setCorrectChoice(index)"
                                                class="w-4 h-4 text-blue-600 flex-shrink-0">

                                            <!-- Choice Label -->
                                            <span
                                                class="w-8 h-8 flex-shrink-0 flex items-center justify-center bg-blue-100 text-blue-800 rounded-full font-bold"
                                                x-text="String.fromCharCode(65 + index)"></span>

                                            <!-- Choice Text -->
                                            <input type="text" x-model="choice.text"
                                                placeholder="Masukkan teks pilihan..." class="form-field flex-1 min-w-0">
                                        </div>

                                        <!-- Delete Choice Button -->
                                        <button type="button" @click="removeChoice(index)"
                                            :disabled="newQuestion.choices.length <= 2"
                                            :class="newQuestion.choices.length <= 2 ? 'text-gray-400 cursor-not-allowed' :
                                                'text-red-500 hover:text-red-700'"
                                            class="p-1 flex-shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <!-- Correct Answer Indicator -->
                            <div x-show="hasCorrectAnswer" class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-center text-blue-700">
                                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm">Jawaban benar telah dipilih</span>
                                </div>
                            </div>
                        </div>

                        <!-- Short Answer Section -->
                        <div x-show="newQuestion.type === 'IS'" x-transition class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">Jawaban yang Benar</label>
                            <div class="space-y-2">
                                <template x-for="(answer, index) in newQuestion.short_answers" :key="index">
                                    <div class="flex items-center gap-3">
                                        <input type="text" x-model="newQuestion.short_answers[index]"
                                            placeholder="Masukkan jawaban..." class="form-field flex-1">
                                        <button type="button" @click="removeShortAnswer(index)"
                                            :disabled="newQuestion.short_answers.length <= 1"
                                            :class="newQuestion.short_answers.length <= 1 ? 'text-gray-400 cursor-not-allowed' :
                                                'text-red-500 hover:text-red-700'"
                                            class="p-1 flex-shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                                <button type="button" @click="addShortAnswer()"
                                    class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>Tambah Jawaban</span>
                                </button>
                                <p class="text-xs text-gray-500">Siswa akan dianggap benar jika menjawab salah satu dari
                                    jawaban di atas.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4 border-t border-gray-200">
                        <button type="button" @click="addQuestion()" :disabled="!isQuestionValid"
                            :class="isQuestionValid
                                ?
                                'bg-blue-600 hover:bg-blue-700 cursor-pointer' :
                                'bg-gray-400 cursor-not-allowed'"
                            class="w-full py-3 text-white font-medium rounded-lg transition-colors">
                            Tambah Soal ke Daftar
                        </button>
                    </div>
                </div>

                <!-- Question List -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-800">Daftar Soal</h2>
                        <span class="text-sm text-gray-600" x-text="questions.length + ' soal'"></span>
                    </div>

                    <!-- Empty State -->
                    <div x-show="questions.length === 0" class="text-center py-8">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        <p class="text-gray-500 mb-4">Belum ada soal. Tambahkan soal pertama Anda!</p>
                    </div>

                    <!-- Questions List Container -->
                    <div x-show="questions.length > 0" class="question-list-container">
                        <div class="space-y-4">
                            <template x-for="(question, index) in questions" :key="question.id">
                                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                                    <div class="flex justify-between items-start mb-3 gap-3">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                                <span
                                                    class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded whitespace-nowrap">
                                                    Soal <span x-text="index + 1"></span>
                                                </span>
                                                <span
                                                    :class="question.type === 'PG' ?
                                                        'bg-blue-100 text-blue-800' :
                                                        'bg-blue-100 text-blue-800'"
                                                    class="text-sm font-medium px-2.5 py-0.5 rounded whitespace-nowrap">
                                                    <span
                                                        x-text="question.type === 'PG' ? 'Pilihan Ganda' : 'Isian Singkat'"></span>
                                                </span>
                                                <span
                                                    class="bg-yellow-100 text-yellow-800 text-sm font-medium px-2.5 py-0.5 rounded whitespace-nowrap">
                                                    <span x-text="question.score"></span> Poin
                                                </span>
                                            </div>
                                            <p class="text-gray-800 font-medium mb-2" x-text="question.question"></p>
                                            <p x-show="question.explanation" class="text-sm text-gray-600">
                                                <span class="font-medium">Penjelasan:</span> <span
                                                    x-text="question.explanation"></span>
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <button @click="editQuestion(index)"
                                                class="p-1 text-blue-600 hover:text-blue-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </button>
                                            <button @click="showDeleteModal(index, question.question)"
                                                class="p-1 text-red-600 hover:text-red-800">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Multiple Choice Answers -->
                                    <div x-show="question.type === 'PG'" class="space-y-2">
                                        <template x-for="(choice, choiceIndex) in question.choices"
                                            :key="choice.id">
                                            <div class="flex items-center gap-3">
                                                <div :class="choice.is_correct ?
                                                    'bg-green-100 border-green-300 text-green-800' :
                                                    'bg-gray-50 border-gray-200 text-gray-700'"
                                                    class="w-8 h-8 flex items-center justify-center rounded-full border font-bold flex-shrink-0">
                                                    <span x-text="String.fromCharCode(65 + choiceIndex)"></span>
                                                </div>
                                                <span
                                                    :class="choice.is_correct ? 'text-green-700 font-medium' : 'text-gray-600'"
                                                    class="break-words" x-text="choice.text"></span>
                                                <span x-show="choice.is_correct" class="text-green-600 flex-shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </span>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Short Answers -->
                                    <div x-show="question.type === 'IS'" class="space-y-2">
                                        <p class="text-sm font-medium text-gray-700">Jawaban yang diterima:</p>
                                        <div class="flex flex-wrap gap-2">
                                            <template x-for="(answer, answerIndex) in question.short_answers"
                                                :key="answerIndex">
                                                <span
                                                    class="bg-blue-100 text-blue-800 text-sm px-2 py-1 rounded break-all">
                                                    <span x-text="answer"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sidebar -->
            <div class="space-y-6">
                <!-- Quiz Info -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Quiz</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Judul</span>
                            <span class="font-medium text-right truncate ml-2"
                                x-text="quizTitle">{{ $quiz->title }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Mata Pelajaran</span>
                            <span class="font-medium">{{ $quiz->subject->name_subject }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Kelas</span>
                            <span class="font-medium">{{ $quiz->class->name_class }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tingkat Kesulitan</span>
                            <span class="font-medium capitalize">{{ $quiz->difficulty_level }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Mode Quiz</span>
                            <span class="font-medium">{{ $quiz->quiz_mode == 'live' ? 'Live' : 'Homework' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Waktu per Soal</span>
                            <span class="font-medium">{{ $quiz->time_per_question }} detik</span>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-600">Total Soal</span>
                                <span class="font-bold text-lg" x-text="questions.length">0</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill" :style="'width: ' + (questions.length / 50) * 100 + '%'">
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Maksimal 50 soal
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-gray-600">Total Poin</span>
                                <span class="font-bold text-lg" x-text="totalPoints">0</span>
                            </div>
                            <div class="progress-bar-container">
                                <div class="progress-bar-fill bg-green-500"
                                    :style="'width: ' + Math.min((totalPoints / 500) * 100, 100) + '%'"></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Maksimal 500 poin
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-blue-50 rounded-lg">
                                <span class="block text-sm text-gray-600">PG</span>
                                <span class="block text-2xl font-bold text-blue-600" x-text="pgCount">0</span>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <span class="block text-sm text-gray-600">Isian</span>
                                <span class="block text-2xl font-bold text-green-600" x-text="isCount">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Actions</h3>
                    <div class="space-y-3">
                        <button @click="saveQuestions()" :disabled="questions.length === 0 || isSaving"
                            :class="questions.length === 0 || isSaving ?
                                'bg-gray-400 cursor-not-allowed' :
                                'bg-blue-600 hover:bg-blue-700 cursor-pointer'"
                            class="w-full py-3 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                            <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Semua Soal'"></span>
                            <svg x-show="isSaving" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                        </button>

                        <button @click="previewQuiz()" :disabled="questions.length === 0"
                            :class="questions.length === 0 ?
                                'bg-gray-300 text-gray-500 cursor-not-allowed' :
                                'bg-purple-500 hover:bg-purple-600 text-white cursor-pointer'"
                            class="w-full py-3 font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                </path>
                            </svg>
                            <span>Preview Quiz</span>
                        </button>

                        <button @click="finalizeQuiz()" :disabled="questions.length === 0 || isFinalizing"
                            :class="questions.length === 0 || isFinalizing ?
                                'bg-gray-400 cursor-not-allowed' :
                                'bg-green-600 hover:bg-green-700 cursor-pointer'"
                            class="w-full py-3 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            <span x-text="isFinalizing ? 'Mempublikasikan...' : 'Publikasikan Quiz'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <div x-show="showImportModal" x-transition.opacity class="custom-modal-backdrop"
            @click.self="showImportModal = false">
            <div class="custom-modal" style="max-width: 600px;">
                <div class="custom-modal-header">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Import Soal</h3>
                        <button @click="showImportModal = false" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="custom-modal-content">
                    <div class="space-y-4">
                        <!-- Tabs for Import Options -->
                        <div class="flex border-b border-gray-200">
                            <button @click="importMode = 'from_exam'"
                                :class="importMode === 'from_exam' ?
                                    'border-b-2 border-blue-500 text-blue-600' :
                                    'text-gray-500 hover:text-gray-700'"
                                class="flex-1 py-2 text-center font-medium">
                                Dari Quiz Lain
                            </button>
                            <button @click="importMode = 'from_file'"
                                :class="importMode === 'from_file' ?
                                    'border-b-2 border-blue-500 text-blue-600' :
                                    'text-gray-500 hover:text-gray-700'"
                                class="flex-1 py-2 text-center font-medium">
                                Dari File
                            </button>
                        </div>

                        <!-- Import from Exam -->
                        <div x-show="importMode === 'from_exam'" x-transition class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Quiz</label>
                                <select x-model="selectedImportQuiz"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">-- Pilih Quiz --</option>
                                    @foreach ($otherQuizzes as $quiz)
                                        <option value="{{ $quiz->id }}">
                                            {{ $quiz->title }} ({{ $quiz->questions_count }} soal)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div x-show="selectedImportQuiz && importPreview.length > 0" class="space-y-3">
                                <h4 class="text-sm font-medium text-gray-700">Preview Soal:</h4>
                                <div class="import-preview">
                                    <template x-for="(question, index) in importPreview" :key="index">
                                        <div class="import-preview-item" :class="{ 'selected': question.selected }">
                                            <div class="flex items-start gap-3">
                                                <input type="checkbox" x-model="question.selected"
                                                    class="mt-1 text-blue-600 focus:ring-blue-500">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="text-xs font-medium px-2 py-0.5 rounded"
                                                            :class="question.type === 'PG' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'">
                                                            <span x-text="question.type === 'PG' ? 'PG' : 'IS'"></span>
                                                        </span>
                                                        <span class="text-xs text-gray-500">
                                                            <span x-text="question.score"></span> poin
                                                        </span>
                                                    </div>
                                                    <p class="text-sm text-gray-800 line-clamp-2"
                                                        x-text="question.question"></p>
                                                    <div x-show="question.choices" class="mt-1">
                                                        <p class="text-xs text-gray-600">Pilihan:</p>
                                                        <div class="flex flex-wrap gap-1 mt-1">
                                                            <template x-for="(choice, cIndex) in question.choices" :key="cIndex">
                                                                <span class="text-xs px-2 py-0.5 rounded"
                                                                    :class="choice.is_correct ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'">
                                                                    <span x-text="String.fromCharCode(65 + cIndex)"></span>.
                                                                    <span x-text="choice.text"></span>
                                                                </span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">
                                        Terpilih: <span x-text="selectedImportCount"></span> dari <span x-text="importPreview.length"></span> soal
                                    </span>
                                    <button @click="selectAllImportQuestions(true)"
                                        class="text-blue-600 hover:text-blue-800 text-sm">
                                        Pilih Semua
                                    </button>
                                </div>
                            </div>

                            <div x-show="selectedImportQuiz && !importPreview.length" class="bg-yellow-50 p-4 rounded-lg">
                                <p class="text-sm text-yellow-700">
                                    Quiz ini tidak memiliki soal yang dapat diimport.
                                </p>
                            </div>
                        </div>

                        <!-- Import from File -->
                        <div x-show="importMode === 'from_file'" x-transition class="space-y-4">
                            <div class="import-section"
                                 @dragover.prevent="handleDragOver($event)"
                                 @dragleave.prevent="handleDragLeave($event)"
                                 @drop.prevent="handleFileDrop($event)"
                                 :class="{ 'dragover': isDragging }">
                                <div class="text-center">
                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    <p class="text-sm text-gray-600 mb-2">
                                        Drag & drop file Excel/CSV di sini atau
                                    </p>
                                    <label for="file-upload" class="cursor-pointer">
                                        <span class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors inline-block">
                                            Pilih File
                                        </span>
                                        <input id="file-upload" type="file" accept=".xlsx,.xls,.csv" class="hidden" @change="handleFileSelect($event)">
                                    </label>
                                    <p class="text-xs text-gray-500 mt-3">
                                        Format yang didukung: Excel (.xlsx, .xls), CSV
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Template dapat diunduh <a href="#" class="text-blue-600 hover:underline">di sini</a>
                                    </p>
                                </div>
                            </div>

                            <div x-show="fileImportPreview.length > 0" class="space-y-3">
                                <h4 class="text-sm font-medium text-gray-700">Preview Soal dari File:</h4>
                                <div class="import-preview">
                                    <template x-for="(question, index) in fileImportPreview" :key="index">
                                        <div class="import-preview-item">
                                            <div class="flex items-start gap-3">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="text-xs font-medium px-2 py-0.5 rounded"
                                                            :class="question.type === 'PG' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'">
                                                            <span x-text="question.type === 'PG' ? 'PG' : 'IS'"></span>
                                                        </span>
                                                        <span class="text-xs text-gray-500">
                                                            <span x-text="question.score"></span> poin
                                                        </span>
                                                    </div>
                                                    <p class="text-sm text-gray-800" x-text="question.question"></p>
                                                    <div x-show="question.choices" class="mt-2 space-y-1">
                                                        <template x-for="(choice, cIndex) in question.choices" :key="cIndex">
                                                            <div class="flex items-center gap-2 text-xs">
                                                                <span class="w-6 h-6 flex items-center justify-center rounded-full"
                                                                    :class="choice.is_correct ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'">
                                                                    <span x-text="String.fromCharCode(65 + cIndex)"></span>
                                                                </span>
                                                                <span :class="choice.is_correct ? 'text-green-700 font-medium' : 'text-gray-600'"
                                                                      x-text="choice.text"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <div class="text-sm text-gray-600">
                                    Total: <span x-text="fileImportPreview.length"></span> soal
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button @click="showImportModal = false"
                            class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-medium">
                        Batal
                    </button>
                    <button @click="importQuestions()"
                            :disabled="!canImport"
                            :class="!canImport ?
                                'bg-gray-400 cursor-not-allowed' :
                                'bg-blue-600 hover:bg-blue-700 cursor-pointer'"
                            class="px-4 py-2 text-white rounded-lg font-medium">
                        Import Soal
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div x-show="showEditModal" x-transition.opacity class="custom-modal-backdrop" @click.self="cancelEdit()">
            <div class="custom-modal" style="max-width: 800px;">
                <div class="custom-modal-header">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Edit Soal</h3>
                        <button @click="cancelEdit()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="custom-modal-content">
                    <div class="space-y-4">
                        <!-- Question Type Tabs -->
                        <div class="flex border-b border-gray-200">
                            <button @click="editingQuestion.type = 'PG'"
                                :class="editingQuestion.type === 'PG' ?
                                    'border-b-2 border-blue-500 text-blue-600' :
                                    'text-gray-500 hover:text-gray-700'"
                                class="flex-1 py-2 text-center font-medium">
                                Pilihan Ganda
                            </button>
                            <button @click="editingQuestion.type = 'IS'"
                                :class="editingQuestion.type === 'IS' ?
                                    'border-b-2 border-blue-500 text-blue-600' :
                                    'text-gray-500 hover:text-gray-700'"
                                class="flex-1 py-2 text-center font-medium">
                                Isian Singkat
                            </button>
                        </div>

                        <!-- Question Text -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pertanyaan</label>
                            <textarea x-model="editingQuestion.question" rows="3" placeholder="Masukkan pertanyaan..." class="form-field"></textarea>
                        </div>

                        <!-- Score and Explanation -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Poin</label>
                                <input type="number" x-model="editingQuestion.score" min="1" max="100" class="form-field">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Penjelasan (Opsional)</label>
                                <input type="text" x-model="editingQuestion.explanation" placeholder="Penjelasan jawaban..." class="form-field">
                            </div>
                        </div>

                        <!-- Multiple Choice Section -->
                        <div x-show="editingQuestion.type === 'PG'" x-transition class="space-y-4">
                            <div class="flex justify-between items-center">
                                <label class="block text-sm font-medium text-gray-700">Pilihan Jawaban</label>
                                <button type="button" @click="addChoiceToEdit()"
                                    class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>Tambah Pilihan</span>
                                </button>
                            </div>

                            <!-- Choices List -->
                            <div class="space-y-3">
                                <template x-for="(choice, index) in editingQuestion.choices" :key="choice.id">
                                    <div class="choice-item" :class="{ 'correct': choice.is_correct }">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <!-- Correct Answer Radio -->
                                            <input type="radio" :name="'edit_correct_choice'"
                                                :checked="choice.is_correct" @change="setCorrectChoiceInEdit(index)"
                                                class="w-4 h-4 text-blue-600 flex-shrink-0">

                                            <!-- Choice Label -->
                                            <span
                                                class="w-8 h-8 flex-shrink-0 flex items-center justify-center bg-blue-100 text-blue-800 rounded-full font-bold"
                                                x-text="String.fromCharCode(65 + index)"></span>

                                            <!-- Choice Text -->
                                            <input type="text" x-model="choice.text"
                                                placeholder="Masukkan teks pilihan..." class="form-field flex-1 min-w-0">
                                        </div>

                                        <!-- Delete Choice Button -->
                                        <button type="button" @click="removeChoiceFromEdit(index)"
                                            :disabled="editingQuestion.choices.length <= 2"
                                            :class="editingQuestion.choices.length <= 2 ? 'text-gray-400 cursor-not-allowed' :
                                                'text-red-500 hover:text-red-700'"
                                            class="p-1 flex-shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <!-- Correct Answer Indicator -->
                            <div x-show="hasCorrectAnswerInEdit" class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-center text-blue-700">
                                    <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm">Jawaban benar telah dipilih</span>
                                </div>
                            </div>
                        </div>

                        <!-- Short Answer Section -->
                        <div x-show="editingQuestion.type === 'IS'" x-transition class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">Jawaban yang Benar</label>
                            <div class="space-y-2">
                                <template x-for="(answer, index) in editingQuestion.short_answers" :key="index">
                                    <div class="flex items-center gap-3">
                                        <input type="text" x-model="editingQuestion.short_answers[index]"
                                            placeholder="Masukkan jawaban..." class="form-field flex-1">
                                        <button type="button" @click="removeShortAnswerFromEdit(index)"
                                            :disabled="editingQuestion.short_answers.length <= 1"
                                            :class="editingQuestion.short_answers.length <= 1 ? 'text-gray-400 cursor-not-allowed' :
                                                'text-red-500 hover:text-red-700'"
                                            class="p-1 flex-shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                                <button type="button" @click="addShortAnswerToEdit()"
                                    class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span>Tambah Jawaban</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button @click="cancelEdit()" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-medium">
                        Batal
                    </button>
                    <button @click="updateQuestion()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                        Update Soal
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-show="showDeleteModal" x-transition.opacity class="custom-modal-backdrop" @click.self="cancelDelete()">
            <div class="custom-modal">
                <div class="custom-modal-header">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <div
                                class="w-10 h-10 bg-gradient-to-r from-red-500 to-red-600 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Hapus Soal</h2>
                            <p class="text-sm text-gray-600 mt-1" x-text="deleteQuestionText"></p>
                            <p class="text-sm text-red-600 font-medium mt-2">Tindakan ini tidak dapat dibatalkan!</p>
                        </div>
                    </div>
                </div>
                <div class="custom-modal-body">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 text-red-700">
                            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-sm">Soal yang dihapus tidak dapat dikembalikan.</p>
                        </div>
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button @click="cancelDelete()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-medium transition-colors">
                        Batal
                    </button>
                    <button @click="confirmDelete()"
                        class="px-4 py-2 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white rounded-lg font-medium transition-colors">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function quizQuestionCreator() {
            return {
                // State
                questions: @json($questionsData),
                newQuestion: {
                    id: null,
                    type: 'PG',
                    question: '',
                    score: 1,
                    explanation: '',
                    choices: [{
                            id: 1,
                            text: '',
                            is_correct: false
                        },
                        {
                            id: 2,
                            text: '',
                            is_correct: false
                        },
                        {
                            id: 3,
                            text: '',
                            is_correct: false
                        },
                        {
                            id: 4,
                            text: '',
                            is_correct: false
                        }
                    ],
                    short_answers: []
                },
                editingQuestion: null,
                editingIndex: null,
                quizTitle: '{{ $quiz->title }}',
                showImportModal: false,
                showEditModal: false,
                showDeleteModal: false,
                importMode: 'from_exam',
                selectedImportQuiz: '',
                importPreview: [],
                fileImportPreview: [],
                selectedFile: null,
                isDragging: false,
                isSaving: false,
                isFinalizing: false,
                deleteIndex: null,
                deleteQuestionText: '',

                // Computed Properties
                get totalPoints() {
                    return this.questions.reduce((sum, q) => sum + parseInt(q.score), 0);
                },
                get pgCount() {
                    return this.questions.filter(q => q.type === 'PG').length;
                },
                get isCount() {
                    return this.questions.filter(q => q.type === 'IS').length;
                },
                get hasCorrectAnswer() {
                    if (this.newQuestion.type !== 'PG') return true;
                    return this.newQuestion.choices.some(c => c.is_correct);
                },
                get hasCorrectAnswerInEdit() {
                    if (!this.editingQuestion) return false;
                    if (this.editingQuestion.type !== 'PG') return true;
                    return this.editingQuestion.choices.some(c => c.is_correct);
                },
                get isQuestionValid() {
                    // Validasi pertanyaan
                    if (!this.newQuestion.question.trim()) return false;
                    if (this.newQuestion.score < 1) return false;

                    // Validasi berdasarkan tipe
                    if (this.newQuestion.type === 'PG') {
                        // Minimal 2 pilihan, minimal 1 benar, semua pilihan harus ada teks
                        if (this.newQuestion.choices.length < 2) return false;
                        if (!this.newQuestion.choices.some(c => c.is_correct)) return false;
                        if (this.newQuestion.choices.some(c => !c.text.trim())) return false;
                    } else if (this.newQuestion.type === 'IS') {
                        // Minimal 1 jawaban
                        if (this.newQuestion.short_answers.length < 1) return false;
                        if (this.newQuestion.short_answers.some(a => !a.trim())) return false;
                    }

                    return true;
                },
                get isEditQuestionValid() {
                    if (!this.editingQuestion) return false;

                    // Validasi pertanyaan
                    if (!this.editingQuestion.question.trim()) return false;
                    if (this.editingQuestion.score < 1) return false;

                    // Validasi berdasarkan tipe
                    if (this.editingQuestion.type === 'PG') {
                        // Minimal 2 pilihan, minimal 1 benar, semua pilihan harus ada teks
                        if (this.editingQuestion.choices.length < 2) return false;
                        if (!this.editingQuestion.choices.some(c => c.is_correct)) return false;
                        if (this.editingQuestion.choices.some(c => !c.text.trim())) return false;
                    } else if (this.editingQuestion.type === 'IS') {
                        // Minimal 1 jawaban
                        if (this.editingQuestion.short_answers.length < 1) return false;
                        if (this.editingQuestion.short_answers.some(a => !a.trim())) return false;
                    }

                    return true;
                },
                get canImport() {
                    if (this.importMode === 'from_exam') {
                        return this.selectedImportQuiz && this.importPreview.some(q => q.selected);
                    } else if (this.importMode === 'from_file') {
                        return this.fileImportPreview.length > 0;
                    }
                    return false;
                },
                get selectedImportCount() {
                    return this.importPreview.filter(q => q.selected).length;
                },

                // Methods
                init() {
                    console.log('Quiz Question Creator initialized');
                    console.log('Initial questions:', this.questions);
                    console.log('Exam ID: {{ $quiz->id }}');
                    console.log('CSRF Token: {{ csrf_token() }}');
                },

                // Choice Management
                addChoice() {
                    this.newQuestion.choices.push({
                        id: this.newQuestion.choices.length + 1,
                        text: '',
                        is_correct: false
                    });
                },

                removeChoice(index) {
                    if (this.newQuestion.choices.length > 2) {
                        this.newQuestion.choices.splice(index, 1);
                    }
                },

                setCorrectChoice(index) {
                    this.newQuestion.choices.forEach((choice, i) => {
                        choice.is_correct = i === index;
                    });
                },

                addShortAnswer() {
                    this.newQuestion.short_answers.push('');
                },

                removeShortAnswer(index) {
                    if (this.newQuestion.short_answers.length > 1) {
                        this.newQuestion.short_answers.splice(index, 1);
                    }
                },

                // Edit Choice Management
                addChoiceToEdit() {
                    this.editingQuestion.choices.push({
                        id: this.editingQuestion.choices.length + 1,
                        text: '',
                        is_correct: false
                    });
                },

                removeChoiceFromEdit(index) {
                    if (this.editingQuestion.choices.length > 2) {
                        this.editingQuestion.choices.splice(index, 1);
                    }
                },

                setCorrectChoiceInEdit(index) {
                    this.editingQuestion.choices.forEach((choice, i) => {
                        choice.is_correct = i === index;
                    });
                },

                addShortAnswerToEdit() {
                    this.editingQuestion.short_answers.push('');
                },

                removeShortAnswerFromEdit(index) {
                    if (this.editingQuestion.short_answers.length > 1) {
                        this.editingQuestion.short_answers.splice(index, 1);
                    }
                },

                // Question Management
                addQuestion() {
                    if (!this.isQuestionValid) {
                        showAlert('warning', 'Perhatian!', 'Harap lengkapi semua field yang diperlukan!');
                        return;
                    }

                    // Create question object
                    const question = {
                        id: Date.now(), // Temporary ID for frontend
                        type: this.newQuestion.type,
                        question: this.newQuestion.question.trim(),
                        score: parseInt(this.newQuestion.score),
                        explanation: this.newQuestion.explanation.trim(),
                        choices: this.newQuestion.type === 'PG' ? [...this.newQuestion.choices.map(c => ({
                            ...c,
                            text: c.text.trim()
                        }))] : [],
                        short_answers: this.newQuestion.type === 'IS' ?
                            this.newQuestion.short_answers.map(a => a.trim()).filter(a => a) : []
                    };

                    this.questions.push(question);
                    this.resetNewQuestion();

                    showAlert('success', 'Berhasil!', 'Soal berhasil ditambahkan ke daftar.');

                    setTimeout(() => {
                        const questionList = document.querySelector('.question-list-container');
                        if (questionList) {
                            questionList.scrollTop = questionList.scrollHeight;
                        }
                    }, 100);
                },

                resetNewQuestion() {
                    this.newQuestion = {
                        id: null,
                        type: 'PG',
                        question: '',
                        score: 1,
                        explanation: '',
                        choices: [{
                                id: 1,
                                text: '',
                                is_correct: false
                            },
                            {
                                id: 2,
                                text: '',
                                is_correct: false
                            },
                            {
                                id: 3,
                                text: '',
                                is_correct: false
                            },
                            {
                                id: 4,
                                text: '',
                                is_correct: false
                            }
                        ],
                        short_answers: []
                    };
                },

                editQuestion(index) {
                    const question = this.questions[index];
                    this.editingIndex = index;

                    // Deep copy the question for editing
                    this.editingQuestion = JSON.parse(JSON.stringify(question));

                    // Ensure choices and short_answers arrays exist
                    if (this.editingQuestion.type === 'PG' && !this.editingQuestion.choices) {
                        this.editingQuestion.choices = [];
                    }
                    if (this.editingQuestion.type === 'IS' && !this.editingQuestion.short_answers) {
                        this.editingQuestion.short_answers = [];
                    }

                    this.showEditModal = true;

                    // Focus on question textarea
                    setTimeout(() => {
                        const textarea = this.$el.querySelector('#editModal textarea');
                        if (textarea) textarea.focus();
                    }, 100);
                },

                updateQuestion() {
                    if (!this.isEditQuestionValid) {
                        showAlert('warning', 'Perhatian!', 'Harap lengkapi semua field yang diperlukan!');
                        return;
                    }

                    // Update the question in the array
                    this.questions[this.editingIndex] = {
                        ...this.editingQuestion,
                        question: this.editingQuestion.question.trim(),
                        score: parseInt(this.editingQuestion.score),
                        explanation: this.editingQuestion.explanation ? this.editingQuestion.explanation.trim() : '',
                        choices: this.editingQuestion.type === 'PG' ?
                            this.editingQuestion.choices.map(c => ({
                                ...c,
                                text: c.text.trim()
                            })) : [],
                        short_answers: this.editingQuestion.type === 'IS' ?
                            this.editingQuestion.short_answers.map(a => a.trim()).filter(a => a) : []
                    };

                    this.cancelEdit();
                    showAlert('success', 'Berhasil!', 'Soal berhasil diperbarui.');
                },

                cancelEdit() {
                    this.editingQuestion = null;
                    this.editingIndex = null;
                    this.showEditModal = false;
                },

                showDeleteModal(index, questionText) {
                    this.deleteIndex = index;
                    this.deleteQuestionText = questionText.length > 100 ?
                        questionText.substring(0, 100) + '...' : questionText;
                    this.showDeleteModal = true;
                },

                cancelDelete() {
                    this.deleteIndex = null;
                    this.deleteQuestionText = '';
                    this.showDeleteModal = false;
                },

                confirmDelete() {
                    if (this.deleteIndex !== null) {
                        this.questions.splice(this.deleteIndex, 1);
                        showAlert('success', 'Berhasil!', 'Soal berhasil dihapus.');
                        this.cancelDelete();
                    }
                },

                // Import Management
                async loadImportPreview() {
                    if (!this.selectedImportQuiz) {
                        this.importPreview = [];
                        return;
                    }

                    try {
                        const response = await fetch(`/guru/quiz/import-preview/${this.selectedImportQuiz}`, {
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.importPreview = data.questions.map(q => ({
                                ...q,
                                selected: true
                            }));
                        } else {
                            this.importPreview = [];
                            showAlert('error', 'Error!', 'Gagal memuat preview soal.');
                        }
                    } catch (error) {
                        console.error('Error loading import preview:', error);
                        this.importPreview = [];
                        showAlert('error', 'Error!', 'Terjadi kesalahan saat memuat preview.');
                    }
                },

                selectAllImportQuestions(select) {
                    this.importPreview.forEach(q => q.selected = select);
                },

                // File Import Methods
                handleDragOver(event) {
                    event.preventDefault();
                    this.isDragging = true;
                },

                handleDragLeave(event) {
                    event.preventDefault();
                    this.isDragging = false;
                },

                handleFileDrop(event) {
                    event.preventDefault();
                    this.isDragging = false;

                    const files = event.dataTransfer.files;
                    if (files.length > 0) {
                        this.processFile(files[0]);
                    }
                },

                handleFileSelect(event) {
                    const files = event.target.files;
                    if (files.length > 0) {
                        this.processFile(files[0]);
                    }
                },

                async processFile(file) {
                    // Validasi file
                    const validExtensions = ['.xlsx', '.xls', '.csv'];
                    const fileExt = '.' + file.name.split('.').pop().toLowerCase();

                    if (!validExtensions.includes(fileExt)) {
                        showAlert('error', 'Error!', 'Format file tidak didukung. Gunakan Excel atau CSV.');
                        return;
                    }

                    if (file.size > 5 * 1024 * 1024) { // 5MB limit
                        showAlert('error', 'Error!', 'Ukuran file terlalu besar. Maksimal 5MB.');
                        return;
                    }

                    try {
                        // Simulasi parsing file
                        // Di implementasi nyata, kirim ke server untuk parsing
                        this.fileImportPreview = [
                            {
                                type: 'PG',
                                question: 'Contoh soal dari file?',
                                score: 10,
                                choices: [
                                    { text: 'Pilihan A', is_correct: false },
                                    { text: 'Pilihan B', is_correct: true },
                                    { text: 'Pilihan C', is_correct: false },
                                    { text: 'Pilihan D', is_correct: false }
                                ]
                            },
                            {
                                type: 'IS',
                                question: 'Ibukota Indonesia adalah?',
                                score: 5,
                                short_answers: ['Jakarta', 'DKI Jakarta']
                            }
                        ];

                        showAlert('info', 'Berhasil!', `Berhasil membaca ${this.fileImportPreview.length} soal dari file.`);
                    } catch (error) {
                        console.error('Error processing file:', error);
                        showAlert('error', 'Error!', 'Gagal membaca file. Pastikan format sesuai template.');
                    }
                },

                async importQuestions() {
                    let questionsToImport = [];

                    if (this.importMode === 'from_exam') {
                        questionsToImport = this.importPreview
                            .filter(q => q.selected)
                            .map(q => ({
                                type: q.type,
                                question: q.question,
                                score: q.score,
                                explanation: q.explanation || '',
                                choices: q.choices || [],
                                short_answers: q.short_answers || []
                            }));
                    } else if (this.importMode === 'from_file') {
                        questionsToImport = this.fileImportPreview;
                    }

                    if (questionsToImport.length === 0) {
                        showAlert('warning', 'Perhatian!', 'Tidak ada soal yang dipilih untuk diimport.');
                        return;
                    }

                    // Check if adding these questions will exceed the limit
                    const totalAfterImport = this.questions.length + questionsToImport.length;
                    if (totalAfterImport > 50) {
                        showAlert('error', 'Error!', `Maksimal 50 soal. Anda akan memiliki ${totalAfterImport} soal setelah import.`);
                        return;
                    }

                    // Add questions to the list
                    questionsToImport.forEach(q => {
                        this.questions.push({
                            id: Date.now() + Math.random(),
                            ...q
                        });
                    });

                    showAlert('success', 'Berhasil!', `Berhasil mengimport ${questionsToImport.length} soal.`);
                    this.showImportModal = false;

                    // Reset import state
                    this.selectedImportQuiz = '';
                    this.importPreview = [];
                    this.fileImportPreview = [];
                    this.selectedFile = null;
                },

                async saveQuestions() {
                    if (this.questions.length === 0) {
                        showAlert('warning', 'Perhatian!', 'Belum ada soal untuk disimpan!');
                        return;
                    }

                    this.isSaving = true;
                    showAlert('info', 'Menyimpan...', 'Sedang menyimpan soal ke server...');

                    try {
                        const formattedQuestions = this.questions.map(q => {
                            const questionData = {
                                question: q.question,
                                type: q.type,
                                score: parseInt(q.score),
                                explanation: q.explanation || null
                            };

                            if (q.type === 'PG') {
                                questionData.choices = q.choices
                                    .map((c, index) => ({
                                        text: c.text.trim(),
                                        is_correct: c.is_correct === true || c.is_correct === 1 || c.is_correct === '1'
                                    }))
                                    .filter(c => c.text !== '');

                                if (questionData.choices.length < 2) {
                                    throw new Error(`Soal "${q.question.substring(0, 50)}..." harus memiliki minimal 2 pilihan jawaban`);
                                }

                                if (!questionData.choices.some(c => c.is_correct)) {
                                    throw new Error(`Soal "${q.question.substring(0, 50)}..." harus memiliki jawaban yang benar`);
                                }
                            } else if (q.type === 'IS') {
                                questionData.short_answers = q.short_answers
                                    .map(a => a.trim())
                                    .filter(a => a !== '');

                                if (questionData.short_answers.length < 1) {
                                    throw new Error(`Soal "${q.question.substring(0, 50)}..." harus memiliki minimal 1 jawaban singkat`);
                                }
                            }

                            return questionData;
                        }).filter(q => {
                            if (!q.question.trim()) return false;
                            return true;
                        });

                        if (formattedQuestions.length === 0) {
                            showAlert('warning', 'Perhatian!', 'Tidak ada soal yang valid untuk disimpan!');
                            this.isSaving = false;
                            return;
                        }

                        const response = await fetch('{{ route("guru.quiz.questions.store", $quiz->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                questions: formattedQuestions,
                                _method: 'POST'
                            })
                        });

                        const responseText = await response.text();
                        let data;
                        try {
                            data = JSON.parse(responseText);
                        } catch (e) {
                            throw new Error('Respon server tidak valid.');
                        }

                        if (data.success) {
                            if (data.questions) {
                                this.questions = data.questions.map((q, index) => ({
                                    id: q.id || Date.now() + index,
                                    question: q.question,
                                    type: q.type,
                                    score: q.score,
                                    explanation: q.explanation,
                                    choices: q.choices ? q.choices.map((c, cIndex) => ({
                                        id: c.id || cIndex,
                                        text: c.text,
                                        is_correct: c.is_correct === true || c.is_correct === 1 || c.is_correct === '1'
                                    })) : [],
                                    short_answers: q.short_answers || []
                                }));
                            }

                            showAlert('success', 'Berhasil!', data.message || 'Soal berhasil disimpan!');

                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                        } else {
                            let errorMessage = data.message || 'Gagal menyimpan soal';
                            if (data.errors) {
                                errorMessage += ': ' + Object.values(data.errors).flat().join(', ');
                            }
                            showAlert('error', 'Gagal!', errorMessage);
                        }
                    } catch (error) {
                        console.error('Error saving questions:', error);
                        showAlert('error', 'Kesalahan!', 'Terjadi kesalahan saat menyimpan soal: ' + error.message);
                    } finally {
                        this.isSaving = false;
                    }
                },

                previewQuiz() {
                    if (this.questions.length === 0) {
                        showAlert('warning', 'Perhatian!', 'Belum ada soal untuk dipreview!');
                        return;
                    }

                    this.saveQuestions().then(() => {
                        window.location.href = '{{ route("guru.quiz.preview", $quiz->id) }}';
                    });
                },

                async finalizeQuiz() {
                    if (this.questions.length === 0) {
                        showAlert('warning', 'Perhatian!', 'Minimal harus ada 1 soal sebelum mempublish quiz!');
                        return;
                    }

                    if (!confirm('Apakah Anda yakin ingin mempublikasikan quiz ini? Quiz akan aktif dan dapat diakses oleh siswa.')) {
                        return;
                    }

                    this.isFinalizing = true;

                    try {
                        await this.saveQuestions();

                        const response = await fetch('{{ route("guru.quiz.finalize", $quiz->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                action: 'publish'
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            showAlert('success', 'Berhasil!', data.message);
                            setTimeout(() => {
                                window.location.href = data.redirect || '{{ route("guru.quiz.index") }}';
                            }, 1500);
                        } else {
                            showAlert('error', 'Gagal!', data.message || 'Gagal mempublish quiz');
                        }
                    } catch (error) {
                        console.error('Error finalizing quiz:', error);
                        showAlert('error', 'Kesalahan!', 'Terjadi kesalahan saat mempublish quiz');
                    } finally {
                        this.isFinalizing = false;
                    }
                }
            };
        }

        // Watch for selectedImportQuiz changes
        document.addEventListener('alpine:init', () => {
            Alpine.effect(() => {
                const app = Alpine.$data(document.querySelector('[x-data="quizQuestionCreator()"]'));
                if (app && app.selectedImportQuiz) {
                    app.loadImportPreview();
                }
            });
        });

        // Fungsi showAlert
        function showAlert(type, title, message, duration = 5000) {
            const existingAlert = document.querySelector('.custom-alert');
            if (existingAlert) {
                existingAlert.remove();
            }

            const alertTypes = {
                success: {
                    icon: '✓',
                    color: 'success',
                    bgColor: 'bg-green-50'
                },
                error: {
                    icon: '✗',
                    color: 'error',
                    bgColor: 'bg-red-50'
                },
                warning: {
                    icon: '⚠',
                    color: 'warning',
                    bgColor: 'bg-yellow-50'
                },
                info: {
                    icon: 'ℹ',
                    color: 'info',
                    bgColor: 'bg-blue-50'
                }
            };

            const alertType = alertTypes[type] || alertTypes.info;

            const alertDiv = document.createElement('div');
            alertDiv.className = `custom-alert ${alertType.color} show`;
            alertDiv.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <span class="text-lg font-bold ${type === 'success' ? 'text-green-600' : type === 'error' ? 'text-red-600' : type === 'warning' ? 'text-yellow-600' : 'text-blue-600'}">
                            ${alertType.icon}
                        </span>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium ${type === 'success' ? 'text-green-800' : type === 'error' ? 'text-red-800' : type === 'warning' ? 'text-yellow-800' : 'text-blue-800'}">
                            ${title}
                        </h3>
                        <div class="mt-2 text-sm ${type === 'success' ? 'text-green-700' : type === 'error' ? 'text-red-700' : type === 'warning' ? 'text-yellow-700' : 'text-blue-700'}">
                            <p>${message}</p>
                        </div>
                    </div>
                    <div class="ml-auto pl-3">
                        <button type="button" onclick="this.parentElement.parentElement.parentElement.remove()"
                            class="inline-flex rounded-md p-1.5 ${type === 'success' ? 'text-green-500 hover:bg-green-100' : type === 'error' ? 'text-red-500 hover:bg-red-100' : type === 'warning' ? 'text-yellow-500 hover:bg-yellow-100' : 'text-blue-500 hover:bg-blue-100'} focus:outline-none">
                            <span class="sr-only">Tutup</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(alertDiv);

            setTimeout(() => {
                if (alertDiv.parentElement) {
                    alertDiv.remove();
                }
            }, duration);
        }

        // Debug mode
        document.addEventListener('DOMContentLoaded', function() {
            const debugBtn = document.createElement('button');
            debugBtn.textContent = 'Debug Console';
            debugBtn.className = 'fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded-lg z-50 hidden';
            debugBtn.onclick = function() {
                const app = document.querySelector('[x-data="quizQuestionCreator()"]').__x.$data;
                console.log('Current State:', app);
                console.log('Questions:', app.questions);
                console.log('New Question:', app.newQuestion);
            };
            document.body.appendChild(debugBtn);

            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                debugBtn.classList.remove('hidden');
            }
        });
    </script>
@endsection
