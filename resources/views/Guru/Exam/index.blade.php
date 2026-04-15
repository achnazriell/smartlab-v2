@extends('layouts.appTeacher')

@section('content')
<div class="space-y-6">

    {{-- ===== PAGE HEADER ===== --}}
    <div class="bg-gradient-to-r from-blue-700 to-blue-500 rounded-2xl p-8 text-white shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Manajemen Ujian</h1>
                <p class="text-blue-100 mt-1 text-sm">Kelola semua ujian yang Anda buat</p>
            </div>
            <a href="{{ route('guru.exams.create') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-white text-blue-700 font-bold rounded-xl shadow-md hover:bg-blue-50 transition text-sm flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Buat Ujian Baru
            </a>
        </div>

        {{-- FILTER ROW --}}
        {{--
            Controller filter params: search, status, type
            $classes  = collection of Class models  → name_class
            $mapels   = collection of Subject models → name_subject
        --}}
        <form method="GET" action="{{ route('guru.exams.index') }}" class="mt-6 flex flex-col sm:flex-row gap-3 flex-wrap">
            <div class="relative flex-1 min-w-[180px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input type="text" name="search" placeholder="Cari judul ujian, mapel, kelas..." value="{{ request('search') }}"
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/60 text-sm outline-none focus:bg-white/20 focus:border-white/40 transition">
            </div>

            {{-- Filter Kelas ($classes dari controller) --}}
            <select name="kelas"
                class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                <option value="" class="text-slate-800">Semua Kelas</option>
                @foreach ($classes ?? [] as $kls)
                <option value="{{ $kls->name_class }}" class="text-slate-800"
                    @selected(request('kelas') === $kls->name_class)>
                    {{ $kls->name_class }}
                </option>
                @endforeach
            </select>

            {{-- Filter Mapel ($mapels dari controller) --}}
            <select name="mapel"
                class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                <option value="" class="text-slate-800">Semua Mapel</option>
                @foreach ($mapels ?? [] as $m)
                <option value="{{ $m->id }}" class="text-slate-800"
                    @selected(request('mapel') == $m->id)>
                    {{ $m->name_subject }}
                </option>
                @endforeach
            </select>

            {{-- Filter Jenis/Type (field: type di model Exam) --}}
            <select name="type"
                class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                <option value="" class="text-slate-800">Semua Jenis</option>
                <option value="UH"      class="text-slate-800" @selected(request('type') === 'UH')>Ulangan Harian</option>
                <option value="UTS"     class="text-slate-800" @selected(request('type') === 'UTS')>UTS</option>
                <option value="UAS"     class="text-slate-800" @selected(request('type') === 'UAS')>UAS</option>
                <option value="LAINNYA" class="text-slate-800" @selected(request('type') === 'LAINNYA')>Lainnya</option>
            </select>

            {{-- Filter Status (field: status di model Exam = 'active'|'draft') --}}
            <select name="status"
                class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                <option value="" class="text-slate-800">Semua Status</option>
                <option value="active" class="text-slate-800" @selected(request('status') === 'active')>Aktif</option>
                <option value="draft"  class="text-slate-800" @selected(request('status') === 'draft')>Draft</option>
            </select>

            <button type="submit"
                class="px-5 py-2.5 bg-white text-blue-700 font-semibold rounded-xl text-sm hover:bg-blue-50 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Filter
            </button>
            @if (request()->hasAny(['search', 'kelas', 'mapel', 'type', 'status']))
            <a href="{{ route('guru.exams.index') }}"
               class="px-4 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm hover:bg-white/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- ===== STATS ===== --}}
    {{--
        $total  = total semua ujian (integer dari controller)
        $active = jumlah status 'active' (integer dari controller)
        $draft  = jumlah status 'draft' (integer dari controller)
    --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $finished = max(0, $total - $active - $draft);
            $examStats = [
                ['label' => 'Total Ujian',  'value' => $total,    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'color' => 'bg-blue-50 text-blue-600'],
                ['label' => 'Ujian Aktif',  'value' => $active,   'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                                                    'color' => 'bg-emerald-50 text-emerald-600'],
                ['label' => 'Draft',        'value' => $draft,    'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',                                                          'color' => 'bg-amber-50 text-amber-500'],
                ['label' => 'Selesai',      'value' => $finished, 'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                                                    'color' => 'bg-slate-100 text-slate-500'],
            ];
        @endphp
        @foreach ($examStats as $s)
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

    {{-- ===== ALERTS ===== --}}
    @if (session('success'))
    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 px-5 py-3.5 rounded-xl flex items-center gap-3 text-sm">
        <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-5 py-3.5 rounded-xl flex items-center gap-3 text-sm">
        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 001.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    {{-- ===== TABLE CARD ===== --}}
    {{-- $exams = LengthAwarePaginator dari controller --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800">Daftar Ujian</h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Menampilkan <span class="font-semibold text-blue-600">{{ $exams->total() }}</span> ujian
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider w-12">No</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Judul Ujian</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">Mapel / Jenis</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden lg:table-cell">Kelas</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">Soal / Durasi</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">Waktu Ujian</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">Status</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($exams as $index => $exam)
                    @php
                        $offset = ($exams->currentPage() - 1) * $exams->perPage();

                        // Field waktu di model Exam: start_at & end_at
                        $startTime  = $exam->start_at ? \Carbon\Carbon::parse($exam->start_at) : null;
                        $endTime    = $exam->end_at   ? \Carbon\Carbon::parse($exam->end_at)   : null;
                        $isActive   = $exam->status === 'active';
                        $isDraft    = $exam->status === 'draft';
                        $isExpired  = $endTime && $endTime->isPast() && $isActive;
                        $isFinished = $exam->status === 'finished' || $isExpired;
                        $isRunning  = $isActive && !$isFinished && $startTime && $endTime
                                      && now()->between($startTime, $endTime);
                        $isUpcoming = $isActive && !$isFinished && $startTime && $startTime->isFuture();

                        // Jumlah soal dari withCount('questions') di controller
                        $soalCount = $exam->questions_count ?? 0;

                        // Jenis badge (field: type di Exam)
                        $typeLabel = match($exam->type) {
                            'UH'      => ['label' => 'Ulangan Harian', 'color' => 'bg-blue-100 text-blue-700'],
                            'UTS'     => ['label' => 'UTS',            'color' => 'bg-amber-100 text-amber-700'],
                            'UAS'     => ['label' => 'UAS',            'color' => 'bg-rose-100 text-rose-700'],
                            'LAINNYA' => ['label' => $exam->custom_type ?? 'Lainnya', 'color' => 'bg-slate-100 text-slate-600'],
                            default   => ['label' => $exam->type,      'color' => 'bg-slate-100 text-slate-600'],
                        };
                    @endphp
                    <tr class="hover:bg-blue-50/40 transition-colors">

                        {{-- No --}}
                        <td class="px-5 py-4 text-slate-500 font-medium">{{ $offset + $index + 1 }}</td>

                        {{-- Judul --}}
                        <td class="px-5 py-4">
                            <p class="font-semibold text-slate-800">{{ $exam->title }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Dibuat {{ $exam->created_at?->format('d M Y') }}
                            </p>
                        </td>

                        {{-- Mapel / Jenis --}}
                        <td class="px-5 py-4 hidden md:table-cell">
                            <p class="text-xs font-semibold text-slate-700">
                                {{ $exam->subject->name_subject ?? '—' }}
                            </p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold {{ $typeLabel['color'] }} mt-1">
                                {{ $typeLabel['label'] }}
                            </span>
                        </td>

                        {{-- Kelas (relasi singular: $exam->class) --}}
                        <td class="px-5 py-4 hidden lg:table-cell">
                            @if ($exam->class)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700">
                                {{ $exam->class->name_class }}
                            </span>
                            @else
                            <span class="text-slate-400 text-xs">—</span>
                            @endif
                        </td>

                        {{-- Soal / Durasi --}}
                        <td class="px-5 py-4 hidden md:table-cell">
                            <p class="text-sm font-bold {{ $soalCount > 0 ? 'text-slate-700' : 'text-amber-500' }}">
                                {{ $soalCount }}
                                <span class="text-xs font-normal text-slate-400">soal</span>
                                @if($soalCount == 0)
                                <span class="text-xs font-normal text-amber-400 block">belum ada soal</span>
                                @endif
                            </p>
                            @if ($exam->duration)
                            <p class="text-xs text-slate-400 mt-0.5">{{ $exam->duration }} menit</p>
                            @endif
                        </td>

                        {{-- Waktu Ujian --}}
                        <td class="px-5 py-4 hidden md:table-cell text-xs text-slate-600">
                            @if ($startTime && $endTime)
                            <p>{{ $startTime->format('d M Y H:i') }}</p>
                            <p class="text-slate-400">s/d {{ $endTime->format('d M Y H:i') }}</p>
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        {{-- Status (berdasarkan field status + waktu) --}}
                        <td class="px-5 py-4 hidden md:table-cell">
                            @if ($isDraft)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-500">
                                    Draft
                                </span>
                            @elseif ($isRunning)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Berlangsung
                                </span>
                            @elseif ($isUpcoming)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-100 text-amber-600">
                                    Terjadwal
                                </span>
                            @elseif ($isExpired)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-500">
                                    Selesai
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-700">
                                    Aktif
                                </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('guru.exams.show', $exam->id) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-semibold transition"
                                   title="Lihat Detail">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Detail
                                </a>
                                @if(!$isFinished)
                                    <a href="{{ route('guru.exams.edit', $exam->id) }}"
                                       class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('guru.exams.destroy', $exam->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Hapus ujian ini? Semua data soal dan hasil akan ikut terhapus.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <span class="p-1.5 text-slate-300 cursor-not-allowed" title="Ujian selesai tidak dapat diedit atau dihapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-14 text-center">
                            <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-slate-700">Belum Ada Ujian</h3>
                            <p class="text-xs text-slate-400 mt-1">Mulai buat ujian pertama Anda</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($exams->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $exams->links('vendor.pagination.tailwind') }}
        </div>
        @endif
    </div>

</div>
@endsection
