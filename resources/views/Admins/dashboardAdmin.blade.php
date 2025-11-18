@extends('layouts.app')

@section('content')
    <!-- Modern dashboard with improved layout and styling -->
    <div class="p-6 space-y-6">
        <!-- Welcome section with modern gradient design -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 bg-black opacity-5"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between">
                    <div class="space-y-2">
                        <h1 class="text-3xl font-bold font-poppins">Selamat Datang, {{ Auth::user()->name }}!</h1>
                        <p class="text-blue-100 text-lg">Siap untuk mengelola data sekolah hari ini?</p>
                    </div>
                    <div class="hidden lg:block">
                        <div class="w-32 h-32 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-white" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics cards with modern design -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Guru Card -->
            <div class="bg-white rounded-xl p-6 card-shadow-lg border border-slate-200">
                <div class="flex items-center justify-between">
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-slate-600">Total Guru</p>
                        <p class="text-3xl font-bold text-slate-900">{{ $totalguru ?? 0 }}</p>
                        <p class="text-xs text-green-600 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Aktif
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" viewBox="0 0 640 512"
                            fill="currentColor">
                            <path
                                d="M208 352c-2.39 0-4.78.35-7.06 1.09C187.98 357.3 174.35 360 160 360c-14.35 0-27.98-2.7-40.95-6.91-2.28-.74-4.66-1.09-7.05-1.09C49.94 352-.33 402.48 0 464.62.14 490.88 21.73 512 48 512h224c26.27 0 47.86-21.12 48-47.38.33-62.14-49.94-112.62-112-112.62zm-48-32c53.02 0 96-42.98 96-96s-42.98-96-96-96-96 42.98-96 96 42.98 96 96 96zM592 0H208c-26.47 0-48 22.25-48 49.59V96c23.42 0 45.1 6.78 64 17.8V64h352v288h-64v-64H384v64h-76.24c19.1 16.69 33.12 38.73 39.69 64H592c26.47 0 48-22.25 48-49.59V49.59C640 22.25 618.47 0 592 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Siswa Card -->
            <div class="bg-white rounded-xl p-6 card-shadow-lg border border-slate-200">
                <div class="flex items-center justify-between">
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-slate-600">Total Siswa</p>
                        <p class="text-3xl font-bold text-slate-900">{{ $totalMurid ?? 0 }}</p>
                        <p class="text-xs text-green-600 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Terdaftar
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path
                                d="M11.7 2.805a.75.75 0 0 1 .6 0A60.65 60.65 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337 49.948 49.948 0 0 0-9.902 3.912l-.003.002c-.114.06-.227.119-.34.18a.75.75 0 0 1-.707 0A50.88 50.88 0 0 0 7.5 12.173v-.224c0-.131.067-.248.172-.311a54.615 54.615 0 0 1 4.653-2.52.75.75 0 0 0-.65-1.352 56.123 56.123 0 0 0-4.78 2.589 1.858 1.858 0 0 0-.859 1.228 49.803 49.803 0 0 0-4.634-1.527.75.75 0 0 1-.231-1.337A60.653 60.653 0 0 1 11.7 2.805Z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Guru Ditempatkan Card -->
            <div class="bg-white rounded-xl p-6 card-shadow-lg border border-slate-200">
                <div class="flex items-center justify-between">
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-slate-600">Guru Ditempatkan</p>
                        <p class="text-3xl font-bold text-slate-900">{{ $assignCount ?? 0 }}</p>
                        <p class="text-xs text-blue-600 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Sudah Ditempatkan
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Guru Belum Ditempatkan Card -->
            <div class="bg-white rounded-xl p-6 card-shadow-lg border border-slate-200">
                <div class="flex items-center justify-between">
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-slate-600">Belum Ditempatkan</p>
                        <p class="text-3xl font-bold text-slate-900">{{ $notAssignCount ?? 0 }}</p>
                        <p class="text-xs text-orange-600 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            Perlu Penempatan
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts section with modern layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Status Guru Chart -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl p-6 card-shadow-lg border border-slate-200">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 font-poppins">Status Penempatan Guru</h3>
                            <p class="text-sm text-slate-600">Distribusi penempatan guru</p>
                        </div>
                    </div>

                    <div id="pie-chart" class="h-64 w-56 lg:w-80"></div>

                    <!-- Modern legend -->
                    <div class="lg:mt-6 mt-0 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-blue-600 rounded-full"></div>
                                <span class="text-sm text-slate-600">Sudah Ditempatkan</span>
                            </div>
                            <span class="text-sm font-medium text-slate-900">{{ $assignCount ?? 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-blue-200 rounded-full"></div>
                                <span class="text-sm text-slate-600">Belum Ditempatkan</span>
                            </div>
                            <span class="text-sm font-medium text-slate-900">{{ $notAssignCount ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Siswa Chart -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl p-6 card-shadow-lg border border-slate-200">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 font-poppins">Statistik Siswa</h3>
                            <p class="text-sm text-slate-600">Data siswa per tahun ajaran</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-blue-600 rounded-full"></div>
                            <span class="text-sm text-slate-600">Jumlah Siswa</span>
                        </div>
                    </div>

                    <div id="bar-chart" class="h-[335px]"></div>
                </div>
            </div>
        </div>

        <!-- Quick actions section -->
        <div class="bg-white rounded-xl p-6 card-shadow-lg border border-slate-200">
            <h3 class="text-lg font-semibold text-slate-900 font-poppins mb-4">Aksi Cepat</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('teachers.index') }}"
                    class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200 group">
                    <div
                        class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-4 group-hover:bg-blue-700 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 640 512"
                            fill="currentColor">
                            <path
                                d="M208 352c-2.39 0-4.78.35-7.06 1.09C187.98 357.3 174.35 360 160 360c-14.35 0-27.98-2.7-40.95-6.91-2.28-.74-4.66-1.09-7.05-1.09C49.94 352-.33 402.48 0 464.62.14 490.88 21.73 512 48 512h224c26.27 0 47.86-21.12 48-47.38.33-62.14-49.94-112.62-112-112.62zm-48-32c53.02 0 96-42.98 96-96s-42.98-96-96-96-96 42.98-96 96 42.98 96 96 96zM592 0H208c-26.47 0-48 22.25-48 49.59V96c23.42 0 45.1 6.78 64 17.8V64h352v288h-64v-64H384v64h-76.24c19.1 16.69 33.12 38.73 39.69 64H592c26.47 0 48-22.25 48-49.59V49.59C640 22.25 618.47 0 592 0z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-slate-900">Kelola Guru</h4>
                        <p class="text-sm text-slate-600">Atur data dan penempatan guru</p>
                    </div>
                </a>

                <a href="/Students"
                    class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors duration-200 group">
                    <div
                        class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center mr-4 group-hover:bg-green-700 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path
                                d="M11.7 2.805a.75.75 0 0 1 .6 0A60.65 60.65 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337 49.948 49.948 0 0 0-9.902 3.912l-.003.002c-.114.06-.227.119-.34.18a.75.75 0 0 1-.707 0A50.88 50.88 0 0 0 7.5 12.173v-.224c0-.131.067-.248.172-.311a54.615 54.615 0 0 1 4.653-2.52.75.75 0 0 0-.65-1.352 56.123 56.123 0 0 0-4.78 2.589 1.858 1.858 0 0 0-.859 1.228 49.803 49.803 0 0 0-4.634-1.527.75.75 0 0 1-.231-1.337A60.653 60.653 0 0 1 11.7 2.805Z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-slate-900">Kelola Siswa</h4>
                        <p class="text-sm text-slate-600">Atur data siswa dan kelas</p>
                    </div>
                </a>

                <a href="{{ route('classes.index') }}"
                    class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors duration-200 group">
                    <div
                        class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center mr-4 group-hover:bg-purple-700 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path fillRule="evenodd"
                                d="M4.5 2.25a.75.75 0 0 0 0 1.5v16.5h-.75a.75.75 0 0 0 0 1.5h16.5a.75.75 0 0 0 0-1.5h-.75V3.75a.75.75 0 0 0 0-1.5h-15ZM9 6a.75.75 0 0 0 0 1.5h1.5a.75.75 0 0 0 0-1.5H9Zm-.75 3.75A.75.75 0 0 1 9 9h1.5a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75ZM9 12a.75.75 0 0 0 0 1.5h1.5a.75.75 0 0 0 0-1.5H9Zm3.75-5.25A.75.75 0 0 1 13.5 6H15a.75.75 0 0 1 0 1.5h-1.5a.75.75 0 0 1-.75-.75ZM13.5 9a.75.75 0 0 0 0 1.5H15A.75.75 0 0 0 15 9h-1.5Zm-.75 3.75a.75.75 0 0 1 .75-.75H15a.75.75 0 0 1 0 1.5h-1.5a.75.75 0 0 1-.75-.75ZM9 19.5v-2.25a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-.75.75h-4.5A.75.75 0 0 1 9 19.5Z"
                                clipRule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-slate-900">Kelola Kelas</h4>
                        <p class="text-sm text-slate-600">Atur kelas dan mata pelajaran</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Updated chart scripts with modern styling -->
    <script>
        const getChartOptions = () => {
            return {
                series: [{{ $assignCount ?? 0 }}, {{ $notAssignCount ?? 0 }}],
                colors: ["#2563eb", "#93c5fd"],
                chart: {
                    type: "donut",
                },
                stroke: {
                    colors: ["transparent"],
                    width: 2,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            background: 'transparent',
                        },
                        labels: {
                            show: false,
                        },
                        dataLabels: {
                            offset: -50
                        }
                    },
                },
                labels: ["Sudah Ditempatkan", "Belum Ditempatkan"],
                dataLabels: {
                    enabled: true,
                    style: {
                        fontFamily: "Inter, sans-serif",
                        fontWeight: 500,
                    },
                    formatter: function(val) {
                        return Math.round(val) + "%"
                    }
                },
                legend: {
                    show: false,
                },
                tooltip: {
                    style: {
                        fontFamily: "Inter, sans-serif",
                    }
                }
            };
        };

        if (document.getElementById("pie-chart") && typeof ApexCharts !== 'undefined') {
            const chart = new ApexCharts(document.getElementById("pie-chart"), getChartOptions());
            chart.render();
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const totals = {!! json_encode($totals ?? [100, 150, 200, 180, 220]) !!}

            const chartConfig = {
                series: [{
                    name: "Total Siswa",
                    data: totals,
                }],
                chart: {
                    type: "bar",
                    height: 320,
                    toolbar: {
                        show: false,
                    },
                    fontFamily: "Inter, sans-serif",
                },
                dataLabels: {
                    enabled: false,
                },
                colors: ["#2563eb"],
                plotOptions: {
                    bar: {
                        columnWidth: "50%",
                        borderRadius: 4,
                    },
                },
                xaxis: {
                    categories: ['2024', '2025', '2026', '2027', '2028'],
                    labels: {
                        style: {
                            colors: "#64748b",
                            fontSize: "12px",
                            fontFamily: "Inter, sans-serif",
                            fontWeight: 500,
                        },
                    },
                    axisBorder: {
                        show: false,
                    },
                    axisTicks: {
                        show: false,
                    },
                },
                yaxis: {
                    min: 0,
                    max: Math.max(...totals) + 100,
                    labels: {
                        style: {
                            colors: "#64748b",
                            fontSize: "12px",
                            fontFamily: "Inter, sans-serif",
                            fontWeight: 500,
                        },
                    },
                },
                grid: {
                    borderColor: "#e2e8f0",
                    strokeDashArray: 4,
                    xaxis: {
                        lines: {
                            show: false,
                        },
                    },
                    yaxis: {
                        lines: {
                            show: true,
                        },
                    },
                },
                tooltip: {
                    theme: "light",
                    style: {
                        fontFamily: "Inter, sans-serif",
                    }
                },
            };

            const chart = new ApexCharts(document.querySelector("#bar-chart"), chartConfig);
            chart.render();
        });
    </script>
@endsection
