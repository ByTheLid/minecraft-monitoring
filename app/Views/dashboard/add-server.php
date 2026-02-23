<?php $layout = 'main'; $currentPage = 'dashboard'; $pageTitle = 'Add Server'; ?>

<div class="container" style="max-width:600px;">
    <h1 class="page-title mb-2">Add Server</h1>

    <?php if ($error = flash('error')): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="/dashboard/add">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">Server Name *</label>
                <input type="text" id="name" name="name" class="form-control"
                       value="<?= e(old('name')) ?>" placeholder="My Awesome Server" required>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label for="ip">Server IP / Domain *</label>
                    <input type="text" id="ip" name="ip" class="form-control"
                           value="<?= e(old('ip')) ?>" placeholder="play.example.com" required>
                </div>
                <div class="form-group">
                    <label for="port">Port</label>
                    <input type="number" id="port" name="port" class="form-control"
                           value="<?= e(old('port', '25565')) ?>" min="1" max="65535">
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control"
                          placeholder="Tell players about your server..."><?= e(old('description')) ?></textarea>
            </div>

            <div class="form-group">
                <label for="website">Website URL</label>
                <input type="url" id="website" name="website" class="form-control"
                       value="<?= e(old('website')) ?>" placeholder="https://example.com">
            </div>

            <!-- RCON Configuration -->
            <div class="card mt-2" style="background:rgba(0,0,0,0.2); border:1px solid rgba(255,255,255,0.05);">
                <h3 style="font-size:14px; margin-bottom:15px; color:var(--accent-gold);">Reward Configuration (RCON)</h3>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label for="rcon_host">RCON Host (Optional)</label>
                        <input type="text" id="rcon_host" name="rcon_host" class="form-control"
                               value="<?= e(old('rcon_host')) ?>" placeholder="Leave empty to use Server IP">
                    </div>
                    <div class="form-group">
                        <label for="rcon_port">RCON Port</label>
                        <input type="number" id="rcon_port" name="rcon_port" class="form-control"
                               value="<?= e(old('rcon_port')) ?>" placeholder="25575">
                    </div>
                </div>

                <div class="form-group">
                    <label for="rcon_password">RCON Password</label>
                    <input type="password" id="rcon_password" name="rcon_password" class="form-control"
                           value="<?= e(old('rcon_password')) ?>">
                </div>

                <div class="form-group">
                    <label for="reward_command">Reward Command</label>
                    <input type="text" id="reward_command" name="reward_command" class="form-control"
                           value="<?= e(old('reward_command')) ?>" placeholder="give {player} diamond 1">
                    <small class="text-muted">Use <code>{player}</code> as a placeholder for the username.</small>
                </div>
            </div>

            <div class="form-group">
                <label for="tags">Tags (comma separated)</label>
                <input type="text" id="tags" name="tags" class="form-control"
                       value="<?= e(old('tags')) ?>" placeholder="survival, pvp, minigames">
            </div>

            <div class="flex gap-1">
                <button type="submit" class="btn btn-primary">Add Server</button>
                <a href="/dashboard" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<style>
.tagify {
    --tags-border-color: rgba(255,255,255,0.1) !important;
    --tags-hover-border-color: var(--accent-green) !important;
    --tags-focus-border-color: var(--accent-green) !important;
    --tag-bg: rgba(16, 185, 129, 0.1) !important;
    --tag-hover: rgba(16, 185, 129, 0.2) !important;
    --tag-text-color: var(--text-primary) !important;
    --placeholder-color: #888 !important;
    background: var(--bg-input) !important;
    border-radius: 6px !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    padding: 0;
}
.tagify__input {
    color: var(--text-primary) !important;
}
.tagify__dropdown {
    box-shadow: 0 4px 15px rgba(0,0,0,0.5) !important;
    border-radius: 6px !important;
    overflow: hidden;
    margin-top: 4px;
}
.tagify__dropdown__wrapper {
    background: var(--bg-card) !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    border-radius: 6px !important;
}
.tagify__dropdown__item {
    color: var(--text-primary) !important;
    padding: 10px 14px !important;
    border-bottom: 1px solid rgba(255,255,255,0.05) !important;
    transition: background 0.2s ease, color 0.2s ease !important;
}
.tagify__dropdown__item:hover, .tagify__dropdown__item--active {
    background: rgba(16, 185, 129, 0.1) !important;
    color: var(--accent-green) !important;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('tags');
    const minecraftTags = [
        "survival", "pvp", "pve", "vanilla", "semi-vanilla", "creative", "minigames", 
        "factions", "skyblock", "prison", "towny", "roleplay", "mcmmo", "smp", "anarchy", 
        "hardcore", "pixelmon", "spigot", "paper", "bukkit", "forge", "fabric", "quests", 
        "economy", "bedwars", "skywars", "parkour", "dropper", "deathrun", "hide and seek", 
        "hunger games", "kitpvp", "uhc", "lifesteal", "earth", "origins", "slimefun", "cars", 
        "guns", "vehicles", "zombies", "magic", "dungeons", "custom enchants", "free ranks", 
        "no grief", "whitelist", "cracked", "premium", "crossplay", "bedrock", "java", 
        "1.8", "1.12", "1.16", "1.19", "1.20", "1.21"
    ];

    new Tagify(input, {
        whitelist: minecraftTags,
        maxTags: 10,
        dropdown: {
            maxItems: 20,
            classname: "tags-look",
            enabled: 0,
            closeOnSelect: false
        },
        originalInputValueFormat: valuesArr => valuesArr.map(item => item.value).join(',')
    });
});
</script>
