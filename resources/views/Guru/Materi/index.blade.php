@extends('layouts.appTeacher')

@section('content')
    <div class="space-y-6">

        {{-- ===== PAGE HEADER ===== --}}
        <div class="bg-gradient-to-r from-blue-700 to-blue-500 rounded-2xl p-8 text-white shadow-lg">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Manajemen Materi</h1>
                    <p class="text-blue-100 mt-1 text-sm">Kelola semua materi pembelajaran yang Anda buat</p>
                </div>
                <a href="{{ route('materis.create') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white text-blue-700 font-bold rounded-xl shadow-md hover:bg-blue-50 transition text-sm flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Materi
                </a>
            </div>

            {{-- FILTER ROW --}}
            <form method="GET" action="{{ route('materis.index') }}"
                class="mt-6 flex flex-col sm:flex-row gap-3 flex-wrap">
                <div class="relative flex-1 min-w-[180px]">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/60" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                    </svg>
                    <input type="text" name="search" placeholder="Cari judul materi..." value="{{ request('search') }}"
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/60 text-sm outline-none focus:bg-white/20 focus:border-white/40 transition">
                </div>
                <select name="kelas"
                    class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                    <option value="" class="text-slate-800">Semua Kelas</option>
                    @foreach ($kelasList ?? [] as $k)
                        <option value="{{ $k }}" class="text-slate-800" @selected(request('kelas') === $k)>
                            {{ $k }}</option>
                    @endforeach
                </select>
                <select name="mapel"
                    class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                    <option value="" class="text-slate-800">Semua Mapel</option>
                    @foreach ($mapelList ?? [] as $m)
                        <option value="{{ $m->id }}" class="text-slate-800" @selected(request('mapel') == $m->id)>
                            {{ $m->name }}</option>
                    @endforeach
                </select>
                <select name="tipe"
                    class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                    <option value="" class="text-slate-800">Semua Tipe</option>
                    <option value="pdf" class="text-slate-800" @selected(request('tipe') === 'pdf')>PDF</option>
                    <option value="video" class="text-slate-800" @selected(request('tipe') === 'video')>Video</option>
                    <option value="link" class="text-slate-800" @selected(request('tipe') === 'link')>Link</option>
                    <option value="doc" class="text-slate-800" @selected(request('tipe') === 'doc')>Dokumen</option>
                </select>
                <select name="sort"
                    class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                    <option value="" class="text-slate-800">Urutkan</option>
                    <option value="terbaru" class="text-slate-800" @selected(request('sort') === 'terbaru')>Terbaru</option>
                    <option value="terlama" class="text-slate-800" @selected(request('sort') === 'terlama')>Terlama</option>
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
                @if (request()->hasAny(['search', 'kelas', 'mapel', 'tipe', 'sort']))
                    <a href="{{ route('materis.index') }}"
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
                $allMateri = $materis ?? collect();
                $total = method_exists($allMateri, 'total') ? $allMateri->total() : $allMateri->count();
                $pdfCount = $allMateri
                    ->filter(fn($m) => strtolower(pathinfo($m->file_materi ?? '', PATHINFO_EXTENSION)) === 'pdf')
                    ->count();
                $videoCount = $allMateri
                    ->filter(
                        fn($m) => in_array(strtolower(pathinfo($m->file_materi ?? '', PATHINFO_EXTENSION)), [
                            'mp4',
                            'webm',
                            'avi',
                            'mov',
                        ]),
                    )
                    ->count();
                $mapelCount = $allMateri->pluck('subject_id')->unique()->count();
                $statList = [
                    [
                        'label' => 'Total Materi',
                        'value' => $total,
                        'icon' =>
                            'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                        'color' => 'bg-blue-50 text-blue-600',
                    ],
                    [
                        'label' => 'Berkas PDF',
                        'value' => $pdfCount,
                        'icon' =>
                            'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                        'color' => 'bg-rose-50 text-rose-500',
                    ],
                    [
                        'label' => 'Video',
                        'value' => $videoCount,
                        'icon' =>
                            'M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
                        'color' => 'bg-violet-50 text-violet-600',
                    ],
                    [
                        'label' => 'Mata Pelajaran',
                        'value' => $mapelCount,
                        'icon' =>
                            'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                        'color' => 'bg-amber-50 text-amber-500',
                    ],
                ];
            @endphp
            @foreach ($statList as $s)
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
            <div
                class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 px-5 py-3.5 rounded-xl flex items-center gap-3 text-sm">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-5 py-3.5 rounded-xl text-sm">
                <div class="flex items-center gap-2 font-semibold mb-2">
                    <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 001.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                    Kesalahan Validasi:
                </div>
                <ul class="list-disc ml-7 space-y-0.5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ===== TABLE CARD ===== --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Daftar Materi</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Menampilkan <span
                        class="font-semibold text-blue-600">{{ method_exists($materis, 'total') ? $materis->total() : $materis->count() }}</span>
                    materi
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th
                                class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider w-12">
                                No</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Judul Materi</th>
                            <th
                                class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">
                                Mata Pelajaran</th>
                            <th
                                class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden lg:table-cell">
                                Kelas</th>
                            <th
                                class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">
                                Tipe</th>
                            <th
                                class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden lg:table-cell">
                                Diunggah</th>
                            <th
                                class="px-5 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($materis as $index => $materi)
                            @php
                                $offset = method_exists($materis, 'currentPage')
                                    ? ($materis->currentPage() - 1) * $materis->perPage()
                                    : 0;
                                $ext = strtolower(pathinfo($materi->file_materi ?? '', PATHINFO_EXTENSION));
                                $tipeConfig = match (true) {
                                    $ext === 'pdf' => [
                                        'label' => 'PDF',
                                        'color' => 'bg-rose-100 text-rose-700',
                                        'icon' =>
                                            'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                                    ],
                                    in_array($ext, ['mp4', 'webm', 'mov']) => [
                                        'label' => 'Video',
                                        'color' => 'bg-violet-100 text-violet-700',
                                        'icon' =>
                                            'M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
                                    ],
                                    in_array($ext, ['doc', 'docx']) => [
                                        'label' => 'Word',
                                        'color' => 'bg-blue-100 text-blue-700',
                                        'icon' =>
                                            'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                    ],
                                    in_array($ext, ['ppt', 'pptx']) => [
                                        'label' => 'PPT',
                                        'color' => 'bg-orange-100 text-orange-700',
                                        'icon' =>
                                            'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z',
                                    ],
                                    !empty($materi->link_materi) => [
                                        'label' => 'Link',
                                        'color' => 'bg-cyan-100 text-cyan-700',
                                        'icon' =>
                                            'M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14',
                                    ],
                                    default => [
                                        'label' => 'File',
                                        'color' => 'bg-slate-100 text-slate-600',
                                        'icon' =>
                                            'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                    ],
                                };
                            @endphp
                            <tr class="hover:bg-blue-50/40 transition-colors">
                                <td class="px-5 py-4 text-slate-500 font-medium">{{ $offset + $index + 1 }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 rounded-xl {{ $tipeConfig['color'] }} flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="{{ $tipeConfig['icon'] }}" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-800 truncate">{{ $materi->title_materi }}
                                            </p>
                                            @if ($materi->deskripsi ?? false)
                                                <p class="text-xs text-slate-400 truncate max-w-[220px]">
                                                    {{ $materi->deskripsi }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 hidden md:table-cell">
                                    <span class="text-xs px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg font-medium">
                                        {{ $materi->subject->name_subject ?? '—' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 hidden lg:table-cell">
                                    @foreach ($materi->classes ?? [] as $c)
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 mr-1 mb-1">
                                            {{ $c->name_class }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="px-5 py-4 hidden md:table-cell">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $tipeConfig['color'] }}">
                                        {{ $tipeConfig['label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 hidden lg:table-cell text-xs text-slate-500">
                                    {{ $materi->created_at ? $materi->created_at->format('d M Y') : '—' }}
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <button onclick="openModal('detailModal_{{ $materi->id }}')"
                                            class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                            title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <a href="{{ route('materis.edit', $materi->id) }}"
                                            class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg transition"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('materis.destroy', $materi->id) }}" method="POST"
                                            class="inline" onsubmit="return confirm('Hapus materi ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition"
                                                title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
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
                                    <div
                                        class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                        <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <h3 class="text-base font-semibold text-slate-700">Belum Ada Materi</h3>
                                    <p class="text-xs text-slate-400 mt-1">Mulai unggah materi pertama Anda</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (method_exists($materis, 'hasPages') && $materis->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
                    {{ $materis->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>

        {{-- ===== DETAIL MODALS ===== --}}
        @foreach ($materis as $materi)
            @php
                // ✅ Gunakan FileHelper::url() agar bekerja di Railway (tanpa symlink)
                $ext2 = \App\Helpers\FileHelper::extension($materi->file_materi);
                $fileUrl2 = \App\Helpers\FileHelper::url($materi->file_materi);
            @endphp
            <div id="detailModal_{{ $materi->id }}"
                class="fixed inset-0 flex items-center justify-center bg-black/50 hidden z-50 p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">

                    <div class="bg-gradient-to-r from-blue-700 to-blue-500 px-7 py-5 flex justify-between items-center">
                        <div>
                            <h5 class="text-xl font-bold text-white">Detail Materi</h5>
                            <p class="text-blue-200 text-sm mt-0.5 truncate max-w-sm">{{ $materi->title_materi }}</p>
                        </div>
                        <button onclick="closeModal('detailModal_{{ $materi->id }}')"
                            class="text-white/70 hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="overflow-y-auto flex-1 px-7 py-6 space-y-5">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Mata
                                    Pelajaran</p>
                                <p class="text-slate-800 font-semibold text-sm">
                                    {{ $materi->subject->name_subject ?? '—' }}</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Kelas</p>
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach ($materi->classes ?? [] as $c)
                                        <span
                                            class="px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-100 text-blue-800">{{ $c->name_class }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Diunggah</p>
                                <p class="text-slate-800 font-semibold text-sm">
                                    {{ $materi->created_at?->format('d M Y, H:i') ?? '—' }}</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Dilihat</p>
                                <p class="text-slate-800 font-semibold text-sm">{{ $materi->view_count ?? 0 }}x</p>
                            </div>
                        </div>

                        @if ($materi->deskripsi ?? false)
                            <div class="bg-slate-50 rounded-xl p-4">
                                <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-2">Deskripsi</p>
                                <p class="text-slate-700 text-sm leading-relaxed">{{ $materi->deskripsi }}</p>
                            </div>
                        @endif

                        <div>
                            <p class="text-sm font-semibold text-slate-700 mb-3">File / Konten Materi</p>

                            @if (!empty($materi->link_materi))
                                <a href="{{ $materi->link_materi }}" target="_blank"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-cyan-50 text-cyan-700 border border-cyan-200 rounded-xl text-sm font-semibold hover:bg-cyan-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    Buka Link Materi
                                </a>
                            @elseif ($fileUrl2)
                                {{-- ✅ Semua src/href pakai $fileUrl2 dari FileHelper::url() --}}
                                @if ($ext2 === 'pdf')
                                    <embed src="{{ $fileUrl2 }}" type="application/pdf"
                                        class="w-full h-80 border border-slate-200 rounded-xl">
                                @elseif (in_array($ext2, ['jpg', 'png', 'jpeg', 'webp']))
                                    <img src="{{ $fileUrl2 }}" alt="Materi"
                                        class="w-full max-h-72 object-contain border border-slate-200 rounded-xl bg-slate-50"
                                        onerror="this.outerHTML='<p class=\'text-sm text-red-500 p-3\'>Gambar tidak dapat dimuat. <a href=\'{{ $fileUrl2 }}\' class=\'underline\'>Download</a></p>'">
                                @elseif (in_array($ext2, ['mp4', 'webm']))
                                    <video controls class="w-full rounded-xl border border-slate-200 max-h-72">
                                        <source src="{{ $fileUrl2 }}" type="video/{{ $ext2 }}">
                                    </video>
                                @else
                                    <div
                                        class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-700">
                                                {{ basename($materi->file_materi) }}</p>
                                            <a href="{{ $fileUrl2 }}" target="_blank"
                                                class="text-xs text-blue-600 hover:text-blue-700 font-medium">Unduh
                                                File</a>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <p class="text-sm text-slate-400 italic">Tidak ada file yang dilampirkan</p>
                            @endif
                        </div>
                    </div>

                    <div class="px-7 py-4 border-t border-slate-100 bg-slate-50 flex justify-between items-center">
                        @if ($fileUrl2)
                            {{-- ✅ Tombol download pakai $fileUrl2 --}}
                            <a href="{{ $fileUrl2 }}" download
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white font-semibold rounded-xl text-sm hover:bg-blue-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Unduh
                            </a>
                        @else
                            <span></span>
                        @endif
                        <button onclick="closeModal('detailModal_{{ $materi->id }}')"
                            class="px-5 py-2 bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm hover:bg-slate-300 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

    <script>
        function openModal(id) {
            const m = document.getElementById(id);
            if (m) {
                m.classList.remove('hidden');
                m.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(id) {
            const m = document.getElementById(id);
            if (m) {
                m.classList.add('hidden');
                m.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
        document.querySelectorAll('.fixed.inset-0').forEach(el => {
            el.addEventListener('click', e => {
                if (e.target === el) closeModal(el.id);
            });
        });
    </script>
@endsection
