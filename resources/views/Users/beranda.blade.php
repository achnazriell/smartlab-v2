<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/png" href="{{ asset('image/logo.png') }}" />
    <title>Smart-LAB</title>

    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('style/beranda.css') }}" />
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>

    <style>
        /* ============== Animations ============== */
        @keyframes floating {
            0% {
                transform: translateY(0)
            }

            50% {
                transform: translateY(-20px)
            }

            100% {
                transform: translateY(0)
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px)
            }

            to {
                opacity: 1;
                transform: translateX(0)
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px)
            }

            to {
                opacity: 1;
                transform: translateX(0)
            }
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1)
            }

            50% {
                transform: scale(1.05)
            }
        }

        @keyframes bounce {

            0%,
            20%,
            53%,
            80%,
            100% {
                transform: translateY(0)
            }

            40%,
            43% {
                transform: translateY(-10px)
            }

            70% {
                transform: translateY(-5px)
            }

            90% {
                transform: translateY(-2px)
            }
        }

        .floating {
            animation: floating 3s ease-in-out infinite
        }

        .floating-delay {
            animation: floating 3s ease-in-out infinite;
            animation-delay: 1s
        }

        .fade-in-up {
            animation: fadeInUp .8s ease-out forwards;
            opacity: 0
        }

        .slide-in-left {
            animation: slideInLeft .8s ease-out forwards;
            opacity: 0
        }

        .slide-in-right {
            animation: slideInRight .8s ease-out forwards;
            opacity: 0
        }

        .pulse {
            animation: pulse 4s ease-in-out infinite
        }

        .pulse-hover:hover {
            animation: pulse .6s ease-in-out
        }

        .bounce-hover:hover {
            animation: bounce .6s ease-in-out
        }

        /* ============== Positioned decorative images ============== */
        .image-container {
            position: absolute;
            z-index: 2;
            pointer-events: none
        }

        .image-wedok {
            right: 5%;
            top: 30%;
            width: 120px
        }

        .image-laki {
            left: 5%;
            top: 30%;
            width: 120px
        }

        @media (min-width: 640px) {
            .image-wedok {
                right: 8%;
                top: 25%;
                width: 160px
            }

            .image-laki {
                left: 8%;
                top: 25%;
                width: 160px
            }
        }

        @media (min-width: 768px) {
            .image-wedok {
                right: 10%;
                top: 22%;
                width: 220px
            }

            .image-laki {
                left: 10%;
                top: 22%;
                width: 220px
            }
        }

        @media (min-width: 1024px) {
            .image-wedok {
                right: 15%;
                top: 20%;
                width: 280px
            }

            .image-laki {
                left: 15%;
                top: 20%;
                width: 280px
            }
        }

        @media (min-width: 1280px) {
            .image-wedok {
                right: 200px;
                top: 180px;
                width: 400px
            }

            .image-laki {
                left: 200px;
                top: 180px;
                width: 400px
            }
        }

        /* ============== Buttons ============== */
        .btn-hover {
            position: relative;
            overflow: hidden;
            transition: all .3s ease;
            touch-action: manipulation
        }

        .btn-hover::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .2), transparent);
            transition: left .5s
        }

        .btn-hover:hover::before {
            left: 100%
        }

        /* ============== Utils ============== */
        html {
            scroll-behavior: smooth
        }

        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all .8s ease-out
        }

        .animate-on-scroll.animated {
            opacity: 1;
            transform: translateY(0)
        }

        /* Carousel fixed height per breakpoint */
        .carousel-container {
            height: 250px
        }

        @media (min-width: 640px) {
            .carousel-container {
                height: 300px
            }
        }

        @media (min-width: 768px) {
            .carousel-container {
                height: 400px
            }
        }

        @media (min-width: 1024px) {
            .carousel-container {
                height: 450px
            }
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem
        }

        /* Stats card float above next section gracefully */
        .stats-section {
            position: absolute;
            z-index: 30;
            bottom: -80px;
            /* ganti dari margin-top */
            left: 50%;
            transform: translateX(-50%);
            padding-bottom: 2rem;
            width: 100%;
            /* biar bisa fleksibel */
            display: flex;
            justify-content: center;
            /* konten di tengah */
        }


        @media (min-width: 768px) {
            .stats-section {
                margin-top: -100px;
                padding-bottom: 3rem
            }
        }
    </style>
</head>

<body class="bg-white">
    <div class="navbar-container">
        <x-navbar></x-navbar>
    </div>

    <!-- =================== HERO =================== -->
    <section class="min-h-screen relative flex items-center justify-center overflow-hidden"
        style="background: url('{{ asset('image/bc atas.svg') }}') no-repeat center center; background-size: cover;">
        <div class="hero-content text-white font-poppins text-center w-full z-10 fade-in-up">
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-3 animate-on-scroll">Platform LMS
                gratis yang</h1>
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-6 animate-on-scroll">membuat belajar
                lebih menarik</h1>
            <p
                class="inline-block px-4 md:px-6 py-2 bg-blue-600 rounded-lg text-white text-sm md:text-lg lg:text-xl font-semibold leading-relaxed max-w-2xl animate-on-scroll">
                Akses materi belajar interaktif kapan saja, di mana saja.
            </p>
            <div class="mt-8 md:mt-12 flex flex-col sm:flex-row justify-center gap-4 md:gap-5 animate-on-scroll">

                <a href="{{ route('login') }}"
                    class="btn-hover pulse-hover text-gray-700 text-lg md:text-xl bg-gray-50 font-medium rounded-xl px-8 md:px-16 py-3 transition-all duration-300 ease-in-out hover:bg-blue-800 hover:text-white transform hover:scale-105">
                    <span class="relative z-10">Masuk</span>
                </a>
            </div>
        </div>

        <!-- Floating images -->
        <div class="image-container image-wedok floating slide-in-right">
            <img src="{{ asset('image/orang wedok.svg') }}" alt="orangwedok" class="w-full h-auto" />
        </div>
        <div class="image-container image-laki floating-delay slide-in-left">
            <img src="{{ asset('image/element laki.svg') }}" alt="wonglanang" class="w-full h-auto" />
        </div>
    </section>

    <!-- =================== STATS =================== -->
    <div class="stats-section flex justify-center px-4">
        <div class="bg-white rounded-2xl shadow-xl p-4 md:p-6 w-full max-w-2xl animate-on-scroll bounce-hover">
            <div class="flex flex-row justify-center items-center gap-6 md:gap-8">
                <div class="flex items-center justify-center text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 256 256"
                        class="mr-3 text-blue-600 bounce-hover sm:w-[60px] sm:h-[60px]">
                        <path fill="currentColor"
                            d="m227.79 52.62l-96-32a11.85 11.85 0 0 0-7.58 0l-96 32A12 12 0 0 0 20 63.37a6 6 0 0 0 0 .63v80a12 12 0 0 0 24 0V80.65l23.71 7.9a67.92 67.92 0 0 0 18.42 85A100.36 100.36 0 0 0 46 209.44a12 12 0 1 0 20.1 13.11C80.37 200.59 103 188 128 188s47.63 12.59 61.95 34.55a12 12 0 1 0 20.1-13.11a100.36 100.36 0 0 0-40.18-35.92a67.92 67.92 0 0 0 18.42-85l39.5-13.17a12 12 0 0 0 0-22.76Zm-99.79-8L186.05 64L128 83.35L70 64ZM172 120a44 44 0 1 1-81.06-23.71l33.27 11.09a11.9 11.9 0 0 0 7.58 0l33.27-11.09A43.85 43.85 0 0 1 172 120" />
                    </svg>
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-blue-600 jumlahsiswa-counter">+5jt</h1>
                        <p class="text-sm md:text-base text-gray-700">Siswa Bergabung</p>
                    </div>
                </div>
                <div class="flex items-center justify-center text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24"
                        class="mr-3 text-blue-600 bounce-hover sm:w-[60px] sm:h-[60px]">
                        <path fill="currentColor"
                            d="M20 17a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H9.46c.35.61.54 1.3.54 2h10v11h-9v2m4-10v2H9v13H7v-6H5v6H3v-8H1.5V9a2 2 0 0 1 2-2zM8 4a2 2 0 0 1-2 2a2 2 0 0 1-2-2a2 2 0 0 1 2-2a2 2 0 0 1 2 2" />
                    </svg>
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-blue-600 jumlahguru-counter">+40</h1>
                        <p class="text-sm md:text-base text-gray-700">Guru Aktif</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =================== FEATURES / CAROUSEL =================== -->
    <section class="min-h-screen bg-cover bg-center relative py-16 md:py-20"
        style="background-image: url('{{ asset('image/landing 2.svg') }}');">
        <div class="container mx-auto px-4">
            <div class="text-center mb-8 md:mb-12 animate-on-scroll">
                <h1 class="text-white font-bold text-xl sm:text-2xl md:text-3xl lg:text-4xl font-poppins">KEUNGGULAN
                    MENGGUNAKAN SMART-LAB</h1>
            </div>

            <!-- Flowbite Carousel (structure aligned to docs) -->
            <div id="default-carousel" class="relative w-full max-w-6xl mx-auto animate-on-scroll"
                data-carousel="slide">
                <div class="relative overflow-hidden rounded-3xl shadow-2xl carousel-container">
                    <div class="hidden duration-700 ease-in-out" data-carousel-item="active">
                        <img src="{{ asset('image/1.png') }}" class="block w-full h-full object-cover rounded-3xl"
                            alt="Slide 1" />
                    </div>
                    <div class="hidden duration-700 ease-in-out" data-carousel-item>
                        <img src="{{ asset('image/2.png') }}" class="block w-full h-full object-cover rounded-3xl"
                            alt="Slide 2" />
                    </div>
                    <div class="hidden duration-700 ease-in-out" data-carousel-item>
                        <img src="{{ asset('image/3.png') }}" class="block w-full h-full object-cover rounded-3xl"
                            alt="Slide 3" />
                    </div>
                </div>

                <!-- Indicators -->
                <div class="flex justify-center space-x-3 mt-6">
                    <button type="button"
                        class="w-3 h-3 rounded-full bg-white opacity-50 hover:opacity-100 transition-opacity pulse-hover"
                        aria-label="Slide 1" data-carousel-slide-to="0"></button>
                    <button type="button"
                        class="w-3 h-3 rounded-full bg-white opacity-50 hover:opacity-100 transition-opacity pulse-hover"
                        aria-label="Slide 2" data-carousel-slide-to="1"></button>
                    <button type="button"
                        class="w-3 h-3 rounded-full bg-white opacity-50 hover:opacity-100 transition-opacity pulse-hover"
                        aria-label="Slide 3" data-carousel-slide-to="2"></button>
                </div>

                <!-- Controls -->
                <button type="button" class="absolute top-1/2 left-4 -translate-y-1/2 z-30 pulse-hover"
                    aria-label="Previous" data-carousel-prev>
                    <span
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-400 hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 6 10" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 1 1 5l4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </span>
                </button>
                <button type="button" class="absolute top-1/2 right-4 -translate-y-1/2 z-30 pulse-hover"
                    aria-label="Next" data-carousel-next>
                    <span
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-400 hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 text-white" viewBox="0 0 6 10" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path d="m1 9 4-4-4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </section>

    <!-- =================== ABOUT =================== -->
    <section class="min-h-screen flex flex-col justify-center items-center relative overflow-hidden py-16 px-4">
        <!-- Background circle -->
        <div
            class="absolute w-80 h-80 md:w-96 md:h-96 lg:w-[650px] lg:h-[650px] bg-cyan-200 rounded-full opacity-80 shadow-lg animate-on-scroll pulse">
        </div>

        <div class="relative z-10 text-center max-w-4xl animate-on-scroll">
            <img src="{{ asset('image/Smart-Lab blue.png') }}" class="w-64 md:w-80 lg:w-96 mx-auto mb-6 bounce-hover"
                alt="logo" />
            <p
                class="text-blue-800 font-semibold text-sm md:text-base lg:text-lg text-center font-poppins leading-relaxed px-4">
                Smart-LAB adalah platform E-learning inovatif yang dirancang untuk memberikan pengalaman belajar yang
                interaktif
                dan menyeluruh bagi pelajar dan profesional. Fokusnya adalah pada pengembangan keterampilan praktis dan
                pemahaman
                mendalam di berbagai bidang.
            </p>
        </div>

        <!-- Decorative elements -->
        <img src="{{ asset('image/motif mtk.png') }}"
            class="absolute top-16 md:top-32 left-4 md:left-12 w-32 md:w-48 lg:w-56 opacity-80 floating"
            alt="Motif MTK" />
        <img src="{{ asset('image/motif bangun ruang.png') }}"
            class="absolute bottom-16 md:bottom-24 right-4 md:right-12 w-48 md:w-64 lg:w-80 opacity-80 floating-delay"
            alt="Motif Bangun Ruang" />

        <!-- Decorative circles -->
        <div
            class="absolute top-8 md:top-16 left-8 md:left-20 w-12 md:w-16 lg:w-20 h-12 md:h-16 lg:h-20 bg-sky-300 rounded-full opacity-60 shadow-lg floating">
        </div>
        <div
            class="absolute top-20 md:top-32 right-8 md:right-16 w-10 md:w-12 lg:w-16 h-10 md:h-12 lg:h-16 bg-sky-400 rounded-full opacity-50 shadow-lg floating-delay">
        </div>
        <div
            class="absolute bottom-16 md:bottom-20 left-16 md:left-32 w-16 md:w-20 lg:w-24 h-16 md:h-20 lg:h-24 bg-sky-500 rounded-full opacity-40 shadow-lg floating">
        </div>
        <div
            class="absolute bottom-8 md:bottom-16 right-16 md:right-28 w-8 md:w-10 lg:w-12 h-8 md:h-10 lg:h-12 bg-sky-300 rounded-full opacity-70 shadow-lg floating-delay">
        </div>
    </section>

    <!-- =================== CTA =================== -->
    <section class="min-h-screen flex flex-col justify-center items-center relative overflow-hidden pt-48">
        <img src="{{ asset('image/motif bawah landing.png') }}" class="absolute inset-0 w-full h-full object-cover"
            alt="motif bawah" />
        <div class="relative z-10 text-center text-blue-900 px-4 animate-on-scroll mb-16">
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold font-poppins mb-8 md:mb-12">
                Ayo berkembang <br />bersama Smart-LAB!
            </h1>

        <!-- =================== FOOTER =================== -->
        <footer class="w-full py-8 md:py-12 text-white relative z-10 mt-48">
            <div class="container mx-auto px-4 flex flex-col justify-between min-h-[300px]">
                <!-- Isi Footer -->
                <div class="flex flex-row lg:flex-col justify-center items-center gap-8 lg:gap-36">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <img src="{{ asset('image/Smart-LAB White Logo.png') }}"
                            class="w-0 md:w-64 lg:w-80 bounce-hover" alt="Logo Smart-LAB" />
                    </div>

                    <!-- Menu Links, Support, Social -->
                    <div class="flex flex-row md:flex-col lg:flex-col gap-8 md:gap-12 text-left lg:text-base text-xs">
                        <!-- Menu Links -->
                        <div class="">
                            <h4 class="font-bold mb-4 border-b-2 border-white pb-2 inline-block">Link Menu</h4>
                            <div class="space-y-2">
                                <a href="#beranda" class="block hover:text-cyan-200">Beranda</a>
                                <a href="#tentang" class="block hover:text-cyan-200">Tentang Kami</a>
                                <a href="#kursus" class="block hover:text-cyan-200">Kursus</a>
                                <a href="#blog" class="block hover:text-cyan-200">Blog</a>
                            </div>
                        </div>
                        <!-- Support -->
                        <div>
                            <h4 class="font-bold mb-4 border-b-2 border-white pb-2 inline-block">Bantuan</h4>
                            <div class="space-y-2">
                                <a href="#hubungi" class="block hover:text-cyan-200">Hubungi</a>
                                <a href="#panduan" class="block hover:text-cyan-200">Panduan Pengguna</a>
                                <a href="#bantuan" class="block hover:text-cyan-200">Pusat Bantuan</a>
                                <a href="#privasi" class="block hover:text-cyan-200">Kebijakan Privasi</a>
                                <a href="#syarat" class="block hover:text-cyan-200">Syarat & Ketentuan</a>
                            </div>
                        </div>
                        <!-- Social -->
                        <div>
                            <h4 class="font-bold mb-4 border-b-2 border-white pb-2 inline-block">Sosial Media</h4>
                            <div class="space-y-2">
                                <a href="#" class="block hover:text-cyan-200">Facebook</a>
                                <a href="#" class="block hover:text-cyan-200">Instagram</a>
                                <a href="#" class="block hover:text-cyan-200">Email</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Copyright di paling bawah -->
                <p class="text-center text-xs md:text-sm opacity-80 mt-4">
                    &copy; {{ date('Y') }} Smart-LAB. Hak Cipta Dilindungi.
                </p>
            </div>
        </footer>
    </section>

    <!-- =================== SCRIPTS =================== -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js" defer></script>
    <script>
        // --------- Counter animation ---------
        function animateNumber(element, start, end, duration) {
            const range = end - start;
            let current = start;
            const increment = range / (duration / 16);
            const timer = setInterval(() => {
                current += increment;
                if (current >= end) {
                    current = end;
                    clearInterval(timer);
                }
                if (end >= 1000000) {
                    element.innerText = '+' + Math.floor(current / 1000000) + 'jt';
                } else {
                    element.innerText = '+' + Math.floor(current);
                }
            }, 16);
        }

        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animated');
                    // Trigger number animation when statistics card becomes visible
                    if (entry.target.querySelector('.jumlahsiswa-counter')) {
                        const siswaElement = entry.target.querySelector('.jumlahsiswa-counter');
                        const guruElement = entry.target.querySelector('.jumlahguru-counter');
                        animateNumber(siswaElement, 1, 5000000, 2000);
                        animateNumber(guruElement, 1, 40, 2000);
                    }
                }
            });
        }, observerOptions);

        document.addEventListener('DOMContentLoaded', function() {
            // Observe all elements to animate on scroll
            document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));

            // Stagger for fade-in elements
            document.querySelectorAll('.fade-in-up').forEach((el, index) => {
                el.style.animationDelay = `${index * 0.2}s`;
            });

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (!href || href === '#') return; // allow non-anchor links
                    const target = document.querySelector(href);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Page load fade-in
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease-in-out';
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });

        // --------- Parallax effect for floating elements (fixed) ---------
        let ticking = false;

        function updateParallax() {
            const scrolled = window.pageYOffset || document.documentElement.scrollTop;
            const parallaxElements = document.querySelectorAll('.floating, .floating-delay');
            parallaxElements.forEach(el => {
                const speed = el.classList.contains('floating-delay') ? 0.15 : 0.25;
                el.style.transform = `translateY(${Math.round(scrolled * speed)}px)`;
            });
            ticking = false;
        }
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(updateParallax);
                ticking = true;
            }
        });
    </script>
</body>

</html>
