@extends('layouts.appTeacher')

@section('content')
<div class="space-y-6">

    {{-- ===== PAGE HEADER ===== --}}
    <div class="bg-gradient-to-r from-blue-700 to-blue-500 rounded-2xl p-8 text-white shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Kelas yang Saya Ajar</h1>
                <p class="text-blue-100 mt-1 text-sm">Daftar semua kelas yang Anda ampu</p>
            </div>
        </div>

        {{-- FILTER ROW --}}
        <form method="GET" action="{{ route('class.index') }}" class="mt-6 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input type="text" name="search" placeholder="Cari nama kelas..." value="{{ request('search') }}"
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/60 text-sm outline-none focus:bg-white/20 focus:border-white/40 transition">
            </div>
            <select name="tingkat"
                class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                <option value="" class="text-slate-800">Semua Tingkat</option>
                <option value="X"   class="text-slate-800" @selected(request('tingkat') === 'X')>Kelas X</option>
                <option value="XI"  class="text-slate-800" @selected(request('tingkat') === 'XI')>Kelas XI</option>
                <option value="XII" class="text-slate-800" @selected(request('tingkat') === 'XII')>Kelas XII</option>
            </select>
            <select name="sort"
                class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                <option value="" class="text-slate-800">Urutkan</option>
                <option value="nama_asc"  class="text-slate-800" @selected(request('sort') === 'nama_asc')>Nama A-Z</option>
                <option value="nama_desc" class="text-slate-800" @selected(request('sort') === 'nama_desc')>Nama Z-A</option>
                <option value="siswa_desc" class="text-slate-800" @selected(request('sort') === 'siswa_desc')>Siswa Terbanyak</option>
                <option value="siswa_asc"  class="text-slate-800" @selected(request('sort') === 'siswa_asc')>Siswa Tersedikit</option>
            </select>
            <button type="submit"
                class="px-5 py-2.5 bg-white text-blue-700 font-semibold rounded-xl text-sm hover:bg-blue-50 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Filter
            </button>
            @if (request()->hasAny(['search', 'tingkat', 'sort']))
            <a href="{{ route('class.index') }}"
               class="px-4 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm hover:bg-white/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- ===== STATS STRIP ===== --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $stats = [
                ['label' => 'Total Kelas',          'value' => $kelasData->count(),                                  'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color' => 'bg-blue-50 text-blue-600'],
                ['label' => 'Total Siswa',           'value' => $kelasData->sum('jumlah_siswa'),                      'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color' => 'bg-emerald-50 text-emerald-600'],
                ['label' => 'Total Mapel',           'value' => $kelasData->flatMap(fn($k) => $k['mapel'])->unique()->count(), 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'color' => 'bg-amber-50 text-amber-600'],
                ['label' => 'Rata-rata Siswa/Kelas', 'value' => $kelasData->count() > 0 ? number_format($kelasData->avg('jumlah_siswa'), 1) : '0', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'color' => 'bg-violet-50 text-violet-600'],
            ];
        @endphp
        @foreach ($stats as $s)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center gap-4">
            <div class="p-3 {{ $s['color'] }} rounded-xl flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $s['icon'] }}"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">{{ $s['label'] }}</p>
                <p class="text-2xl font-bold text-slate-800 mt-0.5">{{ $s['value'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ===== KELAS GRID ===== --}}
    @if ($kelasData->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm text-center py-16">
        <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-slate-700">Belum ada kelas</h3>
        <p class="text-sm text-slate-400 mt-1">Anda belum ditugaskan mengajar di kelas manapun.</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($kelasData as $kelas)
        @php
            preg_match('/^(XII|XI|X)/i', $kelas['kelas'], $match);
            $tingkat = strtoupper($match[1] ?? substr($kelas['kelas'], 0, 1));
            $gradients = [
                'X'   => 'from-blue-600 to-blue-700',
                'XI'  => 'from-violet-600 to-violet-700',
                'XII' => 'from-teal-600 to-emerald-700',
            ];
            $grad = $gradients[$tingkat] ?? 'from-blue-600 to-blue-700';
        @endphp
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-blue-200 transition-all overflow-hidden group">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-13 h-13 min-w-[3.25rem] w-13 h-13 bg-gradient-to-br {{ $grad }} rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-inner">
                        {{ $tingkat }}
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-lg font-bold text-slate-800 truncate">{{ $kelas['kelas'] }}</h3>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-xs text-slate-500">{{ $kelas['jumlah_siswa'] }} siswa</span>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Mata Pelajaran</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($kelas['mapel'] as $subject)
                        <span class="text-xs px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg font-medium">{{ $subject }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400">{{ count($kelas['mapel']) }} mapel</span>
                    <a href="{{ route('class.students', ['kelas' => $kelas['kelas']]) }}"
                       class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-700 group-hover:gap-2.5 transition-all">
                        Lihat Siswa
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
