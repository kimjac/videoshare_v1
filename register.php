<?php
require_once __DIR__ . '/includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } else {
        $stmt = db()->prepare('SELECT COUNT(*) FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ((int)$stmt->fetchColumn() > 0) {
            $error = 'User already exists.';
        } else {
            $stmt = db()->prepare('INSERT INTO users (username, email, password_hash, is_admin) VALUES (?, ?, ?, 0)');
            $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);
            redirect(base_url('login.php'));
        }
    }
}
$metaTitle = meta_title('Register');
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-4">Register</h1>
                <?php if ($error): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>
                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-danger w-100">Create account</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
