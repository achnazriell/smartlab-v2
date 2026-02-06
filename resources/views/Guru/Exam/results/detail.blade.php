{{-- resources/views/guru/exams/results/detail.blade.php --}}
@extends('layouts.appTeacher')

@section('title', 'Hasil Ujian: ' . $exam->title)

@section('styles')
<style>
    /* styles tetap sama */
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 px-4 py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Detail Hasil Ujian: {{ $attempt->student->user->name ?? 'Siswa' }}</h1>
            <p class="text-slate-600">Analisis detail hasil ujian {{ $exam->title }}</p>
        </div>

        <!-- Statistik Attempt -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="stat-card bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-percentage text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-slate-600 text-sm font-medium">Nilai Akhir</p>
                        <h3 class="text-2xl font-bold text-slate-900">{{ number_format($attempt->final_score, 1) }}</h3>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-slate-600 text-sm font-medium">Jawaban Benar</p>
                        <h3 class="text-2xl font-bold text-slate-900">{{ $correctAnswers }}/{{ $totalQuestions }}</h3>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-14 h-14 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-slate-600 text-sm font-medium">Jawaban Salah</p>
                        <h3 class="text-2xl font-bold text-slate-900">{{ $incorrectAnswers }}</h3>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-white rounded-xl shadow-md p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-14 h-14 bg-amber-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-amber-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-slate-600 text-sm font-medium">Waktu Pengerjaan</p>
                        <h3 class="text-2xl font-bold text-slate-900">{{ $timeFormatted }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Jawaban -->
        <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">
                    <i class="fas fa-list-check mr-2 text-blue-600"></i>Detail Jawaban
                </h3>
            </div>
            <div class="p-6">
                @foreach($attempt->answers as $index => $answer)
                <div class="mb-6 pb-6 border-b border-slate-100 last:border-b-0 last:mb-0 last:pb-0">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <span class="font-bold text-slate-900">Soal #{{ $index + 1 }}</span>
                            @if($answer->question->type === 'PG')
                                <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">PG</span>
                            @else
                                <span class="ml-2 px-2 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold">Essay</span>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="font-bold {{ $answer->is_correct ? 'text-green-600' : 'text-red-600' }}">
                                {{ $answer->score }}/{{ $answer->question->score }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p class="text-slate-700 mb-2">{!! $answer->question->question !!}</p>
                    </div>

                    @if($answer->question->type === 'PG')
                    <div class="mb-3">
                        <h6 class="font-semibold text-slate-600 mb-2">Pilihan:</h6>
                        @foreach($answer->question->choices as $choice)
                        <div class="flex items-center mb-2 p-2 rounded {{ $choice->is_correct ? 'bg-green-50 border border-green-200' : ($answer->choice_id == $choice->id ? 'bg-red-50 border border-red-200' : 'bg-slate-50') }}">
                            <input type="radio" disabled {{ $answer->choice_id == $choice->id ? 'checked' : '' }}
                                   class="mr-2 {{ $choice->is_correct ? 'text-green-600' : 'text-red-600' }}">
                            <span class="{{ $choice->is_correct ? 'text-green-700 font-semibold' : '' }}">
                                {{ $choice->choice_text }}
                                @if($choice->is_correct) ✓ @endif
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="mb-3">
                        <h6 class="font-semibold text-slate-600 mb-2">Jawaban Essay:</h6>
                        <div class="p-3 bg-slate-50 rounded border border-slate-200">
                            {{ $answer->answer_text ?? '(Tidak dijawab)' }}
                        </div>
                    </div>

                    @if($answer->feedback)
                    <div class="mb-3">
                        <h6 class="font-semibold text-slate-600 mb-2">Feedback Guru:</h6>
                        <div class="p-3 bg-blue-50 rounded border border-blue-200">
                            {{ $answer->feedback }}
                        </div>
                    </div>
                    @endif
                    @endif

                    @if($answer->question->type === 'ESSAY')
                    <div class="mt-4">
                        <form class="update-score-form" data-question-id="{{ $answer->question_id }}">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Nilai</label>
                                    <input type="number" name="score"
                                           value="{{ $answer->score }}"
                                           min="0" max="{{ $answer->question->score }}"
                                           class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Status</label>
                                    <select name="is_correct" class="w-full px-3 py-2 border border-slate-300 rounded-lg">
                                        <option value="1" {{ $answer->is_correct ? 'selected' : '' }}>Benar</option>
                                        <option value="0" {{ !$answer->is_correct ? 'selected' : '' }}>Salah</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Feedback</label>
                                    <input type="text" name="feedback"
                                           value="{{ $answer->feedback }}"
                                           class="w-full px-3 py-2 border border-slate-300 rounded-lg"
                                           placeholder="Masukan feedback...">
                                </div>
                            </div>
                            <button type="submit" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fas fa-save mr-2"></i>Simpan Perubahan
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between items-center">
            <a href="{{ route('guru.exams.results.index', $exam->id) }}"
               class="px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Hasil
            </a>
            <div class="flex gap-3">
                <button onclick="regradeAttempt({{ $exam->id }}, {{ $attempt->id }})"
                       class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                    <i class="fas fa-redo mr-2"></i> Koreksi Ulang
                </button>
                <button onclick="resetAttempt({{ $exam->id }}, {{ $attempt->id }})"
                       class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-undo mr-2"></i> Reset Attempt
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi update score untuk essay
    document.querySelectorAll('.update-score-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const questionId = this.dataset.questionId;

            fetch(`/guru/exams/{{ $exam->id }}/results/{{ $attempt->id }}/update-score`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    question_id: questionId,
                    score: formData.get('score'),
                    is_correct: formData.get('is_correct'),
                    feedback: formData.get('feedback')
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Nilai berhasil diperbarui!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan: ' + error);
            });
        });
    });

    function regradeAttempt(examId, attemptId) {
        if (confirm('Apakah Anda yakin ingin melakukan koreksi ulang otomatis?')) {
            fetch(`/guru/exams/${examId}/results/${attemptId}/regrade`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Koreksi ulang berhasil!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan: ' + error);
            });
        }
    }

    function resetAttempt(examId, attemptId) {
        if (confirm('Apakah Anda yakin ingin mereset attempt ini? Siswa akan dapat mengulang ujian.')) {
            fetch(`/guru/exams/${examId}/results/${attemptId}/reset`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Attempt berhasil direset!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Terjadi kesalahan: ' + error);
            });
        }
    }
</script>
@endsection
