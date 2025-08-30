<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
include 'db.php';

// ADD student
if (isset($_POST['add'])) {
    $name = $_POST['student_name'];
    $roll = $_POST['roll_no'];
    $class = $_POST['class'];

    $stmt = $mysqli->prepare("INSERT INTO students (student_name, roll_no, class) VALUES (?,?,?)");
    $stmt->bind_param("sss", $name, $roll, $class);
    $stmt->execute();
    header("Location: students.php");
}

// DELETE student
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $mysqli->query("DELETE FROM students WHERE id=$id");
    header("Location: students.php");
}
$students = $mysqli->query("SELECT * FROM students ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Students</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    <div class="container mt-4">
        <h3>Manage Students</h3>
        <form method="POST" class="row g-2 mt-3 mb-4">
            <div class="col-md-3"><input type="text" name="student_name" class="form-control" placeholder="Student Name"
                    required></div>
            <div class="col-md-3"><input type="text" name="roll_no" class="form-control" placeholder="Roll No" required>
            </div>
            <div class="col-md-3"><input type="text" name="class" class="form-control" placeholder="Class" required>
            </div>
            <div class="col-md-3"><button class="btn btn-primary w-100" name="add">Add Student</button></div>
        </form>
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Roll No</th>
                    <th>Class</th>
                    <th>Action</th>
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
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                            onclick="return confirm('Delete this student?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>