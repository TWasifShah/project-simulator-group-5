<?php require app_root() . '/views/layout/header.php'; ?>
<section class="card detail-card">
    <h1><?= e($restaurant['name']) ?></h1>
    <p class="muted"><?= e($restaurant['location']) ?>, <?= e($restaurant['area']) ?></p>
    <h3>Background</h3>
    <p><?= nl2br(e($restaurant['short_background'])) ?></p>
    <h3>Goals</h3>
    <p><?= nl2br(e($restaurant['goals'])) ?></p>
</section>

<section>
    <h2>Menu Items</h2>
    <div class="grid cards">
        <?php foreach ($menuItems as $item): ?>
            <article class="card item-card">
                <?php if ($item['image_path']): ?><img src="<?= e($item['image_path']) ?>" alt="<?= e($item['name']) ?>"><?php endif; ?>
                <h3><?= e($item['name']) ?></h3>
                <p><?= e(mb_substr($item['description'], 0, 120)) ?>...</p>
                <p class="price">Tk <?= number_format((float)$item['price'], 2) ?></p>
                <a class="text-link" href="<?= url('menu-item', ['id' => $item['id']]) ?>">Details</a>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="card">
    <h2>Restaurant Reviews</h2>
    <?php if (is_member()): ?>
        <form id="restaurantReviewForm" class="comment-form" method="post">
            <input type="hidden" name="restaurant_id" value="<?= (int)$restaurant['id'] ?>">
            <label>Rating</label>
            <select name="rating">
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Average</option>
                <option value="2">2 - Poor</option>
                <option value="1">1 - Bad</option>
            </select>
            <textarea name="comment" maxlength="500" placeholder="Write a restaurant review" required></textarea>
            <button class="btn" type="submit">Post Restaurant Review</button>
        </form>
    <?php else: ?>
        <p class="muted">Login as member to post a restaurant review.</p>
    <?php endif; ?>
    <div id="restaurantReviews" class="comments">
        <?php foreach ($restaurantReviews as $rv): ?>
            <div class="comment">
                <strong><?= e($rv['user_name']) ?></strong> <span class="badge">Rating <?= (int)$rv['rating'] ?>/5</span>
                <p><?= nl2br(e($rv['comment'])) ?></p>
                <small><?= e($rv['created_at']) ?></small>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php require app_root() . '/views/layout/footer.php'; ?>
