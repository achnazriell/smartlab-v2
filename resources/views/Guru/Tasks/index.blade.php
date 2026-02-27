@extends('layouts.appTeacher')
@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-400 to-blue-200 rounded-2xl p-8 text-white shadow-lg">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-white">Kelola Tugas</h1>
                </div>
                <a href="{{ route('tasks.create') }}"
                    class="px-6 md:px-8 py-3 md:py-4 bg-white text-blue-600 rounded-xl hover:bg-blue-50 transition-all shadow-md flex items-center space-x-2 font-semibold text-sm md:text-base whitespace-nowrap hover:shadow-lg">
                    <span>Tambah Tugas</span>
                </a>
            </div>

            <div class="flex flex-row gap-3 mt-6">
                <form action="{{ route('tasks.index') }}" method="GET" class="flex-1">
                    <input type="text" name="search" placeholder="Cari tugas atau materi..."
                        value="{{ request('search') }}"
                        class="w-full px-4 py-2.5 rounded-xl text-gray-800 placeholder-white outline-none ring-2 ring-white text-sm md:text-base">
                </form>

                <div class="relative" x-data="{ dropdownOpen: false }">
                    <button @click="dropdownOpen = !dropdownOpen"
                        class="px-4 py-2.5 bg-white text-blue-600 backdrop-blur-sm border border-white/30 rounded-xl hover:bg-white/30 transition-all flex items-center justify-center space-x-2 text-sm md:text-base">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>Filter</span>
                    </button>
                    <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition
                        class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl z-10 overflow-hidden">
                        @foreach ($kelas as $class)
                            <form action="{{ route('tasks.index') }}" method="GET" class="block">
                                <input type="hidden" name="class_id" value="{{ $class->id }}">
                                <button type="submit"
                                    class="block w-full text-left px-4 py-3 text-sm md:text-base text-gray-700 hover:bg-blue-50 transition-colors border-b border-gray-100 last:border-0">
                                    {{ $class->name_class }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-800 px-4 py-3 rounded-lg flex items-center space-x-3 text-sm md:text-base shadow-sm">
                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-lg">
                <div class="flex items-center space-x-2 mb-2">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
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

        <!-- Table -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-50">
                <h3 class="text-lg md:text-xl font-bold text-gray-900">Daftar Tugas</h3>
                <p class="text-xs md:text-sm text-gray-600 mt-1">Total: <span class="font-semibold text-blue-600">{{ $tasks->total() }}</span> tugas</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">No</th>
                            <th class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">Nama Tugas</th>
                            <th class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">Mapel</th>
                            <th class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">Materi</th>
                            <th class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider hidden lg:table-cell">Kelas</th>
                            <th class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider hidden md:table-cell">Deadline</th>
                            <th class="px-4 md:px-6 py-3 text-center text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($tasks as $index => $task)
                            @php $offset = ($tasks->currentPage() - 1) * $tasks->perPage(); @endphp
                            <tr class="hover:bg-blue-50/50 transition-colors">
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm text-gray-900 font-medium">{{ $offset + $index + 1 }}</td>
                                <td class="px-4 md:px-6 py-4">
                                    <span class="text-xs md:text-sm font-semibold text-gray-900">{{ $task->title_task }}</span>
                                </td>
                                <td class="px-4 md:px-6 py-4 text-xs md:text-sm text-gray-700 font-medium hidden lg:table-cell">
                                    {{-- Prioritas: ambil dari subject langsung di task (selalu ada),
                                         fallback ke subject lewat materi jika perlu --}}
                                    {{ $task->subject->name_subject ?? $task->materi?->subject?->name_subject ?? '-' }}
                                </td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $task->materi ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-500' }}">
                                        {{ Str::limit($task->materi->title_materi ?? '— Tanpa Materi —', 18) }}
                                    </span>
                                </td>
                                <td class="px-4 md:px-6 py-4 text-xs md:text-sm text-gray-600 hidden lg:table-cell">
                                    @foreach ($task->classes as $class)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                            {{ Str::limit($class->name_class, 12) }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm text-gray-600 hidden md:table-cell">
                                    <span class="text-orange-600 font-medium">{{ \Carbon\Carbon::parse($task->date_collection)->translatedFormat('d M Y') }}</span>
                                </td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick="openModal('taskDetailModal_{{ $task->id }}')"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat Detail">
                                            <svg class="w-4 md:w-5 h-4 md:h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd" d="M4.998 7.78C6.729 6.345 9.198 5 12 5c2.802 0 5.27 1.345 7.002 2.78a12.713 12.713 0 0 1 2.096 2.183c.253.344.465.682.618.997.14.286.284.658.284 1.04s-.145.754-.284 1.04a6.6 6.6 0 0 1-.618.997 12.712 12.712 0 0 1-2.096 2.183C17.271 17.655 14.802 19 12 19c-2.802 0-5.27-1.345-7.002-2.78a12.712 12.712 0 0 1-2.096-2.183 6.6 6.6 0 0 1-.618-.997C2.144 12.754 2 12.382 2 12s.145-.754.284-1.04c.153-.315.365-.653.618-.997A12.714 12.714 0 0 1 4.998 7.78ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        <a href="{{ route('collections.byTask', $task->id) }}"
                                            class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg" title="Pengumpulan">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 md:w-5 h-4 md:h-5" viewBox="0 -960 960 960" fill="#4f46e5">
                                                <path d="m480-240 160-160-56-56-64 64v-168h-80v168l-64-64-56 56 160 160ZM200-640v440h560v-440H200Zm0 520q-33 0-56.5-23.5T120-200v-499q0-14 4.5-27t13.5-24l50-61q11-14 27.5-21.5T250-840h460q18 0 34.5 7.5T772-811l50 61q9 11 13.5 24t4.5 27v499q0 33-23.5 56.5T760-120H200Zm16-600h528l-34-40H250l-34 40Zm264 300Z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('assesments', $task->id) }}"
                                            class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Penilaian">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 md:w-5 h-4 md:h-5" viewBox="0 -960 960 960" fill="#22c55e">
                                                <path d="M160-400v-80h280v80H160Zm0-160v-80h440v80H160Zm0-160v-80h440v80H160Zm360 560v-123l221-220q9-9 20-13t22-4q12 0 23 4.5t20 13.5l37 37q8 9 12.5 20t4.5 22q0 11-4 22.5T863-380L643-160H520Zm300-263-37-37 37 37ZM580-220h38l121-122-18-19-19-18-122 121v38Zm141-141-19-18 37 37-18-19Z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('tasks.edit', $task->id) }}"
                                            class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 md:w-5 h-4 md:h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.6" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </a>
                                        <button onclick="openModal('deleteModal_{{ $task->id }}')"
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                            <svg class="w-4 md:w-5 h-4 md:h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 0 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 md:px-6 py-12 text-center">
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Belum Ada Tugas</h3>
                                    <p class="text-xs md:text-sm text-gray-500">Mulai dengan membuat tugas pembelajaran pertama</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($tasks->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $tasks->links() }}
                </div>
            @endif
        </div>

        {{-- ===== DELETE MODALS ===== --}}
        @foreach ($tasks as $task)
            <div id="deleteModal_{{ $task->id }}"
                class="fixed inset-0 items-center justify-center bg-black bg-opacity-50 z-50 p-4"
                style="display:none">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm pt-6 pb-6 px-6">
                    <div class="flex justify-between items-center mb-4">
                        <h5 class="text-xl md:text-2xl font-bold text-gray-900">Konfirmasi Penghapusan</h5>
                        <button type="button" class="text-gray-500 hover:text-gray-700" onclick="closeModal('deleteModal_{{ $task->id }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-sm md:text-base text-gray-600 mb-6">
                        Apakah Anda yakin ingin menghapus tugas
                        <strong class="text-blue-600">{{ $task->title_task }}</strong>?
                        Tindakan ini tidak dapat dibatalkan.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button type="button"
                            class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium"
                            onclick="closeModal('deleteModal_{{ $task->id }}')">Batal</button>
                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- ===== DETAIL MODALS ===== --}}
        @foreach ($tasks as $task)
            <div id="taskDetailModal_{{ $task->id }}"
                class="fixed inset-0 items-center justify-center bg-black bg-opacity-50 z-50 p-4"
                style="display:none">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 md:px-8 py-5 flex justify-between items-center">
                        <h5 class="text-xl md:text-2xl font-bold text-white">Detail Tugas</h5>
                        <button type="button" class="text-white hover:text-blue-100 transition-colors"
                            onclick="closeModal('taskDetailModal_{{ $task->id }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 md:w-7 md:h-7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Content -->
                    <div class="overflow-y-auto flex-1 px-6 md:px-8 py-6 space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Nama Tugas</h6>
                                <p class="text-sm md:text-base text-gray-900 mt-2 font-medium">{{ $task->title_task }}</p>
                            </div>
                            <div>
                                <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Materi</h6>
                                <p class="text-sm md:text-base text-gray-900 mt-2 font-medium">{{ $task->materi->title_materi ?? '— Tanpa Materi —' }}</p>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-5">
                            <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Kelas</h6>
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach ($task->classes as $class)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        {{ $class->name_class }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Batas Pengumpulan</h6>
                                    <p class="text-sm md:text-base text-orange-600 mt-2 font-semibold">
                                        {{ \Carbon\Carbon::parse($task->date_collection)->translatedFormat('d M Y H:i') }}
                                    </p>
                                </div>
                                <div>
                                    <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Tanggal Pembuatan</h6>
                                    <p class="text-sm md:text-base text-gray-900 mt-2 font-medium">
                                        {{ \Carbon\Carbon::parse($task->created_at)->translatedFormat('d M Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-5">
                            <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">Deskripsi</h6>
                            <p class="text-sm md:text-base text-gray-700 leading-relaxed">
                                {{ $task->description_task ?? 'Tidak ada deskripsi' }}
                            </p>
                        </div>

                        {{-- ===== FIX 1: File Tugas — tidak pakai Storage::exists, pakai asset langsung + onerror ===== --}}
                        <div class="border-t border-gray-200 pt-5">
                            <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">File Tugas</h6>
                            @php
                                $filePath = $task->file_task ?? null;
                                $fileExt  = $filePath ? strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) : null;
                                $fileUrl  = $filePath ? asset('storage/' . $filePath) : null;
                            @endphp

                            @if ($filePath && in_array($fileExt, ['jpg', 'jpeg', 'png']))
                                <div>
                                    <img src="{{ $fileUrl }}"
                                        alt="File Tugas"
                                        class="w-full h-auto rounded-xl border border-gray-200 shadow-sm"
                                        onerror="this.closest('.img-wrapper').innerHTML='<p class=\'text-sm text-red-500 py-2\'>File gambar tidak dapat dimuat.</p>'">
                                </div>
                            @elseif ($filePath && $fileExt === 'pdf')
                                <div class="space-y-3">
                                    <a href="{{ $fileUrl }}" target="_blank"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        📄 Buka File PDF
                                    </a>
                                    <embed src="{{ $fileUrl }}" type="application/pdf"
                                        class="w-full rounded-xl border border-gray-200" style="height:400px">
                                </div>
                            @elseif ($filePath)
                                {{-- File ada tapi ekstensi tidak dikenal --}}
                                <a href="{{ $fileUrl }}" target="_blank"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-600 text-white rounded-xl text-sm font-semibold hover:bg-gray-700 transition">
                                    📎 Download File
                                </a>
                            @else
                                <div class="flex items-center gap-2 text-gray-400 text-sm py-3 px-4 bg-gray-50 rounded-xl border border-gray-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Tidak ada file yang dilampirkan.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 md:px-8 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
                        <button type="button"
                            class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium"
                            onclick="closeModal('taskDetailModal_{{ $task->id }}')">Tutup</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        // ===== MODAL: hanya buka saat klik, semua tersembunyi saat load =====
        function openModal(id) {
            // Pastikan semua modal tertutup dulu
            document.querySelectorAll('.app-modal').forEach(m => {
                m.style.display = 'none';
            });
            const modal = document.getElementById(id);
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Tandai semua modal dan pastikan tersembunyi
            document.querySelectorAll('[id^="deleteModal_"], [id^="taskDetailModal_"]').forEach(modal => {
                modal.classList.add('app-modal');
                modal.style.display = 'none';

                // Klik background untuk tutup
                modal.addEventListener('click', function (e) {
                    if (e.target === this) closeModal(this.id);
                });
            });
        });
    </script>
@endsection
