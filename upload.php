<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Invalid CSRF token.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = normalize_category($_POST['category'] ?? 'sjov');

        if ($title === '' || $description === '') {
            $error = 'Title and description are required.';
        } else {
            $videoRel = save_uploaded_video($_FILES['video_file'] ?? [], $category);

            if ($videoRel === null) {
                $error = 'Video upload failed. Only MP4 is allowed.';
            } else {
                $thumbRel = save_uploaded_thumbnail($_FILES['thumbnail'] ?? []);
                if ($thumbRel === null) {
                    $videoAbs = __DIR__ . '/' . $videoRel;
                    $thumbRelAttempt = 'uploads/thumbs/' . uniqid('thumb_', true) . '.jpg';
                    $thumbAbs = __DIR__ . '/' . $thumbRelAttempt;
                    if (attempt_generate_thumbnail($videoAbs, $thumbAbs)) {
                        $thumbRel = $thumbRelAttempt;
                    }
                }

                $stmt = db()->prepare('
                    INSERT INTO videos (user_id, title, description, category, video_path, thumbnail, likes, dislikes)
                    VALUES (?, ?, ?, ?, ?, ?, 0, 0)
                ');
                $stmt->execute([
                    current_user()['id'],
                    $title,
                    $description,
                    $category,
                    $videoRel,
                    $thumbRel,
                ]);
                $message = 'Video uploaded successfully.';
            }
        }
    }
}

$metaTitle = meta_title(t('upload_video','Upload video'));
require __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-4"><?= esc(t('upload_video','Upload video')) ?></h1>
                <?php if ($message): ?><div class="alert alert-success"><?= esc($message) ?></div><?php endif; ?>
                <?php if ($error): ?><div class="alert alert-danger"><?= esc($error) ?></div><?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label"><?= esc(t('title','Title')) ?></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= esc(t('description','Description')) ?></label>
                        <textarea name="description" rows="5" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= esc(t('category','Category')) ?></label>
                        <select name="category" class="form-select">
                            <?php foreach (categories() as $cat): ?>
                                <option value="<?= esc($cat) ?>"><?= esc(ucfirst($cat)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= esc(t('video_file','MP4 file')) ?></label>
                        <input type="file" name="video_file" class="form-control" accept="video/mp4" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label"><?= esc(t('thumbnail','Thumbnail')) ?> (optional JPG/PNG/WebP)</label>
                        <input type="file" name="thumbnail" class="form-control" accept="image/jpeg,image/png,image/webp">
                    </div>
                    <button class="btn btn-danger"><?= esc(t('save','Save')) ?></button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
