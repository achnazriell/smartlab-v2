<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/webp" href="{{ asset('image/logo.webp') }}">
    <title>@yield('title', 'Smart Lab')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    @vite(['resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap');

        :root {
            --brand-blue: #2563eb;
            --brand-dark: #1E293B;
        }

        * { font-family: 'Inter', sans-serif; }
        .font-poppins { font-family: 'Poppins', sans-serif; }

        [x-cloak] { display: none !important; }

        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .sidebar-item-active {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #2563eb;
            font-weight: 600;
            border-left: 3px solid #2563eb;
        }

        .sidebar-item-hover:hover {
            background-color: #f8fafc;
            color: #2563eb;
        }

        .sidebar-section-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            padding: 0.5rem 1rem 0.25rem;
        }
    </style>
</head>

<body class="bg-slate-50 font-sans" x-data="{
    sidebarOpen: true,
    mobileSidebarOpen: false,
    isMobile: false
}" x-init="
    const checkScreen = () => {
        isMobile = window.innerWidth < 768;
        if (isMobile) { sidebarOpen = false; mobileSidebarOpen = false; }
        else { sidebarOpen = true; }
    };
    checkScreen();
    window.addEventListener('resize', checkScreen);
" x-cloak>

    <div class="flex h-screen overflow-hidden">
        <!-- Mobile backdrop -->
        <div x-show="isMobile && mobileSidebarOpen"
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @click="mobileSidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm md:hidden">
        </div>

        <!-- Sidebar -->
        <aside class="fixed top-0 left-0 h-full bg-white border-r border-slate-200 shadow-lg z-50 transition-all duration-300 flex flex-col"
            :class="isMobile
                ? (mobileSidebarOpen ? 'w-72 translate-x-0' : 'w-72 -translate-x-full')
                : (sidebarOpen ? 'w-68' : 'w-[72px]')">

            <!-- Sidebar Header -->
            <div class="h-16 flex items-center justify-between px-4 border-b border-slate-100 flex-shrink-0">
                <div class="flex items-center overflow-hidden">
                    <img x-show="!isMobile && !sidebarOpen" class="w-8 h-8 object-contain flex-shrink-0"
                        src="{{ asset('image/logo.webp') }}" alt="Logo">
                    <img x-show="isMobile || sidebarOpen" class="h-9 w-auto object-contain"
                        src="{{ asset('image/logosl.webp') }}" alt="SmartLab">
                </div>
                <button x-show="isMobile" @click="mobileSidebarOpen = false"
                    class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5">

                <!-- Dashboard -->
                <a href="{{ route('home') }}"
                    class="flex items-center py-2.5 px-3 rounded-xl transition-all duration-200 {{ request()->routeIs('home') ? 'sidebar-item-active' : 'text-slate-600 sidebar-item-hover' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Dashboard'">
                    <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('home') ? 'text-blue-600' : '' }}"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="font-medium whitespace-nowrap text-sm">Dashboard</span>
                </a>

                <!-- SECTION: USER MANAGEMENT -->
                <div x-show="sidebarOpen || mobileSidebarOpen" class="sidebar-section-label mt-3">Manajemen Pengguna</div>

                <!-- Guru -->
                <a href="{{ route('teachers.index') }}"
                    class="flex items-center py-2.5 px-3 rounded-xl transition-all duration-200 {{ request()->routeIs('teachers.*') ? 'sidebar-item-active' : 'text-slate-600 sidebar-item-hover' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Kelola Guru'">
                    <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('teachers.*') ? 'text-blue-600' : '' }}"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="font-medium whitespace-nowrap text-sm">Kelola Guru</span>
                </a>

                <!-- Siswa -->
                <a href="{{ route('students.index') }}"
                    class="flex items-center py-2.5 px-3 rounded-xl transition-all duration-200 {{ request()->routeIs('students.*') ? 'sidebar-item-active' : 'text-slate-600 sidebar-item-hover' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Kelola Siswa'">
                    <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('students.*') ? 'text-blue-600' : '' }}"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="font-medium whitespace-nowrap text-sm">Kelola Siswa</span>
                </a>

                <!-- SECTION: AKADEMIK -->
                <div x-show="sidebarOpen || mobileSidebarOpen" class="sidebar-section-label mt-3">Akademik</div>

                <!-- Tahun Ajaran -->
                <a href="{{ route('academic-years.index') }}"
                    class="flex items-center py-2.5 px-3 rounded-xl transition-all duration-200 {{ request()->routeIs('academic-years.*') ? 'sidebar-item-active' : 'text-slate-600 sidebar-item-hover' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Tahun Ajaran'">
                    <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('academic-years.*') ? 'text-blue-600' : '' }}"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="font-medium whitespace-nowrap text-sm">Tahun Ajaran</span>
                </a>

                <!-- Kelola Kelas -->
                <a href="{{ route('classes.index') }}"
                    class="flex items-center py-2.5 px-3 rounded-xl transition-all duration-200 {{ request()->routeIs('classes.*') ? 'sidebar-item-active' : 'text-slate-600 sidebar-item-hover' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Kelola Kelas'">
                    <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('classes.*') ? 'text-blue-600' : '' }}"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="font-medium whitespace-nowrap text-sm">Kelola Kelas</span>
                </a>

                <!-- Kelola Jurusan -->
                <a href="{{ route('departments.index') }}"
                    class="flex items-center py-2.5 px-3 rounded-xl transition-all duration-200 {{ request()->routeIs('departments.*') ? 'sidebar-item-active' : 'text-slate-600 sidebar-item-hover' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Kelola Jurusan'">
                    <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('departments.*') ? 'text-blue-600' : '' }}"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="font-medium whitespace-nowrap text-sm">Kelola Jurusan</span>
                </a>

                <!-- Kelola Mapel -->
                <a href="{{ route('subject.index') }}"
                    class="flex items-center py-2.5 px-3 rounded-xl transition-all duration-200 {{ request()->routeIs('subject.*') ? 'sidebar-item-active' : 'text-slate-600 sidebar-item-hover' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Kelola Mapel'">
                    <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('subject.*') ? 'text-blue-600' : '' }}"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="font-medium whitespace-nowrap text-sm">Kelola Mapel</span>
                </a>

                <!-- SECTION: LAINNYA -->
                <div x-show="sidebarOpen || mobileSidebarOpen" class="sidebar-section-label mt-3">Lainnya</div>

                <!-- Feedback -->
                <a href="{{ route('feedback.index') }}"
                    class="flex items-center py-2.5 px-3 rounded-xl transition-all duration-200 {{ request()->routeIs('feedback.*') ? 'sidebar-item-active' : 'text-slate-600 sidebar-item-hover' }}"
                    :class="(sidebarOpen || mobileSidebarOpen) ? '' : 'justify-center'"
                    :title="(sidebarOpen || mobileSidebarOpen) ? '' : 'Feedback'">
                    <svg class="w-5 h-5 flex-shrink-0 {{ request()->routeIs('feedback.*') ? 'text-blue-600' : '' }}"
                        :class="(sidebarOpen || mobileSidebarOpen) ? 'mr-3' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span x-show="sidebarOpen || mobileSidebarOpen" class="font-medium whitespace-nowrap text-sm">Feedback & Laporan</span>
                </a>
            </nav>

            <!-- Sidebar Footer -->
            <div class="flex-shrink-0 border-t border-slate-100 p-3" x-show="sidebarOpen || mobileSidebarOpen">
                <div class="flex items-center space-x-3 px-2 py-2 rounded-xl bg-slate-50">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="text-white text-xs font-bold">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ Auth::user()->getRoleNames()->first() }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col transition-all duration-300 w-full overflow-x-hidden"
            :class="{
                'md:ml-[272px]': sidebarOpen && !isMobile,
                'md:ml-[72px]': !sidebarOpen && !isMobile,
                'ml-0': isMobile
            }">

            <!-- Header -->
            <header class="bg-white/90 backdrop-blur-md border-b border-slate-200 sticky top-0 z-30 transition-all duration-300">
                <div class="flex justify-between items-center px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <!-- Mobile hamburger -->
                        <button @click="mobileSidebarOpen = true"
                            class="md:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        <!-- Desktop toggle -->
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="hidden md:flex p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                            </svg>
                        </button>

                        <!-- Page title from breadcrumb -->
                        <div class="hidden md:block">
                            <h2 class="text-sm font-semibold text-slate-700">@yield('page-title', 'Admin Panel')</h2>
                        </div>
                    </div>

                    <!-- Mobile Logo -->
                    <div class="md:hidden absolute left-1/2 transform -translate-x-1/2">
                        <img src="{{ asset('image/logosl.webp') }}" class="h-8 w-auto" alt="SmartLab">
                    </div>

                    <!-- Right Actions -->
                    <div class="flex items-center space-x-3">
                        <!-- User Info Desktop -->
                        <div class="hidden md:flex flex-col text-right">
                            <p class="text-sm font-semibold text-slate-800 leading-tight">{{ Auth::user()->name }}</p>
                            <span class="text-xs text-blue-600 font-medium">{{ Auth::user()->getRoleNames()->first() }}</span>
                        </div>

                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ profileOpen: false }">
                            <button @click="profileOpen = !profileOpen"
                                class="w-9 h-9 flex items-center justify-center bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors shadow-md">
                                <span class="text-white text-sm font-bold">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            </button>

                            <div x-show="profileOpen" @click.outside="profileOpen = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-1"
                                class="absolute right-0 mt-2 w-60 bg-white rounded-xl shadow-xl border border-slate-200 z-50 overflow-hidden">

                                <!-- Profile Header -->
                                <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-slate-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                                            <span class="text-white font-bold">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-800 text-sm">{{ Auth::user()->name }}</p>
                                            <p class="text-xs text-slate-500">{{ Auth::user()->email }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Logout -->
                                <div class="p-2 border-t border-slate-100">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="w-full flex items-center space-x-3 px-3 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            <span>Keluar</span>
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

    <!-- Global SweetAlert notifications -->
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                    background: '#fff',
                    color: '#1e293b',
                });
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: '{{ session('error') }}',
                    confirmButtonColor: '#2563eb',
                    background: '#fff',
                    color: '#1e293b',
                });
            });
        </script>
    @endif

    @if (session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: '{{ session('warning') }}',
                    confirmButtonColor: '#2563eb',
                    background: '#fff',
                    color: '#1e293b',
                });
            });
        </script>
    @endif
</body>

</html>
