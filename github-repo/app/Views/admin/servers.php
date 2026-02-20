<?php $layout = 'admin'; $adminPage = 'servers'; $pageTitle = 'Servers'; ?>

<h1 class="page-title mb-2">Manage Servers</h1>

<div class="admin-search">
    <form method="GET" action="/admin/servers" class="flex gap-1">
        <input type="hidden" name="filter" value="<?= e($filter) ?>">
        <input type="text" name="search" class="form-control" placeholder="Search by name or IP..."
               value="<?= e($search ?? '') ?>">
        <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
        <?php if (!empty($search)): ?>
            <a href="/admin/servers?filter=<?= e($filter) ?>" class="btn btn-sm btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="tabs mb-2">
    <a href="/admin/servers?filter=all<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="tab <?= $filter === 'all' ? 'active' : '' ?>">All</a>
    <a href="/admin/servers?filter=pending<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="tab <?= $filter === 'pending' ? 'active' : '' ?>">Pending</a>
    <a href="/admin/servers?filter=approved<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="tab <?= $filter === 'approved' ? 'active' : '' ?>">Approved</a>
    <a href="/admin/servers?filter=blocked<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="tab <?= $filter === 'blocked' ? 'active' : '' ?>">Blocked</a>
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>IP</th>
                <th>Owner</th>
                <th>Status</th>
                <th>State</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($servers as $server): ?>
                <tr>
                    <td><?= $server['id'] ?></td>
                    <td><strong><a href="/admin/servers/<?= $server['id'] ?>"><?= e($server['name']) ?></a></strong></td>
                    <td class="text-muted"><?= e($server['ip']) ?>:<?= $server['port'] ?></td>
                    <td><?= e($server['owner_name'] ?? '—') ?></td>
                    <td>
                        <?php if ($server['is_online'] ?? false): ?>
                            <span class="badge badge-green"><i class="fas fa-circle" style="font-size:8px;"></i> <?= $server['players_online'] ?? 0 ?></span>
                        <?php else: ?>
                            <span class="badge badge-red"><i class="fas fa-circle" style="font-size:8px;"></i> Off</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$server['is_active']): ?>
                            <span class="badge badge-red"><i class="fas fa-ban"></i> Blocked</span>
                        <?php elseif (!$server['is_approved']): ?>
                            <span class="badge badge-gold"><i class="fas fa-clock"></i> Pending</span>
                        <?php else: ?>
                            <span class="badge badge-green"><i class="fas fa-check"></i> Active</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="flex gap-1">
                            <a href="/server/<?= $server['id'] ?>" target="_blank" class="btn btn-sm btn-secondary" title="View public page">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <?php if ($server['is_active'] && !$server['is_approved']): ?>
                                <form method="POST" action="/admin/servers/<?= $server['id'] ?>/approve" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-primary">Approve</button>
                                </form>
                                <button class="btn btn-sm btn-danger"
                                        onclick="confirmAction('/admin/servers/<?= $server['id'] ?>/reject', 'Reject Server', 'Are you sure you want to reject <?= e($server['name']) ?>?')">
                                    Reject
                                </button>
                            <?php elseif ($server['is_active']): ?>
                                <button class="btn btn-sm btn-danger"
                                        onclick="confirmAction('/admin/servers/<?= $server['id'] ?>/reject', 'Block Server', 'Are you sure you want to block <?= e($server['name']) ?>?')">
                                    Block
                                </button>
                            <?php else: ?>
                                <form method="POST" action="/admin/servers/<?= $server['id'] ?>/unblock" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-primary">Unblock</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($servers)): ?>
                <tr><td colspan="7" class="text-center text-muted" style="padding:20px;">No servers found.</td></tr>
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
                <a href="/admin/servers?filter=<?= e($filter) ?>&search=<?= urlencode($search ?? '') ?>&page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
<?php endif; ?>
