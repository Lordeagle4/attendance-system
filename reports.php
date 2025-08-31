<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';

$pageTitle = "Attendance Reports";

$result = $mysqli->query("
    SELECT s.student_name, s.roll_no, s.class,
           SUM(a.status='Present') AS presents,
           SUM(a.status='Absent') AS absents,
           COUNT(a.id) AS total_classes
    FROM students s
    LEFT JOIN attendance a ON s.id = a.student_id
    GROUP BY s.id
");
?>
<?php include __DIR__ . '/partials/header.php'; ?>

<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Attendance Reports</h3>
            <div class="d-flex gap-2 mb-3">
                <div class="mb-3">
                    <a href="export_csv.php" class="btn btn-success">Export as CSV</a>
                </div>
                <div class="mb-3">
                    <a href="export_pdf.php" class="btn btn-danger">Export as PDF</a>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Roll No</th>
                        <th>Class</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Total</th>
                        <th>% Attendance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()):
                        $percentage = ($row['total_classes'] > 0) ? round(($row['presents'] / $row['total_classes']) * 100, 2) : 0;
                        $row = array_map('htmlSafeOutput', $row);
                    ?>
                        <tr>
                            <td><?= $row['student_name'] ?></td>
                            <td><?= $row['roll_no'] ?></td>
                            <td><?= $row['class'] ?></td>
                            <td><?= $row['presents'] ?></td>
                            <td><?= $row['absents'] ?></td>
                            <td><?= $row['total_classes'] ?></td>
                            <td><?= $percentage ?>%</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php include __DIR__ . '/partials/footer.php'; ?>