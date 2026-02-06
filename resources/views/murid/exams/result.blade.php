@extends('layouts.appSiswa')

@section('content')
    <div class="min-h-screen bg-white py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                // Tambahkan di awal file untuk handle cache
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Cache-Control: post-check=0, pre-check=0', false);
                header('Pragma: no-cache');

                // Check jika ini auto-submitted
                $autoSubmitted = request()->has('auto_submitted') ||
                                request()->has('violation') ||
                                session()->has('violation_submitted_' . $attempt->id);

                // Clear exam session data saat melihat hasil
                session()->forget([
                    'current_attempt_' . $exam->id,
                    'fullscreen_required_' . $exam->id,
                    'exam_started_' . $exam->id,
                ]);

                // Clear localStorage jika auto submitted
                if ($autoSubmitted) {
                    echo '<script>
                        if (typeof localStorage !== "undefined") {
                            localStorage.removeItem("quiz_data_' . $exam->id . '");
                            localStorage.removeItem("exam_answers_' . $exam->id . '");
                            localStorage.removeItem("exam_time_' . $exam->id . '");
                        }
                    </script>';
                }

                // Hitung percobaan
                $attemptCount = $exam->attempts()
                    ->where('student_id', auth()->id())
                    ->count();
            @endphp

            <!-- Header -->
            <div class="bg-white rounded-2xl p-6 mb-8 border-2 border-blue-100 shadow-sm">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex-1 space-y-5">
                        <h1 class="text-3xl font-bold text-blue-900 mb-2">{{ $exam->title }}</h1>
                        <div class="flex flex-wrap gap-4 text-slate-600">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Durasi: {{ $exam->duration }} menit</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <span>Mata Pelajaran: {{ $exam->subject->name_subject ?? '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <span>Jenis: {{ $exam->getDisplayType() }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Score Card - Tampilkan hanya jika show_score = true -->
                    @if($exam->show_score)
                        <div class="bg-blue-600 text-white rounded-xl p-6 text-center shadow-sm border-2 border-blue-700">
                            <p class="text-sm font-semibold uppercase tracking-wider mb-2">Nilai Akhir</p>
                            <p class="text-5xl font-bold mb-1">{{ number_format($attempt->final_score, 1) }}</p>
                            <div class="text-sm opacity-90">
                                @php
                                    $grade = 'Tidak Lulus';
                                    $color = 'bg-red-500';
                                    if ($attempt->final_score >= $exam->min_pass_grade) {
                                        if ($attempt->final_score >= 85) {
                                            $grade = 'A (Sangat Baik)';
                                            $color = 'bg-green-500';
                                        } elseif ($attempt->final_score >= 75) {
                                            $grade = 'B (Baik)';
                                            $color = 'bg-blue-500';
                                        } elseif ($attempt->final_score >= 65) {
                                            $grade = 'C (Cukup)';
                                            $color = 'bg-yellow-500';
                                        } elseif ($attempt->final_score >= 55) {
                                            $grade = 'D (Kurang)';
                                            $color = 'bg-orange-500';
                                        } else {
                                            $grade = 'E (Tidak Lulus)';
                                            $color = 'bg-red-500';
                                        }
                                    }
                                @endphp
                                <span
                                    class="inline-block px-3 py-1 rounded-full {{ $color }} text-white text-xs font-semibold mt-2">
                                    {{ $grade }}
                                </span>
                            </div>
                        </div>
                    @else
                        <!-- Tampilkan status sederhana jika show_score = false -->
                        <div class="bg-blue-600 text-white rounded-xl p-6 text-center shadow-sm border-2 border-blue-700">
                            <p class="text-sm font-semibold uppercase tracking-wider mb-2">Status Ujian</p>
                            <p class="text-2xl font-bold mb-1">Selesai</p>
                            <div class="text-sm opacity-90">
                                <span class="inline-block px-3 py-1 rounded-full bg-blue-500 text-white text-xs font-semibold mt-2">
                                    Telah Dikumpulkan
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Violation Alert - SELALU TAMPIL jika ada pelanggaran -->
            @if ($attempt->is_cheating_detected || $attempt->violation_count > 0)
                <div class="mb-8 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.282 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-red-800">
                                @if(!$exam->disable_violations && $attempt->is_cheating_detected)
                                    UJIAN DIHENTIKAN OTOMATIS
                                @else
                                    TERDETEKSI PELANGGARAN
                                @endif
                            </h3>
                            <p class="text-red-700">
                                @if(!$exam->disable_violations && $attempt->is_cheating_detected)
                                    <span class="font-bold">Ujian dihentikan otomatis!</span>
                                    Batas maksimal pelanggaran ({{ $exam->violation_limit ?? 3 }}) telah tercapai.
                                @else
                                    Terdeteksi <span class="font-bold">{{ $attempt->violation_count }} pelanggaran</span> selama ujian.
                                @endif
                            </p>
                            @if(!empty($attempt->violation_log))
                                <div class="mt-3 text-sm text-red-600">
                                    <p class="font-semibold">Riwayat Pelanggaran:</p>
                                    <ul class="list-disc list-inside mt-1 space-y-1">
                                        @foreach(array_slice($attempt->violation_log, -5) as $index => $log)
                                            <li class="text-xs">
                                                <span class="font-medium">{{ $log['type'] ?? 'Pelanggaran' }}</span> -
                                                {{ \Carbon\Carbon::parse($log['timestamp'] ?? now())->format('H:i:s') }}
                                                @if(isset($log['details']))
                                                    <span class="text-gray-600">({{ $log['details'] }})</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Stats Grid - Tampilkan hanya jika show_score = true -->
            @if($exam->show_score)
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <!-- Correct Answers -->
                    <div class="bg-white border-2 border-blue-100 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-blue-700 uppercase tracking-wider">Jawaban Benar</p>
                                <p class="text-3xl font-bold text-blue-900 mt-2">
                                    {{ $attempt->answers->where('is_correct', true)->count() }}
                                </p>
                            </div>
                            <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Wrong Answers -->
                    <div class="bg-white border-2 border-blue-100 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-700 uppercase tracking-wider">Jawaban Salah</p>
                                <p class="text-3xl font-bold text-slate-900 mt-2">
                                    {{ $attempt->answers->where('is_correct', false)->count() }}
                                </p>
                            </div>
                            <div class="bg-slate-200 text-slate-700 p-3 rounded-full">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Score -->
                    <div class="bg-white border-2 border-blue-100 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-blue-700 uppercase tracking-wider">Total Skor</p>
                                <p class="text-3xl font-bold text-blue-900 mt-2">
                                    {{ $attempt->score ?? 0 }}/{{ $exam->questions->sum('score') }}
                                </p>
                            </div>
                            <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Time Taken -->
                    <div class="bg-white border-2 border-blue-100 rounded-xl p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-blue-700 uppercase tracking-wider">Waktu Pengerjaan</p>
                                <p class="text-3xl font-bold text-blue-900 mt-2">
                                    @php
                                        $minutes = floor($attempt->getTimeElapsed() / 60);
                                        $seconds = $attempt->getTimeElapsed() % 60;
                                    @endphp
                                    {{ $minutes }}:{{ str_pad($seconds, 2, '0', STR_PAD_LEFT) }}
                                </p>
                            </div>
                            <div class="bg-blue-100 text-blue-600 p-3 rounded-full">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Message jika score tidak ditampilkan -->
                <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-8 mb-8 text-center">
                    <div class="text-blue-700">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-xl font-bold mb-2">Ujian Telah Dikumpulkan</h3>
                        <p class="mb-2">Terima kasih telah mengerjakan ujian ini.</p>
                        <p class="text-sm">Nilai akan diumumkan oleh guru.</p>
                    </div>
                </div>
            @endif

            <!-- Passing Grade Info - Tampilkan hanya jika show_score = true dan ada min_pass_grade -->
            @if($exam->show_score && $exam->min_pass_grade > 0)
                <div class="mb-8 bg-white border-2 {{ $attempt->final_score >= $exam->min_pass_grade ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-3 rounded-full {{ $attempt->final_score >= $exam->min_pass_grade ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                                @if($attempt->final_score >= $exam->min_pass_grade)
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <h3 class="font-bold text-lg {{ $attempt->final_score >= $exam->min_pass_grade ? 'text-green-800' : 'text-red-800' }}">
                                    @if($attempt->final_score >= $exam->min_pass_grade)
                                        SELAMAT! ANDA LULUS
                                    @else
                                        MAAF, ANDA TIDAK LULUS
                                    @endif
                                </h3>
                                <p class="text-sm {{ $attempt->final_score >= $exam->min_pass_grade ? 'text-green-700' : 'text-red-700' }}">
                                    Nilai minimal kelulusan: {{ $exam->min_pass_grade }}% |
                                    Nilai Anda: {{ number_format($attempt->final_score, 1) }}%
                                    @if($attempt->final_score < $exam->min_pass_grade)
                                        | Kurang: {{ number_format($exam->min_pass_grade - $attempt->final_score, 1) }}%
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-600">Status</div>
                            <span class="px-3 py-1 rounded-full text-sm font-bold {{ $attempt->final_score >= $exam->min_pass_grade ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                {{ $attempt->final_score >= $exam->min_pass_grade ? 'LULUS' : 'TIDAK LULUS' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Detailed Results -->
            <div class="bg-white rounded-2xl p-6 border-2 border-blue-100 shadow-sm mb-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-slate-900">
                        @if($exam->show_correct_answer)
                            Detail Jawaban & Pembahasan
                        @else
                            Jawaban Anda
                        @endif
                    </h2>

                    @if($exam->show_correct_answer)
                        <span class="text-sm text-green-600 font-semibold mt-2 md:mt-0">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Jawaban benar ditampilkan
                        </span>
                    @endif
                </div>

                <!-- Questions List -->
                <div class="space-y-6">
                    @foreach ($attempt->answers->sortBy('question.order') as $index => $answer)
                        @php
                            $question = $answer->question;
                            $isCorrect = $answer->is_correct;
                        @endphp

                        <div class="border border-slate-200 rounded-xl p-6 hover:border-slate-300 transition-colors
                            {{ $exam->show_correct_answer ? ($isCorrect ? 'bg-green-50' : 'bg-red-50') : 'bg-white' }}">
                            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4 mb-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center justify-center w-8 h-8 rounded-full
                                        {{ $exam->show_correct_answer
                                            ? ($isCorrect ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800')
                                            : 'bg-blue-100 text-blue-800' }}
                                        font-bold text-sm">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <span class="font-semibold text-slate-900">Soal #{{ $index + 1 }}</span>
                                        <span class="ml-3 px-2 py-1 bg-gray-200 text-gray-700 text-xs font-medium rounded">
                                            {{ $question->type === 'PG' ? 'Pilihan Ganda' : 'Isian Singkat' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    @if($exam->show_correct_answer)
                                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                                            {{ $isCorrect ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                            {{ $isCorrect ? '✓ Benar' : '✗ Salah' }}
                                        </span>
                                    @endif

                                    @if($exam->show_score)
                                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                            Skor: {{ $answer->score }}/{{ $question->score ?? 0 }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Question Text -->
                            <div class="mb-4">
                                <p class="text-slate-800 font-medium mb-2">Pertanyaan:</p>
                                <div class="bg-white border border-slate-200 rounded-lg p-4">
                                    {!! nl2br(e($question->question)) !!}
                                </div>
                            </div>

                            <!-- Answers Display -->
                            @if ($question->type === 'PG')
                                @if($exam->show_correct_answer)
                                    <!-- SHOW ALL CHOICES WITH INDICATORS -->
                                    <div class="mb-4">
                                        <p class="text-slate-800 font-medium mb-2">Pilihan Jawaban:</p>
                                        <div class="space-y-2">
                                            @foreach ($question->choices->sortBy('order') as $choice)
                                                <div class="flex flex-col sm:flex-row sm:items-start gap-3 p-3 rounded-lg border
                                                    {{ $answer->choice_id == $choice->id
                                                        ? ($isCorrect ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50')
                                                        : ($choice->is_correct ? 'border-green-300 bg-green-50/50' : 'border-gray-200') }}">

                                                    <div class="flex items-center gap-3">
                                                        <span class="flex items-center justify-center w-8 h-8 rounded-full
                                                            {{ $choice->is_correct ? 'bg-green-100 text-green-800 font-bold' : 'bg-gray-100 text-gray-800' }}
                                                            {{ $answer->choice_id == $choice->id ? ($isCorrect ? 'ring-2 ring-green-500' : 'ring-2 ring-red-500') : '' }}">
                                                            {{ $choice->label }}
                                                        </span>

                                                        <div class="flex-1">
                                                            <p class="{{ $answer->choice_id == $choice->id ? 'font-medium' : '' }}">
                                                                {{ $choice->text }}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <!-- Indicators -->
                                                    <div class="flex items-center gap-2 mt-2 sm:mt-0">
                                                        @if ($answer->choice_id == $choice->id)
                                                            <div class="flex items-center gap-2">
                                                                @if ($isCorrect)
                                                                    <span class="text-green-600 font-medium text-sm">
                                                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                        </svg>
                                                                        Jawaban Anda (Benar)
                                                                    </span>
                                                                @else
                                                                    <span class="text-red-600 font-medium text-sm">
                                                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                d="M6 18L18 6M6 6l12 12" />
                                                                        </svg>
                                                                        Jawaban Anda (Salah)
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @elseif ($choice->is_correct)
                                                            <div class="flex items-center gap-2 text-green-600">
                                                                <span class="font-medium text-sm">
                                                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                    Jawaban Benar
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <!-- ONLY SHOW SELECTED ANSWER -->
                                    <div class="mb-4">
                                        <p class="text-slate-800 font-medium mb-2">Jawaban Anda:</p>
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                            @if($answer->choice)
                                                <span class="font-bold">{{ $answer->choice->label }}.</span>
                                                {{ $answer->choice->text }}
                                            @else
                                                <span class="text-gray-500 italic">
                                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                    Tidak dijawab
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                            @else
                                <!-- Isian Singkat (IS) -->
                                <div class="mb-4">
                                    <p class="text-slate-800 font-medium mb-2">Jawaban Anda:</p>
                                    <div class="bg-white border border-slate-200 rounded-lg p-4">
                                        {{ $answer->answer_text ?? '-' }}
                                    </div>
                                </div>

                                @if($exam->show_correct_answer)
                                    <div class="mb-4">
                                        <p class="text-slate-800 font-medium mb-2">Jawaban yang Benar:</p>
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                            @if($question->short_answers)
                                                @php
                                                    $correctAnswers = is_string($question->short_answers)
                                                        ? json_decode($question->short_answers, true)
                                                        : $question->short_answers;
                                                @endphp
                                                @if(is_array($correctAnswers))
                                                    @foreach($correctAnswers as $ans)
                                                        <span class="inline-block px-3 py-1 bg-green-100 text-green-800 rounded-lg mr-2 mb-2 font-medium">
                                                            {{ $ans }}
                                                        </span>
                                                    @endforeach
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif

                            <!-- Explanation -->
                            @if($exam->show_correct_answer && $question->explanation)
                                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <p class="text-blue-800 font-semibold mb-2">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Pembahasan:
                                    </p>
                                    <p class="text-blue-700">{!! nl2br(e($question->explanation)) !!}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
                <a href="{{ route('soal.index') }}"
                    class="flex items-center justify-center gap-2 px-8 py-3 bg-slate-500 hover:bg-slate-600 text-white font-bold rounded-lg transition-all shadow-sm hover:shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali ke Daftar Soal
                </a>

                @if (($exam->limit_attempts ?? 1) > 1 && $attemptCount < $exam->limit_attempts)
                    <a href="{{ route('soal.detail', $exam->id) }}"
                        class="flex items-center justify-center gap-2 px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition-all shadow-sm hover:shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Coba Lagi ({{ $exam->limit_attempts - $attemptCount }} percobaan tersisa)
                    </a>
                @endif

                @if ($exam->type === 'QUIZ' && $exam->enable_retake)
                    <a href="{{ route('soal.detail', $exam->id) }}"
                        class="flex items-center justify-center gap-2 px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-lg transition-all shadow-sm hover:shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Ulangi Quiz
                    </a>
                @endif
            </div>

            <!-- Footer Info -->
            <div class="mt-8 text-center text-slate-500 text-sm">
                <div class="flex flex-wrap justify-center gap-4 mb-2">
                    <p>Ujian dimulai: {{ $attempt->started_at->format('d F Y H:i:s') }}</p>
                    <p>Ujian selesai: {{ $attempt->ended_at->format('d F Y H:i:s') }}</p>
                </div>
                <p class="mb-2">Status:
                    <span class="font-semibold {{ $attempt->is_cheating_detected ? 'text-red-600' : 'text-green-600' }}">
                        {{ $attempt->is_cheating_detected ? 'Terdeteksi Pelanggaran' : 'Normal' }}
                    </span>
                </p>
                @if ($attempt->violation_count > 0)
                    <p class="text-amber-600">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        Terdapat {{ $attempt->violation_count }} pelanggaran selama ujian
                    </p>
                @endif
                <p class="mt-2 text-xs text-gray-400">
                    Percobaan ke-{{ $attemptCount ?? 1 }} dari {{ $exam->limit_attempts ?? 1 }} percobaan maksimal
                </p>

                <!-- Result Visibility Info -->
                <div class="mt-4 p-3 bg-slate-100 rounded-lg inline-block">
                    <p class="text-xs">
                        @if(!$exam->show_score && !$exam->show_correct_answer)
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                            Hasil detail disembunyikan oleh pengajar
                        @elseif($exam->show_score && !$exam->show_correct_answer)
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Hanya nilai yang ditampilkan
                        @elseif(!$exam->show_score && $exam->show_correct_answer)
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Hanya jawaban benar yang ditampilkan
                        @else
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Hasil lengkap ditampilkan
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Print Button -->
    <div class="fixed bottom-6 right-6">
        <button onclick="window.print()"
            class="bg-white text-slate-700 hover:text-slate-900 border-2 border-slate-300
                       hover:border-slate-400 rounded-full p-3 shadow-lg hover:shadow-xl transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
        </button>
    </div>

    <style>
        @media print {
            .fixed {
                display: none !important;
            }

            .bg-gradient-to-b {
                background: white !important;
            }

            .shadow-xl,
            .shadow-lg {
                box-shadow: none !important;
            }

            .border {
                border: 1px solid #ddd !important;
            }

            .rounded-2xl,
            .rounded-xl {
                border-radius: 4px !important;
            }

            .p-6,
            .p-8 {
                padding: 20px !important;
            }

            .mb-8 {
                margin-bottom: 20px !important;
            }

            .gap-6 {
                gap: 15px !important;
            }

            .text-3xl {
                font-size: 24px !important;
            }

            .text-5xl {
                font-size: 36px !important;
            }

            .hover\:shadow-md,
            .hover\:shadow-lg {
                box-shadow: none !important;
            }

            button, a {
                display: none !important;
            }
        }
    </style>
@endsection
