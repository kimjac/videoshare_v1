<?php
require_once __DIR__ . '/functions.php';
$metaTitle = $metaTitle ?? meta_title();
$metaDescription = $metaDescription ?? meta_description();
?>
<!doctype html>
<html lang="<?= esc(current_lang()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($metaTitle) ?></title>
    <meta name="description" content="<?= esc($metaDescription) ?>">
    <meta property="og:title" content="<?= esc($metaTitle) ?>">
    <meta property="og:description" content="<?= esc($metaDescription) ?>">
    <meta property="og:type" content="website">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= esc(base_url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body class="theme-<?= esc(theme()) ?>">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm border-bottom border-danger-subtle">
    <div class="container">
        <a class="navbar-brand text-danger fw-bold" href="<?= esc(base_url()) ?>">VideoShare</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainnav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainnav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= esc(base_url()) ?>"><?= esc(t('nav_home','Home')) ?></a></li>
                <li class="nav-item"><a class="nav-link" href="<?= esc(base_url('upload.php')) ?>"><?= esc(t('nav_upload','Upload')) ?></a></li>
                <?php if (is_admin()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= esc(base_url('admin/index.php')) ?>"><?= esc(t('nav_admin','Admin')) ?></a></li>
                <?php endif; ?>
            </ul>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <a class="btn btn-sm btn-outline-light" href="?lang=da"><?= esc(t('lang_da','DA')) ?></a>
                <a class="btn btn-sm btn-outline-light" href="?lang=en"><?= esc(t('lang_en','EN')) ?></a>
                <a class="btn btn-sm btn-outline-warning" href="?theme=dark"><?= esc(t('theme_dark','Dark')) ?></a>
                <a class="btn btn-sm btn-outline-info" href="?theme=light"><?= esc(t('theme_light','Light')) ?></a>
                <?php if (is_logged_in()): ?>
                    <span class="text-light small"><?= esc(current_user()['username']) ?></span>
                    <a class="btn btn-sm btn-danger" href="<?= esc(base_url('logout.php')) ?>"><?= esc(t('logout','Logout')) ?></a>
                <?php else: ?>
                    <a class="btn btn-sm btn-outline-success" href="<?= esc(base_url('login.php')) ?>"><?= esc(t('login','Login')) ?></a>
                    <a class="btn btn-sm btn-success" href="<?= esc(base_url('register.php')) ?>"><?= esc(t('register','Register')) ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<main class="container py-4">
