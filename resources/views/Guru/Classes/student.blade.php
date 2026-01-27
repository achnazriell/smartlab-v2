@extends('layouts.appTeacher')

@section('content')
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center mb-2">
                    @php
                        $previousPage = session('previous_page', route('class.index'));
                        $isFromDashboard = str_contains($previousPage, 'teacher/dashboard');
                        $backUrl = $isFromDashboard ? route('homeguru') : route('class.index');
                    @endphp

                    <a href="{{ $backUrl }}" class="text-blue-600 hover:text-blue-700 mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>

                    <h1 class="text-3xl font-bold text-slate-800 font-display tracking-tight">Daftar Siswa</h1>
                </div>
                <div class="flex items-center text-slate-600">
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium mr-3">
                        {{ $kelasInfo['nama'] }}
                    </span>
                    <span class="text-sm">
                        {{ $kelasInfo['total_siswa'] }} siswa
                    </span>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Cari siswa..."
                        class="pl-10 pr-4 py-2 border rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Students Table -->
        <div class="bg-white rounded-2xl shadow-md border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full" id="studentsTable">
                    <thead class="bg-slate-50 border-b">
                        <tr>
                            <th class="py-4 px-6 text-left">
                                <div class="flex items-center">
                                    <span class="text-sm font-semibold text-slate-700">No</span>
                                </div>
                            </th>
                            <th class="py-4 px-6 text-left">
                                <span class="text-sm font-semibold text-slate-700">Nama Siswa</span>
                            </th>
                            <th class="py-4 px-6 text-left">
                                <span class="text-sm font-semibold text-slate-700">NIS</span>
                            </th>
                            <th class="py-4 px-6 text-left">
                                <span class="text-sm font-semibold text-slate-700">Email</span>
                            </th>
                            <th class="py-4 px-6 text-left">
                                <span class="text-sm font-semibold text-slate-700">Aksi</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($siswaList as $index => $siswa)
                            <tr class="hover:bg-slate-50 transition-colors student-row">
                                <td class="py-4 px-6">
                                    <span class="text-slate-700">{{ $index + 1 }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center">
                                        <div>
                                            <p class="font-medium text-slate-800 student-name">
                                                {{ $siswa['nama'] ?? 'Nama tidak tersedia' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="font-mono text-slate-700 student-nis">{{ $siswa['nis'] ?? '-' }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="text-sm text-slate-500 student-email">{{ $siswa['email'] ?? '-' }}</p>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('class.student-detail', ['kelas' => $kelasInfo['nama'], 'siswa' => $siswa['id']]) }}"
                                            class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                            Lihat Detail
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center">
                                    <div class="w-20 h-20 mx-auto mb-4 text-slate-400">
                                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-slate-700 mb-2">Tidak ada siswa</h3>
                                    <p class="text-slate-500">Belum ada siswa yang terdaftar di kelas ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t bg-slate-50 flex justify-between items-center">
                <p class="text-sm text-slate-600">
                    Menampilkan <span class="font-semibold">{{ $siswaList->count() }}</span> siswa
                </p>
            </div>
        </div>
    </div>

    <script>
        // Fungsi pencarian
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.student-row');

            rows.forEach(row => {
                const name = row.querySelector('.student-name').textContent.toLowerCase();
                const email = row.querySelector('.student-email').textContent.toLowerCase();
                const nis = row.querySelector('.student-nis').textContent.toLowerCase();

                if (name.includes(searchTerm) || email.includes(searchTerm) || nis.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
@endsection
