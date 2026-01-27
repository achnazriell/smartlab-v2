<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitur - Smart-LAB</title>
    <link rel="icon" type="image/webp" href="{{ asset('image/logo.webp') }}">

    <!-- Tailwind CSS -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Alpine JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        .feature-card {
            transition: all 0.3s ease;
            border-radius: 16px;
            overflow: hidden;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(59, 130, 246, 0.15);
        }

        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, #3b82f6, #0ea5e9);
            border-radius: 2px;
            margin-top: 1rem;
        }

        .gradient-hero {
            background: linear-gradient(135deg, #3b82f6 0%, #0ea5e9 100%);
        }

        .float-animation {
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
            }
        }
    </style>
</head>

<body class="bg-white" x-data="{ activeFeature: 1 }">
    <!-- Navbar -->
    <div class="navbar-container">
        <x-navbar></x-navbar>
    </div>

    <!-- Hero Section -->
    <section class="gradient-hero text-white relative overflow-hidden pt-20 pb-20 lg:pt-32 lg:pb-32">
        <div class="absolute inset-0 opacity-10">
            <div
                class="absolute top-10 left-10 lg:top-20 lg:left-20 w-64 h-64 lg:w-96 lg:h-96 bg-white rounded-full blur-3xl">
            </div>
            <div
                class="absolute bottom-10 right-10 lg:bottom-20 lg:right-20 w-64 h-64 lg:w-96 lg:h-96 bg-white rounded-full blur-3xl">
            </div>
        </div>

        <div class="container mx-auto px-4 lg:px-6 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold mb-6 lg:mb-8 leading-tight float-animation">
                    Fitur Unggulan Smart-LAB
                </h1>
                <p class="text-lg md:text-xl lg:text-2xl opacity-95 font-light leading-relaxed max-w-2xl mx-auto">
                    Temukan berbagai fitur canggih yang membuat pengalaman belajar lebih interaktif dan efektif
                </p>
            </div>
        </div>
    </section>

    <!-- Features Grid Section -->
    <section class="py-16 lg:py-24 bg-slate-50">
        <div class="container mx-auto px-4 lg:px-6">
            <!-- Section Title -->
            <div class="max-w-3xl mx-auto text-center mb-12 lg:mb-20">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 section-title inline-block">
                    Fitur Lengkap Platform
                </h2>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">

                <!-- Feature Card 1 -->
                <div class="feature-card bg-white p-6 lg:p-8 border-t-4 border-sky-400 shadow-sm hover:shadow-lg">
                    <div class="mb-6 lg:mb-8">
                        <div
                            class="w-14 h-14 lg:w-16 lg:h-16 bg-sky-50 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Materi Interaktif</h3>
                    <p class="text-gray-600 text-sm lg:text-base leading-relaxed mb-6">
                        Akses materi pembelajaran berbasis multimedia dengan video, animasi, dan simulasi interaktif
                        yang menarik.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Video pembelajaran HD</span>
                        </li>
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Animasi edukatif</span>
                        </li>
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Simulasi praktikum virtual</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature Card 2 -->
                <div class="feature-card bg-white p-6 lg:p-8 border-t-4 border-sky-400 shadow-sm hover:shadow-lg">
                    <div class="mb-6 lg:mb-8">
                        <div
                            class="w-14 h-14 lg:w-16 lg:h-16 bg-sky-50 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Forum Diskusi</h3>
                    <p class="text-gray-600 text-sm lg:text-base leading-relaxed mb-6">
                        Berdiskusi dengan siswa lain dan guru dalam forum yang aman dan terstruktur.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Ruang diskusi per kelas</span>
                        </li>
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Q&A langsung dengan guru</span>
                        </li>
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Kolaborasi kelompok</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature Card 3 -->
                <div class="feature-card bg-white p-6 lg:p-8 border-t-4 border-sky-400 shadow-sm hover:shadow-lg">
                    <div class="mb-6 lg:mb-8">
                        <div
                            class="w-14 h-14 lg:w-16 lg:h-16 bg-sky-50 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Quiz & Assessment</h3>
                    <p class="text-gray-600 text-sm lg:text-base leading-relaxed mb-6">
                        Sistem penilaian otomatis dengan berbagai jenis soal dan analisis performa belajar.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Quiz interaktif</span>
                        </li>
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Ujian online terproteksi</span>
                        </li>
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Laporan perkembangan</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature Card 4 -->
                <div class="feature-card bg-white p-6 lg:p-8 border-t-4 border-sky-400 shadow-sm hover:shadow-lg">
                    <div class="mb-6 lg:mb-8">
                        <div
                            class="w-14 h-14 lg:w-16 lg:h-16 bg-sky-50 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Belajar Fleksibel</h3>
                    <p class="text-gray-600 text-sm lg:text-base leading-relaxed mb-6">
                        Akses materi kapan saja dan di mana saja melalui berbagai perangkat.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Mobile-friendly</span>
                        </li>
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Offline mode</span>
                        </li>
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Multi-device sync</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature Card 5 -->
                <div class="feature-card bg-white p-6 lg:p-8 border-t-4 border-sky-400 shadow-sm hover:shadow-lg">
                    <div class="mb-6 lg:mb-8">
                        <div
                            class="w-14 h-14 lg:w-16 lg:h-16 bg-sky-50 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Analytics Dashboard</h3>
                    <p class="text-gray-600 text-sm lg:text-base leading-relaxed mb-6">
                        Pantau perkembangan belajar dengan dashboard analitik yang lengkap dan real-time.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Grafik performa</span>
                        </li>
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Rekomendasi belajar</span>
                        </li>
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Progress tracking</span>
                        </li>
                    </ul>
                </div>

                <!-- Feature Card 6 -->
                <div class="feature-card bg-white p-6 lg:p-8 border-t-4 border-sky-400 shadow-sm hover:shadow-lg">
                    <div class="mb-6 lg:mb-8">
                        <div
                            class="w-14 h-14 lg:w-16 lg:h-16 bg-sky-50 rounded-2xl flex items-center justify-center mb-6">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-xl lg:text-2xl font-bold text-gray-900 mb-4">Kustomisasi</h3>
                    <p class="text-gray-600 text-sm lg:text-base leading-relaxed mb-6">
                        Sesuaikan pengalaman belajar sesuai dengan kebutuhan dan preferensi Anda.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Personalized learning path</span>
                        </li>
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Custom notification</span>
                        </li>
                        <li class="flex items-center text-gray-700 text-sm lg:text-base">
                            <svg class="w-5 h-5 text-emerald-500 mr-3 flex-shrink-0" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <span>Interface customization</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="gradient-hero text-white py-16 lg:py-24 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-20 -right-20 w-96 h-96 bg-white rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-white rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-4 lg:px-6 text-center relative z-10">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-6 lg:mb-8">
                Siap Mulai Belajar?
            </h2>
            <p class="text-lg md:text-xl leading-relaxed mb-8 lg:mb-12 max-w-3xl mx-auto opacity-95">
                Bergabunglah dengan jutaan siswa lainnya dan rasakan pengalaman belajar terbaik
            </p>
            <a href="{{ route('login') }}"
                class="inline-block bg-white text-blue-600 font-bold px-8 lg:px-10 py-3 lg:py-4 rounded-lg hover:bg-sky-50 transition-all duration-300 transform hover:scale-105 shadow-lg text-base lg:text-lg">
                Mulai Sekarang
            </a>
        </div>
    </section>
    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 lg:py-16">
        <div class="container mx-auto px-4 lg:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 pb-8 border-b border-gray-700">
                <div>
                    <img src="{{ asset('image/Smart-LAB white logo.webp') }}" alt="Smart-LAB" class="h-12 mb-6">
                    <p class="text-gray-400 text-sm lg:text-base">Platform belajar online terdepan di Indonesia</p>
                </div>
                <div class="text-right">
                    <p class="text-gray-400 text-sm lg:text-base">&copy; {{ date('Y') }} Smart-LAB. Hak Cipta
                        Dilindungi.</p>
                    <p class="text-gray-400 text-xs lg:text-sm mt-2">Hubungi kami: support@smartlab.com</p>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>

</html>
