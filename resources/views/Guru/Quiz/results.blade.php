{{-- resources/views/guru/quiz/results.blade.php --}}
@extends('layouts.appTeacher')

@section('content')
    <div class="space-y-6">
        {{-- Header dengan gradient purple (sama seperti index) --}}
        <div class="bg-gradient-to-r from-purple-500 to-purple-300 rounded-2xl p-8 text-white shadow-lg">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-white">Hasil Quiz</h1>
                    <p class="text-purple-50 mt-2">{{ $quiz->title }}</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('guru.quiz.index') }}"
                        class="px-6 md:px-8 py-3 md:py-4 bg-white text-purple-600 rounded-xl hover:bg-purple-50 transition-all shadow-md flex items-center space-x-2 font-semibold text-sm md:text-base whitespace-nowrap hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>Kembali ke Quiz</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Statistik Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div
                class="bg-white rounded-xl shadow-md border border-gray-100 p-6 flex items-center space-x-5 group hover:border-purple-300 transition-all duration-300">
                <div
                    class="p-4 bg-purple-50 text-purple-600 rounded-lg group-hover:bg-purple-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] text-slate-400 font-extrabold uppercase tracking-[0.2em] mb-1">Total Percobaan</p>
                    <p class="text-3xl font-semibold text-slate-800 tracking-tight">{{ $stats['total_attempts'] }}</p>
                </div>
            </div>
            <div
                class="bg-white rounded-xl shadow-md border border-gray-100 p-6 flex items-center space-x-5 group hover:border-emerald-300 transition-all duration-300">
                <div
                    class="p-4 bg-emerald-50 text-emerald-600 rounded-lg group-hover:bg-emerald-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-[0.2em] mb-1">Rata-rata</p>
                    <p class="text-3xl font-semibold text-slate-800 tracking-tight">
                        {{ number_format($stats['average_score'], 1) }}</p>
                </div>
            </div>
            <div
                class="bg-white rounded-xl shadow-md border border-gray-100 p-6 flex items-center space-x-5 group hover:border-amber-300 transition-all duration-300">
                <div
                    class="p-4 bg-amber-50 text-amber-600 rounded-lg group-hover:bg-amber-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18">
                        </path>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-[0.2em] mb-1">Tertinggi</p>
                    <p class="text-3xl font-semibold text-slate-800 tracking-tight">{{ $stats['highest_score'] }}</p>
                </div>
            </div>
            <div
                class="bg-white rounded-xl shadow-md border border-gray-100 p-6 flex items-center space-x-5 group hover:border-red-300 transition-all duration-300">
                <div
                    class="p-4 bg-red-50 text-red-600 rounded-lg group-hover:bg-red-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-[0.2em] mb-1">Terendah</p>
                    <p class="text-3xl font-semibold text-slate-800 tracking-tight">{{ $stats['lowest_score'] }}</p>
                </div>
            </div>
        </div>

        {{-- Tabel Daftar Percobaan --}}
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-50">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg md:text-xl font-bold text-gray-900">Daftar Percobaan Siswa</h3>
                        <p class="text-xs md:text-sm text-gray-600 mt-1">Total: <span
                                class="font-semibold text-purple-600">{{ $attempts->total() }}</span> percobaan</p>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" data-aos="fade-up">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                No</th>
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                Nama Siswa</th>
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                NIS</th>
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                Skor</th>
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                Tanggal</th>
                            <th
                                class="px-4 md:px-6 py-3 text-center text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($attempts as $index => $attempt)
                            <tr class="hover:bg-purple-50/50 transition-colors">
                                <td
                                    class="px-4 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm text-gray-900 font-medium">
                                    {{ $index + 1 + ($attempts->currentPage() - 1) * $attempts->perPage() }}
                                </td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $attempt->student->name ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm text-gray-600">
                                    {{ $attempt->student->nis ?? '-' }}
                                </td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                    @php
                                        $score = $attempt->final_score ?? 0;
                                        $badgeColor =
                                            $score >= 70
                                                ? 'bg-emerald-100 text-emerald-800'
                                                : ($score >= 50
                                                    ? 'bg-amber-100 text-amber-800'
                                                    : 'bg-red-100 text-red-800');
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $badgeColor }} border">
                                        {{ number_format($score, 1) }}
                                    </span>
                                </td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm text-gray-600">
                                    {{ $attempt->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="{{ route('guru.quiz.attempt.detail', ['quiz' => $quiz->id, 'attempt' => $attempt->id]) }}"
                                            class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                            title="Lihat Detail Jawaban">
                                            <svg class="w-4 md:w-5 h-4 md:h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 md:px-6 py-12 text-center">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Belum Ada Percobaan</h3>
                                    <p class="text-xs md:text-sm text-gray-500">Siswa belum mengerjakan quiz ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($attempts->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $attempts->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .space-y-6>* {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .space-y-6>*:nth-child(1) {
            animation-delay: 0.1s;
        }

        .space-y-6>*:nth-child(2) {
            animation-delay: 0.2s;
        }

        .space-y-6>*:nth-child(3) {
            animation-delay: 0.3s;
        }

        .space-y-6>*:nth-child(4) {
            animation-delay: 0.4s;
        }

        tbody tr {
            animation: fadeIn 0.3s ease-out forwards;
        }

        tbody tr:nth-child(odd) {
            animation-delay: 0.05s;
        }

        tbody tr:nth-child(even) {
            animation-delay: 0.1s;
        }

        /* Smooth transitions */
        button,
        a {
            transition: all 0.2s ease;
        }

        /* Improve modal animations */
        .fixed:not(.hidden) {
            animation: fadeIn 0.2s ease-out;
        }

        .fixed:not(.hidden) .bg-white {
            animation: slideDown 0.3s ease-out;
        }
    </style>
@endsection
