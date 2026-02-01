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
        background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 20px rgba(0, 0, 0, 0.12);
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
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .accuracy-bar {
        height: 24px;
        border-radius: 12px;
        background: #e8f0f8;
        position: relative;
        overflow: hidden;
    }

    .accuracy-fill {
        height: 100%;
        border-radius: 12px;
        transition: width 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        object-fit: cover;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
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
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        border: 2px dashed #dee2e6;
    }

    .empty-state-icon {
        font-size: 4rem;
        color: #adb5bd;
        margin-bottom: 1.5rem;
    }

    .question-type-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }

    .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
        transform: translateX(5px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
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
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    /* Modal Styles */
    .modal-dialog {
        border-radius: 12px;
    }

    .modal-content {
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        border-radius: 12px;
    }

    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0;
        border: none;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
    }

    .modal-body {
        padding: 1.5rem;
    }

    @media (max-width: 768px) {
        .stat-card { margin-bottom: 1rem; }
    }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 px-4 py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Hasil Ujian: {{ $exam->title }}</h1>
            <p class="text-slate-600">Lihat dan analisis hasil ujian {{ $exam->title }}</p>
        </div>

        <!-- Action Buttons -->
        <div class="mb-6 flex flex-wrap gap-3">
            <button type="button" class="inline-flex items-center px-4 py-2 bg-white border-2 border-blue-500 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors font-medium" data-bs-toggle="modal" data-bs-target="#printModal">
                <i class="fas fa-print mr-2"></i> Cetak
            </button>
            <button type="button" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="fas fa-file-pdf mr-2"></i> Export PDF
            </button>
            <a href="{{ route('guru.exams.results.question-analysis', $exam->id) }}"
               class="inline-flex items-center px-4 py-2 bg-cyan-600 text-white rounded-lg hover:bg-cyan-700 transition-colors font-medium">
                <i class="fas fa-chart-bar mr-2"></i> Analisis Soal
            </a>
            <a href="{{ route('guru.exams.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors font-medium">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="stat-card bg-white rounded-xl shadow-md hover:shadow-lg transition-all hover:-translate-y-1 p-6 border border-slate-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-slate-600 text-sm font-medium">Total Siswa</p>
                        <h3 class="text-2xl font-bold text-slate-900">{{ $totalStudents }}</h3>
                    </div>
                </div>
            </div>
            <div class="stat-card bg-white rounded-xl shadow-md hover:shadow-lg transition-all hover:-translate-y-1 p-6 border border-slate-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-slate-600 text-sm font-medium">Selesai</p>
                        <h3 class="text-2xl font-bold text-slate-900">{{ $completedAttempts }}</h3>
                    </div>
                </div>
            </div>
            <div class="stat-card bg-white rounded-xl shadow-md hover:shadow-lg transition-all hover:-translate-y-1 p-6 border border-slate-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-14 h-14 bg-amber-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-amber-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-slate-600 text-sm font-medium">Rata-rata Nilai</p>
                        <h3 class="text-2xl font-bold text-slate-900">{{ number_format($averageScore, 1) }}</h3>
                    </div>
                </div>
            </div>
            <div class="stat-card bg-white rounded-xl shadow-md hover:shadow-lg transition-all hover:-translate-y-1 p-6 border border-slate-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0 w-14 h-14 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-star text-indigo-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-slate-600 text-sm font-medium">Nilai Tertinggi</p>
                        <h3 class="text-2xl font-bold text-slate-900">{{ $maxScore }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribusi Nilai & Analisis Soal -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Distribusi Nilai -->
            <div class="bg-white rounded-xl shadow-md border border-slate-100">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900">
                        <i class="fas fa-chart-pie mr-2 text-blue-600"></i>Distribusi Nilai
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach(['A' => '≥ 85', 'B' => '75-84', 'C' => '65-74', 'D' => '55-64', 'E' => '< 55'] as $grade => $range)
                        @php
                            $count = $scoreDistribution[$grade] ?? 0;
                            $percentage = $totalAttempts > 0 ? ($count / $totalAttempts) * 100 : 0;
                            $colors = [
                                'A' => ['bg' => 'bg-green-500', 'badge' => 'bg-green-100 text-green-700'],
                                'B' => ['bg' => 'bg-blue-500', 'badge' => 'bg-blue-100 text-blue-700'],
                                'C' => ['bg' => 'bg-indigo-500', 'badge' => 'bg-indigo-100 text-indigo-700'],
                                'D' => ['bg' => 'bg-amber-500', 'badge' => 'bg-amber-100 text-amber-700'],
                                'E' => ['bg' => 'bg-red-500', 'badge' => 'bg-red-100 text-red-700'],
                            ];
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg {{ $colors[$grade]['badge'] }} flex items-center justify-center font-bold">
                                        {{ $grade }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-900">{{ $range }}</div>
                                        <small class="text-slate-600">{{ $count }} siswa</small>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-lg text-slate-900">{{ number_format($percentage, 1) }}%</div>
                                </div>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                <div class="h-full {{ $colors[$grade]['bg'] }} rounded-full transition-all duration-500" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Analisis Soal Terbaik & Terburuk -->
            <div class="bg-white rounded-xl shadow-md border border-slate-100">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900">
                        <i class="fas fa-star mr-2 text-amber-500"></i>Analisis Soal
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Soal Terbaik -->
                        <div>
                            <h4 class="font-bold text-green-600 mb-3">
                                <i class="fas fa-trophy mr-2"></i>Soal Terbaik
                            </h4>
                            @php
                                $bestQuestion = $questions->sortByDesc('accuracy')->first();
                            @endphp
                            @if($bestQuestion && $bestQuestion->accuracy > 0)
                            <div class="p-4 border-l-4 border-green-500 rounded-lg bg-green-50">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-semibold">
                                        {{ $bestQuestion->type === 'PG' ? 'PG' : 'ESSAY' }}
                                    </span>
                                    <span class="font-bold text-lg text-green-600">{{ number_format($bestQuestion->accuracy, 0) }}%</span>
                                </div>
                                <p class="text-sm text-slate-700 line-clamp-3 mb-2">
                                    {!! Str::limit(strip_tags($bestQuestion->question), 80) !!}
                                </p>
                                <p class="text-xs text-slate-600">
                                    {{ $bestQuestion->answers_count }}/{{ $totalAttempts }} menjawab
                                </p>
                            </div>
                            @else
                            <div class="text-center py-6 text-slate-500">
                                <i class="fas fa-info-circle text-3xl mb-2 block"></i>
                                <p>Belum ada data</p>
                            </div>
                            @endif
                        </div>

                        <!-- Soal Terburuk -->
                        <div>
                            <h4 class="font-bold text-red-600 mb-3">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Soal Terburuk
                            </h4>
                            @php
                                $worstQuestion = $questions->where('answers_count', '>', 0)->sortBy('accuracy')->first();
                            @endphp
                            @if($worstQuestion && $worstQuestion->accuracy >= 0)
                            <div class="p-4 border-l-4 border-red-500 rounded-lg bg-red-50">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="inline-block px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-semibold">
                                        {{ $worstQuestion->type === 'PG' ? 'PG' : 'ESSAY' }}
                                    </span>
                                    <span class="font-bold text-lg text-red-600">{{ number_format($worstQuestion->accuracy, 0) }}%</span>
                                </div>
                                <p class="text-sm text-slate-700 line-clamp-3 mb-2">
                                    {!! Str::limit(strip_tags($worstQuestion->question), 80) !!}
                                </p>
                                <p class="text-xs text-slate-600">
                                    {{ $worstQuestion->answers_count }}/{{ $totalAttempts }} menjawab
                                </p>
                            </div>
                            @else
                            <div class="text-center py-6 text-slate-500">
                                <i class="fas fa-info-circle text-3xl mb-2 block"></i>
                                <p>Belum ada data</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analisis Per Soal -->
    <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">
                <i class="fas fa-list-check mr-2 text-blue-600"></i>Analisis Per Soal
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left px-6 py-3 font-semibold text-slate-700 text-sm">No</th>
                        <th class="text-left px-6 py-3 font-semibold text-slate-700 text-sm">Pertanyaan</th>
                        <th class="text-center px-6 py-3 font-semibold text-slate-700 text-sm">Tipe</th>
                        <th class="text-center px-6 py-3 font-semibold text-slate-700 text-sm">Skor</th>
                        <th class="text-center px-6 py-3 font-semibold text-slate-700 text-sm">Dijawab</th>
                        <th class="text-center px-6 py-3 font-semibold text-slate-700 text-sm">Akurasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($questions as $index => $question)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 text-sm text-slate-700 max-w-md truncate" title="{{ strip_tags($question->question) }}">
                            {!! Str::limit(strip_tags($question->question), 80) !!}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($question->type === 'PG')
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">PG</span>
                            @else
                                <span class="inline-block px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-semibold">Essay</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-slate-900">{{ $question->score }}</td>
                        <td class="px-6 py-4 text-center text-sm text-slate-700">
                            {{ $question->answers_count }}/{{ $totalAttempts }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex-1 bg-slate-200 rounded-full h-2 overflow-hidden max-w-xs">
                                    <div class="h-full rounded-full transition-all duration-500
                                        @if($question->accuracy >= 70) bg-green-500
                                        @elseif($question->accuracy >= 40) bg-amber-500
                                        @else bg-red-500 @endif"
                                        style="width: {{ $question->accuracy }}%">
                                    </div>
                                </div>
                                <span class="font-bold text-sm min-w-max
                                    @if($question->accuracy >= 70) text-green-600
                                    @elseif($question->accuracy >= 40) text-amber-600
                                    @else text-red-600 @endif">
                                    {{ $question->accuracy }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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

<!-- Print Confirmation Modal -->
<div class="modal fade" id="printModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cetak Hasil Ujian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin mencetak hasil ujian ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="window.print(); bootstrap.Modal.getInstance(document.getElementById('printModal')).hide();">
                    Ya, Cetak
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Export PDF Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export ke PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>File PDF akan diunduh ke komputer Anda. Lanjutkan?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="window.location.href='{{ route('guru.exams.results.export', ['exam' => $exam->id, 'format' => 'pdf']) }}'; bootstrap.Modal.getInstance(document.getElementById('exportModal')).hide();">
                    Ya, Export
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
