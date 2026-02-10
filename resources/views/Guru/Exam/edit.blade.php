@extends('layouts.appTeacher')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Step Indicator -->
        <div class="flex items-center justify-center space-x-4 mb-8">
            <div class="flex items-center text-blue-600">
                <span
                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-600 text-white font-bold text-sm">1</span>
                <span class="ml-2 font-semibold">Edit Pengaturan Ujian</span>
            </div>
            <div class="w-12 h-px bg-slate-300"></div>
            <div class="flex items-center text-slate-400">
                <span
                    class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-200 text-slate-500 font-bold text-sm">2</span>
                <span class="ml-2 font-medium">Kelola Soal</span>
            </div>
        </div>

        {{-- Error Messages --}}
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-lg mb-6">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.282 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <strong class="font-bold">Terjadi Kesalahan!</strong>
                </div>
                <ul class="mt-2 ml-8 list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-lg mb-6">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.282 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <strong class="font-bold">Error!</strong>
                </div>
                <p class="mt-2 ml-8">{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" x-data="examForm()"
            x-init="init()">

            <div class="p-6 border-b border-slate-200 transition-colors duration-300 bg-slate-50">
                <h2 class="text-xl font-bold font-poppins text-slate-800">
                    Edit Ujian
                </h2>
                <p class="text-slate-500 text-sm">
                    Lengkapi informasi dasar ujian.
                </p>
            </div>

            <form action="{{ route('guru.exams.update', $exam->id) }}" method="POST" class="p-6 space-y-8" id="examForm">
                @csrf
                @method('PUT')

                <!-- ============= INFORMASI DASAR ============= -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-slate-800 border-b pb-2">Informasi Dasar</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Judul Ujian</label>
                            <input type="text" name="title" placeholder="Contoh: Ulangan Harian Matematika"
                                value="{{ old('title', $exam->title) }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Jenis Ujian</label>
                            <select name="type" @change="updateType($event.target.value)"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 outline-none transition-all"
                                x-model="examType" required>
                                <option value="UH" {{ old('type', $exam->type) == 'UH' ? 'selected' : '' }}>Ulangan
                                    Harian</option>
                                <option value="UTS" {{ old('type', $exam->type) == 'UTS' ? 'selected' : '' }}>UTS
                                </option>
                                <option value="UAS" {{ old('type', $exam->type) == 'UAS' ? 'selected' : '' }}>UAS
                                </option>
                                <option value="LAINNYA" {{ old('type', $exam->type) == 'LAINNYA' ? 'selected' : '' }}>
                                    Lainnya</option>
                            </select>

                            <div x-show="showOtherInput" x-transition class="mt-2">
                                <input type="text" name="custom_type" placeholder="Masukkan jenis ujian..."
                                    value="{{ old('custom_type', $exam->custom_type) }}"
                                    class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50">
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Mata Pelajaran</label>
                            <select name="subject_id" required
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 outline-none focus:ring-2 focus:ring-blue-500"
                                x-on:change="getClassesBySubject($event.target.value)">
                                <option value="">-- Pilih Mapel --</option>
                                @foreach ($mapels as $mapel)
                                    <option value="{{ $mapel->id }}"
                                        {{ old('subject_id', $exam->subject_id) == $mapel->id ? 'selected' : '' }}>
                                        {{ $mapel->name_subject }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Kelas Target</label>
                            <select name="class_id" required id="class-select"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 outline-none focus:ring-2 focus:ring-blue-500"
                                :disabled="!selectedSubjectId">
                                <option value="">-- Pilih Mapel terlebih dahulu --</option>
                                @foreach ($classes as $class)
                                    @if ($class->id == $exam->class_id)
                                        <option value="{{ $class->id }}" selected>{{ $class->name_class }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <div x-show="loadingClasses" class="text-sm text-blue-600">
                                <svg class="inline w-4 h-4 animate-spin mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                                Memuat kelas...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============= WAKTU & DURASI ============= -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-slate-800 border-b pb-2">Waktu & Durasi</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Durasi (menit)</label>
                            <input type="number" name="duration" placeholder="90" min="1" max="300"
                                value="{{ old('duration', $exam->duration) }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none"
                                required>
                            <p class="text-xs text-slate-500">Total waktu pengerjaan</p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Mulai</label>
                            <input type="datetime-local" name="start_date"
                                value="{{ old('start_date', $exam->start_at ? $exam->start_at->format('Y-m-d\TH:i') : now()->addMinutes(10)->format('Y-m-d\TH:i')) }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none"
                                required>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Selesai</label>
                            <input type="datetime-local" name="end_date"
                                value="{{ old('end_date', $exam->end_at ? $exam->end_at->format('Y-m-d\TH:i') : now()->addHours(2)->format('Y-m-d\TH:i')) }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none"
                                required>
                        </div>
                    </div>
                </div>

                <!-- ============= PENGATURAN SOAL ============= -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-slate-800 border-b pb-2">Pengaturan Soal</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                            <input type="checkbox" name="shuffle_question" id="shuffle_question" value="1"
                                {{ old('shuffle_question', $exam->shuffle_question) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 rounded">
                            <label for="shuffle_question"
                                class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Acak Urutan Soal
                                <span class="block text-xs text-slate-500">Setiap siswa mendapat urutan berbeda</span>
                            </label>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                            <input type="checkbox" name="shuffle_answer" id="shuffle_answer" value="1"
                                {{ old('shuffle_answer', $exam->shuffle_answer) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 rounded">
                            <label for="shuffle_answer" class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Acak Pilihan Jawaban
                                <span class="block text-xs text-slate-500">Untuk soal pilihan ganda</span>
                            </label>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                            <input type="checkbox" name="show_score" id="show_score" value="1"
                                {{ old('show_score', $exam->show_score) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 rounded">
                            <label for="show_score" class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Tampilkan Skor
                                <span class="block text-xs text-slate-500">Siswa dapat melihat skornya</span>
                            </label>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                            <input type="checkbox" name="show_correct_answer" id="show_correct_answer" value="1"
                                {{ old('show_correct_answer', $exam->show_correct_answer) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 rounded">
                            <label for="show_correct_answer"
                                class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Tampilkan Jawaban Benar
                                <span class="block text-xs text-slate-500">Setelah ujian selesai</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ============= KEAMANAN ============= -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-slate-800 border-b pb-2">Keamanan & Pengawasan</h3>

                    <div class="space-y-4">
                        <!-- Security Level Dropdown -->
                        <div class="space-y-2">
                            <label class="text-sm font-semibold text-slate-700">Level Keamanan</label>
                            <select name="security_level" id="security_level"
                                x-on:change="updateSecurityLevel($event.target.value)"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="none"
                                    {{ old('security_level', $exam->security_level ?? 'none') == 'none' ? 'selected' : '' }}>
                                    Tidak Ada</option>
                                <option value="basic"
                                    {{ old('security_level', $exam->security_level) == 'basic' ? 'selected' : '' }}>Basic
                                    (Fullscreen + Block Tab Baru)</option>
                                <option value="strict"
                                    {{ old('security_level', $exam->security_level) == 'strict' ? 'selected' : '' }}>Strict
                                    (Basic + Anti Copy-Paste)</option>
                                <option value="custom"
                                    {{ old('security_level', $exam->security_level) == 'custom' ? 'selected' : '' }}>Custom
                                    (Atur Manual)</option>
                            </select>
                            <p class="text-xs text-slate-500">Mengatur tingkat pengawasan otomatis</p>
                        </div>

                        <!-- Custom Security Settings -->
                        <div id="custom-security-settings"
                            class="space-y-4 {{ $exam->security_level == 'custom' ? '' : 'hidden' }}">
                            <!-- Fullscreen Mode -->
                            <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                                <input type="checkbox" name="fullscreen_mode" id="fullscreen_mode" value="1"
                                    {{ old('fullscreen_mode', $exam->fullscreen_mode) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded">
                                <label for="fullscreen_mode"
                                    class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                    Mode Layar Penuh
                                    <span class="block text-xs text-slate-500">Siswa harus mengerjakan dalam mode
                                        fullscreen</span>
                                </label>
                            </div>

                            <!-- Block New Tab -->
                            <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                                <input type="checkbox" name="block_new_tab" id="block_new_tab" value="1"
                                    {{ old('block_new_tab', $exam->block_new_tab) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded">
                                <label for="block_new_tab"
                                    class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                    Blokir Tab Baru
                                    <span class="block text-xs text-slate-500">Mencegah siswa membuka tab/window
                                        baru</span>
                                </label>
                            </div>

                            <!-- Prevent Copy Paste -->
                            <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                                <input type="checkbox" name="prevent_copy_paste" id="prevent_copy_paste" value="1"
                                    {{ old('prevent_copy_paste', $exam->prevent_copy_paste) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded">
                                <label for="prevent_copy_paste"
                                    class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                    Cegah Copy-Paste
                                    <span class="block text-xs text-slate-500">Nonaktifkan fungsi copy, cut, dan
                                        paste</span>
                                </label>
                            </div>
                        </div>

                        <!-- Violation Settings -->
                        <div class="space-y-4">
                            <!-- Disable Violations -->
                            <div class="flex items-center space-x-3 p-3 bg-red-50 rounded-lg">
                                <input type="checkbox" name="disable_violations" id="disable_violations"
                                    x-on:change="toggleViolationSettings($event.target.checked)"
                                    {{ old('disable_violations', $exam->disable_violations) ? 'checked' : '' }}
                                    class="w-4 h-4 text-red-600 rounded">
                                <label for="disable_violations"
                                    class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                    Nonaktifkan Pelanggaran
                                    <span class="block text-xs text-slate-500">Matikan semua deteksi pelanggaran</span>
                                </label>
                            </div>

                            <!-- Violation Limit -->
                            <div id="violation-limit-container" class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Batas Pelanggaran</label>
                                <input type="number" name="violation_limit" min="1" max="50"
                                    value="{{ old('violation_limit', $exam->violation_limit ?? 3) }}"
                                    class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                                <p class="text-xs text-slate-500">Jumlah pelanggaran maksimal sebelum auto-submit</p>
                            </div>
                        </div>

                        <!-- Proctoring -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
                            <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg">
                                <input type="checkbox" name="enable_proctoring" id="enable_proctoring" value="1"
                                    {{ old('enable_proctoring', $exam->enable_proctoring) ? 'checked' : '' }}
                                    class="w-4 h-4 text-purple-600 rounded">
                                <label for="enable_proctoring"
                                    class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                    Aktifkan Pengawasan AI
                                    <span class="block text-xs text-slate-500">Deteksi wajah & aktivitas
                                        mencurigakan</span>
                                </label>
                            </div>

                            <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg">
                                <input type="checkbox" name="require_camera" id="require_camera" value="1"
                                    {{ old('require_camera', $exam->require_camera) ? 'checked' : '' }}
                                    class="w-4 h-4 text-purple-600 rounded">
                                <label for="require_camera"
                                    class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                    Wajibkan Kamera
                                    <span class="block text-xs text-slate-500">Siswa harus aktifkan kamera</span>
                                </label>
                            </div>

                            <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg">
                                <input type="checkbox" name="require_mic" id="require_mic" value="1"
                                    {{ old('require_mic', $exam->require_mic) ? 'checked' : '' }}
                                    class="w-4 h-4 text-purple-600 rounded">
                                <label for="require_mic" class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                    Wajibkan Mikrofon
                                    <span class="block text-xs text-slate-500">Siswa harus aktifkan mikrofon</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============= HASIL & PERCOBAAN ============= -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-slate-800 border-b pb-2">Hasil & Percobaan</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Tampilkan Hasil Setelah</label>
                            <select name="show_result_after"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="never"
                                    {{ old('show_result_after', $exam->show_result_after ?? 'never') == 'never' ? 'selected' : '' }}>
                                    Tidak Pernah</option>
                                <option value="immediately"
                                    {{ old('show_result_after', $exam->show_result_after) == 'immediately' ? 'selected' : '' }}>
                                    Selesai Mengerjakan</option>
                                <option value="after_submit"
                                    {{ old('show_result_after', $exam->show_result_after) == 'after_submit' ? 'selected' : '' }}>
                                    Setelah Semua Siswa Submit</option>
                                <option value="after_exam"
                                    {{ old('show_result_after', $exam->show_result_after) == 'after_exam' ? 'selected' : '' }}>
                                    Setelah Waktu Ujian Berakhir</option>
                            </select>
                            <p class="text-xs text-slate-500">Hasil ditampilkan setelah</p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Maksimal Percobaan</label>
                            <input type="number" name="limit_attempts" min="1" max="10"
                                value="{{ old('limit_attempts', $exam->limit_attempts ?? 1) }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                            <p class="text-xs text-slate-500">Berapa kali siswa boleh mengerjakan</p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Nilai Kelulusan Minimal</label>
                            <input type="number" name="min_pass_grade" min="0" max="100" step="0.01"
                                value="{{ old('min_pass_grade', $exam->min_pass_grade ?? 0) }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                            <p class="text-xs text-slate-500">Nilai minimal untuk lulus</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-between pt-4 border-t border-slate-200">
                    <a href="{{ route('guru.exams.show', $exam->id) }}"
                        class="inline-flex items-center px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>Kembali</span>
                    </a>

                    <div class="flex gap-3">
                        <a href="{{ route('guru.exams.soal', $exam->id) }}"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
                            <span>Edit Soal</span>
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </a>

                        <button type="submit" id="submitBtn"
                            class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg"
                            :disabled="isSubmitting">
                            <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan'"></span>
                            <svg x-show="!isSubmitting" class="w-5 h-5 ml-2" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                            <svg x-show="isSubmitting" class="w-5 h-5 ml-2 animate-spin" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                </path>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function examForm() {
            return {
                examType: '{{ old('type', $exam->type) }}',
                showOtherInput: {{ old('type', $exam->type) == 'LAINNYA' ? 'true' : 'false' }},
                selectedSubjectId: {{ old('subject_id', $exam->subject_id) ? old('subject_id', $exam->subject_id) : 'null' }},
                loadingClasses: false,
                isSubmitting: false,

                init() {
                    if (this.selectedSubjectId) {
                        this.getClassesBySubject(this.selectedSubjectId);
                    }

                    // Initialize security level based on current value
                    const securityLevel = '{{ $exam->security_level ?? 'none' }}';
                    this.updateSecurityLevel(securityLevel);

                    // Initialize disable_violations toggle
                    const disableViolations = document.getElementById('disable_violations');
                    if (disableViolations) {
                        this.toggleViolationSettings(disableViolations.checked);
                    }

                    document.getElementById('examForm').addEventListener('submit', (e) => {
                        this.handleSubmit(e);
                    });
                },

                updateType(type) {
                    this.examType = type;
                    this.showOtherInput = (type === 'LAINNYA');
                },

                updateSecurityLevel(level) {
                    const customSettings = document.getElementById('custom-security-settings');
                    const fullscreen = document.getElementById('fullscreen_mode');
                    const blockTab = document.getElementById('block_new_tab');
                    const copyPaste = document.getElementById('prevent_copy_paste');
                    const disableViolations = document.getElementById('disable_violations');

                    // Jika disable_violations dicentang, matikan semua
                    if (disableViolations && disableViolations.checked) {
                        if (customSettings) customSettings.classList.add('hidden');
                        if (fullscreen) fullscreen.disabled = true;
                        if (blockTab) blockTab.disabled = true;
                        if (copyPaste) copyPaste.disabled = true;
                        return;
                    }

                    if (level === 'none') {
                        if (customSettings) customSettings.classList.add('hidden');
                        if (fullscreen) {
                            fullscreen.disabled = false;
                            fullscreen.checked = false;
                        }
                        if (blockTab) {
                            blockTab.disabled = false;
                            blockTab.checked = false;
                        }
                        if (copyPaste) {
                            copyPaste.disabled = false;
                            copyPaste.checked = false;
                        }
                    } else if (level === 'basic') {
                        if (customSettings) customSettings.classList.add('hidden');
                        if (fullscreen) {
                            fullscreen.disabled = false;
                            fullscreen.checked = true;
                        }
                        if (blockTab) {
                            blockTab.disabled = false;
                            blockTab.checked = true;
                        }
                        if (copyPaste) {
                            copyPaste.disabled = false;
                            copyPaste.checked = false;
                        }
                    } else if (level === 'strict') {
                        if (customSettings) customSettings.classList.add('hidden');
                        if (fullscreen) {
                            fullscreen.disabled = false;
                            fullscreen.checked = true;
                        }
                        if (blockTab) {
                            blockTab.disabled = false;
                            blockTab.checked = true;
                        }
                        if (copyPaste) {
                            copyPaste.disabled = false;
                            copyPaste.checked = true;
                        }
                    } else if (level === 'custom') {
                        if (customSettings) customSettings.classList.remove('hidden');
                        if (fullscreen) fullscreen.disabled = false;
                        if (blockTab) blockTab.disabled = false;
                        if (copyPaste) copyPaste.disabled = false;
                    }
                },

                toggleViolationSettings(disabled) {
                    const container = document.getElementById('violation-limit-container');
                    const input = container?.querySelector('input[name="violation_limit"]');

                    // Nonaktifkan security level dropdown
                    const securityLevel = document.getElementById('security_level');

                    if (disabled) {
                        if (container) {
                            container.classList.add('opacity-50', 'pointer-events-none');
                        }
                        if (input) input.disabled = true;
                        if (securityLevel) securityLevel.disabled = true;

                        // Matikan semua pengaturan keamanan
                        this.updateSecurityLevel('none');
                    } else {
                        if (container) {
                            container.classList.remove('opacity-50', 'pointer-events-none');
                        }
                        if (input) input.disabled = false;
                        if (securityLevel) securityLevel.disabled = false;

                        // Aktifkan kembali pengaturan keamanan sesuai level
                        if (securityLevel) {
                            this.updateSecurityLevel(securityLevel.value);
                        }
                    }
                },

                async getClassesBySubject(subjectId) {
                    if (!subjectId) {
                        this.resetClassSelect();
                        return;
                    }

                    this.selectedSubjectId = subjectId;
                    this.loadingClasses = true;

                    try {
                        const response = await fetch(`/guru/exams/get-classes-by-subject/${subjectId}`);
                        const data = await response.json();

                        const classSelect = document.getElementById('class-select');
                        classSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';

                        if (data.success && data.classes && data.classes.length > 0) {
                            data.classes.forEach(cls => {
                                const option = document.createElement('option');
                                option.value = cls.id;
                                option.textContent = cls.name;

                                // Preselect the current class
                                if (cls.id == {{ $exam->class_id }}) {
                                    option.selected = true;
                                }

                                classSelect.appendChild(option);
                            });
                            classSelect.disabled = false;
                        } else {
                            classSelect.innerHTML = '<option value="">Tidak ada kelas untuk mapel ini</option>';
                        }
                    } catch (error) {
                        console.error('Error loading classes:', error);
                        document.getElementById('class-select').innerHTML =
                            '<option value="">Gagal memuat data kelas</option>';
                    } finally {
                        this.loadingClasses = false;
                    }
                },

                resetClassSelect() {
                    const classSelect = document.getElementById('class-select');
                    classSelect.innerHTML = '<option value="">-- Pilih Mapel terlebih dahulu --</option>';
                    classSelect.disabled = true;
                    this.selectedSubjectId = null;
                },

                handleSubmit(e) {
                    const form = e.target;
                    const classSelect = document.getElementById('class-select');

                    if (!classSelect || !classSelect.value) {
                        e.preventDefault();
                        alert('Silakan pilih kelas terlebih dahulu.');
                        return;
                    }

                    const startDate = form.querySelector('[name="start_date"]').value;
                    const endDate = form.querySelector('[name="end_date"]').value;

                    if (startDate && endDate) {
                        const start = new Date(startDate);
                        const end = new Date(endDate);

                        if (end <= start) {
                            e.preventDefault();
                            alert('Tanggal selesai harus setelah tanggal mulai.');
                            return;
                        }
                    }

                    this.isSubmitting = true;
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize security level display
            const securityLevel = '{{ $exam->security_level ?? 'none' }}';
            const securityLevelSelect = document.getElementById('security_level');
            if (securityLevelSelect) {
                securityLevelSelect.value = securityLevel;
            }

            // Auto-populate class select if subject is already selected
            const subjectSelect = document.querySelector('[name="subject_id"]');
            const classSelect = document.getElementById('class-select');
            const examClassId = {{ $exam->class_id }};

            if (subjectSelect && subjectSelect.value && examClassId) {
                // Keep the existing selected class
                const existingOption = classSelect.querySelector(`option[value="${examClassId}"]`);
                if (existingOption) {
                    existingOption.selected = true;
                }
            }
        });
    </script>
@endsection
