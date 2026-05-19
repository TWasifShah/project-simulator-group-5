<?php /* Reusable review form partial. Main pages include their own inline version. */ ?>
<form id="reviewForm" class="comment-form" method="post">
    <input type="hidden" name="menu_item_id" value="<?= (int)($menuItemId ?? 0) ?>">
    <textarea name="comment" maxlength="500" required placeholder="Write your review"></textarea>
    <button class="btn" type="submit">Post Review</button>
</form>
