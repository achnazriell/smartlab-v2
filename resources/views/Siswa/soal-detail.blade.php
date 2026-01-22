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

        .card-soal {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background: white;
        }

        .card-soal:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(51, 65, 85, 0.15);
            border-color: #cbd5e1;
        }

        .status-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-belum {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-sudah {
            background-color: #dcfce7;
            color: #15803d;
        }

        .status-kadaluarsa {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>

    <div class="min-h-screen bg-gradient-to-b from-slate-50 to-white">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="loader border-t-4 border-blue-600 rounded-full w-16 h-16 animate-spin"></div>
        </div>

        <!-- Back Navigation -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-6">
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-2 text-slate-600 hover:text-blue-600 font-medium transition-colors duration-200">
                <i class="fas fa-arrow-left text-lg"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>

        <!-- Main Content Container -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
            <!-- Header with gradient and decorative elements -->
            <div class="bg-gradient-to-r from-blue-400 to-blue-300 rounded-3xl shadow-2xl overflow-hidden mb-8">
                <div class="relative px-8 sm:px-10 py-12 sm:py-16">
                    <!-- Decorative accent elements -->
                    <div class="absolute top-0 right-0 w-40 h-40 bg-blue-400 rounded-full opacity-10 -mr-20 -mt-20"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-white rounded-full opacity-5 -ml-16 -mb-16"></div>

                    <div class="relative z-10">
                        <h1 class="text-4xl sm:text-5xl font-bold text-white mb-4 leading-tight">
                            Daftar Soal & Kuis
                        </h1>

                        <!-- Meta information layout -->
                        <div class="flex flex-wrap gap-8 text-blue-50">
                            <div class="flex items-center gap-3">
                                <div class="bg-white bg-opacity-20 rounded-lg p-2.5">
                                    <i class="fas fa-file-lines text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-xs opacity-75 uppercase tracking-wider">Motivasi</p>
                                    <p class="font-semibold">Asah kemampuanmu dengan berbagai soal</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="bg-white bg-opacity-20 rounded-lg p-2.5">
                                    <i class="fas fa-star text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-xs opacity-75 uppercase tracking-wider">Selamat Datang</p>
                                    <p class="font-semibold">Hai, {{ Auth::user()->name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <form action="{{ route('soal.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari soal..."
                        class="flex-1 px-4 py-3 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-xl transition-all hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path
                                d="m19.6 21l-6.3-6.3q-.75.6-1.725.95T9.5 16q-2.725 0-4.612-1.888T3 9.5t1.888-4.612T9.5 3t4.613 1.888T16 9.5q0 1.1-.35 2.075T14.7 13.3l6.3 6.3zM9.5 14q1.875 0 3.188-1.312T14 9.5t-1.312-3.187T9.5 5T6.313 6.313T5 9.5t1.313 3.188T9.5 14" />
                        </svg>
                    </button>
                </form>

                <!-- Filter Status Dropdown -->
                <div class="relative" x-data="{ filterOpen: false }">
                    <button @click="filterOpen = !filterOpen"
                        class="bg-blue-600 hover:bg-blue-700 text-white p-2 sm:p-3 rounded-xl transition-all hover:shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path d="M10 18h4v-2h-4v2zM3 6v2h18V6H3zm3 7h12v-2H6v2z" />
                        </svg>
                    </button>
                    <div x-show="filterOpen" @click.outside="filterOpen = false"
                        class="absolute right-0 mt-2 w-64 bg-white border border-gray-300 rounded-xl shadow-md z-50 py-2">
                        <form action="{{ route('soal.index') }}" method="GET" class="space-y-1">
                            @if (request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            <button type="submit" name="status" value=""
                                class="w-full px-4 py-2 text-left hover:bg-blue-50 transition-colors">
                                Semua Status
                            </button>
                            <button type="submit" name="status" value="belum_dikerjakan"
                                class="w-full px-4 py-2 text-left hover:bg-blue-50 transition-colors">
                                Belum Dikerjakan
                            </button>
                            <button type="submit" name="status" value="sudah_dikerjakan"
                                class="w-full px-4 py-2 text-left hover:bg-blue-50 transition-colors">
                                Sudah Dikerjakan
                            </button>
                            <button type="submit" name="status" value="kadaluarsa"
                                class="w-full px-4 py-2 text-left hover:bg-blue-50 transition-colors">
                                Kadaluarsa
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quiz Cards Grid -->
        <div class="bg-white rounded-2xl shadow-lg border border-blue-100 p-6 lg:p-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($quizzes as $quiz)
                    <div class="card-soal bg-white rounded-2xl shadow-md overflow-hidden">
                        <div class="relative h-48 flex flex-col justify-between text-white p-5"
                            style="background-image: url('image/siswa/cardmapel.svg'); background-size: cover; background-position: center;">
                            <!-- Quiz Info -->
                            <div class="flex-1 flex flex-col justify-center">
                                <h3 class="text-xl lg:text-2xl font-bold mb-2 drop-shadow-lg line-clamp-2">
                                    {{ $quiz->title }}
                                </h3>

                                <!-- Subject Name -->
                                <div class="flex items-center text-white/95 text-sm drop-shadow-md">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 24 24" fill="currentColor" class="mr-1">
                                        <path
                                            d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10s10-4.477 10-10S17.523 2 12 2M8.5 9.5a3.5 3.5 0 1 1 7 0a3.5 3.5 0 0 1-7 0m9.758 7.484A7.99 7.99 0 0 1 12 20a7.99 7.99 0 0 1-6.258-3.016C7.363 15.821 9.575 15 12 15s4.637.821 6.258 1.984" />
                                    </svg>
                                    <span class="ml-1">{{ $quiz->subject->name_subject ?? 'N/A' }}</span>
                                </div>

                                <!-- Teacher Name -->
                                <div class="flex items-center text-white/90 text-xs drop-shadow-md mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                        viewBox="0 0 24 24" fill="currentColor" class="mr-1">
                                        <path
                                            d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                    </svg>
                                    <span class="ml-1">{{ $quiz->teacher->name ?? 'N/A' }}</span>
                                </div>
                            </div>

                            <!-- Quiz Stats -->
                            <div
                                class="flex items-center justify-between mt-4 text-sm text-white/95 border-t border-white/20 pt-3">
                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 20.94c4.97 0 9-3.6 9-8.02s-4.03-8.02-9-8.02-9 3.6-9 8.02" />
                                        <polyline points="8 12 12 16 16 12"></polyline>
                                    </svg>
                                    <span>{{ $quiz->total_questions ?? 0 }} soal</span>
                                </div>

                                <div class="flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                    <span>{{ $quiz->time_limit ?? 0 }} menit</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer with Status and Button -->
                        <div class="p-4 bg-gray-50">
                            <div class="flex items-center justify-between mb-3">
                                @php
                                    $status = 'belum_dikerjakan';
                                    $statusText = 'Belum Dikerjakan';
                                    $statusClass = 'status-belum';

                                    if ($quiz->student_answer && $quiz->student_answer->is_submitted) {
                                        $status = 'sudah_dikerjakan';
                                        $statusText = 'Sudah Dikerjakan';
                                        $statusClass = 'status-sudah';
                                    }

                                    if ($quiz->end_date && \Carbon\Carbon::parse($quiz->end_date)->isPast()) {
                                        $status = 'kadaluarsa';
                                        $statusText = 'Kadaluarsa';
                                        $statusClass = 'status-kadaluarsa';
                                    }
                                @endphp
                                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                <span class="text-xs text-gray-600">
                                    Deadline: {{ \Carbon\Carbon::parse($quiz->end_date)->format('d M Y') ?? 'N/A' }}
                                </span>
                            </div>

                            @if ($status === 'sudah_dikerjakan')
                                <a href="{{ route('soal.hasil', $quiz->id) }}"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors text-center block">
                                    Lihat Hasil
                                </a>
                            @else
                                <a href="{{ route('soal.show', $quiz->id) }}"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors text-center block">
                                    Mulai Kuis
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <!-- Empty State -->
                    <div class="col-span-full py-16 text-center">
                        <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z"></path>
                                <line x1="12" y1="9" x2="12" y2="15"></line>
                                <line x1="9" y1="12" x2="15" y2="12"></line>
                            </svg>
                        </div>
                        <p class="text-gray-700 font-semibold text-lg">Belum Ada Soal</p>
                        <p class="text-gray-400 text-sm mt-1">Soal/Kuis akan muncul di sini setelah ditambahkan</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($quizzes->hasPages())
                <div class="mt-8 pt-6 border-t border-blue-100">
                    {{ $quizzes->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Scripts -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadingScreen = document.getElementById('loadingScreen');
            if (loadingScreen) {
                loadingScreen.classList.add('hidden');
            }
        });
    </script>
@endsection
