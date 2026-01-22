@extends('layouts.appSiswa')

@section('content')
    <style>
        .banner-container {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.15);
            border: 2px solid rgba(37, 99, 235, 0.1);
        }

        .banner-container img {
            width: 100%;
            height: auto;
            display: block;
        }

        .span-nama {
            font-size: clamp(1.5rem, 6vw, 2.5rem);
            font-weight: 700;
            color: white;
            text-shadow: 0 3px 12px rgba(0, 0, 0, 0.3);
            line-height: 1.2;
        }

        .deskripsi {
            font-size: clamp(0.75rem, 2.5vw, 1rem);
            color: rgba(255, 255, 255, 0.95);
            line-height: 1.5;
        }

        .mapel-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 16px;
            overflow: hidden;
            border: 2px solid rgba(37, 99, 235, 0.1);
        }

        .mapel-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.25);
            border-color: rgba(37, 99, 235, 0.3);
        }
    </style>

    <div class="p-4 sm:p-6 lg:p-8">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="loader border-t-4 border-blue-600 rounded-full w-16 h-16 animate-spin"></div>
        </div>
        <!-- Improved banner with better responsive layout -->
        <div class="banner-container mb-8">
            <img src="image/banner mapel.svg" alt="banner mapel" class="w-full">

            <div class="absolute inset-0 flex flex-col justify-center items-center p-5 sm:p-8">
                <p class="span-nama">Hai, {{ Auth::user()->name }}</p>
                <p class="deskripsi text-center mt-3 max-w-2xl">
                    Belajar adalah perjalanan tanpa akhir. Mari jadikan setiap hari kesempatan <br>untuk menambah wawasan
                    dan pengalaman.
                </p>
            </div>
        </div>

        <!-- Header Section with Title and Search -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <h1 class="text-2xl lg:text-3xl font-bold text-blue-900">
                Daftar Mata Pelajaran
            </h1>

            <!-- Improved search form styling with better responsive design -->
            <form action="" method="GET" class="w-full sm:w-auto flex items-center gap-2">
                <input type="text" id="search" name="search" placeholder="Cari mata pelajaran..."
                    class="flex-1 px-4 py-2 sm:py-3 rounded-xl border border-blue-200 bg-white text-gray-700 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <input type="hidden" id="activeTabInput" name="tab" value="">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white p-2 sm:p-3 rounded-xl transition-all hover:shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="currentColor">
                        <path
                            d="m19.6 21l-6.3-6.3q-.75.6-1.725.95T9.5 16q-2.725 0-4.612-1.888T3 9.5t1.888-4.612T9.5 3t4.613 1.888T16 9.5q0 1.1-.35 2.075T14.7 13.3l6.3 6.3zM9.5 14q1.875 0 3.188-1.312T14 9.5t-1.312-3.187T9.5 5T6.313 6.313T5 9.5t1.313 3.188T9.5 14" />
                    </svg>
                </button>
            </form>
        </div>

        <!-- Subjects Grid Container -->
        <div class="bg-white rounded-2xl shadow-lg border border-blue-100 p-6 lg:p-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($subjects as $subject)
                    <a href="{{ route('Materi', ['materi_id' => $subject->id]) }}" class="mapel-card block">
                        <div class="relative h-48 flex flex-col justify-between text-white p-5"
                            style="background-image: url('image/siswa/cardmapel.svg'); background-size: cover; background-position: center;">
                            <!-- Subject Info -->
                            <div class="flex-1 flex flex-col justify-center">
                                <!-- Improved subject name styling with better text contrast -->
                                <h3 class="text-2xl lg:text-3xl font-bold mb-3 drop-shadow-lg line-clamp-2">
                                    {{ $subject->name_subject }}
                                </h3>

                                <!-- Teacher Name -->
                                <div class="flex items-center text-white/95 text-sm drop-shadow-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10s10-4.477 10-10S17.523 2 12 2M8.5 9.5a3.5 3.5 0 1 1 7 0a3.5 3.5 0 0 1-7 0m9.758 7.484A7.99 7.99 0 0 1 12 20a7.99 7.99 0 0 1-6.258-3.016C7.363 15.821 9.575 15 12 15s4.637.821 6.258 1.984" />
                                    </svg>
                                    @php
                                        $kelasId = auth()->user()->student->class_id;

                                        $gurus = \App\Models\User::role('Guru')
                                            ->whereHas('tasks', function ($q) use ($kelasId, $subject) {
                                                $q->where('subject_id', $subject->id)->whereHas('classes', function (
                                                    $c,
                                                ) use ($kelasId) {
                                                    $c->where('classes.id', $kelasId);
                                                });
                                            })
                                            ->distinct()
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
                            <div class="flex items-center justify-between mt-4 text-sm text-white/95">
                                {{-- Tugas --}}
                                <div class="flex items-center gap-1">
                                    <svg width="16" height="16" fill="currentColor">...</svg>

                                    @if ($subject->unfinished_task_count > 0)
                                        <span>{{ $subject->unfinished_task_count }} tugas belum dikerjakan</span>
                                    @else
                                        <span class="opacity-80">Tidak ada tugas</span>
                                    @endif
                                </div>

                                {{-- Materi --}}
                                <div class="flex items-center gap-1">
                                    <svg width="16" height="16" fill="currentColor">...</svg>
                                    <span>{{ $subject->materi_count }} materi</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <!-- Empty State -->
                    <div class="col-span-full py-16 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="w-10 h-10 text-blue-500">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <p class="text-gray-700 font-semibold text-lg">Belum Ada Mata Pelajaran</p>
                        <p class="text-gray-400 text-sm mt-1">Mata pelajaran akan muncul di sini setelah ditambahkan</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($subjects->hasPages())
                <div class="mt-8 pt-6 border-t border-blue-100">
                    {{ $subjects->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>
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
        
        const loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen) {
            loadingScreen.classList.add('hidden');
        }
    </script>
@endsection
