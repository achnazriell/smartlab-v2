@extends('layouts.appTeacher')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-gradient-to-r from-purple-500 to-purple-300 rounded-2xl p-8 text-white shadow-lg">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-white">Detail Jawaban Siswa</h1>
                <p class="text-purple-50 mt-2">{{ $quiz->title }}</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('guru.quiz.results', $quiz->id) }}"
                    class="px-6 md:px-8 py-3 md:py-4 bg-white text-purple-600 rounded-xl hover:bg-purple-50 transition-all shadow-md flex items-center space-x-2 font-semibold text-sm md:text-base whitespace-nowrap hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Kembali ke Hasil</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Informasi Siswa dan Skor --}}
    <div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Nama Siswa</p>
                <p class="text-lg font-semibold text-gray-900">{{ $attempt->student->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">NIS</p>
                <p class="text-lg font-semibold text-gray-900">{{ $attempt->student->nis ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">Tanggal Mengerjakan</p>
                <p class="text-lg font-semibold text-gray-900">{{ $attempt->created_at->format('d M Y, H:i') }}</p>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Skor Akhir</p>
                    <p class="text-3xl font-bold {{ $score >= 70 ? 'text-emerald-600' : ($score >= 50 ? 'text-amber-600' : 'text-red-600') }}">
                        {{ number_format($score, 1) }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Benar / Total</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $correctAnswers }} / {{ $totalQuestions }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Jawaban --}}
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-50">
            <h3 class="text-lg md:text-xl font-bold text-gray-900">Rincian Jawaban</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">No</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Soal</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Jawaban Siswa</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Kunci Jawaban</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                        <th class="px-4 md:px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Skor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($answers as $index => $answer)
                        @php
                            $isCorrect = $answer->is_correct ?? false;
                            $correctChoice = $answer->question->choices->first(); // asumsi hanya satu kunci
                        @endphp
                        <tr class="hover:bg-purple-50/50 transition-colors">
                            <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-4 md:px-6 py-4 text-sm text-gray-900 max-w-xs">
                                <div class="line-clamp-2">{{ $answer->question->question_text }}</div>
                            </td>
                            <td class="px-4 md:px-6 py-4 text-sm text-gray-700">
                                {{ $answer->choice->choice_text ?? '-' }}
                            </td>
                            <td class="px-4 md:px-6 py-4 text-sm text-gray-700">
                                {{ $correctChoice->choice_text ?? '-' }}
                            </td>
                            <td class="px-4 md:px-6 py-4">
                                @if ($isCorrect)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Benar
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                        Salah
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 md:px-6 py-4 text-sm font-medium">
                                {{ $answer->score ?? 0 }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
