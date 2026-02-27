@extends('layouts.appSiswa')

@section('content')
    <div class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="loader border-t-4 border-blue-600 rounded-full w-12 h-12 animate-spin"></div>
        </div>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Daftar Materi</h1>
                <p class="text-slate-500 text-sm mt-0.5">Semua materi pembelajaran tersedia di sini</p>
            </div>

            <!-- Search + Filter -->
            <div class="w-full sm:w-auto flex items-center gap-2">
                <form action="{{ route('semuamateri') }}" method="GET" class="flex items-center gap-2 flex-1 sm:flex-none">
                    <div class="relative flex-1 sm:w-60">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari materi..."
                            class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white p-2.5 rounded-xl transition-all flex-shrink-0">
                        <i class="fas fa-search text-sm"></i>
                    </button>
                </form>

                <!-- Filter Dropdown -->
                <div class="relative flex-shrink-0" x-data="{ filterOpen: false }">
                    <button @click="filterOpen = !filterOpen"
                        class="bg-slate-700 hover:bg-slate-800 text-white p-2.5 rounded-xl transition-all shadow-sm">
                        <i class="fas fa-filter text-sm"></i>
                    </button>
                    <div x-show="filterOpen" @click.outside="filterOpen = false"
                        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        class="absolute right-0 mt-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden">
                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-100">
                            <p class="text-sm font-bold text-slate-700">Filter Mata Pelajaran</p>
                        </div>
                        <form method="GET" action="{{ route('semuamateri') }}" class="p-2 max-h-64 overflow-y-auto">
                            @php
                                $filterSubjects = ['Matematika','Bahasa Indonesia','Bahasa Inggris','Fisika','Kimia','Biologi','Sejarah','Seni Budaya','Pendidikan Agama','Pendidikan Kewarganegaraan'];
                            @endphp
                            @foreach ($filterSubjects as $subj)
                                <button type="submit" name="subject" value="{{ $subj }}"
                                    class="w-full text-left px-3 py-2.5 text-sm text-slate-700 hover:bg-blue-50 hover:text-blue-700 rounded-xl transition-colors font-medium {{ request('subject') === $subj ? 'bg-blue-50 text-blue-700' : '' }}">
                                    {{ $subj }}
                                </button>
                            @endforeach
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if(request('subject'))
            <div class="flex items-center gap-2 mb-4">
                <span class="text-sm text-slate-500">Filter aktif:</span>
                <span class="inline-flex items-center gap-1.5 bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1.5 rounded-full">
                    {{ request('subject') }}
                    <a href="{{ route('semuamateri') }}" class="hover:text-blue-900">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                </span>
            </div>
        @endif

        <!-- Materi List -->
        <div class="space-y-3 sm:space-y-4">
            @forelse ($materis as $materi)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-blue-200 transition-all overflow-hidden">
                    <div class="flex items-start gap-0">
                        <!-- Left accent bar -->
                        <div class="w-1 self-stretch bg-blue-500 flex-shrink-0 rounded-l-2xl"></div>

                        <div class="flex-1 p-4 sm:p-5">
                            <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                                <div class="flex-1 min-w-0">
                                    <!-- Date badge (top on mobile) -->
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center gap-1.5 text-xs text-slate-500 bg-slate-50 px-2.5 py-1 rounded-full border border-slate-100">
                                            <i class="fas fa-calendar text-slate-400 text-xs"></i>
                                            {{ $materi->created_at->translatedFormat('l, d F Y') }}
                                        </span>
                                    </div>

                                    <h2 class="text-base sm:text-lg font-bold text-slate-900 leading-tight line-clamp-2">
                                        {{ $materi->title_materi }}
                                    </h2>

                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <div class="w-5 h-5 bg-blue-100 rounded-md flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-book text-blue-500" style="font-size: 9px;"></i>
                                        </div>
                                        <span class="text-sm text-slate-500">{{ optional($materi->subject)->name_subject ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                <!-- Action button -->
                                <div class="flex-shrink-0">
                                    @if ($materi->file_materi)
                                        <a href="{{ route('materi.show', $materi->id) }}"
                                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl text-sm transition-all shadow-sm hover:shadow-md active:scale-95">
                                            <i class="fas fa-book-open text-xs"></i>
                                            Buka Materi
                                        </a>
                                    @else
                                        <span class="inline-flex items-center gap-2 bg-slate-100 text-slate-400 font-semibold py-2 px-4 rounded-xl text-sm cursor-not-allowed">
                                            <i class="fas fa-file-slash text-xs"></i>
                                            Tidak ada file
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 py-16 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-2xl mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                        </svg>
                    </div>
                    <p class="text-slate-600 font-semibold">Belum ada materi</p>
                    <p class="text-slate-400 text-sm mt-1">Materi untuk kelasmu akan muncul di sini</p>
                </div>
            @endforelse
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loadingScreen = document.getElementById('loadingScreen');
            if (loadingScreen) loadingScreen.classList.add('hidden');
        });
    </script>
@endsection
