<?php $layout = 'admin'; $adminPage = 'seo'; $pageTitle = 'SEO Pages'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="admin-title"><i class="fas fa-search mr-2"></i> SEO Pages</h1>
    <div class="d-flex" style="gap: 10px;">
        <form action="/admin/seo/recalculate" method="POST" style="display: inline;">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-secondary"><i class="fas fa-sync mr-1"></i> Recalculate</button>
        </form>
        <button onclick="document.getElementById('createModal').classList.add('active')" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Add Page
        </button>
    </div>
</div>

<?php if (empty($pages)): ?>
    <div class="card text-center p-5">
        <p class="text-muted mb-0">No SEO pages created yet.</p>
    </div>
<?php else: ?>
    <div class="card p-0 overflow-hidden" style="border-radius: 12px;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>URL Path</th>
                    <th>Category</th>
                    <th>H1</th>
                    <th style="text-align: center;">Servers</th>
                    <th style="text-align: center;">Indexed</th>
                    <th style="width: 120px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $page): ?>
                    <tr>
                        <td><a href="<?= $page['url_path'] ?>" style="color: var(--primary-color); font-weight: 600;"><?= e($page['url_path']) ?></a></td>
                        <td><span class="tag"><?= e($page['category']) ?></span></td>
                        <td><?= e(mb_substr($page['h1'], 0, 50)) ?></td>
                        <td style="text-align: center; font-weight: 700;"><?= $page['server_count'] ?></td>
                        <td style="text-align: center;">
                            <?php if ($page['is_indexed']): ?>
                                <span style="color: #10b981;"><i class="fas fa-check-circle"></i> Yes</span>
                            <?php else: ?>
                                <span style="color: #ef4444;"><i class="fas fa-times-circle"></i> No</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form action="/admin/seo/delete/<?= $page['id'] ?>" method="POST" style="display: inline;" onsubmit="return confirm('Delete?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Create Modal -->
<div class="modal-backdrop" id="createModal">
    <div class="modal" style="max-width: 560px;">
        <div class="modal-header">
            <h3>Create SEO Page</h3>
            <button class="modal-close" onclick="document.getElementById('createModal').classList.remove('active')">&times;</button>
        </div>
        <form action="/admin/seo/store" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category" class="form-control" required>
                    <option value="version">Version</option>
                    <option value="tag">Tag</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Value</label>
                <input type="text" name="value" class="form-control" placeholder="1.20.4 or survival" required>
            </div>
            <div class="form-group">
                <label class="form-label">H1</label>
                <input type="text" name="h1" class="form-control" placeholder="Best Minecraft 1.20 Servers" required>
            </div>
            <div class="form-group">
                <label class="form-label">Meta Title (max 160)</label>
                <input type="text" name="meta_title" class="form-control" maxlength="160" required>
            </div>
            <div class="form-group">
                <label class="form-label">Meta Description (max 320)</label>
                <textarea name="meta_description" class="form-control" rows="2" maxlength="320" required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">SEO Text Template</label>
                <textarea name="seo_text_template" class="form-control" rows="4" placeholder="Use {servers_count}, {version}, {total_online}"></textarea>
                <small class="text-muted">Variables: {servers_count}, {version}, {category}, {total_online}</small>
            </div>
            <button type="submit" class="btn btn-primary btn-block mt-3">Create</button>
        </form>
    </div>
</div>
