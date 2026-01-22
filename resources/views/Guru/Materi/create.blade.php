@extends('layouts.appTeacher')

@section('content')
    <div class="space-y-6">
        <!-- Back button yang lebih jelas dan prominent -->
        <div class="flex items-center gap-3 mb-4">
            <a href="{{ route('materis.index') }}"
                class="flex items-center gap-2 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 hover:text-gray-900 rounded-lg transition-all duration-200 font-medium text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali</span>
            </a>
        </div>

        <!-- Header - Warna lebih subtle, biru soft dengan putih -->
        <div class="bg-blue-50 border-l-4 border-blue-400 rounded-xl shadow-sm p-4 md:p-8">
            <div class="min-w-0">
                <h1 class="text-xl md:text-3xl font-bold text-blue-900">Tambah Materi Baru</h1>
                <p class="text-sm md:text-base text-blue-700 mt-1">Buat materi pembelajaran untuk kelas Anda</p>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-4 md:p-8">
            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-4 rounded-lg mb-6">
                    <div class="flex items-start gap-2 mb-2">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="font-semibold">Kesalahan Validasi</span>
                    </div>
                    <ul class="list-disc ml-7 space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('materis.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Nama Materi (Full Width) -->
                <div>
                    <label for="title_materi" class="block text-sm font-semibold text-gray-900 mb-2.5">
                        Nama Materi <span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="title_materi" name="title_materi"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm"
                        value="{{ old('title_materi') }}" placeholder="Contoh: Aljabar Dasar" required>
                    @error('title_materi')
                        <span class="text-red-600 text-xs mt-1.5 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Grid: Mapel, Kelas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    <!-- Mapel - responsive sizing -->
                    <!-- Mapel -->
                    <div>
                        <label for="subject_id" class="block text-xs md:text-sm font-semibold text-gray-900 mb-2.5">
                            Mata Pelajaran <span class="text-red-600">*</span>
                        </label>
                        <select name="subject_id" id="subject_id"
                            class="w-full px-3 md:px-4 py-2.5 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-xs md:text-sm bg-white"
                            required>
                            <option value="">Pilih Mapel</option>
                            @foreach ($mapels as $subject)
                                <option value="{{ $subject->id }}">
                                    {{ $subject->name_subject }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                            <span class="text-red-600 text-xs mt-1.5 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Kelas - Improved multi-select with chips, search, and select-all -->
                    <!-- Kelas - Dropdown custom dengan chips untuk pilihan -->
                    <div>
                        <label for="class_id" class="block text-xs md:text-sm font-semibold text-gray-900 mb-2.5">
                            Kelas <span class="text-red-600">*</span>
                        </label>
                        <div class="relative" x-data="{ open: false, search: '' }" @click.away="open = false">
                            <!-- Dropdown Button dengan Selected Chips -->
                            <button type="button" @click="open = !open"
                                class="w-full px-3 md:px-4 py-2.5 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-xs md:text-sm bg-white text-left flex justify-between items-center hover:border-gray-400">
                                <div class="flex flex-wrap gap-1 md:gap-1.5 flex-1 items-center">
                                    <div id="button-chips" class="flex flex-wrap gap-1 md:gap-1.5">
                                        <!-- Chips will be populated by JavaScript -->
                                    </div>
                                    <span id="selected-count" class="text-gray-500 text-xs">Pilih Kelas</span>
                                </div>
                                <svg class="w-4 md:w-5 h-4 md:h-5 text-gray-400 transition-transform flex-shrink-0" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open" x-cloak
                                class="absolute top-full mt-2 w-full bg-white border border-gray-300 rounded-lg shadow-lg z-50 max-h-80 overflow-hidden flex flex-col">
                                <!-- Search Bar -->
                                <div class="sticky top-0 px-3 md:px-4 py-2.5 md:py-3 border-b border-gray-200 bg-white">
                                    <input type="text" x-model="search" placeholder="Cari kelas..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs md:text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <!-- Select All Option -->
                                <div class="px-2 md:px-3 py-2 border-b border-gray-200">
                                    <label class="flex items-center gap-2.5 md:gap-3 px-1.5 md:px-2 py-1 hover:bg-blue-50 rounded-md cursor-pointer transition-colors">
                                        <input type="checkbox" id="select-all-classes"
                                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-xs md:text-sm font-medium text-gray-700">Pilih Semua</span>
                                    </label>
                                </div>

                                <!-- Options List -->
                                <div id="class-options" class="overflow-y-auto flex-1 p-2 space-y-1">
                                    <!-- Will be populated by JavaScript -->
                                </div>
                            </div>
                        </div>

                        <!-- Hidden select for form submission -->
                        <select id="class_id" name="class_id[]" multiple class="hidden" required>
                        </select>
                        @error('class_id')
                            <span class="text-red-600 text-xs mt-1.5 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Deskripsi (Full Width) -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-900 mb-2.5">
                        Deskripsi Materi
                    </label>
                    <textarea id="description" name="description" rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm resize-none"
                        placeholder="Jelaskan ringkasan materi...">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="text-red-600 text-xs mt-1.5 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- File Materi (Full Width) -->
                <div>
                    <label for="file_materi" class="block text-sm font-semibold text-gray-900 mb-2.5">
                        File Materi (PDF) <span class="text-red-600">*</span>
                    </label>
                    <p class="text-xs text-gray-600 mb-3">File harus berformat PDF, maksimal 10MB</p>
                    <input type="file" id="file_materi" name="file_materi" accept=".pdf"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 cursor-pointer"
                        required>
                    @error('file_materi')
                        <span class="text-red-600 text-xs mt-1.5 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="pt-6 border-t border-gray-200">
                    <div class="flex flex-col-reverse md:flex-row gap-3">
                        <a href="{{ route('materis.index') }}"
                            class="w-full md:w-auto px-6 py-3 text-center text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors font-medium text-sm">
                            Batal
                        </a>
                        <button type="submit"
                            class="w-full md:w-auto px-6 py-3 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors font-medium text-sm">
                            Tambah Materi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const classSelect = document.getElementById('class_id');
        const subjectSelect = document.getElementById('subject_id');
        const classOptionsDiv = document.getElementById('class-options');
        const selectAllCheckbox = document.getElementById('select-all-classes');
        const buttonChipsDiv = document.getElementById('button-chips');
        const selectedCountSpan = document.getElementById('selected-count');

        subjectSelect.addEventListener('change', function() {
            let subjectId = this.value;
            classSelect.innerHTML = '';
            classOptionsDiv.innerHTML = '';
            buttonChipsDiv.innerHTML = '';
            selectedCountSpan.textContent = 'Pilih Kelas';
            selectAllCheckbox.checked = false;

            if (!subjectId) {
                classOptionsDiv.innerHTML =
                    '<div class="p-4 text-center text-xs md:text-sm text-gray-500">Pilih mapel terlebih dahulu</div>';
                return;
            }

            fetch(`/guru/subjects/${subjectId}/classes`)
                .then(res => res.json())
                .then(data => {
                    if (!data.length) {
                        classOptionsDiv.innerHTML =
                            '<div class="p-4 text-center text-xs md:text-sm text-gray-500">Kelas tidak tersedia</div>';
                        return;
                    }

                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.name_class;
                        classSelect.appendChild(option);
                    });

                    renderClassOptions();
                })
                .catch(err => {
                    console.error(err);
                    classOptionsDiv.innerHTML =
                        '<div class="p-4 text-center text-xs md:text-sm text-red-500">Gagal memuat kelas</div>';
                });
        });

        function renderClassOptions() {
            classOptionsDiv.innerHTML = '';
            const options = classSelect.querySelectorAll('option');

            if (options.length === 0) {
                classOptionsDiv.innerHTML =
                    '<div class="p-4 text-center text-xs md:text-sm text-gray-500">Pilih mapel terlebih dahulu</div>';
                return;
            }

            options.forEach((option) => {
                if (option.value === '') return;

                const label = document.createElement('label');
                label.className =
                    'flex items-center gap-2.5 md:gap-3 px-2 md:px-3 py-2 md:py-2.5 hover:bg-blue-50 rounded-md cursor-pointer transition-colors class-option';
                label.setAttribute('data-name', option.textContent);

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.value = option.value;
                checkbox.className =
                    'w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 checkbox-class';
                checkbox.addEventListener('change', function() {
                    option.selected = this.checked;
                    updateButtonChips();
                    updateSelectAllState();
                });

                const text = document.createElement('span');
                text.className = 'text-xs md:text-sm text-gray-700 flex-1';
                text.textContent = option.textContent;

                label.appendChild(checkbox);
                label.appendChild(text);
                classOptionsDiv.appendChild(label);
            });

            setupSearch();
            updateSelectAllState();
        }

        function setupSearch() {
            const searchInput = document.querySelector('input[placeholder="Cari kelas..."]');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const classOptions = document.querySelectorAll('.class-option');
                    classOptions.forEach(option => {
                        const name = option.getAttribute('data-name').toLowerCase();
                        option.style.display = name.includes(searchTerm) ? '' : 'none';
                    });
                });
            }
        }

        function updateButtonChips() {
            const selected = Array.from(classSelect.querySelectorAll('option:checked'));
            const chipsHtml = selected.map(option => {
                return `
                    <span class="inline-flex items-center gap-1 md:gap-1.5 px-2 md:px-2.5 py-1 md:py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                        ${option.textContent}
                        <button type="button" class="remove-chip" data-value="${option.value}" onclick="removeChip(event, '${option.value}')">
                            <svg class="w-3 md:w-3.5 h-3 md:h-3.5 hover:text-blue-900" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </span>
                `;
            }).join('');

            buttonChipsDiv.innerHTML = chipsHtml;
            selectedCountSpan.textContent = selected.length > 0 ? '' : 'Pilih Kelas';
        }

        function updateSelectAllState() {
            const checkboxes = document.querySelectorAll('.checkbox-class');
            const totalVisible = Array.from(document.querySelectorAll('.class-option')).filter(el => el.style.display !== 'none').length;
            const selectedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
            selectAllCheckbox.checked = totalVisible > 0 && selectedCount === totalVisible;
            selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalVisible;
        }

        function removeChip(event, classId) {
            event.preventDefault();
            const option = Array.from(classSelect.querySelectorAll('option')).find(opt => opt.value === classId);
            if (option) {
                option.selected = false;
                const checkbox = document.querySelector(`.checkbox-class[value="${classId}"]`);
                if (checkbox) checkbox.checked = false;
                updateButtonChips();
                updateSelectAllState();
            }
        }

        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            const checkboxes = document.querySelectorAll('.checkbox-class');
            checkboxes.forEach(checkbox => {
                if (document.querySelector(`.class-option[data-name="${checkbox.value}"]`)?.style.display !== 'none') {
                    checkbox.checked = isChecked;
                    const option = classSelect.querySelector(`option[value="${checkbox.value}"]`);
                    if (option) {
                        option.selected = isChecked;
                    }
                }
            });
            updateButtonChips();
        });
    </script>
@endsection
