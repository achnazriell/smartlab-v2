@extends('layouts.appTeacher')
@section('content')
    <div class="space-y-6">
        <!-- Modern header dengan purple gradient dan design yang lebih fun -->
        <div class="bg-gradient-to-r from-purple-500 to-purple-300 rounded-2xl p-8 text-white shadow-lg">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-white">Kelola Quiz Interaktif</h1>
                    <p class="text-purple-50 mt-2">Buat dan kelola quiz seru untuk siswa</p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('guru.quiz.create') }}"
                        class="px-6 md:px-8 py-3 md:py-4 bg-white text-purple-600 rounded-xl hover:bg-purple-50 transition-all shadow-md flex items-center space-x-2 font-semibold text-sm md:text-base whitespace-nowrap hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Buat Quiz Baru</span>
                    </a>
                </div>
            </div>

            <!-- Search dan filter dengan styling yang lebih modern -->
            <div class="flex flex-col sm:flex-row gap-3 mt-6">
                <form action="{{ route('guru.quiz.index') }}" method="GET" class="flex-1 relative" id="searchForm">
                    <input type="text" name="search" placeholder="Cari quiz, mapel, atau kelas..."
                        value="{{ request('search') }}"
                        class="w-full px-4 py-2.5 rounded-xl text-gray-800 placeholder-purple-300 outline-none ring-2 ring-white text-sm md:text-base transition-all focus:ring-purple-300"
                        onchange="document.getElementById('searchForm').submit();">
                    @if (request('search'))
                        <button type="button" onclick="window.location='{{ route('guru.quiz.index') }}'"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-white hover:text-purple-200 transition-colors"
                            title="Hapus pencarian">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    @endif
                </form>

                <!-- Filter Form -->
                <form action="{{ route('guru.quiz.index') }}" method="GET" id="filterForm" class="flex gap-2">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif

                    <select name="status" onchange="document.getElementById('filterForm').submit()"
                        class="px-4 py-2.5 rounded-xl bg-white text-gray-800 border border-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm shadow-sm hover:bg-purple-50 transition-all duration-200">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>

                    @if($classes->count() > 0)
                    <select name="class_id" onchange="document.getElementById('filterForm').submit()"
                        class="px-4 py-2.5 rounded-xl bg-white text-gray-800 border border-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm shadow-sm hover:bg-purple-50 transition-all duration-200">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name_class }}
                            </option>
                        @endforeach
                    </select>
                    @endif

                    @if($subjects->count() > 0)
                    <select name="subject_id" onchange="document.getElementById('filterForm').submit()"
                        class="px-4 py-2.5 rounded-xl bg-white text-gray-800 border border-purple-300 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm shadow-sm hover:bg-purple-50 transition-all duration-200">
                        <option value="">Semua Mapel</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name_subject }}
                            </option>
                        @endforeach
                    </select>
                    @endif

                    @if (request('status') || request('class_id') || request('subject_id') || request('search'))
                        <button type="button" onclick="resetFilters()"
                            class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl border border-amber-600 transition-colors text-sm shadow-sm flex items-center gap-2 hover:shadow-md transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Reset
                        </button>
                    @endif
                </form>
            </div>
        </div>

        <!-- Summary statistics cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
                class="bg-white rounded-xl shadow-md border border-gray-100 p-6 flex items-center space-x-5 group hover:border-purple-300 transition-all duration-300">
                <div
                    class="p-4 bg-purple-50 text-purple-600 rounded-lg group-hover:bg-purple-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] text-slate-400 font-extrabold uppercase tracking-[0.2em] mb-1">Total Quiz</p>
                    <p class="text-3xl font-semibold text-slate-800 tracking-tight">{{ $quizzes->total() ?? 0 }}</p>
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
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-[0.2em] mb-1">Aktif</p>
                    <p class="text-3xl font-semibold text-slate-800 tracking-tight">
                        {{ $quizzes->where('status', 'active')->count() }}</p>
                </div>
            </div>
            <div
                class="bg-white rounded-xl shadow-md border border-gray-100 p-6 flex items-center space-x-5 group hover:border-amber-300 transition-all duration-300">
                <div
                    class="p-4 bg-amber-50 text-amber-600 rounded-lg group-hover:bg-amber-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-[0.2em] mb-1">Draft</p>
                    <p class="text-3xl font-semibold text-slate-800 tracking-tight">
                        {{ $quizzes->where('status', 'draft')->count() }}</p>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div
                class="bg-green-50 border-l-4 border-green-500 text-green-800 px-4 py-3 rounded-lg flex items-center space-x-3 text-sm md:text-base shadow-sm">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-lg">
                <div class="flex items-center space-x-2 mb-2">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 001.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="font-semibold text-sm md:text-base">Kesalahan:</span>
                </div>
                <ul class="list-disc ml-7 space-y-1 text-xs md:text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Table yang lebih modern dengan white card design -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-50">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg md:text-xl font-bold text-gray-900">Daftar Quiz Interaktif</h3>
                        <p class="text-xs md:text-sm text-gray-600 mt-1">Total: <span
                                class="font-semibold text-purple-600">{{ $quizzes->total() ?? 0 }}</span> Quiz</p>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        @if(request('status'))
                            <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                                Status: {{ request('status') == 'draft' ? 'Draft' : (request('status') == 'active' ? 'Aktif' : 'Nonaktif') }}
                            </span>
                        @endif
                        @if(request('class_id') && $classes->where('id', request('class_id'))->first())
                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                                Kelas: {{ $classes->where('id', request('class_id'))->first()->name_class }}
                            </span>
                        @endif
                        @if(request('subject_id') && $subjects->where('id', request('subject_id'))->first())
                            <span class="px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-medium">
                                Mapel: {{ $subjects->where('id', request('subject_id'))->first()->name_subject }}
                            </span>
                        @endif
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
                                Judul Quiz</th>
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                Mapel / Kelas</th>
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                Mode</th>
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-4 md:px-6 py-3 text-center text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($quizzes as $index => $quiz)
                            <tr class="hover:bg-purple-50/50 transition-colors">
                                <td
                                    class="px-4 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm text-gray-900 font-medium">
                                    {{ $index + 1 + (($quizzes->currentPage() - 1) * $quizzes->perPage()) }}</td>
                                <td class="px-4 md:px-6 py-4">
                                    <div class="text-xs md:text-sm font-semibold text-gray-900">{{ $quiz->title }}</div>
                                    <div class="text-xs text-gray-500 mt-1 flex items-center gap-2">
                                        <span class="flex items-center">
                                            <svg class="w-3 h-3 text-purple-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            {{ $quiz->time_per_question }} detik/soal
                                        </span>
                                        <span>•</span>
                                        <span>{{ $quiz->questions_count ?? 0 }} soal</span>
                                    </div>
                                </td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $quiz->subject?->name_subject ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $quiz->class?->name_class ?? '-' }}</div>
                                </td>
                                <td class="px-4 md:px-6 py-4 text-xs md:text-sm text-gray-700">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xs font-medium px-2 py-1 rounded-full
                                            {{ $quiz->quiz_mode == 'live' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $quiz->quiz_mode == 'live' ? '🎮 Live' : '📚 Homework' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 md:px-6 py-4 text-xs md:text-sm">
                                    @php
                                        $statusClass =
                                            $quiz->status === 'active'
                                                ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
                                                : ($quiz->status === 'draft'
                                                    ? 'bg-amber-100 text-amber-800 border-amber-200'
                                                    : 'bg-slate-100 text-slate-800 border-slate-200');
                                        $statusDot =
                                            $quiz->status === 'active'
                                                ? 'bg-emerald-500'
                                                : ($quiz->status === 'draft'
                                                    ? 'bg-amber-500'
                                                    : 'bg-slate-500');
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusClass }} border">
                                        <span class="w-1.5 h-1.5 rounded-full mr-2 {{ $statusDot }}"></span>
                                        {{ ucfirst($quiz->status) }}
                                    </span>
                                </td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- Detail/Preview button -->
                                        <a href="{{ route('guru.quiz.preview', $quiz->id) }}"
                                            class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                                            title="Preview">
                                            <svg class="w-4 md:w-5 h-4 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </a>
                                        <!-- Soal button -->
                                        <a href="{{ route('guru.quiz.questions', $quiz->id) }}"
                                            class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                                            title="Kelola Soal">
                                            <svg class="w-4 md:w-5 h-4 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                        </a>
                                        <!-- Edit button -->
                                        <a href="{{ route('guru.quiz.edit', $quiz->id) }}"
                                            class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                                            title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 md:w-5 h-4 md:h-5"
                                                fill="none" viewBox="0 0 24 24" stroke-width="2.6"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </a>
                                        <!-- Results button -->
                                        <a href="{{ route('guru.quiz.results', $quiz->id) }}"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            title="Lihat Hasil">
                                            <svg class="w-4 md:w-5 h-4 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                        </a>
                                        <!-- Delete button -->
                                        <button onclick="openDeleteModal('{{ $quiz->id }}', '{{ $quiz->title }}')"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Hapus">
                                            <svg class="w-4 md:w-5 h-4 md:h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd"
                                                    d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 0 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 md:px-6 py-12 text-center">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Belum Ada Quiz</h3>
                                    <p class="text-xs md:text-sm text-gray-500">Mulai dengan membuat quiz interaktif pertama Anda</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($quizzes->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $quizzes->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm pt-6 pb-6 px-6">
            <div class="flex justify-between items-center mb-4">
                <h5 class="text-xl md:text-2xl font-bold text-gray-900">Hapus Quiz</h5>
                <button type="button" class="text-gray-500 hover:text-gray-700"
                    onclick="closeDeleteModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p class="text-sm md:text-base text-gray-600 mb-6">Apakah Anda yakin ingin menghapus quiz <strong
                    class="text-purple-600" id="deleteExamTitle"></strong>? Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="flex gap-3 justify-end">
                <button type="button"
                    class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium"
                    onclick="closeDeleteModal()">Batal</button>
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(quizId, quizTitle) {
            document.getElementById('deleteExamTitle').textContent = quizTitle;
            document.getElementById('deleteForm').action = `/guru/quiz/${quizId}`;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function resetFilters() {
            // Hanya reset parameter filter, pertahankan search jika ada
            const url = new URL(window.location.href);
            const search = url.searchParams.get('search');

            if (search) {
                window.location.href = '{{ route("guru.quiz.index") }}?search=' + search;
            } else {
                window.location.href = '{{ route("guru.quiz.index") }}';
            }
        }

        // Close modal when clicking backdrop
        document.getElementById('deleteModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        // Auto-close success alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successAlerts = document.querySelectorAll('[class*="bg-green-50"]');
            successAlerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.3s ease-out';
                    setTimeout(() => alert.remove(), 300);
                }, 5000);
            });
        });
    </script>

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
