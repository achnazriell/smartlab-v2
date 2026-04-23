@extends('layouts.appTeacher')

@section('content')
    <div class="space-y-6">

        {{-- ===== PAGE HEADER ===== --}}
        <div class="bg-gradient-to-r from-blue-700 to-blue-500 rounded-2xl p-8 text-white shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Manajemen Tugas</h1>
                    <p class="text-blue-100 mt-1 text-sm">Kelola semua tugas yang Anda buat</p>
                </div>
                <a href="{{ route('tasks.create') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white text-blue-700 font-bold rounded-xl shadow-md hover:bg-blue-50 transition text-sm flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Tugas Baru
                </a>
            </div>

            {{-- FILTER ROW --}}
            <form method="GET" action="{{ route('tasks.index') }}" class="mt-6 flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/60" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                    </svg>
                    <input type="text" name="search" placeholder="Cari judul tugas..." value="{{ request('search') }}"
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/60 text-sm outline-none focus:bg-white/20 focus:border-white/40 transition">
                </div>

                {{-- ✅ PERBAIKAN: gunakan $kelas (collection objek) untuk filter kelas --}}
                <select name="class_id"
                    class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                    <option value="" class="text-slate-800">Semua Kelas</option>
                    @foreach ($kelas ?? [] as $k)
                        <option value="{{ $k->id }}" class="text-slate-800" @selected(request('class_id') == $k->id)>
                            {{ $k->name_class }}
                        </option>
                    @endforeach
                </select>

                {{-- ✅ PERBAIKAN: gunakan $mapelList (id + name) untuk filter mapel --}}
                <select name="mapel"
                    class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                    <option value="" class="text-slate-800">Semua Mapel</option>
                    @foreach ($mapelList ?? [] as $m)
                        <option value="{{ $m->id }}" class="text-slate-800" @selected(request('mapel') == $m->id)>
                            {{ $m->name }}
                        </option>
                    @endforeach
                </select>

                <select name="status"
                    class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                    <option value="" class="text-slate-800">Semua Status</option>
                    <option value="aktif" class="text-slate-800" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="berakhir" class="text-slate-800" @selected(request('status') === 'berakhir')>Berakhir</option>
                </select>

                <select name="sort"
                    class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                    <option value="terbaru" class="text-slate-800" @selected(request('sort', 'terbaru') === 'terbaru')>Terbaru</option>
                    <option value="terlama" class="text-slate-800" @selected(request('sort') === 'terlama')>Terlama</option>
                    <option value="deadline_asc" class="text-slate-800" @selected(request('sort') === 'deadline_asc')>Deadline Terdekat</option>
                    <option value="deadline_desc" class="text-slate-800" @selected(request('sort') === 'deadline_desc')>Deadline Terjauh</option>
                    <option value="judul_asc" class="text-slate-800" @selected(request('sort') === 'judul_asc')>Judul A-Z</option>
                </select>

                <button type="submit"
                    class="px-5 py-2.5 bg-white text-blue-700 font-semibold rounded-xl text-sm hover:bg-blue-50 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                    </svg>
                    Filter
                </button>
                @if (request()->hasAny(['search', 'class_id', 'mapel', 'status', 'sort']))
                    <a href="{{ route('tasks.index') }}"
                        class="px-4 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm hover:bg-white/20 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- ===== STATS ===== --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $allTasks     = $tasks ?? collect();
                $now          = now();
                $totalTasks   = $allTasks->total() ?? $allTasks->count();
                $aktifCount   = $allTasks->filter(fn($t) => $t->date_collection && \Carbon\Carbon::parse($t->date_collection)->isFuture())->count();
                $berakhirCount = $allTasks->filter(fn($t) => $t->date_collection && \Carbon\Carbon::parse($t->date_collection)->isPast())->count();
                $noDeadline   = $allTasks->filter(fn($t) => !$t->date_collection)->count();
                $taskStats = [
                    ['label' => 'Total Tugas',   'value' => $totalTasks,    'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color' => 'bg-blue-50 text-blue-600'],
                    ['label' => 'Aktif',          'value' => $aktifCount,    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                     'color' => 'bg-emerald-50 text-emerald-600'],
                    ['label' => 'Berakhir',       'value' => $berakhirCount, 'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                 'color' => 'bg-rose-50 text-rose-500'],
                    ['label' => 'Tanpa Deadline', 'value' => $noDeadline,   'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                        'color' => 'bg-amber-50 text-amber-500'],
                ];
            @endphp
            @foreach ($taskStats as $s)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex items-center gap-4">
                    <div class="p-3 {{ $s['color'] }} rounded-xl flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $s['icon'] }}" />
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
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- ===== TABLE CARD ===== --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Daftar Tugas</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Menampilkan <span class="font-semibold text-blue-600">{{ $tasks->total() ?? $tasks->count() }}</span> tugas
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider w-12">No</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Judul Tugas</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">Mata Pelajaran</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden lg:table-cell">Kelas</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">Deadline</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">Status</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($tasks as $index => $task)
                            @php
                                $offset    = ($tasks->currentPage() - 1) * $tasks->perPage();
                                $deadline  = $task->date_collection ? \Carbon\Carbon::parse($task->date_collection) : null;
                                $isExpired = $deadline && $deadline->isPast();
                            @endphp
                            <tr class="hover:bg-blue-50/40 transition-colors">
                                <td class="px-5 py-4 text-slate-500 font-medium">{{ $offset + $index + 1 }}</td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $task->title_task }}</p>
                                    @if ($task->description_task)
                                        <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $task->description_task }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 hidden md:table-cell">
                                    {{-- ✅ PERBAIKAN: gunakan subject->name_subject (bukan ->name) --}}
                                    <span class="text-xs px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg font-medium">
                                        {{ $task->subject->name_subject ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 hidden lg:table-cell">
                                    @foreach ($task->classes ?? [] as $c)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 mr-1">
                                            {{ $c->name_class }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="px-5 py-4 hidden md:table-cell text-slate-600 text-xs">
                                    <span class="text-orange-600 font-medium">
                                        {{ \Carbon\Carbon::parse($task->date_collection)->translatedFormat('d M Y') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 hidden md:table-cell">
                                    @if ($isExpired)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-500">Berakhir</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-100 text-emerald-700">Aktif</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="{{ route('collections.byTask', $task->id) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-semibold transition"
                                            title="Lihat Pengumpulan">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                            </svg>
                                            Pengumpulan
                                        </a>
                                        <a href="{{ route('tasks.edit', $task->id) }}"
                                            class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST"
                                            class="inline" onsubmit="return confirm('Hapus tugas ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-14 text-center">
                                    <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-semibold text-slate-700">Belum Ada Tugas</h3>
                                    <p class="text-xs text-slate-400 mt-1">Mulai buat tugas pertama Anda</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($tasks->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                    {{ $tasks->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>

    </div>
@endsection
