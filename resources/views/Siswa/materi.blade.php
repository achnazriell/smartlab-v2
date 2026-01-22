@extends('layouts.appSiswa')

@section('content')
    <style>
        .materiModal {
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease-in-out;
            z-index: 50;
        }

        .materiModal .bg-white {
            height: 90%;
            /* Atur tinggi modal */
            max-height: 90%;
            /* Batas maksimum */
            display: flex;
            flex-direction: column;
            /* Agar konten vertikal terorganisir */
        }

        .materiModal embed {
            flex: 1;
            /* Isi seluruh ruang yang tersedia */
            min-height: 0;
            /* Pastikan tidak terjadi overflow */
        }


        @media (max-width: 639px) {
            .covercard {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .carousel-indicators {
            align-items: center;
        }

        .carousel-indicators button {
            width: 10px !important;
            height: 10px !important;
            border-radius: 100%;
            background-color: rgba(255, 255, 255, 0.507) !important;
        }

        .carousel-indicators button.active {
            width: 15px !important;
            height: 15px !important;
            border-radius: 100%;
            background-color: white !important;
        }

        .carousel-item img {
            height: 300px;
            object-fit: cover;
            border-radius: 1rem !important;
        }

        .carousel-item .follow-event-btn {
            z-index: 100;
        }

        .carousel-item:after {
            position: absolute;
            content: "";
            height: 100%;
            width: 100%;
            top: 0;
            left: 0;
            background: linear-gradient(to bottom, rgba(255, 0, 0, 0), rgba(0, 0, 0, 0.65) 100%);
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        .hidden {
            display: none;
        }
    </style>
    <div class="p-4 sm:p-6 lg:p-8">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="loader border-t-4 border-blue-600 rounded-full w-16 h-16 animate-spin"></div>
        </div>

        <div class="bg-white shadow-md rounded-2xl p-1 mb-8">
            <div class="container px-10 py-5 flex justify-between items-center">
                <div class="flex items-center gap-5">
                    <!-- Tabs -->
                    <button id="tab-materi"
                        class="tab-button bg-blue-800 text-white rounded-xl shadow-md px-3 flex items-center justify-center transform transition-all duration-200 active:scale-95"
                        onclick="showFilter('materi')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 m-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4 3h2v18H4zm14 0H7v18h11c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2m-2 6h-6V8h6zm0-2h-6V6h6z" />
                        </svg>
                        <span class="font-semibold text-lg">Materi</span>
                    </button>
                    <button id="tab-tugas"
                        class="tab-button bg-white text-blue-800 rounded-xl shadow-md px-3 flex items-center justify-center transform transition-all duration-200 active:scale-95"
                        onclick="showFilter('tugas')">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 m-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 1H8v4h8z" />
                            <path d="M3 3h3v4h12V3h3v20H3zm12 10v-2H9v2zm0 4v-2H9v2z" />
                        </svg>
                        <span class="font-semibold text-lg">Tugas</span>
                    </button>
                    {{-- end Tabs --}}
                </div>

                <div class="flex items-center gap-3">

                    <!-- Form Pencarian -->
                    <form action="{{ route('Materi', ['materi_id' => $materi_id]) }}" method="GET"
                        class="flex items-center">
                        <input type="text" id="search" name="search" placeholder="Search..."
                            class="rounded-xl border-gray-300 p-3">
                        <input type="hidden" id="activeTabInput" name="tab" value="{{ $activeTab }}">
                        <button type="submit"
                            class="ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold p-3 px-4 rounded-xl">
                            <i class="fas fa-search text-white"></i>
                        </button>
                    </form>
                    {{-- end Search --}}

                    <!-- Filter Urutan (Hanya tampil di Tab Materi) -->
                    <form action="{{ route('Materi', ['materi_id' => $materi_id]) }}" method="GET" id="filter-urutan"
                        class="hidden">
                        <!-- Tetapkan tab aktif secara eksplisit ke 'materi' -->
                        <input type="hidden" id="activeTabInput" name="tab" value="materi">
                        @php
                            $nextOrder = request('order', 'desc') === 'desc' ? 'asc' : 'desc';
                        @endphp
                        <input type="hidden" name="order" value="{{ $nextOrder }}">
                        <button type="submit"
                            class="p-4 rounded-xl bg-blue-500 hover:bg-blue-700 text-white flex items-center justify-center">
                            @if (request('order', 'desc') === 'desc')
                                <i class="fa-solid fa-arrow-up-wide-short text-white"></i>
                            @else
                                <i class="fa-solid fa-arrow-down-wide-short text-white"></i>
                            @endif
                        </button>
                    </form>
                    {{-- End Filter Sort --}}

                    <!-- Dropdown Filter (Hanya tampil di Tab Tugas) -->
                    <div class="relative" id="filter-dropdown-container">
                        <button id="filterButton"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold p-3 px-4 rounded-xl shadow-md">
                            <i class="fas fa-filter text-white"></i>
                        </button>
                        <div id="filterDropdown"
                            class="hidden absolute right-0 mt-5 w-72 bg-white border border-gray-300 rounded-xl pl-1 pb-2 shadow-lg z-50">
                            <div class="px-4 py-3 text-lg font-semibold text-gray-700 border-b border-gray-300">
                                Pilih Status Tugas
                            </div>
                            <form method="GET" action="{{ route('Materi', ['materi_id' => $materi_id]) }}">
                                <input type="hidden" id="activeTabInput" name="tab" value="tugas">
                                <button type="submit" name="status" value="Sudah mengumpulkan"
                                    class="flex items-center justify-center px-4 py-2 text-green-800 bg-green-300 rounded-xl m-2 w-64 h-12">
                                    Sudah Mengumpulkan
                                </button>
                                <button type="submit" name="status" value="Belum mengumpulkan"
                                    class="flex items-center justify-center px-4 py-2 text-yellow-600 bg-yellow-100 rounded-xl m-2 w-64 h-12">
                                    Belum Mengumpulkan
                                </button>
                                <button type="submit" name="status" value="Tidak mengumpulkan"
                                    class="flex items-center justify-center px-4 py-2 text-red-800 bg-red-300 rounded-xl m-2 w-64 h-12">
                                    Tidak Mengumpulkan
                                </button>
                            </form>
                        </div>
                    </div>
                    {{-- End Dropdown Filter --}}
                </div>
            </div>
        </div>

        <div class="container p-10">

            <!-- Header Banner -->
            @if ($materis->isNotEmpty() || $tasks->isNotEmpty())
                <div class="d-flex flex-column flex-root">
                    <div class="flex w-full position-relative" style="position: relative;">
                        <!-- Gambar dengan teks di dalamnya -->
                        <img src="/image/siswa/banner materi.svg" alt="banner mapel" style="width: 100%; height: auto;">

                        <!-- Elemen teks yang berada di atas gambar -->
                        <div
                            style="
                        position: absolute;
                        top: 50%;
                        left: 5%; /* Posisi kiri */
                        transform: translateY(-50%);
                        text-align: left;
                        color: white;
                        max-width: 50%; /* Membatasi lebar teks */
                        overflow-wrap: break-word; /* Memastikan teks panjang terpecah */
                        word-wrap: break-word; /* Kompatibilitas tambahan */
                    ">
                            @if ($subjectName)
                                <p class="text-5xl my-5 font-poppins font-bold uppercase">
                                    {{ $subjectName }}
                                </p>
                            @else
                                <p class="text-5xl my-5 font-poppins font-bold uppercase text-gray-600">
                                    mapel tidak ditemukan
                                </p>
                            @endif
                            <!-- Kontainer tambahan untuk icon dan tulisan -->
                            <div class="flex align-items-center gap-12 mt-4">
                                <!-- Informasi guru -->
                                <div class="flex align-items-center gap-4">
                                    <div
                                        style="
                                    width: 35px;
                                    height: 35px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    border-radius: 50%;
                                    background-color: white;
                                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                            viewBox="0 0 256 256" fill="#1E40AF">
                                            <path
                                                d="M216 40H40a16 16 0 0 0-16 16v144a16 16 0 0 0 16 16h13.39a8 8 0 0 0 7.23-4.57a48 48 0 0 1 86.76 0a8 8 0 0 0 7.23 4.57H216a16 16 0 0 0 16-16V56a16 16 0 0 0-16-16M80 144a24 24 0 1 1 24 24a24 24 0 0 1-24-24m136 56h-56.57a64.4 64.4 0 0 0-28.83-26.16a40 40 0 1 0-53.2 0A64.4 64.4 0 0 0 48.57 200H40V56h176ZM56 96V80a8 8 0 0 1 8-8h128a8 8 0 0 1 8 8v96a8 8 0 0 1-8 8h-16a8 8 0 0 1 0-16h8V88H72v8a8 8 0 0 1-16 0" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 style="margin: 0; font-size: 16px; color: white; font-weight: bold">Pengajar
                                        </h2>
                                        <p style="margin: 0; font-size: 14px; color: white;">
                                            {{ $teacherName }}
                                        </p>
                                    </div>
                                </div>
                                <!-- Informasi siswa -->
                                <div class="flex align-items-center gap-4">
                                    <div
                                        style="
                                    width: 35px;
                                    height: 35px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    border-radius: 50%;
                                    background-color: white;
                                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                ">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                            viewBox="0 0 256 256" fill="#1E40AF">
                                            <path
                                                d="m226.53 56.41l-96-32a8 8 0 0 0-5.06 0l-96 32A8 8 0 0 0 24 64v80a8 8 0 0 0 16 0V75.1l33.59 11.19a64 64 0 0 0 20.65 88.05c-18 7.06-33.56 19.83-44.94 37.29a8 8 0 1 0 13.4 8.74C77.77 197.25 101.57 184 128 184s50.23 13.25 65.3 36.37a8 8 0 0 0 13.4-8.74c-11.38-17.46-27-30.23-44.94-37.29a64 64 0 0 0 20.65-88l44.12-14.7a8 8 0 0 0 0-15.18ZM176 120a48 48 0 1 1-86.65-28.45l36.12 12a8 8 0 0 0 5.06 0l36.12-12A47.9 47.9 0 0 1 176 120m-48-32.43L57.3 64L128 40.43L198.7 64Z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 style="margin: 0; font-size: 16px; color: white; font-weight: bold;">Siswa</h2>
                                        <p style="margin: 0; font-size: 14px; color: white;">{{ $countSiswa }} Siswa</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            {{-- Tap Content Materi --}}
            <div class="my-5">
                <!-- Content -->
                <div class="mt-5">
                    <div id="content-materi" class="tab-content hidden">
                        <!-- List Materi -->
                        @forelse ($materis as $materi)
                            <div class="pt-5">
                                <div class="grid grid-cols-1 gap-10">
                                    <div class="bg-blue-500"
                                        style="border-radius: 15px; padding-left: 30px; position: relative;">
                                        <div class="bg-white shadow-md py-10 px-5"
                                            style="border-top-right-radius: 15px; border-bottom-right-radius: 15px;">
                                            <!-- Judul Materi -->
                                            <h2 class="text-xl font-bold mb-2">{{ $materi->title_materi }}</h2>
                                            <!-- Deskripsi Materi -->
                                            <p class="text-gray-600" style="margin-right: 150px;">
                                                {{ Str::limit($materi->description, 50, '...') ?? 'Tidak ada deskripsi' }}
                                            </p>
                                            <!-- Tombol Lihat Detail -->
                                            <div class="mt-4" style="position: absolute; bottom: 10px; right: 10px;">
                                                <button
                                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl"
                                                    onclick="openModal('showMateriModal_{{ $materi->id }}')">Lihat
                                                    detail
                                                </button>
                                                <a href="{{ Storage::url($materi->file_materi) }}" target="_blank"
                                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-xl">
                                                    Buka Materi
                                                </a>
                                            </div>
                                            <!-- Tanggal Materi -->
                                            <div class="absolute top-5 right-5 text-gray-600 font-semibold text-sm">
                                                {{ \Carbon\Carbon::parse($materi->created_at)->translatedFormat('l, j F Y') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <!-- Jika Tidak Ada Materi -->
                            <div class="flex items-center justify-center h-screen">
                                <div class="text-center">
                                    <div class="text-red-500 mb-5" style="justify-self: center">
                                        <img src="/image/Gelembung.svg" alt="">
                                    </div>
                                    <p class="text-gray-700 text-3xl font-semibold">Belum Ada Materi</p>
                                </div>
                            </div>
                        @endforelse
                        <div class="pagination py-3 px-5">
                            {{ $materis->links('vendor.pagination.tailwind') }}
                        </div>
                        @foreach ($materis as $materi)
                            <div id="showMateriModal_{{ $materi->id }}"
                                class="materiModal fixed inset-0 hidden items-center justify-center bg-gray-900 bg-opacity-50 z-50"
                                style="display:none;">
                                <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl h-auto mx-7 py-8 flex flex-col overflow-hidden"
                                    style="padding-left: 28px">
                                    <!-- Header Modal -->
                                    <div class="flex justify-between items-center border-b pb-4"
                                        style="margin-right: 28px">
                                        <h5 class="text-2xl font-bold text-gray-800">Detail Materi</h5>
                                        <button type="button" class="text-gray-700 hover:text-gray-900"
                                            onclick="closeModal('showMateriModal_{{ $materi->id }}')">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Content Modal -->
                                    <div class="mt-4 flex-1  overflow-y-auto">
                                        <div class="space-y-4" style="margin-right: 28px">
                                            <div class="flex space-x-2">
                                                <h6 class="text-lg font-semibold text-gray-700">Materi:
                                                    <span class="text-gray-600">{{ $materi->title_materi }}</span>
                                                </h6>
                                            </div>
                                            <div class="flex space-x-2">
                                                <h6 class="text-lg font-semibold text-gray-700">Tanggal Pembuatan:
                                                    <span class="text-gray-600">
                                                        {{ \Carbon\Carbon::parse($materi->created_at)->translatedFormat('l, j F Y') }}
                                                    </span>
                                                </h6>
                                            </div>
                                            <div>
                                                <h6 class="text-lg font-semibold text-gray-700">Deskripsi:
                                                    <span
                                                        class="text-gray-600">{{ $materi->description ?? 'Kosong' }}</span>
                                                </h6>
                                            </div>
                                            <div style="width: 100%; height: auto; overflow: hidden;">
                                                <h6 class="text-lg font-semibold text-gray-700 mb-3">File Materi:</h6>
                                                <a href="{{ Storage::url($materi->file_materi) }}" target="_black"
                                                    class="w-[120px] h-[43px] p-2 border-2 text-white bg-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-700 transition">
                                                    Buka PDF
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <!--begin::Scrolltop-->
                <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
                    <span class="svg-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1"
                                transform="rotate(90 13 6)" fill="currentColor" />
                            <path
                                d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z"
                                fill="currentColor" />
                        </svg>
                    </span><!--end::Svg Icon-->
                </div>
            </div>
            {{-- Tab Content Tugas --}}
            <div id="content-tugas" class="tab-content hidden space-y-6">
                @forelse ($tasks as $task)
                    <div style="position: relative;">
                        <div class="bg-white shadow-md py-10 px-5" style="border-radius: 15px;">
                            @php
                                $assessment = $task->collections->first()->assessment ?? null;
                            @endphp

                            <p class="text-gray-600" style="margin-right: 150px; margin-bottom: 5px">Nilai:
                                {{ $assessment && $assessment->mark_task !== null ? $assessment->mark_task : 'Belum Dinilai' }}
                            </p>
                            <h2 class="text-xl font-bold mb-2">{{ $task->title_task }}</h2>
                            <p class="text-gray-600" style="margin-right: 150px">
                                Mapel : {{ $task->Subject->name_subject }}
                            </p>

                            <!-- Status Sudah Dikerjakan dengan ikon -->
                            @php
                                $status = $task->collections->first()->status ?? 'default';
                            @endphp

                            @if ($status === 'Tidak mengumpulkan')
                                <div class="flex items-center text-red-400 mt-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="w-6 h-6 mr-2">
                                        <path fill-rule="evenodd"
                                            d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ $status }}</span>
                                </div>
                            @elseif ($status === 'Belum mengumpulkan')
                                <div class="flex items-center text-yellow-300 mt-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="w-6 h-6 mr-2">
                                        <path fill-rule="evenodd"
                                            d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ $status }}</span>
                                </div>
                            @elseif ($status === 'Sudah mengumpulkan')
                                <div class="flex items-center text-green-400 mt-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="w-6 h-6 mr-2">
                                        <path fill-rule="evenodd"
                                            d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ $status }}</span>
                                </div>
                            @else
                                <div class="flex items-center text-gray-400 mt-10">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="w-6 h-6 mr-2">
                                        <path
                                            d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12 16.5a.75.75 0 0 1 0-1.5h.008a.75.75 0 1 1 0 1.5H12ZM12 6a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 12 6Z" />
                                    </svg>
                                    <span>Status Tidak Diketahui</span>
                                </div>
                            @endif

                            <!-- Tombol Aksi -->
                            <div class="mt-4" style="position: absolute; bottom: 15px; right: 15px;">
                                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-xl"
                                    onclick="openModal('showTaskModal_{{ $task->id }}')">
                                    Lihat detail
                                </button>
                                @php
                                    $status = $task->collections->first()->status ?? 'default';
                                @endphp
                                @if ($status === 'Belum mengumpulkan')
                                    <button
                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-xl"
                                        onclick="openModal('tugasModal-{{ $task->id }}')">
                                        Pengumpulan Tugas
                                    </button>
                                @endif
                            </div>

                            <!-- Tanggal di kanan atas -->
                            <div class="absolute top-5 right-5 text-gray-600 font-semibold text-sm">
                                <span class="text-danger">Deadline </span>
                                {{ \Carbon\Carbon::parse($task->date_collection)->translatedFormat('H:i l, j F Y') }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex items-center justify-center h-screen">
                        <div class="text-center">
                            <div class="text-red-500 mb-5" style="justify-self: center">
                                <img src="/image/Gelembung.svg" alt="">
                            </div>
                            <p class="text-gray-700 text-3xl font-semibold">Belum Ada Tugas</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Show Task --}}
            @foreach ($tasks as $task)
                <div id="showTaskModal_{{ $task->id }}"
                    class="taskModal fixed inset-0 hidden items-center justify-center bg-gray-900 bg-opacity-50 z-50 "
                    style="display:none;">
                    <div
                        class="bg-white rounded-lg shadow-lg w-[90%] md:w-[60%] lg:w-[50%] h-auto max-h-[90%] px-5 py-5 overflow-y-auto">
                        {{-- Header Modal --}}
                        <div class="flex justify-between items-center border-b pb-4 mr-3">
                            <h5 class="text-2xl font-bold text-gray-800">Detail Tugas</h5>
                            <button type="button" class="text-gray-700 hover:text-gray"
                                onclick="closeModal('showTaskModal_{{ $task->id }}')">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        {{-- Content Modal --}}
                        <div class="mt-4 mb-3 space-y-4 overflow-y-auto h-auto ">
                            <div class="flex space-x-2">
                                <h6 class="text-lg font-semibold text-gray-700">Judul:</h6>
                                <p class="text-gray-600">{{ $task->title_task }}</p>
                            </div>
                            <div class="flex space-x-2">
                                <h6 class="text-lg font-semibold text-gray-700">Materi:</h6>
                                <p class="text-gray-600">{{ $task->Materi->title_materi }}</p>
                            </div>
                            <div class="flex space-x-2">
                                <h6 class="text-lg font-semibold text-gray-700">Tanggal Pengumpulan:</h6>
                                <p class="text-gray-700">
                                    {{ \Carbon\Carbon::parse($task->created_at)->translatedFormat('l, j F Y') }}
                                </p>
                            </div>
                            <div class="mr-8">
                                <h6 class="text-lg font-semibold text-gray-700">Deskripsi:</h6>
                                <p class="text-gray-600">{{ $task->description_task }}</p>
                            </div>
                            <div class="mr-8" style="overflow: hidden;">
                                <h6 class="text-lg font-semibold text-gray-700 mb-3">File Tugas</h6>
                                @php
                                    $file = pathinfo($task->file_task, PATHINFO_EXTENSION);
                                @endphp
                                @if (in_array($file, ['jpg', 'png']))
                                    <img src="{{ asset('storage/' . $task->file_task) }}" alt="File Image"
                                        class="mx-auto w-[100%] h-auto border-2 rounded-lg">
                                @elseif($file === 'pdf')
                                    <a href="{{ Storage::url($task->file_task) }}" target="_black"
                                        class="w-[120px] h-[43px] p-2 border-2 text-white bg-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-700 transition">
                                        Buka Tugas
                                    </a>
                                @else
                                    <p class="text-red-500">Tidak Ada</p>
                                @endif
                            </div>
                        </div>
                        <div class="justify-end flex mr-10">
                            <button type="button" class="bg-gray-400 font-semibold text-white rounded-lg py-2 px-4"
                                onclick="closeModal('showTaskModal_{{ $task->id }}')">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
            {{-- Collection --}}
            @foreach ($tasks as $task)
                <div id="tugasModal-{{ $task->id }}"
                    class="tugasModal fixed inset-0 hidden items-center justify-center bg-gray-900 bg-opacity-50 z-50 "
                    style="display: none;">
                    <div class="bg-white rounded-lg px-7 py-5 w-[40%] h-auto shadow-lg">
                        <h5 class="text-xl font-bold mb-4">Pengumpulan Tugas</h5>

                        <button class="absolute top-2 right-2 text-gray-600 hover:text-gray-700"
                            onclick="closeModal('tugasModal-{{ $task->id }}')">
                            &times;
                        </button>

                        <form id="form-{{ $task->id }}"
                            action="{{ route('updateCollection', ['task_id' => $task->id]) }}" method="POST"
                            enctype="multipart/form-data" class="overflow-y-auto h-[56%]">
                            @csrf
                            @method('PUT')

                            <div class="mb-5">
                                <label class="text-gray-700 block font-medium mb-3">Upload File (PDF atau Gambar)</label>
                                <div class="relative border-2 rounded-xl  border-gray-300">
                                    <input type="file" id="file_collection-{{ $task->id }}"
                                        name="file_collection" class="hidden" accept=".pdf,image/*"
                                        onchange="updateFileName(this)">
                                    <label for="file_collection-{{ $task->id }}"
                                        class="bg-blue-500 text-white px-4 py-2 rounded cursor-pointer inline-block">
                                        Pilih File
                                    </label>
                                    <span id="file-name-{{ $task->id }}" class="ml-2 text-gray-600 mt-3">Tidak ada
                                        file yang dipilih</span>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-2">
                                <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded"
                                    onclick="closeModal('tugasModal-{{ $task->id }}')">Batal</button>
                                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Unggah
                                    Tugas</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
            <div class="px-5 py-3">
                {{ $tasks->links() }}
            </div>
        </div>
    </div>


    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="https://class.hummatech.com/user-assets/js/scripts.bundle.js"></script>
    {{-- script Modal --}}
    <script>
        function openModal(modalId) {
            // Tutup semua modal yang terbuka
            const modals = document.querySelectorAll('.tugasModal');
            modals.forEach(modal => modal.classList.add('hidden'));

            // Tampilkan modal yang diinginkan
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
            } else {
                console.error(`Modal dengan ID ${modalId} tidak ditemukan.`);
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
            } else {
                console.error(`Modal dengan ID ${modalId} tidak ditemukan.`);
            }
        }

        function updateFileName(input) {
            const fileNameSpan = document.getElementById(`file-name-${input.id.split('-')[1]}`);
            const fileName = input.files[0]?.name || 'Tidak ada file yang dipilih';
            fileNameSpan.textContent = fileName;
        }

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'flex'; // Tampilkan modal
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none'; // Sembunyikan modal
            }
        }
    </script>
    {{-- end script modal --}}

    {{-- tab content script --}}
    <script defer>
        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('.tab-button');
            const contents = document.querySelectorAll('.tab-content');
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || 'materi'; // Default tab "materi"
            const tabInput = document.getElementById('activeTabInput'); // Pastikan elemen ini ada di HTML

            // Tampilkan tab dan filter awal
            showTab(activeTab);
            showFilter(activeTab);

            // Sembunyikan loading screen setelah tab diatur
            const loadingScreen = document.getElementById('loadingScreen');
            if (loadingScreen) {
                loadingScreen.classList.add('hidden');
            }

            // Event listener untuk klik pada tab
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const selectedTab = tab.id.replace('tab-', '');
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', selectedTab);
                    window.history.pushState({}, '', url);
                    showTab(selectedTab);
                    showFilter(selectedTab);
                });
            });

            // Fungsi untuk menampilkan tab aktif
            function showTab(activeTab) {
                // Perbarui tab aktif di input hidden
                if (tabInput) tabInput.value = activeTab;

                // Perbarui UI tab dan konten
                tabs.forEach(t => {
                    t.classList.remove('active-tab', 'bg-blue-800', 'text-white');
                    t.classList.add('bg-white', 'text-blue-800');
                });

                const targetTab = document.getElementById('tab-' + activeTab);
                const targetContent = document.getElementById('content-' + activeTab);

                if (targetTab) {
                    targetTab.classList.remove('bg-white', 'text-blue-800');
                    targetTab.classList.add('active-tab', 'bg-blue-800', 'text-white');
                }

                contents.forEach(content => content.classList.add('hidden'));
                if (targetContent) targetContent.classList.remove('hidden');
            }

            // Fungsi untuk menampilkan filter berdasarkan tab aktif
            function showFilter(tab) {
                const filterUrutan = document.getElementById('filter-urutan');
                const filterDropdownContainer = document.getElementById('filter-dropdown-container');

                // Sembunyikan semua filter
                if (filterUrutan) filterUrutan.classList.add('hidden');
                if (filterDropdownContainer) filterDropdownContainer.classList.add('hidden');

                // Tampilkan filter sesuai tab
                if (tab === 'materi') {
                    if (filterUrutan) filterUrutan.classList.remove('hidden');
                } else if (tab === 'tugas') {
                    if (filterDropdownContainer) filterDropdownContainer.classList.remove('hidden');
                }
            }

            // Event listener untuk filter dropdown
            const filterButton = document.getElementById('filterButton');
            if (filterButton) {
                filterButton.addEventListener('click', () => {
                    const filterDropdown = document.getElementById('filterDropdown');
                    if (filterDropdown) filterDropdown.classList.toggle('hidden');
                });
            }

            // Event listener untuk filter urutan
            const filterUrutan = document.getElementById('filter-urutan');
            if (filterUrutan && tabInput) {
                filterUrutan.addEventListener('submit', () => {
                    tabInput.value = 'materi';
                })
            }
        });
    </script>

    <script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015"
        integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ=="
        data-cf-beacon='{"rayId":"8f0bc3aff833fd88","version":"2024.10.5","r":1,"token":"a20ac1c0d36b4fa6865d9d244f4efe5a","serverTiming":{"name":{"cfExtPri":true,"cfL4":true,"cfSpeedBrain":true,"cfCacheStatus":true}}}'
        crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    {{-- npm flowbite --}}

    <script src="https://cdn.tailwindcss.com"></script>
@endsection
