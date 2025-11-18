@extends('layouts.appTeacher')
@section('content')
    <!-- CSS -->
    <style>
        /* Gaya tombol aktif */
        .profile.active {
            background-color: #2563eb;
            /* Tailwind blue-600 */
            color: #ffffff;
        }

        #searchForm {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        #searchForm.show {
            opacity: 1;
            visibility: visible;
        }

        .hidden {
            display: none;
        }

        [id^="dropdown-opsi-"] {
            transition: all 0.3s ease-in-out;
        }
    </style>
    <div class="container mx-auto p-4">
        <div class="container mx-auto pt-2">
            <div class="flex items-center space-x-2 my-4">
                <h1 class="text-2xl text-blue-600 font-bold mr-auto">Tugas</h1>
                <!-- Tombol Search dengan Form Animasi -->
                <div class="relative flex items-center">
                    <!-- Tombol Search -->
                    <button id="searchButton"
                        class="p-3 border-2 bg-white text-black rounded-lg flex items-center justify-center transition-all duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-[14px] w-[14px]" viewBox="0 0 24 24">
                            <path fill="black"
                                d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5A6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5S14 7.01 14 9.5S11.99 14 9.5 14" />
                        </svg>
                    </button>

                    <!-- Form Pencarian -->
                    <form id="searchForm" action="{{ route('tasks.index') }}" method="GET" name="search"
                        class="absolute right-full mr-2 mt-4 opacity-0 invisible transition-all duration-300">
                        <input type="text" name="search" placeholder="Cari..."
                            class="p-2 border-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </form>
                </div>

                <div class="">
                    <!-- Dropdown Button -->
                    <button id="profile-button"
                        class="profile flex p-3 text-xs  [&.active]:bg-blue-500  [&.active]:text-blue-100 bg-white border-2 rounded-lg hover:bg-blue-500 hover:text-blue-100 transition"
                        onclick="toggleDropdown('dropdown-opsi')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                            <path fill="currentColor"
                                d="M1.75 3.75A.75.75 0 0 1 2.5 3h11a.75.75 0 0 1 0 1.5h-11a.75.75 0 0 1-.75-.75m2 4A.75.75 0 0 1 4.5 7h7a.75.75 0 0 1 0 1.5h-7a.75.75 0 0 1-.75-.75m2 4A.75.75 0 0 1 6.5 11h3a.75.75 0 0 1 0 1.5h-3a.75.75 0 0 1-.75-.75" />
                        </svg>
                    </button>

                    <div id="dropdown-opsi"
                        class="hidden absolute text-left mt-2 right-32 p-3 border bg-white shadow-lg rounded-md text-md font-medium z-10">
                        <ul class="py-2">
                            @foreach ($kelas as $class)
                                <!-- Lihat -->
                                <li>
                                    <form action="{{ route('tasks.index') }}" method="GET" class="inline">
                                        <input type="hidden" name="class_id" value="{{ $class->id }}">
                                        <button type="submit"
                                            class="flex items-center gap-2 px-4 py-1 text-gray-500 hover:bg-gray-100">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24">
                                                <path fill="currentColor"
                                                    d="M7.402 4.5C7 5.196 7 6.13 7 8v3.027C7.43 11 7.914 11 8.435 11h7.13c.52 0 1.005 0 1.435.027V8c0-1.87 0-2.804-.402-3.5A3 3 0 0 0 15.5 3.402C14.804 3 13.87 3 12 3s-2.804 0-3.5.402A3 3 0 0 0 7.402 4.5M6.25 15.991c-.502-.02-.806-.088-1.014-.315c-.297-.324-.258-.774-.18-1.675c.055-.65.181-1.088.467-1.415C6.035 12 6.858 12 8.505 12h6.99c1.647 0 2.47 0 2.982.586c.286.326.412.764.468 1.415c.077.9.116 1.351-.181 1.675c-.208.227-.512.295-1.014.315V21a.75.75 0 1 1-1.5 0v-5h-8.5v5a.75.75 0 1 1-1.5 0z" />
                                            </svg>
                                            <span class="ml-1">{{ $class->name_class }}</span>
                                        </button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Tombol Tambah Materi -->
                <a class="w-[120px] h-[43px] p-2 border-2 text-xs text-white bg-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-700 transition"
                    onclick="openModal('taskModal')">
                    <svg class="w-[15px] h-[15px] fill-[#ffffff] me-2" viewBox="0 0 448 512"
                        xmlns="http://www.w3.org/2000/svg">

                        <!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                        <path
                            d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z">
                        </path>

                    </svg>
                    Tambah
                </a>
            </div>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                    role="alert">
                    <strong class="font-bold">{{ session('success') }}</strong>
                    <button onclick="this.parentElement.style.display='none'"
                        class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Kesalahan Validasi:</strong>
                    <ul class="list-disc ml-5 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button onclick="this.parentElement.style.display='none'"
                        class="absolute top-0 bottom-0 right-0 px-4 py-3">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            <!-- Tabel Materi -->
            <div class="block max-w bg-white rounded-lg shadow hover:bg-white">
                <h6 class="font-semibold p-3 text-sm ps-5">Daftar Tugas</h6>
                <div class="overflow-x-auto px-5">
                    <table class="min-w-full bg-white text-left rounded-lg border ">
                        <thead>
                            <tr class="border text-center">
                                <th class="px-4 py-2 text-gray-500 text-xs font-semibold">No</th>
                                <th class="px-4 py-2 text-gray-500 text-xs font-semibold">Nama Tugas</th>
                                <th class="px-4 py-2 text-gray-500 text-xs font-semibold">Kelas</th>
                                <th class="px-4 py-2 text-gray-500 text-xs font-semibold">Materi</th>
                                <th class="px-4 py-2 text-gray-500 text-xs font-semibold">Tanggal Pengumpulan Tugas</th>
                                <th class="px-4 py-2 text-gray-500 text-xs font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="">
                            @foreach ($tasks as $index => $task)
                                @php
                                    $offset = ($tasks->currentPage() - 1) * $tasks->perPage();
                                @endphp
                                <tr class="border text-center">
                                    <td class="border py-3 px-6">{{ $offset + $index + 1 }}</td>
                                    <td class="border py-3 px-6">{{ $task->title_task }}</td>
                                    <td class="border py-3 px-6">{{ $task->Classes->name_class }}</td>
                                    <td class="border py-3 px-6">{{ $task->Materi->title_materi }}</td>
                                    <td class="border py-2 px-6">
                                        {{ \Carbon\Carbon::parse($task->date_collection)->translatedFormat('H:i l, j F Y') }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <div style="justify-items: center">
                                            <!-- Dropdown Button -->
                                            <button id="profile-button-{{ $task->id }}"
                                                class="profile flex p-2 text-xs [&.active]:bg-blue-500  [&.active]:text-blue-100 text-blue-500 bg-blue-100 rounded-lg hover:bg-blue-500 hover:text-blue-100 transition"
                                                onclick="toggleDropdown('dropdown-opsi-{{ $task->id }}')">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                                                </svg>
                                            </button>

                                            <div id="dropdown-opsi-{{ $task->id }}"
                                                class="hidden absolute text-left right-32 p-3 border bg-white shadow-lg rounded-md text-md font-medium z-10">
                                                <ul class="py-2">
                                                    <!-- Lihat -->
                                                    <li>
                                                        <a onclick="openModal('Assessmentshow_{{ $task->id }}'); closeDropdown('dropdown-opsi-{{ $task->id }}');"
                                                            class="flex items-center gap-2 px-4 py-1 text-purple-500 hover:bg-gray-100">
                                                            <svg class="w-6 h-6 text-purple-500" aria-hidden="true"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24">
                                                                <path stroke="currentColor" stroke-width="2"
                                                                    d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z" />
                                                                <path stroke="currentColor" stroke-width="2"
                                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                            </svg>
                                                            <span class="ml-1">Lihat</span>
                                                        </a>
                                                    </li>
                                                    <!-- Pengumpulan -->
                                                    <li>
                                                        <a onclick="openModal('showCollection_{{ $task->id }}'); closeDropdown('dropdown-opsi-{{ $task->id }}');"
                                                            class="flex items-center gap-2 px-4 py-1 text-blue-500 hover:bg-blue-100">
                                                            <svg class="w-6 h-6 text-blue-500" aria-hidden="true"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24">
                                                                <path stroke="currentColor" stroke-linecap="round"
                                                                    stroke-linejoin="round" stroke-width="2"
                                                                    d="M15 4h3a1 1 0 0 1 1 1v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h3m0 3h6m-6 5h6m-6 4h6M10 3v4h4V3h-4Z" />
                                                            </svg>
                                                            <span class="ml-1">Pengumpulan</span>
                                                        </a>
                                                    </li>
                                                    <!-- Nilai -->
                                                    <li>
                                                        <a href="{{ route('assesments', $task->id) }}; closeDropdown('dropdown-opsi-{{ $task->id }}');"
                                                            class="flex items-center gap-2 px-4 py-1 text-green-500 hover:bg-gray-100">
                                                            <svg class="w-6 h-6 text-green-500" aria-hidden="true"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24">
                                                                <path stroke="currentColor" stroke-linecap="round"
                                                                    stroke-linejoin="round" stroke-width="2"
                                                                    d="M15 4h3a1 1 0 0 1 1 1v15a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h3m0 3h6m-6 7 2 2 4-4m-5-9v4h4V3h-4Z" />
                                                            </svg>
                                                            <span class="ml-1">Nilai</span>
                                                        </a>
                                                    </li>
                                                    <!-- Edit -->
                                                    <li>
                                                        <a onclick="openModal('editTaskModal{{ $task->id }}'); closeDropdown('dropdown-opsi-{{ $task->id }}');"
                                                            class="flex items-center gap-2 px-4 py-1 text-yellow-400 hover:bg-gray-100">
                                                            <svg class="w-7 h-7 text-yellow-400" aria-hidden="true"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24">
                                                                <path stroke="currentColor" stroke-linecap="round"
                                                                    stroke-linejoin="round" stroke-width="2"
                                                                    d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z" />
                                                            </svg>
                                                            <span>Edit</span>
                                                        </a>
                                                    </li>
                                                    <!-- Hapus -->
                                                    <li>
                                                        <form action="{{ route('tasks.destroy', $task->id) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus task ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="flex items-center gap-2 px-4 py-1 w-full text-start text-red-500 hover:bg-gray-100">
                                                                <svg class="w-6 h-6 text-red-500" aria-hidden="true"
                                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke="currentColor" stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                                                </svg>
                                                                <span class="ml-1">Hapus</span>
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pagination py-3 px-5">
                    {{ $tasks->links('vendor.pagination.tailwind') }}
                </div>
            </div>

            {{-- Modal Show --}}
            @foreach ($tasks as $task)
                <div id="Assessmentshow_{{ $task->id }}"
                    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
                    <div class="bg-white rounded-lg shadow-lg w-[90%] md:w-[60%] lg:w-[50%] h-auto pt-6 pb-7 pl-6 mr-6">
                        {{-- Header Modal --}}
                        <div class="flex justify-between items-center border-b pb-4 mr-6">
                            <h5 class="text-2xl font-bold text-gray-800">Detail Tugas</h5>
                            <button type="button" class="text-gray-700 hover:text-gray00"
                                onclick="closeModal('Assessmentshow_{{ $task->id }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Content Modal --}}
                        <div class="mt-4 space-y-4 overflow-y-auto h-auto max-h-[80%]">
                            <div class="">
                                <h6 class="text-lg font-semibold text-gray-700">Nama Tugas :</h6>
                                <p class="text-gray-600">{{ $task->title_task }}</p>
                            </div>
                            <div class="">
                                <h6 class="text-lg font-semibold text-gray-700">Kelas :</h6>
                                <p class="text-gray-600">{{ $task->classes->name_class }}</p>
                            </div>
                            <div class="">
                                <h6 class="text-lg font-semibold text-gray-700">Nama :</h6>
                                <p class="text-gray-600">{{ $task->Materi->title_materi }}</p>
                            </div>
                            <div class="">
                                <h6 class="text-lg font-semibold text-gray-700">Tanggal Pembuatan :</h6>
                                <p class="text-gray-700">
                                    {{ \Carbon\Carbon::parse($task->created_at)->translatedFormat('l, j F Y') }}
                                </p>
                            </div>
                            <div>
                                <h6 class="text-lg font-semibold text-gray-700">Deskripsi :</h6>
                                <p class="text-gray-600">{{ $task->description_task }}</p>
                            </div>
                            <div class="mr-6">
                                <h6 class="text-lg font-semibold text-gray-700 mb-3">File task</h6>

                                @php
                                    $file = pathinfo($task->file_task, PATHINFO_EXTENSION);
                                @endphp
                                @if (in_array($file, ['jpg', 'png']))
                                    <img src="{{ asset('storage/' . $task->file_task) }}" alt="File Image"
                                        class="mx-auto w-[90%] h-full border-2 rounded-lg">
                                @elseif($file === 'pdf')
                                    <a href="{{ Storage::url($task->file_task) }}" target="_black"
                                        class="w-[120px] h-[43px] p-2 border-2 text-white bg-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-700 transition">
                                        Buka Tugas
                                    </a>
                                @else
                                    <p class="text-gray-500">File Kosong</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Modal Create -->
            <div id="taskModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50"
                style="display: none;" role="dialog" aria-hidden="true">
                <div class="bg-white rounded-lg w-[40%] h-auto pt-6 pb-2 pl-6">
                    <h5 class="text-xl font-bold mb-4" id="modal-title">Tambah Tugas</h5>
                    <form action="{{ route('tasks.store') }}" method="POST" enctype="multipart/form-data"
                        class="overflow-y-auto h-[70%]">
                        @csrf
                        <div class="grid grid-cols-2 gap-4">
                            <div class="mb-4 mr-2">
                                <label for="class_id" class="block text-gray-700 font-bold mb-2">Kelas</label>
                                <select name="class_id" id="class_id" class="w-full px-3 py-2 border rounded">
                                    <option value="" disabled selected>Pilih Kelas</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}"
                                            {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name_class }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('class_id')
                                    <div class="text-red-500 text-sm">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4 mr-6">
                                <label for="materi_id" class="block text-gray-700 font-bold mb-2">Materi</label>
                                <select name="materi_id" id="materi_id" class="w-full px-3 py-2 border rounded">
                                    <option value="" disabled selected>Pilih Materi</option>
                                    @foreach ($materis as $materi)
                                        <option value="{{ $materi->id }}"
                                            {{ old('materi_id') == $materi->id ? 'selected' : '' }}>
                                            {{ $materi->title_materi }}</option>
                                    @endforeach
                                </select>
                                @error('materi_id')
                                    <div class="text-red-500">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-4 mr-6">
                            <label for="title_task" class="block text-gray-700 font-bold mb-2">Judul Tugas</label>
                            <input type="text" id="title_task" name="title_task"
                                class="w-full px-3 py-2 border rounded" value="{{ old('title_task') }}">
                            @error('title_task')
                                <div class="text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-4 mr-6">
                            <label for="date_collection" class="block text-gray-700 font-bold mb-2">
                                Tanggal dan Waktu Pengumpulan Tugas
                            </label>
                            <input type="datetime-local" id="date_collection" name="date_collection"
                                class="w-full px-3 py-2 border rounded" value="{{ old('date_collection') }}">
                            @error('date_collection')
                                <div class="text-red-500">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4 mr-6">
                            <label for="description_task" class="block text-gray-700 font-bold mb-2">Deskripsi</label>
                            <textarea id="description_task" rows="3" name="description_task" class="w-full px-3 py-2 border rounded">{{ old('description_task') }}</textarea>
                            @error('description_task')
                                <div class="text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-4 mr-6 ">
                            <label for="file_task" class="block text-gray-700 font-bold mb-2">File Tugas</label>
                            <!-- Image preview -->
                            <div id="file-preview" class="mt-2">
                                <img id="image-preview" class="mt-2 w-32 mb-2" style="display: none;" alt="Preview" />
                            </div>
                            <input type="File" id="file_task" name="file_task"
                                class="w-full px-3 py-2 border rounded" value="{{ old('file_task') }}">
                            @error('file_task')
                                <div class="text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Repeat other fields -->
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Tambah
                            tugas</button>
                        <button type="button"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold py-2 px-4 rounded"
                            onclick="closeModal('taskModal')">Batal</button>
                    </form>
                </div>
            </div>

            <!-- Modal Update -->
            @foreach ($tasks as $task)
                <div id="editTaskModal{{ $task->id }}"
                    class="taskModal fixed inset-0 z-50 hidden  items-center justify-center bg-black "
                    style="display: none;">
                    <div class="bg-white rounded-lg w-[40%] h-auto pt-6 pb-2 pl-6">
                        <h5 class="text-xl font-bold mb-4" id="modal-title">Ubah Tugas</h5>
                        <form action="{{ route('tasks.update', $task->id) }}" method="POST"
                            enctype="multipart/form-data" class="overflow-y-auto h-[70%]">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-2 gap-4">
                                <div class="mb-4">
                                    <label for="class_id" class="block text-gray-700 font-bold mb-2">Kelas</label>
                                    <select name="class_id" id="class_id" class="w-full px-3 py-2 border rounded">
                                        <option value="" disabled selected>Pilih Kelas</option>
                                        @foreach ($classes as $class)
                                            <option value="{{ $class->id }}"
                                                {{ $task->class_id == $class->id ? 'selected' : '' }}>
                                                {{ $class->name_class }}</option>
                                        @endforeach
                                    </select>
                                    @error('class_id')
                                        <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-4 mr-6">
                                    <label for="materi_id" class="block text-gray-700 font-bold mb-2">Materi</label>
                                    <select name="materi_id" id="materi_id" class="w-full px-3 py-2 border rounded">
                                        <option value="" disabled selected>Pilih Materi</option>
                                        @foreach ($materis as $materi)
                                            <option value="{{ $materi->id }}"
                                                {{ $task->materi_id == $materi->id ? 'selected' : '' }}>
                                                {{ $materi->title_materi }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('materi_id')
                                        <div class="text-red-500">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-4 mr-6">
                                <label for="title_task" class="block text-gray-700 font-bold mb-2">Judul Tugas</label>
                                <input type="text" id="title_task" name="title_task"
                                    class="w-full px-3 py-2 border rounded" value="{{ $task->title_task }}">
                                @error('title_task')
                                    <div class="text-red-500">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4 mr-6">
                                <label for="file_task" class="block text-gray-700 font-bold mb-2">File Tugas</label>
                                <input type="File" id="file_task-{{ $task->id }}" name="file_task"
                                    class="w-full px-3 py-2 border rounded">
                                @error('file_task')
                                    <div class="text-red-500">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4 mr-6">
                                <label for="description_task" class="block text-gray-700 font-bold mb-2">Deskripsi</label>
                                <textarea id="description_task" rows="3" name="description_task" class="w-full px-3 py-2 border rounded">{{ $task->description_task }}</textarea>
                                @error('description_task')
                                    <div class="text-red-500">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4 mr-6">
                                <label for="date_collection" class="block text-gray-700 font-bold mb-2">Tanggal
                                    Pengumpulan
                                    Tugas</label>
                                <input type="datetime-local" id="date_collection" name="date_collection"
                                    class="w-full px-3 py-2 border rounded" value="{{ $task->date_collection }}">
                                @error('date_collection')
                                    <div class="text-red-500">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit"
                                class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Kirim</button>
                            <button type="button"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold py-2 px-4 rounded"
                                onclick="closeModal('editTaskModal{{ $task->id }}')">Batal</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @foreach ($tasks as $task)
        <div id="showCollection_{{ $task->id }}"
            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
            <div class="bg-white rounded-lg shadow-lg w-[50%] h-auto pt-6 pb-7 px-5 mr-6 overflow-x-hidden">
                {{-- Header Modal --}}
                <div class="flex justify-between items-center border-b pb-4">
                    <h5 class="text-2xl font-bold text-gray-800">Daftar Pengumpulan</h5>
                    <button type="button" class="text-gray-700 hover:text-gray00"
                        onclick="closeModal('showCollection_{{ $task->id }}')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Content Modal --}}
                @php
                    $filteredPengumpulans = $pengumpulans[$task->id] ?? collect();
                @endphp

                @if ($filteredPengumpulans->isNotEmpty())
                    <div class="w-[100%]">
                        <table class="min-w-full bg-white text-center rounded-lg">
                            <thead>
                                <tr class="border">
                                    <th class="px-4 py-2 text-gray-500 text-xs font-semibold">No</th>
                                    <th class="px-4 py-2 text-gray-500 text-xs font-semibold">Nama Siswa</th>
                                    <th class="px-4 py-2 text-gray-500 text-xs font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($filteredPengumpulans as $index => $pengumpulan)
                                    <tr class="text-center">
                                        <td class="border px-4 py-2">{{ $index + 1 }}</td>
                                        <td class="border px-4 py-2">{{ $pengumpulan->user->name }}</td>
                                        <td class="border px-4 py-2">{{ $pengumpulan->status }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-center mt-4">Tidak ada pengumpulan untuk tugas ini.</p>
                @endif
            </div>
        </div>
    @endforeach
@endsection

<script>
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function(event) {

            const previewId = this.id.replace('file_task',
                'file-preview'); // Mengganti nama preview ID
            const filePreview = document.getElementById(previewId); // Elemen untuk preview
            const file = event.target.files[0];

            if (file) {
                const fileExtension = file.name.split('.').pop()
                    .toLowerCase(); // Dapatkan ekstensi file
                const reader = new FileReader();

                if (['jpg', 'jpeg', 'png'].includes(fileExtension)) {
                    // Jika file adalah gambar
                    reader.onload = function(e) {
                        filePreview.innerHTML =
                            `<p>File Sekarang</p>
                            <img src="${e.target.result}" class="mt-2 w-32 mb-2" alt="Preview">`;
                    };
                } else if (fileExtension === 'pdf') {
                    // Jika file adalah PDF
                    reader.onload = function(e) {
                        filePreview.innerHTML =
                            `<p>File Sekarang</p>
                            <embed src="${e.target.result}" type="application/pdf" class="mt-2 w-full h-32 mb-2" />`;
                    };
                } else {
                    // Jika format tidak didukung
                    filePreview.innerHTML = `<p class="text-red-500">Format file tidak didukung.</p>`;
                }

                reader.readAsDataURL(file); // Membaca file sebagai URL data
            } else {
                filePreview.innerHTML = ''; // Kosongkan preview jika file dihapus
            }
        });
    });

    function openModal(id) {
        console.log(`Opening modal: ${id}`);
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'flex';
        } else {
            console.error(`Modal dengan ID ${id} tidak ditemukan.`);
        }
    }

    function closeModal(id) {
        console.log(`Closing modal: ${id}`);
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'none';
        } else {
            console.error(`Modal dengan ID ${id} tidak ditemukan.`);
        }
    }

    // Open the modal if validation fails
    @if (session('success'))
        document.addEventListener("DOMContentLoaded", function() {
            closeModal('taskModal'); // Menutup modal setelah data berhasil ditambah
        });
    @endif
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const searchButton = document.getElementById("searchButton");
        const searchForm = document.getElementById("searchForm");

        searchButton.addEventListener("click", function(event) {
            event.stopPropagation(); // Mencegah klik tombol menutup form
            searchForm.classList.toggle("show");
        });

        document.addEventListener("click", function() {
            searchForm.classList.remove("show");
        });

        searchForm.addEventListener("click", function(event) {
            event.stopPropagation(); // Mencegah klik pada form menutup form
        });
    });
</script>
<script>
    var expanded = false;

    function showCheckboxes(taskId) {
        var checkboxes = document.getElementById("checkboxes_" + taskId);
        if (checkboxes.style.display === "block") {
            checkboxes.style.display = "none";
        } else {
            checkboxes.style.display = "block";
        }
    }
</script>
<script>
    function toggleDropdown(id) {
        // Tutup semua dropdown lain dulu
        document.querySelectorAll("[id^='dropdown-opsi-']").forEach(el => {
            if (el.id !== id) {
                el.classList.add("hidden");
            }
        });

        // Toggle dropdown yang diklik
        const dropdown = document.getElementById(id);
        if (dropdown) {
            dropdown.classList.toggle("hidden");
        }
    }

    // Tutup dropdown kalau klik di luar
    document.addEventListener("click", function(event) {
        const isDropdownButton = event.target.closest("button[id^='profile-button-']");
        const isDropdownMenu = event.target.closest("[id^='dropdown-opsi-']");

        if (!isDropdownButton && !isDropdownMenu) {
            document.querySelectorAll("[id^='dropdown-opsi-']").forEach(el => {
                el.classList.add("hidden");
            });
        }
    });
</script>

