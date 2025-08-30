<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

include 'db.php';

// Fetch stats
$students = $mysqli->query("SELECT COUNT(*) AS total FROM students");
$students = $students ? $students->fetch_assoc()['total'] : 0;

$records = $mysqli->query("SELECT COUNT(*) AS total FROM attendance");
$records = $records ? $records->fetch_assoc()['total'] : 0;

$present = $mysqli->query("SELECT COUNT(*) AS total FROM attendance WHERE status='Present'");
$present = $present ? $present->fetch_assoc()['total'] : 0;

$absent = $mysqli->query("SELECT COUNT(*) AS total FROM attendance WHERE status='Absent'");
$absent = $absent ? $absent->fetch_assoc()['total'] : 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard - Attendance System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<body class="bg-light">
    <?php include __DIR__ . '/partials/header.php'; ?>

    <div class="container mt-4">
        <h3 class="fw-bold">Welcome, <?php echo ucfirst($_SESSION['user']['username']); ?> 👋</h3>
        <p class="text-muted">Here’s a quick overview of your attendance system.</p>

        <div class="row g-4 mt-2">
            <!-- Students -->
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-people-fill display-5 text-primary"></i>
                        <h5 class="mt-3">Students</h5>
                        <h2 class="fw-bold"><?php echo $students; ?></h2>
                    </div>
                </div>
            </div>

            <!-- Attendance Records -->
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-calendar-check-fill display-5 text-success"></i>
                        <h5 class="mt-3">Attendance Records</h5>
                        <h2 class="fw-bold"><?php echo $records; ?></h2>
                    </div>
                </div>
            </div>

            <!-- Presents -->
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-person-check-fill display-5 text-success"></i>
                        <h5 class="mt-3">Present</h5>
                        <h2 class="fw-bold"><?php echo $present; ?></h2>
                    </div>
                </div>
            </div>

            <!-- Absents -->
            <div class="col-md-3">
                <div class="card shadow-sm border-0 rounded-3 h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-person-x-fill display-5 text-danger"></i>
                        <h5 class="mt-3">Absent</h5>
                        <h2 class="fw-bold"><?php echo $absent; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="mt-5">
            <h4>Quick Actions</h4>
            <div class="d-flex gap-3 flex-wrap">
                <a href="students.php" class="btn btn-primary"><i class="bi bi-person-plus-fill me-2"></i>Manage
                    Students</a>
                <a href="attendance.php" class="btn btn-success"><i class="bi bi-calendar-plus me-2"></i>Take
                    Attendance</a>
                <a href="reports.php" class="btn btn-warning text-white"><i class="bi bi-graph-up-arrow me-2"></i>View
                    Reports</a>
            </div>
        </div>
    </div>
</body>

</html>