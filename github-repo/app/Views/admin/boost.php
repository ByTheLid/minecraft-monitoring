<?php $layout = 'admin'; $adminPage = 'boost'; $pageTitle = 'Boost Packages'; ?>

<h1 class="page-title mb-2">Boost Packages</h1>

<?php if (!empty($packages)): ?>
    <?php foreach ($packages as $pkg): ?>
        <div class="card mb-2">
            <form method="POST" action="/admin/boost/edit/<?= $pkg['id'] ?>">
                <?= csrf_field() ?>
                <div class="flex-between mb-2">
                    <div class="flex gap-1" style="align-items:center;">
                        <span class="text-muted" style="font-size:12px;">#<?= $pkg['id'] ?></span>
                        <?= $pkg['is_active']
                            ? '<span class="badge badge-green">Active</span>'
                            : '<span class="badge badge-red">Inactive</span>' ?>
                    </div>
                    <div class="flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                        <?php if ($pkg['is_active']): ?>
                            <button type="button" class="btn btn-sm btn-danger"
                                    onclick="confirmAction('/admin/boost/delete/<?= $pkg['id'] ?>', 'Deactivate Package', 'Are you sure you want to deactivate <?= e($pkg['name']) ?>?')">
                                Deactivate
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="grid-4">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" value="<?= e($pkg['name']) ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Points</label>
                        <input type="number" name="points" value="<?= $pkg['points'] ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Price ($)</label>
                        <input type="number" step="0.01" name="price" value="<?= $pkg['price'] ?>" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Duration (days)</label>
                        <input type="number" name="duration_days" value="<?= $pkg['duration_days'] ?>" class="form-control" required>
                    </div>
                </div>
            </form>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card text-center mb-3" style="padding:30px;">
        <p class="text-muted">No packages yet.</p>
    </div>
<?php endif; ?>

<div class="card" style="max-width:600px;">
    <h3 class="section-title mb-2"><i class="fas fa-plus" style="color:var(--accent);"></i> Add New Package</h3>
    <form method="POST" action="/admin/boost/create">
        <?= csrf_field() ?>
        <div class="grid-4">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" placeholder="Silver Boost" required>
            </div>
            <div class="form-group">
                <label>Points</label>
                <input type="number" name="points" class="form-control" placeholder="100" required>
            </div>
            <div class="form-group">
                <label>Price ($)</label>
                <input type="number" step="0.01" name="price" class="form-control" placeholder="4.99" required>
            </div>
            <div class="form-group">
                <label>Duration (days)</label>
                <input type="number" name="duration_days" class="form-control" placeholder="30" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Package</button>
    </form>
</div>
