{{-- resources/views/guru/exams/results/detail.blade.php --}}
@extends('layouts.appTeacher')

@section('title', 'Detail Hasil: ' . ($attempt->student->user->name ?? 'Siswa'))

@section('content')
<div class="min-h-screen bg-[#f4f6fb] py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ===== HEADER ===== --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('guru.exams.results.index', $exam->id) }}"
                    class="inline-flex items-center gap-1.5 text-indigo-600 text-sm font-semibold mb-3 hover:text-indigo-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Hasil Ujian
                </a>
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-indigo-700 font-bold text-lg">{{ strtoupper(substr($attempt->student->user->name ?? 'S', 0, 1)) }}</span>
                    </div>
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900">{{ $attempt->student->user->name ?? 'Siswa' }}</h1>
                        <p class="text-sm text-slate-400">
                            {{ $attempt->student->nis ?? '-' }} &bull;
                            <span class="text-slate-600 font-medium">{{ $exam->title }}</span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="flex gap-2 flex-wrap">
                <button onclick="regradeAttempt({{ $exam->id }}, {{ $attempt->id }})"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 text-white rounded-xl text-sm font-semibold hover:bg-amber-600 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Koreksi Ulang PG
                </button>
                <button onclick="resetAttempt({{ $exam->id }}, {{ $attempt->id }})"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-rose-300 text-rose-600 rounded-xl text-sm font-semibold hover:bg-rose-50 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Reset Attempt
                </button>
            </div>
        </div>

        {{-- ===== SCORE SUMMARY CARD ===== --}}
        @php
            $fs = $attempt->final_score ?? 0;
            $grade = $fs >= 85 ? 'A' : ($fs >= 75 ? 'B' : ($fs >= 65 ? 'C' : ($fs >= 55 ? 'D' : 'E')));
            $gradeConfig = [
                'A' => ['color' => 'emerald', 'label' => 'Sangat Baik'],
                'B' => ['color' => 'blue', 'label' => 'Baik'],
                'C' => ['color' => 'yellow', 'label' => 'Cukup'],
                'D' => ['color' => 'orange', 'label' => 'Kurang'],
                'E' => ['color' => 'red', 'label' => 'Sangat Kurang'],
            ];
            $gc = $gradeConfig[$grade];
        @endphp

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
            <div class="flex flex-col sm:flex-row items-center gap-6">
                {{-- Score Circle --}}
                <div class="flex-shrink-0 flex flex-col items-center">
                    <div class="relative w-28 h-28">
                        <svg class="w-28 h-28 -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="42" fill="none" stroke="#e2e8f0" stroke-width="10"/>
                            <circle cx="50" cy="50" r="42" fill="none"
                                stroke="{{ $gc['color'] === 'emerald' ? '#10b981' : ($gc['color'] === 'blue' ? '#3b82f6' : ($gc['color'] === 'yellow' ? '#eab308' : ($gc['color'] === 'orange' ? '#f97316' : '#ef4444'))) }}"
                                stroke-width="10"
                                stroke-linecap="round"
                                stroke-dasharray="{{ round($fs * 2.639) }}, 263.9"/>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-extrabold text-{{ $gc['color'] }}-600" id="displayScore">{{ number_format($fs, 1) }}</span>
                            <span class="text-xs text-slate-400">/ 100</span>
                        </div>
                    </div>
                    <div class="mt-2 text-center">
                        <span class="inline-block px-3 py-1 bg-{{ $gc['color'] }}-100 text-{{ $gc['color'] }}-800 rounded-full text-sm font-bold">
                            Grade {{ $grade }} — {{ $gc['label'] }}
                        </span>
                    </div>
                </div>

                {{-- Stats Grid --}}
                <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                        <p class="text-2xl font-extrabold text-emerald-700">{{ $correctAnswers }}</p>
                        <p class="text-xs text-emerald-600 font-medium mt-0.5">Benar</p>
                    </div>
                    <div class="text-center p-3 bg-rose-50 rounded-xl border border-rose-100">
                        <p class="text-2xl font-extrabold text-rose-600">{{ $incorrectAnswers }}</p>
                        <p class="text-xs text-rose-500 font-medium mt-0.5">Salah</p>
                    </div>
                    <div class="text-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <p class="text-2xl font-extrabold text-slate-700">{{ $answeredQuestions }}/{{ $totalQuestions }}</p>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Dijawab</p>
                    </div>
                    <div class="text-center p-3 bg-amber-50 rounded-xl border border-amber-100">
                        <p class="text-2xl font-extrabold text-amber-700 font-mono">{{ $timeFormatted }}</p>
                        <p class="text-xs text-amber-600 font-medium mt-0.5">Durasi</p>
                    </div>
                </div>
            </div>

            {{-- Progress bar benar/salah --}}
            <div class="mt-5 pt-5 border-t border-slate-100">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="text-xs text-slate-500">Akurasi Jawaban</span>
                    <span class="text-xs font-bold text-slate-700 ml-auto">
                        {{ $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100) : 0 }}%
                    </span>
                </div>
                <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden flex">
                    <div class="h-full bg-emerald-500 transition-all"
                        style="width: {{ $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100) : 0 }}%"></div>
                    <div class="h-full bg-rose-400 transition-all"
                        style="width: {{ $totalQuestions > 0 ? round(($incorrectAnswers / $totalQuestions) * 100) : 0 }}%"></div>
                </div>
                <div class="flex gap-4 mt-1.5">
                    <span class="flex items-center gap-1 text-xs text-emerald-600"><span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>Benar</span>
                    <span class="flex items-center gap-1 text-xs text-rose-500"><span class="w-2 h-2 rounded-full bg-rose-400 inline-block"></span>Salah</span>
                    <span class="flex items-center gap-1 text-xs text-slate-400"><span class="w-2 h-2 rounded-full bg-slate-200 inline-block"></span>Tidak dijawab</span>
                </div>
            </div>
        </div>

        {{-- Hitung jumlah untuk filter --}}
        @php
            $totalQuestions = $attempt->answers->count();
            $correctCount   = $attempt->answers->where('is_correct', true)->count();
            $wrongCount     = $totalQuestions - $correctCount;

            // Soal yang perlu koreksi manual (ES, IS, SK, dan jika ingin MJ)
            $manualCount = $attempt->answers->filter(function($ans) {
                $type = $ans->question->type ?? '';
                return in_array($type, ['ES', 'ESSAY', 'IS', 'SK']); // tambahkan 'MJ' jika perlu
            })->count();

            // Salah otomatis = semua salah dikurangi manual yang salah
            $autoWrongCount = $wrongCount - $attempt->answers->filter(function($ans) {
                return !$ans->is_correct && in_array($ans->question->type ?? '', ['ES', 'ESSAY', 'IS', 'SK']);
            })->count();
        @endphp

        {{-- ===== FILTER TABS ===== --}}
        <div class="flex gap-2 mb-4 flex-wrap">
            <button onclick="filterAnswers('all')" id="tab-all"
                class="tab-btn px-4 py-1.5 rounded-lg text-sm font-semibold bg-indigo-600 text-white transition">
                Semua ({{ $totalQuestions }})
            </button>
            <button onclick="filterAnswers('correct')" id="tab-correct"
                class="tab-btn px-4 py-1.5 rounded-lg text-sm font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition">
                ✓ Benar ({{ $correctCount }})
            </button>
            <button onclick="filterAnswers('wrong')" id="tab-wrong"
                class="tab-btn px-4 py-1.5 rounded-lg text-sm font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 transition">
                ✗ Salah (Otomatis) ({{ $autoWrongCount }})
            </button>
            <button onclick="filterAnswers('manual')" id="tab-manual"
                class="tab-btn px-4 py-1.5 rounded-lg text-sm font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200 transition">
                📝 Perlu Koreksi Manual ({{ $manualCount }})
            </button>
        </div>

        {{-- ===== JAWABAN DETAIL ===== --}}
        @php
            // Fungsi aman untuk menampilkan data (rekursif, tangani array bersarang)
            $safeStr = function($val, $fallback = '(tidak dijawab)') use (&$safeStr) {
                if (is_null($val)) return $fallback;

                if (is_array($val)) {
                    $isSimple = true;
                    foreach ($val as $item) {
                        if (is_array($item) || is_object($item)) {
                            $isSimple = false;
                            break;
                        }
                    }
                    if ($isSimple) {
                        return implode(', ', array_map('strval', $val));
                    } else {
                        return json_encode($val, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    }
                }

                $decoded = json_decode($val, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $safeStr($decoded, $fallback);
                }

                $str = (string) $val;
                return $str === '' ? $fallback : $str;
            };
        @endphp

        <div class="space-y-4" id="answersContainer">
            @forelse($attempt->answers as $index => $answer)
                @php
                    $q = $answer->question;
                    $isEssay = in_array($q->type ?? '', ['ES', 'ESSAY']);
                    $isManual = in_array($q->type ?? '', ['ES', 'ESSAY', 'IS', 'SK']); // tambahkan MJ jika perlu
                    $typeMap = ['PG'=>'Pilihan Ganda','PGK'=>'PG Kompleks','BS'=>'Benar/Salah','DD'=>'Dropdown','IS'=>'Isian Singkat','ES'=>'Esai','SK'=>'Skala Linear','MJ'=>'Menjodohkan'];
                    $typeBadge = ['PG'=>'bg-blue-100 text-blue-700','ES'=>'bg-rose-100 text-rose-700','PGK'=>'bg-indigo-100 text-indigo-700','IS'=>'bg-amber-100 text-amber-700','BS'=>'bg-purple-100 text-purple-700','SK'=>'bg-teal-100 text-teal-700','DD'=>'bg-cyan-100 text-cyan-700','MJ'=>'bg-orange-100 text-orange-700'];
                    $isCorrect = (bool)$answer->is_correct;
                @endphp
                <div class="answer-card bg-white rounded-2xl border shadow-sm overflow-hidden transition-all
                    {{ $isCorrect ? 'border-emerald-200' : 'border-slate-200' }}"
                    data-correct="{{ $isCorrect ? 'true' : 'false' }}"
                    data-type="{{ $isEssay ? 'essay' : 'objective' }}"
                    data-manual="{{ $isManual ? 'true' : 'false' }}"
                    id="answer-card-{{ $index }}">

                    {{-- Card Header --}}
                    <div class="flex items-center justify-between px-5 py-3.5 border-b
                        {{ $isCorrect ? 'bg-emerald-50 border-emerald-100' : 'bg-slate-50 border-slate-100' }}">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="w-7 h-7 rounded-full text-xs font-bold flex items-center justify-center text-white
                                {{ $isCorrect ? 'bg-emerald-500' : 'bg-slate-400' }}">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-sm font-semibold text-slate-700">Soal #{{ $index + 1 }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $typeBadge[$q->type ?? 'PG'] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $typeMap[$q->type ?? ''] ?? ($q->type ?? '-') }}
                            </span>
                            @if($isManual && !$isCorrect)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                    ⚠ Perlu Koreksi Manual
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-extrabold {{ $isCorrect ? 'text-emerald-700' : 'text-slate-500' }}" id="score-display-{{ $answer->id }}">
                                {{ $answer->score ?? 0 }}/{{ $q->score ?? 0 }} poin
                            </span>
                            @if($isCorrect)
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">✓ Benar</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-600">✗ Salah</span>
                            @endif
                        </div>
                    </div>

                    {{-- Question Text --}}
                    <div class="px-5 pt-4 pb-2">
                        <div class="text-sm text-slate-800 leading-relaxed p-4 bg-slate-50 rounded-xl border border-slate-100">
                            {!! $q->question ?? '' !!}
                        </div>
                    </div>

                    {{-- Answer Section --}}
                    <div class="px-5 py-4">
                        @php
                            $answerRaw   = $answer->answer_text ?? null;
                            $answerStr   = $safeStr($answerRaw);
                            $correctRaw  = $q->correct_answer ?? null;
                            $correctStr  = is_null($correctRaw) ? null : $safeStr($correctRaw, '');
                        @endphp
                        @if(in_array($q->type ?? '', ['PG', 'DD', 'PGK']))
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">Pilihan Jawaban</p>
                            <div class="space-y-2">
                                @foreach($q->choices as $choice)
                                    @php
                                        $pgkSelected = is_array($answerRaw)
                                            ? in_array($choice->label, $answerRaw)
                                            : in_array($choice->label, explode(',', $answerStr));
                                        $isSelected = $q->type === 'PGK'
                                            ? $pgkSelected
                                            : ($answer->choice_id == $choice->id);
                                        $isCorrectChoice = (bool)($choice->is_correct ?? false);
                                    @endphp
                                    <div class="flex items-start gap-3 px-4 py-3 rounded-xl border transition
                                        {{ $isSelected && $isCorrectChoice ? 'border-emerald-400 bg-emerald-50' : '' }}
                                        {{ $isSelected && !$isCorrectChoice ? 'border-rose-400 bg-rose-50' : '' }}
                                        {{ !$isSelected && $isCorrectChoice ? 'border-emerald-200 bg-emerald-50/40' : '' }}
                                        {{ !$isSelected && !$isCorrectChoice ? 'border-slate-100 bg-white' : '' }}">
                                        <span class="flex-shrink-0 w-7 h-7 rounded-full text-xs font-bold flex items-center justify-center
                                            {{ $isCorrectChoice ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}
                                            {{ $isSelected ? 'ring-2 ' . ($isCorrectChoice ? 'ring-emerald-400' : 'ring-rose-400') : '' }}">
                                            {{ $choice->label ?? chr(64 + $loop->iteration) }}
                                        </span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-slate-700">{{ $choice->text ?? $choice->choice_text ?? '' }}</p>
                                            <div class="flex gap-2 mt-0.5 flex-wrap">
                                                @if($isCorrectChoice)
                                                    <span class="text-xs text-emerald-600 font-medium">✓ Kunci jawaban</span>
                                                @endif
                                                @if($isSelected)
                                                    <span class="text-xs {{ $isCorrectChoice ? 'text-emerald-600' : 'text-rose-600' }} font-medium">← Pilihan siswa</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        @elseif(($q->type ?? '') === 'BS')
                            <div class="flex gap-3 items-center">
                                <div>
                                    <p class="text-xs text-slate-400 font-semibold uppercase mb-1">Jawaban Siswa</p>
                                    <span class="px-5 py-2 rounded-xl border-2 text-sm font-bold
                                        {{ $answerStr === 'benar' ? 'border-emerald-400 bg-emerald-50 text-emerald-800' : 'border-rose-400 bg-rose-50 text-rose-700' }}">
                                        {{ ucfirst($answerStr ?: '-') }}
                                    </span>
                                </div>
                            </div>

                        @elseif(($q->type ?? '') === 'IS')
                            <div class="mb-2">
                                <p class="text-xs text-slate-400 font-semibold uppercase mb-1">Jawaban Siswa</p>
                                <div class="px-4 py-2.5 bg-slate-50 border-2 {{ $isCorrect ? 'border-emerald-300' : 'border-slate-200' }} rounded-xl text-sm text-slate-700">
                                    {{ $answerStr }}
                                </div>
                            </div>
                            @if($correctStr)
                                <div class="mt-2">
                                    <p class="text-xs text-slate-400 font-semibold uppercase mb-1">Kunci Jawaban</p>
                                    <div class="px-4 py-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-800">
                                        {{ $correctStr }}
                                    </div>
                                </div>
                            @endif

                        @elseif($isEssay)
                            <div class="mb-3">
                                <p class="text-xs text-slate-400 font-semibold uppercase mb-1">Jawaban Esai Siswa</p>
                                <div class="px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700 whitespace-pre-wrap leading-relaxed min-h-[80px]">
                                    {{ $answerStr !== '(tidak dijawab)' ? $answerStr : '(tidak dijawab)' }}
                                </div>
                            </div>
                            @if($answer->feedback)
                                <div class="mb-3 px-4 py-3 bg-indigo-50 border border-indigo-200 rounded-xl text-sm text-indigo-800">
                                    <span class="font-bold">Feedback Guru:</span> {{ $answer->feedback }}
                                </div>
                            @endif

                        @else
                            <div>
                                <p class="text-xs text-slate-400 font-semibold uppercase mb-1">Jawaban Siswa</p>
                                <div class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-700">
                                    {{ $answerStr }}
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- ===== PENILAIAN MANUAL (Essay / IS / SK) ===== --}}
                    @if($isManual)
                        <div class="mx-5 mb-5 p-4 bg-indigo-50 border border-indigo-100 rounded-2xl">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <p class="text-xs font-bold text-indigo-700 uppercase tracking-wide">Penilaian Manual</p>
                                <span class="ml-auto text-xs text-indigo-500">Maks. {{ $q->score ?? 0 }} poin</span>
                            </div>
                            <form class="update-score-form space-y-3"
                                data-question-id="{{ $answer->question_id }}"
                                data-answer-id="{{ $answer->id }}"
                                data-max-score="{{ $q->score ?? 0 }}">

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    {{-- Nilai --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-indigo-700 mb-1">
                                            Nilai <span class="text-indigo-400">(0 – {{ $q->score ?? 0 }})</span>
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <input type="number" name="score"
                                                value="{{ $answer->score ?? 0 }}"
                                                min="0" max="{{ $q->score ?? 0 }}"
                                                step="0.5"
                                                class="score-input w-full px-3 py-2 border border-indigo-200 bg-white rounded-xl text-sm font-bold text-center focus:ring-2 focus:ring-indigo-400 focus:border-transparent"
                                                id="score-input-{{ $answer->id }}">
                                        </div>
                                        {{-- Slider untuk nilai --}}
                                        <input type="range" min="0" max="{{ $q->score ?? 0 }}" step="0.5"
                                            value="{{ $answer->score ?? 0 }}"
                                            class="w-full mt-2 accent-indigo-600"
                                            oninput="document.getElementById('score-input-{{ $answer->id }}').value = this.value">
                                    </div>

                                    {{-- Status --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-indigo-700 mb-1">Status Jawaban</label>
                                        <select name="is_correct"
                                            class="w-full px-3 py-2 border border-indigo-200 bg-white rounded-xl text-sm focus:ring-2 focus:ring-indigo-400">
                                            <option value="1" {{ ($answer->is_correct ?? false) ? 'selected' : '' }}>✓ Benar</option>
                                            <option value="0" {{ !($answer->is_correct ?? false) ? 'selected' : '' }}>✗ Salah / Sebagian</option>
                                        </select>
                                    </div>

                                    {{-- Feedback --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-indigo-700 mb-1">Feedback (opsional)</label>
                                        <input type="text" name="feedback"
                                            value="{{ $answer->feedback ?? '' }}"
                                            placeholder="Tulis feedback untuk siswa..."
                                            class="w-full px-3 py-2 border border-indigo-200 bg-white rounded-xl text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <button type="submit"
                                        class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Simpan Penilaian
                                    </button>
                                    <span class="save-status text-xs hidden font-semibold"></span>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 py-16 text-center">
                    <svg class="w-14 h-14 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p class="text-slate-500 font-semibold">Tidak ada jawaban yang tercatat.</p>
                </div>
            @endforelse
        </div>

        {{-- ===== FLOATING TOTAL SCORE ===== --}}
        <div class="fixed bottom-6 right-6 z-50">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-xl px-5 py-3 flex items-center gap-3" id="floatingScore">
                <div class="text-center">
                    <p class="text-xs text-slate-400 font-medium">Total Nilai</p>
                    <p class="text-xl font-extrabold text-indigo-600" id="floatingScoreVal">{{ number_format($fs, 1) }}</p>
                </div>
                <div class="w-px h-10 bg-slate-200"></div>
                <div class="text-center">
                    <p class="text-xs text-slate-400 font-medium">Grade</p>
                    <p class="text-xl font-extrabold text-slate-800">{{ $grade }}</p>
                </div>
            </div>
        </div>

        <div class="h-24"></div>

    </div>
</div>

<script>
    // ===== FILTER ANSWERS =====
    function filterAnswers(type) {
        const cards = document.querySelectorAll('.answer-card');
        const tabs = document.querySelectorAll('.tab-btn');

        tabs.forEach(t => {
            t.className = 'tab-btn px-4 py-1.5 rounded-lg text-sm font-semibold bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition';
        });
        document.getElementById('tab-' + type).className = 'tab-btn px-4 py-1.5 rounded-lg text-sm font-semibold bg-indigo-600 text-white transition';

        cards.forEach(card => {
            const isCorrect = card.dataset.correct === 'true';
            const isManual = card.dataset.manual === 'true';   // soal perlu koreksi manual

            if (type === 'all') {
                card.style.display = '';
            } else if (type === 'correct') {
                card.style.display = isCorrect ? '' : 'none';
            } else if (type === 'wrong') {
                // Tampilkan hanya jika salah dan BUKAN manual
                card.style.display = (!isCorrect && !isManual) ? '' : 'none';
            } else if (type === 'manual') {
                // Tampilkan semua soal manual
                card.style.display = isManual ? '' : 'none';
            }
        });
    }

    // ===== SCORE SLIDER SYNC =====
    document.querySelectorAll('input[type="range"]').forEach(slider => {
        const form = slider.closest('.update-score-form');
        const scoreInput = form ? form.querySelector('.score-input') : null;
        if (scoreInput) {
            scoreInput.addEventListener('input', function () {
                slider.value = this.value;
            });
        }
    });

    // ===== SAVE ESSAY SCORE =====
    document.querySelectorAll('.update-score-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const statusEl = this.querySelector('.save-status');
            const formData = new FormData(this);
            const questionId = this.dataset.questionId;
            const answerId = this.dataset.answerId;

            btn.disabled = true;
            btn.innerHTML = `<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Menyimpan...`;

            try {
                const res = await fetch(`/guru/exams/{{ $exam->id }}/results/{{ $attempt->id }}/update-score`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        question_id: questionId,
                        score: parseFloat(formData.get('score')),
                        is_correct: formData.get('is_correct'),
                        feedback: formData.get('feedback')
                    })
                });
                const data = await res.json();
                if (data.success) {
                    statusEl.textContent = '✓ Tersimpan!';
                    statusEl.className = 'save-status text-xs font-semibold text-emerald-600';

                    // Update displayed score on card
                    const scoreDisplay = document.getElementById('score-display-' + answerId);
                    if (scoreDisplay) {
                        const maxScore = this.dataset.maxScore;
                        scoreDisplay.textContent = formData.get('score') + '/' + maxScore + ' poin';
                    }

                    // Update floating total score
                    if (data.final_score !== undefined) {
                        document.getElementById('floatingScoreVal').textContent = parseFloat(data.final_score).toFixed(1);
                        document.getElementById('displayScore').textContent = parseFloat(data.final_score).toFixed(1);
                    }

                    setTimeout(() => { statusEl.className = 'save-status text-xs hidden'; }, 3000);
                } else {
                    statusEl.textContent = '✗ ' + (data.message || 'Gagal menyimpan');
                    statusEl.className = 'save-status text-xs font-semibold text-rose-600';
                }
            } catch (err) {
                statusEl.textContent = '✗ Terjadi kesalahan jaringan';
                statusEl.className = 'save-status text-xs font-semibold text-rose-600';
            } finally {
                btn.disabled = false;
                btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Simpan Penilaian`;
            }
        });
    });

    // ===== REGRADE =====
    async function regradeAttempt(examId, attemptId) {
        if (!confirm('Lakukan koreksi ulang otomatis untuk semua soal PG? Ini akan menimpa nilai PG yang ada.')) return;
        try {
            const res = await fetch(`/guru/exams/${examId}/results/${attemptId}/regrade`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                showToast('Koreksi ulang berhasil! Nilai akhir: ' + data.final_score, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('Gagal: ' + data.message, 'error');
            }
        } catch (e) {
            showToast('Terjadi kesalahan jaringan.', 'error');
        }
    }

    // ===== RESET ATTEMPT =====
    async function resetAttempt(examId, attemptId) {
        if (!confirm('Reset attempt ini? Semua jawaban akan dihapus dan siswa dapat mengerjakan ulang ujian.')) return;
        try {
            const res = await fetch(`/guru/exams/${examId}/results/${attemptId}/reset`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => window.location.href = '{{ route("guru.exams.results.index", $exam->id) }}', 1500);
            } else {
                showToast('Gagal: ' + data.message, 'error');
            }
        } catch (e) {
            showToast('Terjadi kesalahan jaringan.', 'error');
        }
    }

    // ===== TOAST NOTIFICATION =====
    function showToast(message, type = 'success') {
        const existing = document.getElementById('toast-notif');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.id = 'toast-notif';
        toast.className = `fixed top-6 right-6 z-[100] flex items-center gap-3 px-5 py-3 rounded-2xl shadow-xl text-sm font-semibold transition-all
            ${type === 'success' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'}`;
        toast.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                ${type === 'success'
                    ? '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>'
                    : '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>'}
            </svg>
            ${message}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3500);
    }
</script>

<style>
@media print {
    .fixed, button, a[href], .update-score-form { display: none !important; }
    .shadow-sm, .shadow-xl { box-shadow: none !important; }
    body { background: white !important; }
}
</style>
@endsection
