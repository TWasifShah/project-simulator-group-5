<?php require app_root() . '/views/layout/header.php'; ?>
<section class="hero">
    <div>
        <h1>Discover restaurants, food items, and real food stories.</h1>
        <p>Visitors can browse. Members can post reviews and share food experiences.</p>
        <div class="hero-actions">
            <a class="btn" href="<?= url('browse') ?>">Browse Food</a>
            <?php if (!is_logged_in()): ?><a class="btn secondary" href="<?= url('register') ?>">Join as Member</a><?php endif; ?>
        </div>
    </div>
</section>

<section class="card">
    <h2>Search Restaurants and Food Items</h2>
    <form id="searchForm" class="grid-form compact" method="get">
        <input type="text" name="q" placeholder="Search restaurant, food, or cuisine">
        <input type="text" name="location" placeholder="Location/city">
        <input type="text" name="area" placeholder="Area/neighborhood">
        <input type="number" step="0.01" name="min_price" placeholder="Min price">
        <input type="number" step="0.01" name="max_price" placeholder="Max price">
        <button class="btn" type="submit">Search</button>
    </form>
    <div id="searchMessage" class="muted"></div>
    <div id="searchResults" class="search-results"></div>
</section>

<section>
    <div class="section-title"><h2>Latest Restaurants</h2><a href="<?= url('browse') ?>">View all</a></div>
    <div class="grid cards">
        <?php foreach ($restaurants as $r): ?>
            <article class="card">
                <h3><?= e($r['name']) ?></h3>
                <p class="muted"><?= e($r['location']) ?>, <?= e($r['area']) ?></p>
                <p><?= e($r['short_background']) ?></p>
                <a class="text-link" href="<?= url('restaurant', ['id' => $r['id']]) ?>">Open Restaurant</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section>
    <div class="section-title"><h2>Popular Food Items</h2></div>
    <div class="grid cards">
        <?php foreach ($menuItems as $item): ?>
            <article class="card item-card">
                <?php if ($item['image_path']): ?><img src="<?= e($item['image_path']) ?>" alt="<?= e($item['name']) ?>"><?php endif; ?>
                <h3><?= e($item['name']) ?></h3>
                <p class="muted"><?= e($item['restaurant_name']) ?></p>
                <p class="price">Tk <?= number_format((float)$item['price'], 2) ?></p>
                <a class="text-link" href="<?= url('menu-item', ['id' => $item['id']]) ?>">View Details</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php require app_root() . '/views/layout/footer.php'; ?>
