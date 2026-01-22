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
            <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Guru</p>
                        <p class="text-3xl font-bold text-slate-900">{{ $totalGuru ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="#2563EB"
                            stroke="currentColor"
                            viewBox="0 0 640 640"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                            <path
                                d="M192 144C222.9 144 248 118.9 248 88C248 57.1 222.9 32 192 32C161.1 32 136 57.1 136 88C136 118.9 161.1 144 192 144zM176 576L176 416C176 407.2 183.2 400 192 400C200.8 400 208 407.2 208 416L208 576C208 593.7 222.3 608 240 608C257.7 608 272 593.7 272 576L272 240L400 240C417.7 240 432 225.7 432 208C432 190.3 417.7 176 400 176L384 176L384 128L576 128L576 320L384 320L384 288L320 288L320 336C320 362.5 341.5 384 368 384L592 384C618.5 384 640 362.5 640 336L640 112C640 85.5 618.5 64 592 64L368 64C341.5 64 320 85.5 320 112L320 176L197.3 176C151.7 176 108.8 197.6 81.7 234.2L14.3 324.9C3.8 339.1 6.7 359.1 20.9 369.7C35.1 380.3 55.1 377.3 65.7 363.1L112 300.7L112 576C112 593.7 126.3 608 144 608C161.7 608 176 593.7 176 576z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Siswa Card -->
            <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Siswa</p>
                        <p class="text-3xl font-bold text-slate-900">{{ $totalSiswa ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Kelas Card -->
            <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Kelas</p>
                        <p class="text-3xl font-bold text-slate-900">{{ $totalClasses ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Mata Pelajaran Card -->
            <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Mata Pelajaran</p>
                        <p class="text-3xl font-bold text-slate-900">{{ $totalSubjects ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main statistical chart section only -->
        <div class="grid grid-cols-1 gap-6">
            <!-- Data Siswa Chart -->
            <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 font-poppins tracking-tight">Pertumbuhan Siswa</h3>
                        <p class="text-sm text-slate-500">Statistik jumlah siswa per tahun akademik</p>
                    </div>
                    <div class="flex items-center space-x-3 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100">
                        <div class="w-2.5 h-2.5 bg-blue-600 rounded-full"></div>
                        <span class="text-xs font-bold text-slate-600 uppercase">Jumlah Siswa</span>
                    </div>
                </div>

                <div id="bar-chart" class="h-[350px]"></div>
            </div>
        </div>
    </div>

    <style>
        /* Custom scrollbar styling for a cleaner look */
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const totals = {!! json_encode($totals) !!}

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
                    max: Math.max(...totals) + 5,
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
                    show: true,
                    borderColor: "#f1f5f9",
                    strokeDashArray: 4,
                    padding: {
                        left: 20,
                        right: 20,
                        top: 0,
                    },
                },
                fill: {
                    opacity: 1,
                },
                tooltip: {
                    theme: "light",
                    y: {
                        formatter: function(val) {
                            return val + " Siswa"
                        },
                    },
                },
            };

            if (document.getElementById("bar-chart") && typeof ApexCharts !== 'undefined') {
                const chart = new ApexCharts(document.getElementById("bar-chart"), chartConfig);
                chart.render();
            }
        });
    </script>
@endsection
