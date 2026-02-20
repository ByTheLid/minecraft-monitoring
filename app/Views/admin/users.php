<?php $layout = 'admin'; $adminPage = 'users'; $pageTitle = 'Users'; ?>

<h1 class="page-title mb-2">Manage Users</h1>

<div class="admin-search">
    <form method="GET" action="/admin/users" class="flex gap-1">
        <input type="text" name="search" class="form-control" placeholder="Search by username or email..."
               value="<?= e($search ?? '') ?>">
        <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
        <?php if (!empty($search)): ?>
            <a href="/admin/users" class="btn btn-sm btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Servers</th>
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
                        <?php $cnt = $serverCounts[$user['id']] ?? 0; ?>
                        <?= $cnt > 0 ? '<span class="badge badge-blue">' . $cnt . '</span>' : '<span class="text-muted">0</span>' ?>
                    </td>
                    <td>
                        <?= $user['role'] === 'admin'
                            ? '<span class="badge badge-gold"><i class="fas fa-shield-alt"></i> Admin</span>'
                            : '<span class="badge badge-muted">User</span>' ?>
                    </td>
                    <td>
                        <?= $user['is_active']
                            ? '<span class="badge badge-green">Active</span>'
                            : '<span class="badge badge-red">Blocked</span>' ?>
                    </td>
                    <td class="text-muted"><?= time_ago($user['created_at']) ?></td>
                    <td>
                        <div class="flex gap-1">
                            <?php if ($user['id'] !== auth()['id']): ?>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <button class="btn btn-sm btn-secondary"
                                            onclick="confirmAction('/admin/users/<?= $user['id'] ?>/role', 'Demote to User', 'Remove admin rights from <?= e($user['username']) ?>?')">
                                        <i class="fas fa-arrow-down"></i> Demote
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-gold"
                                            onclick="confirmAction('/admin/users/<?= $user['id'] ?>/role', 'Promote to Admin', 'Grant admin rights to <?= e($user['username']) ?>?')">
                                        <i class="fas fa-arrow-up"></i> Promote
                                    </button>
                                <?php endif; ?>

                                <?php if ($user['is_active']): ?>
                                    <button class="btn btn-sm btn-danger"
                                            onclick="confirmAction('/admin/users/<?= $user['id'] ?>/toggle', 'Block User', 'Are you sure you want to block <?= e($user['username']) ?>?')">
                                        Block
                                    </button>
                                <?php else: ?>
                                    <form method="POST" action="/admin/users/<?= $user['id'] ?>/toggle" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-primary">Unblock</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:12px;">Current user</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?>
                <tr><td colspan="8" class="text-center text-muted" style="padding:20px;">No users found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (($meta['total_pages'] ?? 1) > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $meta['total_pages']; $i++): ?>
            <?php if ($i === $meta['page']): ?>
                <span class="active"><?= $i ?></span>
            <?php else: ?>
                <a href="/admin/users?search=<?= urlencode($search ?? '') ?>&page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
<?php endif; ?>
