<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('
    SELECT v.*, u.username
    FROM videos v
    JOIN users u ON u.id = v.user_id
    WHERE v.id = ?
');
$stmt->execute([$id]);
$video = $stmt->fetch();

if (!$video) {
    http_response_code(404);
    exit('Video not found');
}

$comments = comment_tree($id);
$metaTitle = meta_title($video['title']);
$metaDescription = meta_description($video['description']);
require __DIR__ . '/includes/header.php';

function render_comments(array $comments, int $videoId): void {
    foreach ($comments as $comment): ?>
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <strong><?= esc($comment['username']) ?></strong>
                        <div class="small opacity-75"><?= esc($comment['created_at']) ?></div>
                    </div>
                    <div class="comment-actions d-flex gap-2 align-items-start">
                        <button class="btn btn-sm btn-outline-success" data-reply-toggle="reply-<?= (int)$comment['id'] ?>"><?= esc(t('reply','Reply')) ?></button>
                    </div>
                </div>
                <p class="mt-3 mb-2"><?= nl2br(esc($comment['body'])) ?></p>
                <div class="comment-votes d-flex gap-2 align-items-center">
                    <button class="btn btn-sm btn-outline-success" data-comment-vote="up" data-comment-id="<?= (int)$comment['id'] ?>">👍 <span class="upvotes"><?= (int)$comment['upvotes'] ?></span></button>
                    <button class="btn btn-sm btn-outline-danger" data-comment-vote="down" data-comment-id="<?= (int)$comment['id'] ?>">👎 <span class="downvotes"><?= (int)$comment['downvotes'] ?></span></button>
                </div>

                <?php if (is_logged_in()): ?>
                    <form id="reply-<?= (int)$comment['id'] ?>" class="mt-3 d-none" data-ajax-comment-form>
                        <input type="hidden" name="video_id" value="<?= (int)$videoId ?>">
                        <input type="hidden" name="parent_id" value="<?= (int)$comment['id'] ?>">
                        <div class="mb-2">
                            <textarea name="body" class="form-control" rows="3" required></textarea>
                        </div>
                        <button class="btn btn-danger btn-sm"><?= esc(t('submit','Submit')) ?></button>
                    </form>
                <?php endif; ?>

                <?php if (!empty($comment['children'])): ?>
                    <div class="comment-box mt-3">
                        <?php render_comments($comment['children'], $videoId); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach;
}
?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="ratio ratio-16x9 bg-black">
                <video controls poster="<?= esc(thumbnail_for($video)) ?>">
                    <source src="<?= esc(base_url($video['video_path'])) ?>" type="video/mp4">
                </video>
            </div>
            <div class="card-body">
                <h1 class="h3 text-danger"><?= esc($video['title']) ?></h1>
                <div class="small opacity-75 mb-3"><?= esc($video['username']) ?> · <?= esc($video['category']) ?> · <?= esc($video['created_at']) ?></div>
                <div class="video-description"><?= esc($video['description']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h5"><?= esc(t('comments','Comments')) ?></h2>
                <?php if (is_logged_in()): ?>
                    <form class="mb-4" data-ajax-comment-form>
                        <input type="hidden" name="video_id" value="<?= (int)$video['id'] ?>">
                        <input type="hidden" name="parent_id" value="0">
                        <div class="mb-2">
                            <textarea name="body" class="form-control" rows="4" required></textarea>
                        </div>
                        <button class="btn btn-danger"><?= esc(t('submit','Submit')) ?></button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">Log in to comment.</div>
                <?php endif; ?>

                <?php render_comments($comments, (int)$video['id']); ?>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
