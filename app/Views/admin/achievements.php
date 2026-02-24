<?php $layout = 'admin'; $adminPage = 'achievements'; $pageTitle = 'Achievements'; ?>

<div class="flex-between mb-2">
    <h1 style="font-size:16px;">Manage Achievements</h1>
    <button class="btn btn-primary" onclick="openAddModal()">
        <i class="fas fa-plus"></i> Add Achievement
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID / Key</th>
                    <th>Preview</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($achievements as $ach): ?>
                    <tr>
                        <td>
                            <div class="text-sm">#<?= $ach['id'] ?></div>
                            <div class="text-xs text-muted"><?= e($ach['achievement_key']) ?></div>
                        </td>
                        <td>
                            <span class="achievement-badge" style="display:inline-flex; align-items:center; gap:8px; padding:6px 12px; border-radius:8px; background: color-mix(in srgb, <?= e($ach['color']) ?> 15%, transparent); color: <?= e($ach['color']) ?>;">
                                <i class="<?= e($ach['icon']) ?>"></i> <?= e($ach['name']) ?>
                            </span>
                        </td>
                        <td><strong><?= e($ach['name']) ?></strong></td>
                        <td style="max-width: 250px;">
                            <span class="text-sm text-muted"><?= e($ach['description']) ?></span>
                        </td>
                        <td>
                            <div class="flex gap-1" style="align-items:center;">
                                <button class="btn btn-sm btn-secondary" onclick='openEditModal(<?= json_encode($ach) ?>)' title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="/admin/achievements/delete/<?= $ach['id'] ?>" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure? Badges will be removed from users as well.')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($achievements)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding: 24px;">
                            <i class="fas fa-empty-set text-2xl mb-2"></i><br>
                            No achievements found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal-backdrop">
    <div class="modal">
        <button class="modal-close-btn" onclick="closeAddModal()" title="Close">
            <i class="fas fa-times"></i>
        </button>
        <h2 class="mb-2">Create Achievement</h2>
        <form action="/admin/achievements/create" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>System Key</label>
                <input type="text" name="achievement_key" class="form-control" placeholder="first_vote" required>
                <small class="text-muted text-xs">A-Z, 0-9, underscores.</small>
            </div>
            <div class="form-group mt-1">
                <label>Title</label>
                <input type="text" name="name" class="form-control" placeholder="First Blood" required>
            </div>
            <div class="form-group mt-1">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Given for the first vote." required></textarea>
            </div>
            <div class="grid-2 gap-2 mt-1">
                <div class="form-group">
                    <label>Icon Class</label>
                    <div class="icon-picker" data-target="#add_icon" data-current="fa-solid fa-star"></div>
                    <input type="hidden" name="icon" id="add_icon" value="fa-solid fa-star">
                </div>
                <div class="form-group">
                    <label>Highlight Color</label>
                    <input type="color" name="color" class="form-control" value="#3b82f6" style="height:38px; padding:2px;" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block mt-2">Create</button>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-backdrop">
    <div class="modal">
        <button class="modal-close-btn" onclick="closeEditModal()" title="Close">
            <i class="fas fa-times"></i>
        </button>
        <h2 class="mb-2">Edit Achievement</h2>
        <form id="editForm" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>System Key</label>
                <input type="text" name="achievement_key" id="edit_key" class="form-control" required>
            </div>
            <div class="form-group mt-1">
                <label>Title</label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div class="form-group mt-1">
                <label>Description</label>
                <textarea name="description" id="edit_desc" class="form-control" rows="2" required></textarea>
            </div>
            <div class="grid-2 gap-2 mt-1">
                <div class="form-group">
                    <label>Icon Class</label>
                    <div class="icon-picker" id="edit_icon_picker" data-target="#edit_icon" data-current="fa-solid fa-star"></div>
                    <input type="hidden" name="icon" id="edit_icon" required>
                </div>
                <div class="form-group">
                    <label>Highlight Color</label>
                    <input type="color" name="color" id="edit_color" class="form-control" style="height:38px; padding:2px;" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block mt-2">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').classList.add('active');
}
function closeAddModal() {
    document.getElementById('addModal').classList.remove('active');
}

function openEditModal(ach) {
    document.getElementById('editForm').action = '/admin/achievements/edit/' + ach.id;
    document.getElementById('edit_key').value = ach.achievement_key;
    document.getElementById('edit_name').value = ach.name;
    document.getElementById('edit_desc').value = ach.description;
    document.getElementById('edit_icon').value = ach.icon;

    // Update IconPicker component if available
    var pickerPreview = document.querySelector('#edit_icon_picker .icon-picker-preview i');
    if (pickerPreview) {
        pickerPreview.className = ach.icon;
        
        // Update active class in dropdown grid
        var gridItems = document.querySelectorAll('#edit_icon_picker .icon-picker-item');
        gridItems.forEach(el => el.classList.remove('active'));
        var activeItem = document.querySelector('#edit_icon_picker .icon-picker-item[data-icon="' + ach.icon + '"]');
        if (activeItem) activeItem.classList.add('active');
    }

    document.getElementById('edit_color').value = ach.color;
    document.getElementById('editModal').classList.add('active');
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

window.addEventListener('click', function(event) {
    if (event.target === document.getElementById('addModal')) closeAddModal();
    if (event.target === document.getElementById('editModal')) closeEditModal();
});

window.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddModal();
        closeEditModal();
    }
});
</script>
