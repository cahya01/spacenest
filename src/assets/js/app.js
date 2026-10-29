document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('#dashboard-sidebar');
    const sidebarOverlay = document.querySelector('.dashboard-sidebar-overlay');

    if (menuToggle && sidebar && sidebarOverlay) {
        const mobileViewport = window.matchMedia('(max-width: 700px)');

        function setMobileMenuOpen(isOpen, restoreFocus) {
            sidebar.classList.toggle('is-open', isOpen);
            sidebarOverlay.classList.toggle('is-visible', isOpen);
            menuToggle.setAttribute('aria-expanded', String(isOpen));
            menuToggle.setAttribute('aria-label', isOpen ? 'Tutup menu navigasi' : 'Buka menu navigasi');
            sidebar.inert = mobileViewport.matches && !isOpen;
            document.body.classList.toggle('dashboard-menu-open', isOpen);

            if (isOpen) {
                const activeLink = sidebar.querySelector('.dashboard-nav a.active') || sidebar.querySelector('.dashboard-nav a');
                activeLink?.focus();
            } else if (restoreFocus) {
                menuToggle.focus();
            }
        }

        menuToggle.addEventListener('click', function () {
            setMobileMenuOpen(menuToggle.getAttribute('aria-expanded') !== 'true', false);
        });

        sidebarOverlay.addEventListener('click', function () {
            setMobileMenuOpen(false, true);
        });

        sidebar.querySelectorAll('.dashboard-nav a, .dashboard-logout').forEach(function (link) {
            link.addEventListener('click', function () {
                if (mobileViewport.matches) {
                    setMobileMenuOpen(false, false);
                }
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && menuToggle.getAttribute('aria-expanded') === 'true') {
                setMobileMenuOpen(false, true);
            }
        });

        mobileViewport.addEventListener('change', function (event) {
            setMobileMenuOpen(false, false);
            sidebar.inert = event.matches;
        });

        sidebar.inert = mobileViewport.matches;
    }

    const services = {
        skripsi: {
            title: 'Pengajuan Skripsi',
            icon: '🎓',
            intro: 'Gunakan panduan ini untuk menyiapkan pengajuan tugas akhir. Konsultasikan topik dan rencana penelitian dengan dosen pembimbing serta program studi sebelum mengirim berkas.',
            preparation: [
                'Pastikan status akademik Anda aktif dan sudah memenuhi ketentuan pengambilan skripsi yang berlaku di program studi.',
                'Diskusikan topik, ruang lingkup, dan calon dosen pembimbing dengan pihak yang berwenang.',
                'Siapkan data diri dan informasi akademik yang diminta pada formulir pengajuan.'
            ],
            steps: [
                'Masuk ke SIPAK menggunakan akun mahasiswa.',
                'Buka menu pengajuan, pilih layanan skripsi jika tersedia, lalu isi judul atau topik sementara, program studi, dan keterangan yang diminta.',
                'Unggah berkas pendukung dengan isi yang jelas dan dapat dibaca, kemudian periksa kembali semua isian.',
                'Kirim pengajuan dan simpan nomor atau bukti pengajuan untuk memudahkan tindak lanjut.',
                'Pantau status secara berkala. Jika ada catatan perbaikan, lengkapi atau unggah ulang berkas sesuai arahan.'
            ],
            documents: [
                'Kartu Tanda Mahasiswa atau bukti identitas mahasiswa.',
                'Kartu Hasil Studi atau transkrip sementara jika diminta untuk pemeriksaan persyaratan akademik.',
                'Ringkasan rencana atau proposal topik penelitian jika sudah tersedia.',
                'Persetujuan calon pembimbing atau dokumen lain sesuai ketentuan program studi.'
            ],
            note: 'Daftar syarat, format berkas, dan proses penetapan pembimbing dapat berbeda antarprogram studi. Konfirmasikan ketentuan terbaru kepada program studi sebelum mengajukan.'
        },
        magang: {
            title: 'Pengajuan Magang',
            icon: '💼',
            intro: 'Pengajuan magang membantu program studi meninjau rencana kegiatan dan kebutuhan administrasi Anda sebelum kegiatan dimulai.',
            preparation: [
                'Pastikan waktu pelaksanaan sesuai kalender akademik dan ketentuan magang program studi.',
                'Tentukan instansi tujuan, bidang kegiatan, lokasi, serta perkiraan tanggal mulai dan selesai.',
                'Hubungi instansi tujuan untuk mengetahui persyaratan penerimaan peserta magang.'
            ],
            steps: [
                'Masuk ke SIPAK dengan akun mahasiswa.',
                'Pilih layanan magang jika tersedia dan isi nama instansi, alamat atau lokasi, bidang kegiatan, serta rencana periode magang.',
                'Jelaskan tujuan pengajuan dan kebutuhan surat pengantar atau persetujuan kampus.',
                'Lampirkan dokumen yang diminta, periksa kembali nama instansi dan tanggal kegiatan, lalu kirim pengajuan.',
                'Pantau status. Tunggu persetujuan sebelum menganggap magang telah disetujui atau mulai meminta surat resmi.'
            ],
            documents: [
                'Kartu Tanda Mahasiswa atau bukti identitas mahasiswa.',
                'Surat penerimaan atau bukti komunikasi dari instansi tujuan, jika sudah tersedia.',
                'Rencana kegiatan atau proposal magang yang memuat bidang dan periode kegiatan.',
                'Formulir atau persetujuan dosen pembimbing/program studi jika dipersyaratkan.'
            ],
            note: 'Format surat pengantar dan persyaratan magang ditentukan oleh program studi dan instansi tujuan. Periksa keduanya sebelum menetapkan tanggal kegiatan.'
        },
        'surat-aktif': {
            title: 'Surat Aktif Kuliah',
            icon: '📝',
            intro: 'Layanan ini digunakan untuk meminta surat keterangan bahwa Anda terdaftar sebagai mahasiswa aktif pada periode akademik tertentu.',
            preparation: [
                'Pastikan status registrasi akademik semester berjalan sudah sesuai.',
                'Siapkan tujuan penggunaan surat, nama instansi atau pihak penerima, dan jumlah salinan bila diperlukan.',
                'Periksa ejaan nama, nomor induk mahasiswa, program studi, dan periode akademik.'
            ],
            steps: [
                'Masuk ke SIPAK menggunakan akun mahasiswa.',
                'Pilih layanan surat aktif kuliah jika tersedia.',
                'Isi tujuan surat, nama pihak atau instansi penerima, alamat tujuan bila diminta, dan keterangan tambahan.',
                'Lampirkan bukti pendukung hanya jika diminta, lalu periksa ulang data sebelum mengirim.',
                'Pantau status pengajuan dan ikuti petunjuk pengambilan atau penerimaan surat setelah disetujui.'
            ],
            documents: [
                'Kartu Tanda Mahasiswa atau bukti identitas mahasiswa.',
                'Bukti registrasi atau pembayaran semester berjalan jika diminta.',
                'Dokumen dari instansi penerima yang menjelaskan kebutuhan surat, jika ada.'
            ],
            note: 'Pastikan tujuan dan nama penerima ditulis dengan benar. Koreksi setelah surat diterbitkan dapat memerlukan pengajuan ulang.'
        },
        cuti: {
            title: 'Cuti Kuliah',
            icon: '📅',
            intro: 'Pengajuan cuti digunakan untuk meminta izin tidak mengikuti kegiatan akademik pada periode tertentu. Ajukan sebelum batas waktu yang ditetapkan kampus.',
            preparation: [
                'Cari tahu periode pengajuan cuti dan dampaknya terhadap masa studi, registrasi, biaya, beasiswa, serta rencana kembali kuliah.',
                'Diskusikan rencana cuti dengan dosen pembimbing akademik atau program studi.',
                'Tuliskan alasan cuti, semester yang dimohonkan, dan rencana semester aktif kembali secara jelas.'
            ],
            steps: [
                'Masuk ke SIPAK dengan akun mahasiswa.',
                'Pilih layanan cuti jika tersedia dan tentukan semester atau periode cuti yang dimohonkan.',
                'Isi alasan cuti dengan lengkap serta cantumkan rencana kembali aktif.',
                'Unggah surat permohonan dan bukti pendukung yang relevan, kemudian periksa seluruh data.',
                'Kirim pengajuan dan pantau status. Jangan menganggap cuti telah disetujui sebelum menerima keputusan resmi.'
            ],
            documents: [
                'Surat permohonan cuti yang ditandatangani sesuai ketentuan kampus.',
                'Kartu Tanda Mahasiswa atau bukti identitas mahasiswa.',
                'Bukti pendukung alasan cuti, misalnya surat keterangan kesehatan jika alasan cuti terkait kesehatan.',
                'Persetujuan dosen pembimbing akademik atau program studi jika diwajibkan.'
            ],
            note: 'Pengajuan cuti dapat memiliki tenggat dan dampak administratif. Konfirmasikan prosedur, biaya, dan syarat kembali aktif kepada bagian akademik.'
        },
        'dispensasi-krs': {
            title: 'Dispensasi KRS',
            icon: '📄',
            intro: 'Gunakan layanan dispensasi KRS untuk menjelaskan kendala khusus terkait pengisian Kartu Rencana Studi dan meminta peninjauan dari pihak akademik.',
            preparation: [
                'Catat semester, mata kuliah, kelas, dan kendala yang dialami secara spesifik.',
                'Periksa jadwal pengisian KRS, prasyarat mata kuliah, batas beban studi, serta status administrasi.',
                'Hubungi dosen pembimbing akademik atau program studi bila kendala menyangkut persetujuan akademik.'
            ],
            steps: [
                'Masuk ke SIPAK menggunakan akun mahasiswa.',
                'Pilih dispensasi KRS jika layanan tersedia pada periode pengajuan.',
                'Jelaskan kendala, mata kuliah yang terkait, dan tindakan yang sudah dicoba.',
                'Lampirkan bukti pendukung yang relevan dan pastikan informasi semester serta mata kuliah benar.',
                'Kirim pengajuan dan pantau keputusan. Periksa kembali KRS setelah memperoleh persetujuan atau arahan.'
            ],
            documents: [
                'Kartu Tanda Mahasiswa atau bukti identitas mahasiswa.',
                'Tangkapan layar kendala pada sistem KRS, jika kendalanya berupa gangguan sistem.',
                'Kartu Hasil Studi atau bukti prasyarat mata kuliah jika dibutuhkan.',
                'Persetujuan dosen pembimbing akademik atau bukti pendukung lain sesuai kasus.'
            ],
            note: 'Dispensasi tidak otomatis menyetujui perubahan KRS. Keputusan mengikuti kalender akademik serta persetujuan program studi atau bagian akademik.'
        },
        penelitian: {
            title: 'Izin Penelitian',
            icon: '🧪',
            intro: 'Izin penelitian digunakan untuk meminta persetujuan atau surat pengantar penelitian di instansi, sekolah, komunitas, atau lokasi penelitian lainnya.',
            preparation: [
                'Pastikan topik dan metode penelitian sudah dibahas dengan dosen pembimbing.',
                'Tentukan lokasi, pihak yang dituju, jadwal, populasi atau narasumber, dan kegiatan yang akan dilakukan.',
                'Periksa apakah lokasi atau jenis penelitian memerlukan izin etik atau persetujuan tambahan.'
            ],
            steps: [
                'Masuk ke SIPAK menggunakan akun mahasiswa.',
                'Pilih layanan izin penelitian jika tersedia, lalu isi judul, lokasi, tujuan, metode ringkas, dan rentang waktu penelitian.',
                'Masukkan nama instansi atau pihak yang dituju dengan ejaan dan alamat yang benar.',
                'Unggah proposal atau ringkasan penelitian dan dokumen persetujuan pembimbing yang diminta.',
                'Kirim pengajuan. Tunggu persetujuan dan surat resmi sebelum memulai pengambilan data di lokasi.'
            ],
            documents: [
                'Kartu Tanda Mahasiswa atau bukti identitas mahasiswa.',
                'Proposal atau ringkasan penelitian yang telah disetujui pembimbing.',
                'Surat pengantar atau persetujuan dosen pembimbing/program studi jika diwajibkan.',
                'Persetujuan etik atau izin tambahan apabila disyaratkan oleh topik atau lokasi penelitian.'
            ],
            note: 'Ketentuan izin bergantung pada lokasi dan jenis penelitian. Jaga kerahasiaan data responden serta jangan melakukan pengambilan data sebelum izin yang diperlukan terbit.'
        }
    };

    const serviceGrid = document.querySelector('.service-grid');
    const dialog = document.querySelector('#service-dialog');

    if (!serviceGrid || !(dialog instanceof HTMLDialogElement)) {
        return;
    }

    const title = dialog.querySelector('#service-dialog-title');
    const icon = dialog.querySelector('#service-dialog-icon');
    const intro = dialog.querySelector('#service-dialog-intro');
    const preparation = dialog.querySelector('#service-dialog-preparation');
    const steps = dialog.querySelector('#service-dialog-steps');
    const documents = dialog.querySelector('#service-dialog-documents');
    const note = dialog.querySelector('#service-dialog-note');

    function populateList(list, items) {
        list.replaceChildren(...items.map(function (item) {
            const element = document.createElement('li');
            element.textContent = item;
            return element;
        }));
    }

    serviceGrid.addEventListener('click', function (event) {
        const button = event.target.closest('[data-service]');
        if (!button || !serviceGrid.contains(button)) {
            return;
        }

        const service = services[button.dataset.service];
        if (!service) {
            return;
        }

        title.textContent = service.title;
        icon.textContent = service.icon;
        intro.textContent = service.intro;
        populateList(preparation, service.preparation);
        populateList(steps, service.steps);
        populateList(documents, service.documents);
        note.textContent = service.note;
        dialog.showModal();
    });

    dialog.querySelector('.service-dialog-close').addEventListener('click', function () {
        dialog.close();
    });

    dialog.addEventListener('click', function (event) {
        if (event.target === dialog) {
            dialog.close();
        }
    });
});
