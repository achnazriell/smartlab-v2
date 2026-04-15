@extends('layouts.appTeacher')

@section('content')
<div class="space-y-8">

    {{-- ===== WELCOME HERO ===== --}}
    {{-- ===== WELCOME HERO — style lama ===== --}}
    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-blue-600 to-blue-800 p-8 lg:p-12 text-white shadow-2xl shadow-blue-200">
        <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/4 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 translate-y-1/2 -translate-x-1/4 w-64 h-64 bg-blue-400/20 rounded-full blur-2xl"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
            @php
                $namaDepan = explode(' ', trim(Auth::user()->name))[0];
                $nip       = $teacher->nip ?? '';
                $sapaan    = 'Guru';
                if ($nip) {
                    $lastDigit = substr($nip, -1);
                    if (is_numeric($lastDigit)) {
                        $sapaan = ($lastDigit % 2 == 0) ? 'Bu' : 'Pak';
                    }
                }
                $jam   = (int) now()->format('H');
                $salam = $jam < 11 ? 'Selamat Pagi' : ($jam < 15 ? 'Selamat Siang' : ($jam < 18 ? 'Selamat Sore' : 'Selamat Malam'));
            @endphp

            <div class="max-w-2xl">
                <h1 class="text-3xl lg:text-4xl font-semibold mb-2 font-display tracking-tight">
                    Selamat datang kembali,
                    {{ $sapaan }} {{ $namaDepan }}!
                </h1>
                <p class="text-blue-200 text-sm mt-1">
                    {{ $salam }} &mdash; {{ now()->translatedFormat('l, d F Y') }}
                </p>
            </div>

            <div class="flex flex-shrink-0 gap-3">
                <a href="{{ route('tasks.create') }}"
                   class="px-8 py-4 bg-white text-blue-700 font-bold rounded-2xl shadow-xl hover:bg-blue-50 transition-colors">
                    Buat Tugas Baru
                </a>
            </div>
        </div>

        {{-- Strip stat mini di dalam hero --}}
        <div class="relative z-10 mt-8 grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $heroStats = [
                    ['label' => 'Kelas Aktif',    'value' => $kelasData->count()],
                    ['label' => 'Total Siswa',     'value' => $totalSiswa],
                    ['label' => 'Tugas Berjalan',  'value' => $tugasBerjalan ?? '—'],
                    ['label' => 'Selesai Dinilai', 'value' => $tugasDinilai  ?? '—'],
                ];
            @endphp
            @foreach ($heroStats as $s)
            <div class="bg-white/10 backdrop-blur-sm border border-white/10 rounded-2xl px-5 py-4">
                <p class="text-xs text-blue-200/70 uppercase tracking-wider font-medium">{{ $s['label'] }}</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $s['value'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
    {{-- ===== ACTIVITY SUMMARY ROW ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex items-start gap-4 hover:shadow-md transition-shadow">
            <div class="p-3 bg-violet-50 rounded-xl flex-shrink-0">
                <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Pengumpulan Hari Ini</p>
                <p class="text-3xl font-bold text-slate-800 mt-1">{{ $pengumpulanHariIni ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex items-start gap-4 hover:shadow-md transition-shadow">
            <div class="p-3 bg-rose-50 rounded-xl flex-shrink-0">
                <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Belum Dinilai</p>
                <p class="text-3xl font-bold text-slate-800 mt-1">{{ $belumDinilai ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex items-start gap-4 hover:shadow-md transition-shadow">
            <div class="p-3 bg-amber-50 rounded-xl flex-shrink-0">
                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Tugas Lewat Deadline</p>
                <p class="text-3xl font-bold text-slate-800 mt-1">{{ $tugasLewat ?? 0 }}</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex items-start gap-4 hover:shadow-md transition-shadow">
            <div class="p-3 bg-emerald-50 rounded-xl flex-shrink-0">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Kuis Aktif</p>
                <p class="text-3xl font-bold text-slate-800 mt-1">{{ $kuisAktif ?? 0 }}</p>
            </div>
        </div>
    </div>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        {{-- KELAS SAYA --}}
        <div class="xl:col-span-2 space-y-5">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">Kelas Saya</h2>
                <a href="{{ route('class.index') }}"
                   class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                    Lihat Semua
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            @if ($kelasData->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm text-center py-12">
                <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <p class="text-sm text-slate-400">Belum ada kelas yang ditugaskan</p>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($kelasData->take(2) as $kelas)
                @php
                    // $kelas adalah array PHP biasa dari controller
                    $kelasText = $kelas['kelas'];
                    preg_match('/^(XII|XI|X)/i', $kelasText, $match);
                    $tingkat = strtoupper($match[1] ?? substr($kelasText, 0, 2));
                    $colorMap = [
                        'X'   => ['bg' => 'from-blue-600 to-blue-700',    'badge' => 'bg-blue-50 text-blue-700'],
                        'XI'  => ['bg' => 'from-violet-600 to-violet-700', 'badge' => 'bg-violet-50 text-violet-700'],
                        'XII' => ['bg' => 'from-teal-600 to-emerald-700',  'badge' => 'bg-emerald-50 text-emerald-700'],
                    ];
                    $color    = $colorMap[$tingkat] ?? $colorMap['X'];
                    $mapelArr = is_array($kelas['mapel']) ? $kelas['mapel'] : [];
                @endphp
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 hover:shadow-md hover:border-blue-200 transition-all group">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br {{ $color['bg'] }} rounded-xl flex items-center justify-center text-white font-bold text-base flex-shrink-0">
                            {{ $tingkat }}
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate-800 text-base truncate">{{ $kelas['kelas'] }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $kelas['jumlah_siswa'] }} siswa</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-1.5 mb-4 min-h-[28px]">
                        @foreach (array_slice($mapelArr, 0, 3) as $subject)
                        <span class="text-xs px-2.5 py-1 rounded-lg {{ $color['badge'] }} font-medium">{{ $subject }}</span>
                        @endforeach
                        @if (count($mapelArr) > 3)
                        <span class="text-xs px-2.5 py-1 rounded-lg bg-slate-100 text-slate-500 font-medium">+{{ count($mapelArr) - 3 }} lagi</span>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-400">{{ count($mapelArr) }} mata pelajaran</span>
                        <a href="{{ route('class.students', ['kelas' => $kelas['kelas']]) }}"
                           class="text-xs font-semibold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1 group-hover:gap-2 transition-all">
                            Lihat Siswa
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- TUGAS TERBARU + AKSES CEPAT --}}
        <div class="space-y-5">
            <h2 class="text-xl font-bold text-slate-800 tracking-tight">Tugas Terbaru</h2>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                {{-- $tugasTerbaru = Collection Task dari controller --}}
                @forelse ($tugasTerbaru ?? [] as $tugas)
                @php
                    // Field deadline di model Task adalah date_collection
                    $deadline  = $tugas->date_collection
                        ? \Carbon\Carbon::parse($tugas->date_collection)
                        : null;
                    $isExpired = $deadline && $deadline->isPast();
                    $daysLeft  = $deadline ? (int) now()->diffInDays($deadline, false) : null;
                @endphp
                <div class="px-5 py-4 border-b border-slate-100 last:border-0 hover:bg-slate-50 transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-800 truncate">{{ $tugas->title_task }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $deadline ? $deadline->format('d M Y') : 'Tanpa deadline' }}
                            </p>
                        </div>

                        @if ($isExpired)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 font-medium whitespace-nowrap flex-shrink-0">Berakhir</span>
                        @elseif ($daysLeft !== null && $daysLeft <= 2)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-600 font-medium whitespace-nowrap flex-shrink-0">{{ $daysLeft }}h lagi</span>
                        @elseif ($daysLeft !== null)
                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-medium whitespace-nowrap flex-shrink-0">{{ $daysLeft }}h lagi</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-slate-400">
                    <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-sm">Belum ada tugas</p>
                </div>
                @endforelse

                <div class="px-5 py-3 bg-slate-50 border-t border-slate-100">
                    <a href="{{ route('tasks.index') }}"
                       class="text-xs font-semibold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1">
                        Lihat semua tugas
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
