@extends('layouts.appTeacher')
@section('content')
    <div class="space-y-6">
        <!-- Modern header dengan blue gradient dan design yang lebih clean -->
        <div class="bg-gradient-to-r from-blue-400 to-blue-200 rounded-2xl p-8 text-white shadow-lg">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold  text-white ">Kelola Soal</h1>
                </div>
                <a href="{{ route('guru.exams.create') }}"
                    class="px-6 md:px-8 py-3 md:py-4 bg-white text-blue-600 rounded-xl hover:bg-blue-50 transition-all shadow-md flex items-center space-x-2 font-semibold text-sm md:text-base whitespace-nowrap hover:shadow-lg">
                    <span>Tambah Soal</span>
                </a>
            </div>

            <!-- Search dan filter dengan styling yang lebih modern -->
            <div class="flex flex-row gap-3 mt-6">
                <form action="{{ route('guru.exams.index') }}" method="GET" class="flex-1 relative" id="searchForm">
                    <input type="text" name="search" placeholder="Cari Soal, mapel, atau kelas..."
                        value="{{ request('search') }}"
                        class="w-full px-4 py-2.5 rounded-xl text-gray-800 placeholder-white outline-none ring-2 ring-white text-sm md:text-base transition-all focus:ring-blue-300"
                        onchange="document.getElementById('searchForm').submit();">
                    @if (request('search'))
                        <button type="button" onclick="window.location='{{ route('guru.exams.index') }}'"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-white hover:text-blue-200 transition-colors"
                            title="Hapus pencarian">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    @endif
                </form>
            </div>
        </div>
        <!-- Summary statistics cards sama seperti style lama tapi diatur ulang -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
                class="bg-white rounded-xl shadow-md border border-gray-100 p-6 flex items-center space-x-5 group hover:border-blue-300 transition-all duration-300">
                <div
                    class="p-4 bg-blue-50 text-blue-600 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                </div>
                <div>
                    <p class="text-[11px] text-slate-400 font-extrabold uppercase tracking-[0.2em] mb-1">Total Soal</p>
                    <p class="text-3xl font-semibold text-slate-800 tracking-tight">{{ $total ?? 0 }}</p>
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
                    <p class="text-3xl font-semibold text-slate-800 tracking-tight">{{ $active ?? 0 }}</p>
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
                    <p class="text-3xl font-semibold text-slate-800 tracking-tight">{{ $draft ?? 0 }}</p>
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
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-50">
                <h3 class="text-lg md:text-xl font-bold text-gray-900">Daftar Soal</h3>
                <p class="text-xs md:text-sm text-gray-600 mt-1">Total: <span
                        class="font-semibold text-blue-600">{{ $exams->total() ?? 0 }}</span> Soal</p>
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
                                Judul Soal</th>
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                Jenis / Mapel</th>
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                Kelas / Durasi</th>
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider hidden lg:table-cell">
                                Status</th>
                            <th
                                class="px-4 md:px-6 py-3 text-center text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($exams as $index => $exam)
                            <!-- Update hover color dari teal ke blue -->
                            <tr class="hover:bg-blue-50/50 transition-colors">
                                <td
                                    class="px-4 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm text-gray-900 font-medium">
                                    {{ $index + 1 }}</td>
                                <td class="px-4 md:px-6 py-4">
                                    <div class="text-xs md:text-sm font-semibold text-gray-900">{{ $exam->title }}</div>
                                    <div class="text-xs text-gray-500 mt-1">ID:
                                        EX-{{ str_pad($exam->id, 5, '0', STR_PAD_LEFT) }}</div>
                                </td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                    <!-- Update badge color dari teal ke blue -->
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ strtoupper($exam->type) }}
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">{{ $exam->subject?->name_subject ?? '-' }}
                                    </div>
                                </td>
                                <td class="px-4 md:px-6 py-4 text-xs md:text-sm text-gray-700">
                                    <div class="text-sm font-medium text-gray-900">{{ $exam->class?->name_class ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $exam->duration ?? '0' }} Menit</div>
                                </td>
                                <td class="px-4 md:px-6 py-4 text-xs md:text-sm text-gray-600 hidden lg:table-cell">
                                    @php
                                        $statusClass =
                                            $exam->status === 'Aktif'
                                                ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
                                                : ($exam->status === 'Draft'
                                                    ? 'bg-amber-100 text-amber-800 border-amber-200'
                                                    : 'bg-gray-100 text-gray-700 border-gray-200');
                                        $statusDot =
                                            $exam->status === 'Aktif'
                                                ? 'bg-emerald-500'
                                                : ($exam->status === 'Draft'
                                                    ? 'bg-amber-500'
                                                    : 'bg-gray-400');
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statusClass }} border">
                                        <span class="w-1.5 h-1.5 rounded-full mr-2 {{ $statusDot }}"></span>
                                        {{ $exam->status }}
                                    </span>
                                </td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- Detail button -->
                                        <a href="{{ route('guru.exams.show', $exam->id) }}"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            title="Lihat Detail">
                                            <svg class="w-4 md:w-5 h-4 md:h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd"
                                                    d="M4.998 7.78C6.729 6.345 9.198 5 12 5c2.802 0 5.27 1.345 7.002 2.78a12.713 12.713 0 0 1 2.096 2.183c.253.344.465.682.618.997.14.286.284.658.284 1.04s-.145.754-.284 1.04a6.6 6.6 0 0 1-.618.997 12.712 12.712 0 0 1-2.096 2.183C17.271 17.655 14.802 19 12 19c-2.802 0-5.27-1.345-7.002-2.78a12.712 12.712 0 0 1-2.096-2.183 6.6 6.6 0 0 1-.618-.997C2.144 12.754 2 12.382 2 12s.145-.754.284-1.04c.153-.315.365-.653.618-.997A12.714 12.714 0 0 1 4.998 7.78ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                        <!-- Edit button -->
                                        <a href="{{ route('guru.exams.edit', $exam->id) }}"
                                            class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                                            title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 md:w-5 h-4 md:h-5"
                                                fill="none" viewBox="0 0 24 24" stroke-width="2.6"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </a>
                                        <!-- Delete button -->
                                        <button onclick="openModal('deleteModal_{{ $exam->id }}')"
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
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Belum Ada Soal</h3>
                                    <p class="text-xs md:text-sm text-gray-500">Mulai dengan membuat Soal pembelajaran
                                        pertama</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($exams->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $exams->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>

        <!-- Delete modals dengan blue color scheme -->
        @foreach ($exams as $exam)
            <div id="deleteModal_{{ $exam->id }}"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50 p-4">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm pt-6 pb-6 px-6">
                    <div class="flex justify-between items-center mb-4">
                        <h5 class="text-xl md:text-2xl font-bold text-gray-900">Konfirmasi Penghapusan</h5>
                        <button type="button" class="text-gray-500 hover:text-gray-700"
                            onclick="closeModal('deleteModal_{{ $exam->id }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-sm md:text-base text-gray-600 mb-6">Apakah Anda yakin ingin menghapus Soal <strong
                            class="text-blue-600">{{ $exam->title }}</strong>? Tindakan ini tidak dapat dibatalkan.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button type="button"
                            class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium"
                            onclick="closeModal('deleteModal_{{ $exam->id }}')">Batal</button>
                        <form action="{{ route('guru.exams.destroy', $exam->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Detail modal yang lebih elegant dengan blue design -->
        @foreach ($exams as $exam)
            <div id="examDetailModal_{{ $exam->id }}"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50 p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
                    <!-- Modal header dengan blue gradient -->
                    <div
                        class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 md:px-8 py-5 flex justify-between items-center">
                        <h5 class="text-xl md:text-2xl font-bold text-white">Detail Soal</h5>
                        <button type="button" class="text-white hover:text-blue-100 transition-colors"
                            onclick="closeModal('examDetailModal_{{ $exam->id }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="w-6 h-6 md:w-7 md:h-7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal content -->
                    <div class="overflow-y-auto flex-1 px-6 md:px-8 py-6 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Judul Soal</h6>
                                <p class="text-sm md:text-base text-gray-900 mt-2 font-medium">{{ $exam->title }}</p>
                            </div>
                            <div>
                                <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Jenis Soal</h6>
                                <p class="text-sm md:text-base text-gray-900 mt-2 font-medium">
                                    {{ strtoupper($exam->type) }}</p>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Mapel</h6>
                                    <p class="text-sm md:text-base text-gray-900 mt-2 font-medium">
                                        {{ $exam->subject?->name_subject ?? '-' }}</p>
                                </div>
                                <div>
                                    <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Kelas</h6>
                                    <p class="text-sm md:text-base text-gray-900 mt-2 font-medium">
                                        {{ $exam->class?->name_class ?? '-' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Durasi</h6>
                                    <p class="text-sm md:text-base text-gray-900 mt-2 font-medium">{{ $exam->duration }}
                                        Menit</p>
                                </div>
                                <div>
                                    <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Status</h6>
                                    @php
                                        $statusClass =
                                            $exam->status === 'Aktif'
                                                ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
                                                : ($exam->status === 'Draft'
                                                    ? 'bg-amber-100 text-amber-800 border-amber-200'
                                                    : 'bg-gray-100 text-gray-700 border-gray-200');
                                        $statusDot =
                                            $exam->status === 'Aktif'
                                                ? 'bg-emerald-500'
                                                : ($exam->status === 'Draft'
                                                    ? 'bg-amber-500'
                                                    : 'bg-gray-400');
                                    @endphp
                                    <div class="mt-2">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }} border">
                                            <span class="w-1.5 h-1.5 rounded-full mr-2 {{ $statusDot }}"></span>
                                            {{ $exam->status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-5">
                            <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">Deskripsi</h6>
                            <p class="text-sm md:text-base text-gray-700 leading-relaxed">
                                {{ $exam->description ?? 'Tidak ada deskripsi' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

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

        // Close modals when clicking backdrop
        document.addEventListener('DOMContentLoaded', function() {
            const modals = document.querySelectorAll('[id$="Modal_"], [id^="delete"], [id^="examDetail"]');
            modals.forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.classList.add('hidden');
                        this.style.display = 'none';
                        document.body.style.overflow = 'auto';
                    }
                });
            });
        });

        // Add smooth scroll behavior
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
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
