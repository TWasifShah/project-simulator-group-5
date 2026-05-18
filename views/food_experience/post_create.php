<?php require app_root() . '/views/layout/header.php'; ?>
<section class="card form-card">
    <h1><?= !empty($post['id']) ? 'Edit Food Experience Post' : 'Create Food Experience Post' ?></h1>
    <?php $action = !empty($post['id']) ? url('food-experience/edit') : url('food-experience/create'); ?>
    <form id="foodPostForm" method="post" action="<?= e($action) ?>" novalidate>
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <?php if (!empty($post['id'])): ?><input type="hidden" name="id" value="<?= (int)$post['id'] ?>"><?php endif; ?>
    <label>Title</label>
    <input type="text" name="title" maxlength="180" value="<?= e($_POST['title'] ?? $post['title'] ?? '') ?>" required>
    <span class="field-error"><?= e($errors['title'] ?? '') ?></span>
    <label>Content</label>
    <textarea name="content" required><?= e($_POST['content'] ?? $post['content'] ?? '') ?></textarea>
    <span class="field-error"><?= e($errors['content'] ?? '') ?></span>
    <label>Post Type</label>
    <select name="post_type" required>
        <?php $type = $_POST['post_type'] ?? $post['post_type'] ?? 'both'; ?>
        <option value="restaurant" <?= $type === 'restaurant' ? 'selected' : '' ?>>Restaurant</option>
        <option value="food" <?= $type === 'food' ? 'selected' : '' ?>>Food</option>
        <option value="both" <?= $type === 'both' ? 'selected' : '' ?>>Both</option>
    </select>
    <span class="field-error"><?= e($errors['post_type'] ?? '') ?></span>
    <label>Optional Restaurant Link</label>
    <select name="restaurant_id">
        <option value="">None</option>
        <?php foreach ($restaurants as $r): ?>
            <?php $selected = (string)($_POST['restaurant_id'] ?? $post['restaurant_id'] ?? '') === (string)$r['id']; ?>
            <option value="<?= (int)$r['id'] ?>" <?= $selected ? 'selected' : '' ?>><?= e($r['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <label>Optional Menu Item Link</label>
    <select name="menu_item_id">
        <option value="">None</option>
        <?php foreach ($menuItems as $m): ?>
            <?php $selected = (string)($_POST['menu_item_id'] ?? $post['menu_item_id'] ?? '') === (string)$m['id']; ?>
            <option value="<?= (int)$m['id'] ?>" <?= $selected ? 'selected' : '' ?>><?= e($m['name']) ?> - <?= e($m['restaurant_name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn" type="submit">Save Post</button>
</form>
</section>
<?php require app_root() . '/views/layout/footer.php'; ?>
