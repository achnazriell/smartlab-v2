@extends('layouts.appTeacher')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ showForm: false, soalType: 'PG' }">
    <!-- Step Indicator -->
    <div class="flex items-center justify-center space-x-4 mb-8">
        <div class="flex items-center text-green-600">
            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 text-green-600 font-bold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </span>
            <span class="ml-2 font-medium">Pengaturan</span>
        </div>
        <div class="w-12 h-px bg-green-500"></div>
        <div class="flex items-center text-blue-600">
            <span class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-600 text-white font-bold text-sm">2</span>
            <span class="ml-2 font-bold">Daftar & Buat Soal</span>
        </div>
    </div>

    <!-- Header & Stats -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-800 font-poppins">Ulangan Harian 1: Matematika</h2>
            <p class="text-slate-500 text-sm">X-MIPA 1 • 20 Soal • Total Skor: 100</p>
        </div>
        <button @click="showForm = true" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-sm">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Soal
        </button>
    </div>

    <!-- Dynamic Form Modal / Section -->
    <div x-show="showForm" x-transition class="bg-white rounded-xl border-2 border-blue-500 shadow-xl overflow-hidden mb-8" x-cloak>
        <div class="p-4 bg-blue-50 border-b border-blue-100 flex justify-between items-center">
            <h3 class="font-bold text-blue-800">Tambah Butir Soal Baru</h3>
            <button @click="showForm = false" class="text-slate-400 hover:text-red-500"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        <form class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-slate-700">Jenis Soal</label>
                    <select x-model="soalType" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="PG">Pilihan Ganda</option>
                        <option value="IS">Isian Singkat</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-slate-700">Skor</label>
                    <input type="number" placeholder="5" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-semibold text-slate-700">Pertanyaan</label>
                <textarea rows="3" placeholder="Tuliskan pertanyaan di sini..." class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
            </div>

            <!-- PG Options -->
            <div x-show="soalType === 'PG'" class="space-y-4 pt-4 border-t border-slate-100" x-transition>
                <p class="text-xs font-bold text-slate-400 uppercase">Opsi Jawaban</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center space-x-3">
                        <input type="radio" name="correct" class="w-4 h-4 text-blue-600">
                        <input type="text" placeholder="Opsi A" class="flex-1 px-4 py-2 rounded-lg border border-slate-200 outline-none focus:border-blue-500">
                    </div>
                    <div class="flex items-center space-x-3">
                        <input type="radio" name="correct" class="w-4 h-4 text-blue-600">
                        <input type="text" placeholder="Opsi B" class="flex-1 px-4 py-2 rounded-lg border border-slate-200 outline-none focus:border-blue-500">
                    </div>
                    <div class="flex items-center space-x-3">
                        <input type="radio" name="correct" class="w-4 h-4 text-blue-600">
                        <input type="text" placeholder="Opsi C" class="flex-1 px-4 py-2 rounded-lg border border-slate-200 outline-none focus:border-blue-500">
                    </div>
                    <div class="flex items-center space-x-3">
                        <input type="radio" name="correct" class="w-4 h-4 text-blue-600">
                        <input type="text" placeholder="Opsi D" class="flex-1 px-4 py-2 rounded-lg border border-slate-200 outline-none focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- IS Answer -->
            <div x-show="soalType === 'IS'" class="space-y-1 pt-4 border-t border-slate-100" x-transition>
                <label class="text-sm font-semibold text-slate-700">Jawaban Benar</label>
                <input type="text" placeholder="Tuliskan jawaban yang tepat" class="w-full px-4 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <button type="button" @click="showForm = false" class="px-6 py-2 text-slate-500 font-medium">Batal</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-bold rounded-lg shadow hover:bg-blue-700 transition-all">Simpan Soal</button>
            </div>
        </form>
    </div>

    <!-- Question List -->
    <div class="space-y-4">
        <!-- Soal 1 (PG) -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm relative group">
            <div class="absolute top-4 right-4 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <a href="#" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></a>
                <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
            </div>
            <div class="flex items-start space-x-4">
                <div class="w-8 h-8 flex-shrink-0 bg-slate-100 rounded-lg flex items-center justify-center font-bold text-slate-600">1</div>
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-bold uppercase rounded">Pilihan Ganda</span>
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold uppercase rounded">Skor: 5</span>
                    </div>
                    <p class="text-slate-800 font-medium leading-relaxed">Berapakah hasil dari 15 x 12?</p>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="p-3 border border-slate-100 rounded-lg text-sm text-slate-600 bg-slate-50">A. 150</div>
                        <div class="p-3 border border-blue-200 rounded-lg text-sm text-blue-700 bg-blue-50 font-semibold flex justify-between">
                            B. 180
                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div class="p-3 border border-slate-100 rounded-lg text-sm text-slate-600 bg-slate-50">C. 200</div>
                        <div class="p-3 border border-slate-100 rounded-lg text-sm text-slate-600 bg-slate-50">D. 120</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Soal 2 (Isian) -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm relative group">
             <!-- Same Action buttons -->
            <div class="absolute top-4 right-4 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <a href="#" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></a>
                <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
            </div>
            <div class="flex items-start space-x-4">
                <div class="w-8 h-8 flex-shrink-0 bg-slate-100 rounded-lg flex items-center justify-center font-bold text-slate-600">2</div>
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[10px] font-bold uppercase rounded">Isian Singkat</span>
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold uppercase rounded">Skor: 10</span>
                    </div>
                    <p class="text-slate-800 font-medium leading-relaxed">Siapakah penemu teori relativitas?</p>
                    <div class="mt-4 p-3 border border-green-200 rounded-lg text-sm bg-green-50">
                        <span class="text-xs font-bold text-green-700 uppercase block mb-1">Jawaban Benar:</span>
                        <span class="text-green-800 font-semibold">Albert Einstein</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
