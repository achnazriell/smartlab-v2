@extends('layouts.appTeacher')

@section('content')
    <div class="space-y-6">
        <!-- Modern header dengan blue gradient -->
        <div class="bg-gradient-to-r from-blue-400 to-blue-200 rounded-2xl p-8 text-white shadow-lg">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-white">Kelola Materi</h1>
                </div>
                <a href="{{ route('materis.create') }}"
                    class="px-6 md:px-8 py-3 md:py-4 bg-white text-blue-600 rounded-xl hover:bg-blue-50 transition-all shadow-md flex items-center space-x-2 font-semibold text-sm md:text-base whitespace-nowrap hover:shadow-lg">
                    <span>Tambah Materi</span>
                </a>
            </div>

            <!-- Search dan sort dengan styling modern -->
            <div class="flex flex-row  gap-3 mt-6">
                <form action="{{ route('materis.index') }}" method="GET" class="flex-1">
                    <input type="text" name="search" placeholder="Cari materi atau mapel..."
                        value="{{ request('search') }}"
                        class="w-full px-4 py-2.5 rounded-xl text-gray-800 placeholder-white outline-none ring-2 ring-white text-sm md:text-base">
                </form>

                <form action="{{ route('materis.index') }}" method="GET" class="flex items-center">
                    @php $nextOrder = request('order', 'desc') === 'desc' ? 'asc' : 'desc'; @endphp
                    <input type="hidden" name="order" value="{{ $nextOrder }}">
                    <button type="submit"
                        class="px-4 py-2.5 bg-white text-blue-600 backdrop-blur-sm border border-white/30 rounded-xl hover:bg-white/30 transition-all  flex items-center justify-center text-sm md:text-base">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 576 512">
                            @if (request('order', 'desc') === 'desc')
                                <path
                                    d="M151.6 42.4C145.5 35.8 137 32 128 32s-17.5 3.8-23.6 10.4l-88 96c-11.9 13-11.1 33.3 2 45.2s33.3 11.1 45.2-2L96 146.3 96 448c0 17.7 14.3 32 32 32s32-14.3 32-32l0-301.7 32.4 35.4c11.9 13 32.2 13.9 45.2 2s13.9-32.2 2-45.2l-88-96zM320 480l32 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-32 0c-17.7 0-32 14.3-32 32s14.3 32 32 32zm0-128l96 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0c-17.7 0-32 14.3-32 32s14.3 32 32 32zm0-128l160 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-160 0c-17.7 0-32 14.3-32 32s14.3 32 32 32zm0-128l224 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L320 32c-17.7 0-32 14.3-32 32s14.3 32 32 32z" />
                            @else
                                <path
                                    d="M151.6 469.6C145.5 476.2 137 480 128 480s-17.5-3.8-23.6-10.4l-88-96c-11.9-13-11.1-33.3 2-45.2s33.3-11.1 45.2 2L96 365.7V64c0-17.7 14.3-32 32-32s32 14.3 32 32V365.7l32.4-35.4c11.9-13 32.2-13.9 45.2-2s13.9 32.2 2-45.2l-88 96zM320 480c-17.7 0-32-14.3-32-32s14.3-32 32-32h32c17.7 0 32 14.3 32 32s-14.3 32-32 32H320zm0-128c-17.7 0-32-14.3-32-32s14.3-32 32-32h96c17.7 0 32 14.3 32 32s-14.3 32-32 32H320zm0-128c-17.7 0-32-14.3-32-32s14.3-32 32-32H480c17.7 0 32 14.3 32 32s-14.3 32-32 32H320zm0-128c-17.7 0-32-14.3-32-32s14.3-32 32-32H544c17.7 0 32 14.3 32 32s-14.3 32-32 32H320z" />
                            @endif
                        </svg>
                    </button>
                </form>
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
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
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

        <!-- Table dengan white card dan modern styling -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-50">
                <h3 class="text-lg md:text-xl font-bold text-gray-900">Daftar Materi</h3>
                <p class="text-xs md:text-sm text-gray-600 mt-1">Total: <span
                        class="font-semibold text-blue-600">{{ $materis->total() }}</span> materi</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                No</th>
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                Materi</th>
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider hidden lg:table-cell">
                                Mapel</th>
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider hidden md:table-cell">
                                Kelas</th>
                            <th
                                class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider hidden md:table-cell">
                                Tanggal</th>
                            <th
                                class="px-4 md:px-6 py-3 text-center text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($materis as $index => $materi)
                            @php $offset = ($materis->currentPage() - 1) * $materis->perPage(); @endphp
                            <!-- Update hover color dari teal ke blue -->
                            <tr class="hover:bg-blue-50/50 transition-colors">
                                <td
                                    class="px-4 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm text-gray-900 font-medium">
                                    {{ $offset + $index + 1 }}</td>
                                <td class="px-4 md:px-6 py-4">
                                    <div>
                                        <span
                                            class="text-xs md:text-sm font-semibold text-gray-900">{{ $materi->title_materi }}</span>
                                        @if ($materi->description)
                                            <div class="text-xs text-gray-500 truncate max-w-xs mt-1">
                                                {{ Str::limit($materi->description, 50) }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td
                                    class="px-4 md:px-6 py-4 text-xs md:text-sm text-gray-700 font-medium hidden lg:table-cell">
                                    {{ $materi->subject->name_subject ?? '-' }}
                                </td>
                                <td class="px-4 md:px-6 py-4 hidden md:table-cell">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($materi->classes as $class)
                                            <!-- Update badge color dari teal ke blue -->
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                {{ Str::limit($class->name_class, 12) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td
                                    class="px-4 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm text-gray-600 font-medium hidden md:table-cell">
                                    {{ \Carbon\Carbon::parse($materi->created_at)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-4 md:px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <!-- Update icon colors dari teal ke blue -->
                                        <button onclick="openModal('showAssessmentModal_{{ $materi->id }}')"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            title="Lihat Detail">
                                            <svg class="w-4 md:w-5 h-4 md:h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd"
                                                    d="M4.998 7.78C6.729 6.345 9.198 5 12 5c2.802 0 5.27 1.345 7.002 2.78a12.713 12.713 0 0 1 2.096 2.183c.253.344.465.682.618.997.14.286.284.658.284 1.04s-.145.754-.284 1.04a6.6 6.6 0 0 1-.618.997 12.712 12.712 0 0 1-2.096 2.183C17.271 17.655 14.802 19 12 19c-2.802 0-5.27-1.345-7.002-2.78a12.712 12.712 0 0 1-2.096-2.183 6.6 6.6 0 0 1-.618-.997C2.144 12.754 2 12.382 2 12s.145-.754.284-1.04c.153-.315.365-.653.618-.997A12.714 12.714 0 0 1 4.998 7.78ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        <a href="{{ route('materis.edit', $materi->id) }}"
                                            class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors"
                                            title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 md:w-5 h-4 md:h-5"
                                                fill="none" viewBox="0 0 24 24" stroke-width="2.6" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </a>
                                        <button onclick="openModal('deleteModal_{{ $materi->id }}')"
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
                                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Belum Ada Materi</h3>
                                    <p class="text-xs md:text-sm text-gray-500">Mulai dengan membuat materi pembelajaran
                                        pertama</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($materis->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $materis->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>

        <!-- Delete modals dengan blue theme -->
        @foreach ($materis as $materi)
            <div id="deleteModal_{{ $materi->id }}"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50 p-4">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm pt-6 pb-6 px-6">
                    <div class="flex justify-between items-center mb-4">
                        <h5 class="text-xl md:text-2xl font-bold text-gray-900">Konfirmasi Penghapusan</h5>
                        <button type="button" class="text-gray-500 hover:text-gray-700"
                            onclick="closeModal('deleteModal_{{ $materi->id }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="w-6 h-6 md:w-7 md:h-7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-sm md:text-base text-gray-600 mb-6">Apakah Anda yakin ingin menghapus materi <strong
                            class="text-blue-600">{{ $materi->title_materi }}</strong>? Tindakan ini tidak dapat
                        dibatalkan.</p>
                    <div class="flex gap-3 justify-end">
                        <button type="button"
                            class="px-5 py-2.5 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium"
                            onclick="closeModal('deleteModal_{{ $materi->id }}')">Batal</button>
                        <form action="{{ route('materis.destroy', $materi->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Detail modal dengan blue gradient header dan elegant design -->
        @foreach ($materis as $materi)
            <div id="showAssessmentModal_{{ $materi->id }}"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50 p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
                    <!-- Modal header dengan blue gradient -->
                    <div
                        class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 md:px-8 py-5 flex justify-between items-center">
                        <h5 class="text-xl md:text-2xl font-bold text-white">Detail Materi</h5>
                        <button type="button" class="text-white hover:text-blue-100 transition-colors"
                            onclick="closeModal('showAssessmentModal_{{ $materi->id }}')">
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
                                <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Judul Materi</h6>
                                <p class="text-sm md:text-base text-gray-900 mt-2 font-medium">{{ $materi->title_materi }}
                                </p>
                            </div>
                            <div>
                                <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Mata Pelajaran</h6>
                                <p class="text-sm md:text-base text-gray-900 mt-2 font-medium">
                                    {{ $materi->subject->name_subject ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-5">
                            <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">Kelas</h6>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($materi->classes as $class)
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        {{ $class->name_class }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-5">
                            <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">Deskripsi</h6>
                            <p class="text-sm md:text-base text-gray-700 leading-relaxed">
                                {{ $materi->description ?? 'Tidak ada deskripsi' }}</p>
                        </div>

                        <div class="border-t border-gray-200 pt-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Tanggal Dibuat</h6>
                                    <p class="text-sm md:text-base text-gray-900 mt-2 font-medium">
                                        {{ \Carbon\Carbon::parse($materi->created_at)->translatedFormat('d M Y') }}</p>
                                </div>
                                <div>
                                    <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Jam Dibuat</h6>
                                    <p class="text-sm md:text-base text-gray-900 mt-2 font-medium">
                                        {{ \Carbon\Carbon::parse($materi->created_at)->translatedFormat('H:i') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-5">
                            <h6 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-3">File Materi</h6>
                            @php
                                $filePath = $materi->file_materi ?? '';
                                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                $fileUrl = $filePath ? Storage::url($filePath) : null;
                            @endphp

                            @if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp']))
                                <img src="{{ $fileUrl }}" alt="File Materi"
                                    class="w-full h-auto border border-gray-300 rounded-lg max-w-sm">
                            @elseif ($extension === 'pdf')
                                <a href="{{ $fileUrl }}" target="_blank"
                                    class="inline-block px-5 py-2.5 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                    Buka File PDF
                                </a>
                            @else
                                <p class="text-xs md:text-sm text-gray-500 italic">File tidak tersedia atau format tidak
                                    didukung</p>
                            @endif
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
    </script>
@endsection
