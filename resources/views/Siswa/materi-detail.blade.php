@extends('layouts.appSiswa')

@section('content')
    <div class="min-h-screen bg-gradient-to-b from-slate-50 to-white">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="loader border-t-4 border-blue-600 rounded-full w-16 h-16 animate-spin"></div>
        </div>
        <!-- Back Navigation -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-6">
            <a href="{{ route('semuamateri') }}"
                class="inline-flex items-center gap-2 text-slate-600 hover:text-blue-600 font-medium transition-colors duration-200">
                <i class="fas fa-arrow-left text-lg"></i>
                <span>Kembali ke Daftar Materi</span>
            </a>
        </div>

        <!-- Main Content Container -->
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
            <!-- Modernized header with Smart Lab brand colors (blue #1E90FF and black) -->
            <div class="bg-gradient-to-r from-blue-400 to-blue-300 rounded-3xl shadow-2xl overflow-hidden mb-8">
                <div class="relative px-8 sm:px-10 py-12 sm:py-16">
                    <!-- Added decorative accent elements -->
                    <div class="absolute top-0 right-0 w-40 h-40 bg-blue-400 rounded-full opacity-10 -mr-20 -mt-20"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-white rounded-full opacity-5 -ml-16 -mb-16"></div>

                    <div class="relative z-10">
                        <h1 class="text-4xl sm:text-5xl font-bold text-white mb-4 leading-tight">
                            {{ $materi->title_materi }}
                        </h1>

                        <!-- Improved meta information layout -->
                        <div class="flex flex-wrap gap-8 text-blue-50">
                            <div class="flex items-center gap-3">
                                <div class="bg-white bg-opacity-20 rounded-lg p-2.5">
                                    <i class="fas fa-book text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-xs opacity-75 uppercase tracking-wider">Mata Pelajaran</p>
                                    <p class="font-semibold">{{ optional($materi->subject)->name_subject ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="bg-white bg-opacity-20 rounded-lg p-2.5">
                                    <i class="fas fa-calendar text-lg"></i>
                                </div>
                                <div>
                                    <p class="text-xs opacity-75 uppercase tracking-wider">Tanggal Upload</p>
                                    <p class="font-semibold">{{ $materi->created_at->translatedFormat('d F Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content Area -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Modern description card with updated styling -->
                    @if ($materi->description)
                        <div
                            class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border border-slate-100">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="bg-gradient-to-br from-blue-600 to-blue-500 rounded-xl p-3">
                                    <i class="fas fa-file-lines text-white text-lg"></i>
                                </div>
                                <h2 class="text-2xl font-bold text-slate-900">Deskripsi Materi</h2>
                            </div>
                            <p class="text-slate-700 leading-relaxed whitespace-pre-wrap font-medium">
                                {{ $materi->description }}
                            </p>
                        </div>
                    @endif

                    <!-- Redesigned PDF viewer section with modern styling -->
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-slate-100">
                        <div class="bg-gradient-to-r from-slate-500 to-slate-400 px-8 py-6">
                            <div class="flex items-center gap-3">
                                <div class="bg-white bg-opacity-20 rounded-lg p-2.5">
                                    <i class="fas fa-file-pdf text-white text-lg"></i>
                                </div>
                                <h2 class="text-xl font-bold text-white">File Materi</h2>
                            </div>
                        </div>

                        @if ($materi->file_materi)
                            <!-- Enhanced PDF viewer with better styling -->
                            <div class="relative bg-slate-50 overflow-hidden" style="height: 700px;">
                                <embed src="{{ Storage::url($materi->file_materi) }}" type="application/pdf" width="100%"
                                    height="100%" class="w-full h-full" />
                            </div>

                            <!-- Modern action buttons -->
                            <div class="px-8 py-8 bg-slate-50 border-t border-slate-200">
                                <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                                    <a href="{{ Storage::url($materi->file_materi) }}" download
                                        class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 shadow-md hover:shadow-lg">
                                        <i class="fas fa-download"></i>
                                        <span>Download File</span>
                                    </a>
                                    <div class="flex items-center gap-2 text-slate-600 text-sm">
                                        <i class="fas fa-circle-info text-blue-600"></i>
                                        <span>Gunakan tombol di atas untuk mengunduh file</span>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Improved empty state styling -->
                            <div class="p-8">
                                <div
                                    class="bg-amber-50 border-2 border-amber-200 rounded-xl p-6 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                    <i class="fas fa-triangle-exclamation text-amber-600 text-2xl flex-shrink-0"></i>
                                    <div>
                                        <p class="text-amber-900 font-semibold text-lg">File Belum Tersedia</p>
                                        <p class="text-amber-700 text-sm mt-1">Guru belum mengunggah file materi untuk
                                            halaman ini</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Added modern info card sidebar -->
                    <div class="bg-white rounded-2xl p-6 shadow-lg border border-slate-100 sticky top-8">
                        <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
                            <i class="fas fa-circle-info text-blue-600"></i>
                            Informasi
                        </h3>

                        <div class="space-y-5">
                            <!-- Status -->
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Status</p>
                                <div
                                    class="inline-block bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">
                                    <i class="fas fa-check-circle mr-1"></i> Aktif
                                </div>
                            </div>

                            <!-- File Info -->
                            @if ($materi->file_materi)
                                <div class="pt-4 border-t border-slate-200">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Format
                                        File</p>
                                    <div class="flex items-center gap-2 text-slate-700">
                                        <i class="fas fa-file-pdf text-red-500"></i>
                                        <span class="font-medium">PDF</span>
                                    </div>
                                </div>
                            @endif

                            <!-- Quick Action -->
                            <div class="pt-4 border-t border-slate-200">
                                <a href="{{ route('semuamateri') }}"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 px-4 rounded-xl transition-colors duration-200">
                                    <i class="fas fa-arrow-left"></i>
                                    Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen) {
            loadingScreen.classList.add('hidden');
        }
    </script>
@endsection
