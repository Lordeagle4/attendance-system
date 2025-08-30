<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require_login();


$error = $success = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';


    if (!$username || !$full_name || strlen($password) < 6) {
        $error = 'All fields required. Password must be at least 6 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $mysqli->prepare('INSERT INTO users (username, password, full_name) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $username, $hash, $full_name);
        if ($stmt->execute()) {
            $success = 'User created successfully!';
        } else {
            $error = 'Error: ' . ($mysqli->errno === 1062 ? 'Username already exists.' : $mysqli->error);
        }
    }
}
include __DIR__ . '/partials/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="mb-3">Create User</h4>
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
                <form method="post">
                    <?= csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary">Create</button>
                    <a class="btn btn-outline-secondary" href="dashboard.php">Back</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>