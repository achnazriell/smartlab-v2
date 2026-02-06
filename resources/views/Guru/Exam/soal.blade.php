@extends('layouts.appTeacher')

@section('content')
    <!-- SweetAlert2 Library -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
                <span class="ml-2 font-medium">Pengaturan Ujian</span>
            </div>
            <div class="w-12 h-px bg-green-500"></div>
            <div class="flex items-center text-blue-600">
                <span class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-600 text-white font-bold text-sm">2</span>
                <span class="ml-2 font-bold">Buat Soal</span>
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

        <!-- Dynamic Form Modal -->
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

                <!-- Basic Question Info -->
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

                <!-- Question Text -->
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-slate-700">Pertanyaan</label>
                    <textarea id="question-text" rows="3" placeholder="Tuliskan pertanyaan di sini..."
                        class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>

                <!-- PG Options -->
                <div id="pg-options" class="space-y-4 pt-4 border-t border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase">Opsi Jawaban Pilihan Ganda</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @for ($i = 0; $i < 4; $i++)
                            <div class="flex items-center space-x-3">
                                <input type="radio" name="correct_answer" value="{{ $i }}"
                                    class="w-4 h-4 text-blue-600 correct-answer-radio" {{ $i == 0 ? 'checked' : '' }}>
                                <input type="text" data-index="{{ $i }}"
                                    placeholder="Opsi {{ chr(65 + $i) }}"
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

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-slate-200">
                    <button type="button" id="cancel-question-btn"
                        class="px-6 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" id="save-question-btn"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                        Simpan Soal
                    </button>
                </div>
            </form>
        </div>

        <!-- Questions List -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Daftar Soal</h3>
            <div id="questions-list" class="space-y-4">
                @if($exam->questions->count() > 0)
                    @foreach($exam->questions as $index => $question)
                        <div class="p-4 border border-slate-200 rounded-lg hover:border-blue-300 transition-colors">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded">
                                            {{ $question->type }}
                                        </span>
                                        <span class="text-sm text-slate-500">
                                            Skor: {{ $question->score }}
                                        </span>
                                    </div>
                                    <p class="text-slate-800 font-medium">
                                        {{ $index + 1 }}. {{ $question->question }}
                                    </p>

                                    @if($question->type === 'PG')
                                        <div class="mt-2 pl-4 space-y-1">
                                            @foreach($question->choices as $choice)
                                                <div class="text-sm {{ $choice->is_correct ? 'text-green-600 font-semibold' : 'text-slate-600' }}">
                                                    {{ $choice->label }}. {{ $choice->text }}
                                                    @if($choice->is_correct)
                                                        <span class="ml-2">✓</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="mt-2 pl-4 text-sm text-green-600 font-semibold">
                                            Jawaban: {{ is_array($question->short_answers) ? implode(', ', $question->short_answers) : $question->short_answers }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex space-x-2 ml-4">
                                    <button onclick="editQuestion({{ $question->id }})"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="deleteQuestion({{ $question->id }})"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-12 text-slate-400">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-lg font-medium">Belum ada soal</p>
                        <p class="text-sm">Klik "Tambah Soal" untuk memulai</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Finalize Button -->
        <div class="flex justify-end">
            <button onclick="showConfirmationModal()"
                class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg transition-all shadow-md hover:shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Selesai & Simpan Ujian
            </button>
        </div>

        <!-- Confirmation Modal -->
        <div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
                <h3 class="text-xl font-bold text-slate-800 mb-4">Konfirmasi Penyimpanan</h3>
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between">
                        <span class="text-slate-600">Total Soal:</span>
                        <span class="font-bold" id="confirm-total-questions">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Total Skor:</span>
                        <span class="font-bold" id="confirm-total-score">0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Jenis Soal:</span>
                        <span class="font-bold" id="confirm-question-types">-</span>
                    </div>
                </div>
                <p class="text-sm text-slate-600 mb-6">
                    Setelah disimpan, ujian akan masuk status <strong>draft</strong> dan dapat diaktifkan nanti.
                </p>
                <div class="flex space-x-3">
                    <button onclick="hideConfirmationModal()"
                        class="flex-1 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition-colors">
                        Batal
                    </button>
                    <button onclick="finalizeExam()"
                        class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg transition-colors">
                        Ya, Simpan
                    </button>
                </div>
            </div>
        </div>

        <!-- Notification Toast -->
        <div id="notification" class="fixed top-4 right-4 bg-white border-l-4 p-4 rounded-lg shadow-lg hidden z-50 max-w-sm">
            <div class="flex items-center">
                <div id="notification-icon" class="mr-3"></div>
                <p id="notification-message" class="font-medium"></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const examId = {{ $exam->id }};
            let questions = @json($exam->questions);
            let isEditing = false;

            // DOM Elements
            const formSoal = document.getElementById('form-soal');
            const questionForm = document.getElementById('question-form');
            const tambahSoalBtn = document.getElementById('tambah-soal-btn');
            const closeFormBtn = document.getElementById('close-form-btn');
            const cancelBtn = document.getElementById('cancel-question-btn');
            const questionType = document.getElementById('question-type');
            const pgOptions = document.getElementById('pg-options');
            const isAnswer = document.getElementById('is-answer');

            // Event Listeners
            tambahSoalBtn.addEventListener('click', showForm);
            closeFormBtn.addEventListener('click', hideForm);
            cancelBtn.addEventListener('click', hideForm);
            questionType.addEventListener('change', toggleQuestionType);
            questionForm.addEventListener('submit', handleSubmit);

            function toggleQuestionType() {
                if (questionType.value === 'PG') {
                    pgOptions.classList.remove('hidden');
                    isAnswer.classList.add('hidden');
                } else {
                    pgOptions.classList.add('hidden');
                    isAnswer.classList.remove('hidden');
                }
            }

            function showForm() {
                isEditing = false;
                document.getElementById('editing-question-id').value = '';
                document.getElementById('form-title').textContent = 'Tambah Butir Soal Baru';
                questionForm.reset();
                toggleQuestionType();
                formSoal.classList.remove('hidden');
                formSoal.scrollIntoView({ behavior: 'smooth' });
            }

            function hideForm() {
                formSoal.classList.add('hidden');
                questionForm.reset();
                isEditing = false;
            }

            async function handleSubmit(e) {
                e.preventDefault();

                const formData = {
                    type: questionType.value,
                    question: document.getElementById('question-text').value.trim(),
                    score: parseInt(document.getElementById('question-score').value),
                };

                // Validation
                if (!formData.question) {
                    showNotification('Pertanyaan tidak boleh kosong', false);
                    return;
                }

                if (formData.type === 'PG') {
                    const options = Array.from(document.querySelectorAll('.option-input'))
                        .map(input => input.value.trim());

                    const filledOptions = options.filter(opt => opt !== '');
                    if (filledOptions.length < 2) {
                        showNotification('Minimal 2 opsi jawaban harus diisi', false);
                        return;
                    }

                    const correctAnswer = document.querySelector('input[name="correct_answer"]:checked');
                    if (!correctAnswer) {
                        showNotification('Pilih jawaban yang benar', false);
                        return;
                    }

                    formData.options = options;
                    formData.correct_answer = parseInt(correctAnswer.value);
                } else {
                    const shortAnswer = document.getElementById('short-answer').value.trim();
                    if (!shortAnswer) {
                        showNotification('Jawaban tidak boleh kosong', false);
                        return;
                    }
                    formData.short_answer = shortAnswer;
                }

                // Submit
                if (isEditing) {
                    await updateQuestion(formData);
                } else {
                    await createQuestion(formData);
                }
            }

            async function createQuestion(formData) {
                showLoading('Menyimpan soal...');

                try {
                    const response = await fetch(`/guru/exams/${examId}/questions`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();

                    if (data.success) {
                        showNotification('Soal berhasil ditambahkan', true);
                        hideForm();
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

            window.editQuestion = async function(questionId) {
                showLoading('Memuat data soal...');

                try {
                    const response = await fetch(`/guru/exams/${examId}/questions/${questionId}`);
                    const data = await response.json();

                    if (data.success) {
                        const question = data.question;

                        isEditing = true;
                        document.getElementById('editing-question-id').value = questionId;
                        document.getElementById('form-title').textContent = 'Edit Soal';

                        questionType.value = question.type;
                        document.getElementById('question-text').value = question.question;
                        document.getElementById('question-score').value = question.score;

                        toggleQuestionType();

                        if (question.type === 'PG') {
                            const optionInputs = document.querySelectorAll('.option-input');
                            question.choices.forEach((choice, index) => {
                                if (optionInputs[index]) {
                                    optionInputs[index].value = choice.text;
                                }
                                if (choice.is_correct) {
                                    document.querySelector(`input[name="correct_answer"][value="${index}"]`).checked = true;
                                }
                            });
                        } else {
                            const answers = Array.isArray(question.short_answers)
                                ? question.short_answers.join(', ')
                                : question.short_answers;
                            document.getElementById('short-answer').value = answers;
                        }

                        formSoal.classList.remove('hidden');
                        formSoal.scrollIntoView({ behavior: 'smooth' });
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showNotification('Gagal memuat data soal', false);
                } finally {
                    hideLoading();
                }
            }

            async function updateQuestion(formData) {
                const questionId = document.getElementById('editing-question-id').value;
                showLoading('Menyimpan perubahan...');

                try {
                    const response = await fetch(`/guru/exams/${examId}/questions/${questionId}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });

                    const data = await response.json();

                    if (data.success) {
                        showNotification('Soal berhasil diperbarui', true);
                        hideForm();
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

            window.deleteQuestion = async function(questionId) {
                Swal.fire({
                    title: 'Hapus Soal?',
                    text: 'Soal yang dihapus tidak dapat dikembalikan',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        performDeleteQuestion(questionId);
                    }
                });
            }

            async function performDeleteQuestion(questionId) {
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

                    if (data.success) {
                        showNotification('Soal berhasil dihapus', true);
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

            window.showConfirmationModal = function() {
                if (questions.length === 0) {
                    showNotification('Harap tambahkan minimal 1 soal sebelum menyelesaikan', false);
                    return;
                }

                document.getElementById('confirm-total-questions').textContent = questions.length;
                const totalScore = questions.reduce((total, q) => total + parseInt(q.score || 0), 0);
                document.getElementById('confirm-total-score').textContent = totalScore;

                const pgCount = questions.filter(q => q.type === 'PG').length;
                const isCount = questions.filter(q => q.type === 'IS').length;
                let typeText = '';
                if (pgCount > 0 && isCount > 0) {
                    typeText = `${pgCount} PG, ${isCount} Isian`;
                } else if (pgCount > 0) {
                    typeText = `${pgCount} Pilihan Ganda`;
                } else {
                    typeText = `${isCount} Isian Singkat`;
                }
                document.getElementById('confirm-question-types').textContent = typeText;

                document.getElementById('confirm-modal').classList.remove('hidden');
            }

            window.hideConfirmationModal = function() {
                document.getElementById('confirm-modal').classList.add('hidden');
            }

            window.finalizeExam = async function() {
                hideConfirmationModal();
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

                    if (data.success) {
                        showNotification('Ujian berhasil disimpan!', true);
                        setTimeout(() => {
                            window.location.href = data.redirect || '/guru/exams';
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

            function showLoading(message = 'Memproses...') {
                document.getElementById('loading-message').textContent = message;
                document.getElementById('loading-overlay').classList.remove('hidden');
            }

            function hideLoading() {
                document.getElementById('loading-overlay').classList.add('hidden');
            }

            function showNotification(message, isSuccess = true) {
                const notification = document.getElementById('notification');
                const notificationMessage = document.getElementById('notification-message');
                const notificationIcon = document.getElementById('notification-icon');

                notificationMessage.textContent = message;

                if (isSuccess) {
                    notification.className = 'fixed top-4 right-4 bg-white border-l-4 border-green-500 p-4 rounded-lg shadow-lg z-50 max-w-sm';
                    notificationIcon.innerHTML = '<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                } else {
                    notification.className = 'fixed top-4 right-4 bg-white border-l-4 border-red-500 p-4 rounded-lg shadow-lg z-50 max-w-sm';
                    notificationIcon.innerHTML = '<svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                }

                notification.classList.remove('hidden');
                setTimeout(() => {
                    notification.classList.add('hidden');
                }, 3000);
            }

            toggleQuestionType();
        });
    </script>
@endsection
