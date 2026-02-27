<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitur - Smart-LAB</title>
    <link rel="icon" type="image/webp" href="{{ asset('image/logo.webp') }}">
    @vite([ 'resources/js/app.js'])
    <!-- Tailwind CSS -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Alpine JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        .feature-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 24px;
            overflow: hidden;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            position: relative;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #3b82f6 0%, #0ea5e9 100%);
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 25px 50px -12px rgba(59, 130, 246, 0.25);
        }

        .gradient-hero {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #0ea5e9 100%);
            position: relative;
        }

        .gradient-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .float-animation {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .feature-icon {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(14, 165, 233, 0.1) 100%);
            transition: all 0.4s ease;
        }

        .feature-card:hover .feature-icon {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2) 0%, rgba(14, 165, 233, 0.2) 100%);
            transform: scale(1.1) rotate(5deg);
        }

        .section-badge {
            display: inline-block;
            padding: 8px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #0ea5e9 100%);
            color: white;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .feature-list-item {
            transition: all 0.3s ease;
            padding-left: 0;
        }

        .feature-list-item:hover {
            padding-left: 8px;
        }

        .cta-button {
            background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px -10px rgba(255, 255, 255, 0.3);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px -10px rgba(255, 255, 255, 0.5);
        }

        .category-badge {
            font-size: 11px;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
    </style>
</head>

<body class="bg-slate-50" x-data="{ activeCategory: 'all' }">
    <!-- Navbar -->
    <div class="navbar-container">
        <x-navbar></x-navbar>
    </div>

    <!-- Hero Section -->
    <section class="gradient-hero text-white relative overflow-hidden pt-24 pb-24 lg:pt-36 lg:pb-36">
        <div class="container mx-auto px-4 lg:px-6 relative z-10">
            <div class="max-w-5xl mx-auto text-center">
                <span class="section-badge float-animation">Fitur Unggulan</span>
                <h1 class="text-4xl md:text-5xl lg:text-7xl font-bold mb-8 leading-tight tracking-tight">
                    Platform Pembelajaran
                    <span class="block bg-gradient-to-r from-yellow-200 to-orange-300 bg-clip-text text-transparent">Serba Lengkap</span>
                </h1>
                <p class="text-lg md:text-xl lg:text-2xl opacity-95 font-light leading-relaxed max-w-3xl mx-auto">
                    Fitur canggih yang dirancang khusus untuk memberikan pengalaman pembelajaran yang interaktif, efektif, dan menyenangkan
                </p>
            </div>
        </div>
    </section>

    <!-- Feature Categories -->
    <section class="py-12 bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="flex flex-wrap justify-center gap-3">
                <button @click="activeCategory = 'all'"
                    :class="activeCategory === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-6 py-3 rounded-full font-semibold text-sm transition-all duration-300">
                    Semua Fitur
                </button>
                <button @click="activeCategory = 'learning'"
                    :class="activeCategory === 'learning' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-6 py-3 rounded-full font-semibold text-sm transition-all duration-300">
                    Pembelajaran
                </button>
                <button @click="activeCategory = 'assessment'"
                    :class="activeCategory === 'assessment' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-6 py-3 rounded-full font-semibold text-sm transition-all duration-300">
                    Evaluasi
                </button>
                <button @click="activeCategory = 'management'"
                    :class="activeCategory === 'management' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-6 py-3 rounded-full font-semibold text-sm transition-all duration-300">
                    Manajemen
                </button>
                <button @click="activeCategory = 'analytics'"
                    :class="activeCategory === 'analytics' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-6 py-3 rounded-full font-semibold text-sm transition-all duration-300">
                    Analitik
                </button>
            </div>
        </div>
    </section>

    <!-- Features Grid Section -->
    <section class="py-20 lg:py-32 bg-gradient-to-br from-slate-50 to-blue-50">
        <div class="container mx-auto px-4 lg:px-6">
            <!-- Section Title -->
            <div class="max-w-4xl mx-auto text-center mb-16">
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                    Fitur yang Dirancang untuk Kesuksesan Anda
                </h2>
                <p class="text-xl text-gray-600">
                    Setiap fitur dikembangkan dengan fokus pada kemudahan penggunaan dan hasil pembelajaran yang optimal
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">

                <!-- Feature 1: Interactive Quiz System -->
                <div class="feature-card p-8 lg:p-10 border border-gray-100 shadow-lg"
                    x-show="activeCategory === 'all' || activeCategory === 'assessment'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">
                    <span class="category-badge bg-purple-100 text-purple-700 mb-4 inline-block">Evaluasi</span>
                    <div class="feature-icon w-20 h-20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">Quiz Interaktif</h3>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed mb-6">
                        Sistem quiz canggih dengan mode live dan homework, dilengkapi dengan anti-cheating, proctoring, dan analytics real-time.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Mode Live Quiz dengan timer</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Anti-cheating & violation tracking</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Leaderboard & instant feedback</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Multiple question types (PG, Essay)</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature 2: Digital Material Management -->
                <div class="feature-card p-8 lg:p-10 border border-gray-100 shadow-lg"
                    x-show="activeCategory === 'all' || activeCategory === 'learning'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">
                    <span class="category-badge bg-blue-100 text-blue-700 mb-4 inline-block">Pembelajaran</span>
                    <div class="feature-icon w-20 h-20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">Materi Digital</h3>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed mb-6">
                        Akses dan kelola materi pembelajaran dalam berbagai format dengan sistem yang terorganisir berdasarkan kelas dan mata pelajaran.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Upload PDF, Video, PPT</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Organisasi per kelas & mapel</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Preview & download materi</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Version control materi</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature 3: Assignment Management -->
                <div class="feature-card p-8 lg:p-10 border border-gray-100 shadow-lg"
                    x-show="activeCategory === 'all' || activeCategory === 'learning'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">
                    <span class="category-badge bg-green-100 text-green-700 mb-4 inline-block">Pembelajaran</span>
                    <div class="feature-icon w-20 h-20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">Manajemen Tugas</h3>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed mb-6">
                        Sistem penugasan lengkap dengan tracking status pengumpulan, penilaian otomatis, dan feedback untuk setiap siswa.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Upload & submit tugas online</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Deadline reminder otomatis</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Status tracking real-time</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Grading & feedback system</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature 4: Academic Management -->
                <div class="feature-card p-8 lg:p-10 border border-gray-100 shadow-lg"
                    x-show="activeCategory === 'all' || activeCategory === 'management'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">
                    <span class="category-badge bg-orange-100 text-orange-700 mb-4 inline-block">Manajemen</span>
                    <div class="feature-icon w-20 h-20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">Manajemen Akademik</h3>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed mb-6">
                        Kelola tahun ajaran, kelas, jurusan, dan mata pelajaran dengan sistem yang terstruktur dan mudah digunakan.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Multi tahun ajaran</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Manajemen kelas & jurusan</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Database mata pelajaran</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Role management (Guru/Siswa)</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature 5: Analytics Dashboard -->
                <div class="feature-card p-8 lg:p-10 border border-gray-100 shadow-lg"
                    x-show="activeCategory === 'all' || activeCategory === 'analytics'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">
                    <span class="category-badge bg-indigo-100 text-indigo-700 mb-4 inline-block">Analitik</span>
                    <div class="feature-icon w-20 h-20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">Dashboard Analitik</h3>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed mb-6">
                        Pantau progres belajar dengan visualisasi data yang komprehensif dan insight mendalam untuk improvement berkelanjutan.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Real-time performance metrics</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Progress tracking per siswa</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Comparative analytics</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Export reports (PDF/Excel)</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature 6: Feedback System -->
                <div class="feature-card p-8 lg:p-10 border border-gray-100 shadow-lg"
                    x-show="activeCategory === 'all' || activeCategory === 'management'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">
                    <span class="category-badge bg-pink-100 text-pink-700 mb-4 inline-block">Manajemen</span>
                    <div class="feature-icon w-20 h-20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">Sistem Feedback</h3>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed mb-6">
                        Platform komunikasi dua arah untuk menyampaikan saran, kritik, dan rating yang membantu kami meningkatkan kualitas layanan.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Rating & review system</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Kategori feedback terorganisir</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Status tracking feedback</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Response & tindak lanjut</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature 7: Security & Privacy -->
                <div class="feature-card p-8 lg:p-10 border border-gray-100 shadow-lg"
                    x-show="activeCategory === 'all' || activeCategory === 'management'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">
                    <span class="category-badge bg-red-100 text-red-700 mb-4 inline-block">Keamanan</span>
                    <div class="feature-icon w-20 h-20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">Keamanan Data</h3>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed mb-6">
                        Sistem keamanan berlapis dengan enkripsi data, role-based access control, dan compliance dengan standar keamanan internasional.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Enkripsi end-to-end</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Role-based access control</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Regular security audits</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Backup & recovery system</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature 8: Multi-Device Support -->
                <div class="feature-card p-8 lg:p-10 border border-gray-100 shadow-lg"
                    x-show="activeCategory === 'all' || activeCategory === 'learning'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">
                    <span class="category-badge bg-cyan-100 text-cyan-700 mb-4 inline-block">Aksesibilitas</span>
                    <div class="feature-icon w-20 h-20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">Multi-Device</h3>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed mb-6">
                        Akses platform dari mana saja dengan sinkronisasi otomatis di semua perangkat untuk fleksibilitas maksimal.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Responsive design</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Auto-sync across devices</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Offline mode support</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>PWA capability</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature 9: Customization -->
                <div class="feature-card p-8 lg:p-10 border border-gray-100 shadow-lg"
                    x-show="activeCategory === 'all' || activeCategory === 'learning'"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">
                    <span class="category-badge bg-yellow-100 text-yellow-700 mb-4 inline-block">Personalisasi</span>
                    <div class="feature-icon w-20 h-20 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                            </path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">Personalisasi</h3>
                    <p class="text-gray-600 text-base lg:text-lg leading-relaxed mb-6">
                        Sesuaikan pengalaman belajar dengan preferensi personal untuk meningkatkan engagement dan efektivitas pembelajaran.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Adaptive learning path</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Customizable dashboard</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Notification preferences</span>
                        </li>
                        <li class="flex items-start text-gray-700 text-sm lg:text-base feature-list-item">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Theme customization</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="gradient-hero text-white py-20 lg:py-32 relative overflow-hidden">
        <div class="container mx-auto px-4 lg:px-6 text-center relative z-10">
            <span class="section-badge mb-6 float-animation">Mulai Sekarang</span>
            <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-8 leading-tight">
                Siap Mengalami Revolusi
                <span class="block">Pembelajaran Digital?</span>
            </h2>
            <p class="text-lg md:text-xl lg:text-2xl leading-relaxed mb-12 max-w-3xl mx-auto opacity-95">
                Bergabunglah dengan ribuan siswa yang telah merasakan transformasi belajar dengan Smart-LAB
            </p>
            <a href="{{ route('login') }}"
                class="cta-button inline-flex items-center space-x-3 text-blue-600 font-bold px-10 py-5 rounded-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 text-lg group">
                <span>Mulai Belajar Gratis</span>
                <svg class="w-6 h-6 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gradient-to-br from-gray-900 to-gray-800 text-white py-16 lg:py-20">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 pb-12 border-b border-gray-700">
                <div class="lg:col-span-2">
                    <img src="{{ asset('image/Smart-LAB white logo.webp') }}" alt="Smart-LAB" class="h-14 mb-6">
                    <p class="text-gray-300 text-base lg:text-lg leading-relaxed mb-6">
                        Platform pembelajaran digital terdepan yang mengintegrasikan teknologi modern untuk menciptakan pengalaman belajar yang transformatif.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-12 h-12 bg-gray-800 hover:bg-blue-600 rounded-full flex items-center justify-center transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-12 h-12 bg-gray-800 hover:bg-blue-400 rounded-full flex items-center justify-center transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-12 h-12 bg-gray-800 hover:bg-pink-600 rounded-full flex items-center justify-center transition-all duration-300">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="text-xl font-bold mb-6">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="{{ route('home') }}" class="text-gray-300 hover:text-white transition-colors">Beranda</a></li>
                        <li><a href="{{ route('features') }}" class="text-gray-300 hover:text-white transition-colors">Fitur</a></li>
                        <li><a href="{{ route('about') }}" class="text-gray-300 hover:text-white transition-colors">Tentang Kami</a></li>
                        <li><a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition-colors">Masuk</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xl font-bold mb-6">Hubungi Kami</h4>
                    <ul class="space-y-3 text-gray-300">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <a href="mailto:info@smartlab.id" class="hover:text-white transition-colors">info@smartlab.id</a>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span>+62 812-3456-7890</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 mr-2 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span>Surabaya, Indonesia</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 text-center">
                <p class="text-gray-400 text-sm lg:text-base">&copy; {{ date('Y') }} Smart-LAB. All Rights Reserved.</p>
                <p class="text-gray-500 text-xs lg:text-sm mt-2">Dikembangkan dengan ❤️ untuk masa depan pendidikan Indonesia</p>
            </div>
        </div>
    </footer>

    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
</body>

</html>
