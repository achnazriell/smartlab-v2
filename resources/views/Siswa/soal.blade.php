@extends('layouts.appSiswa')

@section('content')
    <style>
        /* ── Banner responsive ── */
        .banner-wrap {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(37,99,235,.15);
        }
        .banner-wrap img {
            width: 100%;
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
            padding: clamp(.75rem,4vw,2.5rem);
            background: rgba(0,0,0,.2);
        }
        .banner-title {
            font-size: clamp(1.2rem, 4.5vw, 2.2rem);
            font-weight: 700;
            color: #fff;
            text-shadow: 0 3px 12px rgba(0,0,0,.35);
            text-align: center;
            line-height: 1.2;
        }
        .banner-desc {
            font-size: clamp(.7rem, 2.2vw, .95rem);
            color: rgba(255,255,255,.92);
            text-align: center;
            margin-top: .5rem;
            line-height: 1.5;
        }

        /* ── Card soal ── */
        .card-soal {
            transition: transform .3s cubic-bezier(.4,0,.2,1), box-shadow .3s, border-color .3s;
            border-radius: 16px;
            overflow: hidden;
            border: 2px solid rgba(37,99,235,.1);
        }
        .card-soal:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(37,99,235,.2);
            border-color: rgba(37,99,235,.3);
        }

        /* ── Status badges ── */
        .status-badge { display:inline-block; padding:.35rem .75rem; border-radius:9999px; font-size:.8rem; font-weight:600; }
        .status-belum  { background:rgba(96,165,250,.12); color:#1e40af; }
        .status-sudah  { background:rgba(34,197,94,.12);  color:#15803d; }
        .status-kadaluarsa { background:rgba(239,68,68,.12); color:#991b1b; }
        .status-ongoing    { background:rgba(245,158,11,.12); color:#92400e; }
        .status-upcoming   { background:rgba(139,92,246,.12); color:#5b21b6; }
        .card-overlay { background:linear-gradient(to top,rgba(0,0,0,.55) 0%,rgba(0,0,0,.15) 60%,transparent 100%); }

        /* ── Filter dropdown ── */
        .fd-wrap { position:relative; }
        .fd-menu {
            position:absolute; right:0; top:calc(100% + 6px);
            width:200px; background:#fff;
            border:1px solid #dde6ff; border-radius:14px;
            box-shadow:0 8px 24px rgba(37,99,235,.14);
            z-index:100; padding:.4rem 0;
        }
        .fd-menu form button {
            display:block; width:100%; text-align:left;
            padding:.5rem 1rem; font-size:.83rem;
            color:#1e3a8a; background:none; border:none;
            cursor:pointer; transition:background .15s;
        }
        .fd-menu form button:hover { background:#eff6ff; }

        @keyframes spin { to { transform:rotate(360deg); } }
        .animate-spin { animation:spin 1s linear infinite; }
        .hidden { display:none!important; }
    </style>

    <div class="p-4 sm:p-6 lg:p-8">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
        </div>

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <strong>Error:</strong> {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        {{-- ── Banner ── --}}
        <div class="banner-wrap mb-6">
            <img src="{{ asset('image/banner mapel.webp') }}" alt="banner soal">
            <div class="banner-overlay">
                <p class="banner-title">Hai, {{ Auth::user()->name }}</p>
                <p class="banner-desc max-w-xl">
                    Latihan soal adalah kunci kesuksesan. Asah kemampuanmu!
                </p>
            </div>
        </div>

        {{-- ── Toolbar ── --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
            <h1 class="text-xl lg:text-2xl font-bold text-blue-900 flex-shrink-0">Daftar Ujian</h1>

            <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
                {{-- Search --}}
                <form action="{{ route('soal.index') }}" method="GET" class="flex items-center gap-2 flex-1 sm:flex-none">
                    @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari ujian…"
                           class="flex-1 sm:w-48 px-3 py-2 rounded-xl border border-blue-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-xl transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="m19.6 21-6.3-6.3q-.75.6-1.725.95T9.5 16q-2.725 0-4.612-1.888T3 9.5t1.888-4.612T9.5 3t4.613 1.888T16 9.5q0 1.1-.35 2.075T14.7 13.3l6.3 6.3zM9.5 14q1.875 0 3.188-1.312T14 9.5t-1.312-3.187T9.5 5T6.313 6.313T5 9.5t1.313 3.188T9.5 14"/>
                        </svg>
                    </button>
                </form>

                {{-- Filter status --}}
                <div class="fd-wrap">
                    <button id="filterBtn"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-xl flex items-center gap-1.5 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z"/></svg>
                        Filter
                        @if(request('status'))
                            <span class="w-2 h-2 bg-yellow-300 rounded-full"></span>
                        @endif
                    </button>
                    <div id="filterMenu" class="fd-menu hidden">
                        <p class="px-3 pt-1 pb-2 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</p>
                        <form action="{{ route('soal.index') }}" method="GET">
                            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                            @foreach(['' => 'Semua Status', 'available' => 'Belum Dikerjakan', 'ongoing' => 'Sedang Dikerjakan', 'completed' => 'Sudah Dikerjakan', 'upcoming' => 'Akan Datang', 'expired' => 'Kadaluarsa'] as $val => $label)
                                <button type="submit" name="status" value="{{ $val }}"
                                        class="{{ request('status') === $val ? 'font-bold text-blue-700' : '' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active filter display --}}
        @if(request('search') || request('status'))
        <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
            <span>Filter:</span>
            @if(request('search'))
                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-lg">Cari: {{ request('search') }}</span>
            @endif
            @if(request('status'))
                @php $labels = ['available'=>'Belum Dikerjakan','ongoing'=>'Sedang Dikerjakan','completed'=>'Sudah Dikerjakan','upcoming'=>'Akan Datang','expired'=>'Kadaluarsa']; @endphp
                <span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-lg">{{ $labels[request('status')] ?? request('status') }}</span>
            @endif
            <a href="{{ route('soal.index') }}" class="text-blue-600 hover:underline">Reset</a>
        </div>
        @endif

        {{-- Cards grid --}}
        <div class="bg-white rounded-2xl shadow-lg border border-blue-100 p-4 sm:p-6 lg:p-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($exams as $exam)
                    @php
                        $totalSoal = $exam->questions_count ?? $exam->questions->count();
                        $attempt   = $exam->attempt ?? null;
                        $attemptCount = $exam->attempt_count ?? 0;

                        if ($attempt && $attempt->status === 'in_progress') {
                            $status = 'ongoing'; $statusText = 'Sedang Dikerjakan'; $statusClass = 'status-ongoing';
                            $buttonText = 'Lanjutkan'; $buttonLink = route('soal.attempt', $exam->id);
                            $buttonClass = 'bg-yellow-500 hover:bg-yellow-600'; $icon = 'fa-arrow-right';
                        } elseif ($attempt && in_array($attempt->status, ['submitted','timeout'])) {
                            $status = 'completed'; $statusText = 'Sudah Dikerjakan'; $statusClass = 'status-sudah';
                            $buttonText = 'Lihat Hasil'; $buttonLink = route('soal.result', $attempt->id);
                            $buttonClass = 'bg-green-600 hover:bg-green-700'; $icon = 'fa-eye';
                        } else {
                            $now = now();
                            if ($exam->start_at && $now < $exam->start_at) {
                                $status = 'upcoming'; $statusText = 'Akan Datang'; $statusClass = 'status-upcoming';
                                $buttonText = 'Lihat Detail'; $buttonLink = route('soal.detail', $exam->id);
                                $buttonClass = 'bg-purple-600 hover:bg-purple-700'; $icon = 'fa-eye';
                            } elseif ($exam->end_at && $now > $exam->end_at) {
                                $status = 'expired'; $statusText = 'Kadaluarsa'; $statusClass = 'status-kadaluarsa';
                                $buttonText = 'Lihat Detail'; $buttonLink = route('soal.detail', $exam->id);
                                $buttonClass = 'bg-gray-500 hover:bg-gray-600'; $icon = 'fa-eye';
                            } else {
                                $status = 'available'; $statusText = 'Belum Dikerjakan'; $statusClass = 'status-belum';
                                $buttonText = 'Mulai Ujian'; $buttonLink = route('soal.detail', $exam->id);
                                $buttonClass = 'bg-blue-600 hover:bg-blue-700'; $icon = 'fa-play';
                            }
                        }
                    @endphp

                    <div class="card-soal bg-white shadow-md">
                        {{-- Card header --}}
                        <div class="relative h-44 flex flex-col justify-between text-white p-5"
                             style="background-image:url('{{ asset('image/cardmapel.webp') }}');background-size:cover;background-position:center;">
                            <div class="card-overlay absolute inset-0"></div>
                            <div class="relative z-10 flex-1 flex flex-col justify-center">
                                <h3 class="text-lg font-bold drop-shadow-lg line-clamp-2 mb-1">{{ $exam->title }}</h3>
                                <p class="text-white/90 text-sm flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2M8.5 9.5a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0m9.758 7.484A7.99 7.99 0 0 1 12 20a7.99 7.99 0 0 1-6.258-3.016C7.363 15.821 9.575 15 12 15s4.637.821 6.258 1.984"/></svg>
                                    <span class="truncate">{{ $exam->subject->name_subject ?? 'N/A' }}</span>
                                </p>
                                <p class="text-white/80 text-xs flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                    <span class="truncate">{{ $exam->teacher->user->name ?? 'N/A' }}</span>
                                </p>
                            </div>
                            <div class="relative z-10 flex items-center justify-between text-sm text-white/90 border-t border-white/25 pt-3">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    <strong>{{ $totalSoal }}</strong>&nbsp;soal
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    {{ $exam->duration ?? 0 }}&nbsp;menit
                                </span>
                            </div>
                        </div>
                        {{-- Card footer --}}
                        <div class="p-4 bg-gray-50">
                            <div class="flex items-center justify-between mb-3 gap-2">
                                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                <span class="text-xs text-gray-500 text-right leading-tight">
                                    @if($exam->end_at)
                                        Deadline:<br>{{ $exam->end_at->format('d M Y, H:i') }}
                                    @else
                                        Tanpa Deadline
                                    @endif
                                </span>
                            </div>
                            <a href="{{ $buttonLink }}"
                               class="w-full {{ $buttonClass }} text-white font-semibold py-2 px-4 rounded-lg transition-colors text-center block text-sm">
                                <i class="fas {{ $icon }} mr-1"></i>{{ $buttonText }}
                            </a>
                            @if($status === 'completed' && $exam->limit_attempts > 0)
                                <div class="mt-2 text-xs text-gray-500 text-center">
                                    Percobaan: {{ $attemptCount }}/{{ $exam->limit_attempts }}
                                    @if($exam->limit_attempts > $attemptCount)
                                        <span class="text-green-600 font-medium"> • Dapat mengulang</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-400">
                                <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                            </svg>
                        </div>
                        <p class="text-gray-700 font-semibold text-lg">Belum Ada Ujian</p>
                        <p class="text-gray-400 text-sm mt-1">
                            @if(request('search') || request('status'))
                                Tidak ada ujian yang cocok dengan filter.
                                <a href="{{ route('soal.index') }}" class="text-blue-600 hover:underline ml-1">Reset</a>
                            @else
                                Ujian akan muncul di sini setelah ditambahkan oleh guru.
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>

            @if($exams instanceof \Illuminate\Contracts\Pagination\Paginator && $exams->hasPages())
                <div class="mt-8 pt-6 border-t border-blue-100">
                    {{ $exams->appends(request()->query())->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Loading
            const ls = document.getElementById('loadingScreen');
            if (ls) { setTimeout(() => { ls.style.opacity='0'; setTimeout(() => ls.style.display='none',300); }, 400); }

            // Auto-hide alerts
            setTimeout(() => {
                document.querySelectorAll('.bg-red-100,.bg-green-100').forEach(el => {
                    el.style.transition='opacity .4s'; el.style.opacity='0';
                    setTimeout(() => el.remove(), 400);
                });
            }, 5000);

            // Filter dropdown
            const filterBtn  = document.getElementById('filterBtn');
            const filterMenu = document.getElementById('filterMenu');
            filterBtn?.addEventListener('click', e => { e.stopPropagation(); filterMenu?.classList.toggle('hidden'); });
            document.addEventListener('click', () => filterMenu?.classList.add('hidden'));
            filterMenu?.addEventListener('click', e => e.stopPropagation());
        });
    </script>
@endsection
