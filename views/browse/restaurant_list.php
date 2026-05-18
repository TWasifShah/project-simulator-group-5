<?php require app_root() . '/views/layout/header.php'; ?>
<section class="card">
    <h1>Browse Restaurants</h1>
    <form id="searchForm" class="grid-form compact" method="get">
        <input type="text" name="q" placeholder="Search restaurant or food item">
        <input type="text" name="location" placeholder="Location">
        <input type="text" name="area" placeholder="Area">
        <input type="number" step="0.01" name="min_price" placeholder="Min price">
        <input type="number" step="0.01" name="max_price" placeholder="Max price">
        <button class="btn" type="submit">Filter</button>
    </form>
    <div id="searchMessage" class="muted"></div>
    <div id="searchResults" class="search-results"></div>
</section>

<section>
    <div class="grid cards">
        <?php foreach ($restaurants as $r): ?>
            <article class="card">
                <h2><?= e($r['name']) ?></h2>
                <p class="muted"><?= e($r['location']) ?>, <?= e($r['area']) ?></p>
                <p><?= e($r['short_background']) ?></p>
                <a class="btn secondary" href="<?= url('restaurant', ['id' => $r['id']]) ?>">See Menu</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require app_root() . '/views/layout/footer.php'; ?>
