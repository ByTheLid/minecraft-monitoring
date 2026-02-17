<?php $layout = 'admin'; $adminPage = 'boost'; $pageTitle = 'Boost Packages'; ?>

<div class="flex-between mb-2">
    <h1 style="font-size:16px;">Boost Packages</h1>
</div>

<!-- Existing packages -->
<div class="table-responsive mb-3">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Points</th>
                <th>Price</th>
                <th>Duration</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($packages as $pkg): ?>
                <tr>
                    <td><?= $pkg['id'] ?></td>
                    <td>
                        <form method="POST" action="/admin/boost/edit/<?= $pkg['id'] ?>" class="flex gap-1" style="align-items:center;">
                            <?= csrf_field() ?>
                            <input type="text" name="name" value="<?= e($pkg['name']) ?>" class="form-control" style="width:120px;">
                            <input type="number" name="points" value="<?= $pkg['points'] ?>" class="form-control" style="width:80px;">
                            <input type="number" step="0.01" name="price" value="<?= $pkg['price'] ?>" class="form-control" style="width:80px;">
                            <input type="number" name="duration_days" value="<?= $pkg['duration_days'] ?>" class="form-control" style="width:80px;">
                            <button type="submit" class="btn btn-sm btn-secondary">Save</button>
                        </form>
                    </td>
                    <td></td><td></td><td></td>
                    <td><?= $pkg['is_active'] ? '<span class="text-green">Active</span>' : '<span class="text-red">Inactive</span>' ?></td>
                    <td>
                        <form method="POST" action="/admin/boost/delete/<?= $pkg['id'] ?>" style="display:inline;">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-danger">Deactivate</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($packages)): ?>
                <tr><td colspan="7" class="text-center text-muted" style="padding:20px;">No packages yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add new -->
<div class="card" style="max-width:500px;">
    <h3 class="mb-2" style="font-size:13px;">Add New Package</h3>
    <form method="POST" action="/admin/boost/create">
        <?= csrf_field() ?>
        <div class="grid-2">
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
