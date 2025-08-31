<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../utils/functions.php';
$config = require __DIR__ . '/../config.php';
$appName = htmlSafeOutput($config['app_name']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $appName; ?> <?php if (!empty($pageTitle)) echo " - " . htmlSafeOutput($pageTitle); ?></title>
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
                    <li class="nav-item"><a class="nav-link <?= active_class('dashboard.php'); ?>" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_class('students.php'); ?>" href="students.php">Students</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_class('attendance.php'); ?>" href="attendance.php">Attendance</a></li>
                    <li class="nav-item"><a class="nav-link <?= active_class('reports.php'); ?>" href="reports.php">Reports</a></li>
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