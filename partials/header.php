<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$config = require __DIR__ . '/../config.php';
$appName = htmlspecialchars($config['app_name'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $appName; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="dashboard.php">📘 <?= $appName; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarsExample">
                <?php if (!empty($_SESSION['user'])): ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="students.php">Students</a></li>
                    <li class="nav-item"><a class="nav-link" href="attendance.php">Attendance</a></li>
                    <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-white-50 small">Signed in as
                        <strong><?= htmlspecialchars($_SESSION['user']['full_name']); ?></strong></span>
                    <a href="logout.php" class="btn btn-sm btn-light">Logout</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container py-4"></div>