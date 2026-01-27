<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/webp" href="{{ asset('image/logo.webp') }}">
    <title>@yield('title', 'Smart Lab - Admin')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/flowbite@1.3.4/dist/flowbite.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
            /* Defining custom brand colors matching the SmartLab logo */
            --brand-blue: #0095FF;
            /* Matching the vibrant blue in the logo */
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

        /* New sidebar styles for a cleaner white aesthetic with brand accents */
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
        <!-- Memastikan backdrop benar-benar menutupi seluruh layar di bawah z-index sidebar -->
        <div x-show="isMobile && mobileSidebarOpen" x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="mobileSidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm md:hidden">
        </div>

        <!-- Sidebar -->
        <!-- Updated sidebar to a modern white design with brand blue accents -->
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
                        src="{{ asset('image/LogoSmartlab.webp') }}">

                </div>

                <!-- Close Mobile -->
                <button x-show="isMobile" @click="mobileSidebarOpen = false"
                    class="mr-5 p-2 rounded-lg hover:bg-slate-100 lg:hidden">
                    ✕
                </button>

            </div>

            <nav class="mt-4 px-3 space-y-1 overflow-y-auto h-[calc(100vh-80px)] pb-10">
                <!-- Dashboard -->
                <a href="{{ route('home') }}"
                    class="flex items-center py-3 px-4 rounded-xl transition-all duration-200 group relative {{ request()->routeIs('dashboard') ? 'sidebar-item-active' : 'text-slate-600 sidebar-item-hover' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Dashboard'">
                    <svg class="w-5 h-5 flex-shrink-0" :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 00-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                        </path>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen"
                        class="font-medium whitespace-nowrap">Dashboard</span>
                </a>

                <!-- Kelola User Dropdown -->
                <div x-data="{ open: {{ request()->routeIs('teachers.*') || request()->is('Students*') ? 'true' : 'false' }} }">
                    <button @click="if(!(sidebarOpen || mobileSidebarOpen)) { sidebarOpen = true }; open = !open"
                        class="w-full flex items-center py-3 px-4 rounded-xl text-slate-600 sidebar-item-hover transition-all duration-200 group relative {{ request()->routeIs('teachers.*') || request()->is('Students*') ? 'text-blue-600' : '' }}"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'justify-between' : 'justify-center'">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 flex-shrink-0" :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                            <span x-show="sidebarOpen || mobileSidebarOpen" class="font-medium whitespace-nowrap">Kelola
                                User</span>
                        </div>
                        <svg x-show="sidebarOpen || mobileSidebarOpen" class="w-4 h-4 transition-transform duration-200"
                            :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>

                    <div x-show="open && (sidebarOpen || mobileSidebarOpen)" x-collapse
                        class="pl-12 pr-4 pb-2 space-y-1">
                        <a href="{{ route('teachers.index') }}"
                            class="block py-2 text-sm font-medium transition-colors {{ request()->routeIs('teachers.*') ? 'text-blue-600' : 'text-slate-500 hover:text-blue-600' }}">
                            Guru
                        </a>
                        <a href="/Students"
                            class="block py-2 text-sm font-medium transition-colors {{ request()->is('Students*') ? 'text-blue-600' : 'text-slate-500 hover:text-blue-600' }}">
                            Murid
                        </a>
                    </div>
                </div>

                <!-- Other items updated to match new style -->
                <a href="{{ route('classes.index') }}"
                    class="flex items-center py-3 px-4 rounded-xl transition-all duration-200 group relative {{ request()->routeIs('classes.index') ? 'sidebar-item-active' : 'text-slate-600 sidebar-item-hover' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'">
                    <svg class="w-5 h-5 flex-shrink-0" :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="font-medium whitespace-nowrap">Kelola
                        Kelas</span>
                </a>

                <a href="{{ route('subject.index') }}"
                    class="flex items-center py-3 px-4 rounded-xl transition-all duration-200 group relative {{ request()->routeIs('subject.index') ? 'sidebar-item-active' : 'text-slate-600 sidebar-item-hover' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'">
                    <svg class="w-5 h-5 flex-shrink-0" :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="font-medium whitespace-nowrap">Kelola
                        Mapel</span>
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
                        <img alt="Logo" src="{{ asset('image/LogoSmartlab.webp') }}" class="h-9 w-auto " />
                    </div>

                    <div class="flex items-center space-x-2 md:space-x-4">
                        <div class="hidden md:flex flex-col text-right">
                            <p class="font-semibold text-slate-800">{{ Auth::user()->name }}</p>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ Auth::user()->getRoleNames()->first() }}
                            </span>
                        </div>

                        <div class="relative" x-data="{ profileOpen: false }">
                            <button @click="profileOpen = !profileOpen"
                                class="flex items-center p-2 text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="h-5 w-5">
                                    <path fill-rule="evenodd"
                                        d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div x-show="profileOpen" @click.outside="profileOpen = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-slate-200 z-50">
                                <div class="px-4 py-3 border-b border-slate-200">
                                    <h3 class="font-semibold text-slate-800">Profil Pengguna</h3>
                                </div>
                                <div class="px-4 py-3 space-y-2">
                                    <p class="text-sm text-slate-600"><span class="font-medium">Nama:</span>
                                        {{ Auth::user()->name }}</p>
                                    <p class="text-sm text-slate-600"><span class="font-medium">Email:</span>
                                        {{ Auth::user()->email }}</p>
                                </div>
                                <div class="px-4 py-3 border-t border-slate-200">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="w-full flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 text-sm font-medium">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
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

            <main class="overflow-y-auto bg-slate-50 flex-1 overflow-x-hidden">
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
