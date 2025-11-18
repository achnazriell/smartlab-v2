@extends('layouts.app')
@section('content')

    {{-- Script Select2 --}}
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Modern teacher management page with improved styling -->
    <div class="p-6 space-y-6">
        <!-- Page header with modern design -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 font-poppins">Manajemen Guru</h1>
                <p class="text-slate-600 mt-1">Kelola data guru dan penempatan kelas</p>
            </div>

            <!-- Modern search button -->
            <div class="relative">
                <button id="searchButton"
                    class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5A6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5S14 7.01 14 9.5S11.99 14 9.5 14" />
                    </svg>
                    <span class="hidden sm:inline">Cari Guru</span>
                </button>

                <!-- Modern search form -->
                <form id="searchForm" action="{{ route('teachers.index') }}" method="GET"
                    class="absolute right-0 top-full mt-2 transition-all duration-300 {{ request('search') ? 'opacity-100 visible' : 'opacity-0 invisible' }}">
                    <div class="bg-white rounded-lg shadow-lg border border-slate-200 p-4 min-w-[300px]">
                        <div class="flex space-x-2">
                            <input type="text" name="search_teacher" placeholder="Cari nama guru..."
                                value="{{ old('search', request('search')) }}"
                                class="flex-1 px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm">
                                Cari
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Alert messages with modern styling -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg relative" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <strong class="font-medium">{{ session('success') }}</strong>
                </div>
                <button onclick="this.parentElement.style.display='none'"
                    class="absolute top-0 bottom-0 right-0 px-4 py-3 text-green-600 hover:text-green-800">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg relative" role="alert">
                <div class="flex items-start">
                    <svg class="w-5 h-5 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <strong class="font-medium">Kesalahan Validasi:</strong>
                        <ul class="list-disc ml-5 mt-2 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button onclick="this.parentElement.style.display='none'"
                    class="absolute top-0 bottom-0 right-0 px-4 py-3 text-red-600 hover:text-red-800">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <!-- Modern data table -->
        <div class="bg-white rounded-xl card-shadow-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900 font-poppins">Daftar Guru</h2>
                <p class="text-sm text-slate-600 mt-1">Total: {{ $teachers->total() }} guru</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">No
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Nama
                                Guru</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">NIP
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Mata
                                Pelajaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @php
                            $offset = ($teachers->currentPage() - 1) * $teachers->perPage();
                        @endphp
                        @foreach ($teachers as $teacher)
                            <tr class="hover:bg-slate-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                    {{ $loop->iteration + $offset }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            <span
                                                class="text-blue-600 font-medium text-sm">{{ substr($teacher->name, 0, 1) }}</span>
                                        </div>
                                        <div class="text-sm font-medium text-slate-900">{{ $teacher->name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $teacher->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-mono">
                                    {{ $teacher->NIP }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    @if ($teacher->class->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($teacher->class->take(2) as $class)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $class->name_class }}
                                                </span>
                                            @endforeach
                                            @if ($teacher->class->count() > 2)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                                    +{{ $teacher->class->count() - 2 }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                            Belum ditempatkan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                    @if ($teacher->subject)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $teacher->subject->name_subject }}
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                            Belum ditentukan
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                                        onclick="openModal('assignModal-{{ $teacher->id }}')">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                        Atur Penempatan
                                    </button>
                                </td>
                            </tr>

                            <!-- Modern modal design -->
                            <div id="assignModal-{{ $teacher->id }}"
                                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                                style="display: none">
                                <div
                                    class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
                                    <div class="px-6 py-4 border-b border-slate-200">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-lg font-semibold text-slate-900 font-poppins">Atur Penempatan
                                                Guru</h3>
                                            <button onclick="closeModal('assignModal-{{ $teacher->id }}')"
                                                class="text-slate-400 hover:text-slate-600 transition-colors duration-200">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <p class="text-sm text-slate-600 mt-1">{{ $teacher->name }}</p>
                                    </div>

                                    <form action="{{ route('teacher.updateAssign', $teacher->id) }}" method="POST">
                                        @method('PUT')
                                        @csrf
                                        <div class="p-5 space-y-6">
                                            <div class="space-y-2">
                                                <label class="block text-sm font-medium text-slate-700">Kelas</label>
                                                <select name="classes_id[]" id="classes_id-{{ $teacher->id }}"
                                                    class="classes w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('classes_id') border-red-300 @enderror"
                                                    multiple="multiple">
                                                    @foreach ($classes as $class)
                                                        <option value="{{ $class->id }}"
                                                            {{ in_array($class->id, old('classes_id', $teacher->class->pluck('id')->toArray())) ? 'selected' : '' }}>
                                                            {{ $class->name_class }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('classes_id')
                                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div class="space-y-2">
                                                <label class="block text-sm font-medium text-slate-700">Mata
                                                    Pelajaran</label>
                                                <select name="subject_id" id="subject_id-{{ $teacher->id }}"
                                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                                    <option value="" disabled selected>Pilih Mata Pelajaran</option>
                                                    @foreach ($subjects as $mapel)
                                                        <option value="{{ $mapel->id }}"
                                                            {{ old('subject_id', $teacher->subject_id) == $mapel->id ? 'selected' : '' }}>
                                                            {{ $mapel->name_subject }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="flex justify-end space-x-3 pt-4 border-t border-slate-200">
                                                <button type="button"
                                                    onclick="closeModal('assignModal-{{ $teacher->id }}')"
                                                    class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                                    Batal
                                                </button>
                                                <button type="submit"
                                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                                    Simpan Perubahan
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Modern pagination -->
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50">
                {{ $teachers->links('vendor.pagination.tailwind') }}
            </div>
        </div>
    </div>


    <script>
        function openModal(id) {
            document.getElementById(id).style.display = 'flex';
            // Reset select2 values
            $(`#${id} .classes`).val([]).trigger('change');
            $(`#${id} select[name="subject_id"]`).val('').trigger('change');
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        $(document).ready(function() {
            // Initialize Select2 for classes
            $('.classes').select2({
                placeholder: "Pilih Kelas",
                width: '100%',
                allowClear: true,
                dropdownParent: $('.classes').parent()
            });
        });

        // Toggle search form
        document.getElementById('searchButton').addEventListener('click', function(e) {
            e.preventDefault();
            const form = document.getElementById('searchForm');
            form.classList.toggle('opacity-100');
            form.classList.toggle('visible');
            form.classList.toggle('opacity-0');
            form.classList.toggle('invisible');
        });

        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.fixed.bg-black.bg-opacity-50');
                modals.forEach(modal => modal.style.display = 'none');
            }
        });

        // Close search form when clicking outside
        document.addEventListener('click', function(e) {
            const searchButton = document.getElementById('searchButton');
            const searchForm = document.getElementById('searchForm');

            if (!searchButton.contains(e.target) && !searchForm.contains(e.target)) {
                searchForm.classList.add('opacity-0', 'invisible');
                searchForm.classList.remove('opacity-100', 'visible');
            }
        });
    </script>
@endsection
