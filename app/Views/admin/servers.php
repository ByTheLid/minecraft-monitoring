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
                    <td>
                        <strong><?= e($server['name']) ?></strong>
                        <?php if (!empty($server['active_boosts'])): ?>
                            <div class="text-gold" style="font-size:11px; margin-top:2px;">
                                <i class="fas fa-bolt"></i> <?= e($server['active_boosts']) ?>
                            </div>
                        <?php endif; ?>
                    </td>
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
                        <div class="flex gap-1" style="align-items:center;">
                            <!-- Votes -->
                            <span class="badge badge-secondary" style="font-size:11px; margin-right:5px;" title="Votes">
                                <i class="fas fa-caret-up"></i> <?= (int)($server['vote_count'] ?? 0) ?>
                            </span>

                            <!-- Analytics -->
                            <a href="/admin/analytics?search=<?= urlencode($server['name']) ?>" class="btn btn-sm btn-secondary" title="Analytics">
                                <i class="fas fa-chart-line"></i>
                            </a>

                            <!-- Edit (Admin can edit any server) -->
                            <a href="/dashboard/edit/<?= $server['id'] ?>" class="btn btn-sm btn-secondary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>

                            <?php if (!$server['is_active']): ?>
                                <!-- Blocked -->
                                <form method="POST" action="/admin/servers/<?= $server['id'] ?>/unblock" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-green" title="Unblock"><i class="fas fa-undo"></i></button>
                                </form>
                            <?php elseif (!$server['is_approved']): ?>
                                <!-- Pending -->
                                <form method="POST" action="/admin/servers/<?= $server['id'] ?>/approve" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-primary" title="Approve"><i class="fas fa-check"></i></button>
                                </form>
                                <form method="POST" action="/admin/servers/<?= $server['id'] ?>/reject" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-danger" title="Reject"><i class="fas fa-times"></i></button>
                                </form>
                            <?php else: ?>
                                <!-- Active -->
                                <form method="POST" action="/admin/servers/<?= $server['id'] ?>/reject" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-danger" title="Block"><i class="fas fa-ban"></i></button>
                                </form>
                            <?php endif; ?>
                            <button onclick="openTools(<?= $server['id'] ?>, '<?= e($server['name']) ?>')" class="btn btn-sm btn-info" title="Tools">
                                <i class="fas fa-wrench"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($servers)): ?>
                <tr><td colspan="7" class="text-center text-muted" style="padding:20px;">No servers found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </table>
</div>

<!-- Tools Modal -->
<div id="toolsModal" class="modal-backdrop">
    <div class="modal">
        <button class="modal-close-btn" onclick="closeToolsModal()" title="Close">
            <i class="fas fa-times"></i>
        </button>
        <h2 class="mb-2">Server Tools</h2>
        <h4 id="toolsServerName" class="mb-2" style="color:var(--text-secondary)">Server Name</h4>
        
        <form id="voteForm" method="POST" action="">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Add Manual Votes</label>
                <div class="flex gap-1">
                    <input type="number" name="count" class="form-control" value="1" min="1" max="100">
                    <button class="btn btn-primary">Add Votes</button>
                </div>
            </div>
        </form>

        <hr class="my-2" style="border:0; border-top:1px solid rgba(255,255,255,0.1);">

        <form id="boostForm" method="POST" action="">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Add Manual Boost</label>
                
                <select name="package_id" id="boostPackageSelect" class="form-control mb-1" onchange="toggleCustomBoostFields()">
                    <option value="custom">-- Custom Boost --</option>
                    <?php foreach ($packages as $pkg): ?>
                        <option value="<?= $pkg['id'] ?>">
                            <?= e($pkg['name']) ?> (<?= $pkg['points'] ?> pts / <?= $pkg['duration_days'] ?> days)
                        </option>
                    <?php endforeach; ?>
                </select>

                <div id="customBoostFields" class="flex gap-1 mb-1">
                    <input type="number" name="days" class="form-control" placeholder="Days" value="7">
                    <input type="number" name="points" class="form-control" placeholder="Points" value="0">
                </div>
                <button class="btn btn-gold btn-block">Give Boost</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleCustomBoostFields() {
    var select = document.getElementById('boostPackageSelect');
    var customFields = document.getElementById('customBoostFields');
    if (select.value === 'custom') {
        customFields.style.display = 'flex';
    } else {
        customFields.style.display = 'none';
    }
}

function openTools(id, name) {
    document.getElementById('toolsServerName').innerText = name;
    document.getElementById('voteForm').action = '/admin/servers/' + id + '/vote';
    document.getElementById('boostForm').action = '/admin/servers/' + id + '/boost';
    
    // Reset boost selection
    document.getElementById('boostPackageSelect').value = 'custom';
    toggleCustomBoostFields();

    document.getElementById('toolsModal').style.display = 'flex';
}
function closeToolsModal() {
    document.getElementById('toolsModal').style.display = 'none';
}

// Close modals when clicking outside or pressing Escape
window.addEventListener('click', function(event) {
    var toolsModal = document.getElementById('toolsModal');
    if (event.target === toolsModal) {
        closeToolsModal();
    }
});

window.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeToolsModal();
    }
});
</script>
