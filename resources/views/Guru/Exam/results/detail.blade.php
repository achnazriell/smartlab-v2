@extends('layouts.appGuru')

@section('content')
<!-- SweetAlert2 Library -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-6">
        <div>
            <h1 class="h2 text-gray-800 mb-2">Detail Hasil Ujian</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('guru.exams.index') }}">Ujian</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('guru.exams.results.index', $exam->id) }}">Hasil Ujian</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-3">
            <button onclick="confirmPrintDetail()" class="btn btn-secondary" style="border-radius: 8px;">
                <i class="fas fa-print mr-2"></i> Cetak
            </button>
            <button onclick="confirmRegradeAttempt()" class="btn btn-warning" style="border-radius: 8px;">
                <i class="fas fa-redo mr-2"></i> Koreksi Ulang
            </button>
            <a href="{{ route('guru.exams.results.index', $exam->id) }}" class="btn btn-primary" style="border-radius: 8px;">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Informasi Siswa -->
    <div class="row mb-6">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="{{ $attempt->student->user->profile_photo_url ?? asset('images/default-avatar.png') }}"
                             class="rounded-circle" width="120" height="120" alt="{{ $attempt->student->user->name }}">
                        <h4 class="mt-3 mb-1">{{ $attempt->student->user->name }}</h4>
                        <p class="text-muted">NIS: {{ $attempt->student->nis ?? '-' }}</p>
                    </div>

                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Kelas</span>
                            <span class="font-weight-bold">{{ $exam->class->name_class }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Mata Pelajaran</span>
                            <span class="font-weight-bold">{{ $exam->subject->name_subject }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Tanggal Ujian</span>
                            <span class="font-weight-bold">{{ $attempt->ended_at->format('d F Y H:i') }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span>Status</span>
                            <span class="badge badge-{{ $attempt->is_cheating_detected ? 'danger' : 'success' }}">
                                {{ $attempt->is_cheating_detected ? 'Terdeteksi Kecurangan' : 'Normal' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card border-left-primary shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-center">
                                <div class="h1 font-weight-bold text-primary mb-2">
                                    {{ number_format($attempt->final_score, 1) }}
                                </div>
                                <div class="text-uppercase font-weight-bold text-primary mb-2">
                                    Nilai Akhir
                                </div>
                                @php
                                    $grade = 'E';
                                    if ($attempt->final_score >= 85) $grade = 'A';
                                    elseif ($attempt->final_score >= 75) $grade = 'B';
                                    elseif ($attempt->final_score >= 65) $grade = 'C';
                                    elseif ($attempt->final_score >= 55) $grade = 'D';
                                @endphp
                                <span class="badge badge-pill
                                    @if($grade === 'A') badge-success
                                    @elseif($grade === 'B') badge-info
                                    @elseif($grade === 'C') badge-primary
                                    @elseif($grade === 'D') badge-warning
                                    @else badge-danger @endif"
                                    style="font-size: 1.2rem; padding: 8px 20px;">
                                    {{ $grade }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card border-left-success shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-center">
                                <div class="h1 font-weight-bold text-success mb-2">
                                    {{ $correctAnswers }}/{{ $totalQuestions }}
                                </div>
                                <div class="text-uppercase font-weight-bold text-success mb-2">
                                    Jawaban Benar
                                </div>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success"
                                         role="progressbar"
                                         style="width: {{ $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0 }}%"
                                         aria-valuenow="{{ $correctAnswers }}"
                                         aria-valuemin="0"
                                         aria-valuemax="{{ $totalQuestions }}">
                                        {{ $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 1) : 0 }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card border-left-info shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-center">
                                <div class="h1 font-weight-bold text-info mb-2">
                                    {{ $timeFormatted }}
                                </div>
                                <div class="text-uppercase font-weight-bold text-info mb-2">
                                    Waktu Pengerjaan
                                </div>
                                <p class="text-muted mb-0">
                                    {{ $exam->duration }} menit (total waktu)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card border-left-warning shadow-sm h-100">
                        <div class="card-body">
                            <div class="text-center">
                                <div class="h1 font-weight-bold text-warning mb-2">
                                    {{ $attempt->violation_count ?? 0 }}
                                </div>
                                <div class="text-uppercase font-weight-bold text-warning mb-2">
                                    Pelanggaran
                                </div>
                                <p class="text-muted mb-0">
                                    @if($attempt->violation_count > 0)
                                        Terdapat {{ $attempt->violation_count }} pelanggaran
                                    @else
                                        Tidak ada pelanggaran
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik Performa -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performa Jawaban</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="h2 font-weight-bold text-success">{{ $correctAnswers }}</div>
                            <div class="text-uppercase text-muted small">Benar</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="h2 font-weight-bold text-danger">{{ $incorrectAnswers }}</div>
                            <div class="text-uppercase text-muted small">Salah</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="h2 font-weight-bold text-warning">{{ $totalQuestions - $answeredQuestions }}</div>
                            <div class="text-uppercase text-muted small">Tidak Dijawab</div>
                        </div>
                    </div>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-success"
                             style="width: {{ $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0 }}%">
                            Benar ({{ $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 1) : 0 }}%)
                        </div>
                        <div class="progress-bar bg-danger"
                             style="width: {{ $totalQuestions > 0 ? ($incorrectAnswers / $totalQuestions) * 100 : 0 }}%">
                            Salah ({{ $totalQuestions > 0 ? round(($incorrectAnswers / $totalQuestions) * 100, 1) : 0 }}%)
                        </div>
                        <div class="progress-bar bg-warning"
                             style="width: {{ $totalQuestions > 0 ? (($totalQuestions - $answeredQuestions) / $totalQuestions) * 100 : 0 }}%">
                            Kosong ({{ $totalQuestions > 0 ? round((($totalQuestions - $answeredQuestions) / $totalQuestions) * 100, 1) : 0 }}%)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Jawaban -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary">Detail Jawaban</h6>
        </div>
        <div class="card-body">
            @foreach($attempt->answers->sortBy('question.order') as $index => $answer)
            @php
                $question = $answer->question;
                $isCorrect = $answer->is_correct;
            @endphp
            <div class="mb-6 p-4 border rounded-lg {{ $isCorrect ? 'border-success bg-success-light' : 'border-danger bg-danger-light' }}">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div class="d-flex align-items-center">
                        <span class="badge badge-{{ $isCorrect ? 'success' : 'danger' }} mr-3" style="font-size: 1rem; padding: 8px 15px;">
                            Soal {{ $index + 1 }}
                        </span>
                        <div>
                            <h5 class="mb-1">
                                {{ $question->type === 'PG' ? 'Pilihan Ganda' : 'Essay' }}
                                <span class="badge badge-info ml-2">{{ $question->score }} poin</span>
                            </h5>
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-{{ $isCorrect ? 'success' : 'danger' }} p-2">
                            <i class="fas {{ $isCorrect ? 'fa-check' : 'fa-times' }} mr-1"></i>
                            {{ $isCorrect ? 'Benar' : 'Salah' }}
                        </span>
                        <span class="badge badge-primary p-2 ml-2">
                            Skor: {{ $answer->score }}/{{ $question->score }}
                        </span>
                    </div>
                </div>

                <!-- Pertanyaan -->
                <div class="mb-4">
                    <h6 class="font-weight-bold text-gray-700 mb-2">Pertanyaan:</h6>
                    <div class="p-3 bg-white border rounded">
                        {!! nl2br(e($question->question)) !!}
                    </div>
                </div>

                <!-- Jawaban -->
                <div class="row">
                    @if($question->type === 'PG')
                    <div class="col-md-6">
                        <h6 class="font-weight-bold text-gray-700 mb-2">Jawaban Siswa:</h6>
                        <div class="p-3 bg-{{ $isCorrect ? 'success' : 'danger' }}-light border-{{ $isCorrect ? 'success' : 'danger' }} rounded">
                            <div class="d-flex align-items-center">
                                <span class="badge badge-{{ $isCorrect ? 'success' : 'danger' }} mr-3" style="font-size: 1rem; padding: 8px 15px;">
                                    {{ $answer->choice->label ?? '-' }}
                                </span>
                                <span class="font-weight-bold">{{ $answer->choice->text ?? 'Tidak menjawab' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="font-weight-bold text-gray-700 mb-2">Jawaban Benar:</h6>
                        @php
                            $correctChoice = $question->choices->where('is_correct', true)->first();
                        @endphp
                        <div class="p-3 bg-success-light border-success rounded">
                            <div class="d-flex align-items-center">
                                <span class="badge badge-success mr-3" style="font-size: 1rem; padding: 8px 15px;">
                                    {{ $correctChoice->label ?? '-' }}
                                </span>
                                <span class="font-weight-bold">{{ $correctChoice->text ?? 'Tidak ada jawaban benar' }}</span>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="col-12">
                        <h6 class="font-weight-bold text-gray-700 mb-2">Jawaban Essay:</h6>
                        <div class="p-3 bg-light border rounded">
                            <p class="mb-0">{{ $answer->answer_text ?? 'Tidak menjawab' }}</p>
                        </div>

                        <!-- Form penilaian manual untuk essay -->
                        <div class="mt-4">
                            <h6 class="font-weight-bold text-gray-700 mb-2">Penilaian Manual:</h6>
                            <form class="update-score-form" data-question-id="{{ $question->id }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Nilai (0-{{ $question->score }})</label>
                                            <input type="number" class="form-control" name="score"
                                                   value="{{ $answer->score }}" min="0" max="{{ $question->score }}"
                                                   step="0.1">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select class="form-control" name="is_correct">
                                                <option value="1" {{ $answer->is_correct ? 'selected' : '' }}>Benar</option>
                                                <option value="0" {{ !$answer->is_correct ? 'selected' : '' }}>Salah</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Feedback (opsional)</label>
                                    <textarea class="form-control" name="feedback" rows="2">{{ $answer->feedback ?? '' }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-save mr-1"></i> Simpan Penilaian
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Penjelasan -->
                @if($question->explanation)
                <div class="mt-4">
                    <h6 class="font-weight-bold text-gray-700 mb-2">Penjelasan:</h6>
                    <div class="p-3 bg-info-light border-info rounded">
                        <p class="mb-0">{{ $question->explanation }}</p>
                    </div>
                </div>
                @endif
            </div>
            @endforeach

            @if($attempt->answers->isEmpty())
            <div class="text-center py-5">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-clipboard-list fa-4x text-gray-300"></i>
                    </div>
                    <h3 class="mt-4">Tidak Ada Jawaban</h3>
                    <p class="text-muted">Siswa tidak mengisi jawaban apapun.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .bg-success-light { background-color: rgba(40, 167, 69, 0.1); }
    .bg-danger-light { background-color: rgba(220, 53, 69, 0.1); }
    .bg-info-light { background-color: rgba(23, 162, 184, 0.1); }
    .empty-state { padding: 3rem; }
    .empty-state-icon { margin-bottom: 1.5rem; }
</style>

<script>
    // Confirmation Functions
    function confirmPrintDetail() {
        Swal.fire({
            title: 'Cetak Detail Hasil?',
            text: 'File akan dicetak dengan semua detail jawaban siswa',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6c757d',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Cetak',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.print();
            }
        });
    }

    function confirmRegradeAttempt() {
        Swal.fire({
            title: 'Koreksi Ulang Otomatis?',
            text: 'Sistem akan mengoreksi ulang semua jawaban siswa secara otomatis',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                regradeAttempt();
            }
        });
    }

    // Form update score untuk essay
    document.querySelectorAll('.update-score-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const questionId = this.dataset.questionId;
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;

            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...';
            btn.disabled = true;

            fetch(`/guru/exams/{{ $exam->id }}/results/{{ $attempt->id }}/score`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Nilai berhasil diperbarui!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Gagal memperbarui nilai: ' + data.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: 'Terjadi kesalahan: ' + error
                });
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
    });

    // Fungsi koreksi ulang
    function regradeAttempt() {
        fetch(`/guru/exams/{{ $exam->id }}/results/{{ $attempt->id }}/regrade`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Koreksi ulang berhasil! Nilai baru: ' + data.final_score,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal melakukan koreksi ulang: ' + data.message
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan',
                text: 'Terjadi kesalahan: ' + error
            });
        });
    }
</script>
@endsection
