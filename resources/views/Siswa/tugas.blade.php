@extends('layouts.appSiswa')

@section('content')
    <style>
        .taskModal {
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
    </style>

    <!--begin::App-->
    <div class="container p-10">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="loader border-t-4 border-blue-600 rounded-full w-16 h-16 animate-spin"></div>
        </div>
        <div class="flex justify-between items-center my-8">
            <h1 class="text-2xl text-gray-700 font-poppins font-bold">
                Daftar Tugas
            </h1>

            <!-- Form pencarian berada di kanan -->
            <div class="flex items-center">
                <form action="{{ route('Tugas') }}" method="GET" class="flex items-center">
                    <input type="text" id="search" name="search" placeholder="Search..."
                        class="rounded-xl border-gray-300 p-3">
                    <!-- Tombol search dengan icon -->
                    <button type="submit"
                        class="ml-2 bg-blue-500 hover:bg-blue-700 text-white font-bold p-3 px-4 rounded-xl">
                        <i class="fas fa-search text-white"></i>
                    </button>
                </form>

                <!-- Dropdown Filter -->
                <div class="ml-4 relative">
                    <button id="filterButton"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold p-3 px-4 rounded-xl shadow-md">
                        <i class="fas fa-filter text-white"></i>
                    </button>
                    <div id="filterDropdown"
                        class="hidden absolute right-0 mt-5 w-72 bg-white border border-gray-300 rounded-xl pl-1 pb-2   shadow-lg z-50">
                        <!-- Filter Header -->
                        <div class="px-4 py-3 text-lg font-semibold text-gray-700 border-b border-gray-300">
                            Pilih Status Tugas
                        </div>
                        <!-- Filter Dropdown Options -->
                        <form method="GET" action="{{ route('Tugas') }}">
                            @csrf
                            <!-- Tombol untuk filter status -->
                            <button type="submit" name="status" value="Sudah mengumpulkan"
                                class="flex items-center justify-center px-4 py-2 text-green-800 bg-green-300 rounded-xl m-2 w-64 h-12"
                                style="a"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                    fill="currentColor" class="w-6 h-6 mr-2">
                                    <path fill-rule="evenodd"
                                        d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z"
                                        clip-rule="evenodd" />
                                </svg>
                                Sudah Mengumpulkan
                            </button>
                            <button type="submit" name="status" value="Belum mengumpulkan"
                                class="flex items-center justify-center px-4 py-2 text-yellow-600 bg-yellow-100 rounded-xl m-2 w-64 h-12"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="w-6 h-6 mr-2">
                                    <path fill-rule="evenodd"
                                        d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z"
                                        clip-rule="evenodd" />
                                </svg>
                                Belum Mengumpulkan
                            </button>
                            <button type="submit" name="status" value="Tidak mengumpulkan"
                                class="flex items-center justify-center px-4 py-2 text-red-800 bg-red-300 rounded-xl m-2 w-64 h-12"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                    class="w-6 h-6 mr-2">
                                    <path fill-rule="evenodd"
                                        d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z"
                                        clip-rule="evenodd" />
                                </svg>
                                Tidak Mengumpulkan
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">{{ session('success') }}</strong>
                <button onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3">
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
                <button onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if (auth()->user()->student && auth()->user()->student->class)
            <div class="space-y-6">
                @forelse ($tasks as $task)
                    <div style="position: relative;">
                        <div class="bg-white shadow-md py-10 px-5" style="border-radius: 15px;">
                            @foreach ($task->collections as $collection)
                                <p class="text-gray-600" style="margin-right: 150px; margin-bottom: 5px">Nilai:
                                    {{ $collection->assessment && $collection->assessment->mark_task !== null ? $collection->assessment->mark_task : 'Belum Dinilai' }}
                                </p>
                            @endforeach
                            <h2 class="text-xl font-bold mb-2">{{ $task->title_task }}</h2>
                            <p class="text-gray-600" style="margin-right: 150px">
                                Mapel : {{ $task->Subject->name_subject }}
                            </p>

                            <!-- Status Sudah Dikerjakan dengan ikon -->
                            @php
                                $status = $task->collection_status ?? 'Belum mengumpulkan';
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
                                    $status = $task->collection_status ?? 'Belum mengumpulkan';
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
            @else
                <div class="flex items-center justify-center h-screen">
                    <div class="text-center">
                        <div class="text-red-500 mb-5" style="justify-self: center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-28 h-28">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <p class="text-gray-700 text-3xl font-semibold">Anda Belum Ada Kelas</p>
                    </div>
                </div>
            </div>
        @endif
        @foreach ($tasks as $task)
            <div id="tugasModal-{{ $task->id }}" class="tugasModal fixed inset-0 flex items-center justify-center"
                style="display: none;">
                <div class="bg-white rounded-lg px-7 py-5 w-[40%] h-auto shadow-lg">
                    <h5 class="text-xl font-bold mb-4">Pengumpulan Tugas</h5>

                    <button class="absolute top-2 right-2 text-gray-500 hover:text-gray-700"
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
                                <input type="file" id="file_collection-{{ $task->id }}" name="file_collection"
                                    class="hidden" accept=".pdf,image/*" onchange="updateFileName(this)">
                                <label for="file_collection-{{ $task->id }}"
                                    class="bg-blue-500 text-white px-4 py-2 rounded cursor-pointer inline-block">
                                    Pilih File
                                </label>
                                <span id="file-name-{{ $task->id }}" class="ml-2 text-gray-500 mt-3">Tidak ada
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
                                <embed src="{{ asset('storage/' . $task->file_task) }}" type="application/pdf"
                                    class="border-2 rounded-lg " style="height: 165vh; width: 100%; display: block;">
                            @else
                                <p class="text-red-500">Format file tidak didukung.</p>
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
        <div class="px-5 py-3">
            {{ $tasks->links() }}
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
            </span>
            <!--end::Svg Icon-->
        </div>
    </div>
    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="https://class.hummatech.com/user-assets/js/scripts.bundle.js"></script>
    <script>
        const loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen) {
            loadingScreen.classList.add('hidden');
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

    <script>
        var options = {
            series: [44, 55, 41, 17, 15],
            chart: {
                type: 'donut',
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };

        var chart = new ApexCharts(document.querySelector("#kt_attendance"), options);
        chart.render();
    </script>
    <!--end::Javascript-->
    <script>
        $('.notification-link').click(function(e) {
            $.ajax({
                url: '/delete-notification/' + e.target.id,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                // success: function(response) {
                //     // Redirect ke halaman tujuan setelah penghapusan berhasil
                //     window.location.href = $(this).attr('href');
                // },
                error: function(xhr) {
                    // Tangani kesalahan jika terjadi
                    console.error(xhr.responseText);
                }
            });
        })
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const filterButton = document.getElementById('filterButton');
            const filterDropdown = document.getElementById('filterDropdown');

            filterButton.addEventListener('click', (event) => {
                event.stopPropagation(); // Mencegah event bubbling
                filterDropdown.classList.toggle('hidden');
            });

            // Tutup dropdown jika klik di luar dropdown
            document.addEventListener('click', () => {
                filterDropdown.classList.add('hidden');
            });
        });
    </script>

    <script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015"
        integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ=="
        data-cf-beacon='{"rayId":"8f0bc3aff833fd88","version":"2024.10.5","r":1,"token":"a20ac1c0d36b4fa6865d9d244f4efe5a","serverTiming":{"name":{"cfExtPri":true,"cfL4":true,"cfSpeedBrain":true,"cfCacheStatus":true}}}'
        crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>

@endsection
