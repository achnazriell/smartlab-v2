@extends('layouts.appSiswa')

@section('content')
    {{-- Restructured layout with proper responsive grid and consistent spacing --}}
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-50 p-4 sm:p-6 lg:p-8">
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-6">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-blue-900">Dashboard</h1>
            <p class="text-sm sm:text-base text-gray-600" id="current-date"></p>
        </div>

        {{-- Main grid layout - 1 column on mobile, 2 columns on lg screens --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
            {{-- Right Column - Profile Panel (full width on mobile, sidebar on lg) --}}
            <div class="lg:col-span-1">
                <div class="rounded-2xl shadow-md overflow-hidden h-full"
                    style="background-image: url('image/wallpaper_blue.jpeg'); background-size: cover; background-position: center;">
                    <div class="p-5 sm:p-6 flex flex-col items-center text-white min-h-[300px] sm:min-h-[350px]">
                        {{-- Avatar --}}
                        <div class="flex flex-col items-center mb-4">
                            <svg class="w-20 h-20 sm:w-24 sm:h-24" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd"
                                    d="M12 20a7.966 7.966 0 0 1-5.002-1.756l.002.001v-.683c0-1.794 1.492-3.25 3.333-3.25h3.334c1.84 0 3.333 1.456 3.333 3.25v.683A7.966 7.966 0 0 1 12 20ZM2 12C2 6.477 6.477 2 12 2s10 4.477 10 10c0 5.5-4.44 9.963-9.932 10h-.138C6.438 21.962 2 17.5 2 12Zm10-5c-1.84 0-3.333 1.455-3.333 3.25S10.159 13.5 12 13.5c1.84 0 3.333-1.455 3.333-3.25S13.841 7 12 7Z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="text-lg sm:text-xl font-semibold mt-3 text-center">{{ Auth::user()->name }}</span>
                            <span
                                class="bg-blue-200 text-blue-800 font-bold text-xs sm:text-sm px-4 py-1 rounded-full mt-2">
                                Murid
                            </span>
                        </div>

                        {{-- Profile Details --}}
                        <div class="w-full mt-4 space-y-3 text-sm sm:text-base">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                        d="M7.402 4.5C7 5.196 7 6.13 7 8v3.027C7.43 11 7.914 11 8.435 11h7.13c.52 0 1.005 0 1.435.027V8c0-1.87 0-2.804-.402-3.5A3 3 0 0 0 15.5 3.402C14.804 3 13.87 3 12 3s-2.804 0-3.5.402A3 3 0 0 0 7.402 4.5M6.25 15.991c-.502-.02-.806-.088-1.014-.315c-.297-.324-.258-.774-.18-1.675c.055-.65.181-1.088.467-1.415C6.035 12 6.858 12 8.505 12h6.99c1.647 0 2.47 0 2.982.586c.286.326.412.764.468 1.415c.077.9.116 1.351-.181 1.675c-.208.227-.512.295-1.014.315V21a.75.75 0 1 1-1.5 0v-5h-8.5v5a.75.75 0 1 1-1.5 0z" />
                                </svg>
                                <div>
                                    <span class="font-semibold">Kelas:</span>
                                    @forelse ($class as $kelas)
                                        {{ $kelas->name_class }}{{ !$loop->last ? ',' : '' }}
                                    @empty
                                        Belum Dapat Kelas
                                    @endforelse
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                        d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2m0 4l-8 5l-8-5V6l8 5l8-5z" />
                                </svg>
                                <div class="break-all">
                                    <span class="font-semibold">Email:</span> {{ Auth::user()->email }}
                                </div>
                            </div>
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                        d="M7.75 2.5a.75.75 0 0 0-1.5 0v1.58c-1.44.115-2.384.397-3.078 1.092c-.695.694-.977 1.639-1.093 3.078h19.842c-.116-1.44-.398-2.384-1.093-3.078c-.694-.695-1.639-.977-3.078-1.093V2.5a.75.75 0 0 0-1.5 0v1.513C15.585 4 14.839 4 14 4h-4c-.839 0-1.585 0-2.25.013z" />
                                    <path fill="currentColor" fill-rule="evenodd"
                                        d="M2 12c0-.839 0-1.585.013-2.25h19.974C22 10.415 22 11.161 22 12v2c0 3.771 0 5.657-1.172 6.828S17.771 22 14 22h-4c-3.771 0-5.657 0-6.828-1.172S2 17.771 2 14zm15 2a1 1 0 1 0 0-2a1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2a1 1 0 0 0 0 2m-4-5a1 1 0 1 1-2 0a1 1 0 0 1 2 0m0 4a1 1 0 1 1-2 0a1 1 0 0 1 2 0m-6-3a1 1 0 1 0 0-2a1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2a1 1 0 0 0 0 2"
                                        clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <span class="font-semibold">Masa Berakhir:</span>
                                    {{ \Carbon\Carbon::parse(Auth::user()->graduation_date)->translatedFormat('j F Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Left Column - Welcome Banner & Task Cards --}}
            <div class="lg:col-span-2 flex flex-col gap-4 sm:gap-6">
                {{-- Welcome Banner --}}
                <div class="relative bg-white rounded-2xl shadow-md overflow-hidden min-h-[180px] sm:min-h-[200px]">
                    <img src="image/dahboardsiswa.svg" alt="dashboard1" class="absolute inset-0 w-full h-full object-cover">
                    <div class="relative z-10 p-5 sm:p-8 flex flex-col justify-center h-full">
                        <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 mb-2">
                            Selamat Datang, {{ Auth::User()->name }}!
                        </h2>
                        @if ($class->isNotEmpty())
                            <div class="flex flex-wrap items-center gap-1 text-sm sm:text-base text-gray-700">
                                <span>Selamat Datang Di Kelas:</span>
                                @foreach ($class as $kelas)
                                    <span class="font-semibold">{{ $kelas->name_class }}{{ !$loop->last ? ',' : '' }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm sm:text-base text-gray-600">Belum Dapat Kelas</p>
                        @endif
                    </div>
                </div>

                {{-- Task Cards Grid - stack on mobile, side by side on sm+ --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    {{-- Card Tugas Belum Dikerjakan --}}
                    <a href="{{ route('Tugas', ['status' => 'Belum mengumpulkan']) }}"
                        class="block rounded-2xl shadow-md overflow-hidden transition-transform hover:scale-[1.02] hover:shadow-lg"
                        style="background: url('image/bg.tugas1.svg') center/cover;">
                        <div class="p-4 sm:p-5 text-white min-h-[160px] sm:min-h-[180px] flex flex-col justify-between">
                            {{-- Header dengan Icon --}}
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
                            {{-- Angka Statistik --}}
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
                        style="background: url('image/bg.tugas1.svg') center/cover;">
                        <div class="p-4 sm:p-5 text-white min-h-[160px] sm:min-h-[180px] flex flex-col justify-between">
                            {{-- Header dengan Icon --}}
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
                            {{-- Angka Statistik --}}
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
        </div>
    </div>

    {{-- Date Script --}}
    <script>
        function updateDate() {
            const dateElement = document.getElementById("current-date");
            const today = new Date();
            const daysOfWeek = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
            const dayName = daysOfWeek[today.getDay()];
            const options = {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            };
            dateElement.textContent = `${dayName}, ${today.toLocaleDateString('id-ID', options)}`;
        }
        updateDate();
    </script>
@endsection
