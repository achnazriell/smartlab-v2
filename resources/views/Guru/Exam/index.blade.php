@extends('layouts.appTeacher')

@section('content')
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800 font-poppins">Daftar Ujian</h2>
                <p class="text-slate-500 text-sm">Kelola semua ujian, UTS, UAS, dan ulangan harian di sini.</p>
            </div>
            <a href="{{ route('exams.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Ujian
            </a>
        </div>

        <!-- Stats Summary (Optional but nice) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center space-x-4">
                <div class="p-3 bg-blue-100 text-blue-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Ujian</p>
                    <p class="text-xl font-bold text-slate-800">{{ $total }}</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center space-x-4">
                <div class="p-3 bg-green-100 text-green-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Aktif</p>
                    <p class="text-xl font-bold text-slate-800">{{ $active }}</p>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex items-center space-x-4">
                <div class="p-3 bg-amber-100 text-amber-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Draft</p>
                    <p class="text-xl font-bold text-slate-800">{{ $draft }}</p>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Ujian</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Jenis / Mapel
                            </th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Kelas / Durasi
                            </th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($exams as $exam)
                            <tr>
                                <td>{{ $exam->title }}</td>
                                <td>{{ strtoupper($exam->type) }}</td>
                                <td>{{ $exam->status }}</td>
                                <td class="flex gap-2">
                                    <a href="{{ route('guru.ujian.edit', $exam->id) }}" class="text-blue-600">Edit</a>

                                    <form action="{{ route('guru.ujian.destroy', $exam->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600" onclick="return confirm('Hapus ujian?')">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
