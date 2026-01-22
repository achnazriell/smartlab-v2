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

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" x-data="{
            examType: '{{ $exam->type ?? 'UH' }}',
            showOtherInput: false,
            updateType(e) {
                this.examType = e.target.value;
                this.showOtherInput = (this.examType === 'Lainnya');
            },
            init() {
                this.showOtherInput = (this.examType === 'Lainnya');
            }
        }">

            <div class="p-6 border-b border-slate-200 transition-colors duration-300"
                :class="examType === 'QUIZ' ? 'bg-purple-50' : 'bg-slate-50'">
                <h2 class="text-xl font-bold font-poppins transition-colors"
                    :class="examType === 'QUIZ' ? 'text-purple-700' : 'text-slate-800'">
                    <span x-text="examType === 'QUIZ' ? 'Edit Gamified Quiz' : 'Edit Soal'"></span>
                </h2>
                <p class="text-slate-500 text-sm">
                    <span
                        x-text="examType === 'QUIZ' ? 'Ubah pengaturan mode permainan interaktif ala Quizizz.' : 'Ubah informasi dasar ujian formal.'"></span>
                </p>
            </div>

            <form action="{{ route('guru.exams.update', $exam->id) }}" method="POST" class="p-6 space-y-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Nama Soal / Judul Kuis</label>
                        <input type="text" name="title" placeholder="Contoh: Bilangan Bulat"
                            value="{{ old('title', $exam->title) }}"
                            class="w-full px-4 py-2 rounded-lg border border-slate-300 outline-none focus:ring-2"
                            :class="examType === 'QUIZ' ? 'focus:ring-purple-500' : 'focus:ring-blue-500'" required>
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Jenis Soal</label>
                        <select name="type" @change="updateType"
                            class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 outline-none transition-all"
                            :class="examType === 'QUIZ' ? 'focus:ring-purple-500' : 'focus:ring-blue-500'">
                            <option value="UH" {{ old('type', $exam->type) === 'UH' ? 'selected' : '' }}>Ulangan Harian (UH)</option>
                            <option value="UTS" {{ old('type', $exam->type) === 'UTS' ? 'selected' : '' }}>UTS</option>
                            <option value="UAS" {{ old('type', $exam->type) === 'UAS' ? 'selected' : '' }}>UAS</option>
                            <option value="QUIZ" {{ old('type', $exam->type) === 'QUIZ' ? 'selected' : '' }}>Interactive Quiz (Game Mode)</option>
                            <option value="Lainnya" {{ old('type', $exam->type) === 'Lainnya' ? 'selected' : '' }}>Lainnya...</option>
                        </select>

                        <div x-show="showOtherInput" x-transition class="mt-2">
                            <input type="text" name="custom_type" placeholder="Masukkan jenis soal..."
                                value="{{ old('custom_type', $exam->custom_type ?? '') }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none bg-slate-50">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Mata Pelajaran</label>
                        <select name="subject_id" required
                            class="w-full px-4 py-2 rounded-lg border border-slate-300 outline-none focus:ring-2"
                            :class="examType === 'QUIZ' ? 'focus:ring-purple-500' : 'focus:ring-blue-500'">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id', $exam->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name_subject }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Kelas Target</label>
                        <select name="class_id" required
                            class="w-full px-4 py-2 rounded-lg border border-slate-300 outline-none focus:ring-2"
                            :class="examType === 'QUIZ' ? 'focus:ring-purple-500' : 'focus:ring-blue-500'">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id', $exam->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name_class }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Pengaturan untuk Ujian Formal --}}
                <div x-show="examType !== 'QUIZ'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">

                    {{-- Durasi dan Waktu --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Durasi Pengerjaan (Menit)</label>
                            <input type="number" name="duration" placeholder="90" min="1" max="300"
                                value="{{ old('duration', $exam->duration ?? '') }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none"
                                required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Mulai</label>
                                <input type="datetime-local" name="start_date"
                                    value="{{ old('start_date', $exam->start_date ? $exam->start_date->format('Y-m-d\TH:i') : '') }}"
                                    class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none"
                                    required>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Selesai</label>
                                <input type="datetime-local" name="end_date"
                                    value="{{ old('end_date', $exam->end_date ? $exam->end_date->format('Y-m-d\TH:i') : '') }}"
                                    class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none"
                                    required>
                            </div>
                        </div>
                    </div>

                    {{-- Pengaturan Dasar --}}
                    <div class="pt-6 border-t border-slate-100">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Pengaturan Dasar</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                            @include('components.toggle-switch', [
                                'label' => 'Acak Urutan Soal',
                                'name' => 'shuffle_question',
                                'checked' => old('shuffle_question', $exam->shuffle_question ?? true),
                                'color' => 'blue',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Acak Urutan Jawaban',
                                'name' => 'shuffle_answer',
                                'checked' => old('shuffle_answer', $exam->shuffle_answer ?? true),
                                'color' => 'blue',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Tampilkan Nilai Akhir',
                                'name' => 'show_score',
                                'checked' => old('show_score', $exam->show_score ?? true),
                                'color' => 'blue',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Izinkan Salin Teks',
                                'name' => 'allow_copy',
                                'checked' => old('allow_copy', $exam->allow_copy ?? false),
                                'color' => 'blue',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Izinkan Screenshot',
                                'name' => 'allow_screenshot',
                                'checked' => old('allow_screenshot', $exam->allow_screenshot ?? false),
                                'color' => 'blue',
                            ])
                        </div>
                    </div>

                    {{-- Pengaturan Keamanan Lanjutan --}}
                    <div class="pt-6 border-t border-slate-100">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Pengaturan Keamanan
                            Lanjutan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @include('components.toggle-switch', [
                                'label' => 'Wajib Kamera',
                                'name' => 'require_camera',
                                'checked' => old('require_camera', $exam->require_camera ?? false),
                                'color' => 'blue',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Wajib Mikrofon',
                                'name' => 'require_mic',
                                'checked' => old('require_mic', $exam->require_mic ?? false),
                                'color' => 'blue',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Aktifkan Proctoring',
                                'name' => 'enable_proctoring',
                                'checked' => old('enable_proctoring', $exam->enable_proctoring ?? true),
                                'color' => 'blue',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Blokir Tab Baru',
                                'name' => 'block_new_tab',
                                'checked' => old('block_new_tab', $exam->block_new_tab ?? true),
                                'color' => 'blue',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Mode Layar Penuh',
                                'name' => 'fullscreen_mode',
                                'checked' => old('fullscreen_mode', $exam->fullscreen_mode ?? true),
                                'color' => 'blue',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Auto Submit',
                                'name' => 'auto_submit',
                                'checked' => old('auto_submit', $exam->auto_submit ?? true),
                                'color' => 'blue',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Cegah Copy-Paste',
                                'name' => 'prevent_copy_paste',
                                'checked' => old('prevent_copy_paste', $exam->prevent_copy_paste ?? true),
                                'color' => 'blue',
                            ])
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Batas Percobaan</label>
                                <input type="number" name="limit_attempts" min="1" max="10"
                                    value="{{ old('limit_attempts', $exam->limit_attempts ?? 1) }}"
                                    class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                                <p class="text-xs text-slate-500">Jumlah maksimal percobaan ujian</p>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Nilai Minimum Lulus</label>
                                <input type="number" name="min_pass_grade" min="0" max="100"
                                    step="0.1" value="{{ old('min_pass_grade', $exam->min_pass_grade ?? 0) }}"
                                    class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                                <p class="text-xs text-slate-500">Nilai minimal untuk dinyatakan lulus (0-100)</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Tampilkan Jawaban Benar</label>
                                <select name="show_correct_answer"
                                    class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="0" {{ old('show_correct_answer', $exam->show_correct_answer ?? 0) == 0 ? 'selected' : '' }}>Tidak Pernah</option>
                                    <option value="1" {{ old('show_correct_answer', $exam->show_correct_answer ?? 0) == 1 ? 'selected' : '' }}>Setelah Ujian</option>
                                    <option value="2" {{ old('show_correct_answer', $exam->show_correct_answer ?? 0) == 2 ? 'selected' : '' }}>Setelah Setiap Soal</option>
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-slate-700">Tampilkan Hasil</label>
                                <select name="show_result_after"
                                    class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="never" {{ old('show_result_after', $exam->show_result_after ?? 'never') === 'never' ? 'selected' : '' }}>Tidak Pernah</option>
                                    <option value="immediately" {{ old('show_result_after', $exam->show_result_after ?? 'never') === 'immediately' ? 'selected' : '' }}>Sesaat Setelah Submit</option>
                                    <option value="after_submit" {{ old('show_result_after', $exam->show_result_after ?? 'never') === 'after_submit' ? 'selected' : '' }}>Setelah Semua Submit</option>
                                    <option value="after_exam" {{ old('show_result_after', $exam->show_result_after ?? 'never') === 'after_exam' ? 'selected' : '' }}>Setelah Ujian Berakhir</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pengaturan untuk QUIZ --}}
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
                                <option value="30" {{ old('time_per_question', $exam->time_per_question ?? 60) == 30 ? 'selected' : '' }}>30 Detik (Cepat)</option>
                                <option value="60" {{ old('time_per_question', $exam->time_per_question ?? 60) == 60 ? 'selected' : '' }}>60 Detik (Normal)</option>
                                <option value="120" {{ old('time_per_question', $exam->time_per_question ?? 60) == 120 ? 'selected' : '' }}>2 Menit (Analisis)</option>
                                <option value="0" {{ old('time_per_question', $exam->time_per_question ?? 60) == 0 ? 'selected' : '' }}>Tidak Ada Batas</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-purple-900">Mode Quiz</label>
                            <select name="quiz_mode" required
                                class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:ring-2 focus:ring-purple-500 outline-none bg-white">
                                <option value="live" {{ old('quiz_mode', $exam->quiz_mode ?? 'homework') === 'live' ? 'selected' : '' }}>Live (Guru Mengontrol)</option>
                                <option value="homework" {{ old('quiz_mode', $exam->quiz_mode ?? 'homework') === 'homework' ? 'selected' : '' }}>Homework (Siswa Mandiri)</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-purple-900">Tingkat Kesulitan</label>
                            <select name="difficulty_level"
                                class="w-full px-4 py-2 rounded-lg border border-purple-200 focus:ring-2 focus:ring-purple-500 outline-none bg-white">
                                <option value="easy" {{ old('difficulty_level', $exam->difficulty_level ?? 'medium') === 'easy' ? 'selected' : '' }}>Mudah</option>
                                <option value="medium" {{ old('difficulty_level', $exam->difficulty_level ?? 'medium') === 'medium' ? 'selected' : '' }}>Sedang</option>
                                <option value="hard" {{ old('difficulty_level', $exam->difficulty_level ?? 'medium') === 'hard' ? 'selected' : '' }}>Sulit</option>
                            </select>
                        </div>
                    </div>

                    {{-- Toggle Settings untuk Quiz --}}
                    <div class="pt-4 border-t border-purple-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                            @include('components.toggle-switch', [
                                'label' => 'Tampilkan Leaderboard',
                                'name' => 'show_leaderboard',
                                'checked' => old('show_leaderboard', $exam->show_leaderboard ?? true),
                                'color' => 'purple',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Musik Latar',
                                'name' => 'enable_music',
                                'checked' => old('enable_music', $exam->enable_music ?? true),
                                'color' => 'purple',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Tampilkan Meme (Benar/Salah)',
                                'name' => 'enable_memes',
                                'checked' => old('enable_memes', $exam->enable_memes ?? true),
                                'color' => 'purple',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Izinkan Power-ups',
                                'name' => 'enable_powerups',
                                'checked' => old('enable_powerups', $exam->enable_powerups ?? true),
                                'color' => 'purple',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Acak Urutan Soal',
                                'name' => 'randomize_questions',
                                'checked' => old('randomize_questions', $exam->randomize_questions ?? true),
                                'color' => 'purple',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Feedback Instan',
                                'name' => 'instant_feedback',
                                'checked' => old('instant_feedback', $exam->instant_feedback ?? true),
                                'color' => 'purple',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Bonus Streak',
                                'name' => 'streak_bonus',
                                'checked' => old('streak_bonus', $exam->streak_bonus ?? true),
                                'color' => 'purple',
                            ])
                            @include('components.toggle-switch', [
                                'label' => 'Bonus Waktu',
                                'name' => 'time_bonus',
                                'checked' => old('time_bonus', $exam->time_bonus ?? true),
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
                        <span>Simpan Perubahan</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
