@extends('layouts.appTeacher')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Step Indicator -->
    <div class="flex items-center justify-center space-x-4 mb-8">
        <div class="flex items-center text-blue-600">
            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-600 text-white font-bold text-sm">1</span>
            <span class="ml-2 font-semibold">Pengaturan Ujian</span>
        </div>
        <div class="w-12 h-px bg-slate-300"></div>
        <div class="flex items-center text-slate-400">
            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-200 text-slate-500 font-bold text-sm">2</span>
            <span class="ml-2 font-medium">Buat Soal</span>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 bg-slate-50">
            <h2 class="text-xl font-bold text-slate-800 font-poppins">Tambah Ujian Baru</h2>
            <p class="text-slate-500 text-sm">Lengkapi informasi dasar ujian sebelum membuat butir soal.</p>
        </div>

        <form action="#" method="POST" class="p-6 space-y-8">
            <!-- Informasi Dasar -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-slate-700">Nama Ujian</label>
                    <input type="text" placeholder="Contoh: Ulangan Harian Bab 1" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all" required>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-slate-700">Jenis Ujian</label>
                    <select class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="UH">Ulangan Harian (UH)</option>
                        <option value="UTS">UTS</option>
                        <option value="UAS">UAS</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-slate-700">Mata Pelajaran</label>
                    <select class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option>Matematika</option>
                        <option>Fisika</option>
                        <option>Biologi</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-slate-700">Kelas</label>
                    <select class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option>X-MIPA 1</option>
                        <option>X-MIPA 2</option>
                        <option>XI-MIPA 1</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-slate-700">Waktu Pengerjaan (Menit)</label>
                    <input type="number" placeholder="90" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Tanggal Mulai</label>
                        <input type="datetime-local" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div class="space-y-1">
                        <label class="text-sm font-semibold text-slate-700">Tanggal Selesai</label>
                        <input type="datetime-local" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- Toggles Section -->
            <div class="pt-6 border-t border-slate-100">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Pengaturan Keamanan & Hasil</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                    <!-- Toggle 1 -->
                    <label class="flex items-center justify-between cursor-pointer group">
                        <span class="text-slate-700 group-hover:text-blue-600 transition-colors">Izinkan Screenshot</span>
                        <div class="relative inline-flex items-center cursor-pointer" x-data="{ checked: false }" @click="checked = !checked">
                            <input type="checkbox" class="sr-only" :checked="checked">
                            <div class="w-11 h-6 rounded-full transition-colors duration-200" :class="checked ? 'bg-blue-600' : 'bg-slate-300'"></div>
                            <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform duration-200" :class="checked ? 'translate-x-5' : ''"></div>
                        </div>
                    </label>
                    <!-- Toggle 2 -->
                    <label class="flex items-center justify-between cursor-pointer group">
                        <span class="text-slate-700 group-hover:text-blue-600 transition-colors">Izinkan Salin Soal</span>
                        <div class="relative inline-flex items-center cursor-pointer" x-data="{ checked: false }" @click="checked = !checked">
                            <input type="checkbox" class="sr-only" :checked="checked">
                            <div class="w-11 h-6 rounded-full transition-colors duration-200" :class="checked ? 'bg-blue-600' : 'bg-slate-300'"></div>
                            <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform duration-200" :class="checked ? 'translate-x-5' : ''"></div>
                        </div>
                    </label>
                    <!-- Toggle 3 -->
                    <label class="flex items-center justify-between cursor-pointer group">
                        <span class="text-slate-700 group-hover:text-blue-600 transition-colors">Acak Soal</span>
                        <div class="relative inline-flex items-center cursor-pointer" x-data="{ checked: true }" @click="checked = !checked">
                            <input type="checkbox" class="sr-only" :checked="checked">
                            <div class="w-11 h-6 rounded-full transition-colors duration-200" :class="checked ? 'bg-blue-600' : 'bg-slate-300'"></div>
                            <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform duration-200" :class="checked ? 'translate-x-5' : ''"></div>
                        </div>
                    </label>
                    <!-- Toggle 4 -->
                    <label class="flex items-center justify-between cursor-pointer group">
                        <span class="text-slate-700 group-hover:text-blue-600 transition-colors">Tampilkan Nilai di Akhir</span>
                        <div class="relative inline-flex items-center cursor-pointer" x-data="{ checked: true }" @click="checked = !checked">
                            <input type="checkbox" class="sr-only" :checked="checked">
                            <div class="w-11 h-6 rounded-full transition-colors duration-200" :class="checked ? 'bg-blue-600' : 'bg-slate-300'"></div>
                            <div class="absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform duration-200" :class="checked ? 'translate-x-5' : ''"></div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center justify-end space-x-4">
                <a href="/guru/ujian" class="px-6 py-2 text-slate-600 hover:text-slate-800 font-medium">Batal</a>
                <button type="submit" class="px-8 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-md transition-all">
                    Lanjut Buat Soal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
