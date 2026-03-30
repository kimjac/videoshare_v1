<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Login required.']);
    exit;
}
if (!verify_csrf($_POST['csrf_token'] ?? null)) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}

$commentId = (int)($_POST['comment_id'] ?? 0);
$vote = $_POST['vote'] ?? '';

if ($commentId < 1 || !in_array($vote, ['up', 'down'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$column = $vote === 'up' ? 'upvotes' : 'downvotes';
db()->prepare("UPDATE comments SET {$column} = {$column} + 1 WHERE id = ?")->execute([$commentId]);

$stmt = db()->prepare('SELECT upvotes, downvotes FROM comments WHERE id = ?');
$stmt->execute([$commentId]);
$row = $stmt->fetch();

echo json_encode([
    'success' => true,
    'upvotes' => (int)($row['upvotes'] ?? 0),
    'downvotes' => (int)($row['downvotes'] ?? 0),
]);
