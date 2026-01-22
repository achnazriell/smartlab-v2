@extends('layouts.appSiswa')

@section('content')
    <style>
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

        @media (max-width: 639px) {
            .covercard {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>

    <div class="container p-10">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="loader border-t-4 border-blue-600 rounded-full w-16 h-16 animate-spin"></div>
        </div>
        <!-- Header Section -->
        <div class="flex justify-between items-center my-8">
            <h1 class="text-2xl text-gray-700 font-poppins font-bold">
                Daftar Materi
            </h1>

            <!-- Filter & Search Tools -->
            <div class="flex items-center gap-2 flex-wrap">

                <!-- Search Form -->
                <form action="{{ route('semuamateri') }}" method="GET" class="flex items-center gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                        class="rounded-xl border border-gray-300 p-3 text-sm">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold p-3 px-4 rounded-xl">
                        <i class="fas fa-search"></i>
                    </button>
                </form>

                <!-- Filter Mata Pelajaran Dropdown -->
                <div class="relative">
                    <button id="filterButton"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold p-3 px-4 rounded-xl shadow-md">
                        <i class="fas fa-filter text-white"></i>
                    </button>
                    <div id="filterDropdown"
                        class="hidden absolute right-0 mt-2 w-72 bg-white border border-gray-300 rounded-xl shadow-md z-50"
                        style="max-height: 300px; overflow-y: auto;">

                        <!-- Fixed filter header styling -->
                        <div
                            class="px-4 py-3 text-lg font-semibold text-gray-700 border-b border-gray-300 sticky top-0 bg-white z-10">
                            Pilih Mata Pelajaran
                        </div>

                        <!-- Fixed filter form - changed from status to subject filter -->
                        <form method="GET" action="{{ route('semuamateri') }}" class="p-2">
                            @php
                                $subjects = [
                                    'Matematika',
                                    'Bahasa Indonesia',
                                    'Bahasa Inggris',
                                    'Fisika',
                                    'Kimia',
                                    'Biologi',
                                    'Sejarah',
                                    'Seni Budaya',
                                    'Pendidikan Agama',
                                    'Pendidikan Kewarganegaraan',
                                ];
                            @endphp

                            @foreach ($subjects as $subject)
                                <button type="submit" name="subject" value="{{ $subject }}"
                                    class="w-full flex items-center justify-center px-4 py-2 font-bold text-gray-800 bg-gray-100 hover:text-white hover:bg-blue-400 active:text-white active:bg-blue-500 rounded-xl m-2 h-12">
                                    {{ $subject }}
                                </button>
                            @endforeach
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Materi List Section -->
        <div class="space-y-4">
            @forelse ($materis as $materi)
                <div class="relative bg-white shadow-md py-6 px-5 rounded-xl">
                    <!-- Fixed tag structure for title and info -->
                    <h2 class="text-xl font-bold mb-2">
                        {{ $materi->title_materi }}
                    </h2>

                    <p class="text-gray-600 mb-4">
                        Mapel: {{ optional($materi->subject)->name_subject ?? 'N/A' }}
                    </p>

                    <!-- Removed "Lihat Detail" button and changed "Buka Materi" to navigate to materi.detail -->
                    <div class="flex gap-2 absolute bottom-4 right-4">
                        @if ($materi->file_materi)
                            <a href="{{ route('materi.show', $materi->id) }}"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl">
                                Buka Materi
                            </a>
                        @endif
                    </div>

                    <!-- Date -->
                    <div class="absolute top-5 right-5 text-sm text-gray-600">
                        {{ $materi->created_at->translatedFormat('l, d F Y') }}
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-md py-10 px-5 rounded-xl text-center">
                    <p class="text-gray-500">Belum ada materi untuk kelas kamu</p>
                </div>
            @endforelse
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <script>
        const loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen) {
            loadingScreen.classList.add('hidden');
        }

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const filterButton = document.getElementById('filterButton');
            const filterDropdown = document.getElementById('filterDropdown');

            filterButton?.addEventListener('click', (event) => {
                event.stopPropagation();
                filterDropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', (event) => {
                if (!filterDropdown.contains(event.target) && event.target !== filterButton) {
                    filterDropdown.classList.add('hidden');
                }
            });
        });
    </script>
@endsection
