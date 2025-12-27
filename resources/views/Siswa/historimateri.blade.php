<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<!-- Mirrored from preview.keenthemes.com/metronic8/demo31/ by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 14 Feb 2023 14:27:54 GMT -->
<!-- Added by HTTrack -->
<meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->

<head>
    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}">
    <title>Smart-LAB</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:type" content="article" />
    <meta name="csrf-token" content="8IMVNabevkMVEFpvO472s41XBcvpCVja5sJxIXQO">
    <meta property="og:description" content="Improve your skill with hummatech internship.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" /> <!--end::Fonts-->

    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    {{-- <link href="https://class.hummatech.com/user-assets/plugins/global/plugins.bundle.css" rel="stylesheet"
        type="text/css" /> --}}
    <link href="https://class.hummatech.com/user-assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->

    {{-- <link href="https://class.hummatech.com/user-assets/plugins/custom/datatables/datatables.bundle.css"
        rel="stylesheet" type="text/css" /> --}}

    {{-- flowbite --}}
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />

    <!-- Link Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
        .materiModal {
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease-in-out;
            z-index: 50;
        }

        .materiModal .bg-white {
            height: 90%;
            /* Atur tinggi modal */
            max-height: 90%;
            /* Batas maksimum */
            display: flex;
            flex-direction: column;
            /* Agar konten vertikal terorganisir */
        }

        .materiModal embed {
            flex: 1;
            /* Isi seluruh ruang yang tersedia */
            min-height: 0;
            /* Pastikan tidak terjadi overflow */
        }


        @media (max-width: 639px) {
            .covercard {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .carousel-indicators {
            align-items: center;
        }

        .carousel-indicators button {
            width: 10px !important;
            height: 10px !important;
            border-radius: 100%;
            background-color: rgba(255, 255, 255, 0.507) !important;
        }

        .carousel-indicators button.active {
            width: 15px !important;
            height: 15px !important;
            border-radius: 100%;
            background-color: white !important;
        }

        .carousel-item img {
            height: 300px;
            object-fit: cover;
            border-radius: 1rem !important;
        }

        .carousel-item .follow-event-btn {
            z-index: 100;
        }

        .carousel-item:after {
            position: absolute;
            content: "";
            height: 100%;
            width: 100%;
            top: 0;
            left: 0;
            background: linear-gradient(to bottom, rgba(255, 0, 0, 0), rgba(0, 0, 0, 0.65) 100%);
        }
    </style>

</head>

<body id="kt_app_body" data-kt-app-header-fixed="true" data-kt-app-header-fixed-mobile="true"
    data-kt-app-sidebar-enabled="true" data-kt-app-sidebar-fixed="true" data-kt-app-sidebar-push-toolbar="true"
    data-kt-app-sidebar-push-footer="true" data-kt-app-sidebar-stacked="true" class="app-default">
    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "light";
        var themeMode;

        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }

            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }

            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    <!--end::Theme mode setup on page load-->

    <x-navbarsiswa></x-navbarsiswa>

    <div class="container p-10">

        <div class="flex justify-between items-center my-8">
            <h1 class="text-2xl text-gray-700 font-poppins font-bold">
                Daftar Materi
            </h1>

            <div class="flex items-center gap-2">
                <button id="dropdownHoverButton" data-dropdown-toggle="dropdownHover" data-dropdown-trigger="hover"
                    class="text-gray-600 bg-white hover:bg-gray-50 ring-gray-200 ring-1 rounded-xl text-sm px-5 py-3.5 text-center inline-flex items-center "
                    type="button">Pilih Kelas <svg class="w-2.5 h-2.5 ms-3" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 4 4 4-4" />
                    </svg>
                </button>

                <!-- Dropdown menu -->
                <div id="dropdownHover" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow-sm w-44">
                    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownHoverButton">
                        <li>
                            <a href="#"
                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-blue-400 dark:hover:text-white">Kelas
                                10</a>
                        </li>
                        <li>
                            <a href="#"
                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-blue-400 dark:hover:text-white">Kelas
                                11</a>
                        </li>
                        <li>
                            <a href="#"
                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-blue-400 dark:hover:text-white">Kelas
                                12</a>
                        </li>
                    </ul>
                </div>

                <form action="{{ route('Tugas') }}" method="GET" class="flex items-center">
                    <input type="text" id="search" name="search" placeholder="Search..."
                        class="rounded-xl border-gray-300 p-3">
                    <!-- Tombol search dengan icon -->
                    <button type="submit"
                        class="ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold p-3 px-4 rounded-xl">
                        <i class="fas fa-search text-white"></i>
                    </button>
                </form>

                <!-- Dropdown Filter -->
                <div class="relative">
                    <button id="filterButton"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold p-3 px-4 rounded-xl shadow-md">
                        <i class="fas fa-filter text-white"></i>
                    </button>
                    <div id="filterDropdown"
                        class="hidden absolute right-0 mt-5 w-72 bg-white border border-gray-300 rounded-xl pl-1 pb-2 shadow-md z-50"
                        style="max-height: 200px; overflow-y: auto;">

                        <!-- Filter Header (Tetap di atas saat scroll) -->
                        <div class="px-4 py-3 text-lg font-semibold text-gray-700 border-b border-gray-300"
                            style="position: sticky; top: 0; background-color: white; z-index: 10;">
                            Pilih Mata Pelajaran
                        </div>
                        <!-- Filter Dropdown Options -->
                        <form method="GET" action="{{ route('Tugas') }}">
                            @csrf
                            <button type="submit" name="status" value="Sudah mengumpulkan"
                                class="flex items-center justify-center px-4 py-2 font-bold text-gray-800 bg-gray-100
                                hover:!text-white hover:bg-blue-400
                                active:!text-white active:bg-blue-500
                                rounded-xl m-2 w-64 h-12">
                                Matematika
                            </button>
                            <button type="submit" name="status" value="Belum mengumpulkan"
                                class="flex items-center justify-center px-4 py-2 font-bold text-gray-800 bg-gray-100
                                hover:!text-white hover:bg-blue-400
                                active:!text-white active:bg-blue-500
                                rounded-xl m-2 w-64 h-12">
                                Bahasa Indonesia
                            </button>
                            <button type="submit" name="status" value="Belum mengumpulkan"
                                class="flex items-center justify-center px-4 py-2 font-bold text-gray-800 bg-gray-100
                                hover:!text-white hover:bg-blue-400
                                active:!text-white active:bg-blue-500
                                rounded-xl m-2 w-64 h-12">
                                Bahasa Inggris
                            </button>
                            <button type="submit" name="status" value="Belum mengumpulkan"
                                class="flex items-center justify-center px-4 py-2 font-bold text-gray-800 bg-gray-100
                                hover:!text-white hover:bg-blue-400
                                active:!text-white active:bg-blue-500
                                rounded-xl m-2 w-64 h-12">
                                Fisika
                            </button>
                            <button type="submit" name="status" value="Belum mengumpulkan"
                                class="flex items-center justify-center px-4 py-2 font-bold text-gray-800 bg-gray-100
                                hover:!text-white hover:bg-blue-400
                                active:!text-white active:bg-blue-500
                                rounded-xl m-2 w-64 h-12">
                                Kimia
                            </button>
                            <button type="submit" name="status" value="Belum mengumpulkan"
                                class="flex items-center justify-center px-4 py-2 font-bold text-gray-800 bg-gray-100
                                hover:!text-white hover:bg-blue-400
                                active:!text-white active:bg-blue-500
                                rounded-xl m-2 w-64 h-12">
                                Biologi
                            </button>
                            <button type="submit" name="status" value="Belum mengumpulkan"
                                class="flex items-center justify-center px-4 py-2 font-bold text-gray-800 bg-gray-100
                                hover:!text-white hover:bg-blue-400
                                active:!text-white active:bg-blue-500
                                rounded-xl m-2 w-64 h-12">
                                Sejarah
                            </button>
                            <button type="submit" name="status" value="Belum mengumpulkan"
                                class="flex items-center justify-center px-4 py-2 font-bold text-gray-800 bg-gray-100
                                hover:!text-white hover:bg-blue-400
                                active:!text-white active:bg-blue-500
                                rounded-xl m-2 w-64 h-12">
                                Seni Budaya
                            </button>
                            <button type="submit" name="status" value="Belum mengumpulkan"
                                class="flex items-center justify-center px-4 py-2 font-bold text-gray-800 bg-gray-100
                                hover:!text-white hover:bg-blue-400
                                active:!text-white active:bg-blue-500
                                rounded-xl m-2 w-64 h-12">
                                Pendidikan Agama
                            </button>
                            <button type="submit" name="status" value="Belum mengumpulkan"
                                class="flex items-center justify-center px-4 py-2 font-bold text-gray-800 bg-gray-100
                                hover:!text-white hover:bg-blue-400
                                active:!text-white active:bg-blue-500
                                rounded-xl m-2 w-64 h-12">
                                Pendidikan Kewarganegaraan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div style="position: relative;">
                <div class="bg-white shadow-md py-10 px-5" style="border-radius: 15px;">
                    <h2 class="text-xl font-bold mb-2">Matematika Dasar</h2>
                    <p class="text-gray-600" style="margin-right: 150px">
                        Mapel : Matematika
                    </p>

                    {{-- button detail dan buka materi --}}
                    <div class="mt-4 flex gap-2" style="position: absolute; bottom: 15px; right: 15px;">
                        <button
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl flex-1 whitespace-nowrap">
                            Lihat detail
                        </button>
                        <a href="" target="_blank"
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-xl flex-1 text-center whitespace-nowrap">
                            Buka Materi
                        </a>
                    </div>

                    <!-- Tanggal Materi -->
                    <div class="absolute top-5 right-5 text-gray-600 font-semibold text-sm">
                        Kamis, 10 September 2024
                    </div>
                </div>
            </div>

            <div style="position: relative;">
                <div class="bg-white shadow-md py-10 px-5" style="border-radius: 15px;">
                    <h2 class="text-xl font-bold mb-2">Seni Kriya dan Pahat</h2>
                    <p class="text-gray-600" style="margin-right: 150px">
                        Mapel : Seni Budaya
                    </p>

                    {{-- button detail dan buka materi --}}
                    <div class="mt-4 flex gap-2" style="position: absolute; bottom: 15px; right: 15px;">
                        <button
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl flex-1 whitespace-nowrap">
                            Lihat detail
                        </button>
                        <a href="" target="_blank"
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-xl flex-1 text-center whitespace-nowrap">
                            Buka Materi
                        </a>
                    </div>

                    <!-- Tanggal Materi -->
                    <div class="absolute top-5 right-5 text-gray-600 font-semibold text-sm">
                        Kamis, 2 September 2024
                    </div>
                </div>
            </div>

            <div style="position: relative;">
                <div class="bg-white shadow-md py-10 px-5" style="border-radius: 15px;">
                    <h2 class="text-xl font-bold mb-2">Konsep Molekul</h2>
                    <p class="text-gray-600" style="margin-right: 150px">
                        Mapel : Kimia
                    </p>

                    {{-- button detail dan buka materi --}}
                    <div class="mt-4 flex gap-2" style="position: absolute; bottom: 15px; right: 15px;">
                        <button
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl flex-1 whitespace-nowrap">
                            Lihat detail
                        </button>
                        <a href="" target="_blank"
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-xl flex-1 text-center whitespace-nowrap">
                            Buka Materi
                        </a>
                    </div>

                    <!-- Tanggal Materi -->
                    <div class="absolute top-5 right-5 text-gray-600 font-semibold text-sm">
                        Kamis, 15 Oktober 2024
                    </div>
                </div>
            </div>
        </div>

        <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
        <script src="https://class.hummatech.com/user-assets/js/scripts.bundle.js"></script>
        {{-- script Modal --}}
        {{-- <script>
        function openModal(modalId) {
            // Tutup semua modal yang terbuka
            const modals = document.querySelectorAll('.tugasModal');
            modals.forEach(modal => modal.classList.add('hidden'));

            // Tampilkan modal yang diinginkan
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
            } else {
                console.error(`Modal dengan ID ${modalId} tidak ditemukan.`);
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
            } else {
                console.error(`Modal dengan ID ${modalId} tidak ditemukan.`);
            }
        }

        function updateFileName(input) {
            const fileNameSpan = document.getElementById(`file-name-${input.id.split('-')[1]}`);
            const fileName = input.files[0]?.name || 'Tidak ada file yang dipilih';
            fileNameSpan.textContent = fileName;
        }

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex'; // Tampilkan modal
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none'; // Sembunyikan modal
            }
        }
    </script> --}}
        {{-- end script modal --}}

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const filterButton = document.getElementById('filterButton');
                const filterDropdown = document.getElementById('filterDropdown');

                filterButton.addEventListener('click', (event) => {
                    event.stopPropagation(); // Mencegah event bubbling
                    filterDropdown.classList.toggle('hidden');
                });

                // Tutup dropdown jika klik di luar dropdown
                document.addEventListener('click', () => {
                    filterDropdown.classList.add('hidden');
                });
            });
        </script>

        <script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015"
            integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ=="
            data-cf-beacon='{"rayId":"8f0bc3aff833fd88","version":"2024.10.5","r":1,"token":"a20ac1c0d36b4fa6865d9d244f4efe5a","serverTiming":{"name":{"cfExtPri":true,"cfL4":true,"cfSpeedBrain":true,"cfCacheStatus":true}}}'
            crossorigin="anonymous"></script>

        <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
        {{-- npm flowbite --}}

        <script src="https://cdn.tailwindcss.com"></script>
</body>
<!--end::Body-->

<!-- Mirrored from preview.keenthemes.com/metronic8/demo31/ by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 14 Feb 2023 14:30:13 GMT -->

</html>
