<?php require app_root() . '/views/layout/header.php'; ?>
<section class="section-title"><h1>Members</h1></section>
<section class="card table-card">
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Joined</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($members as $m): ?>
            <tr>
                <td><?= e($m['name']) ?></td>
                <td><?= e($m['email']) ?></td>
                <td><?= e($m['created_at']) ?></td>
                <td>
                    <form method="post" action="<?= url('admin/members/delete') ?>" onsubmit="return confirm('Delete this member and all related content?')">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                        <button class="link-danger" type="submit">Delete Member</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require app_root() . '/views/layout/footer.php'; ?>
