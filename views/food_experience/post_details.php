<?php require app_root() . '/views/layout/header.php'; ?>
<article class="card detail-card">
    <span class="badge"><?= e($post['post_type']) ?></span>
    <h1><?= e($post['title']) ?></h1>
    <p class="muted">By <?= e($post['author_name']) ?> on <?= e($post['created_at']) ?></p>
    <?php if ($post['restaurant_name']): ?><p>Restaurant: <?= e($post['restaurant_name']) ?></p><?php endif; ?>
    <?php if ($post['menu_item_name']): ?><p>Food Item: <?= e($post['menu_item_name']) ?></p><?php endif; ?>
    <p><?= nl2br(e($post['content'])) ?></p>
    <?php if (is_admin() || current_user_id() === (int)$post['user_id']): ?>
        <div class="actions">
            <?php if (current_user_id() === (int)$post['user_id']): ?><a class="btn secondary" href="<?= url('food-experience/edit', ['id' => $post['id']]) ?>">Edit</a><?php endif; ?>
            <form method="post" action="<?= url('food-experience/delete') ?>" onsubmit="return confirm('Delete this post?')">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                <button class="link-danger" type="submit">Delete Post</button>
            </form>
        </div>
    <?php endif; ?>
</article>

<section class="card">
    <h2>Comments</h2>
    <?php if (is_logged_in()): ?>
        <form id="foodCommentForm" class="comment-form" method="post">
            <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
            <textarea name="comment" maxlength="500" placeholder="Write a comment" required></textarea>
            <button class="btn" type="submit">Post Comment</button>
        </form>
    <?php else: ?>
        <p class="muted">Visitors can read comments. Login to comment.</p>
    <?php endif; ?>
    <div id="foodCommentsList" class="comments">
        <?php foreach ($comments as $c): ?>
            <div class="comment" data-comment-id="<?= (int)$c['id'] ?>">
                <strong><?= e($c['user_name']) ?></strong>
                <p><?= nl2br(e($c['comment'])) ?></p>
                <small><?= e($c['created_at']) ?></small>
                <?php if (is_admin() || current_user_id() === (int)$c['user_id']): ?>
                    <button class="link-danger delete-food-comment" data-id="<?= (int)$c['id'] ?>" type="button">Delete</button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php require app_root() . '/views/layout/footer.php'; ?>
