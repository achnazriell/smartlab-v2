<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    <title>SmartLab</title>
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @vite(['resources/css/app.css', 'resources/css/style.css', 'resources/js/app.js'])

    <!-- Scripts -->
    <script src="https://unpkg.com/flowbite@1.3.4/dist/flowbite.js"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/algoliasearch@4.10.5/dist/algoliasearch.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- SweetAlert2 CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css">

    <!-- Preline Select CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/preline@latest/dist/preline.min.css">

    {{-- font poppins --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        .font-poppins {
            font-family: 'Poppins', sans-serif;
        }

        /* Custom scrollbar styling */
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

        /* Sidebar animations */
        .sidebar-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Modern card shadows */
        .card-shadow {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }

        .card-shadow-lg {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>

    <!-- Preline Select JS -->
    <script src="https://cdn.jsdelivr.net/npm/preline@latest/dist/preline.min.js"></script>
</head>

<body class="bg-slate-50 font-sans" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">
        <!-- Modern Sidebar with improved design -->
        <aside class="sidebar-transition bg-gradient-to-b from-blue-600 via-blue-700 to-blue-800 shadow-2xl border-r border-blue-300 fixed left-0 top-0 h-full z-40"
               :class="sidebarOpen ? 'w-80' : 'w-16'">

            <!-- Added sidebar header with toggle button -->
            <div class="flex items-center justify-between p-6 border-b border-blue-500">
                <div class="text-center font-bold text-xl text-white" x-show="sidebarOpen" x-transition>
                    <div class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
                            <span class="text-blue-600 font-bold text-sm">S</span>
                        </div>
                        <span>SmartLab Admin</span>
                    </div>
                </div>
                <!-- Always show toggle button even in mini sidebar -->
                <div class="flex items-center justify-center" :class="sidebarOpen ? '' : 'w-full'">
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="p-2 rounded-lg bg-blue-500 hover:bg-blue-400 text-white transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  :d="sidebarOpen ? 'M11 19l-7-7 7-7M21 12H3' : 'M13 5l7 7-7 7M5 12h14'"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Updated navigation to show icons in mini sidebar -->
            <nav class="mt-6 px-4 overflow-y-auto h-full pb-20">
                <a href="{{ route('home') }}"
                    class="flex items-center py-3 px-4 mb-2 text-white hover:bg-blue-500 rounded-lg transition-all duration-200 group relative {{ request()->routeIs('dashboard') ? 'bg-blue-500' : '' }}"
                    :class="sidebarOpen ? '' : 'justify-center'"
                    :title="sidebarOpen ? '' : 'Dashboard'">
                    <svg class="w-5 h-5 flex-shrink-0" :class="sidebarOpen ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">Dashboard</span>
                    <!-- Tooltip for mini sidebar -->
                    <div x-show="!sidebarOpen" class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                        Dashboard
                    </div>
                </a>

                <a href="{{ route('teachers.index') }}"
                    class="flex items-center py-3 px-4 mb-2 text-white hover:bg-blue-500 rounded-lg transition-all duration-200 group relative {{ request()->routeIs('teachers.index') ? 'bg-blue-500' : '' }}"
                    :class="sidebarOpen ? '' : 'justify-center'"
                    :title="sidebarOpen ? '' : 'Kelola Guru'">
                    <svg class="w-5 h-5 flex-shrink-0" :class="sidebarOpen ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">Kelola Guru</span>
                    <div x-show="!sidebarOpen" class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                        Kelola Guru
                    </div>
                </a>

                <a href="/Students"
                    class="flex items-center py-3 px-4 mb-2 text-white hover:bg-blue-500 rounded-lg transition-all duration-200 group relative {{ request()->routeIs('Students') ? 'bg-blue-500' : '' }}"
                    :class="sidebarOpen ? '' : 'justify-center'"
                    :title="sidebarOpen ? '' : 'Kelola Siswa'">
                    <svg class="w-5 h-5 flex-shrink-0" :class="sidebarOpen ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">Kelola Siswa</span>
                    <div x-show="!sidebarOpen" class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                        Kelola Siswa
                    </div>
                </a>

                <a href="{{ route('classes.index') }}"
                    class="flex items-center py-3 px-4 mb-2 text-white hover:bg-blue-500 rounded-lg transition-all duration-200 group relative {{ request()->routeIs('classes.index') ? 'bg-blue-500' : '' }}"
                    :class="sidebarOpen ? '' : 'justify-center'"
                    :title="sidebarOpen ? '' : 'Kelola Kelas'">
                    <svg class="w-5 h-5 flex-shrink-0" :class="sidebarOpen ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">Kelola Kelas</span>
                    <div x-show="!sidebarOpen" class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                        Kelola Kelas
                    </div>
                </a>

                <a href="{{ route('subject.index') }}"
                    class="flex items-center py-3 px-4 mb-2 text-white hover:bg-blue-500 rounded-lg transition-all duration-200 group relative {{ request()->routeIs('subject.index') ? 'bg-blue-500' : '' }}"
                    :class="sidebarOpen ? '' : 'justify-center'"
                    :title="sidebarOpen ? '' : 'Kelola Mapel'">
                    <svg class="w-5 h-5 flex-shrink-0" :class="sidebarOpen ? 'mr-3' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <span x-show="sidebarOpen" x-transition class="whitespace-nowrap">Kelola Mapel</span>
                    <div x-show="!sidebarOpen" class="absolute left-full ml-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap">
                        Kelola Mapel
                    </div>
                </a>
            </nav>
        </aside>

        <!-- Main content area with modern header -->
        <div class="flex-1 flex flex-col transition-all duration-300" :class="sidebarOpen ? 'ml-80' : 'ml-16'">
            <!-- Modern header with improved styling -->
            <header class="bg-white border-b border-slate-200 card-shadow sticky top-0 z-40">
                <div class="flex justify-between items-center px-6 py-4">
                    <!-- Logo section -->
                    <div class="flex items-center space-x-4">
                        <img alt="Logo" src="{{ asset('image/logo.png') }}" class="h-8 w-8" />
                        <div class="hidden md:block">
                            <h1 class="font-poppins font-semibold text-lg text-slate-800">SmartLab</h1>
                            <p class="text-xs text-slate-500">Sistem Manajemen Sekolah</p>
                        </div>
                    </div>

                    <!-- User profile section with modern design -->
                    <div class="flex items-center space-x-4">
                        <div class="hidden md:flex flex-col text-right">
                            <p class="font-semibold text-slate-800">{{ Auth::user()->name }}</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ Auth::user()->getRoleNames()->first() }}
                            </span>
                        </div>

                        <div class="relative">
                            <button id="profile-button" class="flex items-center p-2 text-white bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md" onclick="toggleDropdown('dropdown-profile')">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                                    <path fillRule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clipRule="evenodd" />
                                </svg>
                            </button>

                            <!-- Modern dropdown menu -->
                            <div id="dropdown-profile" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-slate-200 z-50">
                                <div class="px-4 py-3 border-b border-slate-200">
                                    <h3 class="font-semibold text-slate-800">Profil Pengguna</h3>
                                </div>
                                <div class="px-4 py-3 space-y-2">
                                    <p class="text-sm text-slate-600"><span class="font-medium">Nama:</span> {{ Auth::user()->name }}</p>
                                    <p class="text-sm text-slate-600"><span class="font-medium">Email:</span> {{ Auth::user()->email }}</p>
                                </div>
                                <div class="px-4 py-3 border-t border-slate-200">
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 text-sm font-medium">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6-4v8" />
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

            <!-- Content area -->
            <main class="flex-1 overflow-y-auto bg-slate-50">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Simplified JavaScript to work with Alpine.js -->
    <script>
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('dropdown-profile');
            const button = document.getElementById('profile-button');

            if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });
    </script>

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
