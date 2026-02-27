@extends('layouts.appSiswa')

@section('content')
    <style>
        .btn-locked { cursor: not-allowed; opacity: 0.65; }
        .file-type-error { display: none; color: #dc2626; font-size: 0.75rem; margin-top: 4px; }
        .file-type-error.show { display: block; }
        .app-modal { display: none; }
        .app-modal.flex { display: flex; }
    </style>

    <div class="min-h-screen bg-slate-50 p-4 sm:p-6 lg:p-8">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="loader border-t-4 border-blue-600 rounded-full w-12 h-12 animate-spin"></div>
        </div>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Daftar Tugas</h1>
                <p class="text-slate-500 text-sm mt-0.5">Semua tugas dari guru kamu</p>
            </div>

            <div class="w-full sm:w-auto flex items-center gap-2">
                <form action="{{ route('Tugas') }}" method="GET" class="flex items-center gap-2 flex-1 sm:flex-none">
                    <div class="relative flex-1 sm:w-60">
                        <input type="text" name="search" placeholder="Cari tugas..." value="{{ request('search') }}"
                            class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white p-2.5 rounded-xl transition-all flex-shrink-0">
                        <i class="fas fa-search text-sm"></i>
                    </button>
                </form>

                <!-- Filter Dropdown -->
                <div class="relative flex-shrink-0" x-data="{ filterOpen: false }">
                    <button @click="filterOpen = !filterOpen"
                        class="bg-slate-700 hover:bg-slate-800 text-white p-2.5 rounded-xl transition-all shadow-sm">
                        <i class="fas fa-filter text-sm"></i>
                    </button>
                    <div x-show="filterOpen" @click.outside="filterOpen = false"
                        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        class="absolute right-0 mt-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden">
                        <div class="px-4 py-3 bg-slate-50 border-b border-slate-100">
                            <p class="text-sm font-bold text-slate-700">Filter Status Tugas</p>
                        </div>
                        <form method="GET" action="{{ route('Tugas') }}" class="p-2">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <button type="submit" name="status" value="Sudah mengumpulkan"
                                class="w-full flex items-center gap-3 px-3 py-2.5 text-sm rounded-xl hover:bg-emerald-50 transition-colors {{ request('status') === 'Sudah mengumpulkan' ? 'bg-emerald-50 text-emerald-700' : 'text-slate-700' }}">
                                <div class="w-5 h-5 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                                </div>
                                <span class="font-medium">Sudah Mengumpulkan</span>
                            </button>
                            <button type="submit" name="status" value="Belum mengumpulkan"
                                class="w-full flex items-center gap-3 px-3 py-2.5 text-sm rounded-xl hover:bg-amber-50 transition-colors {{ request('status') === 'Belum mengumpulkan' ? 'bg-amber-50 text-amber-700' : 'text-slate-700' }}">
                                <div class="w-5 h-5 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <div class="w-2 h-2 bg-amber-400 rounded-full"></div>
                                </div>
                                <span class="font-medium">Belum Mengumpulkan</span>
                            </button>
                            <button type="submit" name="status" value="Tidak mengumpulkan"
                                class="w-full flex items-center gap-3 px-3 py-2.5 text-sm rounded-xl hover:bg-red-50 transition-colors {{ request('status') === 'Tidak mengumpulkan' ? 'bg-red-50 text-red-700' : 'text-slate-700' }}">
                                <div class="w-5 h-5 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                </div>
                                <span class="font-medium">Tidak Mengumpulkan</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl mb-4 flex items-center gap-3">
                <i class="fas fa-check-circle text-emerald-500"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-4">
                <p class="text-sm font-bold mb-1">Kesalahan:</p>
                @foreach ($errors->all() as $error)<p class="text-sm">• {{ $error }}</p>@endforeach
            </div>
        @endif

        <!-- Task List -->
        @if($tasks->isNotEmpty())
            <div class="space-y-3 sm:space-y-4">
                @foreach ($tasks as $task)
                    @php
                        $collection = $task->collections->first();
                        $nilai = $collection && $collection->assessment ? $collection->assessment->mark_task : 'Belum Dinilai';
                        $status = $task->collection_status ?? 'Belum mengumpulkan';
                        $hasMateri = $task->materi !== null;
                        $materiId = $hasMateri ? $task->materi->id : null;
                        $materiUrl = $hasMateri ? route('materi.show', $materiId) : null;
                        $materiJudul = $hasMateri ? $task->materi->title_materi : null;

                        $statusConfig = match($status) {
                            'Sudah mengumpulkan' => ['color' => 'emerald', 'icon' => 'fa-check-circle', 'label' => 'Sudah Mengumpulkan'],
                            'Tidak mengumpulkan' => ['color' => 'red', 'icon' => 'fa-times-circle', 'label' => 'Tidak Mengumpulkan'],
                            default => ['color' => 'amber', 'icon' => 'fa-clock', 'label' => 'Belum Mengumpulkan'],
                        };
                    @endphp

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md hover:border-slate-200 transition-all overflow-hidden">
                        <div class="flex items-start gap-0">
                            <!-- Status bar -->
                            <div class="w-1 self-stretch flex-shrink-0 rounded-l-2xl
                                {{ $status === 'Sudah mengumpulkan' ? 'bg-emerald-400' : ($status === 'Tidak mengumpulkan' ? 'bg-red-400' : 'bg-amber-400') }}">
                            </div>

                            <div class="flex-1 p-4 sm:p-5">
                                <!-- Top row: date + status -->
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                    <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                        <i class="fas fa-calendar-alt text-slate-400"></i>
                                        <span class="text-red-500 font-semibold">Deadline:</span>
                                        {{ \Carbon\Carbon::parse($task->date_collection)->translatedFormat('d F Y, H:i') }}
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full
                                        {{ $status === 'Sudah mengumpulkan' ? 'bg-emerald-50 text-emerald-700' : ($status === 'Tidak mengumpulkan' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700') }}">
                                        <i class="fas {{ $statusConfig['icon'] }} text-xs"></i>
                                        {{ $statusConfig['label'] }}
                                    </span>
                                </div>

                                <!-- Nilai -->
                                <p class="text-xs text-slate-500 mb-1">
                                    Nilai: <span class="font-semibold text-slate-700">{{ $nilai }}</span>
                                </p>

                                <!-- Title + Mapel -->
                                <h2 class="text-base sm:text-lg font-bold text-slate-900 leading-tight mb-1">
                                    {{ $task->title_task }}
                                </h2>
                                <p class="text-sm text-slate-500 mb-3">{{ $task->subject->name_subject ?? '-' }}</p>

                                <!-- Materi lock badges -->
                                @if ($hasMateri && $status === 'Belum mengumpulkan')
                                    <div id="materi-lock-badge-{{ $task->id }}"
                                        class="inline-flex items-center gap-1.5 mb-3 px-3 py-1.5 rounded-xl text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fas fa-lock text-amber-500"></i>
                                        <span>Baca materi dulu: <strong>{{ $materiJudul }}</strong></span>
                                    </div>
                                    <div id="materi-unlocked-badge-{{ $task->id }}"
                                        class="hidden inline-flex items-center gap-1.5 mb-3 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-lock-open text-emerald-500"></i>
                                        <span>Materi sudah dibaca — tugas terbuka</span>
                                    </div>
                                @endif

                                <!-- Action Buttons -->
                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                    <button class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl text-sm transition-all active:scale-95"
                                        onclick="openModal('showTaskModal_{{ $task->id }}')">
                                        <i class="fas fa-eye text-xs"></i>
                                        Lihat Detail
                                    </button>

                                    @if ($status === 'Belum mengumpulkan')
                                        @if ($hasMateri)
                                            <button id="btn-kumpul-locked-{{ $task->id }}"
                                                class="btn-locked inline-flex items-center gap-2 bg-slate-300 text-slate-500 font-semibold py-2 px-4 rounded-xl text-sm"
                                                onclick="showMateriLockAlert({{ $materiId }}, '{{ addslashes($materiUrl) }}', '{{ addslashes($materiJudul) }}')"
                                                title="Baca materi terlebih dahulu">
                                                <i class="fas fa-lock text-xs"></i>
                                                Kumpulkan
                                            </button>
                                            <button id="btn-kumpul-unlocked-{{ $task->id }}"
                                                class="hidden inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-xl text-sm transition-all active:scale-95"
                                                onclick="openModal('tugasModal-{{ $task->id }}')">
                                                <i class="fas fa-upload text-xs"></i>
                                                Kumpulkan
                                            </button>
                                        @else
                                            <button class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-xl text-sm transition-all active:scale-95"
                                                onclick="openModal('tugasModal-{{ $task->id }}')">
                                                <i class="fas fa-upload text-xs"></i>
                                                Kumpulkan
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 py-16 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-slate-100 rounded-2xl mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-slate-700 font-semibold">Tidak Ada Tugas</p>
                <p class="text-slate-400 text-sm mt-1">Belum ada tugas untuk kelas Anda.</p>
            </div>
        @endif

        <div class="px-1 py-4">{{ $tasks->links() }}</div>
    </div>

    {{-- ===== MODALS ===== --}}
    @foreach ($tasks as $task)
        @php $status = $task->collection_status ?? 'Belum mengumpulkan'; @endphp

        {{-- Modal Pengumpulan --}}
        @if ($status === 'Belum mengumpulkan')
        <div id="tugasModal-{{ $task->id }}"
            class="app-modal fixed inset-0 items-center justify-center bg-slate-900/60 backdrop-blur-sm z-50 p-4">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
                <div class="flex justify-between items-center p-5 border-b border-slate-100">
                    <h5 class="text-lg font-bold text-slate-900">Pengumpulan Tugas</h5>
                    <button class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-100 text-slate-500 transition-colors"
                        onclick="closeModal('tugasModal-{{ $task->id }}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="{{ route('updateCollection', ['task_id' => $task->id]) }}" method="POST"
                    enctype="multipart/form-data" onsubmit="return validateFileBeforeSubmit(this)" class="p-5">
                    @csrf @method('PUT')
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Upload File <span class="text-slate-400 font-normal">(PDF, JPG, atau PNG)</span>
                        </label>
                        <div class="border-2 border-dashed border-slate-200 rounded-xl p-4 bg-slate-50 hover:border-blue-300 transition-colors">
                            <input type="file" id="file_collection-{{ $task->id }}" name="file_collection" class="hidden"
                                accept=".pdf,.jpg,.jpeg,.png" onchange="handleFileSelect(this, '{{ $task->id }}')">
                            <label for="file_collection-{{ $task->id }}"
                                class="flex flex-col items-center gap-2 cursor-pointer">
                                <i class="fas fa-cloud-upload-alt text-blue-400 text-2xl"></i>
                                <span class="text-sm text-slate-600">Klik untuk pilih file</span>
                                <span id="file-name-{{ $task->id }}" class="text-xs text-slate-400">Belum ada file dipilih</span>
                            </label>
                        </div>
                        <p id="file-error-{{ $task->id }}" class="file-type-error">⚠ Hanya file PDF, JPG, atau PNG yang diizinkan.</p>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors"
                            onclick="closeModal('tugasModal-{{ $task->id }}')">Batal</button>
                        <button type="submit" id="submit-btn-{{ $task->id }}"
                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors">
                            <i class="fas fa-upload mr-1.5 text-xs"></i>Unggah Tugas
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Modal Detail --}}
        <div id="showTaskModal_{{ $task->id }}"
            class="app-modal fixed inset-0 items-center justify-center bg-slate-900/60 backdrop-blur-sm z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
                <div class="flex justify-between items-center p-5 border-b border-slate-100 flex-shrink-0">
                    <h5 class="text-lg font-bold text-slate-900">Detail Tugas</h5>
                    <button class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-slate-100 text-slate-500 transition-colors"
                        onclick="closeModal('showTaskModal_{{ $task->id }}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="overflow-y-auto flex-1 p-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1">Judul</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $task->title_task }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3">
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1">Deadline</p>
                            <p class="text-sm font-semibold text-slate-800">{{ \Carbon\Carbon::parse($task->date_collection)->translatedFormat('H:i, d F Y') }}</p>
                        </div>
                    </div>
                    @if ($task->materi)
                    <div class="bg-blue-50 rounded-xl p-3">
                        <p class="text-xs text-blue-600 font-medium uppercase tracking-wider mb-1">Materi Terkait</p>
                        <a href="{{ route('materi.show', $task->materi->id) }}" class="text-sm font-semibold text-blue-700 hover:underline flex items-center gap-1">
                            {{ $task->materi->title_materi }}
                            <i class="fas fa-external-link-alt text-xs"></i>
                        </a>
                    </div>
                    @endif
                    @if($task->description_task)
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-2">Deskripsi</p>
                        <p class="text-sm text-slate-700 bg-slate-50 rounded-xl p-3 leading-relaxed">{{ $task->description_task }}</p>
                    </div>
                    @endif
                    @php
                        $filePath = $task->file_task ?? null;
                        $fileExt = $filePath ? strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) : null;
                        $fileUrl = $filePath ? asset('storage/' . $filePath) : null;
                    @endphp
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-2">File Tugas</p>
                        @if ($filePath && in_array($fileExt, ['jpg','jpeg','png']))
                            <img src="{{ $fileUrl }}" alt="File Tugas" class="w-full h-auto rounded-xl border border-slate-200">
                        @elseif ($filePath && $fileExt === 'pdf')
                            <a href="{{ $fileUrl }}" target="_blank"
                                class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-blue-700 transition-colors">
                                <i class="fas fa-file-pdf"></i> Buka PDF
                            </a>
                        @elseif ($filePath)
                            <a href="{{ $fileUrl }}" target="_blank"
                                class="inline-flex items-center gap-2 bg-slate-600 text-white text-sm font-semibold px-4 py-2.5 rounded-xl hover:bg-slate-700 transition-colors">
                                <i class="fas fa-paperclip"></i> Download File
                            </a>
                        @else
                            <p class="text-slate-400 text-sm bg-slate-50 rounded-xl p-3 border border-slate-100">Tidak ada file dilampirkan.</p>
                        @endif
                    </div>
                </div>
                <div class="p-4 border-t border-slate-100 flex justify-end flex-shrink-0">
                    <button class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors"
                        onclick="closeModal('showTaskModal_{{ $task->id }}')">Tutup</button>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Toast --}}
    <div id="materi-lock-toast"
        class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-[200] bg-amber-700 text-white px-5 py-4 rounded-2xl shadow-2xl max-w-sm w-[90%] text-center">
        <p class="font-semibold text-sm" id="materi-lock-toast-msg"></p>
        <a id="materi-lock-toast-link" href="#"
            class="mt-2 inline-block bg-white text-amber-700 font-bold text-xs px-4 py-1.5 rounded-lg hover:bg-amber-50 transition">
            Buka Materi Sekarang
        </a>
    </div>

    <script>
        const loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen) loadingScreen.classList.add('hidden');

        function openModal(modalId) {
            document.querySelectorAll('.app-modal').forEach(m => m.style.display = 'none');
            const modal = document.getElementById(modalId);
            if (modal) { modal.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
        }
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) { modal.style.display = 'none'; document.body.style.overflow = 'auto'; }
        }

        const ALLOWED_EXTENSIONS = ['pdf','jpg','jpeg','png'];
        function handleFileSelect(input, taskId) {
            const file = input.files[0];
            const nameSpan = document.getElementById('file-name-' + taskId);
            const errorEl = document.getElementById('file-error-' + taskId);
            const submitBtn = document.getElementById('submit-btn-' + taskId);
            if (!file) { nameSpan.textContent = 'Belum ada file dipilih'; errorEl.classList.remove('show'); return; }
            const ext = file.name.split('.').pop().toLowerCase();
            if (!ALLOWED_EXTENSIONS.includes(ext)) {
                input.value = ''; nameSpan.textContent = 'Belum ada file dipilih';
                errorEl.classList.add('show'); if (submitBtn) submitBtn.disabled = true;
            } else {
                nameSpan.textContent = file.name; errorEl.classList.remove('show');
                if (submitBtn) submitBtn.disabled = false;
            }
        }
        function validateFileBeforeSubmit(form) {
            const fileInput = form.querySelector('input[type="file"]');
            if (!fileInput || !fileInput.files.length) { alert('Pilih file terlebih dahulu.'); return false; }
            const ext = fileInput.files[0].name.split('.').pop().toLowerCase();
            if (!ALLOWED_EXTENSIONS.includes(ext)) { alert('Hanya PDF, JPG, dan PNG yang diizinkan.'); return false; }
            return true;
        }

        const taskMateriMap = {!! json_encode(
            $tasks->filter(fn($t) => $t->materi !== null && ($t->collection_status ?? 'Belum mengumpulkan') === 'Belum mengumpulkan')
                  ->mapWithKeys(fn($t) => [(string)$t->id => $t->materi->id])
        ) !!};

        function isMateriRead(materiId) { return localStorage.getItem(`materi_read_${materiId}`) === 'done'; }
        function unlockTaskButton(taskId) {
            const locked = document.getElementById(`btn-kumpul-locked-${taskId}`);
            const unlocked = document.getElementById(`btn-kumpul-unlocked-${taskId}`);
            const lockBadge = document.getElementById(`materi-lock-badge-${taskId}`);
            const unlockedBadge = document.getElementById(`materi-unlocked-badge-${taskId}`);
            if (locked) locked.classList.add('hidden');
            if (unlocked) unlocked.classList.remove('hidden');
            if (lockBadge) lockBadge.classList.add('hidden');
            if (unlockedBadge) unlockedBadge.classList.remove('hidden');
        }
        function checkAllLocks() {
            Object.entries(taskMateriMap).forEach(([taskId, materiId]) => {
                if (isMateriRead(materiId)) unlockTaskButton(taskId);
            });
        }
        function showMateriLockAlert(materiId, materiUrl, materiJudul) {
            const toast = document.getElementById('materi-lock-toast');
            document.getElementById('materi-lock-toast-msg').textContent = `Kamu harus membaca materi "${materiJudul}" minimal 5 menit sebelum mengumpulkan tugas ini.`;
            document.getElementById('materi-lock-toast-link').href = materiUrl;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 5000);
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.app-modal').forEach(modal => {
                modal.style.display = 'none';
                modal.addEventListener('click', function(e) { if (e.target === this) closeModal(this.id); });
            });
            checkAllLocks();
        });
    </script>
@endsection
