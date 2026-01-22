@extends('layouts.appTeacher')

@section('content')
    <div class="space-y-6">
        <!-- Back button yang lebih jelas dan prominent -->
        <div class="flex items-center gap-3 mb-4">
            <a href="{{ route('tasks.index') }}"
                class="flex items-center gap-2 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 hover:text-gray-900 rounded-lg transition-all duration-200 font-medium text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>

        <!-- Modern header dengan blue gradient seperti Task index -->
        <div class="bg-gradient-to-r from-blue-400 to-blue-200 rounded-2xl p-8 text-white shadow-lg">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-white">Penilaian Tugas</h1>
                </div>
            </div>

            <!-- Task info dengan styling modern -->
            <div
                class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white/10 backdrop-blur-sm rounded-xl p-4">
                <div>
                    <p class="text-blue-50 text-sm">Tugas:</p>
                    <p class="text-white font-semibold text-sm md:text-base">{{ $task->title_task }}</p>
                </div>
                <div class="text-white font-semibold text-sm md:text-base">
                    <span class="text-emerald-400">{{ $countCollection }}</span>
                    <span class="text-white"> / {{ $countSiswa }} Siswa mengumpulkan</span>
                </div>
            </div>
        </div>

        <!-- Success message -->
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

        <!-- Error messages -->
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-lg">
                <div class="flex items-center space-x-2 mb-2">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 001.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="font-semibold text-sm md:text-base">Kesalahan Validasi:</span>
                </div>
                <ul class="list-disc ml-7 space-y-1 text-xs md:text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Table card dengan styling modern seperti Task index -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-50">
                <h3 class="text-lg md:text-xl font-bold text-gray-900">Daftar Penilaian</h3>
                <p class="text-xs md:text-sm text-gray-600 mt-1">Total: <span
                        class="font-semibold text-blue-600">{{ $collections->total() ?? 0 }}</span> siswa</p>
            </div>

            <div class="overflow-x-auto">
                <form action="{{ route('assessments.store', ['task' => $task->id]) }}" method="POST">
                    @csrf
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th
                                    class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                    No</th>
                                <th
                                    class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                    Nama Siswa</th>
                                <th
                                    class="px-4 md:px-6 py-3 text-left text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                    Kelas</th>
                                <th
                                    class="px-4 md:px-6 py-3 text-center text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                    Nilai</th>
                                <th
                                    class="px-4 md:px-6 py-3 text-center text-xs md:text-sm font-semibold text-gray-700 uppercase tracking-wider">
                                    Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($collections as $index => $collection)
                                @php
                                    $offset = ($collections->currentPage() - 1) * $collections->perPage();
                                    $assessment = $collection->assessment;
                                @endphp
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td
                                        class="px-4 md:px-6 py-4 whitespace-nowrap text-xs md:text-sm text-gray-900 font-medium">
                                        {{ $offset + $index + 1 }}</td>
                                    <td class="px-4 md:px-6 py-4">
                                        <span
                                            class="text-xs md:text-sm font-semibold text-gray-900">{{ $collection->user->name }}</span>
                                    </td>
                                    <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                                        @foreach ($collection->user->classes as $class)
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $class->name_class }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="px-4 md:px-6 py-4 text-center">
                                        <input type="number"
                                            name="mark_task[{{ $collection->user_id }}][{{ $collection->id }}]"
                                            value="{{ $collection->assessment->mark_task ?? '' }}"
                                            class="w-20 mx-auto p-2 text-center border rounded-lg text-xs md:text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            style="{{ $collection->assessment
                                                ? 'background-color: rgba(34, 197, 94, 0.1); color: #15803d; border-color: #22c55e;'
                                                : 'background-color: #FFFFFF; border-color: #e5e7eb;' }}"
                                            min="0" max="100">
                                    </td>
                                    <td class="px-4 md:px-6 py-4 text-center">
                                        <button type="button"
                                            class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                            onclick="openModal('showCollectionModal_{{ $collection->id }}')"
                                            title="Lihat Detail">
                                            <svg class="w-4 md:w-5 h-4 md:h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd"
                                                    d="M4.998 7.78C6.729 6.345 9.198 5 12 5c2.802 0 5.27 1.345 7.002 2.78a12.713 12.713 0 0 1 2.096 2.183c.253.344.465.682.618.997.14.286.284.658.284 1.04s-.145.754-.284 1.04a6.6 6.6 0 0 1-.618.997 12.712 12.712 0 0 1-2.096 2.183C17.271 17.655 14.802 19 12 19c-2.802 0-5.27-1.345-7.002-2.78a12.712 12.712 0 0 1-2.096-2.183 6.6 6.6 0 0 1-.618-.997C2.144 12.754 2 12.382 2 12s.145-.754.284-1.04c.153-.315.365-.653.618-.997A12.714 12.714 0 0 1 4.998 7.78ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 md:px-6 py-12 text-center">
                                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Belum Ada Pengumpulan</h3>
                                        <p class="text-xs md:text-sm text-gray-500">Tidak ada siswa yang mengumpulkan tugas
                                            ini</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Submit button -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end">
                        <button type="submit"
                            class="flex items-center bg-blue-600 text-white px-6 md:px-8 py-2.5 md:py-3 rounded-xl shadow-lg hover:bg-blue-700 transition-all duration-300 font-semibold text-sm md:text-base">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M13.5 8H4m4 6h8m0 0-2-2m2 2-2 2M4 6v13a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1h-5.032a1 1 0 0 1-.768-.36l-1.9-2.28a1 1 0 0 0-.768-.36H5a1 1 0 0 0-1 1Z" />
                            </svg>
                            Simpan Nilai
                        </button>
                    </div>
                </form>
            </div>

            @if ($collections->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $collections->links('vendor.pagination.tailwind') }}
                </div>
            @endif
        </div>

        <!-- Detail modals -->
        @foreach ($collections as $collection)
            <div id="showCollectionModal_{{ $collection->id }}"
                class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50 p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col">
                    <!-- Modal header dengan blue gradient -->
                    <div
                        class="bg-gradient-to-r from-blue-600 to-blue-500 px-6 md:px-8 py-5 flex justify-between items-center">
                        <h5 class="text-xl md:text-2xl font-bold text-white">Bukti Pengumpulan</h5>
                        <button type="button" class="text-white hover:text-blue-100 transition-colors"
                            onclick="closeModal('showCollectionModal_{{ $collection->id }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" class="w-6 h-6 md:w-7 md:h-7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="overflow-y-auto flex-1 px-6 md:px-8 py-6 space-y-5">
                        <div>
                            <h6 class="text-lg font-semibold text-gray-700 mb-2">Nama Siswa:</h6>
                            <p class="text-gray-600 text-sm md:text-base">{{ $collection->user->name }}</p>
                        </div>
                        <div>
                            <h6 class="text-lg font-semibold text-gray-700 mb-3">Bukti :</h6>
                            @php $file = pathinfo($collection->file_collection ?? '', PATHINFO_EXTENSION); @endphp
                            @if (in_array($file, ['jpg', 'png', 'jpeg']))
                                <img src="{{ asset('storage/' . $collection->file_collection) }}" alt="File Image"
                                    class="w-full h-auto border-2 border-gray-200 rounded-lg">
                            @elseif($file === 'pdf')
                                <embed src="{{ asset('storage/' . $collection->file_collection) }}"
                                    type="application/pdf" class="w-full h-96 border-2 border-gray-200 rounded-lg">
                            @else
                                <p class="text-gray-500 text-sm">File tidak tersedia</p>
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
