@extends(Auth::user()->hasRole('Guru') ? 'layouts.appTeacher' : 'layouts.appSiswa')

@section('title', 'Profile - ' . Auth::user()->name)

@section('content')
    <div class="space-y-8 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center ">
                    <h1 class="text-3xl font-bold text-slate-800 font-display tracking-tight">PROFILE</h1>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="window.history.back()"
                    class="flex items-center text-blue-600 hover:text-blue-700 mr-3 group">
                    <svg class="w-5 h-5 transform group-hover:-translate-x-1 transition-transform" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="ml-1 text-sm font-bold">KEMBALI</span>
                </button>
            </div>
        </div>

        <!-- Profile Card -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar Info (Foto Profil) -->
            <div class="space-y-6">
                <!-- Foto Profil -->
                <div class="bg-white rounded-2xl shadow-md border p-6">
                    <h3 class="font-bold text-slate-800 mb-4">Foto Profil</h3>
                    <div class="flex flex-col items-center">
                        <!-- Foto Profil -->
                        <div class="relative mb-4">
                            @php
                                $photoPath = $user->profile_photo
                                    ? 'uploads/profile-photos/' . $user->profile_photo
                                    : null;
                                $photoExists = $photoPath && file_exists(public_path($photoPath));
                            @endphp

                            @if ($photoExists)
                                <img src="{{ asset($photoPath) }}" alt="{{ $user->name }}"
                                    class="w-32 h-32 rounded-2xl object-cover shadow-lg border-2 border-blue-200">
                            @else
                                <div
                                    class="w-32 h-32 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center text-white shadow-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="size-14">
                                        <path fill-rule="evenodd"
                                            d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            @endif

                            <!-- Tombol Upload Overlay -->
                            <div
                                class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 hover:bg-opacity-30 rounded-2xl transition-all opacity-0 hover:opacity-100">
                                <button type="button" onclick="openUploadModal()" class="cursor-pointer">
                                    <div class="bg-white p-2 rounded-full shadow-lg hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="flex flex-col gap-2 w-full">
                            <button type="button" onclick="openUploadModal()"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                Upload Foto Baru
                            </button>

                            @if ($photoExists)
                                <button type="button" onclick="confirmDeletePhoto()"
                                    class="px-4 py-2 bg-white border border-red-200 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 transition-colors flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Hapus Foto
                                </button>
                            @endif
                        </div>

                        <!-- Form Hapus Foto -->
                        <form id="delete-photo-form" action="{{ route('profile.delete-photo') }}" method="POST"
                            class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>

                        <!-- Info Foto -->
                        <p class="text-xs text-slate-500 text-center mt-4">
                            Format: JPG, PNG, GIF, WebP (maks. 2MB)
                        </p>
                    </div>
                </div>

                <!-- Card Info Akun -->
                <div class="bg-white rounded-2xl shadow-md border p-6">
                    <h3 class="font-bold text-slate-800 mb-4">Informasi Akun</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Status Akun</span>
                            <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-xs font-medium rounded-full">
                                Aktif
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-600">Bergabung Sejak</span>
                            <span class="text-sm font-medium text-slate-800">
                                {{ $user->created_at->format('d M Y') }}
                            </span>
                        </div>
                        @if (Auth::user()->hasRole('Guru'))
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-slate-600">Kelas Diajar</span>
                                <span class="text-sm font-medium text-slate-800">
                                    {{ $additionalData['kelas_diajar'] }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Mata Pelajaran (Untuk Guru) -->
                @if (Auth::user()->hasRole('Guru') && !empty($additionalData['mata_pelajaran']))
                    <div class="bg-white rounded-2xl shadow-md border p-6">
                        <h3 class="font-bold text-slate-800 mb-4">Mata Pelajaran</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($additionalData['mata_pelajaran'] as $mapel)
                                <span
                                    class="inline-block bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg text-sm font-medium">
                                    {{ $mapel }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Informasi Profil (Read-only) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-md border p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-slate-800">Informasi Profil</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Data Dasar -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                                <div class="w-full px-4 py-2.5 border border-slate-200 bg-slate-50 rounded-xl">
                                    {{ $user->name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                                <div class="w-full px-4 py-2.5 border border-slate-200 bg-slate-50 rounded-xl">
                                    {{ $user->email }}
                                </div>
                            </div>

                        </div>

                        <!-- Data Spesifik Role -->
                        <div class="space-y-4">
                            @if (Auth::user()->hasRole('Guru'))
                                @if (!empty($additionalData['kelas_diajar']) && $additionalData['kelas_diajar'] > 0)
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Total Kelas
                                            Diajar</label>
                                        <div class="w-full px-4 py-2.5 border border-slate-200 bg-slate-50 rounded-xl">
                                            {{ $additionalData['kelas_diajar'] ?? '0' }} Kelas
                                        </div>
                                    </div>
                                @endif
                            @endif

                            @if (Auth::user()->hasRole('Guru'))
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">NIP</label>
                                    <div class="w-full px-4 py-2.5 border border-slate-200 bg-slate-50 rounded-xl">
                                        {{ $additionalData['nip'] ?? '-' }}
                                    </div>
                                </div>
                            @endif
                            
                            @if (Auth::user()->hasRole('Siswa') || Auth::user()->hasRole('Murid'))
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">NIS</label>
                                    <div class="w-full px-4 py-2.5 border border-slate-200 bg-slate-50 rounded-xl">
                                        {{ $additionalData['nis'] ?? '-' }}
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
                                    <div class="w-full px-4 py-2.5 border border-slate-200 bg-slate-50 rounded-xl">
                                        {{ $additionalData['kelas'] ?? '-' }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Informasi Tambahan -->
                <div class="bg-white rounded-2xl shadow-md border p-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Bantuan</h3>
                    <ul class="list-disc list-inside text-slate-600 space-y-2">
                        <li>Untuk mengubah data pribadi, hubungi administrator</li>
                        <li>Foto profil dapat diubah dengan mengklik area foto</li>
                        <li>Klik tombol "Upload Foto Baru" untuk mengganti foto</li>
                        <li>Klik "Hapus Foto" untuk menghapus foto profil</li>
                        @if (Auth::user()->hasRole('Guru'))
                            <li>Data mata pelajaran dan kelas diambil dari sistem</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Upload Foto -->
    <div id="upload-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full animate-slide-up">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Upload Foto Profil</h3>
                        <p class="text-sm text-slate-500 mt-1">Pilih foto untuk profil Anda</p>
                    </div>
                    <button type="button" onclick="closeUploadModal()"
                        class="text-slate-400 hover:text-slate-700 hover:bg-slate-100 p-1 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Form Upload Foto -->
                <form id="upload-photo-form" action="{{ route('profile.update-photo') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-3">Pilih Foto</label>
                        <div id="upload-container"
                            class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:border-blue-400 transition-colors cursor-pointer bg-slate-50 hover:bg-blue-50">
                            <input type="file" id="profile-photo-input" name="profile_photo" accept="image/*"
                                class="hidden" onchange="previewImage(event)">

                            <div id="upload-placeholder" class="space-y-3">
                                <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-slate-700">Klik atau tarik file ke sini</p>
                                    <p class="text-xs text-slate-500 mt-1">Format: JPG, PNG, GIF, WebP (maks. 2MB)</p>
                                </div>
                            </div>

                            <!-- Preview Image -->
                            <div id="image-preview-container" class="hidden">
                                <p class="text-sm font-medium text-slate-700 mb-3">Preview Foto:</p>
                                <div class="flex items-center justify-center">
                                    <div class="relative">
                                        <img id="image-preview"
                                            class="w-32 h-32 rounded-lg object-cover border-4 border-white shadow-lg">
                                        <div
                                            class="absolute -top-2 -right-2 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center shadow-md">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <p id="file-name" class="text-sm text-slate-600 mt-3 font-medium"></p>
                                <p id="file-size" class="text-xs text-slate-500 mt-1"></p>
                                <button type="button" onclick="resetUpload()"
                                    class="mt-4 text-sm text-blue-600 hover:text-blue-700 font-medium">
                                    ← Pilih foto lain
                                </button>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div id="upload-error" class="hidden mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p id="error-message" class="text-sm text-red-700"></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeUploadModal()"
                            class="px-4 py-2.5 border border-slate-300 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
                            Batal
                        </button>
                        <button type="submit" id="upload-submit-btn" disabled
                            class="px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white text-sm font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Upload Foto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Pastikan SweetAlert sudah dimuat
        if (typeof Swal === 'undefined') {
            console.warn('SweetAlert belum dimuat. Memuat dari CDN...');
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            script.onload = function() {
                console.log('SweetAlert2 loaded successfully');
                initializeSweetAlert();
            };
            document.head.appendChild(script);
        } else {
            initializeSweetAlert();
        }

        // Modal functions
        function openUploadModal() {
            const modal = document.getElementById('upload-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            } else {
                console.error('Modal tidak ditemukan');
            }
        }

        function closeUploadModal() {
            const modal = document.getElementById('upload-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
                resetUpload();
            }
        }

        function resetUpload() {
            const input = document.getElementById('profile-photo-input');
            const previewContainer = document.getElementById('image-preview-container');
            const placeholder = document.getElementById('upload-placeholder');
            const submitBtn = document.getElementById('upload-submit-btn');
            const errorContainer = document.getElementById('upload-error');
            const fileName = document.getElementById('file-name');
            const fileSize = document.getElementById('file-size');

            if (input) input.value = '';
            if (previewContainer) previewContainer.classList.add('hidden');
            if (placeholder) placeholder.classList.remove('hidden');
            if (submitBtn) submitBtn.disabled = true;
            if (errorContainer) errorContainer.classList.add('hidden');
            if (fileName) fileName.textContent = '';
            if (fileSize) fileSize.textContent = '';
        }

        // Preview image sebelum upload
        function previewImage(event) {
            const input = event.target || document.getElementById('profile-photo-input');
            const preview = document.getElementById('image-preview');
            const previewContainer = document.getElementById('image-preview-container');
            const placeholder = document.getElementById('upload-placeholder');
            const submitBtn = document.getElementById('upload-submit-btn');
            const errorContainer = document.getElementById('upload-error');
            const errorMessage = document.getElementById('error-message');
            const fileName = document.getElementById('file-name');
            const fileSize = document.getElementById('file-size');

            // Reset error
            if (errorContainer) errorContainer.classList.add('hidden');

            if (input && input.files && input.files[0]) {
                const file = input.files[0];

                // Validasi ukuran file (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    if (errorMessage) errorMessage.textContent = 'Ukuran file maksimal 2MB';
                    if (errorContainer) errorContainer.classList.remove('hidden');
                    resetUpload();
                    return;
                }

                // Validasi tipe file
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    if (errorMessage) errorMessage.textContent = 'Hanya format JPG, PNG, GIF, WebP yang diperbolehkan';
                    if (errorContainer) errorContainer.classList.remove('hidden');
                    resetUpload();
                    return;
                }

                // Tampilkan info file
                if (fileName) fileName.textContent = file.name;
                if (fileSize) {
                    const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
                    fileSize.textContent = `Ukuran: ${sizeInMB} MB`;
                }

                const reader = new FileReader();

                reader.onload = function(e) {
                    if (preview) preview.src = e.target.result;
                    if (previewContainer) previewContainer.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
                    if (submitBtn) submitBtn.disabled = false;
                }

                reader.onerror = function() {
                    if (errorMessage) errorMessage.textContent = 'Gagal membaca file';
                    if (errorContainer) errorContainer.classList.remove('hidden');
                    resetUpload();
                }

                reader.readAsDataURL(file);
            }
        }

        // Drag and drop untuk upload area
        document.addEventListener('DOMContentLoaded', function() {
            const uploadArea = document.getElementById('upload-container');
            const fileInput = document.getElementById('profile-photo-input');

            if (!uploadArea || !fileInput) {
                console.warn('Element upload tidak ditemukan');
                return;
            }

            uploadArea.addEventListener('click', function() {
                fileInput.click();
            });

            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadArea.classList.add('border-blue-500', 'bg-blue-100');
                uploadArea.classList.remove('border-slate-300', 'bg-slate-50');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('border-blue-500', 'bg-blue-100');
                uploadArea.classList.add('border-slate-300', 'bg-slate-50');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('border-blue-500', 'bg-blue-100');
                uploadArea.classList.add('border-slate-300', 'bg-slate-50');

                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    const event = new Event('change');
                    fileInput.dispatchEvent(event);
                }
            });

            // Close modal dengan ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeUploadModal();
                }
            });

            // Close modal dengan klik di luar
            const modal = document.getElementById('upload-modal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target.id === 'upload-modal') {
                        closeUploadModal();
                    }
                });
            }
        });

        // Konfirmasi hapus foto dengan modal yang lebih baik
        function confirmDeletePhoto() {
            if (typeof Swal === 'undefined') {
                if (confirm('Hapus foto profil? Foto akan dihapus permanen.')) {
                    document.getElementById('delete-photo-form').submit();
                }
                return;
            }

            Swal.fire({
                title: '<div class="text-xl font-bold text-slate-800">Hapus Foto Profil?</div>',
                html: `
            <div class="text-center py-4">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <p class="text-slate-600 mb-2">Foto profil akan dihapus permanen.</p>
                <p class="text-sm text-slate-500">Anda akan kembali menggunakan avatar default.</p>
            </div>
        `,
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus Foto',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-2xl shadow-xl',
                    confirmButton: 'px-5 py-2.5 text-sm mx-4 hover:bg-red-100 font-medium rounded-lg border border-slate-300',
                    cancelButton: 'px-5 py-2.5 text-sm font-medium hover:bg-slate-100 rounded-lg border border-slate-300'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Sedang menghapus foto profil',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Submit form
                    document.getElementById('delete-photo-form').submit();
                }
            });
        }

        // Fungsi inisialisasi SweetAlert untuk notifikasi
        function initializeSweetAlert() {
            @if (session('success'))
                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: '<div class="text-xl font-bold text-emerald-800">Berhasil!</div>',
                        html: `
                <div class="text-center py-2">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-slate-700">{{ session('success') }}</p>
                </div>
            `,
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Oke',
                        customClass: {
                            popup: 'rounded-2xl shadow-xl',
                            confirmButton: 'px-5 py-2.5 text-sm font-medium rounded-lg'
                        },
                        buttonsStyling: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: () => {
                            // Close modal jika ada
                            closeUploadModal();
                        }
                    });
                }, 100);
            @endif

            @if (session('error'))
                setTimeout(() => {
                    Swal.fire({
                        icon: 'error',
                        title: '<div class="text-xl font-bold text-red-800">Gagal!</div>',
                        html: `
                <div class="text-center py-2">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-slate-700">{{ session('error') }}</p>
                </div>
            `,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Oke',
                        customClass: {
                            popup: 'rounded-2xl shadow-xl',
                            confirmButton: 'px-5 py-2.5 text-sm font-medium rounded-lg'
                        },
                        buttonsStyling: false
                    });
                }, 100);
            @endif
        }

        // Fallback jika SweetAlert gagal dimuat
        function showSuccessMessage(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: message,
                    timer: 3000
                });
            } else {
                alert(message);
            }
        }

        function showErrorMessage(message) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: message
                });
            } else {
                alert('Error: ' + message);
            }
        }
    </script>

    <style>
        /* Animasi untuk modal */
        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-up {
            animation: slide-up 0.3s ease-out;
        }

        /* Style untuk tombol kembali */
        .back-button {
            transition: all 0.2s ease;
        }

        .back-button:hover {
            transform: translateX(-2px);
        }

        /* Style untuk upload area */
        #upload-container {
            transition: all 0.3s ease;
        }

        /* Disable scroll ketika modal terbuka */
        body.overflow-hidden {
            overflow: hidden;
        }

        /* Style untuk preview image */
        #image-preview {
            transition: transform 0.3s ease;
        }

        #image-preview:hover {
            transform: scale(1.05);
        }

        /* Style untuk error message */
        #upload-error {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* SweetAlert customization */
        .swal2-popup {
            border-radius: 1rem !important;
            font-family: inherit !important;
        }
    </style>

    <!-- Pastikan SweetAlert dimuat -->
    @if (!request()->ajax())
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @endif
@endsection
