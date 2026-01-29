@extends('layouts.appTeacher')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-center space-x-4 mb-8">
            <div class="flex items-center text-blue-600">
                <span
                    class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-600 text-white font-bold text-sm">1</span>
                <span class="ml-2 font-semibold">Pengaturan Soal</span>
            </div>
            <div class="w-12 h-px bg-slate-300"></div>
            <div class="flex items-center text-slate-400">
                <span
                    class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-200 text-slate-500 font-bold text-sm">2</span>
                <span class="ml-2 font-medium">Buat Soal</span>
            </div>
        </div>

        {{-- Error Messages --}}
        @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-lg mb-6">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.282 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <strong class="font-bold">Terjadi Kesalahan!</strong>
            </div>
            <ul class="mt-2 ml-8 list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded-lg mb-6">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.282 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <strong class="font-bold">Error!</strong>
            </div>
            <p class="mt-2 ml-8">{{ session('error') }}</p>
        </div>
        @endif

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" x-data="{
            examType: '{{ old('type', 'UH') }}',
            showOtherInput: {{ old('type') == 'Lainnya' ? 'true' : 'false' }},
            updateType(e) {
                this.examType = e.target.value;
                this.showOtherInput = (this.examType === 'Lainnya');
            }
        }">

            <div class="p-6 border-b border-slate-200 transition-colors duration-300"
                :class="examType === 'QUIZ' ? 'bg-purple-50' : 'bg-slate-50'">
                <h2 class="text-xl font-bold font-poppins transition-colors"
                    :class="examType === 'QUIZ' ? 'text-purple-700' : 'text-slate-800'">
                    <span x-text="examType === 'QUIZ' ? 'Setup Gamified Quiz' : 'Tambah Soal Baru'"></span>
                </h2>
                <p class="text-slate-500 text-sm">
                    <span
                        x-text="examType === 'QUIZ' ? 'Atur mode permainan interaktif ala Quizizz.' : 'Lengkapi informasi dasar ujian formal.'"></span>
                </p>
            </div>

            <form action="{{ route('guru.exams.store') }}" method="POST" class="p-6 space-y-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Nama Soal / Judul Kuis</label>
                        <input type="text" name="title" placeholder="Contoh: Bilangan Bulat"
                            value="{{ old('title') }}"
                            class="w-full px-4 py-2 rounded-lg border border-slate-300 outline-none focus:ring-2"
                            :class="examType === 'QUIZ' ? 'focus:ring-purple-500' : 'focus:ring-blue-500'" required>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Jenis Soal</label>
                        <select name="type" @change="updateType"
                            class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 outline-none transition-all"
                            :class="examType === 'QUIZ' ? 'focus:ring-purple-500' : 'focus:ring-blue-500'">
                            <option value="UH" {{ old('type', 'UH') == 'UH' ? 'selected' : '' }}>Ulangan Harian (UH)</option>
                            <option value="UTS" {{ old('type') == 'UTS' ? 'selected' : '' }}>UTS</option>
                            <option value="UAS" {{ old('type') == 'UAS' ? 'selected' : '' }}>UAS</option>
                            <option value="QUIZ" {{ old('type') == 'QUIZ' ? 'selected' : '' }}>Interactive Quiz (Game Mode)</option>
                            <option value="Lainnya" {{ old('type') == 'Lainnya' ? 'selected' : '' }}>Lainnya...</option>
                        </select>

                        <div x-show="showOtherInput" x-transition class="mt-2">
                            <input type="text" name="custom_type" placeholder="Masukkan jenis soal..."
                                value="{{ old('custom_type') }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50">
                        </div>
                    </div>

                    {{-- Bagian Mata Pelajaran --}}
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Mata Pelajaran</label>
                        <select name="subject_id" required
                            class="w-full px-4 py-2 rounded-lg border border-slate-300 outline-none focus:ring-2"
                            :class="examType === 'QUIZ' ? 'focus:ring-purple-500' : 'focus:ring-blue-500'"
                            x-on:change="getClassesBySubject($event.target.value)">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach ($mapels as $mapel)
                                <option value="{{ $mapel->id }}" {{ old('subject_id') == $mapel->id ? 'selected' : '' }}>
                                    {{ $mapel->name_subject }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Bagian Kelas Target (akan di-update via AJAX) --}}
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Kelas Target</label>
                        <select name="class_id" required id="class-select"
                            class="w-full px-4 py-2 rounded-lg border border-slate-300 outline-none focus:ring-2"
                            :class="examType === 'QUIZ' ? 'focus:ring-purple-500' : 'focus:ring-blue-500'">
                            <option value="">-- Pilih Mapel terlebih dahulu --</option>
                            {{-- Options akan diisi via JavaScript --}}
                        </select>
                    </div>
                </div>

                {{-- TAMBAHKAN BAGIAN INI: Pengaturan Keamanan untuk Ujian Formal --}}
                <div x-show="examType !== 'QUIZ'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">

                    {{-- Jadwal Ujian --}}
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Mulai</label>
                            <input type="datetime-local" name="start_date" required
                                value="{{ old('start_date') }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Selesai</label>
                            <input type="datetime-local" name="end_date" required
                                value="{{ old('end_date') }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    {{-- SECTION 1: Pengaturan Dasar --}}
                    <div class="pt-6 border-t border-slate-100">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">1. Pengaturan Dasar</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                            <div class="flex items-center">
                                <input type="checkbox" name="shuffle_question" id="shuffle_question"
                                    value="1" {{ old('shuffle_question', true) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                                <label for="shuffle_question" class="ml-3 text-sm font-medium text-slate-700 cursor-pointer">
                                    Acak Urutan Soal
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="shuffle_answer" id="shuffle_answer"
                                    value="1" {{ old('shuffle_answer', true) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                                <label for="shuffle_answer" class="ml-3 text-sm font-medium text-slate-700 cursor-pointer">
                                    Acak Urutan Jawaban
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="show_score" id="show_score"
                                    value="1" {{ old('show_score', true) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                                <label for="show_score" class="ml-3 text-sm font-medium text-slate-700 cursor-pointer">
                                    Tampilkan Nilai Akhir
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Durasi Ujian (Menit)</label>
                                <input type="number" name="duration" placeholder="90" min="1" max="300"
                                    value="{{ old('duration', 90) }}"
                                    class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none"
                                    required>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Tampilkan Jawaban Benar</label>
                                <select name="show_correct_answer"
                                    class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="0" {{ old('show_correct_answer') == '0' ? 'selected' : '' }}>Tidak Pernah</option>
                                    <option value="1" {{ old('show_correct_answer') == '1' ? 'selected' : '' }}>Setelah Ujian</option>
                                    <option value="2" {{ old('show_correct_answer') == '2' ? 'selected' : '' }}>Setelah Setiap Soal</option>
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1 mt-6">
                            <label class="text-sm font-semibold text-slate-700">Tampilkan Hasil</label>
                            <select name="show_result_after"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="never" {{ old('show_result_after') == 'never' ? 'selected' : '' }}>Tidak Pernah</option>
                                <option value="immediately" {{ old('show_result_after') == 'immediately' ? 'selected' : '' }}>Sesaat Setelah Submit</option>
                                <option value="after_submit" {{ old('show_result_after') == 'after_submit' ? 'selected' : '' }}>Setelah Semua Submit</option>
                                <option value="after_exam" {{ old('show_result_after') == 'after_exam' ? 'selected' : '' }}>Setelah Ujian Berakhir</option>
                            </select>
                        </div>
                    </div>

                    {{-- SECTION 2: Pengaturan Keamanan --}}
                    <div class="pt-6 border-t border-slate-100">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">2. Pengaturan Keamanan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                            <div class="flex items-center">
                                <input type="checkbox" name="fullscreen_mode" id="fullscreen_mode"
                                    value="1" {{ old('fullscreen_mode', true) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                                <label for="fullscreen_mode" class="ml-3 text-sm font-medium text-slate-700 cursor-pointer">
                                    Mode Layar Penuh
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="block_new_tab" id="block_new_tab"
                                    value="1" {{ old('block_new_tab', true) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                                <label for="block_new_tab" class="ml-3 text-sm font-medium text-slate-700 cursor-pointer">
                                    Blokir Tab Baru
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="prevent_copy_paste" id="prevent_copy_paste"
                                    value="1" {{ old('prevent_copy_paste', true) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                                <label for="prevent_copy_paste" class="ml-3 text-sm font-medium text-slate-700 cursor-pointer">
                                    Cegah Copy-Paste
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="auto_submit" id="auto_submit"
                                    value="1" {{ old('auto_submit', false) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                                <label for="auto_submit" class="ml-3 text-sm font-medium text-slate-700 cursor-pointer">
                                    Auto Submit Saat Pelanggaran
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 3: Proctoring --}}
                    <div class="pt-6 border-t border-slate-100" x-data="{
                        proctoring: {{ old('enable_proctoring', false) ? 'true' : 'false' }}
                    }">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">3. Proctoring</h3>

                        <div class="mb-6 flex items-center">
                            <input type="checkbox" name="enable_proctoring" id="enable_proctoring"
                                value="1" {{ old('enable_proctoring', false) ? 'checked' : '' }}
                                @change="proctoring = $event.target.checked"
                                class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                            <label for="enable_proctoring" class="ml-3 text-sm font-medium text-slate-700 cursor-pointer">
                                Aktifkan Proctoring
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6" x-show="proctoring" x-transition>
                            <div class="flex items-center">
                                <input type="checkbox" name="require_camera" id="require_camera"
                                    value="1" {{ old('require_camera', false) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                                <label for="require_camera" class="ml-3 text-sm font-medium text-slate-700 cursor-pointer">
                                    Wajib Kamera
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="require_mic" id="require_mic"
                                    value="1" {{ old('require_mic', false) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                                <label for="require_mic" class="ml-3 text-sm font-medium text-slate-700 cursor-pointer">
                                    Wajib Mikrofon
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="save_violation_log" id="save_violation_log"
                                    value="1" {{ old('save_violation_log', true) ? 'checked' : '' }}
                                    class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                                <label for="save_violation_log" class="ml-3 text-sm font-medium text-slate-700 cursor-pointer">
                                    Simpan Log Pelanggaran
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 4: Kontrol Lanjutan --}}
                    <div class="pt-6 border-t border-slate-100">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">4. Kontrol Lanjutan</h3>

                        <div class="flex items-center mb-6">
                            <input type="checkbox" name="allow_screenshot" id="allow_screenshot"
                                value="1" {{ old('allow_screenshot', false) ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 cursor-pointer">
                            <label for="allow_screenshot" class="ml-3 text-sm font-medium text-slate-700 cursor-pointer">
                                Izinkan Screenshot (Kebijakan)
                            </label>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Batas Maksimal Pelanggaran</label>
                                <input type="number" name="max_violations" min="0" max="50"
                                    value="{{ old('max_violations', 10) }}"
                                    class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                                <p class="text-xs text-slate-500">Jumlah maksimal pelanggaran sebelum auto submit (0 = tidak ada batas)</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Batas Percobaan</label>
                                <input type="number" name="limit_attempts" min="1" max="10"
                                    value="{{ old('limit_attempts', 1) }}"
                                    class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                                <p class="text-xs text-slate-500">Jumlah maksimal percobaan ujian per siswa</p>
                            </div>
                        </div>

                        <div class="space-y-1 mt-6">
                            <label class="text-sm font-semibold text-slate-700">Nilai Minimum Lulus</label>
                            <input type="number" name="min_pass_grade" min="0" max="100"
                                step="0.1" value="{{ old('min_pass_grade', 0) }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                            <p class="text-xs text-slate-500">Nilai minimal untuk dinyatakan lulus (0-100)</p>
                        </div>
                    </div>
                </div>

                {{-- TAMBAHKAN BAGIAN INI: Pengaturan untuk QUIZ --}}
                <div x-show="examType === 'QUIZ'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    class="bg-purple-50 p-6 rounded-xl border border-purple-100">

                    {{-- Header Quiz --}}
                    <div class="flex items-center space-x-2 mb-6 text-purple-800">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <h3 class="font-bold text-lg">Pengaturan Quiz Playground</h3>
                    </div>

                    {{-- Settings Grid untuk Quiz --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-purple-900">Waktu Per Soal (Detik)</label>
                            <select name="time_per_question" required
                                class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:ring-2 focus:ring-purple-500 outline-none bg-white">
                                <option value="30" {{ old('time_per_question', 60) == 30 ? 'selected' : '' }}>30 Detik (Cepat)</option>
                                <option value="60" {{ old('time_per_question', 60) == 60 ? 'selected' : '' }}>60 Detik (Normal)</option>
                                <option value="120" {{ old('time_per_question', 60) == 120 ? 'selected' : '' }}>2 Menit (Analisis)</option>
                                <option value="0" {{ old('time_per_question', 60) == 0 ? 'selected' : '' }}>Tidak Ada Batas</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-purple-900">Mode Quiz</label>
                            <select name="quiz_mode" required
                                class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:ring-2 focus:ring-purple-500 outline-none bg-white">
                                <option value="live" {{ old('quiz_mode', 'live') == 'live' ? 'selected' : '' }}>Live (Guru Mengontrol)</option>
                                <option value="homework" {{ old('quiz_mode', 'live') == 'homework' ? 'selected' : '' }}>Homework (Siswa Mandiri)</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-purple-900">Tingkat Kesulitan</label>
                            <select name="difficulty_level"
                                class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:ring-2 focus:ring-purple-500 outline-none bg-white">
                                <option value="easy" {{ old('difficulty_level', 'medium') == 'easy' ? 'selected' : '' }}>Mudah</option>
                                <option value="medium" {{ old('difficulty_level', 'medium') == 'medium' ? 'selected' : '' }}>Sedang</option>
                                <option value="hard" {{ old('difficulty_level', 'medium') == 'hard' ? 'selected' : '' }}>Sulit</option>
                            </select>
                        </div>
                    </div>

                    {{-- Toggle Settings untuk Quiz --}}
                    <div class="pt-4 border-t border-purple-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                            @include('components.toggle-switch', [
                                'label' => 'Tampilkan Leaderboard',
                                'name' => 'show_leaderboard',
                                'checked' => old('show_leaderboard', true),
                                'color' => 'purple',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Musik Latar',
                                'name' => 'enable_music',
                                'checked' => old('enable_music', true),
                                'color' => 'purple',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Tampilkan Meme (Benar/Salah)',
                                'name' => 'enable_memes',
                                'checked' => old('enable_memes', true),
                                'color' => 'purple',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Izinkan Power-ups',
                                'name' => 'enable_powerups',
                                'checked' => old('enable_powerups', true),
                                'color' => 'purple',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Acak Urutan Soal',
                                'name' => 'randomize_questions',
                                'checked' => old('randomize_questions', true),
                                'color' => 'purple',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Feedback Instan',
                                'name' => 'instant_feedback',
                                'checked' => old('instant_feedback', true),
                                'color' => 'purple',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Bonus Streak',
                                'name' => 'streak_bonus',
                                'checked' => old('streak_bonus', true),
                                'color' => 'purple',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Bonus Waktu',
                                'name' => 'time_bonus',
                                'checked' => old('time_bonus', true),
                                'color' => 'purple',
                            ])
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex items-center justify-end space-x-4">
                    <a href="{{ route('guru.exams.index') }}"
                        class="px-6 py-2 text-slate-600 hover:text-slate-800 font-medium">Batal</a>
                    <button type="submit"
                        class="px-8 py-2 text-white font-bold rounded-lg shadow-md transition-all flex items-center"
                        :class="examType === 'QUIZ' ? 'bg-purple-600 hover:bg-purple-700' : 'bg-blue-600 hover:bg-blue-700'">
                        <span>Lanjut Buat Soal</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const subjectSelect = document.querySelector('select[name="subject_id"]');
            const classSelect = document.getElementById('class-select');

            if (subjectSelect) {
                subjectSelect.addEventListener('change', function() {
                    const subjectId = this.value;
                    console.log('Subject selected:', subjectId);

                    if (!subjectId) {
                        classSelect.innerHTML =
                            '<option value="">-- Pilih Mapel terlebih dahulu --</option>';
                        return;
                    }

                    // Tampilkan loading
                    classSelect.innerHTML = '<option value="">Memuat kelas...</option>';
                    classSelect.disabled = true;

                    fetch(`/guru/exams/get-classes-by-subject/${subjectId}`)
                        .then(response => {
                            console.log('Response status:', response.status);
                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Data received:', data);

                            classSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';

                            if (data.classes && data.classes.length > 0) {
                                data.classes.forEach(cls => {
                                    const option = document.createElement('option');
                                    option.value = cls.id;
                                    option.textContent = cls.name;
                                    // Set selected jika ini old value
                                    if (cls.id == {{ old('class_id', 0) }}) {
                                        option.selected = true;
                                    }
                                    classSelect.appendChild(option);
                                });
                                classSelect.disabled = false;
                            } else {
                                classSelect.innerHTML =
                                    '<option value="">Tidak ada kelas untuk mapel ini</option>';
                                console.warn('No classes found for subject:', subjectId, data);
                            }

                            // Tampilkan debug info jika ada
                            if (data.debug) {
                                console.log('Debug info:', data.debug);
                            }
                            if (data.error) {
                                console.error('Server error:', data.error);
                            }
                        })
                        .catch(error => {
                            console.error('Fetch error:', error);
                            classSelect.innerHTML = '<option value="">Gagal memuat data</option>';
                            classSelect.disabled = false;
                        });
                });
            }

            // Inisialisasi untuk old value
            @if(old('subject_id'))
                setTimeout(() => {
                    const oldSubjectId = "{{ old('subject_id') }}";
                    if (oldSubjectId && subjectSelect) {
                        subjectSelect.value = oldSubjectId;
                        subjectSelect.dispatchEvent(new Event('change'));

                        // Tunggu dulu sebelum set kelas
                        setTimeout(() => {
                            const oldClassId = "{{ old('class_id') }}";
                            if (oldClassId && classSelect) {
                                classSelect.value = oldClassId;
                            }
                        }, 1000);
                    }
                }, 500);
            @endif
        });
    </script>
@endsection
