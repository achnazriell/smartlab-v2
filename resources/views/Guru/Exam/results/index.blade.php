{{-- resources/views/guru/exams/results/index.blade.php --}}
@extends('layouts.appTeacher')

@section('title', 'Hasil Ujian: ' . $exam->title)

@section('styles')
<style>
    .stat-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .grade-badge {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        color: white;
    }

    .accuracy-bar {
        height: 24px;
        border-radius: 12px;
        background: #f0f0f0;
        position: relative;
        overflow: hidden;
    }

    .accuracy-fill {
        height: 100%;
        border-radius: 12px;
        transition: width 0.6s ease;
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        object-fit: cover;
    }

    .status-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
    }

    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
        background: #f8f9fa;
        border-radius: 12px;
        border: 2px dashed #dee2e6;
    }

    .empty-state-icon {
        font-size: 4rem;
        color: #adb5bd;
        margin-bottom: 1.5rem;
    }

    .question-type-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
        transform: translateX(5px);
    }

    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 2px;
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        transform: scale(1.1);
    }

    .progress-thin {
        height: 6px;
        border-radius: 3px;
        margin-top: 5px;
    }

    .student-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .avatar-placeholder {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-5">
        <div class="mb-3 mb-md-0">
            <div class="d-flex align-items-center mb-2">
                <h1 class="h3 mb-0 text-gray-800 me-3">Hasil Ujian</h1>
                <span class="badge bg-{{ $exam->status === 'active' ? 'success' : 'secondary' }} fs-6">
                    {{ $exam->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            <h2 class="h4 text-gray-600 mb-2">{{ $exam->title }}</h2>
            <div class="d-flex flex-wrap gap-3">
                <div class="d-flex align-items-center text-muted">
                    <i class="fas fa-book me-2"></i>
                    <span>{{ $exam->subject->name_subject ?? 'Mata Pelajaran' }}</span>
                </div>
                <div class="d-flex align-items-center text-muted">
                    <i class="fas fa-users me-2"></i>
                    <span>{{ $exam->class->name_class ?? 'Kelas' }}</span>
                </div>
                <div class="d-flex align-items-center text-muted">
                    <i class="fas fa-clock me-2"></i>
                    <span>{{ $exam->duration }} menit</span>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <button onclick="window.print()" class="btn btn-outline-primary d-flex align-items-center">
                <i class="fas fa-print me-2"></i> Cetak
            </button>
            <a href="{{ route('guru.exams.results.export', ['exam' => $exam->id, 'format' => 'pdf']) }}"
               class="btn btn-danger d-flex align-items-center">
                <i class="fas fa-file-pdf me-2"></i> Export PDF
            </a>
            <a href="{{ route('guru.exams.results.question-analysis', $exam->id) }}"
               class="btn btn-info d-flex align-items-center">
                <i class="fas fa-chart-bar me-2"></i> Analisis Soal
            </a>
            <a href="{{ route('guru.exams.index') }}" class="btn btn-secondary d-flex align-items-center">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Statistik Utama -->
    <div class="row mb-5">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-primary">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <div class="text-sm text-muted mb-1">Total Siswa</div>
                            <div class="h3 fw-bold mb-0">{{ $totalStudents }}</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress progress-thin">
                            <div class="progress-bar bg-primary" style="width: {{ $totalAttempts > 0 ? ($totalAttempts / $totalStudents) * 100 : 0 }}%"></div>
                        </div>
                        <small class="text-muted">{{ $totalAttempts }} siswa telah mengerjakan</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-success">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <div class="text-sm text-muted mb-1">Rata-rata Nilai</div>
                            <div class="h3 fw-bold mb-0">{{ number_format($avgScore, 1) }}</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between small text-muted">
                            <span>Min: {{ $attempts->min('final_score') ? number_format($attempts->min('final_score'), 1) : '0' }}</span>
                            <span>Max: {{ $attempts->max('final_score') ? number_format($attempts->max('final_score'), 1) : '0' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-info">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <div class="text-sm text-muted mb-1">Kelulusan</div>
                            <div class="h3 fw-bold mb-0">
                                {{ $totalAttempts > 0 ? round(($attempts->where('final_score', '>=', $exam->min_pass_grade ?? 60)->count() / $totalAttempts) * 100, 0) : 0 }}%
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between">
                            <span class="badge bg-success">{{ $attempts->where('final_score', '>=', $exam->min_pass_grade ?? 60)->count() }} Lulus</span>
                            <span class="badge bg-warning">{{ $attempts->where('final_score', '<', $exam->min_pass_grade ?? 60)->count() }} Tidak Lulus</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-left-warning">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div>
                            <div class="text-sm text-muted mb-1">Total Soal</div>
                            <div class="h3 fw-bold mb-0">{{ $questions->count() }}</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        @php
                            $pgCount = $questions->where('type', 'PG')->count();
                            $essayCount = $questions->count() - $pgCount;
                        @endphp
                        <div class="d-flex justify-content-between">
                            <span class="badge bg-info">{{ $pgCount }} PG</span>
                            <span class="badge bg-warning">{{ $essayCount }} Essay</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribusi Nilai & Analisis Soal -->
    <div class="row mb-5">
        <!-- Distribusi Nilai -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Distribusi Nilai
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach(['A' => '≥ 85', 'B' => '75-84', 'C' => '65-74', 'D' => '55-64', 'E' => '< 55'] as $grade => $range)
                        @php
                            $count = $scoreDistribution[$grade] ?? 0;
                            $percentage = $totalAttempts > 0 ? ($count / $totalAttempts) * 100 : 0;
                            $colors = [
                                'A' => ['bg' => 'bg-success', 'text' => 'text-success'],
                                'B' => ['bg' => 'bg-info', 'text' => 'text-info'],
                                'C' => ['bg' => 'bg-primary', 'text' => 'text-primary'],
                                'D' => ['bg' => 'bg-warning', 'text' => 'text-warning'],
                                'E' => ['bg' => 'bg-danger', 'text' => 'text-danger'],
                            ];
                        @endphp
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="grade-badge {{ $colors[$grade]['bg'] }} me-3">
                                        {{ $grade }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $range }}</div>
                                        <small class="text-muted">{{ $count }} siswa</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold fs-5 {{ $colors[$grade]['text'] }}">{{ number_format($percentage, 1) }}%</div>
                                </div>
                            </div>
                            <div class="accuracy-bar">
                                <div class="accuracy-fill {{ $colors[$grade]['bg'] }}" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Analisis Soal Terbaik & Terburuk -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-star me-2"></i>Analisis Soal
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Soal Terbaik -->
                        <div class="col-md-6 mb-4 mb-md-0">
                            <h6 class="fw-bold text-success mb-3">
                                <i class="fas fa-trophy me-2"></i>Soal Terbaik
                            </h6>
                            @php
                                $bestQuestion = $questions->sortByDesc('accuracy')->first();
                            @endphp
                            @if($bestQuestion && $bestQuestion->accuracy > 0)
                            <div class="p-3 border rounded bg-success bg-opacity-5">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-success question-type-badge">
                                        {{ $bestQuestion->type === 'PG' ? 'PG' : 'ESSAY' }}
                                    </span>
                                    <span class="fw-bold text-success fs-5">{{ number_format($bestQuestion->accuracy, 0) }}%</span>
                                </div>
                                <div class="text-truncate" style="max-height: 60px; overflow: hidden;">
                                    {!! Str::limit(strip_tags($bestQuestion->question), 80) !!}
                                </div>
                                <div class="mt-2 text-muted small">
                                    {{ $bestQuestion->answers_count }}/{{ $totalAttempts }} menjawab
                                </div>
                            </div>
                            @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <p class="mb-0">Belum ada data</p>
                            </div>
                            @endif
                        </div>

                        <!-- Soal Terburuk -->
                        <div class="col-md-6">
                            <h6 class="fw-bold text-danger mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>Soal Terburuk
                            </h6>
                            @php
                                $worstQuestion = $questions->where('answers_count', '>', 0)->sortBy('accuracy')->first();
                            @endphp
                            @if($worstQuestion && $worstQuestion->accuracy >= 0)
                            <div class="p-3 border rounded bg-danger bg-opacity-5">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-danger question-type-badge">
                                        {{ $worstQuestion->type === 'PG' ? 'PG' : 'ESSAY' }}
                                    </span>
                                    <span class="fw-bold text-danger fs-5">{{ number_format($worstQuestion->accuracy, 0) }}%</span>
                                </div>
                                <div class="text-truncate" style="max-height: 60px; overflow: hidden;">
                                    {!! Str::limit(strip_tags($worstQuestion->question), 80) !!}
                                </div>
                                <div class="mt-2 text-muted small">
                                    {{ $worstQuestion->answers_count }}/{{ $totalAttempts }} menjawab
                                </div>
                            </div>
                            @else
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <p class="mb-0">Belum ada data</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analisis Per Soal -->
    <div class="card shadow-sm mb-5">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold text-primary">
                <i class="fas fa-list-check me-2"></i>Analisis Per Soal
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%" class="ps-4">No</th>
                            <th width="45%">Pertanyaan</th>
                            <th width="10%" class="text-center">Tipe</th>
                            <th width="10%" class="text-center">Skor</th>
                            <th width="15%" class="text-center">Dijawab</th>
                            <th width="15%" class="text-center">Akurasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($questions as $index => $question)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $index + 1 }}</td>
                            <td>
                                <div class="text-truncate" style="max-width: 400px;"
                                     title="{{ strip_tags($question->question) }}">
                                    {!! Str::limit(strip_tags($question->question), 100) !!}
                                </div>
                            </td>
                            <td class="text-center">
                                @if($question->type === 'PG')
                                    <span class="badge bg-info">PG</span>
                                @else
                                    <span class="badge bg-warning">Essay</span>
                                @endif
                            </td>
                            <td class="text-center fw-bold">{{ $question->score }}</td>
                            <td class="text-center">
                                {{ $question->answers_count }}/{{ $totalAttempts }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 me-3">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar
                                                @if($question->accuracy >= 70) bg-success
                                                @elseif($question->accuracy >= 40) bg-warning
                                                @else bg-danger @endif"
                                                role="progressbar"
                                                style="width: {{ $question->accuracy }}%"
                                                aria-valuenow="{{ $question->accuracy }}"
                                                aria-valuemin="0"
                                                aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="fw-bold {{ $question->accuracy >= 70 ? 'text-success' : ($question->accuracy >= 40 ? 'text-warning' : 'text-danger') }}">
                                        {{ $question->accuracy }}%
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Daftar Hasil Siswa -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <h5 class="mb-3 mb-md-0 fw-bold text-primary">
                    <i class="fas fa-graduation-cap me-2"></i>Hasil Ujian Siswa
                </h5>
                <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-md-auto">
                    <div class="input-group input-group-sm" style="max-width: 250px;">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Cari nama atau NIS...">
                    </div>
                    <button class="btn btn-sm btn-outline-primary" onclick="sortTable()">
                        <i class="fas fa-sort-amount-down me-1"></i> Urutkan Nilai
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="resultsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="ps-4">No</th>
                            <th width="25%">Siswa</th>
                            <th width="10%" class="text-center">Nilai</th>
                            <th width="10%" class="text-center">Grade</th>
                            <th width="15%" class="text-center">Jawaban Benar</th>
                            <th width="15%" class="text-center">Status</th>
                            <th width="10%" class="text-center">Waktu</th>
                            <th width="10%" class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attempts as $index => $attempt)
                        @php
                            // PERBAIKAN DI SINI: Gunakan optional() untuk menghindari error jika user null
                            $student = $attempt->student;
                            $user = optional($student)->user;
                            $studentName = $user ? $user->name : 'Siswa Tidak Ditemukan';
                            $studentNis = $student->nis ?? '-';
                            $avatarUrl = $user && $user->profile_photo_url ? $user->profile_photo_url : asset('images/default-avatar.png');

                            $grade = 'E';
                            if ($attempt->final_score >= 85) $grade = 'A';
                            elseif ($attempt->final_score >= 75) $grade = 'B';
                            elseif ($attempt->final_score >= 65) $grade = 'C';
                            elseif ($attempt->final_score >= 55) $grade = 'D';

                            $timeElapsed = $attempt->getTimeElapsed();
                            $minutes = floor($timeElapsed / 60);
                            $seconds = $timeElapsed % 60;
                            $timeFormatted = sprintf('%02d:%02d', $minutes, $seconds);

                            $gradeColors = [
                                'A' => ['bg' => 'bg-success', 'text' => 'text-success'],
                                'B' => ['bg' => 'bg-info', 'text' => 'text-info'],
                                'C' => ['bg' => 'bg-primary', 'text' => 'text-primary'],
                                'D' => ['bg' => 'bg-warning', 'text' => 'text-warning'],
                                'E' => ['bg' => 'bg-danger', 'text' => 'text-danger'],
                            ];

                            $scoreColors = [
                                'A' => 'text-success',
                                'B' => 'text-info',
                                'C' => 'text-primary',
                                'D' => 'text-warning',
                                'E' => 'text-danger',
                            ];
                        @endphp
                        <tr>
                            <td class="ps-4 fw-bold">{{ $index + 1 }}</td>
                            <td>
                                <div class="student-info">
                                    @if($user && $user->profile_photo_url)
                                    <img src="{{ $avatarUrl }}"
                                         class="student-avatar"
                                         alt="{{ $studentName }}"
                                         onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                                    @else
                                    <div class="avatar-placeholder">
                                        {{ strtoupper(substr($studentName, 0, 1)) }}
                                    </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $studentName }}</div>
                                        <small class="text-muted">NIS: {{ $studentNis }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold fs-5 {{ $scoreColors[$grade] }}">
                                    {{ number_format($attempt->final_score, 1) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $gradeColors[$grade]['bg'] }} px-3 py-2 fs-6">
                                    {{ $grade }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="fw-semibold">
                                    {{ $attempt->answers->where('is_correct', true)->count() }}/{{ $exam->questions()->count() }}
                                </div>
                                <small class="text-muted">
                                    {{ $exam->questions()->count() > 0 ? round(($attempt->answers->where('is_correct', true)->count() / $exam->questions()->count()) * 100, 0) : 0 }}%
                                </small>
                            </td>
                            <td class="text-center">
                                @if($attempt->is_cheating_detected)
                                <span class="badge bg-danger py-2">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Kecurangan
                                </span>
                                @elseif($attempt->violation_count > 0)
                                <span class="badge bg-warning py-2">
                                    <i class="fas fa-exclamation-circle me-1"></i> Pelanggaran
                                </span>
                                @else
                                <span class="badge bg-success py-2">
                                    <i class="fas fa-check me-1"></i> Normal
                                </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="fw-semibold">{{ $timeFormatted }}</div>
                                <small class="text-muted">menit</small>
                            </td>
                            <td class="text-center pe-4">
                                <div class="d-flex justify-content-center">
                                    <a href="{{ route('guru.exams.results.show', ['exam' => $exam->id, 'attempt' => $attempt->id]) }}"
                                       class="action-btn btn btn-sm btn-info" title="Detail Hasil">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($student)
                                    <a href="{{ route('guru.exams.results.by-student', ['exam' => $exam->id, 'student' => $student->id]) }}"
                                       class="action-btn btn btn-sm btn-secondary ms-2" title="Riwayat Siswa">
                                        <i class="fas fa-history"></i>
                                    </a>
                                    @endif
                                    <button class="action-btn btn btn-sm btn-warning ms-2"
                                            onclick="regradeAttempt({{ $exam->id }}, {{ $attempt->id }})"
                                            title="Koreksi Ulang">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($attempts->isEmpty())
            <div class="empty-state mt-4">
                <div class="empty-state-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <h3 class="mt-3">Belum Ada Hasil Ujian</h3>
                <p class="text-muted mb-4">Siswa belum mengerjakan ujian ini.</p>
                <a href="{{ route('guru.exams.show', $exam->id) }}" class="btn btn-primary">
                    <i class="fas fa-eye me-2"></i> Lihat Detail Ujian
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal untuk Koreksi Ulang -->
<div class="modal fade" id="regradeModal" tabindex="-1" role="dialog" aria-labelledby="regradeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="regradeModalLabel">
                    <i class="fas fa-redo me-2"></i>Koreksi Ulang Ujian
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5 class="fw-bold mb-2">Konfirmasi Koreksi Ulang</h5>
                </div>
                <p>Apakah Anda yakin ingin melakukan koreksi ulang otomatis untuk ujian ini?</p>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    Sistem akan melakukan penilaian ulang untuk soal pilihan ganda. Nilai essay tidak akan berubah.
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Batal
                </button>
                <button type="button" class="btn btn-warning" id="confirmRegrade">
                    <i class="fas fa-redo me-2"></i>Ya, Koreksi Ulang
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi pencarian
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#resultsTable tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    // Fungsi pengurutan
    let sortDirection = true;
    function sortTable() {
        const table = document.getElementById('resultsTable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const scoreA = parseFloat(a.cells[2].textContent);
            const scoreB = parseFloat(b.cells[2].textContent);

            return sortDirection ? scoreB - scoreA : scoreA - scoreB;
        });

        // Hapus dan tambah kembali rows yang sudah diurutkan
        rows.forEach(row => {
            tbody.removeChild(row);
            tbody.appendChild(row);
        });

        // Update nomor urut
        const newRows = tbody.querySelectorAll('tr');
        newRows.forEach((row, index) => {
            row.cells[0].textContent = index + 1;
        });

        sortDirection = !sortDirection;

        // Tampilkan pesan sorting
        const sortMessage = sortDirection ? ' (Tertinggi ke Terendah)' : ' (Terendah ke Tertinggi)';
        alert('Tabel diurutkan berdasarkan nilai' + sortMessage);
    }

    // Fungsi koreksi ulang
    let currentExamId, currentAttemptId;
    function regradeAttempt(examId, attemptId) {
        currentExamId = examId;
        currentAttemptId = attemptId;
        const modal = new bootstrap.Modal(document.getElementById('regradeModal'));
        modal.show();
    }

    document.getElementById('confirmRegrade').addEventListener('click', function() {
        const btn = this;
        const originalText = btn.innerHTML;

        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
        btn.disabled = true;

        fetch(`/guru/exams/${currentExamId}/results/${currentAttemptId}/regrade`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Tutup modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('regradeModal'));
                modal.hide();

                // Tampilkan notifikasi sukses
                showNotification('success', 'Koreksi ulang berhasil!', 'Nilai telah diperbarui.');

                // Reload halaman setelah 2 detik
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                throw new Error(data.message || 'Gagal melakukan koreksi ulang');
            }
        })
        .catch(error => {
            showNotification('error', 'Terjadi Kesalahan', error.message);
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });

    // Fungsi untuk menampilkan notifikasi
    function showNotification(type, title, message) {
        // Buat elemen notifikasi
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 350px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;

        notification.innerHTML = `
            <strong>${title}</strong>
            <p class="mb-0">${message}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Tambahkan ke body
        document.body.appendChild(notification);

        // Hapus otomatis setelah 5 detik
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

    // Fungsi untuk menangani error gambar
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('error', function() {
                this.src = '{{ asset('images/default-avatar.png') }}';
            });
        });
    });
</script>
@endsection
