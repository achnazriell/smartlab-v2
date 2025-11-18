@extends('layouts.appTeacher')

@section('content')
    <div class="space-y-6">
        <!-- Modern page header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-blue-600">Kelola Materi</h1>
                <p class="text-blue-500 mt-1">Kelola materi pembelajaran untuk kelas Anda</p>
            </div>
            <div class="flex items-center space-x-3">
                <!-- Search Button -->
                <div class="relative" x-data="{ searchOpen: false }">
                    <button @click="searchOpen = !searchOpen"
                            class="p-3 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors shadow-sm">
                        <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5A6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5S14 7.01 14 9.5S11.99 14 9.5 14"/>
                        </svg>
                    </button>
                    <form x-show="searchOpen" @click.away="searchOpen = false" x-transition
                          action="{{ route('materis.index') }}" method="GET"
                          class="absolute right-0 top-full mt-2 w-64">
                        <input type="text" name="search" placeholder="Cari materi..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </form>
                </div>

                <!-- Sort Button -->
                <form action="{{ route('materis.index') }}" method="GET">
                    @php $nextOrder = request('order', 'desc') === 'desc' ? 'asc' : 'desc'; @endphp
                    <input type="hidden" name="order" value="{{ $nextOrder }}">
                    <button type="submit"
                            class="p-3 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors shadow-sm">
                        <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 576 512">
                            @if (request('order', 'desc') === 'desc')
                                <path d="M151.6 42.4C145.5 35.8 137 32 128 32s-17.5 3.8-23.6 10.4l-88 96c-11.9 13-11.1 33.3 2 45.2s33.3 11.1 45.2-2L96 146.3 96 448c0 17.7 14.3 32 32 32s32-14.3 32-32l0-301.7 32.4 35.4c11.9 13 32.2 13.9 45.2 2s13.9-32.2 2-45.2l-88-96zM320 480l32 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-32 0c-17.7 0-32 14.3-32 32s14.3 32 32 32zm0-128l96 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0c-17.7 0-32 14.3-32 32s14.3 32 32 32zm0-128l160 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-160 0c-17.7 0-32 14.3-32 32s14.3 32 32 32zm0-128l224 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L320 32c-17.7 0-32 14.3-32 32s14.3 32 32 32z"/>
                            @else
                                <path d="M151.6 469.6C145.5 476.2 137 480 128 480s-17.5-3.8-23.6-10.4l-88-96c-11.9-13-11.1-33.3 2-45.2s33.3-11.1 45.2 2L96 365.7V64c0-17.7 14.3-32 32-32s32 14.3 32 32V365.7l32.4-35.4c11.9-13 32.2-13.9 45.2-2s13.9 32.2 2 45.2l-88 96zM320 480c-17.7 0-32-14.3-32-32s14.3-32 32-32h32c17.7 0 32 14.3 32 32s-14.3 32-32 32H320zm0-128c-17.7 0-32-14.3-32-32s14.3-32 32-32h96c17.7 0 32 14.3 32 32s-14.3 32-32 32H320zm0-128c-17.7 0-32-14.3-32-32s14.3-32 32-32H480c17.7 0 32 14.3 32 32s-14.3 32-32 32H320zm0-128c-17.7 0-32-14.3-32-32s14.3-32 32-32H544c17.7 0 32 14.3 32 32s-14.3 32-32 32H320z"/>
                            @endif
                        </svg>
                    </button>
                </form>

                <!-- Add Material Button -->
                <button onclick="openModal('materiModal')"
                        class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors shadow-sm flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 448 512">
                        <path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/>
                    </svg>
                    <span class="font-medium">Tambah Materi</span>
                </button>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex items-center space-x-2">
                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="ml-auto">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl">
                <div class="flex items-center space-x-2 mb-2">
                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">Kesalahan Validasi:</span>
                </div>
                <ul class="list-disc ml-7 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Modern materials table -->
        <div class="card-modern overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Daftar Materi</h3>
                <p class="text-sm text-gray-600">Total: {{ $materis->total() }} materi</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Materi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($materis as $index => $materi)
                            @php $offset = ($materis->currentPage() - 1) * $materis->perPage(); @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $offset + $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($materi->classes as $class)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $class->name_class }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $materi->title_materi }}</div>
                                    @if($materi->description)
                                        <div class="text-sm text-gray-500 truncate max-w-xs">{{ $materi->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($materi->created_at)->translatedFormat('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button onclick="openModal('showAssessmentModal_{{ $materi->id }}')"
                                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd" d="M4.998 7.78C6.729 6.345 9.198 5 12 5c2.802 0 5.27 1.345 7.002 2.78a12.713 12.713 0 0 1 2.096 2.183c.253.344.465.682.618.997.14.286.284.658.284 1.04s-.145.754-.284 1.04a6.6 6.6 0 0 1-.618.997 12.712 12.712 0 0 1-2.096 2.183C17.271 17.655 14.802 19 12 19c-2.802 0-5.27-1.345-7.002-2.78a12.712 12.712 0 0 1-2.096-2.183 6.6 6.6 0 0 1-.618-.997C2.144 12.754 2 12.382 2 12s.145-.754.284-1.04c.153-.315.365-.653.618-.997A12.714 12.714 0 0 1 4.998 7.78ZM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                        <button onclick="openModal('materiModal-{{ $materi->id }}')"
                                                class="p-2 text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd" d="M11.32 6.176H5c-1.105 0-2 .949-2 2.118v10.588C3 20.052 3.895 21 5 21h11c1.105 0 2-.948 2-2.118v-7.75l-3.914 4.144A2.46 2.46 0 0 1 12.81 16l-2.681.568c-1.75.37-3.292-1.263-2.942-3.115l.536-2.839c.097-.512.335-.983.684-1.352l2.914-3.086Z" clip-rule="evenodd"/>
                                                </svg>
                                        </button>
                                        <form action="{{ route('materis.destroy', $materi->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menghapus materi ini?')"
                                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path fill-rule="evenodd" d="M16.5 4.478v.227a48.816 48.816 0 0 1 3.878.512.75.75 0 1 1-.256 1.478l-.209-.035-1.005 13.07a3 3 0 0 1-2.991 2.77H8.084a3 3 0 0 1-2.991-2.77L4.087 6.66l-.209.035a.75.75 0 0 1-.256-1.478A48.567 48.567 0 0 1 7.5 4.705v-.227c0-1.564 1.213-2.9 2.816-2.951a52.662 52.662 0 0 1 3.369 0c1.603.051 2.815 1.387 2.815 2.951Zm-6.136-1.452a51.196 51.196 0 0 1 3.273 0C14.39 3.05 15 3.684 15 4.478v.113a49.488 49.488 0 0 0-6 0v-.113c0-.794.609-1.428 1.364-1.452Zm-.355 5.945a.75.75 0 1 0-1.5.058l.347 9a.75.75 0 1 0 1.499-.058l-.346-9Zm5.48.058a.75.75 0 1 0-1.498-.058l-.347 9a.75.75 0 0 0 1.5.058l.345-9Z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Materi</h3>
                                        <p class="text-gray-500">Mulai dengan menambahkan materi pembelajaran pertama Anda</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($materis->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $materis->links('vendor.pagination.tailwind') }}
                    </div>
                @endif
            </div>

            {{-- Modal Show --}}
            @foreach ($materis as $materi)
                <div id="showAssessmentModal_{{ $materi->id }}"
                    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
                    <div class="bg-white rounded-lg shadow-lg w-[90%] md:w-[60%] lg:w-[50%] h-auto pt-6 pb-7 pl-6 mr-6">
                        {{-- Header Modal --}}
                        <div class="flex justify-between items-center border-b pb-4 mr-6">
                            <h5 class="text-2xl font-bold text-gray-800">Detail Materi</h5>
                            <button type="button" class="text-gray-700 hover:text-gray-00"
                                onclick="closeModal('showAssessmentModal_{{ $materi->id }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Content Modal --}}
                        <div class="mt-4 space-y-4 overflow-y-auto h-auto max-h-[80%]">
                            <div class="flex space-x-2">
                                <h6 class="text-lg font-semibold text-gray-700">Materi:
                                    <span class="text-gray-500">{{ $materi->title_materi }}</span>
                                </h6>
                            </div>
                            <div class="flex space-x-2">
                                <h6 class="text-lg font-semibold text-gray-700">Kelas:
                                    <span
                                        class="text-gray-500">{{ $materi->classes->pluck('name_class')->implode(', ') }}</span>
                                </h6>
                            </div>
                            <div class="flex space-x-2">
                                <h6 class="text-lg font-semibold text-gray-700">Tanggal Pembuatan:
                                    <span class="text-gray-500">
                                        {{ \Carbon\Carbon::parse($materi->created_at)->translatedFormat('l, j F Y') }}
                                    </span>
                                </h6>
                            </div>
                            <div>
                                <h6 class="text-lg font-semibold text-gray-700 mb-3">Deskripsi:
                                    <span class="text-gray-500">{{ $materi->description ?? 'Kosong' }}</span>
                                </h6>
                            </div>
                            <div class="mr-6">
                                <h6 class="text-lg font-semibold text-gray-700 mb-3">File Materi</h6>
                                <a href="{{ Storage::url($materi->file_materi) }}" target="_blank"
                                    class="w-[120px] h-[43px] p-2 border-2 text-xs text-white bg-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-700 transition">Lihat Materi</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach


            <!-- Modal Edit -->
            @foreach ($materis as $materi)
                <div id="materiModal-{{ $materi->id }}"
                    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
                    <div class="bg-white rounded-lg pt-6 pb-2 pl-6 w-[40%] h-auto shadow-lg">
                        <h5 class="text-xl font-bold mb-4">Ubah Materi</h5>
                        <form action="{{ route('materis.update', $materi->id) }}" method="POST"
                            enctype="multipart/form-data" class="overflow-y-auto">
                            @csrf
                            @method('PUT')
                            <div class="mb-3 mr-6">
                                <label for="classes_id" class="block font-medium mb-1">Kelas</label>
                                <select class="js-example-basic-multiple px-3 py-5 border rounded" name="class_id[]"
                                    multiple="multiple">
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}"
                                            {{ in_array($class->id, old('classes_id', $materi->classes->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $class->name_class }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="mb-3 mr-6">
                                <label for="title_materi-{{ $materi->id }}" class="block font-medium mb-1">Nama
                                    Materi</label>
                                <input type="text" id="title_materi-{{ $materi->id }}" name="title_materi"
                                    class="w-full border rounded px-3 py-2" value="{{ $materi->title_materi }}">
                            </div>

                            <div class="mb-3 mr-6">
                                <label for="description-{{ $materi->id }}"
                                    class="block font-medium mb-1">Deskripsi</label>
                                <textarea id="description-{{ $materi->id }}" rows="2" name="description"
                                    class="w-full px-3 py-2 border rounded">{{ $materi->description }}</textarea>
                            </div>
                            <div class="mb-3 mr-6">
                                <label for="file_materi-{{ $materi->id }}" class="block font-medium mb-1">File
                                    Materi</label>
                                <small>File Harus Berformat PDF</small>

                                <!-- Input File -->
                                <input type="file" id="file_materi-{{ $materi->id }}" name="file_materi"
                                    class="w-full border rounded px-3 py-2"
                                    onchange="updateFilePreview({{ $materi->id }})">

                                <!-- Preview File Lama -->
                                <div id="file-preview-{{ $materi->id }}" class="mt-2">
                                    @if ($materi->file_materi)
                                        <p>File saat ini:
                                            <a href="{{ asset('storage/' . $materi->file_materi) }}" target="_blank"
                                                class="text-blue-500">
                                                {{ basename($materi->file_materi) }}
                                            </a>
                                        </p>
                                    @else
                                        <p class="text-gray-500">Tidak ada file yang diunggah sebelumnya.</p>
                                    @endif
                                </div>
                            </div>

                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Simpan
                                Perubahan</button>
                            <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded"
                                onclick="closeModal('materiModal-{{ $materi->id }}')">Batal</button>
                        </form>
                    </div>
                </div>
            @endforeach

            <!-- Modal Tambah -->
            <div id="materiModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-lg pt-6 pb-2 pl-6 w-[40%] h-auto shadow-lg">
                    <h5 class="text-xl font-bold mb-4">Tambah Materi</h5>

                    <form action="{{ route('materis.store') }}" method="POST" enctype="multipart/form-data"
                        class="overflow-y-auto h-[56%]">
                        @csrf

                        <!-- Kelas -->
                        <div class="mb-3 mr-6">
                            <label for="classes_id" class="block font-medium mb-1">Kelas</label>
                            <select class="js-example-basic-multiple px-3 py-5 border rounded" name="class_id[]"
                                multiple="multiple">
                                <!-- Tambahkan opsi jika perlu -->
                                @foreach ($classes as $class)
                                    <option
                                        value="{{ $class->id }}"{{ in_array($class->id, old('class_id', [])) ? 'selected' : '' }}>
                                        {{ $class->name_class }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Nama Materi -->
                        <div class="mb-3 mr-6">
                            <label for="title_materi" class="block font-medium mb-1">Nama Materi</label>
                            <input type="text" id="title_materi" name="title_materi"
                                class="w-full border rounded px-3 py-2" value="{{ old('title_materi') }}">
                        </div>

                        <!-- Deskripsi -->
                        <div class="mb-3 mr-6">
                            <label for="description" class="block font-medium mb-1">Deskripsi</label>
                            <textarea id="description" rows="2" name="description" class="w-full px-3 py-2 border rounded">{{ old('description') }}</textarea>
                        </div>

                        <!-- File Materi -->
                        <div class="mb-3 mr-6">
                            <label for="file_materi" class="block font-medium mb-1">File Materi</label>
                            <small>File Harus Berformat PDF</small>

                            <!-- Input File -->
                            <input type="file" id="file_materi-new" name="file_materi"
                                class="w-full border rounded px-3 py-2">
                            <div id="file-preview-new" class="mt-2"></div> <!-- Elemen untuk menampilkan preview -->

                        </div>

                        <!-- Tombol Submit -->
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">
                            Tambah Materi
                        </button>
                        <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded"
                            onclick="closeModal('materiModal')">
                            Batal
                        </button>
                    </form>
                </div>
            </div>

        </div>
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

    <!-- External Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.js-example-basic-multiple').select2({
                placeholder: "Pilih Kelas",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endsection
