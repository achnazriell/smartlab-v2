<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Smart Lab - Murid')</title>
    <link rel="icon" type="image/webp" href="{{ asset('image/logo.webp') }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <!-- Di dalam head tag layout.blade.php -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- CSS sidebar --}}
    <link rel="stylesheet" href="{{ asset('css/sidebar.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-blue-50">
    <!-- Navbar langsung di sini tanpa component extends -->
    <header
        class="fixed top-0 left-0 right-0 h-16 bg-white shadow-md z-50 flex items-center justify-between px-4 sm:px-6 border-b border-blue-100">
        <!-- Logo Section -->
        <a href="/dashboard" class="flex items-center gap-2 flex-shrink-0">
            <img src="{{ asset('image/logo.webp') }}" alt="Smart-Lab" class="h-10 w-auto">
            <span class="hidden sm:block text-sm font-bold text-blue-900">Smart Lab</span>
        </a>

        <!-- User Info & Profile Button -->
        <div class="flex items-center gap-3 sm:gap-4">
            <!-- User Info (hidden on mobile) -->
            <div class="hidden sm:block text-right">
                <p class="text-xs sm:text-sm font-semibold text-blue-900 uppercase">{{ Auth::user()->name }}</p>
                <span class="inline-block px-2 py-0.5 text-xs font-medium text-white bg-blue-600 rounded-full">
                    {{ Auth::user()->getRoleNames()->first() }}
                </span>
            </div>

            <!-- Profile Button with Alpine dropdown -->
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                {{-- Update tombol avatar --}}
                <button @click="open = !open"
                    class="w-10 h-10 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center transition-colors shadow-md hover:shadow-lg overflow-hidden">

                    @php
                        $user = Auth::user();
                        $photoPath = $user->profile_photo ? 'uploads/profile-photos/' . $user->profile_photo : null;
                        $photoExists = $photoPath && file_exists(public_path($photoPath));
                    @endphp

                    @if ($photoExists)
                        <img src="{{ asset($photoPath) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z"
                                clip-rule="evenodd" />
                        </svg>
                    @endif
                </button>

                {{-- Di bagian dropdown profile Siswa --}}
                <div x-show="open" x-transition
                    class="absolute right-0 top-12 w-64 bg-white rounded-xl shadow-lg border border-blue-100 overflow-hidden z-50">
                    <div class="p-3 space-y-2">
                        {{-- Tombol Profile --}}
                        <a href="{{ route('profile.index') }}"
                            class="w-full flex items-center gap-2 px-4 py-2.5 bg-white border border-blue-200 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Lihat Profile
                        </a>
                        {{-- Tombol Feedback --}}
                        <a href="{{ route('feedbacks.index') }}"
                            class="w-full flex items-center gap-2 px-4 py-2.5 bg-white border border-blue-200 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                            Feedback & Laporan
                        </a>

                        {{-- Tombol Logout --}}
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                KELUAR
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Sidebar Navigation with Alpine.js -->
    <aside class="fixed left-0 top-16 bottom-0 w-20 bg-white border-r border-blue-100 z-40 flex flex-col py-4 shadow-md"
        x-data="{ expanded: false }" @resize.window="expanded = window.innerWidth >= 1024 ? true : expanded">

        <nav class="flex flex-col items-center gap-2 px-2 flex-1">
            <!-- Beranda -->
            <a href="/dashboard"
                class="w-full flex flex-col items-center py-3 px-2 rounded-lg transition-all {{ request()->is('dashboard') ? 'bg-blue-100 shadow-md border-l-4 border-blue-600' : 'hover:bg-blue-50' }}">
                <svg class="w-6 h-6 {{ request()->is('dashboard') ? 'text-blue-600' : 'text-blue-700' }}"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                    <path
                        d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z" />
                </svg>
                <span
                    class="text-xs font-medium mt-1 {{ request()->is('dashboard') ? 'text-blue-600' : 'text-blue-700' }}">Beranda</span>
            </a>

            <!-- Mapel -->
            <a href="/mapel"
                class="w-full flex flex-col items-center py-3 px-2 rounded-lg transition-all {{ request()->is('mapel', 'materi') ? 'bg-blue-100 shadow-md border-l-4 border-blue-600' : 'hover:bg-blue-50' }}">
                <svg class="w-6 h-6 {{ request()->is('mapel', 'materi') ? 'text-blue-600' : 'text-blue-700' }}"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M11.25 4.533A9.707 9.707 0 006 3a9.735 9.735 0 00-3.25.555.75.75 0 00-.5.707v14.25a.75.75 0 001 .707A8.237 8.237 0 016 18.75c1.995 0 3.823.707 5.25 1.886V4.533zM12.75 20.636A8.214 8.214 0 0118 18.75c.966 0 1.89.166 2.75.47a.75.75 0 001-.708V4.262a.75.75 0 00-.5-.707A9.735 9.735 0 0018 3a9.707 9.707 0 00-5.25 1.533v16.103z" />
                </svg>
                <span
                    class="text-xs font-medium mt-1 {{ request()->is('mapel', 'materi') ? 'text-blue-600' : 'text-blue-700' }}">Mapel</span>
            </a>

            <!-- Tugas -->
            <a href="/tugas"
                class="w-full flex flex-col items-center py-3 px-2 rounded-lg transition-all {{ request()->is('tugas') ? 'bg-blue-100 shadow-md border-l-4 border-blue-600' : 'hover:bg-blue-50' }}">
                <svg class="w-6 h-6 {{ request()->is('tugas') ? 'text-blue-600' : 'text-blue-700' }}"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M7.502 6h7.128A3.375 3.375 0 0118 9.375v9.375a3 3 0 003-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 00-.673-.05A3 3 0 0015 1.5h-1.5a3 3 0 00-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6zM13.5 3A1.5 1.5 0 0012 4.5h4.5A1.5 1.5 0 0015 3h-1.5z"
                        clip-rule="evenodd" />
                    <path fill-rule="evenodd"
                        d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 013 20.625V9.375zM6 12a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H6.75a.75.75 0 01-.75-.75V12zm2.25 0a.75.75 0 01.75-.75h3.75a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75zM6 15a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H6.75a.75.75 0 01-.75-.75V15zm2.25 0a.75.75 0 01.75-.75h3.75a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75zM6 18a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H6.75a.75.75 0 01-.75-.75V18zm2.25 0a.75.75 0 01.75-.75h3.75a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75z"
                        clip-rule="evenodd" />
                </svg>
                <span
                    class="text-xs font-medium mt-1 {{ request()->is('tugas') ? 'text-blue-600' : 'text-blue-700' }}">Tugas</span>
            </a>

            <!-- Materi -->
            <a href="/semuamateri"
                class="w-full flex flex-col items-center py-3 px-2 rounded-lg transition-all {{ request()->is('semuamateri') ? 'bg-blue-100 shadow-md border-l-4 border-blue-600' : 'hover:bg-blue-50' }}">
                <svg class="w-6 h-6 {{ request()->is('semuamateri') ? 'text-blue-600' : 'text-blue-700' }}"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0016.5 9h-1.875a1.875 1.875 0 01-1.875-1.875V5.25A3.75 3.75 0 009 1.5H5.625z" />
                    <path
                        d="M12.971 1.816A5.23 5.23 0 0114.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 013.434 1.279 9.768 9.768 0 00-6.963-6.963z" />
                </svg>
                <span
                    class="text-xs font-medium mt-1 {{ request()->is('semuamateri') ? 'text-blue-600' : 'text-blue-700' }}">Materi</span>
            </a>
            <a href="/soal"
                class="w-full flex flex-col items-center py-3 px-2 rounded-lg transition-all {{ request()->is('soal') ? 'bg-blue-100 shadow-md border-l-4 border-blue-600' : 'hover:bg-blue-50' }}">
                <svg class="w-6 h-6 {{ request()->is('soal') ? 'text-blue-600' : 'text-blue-700' }}"
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c.966 0 1.89.166 2.75.47a.75.75 0 0 0 1-.708V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533v16.103Z" />
                </svg>
                <span
                    class="text-xs font-medium mt-1 {{ request()->is('soal') ? 'text-blue-600' : 'text-blue-700' }}">Soal</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main id="content" class="ml-20 mt-16 min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50">
        @yield('content')
    </main>

    @yield('scripts')
</body>

</html>
