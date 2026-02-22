@extends('layouts.appSiswa')

@section('content')
    <style>
        .banner-container {
            position: relative;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(59, 130, 246, 0.2);
            border: none;
        }

        .banner-container img {
            width: 100%;
            height: auto;
            display: block;
        }

        .span-nama {
            font-size: clamp(1.8rem, 7vw, 2.8rem);
            font-weight: 800;
            color: white;
            text-shadow: 0 4px 16px rgba(0, 0, 0, 0.35);
            line-height: 1.1;
        }

        .deskripsi {
            font-size: clamp(0.9rem, 2.8vw, 1.1rem);
            color: rgba(255, 255, 255, 0.97);
            line-height: 1.6;
            font-weight: 500;
        }

        .card-quiz {
            transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            border-radius: 18px;
            overflow: hidden;
            border: none;
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.12);
        }

        .card-quiz:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 48px rgba(59, 130, 246, 0.25);
        }

        .quiz-header-gradient {
            background-image: url('{{ asset('image/cardquiz.webp') }}');
            background-size: cover;
            background-position: center;
            color: white;
            position: relative;
        }

        .quiz-header-gradient::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.15) 0%, rgba(0, 0, 0, 0.08) 100%);
        }

        .status-badge {
            display: inline-block;
            padding: 0.4rem 0.9rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .status-available {
            background-color: rgba(34, 197, 94, 0.12);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .status-ongoing {
            background-color: rgba(245, 158, 11, 0.12);
            color: #92400e;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .status-completed {
            background-color: rgba(107, 114, 128, 0.12);
            color: #374151;
            border: 1px solid rgba(107, 114, 128, 0.3);
        }

        .status-upcoming {
            background-color: rgba(59, 130, 246, 0.12);
            color: #1e40af;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .tab-active {
            border-color: #2563eb !important;
            color: #2563eb !important;
            font-weight: 700;
        }

        .btn-icon {
            display: inline-block;
            margin-right: 6px;
        }

        .quiz-header-gradient>* {
            position: relative;
            z-index: 1;
        }

        /* Custom Modal Styles */
        .custom-modal-backdrop {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 9998;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease-out;
        }

        .custom-modal-backdrop.active {
            display: flex;
            opacity: 1;
        }

        .custom-modal {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 90%;
            margin: auto;
            transform: translateY(-20px);
            opacity: 0;
            transition: all 0.3s ease-out;
        }

        .custom-modal.active {
            transform: translateY(0);
            opacity: 1;
        }

        .custom-modal-header {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .custom-modal-body {
            padding: 1rem 1.5rem;
        }

        .custom-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }

        /* Custom Alert Styles */
        .custom-alert {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            z-index: 9999;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            max-width: 400px;
        }

        .custom-alert.show {
            transform: translateX(0);
            opacity: 1;
        }

        .custom-alert.success {
            border-left: 4px solid #10b981;
        }

        .custom-alert.error {
            border-left: 4px solid #ef4444;
        }

        .custom-alert.warning {
            border-left: 4px solid #f59e0b;
        }

        .custom-alert.info {
            border-left: 4px solid #3b82f6;
        }

        .alert-icon {
            flex-shrink: 0;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert-icon.success {
            background-color: #d1fae5;
            color: #047857;
        }

        .alert-icon.error {
            background-color: #fee2e2;
            color: #dc2626;
        }

        .alert-icon.warning {
            background-color: #fef3c7;
            color: #d97706;
        }

        .alert-icon.info {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .alert-content {
            flex: 1;
        }

        .alert-title {
            font-weight: 600;
            color: #111827;
            font-size: 0.875rem;
            margin-bottom: 0.125rem;
        }

        .alert-message {
            color: #6b7280;
            font-size: 0.75rem;
        }

        .alert-close {
            color: #9ca3af;
            cursor: pointer;
            padding: 0.25rem;
            transition: color 0.2s;
        }

        .alert-close:hover {
            color: #374151;
        }
    </style>

    <div class="p-4 sm:p-6 lg:p-8">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="loader border-t-4 border-blue-600 rounded-full w-16 h-16 animate-spin"></div>
        </div>

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <strong>Error:</strong> {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- Banner Header -->
        <div class="banner-container mb-8">
            <img src="{{ asset('image/banner mapel.webp') }}" alt="banner quiz" class="w-full">
            <div class="absolute inset-0 flex flex-col justify-center items-center p-5 sm:p-8">
                <p class="span-nama">Hai, {{ Auth::user()->name }}</p>
                <p class="deskripsi text-center mt-3 max-w-2xl">
                    Ikuti quiz interaktif dengan fitur menarik untuk meningkatkan pemahaman Anda
                </p>
            </div>
        </div>

        <!-- Header Section with Title and Search -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <h1 class="text-2xl lg:text-3xl font-bold text-blue-900">
                Quiz Interaktif
            </h1>

            <!-- Search and Filter Section -->
            <div class="w-full sm:w-auto flex items-center gap-2 flex-wrap">
                <form action="{{ route('quiz.index') }}" method="GET" class="flex items-center gap-2">
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari quiz..."
                        class="flex-1 px-4 py-2 sm:py-3 rounded-xl border border-blue-200 bg-white text-gray-700 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white p-2 sm:p-3 rounded-xl transition-all hover:shadow-lg">
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
                        <form action="{{ route('quiz.index') }}" method="GET" class="space-y-1">
                            @if (request('search'))
                                <input type="hidden" name="search" value="{{ request('search') }}">
                            @endif
                            <button type="submit" name="status" value=""
                                class="w-full px-4 py-2 text-left hover:bg-blue-50 transition-colors">
                                Semua Status
                            </button>
                            <button type="submit" name="status" value="available"
                                class="w-full px-4 py-2 text-left hover:bg-blue-50 transition-colors">
                                Tersedia
                            </button>
                            <button type="submit" name="status" value="ongoing"
                                class="w-full px-4 py-2 text-left hover:bg-blue-50 transition-colors">
                                Sedang Dikerjakan
                            </button>
                            <button type="submit" name="status" value="completed"
                                class="w-full px-4 py-2 text-left hover:bg-blue-50 transition-colors">
                                Selesai
                            </button>
                            <button type="submit" name="status" value="upcoming"
                                class="w-full px-4 py-2 text-left hover:bg-blue-50 transition-colors">
                                Akan Datang
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quiz Grid -->
        @if ($quizzes->count() > 0)
            <div class="bg-white rounded-2xl shadow-lg border border-blue-100 p-6 lg:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($quizzes as $quiz)
                        @php
                            $status = $quiz->display_status ?? 'available';

                            // Default values
                            $statusClass = 'status-available';
                            $statusText = 'Tersedia';
                            $buttonClass = 'bg-blue-600 hover:bg-blue-700';
                            $buttonText = 'Lihat Quiz';
                            $buttonLink = route('quiz.room', $quiz->id);
                            $disabled = false;
                            $icon = 'fa-eye';

                            // Jika sudah selesai (completed) → Lihat Hasil
                            if ($status === 'completed') {
                                $statusClass = 'status-completed';
                                $statusText = 'Selesai';
                                $buttonClass = 'bg-green-600 hover:bg-green-700';
                                $buttonText = 'Lihat Hasil';
                                $buttonLink =
                                    $quiz->last_attempt && $quiz->last_attempt->id
                                        ? route('quiz.result', [
                                            'quiz' => $quiz->id,
                                            'attempt' => $quiz->last_attempt->id,
                                        ])
                                        : route('quiz.index');
                                $icon = 'fa-check-circle';
                            }
                            // Jika quiz sedang berlangsung (room terbuka & sudah dimulai)
                            elseif ($quiz->is_room_open && $quiz->is_quiz_started) {
                                $statusClass = 'status-ongoing';
                                $statusText = 'Sedang Berlangsung';
                                $buttonClass = 'bg-yellow-500 hover:bg-yellow-600';
                                $buttonText = 'Gabung Quiz';
                                $buttonLink = route('quiz.room', $quiz->id);
                                $icon = 'fa-play-circle';
                            }
                            // Jika ruangan terbuka tapi belum dimulai
                            elseif ($quiz->is_room_open && !$quiz->is_quiz_started) {
                                $statusClass = 'status-waiting';
                                $statusText = 'Ruangan Terbuka';
                                $buttonClass = 'bg-blue-600 hover:bg-blue-700';
                                $buttonText = 'Masuk Ruangan';
                                $buttonLink = route('quiz.room', $quiz->id);
                                $icon = 'fa-door-open';
                            }
                            // Jika belum dimulai (upcoming)
                            elseif ($status === 'upcoming') {
                                $statusClass = 'status-upcoming';
                                $statusText = 'Akan Datang';
                                $buttonClass = 'bg-gray-400 cursor-not-allowed';
                                $buttonText = 'Belum Dimulai';
                                $disabled = true;
                                $icon = 'fa-clock';
                            }
                            // Jika sudah selesai karena waktu habis (finished)
                            elseif ($status === 'finished') {
                                $statusClass = 'status-completed';
                                $statusText = 'Selesai';
                                $buttonClass = 'bg-gray-400 cursor-not-allowed';
                                $buttonText = 'Quiz Berakhir';
                                $disabled = true;
                                $icon = 'fa-calendar-times';
                            }
                        @endphp

                        <div class="card-quiz bg-white shadow-md">
                            <!-- Quiz Header with Gradient Background -->
                            <div class="quiz-header-gradient p-6 relative">
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
                                        <span class="ml-1">
                                            {{ $quiz->teacher->user->name ?? 'Guru' }}
                                        </span>
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
                                        <span>{{ $quiz->questions_count ?? 0 }} soal</span>
                                    </div>

                                    <div class="flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        <span>{{ $quiz->duration ?? 0 }} menit</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Card Footer with Status and Button -->
                            <div class="p-4 bg-gray-50">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                    <span class="text-xs text-gray-600">
                                        @if ($quiz->end_at)
                                            Deadline: {{ $quiz->end_at->format('d M Y, H:i') }}
                                        @else
                                            Tanpa Deadline
                                        @endif
                                    </span>
                                </div>

                                @if ($disabled)
                                    @if ($status === 'upcoming')
                                        <button disabled
                                            class="w-full {{ $buttonClass }} text-white font-semibold py-2 px-4 rounded-lg transition-colors text-center block">
                                            <i class="fas {{ $icon }} btn-icon"></i>
                                            Belum Dibuka
                                        </button>
                                    @else
                                        <button disabled
                                            class="w-full {{ $buttonClass }} text-white font-semibold py-2 px-4 rounded-lg transition-colors text-center block">
                                            <i class="fas {{ $icon }} btn-icon"></i>
                                            {{ $buttonText }}
                                        </button>
                                    @endif
                                @else
                                    <!-- Untuk semua status yang aktif, langsung ke ruangan -->
                                    <a href="{{ $buttonLink }}"
                                        class="w-full {{ $buttonClass }} text-white font-semibold py-2 px-4 rounded-lg transition-colors text-center block">
                                        <i class="fas {{ $icon }} btn-icon"></i>
                                        {{ $buttonText }}
                                    </a>
                                @endif

                                @if ($status === 'completed' && $quiz->can_retake)
                                    <button onclick="showRetakeConfirm('{{ $quiz->id }}', '{{ $quiz->title }}')"
                                        class="w-full mt-2 border border-blue-600 text-blue-600 hover:bg-blue-50 font-medium py-2 px-4 rounded-lg transition-colors text-center block">
                                        <i class="fas fa-redo btn-icon"></i>
                                        Ulangi Quiz
                                    </button>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if ($quizzes->hasPages())
                    <div class="mt-8 pt-6 border-t border-blue-100">
                        {{ $quizzes->links('vendor.pagination.tailwind') }}
                    </div>
                @endif
            @else
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
                    <p class="text-gray-700 font-semibold text-lg">Belum Ada Quiz</p>
                    <p class="text-gray-400 text-sm mt-1">
                        @if (request()->has('search'))
                            Tidak ada quiz yang cocok dengan pencarian Anda.
                        @elseif(request()->routeIs('quiz.active'))
                            Tidak ada quiz aktif saat ini.
                        @elseif(request()->routeIs('quiz.upcoming'))
                            Tidak ada quiz yang akan datang.
                        @elseif(request()->routeIs('quiz.completed'))
                            Belum ada quiz yang diselesaikan.
                        @else
                            Quiz akan muncul di sini setelah ditambahkan
                        @endif
                    </p>
                </div>
            </div>
        @endif
    </div>

    <!-- Retake Confirmation Modal -->
    <div class="custom-modal-backdrop" id="retakeModalBackdrop">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <div
                            class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900" id="retakeModalTitle"></h2>
                        <p class="text-sm text-gray-600 mt-1">Anda yakin ingin mengulang quiz ini?</p>
                    </div>
                </div>
            </div>
            <div class="custom-modal-body">
                <div class="space-y-4">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 text-yellow-700">
                            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-sm font-medium">Hasil percobaan sebelumnya akan dihitung ulang!</p>
                        </div>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="space-y-2 text-sm">
                            <p class="text-blue-700 font-medium mb-2">Informasi Quiz:</p>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Nilai sebelumnya:</span>
                                <span class="font-medium" id="previousScore">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Jumlah percobaan:</span>
                                <span class="font-medium" id="attemptCount">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button onclick="hideRetakeModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded-lg font-medium transition-colors">
                    Batal
                </button>
                <button onclick="proceedRetake()"
                    class="px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-lg font-medium transition-colors">
                    Ulangi Quiz
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hide loading screen
            const loadingScreen = document.getElementById('loadingScreen');
            if (loadingScreen) {
                setTimeout(() => {
                    loadingScreen.style.opacity = '0';
                    setTimeout(() => {
                        loadingScreen.style.display = 'none';
                    }, 300);
                }, 500);
            }

            // Auto-hide alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.bg-red-100, .bg-green-100');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                });
            }, 5000);
        });

        // Retake modal variables
        let currentRetakeQuizId = null;

        function showRetakeConfirm(quizId, quizTitle) {
            currentRetakeQuizId = quizId;

            // Set modal content
            document.getElementById('retakeModalTitle').textContent = `Ulangi Quiz: ${quizTitle}`;

            // You would typically fetch previous attempt data here
            // For now, we'll show placeholder data
            document.getElementById('previousScore').textContent = 'Belum ada data';
            document.getElementById('attemptCount').textContent = '0';

            // Show modal
            const backdrop = document.getElementById('retakeModalBackdrop');
            const modal = backdrop.querySelector('.custom-modal');

            backdrop.classList.add('active');
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);

            document.body.style.overflow = 'hidden';
        }

        function hideRetakeModal() {
            const backdrop = document.getElementById('retakeModalBackdrop');
            const modal = backdrop.querySelector('.custom-modal');

            modal.classList.remove('active');
            setTimeout(() => {
                backdrop.classList.remove('active');
                document.body.style.overflow = '';
            }, 300);
        }

        function proceedRetake() {
            if (!currentRetakeQuizId) return;

            hideRetakeModal();

            // Show loading alert
            showAlert('info', 'Memproses...', 'Sedang mempersiapkan quiz untuk Anda...', 2000);

            // Redirect to quiz detail page
            setTimeout(() => {
                window.location.href = `/quiz/${currentRetakeQuizId}/detail`;
            }, 500);
        }

        // Custom Alert Function
        function showAlert(type, title, message, duration = 5000) {
            const alertTypes = {
                success: {
                    icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>',
                    color: 'success'
                },
                error: {
                    icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/></svg>',
                    color: 'error'
                },
                warning: {
                    icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/></svg>',
                    color: 'warning'
                },
                info: {
                    icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/></svg>',
                    color: 'info'
                }
            };

            const alertType = alertTypes[type] || alertTypes.info;

            const alertDiv = document.createElement('div');
            alertDiv.className = `custom-alert ${alertType.color}`;
            alertDiv.innerHTML = `
                <div class="alert-icon ${alertType.color}">
                    ${alertType.icon}
                </div>
                <div class="alert-content">
                    <div class="alert-title">${title}</div>
                    <div class="alert-message">${message}</div>
                </div>
                <div class="alert-close" onclick="this.parentElement.remove()">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                    </svg>
                </div>
            `;

            document.body.appendChild(alertDiv);

            // Animate in
            setTimeout(() => {
                alertDiv.classList.add('show');
            }, 10);

            // Auto remove after duration
            setTimeout(() => {
                if (alertDiv.parentElement) {
                    alertDiv.classList.remove('show');
                    setTimeout(() => {
                        if (alertDiv.parentElement) {
                            alertDiv.remove();
                        }
                    }, 400);
                }
            }, duration);
        }

        // Close modal on backdrop click
        document.getElementById('retakeModalBackdrop').addEventListener('click', function(e) {
            if (e.target === this) {
                hideRetakeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideRetakeModal();
            }
        });
    </script>
@endsection
