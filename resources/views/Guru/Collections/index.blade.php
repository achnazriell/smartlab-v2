@extends('layouts.appTeacher')

@section('content')
<div class="space-y-6">

    {{-- ===== BACK BUTTON ===== --}}
    <div>
        <a href="{{ route('tasks.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl transition text-sm font-medium shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Tugas
        </a>
    </div>

    {{-- ===== PAGE HEADER ===== --}}
    <div class="bg-gradient-to-r from-blue-700 to-blue-500 rounded-2xl p-8 text-white shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Kelola Pengumpulan</h1>
                <p class="text-blue-100 mt-1 text-sm">Tugas: <span class="font-semibold text-white">{{ $task->title_task }}</span></p>
            </div>
            <div class="flex items-center gap-3">
                <div class="bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-center">
                    <p class="text-2xl font-bold">{{ $collections->total() ?? 0 }}</p>
                    <p class="text-xs text-blue-200 mt-0.5">Total</p>
                </div>
                <div class="bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-center">
                    <p class="text-2xl font-bold">{{ $collections->where('status', 'Dikumpulkan')->count() }}</p>
                    <p class="text-xs text-blue-200 mt-0.5">Dikumpulkan</p>
                </div>
            </div>
        </div>

        {{-- FILTER ROW --}}
        <form action="{{ route('collections.byTask', $task->id) }}" method="GET" class="mt-6 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input type="text" name="search" placeholder="Cari nama siswa..." value="{{ request('search') }}"
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/60 text-sm outline-none focus:bg-white/20 focus:border-white/40 transition">
            </div>
            <select name="status"
                class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                <option value="" class="text-slate-800">Semua Status</option>
                <option value="Dikumpulkan" class="text-slate-800" @selected(request('status') === 'Dikumpulkan')>Dikumpulkan</option>
                <option value="Terlambat"   class="text-slate-800" @selected(request('status') === 'Terlambat')>Terlambat</option>
            </select>
            <select name="kelas"
                class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                <option value="" class="text-slate-800">Semua Kelas</option>
                @foreach ($kelasList ?? [] as $k)
                <option value="{{ $k }}" class="text-slate-800" @selected(request('kelas') === $k)>{{ $k }}</option>
                @endforeach
            </select>
            <select name="sort"
                class="px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-sm outline-none focus:bg-white/20 transition">
                <option value="" class="text-slate-800">Urutkan</option>
                <option value="terbaru"  class="text-slate-800" @selected(request('sort') === 'terbaru')>Terbaru</option>
                <option value="terlama"  class="text-slate-800" @selected(request('sort') === 'terlama')>Terlama</option>
                <option value="nama_asc" class="text-slate-800" @selected(request('sort') === 'nama_asc')>Nama A-Z</option>
            </select>
            <button type="submit"
                class="px-5 py-2.5 bg-white text-blue-700 font-semibold rounded-xl text-sm hover:bg-blue-50 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Filter
            </button>
            @if (request()->hasAny(['search', 'status', 'kelas', 'sort']))
            <a href="{{ route('collections.byTask', $task->id) }}"
               class="px-4 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm hover:bg-white/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- ===== ALERT MESSAGES ===== --}}
    @if (session('success'))
    <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 px-5 py-3.5 rounded-xl flex items-center gap-3 text-sm shadow-sm">
        <svg class="w-5 h-5 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if ($errors->any())
    <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-5 py-3.5 rounded-xl text-sm shadow-sm">
        <div class="flex items-center gap-2 font-semibold mb-2">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 001.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
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
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-800">Daftar Pengumpulan</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Menampilkan <span class="font-semibold text-blue-600">{{ $collections->total() ?? 0 }}</span> pengumpulan
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider w-12">No</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Nama Tugas</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Nama Siswa</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Kelas</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden md:table-cell">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider hidden lg:table-cell">Waktu Kumpul</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($collections as $index => $collection)
                    @php $offset = ($collections->currentPage() - 1) * $collections->perPage(); @endphp
                    <tr class="hover:bg-blue-50/40 transition-colors">
                        <td class="px-5 py-4 text-slate-500 font-medium">{{ $offset + $index + 1 }}</td>
                        <td class="px-5 py-4">
                            <span class="font-semibold text-slate-800">{{ $collection->Task->title_task }}</span>
                        </td>
                        <td class="px-5 py-4 text-slate-700">{{ $collection->user->name }}</td>
                        <td class="px-5 py-4">
                            @foreach ($collection->user->classes as $class)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $class->name_class }}
                            </span>
                            @endforeach
                        </td>
                        <td class="px-5 py-4 hidden md:table-cell">
                            @php
                                $isKumpul  = $collection->status === 'Dikumpulkan';
                                $statusCls = $isKumpul
                                    ? 'bg-emerald-100 text-emerald-700 border border-emerald-200'
                                    : 'bg-amber-100 text-amber-700 border border-amber-200';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $statusCls }}">
                                @if ($isKumpul)
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                @else
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/>
                                </svg>
                                @endif
                                {{ $collection->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4 hidden lg:table-cell text-slate-500 text-xs">
                            {{ $collection->created_at ? $collection->created_at->format('d M Y, H:i') : '—' }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            <button onclick="openModal('showModal_{{ $collection->id }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-xs font-semibold transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-14 text-center">
                            <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-base font-semibold text-slate-700">Belum Ada Pengumpulan</h3>
                            <p class="text-xs text-slate-400 mt-1">Siswa akan mulai mengumpulkan tugas di sini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($collections->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50">
            {{ $collections->links('vendor.pagination.tailwind') }}
        </div>
        @endif
    </div>

    {{-- ===== MODALS ===== --}}
    @foreach ($collections as $collection)
    <div id="showModal_{{ $collection->id }}"
         class="fixed inset-0 flex items-center justify-center bg-black/50 hidden z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">

            {{-- Modal Header --}}
            <div class="bg-gradient-to-r from-blue-700 to-blue-500 px-7 py-5 flex justify-between items-center">
                <div>
                    <h5 class="text-xl font-bold text-white">Detail Pengumpulan</h5>
                    <p class="text-blue-200 text-sm mt-0.5">{{ $collection->Task->title_task }}</p>
                </div>
                <button onclick="closeModal('showModal_{{ $collection->id }}')"
                    class="text-white/70 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="overflow-y-auto flex-1 px-7 py-6 space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Nama Siswa</p>
                        <p class="text-slate-800 font-semibold">{{ $collection->user->name }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Kelas</p>
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach ($collection->user->classes as $class)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $class->name_class }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Status</p>
                        @php
                            $isK = $collection->status === 'Dikumpulkan';
                            $sC  = $isK ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $sC }} mt-1">
                            {{ $collection->status }}
                        </span>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4">
                        <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider mb-1">Waktu Kumpul</p>
                        <p class="text-slate-800 font-semibold text-sm">
                            {{ $collection->created_at ? $collection->created_at->format('d M Y, H:i') : '—' }}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700 mb-3">Bukti Pengumpulan</p>
                    @php $ext = pathinfo($collection->file_collection, PATHINFO_EXTENSION); @endphp
                    @if (in_array(strtolower($ext), ['jpg', 'png', 'jpeg', 'webp']))
                    <img src="{{ asset('storage/' . $collection->file_collection) }}" alt="Bukti"
                         class="w-full max-h-72 object-contain border border-slate-200 rounded-xl bg-slate-50">
                    @elseif (strtolower($ext) === 'pdf')
                    <embed src="{{ asset('storage/' . $collection->file_collection) }}" type="application/pdf"
                           class="w-full h-80 border border-slate-200 rounded-xl">
                    @else
                    <div class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-200 rounded-xl">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-slate-700">{{ basename($collection->file_collection) }}</p>
                            <a href="{{ asset('storage/' . $collection->file_collection) }}" target="_blank"
                               class="text-xs text-blue-600 hover:text-blue-700 font-medium">Unduh File</a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="px-7 py-4 border-t border-slate-100 bg-slate-50 flex justify-end">
                <button onclick="closeModal('showModal_{{ $collection->id }}')"
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
    const modal = document.getElementById(id);
    if (modal) { modal.classList.remove('hidden'); modal.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) { modal.classList.add('hidden'); modal.style.display = 'none'; document.body.style.overflow = 'auto'; }
}
document.querySelectorAll('.fixed.inset-0').forEach(modal => {
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(modal.id); });
});
</script>
@endsection
