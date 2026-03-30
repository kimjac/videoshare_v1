<?php
require_once __DIR__ . '/includes/functions.php';

$search = trim($_GET['q'] ?? '');
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'popular';
$page = (int)($_GET['page'] ?? 1);
[$page, $offset] = paginate($page, ITEMS_PER_PAGE);

$sql = '
    SELECT v.*,
           u.username,
           COUNT(c.id) AS comment_count
    FROM videos v
    JOIN users u ON u.id = v.user_id
    LEFT JOIN comments c ON c.video_id = v.id
    WHERE 1=1
';
$params = [];

if ($search !== '') {
    $sql .= ' AND (v.title LIKE ? OR v.description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if ($category !== '' && in_array($category, categories(), true)) {
    $sql .= ' AND v.category = ?';
    $params[] = $category;
}

$sql .= ' GROUP BY v.id ';

if ($sort === 'latest') {
    $sql .= ' ORDER BY v.created_at DESC ';
} else {
    $sql .= ' ORDER BY comment_count DESC, v.likes DESC, v.dislikes ASC, v.created_at DESC ';
}

$countSql = 'SELECT COUNT(*) FROM videos v WHERE 1=1';
$countParams = [];
if ($search !== '') {
    $countSql .= ' AND (v.title LIKE ? OR v.description LIKE ?)';
    $countParams[] = '%' . $search . '%';
    $countParams[] = '%' . $search . '%';
}
if ($category !== '' && in_array($category, categories(), true)) {
    $countSql .= ' AND v.category = ?';
    $countParams[] = $category;
}

$totalStmt = db()->prepare($countSql);
$totalStmt->execute($countParams);
$totalVideos = (int)$totalStmt->fetchColumn();
$totalPages = max(1, (int)ceil($totalVideos / ITEMS_PER_PAGE));

$sql .= ' LIMIT ' . ITEMS_PER_PAGE . ' OFFSET ' . $offset;
$stmt = db()->prepare($sql);
$stmt->execute($params);
$videos = $stmt->fetchAll();

$metaTitle = meta_title(t('home_title','Latest videos'));
$metaDescription = meta_description('Browse videos sorted by engagement, comments and likes.');
require __DIR__ . '/includes/header.php';
?>
<div class="row g-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <form class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label"><?= esc(t('search','Search')) ?></label>
                        <input type="text" name="q" class="form-control" value="<?= esc($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?= esc(t('category','Category')) ?></label>
                        <select name="category" class="form-select">
                            <option value=""><?= esc(t('all_categories','All categories')) ?></option>
                            <?php foreach (categories() as $cat): ?>
                                <option value="<?= esc($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= esc(ucfirst($cat)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><?= esc(t('sort','Sort')) ?></label>
                        <select name="sort" class="form-select">
                            <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>><?= esc(t('sort_popular','Most comments/likes')) ?></option>
                            <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>><?= esc(t('sort_latest','Latest')) ?></option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-danger w-100"><?= esc(t('submit','Submit')) ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php foreach ($videos as $video): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card video-card shadow-sm h-100">
                <img src="<?= esc(thumbnail_for($video)) ?>" class="video-thumb" alt="<?= esc($video['title']) ?>">
                <div class="card-body d-flex flex-column">
                    <h5 class="text-danger"><?= esc($video['title']) ?></h5>
                    <div class="small opacity-75 mb-2">
                        <?= esc($video['category']) ?> · <?= (int)$video['comment_count'] ?> <?= esc(t('comments','Comments')) ?>
                    </div>
                    <p class="mb-3"><?= esc(mb_strimwidth($video['description'], 0, 140, '...')) ?></p>
                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <span class="small"><?= esc($video['username']) ?></span>
                        <a class="btn btn-danger btn-sm" href="<?= esc(base_url('video.php?id=' . (int)$video['id'])) ?>"><?= esc(t('watch_video','Watch video')) ?></a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="col-12">
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(['q' => $search, 'category' => $category, 'sort' => $sort, 'page' => $i]) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
