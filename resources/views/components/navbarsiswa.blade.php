<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">

<!-- Header -->
<header class="fixed top-0 left-0 right-0 h-16 bg-white shadow-sm z-50 flex items-center justify-between px-6">
    <!-- Logo -->
    <a href="/dashboard" class="flex items-center">
        <img src="{{ asset('image/logo.png') }}" alt="Smart-Lab" class="h-10">
    </a>

    <!-- User Info -->
    <div class="flex items-center gap-3">
        <div class="text-right hidden sm:block">
            <p class="text-sm font-semibold text-gray-800 uppercase">{{ Auth::user()->name }}</p>
            <span class="inline-block px-2 py-0.5 text-xs font-medium text-blue-600 bg-blue-100 rounded">{{ Auth::user()->getRoleNames()->first() }}</span>
        </div>

        <!-- Profile Button -->
        <div class="relative">
            <button onclick="toggleProfileDropdown()" class="w-10 h-10 bg-blue-500 hover:bg-blue-600 text-white rounded-full flex items-center justify-center transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                </svg>
            </button>

            <!-- Profile Dropdown -->
            <div id="profile-dropdown" class="hidden absolute right-0 top-12 w-64 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-500 mb-2">PROFILE</h3>
                    <p class="text-sm text-gray-600">Nama: {{ Auth::user()->name }}</p>
                    <p class="text-sm text-gray-600">Email: {{ Auth::user()->email }}</p>
                </div>
                <div class="p-3">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            KELUAR
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Sidebar -->
<aside class="fixed left-0 top-16 bottom-0 w-20 bg-gray-50 border-r border-gray-200 z-40 flex flex-col py-4">
    <nav class="flex flex-col items-center gap-2 px-2">
        <!-- Beranda -->
        <a href="/dashboard" class="w-full flex flex-col items-center py-3 px-2 rounded-xl transition-all {{ request()->is('dashboard') ? 'bg-white shadow-md border border-gray-200' : 'hover:bg-gray-100' }}">
            <svg class="w-6 h-6 {{ request()->is('dashboard') ? 'text-blue-600' : 'text-blue-800' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M11.47 3.84a.75.75 0 011.06 0l8.69 8.69a.75.75 0 101.06-1.06l-8.689-8.69a2.25 2.25 0 00-3.182 0l-8.69 8.69a.75.75 0 001.061 1.06l8.69-8.69z" />
                <path d="M12 5.432l8.159 8.159c.03.03.06.058.091.086v6.198c0 1.035-.84 1.875-1.875 1.875H15a.75.75 0 01-.75-.75v-4.5a.75.75 0 00-.75-.75h-3a.75.75 0 00-.75.75V21a.75.75 0 01-.75.75H5.625a1.875 1.875 0 01-1.875-1.875v-6.198a2.29 2.29 0 00.091-.086L12 5.43z" />
            </svg>
            <span class="text-xs font-medium mt-1 {{ request()->is('dashboard') ? 'text-blue-600' : 'text-blue-800' }}">Beranda</span>
        </a>

        <!-- Mapel -->
        <a href="/mapel" class="w-full flex flex-col items-center py-3 px-2 rounded-xl transition-all {{ request()->is('mapel', 'materi') ? 'bg-white shadow-md border border-gray-200' : 'hover:bg-gray-100' }}">
            <svg class="w-6 h-6 {{ request()->is('mapel', 'materi') ? 'text-blue-600' : 'text-blue-800' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M11.25 4.533A9.707 9.707 0 006 3a9.735 9.735 0 00-3.25.555.75.75 0 00-.5.707v14.25a.75.75 0 001 .707A8.237 8.237 0 016 18.75c1.995 0 3.823.707 5.25 1.886V4.533zM12.75 20.636A8.214 8.214 0 0118 18.75c.966 0 1.89.166 2.75.47a.75.75 0 001-.708V4.262a.75.75 0 00-.5-.707A9.735 9.735 0 0018 3a9.707 9.707 0 00-5.25 1.533v16.103z" />
            </svg>
            <span class="text-xs font-medium mt-1 {{ request()->is('mapel', 'materi') ? 'text-blue-600' : 'text-blue-800' }}">Mapel</span>
        </a>

        <!-- Tugas -->
        <a href="/tugas" class="w-full flex flex-col items-center py-3 px-2 rounded-xl transition-all {{ request()->is('tugas') ? 'bg-white shadow-md border border-gray-200' : 'hover:bg-gray-100' }}">
            <svg class="w-6 h-6 {{ request()->is('tugas') ? 'text-blue-600' : 'text-blue-800' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0118 9.375v9.375a3 3 0 003-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 00-.673-.05A3 3 0 0015 1.5h-1.5a3 3 0 00-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6zM13.5 3A1.5 1.5 0 0012 4.5h4.5A1.5 1.5 0 0015 3h-1.5z" clip-rule="evenodd" />
                <path fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 013 20.625V9.375zM6 12a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H6.75a.75.75 0 01-.75-.75V12zm2.25 0a.75.75 0 01.75-.75h3.75a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75zM6 15a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H6.75a.75.75 0 01-.75-.75V15zm2.25 0a.75.75 0 01.75-.75h3.75a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75zM6 18a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H6.75a.75.75 0 01-.75-.75V18zm2.25 0a.75.75 0 01.75-.75h3.75a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
            </svg>
            <span class="text-xs font-medium mt-1 {{ request()->is('tugas') ? 'text-blue-600' : 'text-blue-800' }}">Tugas</span>
        </a>

        <!-- Materi -->
        <a href="/pilihkelasmateri" class="w-full flex flex-col items-center py-3 px-2 rounded-xl transition-all {{ request()->is('pilihkelasmateri') ? 'bg-white shadow-md border border-gray-200' : 'hover:bg-gray-100' }}">
            <svg class="w-6 h-6 {{ request()->is('pilihkelasmateri') ? 'text-blue-600' : 'text-blue-800' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0016.5 9h-1.875a1.875 1.875 0 01-1.875-1.875V5.25A3.75 3.75 0 009 1.5H5.625z" />
                <path d="M12.971 1.816A5.23 5.23 0 0114.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 013.434 1.279 9.768 9.768 0 00-6.963-6.963z" />
            </svg>
            <span class="text-xs font-medium mt-1 {{ request()->is('pilihkelasmateri') ? 'text-blue-600' : 'text-blue-800' }}">Materi</span>
        </a>

        @if (auth()->check() && !auth()->user()->class()->exists())
        <!-- Kelas -->
        <a href="/PilihKelas" class="w-full flex flex-col items-center py-3 px-2 rounded-xl transition-all {{ request()->is('PilihKelas') ? 'bg-white shadow-md border border-gray-200' : 'hover:bg-gray-100' }}">
            <svg class="w-6 h-6 {{ request()->is('PilihKelas') ? 'text-blue-600' : 'text-blue-800' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                <path d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z" />
            </svg>
            <span class="text-xs font-medium mt-1 {{ request()->is('PilihKelas') ? 'text-blue-600' : 'text-blue-800' }}">Kelas</span>
        </a>
        @endif
    </nav>
</aside>

<!-- Mobile Menu Button -->
<button onclick="toggleMobileSidebar()" class="lg:hidden fixed bottom-4 left-4 z-50 w-12 h-12 bg-blue-600 text-white rounded-full shadow-lg flex items-center justify-center">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>


<script>
    function toggleProfileDropdown() {
        const dropdown = document.getElementById('profile-dropdown');
        dropdown.classList.toggle('hidden');
    }

    function toggleMobileSidebar() {
        const sidebar = document.querySelector('aside');
        sidebar.classList.toggle('-translate-x-full');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('profile-dropdown');
        const profileBtn = event.target.closest('button[onclick="toggleProfileDropdown()"]');

        if (!profileBtn && !dropdown.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>

</body>
</html>
