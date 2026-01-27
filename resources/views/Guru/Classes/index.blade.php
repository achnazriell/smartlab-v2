@extends('layouts.appTeacher')

@section('content')
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-800 font-display tracking-tight">Kelas yang Saya Ajar</h1>
                <p class="text-slate-600 mt-2">Daftar semua kelas yang Anda ajar</p>
            </div>
            <a href="{{ route('homeguru') }}"
                class="px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-colors">
                ← Kembali ke Dashboard
            </a>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-md border">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-xl mr-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total Kelas</p>
                        <p class="text-2xl font-bold text-slate-800">{{ $kelasData->count() }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-md border">
                <div class="flex items-center">
                    <div class="p-3 bg-emerald-100 rounded-xl mr-4">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total Siswa</p>
                        <p class="text-2xl font-bold text-slate-800">{{ $kelasData->sum('jumlah_siswa') }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-md border">
                <div class="flex items-center">
                    <div class="p-3 bg-amber-100 rounded-xl mr-4">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Total Mata Pelajaran</p>
                        <p class="text-2xl font-bold text-slate-800">
                            {{ $kelasData->flatMap(fn($k) => $k['mapel'])->unique()->count() }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-md border">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-xl mr-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Rata-rata Siswa/Kelas</p>
                        <p class="text-2xl font-bold text-slate-800">
                            @if ($kelasData->count() > 0)
                                {{ number_format($kelasData->avg('jumlah_siswa'), 1) }}
                            @else
                                0
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kelas Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($kelasData as $kelas)
                <div class="bg-white rounded-2xl shadow-md border hover:shadow-lg transition-shadow overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center">
                                <div
                                    class="w-12 h-12 bg-gradient-to-br from-blue-600 to-blue-700 rounded-xl flex items-center justify-center text-white font-bold text-lg mr-4">
                                    @php
                                        // Ekstrak tingkat kelas (X, XI, XII)
                                        preg_match('/^(XII|XI|X)/i', $kelas['kelas'], $match);
                                        $tingkat = strtoupper($match[1] ?? substr($kelas['kelas'], 0, 1));
                                    @endphp
                                    {{ $tingkat }}
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-slate-800">{{ $kelas['kelas'] }}</h3>
                                    <p class="text-sm text-slate-500">{{ $kelas['jumlah_siswa'] }} siswa</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <p class="text-sm font-medium text-slate-700 mb-2">Mata Pelajaran:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($kelas['mapel'] as $subject)
                                    <span class="inline-block bg-blue-50 text-blue-700 px-3 py-1 rounded-lg text-sm">
                                        {{ $subject }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-4 border-t">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-slate-500">
                                    {{ count($kelas['mapel']) }} mapel
                                </span>
                                <a href="{{ route('class.students', ['kelas' => $kelas['kelas']]) }}"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                    Lihat Siswa →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($kelasData->isEmpty())
            <div class="text-center py-12">
                <div class="w-24 h-24 mx-auto mb-6 text-slate-400">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-xl font-medium text-slate-700 mb-2">Belum ada kelas</h3>
                <p class="text-slate-500">Anda belum ditugaskan mengajar di kelas manapun.</p>
            </div>
        @endif
    </div>
@endsection
