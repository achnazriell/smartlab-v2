@extends('layouts.appSiswa')

@section('content')
    <div class="min-h-screen bg-slate-50">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="loader border-t-4 border-blue-600 rounded-full w-12 h-12 animate-spin"></div>
        </div>

        <!-- Back Navigation -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-4">
            <a href="{{ route('semuamateri') }}"
                class="inline-flex items-center gap-2 text-slate-500 hover:text-blue-600 font-medium text-sm transition-colors duration-200 group">
                <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Daftar Materi
            </a>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
            <!-- Hero Header -->
            <div class="relative bg-gradient-to-br from-blue-600 via-blue-500 to-blue-400 rounded-2xl sm:rounded-3xl shadow-xl overflow-hidden mb-6">
                <div class="absolute inset-0">
                    <div class="absolute -top-10 -right-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="absolute bottom-0 -left-10 w-40 h-40 bg-blue-800/20 rounded-full blur-xl"></div>
                </div>
                <div class="relative z-10 px-6 sm:px-10 py-8 sm:py-12">
                    <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm text-white text-xs font-semibold px-3 py-1.5 rounded-full mb-4">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0016.5 9h-1.875a1.875 1.875 0 01-1.875-1.875V5.25A3.75 3.75 0 009 1.5H5.625z"/>
                        </svg>
                        Materi Pembelajaran
                    </div>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-white mb-4 leading-tight">
                        {{ $materi->title_materi }}
                    </h1>
                    <div class="flex flex-wrap gap-4 sm:gap-6 text-blue-100 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-book text-xs"></i>
                            </div>
                            <div>
                                <p class="text-blue-200 text-xs">Mata Pelajaran</p>
                                <p class="font-semibold text-white">{{ optional($materi->subject)->name_subject ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-calendar text-xs"></i>
                            </div>
                            <div>
                                <p class="text-blue-200 text-xs">Tanggal Upload</p>
                                <p class="font-semibold text-white">{{ $materi->created_at->translatedFormat('d F Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reading Timer Banner -->
            <div id="reading-banner" class="mb-6">
                <div id="reading-in-progress"
                    class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <div class="flex-shrink-0 bg-amber-100 rounded-xl p-2.5 hidden sm:flex items-center justify-center">
                        <i class="fas fa-clock text-amber-600 text-xl"></i>
                    </div>
                    <div class="flex-1 w-full">
                        <div class="flex items-center gap-2 mb-1">
                            <i class="fas fa-clock text-amber-600 sm:hidden"></i>
                            <p class="text-amber-900 font-bold text-sm sm:text-base">Baca Materi Terlebih Dahulu</p>
                        </div>
                        <p class="text-amber-700 text-xs sm:text-sm">
                            Silakan baca materi minimal <strong>5 menit</strong> sebelum mengerjakan tugas terkait.
                        </p>
                        <div class="mt-3 flex items-center gap-3">
                            <div class="flex-1 bg-amber-200 rounded-full h-2 overflow-hidden">
                                <div id="reading-progress-bar" class="bg-amber-500 h-2 rounded-full transition-all duration-1000" style="width: 0%"></div>
                            </div>
                            <span id="reading-timer-text" class="text-amber-800 font-mono font-bold text-sm whitespace-nowrap">05:00</span>
                        </div>
                    </div>
                </div>

                <div id="reading-done"
                    class="hidden bg-green-50 border-2 border-green-300 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                    <div class="flex-shrink-0 bg-green-100 rounded-xl p-2.5 flex items-center justify-center">
                        <i class="fas fa-circle-check text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-green-900 font-bold text-sm sm:text-base">Materi Sudah Dibaca!</p>
                        <p class="text-green-700 text-xs sm:text-sm mt-0.5">
                            Kamu sudah membaca materi ini dan bisa mengerjakan tugas terkait.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Deskripsi -->
                    @if ($materi->description)
                        <div class="bg-white rounded-2xl p-5 sm:p-7 shadow-sm border border-slate-100">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-file-lines text-white text-sm"></i>
                                </div>
                                <h2 class="text-lg sm:text-xl font-bold text-slate-900">Deskripsi Materi</h2>
                            </div>
                            <p class="text-slate-600 leading-relaxed whitespace-pre-wrap text-sm sm:text-base">
                                {{ $materi->description }}
                            </p>
                        </div>
                    @endif

                    <!-- File Materi — Direct PDF Viewer with Page Pagination -->
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-slate-700 to-slate-600 px-5 sm:px-7 py-4 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-file-pdf text-white text-sm"></i>
                                </div>
                                <h2 class="text-base sm:text-lg font-bold text-white">File Materi</h2>
                            </div>
                            @if ($materi->file_materi)
                                <a href="{{ Storage::url($materi->file_materi) }}" download
                                    class="inline-flex items-center gap-1.5 bg-white/20 hover:bg-white/30 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                                    <i class="fas fa-download text-xs"></i>
                                    <span class="hidden sm:inline">Download</span>
                                </a>
                            @endif
                        </div>

                        @if ($materi->file_materi)
                            {{-- PDF Viewer Container --}}
                            <div class="bg-slate-100 p-3 sm:p-4">
                                <!-- Page Controls -->
                                <div id="pdf-controls"
                                    class="flex items-center justify-between bg-white rounded-xl px-4 py-2.5 mb-3 shadow-sm border border-slate-200">
                                    <button id="pdf-prev"
                                        class="flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-blue-600 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                        disabled>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                        </svg>
                                        <span class="hidden sm:inline">Sebelumnya</span>
                                    </button>

                                    <div class="flex items-center gap-2 text-sm text-slate-700">
                                        <span class="font-medium">Halaman</span>
                                        <span id="pdf-page-display"
                                            class="bg-blue-600 text-white font-bold px-3 py-0.5 rounded-lg min-w-[2.5rem] text-center">1</span>
                                        <span class="font-medium">dari</span>
                                        <span id="pdf-total-pages" class="font-bold text-slate-900">—</span>
                                    </div>

                                    <button id="pdf-next"
                                        class="flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-blue-600 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                                        <span class="hidden sm:inline">Berikutnya</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Canvas Viewer -->
                                <div id="pdf-canvas-wrapper"
                                    class="relative flex items-center justify-center bg-slate-200 rounded-xl overflow-hidden"
                                    style="min-height: 400px;">
                                    <canvas id="pdf-canvas" class="max-w-full rounded-xl shadow-md block"></canvas>
                                    <div id="pdf-loading"
                                        class="absolute inset-0 flex items-center justify-center bg-slate-100 rounded-xl">
                                        <div class="text-center">
                                            <div class="w-10 h-10 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-3"></div>
                                            <p class="text-slate-500 text-sm font-medium">Memuat PDF...</p>
                                        </div>
                                    </div>
                                    <div id="pdf-error" class="hidden absolute inset-0 flex items-center justify-center bg-slate-100 rounded-xl p-6">
                                        <div class="text-center">
                                            <i class="fas fa-exclamation-triangle text-amber-500 text-3xl mb-3"></i>
                                            <p class="text-slate-700 font-semibold mb-1">Gagal memuat PDF</p>
                                            <p class="text-slate-500 text-sm mb-4">Coba buka langsung atau download file.</p>
                                            <a href="{{ Storage::url($materi->file_materi) }}" target="_blank"
                                                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors">
                                                <i class="fas fa-external-link-alt text-xs"></i>
                                                Buka PDF
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mobile quick action -->
                                <div class="mt-3 flex gap-2 sm:hidden">
                                    <a href="{{ Storage::url($materi->file_materi) }}" target="_blank"
                                        class="flex-1 flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                                        <i class="fas fa-external-link-alt text-xs"></i>
                                        Buka Fullscreen
                                    </a>
                                    <a href="{{ Storage::url($materi->file_materi) }}" download
                                        class="flex-1 flex items-center justify-center gap-2 bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold py-2.5 rounded-xl transition-colors">
                                        <i class="fas fa-download text-xs"></i>
                                        Download
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="p-6">
                                <div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-5 flex items-start gap-4">
                                    <i class="fas fa-triangle-exclamation text-amber-500 text-xl flex-shrink-0 mt-0.5"></i>
                                    <div>
                                        <p class="text-amber-900 font-semibold">File Belum Tersedia</p>
                                        <p class="text-amber-700 text-sm mt-1">Guru belum mengunggah file materi untuk halaman ini.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl p-5 sm:p-6 shadow-sm border border-slate-100 lg:sticky lg:top-24">
                        <h3 class="text-base font-bold text-slate-900 mb-5 flex items-center gap-2">
                            <i class="fas fa-circle-info text-blue-500"></i>
                            Informasi
                        </h3>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-book text-blue-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-medium">Mata Pelajaran</p>
                                    <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ optional($materi->subject)->name_subject ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-calendar-check text-green-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-medium">Diupload</p>
                                    <p class="text-sm font-semibold text-slate-800 mt-0.5">{{ $materi->created_at->translatedFormat('d F Y') }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl">
                                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0" id="sidebar-status-icon-wrap">
                                    <i class="fas fa-clock text-amber-600 text-xs" id="sidebar-status-icon"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-medium">Status Membaca</p>
                                    <div id="sidebar-reading-status"
                                        class="inline-flex items-center gap-1.5 mt-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                        <span id="sidebar-reading-label">Belum selesai</span>
                                    </div>
                                </div>
                            </div>

                            @if ($materi->file_materi)
                            <div class="flex items-start gap-3 p-3 bg-slate-50 rounded-xl">
                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-file-pdf text-red-500 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-medium">Format File</p>
                                    <p class="text-sm font-semibold text-slate-800 mt-0.5">PDF Document</p>
                                </div>
                            </div>
                            @endif

                            @if ($materi->file_materi)
                            <a href="{{ Storage::url($materi->file_materi) }}" download
                                class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl transition-colors text-sm">
                                <i class="fas fa-download text-xs"></i>
                                Download PDF
                            </a>
                            @endif

                            <a href="{{ route('semuamateri') }}"
                                class="w-full inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 px-4 rounded-xl transition-colors text-sm">
                                <i class="fas fa-arrow-left text-xs"></i>
                                Kembali ke Daftar Materi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PDF.js from CDN --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.3.136/pdf.min.mjs" type="module"></script>

    <script type="module">
        // PDF.js setup
        const { pdfjsLib } = globalThis;

        // Reading timer
        const MATERI_ID     = {{ $materi->id }};
        const TOTAL_SECONDS = 300;
        const STORAGE_KEY   = `materi_read_${MATERI_ID}`;

        const progressBar   = document.getElementById('reading-progress-bar');
        const timerText     = document.getElementById('reading-timer-text');
        const inProgressEl  = document.getElementById('reading-in-progress');
        const doneEl        = document.getElementById('reading-done');
        const sidebarStatus = document.getElementById('sidebar-reading-status');
        const sidebarLabel  = document.getElementById('sidebar-reading-label');
        const sidebarIconWrap = document.getElementById('sidebar-status-icon-wrap');
        const sidebarIcon   = document.getElementById('sidebar-status-icon');

        function markAsDone() {
            localStorage.setItem(STORAGE_KEY, 'done');
            inProgressEl.classList.add('hidden');
            doneEl.classList.remove('hidden');
            sidebarStatus.className = 'inline-flex items-center gap-1.5 mt-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700';
            sidebarLabel.textContent = 'Sudah selesai';
            sidebarIconWrap.className = 'w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0';
            sidebarIcon.className = 'fas fa-circle-check text-green-600 text-xs';
        }

        function formatTime(seconds) {
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            return `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        }

        if (localStorage.getItem(STORAGE_KEY) === 'done') {
            markAsDone();
        } else {
            let elapsed = 0;
            const timer = setInterval(() => {
                elapsed++;
                const remaining = TOTAL_SECONDS - elapsed;
                const pct = (elapsed / TOTAL_SECONDS) * 100;
                progressBar.style.width = pct + '%';
                timerText.textContent = formatTime(remaining > 0 ? remaining : 0);
                if (elapsed >= TOTAL_SECONDS) { clearInterval(timer); markAsDone(); }
            }, 1000);
        }

        // Hide loading screen
        const loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen) loadingScreen.classList.add('hidden');

        @if ($materi->file_materi)
        // PDF Viewer
        const PDF_URL = "{{ Storage::url($materi->file_materi) }}";

        const canvas       = document.getElementById('pdf-canvas');
        const ctx          = canvas.getContext('2d');
        const loadingEl    = document.getElementById('pdf-loading');
        const errorEl      = document.getElementById('pdf-error');
        const prevBtn      = document.getElementById('pdf-prev');
        const nextBtn      = document.getElementById('pdf-next');
        const pageDisplay  = document.getElementById('pdf-page-display');
        const totalDisplay = document.getElementById('pdf-total-pages');

        let pdfDoc    = null;
        let currentPage = 1;
        let rendering = false;

        // Set PDF.js worker
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.3.136/pdf.worker.min.mjs';

            pdfjsLib.getDocument(PDF_URL).promise.then(doc => {
                pdfDoc = doc;
                totalDisplay.textContent = doc.numPages;
                loadingEl.classList.add('hidden');
                renderPage(1);
            }).catch(err => {
                console.error('PDF load error:', err);
                loadingEl.classList.add('hidden');
                errorEl.classList.remove('hidden');
            });
        } else {
            // Fallback: try again after a tick (module might not be loaded)
            loadingEl.classList.add('hidden');
            errorEl.classList.remove('hidden');
        }

        function renderPage(num) {
            if (!pdfDoc || rendering) return;
            rendering = true;
            loadingEl.style.background = 'rgba(241,245,249,0.8)';
            loadingEl.classList.remove('hidden');

            pdfDoc.getPage(num).then(page => {
                const wrapper = document.getElementById('pdf-canvas-wrapper');
                const maxWidth = wrapper.clientWidth - 24;
                const viewport = page.getViewport({ scale: 1 });
                const scale = Math.min(maxWidth / viewport.width, 2);
                const scaled = page.getViewport({ scale });

                canvas.width  = scaled.width;
                canvas.height = scaled.height;

                page.render({ canvasContext: ctx, viewport: scaled }).promise.then(() => {
                    rendering = false;
                    loadingEl.classList.add('hidden');
                    pageDisplay.textContent = num;
                    prevBtn.disabled = num <= 1;
                    nextBtn.disabled = num >= pdfDoc.numPages;
                });
            }).catch(() => {
                rendering = false;
                loadingEl.classList.add('hidden');
            });
        }

        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) { currentPage--; renderPage(currentPage); }
        });
        nextBtn.addEventListener('click', () => {
            if (pdfDoc && currentPage < pdfDoc.numPages) { currentPage++; renderPage(currentPage); }
        });

        // Keyboard navigation
        document.addEventListener('keydown', e => {
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') nextBtn.click();
            if (e.key === 'ArrowLeft'  || e.key === 'ArrowUp')   prevBtn.click();
        });
        @endif
    </script>
@endsection
