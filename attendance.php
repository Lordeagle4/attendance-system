<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';

if (isset($_POST['save'])) {
    $date = $_POST['date'];
    foreach ($_POST['status'] as $student_id => $status) {
        $stmt = $mysqli->prepare("INSERT INTO attendance (student_id, date, status) VALUES (?,?,?) 
                                ON DUPLICATE KEY UPDATE status=?");
        $stmt->bind_param("isss", $student_id, $date, $status, $status);
        $stmt->execute();
    }
    $msg = "Attendance saved successfully!";
}

$students = $mysqli->query("SELECT * FROM students ORDER BY student_name ASC");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    <div class="container mt-4">
        <h3>Mark Attendance</h3>
        <?php if (isset($msg))
            echo "<div class='alert alert-success'>$msg</div>"; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Select Date:</label>
                <input type="date" name="date" class="form-control" required>
            </div>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Roll No</th>
                        <th>Class</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $students->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['student_name'] ?></td>
                        <td><?= $row['roll_no'] ?></td>
                        <td><?= $row['class'] ?></td>
                        <td>
                            <select name="status[<?= $row['id'] ?>]" class="form-select">
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                            </select>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <button class="btn btn-success" name="save">Save Attendance</button>
        </form>
    </div>
</body>

</html>