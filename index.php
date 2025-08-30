<?php
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';


if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$msg = $_GET['msg'] ?? '';
$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';


    $stmt = $mysqli->prepare('SELECT id, username, password, full_name FROM users WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();


    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = ['id' => $user['id'], 'username' => $user['username'], 'full_name' => $user['full_name']];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}


include __DIR__ . '/partials/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h3 class="mb-3 text-center">Welcome 👋</h3>
                <p class="text-muted text-center">Sign in to continue</p>
                <?php if ($msg): ?>
                    <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <form method="post" class="mt-3">
                    <?= csrf_field(); ?>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100">Login</button>
                </form>
                <div class="mt-3 small text-center text-muted">
                    Default admin: <code>admin / admin123</code>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>