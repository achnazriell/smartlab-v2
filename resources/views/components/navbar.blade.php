<style>
    .navbar-container {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        /* memastikan navbar di atas konten lainnya */
        background-color: white;
        /* sesuaikan dengan warna navbar */
    }

    /* Tambahkan padding top pada konten agar tidak tertutup navbar */
    .content {
        padding-top: 60px;
        /* sesuaikan dengan tinggi navbar */
    }
</style>

<nav class="bg-white px-5 py-2 shadow-md">
    <div class="container mx-auto flex items-center justify-between">
        <!-- Kiri: Logo -->
        <div class="flex justify-start text-center">
            <img src="image/SMART-LAB.png" alt="Logo" class="h-10 w-auto">
        </div>
        <!-- buttons -->
        <div class="flex justify-end">
            <!-- Sign-in or Profile Button -->
            @guest
                @if (Route::has('login'))
                    <a href="/login" id="Login-button"
                        class="flex items-center gap-2 px-4 py-2 text-blue-800 bg-white hover:bg-blue-300 hover:shadow-lg transition rounded-full border border-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                            <path fill-rule="evenodd"
                                d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="text-lg font-medium">Masuk</span>
                    </a>
                @endif
            @else
                <!-- Profile Button -->
                <div class="pl-11">
                    <a href="#" id="profile-button"
                        class="flex items-center p-3 text-white bg-blue-600 hover:bg-blue-700 transition rounded-full border border-blue-500"
                        onclick="toggleDropdown('dropdown-profile')">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                            <path fill-rule="evenodd"
                                d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z"
                                clip-rule="evenodd" />
                        </svg>
                    </a>
                </div>
                <!-- Dropdown Profile -->
                <div id="dropdown-profile"
                    class="absolute hidden p-4  bg-white shadow-lg rounded-md w-max min-w-[200px] text-gray-800 text-sm z-10 transition-transform transform scale-95 opacity-0 origin-top scale-100 opacity-100"
                    style="top: 80px">
                    <!-- Display Email -->
                    <h1 class="font-poppins px-4 py-2 text-xl font-bold text-gray-500 break-words">PROFILE</h1>
                    <div class="font-poppins px-4 py-2 text-md text-gray-500 break-words">Nama: {{ Auth::user()->name }}
                    </div>
                    <div class="font-poppins px-4 py-2 border-b text-md text-gray-500">Email: {{ Auth::user()->email }}
                    </div>

                    <!-- Logout Button -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 m-2 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900 flex items-center space-x-2">
                            <!-- Icon -->
                            <svg class="w-6 h-6 text-white dark:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2" />
                            </svg>
                            <!-- Text -->
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            @endguest
        </div>
    </div>
</nav>

<script>
    function toggleDropdown(dropdownId) {
        const dropdown = document.getElementById(dropdownId);
        dropdown.classList.toggle('hidden');
    }
</script>

<script>
    function toggleDropdown(id) {
        const dropdown = document.getElementById(id);
        dropdown.classList.toggle('hidden');

        // Animate dropdown open/close
        if (dropdown.classList.contains('hidden')) {
            dropdown.classList.add('scale-95', 'opacity-0');
            dropdown.classList.remove('scale-100', 'opacity-100');
        } else {
            dropdown.classList.remove('scale-95', 'opacity-0');
            dropdown.classList.add('scale-100', 'opacity-100');
        }
    }
</script>
