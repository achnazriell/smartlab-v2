<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir {{ $class->name_class }} - {{ $academicYear->name }}</title>
    <style>
        @page {
            size: {{ $cssPaperSize ?? 'A4' }};
            margin: 2cm;
            /* Hapus header/footer browser */
            @top-left { content: ""; }
            @top-center { content: ""; }
            @top-right { content: ""; }
            @bottom-left { content: ""; }
            @bottom-center { content: ""; }
            @bottom-right { content: ""; }
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 0;
            padding: 0;
            line-height: 1.4;
            color: #000;
        }
        .kop-surat {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
        }
        .kop-surat h1 {
            font-size: 28px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: Arial, sans-serif;
        }
        .kop-surat h2 {
            font-size: 22px;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .kop-surat .alamat {
            font-size: 14px;
            margin: 5px 0;
            line-height: 1.6;
        }
        .kop-surat .kontak {
            font-size: 12px;
            margin: 5px 0;
            color: #333;
        }
        .judul {
            text-align: center;
            margin: 30px 0 20px;
        }
        .judul h3 {
            font-size: 18px;
            font-weight: bold;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
        }
        .judul p {
            font-size: 14px;
            margin: 5px 0 0;
        }
        .info-kelas {
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            border: 1px solid #000;
            padding: 10px;
            background-color: #f9f9f9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 12px;
            table-layout: fixed;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            border: 1px solid #000;
            padding: 8px 4px;
            text-align: center;
        }
        td {
            border: 1px solid #000;
            padding: 6px 4px;
            vertical-align: middle;
        }
        td.nomor {
            text-align: center;
        }
        td.tanda-tangan {
            text-align: center;
        }
        /* Lebar kolom */
        colgroup col.no { width: 5%; }
        colgroup col.nis { width: 15%; }
        colgroup col.nama { width: 35%; }
        colgroup col.lp { width: 8%; }
        colgroup col.ttd { width: 22%; }
        colgroup col.ket { width: 15%; }

        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }
        .footer .left { width: 40%; }
        .footer .right { width: 40%; text-align: center; }
        .footer .garis-ttd {
            margin-top: 40px;
            border-bottom: 1px solid #000;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }
        .footer .nama-terang {
            margin-top: 5px;
            font-weight: bold;
        }
        .footer .nip {
            font-size: 11px;
        }
        .catatan {
            font-size: 11px;
            margin-top: 10px;
            font-style: italic;
        }
        .no-print {
            margin-bottom: 20px;
            text-align: center;
        }
        .no-print button {
            padding: 8px 20px;
            margin-right: 10px;
            cursor: pointer;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .no-print button:hover {
            background-color: #2563eb;
        }
        .instruksi {
            color: #d32f2f;
            font-size: 13px;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ffcdd2;
            background-color: #ffebee;
            border-radius: 5px;
            text-align: left;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Cetak</button>
        <button onclick="window.close()">Tutup</button>

    </div>

    <!-- Kop Surat -->
    <div class="kop-surat">
        <h1>PEMERINTAH PROVINSI JAWA TIMUR</h1>
        <h2>SMK NEGERI 1 DLANGGU</h2>
        <div class="alamat">
            Jl. Jenderal Ahmad Yani No. 17, Kedunglengkong, Pohkecik, Kec. Dlanggu, Kab. Mojokerto, Jawa Timur 61371
        </div>
        <div class="kontak">
            Telp: (0321) 513093 | Fax: (0321) 513642 | Email: smkn1_dlanggu@yahoo.com
        </div>
        <div class="kontak">
            Website: www.smkn1dlanggu.sch.id | NPSN: 20502729 | Akreditasi: A
        </div>
    </div>

    <!-- Judul -->
    <div class="judul">
        <h3>DAFTAR HADIR SISWA</h3>
        <p>Tahun Ajaran {{ $academicYear->name }}</p>
        @if($month)
            <p>Bulan: {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}</p>
        @endif
    </div>

    <!-- Info Kelas -->
    <div class="info-kelas">
        <span><strong>Kelas:</strong> {{ $class->name_class }}</span>
        <span><strong>Wali Kelas:</strong> ........................................</span>
        <span><strong>Tanggal Cetak:</strong> {{ $date }}</span>
    </div>

    <!-- Tabel Absensi -->
    <table>
        <colgroup>
            <col class="no">
            <col class="nis">
            <col class="nama">
            <col class="lp">
            <col class="ttd">
            <col class="ket">
        </colgroup>
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>L/P</th>
                <th>Tanda Tangan</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($students as $index => $student)
            <tr>
                <td class="nomor">{{ $index + 1 }}</td>
                <td>{{ $student->nis }}</td>
                <td>{{ $student->user->name }}</td>
                <td class="text-center">-</td>
                <td class="tanda-tangan"></td>
                <td></td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">
                    Tidak ada data siswa untuk kelas ini
                </td>
            </tr>
            @endforelse

            <!-- Baris kosong untuk cadangan -->
            @for($i = 0; $i < 5; $i++)
            <tr>
                <td class="nomor">{{ count($students) + $i + 1 }}</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endfor
        </tbody>
    </table>

    <!-- Catatan -->
    <p class="catatan">Catatan: Tanda tangan siswa sebagai bukti kehadiran.</p>

    <!-- Footer Tanda Tangan -->
    <div class="footer">
        <div class="left">
            <p>Mengetahui,<br>Kepala Sekolah</p>
            <div class="garis-ttd"></div>
            <div class="nama-terang"><strong>{{ $kepalaSekolah ?? 'Prapri Widodo' }}</strong></div>
            <div class="nip">NIP. 19651234 199003 1 012</div>
        </div>
        <div class="right">
            <p>Dlanggu, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Wali Kelas</p>
            <div class="garis-ttd"></div>
            <div class="nama-terang"><strong>........................................</strong></div>
            <div class="nip">NIP. ................................</div>
        </div>
    </div>
</body>
</html>
