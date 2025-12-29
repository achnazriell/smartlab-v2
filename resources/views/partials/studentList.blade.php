    <div id="table-container">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white text-left rounded-lg">
                <thead>
                    <tr class="border">
                        <th class="px-4 py-2 text-gray-500 text-sm font-bold">No</th>
                        <th class="px-4 py-2 text-gray-500 text-sm font-bold">Nama Murid</th>
                        <th class="px-4 py-2 text-gray-500 text-sm font-bold">Email</th>
                    </tr>
                </thead>
                <tbody>

                    @php
                        $globalIndex = ($students->currentPage() - 1) * $students->perPage();
                    @endphp

                    @if ($teacherClass->classes->users()->count() > 0) <!-- Periksa jika ada siswa -->
                        @foreach ($students as $student)
                            <tr class="border">
                                <td class="px-4 py-2">{{ $globalIndex + $loop->iteration }}</td>
                                <td class="px-4 py-2">{{ $student->user->name }}</td>
                                <td class="px-4 py-2">{{ $student->user->email }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="border">
                            <td class="px-4 py-2" colspan="3">Belum Ada Murid di Kelas ini</td>
                        </tr>
                    @endif


                </tbody>
            </table>
        </div>
        <div class="pagination px-5 py-3">
            <!-- Di dalam studentList.blade.php, pastikan pagination sudah benar -->
            @if ($students->hasPages())
                <div class="pagination">
                    {{ $students->withQueryString()->links() }}
                    <!-- Menambahkan denganQueryString() untuk menjaga URL tetap utuh -->
                </div>
            @endif
        </div>
    </div>
