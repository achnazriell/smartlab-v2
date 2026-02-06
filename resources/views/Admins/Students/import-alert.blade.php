{{-- File: resources/views/components/import-alert.blade.php --}}
{{-- Letakkan di bagian atas view Admins/Students/index.blade.php --}}

@if(session('import_stats'))
    @php
        $stats = session('import_stats');
        $status = session('import_status', 'info');
    @endphp

    <div class="mb-6 animate-fade-in">
        <div class="space-y-4">

            {{-- Header Ringkasan --}}
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-200 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        @if($status === 'warning' || !empty($stats['errors']) || $stats['duplicate'] > 0)
                            <div class="bg-amber-100 p-2 rounded-lg">
                                <svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        @elseif($stats['imported'] > 0)
                            <div class="bg-green-100 p-2 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        @else
                            <div class="bg-blue-100 p-2 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        @endif

                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">Hasil Import Data Murid</h3>
                            <p class="text-sm text-slate-600 mt-1">Total <span class="font-semibold">{{ $stats['total_processed'] }}</span> data diproses</p>
                        </div>
                    </div>
                </div>

                {{-- Statistik Ringkas --}}
                <div class="flex flex-wrap gap-2">
                    @if($stats['imported'] > 0)
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-700">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $stats['imported'] }} Berhasil
                        </span>
                    @endif

                    @if($stats['duplicate'] > 0)
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-amber-100 text-amber-700">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $stats['duplicate'] }} Duplikat
                        </span>
                    @endif

                    @if($stats['skipped'] > 0)
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-slate-100 text-slate-700">
                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $stats['skipped'] }} Dilewati
                        </span>
                    @endif
                </div>
            </div>

            {{-- Data Berhasil --}}
            @if(!empty($stats['success_data']))
                <div class="border border-green-200 rounded-xl overflow-hidden bg-white shadow-sm">
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-4 py-3 border-b border-green-200">
                        <h4 class="font-semibold text-green-800 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            Data Berhasil Diimport
                            <span class="ml-2 bg-green-200 text-green-800 px-2.5 py-0.5 rounded-full text-xs font-medium">{{ $stats['imported'] }} murid</span>
                        </h4>
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">NIS</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Kelas</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach(array_slice($stats['success_data'], 0, 50) as $index => $item)
                                    <tr class="hover:bg-green-50 transition-colors duration-150">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 font-medium">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item['nama'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 font-mono">{{ $item['nis'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-blue-600 font-medium">
                                            {{ $item['kelas'] ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if(count($stats['success_data']) > 50)
                            <div class="bg-gradient-to-r from-gray-50 to-green-50 px-4 py-3 border-t border-gray-200">
                                <p class="text-sm text-gray-600 text-center flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                                    </svg>
                                    dan <strong class="mx-1">{{ count($stats['success_data']) - 50 }}</strong> data lainnya berhasil diimport
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Data Duplikat --}}
            @if($stats['duplicate'] > 0 && !empty($stats['duplicate_data']))
                <div class="border border-amber-200 rounded-xl overflow-hidden bg-white shadow-sm">
                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-4 py-3 border-b border-amber-200">
                        <h4 class="font-semibold text-amber-800 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Data Duplikat
                            <span class="ml-2 bg-amber-200 text-amber-800 px-2.5 py-0.5 rounded-full text-xs font-medium">{{ $stats['duplicate'] }} data</span>
                        </h4>
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">NIS</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Alasan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach(array_slice($stats['duplicate_data'], 0, 30) as $index => $item)
                                    <tr class="hover:bg-amber-50 transition-colors duration-150">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 font-medium">{{ $index + 1 }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $item['nama'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600 font-mono">{{ $item['nis'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-slate-600">{{ $item['email'] }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-xs">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-medium bg-amber-100 text-amber-800">
                                                {{ $item['reason'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        @if(count($stats['duplicate_data']) > 30)
                            <div class="bg-gradient-to-r from-gray-50 to-amber-50 px-4 py-3 border-t border-gray-200">
                                <p class="text-sm text-gray-600 text-center flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    dan <strong class="mx-1">{{ count($stats['duplicate_data']) - 30 }}</strong> data duplikat lainnya
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Kelas Dibuat Otomatis --}}
            @if(!empty($stats['created_classes']))
                <div class="border border-blue-200 rounded-xl overflow-hidden bg-white shadow-sm">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3 border-b border-blue-200">
                        <h4 class="font-semibold text-blue-800 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"></path>
                            </svg>
                            Kelas Dibuat Otomatis
                            <span class="ml-2 bg-blue-200 text-blue-800 px-2.5 py-0.5 rounded-full text-xs font-medium">{{ count($stats['created_classes']) }} kelas</span>
                        </h4>
                    </div>
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($stats['created_classes'] as $className => $count)
                                <div class="flex justify-between items-center bg-gradient-to-r from-blue-50 to-indigo-50 p-3 rounded-lg border border-blue-200 hover:shadow-md transition-shadow duration-200">
                                    <span class="font-semibold text-slate-700">{{ $className }}</span>
                                    <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-bold">{{ $count }} siswa</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Error Details --}}
            @if(!empty($stats['errors']))
                <div class="border border-red-200 rounded-xl overflow-hidden bg-white shadow-sm">
                    <div class="bg-gradient-to-r from-red-50 to-pink-50 px-4 py-3 border-b border-red-200">
                        <h4 class="font-semibold text-red-800 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            Kesalahan Validasi
                            <span class="ml-2 bg-red-200 text-red-800 px-2.5 py-0.5 rounded-full text-xs font-medium">{{ count($stats['errors']) }} error</span>
                        </h4>
                    </div>
                    <div class="max-h-72 overflow-y-auto">
                        <div class="divide-y divide-red-100">
                            @foreach(array_slice($stats['errors'], 0, 15) as $index => $error)
                                <div class="px-4 py-3 hover:bg-red-50 transition-colors duration-150 flex items-start">
                                    <span class="flex-shrink-0 inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-600 text-xs font-bold mr-3">{{ $index + 1 }}</span>
                                    <span class="text-sm text-gray-700">{{ $error }}</span>
                                </div>
                            @endforeach
                        </div>

                        @if(count($stats['errors']) > 15)
                            <div class="bg-gradient-to-r from-gray-50 to-red-50 px-4 py-3 border-t border-red-200">
                                <p class="text-sm text-gray-600 text-center flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    dan <strong class="mx-1">{{ count($stats['errors']) - 15 }}</strong> error lainnya
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>
@endif
