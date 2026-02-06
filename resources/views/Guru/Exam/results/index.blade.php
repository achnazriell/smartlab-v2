@extends('layouts.appTeacher')

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Hasil Ujian: {{ $exam->title }}</h1>
                        <p class="text-gray-600 mt-1">{{ $exam->subject->name }} • Kelas {{ $exam->class->name_class }}</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('guru.exams.index') }}"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Kembali ke Daftar Ujian
                        </a>
                        <button onclick="window.print()"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Cetak Hasil
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistik Utama -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 mr-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Total Siswa</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalStudents }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 mr-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Mengerjakan</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalAttempts }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 mr-4">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Rata-rata Nilai</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($avgScore, 1) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 mr-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Tidak Mengerjakan</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalStudents - $totalAttempts }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Hasil Siswa -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Daftar Hasil Siswa</h2>
                    <p class="text-sm text-gray-600 mt-1">Kelas: {{ $exam->class->name_class }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nama Siswa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    NIS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Nilai</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Grade</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($attempts as $attempt)
                                @php
                                    // Gunakan student_data yang sudah disiapkan di controller
                                    $studentData = $attempt->student_data ?? null;
                                    $studentName = $studentData ? $studentData->name : 'Siswa Tidak Ditemukan';
                                    $studentEmail = $studentData ? $studentData->email : '-';
                                    $studentNis = $studentData ? $studentData->nis : '-';
                                    $avatarUrl = $studentData
                                        ? $studentData->profile_photo_url ?? asset('images/default-avatar.png')
                                        : asset('images/default-avatar.png');
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full" src="{{ $avatarUrl }}"
                                                    alt="{{ $studentName }}"
                                                    onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $studentName }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $studentEmail }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $studentNis }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
            {{ $attempt->status == 'submitted' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $attempt->status == 'submitted' ? 'Selesai' : 'Waktu Habis' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="text-lg font-bold text-gray-900">
                                            {{ number_format($attempt->final_score, 1) }}</div>
                                        @php
                                            $totalQuestionScore = $exam->questions()->sum('score');
                                        @endphp
                                        <div class="text-sm text-gray-500">Skor:
                                            {{ $attempt->score }}/{{ $totalQuestionScore }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $grade =
                                                $attempt->final_score >= 85
                                                    ? 'A'
                                                    : ($attempt->final_score >= 75
                                                        ? 'B'
                                                        : ($attempt->final_score >= 65
                                                            ? 'C'
                                                            : ($attempt->final_score >= 55
                                                                ? 'D'
                                                                : 'E')));

                                            $gradeColor = [
                                                'A' => 'bg-green-100 text-green-800',
                                                'B' => 'bg-blue-100 text-blue-800',
                                                'C' => 'bg-yellow-100 text-yellow-800',
                                                'D' => 'bg-orange-100 text-orange-800',
                                                'E' => 'bg-red-100 text-red-800',
                                            ][$grade];
                                        @endphp
                                        <span
                                            class="px-3 py-1 inline-flex text-sm font-semibold rounded-full {{ $gradeColor }}">
                                            {{ $grade }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('guru.exams.results.show', [$exam->id, $attempt->id]) }}"
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                            <i class="fas fa-eye mr-1"></i> Detail
                                        </a>
                                        @if ($studentData)
                                            <a href="{{ route('guru.exams.results.by-student', [$exam->id, $attempt->student_id]) }}"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                <i class="fas fa-history mr-1"></i> Riwayat
                                            </a>
                                        @endif
                                        <button onclick="resetAttempt({{ $attempt->id }})"
                                            class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-redo mr-1"></i> Reset
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        <p class="mt-2">Belum ada siswa yang mengerjakan ujian ini</p>
                                    </td>
                                </tr>
                            @endforelse`    
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Distribusi Nilai -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Nilai</h2>
                <div class="grid grid-cols-5 gap-4">
                    @foreach ($scoreDistribution as $grade => $count)
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 mb-1">{{ $count }}</div>
                            <div
                                class="text-sm font-medium
                        {{ $grade == 'A'
                            ? 'text-green-600'
                            : ($grade == 'B'
                                ? 'text-blue-600'
                                : ($grade == 'C'
                                    ? 'text-yellow-600'
                                    : ($grade == 'D'
                                        ? 'text-orange-600'
                                        : 'text-red-600'))) }}">
                                {{ $grade }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $totalAttempts > 0 ? round(($count / $totalAttempts) * 100, 1) : 0 }}%
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Analisis Soal -->
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Analisis Per Soal</h2>
                    <a href="{{ route('guru.exams.results.question-analysis', $exam->id) }}"
                        class="text-sm text-blue-600 hover:text-blue-800">
                        <i class="fas fa-chart-bar mr-1"></i> Analisis Detail
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Soal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tipe</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bobot</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dijawab</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Benar</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Akurasi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($questions as $question)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ Str::limit(strip_tags($question->question), 50) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span
                                            class="px-2 py-1 text-xs rounded-full
                                    {{ $question->type == 'PG' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $question->type == 'PG' ? 'Pilihan Ganda' : 'Essay' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $question->score }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $question->answers_count }}/{{ $totalAttempts }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $question->correct_answers_count }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 mr-3">
                                                <div class="bg-{{ $question->accuracy >= 70 ? 'green' : ($question->accuracy >= 40 ? 'yellow' : 'red') }}-600 h-2.5 rounded-full"
                                                    style="width: {{ min($question->accuracy, 100) }}%"></div>
                                            </div>
                                            <span
                                                class="font-medium {{ $question->accuracy >= 70 ? 'text-green-600' : ($question->accuracy >= 40 ? 'text-yellow-600' : 'text-red-600') }}">
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
        </div>
    </div>

    <script>
        function resetAttempt(attemptId) {
            if (confirm('Apakah Anda yakin ingin mereset attempt ini? Siswa akan dapat mengulang ujian.')) {
                fetch(`/guru/exams/{{ $exam->id }}/results/${attemptId}/reset`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Gagal mereset attempt: ' + data.message);
                        }
                    })
                    .catch(error => {
                        alert('Terjadi kesalahan: ' + error);
                    });
            }
        }
    </script>
@endsection
