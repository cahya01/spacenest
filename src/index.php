<?php
session_start();

if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'] ?? 'mahasiswa';
    header('Location: ' . $role . '/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPAK | Sistem Informasi Pengajuan Akademik Kampus</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
    <script src="assets/js/app.js?v=<?= filemtime(__DIR__ . '/assets/js/app.js') ?>" defer></script>
</head>
<body>
    <header class="site-header">
        <div class="container nav-wrap">
            <div class="brand">
                <img src="assets/img/WhatsApp Image 2026-10-16 at 20.53.26.jpeg" alt="Logo Universitas Muhammadiyah Indonesia">
                <div class="brand-text">
                    <span class="brand-title">SIPAK</span>
                    <small>Sistem Informasi Pengajuan Akademik Kampus</small>
                </div>
            </div>

            <nav class="main-nav" aria-label="Navigasi utama">
                <a href="#" class="active">Beranda</a>
                <a href="#layanan">Layanan Pengajuan</a>
                <a href="#alur">Alur Pengajuan</a>
                <a href="#panduan">Panduan</a>
            </nav>

            <div class="nav-actions">
                <a href="login.php" class="btn btn-outline">Masuk</a>
                <a href="daftar.php" class="btn btn-primary">Daftar</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container hero-wrap">
                <div class="hero-copy">
                    <span class="eyebrow">SELAMAT DATANG DI SIPAK</span>
                    <h1>Urus Pengajuan Akademik Lebih Mudah</h1>
                    <p>
                        Satu portal untuk pengajuan layanan akademik dan administrasi mahasiswa.
                    </p>

                    <div class="cta-row">
                        <a href="login.php" class="btn btn-primary">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 11 18-8-8 18-2.7-7.3L3 11Zm7.3 2.7L21 3"/></svg>
                            Ajukan Sekarang
                            <span aria-hidden="true">→</span>
                        </a>
                        <a href="#layanan" class="btn btn-outline">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                            Lihat Layanan
                        </a>
                    </div>
                </div>
                <div class="hero-art">
                    <img src="assets/img/hero-illustration.svg" alt="Ilustrasi proses pengajuan akademik secara online">
                </div>
            </div>
        </section>

        <section class="hero-highlights" aria-label="Keunggulan SIPAK">
            <div class="container highlights-grid">
                <article class="highlight-item">
                    <span class="highlight-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 12.5 9.2 18 20 6"/></svg>
                    </span>
                    <div><h2>Mudah Digunakan</h2><p>Ajukan layanan kapan saja</p></div>
                </article>
                <article class="highlight-item">
                    <span class="highlight-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/><path d="m9 12 2 2 4-4"/></svg>
                    </span>
                    <div><h2>Proses Transparan</h2><p>Pantau status setiap pengajuan</p></div>
                </article>
                <article class="highlight-item">
                    <span class="highlight-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    </span>
                    <div><h2>Status Real-Time</h2><p>Informasi selalu diperbarui</p></div>
                </article>
            </div>
        </section>

        <section class="services" id="layanan">
            <div class="container section-head">
                <div>
                    <span class="section-tag">LAYANAN PENGAJUAN</span>
                    <h2>Pilih Layanan yang Anda Butuhkan</h2>
                </div>
                <a href="#daftar-layanan" class="mini-link">Lihat Semua Layanan →</a>
            </div>

            <div class="container service-grid" id="daftar-layanan">
                <button class="service-card blue" type="button" data-service="skripsi" aria-haspopup="dialog" aria-controls="service-dialog">
                    <span class="service-icon" aria-hidden="true">🎓</span>
                    <span class="service-title">Pengajuan Skripsi</span>
                    <span class="card-arrow" aria-hidden="true">→</span>
                </button>
                <button class="service-card yellow" type="button" data-service="magang" aria-haspopup="dialog" aria-controls="service-dialog">
                    <span class="service-icon" aria-hidden="true">💼</span>
                    <span class="service-title">Pengajuan Magang</span>
                    <span class="card-arrow" aria-hidden="true">→</span>
                </button>
                <button class="service-card blue" type="button" data-service="surat-aktif" aria-haspopup="dialog" aria-controls="service-dialog">
                    <span class="service-icon" aria-hidden="true">📝</span>
                    <span class="service-title">Surat Aktif Kuliah</span>
                    <span class="card-arrow" aria-hidden="true">→</span>
                </button>
                <button class="service-card yellow" type="button" data-service="cuti" aria-haspopup="dialog" aria-controls="service-dialog">
                    <span class="service-icon" aria-hidden="true">📅</span>
                    <span class="service-title">Cuti Kuliah</span>
                    <span class="card-arrow" aria-hidden="true">→</span>
                </button>
                <button class="service-card blue" type="button" data-service="dispensasi-krs" aria-haspopup="dialog" aria-controls="service-dialog">
                    <span class="service-icon" aria-hidden="true">📄</span>
                    <span class="service-title">Dispensasi KRS</span>
                    <span class="card-arrow" aria-hidden="true">→</span>
                </button>
                <button class="service-card yellow" type="button" data-service="penelitian" aria-haspopup="dialog" aria-controls="service-dialog">
                    <span class="service-icon" aria-hidden="true">🧪</span>
                    <span class="service-title">Izin Penelitian</span>
                    <span class="card-arrow" aria-hidden="true">→</span>
                </button>
            </div>
        </section>

        <dialog class="service-dialog" id="service-dialog" aria-labelledby="service-dialog-title" aria-describedby="service-dialog-intro">
            <div class="service-dialog-header">
                <div class="service-dialog-heading">
                    <span class="service-dialog-icon" id="service-dialog-icon" aria-hidden="true"></span>
                    <div>
                        <span class="section-tag">PANDUAN LAYANAN</span>
                        <h2 id="service-dialog-title"></h2>
                    </div>
                </div>
                <button class="service-dialog-close" type="button" aria-label="Tutup panduan">&times;</button>
            </div>
            <div class="service-dialog-content">
                <p class="service-dialog-intro" id="service-dialog-intro"></p>
                <div class="service-guide-grid">
                    <section class="service-guide-section">
                        <h3>Sebelum mengajukan</h3>
                        <ul id="service-dialog-preparation"></ul>
                    </section>
                    <section class="service-guide-section">
                        <h3>Langkah pengajuan</h3>
                        <ol id="service-dialog-steps"></ol>
                    </section>
                    <section class="service-guide-section service-guide-documents">
                        <h3>Dokumen yang perlu disiapkan</h3>
                        <ul id="service-dialog-documents"></ul>
                    </section>
                </div>
                <p class="service-dialog-note" id="service-dialog-note"></p>
            </div>
            <div class="service-dialog-footer">
                <span>Sudah siap? Masuk ke SIPAK untuk memulai.</span>
                <a class="btn btn-primary" href="login.php">Masuk untuk Mengajukan <span aria-hidden="true">→</span></a>
            </div>
        </dialog>

        <section class="process" id="alur">
            <div class="container process-grid" id="panduan">
                <div class="process-card">
                    <div class="process-icon dark">🧾</div>
                    <h3>Cara Pengajuan Sangat Mudah</h3>
                </div>
                <div class="process-card">
                    <div class="process-icon">📌</div>
                    <h3>Pilih Layanan</h3>
                    <p>Tekan jenis pengajuan yang anda butuhkan.</p>
                </div>
                <div class="process-card">
                    <div class="process-icon">📤</div>
                    <h3>Lengkapi Formulir</h3>
                    <p>Isi data dan unggah dokumen pendukung.</p>
                </div>
                <div class="process-card">
                    <div class="process-icon">✅</div>
                    <h3>Verifikasi</h3>
                    <p>Proses pemeriksaan dan notifikasi status.</p>
                </div>
                <div class="process-card">
                    <div class="process-icon">📥</div>
                    <h3>Hasil Pengajuan</h3>
                    <p>Notifikasi status pengajuan akan dikirimkan.</p>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container footer-wrap">
            <div class="footer-brand">
                <img src="assets/img/WhatsApp Image 2026-10-16 at 20.53.26.jpeg" alt="Logo UMI">
                <div>
                    <h4>UNIVERSITAS MUHAMMADIYAH</h4>
                    <h5>INDONESIA</h5>
                </div>
            </div>

            <div class="footer-info">
                <div class="info-item">📍 Jl. KH. Ahmad Dahlan No. 23, Jakarta, Indonesia</div>
                <div class="info-item">📞 (021) 1234 5678</div>
                <div class="info-item">✉️ info@umi.ac.id</div>
            </div>

            <div class="socials" aria-label="Media sosial">
                <span>◎</span>
                <span>f</span>
                <span>◌</span>
                <span>X</span>
            </div>
        </div>
    </footer>
</body>
</html>
