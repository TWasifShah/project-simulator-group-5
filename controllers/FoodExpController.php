<?php
class FoodExpController
{
    public function list(): void
    {
        $title = 'Food Experience';
        $posts = FoodExperience::posts();
        require app_root() . '/views/food_experience/posts_list.php';
    }

    public function details(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $post = FoodExperience::findPost($id);
        if (!$post) {
            flash('error', 'Post not found.');
            redirect('food-experience');
        }
        $comments = FoodExperience::comments($id);
        $title = $post['title'];
        require app_root() . '/views/food_experience/post_details.php';
    }

    public function create(): void
    {
        require_login();
        $title = 'Create Food Experience Post';
        $errors = [];
        $restaurants = Restaurant::all();
        $menuItems = MenuItem::all();
        require app_root() . '/views/food_experience/post_create.php';
    }

    public function store(): void
    {
        require_login();
        require_csrf_or_fail();
        [$errors, $data] = $this->validatePost();
        if ($errors) {
            $title = 'Create Food Experience Post';
            $restaurants = Restaurant::all();
            $menuItems = MenuItem::all();
            require app_root() . '/views/food_experience/post_create.php';
            return;
        }
        FoodExperience::createPost(current_user_id(), $data['title'], $data['content'], $data['post_type'], $data['restaurant_id'], $data['menu_item_id']);
        flash('success', 'Food experience post published.');
        redirect('food-experience');
    }

    public function edit(): void
    {
        require_login();
        $post = FoodExperience::findPost((int)($_GET['id'] ?? 0));
        if (!$post || ((int)$post['user_id'] !== current_user_id() && !is_admin())) {
            flash('error', 'You cannot edit this post.');
            redirect('food-experience');
        }
        $title = 'Edit Food Experience Post';
        $errors = [];
        $restaurants = Restaurant::all();
        $menuItems = MenuItem::all();
        require app_root() . '/views/food_experience/post_create.php';
    }

    public function update(): void
    {
        require_login();
        require_csrf_or_fail();
        $id = (int)($_POST['id'] ?? 0);
        $post = FoodExperience::findPost($id);
        if (!$post || (int)$post['user_id'] !== current_user_id()) {
            flash('error', 'You cannot update this post.');
            redirect('food-experience');
        }
        [$errors, $data] = $this->validatePost();
        if ($errors) {
            $title = 'Edit Food Experience Post';
            $restaurants = Restaurant::all();
            $menuItems = MenuItem::all();
            require app_root() . '/views/food_experience/post_create.php';
            return;
        }
        FoodExperience::updatePost($id, current_user_id(), $data['title'], $data['content'], $data['post_type'], $data['restaurant_id'], $data['menu_item_id']);
        flash('success', 'Post updated.');
        redirect('food-experience/details', ['id' => $id]);
    }

    public function delete(): void
    {
        require_login();
        require_csrf_or_fail();
        $id = (int)($_POST['id'] ?? 0);
        if (is_admin()) {
            FoodExperience::deletePost($id);
        } else {
            FoodExperience::deleteOwnPost($id, current_user_id());
        }
        flash('success', 'Post deleted.');
        redirect('food-experience');
    }

    private function validatePost(): array
    {
        $data = [
            'title' => trim_input('title'),
            'content' => trim_input('content'),
            'post_type' => trim_input('post_type'),
            'restaurant_id' => !empty($_POST['restaurant_id']) ? (int)$_POST['restaurant_id'] : null,
            'menu_item_id' => !empty($_POST['menu_item_id']) ? (int)$_POST['menu_item_id'] : null
        ];
        $errors = [];
        if ($data['title'] === '' || mb_strlen($data['title']) > 180) $errors['title'] = 'Title is required and must be within 180 characters.';
        if ($data['content'] === '') $errors['content'] = 'Content is required.';
        if (!in_array($data['post_type'], ['restaurant', 'food', 'both'], true)) $errors['post_type'] = 'Select a valid post type.';
        if ($data['restaurant_id'] && !Restaurant::find($data['restaurant_id'])) $errors['restaurant_id'] = 'Selected restaurant not found.';
        if ($data['menu_item_id'] && !MenuItem::find($data['menu_item_id'])) $errors['menu_item_id'] = 'Selected menu item not found.';
        return [$errors, $data];
    }

    public function addComment(): void
    {
        require_login();
        require_csrf_or_fail();
        $postId = (int)($_POST['post_id'] ?? 0);
        $comment = trim((string)($_POST['comment'] ?? ''));
        if (!FoodExperience::findPost($postId)) json_response(false, 'Post not found.', [], 404);
        if ($comment === '' || mb_strlen($comment) > 500) json_response(false, 'Comment is required and must be within 500 characters.', [], 422);
        $commentId = FoodExperience::addComment($postId, current_user_id(), $comment);
        json_response(true, 'Comment posted.', [
            'comment' => [
                'id' => $commentId,
                'user_name' => e($_SESSION['name']),
                'comment' => e($comment),
                'created_at' => date('Y-m-d H:i:s'),
                'can_delete' => true
            ]
        ]);
    }

    public function deleteComment(): void
    {
        require_login();
        require_csrf_or_fail();
        $id = (int)($_POST['id'] ?? 0);
        if (is_admin()) {
            FoodExperience::deleteComment($id);
            json_response(true, 'Comment deleted by admin.');
        }
        $ok = FoodExperience::deleteOwnComment($id, current_user_id());
        if (!$ok) json_response(false, 'You can delete only your own comment.', [], 403);
        json_response(true, 'Comment deleted.');
    }
}
