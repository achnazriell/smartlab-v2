<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar SmartLab - Platform Edukasi Modern</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #0066cc;
            --primary-light: #e6f0ff;
            --primary-dark: #004fa3;
            --success: #10b981;
            --border: #d1d5db;
            --text-light: #6b7280;
            --bg-light: #f9fafb;
        }

        body {
            background-image: url('{{ asset('image/background-guest2.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 40px 32px 32px;
            text-align: center;
            color: white;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .progress-container {
            display: flex;
            gap: 12px;
            padding: 24px 32px;
            background: var(--bg-light);
            border-bottom: 1px solid var(--border);
        }

        .progress-step {
            flex: 1;
            position: relative;
        }

        .progress-step-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--border);
            color: var(--text-light);
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }

        .progress-step.active .progress-step-number {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
        }

        .progress-step.completed .progress-step-number {
            background: var(--success);
            color: white;
        }

        .progress-step-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-light);
            text-align: center;
            transition: all 0.3s ease;
        }

        .progress-step.active .progress-step-label {
            color: var(--primary);
            font-weight: 700;
        }

        .progress-step.completed .progress-step-label {
            color: var(--success);
        }

        .content {
            padding: 32px;
        }

        .step-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .step-subtitle {
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input,
        select {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
            background: white;
            color: #111827;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
        }

        input::placeholder {
            color: #9ca3af;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }

        button {
            flex: 1;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 102, 204, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: var(--bg-light);
            color: #6b7280;
            border: 2px solid var(--border);
        }

        .btn-secondary:hover {
            background: white;
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
        }

        .form-row button {
            flex: 1;
        }

        @media (max-width: 640px) {
            .header h1 {
                font-size: 24px;
            }

            .content {
                padding: 24px;
            }

            .progress-container {
                padding: 20px 24px;
            }

            .progress-step-label {
                font-size: 11px;
            }

            .progress-step-number {
                width: 28px;
                height: 28px;
                font-size: 11px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body x-cloak x-data="registerForm()" class="min-h-screen">
    <div class="w-full max-w-[480px] bg-white bg-opacity-70 backdrop-blur-md rounded-2xl shadow-md overflow-hidden">
        <!-- Header -->
        <div class="header">
            <h1>Daftar SmartLab</h1>
            <p>Bergabunglah dengan platform edukasi modern</p>
        </div>

        <!-- Progress Bar -->
        <div class="progress-container">
            <div class="progress-step" :class="{ 'active': step === 1, 'completed': step > 1 }">
                <div class="progress-step-number">1</div>
                <div class="progress-step-label">Akun</div>
            </div>
            <div class="progress-step" :class="{ 'active': step === 2, 'completed': step > 2 }">
                <div class="progress-step-number">2</div>
                <div class="progress-step-label">Pribadi</div>
            </div>
            <div class="progress-step" :class="{ 'active': step === 3 }">
                <div class="progress-step-number">3</div>
                <div class="progress-step-label">Sekolah</div>
            </div>
        </div>

        <!-- Form Content -->
        <div class="content">
            <!-- STEP 1: Data Akun -->
            <div x-show="step === 1" class="fade-in">
                <h2 class="step-title">Data Akun</h2>
                <p class="step-subtitle">Buat akun untuk mengakses platform SmartLab</p>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" placeholder="nama@example.com" x-model="form.email" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" placeholder="Minimal 8 karakter" x-model="form.password" required>
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" placeholder="Ulangi password" x-model="form.confirmPassword" required>
                </div>

                <div class="button-group">
                    <button class="btn-primary" @click="nextStep()">
                        Lanjutkan →
                    </button>
                </div>
            </div>

            <!-- STEP 2: Data Pribadi -->
            <div x-show="step === 2" class="fade-in">
                <h2 class="step-title">Data Pribadi</h2>
                <p class="step-subtitle">Informasi dasar untuk profil Anda</p>

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" placeholder="Masukkan nama lengkap" x-model="form.fullName" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Jenis Kelamin</label>
                        <select x-model="form.gender" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="laki-laki">Laki-laki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Lahir</label>
                        <input type="date" x-model="form.birthDate" required>
                    </div>
                </div>

                <div class="button-group">
                    <button class="btn-secondary" @click="prevStep()">← Kembali</button>
                    <button class="btn-primary" @click="nextStep()">
                        Lanjutkan →
                    </button>
                </div>
            </div>

            <!-- STEP 3: Data Sekolah -->
            <div x-show="step === 3" class="fade-in">
                <h2 class="step-title">Data Sekolah</h2>
                <p class="step-subtitle">Lengkapi informasi institusi dan peran Anda</p>

                <div class="form-group">
                    <label>Daftar Sebagai</label>
                    <select x-model="form.role" required>
                        <option value="">Pilih Role</option>
                        <option value="siswa">Siswa</option>
                        <option value="guru">Guru</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nama Sekolah / Institusi</label>
                    <input type="text" placeholder="Nama sekolah atau universitas" x-model="form.schoolName"
                        required>
                </div>

                <div class="form-group">
                    <label x-text="form.role === 'siswa' ? 'Kelas' : 'Bidang Mengajar'"></label>
                    <input type="text"
                        :placeholder="form.role === 'siswa' ? 'Contoh: XI IPA 1' : 'Contoh: Matematika'"
                        x-model="form.classOrSubject" required>
                </div>

                <div class="button-group">
                    <button class="btn-secondary" @click="prevStep()">← Kembali</button>
                    <button class="btn-success" @click="submitForm()">
                        ✓ Selesaikan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function registerForm() {
            return {
                step: 1,
                form: {
                    email: '',
                    password: '',
                    confirmPassword: '',
                    fullName: '',
                    gender: '',
                    birthDate: '',
                    role: '',
                    schoolName: '',
                    classOrSubject: ''
                },
                nextStep() {
                    if (this.validateStep()) {
                        this.step++;
                    }
                },
                prevStep() {
                    this.step--;
                },
                validateStep() {
                    if (this.step === 1) {
                        if (!this.form.email || !this.form.password || !this.form.confirmPassword) {
                            alert('Semua field harus diisi');
                            return false;
                        }
                        if (this.form.password !== this.form.confirmPassword) {
                            alert('Password tidak cocok');
                            return false;
                        }
                        if (this.form.password.length < 8) {
                            alert('Password minimal 8 karakter');
                            return false;
                        }
                    }
                    if (this.step === 2) {
                        if (!this.form.fullName || !this.form.gender || !this.form.birthDate) {
                            alert('Semua field harus diisi');
                            return false;
                        }
                    }
                    if (this.step === 3) {
                        if (!this.form.role || !this.form.schoolName || !this.form.classOrSubject) {
                            alert('Semua field harus diisi');
                            return false;
                        }
                    }
                    return true;
                },
                submitForm() {
                    if (this.validateStep()) {
                        console.log('Form Data:', this.form);
                        alert('Pendaftaran berhasil! Silakan cek email Anda untuk verifikasi.');
                        // Di sini Anda bisa menambahkan logika untuk mengirim data ke server
                    }
                }
            }
        }
    </script>
</body>

</html>
