<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    <title>SmartLab</title>
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

        .card-shadow {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .card-shadow-lg {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        [x-cloak] {
            display: none !important;
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
    sidebarOpen: false,
    mobileSidebarOpen: false,
    userMenuOpen: false,
    headerScrolled: false,
    isMobile: false
}" x-init="const checkScreen = () => {
    isMobile = window.innerWidth < 768;

    if (isMobile) {
        sidebarOpen = false;
        mobileSidebarOpen = false;
    } else {
        sidebarOpen = true;
        mobileSidebarOpen = false;
    }
};

checkScreen();

window.addEventListener('resize', checkScreen);

window.addEventListener('scroll', () => {
    headerScrolled = window.scrollY > 10;
});" x-cloak>
    <div class="flex h-screen overflow-hidden">
        <!-- Mobile overlay backdrop -->
        <!-- Memastikan backdrop benar-benar menutupi seluruh layar di bawah z-index sidebar -->
        <div x-show="isMobile && mobileSidebarOpen" x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="mobileSidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/60 md:hidden">
        </div>

        <!-- Sidebar diperbaiki logic classnya agar tidak mini di mobile -->
        <aside
            class="sidebar-transition bg-gradient-to-b from-blue-600 via-blue-700 to-blue-800 shadow-2xl border-r border-blue-300 fixed left-0 top-0 h-full z-50"
            :class="{
                'w-80': sidebarOpen && !isMobile,
                'w-16': !sidebarOpen && !isMobile,
                'w-80 translate-x-0': isMobile && mobileSidebarOpen,
                'w-80 -translate-x-full': isMobile && !mobileSidebarOpen
            }">

            <!-- Sidebar Header -->
            <div class="flex items-center justify-between p-6 border-b border-blue-500">
                <div class="text-center font-bold text-xl text-white" x-show="sidebarOpen || mobileSidebarOpen"
                    x-transition>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-blue-600 font-bold text-sm">S</span>
                        </div>
                        <span class="whitespace-nowrap overflow-hidden">SmartLab Admin</span>
                    </div>
                </div>
                <div class="flex items-center justify-center"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'w-full'">
                    <!-- Perbaikan tombol toggle: di mobile tombol ini akan menutup sidebar -->
                    <button @click="isMobile ? mobileSidebarOpen = false : sidebarOpen = !sidebarOpen"
                        class="p-2 rounded-lg bg-blue-500 hover:bg-blue-400 text-white transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
            </div>

            <nav class="mt-6 px-4 overflow-y-auto h-[calc(100vh-100px)] pb-20">
                <!-- Dashboard -->
                <a href="{{ route('home') }}"
                    class="flex items-center py-3 px-4 mb-2 text-white hover:bg-blue-500 rounded-lg transition-all duration-200 group relative {{ request()->routeIs('dashboard') ? 'bg-blue-500' : '' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Dashboard'">
                    <svg class="w-5 h-5 flex-shrink-0" :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen" x-transition
                        class="whitespace-nowrap">Dashboard</span>
                    <div x-show="!sidebarOpen && !mobileSidebarOpen && !isMobile"
                        class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                        Dashboard
                    </div>
                </a>

                <!-- Dropdown Kelola User -->
                <div class="mb-2">
                    <button
                        @click.stop="if(!(sidebarOpen || mobileSidebarOpen)) { sidebarOpen = true; setTimeout(() => userMenuOpen = true, 300) } else { userMenuOpen = !userMenuOpen }"
                        class="w-full flex items-center py-3 px-4 text-white hover:bg-blue-500 rounded-lg transition-all duration-200 group relative {{ request()->routeIs('teachers.*') || request()->is('Students*') ? 'bg-blue-500' : '' }}"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'justify-between' : 'justify-center'">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 flex-shrink-0" :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                            <span x-show="sidebarOpen || mobileSidebarOpen" x-transition.opacity
                                class="whitespace-nowrap">Kelola User</span>
                        </div>
                        <svg x-show="sidebarOpen || mobileSidebarOpen" class="w-4 h-4 transition-transform duration-300"
                            :class="userMenuOpen ? 'rotate-180' : 'rotate-0'" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                        <div x-show="userMenuOpen && (sidebarOpen || mobileSidebarOpen)"
                            x-transition:enter="transition-all ease-out duration-300"
                            x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-40"
                            x-transition:leave="transition-all ease-in duration-200"
                            x-transition:leave-start="opacity-100 max-h-40" x-transition:leave-end="opacity-0 max-h-0"
                            class="overflow-hidden mt-1 ml-4 pl-4 border-l-2 border-blue-400 space-y-1">

                            <a href="{{ route('teachers.index') }}"
                                class="flex items-center py-2.5 px-3 text-white/90 hover:text-white hover:bg-blue-500/50 rounded-lg transition-all duration-200 text-sm {{ request()->routeIs('teachers.*') ? 'bg-blue-500/50 text-white' : '' }}">
                                <span>Guru</span>
                            </a>

                            <a href="/Students"
                                class="flex items-center py-2.5 px-3 text-white/90 hover:text-white hover:bg-blue-500/50 rounded-lg transition-all duration-200 text-sm {{ request()->is('Students*') ? 'bg-blue-500/50 text-white' : '' }}">
                                <span>Murid</span>
                            </a>
                        </div>
                    </button>
                </div>

                <!-- Kelola Kelas -->
                <a href="{{ route('classes.index') }}"
                    class="flex items-center py-3 px-4 mb-2 text-white hover:bg-blue-500 rounded-lg transition-all duration-200 group relative {{ request()->routeIs('classes.index') ? 'bg-blue-500' : '' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Kelola Kelas'">
                    <svg class="w-5 h-5 flex-shrink-0" :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen" x-transition class="whitespace-nowrap">Kelola
                        Kelas</span>
                    <div x-show="!sidebarOpen && !mobileSidebarOpen && !isMobile"
                        class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                        Kelola Kelas
                    </div>
                </a>

                <!-- Kelola Mapel -->
                <a href="{{ route('subject.index') }}"
                    class="flex items-center py-3 px-4 mb-2 text-white hover:bg-blue-500 rounded-lg transition-all duration-200 group relative {{ request()->routeIs('subject.index') ? 'bg-blue-500' : '' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Kelola Mapel'">
                    <svg class="w-5 h-5 flex-shrink-0" :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                        </path>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen" x-transition class="whitespace-nowrap">Kelola
                        Mapel</span>
                    <div x-show="!sidebarOpen && !mobileSidebarOpen && !isMobile"
                        class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50">
                        Kelola Mapel
                    </div>
                </a>
            </nav>
        </aside>

        <!-- Main content - no margin shift on mobile -->
        <div class="flex-1 flex flex-col transition-all duration-300 w-full overflow-x-hidden"
            :class="{
                'md:ml-80': sidebarOpen && !isMobile,
                'md:ml-16': !sidebarOpen && !isMobile,
                'ml-0': isMobile // Memastikan tidak ada margin di mobile agar konten tidak terdorong
            }">
            <!-- Updated header with mobile hamburger and centered logo -->
            <header class="bg-white border-b border-slate-200 sticky top-0 z-30 transition-shadow duration-300"
                :class="headerScrolled ? 'header-scrolled' : 'card-shadow'">
                <div class="flex justify-between items-center px-4 md:px-6 py-3 md:py-4">
                    <!-- Tombol hamburger hanya muncul di mobile -->
                    <div class="flex items-center">
                        <button @click="mobileSidebarOpen = true"
                            class="md:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600 mr-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                        <!-- Desktop logo -->
                        <div class="hidden md:flex items-center space-x-4">
                            <img alt="Logo" src="{{ asset('image/logo.png') }}" class="h-8 w-8" />
                            <div>
                                <h1 class="font-poppins font-semibold text-lg text-slate-800">SmartLab</h1>
                                <p class="text-xs text-slate-500">Sistem Manajemen Sekolah</p>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile centered logo -->
                    <div class="md:hidden absolute left-1/2 transform -translate-x-1/2 flex items-center">
                        <img alt="Logo" src="{{ asset('image/logo.png') }}" class="h-7 w-7 mr-2" />
                        <h1 class="font-poppins font-semibold text-base text-slate-800">SmartLab</h1>
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
