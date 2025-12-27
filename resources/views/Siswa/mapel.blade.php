@extends('layouts.appSiswa')

@section('content')
    <style>
        /* Improved styles for modern look */
        .banner-container {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.2);
        }

        .banner-container img {
            width: 100%;
            height: auto;
            display: block;
        }

        .namasiswa {
            position: absolute;
            top: 20%;
            left: 5%;
            margin: 0;
        }

        .span-nama {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }


        .deskripsi {
            position: absolute;
            top: 40%;
            left: 5%;
            max-width: 50%;
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .span-nama {
                font-size: 1.2rem;
            }

            .deskripsi {
                font-size: 0.75rem;
                max-width: 70%;
            }
        }

        /* Card hover effect */
        .mapel-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 16px;
            overflow: hidden;
        }

        .mapel-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.3);
        }

        /* Title underline animation */
        @keyframes expandWidth {
            from {
                width: 0;
            }

            to {
                width: 100%;
            }
        }

        .title-underline {
            position: absolute;
            bottom: -4px;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
            border-radius: 2px;
            animation: expandWidth 0.6s ease-out forwards;
        }

        /* Search input focus */
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        /* Empty state */
        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }
    </style>

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 ml-20 mt-16 lg:p-10">
        <!-- Banner Section -->
        <div class="banner-container mb-8 relative">
            <img src="image/banner mapel.svg" alt="banner mapel" class="w-full">

            <!-- Wrapper teks -->
            <div class="absolute inset-0 flex flex-col justify-center pl-8 py-6">
                <p class="span-nama text-white font-bold">
                    Hai, {{ Auth::user()->name }}
                </p>

                <p class="deskripsi text-white/90 max-w-md mt-2">
                    Belajar adalah perjalanan tanpa akhir. Mari jadikan setiap hari kesempatan
                    untuk menambah wawasan dan pengalaman.
                </p>
            </div>
        </div>

        <!-- Header Section with Title and Search -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-800 relative" style="font-family: 'Poppins', sans-serif;">
                Daftar Mata Pelajaran
                <span class="title-underline"></span>
            </h1>

            <!-- Search Form -->
            <form action="" method="GET" class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" id="search" name="search" placeholder="Cari mata pelajaran..."
                        class="search-input w-64 px-4 py-3 pr-10 rounded-xl border border-gray-200 bg-white text-gray-700 text-sm transition-all duration-200"
                        style="font-family: 'Poppins', sans-serif;">
                </div>
                <input type="hidden" id="activeTabInput" name="tab" value="">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white p-3 rounded-xl transition-all duration-200 hover:shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                        <path fill="currentColor"
                            d="m19.6 21l-6.3-6.3q-.75.6-1.725.95T9.5 16q-2.725 0-4.612-1.888T3 9.5t1.888-4.612T9.5 3t4.613 1.888T16 9.5q0 1.1-.35 2.075T14.7 13.3l6.3 6.3zM9.5 14q1.875 0 3.188-1.312T14 9.5t-1.312-3.187T9.5 5T6.313 6.313T5 9.5t1.313 3.188T9.5 14" />
                    </svg>
                </button>
            </form>
        </div>

        <!-- Subjects Grid Container -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($subjects as $subject)
                    <a href="{{ route('Materi', ['materi_id' => $subject->id]) }}" class="mapel-card block">
                        <div class="relative h-64 flex flex-col justify-between text-white p-5"
                            style="background-image: url('image/siswa/cardmapel.svg'); background-size: cover; background-position: center;">
                            <!-- Subject Info -->
                            <div class="flex-1 flex flex-col justify-center">
                                <!-- Subject Name -->
                                <h3 class="text-3xl lg:text-4xl font-bold mb-3"
                                    style="font-family: 'Poppins', sans-serif; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                                    {{ $subject->name_subject }}
                                </h3>

                                <!-- Teacher Name -->
                                <div class="flex items-center text-white/90 text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24">
                                        <g fill="none" fill-rule="evenodd">
                                            <path
                                                d="m12.594 23.258l-.012.002l-.071.035l-.02.004l-.014-.004l-.071-.036q-.016-.004-.024.006l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.016-.018m.264-.113l-.014.002l-.184.093l-.01.01l-.003.011l.018.43l.005.012l.008.008l.201.092q.019.005.029-.008l.004-.014l-.034-.614q-.005-.019-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.003-.011l.018-.43l-.003-.012l-.01-.01z" />
                                            <path fill="currentColor"
                                                d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10s10-4.477 10-10S17.523 2 12 2M8.5 9.5a3.5 3.5 0 1 1 7 0a3.5 3.5 0 0 1-7 0m9.758 7.484A7.99 7.99 0 0 1 12 20a7.99 7.99 0 0 1-6.258-3.016C7.363 15.821 9.575 15 12 15s4.637.821 6.258 1.984" />
                                        </g>
                                    </svg>
                                    @php
                                        // Ambil ID kelas user
                                        $kelasUser = auth()->user()->class->pluck('id');

                                        // Cari guru yang mengajar kelas yang sama
                                        $gurus = \App\Models\User::role('Guru')
                                            ->whereHas('class', function ($q) use ($kelasUser) {
                                                $q->whereIn('classes.id', $kelasUser);
                                            })
                                            ->get();
                                    @endphp

                                    @if ($gurus->count())
                                        @foreach ($gurus as $guru)
                                            <span class="ml-2">{{ $guru->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="ml-2">Belum ada guru</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Stats Row -->
                            <div class="flex items-center gap-3 mt-4">
                                @if (auth()->user() && auth()->user()->class()->exists())
                                    <div class="flex-1 bg-blue-800/80 backdrop-blur-sm rounded-lg py-2 px-3">
                                        <div class="flex items-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                viewBox="0 0 24 24">
                                                <path fill="currentColor" d="M16 1H8v4h8z" />
                                                <path fill="currentColor"
                                                    d="M3 3h3v4h12V3h3v8.674A7 7 0 0 0 13.101 23H3z" />
                                                <path fill="currentColor"
                                                    d="M12.5 18a5.5 5.5 0 1 1 11 0a5.5 5.5 0 0 1-11 0m7.914 1L19 17.586v-1.834h-2v2.662l2 2z" />
                                            </svg>
                                            <span class="text-xs ml-2">{{ $subject->task_count }} Tugas</span>
                                        </div>
                                    </div>
                                @endif

                                <div class="bg-blue-800/80 backdrop-blur-sm rounded-lg py-2 px-3">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            viewBox="0 0 24 24">
                                            <path fill="currentColor"
                                                d="M13 9V3.5L18.5 9M6 2c-1.11 0-2 .89-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z" />
                                        </svg>
                                        <span class="text-xs ml-2">{{ $subject->materi_count }} Materi</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <!-- Empty State -->
                    <div class="col-span-full empty-state">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-full mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-10 h-10 text-red-500">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <p class="text-gray-600 font-semibold text-lg" style="font-family: 'Poppins', sans-serif;">Belum Ada
                            Mata Pelajaran</p>
                        <p class="text-gray-400 text-sm mt-1">Mata pelajaran akan muncul di sini setelah ditambahkan</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($subjects->hasPages())
                <div class="mt-8 pt-6 border-t border-gray-100">
                    {{ $subjects->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Scroll to Top Button -->
    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
        <span class="svg-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)"
                    fill="currentColor" />
                <path
                    d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z"
                    fill="currentColor" />
            </svg>
        </span>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session(key: 'error'))
                Swal.fire({
                    icon: "error",
                    title: "Oops... Maaf",
                    text: "Anda Belum Memiliki Kelas",
                });
            @endif
        });
    </script>
@endsection
