<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';

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
<!DOCTYPE html>
<html>

<head>
    <title>Attendance Reports</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    <div class="container mt-4">
        <h3>Attendance Reports</h3>
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
</body>

</html>