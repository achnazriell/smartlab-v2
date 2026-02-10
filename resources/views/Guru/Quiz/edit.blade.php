@extends('layouts.appTeacher')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Step Indicator -->
        <div class="flex items-center justify-center space-x-4 mb-8">
            <div class="flex items-center text-purple-600">
                <span
                    class="w-8 h-8 flex items-center justify-center rounded-full bg-purple-600 text-white font-bold text-sm">1</span>
                <span class="ml-2 font-semibold">Edit Pengaturan Quiz</span>
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

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" x-data="quizForm()"
            x-init="init()">

            <div class="p-6 border-b border-slate-200 transition-colors duration-300 bg-purple-50">
                <h2 class="text-xl font-bold font-poppins text-purple-700">
                    🎮 Edit Quiz Interaktif
                </h2>
                <p class="text-slate-500 text-sm">
                    Atur ulang quiz interaktif yang seru dan menyenangkan untuk siswa.
                </p>
            </div>

            <form action="{{ route('guru.quiz.update', $quiz->id) }}" method="POST" class="p-6 space-y-8" id="quizForm" @submit.prevent="handleSubmit">
                @csrf
                @method('PUT')
                <input type="hidden" name="type" value="QUIZ">

                <!-- ============= INFORMASI DASAR QUIZ ============= -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-slate-800 border-b border-purple-200 pb-2">Informasi Dasar Quiz</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Judul Quiz <span class="text-red-500">*</span></label>
                            <input type="text" name="title" placeholder="Contoh: Quiz Matematika Seru"
                                value="{{ old('title', $quiz->title) }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 outline-none focus:ring-2 focus:ring-purple-500"
                                required x-model="title">
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Mode Quiz <span class="text-red-500">*</span></label>
                            <select name="quiz_mode" required x-model="quizMode"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 outline-none transition-all focus:ring-purple-500">
                                <option value="live" {{ old('quiz_mode', $quiz->quiz_mode) == 'live' ? 'selected' : '' }}>🎮 Live Quiz</option>
                                <option value="homework" {{ old('quiz_mode', $quiz->quiz_mode) == 'homework' ? 'selected' : '' }}>📚 Homework</option>
                            </select>
                            <p class="text-xs text-slate-500 mt-1">
                                <span x-show="quizMode === 'live'">Kompetisi langsung dengan timer</span>
                                <span x-show="quizMode === 'homework'">Tugas mandiri dengan deadline</span>
                            </p>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Mata Pelajaran <span class="text-red-500">*</span></label>
                            <select name="subject_id" required
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 outline-none focus:ring-2 focus:ring-purple-500"
                                x-on:change="getClassesBySubject($event.target.value)"
                                x-model="subjectId">
                                <option value="">-- Pilih Mapel --</option>
                                @foreach ($mapels as $mapel)
                                    <option value="{{ $mapel->id }}"
                                        {{ old('subject_id', $quiz->subject_id) == $mapel->id ? 'selected' : '' }}>
                                        {{ $mapel->name_subject }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Kelas Target <span class="text-red-500">*</span></label>
                            <select name="class_id" required id="class-select"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 outline-none focus:ring-2 focus:ring-purple-500"
                                :disabled="!selectedSubjectId">
                                <option value="">-- Pilih Mapel terlebih dahulu --</option>
                                @if($quiz->class_id && $classes->isNotEmpty())
                                    @foreach ($classes as $class)
                                        @if ($class->id == $quiz->class_id)
                                            <option value="{{ $class->id }}" selected>{{ $class->name_class }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                            <div x-show="loadingClasses" class="text-sm text-purple-600">
                                <svg class="inline w-4 h-4 animate-spin mr-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                                    </path>
                                </svg>
                                Memuat kelas...
                            </div>
                        </div>

                        {{-- <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Tingkat Kesulitan <span class="text-red-500">*</span></label>
                            <select name="difficulty_level" required
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 outline-none transition-all focus:ring-purple-500">
                                <option value="easy"
                                    {{ old('difficulty_level', $quiz->difficulty_level) == 'easy' ? 'selected' : '' }}>🎯 Mudah</option>
                                <option value="medium"
                                    {{ old('difficulty_level', $quiz->difficulty_level) == 'medium' ? 'selected' : '' }}>🎯 Sedang</option>
                                <option value="hard" {{ old('difficulty_level', $quiz->difficulty_level) == 'hard' ? 'selected' : '' }}>🎯 Sulit</option>
                            </select>
                        </div> --}}

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Waktu Per Soal (detik) <span class="text-red-500">*</span></label>
                            <input type="number" name="time_per_question" min="5" max="300"
                                value="{{ old('time_per_question', $quiz->time_per_question) }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 outline-none"
                                required>
                            <p class="text-xs text-slate-500 mt-1">Durasi untuk menjawab setiap soal</p>
                        </div>

                        <!-- TAMBAHKAN DURASI TOTAL -->
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Durasi Total Quiz (menit)</label>
                            <input type="number" name="duration" min="1" max="480"
                                value="{{ old('duration', $quiz->duration) }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 outline-none">
                            <p class="text-xs text-slate-500 mt-1">Total waktu pengerjaan quiz (optional)</p>
                        </div>
                    </div>
                </div>

                <!-- ============= PENGATURAN QUIZ INTERAKTIF ============= -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-slate-800 border-b border-purple-200 pb-2">🎪 Fitur Interaktif</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg">
                            <input type="checkbox" name="show_leaderboard" value="1"
                                {{ old('show_leaderboard', $quiz->show_leaderboard) ? 'checked' : '' }} class="w-4 h-4 text-purple-600 rounded">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Tampilkan Leaderboard
                                <span class="block text-xs text-slate-500">Peringkat siswa secara real-time</span>
                            </label>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg">
                            <input type="checkbox" name="instant_feedback" value="1"
                                {{ old('instant_feedback', $quiz->instant_feedback) ? 'checked' : '' }} class="w-4 h-4 text-purple-600 rounded">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Feedback Instan
                                <span class="block text-xs text-slate-500">Tampilkan jawaban benar/salah langsung</span>
                            </label>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg">
                            <input type="checkbox" name="enable_music" value="1"
                                {{ old('enable_music', $quiz->enable_music) ? 'checked' : '' }} class="w-4 h-4 text-purple-600 rounded">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Background Music
                                <span class="block text-xs text-slate-500">Putar musik latar saat quiz</span>
                            </label>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg">
                            <input type="checkbox" name="enable_memes" value="1"
                                {{ old('enable_memes', $quiz->enable_memes) ? 'checked' : '' }} class="w-4 h-4 text-purple-600 rounded">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Tampilkan Memes
                                <span class="block text-xs text-slate-500">Tampilkan meme setelah jawaban</span>
                            </label>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg">
                            <input type="checkbox" name="enable_powerups" value="1"
                                {{ old('enable_powerups', $quiz->enable_powerups) ? 'checked' : '' }} class="w-4 h-4 text-purple-600 rounded">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Power-ups & Bonus
                                <span class="block text-xs text-slate-500">Bonus poin untuk jawaban cepat/benar beruntun</span>
                            </label>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg">
                            <input type="checkbox" name="streak_bonus" value="1"
                                {{ old('streak_bonus', $quiz->streak_bonus) ? 'checked' : '' }} class="w-4 h-4 text-purple-600 rounded">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Bonus Streak
                                <span class="block text-xs text-slate-500">Bonus poin untuk jawaban benar beruntun</span>
                            </label>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg">
                            <input type="checkbox" name="time_bonus" value="1"
                                {{ old('time_bonus', $quiz->time_bonus) ? 'checked' : '' }} class="w-4 h-4 text-purple-600 rounded">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Bonus Waktu Cepat
                                <span class="block text-xs text-slate-500">Bonus untuk jawaban cepat</span>
                            </label>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg">
                            <input type="checkbox" name="enable_retake" value="1"
                                {{ old('enable_retake', $quiz->enable_retake) ? 'checked' : '' }} class="w-4 h-4 text-purple-600 rounded">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Izinkan Ulang Quiz
                                <span class="block text-xs text-slate-500">Siswa boleh mengulang quiz</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ============= PENGATURAN SOAL ============= -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-slate-800 border-b border-purple-200 pb-2">📝 Pengaturan Soal</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                            <input type="checkbox" name="shuffle_question" value="1"
                                {{ old('shuffle_question', $quiz->shuffle_question) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Acak Urutan Soal
                                <span class="block text-xs text-slate-500">Setiap siswa mendapat urutan berbeda</span>
                            </label>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                            <input type="checkbox" name="shuffle_answer" value="1"
                                {{ old('shuffle_answer', $quiz->shuffle_answer) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Acak Pilihan Jawaban
                                <span class="block text-xs text-slate-500">Untuk soal pilihan ganda</span>
                            </label>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                            <input type="checkbox" name="show_score" value="1"
                                {{ old('show_score', $quiz->show_score) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Tampilkan Skor
                                <span class="block text-xs text-slate-500">Siswa dapat melihat skornya</span>
                            </label>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                            <input type="checkbox" name="show_correct_answer" value="1"
                                {{ old('show_correct_answer', $quiz->show_correct_answer) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Tampilkan Jawaban Benar
                                <span class="block text-xs text-slate-500">Setelah quiz selesai</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ============= KEAMANAN ============= -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-slate-800 border-b border-purple-200 pb-2">🔒 Keamanan Quiz</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center space-x-3 p-3 bg-red-50 rounded-lg">
                            <input type="checkbox" name="fullscreen_mode" value="1"
                                {{ old('fullscreen_mode', $quiz->fullscreen_mode) ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Mode Layar Penuh
                                <span class="block text-xs text-slate-500">Wajib fullscreen selama quiz</span>
                            </label>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-red-50 rounded-lg">
                            <input type="checkbox" name="block_new_tab" value="1"
                                {{ old('block_new_tab', $quiz->block_new_tab) ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Blokir Tab Baru
                                <span class="block text-xs text-slate-500">Mencegah siswa membuka tab/window baru</span>
                            </label>
                        </div>

                        <div class="flex items-center space-x-3 p-3 bg-red-50 rounded-lg">
                            <input type="checkbox" name="prevent_copy_paste" value="1"
                                {{ old('prevent_copy_paste', $quiz->prevent_copy_paste) ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded">
                            <label class="text-sm font-medium text-slate-700 cursor-pointer flex-1">
                                Cegah Copy-Paste
                                <span class="block text-xs text-slate-500">Nonaktifkan fungsi copy, cut, dan paste</span>
                            </label>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Tampilkan Hasil Setelah</label>
                            <select name="show_result_after"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 outline-none">
                                <option value="immediately"
                                    {{ old('show_result_after', $quiz->show_result_after) == 'immediately' ? 'selected' : '' }}>Selesai Mengerjakan</option>
                                <option value="never"
                                    {{ old('show_result_after', $quiz->show_result_after) == 'never' ? 'selected' : '' }}>Tidak Pernah</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Maksimal Percobaan</label>
                            <input type="number" name="limit_attempts" min="1" max="10"
                                value="{{ old('limit_attempts', $quiz->limit_attempts) }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 outline-none">
                        </div>

                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Nilai Kelulusan Minimal</label>
                            <input type="number" name="min_pass_grade" min="0" max="100" step="0.01"
                                value="{{ old('min_pass_grade', $quiz->min_pass_grade) }}"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 outline-none">
                        </div>
                    </div>
                </div>

                <!-- ============= STATUS QUIZ ============= -->
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-slate-800 border-b border-purple-200 pb-2">📊 Status Quiz</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Status Quiz</label>
                            <select name="status"
                                class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-purple-500 outline-none">
                                <option value="draft" {{ old('status', $quiz->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="active" {{ old('status', $quiz->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ old('status', $quiz->status) == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                            <p class="text-xs text-slate-500 mt-1">Status quiz saat ini</p>
                        </div>

                        <!-- Room Status Display -->
                        <div class="space-y-1">
                            <label class="text-sm font-semibold text-slate-700">Status Ruangan</label>
                            <div class="flex items-center space-x-3 p-3 rounded-lg
                                {{ $quiz->is_room_open ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }} border">
                                @if($quiz->is_quiz_started)
                                    <span class="w-3 h-3 rounded-full bg-yellow-500 animate-pulse"></span>
                                    <span class="text-sm font-medium text-yellow-700">Quiz Sedang Berlangsung</span>
                                @elseif($quiz->is_room_open)
                                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                                    <span class="text-sm font-medium text-green-700">Ruangan Terbuka</span>
                                @else
                                    <span class="w-3 h-3 rounded-full bg-gray-400"></span>
                                    <span class="text-sm font-medium text-gray-600">Ruangan Tertutup</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                @if($quiz->is_quiz_started)
                                    Quiz sudah dimulai oleh guru
                                @elseif($quiz->is_room_open)
                                    Siswa dapat masuk ruangan
                                @else
                                    Ruangan belum dibuka
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-between pt-4 border-t border-slate-200">
                    <a href="{{ route('guru.quiz.index') }}"
                        class="inline-flex items-center px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold rounded-lg transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span>Kembali</span>
                    </a>

                    <div class="flex gap-3">
                        <a href="{{ route('guru.quiz.preview', $quiz->id) }}"
                            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <span>Preview & Room</span>
                        </a>

                        <a href="{{ route('guru.quiz.questions', $quiz->id) }}"
                            class="inline-flex items-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg">
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
        function quizForm() {
            return {
                quizMode: '{{ old('quiz_mode', $quiz->quiz_mode) }}',
                selectedSubjectId: {{ old('subject_id', $quiz->subject_id) ? old('subject_id', $quiz->subject_id) : 'null' }},
                loadingClasses: false,
                isSubmitting: false,
                title: '{{ old('title', $quiz->title) }}',
                subjectId: '{{ old('subject_id', $quiz->subject_id) }}',

                init() {
                    if (this.selectedSubjectId) {
                        this.getClassesBySubject(this.selectedSubjectId);
                    }

                    // Auto-populate class select if subject is already selected
                    const subjectSelect = document.querySelector('[name="subject_id"]');
                    const classSelect = document.getElementById('class-select');
                    const examClassId = {{ $quiz->class_id }};

                    if (subjectSelect && subjectSelect.value && examClassId) {
                        // Keep the existing selected class
                        const existingOption = classSelect.querySelector(`option[value="${examClassId}"]`);
                        if (existingOption) {
                            existingOption.selected = true;
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
                        console.log('Response status:', response.status);

                        const responseText = await response.text();
                        console.log('Response text:', responseText);

                        let data;
                        try {
                            data = JSON.parse(responseText);
                        } catch (e) {
                            console.error('Failed to parse JSON:', e);
                            throw new Error('Respon server tidak valid.');
                        }

                        console.log('Data received:', data);

                        const classSelect = document.getElementById('class-select');
                        classSelect.innerHTML = '<option value="">-- Pilih Kelas --</option>';

                        if (data.success && data.classes && data.classes.length > 0) {
                            data.classes.forEach(cls => {
                                const option = document.createElement('option');
                                option.value = cls.id;
                                option.textContent = cls.name;

                                // Preselect the current class
                                const currentClassId = {{ $quiz->class_id }};
                                if (cls.id == currentClassId) {
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
                        alert('Gagal memuat kelas: ' + error.message);
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

                async handleSubmit(e) {
                    const form = e.target;
                    const classSelect = document.getElementById('class-select');

                    if (!classSelect || !classSelect.value) {
                        alert('Silakan pilih kelas terlebih dahulu.');
                        return;
                    }

                    this.isSubmitting = true;

                    try {
                        // Submit form via AJAX
                        const formData = new FormData(form);

                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Redirect ke halaman detail quiz
                            window.location.href = data.redirect || '{{ route("guru.quiz.index") }}';
                        } else {
                            if (data.errors) {
                                const errors = Object.values(data.errors).flat().join('\n');
                                alert('Gagal menyimpan perubahan:\n' + errors);
                            } else {
                                alert(data.message || 'Gagal menyimpan perubahan');
                            }
                            this.isSubmitting = false;
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menyimpan');
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
@endsection

