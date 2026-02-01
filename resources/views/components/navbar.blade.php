{{-- navbar.blade.php --}}
<style>
    .navbar-container {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background-color: white;
        transition: all 0.3s ease;
        height: 64px;
        /* Tambahkan tinggi tetap */
    }

    .navbar-scrolled {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        background-color: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
    }

    .content {
        padding-top: 70px;
    }

    /* Alpine.js mobile menu styles */
    [x-cloak] {
        display: none !important;
    }

    /* Tambahkan ini */

    .mobile-overlay {
        position: fixed;
        top: 64px;
        /* Mulai dari bawah navbar */
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 999;
        /* Kurangi sedikit dari navbar */
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        overflow-y: auto;
        transition: all 0.3s ease-out;
    }

    .menu-item {
        opacity: 0;
        transform: translateY(-10px);
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .menu-item.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .profile-section {
        border-bottom: 1px solid rgb(229, 231, 235);
    }

    /* Mobile hamburger button visibility */
    .mobile-menu-btn {
        align-items: center;
        justify-content: center;
        height: 40px;
        width: 40px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
</style>

<nav class="navbar-container bg-white px-4 sm:px-5 py-3 shadow-sm transition-colors duration-300" x-data="{
    isOpen: false,
    scrolled: false,
    mobileMenuItems: [
        { href: '/Beranda', label: 'Beranda' },
        { href: '/Fitur', label: 'Fitur' },
        { href: '/Tentang', label: 'Tentang' },
        { href: '/Kontak', label: 'Kontak' },
    ],

    init() {
        window.addEventListener('scroll', () => {
            this.scrolled = window.scrollY > 10;
        });

        window.addEventListener('resize', () => {
            const isMobile = window.innerWidth < 768;
            if (!isMobile && this.isOpen) {
                this.isOpen = false;
                document.body.style.overflow = 'auto';
            }
        });
    },

    toggleMenu() {
        this.isOpen = !this.isOpen;

        if (this.isOpen) {
            document.body.style.overflow = 'hidden';
            // Trigger animation pada menu items
            setTimeout(() => {
                const menuItems = document.querySelectorAll('.menu-item');
                menuItems.forEach((item, index) => {
                    setTimeout(() => {
                        item.classList.add('visible');
                    }, index * 50);
                });
            }, 50);
        } else {
            document.body.style.overflow = 'auto';
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.classList.remove('visible');
            });
        }
    },

    closeMenu() {
        this.isOpen = false;
        document.body.style.overflow = 'auto';
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            item.classList.remove('visible');
        });
    },

    isActive(route) {
        try {
            const currentPath = window.location.pathname;
            const routePath = new URL(route, window.location.origin).pathname;
            return currentPath === routePath;
        } catch (e) {
            return false;
        }
    }
}"
    x-init="init()" @scroll.window="scrolled = window.scrollY > 10" :class="{ 'navbar-scrolled': scrolled }">

    <div class="container mx-auto flex items-center justify-between h-full">
        <!-- Logo -->
        <div class="flex items-center">
            <img src="{{ asset('image/logosl.webp') }}" alt="Logo SmartLab" class="h-8 sm:h-10 w-auto">
        </div>

        <!-- Menu untuk Desktop -->
        <div class="hidden md:flex items-center space-x-6">

            <a href="/Beranda" class="text-gray-700 hover:text-blue-600 font-medium transition">
                Beranda
            </a>
            <a href="/Fitur" class="text-gray-700 hover:text-blue-600 font-medium transition">
                Fitur
            </a>
            <a href="/Tentang" class="text-gray-700 hover:text-blue-600 font-medium transition">
                Tentang
            </a>
            <a href="/Kontak" class="text-gray-700 hover:text-blue-600 font-medium transition">
                Kontak
            </a>
        </div>

        <!-- Right Side Actions (Desktop) -->
        <div class="hidden md:flex items-center space-x-4">
            @guest
                <a href="{{ route('login') }}"
                    class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                    Masuk
                </a>
            @endguest

            @auth
                <!-- Profile Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center space-x-2 p-2 hover:bg-gray-100 rounded-lg transition">
                        <div
                            class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="text-gray-700 text-sm">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50 border border-gray-200"
                        style="display: none;">
                        <div class="px-4 py-3 border-b border-gray-200">
                            <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <div class="border-t border-gray-200">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endauth
        </div>

        <!-- Mobile Menu Button - SIMPLIFIED VERSION -->
        <div class="md:hidden flex items-center z-50">
            <button @click="toggleMenu()"
                class="flex items-center justify-center w-10 h-10 rounded-lg transition
               hover:bg-gray-100"
                aria-label="Toggle mobile menu">
                <div class="relative w-6 h-6">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </div>
            </button>
        </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="mobile-overlay md:hidden"
        @click="closeMenu()" x-cloak>

        <div class="h-full flex flex-col px-4 sm:px-6 py-6" @click.stop>
            <!-- Navigation Menu Items -->
            <ul class="flex flex-col gap-2 mb-6">
                <template x-for="(item, index) in mobileMenuItems" :key="index">
                    <li class="menu-item">
                        <a :href="item.route || item.href" @click="closeMenu()"
                            :class="{
                                'flex items-center justify-between w-full py-3 px-4 rounded-lg transition-all duration-300': true,
                                'bg-blue-50 text-blue-600 font-semibold': item.route && isActive(item.route),
                                'text-gray-700 font-medium hover:bg-gray-100': !(item.route && isActive(item.route))
                            }">
                            <span x-text="item.label"></span>
                            <template x-if="item.route && isActive(item.route)">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </template>
                        </a>
                    </li>
                </template>
            </ul>

            <!-- Divider -->
            <div class="w-full h-px bg-gray-200 my-4"></div>

            <!-- Guest Actions -->
            @guest
                <div class="menu-item">
                    <a href="{{ route('login') }}" @click="closeMenu()"
                        class="flex items-center justify-center w-full py-3 px-4 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition-all duration-300">
                        Masuk
                    </a>
                </div>
            @endguest

            <!-- Auth Actions -->
            @auth
                <div class="menu-item">
                    <div class="profile-section pb-4 mb-4">
                        <div class="flex items-center space-x-3 mb-4">
                            <div
                                class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-semibold">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="menu-item">
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" @click="closeMenu()"
                            class="flex items-center space-x-2 w-full py-3 px-4 rounded-lg text-red-600 hover:bg-red-50 transition-all duration-300 font-medium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</nav>
