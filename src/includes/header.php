<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPAK</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header class="topbar">
        <div class="brand">SIPAK</div>
        <div class="user-info">
            <span><?php echo htmlspecialchars($_SESSION['user']['username'] ?? 'User'); ?></span>
            <a href="../logout.php">Logout</a>
        </div>
    </header>
