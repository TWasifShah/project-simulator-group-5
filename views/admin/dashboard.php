<?php require app_root() . '/views/layout/header.php'; ?>
<section class="dashboard-head">
    <h1>Admin Dashboard</h1>
    <p class="muted">Manage restaurant content, menu items, members, reviews, and food experience posts.</p>
</section>
<div class="grid stats">
    <div class="card stat"><span><?= $counts['restaurants'] ?></span><p>Restaurants</p></div>
    <div class="card stat"><span><?= $counts['menu_items'] ?></span><p>Menu Items</p></div>
    <div class="card stat"><span><?= $counts['reviews'] ?></span><p>Food Reviews</p></div>
    <div class="card stat"><span><?= $counts['food_posts'] ?></span><p>Experience Posts</p></div>
    <div class="card stat"><span><?= $counts['users'] ?></span><p>Total Users</p></div>
</div>
<section class="card quick-links">
    <a class="btn" href="<?= url('admin/restaurants/create') ?>">Add Restaurant</a>
    <a class="btn" href="<?= url('admin/menu-items/create') ?>">Add Menu Item</a>
    <a class="btn secondary" href="<?= url('admin/reviews') ?>">Moderate Reviews</a>
    <a class="btn secondary" href="<?= url('admin/food-moderation') ?>">Food Experience Moderation</a>
</section>
<?php require app_root() . '/views/layout/footer.php'; ?>
