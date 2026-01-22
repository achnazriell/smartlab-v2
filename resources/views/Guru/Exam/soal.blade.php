@extends('layouts.appTeacher')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" id="soal-manager">
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center">
            <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div>
            <p id="loading-message" class="text-slate-700 font-medium">Menyimpan...</p>
        </div>
    </div>

    <!-- Step Indicator -->
    <div class="flex items-center justify-center space-x-4 mb-8">
        <div class="flex items-center text-green-600">
            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 text-green-600 font-bold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </span>
            <span class="ml-2 font-medium">Pengaturan</span>
        </div>
        <div class="w-12 h-px bg-green-500"></div>
        <div class="flex items-center text-blue-600">
            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-600 text-white font-bold text-sm">2</span>
            <span class="ml-2 font-bold">Daftar & Buat Soal</span>
        </div>
    </div>

    <!-- Header & Stats -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800 font-poppins">{{ $exam->title }}</h2>
            <p class="text-slate-500 text-sm">
                {{ $exam->class?->name_class ?? 'Kelas' }} •
                <span id="total-questions">{{ $exam->questions->count() }}</span> Soal •
                Total Skor: <span id="total-score">{{ $exam->questions->sum('score') }}</span>
            </p>
        </div>
        <button id="tambah-soal-btn"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Soal
        </button>
    </div>

    <!-- Dynamic Form Modal / Section -->
    <div id="form-soal" class="bg-white rounded-xl border-2 border-blue-500 shadow-xl overflow-hidden mb-8 hidden">
        <div class="p-4 bg-blue-50 border-b border-blue-100 flex justify-between items-center">
            <h3 id="form-title" class="font-bold text-blue-800">Tambah Butir Soal Baru</h3>
            <button id="close-form-btn" class="text-slate-400 hover:text-red-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="question-form" class="p-6 space-y-6">
            @csrf
            <input type="hidden" id="editing-question-id" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-slate-700">Jenis Soal</label>
                    <select id="question-type"
                            class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="PG">Pilihan Ganda</option>
                        <option value="IS">Isian Singkat</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-slate-700">Skor</label>
                    <input type="number" id="question-score" value="10" min="1" max="100"
                           class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-semibold text-slate-700">Pertanyaan</label>
                <textarea id="question-text" rows="3" placeholder="Tuliskan pertanyaan di sini..."
                          class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
            </div>

            <!-- PG Options -->
            <div id="pg-options" class="space-y-4 pt-4 border-t border-slate-100">
                <p class="text-xs font-bold text-slate-400 uppercase">Opsi Jawaban</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @for($i = 0; $i < 4; $i++)
                    <div class="flex items-center space-x-3">
                        <input type="radio" name="correct_answer" value="{{ $i }}"
                               class="w-4 h-4 text-blue-600 correct-answer-radio" {{ $i == 0 ? 'checked' : '' }}>
                        <input type="text" data-index="{{ $i }}" placeholder="Opsi {{ chr(65 + $i) }}"
                               class="option-input flex-1 px-4 py-2 rounded-lg border border-slate-200 outline-none focus:border-blue-500">
                    </div>
                    @endfor
                </div>
                <p class="text-xs text-slate-500">Pilih satu jawaban yang benar dengan mencentang radio button</p>
            </div>

            <!-- IS Answer -->
            <div id="is-answer" class="space-y-1 pt-4 border-t border-slate-100 hidden">
                <label class="text-sm font-semibold text-slate-700">
                    Jawaban Benar
                    <span class="text-xs text-slate-500">(pisahkan dengan koma untuk multiple jawaban)</span>
                </label>
                <input type="text" id="short-answer" placeholder="Contoh: jawaban1, jawaban2, jawaban lain"
                       class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                <p class="text-xs text-slate-500">Sistem akan menerima semua jawaban yang dipisahkan koma</p>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" id="cancel-btn"
                        class="px-6 py-2 text-slate-500 font-medium hover:text-slate-700">
                    Batal
                </button>
                <button type="submit" id="submit-btn"
                        class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg shadow hover:bg-blue-700 transition-all">
                    Simpan Soal
                </button>
            </div>
        </form>
    </div>

    <!-- Question List -->
    <div class="space-y-4" id="question-list">
        @foreach($exam->questions as $index => $question)
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm relative group question-item" data-id="{{ $question->id }}">
            <div class="absolute top-4 right-4 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <button class="edit-question-btn p-2 text-amber-600 hover:bg-amber-50 rounded-lg" data-id="{{ $question->id }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </button>
                <button class="delete-question-btn p-2 text-red-600 hover:bg-red-50 rounded-lg" data-id="{{ $question->id }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
            <div class="flex items-start space-x-4">
                <div class="w-8 h-8 flex-shrink-0 bg-slate-100 rounded-lg flex items-center justify-center font-bold text-slate-600">
                    {{ $index + 1 }}
                </div>
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="px-2.5 py-1 text-[10px] font-bold uppercase rounded-md
                            {{ $question->type === 'PG' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $question->type === 'PG' ? 'Pilihan Ganda' : 'Isian Singkat' }}
                        </span>
                        <span class="px-2.5 py-1 bg-slate-100 text-slate-700 text-[10px] font-bold uppercase rounded-md">
                            Skor: {{ $question->score }}
                        </span>
                    </div>
                    <p class="text-slate-800 font-medium leading-relaxed">{{ $question->question }}</p>

                    @if($question->type === 'PG')
                    <!-- PG Options Display -->
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($question->choices as $choice)
                        <div class="p-3 rounded-lg text-sm flex justify-between items-center
                            {{ $choice->is_correct ? 'border-2 border-emerald-400 bg-emerald-50 text-emerald-700 font-semibold' : 'border border-slate-200 bg-white text-slate-700' }}">
                            <span>{{ $choice->label }}. {{ $choice->text }}</span>
                            @if($choice->is_correct)
                            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                      clip-rule="evenodd"></path>
                            </svg>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <!-- IS Answer Display -->
                    <div class="mt-4 space-y-2">
                        <div class="p-3 border-2 border-emerald-400 rounded-lg text-sm bg-emerald-50">
                            <span class="text-xs font-bold text-emerald-700 uppercase block mb-1">
                                Jawaban Benar <span class="text-slate-500 text-xs normal-case">(salah satu dari):</span>
                            </span>
                            <div class="flex flex-wrap gap-2">
                                @php
                                    $shortAnswers = $question->short_answers ?? [];
                                    if (is_string($shortAnswers)) {
                                        $shortAnswers = json_decode($shortAnswers, true) ?? [];
                                    }
                                @endphp
                                @foreach($shortAnswers as $answer)
                                <span class="text-emerald-800 font-semibold bg-white px-3 py-1 rounded-md border border-emerald-200">
                                    {{ $answer }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        <p class="text-xs text-slate-500">
                            {{ count($shortAnswers) }} jawaban diterima
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Navigation Buttons -->
    <div class="flex flex-col sm:flex-row gap-4 justify-between pt-8 border-t border-slate-200">
        <a href="{{ route('guru.exams.index') }}"
           class="inline-flex items-center justify-center px-6 py-3 text-slate-600 font-semibold border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Kembali ke Daftar
        </a>
        <div class="flex space-x-3">
            <a href="{{ route('guru.exams.show', $exam->id) }}"
               class="inline-flex items-center justify-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition-colors shadow-md">
                Lihat Preview
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                </svg>
            </a>
            <button id="finalize-btn"
                    class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors shadow-md">
                Selesai & Simpan
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
// Sederhanakan questions data
const examId = {{ $exam->id }};
let questions = [];

// Initialize questions from server-side data
@foreach($exam->questions as $question)
questions.push({
    id: {{ $question->id }},
    type: '{{ $question->type }}',
    question: `{{ addslashes($question->question) }}`,
    score: {{ $question->score }},
    choices: @if($question->type === 'PG')
        [
            @foreach($question->choices as $choice)
            {
                label: '{{ $choice->label }}',
                text: `{{ addslashes($choice->text) }}`,
                is_correct: {{ $choice->is_correct ? 'true' : 'false' }}
            },
            @endforeach
        ]
    @else
        []
    @endif,
    short_answers: @if($question->type === 'IS')
        @php
            $shortAnswers = $question->short_answers ?? [];
            if (is_string($shortAnswers)) {
                $shortAnswers = json_decode($shortAnswers, true) ?? [];
            }
        @endphp
        {!! json_encode($shortAnswers) !!}
    @else
        []
    @endif
});
@endforeach

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - Initializing soal manager');
    console.log('Questions:', questions);

    // State variables
    let isEditing = false;
    let currentQuestionId = null;

    // Update stats
    function updateStats() {
        const totalQuestions = questions.length;
        const totalScore = questions.reduce((total, q) => total + parseInt(q.score || 0), 0);

        document.getElementById('total-questions').textContent = totalQuestions;
        document.getElementById('total-score').textContent = totalScore;
    }

    // Initialize stats
    updateStats();

    // Show/hide form
    function showForm(isEdit = false, questionId = null) {
        const form = document.getElementById('form-soal');
        const title = document.getElementById('form-title');
        const submitBtn = document.getElementById('submit-btn');

        if (isEdit) {
            title.textContent = 'Edit Soal';
            submitBtn.textContent = 'Update Soal';
            isEditing = true;
            currentQuestionId = questionId;
        } else {
            title.textContent = 'Tambah Butir Soal Baru';
            submitBtn.textContent = 'Simpan Soal';
            isEditing = false;
            currentQuestionId = null;
        }

        form.classList.remove('hidden');
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function hideForm() {
        document.getElementById('form-soal').classList.add('hidden');
        resetForm();
    }

    function resetForm() {
        document.getElementById('editing-question-id').value = '';
        document.getElementById('question-type').value = 'PG';
        document.getElementById('question-score').value = '5';
        document.getElementById('question-text').value = '';
        document.getElementById('short-answer').value = '';

        // Reset option inputs
        document.querySelectorAll('.option-input').forEach((input, index) => {
            input.value = '';
            if (index === 0) {
                input.placeholder = 'Opsi A';
            } else if (index === 1) {
                input.placeholder = 'Opsi B';
            } else if (index === 2) {
                input.placeholder = 'Opsi C';
            } else if (index === 3) {
                input.placeholder = 'Opsi D';
            }
        });

        // Reset radio buttons
        document.querySelectorAll('.correct-answer-radio').forEach((radio, index) => {
            radio.checked = index === 0;
        });

        // Show PG options by default
        toggleQuestionType();

        isEditing = false;
        currentQuestionId = null;
    }

    function toggleQuestionType() {
        const type = document.getElementById('question-type').value;
        const pgOptions = document.getElementById('pg-options');
        const isAnswer = document.getElementById('is-answer');

        if (type === 'PG') {
            pgOptions.classList.remove('hidden');
            isAnswer.classList.add('hidden');
        } else {
            pgOptions.classList.add('hidden');
            isAnswer.classList.remove('hidden');
        }
    }

    // Show loading
    function showLoading(message = 'Menyimpan...') {
        document.getElementById('loading-message').textContent = message;
        document.getElementById('loading-overlay').classList.remove('hidden');
    }

    // Hide loading
    function hideLoading() {
        document.getElementById('loading-overlay').classList.add('hidden');
    }

    // Show notification
    function showNotification(message, isSuccess = true) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.custom-notification');
        existingNotifications.forEach(notification => notification.remove());

        const div = document.createElement('div');
        div.className = `custom-notification fixed top-4 right-4 px-4 py-3 rounded-lg shadow-lg border z-50 max-w-md
                        ${isSuccess ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-red-100 text-red-800 border-red-200'}`;
        div.innerHTML = `
            <div class="flex items-center">
                <span class="mr-2">${isSuccess ? '✅' : '❌'}</span>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(div);

        // Remove after 3 seconds
        setTimeout(() => {
            div.style.opacity = '0';
            div.style.transition = 'opacity 0.3s';
            setTimeout(() => div.remove(), 300);
        }, 3000);
    }

    // Event Listeners
    document.getElementById('tambah-soal-btn').addEventListener('click', function(e) {
        e.preventDefault();
        showForm(false);
    });

    document.getElementById('close-form-btn').addEventListener('click', function(e) {
        e.preventDefault();
        hideForm();
    });

    document.getElementById('cancel-btn').addEventListener('click', function(e) {
        e.preventDefault();
        hideForm();
    });

    document.getElementById('question-type').addEventListener('change', toggleQuestionType);

    // Edit question buttons
    document.querySelectorAll('.edit-question-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const questionId = parseInt(this.getAttribute('data-id'));
            editQuestion(questionId);
        });
    });

    // Delete question buttons
    document.querySelectorAll('.delete-question-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const questionId = parseInt(this.getAttribute('data-id'));
            deleteQuestion(questionId);
        });
    });

    // Finalize button
    document.getElementById('finalize-btn').addEventListener('click', function() {
        finalizeExam();
    });

    // Form submission
    document.getElementById('question-form').addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted, isEditing:', isEditing);
        if (isEditing) {
            updateQuestion();
        } else {
            addQuestion();
        }
    });

    // Functions
    async function addQuestion() {
        console.log('addQuestion called');

        // Get form values
        const type = document.getElementById('question-type').value;
        const score = document.getElementById('question-score').value;
        const questionText = document.getElementById('question-text').value;

        // Validation
        if (!questionText.trim()) {
            showNotification('Pertanyaan tidak boleh kosong', false);
            return;
        }

        if (type === 'PG') {
            const options = [];
            let correctAnswer = null;

            document.querySelectorAll('.option-input').forEach((input, index) => {
                if (input.value.trim()) {
                    options.push(input.value.trim());
                }
            });

            document.querySelectorAll('.correct-answer-radio').forEach((radio, index) => {
                if (radio.checked) {
                    correctAnswer = index;
                }
            });

            if (options.length < 2) {
                showNotification('Minimal 2 opsi harus diisi', false);
                return;
            }

            if (correctAnswer === null) {
                showNotification('Pilih jawaban yang benar', false);
                return;
            }

            // Send request
            showLoading('Menyimpan soal...');

            try {
                const formData = new FormData();
                formData.append('question', questionText);
                formData.append('type', type);
                formData.append('score', score);

                options.forEach((option, index) => {
                    formData.append(`options[${index}]`, option);
                });
                formData.append('correct_answer', correctAnswer);

                const response = await fetch(`/guru/exams/${examId}/questions`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                console.log('Add response:', data);

                if (data.success) {
                    questions.push(data.question);
                    updateStats();
                    showNotification('Soal berhasil ditambahkan', true);
                    hideForm();
                    // Reload to see new question
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Terjadi kesalahan', false);
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Terjadi kesalahan saat menyimpan soal', false);
            } finally {
                hideLoading();
            }

        } else {
            // IS type
            const shortAnswer = document.getElementById('short-answer').value;

            if (!shortAnswer.trim()) {
                showNotification('Jawaban benar tidak boleh kosong', false);
                return;
            }

            showLoading('Menyimpan soal...');

            try {
                const formData = new FormData();
                formData.append('question', questionText);
                formData.append('type', type);
                formData.append('score', score);
                formData.append('short_answer', shortAnswer);

                const response = await fetch(`/guru/exams/${examId}/questions`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();
                console.log('Add IS response:', data);

                if (data.success) {
                    questions.push(data.question);
                    updateStats();
                    showNotification('Soal berhasil ditambahkan', true);
                    hideForm();
                    // Reload to see new question
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification(data.message || 'Terjadi kesalahan', false);
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Terjadi kesalahan saat menyimpan soal', false);
            } finally {
                hideLoading();
            }
        }
    }

    async function editQuestion(questionId) {
        console.log('editQuestion called:', questionId);
        showLoading('Mengambil data soal...');

        try {
            const response = await fetch(`/guru/exams/${examId}/questions/${questionId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            console.log('Edit response:', data);

            if (data.success) {
                const question = data.question;

                // Fill form
                document.getElementById('editing-question-id').value = question.id;
                document.getElementById('question-type').value = question.type;
                document.getElementById('question-score').value = question.score;
                document.getElementById('question-text').value = question.question;

                if (question.type === 'PG') {
                    // Fill options
                    document.querySelectorAll('.option-input').forEach((input, index) => {
                        if (question.choices && question.choices[index]) {
                            input.value = question.choices[index].text || '';
                        } else {
                            input.value = '';
                        }
                    });

                    // Check correct answer
                    document.querySelectorAll('.correct-answer-radio').forEach((radio, index) => {
                        if (question.choices && question.choices[index]) {
                            radio.checked = question.choices[index].is_correct;
                        } else {
                            radio.checked = index === 0;
                        }
                    });

                    document.getElementById('short-answer').value = '';
                } else {
                    document.getElementById('short-answer').value = question.short_answers ? question.short_answers.join(', ') : '';

                    // Clear options
                    document.querySelectorAll('.option-input').forEach(input => {
                        input.value = '';
                    });
                }

                toggleQuestionType();
                showForm(true, questionId);
            } else {
                showNotification(data.message || 'Gagal mengambil data soal', false);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat mengambil data soal', false);
        } finally {
            hideLoading();
        }
    }

    async function updateQuestion() {
        const questionId = document.getElementById('editing-question-id').value;
        const type = document.getElementById('question-type').value;
        const score = document.getElementById('question-score').value;
        const questionText = document.getElementById('question-text').value;

        console.log('updateQuestion called:', questionId, type, score, questionText);

        if (!questionText.trim()) {
            showNotification('Pertanyaan tidak boleh kosong', false);
            return;
        }

        showLoading('Memperbarui soal...');

        try {
            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('question', questionText);
            formData.append('type', type);
            formData.append('score', score);

            if (type === 'PG') {
                const options = [];
                let correctAnswer = null;

                document.querySelectorAll('.option-input').forEach((input, index) => {
                    if (input.value.trim()) {
                        options.push(input.value.trim());
                    }
                });

                document.querySelectorAll('.correct-answer-radio').forEach((radio, index) => {
                    if (radio.checked) {
                        correctAnswer = index;
                    }
                });

                options.forEach((option, index) => {
                    formData.append(`options[${index}]`, option);
                });
                formData.append('correct_answer', correctAnswer);
            } else {
                const shortAnswer = document.getElementById('short-answer').value;
                formData.append('short_answer', shortAnswer);
            }

            const response = await fetch(`/guru/exams/${examId}/questions/${questionId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();
            console.log('Update response:', data);

            if (data.success) {
                // Update in questions array
                const index = questions.findIndex(q => q.id == questionId);
                if (index !== -1) {
                    questions[index] = data.question;
                }

                updateStats();
                showNotification('Soal berhasil diperbarui', true);
                hideForm();
                // Reload to see updated question
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.message || 'Terjadi kesalahan', false);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat mengupdate soal', false);
        } finally {
            hideLoading();
        }
    }

    async function deleteQuestion(questionId) {
        console.log('deleteQuestion called:', questionId);

        if (!confirm('Apakah Anda yakin ingin menghapus soal ini?')) {
            return;
        }

        showLoading('Menghapus soal...');

        try {
            const response = await fetch(`/guru/exams/${examId}/questions/${questionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            console.log('Delete response:', data);

            if (data.success) {
                questions = questions.filter(q => q.id != questionId);
                updateStats();
                showNotification('Soal berhasil dihapus', true);
                // Reload to see updated list
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification(data.message || 'Terjadi kesalahan', false);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat menghapus soal', false);
        } finally {
            hideLoading();
        }
    }

    async function finalizeExam() {
        console.log('finalizeExam called');

        if (questions.length === 0) {
            showNotification('Harap tambahkan minimal 1 soal sebelum menyelesaikan', false);
            return;
        }

        if (!confirm('Apakah Anda yakin ingin menyelesaikan pembuatan soal? Ujian akan disimpan dan dipublikasikan.')) {
            return;
        }

        showLoading('Menyimpan ujian...');

        try {
            const response = await fetch(`/guru/exams/${examId}/finalize`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            console.log('Finalize response:', data);

            if (data.success) {
                showNotification('Ujian berhasil disimpan!', true);
                setTimeout(() => {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = '/guru/exams';
                    }
                }, 1500);
            } else {
                showNotification(data.message || 'Terjadi kesalahan', false);
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Terjadi kesalahan saat menyimpan ujian', false);
        } finally {
            hideLoading();
        }
    }

    // Initialize
    toggleQuestionType();
    console.log('Soal manager initialized successfully');
});
</script>

<style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }

    /* Custom notification style */
    .custom-notification {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
</style>
@endsection
