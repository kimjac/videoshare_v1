<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        exit('Invalid CSRF token');
    }

    if (isset($_POST['delete_comment'])) {
        $commentId = (int)$_POST['comment_id'];
        db()->prepare('DELETE FROM comments WHERE id = ? OR parent_id = ?')->execute([$commentId, $commentId]);
    }

    if (isset($_POST['update_comment'])) {
        $commentId = (int)$_POST['comment_id'];
        $body = trim($_POST['body'] ?? '');
        db()->prepare('UPDATE comments SET body = ? WHERE id = ?')->execute([$body, $commentId]);
    }
}

$comments = db()->query('
    SELECT c.*, v.title AS video_title, u.username
    FROM comments c
    JOIN videos v ON v.id = c.video_id
    JOIN users u ON u.id = c.user_id
    ORDER BY c.created_at DESC
    LIMIT 100
')->fetchAll();

$videos = db()->query('
    SELECT v.*, u.username
    FROM videos v
    JOIN users u ON u.id = v.user_id
    ORDER BY v.created_at DESC
    LIMIT 100
')->fetchAll();

$metaTitle = meta_title(t('admin_panel','Admin panel'));
require __DIR__ . '/../includes/header.php';
?>
<div class="row g-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-0"><?= esc(t('admin_panel','Admin panel')) ?></h1>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3"><?= esc(t('moderate_comments','Moderate comments')) ?></h2>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($comments as $comment): ?>
                        <form method="post" class="border rounded p-3">
                            <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                            <input type="hidden" name="comment_id" value="<?= (int)$comment['id'] ?>">
                            <div class="small opacity-75 mb-2"><?= esc($comment['video_title']) ?> · <?= esc($comment['username']) ?></div>
                            <textarea name="body" rows="3" class="form-control mb-2"><?= esc($comment['body']) ?></textarea>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-warning" name="update_comment" value="1">Save</button>
                                <button class="btn btn-sm btn-outline-danger" name="delete_comment" value="1" onclick="return confirm('Delete comment?')">Delete</button>
                            </div>
                        </form>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Recent videos</h2>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>User</th>
                                <th>Category</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($videos as $video): ?>
                                <tr>
                                    <td><?= (int)$video['id'] ?></td>
                                    <td><a href="<?= esc(base_url('video.php?id=' . (int)$video['id'])) ?>"><?= esc($video['title']) ?></a></td>
                                    <td><?= esc($video['username']) ?></td>
                                    <td><?= esc($video['category']) ?></td>
                                    <td><?= esc($video['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-secondary mt-3 mb-0">
                    This v1 admin area includes moderation and overview. Settings/rights management can be added in v2.
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
