@extends('layouts.appTeacher')

@section('content')
<style>
    :root {
        --primary: #6C3DE5;
        --primary-light: #8B5CF6;
        --primary-dark: #5B21B6;
        --success: #10B981;
        --danger: #EF4444;
        --warning: #F59E0B;
    }

    * { box-sizing: border-box; }

    body { font-family: 'Inter', 'Poppins', sans-serif; }

    /* ===== STICKY HEADER ===== */
    .quiz-header {
        position: sticky;
        top: 0;
        z-index: 40;
        background: white;
        border-bottom: 2px solid #F3F4F6;
        padding: 1rem 1.5rem;
        margin: -1.5rem -1.5rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        box-shadow: 0 2px 16px rgba(108,61,229,0.08);
    }

    .quiz-header-left h1 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #111827;
        margin-bottom: 0.15rem;
    }

    .quiz-header-left p {
        font-size: 0.8rem;
        color: #6B7280;
    }

    .header-stats {
        display: flex;
        gap: 0.75rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .stat-pill {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.9rem;
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .stat-pill.blue { background: #EFF6FF; color: #1D4ED8; }
    .stat-pill.green { background: #ECFDF5; color: #059669; }
    .stat-pill.purple { background: #F5F3FF; color: #7C3AED; }

    /* ===== MAIN LAYOUT ===== */
    .main-layout {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 1.5rem;
        align-items: start;
    }

    @media (max-width: 1024px) {
        .main-layout { grid-template-columns: 1fr; }
    }

    /* ===== CARD ===== */
    .card {
        background: white;
        border-radius: 16px;
        border: 1.5px solid #F3F4F6;
        box-shadow: 0 2px 12px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #F3F4F6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header h2 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #111827;
    }

    .card-body { padding: 1.25rem; }

    /* ===== FORM FIELD ===== */
    .form-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.4rem;
    }

    .form-input, .form-textarea, .form-select {
        width: 100%;
        padding: 0.65rem 0.9rem;
        border: 1.5px solid #E5E7EB;
        border-radius: 10px;
        font-size: 0.875rem;
        color: #111827;
        background: white;
        transition: all 0.2s;
        outline: none;
    }

    .form-input:focus, .form-textarea:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(108,61,229,0.1);
    }

    .form-textarea { resize: vertical; min-height: 80px; }

    /* ===== CHOICE ITEM ===== */
    .choice-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem;
        border: 1.5px solid #E5E7EB;
        border-radius: 12px;
        background: #FAFAFA;
        transition: all 0.2s;
        margin-bottom: 0.5rem;
    }

    .choice-item.correct-choice {
        background: #F0FDF4;
        border-color: #86EFAC;
    }

    .choice-label {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #EEF2FF;
        color: #4338CA;
        font-weight: 700;
        font-size: 0.8rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .choice-item.correct-choice .choice-label {
        background: #10B981;
        color: white;
    }

    /* ===== QUESTION CARD LIST ===== */
    .question-list { max-height: 620px; overflow-y: auto; padding-right: 4px; }

    .question-list::-webkit-scrollbar { width: 5px; }
    .question-list::-webkit-scrollbar-track { background: #F9FAFB; }
    .question-list::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 3px; }

    .q-card {
        border: 1.5px solid #E5E7EB;
        border-radius: 14px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        background: white;
        transition: all 0.2s;
        cursor: pointer;
    }

    .q-card:hover { border-color: var(--primary-light); box-shadow: 0 4px 16px rgba(108,61,229,0.1); }

    .q-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 0.6rem;
    }

    .q-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
        font-size: 0.75rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .q-type-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        font-weight: 600;
        background: #EDE9FE;
        color: #6D28D9;
    }

    .q-score-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.6rem;
        border-radius: 999px;
        font-weight: 600;
        background: #FEF3C7;
        color: #92400E;
    }

    .q-text {
        font-size: 0.85rem;
        color: #374151;
        font-weight: 500;
        margin-bottom: 0.6rem;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .q-choices {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .q-choice-chip {
        font-size: 0.72rem;
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        background: #F3F4F6;
        color: #6B7280;
    }

    .q-choice-chip.correct {
        background: #D1FAE5;
        color: #065F46;
        font-weight: 600;
    }

    .q-actions {
        display: flex;
        gap: 0.4rem;
        flex-shrink: 0;
    }

    .btn-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-icon.edit { background: #EEF2FF; color: #4338CA; }
    .btn-icon.edit:hover { background: #C7D2FE; }
    .btn-icon.delete { background: #FEF2F2; color: #DC2626; }
    .btn-icon.delete:hover { background: #FECACA; }

    /* ===== BUTTONS ===== */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.6rem 1.2rem;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--primary-light));
        color: white;
    }

    .btn-primary:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(108,61,229,0.35);
    }

    .btn-success { background: linear-gradient(135deg, #10B981, #059669); color: white; }
    .btn-success:hover:not(:disabled) { box-shadow: 0 6px 20px rgba(16,185,129,0.35); transform: translateY(-1px); }

    .btn-purple { background: linear-gradient(135deg, #7C3AED, #A78BFA); color: white; }
    .btn-purple:hover:not(:disabled) { box-shadow: 0 6px 20px rgba(124,58,237,0.35); transform: translateY(-1px); }

    .btn-outline {
        background: white;
        border: 1.5px solid #E5E7EB;
        color: #374151;
    }

    .btn-outline:hover { background: #F9FAFB; border-color: #D1D5DB; }

    .btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none !important; box-shadow: none !important; }

    .btn-sm { padding: 0.4rem 0.9rem; font-size: 0.78rem; }
    .btn-full { width: 100%; }

    /* ===== PROGRESS ===== */
    .progress-bar {
        height: 6px;
        background: #F3F4F6;
        border-radius: 999px;
        overflow: hidden;
        margin-top: 0.4rem;
    }

    .progress-fill {
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, var(--primary), var(--primary-light));
        transition: width 0.4s ease;
    }

    /* ===== MODAL ===== */
    .modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        z-index: 9998;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .modal {
        background: white;
        border-radius: 20px;
        box-shadow: 0 24px 64px rgba(0,0,0,0.2);
        width: 100%;
        max-width: 620px;
        max-height: 92vh;
        display: flex;
        flex-direction: column;
        animation: modalIn 0.25s ease;
    }

    .modal.modal-sm { max-width: 420px; }

    @keyframes modalIn {
        from { opacity: 0; transform: scale(0.95) translateY(20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #F3F4F6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .modal-header h3 { font-size: 1rem; font-weight: 700; color: #111827; }

    .modal-body {
        padding: 1.25rem 1.5rem;
        overflow-y: auto;
        flex: 1;
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #F3F4F6;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        flex-shrink: 0;
    }

    /* ===== TABS ===== */
    .tabs {
        display: flex;
        border-bottom: 2px solid #F3F4F6;
        margin-bottom: 1rem;
    }

    .tab-btn {
        padding: 0.6rem 1.2rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: #6B7280;
        border: none;
        background: none;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s;
    }

    .tab-btn.active { color: var(--primary); border-bottom-color: var(--primary); }

    /* ===== IMPORT FILE DROP ZONE ===== */
    .drop-zone {
        border: 2px dashed #D1D5DB;
        border-radius: 14px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .drop-zone:hover, .drop-zone.dragover {
        border-color: var(--primary);
        background: #F5F3FF;
    }

    .drop-zone input[type="file"] { display: none; }

    /* ===== IMPORT PREVIEW LIST ===== */
    .import-list { max-height: 300px; overflow-y: auto; }

    .import-item {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.75rem;
        border-bottom: 1px solid #F3F4F6;
        transition: background 0.15s;
    }

    .import-item:hover { background: #F9FAFB; }
    .import-item.selected { background: #EDE9FE; border-left: 3px solid var(--primary); }

    /* ===== TOAST ===== */
    .toast-container {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        width: 340px;
    }

    .toast {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.9rem 1.1rem;
        border-radius: 12px;
        background: white;
        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        border-left: 4px solid;
        animation: toastIn 0.35s cubic-bezier(0.34,1.56,0.64,1);
    }

    .toast.success { border-color: #10B981; }
    .toast.error { border-color: #EF4444; }
    .toast.warning { border-color: #F59E0B; }
    .toast.info { border-color: #3B82F6; }

    @keyframes toastIn {
        from { opacity: 0; transform: translateX(60px); }
        to { opacity: 1; transform: translateX(0); }
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #6B7280;
    }

    .empty-state svg { opacity: 0.3; margin: 0 auto 1rem; }
    .empty-state h3 { font-size: 1rem; font-weight: 600; color: #374151; margin-bottom: 0.4rem; }

    /* ===== TEMPLATE DOWNLOAD HINT ===== */
    .template-hint {
        background: #F0FDF4;
        border: 1px solid #BBF7D0;
        border-radius: 10px;
        padding: 0.85rem 1rem;
        font-size: 0.8rem;
        color: #065F46;
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
    }

    /* ===== DRAG HANDLE ===== */
    .drag-handle { cursor: grab; color: #9CA3AF; flex-shrink: 0; }
    .drag-handle:active { cursor: grabbing; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6"
     x-data="quizQnA()"
     x-init="init()">

    {{-- ===== STICKY HEADER ===== --}}
    <div class="quiz-header">
        <div class="quiz-header-left">
            <h1>Buat Soal: {{ Str::limit($quiz->title, 45) }}</h1>
            <p>{{ $quiz->subject->name_subject }} &bull; Kelas {{ $quiz->class->name_class }} &bull;
               {{ $quiz->time_per_question }}s/soal &bull;
               {{ $quiz->difficulty_level == 'easy' ? 'Mudah' : ($quiz->difficulty_level == 'medium' ? 'Sedang' : 'Sulit') }}
            </p>
        </div>
        <div class="header-stats">
            <div class="stat-pill blue">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span x-text="questions.length + ' Soal'"></span>
            </div>
            <div class="stat-pill green">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-text="totalPoints + ' Poin'"></span>
            </div>
            <button @click="showImportModal = true" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                Import Soal
            </button>
            <a href="{{ route('guru.quiz.index') }}" class="btn btn-outline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>
    </div>

    {{-- ===== MAIN GRID ===== --}}
    <div class="main-layout">

        {{-- ===== LEFT: FORM + LIST ===== --}}
        <div class="space-y-5">

            {{-- FORM TAMBAH SOAL --}}
            <div class="card">
                <div class="card-header">
                    <h2>Tambah Soal Pilihan Ganda</h2>
                    <span class="stat-pill purple" x-show="questions.length > 0" x-text="questions.length + '/50'"></span>
                </div>
                <div class="card-body space-y-4">

                    {{-- Pertanyaan --}}
                    <div>
                        <label class="form-label">Pertanyaan <span class="text-red-500">*</span></label>
                        <textarea x-model="form.question" class="form-textarea" rows="3"
                            placeholder="Tulis pertanyaan di sini..."></textarea>
                        <div class="text-xs text-gray-400 mt-1 text-right" x-text="form.question.length + '/2000'"></div>
                    </div>

                    {{-- Score + Explanation --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Poin <span class="text-red-500">*</span></label>
                            <input type="number" x-model.number="form.score" min="1" max="100" class="form-input" placeholder="10">
                        </div>
                        <div>
                            <label class="form-label">Penjelasan (Opsional)</label>
                            <input type="text" x-model="form.explanation" class="form-input" placeholder="Alasan jawaban benar...">
                        </div>
                    </div>

                    {{-- Pilihan Jawaban --}}
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="form-label mb-0">Pilihan Jawaban <span class="text-red-500">*</span></label>
                            <button @click="addChoice()" :disabled="form.choices.length >= 6" class="btn btn-outline btn-sm" type="button">
                                + Tambah Pilihan
                            </button>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(choice, i) in form.choices" :key="choice._id">
                                <div class="choice-item" :class="{ 'correct-choice': choice.is_correct }">
                                    <input type="radio" :name="'correct_new'" :checked="choice.is_correct"
                                        @change="setCorrect(i)" class="w-4 h-4 text-purple-600 flex-shrink-0" style="accent-color: #6C3DE5">
                                    <div class="choice-label" x-text="String.fromCharCode(65+i)"></div>
                                    <input type="text" x-model="choice.text" class="form-input"
                                        :placeholder="'Pilihan ' + String.fromCharCode(65+i)">
                                    <button @click="removeChoice(i)" :disabled="form.choices.length <= 2"
                                        class="btn-icon delete flex-shrink-0" type="button">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <div x-show="form.choices.some(c=>c.is_correct)"
                            class="mt-2 flex items-center gap-1.5 text-emerald-600 text-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Jawaban benar sudah dipilih
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-2 border-t border-gray-100">
                        <button @click="addQuestion()" :disabled="!isFormValid || isSavingSingle"
                            class="btn btn-primary btn-full py-3">
                            <span x-show="!isSavingSingle">
                                Tambah Soal ke Daftar
                            </span>
                            <span x-show="isSavingSingle" class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- DAFTAR SOAL --}}
            <div class="card">
                <div class="card-header">
                    <h2>Daftar Soal</h2>
                    <span class="text-sm text-gray-500" x-text="questions.length + ' soal'"></span>
                </div>
                <div class="card-body">
                    <div x-show="questions.length === 0" class="empty-state">
                        <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <h3>Belum ada soal</h3>
                        <p class="text-sm">Tambahkan soal menggunakan form di atas atau import dari file.</p>
                    </div>
                    <div class="question-list" x-show="questions.length > 0">
                        <template x-for="(q, idx) in questions" :key="q.id">
                            <div class="q-card">
                                <div class="q-card-header">
                                    <div class="flex items-start gap-2 flex-1 min-w-0">
                                        <span class="q-number" x-text="idx+1"></span>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap gap-1 mb-1">
                                                <span class="q-type-badge">PG</span>
                                                <span class="q-score-badge" x-text="q.score + ' poin'"></span>
                                            </div>
                                            <p class="q-text" x-text="q.question"></p>
                                        </div>
                                    </div>
                                    <div class="q-actions">
                                        <button @click="openEditModal(idx)" class="btn-icon edit" title="Edit">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button @click="openDeleteModal(idx)" class="btn-icon delete" title="Hapus">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="q-choices">
                                    <template x-for="(c, ci) in q.choices" :key="ci">
                                        <span class="q-choice-chip" :class="{ 'correct': c.is_correct }">
                                            <span x-text="String.fromCharCode(65+ci)"></span>.
                                            <span x-text="c.text.length > 20 ? c.text.substring(0,20)+'...' : c.text"></span>
                                            <span x-show="c.is_correct">✓</span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== RIGHT: SIDEBAR ===== --}}
        <div class="space-y-4">

            {{-- Quiz Info --}}
            <div class="card">
                <div class="card-header"><h2>Info Quiz</h2></div>
                <div class="card-body space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Mapel</span>
                        <span class="font-medium">{{ $quiz->subject->name_subject }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Kelas</span>
                        <span class="font-medium">{{ $quiz->class->name_class }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Mode</span>
                        <span class="font-medium">{{ $quiz->quiz_mode == 'live' ? 'Live' : 'Homework' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Waktu/Soal</span>
                        <span class="font-medium">{{ $quiz->time_per_question }}s</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Status</span>
                        <span class="font-medium">
                            @if($quiz->status === 'published')
                                <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Published</span>
                            @elseif($quiz->status === 'draft')
                                <span class="text-yellow-600">Draft</span>
                            @else
                                <span class="text-gray-500">{{ $quiz->status }}</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- Statistik --}}
            <div class="card">
                <div class="card-header"><h2>Statistik</h2></div>
                <div class="card-body space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Total Soal</span>
                            <span class="font-bold" x-text="questions.length + '/50'"></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" :style="'width: ' + Math.min((questions.length/50)*100, 100) + '%'"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600">Total Poin</span>
                            <span class="font-bold text-purple-600" x-text="totalPoints + ' poin'"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card">
                <div class="card-header"><h2>Aksi</h2></div>
                <div class="card-body space-y-3">
                    <button @click="saveAllQuestions()" :disabled="questions.length === 0 || isSaving"
                        class="btn btn-primary btn-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Semua Soal'"></span>
                    </button>

                    <button @click="previewQuiz()" :disabled="questions.length === 0"
                        class="btn btn-purple btn-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        Preview Quiz
                    </button>

                    <button @click="finalizeQuiz()" :disabled="questions.length === 0 || isFinalizing"
                        class="btn btn-success btn-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="isFinalizing ? 'Mempublikasikan...' : 'Publikasikan Quiz'"></span>
                    </button>

                    @if($quiz->quiz_mode === 'live')
                    <a href="{{ route('guru.quiz.room', $quiz->id) }}"
                        class="btn btn-outline btn-full">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.723v6.554a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
                        Buka Ruangan
                    </a>
                    @endif
                </div>
            </div>

            {{-- Template Download --}}
            <div class="template-hint">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="font-semibold mb-1">Import dari Excel/CSV?</p>
                    <p>Format kolom: <code>question, option_a, option_b, option_c, option_d, correct_answer (A/B/C/D), score, explanation</code></p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: IMPORT ===== --}}
    <div x-show="showImportModal" class="modal-backdrop" @click.self="showImportModal = false" x-cloak>
        <div class="modal">
            <div class="modal-header">
                <h3>📥 Import Soal Pilihan Ganda</h3>
                <button @click="showImportModal = false" class="btn-icon delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="tabs">
                    <button class="tab-btn" :class="{ 'active': importTab === 'from_exam' }" @click="importTab = 'from_exam'">
                        Dari Quiz Lain
                    </button>
                    <button class="tab-btn" :class="{ 'active': importTab === 'from_file' }" @click="importTab = 'from_file'">
                        Dari File Excel/CSV
                    </button>
                </div>

                {{-- TAB: From Exam --}}
                <div x-show="importTab === 'from_exam'" class="space-y-4">
                    <div>
                        <label class="form-label">Pilih Quiz Sumber</label>
                        <select x-model="importSourceQuizId" @change="loadImportPreview()"
                            class="form-select">
                            <option value="">-- Pilih Quiz --</option>
                            @foreach($otherQuizzes as $otherQuiz)
                            <option value="{{ $otherQuiz->id }}">
                                {{ $otherQuiz->title }} ({{ $otherQuiz->questions_count }} soal)
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="isLoadingPreview" class="text-center py-6 text-gray-500">
                        <svg class="w-8 h-8 animate-spin mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Memuat soal...
                    </div>

                    <div x-show="!isLoadingPreview && importPreview.length > 0" class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">
                                Terpilih: <strong x-text="importPreview.filter(q=>q.selected).length"></strong>
                                dari <strong x-text="importPreview.length"></strong> soal PG
                            </span>
                            <div class="flex gap-2">
                                <button @click="importPreview.forEach(q=>q.selected=true)" class="btn btn-outline btn-sm">Pilih Semua</button>
                                <button @click="importPreview.forEach(q=>q.selected=false)" class="btn btn-outline btn-sm">Hapus Pilihan</button>
                            </div>
                        </div>
                        <div class="import-list border border-gray-200 rounded-xl overflow-hidden">
                            <template x-for="(q, idx) in importPreview" :key="idx">
                                <div class="import-item" :class="{ 'selected': q.selected }" @click="q.selected = !q.selected">
                                    <input type="checkbox" x-model="q.selected" @click.stop class="w-4 h-4 flex-shrink-0" style="accent-color: #6C3DE5">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex gap-1.5 mb-1">
                                            <span class="q-type-badge text-xs">PG</span>
                                            <span class="q-score-badge text-xs" x-text="q.score + ' poin'"></span>
                                        </div>
                                        <p class="text-sm text-gray-700 font-medium" x-text="q.question"></p>
                                        <div class="q-choices mt-1">
                                            <template x-for="(c, ci) in q.choices" :key="ci">
                                                <span class="q-choice-chip text-xs" :class="{ 'correct': c.is_correct }">
                                                    <span x-text="String.fromCharCode(65+ci)"></span>. <span x-text="c.text && c.text.length > 15 ? c.text.substring(0,15)+'...' : c.text"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div x-show="!isLoadingPreview && importSourceQuizId && importPreview.length === 0"
                        class="text-center py-6 text-yellow-600 bg-yellow-50 rounded-xl text-sm">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Quiz ini tidak memiliki soal Pilihan Ganda.
                    </div>
                </div>

                {{-- TAB: From File --}}
                <div x-show="importTab === 'from_file'" class="space-y-4">
                    <div class="template-hint">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="font-semibold">Format kolom Excel/CSV:</p>
                            <p class="mt-0.5"><code>question | option_a | option_b | option_c | option_d | correct_answer | score | explanation</code></p>
                            <p class="mt-0.5">correct_answer diisi: <strong>A</strong>, <strong>B</strong>, <strong>C</strong>, atau <strong>D</strong></p>
                        </div>
                    </div>

                    <div class="drop-zone" :class="{ 'dragover': isDragging }"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleFileDrop($event)"
                        @click="$refs.fileInput.click()">
                        <input type="file" x-ref="fileInput" accept=".xlsx,.xls,.csv" @change="handleFileSelect($event)">
                        <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="text-sm text-gray-600 font-medium" x-text="selectedFileName || 'Klik atau drag & drop file Excel/CSV di sini'"></p>
                        <p class="text-xs text-gray-400 mt-1">Format: .xlsx, .xls, .csv (maks. 5MB)</p>
                    </div>

                    <div x-show="isUploadingFile" class="text-center py-4 text-purple-600 text-sm">
                        <svg class="w-6 h-6 animate-spin mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Memproses file...
                    </div>

                    <div x-show="fileImportPreview.length > 0" class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Ditemukan <strong x-text="fileImportPreview.length"></strong> soal valid</span>
                        </div>
                        <div class="import-list border border-gray-200 rounded-xl overflow-hidden">
                            <template x-for="(q, idx) in fileImportPreview.slice(0, 10)" :key="idx">
                                <div class="import-item">
                                    <span class="q-number" x-text="idx+1"></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-700 font-medium" x-text="q.question"></p>
                                        <div class="q-choices mt-1">
                                            <template x-for="(c, ci) in q.choices" :key="ci">
                                                <span class="q-choice-chip text-xs" :class="{ 'correct': c.is_correct }">
                                                    <span x-text="String.fromCharCode(65+ci)"></span>. <span x-text="c.text && c.text.length > 15 ? c.text.substring(0,15)+'...' : c.text"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                    <span class="q-score-badge text-xs flex-shrink-0" x-text="q.score + ' poin'"></span>
                                </div>
                            </template>
                            <div x-show="fileImportPreview.length > 10" class="text-center py-2 text-sm text-gray-500 border-t">
                                + <span x-text="fileImportPreview.length - 10"></span> soal lainnya
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button @click="showImportModal = false" class="btn btn-outline">Batal</button>
                <button @click="executeImport()" :disabled="!canImport || isImporting"
                    class="btn btn-primary">
                    <span x-show="!isImporting" x-text="'Import ' + importCount + ' Soal'"></span>
                    <span x-show="isImporting" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Mengimport...
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: EDIT ===== --}}
    <div x-show="showEditModal" class="modal-backdrop" @click.self="showEditModal = false" x-cloak>
        <div class="modal">
            <div class="modal-header">
                <h3>✏️ Edit Soal</h3>
                <button @click="showEditModal = false" class="btn-icon delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="modal-body space-y-4" x-show="editForm">
                <div>
                    <label class="form-label">Pertanyaan</label>
                    <textarea x-model="editForm.question" class="form-textarea" rows="3"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Poin</label>
                        <input type="number" x-model.number="editForm.score" min="1" max="100" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Penjelasan</label>
                        <input type="text" x-model="editForm.explanation" class="form-input">
                    </div>
                </div>
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="form-label mb-0">Pilihan Jawaban</label>
                        <button @click="addEditChoice()" :disabled="editForm.choices.length >= 6" class="btn btn-outline btn-sm">+ Tambah</button>
                    </div>
                    <div class="space-y-2">
                        <template x-for="(c, i) in editForm.choices" :key="c._id || i">
                            <div class="choice-item" :class="{ 'correct-choice': c.is_correct }">
                                <input type="radio" name="edit_correct" :checked="c.is_correct"
                                    @change="setEditCorrect(i)" class="w-4 h-4" style="accent-color: #6C3DE5">
                                <div class="choice-label" x-text="String.fromCharCode(65+i)"></div>
                                <input type="text" x-model="c.text" class="form-input">
                                <button @click="removeEditChoice(i)" :disabled="editForm.choices.length <= 2" class="btn-icon delete flex-shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button @click="showEditModal = false" class="btn btn-outline">Batal</button>
                <button @click="saveEdit()" :disabled="!isEditFormValid || isSavingEdit" class="btn btn-primary">
                    <span x-show="!isSavingEdit">Simpan Perubahan</span>
                    <span x-show="isSavingEdit">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== MODAL: DELETE ===== --}}
    <div x-show="showDeleteModal" class="modal-backdrop" @click.self="showDeleteModal = false" x-cloak>
        <div class="modal modal-sm">
            <div class="modal-header">
                <h3 class="text-red-600">🗑️ Hapus Soal</h3>
                <button @click="showDeleteModal = false" class="btn-icon delete">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700 mb-4">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Soal ini akan dihapus permanen dan tidak dapat dikembalikan.
                </div>
                <p class="text-sm text-gray-600 font-medium" x-text="deleteQuestionText"></p>
            </div>
            <div class="modal-footer">
                <button @click="showDeleteModal = false" class="btn btn-outline">Batal</button>
                <button @click="confirmDelete()" :disabled="isDeleting"
                    class="btn" style="background: #EF4444; color: white">
                    <span x-show="!isDeleting">Ya, Hapus</span>
                    <span x-show="isDeleting">Menghapus...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== TOAST CONTAINER ===== --}}
    <div id="toast-container" class="toast-container"></div>

</div>

<script>
function quizQnA() {
    return {
        // ===== STATE =====
        questions: @json($questionsData).filter(q => q.type === 'PG'),

        // Form tambah soal baru
        form: {
            question: '',
            score: 10,
            explanation: '',
            choices: [
                { _id: 1, text: '', is_correct: false },
                { _id: 2, text: '', is_correct: false },
                { _id: 3, text: '', is_correct: false },
                { _id: 4, text: '', is_correct: false },
            ]
        },

        // Edit
        showEditModal: false,
        editForm: null,
        editIndex: null,
        editQuestionId: null,
        isSavingEdit: false,

        // Delete
        showDeleteModal: false,
        deleteIndex: null,
        deleteQuestionId: null,
        deleteQuestionText: '',
        isDeleting: false,

        // Import
        showImportModal: false,
        importTab: 'from_exam',
        importSourceQuizId: '',
        importPreview: [],
        isLoadingPreview: false,
        fileImportPreview: [],
        selectedFileName: '',
        isUploadingFile: false,
        isDragging: false,
        isImporting: false,

        // Saving
        isSavingSingle: false,
        isSaving: false,
        isFinalizing: false,

        // ===== COMPUTED =====
        get totalPoints() {
            return this.questions.reduce((s, q) => s + (parseInt(q.score) || 0), 0);
        },
        get isFormValid() {
            if (!this.form.question.trim()) return false;
            if (this.form.score < 1) return false;
            if (this.form.choices.length < 2) return false;
            if (!this.form.choices.some(c => c.is_correct)) return false;
            if (this.form.choices.some(c => !c.text.trim())) return false;
            return true;
        },
        get isEditFormValid() {
            if (!this.editForm) return false;
            if (!this.editForm.question.trim()) return false;
            if (this.editForm.score < 1) return false;
            if (!this.editForm.choices.some(c => c.is_correct)) return false;
            if (this.editForm.choices.some(c => !c.text.trim())) return false;
            return true;
        },
        get canImport() {
            if (this.importTab === 'from_exam') return this.importPreview.some(q => q.selected);
            if (this.importTab === 'from_file') return this.fileImportPreview.length > 0;
            return false;
        },
        get importCount() {
            if (this.importTab === 'from_exam') return this.importPreview.filter(q => q.selected).length;
            return this.fileImportPreview.length;
        },

        // ===== INIT =====
        init() {
            console.log('[INIT] quizQnA ready, questions:', this.questions.length);
        },

        // ===== FORM: CHOICES =====
        addChoice() {
            if (this.form.choices.length >= 6) return;
            this.form.choices.push({ _id: Date.now(), text: '', is_correct: false });
        },
        removeChoice(i) {
            if (this.form.choices.length <= 2) return;
            this.form.choices.splice(i, 1);
        },
        setCorrect(i) {
            this.form.choices.forEach((c, idx) => c.is_correct = idx === i);
        },

        // ===== FORM: ADD QUESTION =====
        async addQuestion() {
            if (!this.isFormValid) {
                showToast('warning', 'Perhatian', 'Lengkapi semua field. Pilih 1 jawaban benar!');
                return;
            }
            if (this.questions.length >= 50) {
                showToast('error', 'Batas Soal', 'Maksimal 50 soal per quiz.');
                return;
            }
            this.isSavingSingle = true;
            try {
                const res = await fetch(`/guru/quiz/{{ $quiz->id }}/question`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        type: 'PG',
                        question: this.form.question.trim(),
                        score: parseInt(this.form.score),
                        explanation: this.form.explanation.trim() || null,
                        choices: this.form.choices.map(c => ({ text: c.text.trim(), is_correct: c.is_correct }))
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.questions.push({
                        ...data.question,
                        choices: data.question.choices || this.form.choices.map(c => ({ text: c.text, is_correct: c.is_correct }))
                    });
                    this.resetForm();
                    showToast('success', 'Berhasil!', 'Soal berhasil ditambahkan.');
                } else {
                    showToast('error', 'Gagal', data.message || 'Terjadi kesalahan');
                }
            } catch(e) {
                showToast('error', 'Error', 'Gagal menghubungi server');
            } finally {
                this.isSavingSingle = false;
            }
        },

        resetForm() {
            this.form = {
                question: '',
                score: 10,
                explanation: '',
                choices: [
                    { _id: 1, text: '', is_correct: false },
                    { _id: 2, text: '', is_correct: false },
                    { _id: 3, text: '', is_correct: false },
                    { _id: 4, text: '', is_correct: false },
                ]
            };
        },

        // ===== EDIT =====
        openEditModal(idx) {
            const q = this.questions[idx];
            this.editIndex = idx;
            this.editQuestionId = q.id;
            this.editForm = {
                question: q.question,
                score: q.score,
                explanation: q.explanation || '',
                choices: (q.choices || []).map((c, i) => ({
                    _id: c.id || Date.now() + i,
                    text: c.text,
                    is_correct: c.is_correct
                }))
            };
            this.showEditModal = true;
        },
        addEditChoice() {
            if (this.editForm.choices.length >= 6) return;
            this.editForm.choices.push({ _id: Date.now(), text: '', is_correct: false });
        },
        removeEditChoice(i) {
            if (this.editForm.choices.length <= 2) return;
            this.editForm.choices.splice(i, 1);
        },
        setEditCorrect(i) {
            this.editForm.choices.forEach((c, idx) => c.is_correct = idx === i);
        },
        async saveEdit() {
            if (!this.isEditFormValid) {
                showToast('warning', 'Perhatian', 'Lengkapi semua field. Pilih 1 jawaban benar!');
                return;
            }
            this.isSavingEdit = true;
            try {
                const res = await fetch(`/guru/quiz/{{ $quiz->id }}/questions/${this.editQuestionId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        type: 'PG',
                        question: this.editForm.question.trim(),
                        score: parseInt(this.editForm.score),
                        explanation: this.editForm.explanation.trim() || null,
                        choices: this.editForm.choices.map(c => ({ text: c.text.trim(), is_correct: c.is_correct }))
                    })
                });
                const data = await res.json();
                if (data.success) {
                    this.questions[this.editIndex] = {
                        ...data.question,
                        choices: data.question.choices || this.editForm.choices
                    };
                    this.questions = [...this.questions]; // trigger reactivity
                    this.showEditModal = false;
                    showToast('success', 'Berhasil!', 'Soal berhasil diperbarui.');
                } else {
                    showToast('error', 'Gagal', data.message || 'Terjadi kesalahan');
                }
            } catch(e) {
                showToast('error', 'Error', 'Gagal menghubungi server');
            } finally {
                this.isSavingEdit = false;
            }
        },

        // ===== DELETE =====
        openDeleteModal(idx) {
            const q = this.questions[idx];
            this.deleteIndex = idx;
            this.deleteQuestionId = q.id;
            this.deleteQuestionText = q.question.length > 100 ? q.question.substring(0, 100) + '...' : q.question;
            this.showDeleteModal = true;
        },
        async confirmDelete() {
            this.isDeleting = true;
            try {
                const res = await fetch(`/guru/quiz/{{ $quiz->id }}/questions/${this.deleteQuestionId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    this.questions.splice(this.deleteIndex, 1);
                    this.showDeleteModal = false;
                    showToast('success', 'Dihapus!', 'Soal berhasil dihapus.');
                } else {
                    showToast('error', 'Gagal', data.message || 'Terjadi kesalahan');
                }
            } catch(e) {
                showToast('error', 'Error', 'Gagal menghubungi server');
            } finally {
                this.isDeleting = false;
            }
        },

        // ===== IMPORT: FROM EXAM =====
        async loadImportPreview() {
            if (!this.importSourceQuizId) { this.importPreview = []; return; }
            this.isLoadingPreview = true;
            try {
                const res = await fetch(`/guru/quiz/${this.importSourceQuizId}/questions/list`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await res.json();
                if (data.success) {
                    this.importPreview = (data.questions || [])
                        .filter(q => q.type === 'PG')
                        .map(q => ({ ...q, selected: true }));
                } else {
                    this.importPreview = [];
                }
            } catch(e) {
                this.importPreview = [];
                showToast('error', 'Error', 'Gagal memuat soal');
            } finally {
                this.isLoadingPreview = false;
            }
        },

        // ===== IMPORT: FROM FILE =====
        handleFileDrop(e) {
            this.isDragging = false;
            if (e.dataTransfer.files.length > 0) this.uploadFile(e.dataTransfer.files[0]);
        },
        handleFileSelect(e) {
            if (e.target.files.length > 0) this.uploadFile(e.target.files[0]);
        },
        async uploadFile(file) {
            const ext = '.' + file.name.split('.').pop().toLowerCase();
            if (!['.xlsx','.xls','.csv'].includes(ext)) {
                showToast('error', 'Format Salah', 'Gunakan file .xlsx, .xls, atau .csv'); return;
            }
            if (file.size > 5 * 1024 * 1024) {
                showToast('error', 'File Terlalu Besar', 'Maksimal 5MB'); return;
            }
            this.selectedFileName = file.name;
            this.isUploadingFile = true;
            this.fileImportPreview = [];

            const formData = new FormData();
            formData.append('file', file);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const res = await fetch('{{ route('guru.quiz.questions.import', $quiz->id) }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    this.fileImportPreview = (data.questions || []).filter(q => q.type === 'PG');
                    showToast('success', 'File Dibaca!', `Ditemukan ${this.fileImportPreview.length} soal PG.`);
                } else {
                    showToast('error', 'Gagal', data.message || 'Gagal memproses file');
                }
            } catch(e) {
                showToast('error', 'Error', 'Gagal mengupload file');
            } finally {
                this.isUploadingFile = false;
            }
        },

        // ===== EXECUTE IMPORT =====
        async executeImport() {
            let questionsToImport = [];
            if (this.importTab === 'from_exam') {
                questionsToImport = this.importPreview.filter(q => q.selected).map(q => ({
                    type: 'PG', question: q.question, score: q.score,
                    explanation: q.explanation || '', choices: q.choices || []
                }));
            } else {
                questionsToImport = this.fileImportPreview;
            }

            if (questionsToImport.length === 0) {
                showToast('warning', 'Perhatian', 'Pilih minimal 1 soal untuk diimport.'); return;
            }
            if (this.questions.length + questionsToImport.length > 50) {
                showToast('error', 'Melebihi Batas', `Setelah import akan ada ${this.questions.length + questionsToImport.length} soal (maks 50).`); return;
            }

            this.isImporting = true;
            try {
                // Send to server as bulk
                const res = await fetch('{{ route('guru.quiz.questions.store', $quiz->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ questions: questionsToImport, append: true })
                });
                const data = await res.json();
                if (data.success) {
                    // Reload questions from server
                    await this.reloadQuestions();
                    showToast('success', 'Import Berhasil!', `Berhasil mengimport ${questionsToImport.length} soal.`);
                    this.showImportModal = false;
                    this.importPreview = [];
                    this.fileImportPreview = [];
                    this.selectedFileName = '';
                    this.importSourceQuizId = '';
                } else {
                    showToast('error', 'Gagal', data.message || 'Gagal mengimport soal');
                }
            } catch(e) {
                showToast('error', 'Error', 'Gagal menghubungi server');
            } finally {
                this.isImporting = false;
            }
        },

        async reloadQuestions() {
            try {
                const res = await fetch(`/guru/quiz/{{ $quiz->id }}/questions/list`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                const data = await res.json();
                if (data.success) {
                    this.questions = (data.questions || []).filter(q => q.type === 'PG');
                }
            } catch(e) {}
        },

        // ===== SAVE ALL =====
        async saveAllQuestions() {
            if (this.questions.length === 0) {
                showToast('warning', 'Perhatian', 'Belum ada soal!'); return;
            }
            this.isSaving = true;
            showToast('info', 'Menyimpan...', 'Sedang menyimpan soal ke server...');
            try {
                const formatted = this.questions.filter(q => q.type === 'PG').map(q => ({
                    question: q.question,
                    type: 'PG',
                    score: parseInt(q.score),
                    explanation: q.explanation || null,
                    choices: (q.choices || []).map(c => ({ text: c.text, is_correct: c.is_correct }))
                }));

                const res = await fetch('{{ route('guru.quiz.questions.store', $quiz->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ questions: formatted })
                });
                const data = await res.json();
                if (data.success) {
                    if (data.questions) {
                        this.questions = data.questions.filter(q => q.type === 'PG');
                    }
                    showToast('success', 'Tersimpan!', `${formatted.length} soal berhasil disimpan.`);
                } else {
                    showToast('error', 'Gagal', data.message || 'Gagal menyimpan soal');
                }
            } catch(e) {
                showToast('error', 'Error', 'Terjadi kesalahan: ' + e.message);
            } finally {
                this.isSaving = false;
            }
        },

        // ===== PREVIEW & FINALIZE =====
        async previewQuiz() {
            if (this.questions.length === 0) {
                showToast('warning', 'Perhatian', 'Tambah minimal 1 soal dulu!'); return;
            }
            await this.saveAllQuestions();
            window.location.href = '{{ route('guru.quiz.preview', $quiz->id) }}';
        },

        async finalizeQuiz() {
            if (this.questions.length === 0) {
                showToast('warning', 'Perhatian', 'Minimal harus ada 1 soal!'); return;
            }
            if (!confirm('Publikasikan quiz ini? Quiz akan aktif dan dapat diakses siswa.')) return;
            this.isFinalizing = true;
            try {
                await this.saveAllQuestions();
                const res = await fetch('{{ route('guru.quiz.finalize', $quiz->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ action: 'publish' })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('success', 'Dipublikasikan!', data.message);
                    setTimeout(() => {
                        window.location.href = data.redirect || '{{ route('guru.quiz.index') }}';
                    }, 1500);
                } else {
                    showToast('error', 'Gagal', data.message || 'Gagal mempublish quiz');
                }
            } catch(e) {
                showToast('error', 'Error', 'Terjadi kesalahan');
            } finally {
                this.isFinalizing = false;
            }
        }
    }
}

// ===== TOAST HELPER =====
function showToast(type, title, msg, duration = 4000) {
    const icons = {
        success: '<i class="fas fa-check-circle" style="color:#10B981"></i>',
        error:   '<i class="fas fa-times-circle" style="color:#EF4444"></i>',
        warning: '<i class="fas fa-exclamation-triangle" style="color:#F59E0B"></i>',
        info:    '<i class="fas fa-info-circle" style="color:#3B82F6"></i>'
    };
    const el = document.createElement('div');
    el.className = `toast ${type}`;
    el.innerHTML = `
        <div style="font-size:1.1rem;flex-shrink:0">${icons[type] || icons.info}</div>
        <div style="flex:1;min-width:0">
            <div style="font-size:0.82rem;font-weight:700;color:#111827;margin-bottom:2px">${title}</div>
            <div style="font-size:0.78rem;color:#6B7280;word-break:break-word">${msg}</div>
        </div>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:0.875rem;flex-shrink:0;padding:0"><i class="fas fa-times"></i></button>
    `;
    const container = document.getElementById('toast-container');
    container.appendChild(el);
    setTimeout(() => {
        el.style.transition = 'all 0.3s ease';
        el.style.opacity = '0';
        el.style.transform = 'translateX(60px)';
        setTimeout(() => el.remove(), 300);
    }, duration);
}
</script>
@endsection
