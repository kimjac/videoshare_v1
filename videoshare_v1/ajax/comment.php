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

$videoId = (int)($_POST['video_id'] ?? 0);
$parentId = (int)($_POST['parent_id'] ?? 0);
$body = trim($_POST['body'] ?? '');

if ($videoId < 1 || $body === '') {
    echo json_encode(['success' => false, 'message' => 'Missing data.']);
    exit;
}

$stmt = db()->prepare('INSERT INTO comments (video_id, user_id, parent_id, body, upvotes, downvotes) VALUES (?, ?, ?, ?, 0, 0)');
$stmt->execute([$videoId, current_user()['id'], $parentId, $body]);

echo json_encode(['success' => true]);
