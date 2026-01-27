@extends('layouts.appTeacher')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('class.students', ['kelas' => $kelas]) }}" class="text-blue-600 hover:text-blue-700 mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Detail Siswa</h1>
                </div>
            </div>
        </div>

        <!-- Profil Siswa -->
        <div class="bg-white rounded-2xl shadow-md border p-6">
            <div class="flex items-start space-x-6">
                <!-- Avatar/Inisial -->
                <div
                    class="w-24 h-24 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center text-white text-3xl font-bold">
                    {{ substr($siswa->user->name ?? '??', 0, 2) }}
                </div>

                <!-- Data Siswa -->
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-slate-800 mb-2">{{ $siswa->user->name ?? 'Nama tidak tersedia' }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <p class="text-sm text-slate-500">NIS</p>
                            <p class="font-medium">{{ $siswa->nis ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Email</p>
                            <p class="font-medium">{{ $siswa->user->email ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Kelas</p>
                            <p class="font-medium">{{ $kelas }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500">Status</p>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ ($siswa->status ?? '') == 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ucfirst($siswa->status ?? 'tidak aktif') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informasi Tambahan -->
        <div class="bg-white rounded-2xl shadow-md border p-6">
            <h3 class="font-semibold text-slate-800 mb-4">Informasi Tambahan</h3>
            <p class="text-slate-600">Halaman detail siswa ini bisa dikembangkan lebih lanjut untuk menampilkan:</p>
            <ul class="list-disc list-inside text-slate-600 mt-2 space-y-1">
                <li>Riwayat nilai dan tugas</li>
                <li>Kehadiran</li>
                <li>Catatan akademik</li>
                <li>Informasi kontak orang tua</li>
            </ul>
        </div>
    </div>
@endsection
