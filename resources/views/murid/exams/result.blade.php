{{-- resources/views/murid/exams/result.blade.php --}}
@extends('layouts.appSiswa')

@section('content')
    <div class="min-h-screen bg-gradient-to-b from-slate-50 to-white py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="bg-white shadow-xl rounded-2xl p-6 mb-8 border border-slate-100">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-slate-900 mb-2">{{ $exam->title }}</h1>
                        <div class="flex flex-wrap gap-4 text-slate-600">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Durasi: {{ $exam->duration }} menit</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span>Mata Pelajaran: {{ $exam->subject->name_subject ?? '-' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>Nama: {{ $attempt->student->name ?? Auth::user()->name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Score Card -->
                    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-6 text-center shadow-lg">
                        <p class="text-sm font-semibold uppercase tracking-wider mb-2">Nilai Akhir</p>
                        <p class="text-5xl font-bold mb-1">{{ number_format($attempt->final_score, 1) }}</p>
                        <div class="text-sm opacity-90">
                            @php
                                $grade = 'Tidak Lulus';
                                $color = 'bg-red-500';
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
                                }
                            @endphp
                            <span class="inline-block px-3 py-1 rounded-full {{ $color }} text-white text-xs font-semibold">
                                {{ $grade }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Correct Answers -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-green-800 uppercase tracking-wider">Jawaban Benar</p>
                            <p class="text-3xl font-bold text-green-900 mt-2">
                                {{ $attempt->answers->where('is_correct', true)->count() }}
                            </p>
                        </div>
                        <div class="bg-green-500 text-white p-3 rounded-full">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Wrong Answers -->
                <div class="bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-red-800 uppercase tracking-wider">Jawaban Salah</p>
                            <p class="text-3xl font-bold text-red-900 mt-2">
                                {{ $attempt->answers->where('is_correct', false)->count() }}
                            </p>
                        </div>
                        <div class="bg-red-500 text-white p-3 rounded-full">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Score -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-blue-800 uppercase tracking-wider">Total Skor</p>
                            <p class="text-3xl font-bold text-blue-900 mt-2">
                                {{ $attempt->score ?? 0 }}
                            </p>
                        </div>
                        <div class="bg-blue-500 text-white p-3 rounded-full">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Time Taken -->
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-purple-800 uppercase tracking-wider">Waktu Pengerjaan</p>
                            <p class="text-3xl font-bold text-purple-900 mt-2">
                                @php
                                    $minutes = floor($attempt->getTimeElapsed() / 60);
                                    $seconds = $attempt->getTimeElapsed() % 60;
                                @endphp
                                {{ $minutes }}:{{ str_pad($seconds, 2, '0', STR_PAD_LEFT) }}
                            </p>
                        </div>
                        <div class="bg-purple-500 text-white p-3 rounded-full">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Results -->
            <div class="bg-white shadow-xl rounded-2xl p-6 border border-slate-100 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-slate-900">Detail Jawaban</h2>

                    @if($exam->show_correct_answer)
                    <a href="{{ route('soal.exams.review', ['exam' => $exam->id, 'attempt' => $attempt->id]) }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Review Lengkap
                    </a>
                    @endif
                </div>

                <!-- Questions List -->
                <div class="space-y-6">
                    @foreach($attempt->answers->sortBy('question.order') as $index => $answer)
                        @php
                            $question = $answer->question;
                            $isCorrect = $answer->is_correct;
                        @endphp

                        <div class="border border-slate-200 rounded-xl p-6 hover:border-slate-300 transition-colors {{ $isCorrect ? 'bg-green-50' : 'bg-red-50' }}">
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center justify-center w-8 h-8 rounded-full
                                        {{ $isCorrect ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}
                                        font-bold text-sm">
                                        {{ $index + 1 }}
                                    </span>
                                    <span class="font-semibold text-slate-900">Soal #{{ $index + 1 }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                                        {{ $isCorrect ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                        {{ $isCorrect ? 'Benar' : 'Salah' }}
                                    </span>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                        Skor: {{ $answer->score }}/{{ $question->score ?? 0 }}
                                    </span>
                                </div>
                            </div>

                            <!-- Question Text -->
                            <div class="mb-4">
                                <p class="text-slate-800 font-medium mb-2">Pertanyaan:</p>
                                <div class="bg-white border border-slate-200 rounded-lg p-4">
                                    {!! nl2br(e($question->question)) !!}
                                </div>
                            </div>

                            <!-- PERBAIKAN: Tampilkan semua pilihan untuk soal PG -->
                            @if($question->type === 'PG')
                                <!-- Student's Answer -->
                                <div class="mb-4">
                                    <p class="text-slate-800 font-medium mb-2">Jawaban Anda:</p>
                                    <div class="space-y-2">
                                        @foreach($question->choices->sortBy('order') as $choice)
                                            <div class="flex items-start gap-3 p-3 rounded-lg border
                                                {{ $answer->choice_id == $choice->id ?
                                                    ($isCorrect ? 'border-green-500 bg-green-50' : 'border-red-500 bg-red-50') :
                                                    'border-gray-200' }}">
                                                <!-- Label Pilihan (A, B, C, D) -->
                                                <span class="flex items-center justify-center w-8 h-8 rounded-full
                                                    {{ $choice->is_correct ? 'bg-green-100 text-green-800 font-bold' : 'bg-gray-100 text-gray-800' }}
                                                    {{ $answer->choice_id == $choice->id ? ($isCorrect ? 'ring-2 ring-green-500' : 'ring-2 ring-red-500') : '' }}">
                                                    {{ $choice->label }}
                                                </span>

                                                <!-- Teks Pilihan -->
                                                <div class="flex-1">
                                                    <p class="{{ $answer->choice_id == $choice->id ? 'font-medium' : '' }}">
                                                        {{ $choice->text }}
                                                    </p>
                                                </div>

                                                <!-- Icon untuk jawaban yang dipilih -->
                                                @if($answer->choice_id == $choice->id)
                                                    <div class="flex items-center gap-2">
                                                        @if($isCorrect)
                                                            <span class="text-green-600 font-medium">✓ Jawaban Anda</span>
                                                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                        @else
                                                            <span class="text-red-600 font-medium">✗ Jawaban Anda</span>
                                                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                      d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        @endif
                                                    </div>
                                                @endif

                                                <!-- Icon untuk jawaban benar (jika bukan yang dipilih) -->
                                                @if($choice->is_correct && $answer->choice_id != $choice->id)
                                                    <div class="flex items-center gap-2 text-green-600">
                                                        <span class="font-medium">✓ Jawaban Benar</span>
                                                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Summary -->
                                <div class="mt-4 p-3 rounded-lg
                                    {{ $isCorrect ? 'bg-green-100 border border-green-300' : 'bg-red-100 border border-red-300' }}">
                                    <p class="{{ $isCorrect ? 'text-green-800' : 'text-red-800' }} font-medium">
                                        @if($isCorrect)
                                            ✓ Anda memilih jawaban yang benar:
                                            <span class="font-bold">{{ $answer->choice->label ?? '' }}. {{ $answer->choice->text ?? '' }}</span>
                                        @else
                                            ✗ Anda memilih jawaban:
                                            <span class="font-bold">{{ $answer->choice->label ?? '' }}. {{ $answer->choice->text ?? '' }}</span>

                                            @php
                                                $correctChoice = $question->choices->where('is_correct', true)->first();
                                            @endphp

                                            @if($correctChoice)
                                                <br>
                                                ✓ Jawaban yang benar:
                                                <span class="font-bold">{{ $correctChoice->label }}. {{ $correctChoice->text }}</span>
                                            @endif
                                        @endif
                                    </p>
                                </div>
                            @endif

                            <!-- Untuk soal IS (Isian Singkat) -->
                            @if($question->type === 'IS')
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Student's Answer -->
                                    <div>
                                        <p class="text-slate-800 font-medium mb-2">Jawaban Anda:</p>
                                        <div class="bg-white border border-slate-200 rounded-lg p-4">
                                            <p class="text-slate-700">{{ $answer->answer_text ?? '-' }}</p>
                                        </div>
                                    </div>

                                    <!-- Correct Answer -->
                                    @if($exam->show_correct_answer)
                                        <div>
                                            <p class="text-slate-800 font-medium mb-2">Jawaban Benar:</p>
                                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                                @if(!empty($question->short_answers))
                                                    <p class="text-green-800 font-medium mb-2">Kunci jawaban:</p>
                                                    <ul class="list-disc list-inside space-y-1 text-green-700">
                                                        @foreach($question->short_answers as $shortAnswer)
                                                            <li>{{ $shortAnswer }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="text-green-600 font-medium">Tidak ada jawaban benar yang ditetapkan</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Explanation -->
                            @if($question->explanation && $exam->show_correct_answer)
                                <div class="mt-4">
                                    <p class="text-slate-800 font-medium mb-2">Penjelasan:</p>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <p class="text-blue-800">{{ $question->explanation }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('soal.index') }}"
                   class="bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600
                          text-white font-bold py-3 px-8 rounded-xl transition-all shadow-md hover:shadow-lg
                          text-center">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali ke Daftar Ujian
                </a>

                @if($exam->show_result_after === 'immediately' || $exam->show_result_after === 'review')
                <a href="{{ route('soal.exams.review', ['exam' => $exam->id, 'attempt' => $attempt->id]) }}"
                   class="bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600
                          text-white font-bold py-3 px-8 rounded-xl transition-all shadow-md hover:shadow-lg
                          text-center">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Review Lengkap
                </a>
                @endif

                @if($exam->limit_attempts > 1)
                    @php
                        $attemptCount = \App\Models\ExamAttempt::where('exam_id', $exam->id)
                            ->where('student_id', Auth::id())
                            ->whereIn('status', ['submitted', 'timeout'])
                            ->count();
                    @endphp
                    @if($attemptCount < $exam->limit_attempts)
                    <a href="{{ route('soal.exams.start', $exam->id) }}"
                       class="bg-gradient-to-r from-purple-600 to-purple-500 hover:from-purple-700 hover:to-purple-600
                              text-white font-bold py-3 px-8 rounded-xl transition-all shadow-md hover:shadow-lg
                              text-center">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Coba Lagi ({{ $attemptCount + 1 }}/{{ $exam->limit_attempts }})
                    </a>
                    @endif
                @endif
            </div>

            <!-- Footer Info -->
            <div class="mt-8 text-center text-slate-500 text-sm">
                <p>Ujian diselesaikan pada: {{ $attempt->ended_at->format('d F Y H:i:s') }}</p>
                <p class="mt-1">Status:
                    <span class="font-semibold {{ $attempt->is_cheating_detected ? 'text-red-600' : 'text-green-600' }}">
                        {{ $attempt->is_cheating_detected ? 'Terdeteksi Pelanggaran' : 'Normal' }}
                    </span>
                </p>
                @if($attempt->violation_count > 0)
                    <p class="mt-1 text-amber-600">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        Terdapat {{ $attempt->violation_count }} pelanggaran selama ujian
                    </p>
                @endif
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
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
        </button>
    </div>

    <style>
        @media print {
            .fixed { display: none !important; }
            .bg-gradient-to-b { background: white !important; }
            .shadow-xl, .shadow-lg { box-shadow: none !important; }
            .border { border: 1px solid #ddd !important; }
            .rounded-2xl, .rounded-xl { border-radius: 4px !important; }
            .p-6, .p-8 { padding: 20px !important; }
            .mb-8 { margin-bottom: 20px !important; }
            .gap-6 { gap: 15px !important; }
            .text-3xl { font-size: 24px !important; }
            .text-5xl { font-size: 36px !important; }
        }
    </style>
@endsection
