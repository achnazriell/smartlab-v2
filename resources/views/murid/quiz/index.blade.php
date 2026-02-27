@extends('layouts.appSiswa')

@section('content')
    <style>
        /* ── Banner responsive ── */
        .banner-wrap {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(59,130,246,.2);
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
            font-weight: 800;
            color: #fff;
            text-shadow: 0 4px 16px rgba(0,0,0,.35);
            text-align: center;
            line-height: 1.15;
        }
        .banner-desc {
            font-size: clamp(.7rem, 2.2vw, .95rem);
            color: rgba(255,255,255,.95);
            text-align: center;
            margin-top: .5rem;
            line-height: 1.6;
        }

        /* ── Card quiz ── */
        .card-quiz {
            transition: transform .35s cubic-bezier(.34,1.56,.64,1), box-shadow .3s;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(59,130,246,.12);
        }
        .card-quiz:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 48px rgba(59,130,246,.25);
        }
        .quiz-header {
            background-image: url('{{ asset('image/cardquiz.webp') }}');
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .quiz-header::after {
            content:'';
            position:absolute;inset:0;
            background:linear-gradient(135deg,rgba(0,0,0,.18) 0%,rgba(0,0,0,.08) 100%);
        }
        .quiz-header > * { position:relative; z-index:1; }

        /* ── Status badges ── */
        .status-badge { display:inline-block; padding:.4rem .9rem; border-radius:9999px; font-size:.8rem; font-weight:700; letter-spacing:.3px; }
        .status-available { background:rgba(34,197,94,.12); color:#15803d; border:1px solid rgba(34,197,94,.3); }
        .status-ongoing   { background:rgba(245,158,11,.12); color:#92400e; border:1px solid rgba(245,158,11,.3); }
        .status-completed { background:rgba(107,114,128,.12); color:#374151; border:1px solid rgba(107,114,128,.3); }
        .status-upcoming  { background:rgba(59,130,246,.12); color:#1e40af; border:1px solid rgba(59,130,246,.3); }
        .status-waiting   { background:rgba(16,185,129,.12); color:#065f46; border:1px solid rgba(16,185,129,.3); }

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

        /* ── Custom modal ── */
        .custom-modal-backdrop { position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:9998;display:none;opacity:0;transition:opacity .3s; }
        .custom-modal-backdrop.active { display:flex;opacity:1; }
        .custom-modal { background:#fff;border-radius:1rem;box-shadow:0 20px 60px rgba(0,0,0,.3);max-width:400px;width:90%;margin:auto;transform:translateY(-20px);opacity:0;transition:all .3s; }
        .custom-modal.active { transform:translateY(0);opacity:1; }

        @keyframes spin { to { transform:rotate(360deg); } }
        .animate-spin { animation:spin 1s linear infinite; }
        .hidden { display:none!important; }
    </style>

    <div class="p-4 sm:p-6 lg:p-8">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="border-t-4 border-blue-600 rounded-full w-16 h-16 animate-spin"></div>
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
            <img src="{{ asset('image/banner mapel.webp') }}" alt="banner quiz">
            <div class="banner-overlay">
                <p class="banner-title">Hai, {{ Auth::user()->name }}</p>
                <p class="banner-desc max-w-xl">
                    Ikuti quiz interaktif untuk meningkatkan pemahaman materi
                </p>
            </div>
        </div>

        {{-- ── Toolbar ── --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
            <h1 class="text-xl lg:text-2xl font-bold text-blue-900 flex-shrink-0">Quiz Interaktif</h1>

            <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
                {{-- Search --}}
                <form action="{{ route('quiz.index') }}" method="GET" class="flex items-center gap-2 flex-1 sm:flex-none">
                    @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari quiz…"
                           class="flex-1 sm:w-48 px-3 py-2 rounded-xl border border-blue-200 text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-xl transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                            <path d="m19.6 21-6.3-6.3q-.75.6-1.725.95T9.5 16q-2.725 0-4.612-1.888T3 9.5t1.888-4.612T9.5 3t4.613 1.888T16 9.5q0 1.1-.35 2.075T14.7 13.3l6.3 6.3zM9.5 14q1.875 0 3.188-1.312T14 9.5t-1.312-3.187T9.5 5T6.313 6.313T5 9.5t1.313 3.188T9.5 14"/>
                        </svg>
                    </button>
                </form>

                {{-- Filter --}}
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
                        <form action="{{ route('quiz.index') }}" method="GET">
                            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                            @foreach(['' => 'Semua Status', 'available' => 'Tersedia', 'ongoing' => 'Sedang Dikerjakan', 'completed' => 'Selesai', 'upcoming' => 'Akan Datang'] as $val => $label)
                                <button type="submit" name="status" value="{{ $val }}"
                                        class="{{ request('status', '') === $val ? 'font-bold text-blue-700' : '' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active filter --}}
        @if(request('search') || request('status'))
        <div class="mb-4 flex items-center gap-2 text-sm text-gray-500">
            <span>Filter:</span>
            @if(request('search'))
                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-lg">Cari: {{ request('search') }}</span>
            @endif
            @if(request('status'))
                @php $sLabels = ['available'=>'Tersedia','ongoing'=>'Sedang Dikerjakan','completed'=>'Selesai','upcoming'=>'Akan Datang']; @endphp
                <span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-lg">{{ $sLabels[request('status')] ?? request('status') }}</span>
            @endif
            <a href="{{ route('quiz.index') }}" class="text-blue-600 hover:underline">Reset</a>
        </div>
        @endif

        {{-- ── Quiz Grid ── --}}
        @if($quizzes->count() > 0)
            <div class="bg-white rounded-2xl shadow-lg border border-blue-100 p-4 sm:p-6 lg:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($quizzes as $quiz)
                        @php
                            $status = $quiz->display_status ?? 'available';
                            $statusClass = 'status-available'; $statusText = 'Tersedia';
                            $buttonClass = 'bg-blue-600 hover:bg-blue-700';
                            $buttonText = 'Lihat Quiz'; $buttonLink = route('quiz.room', $quiz->id);
                            $disabled = false; $icon = 'fa-eye';

                            if ($status === 'completed') {
                                $statusClass = 'status-completed'; $statusText = 'Selesai';
                                $buttonClass = 'bg-green-600 hover:bg-green-700'; $buttonText = 'Lihat Hasil';
                                $buttonLink = $quiz->last_attempt && $quiz->last_attempt->id
                                    ? route('quiz.result', ['quiz' => $quiz->id, 'attempt' => $quiz->last_attempt->id])
                                    : route('quiz.index');
                                $icon = 'fa-check-circle';
                            } elseif ($quiz->is_room_open && isset($quiz->is_quiz_started) && $quiz->is_quiz_started) {
                                $statusClass = 'status-ongoing'; $statusText = 'Sedang Berlangsung';
                                $buttonClass = 'bg-yellow-500 hover:bg-yellow-600'; $buttonText = 'Gabung Quiz';
                                $buttonLink = route('quiz.room', $quiz->id); $icon = 'fa-play-circle';
                            } elseif (isset($quiz->is_room_open) && $quiz->is_room_open) {
                                $statusClass = 'status-waiting'; $statusText = 'Ruangan Terbuka';
                                $buttonClass = 'bg-blue-600 hover:bg-blue-700'; $buttonText = 'Masuk Ruangan';
                                $buttonLink = route('quiz.room', $quiz->id); $icon = 'fa-door-open';
                            } elseif ($status === 'upcoming') {
                                $statusClass = 'status-upcoming'; $statusText = 'Akan Datang';
                                $buttonClass = 'bg-gray-400 cursor-not-allowed'; $buttonText = 'Belum Dimulai';
                                $disabled = true; $icon = 'fa-clock';
                            } elseif ($status === 'finished') {
                                $statusClass = 'status-completed'; $statusText = 'Selesai';
                                $buttonClass = 'bg-gray-400 cursor-not-allowed'; $buttonText = 'Quiz Berakhir';
                                $disabled = true; $icon = 'fa-calendar-times';
                            }
                        @endphp

                        <div class="card-quiz bg-white">
                            {{-- Quiz header --}}
                            <div class="quiz-header p-5 color-white" style="color:white; min-height:170px; display:flex; flex-direction:column; justify-content:space-between;">
                                <div>
                                    <h3 class="text-lg font-bold mb-2 drop-shadow-lg line-clamp-2 text-white">{{ $quiz->title }}</h3>
                                    <div class="flex items-center text-white/90 text-sm gap-1 mb-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" class="flex-shrink-0"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2M8.5 9.5a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0m9.758 7.484A7.99 7.99 0 0 1 12 20a7.99 7.99 0 0 1-6.258-3.016C7.363 15.821 9.575 15 12 15s4.637.821 6.258 1.984"/></svg>
                                        <span class="ml-1">{{ $quiz->subject->name_subject ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center text-white/80 text-xs gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="flex-shrink-0"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                        <span class="ml-1">{{ $quiz->teacher->user->name ?? 'Guru' }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-sm text-white/90 border-t border-white/20 pt-3 mt-3">
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        <span>{{ $quiz->questions_count ?? 0 }} soal</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        <span>{{ $quiz->duration ?? 0 }} menit</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Card footer --}}
                            <div class="p-4 bg-gray-50">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                    <span class="text-xs text-gray-500 text-right leading-tight">
                                        @if(isset($quiz->end_at) && $quiz->end_at)
                                            Deadline:<br>{{ $quiz->end_at->format('d M Y, H:i') }}
                                        @else
                                            Tanpa Deadline
                                        @endif
                                    </span>
                                </div>
                                @if($disabled)
                                    <button disabled class="w-full {{ $buttonClass }} text-white font-semibold py-2 px-4 rounded-lg text-center block text-sm">
                                        <i class="fas {{ $icon }} mr-1"></i>{{ $buttonText }}
                                    </button>
                                @else
                                    <a href="{{ $buttonLink }}" class="w-full {{ $buttonClass }} text-white font-semibold py-2 px-4 rounded-lg transition-colors text-center block text-sm">
                                        <i class="fas {{ $icon }} mr-1"></i>{{ $buttonText }}
                                    </a>
                                @endif
                                @if($status === 'completed' && isset($quiz->can_retake) && $quiz->can_retake)
                                    <button onclick="showRetakeConfirm('{{ $quiz->id }}','{{ $quiz->title }}')"
                                            class="w-full mt-2 border border-blue-600 text-blue-600 hover:bg-blue-50 font-medium py-2 px-4 rounded-lg text-sm">
                                        <i class="fas fa-redo mr-1"></i>Ulangi Quiz
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($quizzes->hasPages())
                    <div class="mt-8 pt-6 border-t border-blue-100">
                        {{ $quizzes->appends(request()->query())->links('vendor.pagination.tailwind') }}
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-lg border border-blue-100 p-8">
                <div class="py-12 text-center">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-400">
                            <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"/>
                            <line x1="12" y1="9" x2="12" y2="15"/><line x1="9" y1="12" x2="15" y2="12"/>
                        </svg>
                    </div>
                    <p class="text-gray-700 font-semibold text-lg">Belum Ada Quiz</p>
                    <p class="text-gray-400 text-sm mt-1">
                        @if(request()->has('search') || request()->has('status'))
                            Tidak ada quiz yang cocok.
                            <a href="{{ route('quiz.index') }}" class="text-blue-600 hover:underline ml-1">Reset</a>
                        @else
                            Quiz akan muncul di sini setelah ditambahkan.
                        @endif
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- Retake Modal --}}
    <div class="custom-modal-backdrop" id="retakeModalBackdrop">
        <div class="custom-modal">
            <div style="padding:1.5rem 1.5rem 1rem; border-bottom:1px solid #e5e7eb;">
                <h2 class="text-xl font-bold text-gray-900" id="retakeModalTitle"></h2>
                <p class="text-sm text-gray-600 mt-1">Anda yakin ingin mengulang quiz ini?</p>
            </div>
            <div style="padding:1rem 1.5rem;">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm font-medium text-yellow-700">⚠ Hasil percobaan sebelumnya akan dihitung ulang!</p>
                </div>
            </div>
            <div style="padding:1rem 1.5rem; border-top:1px solid #e5e7eb; display:flex; justify-content:flex-end; gap:.75rem;">
                <button onclick="hideRetakeModal()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-medium">Batal</button>
                <button onclick="proceedRetake()"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">Ulangi Quiz</button>
            </div>
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

        let currentRetakeQuizId = null;
        function showRetakeConfirm(quizId, quizTitle) {
            currentRetakeQuizId = quizId;
            document.getElementById('retakeModalTitle').textContent = 'Ulangi Quiz: ' + quizTitle;
            const backdrop = document.getElementById('retakeModalBackdrop');
            const modal    = backdrop.querySelector('.custom-modal');
            backdrop.classList.add('active');
            setTimeout(() => modal.classList.add('active'), 10);
            document.body.style.overflow = 'hidden';
        }
        function hideRetakeModal() {
            const backdrop = document.getElementById('retakeModalBackdrop');
            const modal    = backdrop.querySelector('.custom-modal');
            modal.classList.remove('active');
            setTimeout(() => { backdrop.classList.remove('active'); document.body.style.overflow = ''; }, 300);
        }
        function proceedRetake() {
            if (!currentRetakeQuizId) return;
            hideRetakeModal();
            setTimeout(() => { window.location.href = '/quiz/' + currentRetakeQuizId + '/detail'; }, 300);
        }
        document.getElementById('retakeModalBackdrop')?.addEventListener('click', function(e) {
            if (e.target === this) hideRetakeModal();
        });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') hideRetakeModal(); });
    </script>
@endsection
