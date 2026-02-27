@extends('layouts.appTeacher')

@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .question-type-card {
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .question-type-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .question-type-card.selected {
            border-color: #3b82f6;
            background-color: #f8fafc;
        }

        .drag-over {
            border-color: #3b82f6 !important;
            background-color: #f1f5f9 !important;
        }

        .option-row {
            animation: slideIn 0.2s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-6px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .scale-option-btn.selected {
            background: #3b82f6;
            color: white;
        }

        textarea.auto-resize {
            min-height: 80px;
            resize: vertical;
        }

        .badge-type {
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 999px;
            font-weight: 600;
            letter-spacing: 0.3px;
            background: #e2e8f0;
            color: #334155;
        }

        .question-card:hover .question-actions {
            opacity: 1;
        }

        .question-actions {
            opacity: 0;
            transition: opacity 0.2s;
        }

        button,
        .btn {
            transition: background-color 0.15s, border-color 0.15s;
        }
    </style>

    <div class="max-w-5xl mx-auto space-y-6" id="soal-manager">

        <!-- Loading Overlay -->
        <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white p-6 rounded-lg shadow-lg flex flex-col items-center">
                <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mb-4"></div>
                <p id="loading-message" class="text-slate-700 font-medium">Menyimpan...</p>
            </div>
        </div>

        <!-- Step Indicator (sederhana) -->
        <div class="flex items-center justify-center space-x-4 mb-8 text-sm">
            <div class="flex items-center text-slate-500">
                <span
                    class="w-7 h-7 flex items-center justify-center rounded-full bg-slate-200 text-slate-700 font-medium">1</span>
                <span class="ml-2">Pengaturan Ujian</span>
            </div>
            <div class="w-12 h-px bg-slate-300"></div>
            <div class="flex items-center text-blue-600">
                <span
                    class="w-7 h-7 flex items-center justify-center rounded-full bg-blue-600 text-white font-medium">2</span>
                <span class="ml-2 font-medium">Buat Soal</span>
            </div>
        </div>

        <!-- Header & Stats -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">{{ $exam->title }}</h2>
                    <p class="text-slate-500 text-sm mt-0.5">
                        {{ $exam->class?->name_class ?? 'Kelas' }} •
                        <span id="total-questions">{{ $exam->questions->count() }}</span> Soal •
                        Total Skor: <span id="total-score">{{ $exam->questions->sum('score') }}</span>
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Download Template -->
                    <a href="/guru/exams/{{ $exam->id }}/import-template"
                        class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition-colors text-sm">
                        Template
                    </a>
                    <!-- Import -->
                    <button id="import-soal-btn"
                        class="px-3 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-medium rounded-lg transition-colors text-sm">
                        Import Excel/CSV
                    </button>
                    <!-- Auto Score -->
                    <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 p-1 rounded-lg">
                        <label class="text-xs font-medium text-slate-600 ml-1.5">Nilai:</label>
                        <input type="number" id="total-nilai-otomatis" value="100" min="0"
                            class="w-16 px-2 py-1 border border-slate-300 rounded text-sm focus:ring-1 focus:ring-blue-400 outline-none">
                        <button id="apply-auto-score"
                            class="px-2.5 py-1 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded text-xs transition-colors">
                            Rata
                        </button>
                    </div>
                    <!-- Tambah Soal -->
                    <button id="tambah-soal-btn"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-sm text-sm">
                        Tambah Soal
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- FORM SOAL -->
        <!-- ============================================================ -->
        <div id="form-soal" class="bg-white rounded-xl border-2 border-blue-500 shadow-xl overflow-hidden mb-8 hidden">
            <div class="p-4 bg-slate-100 border-b border-slate-200 flex justify-between items-center">
                <h3 id="form-title" class="font-semibold text-slate-800">
                    Tambah Butir Soal
                </h3>
                <button id="close-form-btn" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="question-form" class="p-6 space-y-6">
                @csrf
                <input type="hidden" id="editing-question-id" value="">

                <!-- STEP 1: Pilih Jenis Soal (tanpa ikon, hanya teks) -->
                <div>
                    <label class="text-sm font-medium text-slate-700">Pilih Jenis Soal</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2 mt-3" id="question-type-grid">

                        <div class="question-type-card selected border-2 border-blue-500 bg-slate-50 rounded-lg p-3 text-center"
                            data-type="PG">
                            <div class="text-xs font-bold text-slate-800">PG</div>
                            <div class="text-xs text-slate-500">Pilihan Ganda</div>
                        </div>

                        <div class="question-type-card border-2 border-slate-200 rounded-lg p-3 text-center"
                            data-type="PGK">
                            <div class="text-xs font-bold text-slate-800">PGK</div>
                            <div class="text-xs text-slate-500">PG Kompleks</div>
                        </div>

                        <div class="question-type-card border-2 border-slate-200 rounded-lg p-3 text-center" data-type="BS">
                            <div class="text-xs font-bold text-slate-800">B/S</div>
                            <div class="text-xs text-slate-500">Benar / Salah</div>
                        </div>

                        <div class="question-type-card border-2 border-slate-200 rounded-lg p-3 text-center" data-type="DD">
                            <div class="text-xs font-bold text-slate-800">DD</div>
                            <div class="text-xs text-slate-500">Dropdown</div>
                        </div>

                        <div class="question-type-card border-2 border-slate-200 rounded-lg p-3 text-center" data-type="IS">
                            <div class="text-xs font-bold text-slate-800">IS</div>
                            <div class="text-xs text-slate-500">Isian Singkat</div>
                        </div>

                        <div class="question-type-card border-2 border-slate-200 rounded-lg p-3 text-center" data-type="ES">
                            <div class="text-xs font-bold text-slate-800">ES</div>
                            <div class="text-xs text-slate-500">Esai</div>
                        </div>

                        <div class="question-type-card border-2 border-slate-200 rounded-lg p-3 text-center" data-type="SK">
                            <div class="text-xs font-bold text-slate-800">SK</div>
                            <div class="text-xs text-slate-500">Skala Linear</div>
                        </div>

                        <div class="question-type-card border-2 border-slate-200 rounded-lg p-3 text-center" data-type="MJ">
                            <div class="text-xs font-bold text-slate-800">MJ</div>
                            <div class="text-xs text-slate-500">Menjodohkan</div>
                        </div>
                    </div>
                    <input type="hidden" id="question-type" value="PG">
                </div>

                <!-- STEP 2: Skor + Pertanyaan -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="md:col-span-3 space-y-1">
                        <label class="text-sm font-medium text-slate-700">Pertanyaan <span
                                class="text-red-400">*</span></label>
                        <textarea id="question-text" rows="3" placeholder="Tuliskan pertanyaan di sini..."
                            class="auto-resize w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-500 outline-none text-sm"></textarea>
                    </div>
                    <div class="space-y-2">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Skor</label>
                            <input type="number" id="question-score" value="10" min="0" max="100"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-500 outline-none text-sm">
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="no-score" class="w-4 h-4 text-blue-600 rounded border-slate-300">
                            <span class="text-xs text-slate-600">Tanpa nilai (manual)</span>
                        </label>
                        <div id="explanation-toggle-wrap" class="hidden">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="show-explanation-check"
                                    class="w-4 h-4 text-blue-600 rounded border-slate-300">
                                <span class="text-xs text-slate-600">Tampilkan pembahasan</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Pembahasan (optional) -->
                <div id="explanation-section" class="hidden space-y-1">
                    <label class="text-sm font-medium text-slate-700">Pembahasan / Kunci Jawaban</label>
                    <textarea id="question-explanation" rows="2" placeholder="Opsional: penjelasan jawaban untuk siswa..."
                        class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-1 focus:ring-blue-400 outline-none text-sm"></textarea>
                </div>

                <!-- ========== PG: Pilihan Ganda ========== -->
                <div id="section-PG" class="space-y-3 pt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Opsi Jawaban</p>
                        <button type="button" id="add-option-btn"
                            class="text-xs text-blue-600 hover:underline font-medium">+ Tambah Opsi</button>
                    </div>
                    <div id="pg-options-list" class="space-y-2"></div>
                    <p class="text-xs text-slate-400">Pilih satu jawaban benar</p>
                </div>

                <!-- ========== PGK: PG Kompleks ========== -->
                <div id="section-PGK" class="space-y-3 pt-4 border-t border-slate-100 hidden">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Opsi Jawaban</p>
                        <button type="button" id="add-option-pgk-btn"
                            class="text-xs text-blue-600 hover:underline font-medium">+ Tambah Opsi</button>
                    </div>
                    <div id="pgk-options-list" class="space-y-2"></div>
                    <p class="text-xs text-slate-400">Centang semua jawaban yang benar</p>
                </div>

                <!-- ========== BS: Benar / Salah ========== -->
                <div id="section-BS" class="pt-4 border-t border-slate-100 hidden">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-3">Jawaban yang Benar</p>
                    <div class="flex gap-4">
                        <label
                            class="flex items-center gap-2 cursor-pointer flex-1 border border-slate-200 rounded-lg p-3 hover:border-blue-300 transition-colors has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="bs-correct" value="benar" class="w-4 h-4 text-blue-600">
                            <span class="text-sm text-slate-700">Benar</span>
                        </label>
                        <label
                            class="flex items-center gap-2 cursor-pointer flex-1 border border-slate-200 rounded-lg p-3 hover:border-blue-300 transition-colors has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="bs-correct" value="salah" class="w-4 h-4 text-blue-600">
                            <span class="text-sm text-slate-700">Salah</span>
                        </label>
                    </div>
                </div>

                <!-- ========== DD: Dropdown ========== -->
                <div id="section-DD" class="space-y-3 pt-4 border-t border-slate-100 hidden">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Opsi Dropdown</p>
                        <button type="button" id="add-option-dd-btn"
                            class="text-xs text-blue-600 hover:underline font-medium">+ Tambah Opsi</button>
                    </div>
                    <div id="dd-options-list" class="space-y-2"></div>
                    <p class="text-xs text-slate-400">Pilih satu jawaban benar</p>
                </div>

                <!-- ========== IS: Isian Singkat ========== -->
                <div id="section-IS" class="pt-4 border-t border-slate-100 hidden">
                    <label class="text-sm font-medium text-slate-700">
                        Jawaban yang Diterima
                        <span class="text-xs font-normal text-slate-400">(pisahkan dengan koma)</span>
                    </label>
                    <input type="text" id="short-answer" placeholder="Contoh: Indonesia, jakarta"
                        class="mt-1 w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-500 outline-none text-sm">
                    <div class="mt-2 flex items-center gap-2">
                        <input type="checkbox" id="case-sensitive"
                            class="w-4 h-4 text-blue-600 rounded border-slate-300">
                        <label for="case-sensitive" class="text-xs text-slate-600">Peka huruf besar/kecil</label>
                    </div>
                </div>

                <!-- ========== ES: Esai ========== -->
                <div id="section-ES" class="pt-4 border-t border-slate-100 hidden">
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4">
                        <p class="text-sm font-medium text-slate-700">Soal Esai — Penilaian Manual</p>
                        <p class="text-xs text-slate-500 mt-1">Jawaban siswa harus dinilai manual. Skor otomatis 0 sampai
                            diperiksa.</p>
                    </div>
                    <div class="mt-3 space-y-1">
                        <label class="text-sm font-medium text-slate-700">Panduan Penilaian (Opsional)</label>
                        <textarea id="essay-rubric" rows="3" placeholder="Contoh: Jawaban harus mencakup definisi, contoh, dampak..."
                            class="w-full px-4 py-2 rounded-lg border border-slate-200 focus:ring-1 focus:ring-blue-400 outline-none text-sm"></textarea>
                    </div>
                </div>

                <!-- ========== SK: Skala Linear ========== -->
                <div id="section-SK" class="pt-4 border-t border-slate-100 hidden">
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-3">Konfigurasi Skala</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-slate-600">Nilai Minimum</label>
                            <select id="scale-min"
                                class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-500 outline-none text-sm">
                                <option value="0">0</option>
                                <option value="1" selected>1</option>
                            </select>
                            <input type="text" id="scale-min-label" placeholder="Label min"
                                class="mt-2 w-full px-3 py-1.5 rounded border border-slate-200 text-xs outline-none focus:ring-1 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-600">Nilai Maksimum</label>
                            <select id="scale-max"
                                class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-1 focus:ring-blue-500 outline-none text-sm">
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5" selected>5</option>
                                <option value="7">7</option>
                                <option value="10">10</option>
                            </select>
                            <input type="text" id="scale-max-label" placeholder="Label max"
                                class="mt-2 w-full px-3 py-1.5 rounded border border-slate-200 text-xs outline-none focus:ring-1 focus:ring-blue-400">
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-xs text-slate-500 mb-2">Preview skala:</p>
                        <div id="scale-preview" class="flex gap-2 flex-wrap"></div>
                    </div>
                    <div class="mt-3">
                        <label class="text-xs font-medium text-slate-600">Jawaban Benar (nilai)</label>
                        <input type="number" id="scale-correct" min="0" max="10" placeholder="Opsional"
                            class="mt-1 w-24 px-3 py-1.5 rounded border border-slate-300 text-sm outline-none focus:ring-1 focus:ring-blue-400">
                    </div>
                </div>

                <!-- ========== MJ: Menjodohkan ========== -->
                <div id="section-MJ" class="pt-4 border-t border-slate-100 hidden">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Pasangan Kiri ↔ Kanan</p>
                        <button type="button" id="add-match-btn"
                            class="text-xs text-blue-600 hover:underline font-medium">+ Tambah Pasangan</button>
                    </div>
                    <div class="grid grid-cols-5 gap-2 mb-2 text-xs font-medium text-slate-500">
                        <div class="col-span-2 text-center">Kolom A</div>
                        <div class="col-span-1"></div>
                        <div class="col-span-2 text-center">Kolom B</div>
                    </div>
                    <div id="match-pairs-list" class="space-y-2"></div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-slate-200">
                    <button type="button" id="cancel-question-btn"
                        class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition-colors text-sm">
                        Batal
                    </button>
                    <button type="submit" id="save-question-btn"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-sm text-sm">
                        Simpan Soal
                    </button>
                </div>
            </form>
        </div>

        <!-- ============================================================ -->
        <!-- QUESTIONS LIST -->
        <!-- ============================================================ -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold text-slate-800">Daftar Soal</h3>
                <div class="flex gap-2 text-xs">
                    <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded-full font-medium" id="count-PG">0
                        PG</span>
                    <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded-full font-medium" id="count-IS">0
                        Isian</span>
                    <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded-full font-medium" id="count-ES">0
                        Esai</span>
                    <span class="bg-slate-100 text-slate-700 px-2 py-1 rounded-full font-medium" id="count-other">0
                        Lainnya</span>
                </div>
            </div>
            <div id="questions-list" class="space-y-3">
                @if ($exam->questions->count() > 0)
                    @foreach ($exam->questions->sortBy('order') as $index => $question)
                        @php
                            $typeLabels = [
                                'PG' => 'Pilihan Ganda',
                                'PGK' => 'PG Kompleks',
                                'BS' => 'Benar/Salah',
                                'DD' => 'Dropdown',
                                'IS' => 'Isian Singkat',
                                'ES' => 'Esai',
                                'SK' => 'Skala',
                                'MJ' => 'Menjodohkan',
                            ];
                            $label = $typeLabels[$question->type] ?? $question->type;
                        @endphp
                        <div
                            class="question-card group relative p-4 border border-slate-200 rounded-xl hover:border-slate-300 transition-all">
                            <div class="flex justify-between items-start gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span
                                            class="w-6 h-6 flex items-center justify-center bg-slate-100 text-slate-600 font-medium text-xs rounded-full">
                                            {{ $index + 1 }}
                                        </span>
                                        <span class="badge-type">{{ $label }}</span>
                                        @if ($question->score == 0)
                                            <span class="badge-type bg-slate-200 text-slate-600">Manual</span>
                                        @else
                                            <span class="text-xs text-slate-500">{{ $question->score }} poin</span>
                                        @endif
                                    </div>
                                    <p class="text-slate-800 text-sm leading-relaxed">
                                        {{ $question->question }}
                                    </p>

                                    {{-- Show choices/answer based on type --}}
                                    @if (in_array($question->type, ['PG', 'PGK', 'DD']) && $question->choices->count())
                                        <div class="mt-2 pl-2 space-y-0.5">
                                            @foreach ($question->choices->sortBy('order') as $choice)
                                                <div
                                                    class="text-xs {{ $choice->is_correct ? 'text-blue-600 font-medium' : 'text-slate-500' }}">
                                                    {{ $choice->label }}. {{ $choice->text }}
                                                    @if ($choice->is_correct)
                                                        ✓
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($question->type === 'BS')
                                        @php
                                            $bsAnswers = is_array($question->short_answers)
                                                ? $question->short_answers
                                                : json_decode($question->short_answers ?? '[]', true);
                                            $bsCorrect = $bsAnswers[0] ?? '';
                                        @endphp
                                        <div
                                            class="mt-2 text-xs font-medium {{ $bsCorrect === 'benar' ? 'text-blue-600' : 'text-blue-600' }}">
                                            Jawaban: {{ ucfirst($bsCorrect) }}
                                        </div>
                                    @elseif($question->type === 'IS')
                                        @php
                                            $ans = is_array($question->short_answers)
                                                ? $question->short_answers
                                                : json_decode($question->short_answers ?? '[]', true);
                                            // Extract answers list
                                            if (isset($ans['answers']) && is_array($ans['answers'])) {
                                                $answerList = $ans['answers'];
                                            } else {
                                                $answerList = (array) $ans; // fallback to simple array
                                            }
                                        @endphp
                                        <div class="mt-2 text-xs text-blue-600 font-medium">
                                            Jawaban: {{ implode(', ', $answerList) }}
                                        </div>
                                    @elseif($question->type === 'ES')
                                        <div class="mt-2 text-xs text-slate-500">Penilaian manual</div>
                                    @elseif($question->type === 'SK')
                                        @php
                                            $skData = is_array($question->short_answers)
                                                ? $question->short_answers
                                                : json_decode($question->short_answers ?? '{}', true);
                                        @endphp
                                        <div class="mt-2 text-xs text-slate-500">
                                            Skala {{ $skData['min'] ?? 1 }} – {{ $skData['max'] ?? 5 }}
                                        </div>
                                    @elseif($question->type === 'MJ')
                                        @php
                                            $pairs = is_array($question->short_answers)
                                                ? $question->short_answers
                                                : json_decode($question->short_answers ?? '[]', true);
                                        @endphp
                                        <div class="mt-2 space-y-0.5">
                                            @foreach ((array) $pairs as $pair)
                                                <div class="text-xs text-slate-500">
                                                    <span
                                                        class="font-medium text-slate-700">{{ $pair['left'] ?? '' }}</span>
                                                    → {{ $pair['right'] ?? '' }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="question-actions flex gap-1 flex-shrink-0">
                                    <button onclick="editQuestion({{ $question->id }})"
                                        class="p-1.5 text-slate-400 hover:text-blue-600 rounded transition-colors"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button onclick="deleteQuestion({{ $question->id }})"
                                        class="p-1.5 text-slate-400 hover:text-red-500 rounded transition-colors"
                                        title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-16 text-slate-400">
                        <p class="text-base font-medium">Belum ada soal</p>
                        <p class="text-sm mt-1">Klik "Tambah Soal" atau import dari Excel/CSV</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Finalize Button -->
        <div class="flex justify-between items-center">
            <a href="/guru/exams/{{ $exam->id }}/edit"
                class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition-colors text-sm">
                ← Kembali ke Pengaturan
            </a>
            <button onclick="showConfirmationModal()"
                class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                Selesai & Simpan Ujian
            </button>
        </div>

        <!-- ============================================================ -->
        <!-- CONFIRMATION MODAL -->
        <!-- ============================================================ -->
        <div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
                <h3 class="text-lg font-semibold text-slate-800 mb-4">Konfirmasi Penyimpanan</h3>
                <div class="space-y-3 mb-6 bg-slate-50 rounded-lg p-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Total Soal</span>
                        <span class="font-medium" id="confirm-total-questions">0</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Total Skor</span>
                        <span class="font-medium" id="confirm-total-score">0</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Jenis Soal</span>
                        <span class="font-medium" id="confirm-question-types">-</span>
                    </div>
                </div>
                <p class="text-sm text-slate-500 mb-5">Ujian akan masuk status draft dan dapat diaktifkan kapan saja.</p>
                <div class="flex gap-3">
                    <button onclick="hideConfirmationModal()"
                        class="flex-1 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition-colors text-sm">
                        Batal
                    </button>
                    <button onclick="finalizeExam()"
                        class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors text-sm">
                        Ya, Simpan Ujian
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- IMPORT MODAL (sederhana) -->
        <!-- ============================================================ -->
        <div id="import-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-xl max-w-xl w-full mx-4 shadow-2xl overflow-hidden">
                <div class="p-5 bg-slate-100 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-slate-800">Import Soal</h3>
                    <button onclick="closeImportModal()" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    <!-- Format Info -->
                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-5 text-xs">
                        <p class="font-medium text-slate-700 mb-2">📋 Format Kolom File:</p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-200">
                                        <th class="px-2 py-1 text-left border border-slate-300">no</th>
                                        <th class="px-2 py-1 text-left border border-slate-300">pertanyaan</th>
                                        <th class="px-2 py-1 text-left border border-slate-300">tipe</th>
                                        <th class="px-2 py-1 text-left border border-slate-300">skor</th>
                                        <th class="px-2 py-1 text-left border border-slate-300">opsi_a…e</th>
                                        <th class="px-2 py-1 text-left border border-slate-300">jawaban</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="px-2 py-1 border border-slate-200">1</td>
                                        <td class="px-2 py-1 border border-slate-200">Ibu kota RI?</td>
                                        <td class="px-2 py-1 border border-slate-200">PG</td>
                                        <td class="px-2 py-1 border border-slate-200">10</td>
                                        <td class="px-2 py-1 border border-slate-200">Jakarta,Bandung,Bali,Medan</td>
                                        <td class="px-2 py-1 border border-slate-200">A</td>
                                    </tr>
                                    <tr class="bg-slate-50">
                                        <td class="px-2 py-1 border border-slate-200">2</td>
                                        <td class="px-2 py-1 border border-slate-200">Jelaskan fotosintesis</td>
                                        <td class="px-2 py-1 border border-slate-200">ES</td>
                                        <td class="px-2 py-1 border border-slate-200">20</td>
                                        <td class="px-2 py-1 border border-slate-200">-</td>
                                        <td class="px-2 py-1 border border-slate-200">-</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-2 text-slate-600">
                            <div><span class="font-medium">Tipe:</span> PG, PGK, BS, DD, IS, ES, SK, MJ</div>
                            <div><span class="font-medium">BS jawaban:</span> Benar/Salah</div>
                            <div><span class="font-medium">IS jawaban:</span> jawaban1,jawaban2</div>
                            <div><span class="font-medium">PGK jawaban:</span> A,C</div>
                        </div>
                    </div>

                    <!-- Drop Zone -->
                    <form id="import-form" enctype="multipart/form-data">
                        @csrf
                        <div id="drop-zone"
                            class="border-2 border-dashed border-slate-300 rounded-xl p-8 text-center cursor-pointer hover:border-blue-400 hover:bg-slate-50 transition-colors"
                            onclick="document.getElementById('import-file-input').click()">
                            <p class="font-medium text-slate-700">Klik atau drag & drop file di sini</p>
                            <p class="text-xs text-slate-400 mt-1">.xlsx, .xls, .csv — Maks 5MB</p>
                            <p id="file-chosen" class="mt-3 text-sm font-medium text-blue-600 hidden"></p>
                        </div>
                        <input type="file" id="import-file-input" name="file" accept=".xlsx,.xls,.csv"
                            class="hidden">

                        <!-- Options -->
                        <div class="mt-4 flex items-center gap-3">
                            <label class="flex items-center gap-2 cursor-pointer text-sm">
                                <input type="checkbox" id="import-replace" name="replace"
                                    class="w-4 h-4 text-blue-600 rounded border-slate-300">
                                <span class="text-slate-600">Ganti semua soal yang ada</span>
                            </label>
                        </div>

                        <div class="flex gap-3 mt-5">
                            <button type="button" onclick="closeImportModal()"
                                class="flex-1 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg text-sm transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm transition-colors shadow-sm">
                                Import Soal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notification Toast (tanpa ikon) -->
        <div id="notification"
            class="fixed top-4 right-4 bg-white border-l-4 p-4 rounded-lg shadow-lg hidden z-50 max-w-sm border-slate-400">
            <p id="notification-message" class="font-medium text-sm text-slate-700"></p>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- JAVASCRIPT (disesuaikan untuk ikon minimal) --}}
    {{-- ================================================================ --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const examId = {{ $exam->id }};
            let questions = @json($exam->questions->sortBy('order')->values());
            let isEditing = false;
            let currentType = 'PG';

            // ============ DOM refs ============
            const formSoal = document.getElementById('form-soal');
            const questionForm = document.getElementById('question-form');
            const tambahBtn = document.getElementById('tambah-soal-btn');
            const closeFormBtn = document.getElementById('close-form-btn');
            const cancelBtn = document.getElementById('cancel-question-btn');
            const noScoreCheck = document.getElementById('no-score');
            const scoreInput = document.getElementById('question-score');
            const showExpCheck = document.getElementById('show-explanation-check');
            const expSection = document.getElementById('explanation-section');
            const expToggleWrap = document.getElementById('explanation-toggle-wrap');

            // All section panels
            const allSections = ['PG', 'PGK', 'BS', 'DD', 'IS', 'ES', 'SK', 'MJ'];
            const validTypes = ['PG', 'PGK', 'BS', 'DD', 'IS', 'ES', 'SK', 'MJ'];
            // ============ TYPE SELECTOR ============
            document.querySelectorAll('.question-type-card').forEach(card => {
                card.addEventListener('click', () => {
                    document.querySelectorAll('.question-type-card').forEach(c => {
                        c.classList.remove('selected', 'border-blue-500', 'bg-slate-50');
                        c.classList.add('border-slate-200');
                    });
                    card.classList.add('selected', 'border-blue-500', 'bg-slate-50');
                    card.classList.remove('border-slate-200');
                    currentType = card.dataset.type;
                    document.getElementById('question-type').value = currentType;
                    showSection(currentType);
                });
            });

            function showSection(type) {
                allSections.forEach(t => {
                    const s = document.getElementById('section-' + t);
                    if (s) s.classList.add('hidden');
                });
                const target = document.getElementById('section-' + type);
                if (target) target.classList.remove('hidden');

                const hasAnswer = ['PG', 'PGK', 'BS', 'DD', 'IS'].includes(type);
                expToggleWrap.classList.toggle('hidden', !hasAnswer);
                if (!hasAnswer) {
                    showExpCheck.checked = false;
                    expSection.classList.add('hidden');
                }

                if (type === 'SK') updateScalePreview();
            }

            showExpCheck.addEventListener('change', () => {
                expSection.classList.toggle('hidden', !showExpCheck.checked);
            });

            noScoreCheck.addEventListener('change', () => {
                scoreInput.value = noScoreCheck.checked ? 0 : 10;
                scoreInput.disabled = noScoreCheck.checked;
            });

            // ============ OPTION BUILDERS (dengan ikon X minimal) ============
            function buildOptionRow(containerId, index, text = '', isCorrect = false, isRadio = true) {
                const container = document.getElementById(containerId);
                const inputName = isRadio ? 'correct_radio_' + containerId : 'correct_check_' + containerId;
                const row = document.createElement('div');
                row.className = 'option-row flex items-center gap-2';
                row.dataset.index = index;
                const label = String.fromCharCode(65 + index);
                row.innerHTML = `
                <input type="${isRadio ? 'radio' : 'checkbox'}" name="${inputName}" value="${index}"
                    ${isCorrect ? 'checked' : ''}
                    class="w-4 h-4 text-blue-600 flex-shrink-0 correct-selector border-slate-300">
                <span class="w-6 h-6 flex items-center justify-center bg-slate-100 text-slate-500 rounded text-xs font-medium flex-shrink-0">${label}</span>
                <input type="text" placeholder="Opsi ${label}..." value="${text}"
                    class="flex-1 px-3 py-1.5 rounded-lg border border-slate-200 outline-none focus:border-blue-400 text-sm option-text">
                <button type="button" onclick="this.parentElement.remove(); renumberOptions('${containerId}')"
                    class="text-slate-300 hover:text-slate-500 flex-shrink-0" title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>`;
                container.appendChild(row);
            }

            window.renumberOptions = function(containerId) {
                const container = document.getElementById(containerId);
                container.querySelectorAll('.option-row').forEach((row, i) => {
                    const lbl = String.fromCharCode(65 + i);
                    row.dataset.index = i;
                    const sel = row.querySelector('.correct-selector');
                    if (sel) sel.value = i;
                    const span = row.querySelector('span');
                    if (span) span.textContent = lbl;
                    const input = row.querySelector('.option-text');
                    if (input) input.placeholder = `Opsi ${lbl}...`;
                });
            };

            function initOptions(containerId, defaultCount = 4, isRadio = true) {
                const container = document.getElementById(containerId);
                container.innerHTML = '';
                for (let i = 0; i < defaultCount; i++) {
                    buildOptionRow(containerId, i, '', i === 0, isRadio);
                }
            }

            document.getElementById('add-option-btn').addEventListener('click', () => {
                const count = document.querySelectorAll('#pg-options-list .option-row').length;
                if (count >= 6) return showNotification('Maksimal 6 opsi', false);
                buildOptionRow('pg-options-list', count, '', false, true);
            });

            document.getElementById('add-option-pgk-btn').addEventListener('click', () => {
                const count = document.querySelectorAll('#pgk-options-list .option-row').length;
                if (count >= 6) return showNotification('Maksimal 6 opsi', false);
                buildOptionRow('pgk-options-list', count, '', false, false);
            });

            document.getElementById('add-option-dd-btn').addEventListener('click', () => {
                const count = document.querySelectorAll('#dd-options-list .option-row').length;
                if (count >= 10) return showNotification('Maksimal 10 opsi', false);
                buildOptionRow('dd-options-list', count, '', false, true);
            });

            document.getElementById('add-match-btn').addEventListener('click', () => addMatchPair());

            function addMatchPair(left = '', right = '') {
                const list = document.getElementById('match-pairs-list');
                const row = document.createElement('div');
                row.className = 'option-row grid grid-cols-5 gap-2 items-center';
                row.innerHTML = `
                <input type="text" placeholder="Kolom A..." value="${escapeHtml(left)}"
                    class="col-span-2 px-3 py-1.5 rounded-lg border border-slate-200 outline-none focus:border-blue-400 text-sm match-left">
                <div class="flex justify-center text-slate-300">→</div>
                <input type="text" placeholder="Kolom B..." value="${escapeHtml(right)}"
                    class="col-span-2 px-3 py-1.5 rounded-lg border border-slate-200 outline-none focus:border-blue-400 text-sm match-right">
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-300 hover:text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>`;
                list.appendChild(row);
            }

            ['scale-min', 'scale-max'].forEach(id => {
                document.getElementById(id).addEventListener('change', updateScalePreview);
            });

            function updateScalePreview() {
                const min = parseInt(document.getElementById('scale-min').value) || 1;
                const max = parseInt(document.getElementById('scale-max').value) || 5;
                const preview = document.getElementById('scale-preview');
                preview.innerHTML = '';
                for (let i = min; i <= max; i++) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className =
                        'scale-option-btn w-8 h-8 rounded-full border border-slate-300 text-xs font-medium text-slate-600 hover:border-blue-400 transition-colors';
                    btn.textContent = i;
                    preview.appendChild(btn);
                }
            }
            updateScalePreview();

            // ============ FORM OPEN/CLOSE ============
            function showForm() {
                isEditing = false;
                document.getElementById('editing-question-id').value = '';
                document.getElementById('form-title').textContent = 'Tambah Butir Soal Baru';
                questionForm.reset();
                noScoreCheck.checked = false;
                scoreInput.disabled = false;
                expSection.classList.add('hidden');
                showExpCheck.checked = false;

                document.querySelectorAll('.question-type-card').forEach(c => {
                    c.classList.remove('selected', 'border-blue-500', 'bg-slate-50');
                    c.classList.add('border-slate-200');
                });
                const pgCard = document.querySelector('[data-type="PG"]');
                if (pgCard) {
                    pgCard.classList.add('selected', 'border-blue-500', 'bg-slate-50');
                    pgCard.classList.remove('border-slate-200');
                }
                currentType = 'PG';
                document.getElementById('question-type').value = 'PG';
                initOptions('pg-options-list', 4, true);
                initOptions('pgk-options-list', 4, false);
                initOptions('dd-options-list', 4, true);
                document.getElementById('match-pairs-list').innerHTML = '';
                addMatchPair();
                addMatchPair();
                showSection('PG');
                formSoal.classList.remove('hidden');
                formSoal.scrollIntoView({
                    behavior: 'smooth'
                });
            }

            function hideForm() {
                formSoal.classList.add('hidden');
                questionForm.reset();
                isEditing = false;
            }

            tambahBtn.addEventListener('click', showForm);
            closeFormBtn.addEventListener('click', hideForm);
            cancelBtn.addEventListener('click', hideForm);

            // ============ COLLECT FORM DATA ============
            function collectFormData() {
                const type = document.getElementById('question-type').value;

                // Validasi tambahan
                if (!type || type === '') {
                    throw new Error('Tipe soal tidak boleh kosong');
                }

                const score = noScoreCheck.checked ? 0 : parseInt(scoreInput.value);
                const question = document.getElementById('question-text').value.trim();
                const explanation = document.getElementById('question-explanation').value.trim();

                const data = {
                    type,
                    score,
                    question
                };
                if (explanation) data.explanation = explanation;

                if (type === 'PG' || type === 'DD') {
                    const listId = type === 'PG' ? 'pg-options-list' : 'dd-options-list';
                    const rows = document.querySelectorAll(`#${listId} .option-row`);
                    const options = [];
                    let correctAnswer = -1;
                    rows.forEach((row, i) => {
                        const text = row.querySelector('.option-text')?.value.trim() || '';
                        const sel = row.querySelector('.correct-selector');
                        options.push(text);
                        if (sel?.checked) correctAnswer = i;
                    });
                    data.options = options;
                    data.correct_answer = correctAnswer;
                }

                if (type === 'PGK') {
                    const rows = document.querySelectorAll('#pgk-options-list .option-row');
                    const options = [];
                    const correct = [];
                    rows.forEach((row, i) => {
                        const text = row.querySelector('.option-text')?.value.trim() || '';
                        const sel = row.querySelector('.correct-selector');
                        options.push(text);
                        if (sel?.checked) correct.push(i);
                    });
                    data.options = options;
                    data.correct_answers = correct;
                }

                if (type === 'BS') {
                    const bsVal = document.querySelector('input[name="bs-correct"]:checked')?.value;
                    data.short_answer = bsVal || '';
                }

                if (type === 'IS') {
                    data.short_answer = document.getElementById('short-answer').value.trim();
                    data.case_sensitive = document.getElementById('case-sensitive').checked;
                }

                if (type === 'ES') {
                    data.rubric = document.getElementById('essay-rubric').value.trim();
                }

                if (type === 'SK') {
                    data.scale_min = parseInt(document.getElementById('scale-min').value);
                    data.scale_max = parseInt(document.getElementById('scale-max').value);
                    data.scale_min_label = document.getElementById('scale-min-label').value.trim();
                    data.scale_max_label = document.getElementById('scale-max-label').value.trim();
                    const scCorrect = document.getElementById('scale-correct').value;
                    data.scale_correct = scCorrect ? parseInt(scCorrect) : null;
                }

                if (type === 'MJ') {
                    const pairs = [];
                    document.querySelectorAll('#match-pairs-list .option-row').forEach(row => {
                        const left = row.querySelector('.match-left')?.value.trim();
                        const right = row.querySelector('.match-right')?.value.trim();
                        if (left || right) pairs.push({
                            left,
                            right
                        });
                    });
                    data.pairs = pairs;
                }

                return data;
            }

            // ============ VALIDATION ============
            function validateFormData(data) {
                if (!data.question) return 'Pertanyaan tidak boleh kosong';

                if (['PG', 'DD'].includes(data.type)) {
                    const filled = (data.options || []).filter(o => o !== '');
                    if (filled.length < 2) return 'Minimal 2 opsi jawaban harus diisi';
                    if (data.correct_answer < 0) return 'Pilih jawaban yang benar';
                }

                if (data.type === 'PGK') {
                    const filled = (data.options || []).filter(o => o !== '');
                    if (filled.length < 2) return 'Minimal 2 opsi jawaban harus diisi';
                    if (!data.correct_answers || data.correct_answers.length === 0)
                        return 'Pilih minimal 1 jawaban yang benar';
                }

                if (data.type === 'BS' && !data.short_answer) return 'Pilih jawaban Benar atau Salah';
                if (data.type === 'IS' && !data.short_answer) return 'Jawaban tidak boleh kosong';

                if (data.type === 'MJ') {
                    if (!data.pairs || data.pairs.length < 2) return 'Minimal 2 pasangan untuk soal menjodohkan';
                    if (data.pairs.some(p => !p.left || !p.right)) return 'Semua pasangan harus terisi';
                }

                if (data.type === 'SK' && data.scale_max <= data.scale_min)
                    return 'Nilai maksimum harus lebih besar dari minimum';

                return null;
            }

            // ============ SUBMIT FORM ============
            questionForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const data = collectFormData();
                const error = validateFormData(data);
                if (error) return showNotification(error, false);

                if (isEditing) {
                    await updateQuestion(data);
                } else {
                    await createQuestion(data);
                }
            });

            async function createQuestion(formData) {
                showLoading('Menyimpan soal...');
                try {
                    const resp = await fetch(`/guru/exams/${examId}/questions`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken(),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });
                    const data = await resp.json();
                    if (data.success) {
                        showNotification('Soal berhasil ditambahkan', true);
                        hideForm();
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showNotification(data.message || 'Terjadi kesalahan', false);
                    }
                } catch (err) {
                    showNotification('Gagal terhubung ke server', false);
                } finally {
                    hideLoading();
                }
            }

            async function updateQuestion(formData) {
                const questionId = document.getElementById('editing-question-id').value;
                showLoading('Menyimpan perubahan...');
                try {
                    const resp = await fetch(`/guru/exams/${examId}/questions/${questionId}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken(),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });
                    const data = await resp.json();
                    if (data.success) {
                        showNotification('Soal berhasil diperbarui', true);
                        hideForm();
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showNotification(data.message || 'Terjadi kesalahan', false);
                    }
                } catch (err) {
                    showNotification('Gagal terhubung ke server', false);
                } finally {
                    hideLoading();
                }
            }

            // ============ EDIT ============
            window.editQuestion = async function(questionId) {
                showLoading('Memuat data soal...');
                try {
                    const resp = await fetch(`/guru/exams/${examId}/questions/${questionId}`);
                    const data = await resp.json();

                    if (!data.success) {
                        showNotification('Gagal memuat data soal', false);
                        return;
                    }

                    const q = data.question;
                    isEditing = true;
                    document.getElementById('editing-question-id').value = questionId;
                    document.getElementById('form-title').textContent = 'Edit Soal';
                    document.getElementById('question-text').value = q.question;
                    document.getElementById('question-score').value = q.score;
                    document.getElementById('question-explanation').value = q.explanation || '';

                    noScoreCheck.checked = q.score == 0;
                    scoreInput.disabled = q.score == 0;

                    if (q.explanation) {
                        showExpCheck.checked = true;
                        expSection.classList.remove('hidden');
                    }

                    // === PERBAIKAN UTAMA: Set type dengan benar ===
                    currentType = q.type;
                    document.getElementById('question-type').value = q.type; // <-- PENTING!

                    // Update tampilan kartu type
                    document.querySelectorAll('.question-type-card').forEach(c => {
                        c.classList.remove('selected', 'border-blue-500', 'bg-slate-50');
                        c.classList.add('border-slate-200');
                    });
                    const typeCard = document.querySelector(`[data-type="${q.type}"]`);
                    if (typeCard) {
                        typeCard.classList.add('selected', 'border-blue-500', 'bg-slate-50');
                        typeCard.classList.remove('border-slate-200');
                    }

                    // Reset semua opsi
                    initOptions('pg-options-list', 4, true);
                    initOptions('pgk-options-list', 4, false);
                    initOptions('dd-options-list', 4, true);
                    document.getElementById('match-pairs-list').innerHTML = '';

                    // Isi data sesuai tipe
                    if (['PG', 'DD'].includes(q.type) && q.choices?.length) {
                        const listId = q.type === 'PG' ? 'pg-options-list' : 'dd-options-list';
                        document.getElementById(listId).innerHTML = '';
                        q.choices.forEach((ch, i) => {
                            buildOptionRow(listId, i, ch.text, ch.is_correct, true);
                        });
                    }

                    if (q.type === 'PGK' && q.choices?.length) {
                        document.getElementById('pgk-options-list').innerHTML = '';
                        q.choices.forEach((ch, i) => {
                            buildOptionRow('pgk-options-list', i, ch.text, ch.is_correct, false);
                        });
                    }

                    if (q.type === 'BS') {
                        const bsVal = Array.isArray(q.short_answers) ? q.short_answers[0] : q.short_answers;
                        const radio = document.querySelector(`input[name="bs-correct"][value="${bsVal}"]`);
                        if (radio) radio.checked = true;
                    }

                    if (q.type === 'IS') {
                        const answers = Array.isArray(q.short_answers) ?
                            (q.short_answers.answers || q.short_answers).join(', ') :
                            q.short_answers;
                        document.getElementById('short-answer').value = answers || '';
                        if (q.short_answers?.case_sensitive) {
                            document.getElementById('case-sensitive').checked = true;
                        }
                    }

                    if (q.type === 'ES') {
                        const rubric = q.short_answers?.rubric || '';
                        document.getElementById('essay-rubric').value = rubric;
                    }

                    if (q.type === 'SK' && q.short_answers) {
                        const sk = q.short_answers;
                        document.getElementById('scale-min').value = sk.min || 1;
                        document.getElementById('scale-max').value = sk.max || 5;
                        document.getElementById('scale-min-label').value = sk.min_label || '';
                        document.getElementById('scale-max-label').value = sk.max_label || '';
                        document.getElementById('scale-correct').value = sk.correct ?? '';
                        updateScalePreview();
                    }

                    if (q.type === 'MJ' && q.short_answers) {
                        const pairs = Array.isArray(q.short_answers) ? q.short_answers : [];
                        if (pairs.length === 0) {
                            addMatchPair();
                            addMatchPair();
                        } else {
                            pairs.forEach(p => addMatchPair(p.left, p.right));
                        }
                    }

                    showSection(q.type);
                    formSoal.classList.remove('hidden');
                    formSoal.scrollIntoView({
                        behavior: 'smooth'
                    });

                } catch (err) {
                    showNotification('Gagal memuat data soal', false);
                } finally {
                    hideLoading();
                }
            };

            // ============ DELETE ============
            window.deleteQuestion = function(questionId) {
                Swal.fire({
                    title: 'Hapus Soal?',
                    text: 'Soal yang dihapus tidak dapat dikembalikan',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then(res => {
                    if (res.isConfirmed) performDeleteQuestion(questionId);
                });
            };

            async function performDeleteQuestion(questionId) {
                showLoading('Menghapus soal...');
                try {
                    const resp = await fetch(`/guru/exams/${examId}/questions/${questionId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken(),
                            'Accept': 'application/json'
                        }
                    });
                    const data = await resp.json();
                    if (data.success) {
                        showNotification('Soal berhasil dihapus', true);
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showNotification(data.message || 'Gagal menghapus', false);
                    }
                } catch (err) {
                    showNotification('Gagal terhubung ke server', false);
                } finally {
                    hideLoading();
                }
            }

            // ============ IMPORT ============
            const importBtn = document.getElementById('import-soal-btn');
            const importModal = document.getElementById('import-modal');
            const importForm = document.getElementById('import-form');
            const fileInput = document.getElementById('import-file-input');
            const fileChosen = document.getElementById('file-chosen');
            const dropZone = document.getElementById('drop-zone');

            importBtn.addEventListener('click', () => importModal.classList.remove('hidden'));
            window.closeImportModal = () => importModal.classList.add('hidden');

            fileInput.addEventListener('change', () => {
                if (fileInput.files[0]) {
                    fileChosen.textContent = '✓ ' + fileInput.files[0].name;
                    fileChosen.classList.remove('hidden');
                }
            });

            dropZone.addEventListener('dragover', e => {
                e.preventDefault();
                dropZone.classList.add('drag-over');
            });
            dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
            dropZone.addEventListener('drop', e => {
                e.preventDefault();
                dropZone.classList.remove('drag-over');
                if (e.dataTransfer.files[0]) {
                    fileInput.files = e.dataTransfer.files;
                    fileChosen.textContent = '✓ ' + e.dataTransfer.files[0].name;
                    fileChosen.classList.remove('hidden');
                }
            });

            importForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!fileInput.files[0]) {
                    showNotification('Pilih file terlebih dahulu', false);
                    return;
                }
                const fd = new FormData(importForm);
                showLoading('Mengimpor soal...');
                try {
                    const resp = await fetch(`/guru/exams/${examId}/import-questions`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken()
                        },
                        body: fd
                    });
                    const data = await resp.json();
                    if (data.success) {
                        closeImportModal();
                        Swal.fire({
                            icon: 'success',
                            title: 'Import Berhasil!',
                            html: `<b>${data.imported}</b> soal berhasil diimpor.${data.skipped > 0 ? `<br><span class="text-orange-500">${data.skipped} soal dilewati</span>` : ''}`,
                            confirmButtonText: 'Lihat Soal'
                        }).then(() => location.reload());
                    } else {
                        showNotification(data.message || 'Gagal mengimpor', false);
                    }
                } catch (err) {
                    showNotification('Terjadi kesalahan saat import', false);
                } finally {
                    hideLoading();
                }
            });

            // ============ AUTO SCORE ============
            document.getElementById('apply-auto-score').addEventListener('click', () => {
                const totalNilai = parseFloat(document.getElementById('total-nilai-otomatis').value) || 0;
                if (totalNilai <= 0) return showNotification('Total nilai harus > 0', false);
                if (questions.length === 0) return showNotification('Belum ada soal', false);
                const perSoal = (totalNilai / questions.length).toFixed(2);
                Swal.fire({
                    title: 'Terapkan Nilai Rata?',
                    html: `${questions.length} soal × <b>${perSoal}</b> poin = ${totalNilai}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Terapkan',
                    cancelButtonText: 'Batal'
                }).then(res => {
                    if (res.isConfirmed) applyAutoScore(perSoal);
                });
            });

            async function applyAutoScore(perSoal) {
                showLoading('Memperbarui nilai...');
                try {
                    const resp = await fetch(`/guru/exams/${examId}/update-scores`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken(),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            nilai_per_soal: perSoal
                        })
                    });
                    const data = await resp.json();
                    if (data.success) {
                        showNotification('Nilai berhasil diperbarui', true);
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showNotification(data.message || 'Gagal', false);
                    }
                } catch (err) {
                    showNotification('Terjadi kesalahan', false);
                } finally {
                    hideLoading();
                }
            }

            // ============ FINALIZE ============
            window.showConfirmationModal = function() {
                if (questions.length === 0) {
                    showNotification('Tambahkan minimal 1 soal terlebih dahulu', false);
                    return;
                }
                document.getElementById('confirm-total-questions').textContent = questions.length;
                const totalScore = questions.reduce((t, q) => t + parseInt(q.score || 0), 0);
                document.getElementById('confirm-total-score').textContent = totalScore;

                const typeCounts = {};
                questions.forEach(q => typeCounts[q.type] = (typeCounts[q.type] || 0) + 1);
                const typeLabels = {
                    PG: 'PG',
                    PGK: 'PGK',
                    BS: 'B/S',
                    DD: 'DD',
                    IS: 'IS',
                    ES: 'ES',
                    SK: 'SK',
                    MJ: 'MJ'
                };
                const typeText = Object.entries(typeCounts).map(([t, n]) => `${n} ${typeLabels[t]||t}`).join(
                    ', ');
                document.getElementById('confirm-question-types').textContent = typeText;

                document.getElementById('confirm-modal').classList.remove('hidden');
            };

            window.hideConfirmationModal = () => document.getElementById('confirm-modal').classList.add('hidden');

            window.finalizeExam = async function() {
                hideConfirmationModal();
                showLoading('Menyimpan ujian...');
                try {
                    const resp = await fetch(`/guru/exams/${examId}/finalize`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken(),
                            'Accept': 'application/json'
                        }
                    });
                    const data = await resp.json();
                    if (data.success) {
                        Swal.fire({
                                icon: 'success',
                                title: 'Ujian Tersimpan!',
                                text: 'Ujian masuk status draft.',
                                confirmButtonText: 'OK'
                            })
                            .then(() => window.location.href = data.redirect || '/guru/exams');
                    } else {
                        showNotification(data.message || 'Terjadi kesalahan', false);
                    }
                } catch (err) {
                    showNotification('Terjadi kesalahan saat menyimpan', false);
                } finally {
                    hideLoading();
                }
            };

            // ============ COUNTER BADGES ============
            function updateCountBadges() {
                const counts = {
                    PG: 0,
                    IS: 0,
                    ES: 0,
                    other: 0
                };
                questions.forEach(q => {
                    if (q.type === 'PG' || q.type === 'PGK' || q.type === 'BS' || q.type === 'DD') counts
                        .PG++;
                    else if (q.type === 'IS') counts.IS++;
                    else if (q.type === 'ES') counts.ES++;
                    else counts.other++;
                });
                document.getElementById('count-PG').textContent = `${counts.PG} PG`;
                document.getElementById('count-IS').textContent = `${counts.IS} Isian`;
                document.getElementById('count-ES').textContent = `${counts.ES} Esai`;
                document.getElementById('count-other').textContent = `${counts.other} Lainnya`;
            }
            updateCountBadges();

            // ============ HELPERS ============
            function csrfToken() {
                return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            }

            function showLoading(msg = 'Memproses...') {
                document.getElementById('loading-message').textContent = msg;
                document.getElementById('loading-overlay').classList.remove('hidden');
            }

            function hideLoading() {
                document.getElementById('loading-overlay').classList.add('hidden');
            }

            function showNotification(message, isSuccess = true) {
                const el = document.getElementById('notification');
                const msg = document.getElementById('notification-message');
                msg.textContent = message;
                el.className =
                    `fixed top-4 right-4 bg-white border-l-4 p-4 rounded-lg shadow-lg z-50 max-w-sm ${isSuccess ? 'border-green-500' : 'border-red-500'}`;
                el.classList.remove('hidden');
                setTimeout(() => el.classList.add('hidden'), 3500);
            }

            function escapeHtml(text) {
                if (!text) return '';
                return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g,
                    '&quot;');
            }

            // ============ INIT ============
            initOptions('pg-options-list', 4, true);
            initOptions('pgk-options-list', 4, false);
            initOptions('dd-options-list', 4, true);
            addMatchPair();
            addMatchPair();
            showSection('PG');
        });
    </script>
@endsection
