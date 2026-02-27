@extends('layouts.appSiswa')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 p-4 sm:p-6 lg:p-8">
        <div id="loadingScreen" class="fixed inset-0 bg-white z-50 flex justify-center items-center">
            <div class="loader border-t-4 border-blue-600 rounded-full w-16 h-16 animate-spin"></div>
        </div>

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-8">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-blue-950">Dashboard</h1>
            <p class="text-sm sm:text-base text-gray-500 font-medium" id="current-date"></p>
        </div>

        {{-- Main grid layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
            {{-- Left Column - Welcome Banner & Task Cards --}}
            <div class="lg:col-span-2 flex flex-col gap-4 sm:gap-6">
                {{-- Welcome Banner --}}
                <div class="relative bg-white rounded-2xl shadow-md overflow-hidden min-h-[160px] sm:min-h-[200px]">
                    <img src="{{ asset('image/banner dashboard siswa.webp') }}" alt="dashboard1"
                        class="absolute inset-0 w-full h-full object-cover">
                    <div class="relative z-10 p-5 sm:p-8 flex flex-col justify-center h-full min-h-[160px] sm:min-h-[200px]">
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 mb-2">
                            Selamat Datang, {{ Auth::User()->name }}!
                        </h2>
                        @if (!empty($class))
                            <div class="flex flex-wrap items-center gap-1 text-sm sm:text-base text-gray-700">
                                <span>Selamat Datang Di Kelas:</span>
                                <span class="font-semibold">{{ $class }}</span>
                            </div>
                        @else
                            <p class="text-sm sm:text-base text-gray-600">Belum Dapat Kelas</p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    {{-- Card Tugas Belum Dikerjakan --}}
                    <a href="{{ route('Tugas', ['status' => 'Belum mengumpulkan']) }}"
                        class="block rounded-2xl shadow-md overflow-hidden transition-transform hover:scale-[1.02] hover:shadow-lg"
                        style="background: url('{{ asset('image/bg.tugas1.webp') }}') center/cover;">
                        <div class="p-4 sm:p-5 text-white min-h-[160px] sm:min-h-[180px] flex flex-col justify-between">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 sm:w-12 sm:h-12"
                                        viewBox="0 0 20 20">
                                        <path fill="currentColor"
                                            d="M5.75 3A2.75 2.75 0 0 0 3 5.75v8.5A2.75 2.75 0 0 0 5.75 17H9.6a5.5 5.5 0 0 1-.6-2.5c0-1.33.472-2.55 1.257-3.5H9.5a.5.5 0 0 1 0-1h1.837c.895-.63 1.986-1 3.163-1c.9 0 1.75.216 2.5.6V5.75A2.75 2.75 0 0 0 14.25 3zM7.5 7.25a.75.75 0 1 1-1.5 0a.75.75 0 0 1 1.5 0M6.75 9.5a.75.75 0 1 1 0 1.5a.75.75 0 0 1 0-1.5m.75 3.75a.75.75 0 1 1-1.5 0a.75.75 0 0 1 1.5 0M9.5 8a.5.5 0 0 1 0-1h4a.5.5 0 0 1 0 1zm5 11a4.5 4.5 0 1 0 0-9a4.5 4.5 0 0 0 0 9m-.5-6.5a.5.5 0 0 1 1 0V14h1a.5.5 0 0 1 0 1h-1.5a.5.5 0 0 1-.5-.5z" />
                                    </svg>
                                </div>
                                <span class="font-semibold text-sm sm:text-base leading-tight">
                                    Tugas yang Belum Dikerjakan
                                </span>
                            </div>
                            <div class="flex items-end gap-2 mt-4">
                                <span class="text-5xl sm:text-6xl lg:text-7xl font-bold leading-none">
                                    {{ $countNotCollected }}
                                </span>
                                <span class="text-base sm:text-lg font-medium mb-2">Tugas</span>
                            </div>
                        </div>
                    </a>

                    {{-- Card Tugas Sudah Dikerjakan --}}
                    <a href="{{ route('Tugas', ['status' => 'Sudah mengumpulkan']) }}"
                        class="block rounded-2xl shadow-md overflow-hidden transition-transform hover:scale-[1.02] hover:shadow-lg"
                        style="background: url('{{ asset('image/bg.tugas1.webp') }}') center/cover;">
                        <div class="p-4 sm:p-5 text-white min-h-[160px] sm:min-h-[180px] flex flex-col justify-between">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 sm:w-12 sm:h-12"
                                        viewBox="0 0 20 20">
                                        <path fill="currentColor"
                                            d="M4 4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v9.883l-2.495 2.52l-.934-.953a1.5 1.5 0 1 0-2.142 2.1l.441.45H6a2 2 0 0 1-2-2zm5 5.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4a.5.5 0 0 0-.5.5M9.5 5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1zM9 13.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 0 0-1h-4a.5.5 0 0 0-.5.5m-2-3a1 1 0 1 0 0-2a1 1 0 0 0 0 2m1-5a1 1 0 1 0-2 0a1 1 0 0 0 2 0m-1 9a1 1 0 1 0 0-2a1 1 0 0 0 0 2m10.855.352a.5.5 0 0 0-.71-.704l-3.643 3.68l-1.645-1.678a.5.5 0 1 0-.714.7l1.929 1.968a.6.6 0 0 0 .855.002z" />
                                    </svg>
                                </div>
                                <span class="font-semibold text-sm sm:text-base leading-tight">
                                    Tugas yang Sudah Dikerjakan
                                </span>
                            </div>
                            <div class="flex items-end gap-2 mt-4">
                                <span class="text-5xl sm:text-6xl lg:text-7xl font-bold leading-none">
                                    {{ $countCollected }}
                                </span>
                                <span class="text-base sm:text-lg font-medium mb-2">Tugas</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Right Column - Aktivitas Terakhir --}}
            <div class="flex flex-col gap-4 sm:gap-6">
                <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4 sm:p-6">
                        <h3 class="text-lg font-bold text-white">Aktivitas Terakhir</h3>
                    </div>
                    <div class="p-4 sm:p-6 space-y-4 max-h-[300px] overflow-y-auto">
                        @if(isset($recentActivities) && count($recentActivities) > 0)
                            @foreach($recentActivities as $activity)
                                <div class="flex items-start gap-3 pb-4 border-b border-gray-200 last:border-b-0 hover:bg-gray-50 rounded-lg p-2 -mx-2 transition-colors">
                                    <div class="flex-shrink-0 w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-900">
                                            <span class="font-semibold">{{ $activity['title'] }}</span>
                                            @if(isset($activity['subtitle']))
                                                <br><span class="text-xs text-gray-600">{{ $activity['subtitle'] }}</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $activity['time'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300 mx-auto mb-3"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.801 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.801 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                </svg>
                                <p class="text-gray-500 text-sm mb-1">Belum ada aktivitas terbaru</p>
                                <p class="text-xs text-gray-400">Mulai kerjakan tugas untuk melihat aktivitas di sini</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateDate() {
            const dateElement = document.getElementById("current-date");
            const today = new Date();
            const daysOfWeek = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
            const dayName = daysOfWeek[today.getDay()];
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            dateElement.textContent = `${dayName}, ${today.toLocaleDateString('id-ID', options)}`;
        }
        updateDate();
        window.addEventListener('load', function () {
            const loadingScreen = document.getElementById('loadingScreen');
            if (loadingScreen) loadingScreen.classList.add('hidden');
        });
    </script>
@endsection
