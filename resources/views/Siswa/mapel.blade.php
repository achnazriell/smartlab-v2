@extends('layouts.appSiswa')

@section('content')
    <style>
        /* ── Banner responsive ── */
        .banner-wrap {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(37,99,235,.15);
            border: 2px solid rgba(37,99,235,.1);
        }
        .banner-wrap img {
            width: 100%;
            /* tinggi responsif: pendek di mobile, lebih tinggi di desktop */
            height: clamp(140px, 28vw, 240px);
            object-fit: cover;
            display: block;
        }
        .banner-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: clamp(1rem,4vw,2.5rem);
            background: rgba(0,0,0,.15);
        }
        .banner-title {
            font-size: clamp(1.2rem, 4.5vw, 2.2rem);
            font-weight: 700;
            color: #fff;
            text-shadow: 0 3px 12px rgba(0,0,0,.35);
            line-height: 1.2;
            text-align: center;
        }
        .banner-desc {
            font-size: clamp(.72rem, 2.2vw, .95rem);
            color: rgba(255,255,255,.93);
            line-height: 1.5;
            text-align: center;
            margin-top: .5rem;
        }

        /* ── Card mapel ── */
        .mapel-card {
            transition: transform .3s cubic-bezier(.4,0,.2,1), box-shadow .3s, border-color .3s;
            border-radius: 16px;
            overflow: hidden;
            border: 2px solid rgba(37,99,235,.1);
            display: block;
        }
        .mapel-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(37,99,235,.25);
            border-color: rgba(37,99,235,.3);
        }

        /* ── Filter dropdown ── */
        .filter-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 6px);
            width: 200px;
            background: #fff;
            border: 1px solid #dde6ff;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(37,99,235,.15);
            z-index: 100;
            padding: .5rem 0;
        }
        .filter-dropdown a, .filter-dropdown button {
            display: block;
            width: 100%;
            text-align: left;
            padding: .55rem 1rem;
            font-size: .85rem;
            color: #1e3a8a;
            background: none;
            border: none;
            cursor: pointer;
            transition: background .15s;
        }
        .filter-dropdown a:hover, .filter-dropdown button:hover { background: #eff6ff; }
        .filter-dropdown .active-filter { font-weight: 700; color: #2563eb; background: #dbeafe; border-radius: 6px; }

        @keyframes spin { to { transform:rotate(360deg); } }
        .animate-spin { animation: spin 1s linear infinite; }
        .hidden { display:none!important; }
    </style>

    <div class="p-4 sm:p-6 lg:p-8">
        {{-- Loading --}}
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="border-t-4 border-blue-600 rounded-full w-16 h-16 animate-spin"></div>
        </div>

        {{-- ── Banner ── --}}
        <div class="banner-wrap mb-6">
            <img src="{{ asset('image/banner mapel.webp') }}" alt="banner mapel">
            <div class="banner-overlay">
                <p class="banner-title">Hai, {{ Auth::user()->name }}</p>
                <p class="banner-desc max-w-xl">
                    Belajar adalah perjalanan tanpa akhir. Mari jadikan setiap hari kesempatan untuk menambah wawasan.
                </p>
            </div>
        </div>

        {{-- ── Toolbar: judul + search + sort + filter ── --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
            <h1 class="text-xl lg:text-2xl font-bold text-blue-900 flex-shrink-0">Daftar Mata Pelajaran</h1>

            <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
                {{-- Search --}}
                <form action="{{ route('mapel') }}" method="GET"
                      class="flex items-center gap-2 flex-1 sm:flex-none">
                    {{-- Pertahankan sort & filter saat search --}}
                    @if(request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari mata pelajaran…"
                           class="flex-1 sm:w-52 px-3 py-2 rounded-xl border border-blue-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-xl transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="m19.6 21-6.3-6.3q-.75.6-1.725.95T9.5 16q-2.725 0-4.612-1.888T3 9.5t1.888-4.612T9.5 3t4.613 1.888T16 9.5q0 1.1-.35 2.075T14.7 13.3l6.3 6.3zM9.5 14q1.875 0 3.188-1.312T14 9.5t-1.312-3.187T9.5 5T6.313 6.313T5 9.5t1.313 3.188T9.5 14"/>
                        </svg>
                    </button>
                </form>

                {{-- Sort A-Z / Z-A --}}
                <form action="{{ route('mapel') }}" method="GET">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @php $nextSort = request('sort','asc') === 'asc' ? 'desc' : 'asc'; @endphp
                    <input type="hidden" name="sort" value="{{ $nextSort }}">
                    <button type="submit"
                            title="{{ request('sort','asc') === 'asc' ? 'Urutan A–Z (klik untuk Z–A)' : 'Urutan Z–A (klik untuk A–Z)' }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-xl transition-all flex items-center gap-1.5 text-sm font-medium">
                        @if(request('sort','asc') === 'asc')
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 18h6v-2H3v2zm0-5h12v-2H3v2zm0-7v2h18V6H3z"/></svg>
                            A–Z
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M3 6h18V4H3v2zm12 8H3v2h12v-2zm-6-7H3v2h6V7z"/></svg>
                            Z–A
                        @endif
                    </button>
                </form>

                {{-- Filter tugas --}}
                <div class="relative" id="filterWrap">
                    <button id="filterBtn"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-xl transition-all flex items-center gap-1.5 text-sm font-medium"
                            type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg>
                        Filter
                        @if(request('tugas'))
                            <span class="w-2 h-2 bg-yellow-300 rounded-full"></span>
                        @endif
                    </button>
                    <div id="filterDropdown" class="filter-dropdown hidden">
                        @php $filterRoute = route('mapel'); @endphp
                        <a href="{{ $filterRoute }}"
                           class="{{ !request('tugas') ? 'active-filter' : '' }}">
                            Semua Mapel
                        </a>
                        <a href="{{ $filterRoute }}?tugas=ada{{ request('search') ? '&search='.request('search') : '' }}{{ request('sort') ? '&sort='.request('sort') : '' }}"
                           class="{{ request('tugas') === 'ada' ? 'active-filter' : '' }}">
                            Ada Tugas Aktif
                        </a>
                        <a href="{{ $filterRoute }}?tugas=selesai{{ request('search') ? '&search='.request('search') : '' }}{{ request('sort') ? '&sort='.request('sort') : '' }}"
                           class="{{ request('tugas') === 'selesai' ? 'active-filter' : '' }}">
                            Semua Tugas Selesai
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Grid mapel ── --}}
        <div class="bg-white rounded-2xl shadow-lg border border-blue-100 p-4 sm:p-6">
            @if(request('search') || request('tugas') || request('sort'))
                <p class="text-sm text-gray-500 mb-4">
                    Menampilkan hasil
                    @if(request('search')) untuk "<strong>{{ request('search') }}</strong>"@endif
                    @if(request('tugas') === 'ada') — <span class="text-yellow-600 font-medium">Ada tugas aktif</span>@endif
                    @if(request('tugas') === 'selesai') — <span class="text-green-600 font-medium">Semua tugas selesai</span>@endif
                    @if(request('sort')) — urutan {{ request('sort') === 'desc' ? 'Z–A' : 'A–Z' }}@endif
                    ({{ $subjects->total() }} mapel)
                    <a href="{{ route('mapel') }}" class="ml-2 text-blue-600 hover:underline text-xs">Reset</a>
                </p>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($subjects as $subject)
                    @php
                        $guruName = $subject->teacherAssignments
                            ->map(fn($tsa) => optional(optional($tsa->teacher)->user)->name)
                            ->filter()->first();
                        // Filter client-side by tugas param
                        $skip = false;
                        if(request('tugas') === 'ada' && ($subject->unfinished_task_count ?? 0) == 0) $skip = true;
                        if(request('tugas') === 'selesai' && ($subject->unfinished_task_count ?? 0) > 0) $skip = true;
                    @endphp
                    @if(!$skip)
                    <a href="{{ route('Materi', ['materi_id' => $subject->id]) }}" class="mapel-card">
                        <div class="relative h-44 flex flex-col justify-between text-white p-5"
                             style="background-image:url('{{ asset('image/siswa/cardmapel.svg') }}');background-size:cover;background-position:center;">
                            <div class="flex-1 flex flex-col justify-center">
                                <h3 class="text-xl lg:text-2xl font-bold mb-2 drop-shadow-lg line-clamp-2">
                                    {{ $subject->name_subject }}
                                </h3>
                                <div class="flex items-center text-white/90 text-sm drop-shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="flex-shrink-0">
                                        <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2M8.5 9.5a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0m9.758 7.484A7.99 7.99 0 0 1 12 20a7.99 7.99 0 0 1-6.258-3.016C7.363 15.821 9.575 15 12 15s4.637.821 6.258 1.984"/>
                                    </svg>
                                    <span class="ml-1.5 truncate">{{ $guruName ?? 'Belum ada guru' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm text-white/90 border-t border-white/20 pt-3">
                                <div class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M16 1H8v4h8zM3 3h3v4h12V3h3v20H3zm12 10v-2H9v2zm0 4v-2H9v2z"/>
                                    </svg>
                                    @if(($subject->unfinished_task_count ?? 0) > 0)
                                        <span class="font-medium text-yellow-200">{{ $subject->unfinished_task_count }} tugas belum</span>
                                    @else
                                        <span class="opacity-80">Semua selesai ✓</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M4 3h2v18H4zm14 0H7v18h11c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2m-2 6h-6V8h6zm0-2h-6V6h6z"/>
                                    </svg>
                                    <span>{{ $subject->materi_count ?? 0 }} materi</span>
                                </div>
                            </div>
                        </div>
                    </a>
                    @endif
                @empty
                    <div class="col-span-full py-16 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                 stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-blue-400">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                        </div>
                        @if(request('search'))
                            <p class="text-gray-700 font-semibold text-lg">Tidak Ada Hasil</p>
                            <p class="text-gray-400 text-sm mt-1">Mata pelajaran "{{ request('search') }}" tidak ditemukan</p>
                            <a href="{{ route('mapel') }}" class="mt-3 inline-block text-blue-600 text-sm hover:underline">← Lihat semua</a>
                        @else
                            <p class="text-gray-700 font-semibold text-lg">Belum Ada Mata Pelajaran</p>
                            <p class="text-gray-400 text-sm mt-1">Mata pelajaran akan muncul setelah guru ditugaskan ke kelas</p>
                        @endif
                    </div>
                @endforelse
            </div>

            @if($subjects->hasPages())
                <div class="mt-6 pt-5 border-t border-blue-100">
                    {{ $subjects->appends(request()->query())->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Loading screen
            const ls = document.getElementById('loadingScreen');
            if (ls) ls.classList.add('hidden');

            // Filter dropdown toggle
            const btn = document.getElementById('filterBtn');
            const dd  = document.getElementById('filterDropdown');
            btn?.addEventListener('click', e => { e.stopPropagation(); dd.classList.toggle('hidden'); });
            document.addEventListener('click', () => dd?.classList.add('hidden'));
            dd?.addEventListener('click', e => e.stopPropagation());

            @if(session('error'))
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon:'error', title:'Oops…', text:'{{ session("error") }}' });
                }
            @endif
        });
    </script>
@endsection
