<?php require app_root() . '/views/layout/header.php'; ?>
<section class="section-title">
    <div><h1>Food Experience</h1><p class="muted">Read descriptive food and restaurant stories.</p></div>
    <?php if (is_logged_in()): ?><a class="btn" href="<?= url('food-experience/create') ?>">Add Post</a><?php endif; ?>
</section>
<div class="grid cards">
    <?php foreach ($posts as $p): ?>
        <article class="card post-card">
            <span class="badge"><?= e($p['post_type']) ?></span>
            <h2><a href="<?= url('food-experience/details', ['id' => $p['id']]) ?>"><?= e($p['title']) ?></a></h2>
            <p class="muted">By <?= e($p['author_name']) ?> on <?= e($p['created_at']) ?></p>
            <?php if ($p['restaurant_name'] || $p['menu_item_name']): ?>
                <p class="muted">Linked: <?= e($p['restaurant_name'] ?? '') ?> <?= $p['menu_item_name'] ? ' / ' . e($p['menu_item_name']) : '' ?></p>
            <?php endif; ?>
            <p><?= e(mb_substr($p['content'], 0, 180)) ?>...</p>
            <a class="text-link" href="<?= url('food-experience/details', ['id' => $p['id']]) ?>">Read More</a>
        </article>
    <?php endforeach; ?>
</div>
<?php require app_root() . '/views/layout/footer.php'; ?>
