<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/webp" href="{{ asset('image/logo.webp') }}">
    <title>@yield('title', 'Smart Lab - Guru')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite([ 'resources/js/app.js'])
    <script src="https://unpkg.com/flowbite@1.3.4/dist/flowbite.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        :root {
            --brand-blue: #2563EB;
            --brand-blue-light: #EFF6FF;
            --brand-blue-mid: #DBEAFE;
            --sidebar-width: 264px;
            --sidebar-collapsed: 72px;
        }

        * { font-family: 'Inter', sans-serif; }
        .font-jakarta { font-family: 'Plus Jakarta Sans', sans-serif; }

        [x-cloak] { display: none !important; }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }

        /* Sidebar active state */
        .nav-item-active {
            background: var(--brand-blue-light);
            color: var(--brand-blue);
            border-left: 3px solid var(--brand-blue);
        }
        .nav-item-active .nav-icon { color: var(--brand-blue); }
        .nav-item-active .nav-badge { background: var(--brand-blue); color: white; }

        .nav-item {
            border-left: 3px solid transparent;
            transition: all 0.18s ease;
        }
        .nav-item:hover {
            background: #F8FAFC;
            color: var(--brand-blue);
            border-left-color: #BFDBFE;
        }
        .nav-item:hover .nav-icon { color: var(--brand-blue); }

        /* Section dividers */
        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #94A3B8;
            padding: 0 16px;
            margin: 8px 0 4px;
        }

        .sidebar-transition { transition: width 0.25s cubic-bezier(0.4,0,0.2,1), transform 0.25s cubic-bezier(0.4,0,0.2,1); }

        /* Header breadcrumb */
        .page-breadcrumb {
            font-size: 13px;
            color: #64748B;
        }

        /* Notification dot */
        .notif-dot {
            width: 8px; height: 8px;
            background: #EF4444;
            border-radius: 50%;
            position: absolute; top: 8px; right: 8px;
        }

        /* Profile card in sidebar */
        .sidebar-profile-card {
            background: linear-gradient(135deg, #EFF6FF, #DBEAFE);
            border: 1px solid #BFDBFE;
            border-radius: 12px;
        }
    </style>
</head>

<body class="bg-slate-50" x-data="{
    sidebarOpen: true,
    mobileSidebarOpen: false,
    isMobile: false,
    currentPage: '{{ request()->route()->getName() ?? '' }}'
}" x-init="
    const check = () => {
        isMobile = window.innerWidth < 1024;
        if (isMobile) { sidebarOpen = false; }
        else { sidebarOpen = true; }
    };
    check();
    window.addEventListener('resize', check);
" x-cloak>

    <div class="flex h-screen overflow-hidden">

        {{-- ===== MOBILE OVERLAY ===== --}}
        <div x-show="isMobile && mobileSidebarOpen"
            x-transition:enter="transition-opacity duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="mobileSidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden">
        </div>

        {{-- ===== SIDEBAR ===== --}}
        <aside class="fixed top-0 left-0 h-full bg-white border-r border-slate-200 z-50 flex flex-col sidebar-transition overflow-hidden"
            :style="isMobile
                ? (mobileSidebarOpen ? 'width:264px;transform:translateX(0)' : 'width:264px;transform:translateX(-100%)')
                : (sidebarOpen ? 'width:264px' : 'width:72px')">

            {{-- Logo Area --}}
            <div class="h-16 flex items-center border-b border-slate-100 flex-shrink-0 px-4 relative">
                <div class="flex items-center w-full overflow-hidden">
                    {{-- Full Logo --}}
                    <div x-show="sidebarOpen || mobileSidebarOpen" class="flex items-center gap-2 ml-2">
                        <img src="{{ asset('image/logosl.webp') }}" alt="SmartLab" class="h-9 w-auto object-contain">
                    </div>
                    {{-- Mini Logo --}}
                    <div x-show="!sidebarOpen && !mobileSidebarOpen" class="flex items-center justify-center w-full">
                        <img src="{{ asset('image/logo.webp') }}" alt="SL" class="w-8 h-8 object-contain">
                    </div>
                </div>
                {{-- Mobile close --}}
                <button x-show="isMobile" @click="mobileSidebarOpen = false"
                    class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 rounded-lg hover:bg-slate-100 text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5">

                {{-- MAIN --}}
                <div x-show="sidebarOpen || mobileSidebarOpen" class="nav-section-label">Utama</div>

                {{-- Dashboard --}}
                <a href="{{ route('homeguru') }}"
                    class="nav-item flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition-all group
                    {{ request()->routeIs('homeguru') ? 'nav-item-active' : 'text-slate-600' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center px-2'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Dashboard'">
                    <span class="nav-icon flex-shrink-0 {{ request()->routeIs('homeguru') ? 'text-blue-600' : 'text-slate-500' }} group-hover:text-blue-600 transition-colors"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </span>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="whitespace-nowrap">Dashboard</span>
                </a>

                {{-- Kelas Saya --}}
                <a href="{{ route('class.index') }}"
                    class="nav-item flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition-all group
                    {{ request()->routeIs('guru.kelas') || request()->routeIs('class.*') ? 'nav-item-active' : 'text-slate-600' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center px-2'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Kelas Saya'">
                    <span class="nav-icon flex-shrink-0 {{ request()->routeIs('guru.kelas') || request()->routeIs('class.*') ? 'text-blue-600' : 'text-slate-500' }} group-hover:text-blue-600 transition-colors"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </span>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="whitespace-nowrap">Kelas Saya</span>
                </a>

                {{-- KONTEN --}}
                <div x-show="sidebarOpen || mobileSidebarOpen" class="nav-section-label mt-4">Konten</div>

                {{-- Materi --}}
                <a href="{{ route('materis.index') }}"
                    class="nav-item flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition-all group
                    {{ request()->routeIs('materis.*') ? 'nav-item-active' : 'text-slate-600' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center px-2'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Materi'">
                    <span class="nav-icon flex-shrink-0 {{ request()->routeIs('materis.*') ? 'text-blue-600' : 'text-slate-500' }} group-hover:text-blue-600 transition-colors"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </span>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="whitespace-nowrap">Materi</span>
                </a>

                {{-- Tugas --}}
                <a href="{{ route('tasks.index') }}"
                    class="nav-item flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition-all group
                    {{ request()->routeIs('tasks.*') ? 'nav-item-active' : 'text-slate-600' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center px-2'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Tugas'">
                    <span class="nav-icon flex-shrink-0 {{ request()->routeIs('tasks.*') ? 'text-blue-600' : 'text-slate-500' }} group-hover:text-blue-600 transition-colors"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </span>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="whitespace-nowrap">Tugas</span>
                </a>

                {{-- PENILAIAN --}}
                <div x-show="sidebarOpen || mobileSidebarOpen" class="nav-section-label mt-4">Penilaian</div>

                {{-- Ujian --}}
                <a href="{{ route('guru.exams.index') }}"
                    class="nav-item flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition-all group
                    {{ request()->routeIs('guru.exams*') ? 'nav-item-active' : 'text-slate-600' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center px-2'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Ujian'">
                    <span class="nav-icon flex-shrink-0 {{ request()->routeIs('guru.exams*') ? 'text-blue-600' : 'text-slate-500' }} group-hover:text-blue-600 transition-colors"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </span>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="whitespace-nowrap">Ujian</span>
                </a>

                {{-- Quiz --}}
                <a href="{{ route('guru.quiz.index') }}"
                    class="nav-item flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition-all group
                    {{ request()->routeIs('guru.quiz*') ? 'nav-item-active' : 'text-slate-600' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center px-2'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Quiz Interaktif'">
                    <span class="nav-icon flex-shrink-0 {{ request()->routeIs('guru.quiz*') ? 'text-blue-600' : 'text-slate-500' }} group-hover:text-blue-600 transition-colors"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </span>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="whitespace-nowrap">Quiz Interaktif</span>
                    {{-- Badge active count --}}
                </a>

                {{-- LAINNYA --}}
                <div x-show="sidebarOpen || mobileSidebarOpen" class="nav-section-label mt-4">Lainnya</div>

                {{-- Feedback --}}
                <a href="{{ route('feedbacks.index') }}"
                    class="nav-item flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition-all group
                    {{ request()->routeIs('feedbacks.*') ? 'nav-item-active' : 'text-slate-600' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center px-2'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Feedback'">
                    <span class="nav-icon flex-shrink-0 {{ request()->routeIs('feedbacks.*') ? 'text-blue-600' : 'text-slate-500' }} group-hover:text-blue-600 transition-colors"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                        </svg>
                    </span>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="whitespace-nowrap">Feedback</span>
                </a>

                {{-- Profile --}}
                <a href="{{ route('profile.index') }}"
                    class="nav-item flex items-center rounded-xl px-3 py-2.5 text-sm font-medium transition-all group
                    {{ request()->routeIs('profile.*') ? 'nav-item-active' : 'text-slate-600' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center px-2'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Profil Saya'">
                    <span class="nav-icon flex-shrink-0 {{ request()->routeIs('profile.*') ? 'text-blue-600' : 'text-slate-500' }} group-hover:text-blue-600 transition-colors"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </span>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="whitespace-nowrap">Profil Saya</span>
                </a>

            </nav>

            {{-- ===== SIDEBAR FOOTER - Profile Card ===== --}}
            <div x-show="sidebarOpen || mobileSidebarOpen" class="flex-shrink-0 p-3 border-t border-slate-100">
                <div class="sidebar-profile-card p-3 flex items-center gap-3">
                    @php
                        $user = Auth::user();
                        $photoPath = $user->profile_photo ? 'uploads/profile-photos/' . $user->profile_photo : null;
                        $photoExists = $photoPath && file_exists(public_path($photoPath));
                    @endphp
                    <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center flex-shrink-0 overflow-hidden">
                        @if($photoExists)
                            <img src="{{ asset($photoPath) }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-white text-sm font-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-800 truncate">{{ $user->name }}</p>
                        <p class="text-xs text-blue-600 font-medium truncate">{{ $user->getRoleNames()->first() }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Keluar"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Collapsed mini avatar --}}
            <div x-show="!sidebarOpen && !mobileSidebarOpen" class="flex-shrink-0 p-3 border-t border-slate-100 flex justify-center">
                <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center overflow-hidden">
                    @if($photoExists)
                        <img src="{{ asset($photoPath) }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-white text-sm font-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                    @endif
                </div>
            </div>

        </aside>

        {{-- ===== MAIN CONTENT ===== --}}
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-250"
            :style="isMobile ? 'margin-left:0' : (sidebarOpen ? 'margin-left:264px' : 'margin-left:72px')">

            {{-- HEADER --}}
            <header class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-30 flex-shrink-0">
                <div class="flex items-center justify-between h-16 px-4 md:px-6">

                    {{-- Left: Toggle + Breadcrumb --}}
                    <div class="flex items-center gap-3">
                        {{-- Mobile hamburger --}}
                        <button @click="mobileSidebarOpen = true"
                            class="lg:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        {{-- Desktop toggle --}}
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="hidden lg:flex p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        {{-- Mobile Logo --}}
                        <div class="lg:hidden">
                            <img src="{{ asset('image/logosl.webp') }}" alt="SmartLab" class="h-8 w-auto">
                        </div>

                    </div>

                    {{-- Right: Actions + Profile --}}
                    <div class="flex items-center gap-2">


                        {{-- Divider --}}
                        <div class="w-px h-6 bg-slate-200"></div>

                        {{-- Profile Dropdown --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl hover:bg-slate-100 transition-colors">
                                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center overflow-hidden flex-shrink-0">
                                    @if($photoExists)
                                        <img src="{{ asset($photoPath) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-white text-xs font-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="hidden md:block text-left">
                                    <p class="text-sm font-semibold text-slate-800 leading-tight">{{ Str::limit($user->name, 18) }}</p>
                                    <p class="text-xs text-slate-500 leading-tight">{{ $user->getRoleNames()->first() }}</p>
                                </div>
                                <svg class="w-4 h-4 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open" @click.outside="open = false"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-slate-200 z-50 py-1 overflow-hidden">

                                {{-- User info header --}}
                                <div class="px-4 py-3 border-b border-slate-100">
                                    <p class="text-sm font-semibold text-slate-800">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $user->email }}</p>
                                </div>

                                <a href="{{ route('profile.index') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Profil Saya
                                </a>

                                <a href="{{ route('feedbacks.index') }}"
                                    class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                    Feedback & Laporan
                                </a>

                                <div class="border-t border-slate-100 mt-1">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            Keluar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- MAIN CONTENT --}}
            <main class="flex-1 overflow-y-auto overflow-x-hidden bg-slate-50 p-5 md:p-6">
                @yield('content')
            </main>

        </div>
    </div>

</body>
</html>
