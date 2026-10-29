<?php

function dashboardEscape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function dashboardQueryRows(mysqli $connection, $sql)
{
    $result = $connection->query($sql);
    if ($result === false) {
        throw new RuntimeException('Gagal memuat data dashboard: ' . $connection->error);
    }

    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
    return $rows;
}

function dashboardLoadData($role, $username)
{
    $connection = getDbConnection();
    $userId = null;

    if ($role === 'mahasiswa') {
        $statement = $connection->prepare('SELECT id FROM users WHERE username = ? AND role = ? LIMIT 1');
        if ($statement === false) {
            throw new RuntimeException('Gagal menyiapkan data akun: ' . $connection->error);
        }

        $statement->bind_param('ss', $username, $role);
        if (!$statement->execute()) {
            throw new RuntimeException('Gagal memuat data akun: ' . $statement->error);
        }
        $statement->bind_result($userId);
        $statement->fetch();
        $statement->close();
        $userId = $userId === null ? 0 : (int) $userId;
    }

    $summarySql = "SELECT COUNT(*) AS total,
        COALESCE(SUM(status = 'diajukan'), 0) AS diajukan,
        COALESCE(SUM(status = 'diproses'), 0) AS diproses,
        COALESCE(SUM(status = 'disetujui'), 0) AS disetujui,
        COALESCE(SUM(status = 'ditolak'), 0) AS ditolak
        FROM pengajuan";

    if ($role === 'mahasiswa') {
        $summarySql .= ' WHERE user_id = ?';
        $statement = $connection->prepare($summarySql);
        if ($statement === false) {
            throw new RuntimeException('Gagal menyiapkan ringkasan pengajuan: ' . $connection->error);
        }

        $statement->bind_param('i', $userId);
    } else {
        $statement = $connection->prepare($summarySql);
        if ($statement === false) {
            throw new RuntimeException('Gagal menyiapkan ringkasan pengajuan: ' . $connection->error);
        }
    }

    if (!$statement->execute()) {
        throw new RuntimeException('Gagal memuat ringkasan pengajuan: ' . $statement->error);
    }

    $statement->bind_result($total, $diajukan, $diproses, $disetujui, $ditolak);
    $statement->fetch();
    $statement->close();

    $recentSql = "SELECT p.judul, p.kategori, p.status, p.created_at, u.username
        FROM pengajuan p
        INNER JOIN users u ON u.id = p.user_id";
    if ($role === 'mahasiswa') {
        $recentSql .= ' WHERE p.user_id = ' . (int) $userId;
    }
    $recentSql .= ' ORDER BY p.created_at DESC LIMIT 6';

    $data = [
        'summary' => [
            'total' => (int) $total,
            'diajukan' => (int) $diajukan,
            'diproses' => (int) $diproses,
            'disetujui' => (int) $disetujui,
            'ditolak' => (int) $ditolak,
        ],
        'recent' => dashboardQueryRows($connection, $recentSql),
        'users' => [
            'total' => 0,
            'mahasiswa' => 0,
            'dosen' => 0,
            'admin' => 0,
            'superadmin' => 0,
        ],
        'categories' => [],
    ];

    foreach (dashboardQueryRows($connection, 'SELECT role, COUNT(*) AS total FROM users GROUP BY role') as $row) {
        if (array_key_exists($row['role'], $data['users'])) {
            $data['users'][$row['role']] = (int) $row['total'];
        }
        $data['users']['total'] += (int) $row['total'];
    }

    if ($role === 'admin') {
        $data['categories'] = dashboardQueryRows(
            $connection,
            'SELECT kategori, COUNT(*) AS total FROM pengajuan GROUP BY kategori ORDER BY total DESC, kategori ASC LIMIT 5'
        );
    }

    $connection->close();
    return $data;
}

function dashboardLoadDataSafely($role, $username)
{
    try {
        return dashboardLoadData($role, $username);
    } catch (RuntimeException $error) {
        error_log('SPACENEST dashboard data error: ' . $error->getMessage());
        return [
            'error' => 'Data dashboard belum dapat dimuat. Pastikan server database aktif dan database yang dikonfigurasi sudah disiapkan.',
            'summary' => [
                'total' => 0,
                'diajukan' => 0,
                'diproses' => 0,
                'disetujui' => 0,
                'ditolak' => 0,
            ],
            'recent' => [],
            'users' => [
                'total' => 0,
                'mahasiswa' => 0,
                'dosen' => 0,
                'admin' => 0,
                'superadmin' => 0,
            ],
            'categories' => [],
        ];
    }
}

function dashboardNavigation($role)
{
    $navigation = [
        'mahasiswa' => [
            ['icon' => '⌂', 'label' => 'Dashboard', 'href' => 'dashboard.php'],
            ['icon' => '＋', 'label' => 'Buat Pengajuan', 'href' => 'buat_pengajuan.php'],
            ['icon' => '◷', 'label' => 'Riwayat Pengajuan', 'href' => 'riwayat_pengajuan.php'],
            ['icon' => '♙', 'label' => 'Profil', 'href' => 'profil.php'],
        ],
        'dosen' => [
            ['icon' => '⌂', 'label' => 'Dashboard', 'href' => 'dashboard.php'],
            ['icon' => '▤', 'label' => 'Daftar Pengajuan', 'href' => 'daftar_pengajuan.php'],
            ['icon' => '✓', 'label' => 'Verifikasi', 'href' => 'verifikasi.php'],
        ],
        'admin' => [
            ['icon' => '⌂', 'label' => 'Dashboard', 'href' => 'dashboard.php'],
            ['icon' => '▤', 'label' => 'Kelola Pengajuan', 'href' => 'kelola_pengajuan.php'],
            ['icon' => '♙', 'label' => 'Kelola Pengguna', 'href' => 'kelola_pengguna.php'],
            ['icon' => '▦', 'label' => 'Kelola Kategori', 'href' => 'kelola_kategori.php'],
            ['icon' => '▧', 'label' => 'Laporan', 'href' => 'laporan.php'],
        ],
        'superadmin' => [
            ['icon' => '⌂', 'label' => 'Dashboard', 'href' => 'dashboard.php'],
            ['icon' => '♙', 'label' => 'Pengguna', 'href' => 'pengguna.php'],
            ['icon' => '◇', 'label' => 'Role', 'href' => 'role.php'],
            ['icon' => '⚙', 'label' => 'Pengaturan', 'href' => 'pengaturan.php'],
        ],
    ];

    return $navigation[$role] ?? [];
}

function dashboardRenderStart($role, $username, $title, $searchHint, $error = '')
{
    $safeUsername = dashboardEscape($username);
    $title = dashboardEscape($title);
    $searchHint = dashboardEscape($searchHint);
    $roleLabel = [
        'mahasiswa' => 'Mahasiswa',
        'dosen' => 'Dosen',
        'admin' => 'Admin',
        'superadmin' => 'Superadmin',
    ][$role] ?? 'Pengguna';
    $initial = strtoupper(substr((string) $username, 0, 1));
    $navigation = dashboardNavigation($role);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $title; ?> | SPACENEST</title>
        <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo filemtime(__DIR__ . '/../assets/css/style.css'); ?>">
    </head>
    <body class="dashboard-page">
        <div class="dashboard-shell">
            <aside class="dashboard-sidebar" id="dashboard-sidebar">
                <a class="dashboard-logo" href="dashboard.php">
                    <img src="../assets/img/WhatsApp%20Image%202026-10-16%20at%2020.53.26.jpeg" alt="">
                    <span><strong>SPACENEST</strong><small><?php echo $roleLabel; ?></small></span>
                </a>
                <div class="dashboard-sidebar-label">MENU UTAMA</div>
                <nav aria-label="Navigasi dashboard">
                    <ul class="dashboard-nav">
                        <?php foreach ($navigation as $item): ?>
                            <li>
                                <a class="<?php echo basename($_SERVER['SCRIPT_NAME']) === $item['href'] ? 'active' : ''; ?>" href="<?php echo dashboardEscape($item['href']); ?>">
                                    <span class="dashboard-nav-icon" aria-hidden="true"><?php echo dashboardEscape($item['icon']); ?></span>
                                    <span><?php echo dashboardEscape($item['label']); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
                <a class="dashboard-logout" href="../logout.php"><span aria-hidden="true">↪</span> Keluar</a>
                <div class="dashboard-sidebar-user">
                    <span class="dashboard-avatar"><?php echo dashboardEscape($initial); ?></span>
                    <span><strong><?php echo $safeUsername; ?></strong><small><?php echo $roleLabel; ?></small></span>
                </div>
            </aside>
            <button class="dashboard-sidebar-overlay" type="button" aria-label="Tutup menu"></button>
            <div class="dashboard-main">
                <header class="dashboard-topbar">
                    <button class="mobile-menu-toggle" type="button" aria-label="Buka menu navigasi" aria-controls="dashboard-sidebar" aria-expanded="false">
                        <span aria-hidden="true"></span>
                        <span aria-hidden="true"></span>
                        <span aria-hidden="true"></span>
                    </button>
                    <div class="dashboard-search">
                        <span aria-hidden="true">⌕</span>
                        <input type="search" placeholder="<?php echo $searchHint; ?>" aria-label="<?php echo $searchHint; ?>">
                    </div>
                    <div class="dashboard-topbar-actions">
                        <span class="dashboard-date"><?php echo dashboardEscape(date('d M Y')); ?></span>
                        <button class="dashboard-notification" type="button" aria-label="Notifikasi">♧<span></span></button>
                        <span class="dashboard-avatar dashboard-avatar-light"><?php echo dashboardEscape($initial); ?></span>
                    </div>
                </header>
                <main class="dashboard-content">
                    <?php if ($error !== ''): ?>
                        <div class="dashboard-alert" role="alert">
                            <strong>Data belum tersedia</strong>
                            <span><?php echo dashboardEscape($error); ?></span>
                        </div>
                    <?php endif; ?>
    <?php
}

function dashboardRenderEnd()
{
    ?>
                </main>
                <footer class="dashboard-footer">
                    <span>SPACENEST · System for Faculty Space & Resource Management</span>
                    <span>&copy; <?php echo date('Y'); ?> Universitas Muhammadiyah Indonesia</span>
                </footer>
            </div>
        </div>
        <script src="../assets/js/app.js"></script>
    </body>
    </html>
    <?php
}

function dashboardMetricCard($label, $value, $icon, $tone, $hint)
{
    ?>
    <article class="dashboard-metric">
        <span class="metric-icon <?php echo dashboardEscape($tone); ?>" aria-hidden="true"><?php echo dashboardEscape($icon); ?></span>
        <div class="metric-copy">
            <span><?php echo dashboardEscape($label); ?></span>
            <strong><?php echo dashboardEscape($value); ?></strong>
            <small><?php echo dashboardEscape($hint); ?></small>
        </div>
    </article>
    <?php
}

function dashboardStatusLabel($status)
{
    $labels = [
        'diajukan' => 'Diajukan',
        'diproses' => 'Diproses',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
    ];
    return $labels[$status] ?? ucfirst((string) $status);
}

function dashboardRecentTable($rows, $showUsername)
{
    ?>
    <div class="dashboard-table-wrap">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <?php if ($showUsername): ?><th>Pengaju</th><?php endif; ?>
                    <th>Pengajuan</th>
                    <th>Kategori</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr><td class="dashboard-empty" colspan="<?php echo $showUsername ? '5' : '4'; ?>">Belum ada pengajuan untuk ditampilkan.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php if ($showUsername): ?><td><?php echo dashboardEscape($row['username']); ?></td><?php endif; ?>
                            <td class="table-title"><?php echo dashboardEscape($row['judul']); ?></td>
                            <td><?php echo dashboardEscape($row['kategori']); ?></td>
                            <td><?php echo dashboardEscape(date('d M Y', strtotime($row['created_at']))); ?></td>
                            <td><span class="status-pill status-<?php echo dashboardEscape($row['status']); ?>"><?php echo dashboardEscape(dashboardStatusLabel($row['status'])); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
