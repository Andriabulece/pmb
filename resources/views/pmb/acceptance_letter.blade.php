<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Keputusan Penerimaan Mahasiswa Baru</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; line-height: 1.6; color: #333; margin: 30px; }
        .header { text-align: center; border-bottom: 2px solid #222; padding-bottom: 12px; margin-bottom: 25px; }
        .header h2 { margin: 0; text-transform: uppercase; font-size: 18px; }
        .header h3 { margin: 4px 0 0; font-size: 15px; font-weight: normal; }
        .content { margin-bottom: 25px; }
        table.info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table.info-table td { padding: 6px 10px; }
        table.info-table td.label { width: 30%; font-weight: bold; background: #f9f9f9; }
        .badge { display: inline-block; padding: 4px 10px; background: #28a745; color: white; font-weight: bold; border-radius: 4px; }
        .footer { margin-top: 50px; text-align: right; }
        .signature-box { display: inline-block; text-align: center; width: 220px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>PANITIA PENERIMAAN MAHASISWA BARU (PMB)</h2>
        <h3>{{ $registration->studyProgram->faculty->name ?? 'FAKULTAS ILMU SOSIAL DAN ILMU POLITIK' }}</h3>
        <p style="margin: 3px 0 0; font-size: 11px;">Sistem Informasi Seleksi & Registrasi Mahasiswa Baru Terpadu</p>
    </div>

    <div class="content">
        <p>Berdasarkan hasil verifikasi administrasi berkas, asesmen akademik, serta validasi pembayaran pendaftaran ulang, panitia PMB menerangkan bahwa:</p>

        <table class="info-table" border="1" bordercolor="#e0e0e0">
            <tr>
                <td class="label">Nomor Registrasi</td>
                <td><strong>{{ $registration->registration_number }}</strong></td>
            </tr>
            <tr>
                <td class="label">Nama Calon Mahasiswa</td>
                <td><strong>{{ $registration->full_name }}</strong></td>
            </tr>
            <tr>
                <td class="label">Program Studi Pilihan</td>
                <td>[{{ $registration->studyProgram->degree }}] {{ $registration->studyProgram->name }}</td>
            </tr>
            <tr>
                <td class="label">Asal Sekolah / Kampus</td>
                <td>{{ $registration->previous_school }}</td>
            </tr>
            <tr>
                <td class="label">Status Penerimaan</td>
                <td><span class="badge">RESMI DITERIMA</span></td>
            </tr>
            <tr>
                <td class="label">Tanggal Diterima</td>
                <td>{{ $registration->accepted_at ? $registration->accepted_at->format('d F Y') : now()->format('d F Y') }}</td>
            </tr>
        </table>

        <p>Dinyatakan telah memenuhi segala persyaratan dan <strong>RESMI DITERIMA</strong> sebagai mahasiswa baru pada Tahun Akademik aktif. Silakan membawa surat keterangan ini saat orientasi akademik dan proses pencetakan Kartu Tanda Mahasiswa (KTM).</p>
    </div>

    <div class="footer">
        <div class="signature-box">
            <p>Ditetapkan di Kampus Terpadu,<br>{{ now()->format('d F Y') }}</p>
            <br><br><br>
            <p><strong>Ketua Panitia PMB / Dekanat</strong><br>NIP. 19850101 201012 1 001</p>
        </div>
    </div>
</body>
</html>

