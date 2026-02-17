<?php $layout = 'admin'; $adminPage = 'servers'; $pageTitle = 'Servers'; ?>

<div class="flex-between mb-2">
    <h1 style="font-size:16px;">Manage Servers</h1>
</div>

<div class="tabs mb-2">
    <a href="/admin/servers?filter=all" class="tab <?= $filter === 'all' ? 'active' : '' ?>">All</a>
    <a href="/admin/servers?filter=pending" class="tab <?= $filter === 'pending' ? 'active' : '' ?>">Pending</a>
    <a href="/admin/servers?filter=approved" class="tab <?= $filter === 'approved' ? 'active' : '' ?>">Approved</a>
    <a href="/admin/servers?filter=blocked" class="tab <?= $filter === 'blocked' ? 'active' : '' ?>">Blocked</a>
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
                    <td><strong><?= e($server['name']) ?></strong></td>
                    <td class="text-muted"><?= e($server['ip']) ?>:<?= $server['port'] ?></td>
                    <td><?= e($server['owner_name'] ?? '—') ?></td>
                    <td>
                        <?php if ($server['is_online'] ?? false): ?>
                            <span class="status-badge status-online"><span class="status-dot"></span> <?= $server['players_online'] ?? 0 ?></span>
                        <?php else: ?>
                            <span class="status-badge status-offline"><span class="status-dot"></span> Off</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$server['is_active']): ?>
                            <span class="text-red">Blocked</span>
                        <?php elseif (!$server['is_approved']): ?>
                            <span class="text-gold">Pending</span>
                        <?php else: ?>
                            <span class="text-green">Active</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($server['is_active'] && !$server['is_approved']): ?>
                            <form method="POST" action="/admin/servers/<?= $server['id'] ?>/approve" style="display:inline;">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-primary">Approve</button>
                            </form>
                            <form method="POST" action="/admin/servers/<?= $server['id'] ?>/reject" style="display:inline;">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-danger">Reject</button>
                            </form>
                        <?php elseif ($server['is_active']): ?>
                            <form method="POST" action="/admin/servers/<?= $server['id'] ?>/reject" style="display:inline;">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-danger">Block</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($servers)): ?>
                <tr><td colspan="7" class="text-center text-muted" style="padding:20px;">No servers found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
