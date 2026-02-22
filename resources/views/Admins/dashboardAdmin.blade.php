@extends('layouts.app')

@section('title', 'Dashboard Admin - Smart Lab')
@section('page-title', 'Dashboard')

@section('content')
<div class="p-6 space-y-6">

    {{-- ===== WELCOME BANNER ===== --}}
    <div class="relative bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 rounded-2xl p-6 text-white overflow-hidden shadow-lg">
        <div class="absolute -right-12 -top-12 w-52 h-52 bg-white/10 rounded-full"></div>
        <div class="absolute -right-4 -bottom-16 w-40 h-40 bg-white/5 rounded-full"></div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <p class="text-blue-200 text-sm bg-gradie font-medium">{{ now()->translatedFormat('l, d F Y') }}</p>
                <h1 class="text-2xl font-bold font-poppins mt-0.5">Selamat Datang, {{ Auth::user()->name }}!</h1>
                <p class="text-blue-100 text-sm mt-1">Pantau dan kelola data sekolah dari satu tempat.</p>
            </div>
            <div class="hidden md:flex w-16 h-16 bg-white/20 rounded-2xl items-center justify-center flex-shrink-0">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ===== STAT CARDS ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('teachers.index') }}"
            class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md hover:border-blue-300 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Guru</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1 group-hover:text-blue-600 transition-colors">{{ $totalGuru ?? 0 }}</p>
                    <p class="text-xs text-blue-500 mt-1.5 font-medium">Lihat semua →</p>
                </div>
                <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 transition-colors">
                    <svg class="w-5 h-5 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </a>

        <a href="{{ route('students.index') }}"
            class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md hover:border-blue-300 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Siswa</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1 group-hover:text-blue-600 transition-colors">{{ $totalMurid ?? 0 }}</p>
                    <p class="text-xs text-blue-500 mt-1.5 font-medium">Lihat semua →</p>
                </div>
                <div class="w-11 h-11 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 transition-colors">
                    <svg class="w-5 h-5 text-indigo-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </div>
            </div>
        </a>

        <a href="{{ route('classes.index') }}"
            class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md hover:border-blue-300 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Kelas</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1 group-hover:text-blue-600 transition-colors">{{ $totalClasses ?? 0 }}</p>
                    <p class="text-xs text-blue-500 mt-1.5 font-medium">Lihat semua →</p>
                </div>
                <div class="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 transition-colors">
                    <svg class="w-5 h-5 text-emerald-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </a>

        <a href="{{ route('subject.index') }}"
            class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md hover:border-blue-300 transition-all group">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Mapel</p>
                    <p class="text-3xl font-bold text-slate-900 mt-1 group-hover:text-blue-600 transition-colors">{{ $totalSubjects ?? 0 }}</p>
                    <p class="text-xs text-blue-500 mt-1.5 font-medium">Lihat semua →</p>
                </div>
                <div class="w-11 h-11 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 transition-colors">
                    <svg class="w-5 h-5 text-amber-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
        </a>
    </div>

    {{-- ===== BAR CHART – full width ===== --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-bold text-slate-900 font-poppins">Pertumbuhan Siswa per Tahun</h3>
                <p class="text-sm text-slate-400 mt-0.5">Jumlah siswa terdaftar berdasarkan tahun pendaftaran</p>
            </div>
            <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200">
                <div class="w-2.5 h-2.5 bg-blue-600 rounded-full"></div>
                <span>Jumlah Siswa</span>
            </div>
        </div>
        <div id="bar-chart-wrap" class="w-full" style="height: 300px;">
            <div id="bar-chart" style="width:100%; height:100%;"></div>
        </div>
    </div>

    {{-- ===== BOTTOM ROW: Donut + Mini Cards ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Donut: Guru per Mapel --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <div class="mb-5">
                <h3 class="text-base font-bold text-slate-900 font-poppins">Distribusi Guru per Mapel</h3>
                <p class="text-sm text-slate-400 mt-0.5">Jumlah guru yang mengajar tiap mata pelajaran</p>
            </div>
            <div id="donut-chart-wrap" class="w-full" style="height: 280px;">
                <div id="donut-chart" style="width:100%; height:100%;"></div>
            </div>
        </div>

        {{-- Right column: 3 summary cards stacked --}}
        <div class="flex flex-col gap-4">

            {{-- Rasio Siswa per Kelas --}}
            @php
                $totalStudents = $totalMurid ?? 0;
                $totalCls      = $totalClasses ?? 0;
                $ratio         = $totalCls > 0 ? round($totalStudents / $totalCls, 1) : 0;
                $ratioPct      = min(100, $ratio > 0 ? round(($ratio / 40) * 100) : 0);
            @endphp
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex-1">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Rata-rata Siswa / Kelas</p>
                        <p class="text-3xl font-bold text-slate-900 mt-0.5">{{ $ratio }}</p>
                    </div>
                    <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2.5">
                    <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-700" style="width: {{ $ratioPct }}%"></div>
                </div>
                <p class="text-xs text-slate-400 mt-1.5">Kapasitas ideal: 30–40 siswa/kelas</p>
            </div>

            {{-- Kelas per Angkatan --}}
            @php
                use App\Models\Classes;
                $gradeX   = Classes::where('name_class', 'like', 'X %')->count();
                $gradeXI  = Classes::where('name_class', 'like', 'XI %')->count();
                $gradeXII = Classes::where('name_class', 'like', 'XII %')->count();
                $maxG     = max($gradeX, $gradeXI, $gradeXII, 1);
            @endphp
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex-1">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4">Kelas per Angkatan</p>
                <div class="space-y-3">
                    @foreach([['Kelas X', $gradeX, $maxG], ['Kelas XI', $gradeXI, $maxG], ['Kelas XII', $gradeXII, $maxG]] as [$lbl, $val, $max])
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold text-slate-600 w-14 shrink-0">{{ $lbl }}</span>
                        <div class="flex-1 bg-slate-100 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-700"
                                style="width: {{ $max > 0 ? round(($val/$max)*100) : 0 }}%"></div>
                        </div>
                        <span class="text-xs font-bold text-slate-900 w-5 text-right shrink-0">{{ $val }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Guru vs Mapel --}}
            @php
                $totalG   = $totalGuru ?? 0;
                $mapelCnt = count((array)($mapelLabels ?? []));
                $avgGuru  = $mapelCnt > 0 ? round($totalG / $mapelCnt, 1) : 0;
            @endphp
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 flex-1">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Rata-rata Guru / Mapel</p>
                <p class="text-3xl font-bold text-slate-900 mb-3">{{ $avgGuru }}</p>
                <div class="flex items-center gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-blue-600 rounded-full shrink-0"></div>
                        <span class="text-slate-600 text-xs">{{ $totalG }} guru</span>
                    </div>
                    <div class="text-slate-300">•</div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-slate-200 rounded-full shrink-0"></div>
                        <span class="text-slate-600 text-xs">{{ $mapelCnt }} mapel</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const totals      = {!! json_encode($totals ?? []) !!};
    const mapelLabels = {!! json_encode(array_slice((array)($mapelLabels ?? []), 0, 7)) !!};
    const mapelTotals = {!! json_encode(array_slice((array)($mapelTotals ?? []), 0, 7)) !!};

    // Derive year labels from count of data points
    const currentYear = new Date().getFullYear();
    const yearLabels  = totals.map((_, i) => String(currentYear - totals.length + 1 + i));
    const maxVal      = totals.length ? Math.max(...totals) : 10;

    // ── BAR CHART ───────────────────────────────────────────────────────
    const barEl = document.getElementById('bar-chart');
    if (barEl && typeof ApexCharts !== 'undefined') {
        const bar = new ApexCharts(barEl, {
            series: [{ name: 'Total Siswa', data: totals.length ? totals : [0] }],
            chart: {
                type: 'bar',
                height: '100%',
                width: '100%',
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
                redrawOnParentResize: true,
                redrawOnWindowResize: true,
            },
            dataLabels: {
                enabled: true,
                formatter: v => v > 0 ? v : '',
                style: { fontSize: '11px', fontWeight: '700', colors: ['#fff'] },
                background: { enabled: false },
                offsetY: -2,
            },
            colors: ['#2563eb'],
            plotOptions: {
                bar: {
                    columnWidth: '50%',
                    borderRadius: 7,
                    borderRadiusApplication: 'end',
                },
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'vertical',
                    gradientToColors: ['#93c5fd'],
                    stops: [0, 100],
                    opacityFrom: 1,
                    opacityTo: 0.85,
                },
            },
            xaxis: {
                categories: yearLabels.length ? yearLabels : ['—'],
                labels: { style: { colors: '#94a3b8', fontSize: '12px', fontWeight: 500 } },
                axisBorder: { show: false },
                axisTicks:  { show: false },
            },
            yaxis: {
                min: 0,
                max: maxVal + Math.max(Math.ceil(maxVal * 0.18), 2),
                tickAmount: 5,
                labels: {
                    style: { colors: '#94a3b8', fontSize: '12px' },
                    formatter: v => Number.isInteger(v) ? v : Math.round(v),
                },
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                xaxis: { lines: { show: false } },
                padding: { left: 4, right: 4, top: 4, bottom: 0 },
            },
            tooltip: {
                theme: 'light',
                y: { formatter: v => v + ' Siswa' },
            },
        });
        bar.render();

        // Reflow on resize
        let t1;
        window.addEventListener('resize', () => {
            clearTimeout(t1);
            t1 = setTimeout(() => bar.updateOptions({}), 250);
        });
    }

    // ── DONUT CHART ─────────────────────────────────────────────────────
    const donutEl = document.getElementById('donut-chart');
    if (donutEl) {
        if (typeof ApexCharts !== 'undefined' && mapelLabels.length > 0) {
            const donut = new ApexCharts(donutEl, {
                series: mapelTotals,
                chart: {
                    type: 'donut',
                    height: '100%',
                    width: '100%',
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif',
                    animations: { enabled: true, speed: 700 },
                    redrawOnParentResize: true,
                    redrawOnWindowResize: true,
                },
                labels: mapelLabels,
                colors: ['#1d4ed8','#2563eb','#3b82f6','#60a5fa','#93c5fd','#bfdbfe','#dbeafe'],
                legend: {
                    position: 'bottom',
                    fontSize: '12px',
                    fontWeight: 500,
                    labels: { colors: '#475569' },
                    markers: { width: 9, height: 9, radius: 3 },
                    itemMargin: { horizontal: 8, vertical: 3 },
                },
                dataLabels: {
                    enabled: true,
                    formatter: v => v.toFixed(1) + '%',
                    style: { fontSize: '11px', fontWeight: '700', colors: ['#fff'] },
                    dropShadow: { enabled: false },
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total Guru',
                                    fontSize: '12px',
                                    fontWeight: 600,
                                    color: '#64748b',
                                    formatter: () => mapelTotals.reduce((a, b) => a + b, 0),
                                },
                            },
                        },
                    },
                },
                stroke: { width: 0 },
                tooltip: {
                    theme: 'light',
                    y: { formatter: v => v + ' Guru' },
                },
            });
            donut.render();

            let t2;
            window.addEventListener('resize', () => {
                clearTimeout(t2);
                t2 = setTimeout(() => donut.updateOptions({}), 250);
            });
        } else {
            donutEl.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full text-slate-400">
                    <svg class="w-12 h-12 mb-3 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="text-sm">Belum ada data mapel</p>
                </div>`;
        }
    }
});
</script>

<style>
/* Force ApexCharts SVG to always fill its wrapper */
#bar-chart   > .apexcharts-canvas,
#donut-chart > .apexcharts-canvas { width: 100% !important; }

#bar-chart   > .apexcharts-canvas > svg,
#donut-chart > .apexcharts-canvas > svg { width: 100% !important; }
</style>
@endsection
