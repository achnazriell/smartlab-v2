@extends('layouts.appTeacher')

@section('content')
    <div class="space-y-8">
        <!-- Welcome Section -->
        <div
            class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-blue-600 to-blue-800 p-8 lg:p-12 text-white shadow-2xl shadow-blue-200">
            <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-96 h-96 bg-white/10 rounded-full blur-3xl">
            </div>
            <div
                class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/4 w-64 h-64 bg-blue-400/20 rounded-full blur-2xl">
            </div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                @php
                    $namaDepan = explode(' ', trim(Auth::user()->name))[0];
                @endphp

                <div class="max-w-2xl">
                    <h1 class="text-3xl lg:text-4xl font-semibold mb-4 font-display tracking-tight">
                        Selamat datang kembali,
                        @php
                            $nip = $teacher->nip ?? '';
                            $sapaan = 'Guru';
                            if ($nip) {
                                // Logika sederhana: jika digit terakhir genap => Bu, ganjil => Pak
                                $lastDigit = substr($nip, -1);
                                if (is_numeric($lastDigit)) {
                                    $sapaan = $lastDigit % 2 == 0 ? 'Bu' : 'Pak';
                                }
                            }
                        @endphp
                        {{ $sapaan }}
                        {{ $namaDepan }}! 👋
                    </h1>
                </div>

                <div class="flex flex-shrink-0">
                    <a href="{{ route('tasks.create') }}"
                        class="px-8 py-4 bg-white text-blue-700 font-bold rounded-2xl shadow-xl">
                        Buat Tugas Baru
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
                class="card-modern p-6 group rounded-xl bg-white shadow-md hover:border-blue-300 transition-all duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div
                        class="p-4 bg-blue-50 text-blue-600 rounded-2xl group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">Total Siswa Aktif</p>
                <p class="text-4xl font-semibold text-slate-900 mt-2 font-display tracking-tight">{{ $totalSiswa }}</p>
            </div>

            <div
                class="card-modern p-6 group rounded-xl bg-white shadow-md hover:border-amber-300 transition-all duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div
                        class="p-4 bg-amber-50 text-amber-600 rounded-2xl group-hover:bg-amber-500 group-hover:text-white transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">
                    Tugas Berjalan
                </p>

                @if ($tugasBerjalan === null)
                    <p class="text-sm text-slate-400 italic mt-2">
                        Fitur belum tersedia
                    </p>
                @else
                    <p class="text-4xl font-semibold text-slate-900 mt-2">
                        {{ $tugasBerjalan }}
                    </p>
                @endif
            </div>

            <div
                class="card-modern p-6 group rounded-xl bg-white shadow-md hover:border-emerald-300 transition-all duration-300">
                <div class="flex items-center justify-between mb-6">
                    <div
                        class="p-4 bg-emerald-50 text-emerald-600 rounded-2xl group-hover:bg-emerald-500 group-hover:text-white transition-all duration-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">
                    Selesai Dinilai
                </p>

                @if ($tugasDinilai === null)
                    <p class="text-sm text-slate-400 italic mt-2">
                        Belum ada data penilaian
                    </p>
                @else
                    <p class="text-4xl font-semibold text-slate-900 mt-2">
                        {{ $tugasDinilai }}
                    </p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Classes Section -->
            <div class="lg:col-span-3 space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-slate-800 font-display tracking-tight">Kelas Saya</h2>
                    <a href="{{ route('class.index') }}"
                        class="text-sm font-bold text-blue-600 hover:text-blue-700 flex items-center group">
                        Lihat Semua Kelas
                        <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Class Card -->
                    @foreach ($kelasData as $kelas)
                        <div
                            class="card-modern p-6 rounded-xl bg-white shadow-md hover:border-blue-400 transition-all group relative">
                            <div class="flex justify-between items-start mb-6">
                                @php
                                    $kelasText = is_array($kelas) ? $kelas['kelas'] ?? '' : $kelas->kelas ?? '';
                                    preg_match('/^(XII|XI|X)/i', $kelasText, $match);
                                @endphp

                                <div
                                    class="w-14 h-14 bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl flex items-center justify-center text-white font-semibold text-xl">
                                    {{ strtoupper($match[1] ?? '-') }}
                                </div>

                                <span class="text-xs font-bold px-3 py-1 bg-white border rounded-xl">
                                    {{ $kelas['jumlah_siswa'] }} Siswa
                                </span>
                            </div>

                            <h3 class="text-xl font-bold text-slate-800 mb-1">
                                {{ $kelas['kelas'] }}
                            </h3>

                            <p class="text-sm italic text-slate-500 mb-6">
                                @foreach ($kelas['mapel'] as $subject)
                                    <span
                                        class="inline-block bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs mr-1 mb-1">
                                        {{ $subject }}
                                    </span>
                                @endforeach
                            </p>

                            <!-- Tombol Lihat Siswa -->
                            <div class="flex justify-end mt-4 pt-4 border-t">
                                <a href="{{ route('class.students', ['kelas' => $kelas['kelas']]) }}"
                                    class="flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 group">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Lihat Siswa
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
