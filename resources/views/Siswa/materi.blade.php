@extends('layouts.appSiswa')

@section('content')
    <style>
        /* ── Banner responsive ── */
        .banner-wrap {
            position: relative;
            border-radius: 18px;
            overflow: hidden;
        }
        .banner-wrap img {
            width: 100%;
            height: clamp(130px, 25vw, 220px);
            object-fit: cover;
            display: block;
        }
        .banner-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: clamp(.75rem,3vw,2rem) clamp(1rem,5vw,2.5rem);
            background: linear-gradient(90deg, rgba(0,0,0,.45) 0%, rgba(0,0,0,.1) 100%);
        }
        .banner-subject {
            font-size: clamp(1.3rem, 5vw, 2.8rem);
            font-weight: 800;
            color: #fff;
            text-shadow: 0 3px 14px rgba(0,0,0,.4);
            line-height: 1.15;
            text-transform: uppercase;
        }
        .banner-meta { display: flex; flex-wrap: wrap; gap: clamp(.5rem,2vw,2rem); margin-top: .6rem; }
        .banner-meta-item {
            display: flex;
            align-items: center;
            gap: .4rem;
            color: rgba(255,255,255,.9);
        }
        .meta-icon-wrap {
            width: clamp(1.5rem,4vw,2rem);
            height: clamp(1.5rem,4vw,2rem);
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .meta-label { font-size: clamp(.6rem,1.6vw,.75rem); font-weight: 700; }
        .meta-value { font-size: clamp(.65rem,1.8vw,.85rem); }

        /* ── Tabs ── */
        .tab-btn {
            display: flex; align-items: center; gap: .4rem;
            padding: .45rem .9rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: .85rem;
            transition: all .2s;
            border: 2px solid transparent;
            cursor: pointer;
        }
        .tab-btn.active { background:#1e3a8a; color:#fff; }
        .tab-btn.inactive { background:#fff; color:#1e3a8a; border-color:#bfdbfe; }
        .tab-btn:hover:not(.active) { background:#eff6ff; }

        /* ── Card ── */
        .card-item {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(37,99,235,.08);
            border-left: 4px solid #3b82f6;
            overflow: hidden;
            transition: box-shadow .2s;
        }
        .card-item:hover { box-shadow: 0 6px 20px rgba(37,99,235,.14); }
        .card-item.border-green { border-left-color: #22c55e; }
        .card-item.border-red   { border-left-color: #ef4444; }
        .card-item.border-yellow{ border-left-color: #f59e0b; }

        /* ── Filter dropdown ── */
        .fd-wrap { position: relative; }
        .fd-menu {
            position: absolute; right: 0; top: calc(100% + 6px);
            width: 200px; background: #fff;
            border: 1px solid #dde6ff; border-radius: 14px;
            box-shadow: 0 8px 24px rgba(37,99,235,.14);
            z-index: 100; padding: .4rem 0;
        }
        .fd-menu form button {
            display: block; width: 100%; text-align: left;
            padding: .5rem 1rem; font-size: .83rem;
            color: #1e3a8a; background: none; border: none;
            cursor: pointer; transition: background .15s;
        }
        .fd-menu form button:hover { background: #eff6ff; }

        @keyframes spin { to { transform:rotate(360deg); } }
        .animate-spin { animation: spin 1s linear infinite; }
        .hidden { display:none!important; }

        /* materiModal */
        .materiModal { top:0;left:0;right:0;bottom:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.5);z-index:50; }
        .materiModal .bg-white { max-height:90%;display:flex;flex-direction:column; }
    </style>

    <div class="p-3 sm:p-5 lg:p-8">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="border-t-4 border-blue-600 rounded-full w-16 h-16 animate-spin"></div>
        </div>

        {{-- ── Tabs + Search + Filter bar ── --}}
        <div class="bg-white shadow-md rounded-2xl p-3 sm:p-4 mb-5">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                {{-- Tabs --}}
                <div class="flex items-center gap-2">
                    <button id="tab-materi" class="tab-btn active" onclick="switchTab('materi')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4 3h2v18H4zm14 0H7v18h11c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2m-2 6h-6V8h6zm0-2h-6V6h6z"/>
                        </svg>
                        Materi
                    </button>
                    <button id="tab-tugas" class="tab-btn inactive" onclick="switchTab('tugas')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 1H8v4h8z"/><path d="M3 3h3v4h12V3h3v20H3zm12 10v-2H9v2zm0 4v-2H9v2z"/>
                        </svg>
                        Tugas
                    </button>
                </div>

                {{-- Search + Sort + Filter --}}
                <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
                    {{-- Search --}}
                    <form action="{{ route('Materi', ['materi_id' => $subjectId]) }}" method="GET"
                          class="flex items-center gap-2 flex-1 sm:flex-none" id="searchForm">
                        <input type="hidden" id="tabInput" name="tab" value="{{ $activeTab }}">
                        @if(request('order')) <input type="hidden" name="order" value="{{ request('order') }}"> @endif
                        @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari…"
                               class="flex-1 sm:w-44 px-3 py-2 rounded-xl border border-blue-200 text-sm
                                      focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white p-2 rounded-xl">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>

                    {{-- Sort materi (A–Z / terbaru) --}}
                    <div id="wrapSort">
                        <form action="{{ route('Materi', ['materi_id' => $subjectId]) }}" method="GET">
                            <input type="hidden" name="tab" value="materi">
                            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                            @php $nextOrder = request('order','desc') === 'desc' ? 'asc' : 'desc'; @endphp
                            <input type="hidden" name="order" value="{{ $nextOrder }}">
                            <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-2 rounded-xl flex items-center gap-1.5 text-sm"
                                    title="{{ request('order','desc') === 'desc' ? 'Terlama dulu' : 'Terbaru dulu' }}">
                                @if(request('order','desc') === 'desc')
                                    <i class="fa-solid fa-arrow-down-wide-short"></i> Terbaru
                                @else
                                    <i class="fa-solid fa-arrow-up-wide-short"></i> Terlama
                                @endif
                            </button>
                        </form>
                    </div>

                    {{-- Filter status tugas --}}
                    <div class="fd-wrap" id="wrapFilter">
                        <button id="filterBtn"
                                class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-2 rounded-xl flex items-center gap-1.5 text-sm"
                                type="button">
                            <i class="fas fa-filter"></i>
                            @if(request('status'))
                                <span class="w-2 h-2 bg-yellow-300 rounded-full"></span>
                            @endif
                        </button>
                        <div id="filterMenu" class="fd-menu hidden">
                            <p class="px-3 pt-1 pb-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status Tugas</p>
                            <form method="GET" action="{{ route('Materi', ['materi_id' => $subjectId]) }}">
                                <input type="hidden" name="tab" value="tugas">
                                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                                <button type="submit" name="status" value=""
                                        class="{{ !request('status') ? 'font-bold text-blue-700' : '' }}">
                                    Semua Status
                                </button>
                                <button type="submit" name="status" value="Sudah mengumpulkan"
                                        class="{{ request('status') === 'Sudah mengumpulkan' ? 'font-bold text-green-700' : '' }}">
                                    Sudah Mengumpulkan
                                </button>
                                <button type="submit" name="status" value="Belum mengumpulkan"
                                        class="{{ request('status') === 'Belum mengumpulkan' ? 'font-bold text-yellow-700' : '' }}">
                                    Belum Mengumpulkan
                                </button>
                                <button type="submit" name="status" value="Tidak mengumpulkan"
                                        class="{{ request('status') === 'Tidak mengumpulkan' ? 'font-bold text-red-700' : '' }}">
                                    Tidak Mengumpulkan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Banner mapel ── --}}
        @if($materis->isNotEmpty() || $tasks->isNotEmpty())
        <div class="banner-wrap mb-5">
            <img src="{{ asset('image/siswa/banner materi.svg') }}" alt="banner">
            <div class="banner-overlay">
                <p class="banner-subject">{{ $subjectName ?? 'Mapel tidak ditemukan' }}</p>
                <div class="banner-meta">
                    <div class="banner-meta-item">
                        <div class="meta-icon-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 256 256" fill="#1E40AF">
                                <path d="M216 40H40a16 16 0 0 0-16 16v144a16 16 0 0 0 16 16h13.39a8 8 0 0 0 7.23-4.57a48 48 0 0 1 86.76 0a8 8 0 0 0 7.23 4.57H216a16 16 0 0 0 16-16V56a16 16 0 0 0-16-16M80 144a24 24 0 1 1 24 24a24 24 0 0 1-24-24m136 56h-56.57a64.4 64.4 0 0 0-28.83-26.16a40 40 0 1 0-53.2 0A64.4 64.4 0 0 0 48.57 200H40V56h176ZM56 96V80a8 8 0 0 1 8-8h128a8 8 0 0 1 8 8v96a8 8 0 0 1-8 8h-16a8 8 0 0 1 0-16h8V88H72v8a8 8 0 0 1-16 0"/>
                            </svg>
                        </div>
                        <div>
                            <p class="meta-label">Pengajar</p>
                            <p class="meta-value">{{ $teacherName }}</p>
                        </div>
                    </div>
                    <div class="banner-meta-item">
                        <div class="meta-icon-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 256 256" fill="#1E40AF">
                                <path d="m226.53 56.41l-96-32a8 8 0 0 0-5.06 0l-96 32A8 8 0 0 0 24 64v80a8 8 0 0 0 16 0V75.1l33.59 11.19a64 64 0 0 0 20.65 88.05c-18 7.06-33.56 19.83-44.94 37.29a8 8 0 1 0 13.4 8.74C77.77 197.25 101.57 184 128 184s50.23 13.25 65.3 36.37a8 8 0 0 0 13.4-8.74c-11.38-17.46-27-30.23-44.94-37.29a64 64 0 0 0 20.65-88l44.12-14.7a8 8 0 0 0 0-15.18Z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="meta-label">Siswa</p>
                            <p class="meta-value">{{ $countSiswa }} Siswa</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Active filter info ── --}}
        @if(request('search') || request('status'))
        <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
            <span>Filter aktif:</span>
            @if(request('search'))
                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-lg">Cari: {{ request('search') }}</span>
            @endif
            @if(request('status'))
                <span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-lg">{{ request('status') }}</span>
            @endif
            <a href="{{ route('Materi', ['materi_id' => $subjectId, 'tab' => $activeTab]) }}"
               class="text-blue-600 hover:underline">Reset</a>
        </div>
        @endif

        {{-- ── TAB CONTENT: MATERI ── --}}
        <div id="content-materi" class="tab-content space-y-4">
            @forelse($materis as $materi)
                <div class="card-item">
                    <div class="p-4 sm:p-5 relative">
                        <div class="absolute top-3 right-3 text-right text-xs text-gray-400">
                            {{ \Carbon\Carbon::parse($materi->created_at)->translatedFormat('j F Y') }}
                        </div>
                        <h2 class="text-base sm:text-lg font-bold mb-1 pr-24">{{ $materi->title_materi }}</h2>
                        <p class="text-gray-500 text-sm pr-4 mb-3">
                            {{ Str::limit($materi->description, 100, '…') ?? 'Tidak ada deskripsi' }}
                        </p>
                        <div class="flex flex-wrap gap-2">
                            <button onclick="openModal('showMateriModal_{{ $materi->id }}')"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-1.5 px-3 rounded-xl text-sm">
                                Lihat detail
                            </button>
                            @if($materi->file_materi)
                                <a href="{{ route('materi.show', $materi->id) }}"
                                   class="bg-green-500 hover:bg-green-700 text-white font-semibold py-1.5 px-3 rounded-xl text-sm">
                                    Buka Materi
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <img src="{{ asset('image/Gelembung.svg') }}" alt="" class="mx-auto mb-4 w-24 opacity-70">
                    <p class="text-gray-600 font-semibold text-lg">
                        {{ request('search') ? 'Tidak ada materi yang cocok' : 'Belum Ada Materi' }}
                    </p>
                </div>
            @endforelse
            <div class="py-2">{{ $materis->appends(request()->except('page'))->links('vendor.pagination.tailwind') }}</div>

            {{-- Modals Materi --}}
            @foreach($materis as $materi)
                <div id="showMateriModal_{{ $materi->id }}" class="materiModal fixed inset-0 hidden items-center justify-center z-50" style="display:none;">
                    <div class="bg-white rounded-xl shadow-xl w-[95%] sm:max-w-2xl max-h-[90vh] mx-2 p-5 flex flex-col overflow-hidden">
                        <div class="flex justify-between items-center border-b pb-3 mb-3">
                            <h5 class="text-lg font-bold text-gray-800">Detail Materi</h5>
                            <button onclick="closeModal('showMateriModal_{{ $materi->id }}')" class="text-gray-400 hover:text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="flex-1 overflow-y-auto space-y-3 text-sm">
                            <div><span class="font-semibold text-gray-700">Materi: </span><span class="text-gray-600">{{ $materi->title_materi }}</span></div>
                            <div><span class="font-semibold text-gray-700">Tanggal: </span><span class="text-gray-600">{{ \Carbon\Carbon::parse($materi->created_at)->translatedFormat('l, j F Y') }}</span></div>
                            <div><span class="font-semibold text-gray-700">Deskripsi: </span><span class="text-gray-600">{{ $materi->description ?? 'Kosong' }}</span></div>
                            <div>
                                <p class="font-semibold text-gray-700 mb-2">File Materi:</p>
                                @if($materi->file_materi)
                                    <a href="{{ Storage::url($materi->file_materi) }}" target="_blank"
                                       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
                                        <i class="fas fa-file-pdf"></i> Buka PDF
                                    </a>
                                @else
                                    <p class="text-gray-400 text-sm">Tidak ada file</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ── TAB CONTENT: TUGAS ── --}}
        <div id="content-tugas" class="tab-content hidden space-y-4">
            @forelse($tasks as $task)
                @php
                    $assessment  = $task->collections->first()->assessment ?? null;
                    $status      = $task->collections->first()->status ?? 'default';
                    $hasMateri   = $task->materi !== null;
                    $materiId    = $hasMateri ? $task->materi->id : null;
                    $materiUrl   = $hasMateri ? route('materi.show', $materiId) : null;
                    $materiJudul = $hasMateri ? $task->materi->title_materi : null;
                    $borderClass = $status === 'Sudah mengumpulkan' ? 'border-green' : ($status === 'Tidak mengumpulkan' ? 'border-red' : 'border-yellow');
                @endphp
                <div class="card-item {{ $borderClass }}">
                    <div class="p-4 sm:p-5 relative">
                        <div class="absolute top-3 right-3 text-right text-xs text-gray-400">
                            <span class="text-red-500 font-semibold block">Deadline</span>
                            {{ \Carbon\Carbon::parse($task->date_collection)->translatedFormat('H:i, j F Y') }}
                        </div>
                        <p class="text-xs text-gray-400 mb-0.5">Nilai: {{ $assessment && $assessment->mark_task !== null ? $assessment->mark_task : 'Belum Dinilai' }}</p>
                        <h2 class="text-base sm:text-lg font-bold mb-1 pr-28">{{ $task->title_task }}</h2>
                        <p class="text-gray-500 text-sm pr-4 mb-1">{{ Str::limit($task->description_task, 90, '…') }}</p>

                        @if($hasMateri && $status === 'Belum mengumpulkan')
                            <div id="materi-lock-badge-{{ $task->id }}"
                                 class="inline-flex items-center gap-1.5 mt-2 px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700 border border-amber-300">
                                <i class="fas fa-lock text-amber-500"></i>
                                Baca materi dulu: <strong>{{ $materiJudul }}</strong>
                            </div>
                            <div id="materi-unlocked-badge-{{ $task->id }}"
                                 class="hidden inline-flex items-center gap-1.5 mt-2 px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-300">
                                <i class="fas fa-lock-open text-green-500"></i> Materi sudah dibaca — tugas terbuka
                            </div>
                        @endif

                        <div class="mt-2">
                            @if($status === 'Tidak mengumpulkan')
                                <span class="inline-flex items-center gap-1 text-red-400 text-sm font-semibold"> {{ $status }}</span>
                            @elseif($status === 'Belum mengumpulkan')
                                <span class="inline-flex items-center gap-1 text-yellow-500 text-sm font-semibold">{{ $status }}</span>
                            @elseif($status === 'Sudah mengumpulkan')
                                <span class="inline-flex items-center gap-1 text-green-500 text-sm font-semibold">{{ $status }}</span>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-2 mt-3">
                            <button onclick="openModal('showTaskModal_{{ $task->id }}')"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-1.5 px-3 rounded-xl text-sm">
                                Lihat detail
                            </button>
                            @if($status === 'Belum mengumpulkan')
                                @if($hasMateri)
                                    <button id="btn-kumpul-locked-{{ $task->id }}"
                                            class="bg-gray-400 cursor-not-allowed text-white font-semibold py-1.5 px-3 rounded-xl text-sm inline-flex items-center gap-1"
                                            onclick="showMateriLockAlert({{ $materiId }}, '{{ addslashes($materiUrl) }}', '{{ addslashes($materiJudul) }}')">
                                        <i class="fas fa-lock"></i> Kumpulkan
                                    </button>
                                    <button id="btn-kumpul-unlocked-{{ $task->id }}"
                                            class="hidden bg-green-500 hover:bg-green-700 text-white font-semibold py-1.5 px-3 rounded-xl text-sm"
                                            onclick="openModal('tugasModal-{{ $task->id }}')">
                                        Pengumpulan Tugas
                                    </button>
                                @else
                                    <button onclick="openModal('tugasModal-{{ $task->id }}')"
                                            class="bg-green-500 hover:bg-green-700 text-white font-semibold py-1.5 px-3 rounded-xl text-sm">
                                        Pengumpulan Tugas
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <img src="{{ asset('image/Gelembung.svg') }}" alt="" class="mx-auto mb-4 w-24 opacity-70">
                    <p class="text-gray-600 font-semibold text-lg">
                        {{ request('search') || request('status') ? 'Tidak ada tugas yang cocok' : 'Belum Ada Tugas' }}
                    </p>
                    @if(request('search') || request('status'))
                        <a href="{{ route('Materi', ['materi_id' => $subjectId, 'tab' => 'tugas']) }}"
                           class="mt-2 inline-block text-blue-600 text-sm hover:underline">Reset filter</a>
                    @endif
                </div>
            @endforelse
            <div class="py-2">{{ $tasks->appends(request()->except('page'))->links('vendor.pagination.tailwind') }}</div>
        </div>
    </div>

    {{-- Modal Detail Tugas --}}
    @foreach($tasks as $task)
        <div id="showTaskModal_{{ $task->id }}" class="taskModal fixed inset-0 hidden items-center justify-center z-50" style="display:none;">
            <div class="bg-white rounded-xl shadow-xl w-[95%] sm:w-[85%] md:w-[60%] max-h-[90vh] px-4 py-5 overflow-y-auto">
                <div class="flex justify-between items-center border-b pb-3 mb-3">
                    <h5 class="text-lg font-bold text-gray-800">Detail Tugas</h5>
                    <button onclick="closeModal('showTaskModal_{{ $task->id }}')" class="text-gray-400 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-3 text-sm">
                    <div><span class="font-semibold text-gray-700">Judul: </span><span class="text-gray-600">{{ $task->title_task }}</span></div>
                    <div>
                        <span class="font-semibold text-gray-700">Materi: </span>
                        @if($task->materi)
                            <a href="{{ route('materi.show', $task->materi->id) }}" class="text-blue-600 hover:underline font-medium">
                                {{ $task->materi->title_materi }}
                            </a>
                        @else
                            <span class="text-gray-600">-</span>
                        @endif
                    </div>
                    <div><span class="font-semibold text-gray-700">Deadline: </span><span>{{ \Carbon\Carbon::parse($task->date_collection)->translatedFormat('H:i, l j F Y') }}</span></div>
                    <div><p class="font-semibold text-gray-700 mb-1">Deskripsi:</p><p class="text-gray-600">{{ $task->description_task ?? '-' }}</p></div>
                    <div>
                        <p class="font-semibold text-gray-700 mb-2">File Tugas:</p>
                        @php
                            $filePath = $task->file_task ?? null;
                            $fileExt  = $filePath ? strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) : null;
                            $fileUrl  = $filePath ? asset('storage/'.$filePath) : null;
                        @endphp
                        @if($filePath && in_array($fileExt, ['jpg','jpeg','png']))
                            <img src="{{ $fileUrl }}" alt="File" class="w-full h-auto border-2 rounded-lg">
                        @elseif($filePath && $fileExt === 'pdf')
                            <a href="{{ $fileUrl }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700"><i class="fas fa-file-pdf"></i> Buka Tugas</a>
                        @elseif($filePath)
                            <a href="{{ $fileUrl }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-xl text-sm font-semibold hover:bg-gray-700">📎 Download</a>
                        @else
                            <p class="text-gray-400 text-sm py-2 px-3 bg-gray-50 rounded-lg border">Tidak ada file dilampirkan.</p>
                        @endif
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <button onclick="closeModal('showTaskModal_{{ $task->id }}')" class="bg-gray-400 text-white rounded-lg py-2 px-4 text-sm">Tutup</button>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Modal Pengumpulan Tugas --}}
    @foreach($tasks as $task)
        @php $tStatus = $task->collections->first()->status ?? 'default'; @endphp
        @if($tStatus === 'Belum mengumpulkan')
        <div id="tugasModal-{{ $task->id }}" class="tugasModal fixed inset-0 hidden items-center justify-center z-50" style="display:none;">
            <div class="bg-white rounded-xl px-5 py-5 w-[95%] max-w-md mx-auto shadow-xl">
                <div class="flex justify-between items-center mb-4">
                    <h5 class="text-lg font-bold">Pengumpulan Tugas</h5>
                    <button onclick="closeModal('tugasModal-{{ $task->id }}')" class="text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
                </div>
                <form action="{{ route('updateCollection', ['task_id' => $task->id]) }}" method="POST"
                      enctype="multipart/form-data" onsubmit="return validateFileBeforeSubmit(this)">
                    @csrf @method('PUT')
                    <div class="mb-5">
                        <label class="text-gray-700 block font-medium mb-2">
                            Upload File <span class="text-sm font-normal text-gray-500">(PDF, JPG, atau PNG)</span>
                        </label>
                        <div class="border-2 rounded-xl border-gray-300 p-3">
                            <input type="file" id="file_collection-{{ $task->id }}" name="file_collection"
                                   class="hidden" accept=".pdf,.jpg,.jpeg,.png"
                                   onchange="handleFileSelect(this,'{{ $task->id }}')">
                            <label for="file_collection-{{ $task->id }}"
                                   class="bg-blue-500 text-white px-4 py-2 rounded cursor-pointer inline-block hover:bg-blue-600 text-sm">
                                Pilih File
                            </label>
                            <span id="file-name-{{ $task->id }}" class="ml-2 text-gray-500 text-sm">Tidak ada file dipilih</span>
                        </div>
                        <p id="file-error-{{ $task->id }}" class="text-red-600 text-xs mt-1 hidden">⚠ Hanya PDF, JPG, atau PNG.</p>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeModal('tugasModal-{{ $task->id }}')"
                                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 text-sm">Batal</button>
                        <button type="submit" id="submit-btn-{{ $task->id }}"
                                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-sm">
                            Unggah Tugas
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    @endforeach

    {{-- Toast lock materi --}}
    <div id="materi-lock-toast"
         class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-[200] bg-amber-600 text-white px-5 py-4 rounded-2xl shadow-2xl max-w-sm w-[90%] text-center">
        <p class="font-semibold text-sm" id="materi-lock-toast-msg"></p>
        <a id="materi-lock-toast-link" href="#"
           class="mt-2 inline-block bg-white text-amber-700 font-bold text-xs px-4 py-1.5 rounded-lg hover:bg-amber-50">
            Buka Materi Sekarang
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        /* ── Modal helpers ── */
        function openModal(id) {
            const m = document.getElementById(id);
            if (m) { m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
        }
        function closeModal(id) {
            const m = document.getElementById(id);
            if (m) { m.style.display = 'none'; document.body.style.overflow = 'auto'; }
        }

        /* ── Tab switching ── */
        const activeTab = '{{ $activeTab }}';

        function switchTab(tab) {
            // Update hidden input agar search tetap tahu tab mana
            document.getElementById('tabInput').value = tab;

            // Update URL tanpa reload supaya UX smooth
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);

            // Toggle content
            ['materi','tugas'].forEach(t => {
                const c = document.getElementById('content-' + t);
                if (c) c.classList.toggle('hidden', t !== tab);
            });

            // Toggle buttons
            ['materi','tugas'].forEach(t => {
                const b = document.getElementById('tab-' + t);
                if (!b) return;
                if (t === tab) { b.className = 'tab-btn active'; }
                else           { b.className = 'tab-btn inactive'; }
            });

            // Tampilkan sort/filter yang sesuai
            const ws = document.getElementById('wrapSort');
            const wf = document.getElementById('wrapFilter');
            if (ws) ws.classList.toggle('hidden', tab !== 'materi');
            if (wf) wf.classList.toggle('hidden', tab !== 'tugas');
        }

        /* ── File upload helpers ── */
        const ALLOWED = ['pdf','jpg','jpeg','png'];
        function handleFileSelect(input, taskId) {
            const file = input.files[0];
            const nameSpan = document.getElementById('file-name-' + taskId);
            const errEl    = document.getElementById('file-error-' + taskId);
            const submitBtn= document.getElementById('submit-btn-' + taskId);
            if (!file) { nameSpan.textContent='Tidak ada file'; errEl.classList.add('hidden'); return; }
            const ext = file.name.split('.').pop().toLowerCase();
            if (!ALLOWED.includes(ext)) {
                input.value=''; nameSpan.textContent='Tidak ada file';
                errEl.classList.remove('hidden');
                if (submitBtn) submitBtn.disabled = true;
            } else {
                nameSpan.textContent = file.name;
                errEl.classList.add('hidden');
                if (submitBtn) submitBtn.disabled = false;
            }
        }
        function validateFileBeforeSubmit(form) {
            const fi = form.querySelector('input[type="file"]');
            if (!fi || !fi.files.length) { alert('Pilih file terlebih dahulu.'); return false; }
            const ext = fi.files[0].name.split('.').pop().toLowerCase();
            if (!ALLOWED.includes(ext)) { alert('File harus PDF, JPG, atau PNG.'); return false; }
            return true;
        }

        /* ── Materi lock (localStorage) ── */
        const taskMateriMap = {!! json_encode(
            $tasks->filter(fn($t) => $t->materi !== null && ($t->collections->first()->status ?? '') === 'Belum mengumpulkan')
                  ->mapWithKeys(fn($t) => [(string)$t->id => $t->materi->id])
        ) !!};

        function isMateriRead(mId) { return localStorage.getItem('materi_read_' + mId) === 'done'; }
        function unlockTaskButton(tId) {
            const locked = document.getElementById('btn-kumpul-locked-' + tId);
            const unlocked= document.getElementById('btn-kumpul-unlocked-' + tId);
            const lb = document.getElementById('materi-lock-badge-' + tId);
            const ub = document.getElementById('materi-unlocked-badge-' + tId);
            if (locked)   locked.classList.add('hidden');
            if (unlocked) unlocked.classList.remove('hidden');
            if (lb)       lb.classList.add('hidden');
            if (ub)       ub.classList.remove('hidden');
        }
        function checkAllLocks() {
            Object.entries(taskMateriMap).forEach(([tId, mId]) => {
                if (isMateriRead(mId)) unlockTaskButton(tId);
            });
        }
        function showMateriLockAlert(mId, url, judul) {
            const toast = document.getElementById('materi-lock-toast');
            document.getElementById('materi-lock-toast-msg').textContent =
                `Baca materi "${judul}" minimal 5 menit sebelum mengumpulkan.`;
            document.getElementById('materi-lock-toast-link').href = url;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 5000);
        }

        /* ── Filter dropdown ── */
        document.addEventListener('DOMContentLoaded', () => {
            // Init tab
            switchTab(activeTab);

            // Loading screen
            const ls = document.getElementById('loadingScreen');
            if (ls) ls.classList.add('hidden');

            checkAllLocks();

            // Filter dropdown toggle
            const filterBtn = document.getElementById('filterBtn');
            const filterMenu= document.getElementById('filterMenu');
            filterBtn?.addEventListener('click', e => {
                e.stopPropagation();
                filterMenu?.classList.toggle('hidden');
            });
            document.addEventListener('click', () => filterMenu?.classList.add('hidden'));
            filterMenu?.addEventListener('click', e => e.stopPropagation());

            // Toast click outside
            document.getElementById('materi-lock-toast')?.addEventListener('click', function(e) {
                if (e.target === this) this.classList.add('hidden');
            });
        });
    </script>
@endsection
