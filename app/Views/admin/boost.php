<?php $layout = 'admin'; $adminPage = 'boost'; $pageTitle = 'Boost Packages'; ?>

<div class="flex-between mb-2">
    <h1 style="font-size:16px;">Boost Packages</h1>
</div>

<div class="flex flex-col gap-2">
    <!-- Existing Packages -->
    <div class="card">
        <h3 class="section-title mb-2">Existing Packages</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Color</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packages as $pkg): ?>
                        <tr>
                            <td><?= $pkg['id'] ?></td>
                            <td>
                                <strong><?= e($pkg['name']) ?></strong>
                                <?php if ($pkg['is_popular']): ?>
                                    <span class="badge badge-gold" style="font-size:10px;">POP</span>
                                <?php endif; ?>
                                <div class="text-muted" style="font-size:11px;">
                                    <?= $pkg['points'] ?> pts / <?= $pkg['duration_days'] ?> days
                                </div>
                            </td>
                            <td>$<?= number_format($pkg['price'], 2) ?></td>
                            <td>
                                <div style="width:20px; height:20px; background-color:<?= e($pkg['color']) ?>; border-radius:4px; border:1px solid #444;"></div>
                            </td>
                            <td>
                                <?php if ($pkg['is_active']): ?>
                                    <span class="text-green">Active</span>
                                <?php else: ?>
                                    <span class="text-red">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="flex gap-1">
                                    <!-- Edit -->
                                    <button onclick='openEditModal(<?= json_encode($pkg) ?>)' class="btn btn-sm btn-secondary" type="button" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <!-- Toggle Status -->
                                    <?php if ($pkg['is_active']): ?>
                                        <form method="POST" action="/admin/boost/deactivate/<?= $pkg['id'] ?>" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-warning" title="Deactivate"><i class="fas fa-pause"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="/admin/boost/activate/<?= $pkg['id'] ?>" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-green" title="Activate"><i class="fas fa-play"></i></button>
                                        </form>
                                    <?php endif; ?>

                                    <!-- Delete -->
                                    <button onclick="openDeleteModal(<?= $pkg['id'] ?>)" class="btn btn-sm btn-danger" type="button" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($packages)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No packages found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add New Package -->
    <div class="card mt-2">
        <h3 class="section-title mb-2">Add New Package</h3>
        <form method="POST" action="/admin/boost/create">
            <?= csrf_field() ?>
            
            <div class="grid-2 gap-2">
                <div class="form-group">
                    <label>Package Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Platinum Boost" required>
                </div>
                <div class="form-actions flex gap-1 items-end">
                     <!-- Spacing -->
                </div>

                <div class="form-group">
                    <label>Price ($)</label>
                    <input type="number" step="0.01" name="price" class="form-control" placeholder="9.99" required>
                </div>
                <div class="form-group">
                    <label>Points</label>
                    <input type="number" name="points" class="form-control" placeholder="500" required>
                </div>

                <div class="form-group">
                    <label>Duration (Days)</label>
                    <input type="number" name="duration_days" class="form-control" value="30" required>
                </div>
                <div class="form-group">
                    <label>Highlight Color</label>
                    <input type="color" name="color" class="form-control" value="#ffcc00" style="height:38px; padding:2px;">
                </div>
            </div>

            <div class="form-group mt-1">
                <label>Features <span class="text-muted">(JSON Array)</span></label>
                <input type="text" name="features" class="form-control" value='["Priority Support", "2x Votes"]' placeholder='["Feature 1", "Feature 2"]'>
            </div>

            <div class="form-group mt-1">
                 <label class="custom-checkbox">
                    <input type="checkbox" name="is_popular" value="1">
                    <span class="checkmark"></span>
                    Mark as Popular
                </label>
            </div>

            <button type="submit" class="btn btn-primary mt-2">Create Package</button>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-backdrop">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Package</h3>
            <span class="close" onclick="closeEditModal()" style="cursor:pointer;">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editForm" method="POST" action="">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="flex gap-1">
                    <div class="form-group" style="flex:1;">
                        <label>Price</label>
                        <input type="number" step="0.01" name="price" id="edit_price" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Points</label>
                        <input type="number" name="points" id="edit_points" class="form-control" required>
                    </div>
                </div>
                <div class="flex gap-1">
                    <div class="form-group" style="flex:1;">
                        <label>Days</label>
                        <input type="number" name="duration_days" id="edit_days" class="form-control" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Color</label>
                        <input type="color" name="color" id="edit_color" class="form-control" style="height:38px;">
                    </div>
                </div>
                 <div class="form-group">
                    <label>Features</label>
                    <input type="text" name="features" id="edit_features" class="form-control">
                </div>
                <div class="form-group">
                    <label>Active</label>
                    <select name="is_popular" id="edit_popular" class="form-control">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal-backdrop">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header">
            <h3>Delete Package</h3>
            <span class="close" onclick="closeDeleteModal()" style="cursor:pointer;">&times;</span>
        </div>
        <div class="modal-body">
            <p class="mb-2 text-red">Are you sure? This cannot be undone.</p>
            <form id="deleteForm" method="POST" action="">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Admin Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Password">
                </div>
                <button type="submit" class="btn btn-danger btn-block">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(pkg) {
    document.getElementById('editForm').action = '/admin/boost/edit/' + pkg.id;
    document.getElementById('edit_name').value = pkg.name;
    document.getElementById('edit_price').value = pkg.price;
    document.getElementById('edit_points').value = pkg.points;
    document.getElementById('edit_days').value = pkg.duration_days;
    document.getElementById('edit_color').value = pkg.color;
    document.getElementById('edit_features').value = pkg.features;
    document.getElementById('edit_popular').value = pkg.is_popular;
    document.getElementById('edit_popular').value = pkg.is_popular;
    document.getElementById('editModal').style.display = 'flex';
}
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function openDeleteModal(id) {
    document.getElementById('deleteForm').action = '/admin/boost/delete/' + id;
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modals when clicking outside
window.onclick = function(event) {
    var editModal = document.getElementById('editModal');
    var deleteModal = document.getElementById('deleteModal');
    if (event.target == editModal) {
        editModal.style.display = "none";
    }
    if (event.target == deleteModal) {
        deleteModal.style.display = "none";
    }
}
</script>
