<?php $layout = 'admin'; $adminPage = 'users'; $pageTitle = 'Users'; ?>

<h1 style="font-size:16px;" class="mb-2">Manage Users</h1>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Registered</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><strong><?= e($user['username']) ?></strong></td>
                    <td class="text-muted"><?= e($user['email']) ?></td>
                    <td>
                        <?= $user['role'] === 'admin'
                            ? '<span class="text-gold">Admin</span>'
                            : '<span class="text-muted">User</span>' ?>
                    </td>
                    <td>
                        <?= $user['is_active']
                            ? '<span class="text-green">Active</span>'
                            : '<span class="text-red">Blocked</span>' ?>
                    </td>
                    <td class="text-muted"><?= time_ago($user['created_at']) ?></td>
                    <td>
                        <form method="POST" action="/admin/users/<?= $user['id'] ?>/toggle" style="display:inline;">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm <?= $user['is_active'] ? 'btn-danger' : 'btn-primary' ?>">
                                <?= $user['is_active'] ? 'Block' : 'Unblock' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
