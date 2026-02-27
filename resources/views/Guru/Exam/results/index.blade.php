{{-- resources/views/Guru/Exam/results/index.blade.php --}}
@extends('layouts.appTeacher')

@section('title', 'Hasil Ujian: ' . $exam->title)

@section('content')
<div class="min-h-screen bg-[#f4f6fb] py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ===== HEADER ===== --}}
        <div class="mb-7 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
            <div>
                <a href="{{ route('guru.exams.show', $exam->id) }}"
                    class="inline-flex items-center gap-1.5 text-indigo-600 text-sm font-semibold mb-3 hover:text-indigo-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Detail Ujian
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 leading-tight">{{ $exam->title }}</h1>
                <div class="flex items-center gap-2 mt-1 flex-wrap">
                    <span class="text-sm text-slate-500">{{ $exam->subject->name_subject ?? '-' }}</span>
                    <span class="text-slate-300">•</span>
                    <span class="text-sm text-slate-500">{{ $exam->class->name_class ?? '-' }}</span>
                    <span class="text-slate-300">•</span>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full
                        {{ $exam->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $exam->status === 'active' ? 'bg-emerald-500' : 'bg-slate-400' }} inline-block"></span>
                        {{ ucfirst($exam->status ?? 'closed') }}
                    </span>
                </div>
            </div>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('guru.exams.results.export', $exam->id) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export Hasil
                </a>
            </div>
        </div>

        {{-- ===== STATISTIK CARDS ===== --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-7">
            @php
                $statCards = [
                    ['label' => 'Total Siswa', 'value' => $totalStudents, 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color' => 'text-slate-700', 'iconBg' => 'bg-slate-100', 'iconColor' => 'text-slate-500'],
                    ['label' => 'Sudah Ikut', 'value' => $uniqueParticipants, 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color' => 'text-indigo-700', 'iconBg' => 'bg-indigo-100', 'iconColor' => 'text-indigo-500'],
                    ['label' => 'Rata-rata Nilai', 'value' => number_format($avgScore, 1), 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'color' => 'text-blue-700', 'iconBg' => 'bg-blue-100', 'iconColor' => 'text-blue-500'],
                    ['label' => 'Nilai Tertinggi', 'value' => number_format($maxScore, 1), 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', 'color' => 'text-emerald-700', 'iconBg' => 'bg-emerald-100', 'iconColor' => 'text-emerald-500'],
                    ['label' => 'Belum Ikut', 'value' => $belumIkut, 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'text-rose-700', 'iconBg' => 'bg-rose-100', 'iconColor' => 'text-rose-400'],
                ];
            @endphp
            @foreach($statCards as $card)
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex flex-col gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $card['iconBg'] }} flex items-center justify-center">
                        <svg class="w-5 h-5 {{ $card['iconColor'] }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">{{ $card['label'] }}</p>
                        <p class="text-2xl font-extrabold {{ $card['color'] }} mt-0.5">{{ $card['value'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ===== DISTRIBUSI NILAI ===== --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-7">
            <h2 class="text-base font-bold text-slate-800 mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Distribusi Nilai
            </h2>
            <div class="grid grid-cols-5 gap-3">
                @php
                    $gradeConfig = [
                        'A' => ['range' => '≥ 85', 'bar' => 'bg-emerald-500', 'badge' => 'bg-emerald-100 text-emerald-800', 'label' => 'Sangat Baik'],
                        'B' => ['range' => '75–84', 'bar' => 'bg-blue-500', 'badge' => 'bg-blue-100 text-blue-800', 'label' => 'Baik'],
                        'C' => ['range' => '65–74', 'bar' => 'bg-yellow-500', 'badge' => 'bg-yellow-100 text-yellow-800', 'label' => 'Cukup'],
                        'D' => ['range' => '55–64', 'bar' => 'bg-orange-500', 'badge' => 'bg-orange-100 text-orange-800', 'label' => 'Kurang'],
                        'E' => ['range' => '< 55', 'bar' => 'bg-red-500', 'badge' => 'bg-red-100 text-red-800', 'label' => 'Sangat Kurang'],
                    ];
                @endphp
                @foreach($gradeConfig as $grade => $cfg)
                    @php
                        $count = $scoreDistribution[$grade] ?? 0;
                        $percent = $totalAttempts > 0 ? round(($count / $totalAttempts) * 100, 1) : 0;
                    @endphp
                    <div class="flex flex-col items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $cfg['badge'] }}">{{ $grade }}</span>
                        <div class="relative w-full flex flex-col items-center" style="height: 80px;">
                            <div class="w-full flex-1 flex items-end justify-center">
                                <div class="{{ $cfg['bar'] }} rounded-t-lg w-3/4 transition-all duration-500"
                                    style="height: {{ max(4, $percent * 0.8) }}px; min-height: 4px;"></div>
                            </div>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-extrabold text-slate-800">{{ $count }}</p>
                            <p class="text-xs text-slate-400">{{ $percent }}%</p>
                            <p class="text-xs text-slate-400 leading-tight hidden md:block">{{ $cfg['range'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ===== DAFTAR PESERTA ===== --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-7">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Daftar Peserta
                    <span class="ml-1 text-xs font-semibold px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded-full">{{ $totalAttempts }}</span>
                </h2>
                {{-- Filter/Search --}}
                <div class="flex items-center gap-2">
                    <input type="text" id="searchInput" placeholder="Cari nama / NIS..."
                        class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300 w-48">
                    <select id="gradeFilter" class="text-sm border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        <option value="">Semua Grade</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full" id="participantsTable">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wide w-10">No</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wide">Siswa</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wide cursor-pointer hover:text-slate-600" onclick="sortTable('score')">
                                Nilai <span class="sort-icon" data-col="score">↕</span>
                            </th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wide">Grade</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wide">Durasi</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wide">Waktu</th>
                            <th class="px-5 py-3 text-left text-xs font-bold text-slate-400 uppercase tracking-wide">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-bold text-slate-400 uppercase tracking-wide">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50" id="participantsBody">
                        @forelse($attempts->sortByDesc('final_score') as $index => $attempt)
                            @php
                                $fs = $attempt->final_score ?? 0;
                                $grade = $fs >= 85 ? 'A' : ($fs >= 75 ? 'B' : ($fs >= 65 ? 'C' : ($fs >= 55 ? 'D' : 'E')));
                                $gradeColor = ['A'=>'emerald','B'=>'blue','C'=>'yellow','D'=>'orange','E'=>'red'][$grade];
                                $timeElapsed = $attempt->getTimeElapsed();
                                $minutes = floor($timeElapsed / 60);
                                $seconds = $timeElapsed % 60;
                                $isTimeout = $attempt->status === 'timeout';
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors participant-row"
                                data-name="{{ strtolower($attempt->student_data->name ?? '') }}"
                                data-nis="{{ $attempt->student_data->nis ?? '' }}"
                                data-grade="{{ $grade }}">
                                <td class="px-5 py-4 text-sm text-slate-400 font-medium">{{ $index + 1 }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                            <span class="text-indigo-700 font-bold text-sm">{{ strtoupper(substr($attempt->student_data->name ?? 'S', 0, 1)) }}</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800">{{ $attempt->student_data->name ?? 'Siswa' }}</p>
                                            <p class="text-xs text-slate-400">{{ $attempt->student_data->nis ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-20 bg-slate-100 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full bg-{{ $gradeColor }}-500"
                                                style="width: {{ min(100, $fs) }}%"></div>
                                        </div>
                                        <span class="text-sm font-bold text-{{ $gradeColor }}-700">{{ number_format($fs, 1) }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                        bg-{{ $gradeColor }}-100 text-{{ $gradeColor }}-800">
                                        {{ $grade }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600 font-mono">
                                    {{ sprintf('%02d:%02d', $minutes, $seconds) }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-500">
                                    @if($attempt->ended_at)
                                        {{ $attempt->ended_at->format('H:i') }}
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($isTimeout)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Timeout
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-full">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            Selesai
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('guru.exams.results.show', [$exam->id, $attempt->id]) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition"
                                            title="Lihat Detail & Koreksi">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Detail
                                        </a>
                                        <a href="{{ route('guru.exams.results.by-student', [$exam->id, $attempt->student_id]) }}"
                                            class="inline-flex items-center p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-100 transition"
                                            title="Riwayat Siswa">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3 text-slate-400">
                                        <svg class="w-14 h-14 text-slate-200" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        <p class="font-semibold text-slate-500">Belum ada siswa yang mengumpulkan ujian</p>
                                        <p class="text-sm">Hasil akan muncul setelah siswa menyelesaikan ujian.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attempts->count() === 0)
                {{-- empty state handled above --}}
            @else
                <div class="px-6 py-3 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                    <p class="text-xs text-slate-400">
                        Menampilkan <span id="visibleCount">{{ $attempts->count() }}</span> dari {{ $attempts->count() }} peserta
                    </p>
                </div>
            @endif
        </div>

        {{-- ===== ANALISIS SOAL RINGKAS ===== --}}
        @if($questions->count() > 0)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Analisis Per Soal
                    <span class="text-xs font-semibold px-2 py-0.5 bg-slate-100 text-slate-500 rounded-full">{{ $questions->count() }} soal</span>
                </h2>
                <a href="{{ route('guru.exams.results.question-analysis', $exam->id) }}"
                    class="text-indigo-600 text-sm font-semibold hover:underline flex items-center gap-1">
                    Lihat lengkap
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div class="space-y-3">
                @foreach($questions as $q)
                    @php
                        $acc = $q->accuracy ?? 0;
                        $accColor = $acc >= 70 ? 'emerald' : ($acc >= 50 ? 'yellow' : 'red');
                        $typeLabels = ['PG'=>'PG','ES'=>'Esai','IS'=>'Isian','BS'=>'B/S','PGK'=>'PGK','DD'=>'DD','SK'=>'Skala','MJ'=>'Jodoh'];
                    @endphp
                    <div class="flex items-center gap-4 p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition">
                        <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold flex items-center justify-center flex-shrink-0">
                            {{ $loop->iteration }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-700 truncate">{{ Str::limit(strip_tags($q->question), 80) }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-slate-400">{{ $q->answers_count }} dijawab</span>
                                <span class="text-xs bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded font-medium">{{ $typeLabels[$q->type] ?? $q->type }}</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <div class="w-24">
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-{{ $accColor }}-500" style="width: {{ $acc }}%"></div>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-{{ $accColor }}-600 w-12 text-right">{{ $acc }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

<script>
    // Search & filter
    const searchInput = document.getElementById('searchInput');
    const gradeFilter = document.getElementById('gradeFilter');
    const rows = document.querySelectorAll('.participant-row');
    const visibleCount = document.getElementById('visibleCount');

    function filterTable() {
        const searchVal = searchInput.value.toLowerCase();
        const gradeVal = gradeFilter.value;
        let visible = 0;

        rows.forEach(row => {
            const name = row.dataset.name || '';
            const nis = row.dataset.nis || '';
            const grade = row.dataset.grade || '';
            const matchSearch = name.includes(searchVal) || nis.includes(searchVal);
            const matchGrade = !gradeVal || grade === gradeVal;
            if (matchSearch && matchGrade) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        if (visibleCount) visibleCount.textContent = visible;
    }

    searchInput?.addEventListener('input', filterTable);
    gradeFilter?.addEventListener('change', filterTable);
</script>
@endsection
