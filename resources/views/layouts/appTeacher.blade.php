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
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/flowbite@1.3.4/dist/flowbite.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/algoliasearch@4.10.5/dist/algoliasearch.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/preline@latest/dist/preline.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap');

        :root {
            /* Define custom brand colors matching SmartLab logo */
            --brand-blue: #0095FF;
            --brand-dark: #1E293B;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        .font-poppins {
            font-family: 'Poppins', sans-serif;
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .sidebar-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Updated sidebar styles for modern white aesthetic with brand accents */
        .sidebar-item-active {
            background-color: rgba(0, 149, 255, 0.1);
            color: var(--brand-blue);
            border-right: 3px solid var(--brand-blue);
        }

        .sidebar-item-hover:hover {
            background-color: #f8fafc;
            color: var(--brand-blue);
        }

        /* Header scroll effect */
        .header-scrolled {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
        }

        .table-wrapper table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
        }

        .table-wrapper th,
        .table-wrapper td {
            white-space: nowrap;
        }

        .panel {
            padding: 15px;
        }

        .scroll-inner {
            &::-webkit-scrollbar {
                width: 10px;
            }

            &::-webkit-scrollbar:horizontal {
                height: 10px;
            }

            &::-webkit-scrollbar-track {
                background-color: transparentize(#ccc, 0.7);
            }

            &::-webkit-scrollbar-thumb {
                border-radius: 15px;
                background: transparentize(#ccc, 0.5);
                box-shadow: inset 0 0 6px rgba(255, 255, 255, 0.811);
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/preline@latest/dist/preline.min.js"></script>
</head>

<body class="bg-slate-50 font-sans" x-data="{
    sidebarOpen: true,
    mobileSidebarOpen: false,
    userMenuOpen: false,
    headerScrolled: false,
    isMobile: false
}" x-init="const checkScreen = () => {
    isMobile = window.innerWidth < 768

    if (isMobile) {
        sidebarOpen = false
        mobileSidebarOpen = false
    } else {
        sidebarOpen = true
        mobileSidebarOpen = false
    }
}

checkScreen()
window.addEventListener('resize', checkScreen)
window.addEventListener('scroll', () => headerScrolled = window.scrollY > 10)" x-cloak>

    <div class="flex h-screen overflow-hidden">
        <!-- Mobile overlay backdrop -->
        <div x-show="isMobile && mobileSidebarOpen" x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="mobileSidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm md:hidden">
        </div>

        <!-- Sidebar -->
        <!-- Redesigned sidebar to modern white design with brand accents, using LogoSmartlab.webp -->
        <aside class="fixed top-0 left-0 h-full bg-white border-r shadow-xl z-50 transition-all duration-300"
            :class="isMobile
                ?
                (mobileSidebarOpen ? 'w-72 translate-x-0' : 'w-72 -translate-x-full') :
                (sidebarOpen ? 'w-72' : 'w-20')">

            <!-- Sidebar Header -->
            <div class="h-16 flex items-center border-b border-slate-100">

                <div class="flex items-center w-full transition-all duration-300"
                    :class="(!isMobile && !sidebarOpen) ? 'justify-center' : 'justify-start'">

                    <!-- Logo Mini -->
                    <img x-show="!isMobile && !sidebarOpen" class="w-9 h-9 object-contain"
                        src="{{ asset('image/logo.webp') }}">

                    <!-- Logo Full -->
                    <img x-show="isMobile || sidebarOpen" class="w-auto h-10 object-contain ml-7"
                        src="{{ asset('image/logosl.webp') }}">

                </div>

                <!-- Close Mobile -->
                <button x-show="isMobile" @click="mobileSidebarOpen = false"
                    class="mr-5 p-2 rounded-lg hover:bg-slate-100 lg:hidden">
                    ✕
                </button>

            </div>

            <nav class="mt-4 px-3 space-y-1 overflow-y-auto h-[calc(100vh-80px)] pb-10">
                <!-- Dashboard -->
                <a href="{{ route('homeguru') }}"
                    class="flex items-center py-3 px-4 rounded-xl transition-all duration-200 group relative {{ request()->routeIs('homeguru') ? 'sidebar-item-active' : 'text-slate-600 sidebar-item-hover' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Dashboard'">
                    <svg class="w-6 h-6 flex-shrink-0" :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 00-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen"
                        class="font-medium text-md whitespace-nowrap">Dashboard</span>
                </a>

                <!-- Materi -->
                <a href="{{ route('materis.index') }}"
                    class="flex items-center py-3 px-4 rounded-xl transition-all duration-200 group relative {{ request()->routeIs('materis.*') ? 'sidebar-item-active' : 'text-slate-600 sidebar-item-hover' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Materi'">
                    <svg class="w-6 h-6 flex-shrink-0" :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                        </path>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen"
                        class="font-medium text-md whitespace-nowrap">Materi</span>
                </a>

                <!-- Tugas -->
                <a href="{{ route('tasks.index') }}"
                    class="flex items-center py-3 px-4 rounded-xl transition-all duration-200 group relative {{ request()->routeIs('tasks.*') ? 'sidebar-item-active' : 'text-slate-600 sidebar-item-hover' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Tugas'">
                    <svg class="w-6 h-6 flex-shrink-0" :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                        </path>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen"
                        class="font-medium text-md whitespace-nowrap">Tugas</span>
                </a>

                <!-- SOAL -->
                <a href="{{ route('guru.exams.index') }}"
                    class="flex items-center py-3 px-4 rounded-xl transition-all duration-200 group relative {{ request()->routeIs('guru.*') ? 'sidebar-item-active' : 'text-slate-600 sidebar-item-hover' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Soal'">
                    <svg class="w-6 h-6 flex-shrink-0" :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen"
                        class="font-medium text-md whitespace-nowrap">Soal</span>
                </a>
            </nav>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col transition-all duration-300 w-full overflow-x-hidden"
            :class="{
                'md:ml-72': sidebarOpen && !isMobile,
                'md:ml-20': !sidebarOpen && !isMobile,
                'ml-0': isMobile
            }">
            <!-- Header -->
            <header
                class="bg-white/80 backdrop-blur-md border-b border-slate-200 shadow-md sticky top-0 z-30 transition-all duration-300">
                <div class="flex justify-between items-center px-6 py-3">
                    <!-- Tombol hamburger hanya muncul di mobile -->
                    <div class="flex items-center">
                        <button @click="mobileSidebarOpen = true"
                            class="md:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600 mr-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <div class="hidden md:flex items-center "
                            :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'w-full'">
                            <button @click="isMobile ? mobileSidebarOpen = false : sidebarOpen = !sidebarOpen"
                                class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Mobile centered logo -->
                    <div class="md:hidden absolute left-1/2 transform -translate-x-1/2 flex items-center ml-11">
                        <img alt="Logo" src="{{ asset('image/logosl.webp') }}" class="h-9 w-auto " />
                    </div>

                    <div class="flex items-center space-x-2 md:space-x-3">
                        <div class="hidden md:flex flex-col text-right">
                            <div>
                                <p class="font-semibold text-slate-800">{{ Auth::user()->name }}</p>
                            </div>
                            <div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ Auth::user()->getRoleNames()->first() }}
                                </span>
                            </div>
                        </div>

                        <!-- Bagian dropdown profile Guru -->
                        <div class="relative" x-data="{ profileOpen: false }">
                            {{-- Update tombol avatar --}}
                            <button @click="profileOpen = !profileOpen"
                                class="w-12 h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center transition-colors shadow-md hover:shadow-lg overflow-hidden">

                                @php
                                    $user = Auth::user();
                                    $photoPath = $user->profile_photo
                                        ? 'uploads/profile-photos/' . $user->profile_photo
                                        : null;
                                    $photoExists = $photoPath && file_exists(public_path($photoPath));
                                @endphp

                                @if ($photoExists)
                                    <img src="{{ asset($photoPath) }}" alt="{{ $user->name }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </button>

                            {{-- Dropdown menu --}}
                            <div x-show="profileOpen" @click.outside="profileOpen = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-5 w-64 bg-white rounded-lg shadow-lg border border-slate-200 z-50">
                                <div class="border-t border-slate-200">
                                    {{-- Tombol Profile --}}
                                    <a href="{{ route('profile.index') }}"
                                        class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                        <svg class="w-4 h-4 mr-3 text-slate-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Lihat Profile
                                    </a>
                                    {{-- Tombol Feedback --}}
                                    <a href="{{ route('feedbacks.index') }}"
                                        class="flex items-center px-4 py-3 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                        <svg class="w-4 h-4 mr-3 text-slate-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                        </svg>
                                        Feedback & Laporan
                                    </a>
                                    {{-- Tombol Logout --}}
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="w-full flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors border-t border-slate-200">
                                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6-4v8" />
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

            <main class="overflow-y-auto bg-slate-50 p-6 flex-1 overflow-x-hidden">
                @yield('content')
            </main>
        </div>
    </div>

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: '{{ session('error') }}',
                background: '#f8fafc',
                color: '#1e293b',
                confirmButtonColor: '#3b82f6',
            });
        </script>
    @endif
</body>

</html>
