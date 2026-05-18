<?php require app_root() . '/views/layout/header.php'; ?>
<section class="card detail-card menu-detail">
    <?php if ($item['image_path']): ?><img class="detail-image" src="<?= e($item['image_path']) ?>" alt="<?= e($item['name']) ?>"><?php endif; ?>
    <div>
        <h1><?= e($item['name']) ?></h1>
        <p class="muted">Restaurant: <a href="<?= url('restaurant', ['id' => $item['restaurant_id']]) ?>"><?= e($item['restaurant_name']) ?></a></p>
        <p class="muted"><?= e($item['location']) ?>, <?= e($item['area']) ?></p>
        <p class="price large">Tk <?= number_format((float)$item['price'], 2) ?></p>
        <p><?= nl2br(e($item['description'])) ?></p>
    </div>
</section>

<section class="card">
    <h2>Food Item Reviews</h2>
    <?php if (is_member()): ?>
        <form id="reviewForm" class="comment-form" method="post">
            <input type="hidden" name="menu_item_id" value="<?= (int)$item['id'] ?>">
            <textarea name="comment" maxlength="500" placeholder="Write your review" required></textarea>
            <button class="btn" type="submit">Post Review</button>
        </form>
    <?php else: ?>
        <p class="muted">Visitors can read reviews. Login as member to post a review.</p>
    <?php endif; ?>

    <div id="reviewsList" class="comments">
        <?php foreach ($reviews as $review): ?>
            <div class="comment" data-review-id="<?= (int)$review['id'] ?>">
                <strong><?= e($review['user_name']) ?></strong>
                <p><?= nl2br(e($review['comment'])) ?></p>
                <small><?= e($review['created_at']) ?></small>
                <?php if (is_admin() || current_user_id() === (int)$review['user_id']): ?>
                    <button class="link-danger delete-review" data-id="<?= (int)$review['id'] ?>" type="button">Delete</button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php require app_root() . '/views/layout/footer.php'; ?>
