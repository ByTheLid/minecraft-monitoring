/**
 * MC Monitor — Main Application JS
 */

// ==========================================
// Theme Toggle
// ==========================================
(function() {
    const toggle = document.getElementById('themeToggle');
    const html = document.documentElement;
    const saved = localStorage.getItem('theme') || 'dark';

    html.setAttribute('data-theme', saved);
    if (toggle) {
        toggle.innerHTML = saved === 'dark' ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
        toggle.addEventListener('click', () => {
            const current = html.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
            toggle.innerHTML = next === 'dark' ? '<i class="fas fa-moon"></i>' : '<i class="fas fa-sun"></i>';
        });
    }
})();

// ==========================================
// Mobile Nav Toggle
// ==========================================
(function() {
    const btn = document.getElementById('navToggle');
    const menu = document.getElementById('navMenu');
    if (btn && menu) {
        btn.addEventListener('click', () => {
            menu.classList.toggle('active');
        });
    }
})();

// ==========================================
// Copy IP
// ==========================================
document.addEventListener('click', function(e) {
    const el = e.target.closest('.copy-ip');
    if (!el) return;

    const ip = el.dataset.ip;
    navigator.clipboard.writeText(ip).then(() => {
        el.classList.add('copied');
        const origHtml = el.innerHTML;
        el.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => {
            el.innerHTML = origHtml;
            el.classList.remove('copied');
        }, 2000);
    });
});

// ==========================================
// Toast auto-hide
// ==========================================
(function() {
    const toasts = document.querySelectorAll('.toast');
    toasts.forEach(toast => {
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    });
})();

// ==========================================
// API Helper
// ==========================================
const api = {
    async get(url) {
        const res = await fetch(url, {
            headers: { 'Accept': 'application/json' }
        });
        return res.json();
    },

    async post(url, data = {}) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        return res.json();
    }
};

// ==========================================
// Vote
// ==========================================
async function voteServer(serverId, btn) {
    if (btn.classList.contains('voted') || btn.disabled) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    try {
        const res = await api.post(`/api/servers/${serverId}/vote`);

        if (res.success) {
            btn.innerHTML = `<i class="fas fa-caret-up"></i> ${res.data.vote_count}`;
            btn.classList.add('voted');
            showToast('Vote recorded!', 'success');

            // Update vote count on detail page stat card if present
            const statValues = document.querySelectorAll('.stat-card .stat-value');
            statValues.forEach(el => {
                if (el.closest('.stat-card')?.querySelector('.stat-label')?.textContent.includes('Votes')) {
                    el.innerHTML = `<i class="fas fa-thumbs-up"></i> ${res.data.vote_count}`;
                }
            });
        } else {
            btn.innerHTML = '<i class="fas fa-caret-up"></i> Vote';
            btn.disabled = false;
            showToast(res.error?.message || 'Vote failed', 'error');
        }
    } catch (err) {
        btn.innerHTML = '<i class="fas fa-caret-up"></i> Vote';
        btn.disabled = false;
        showToast('Network error', 'error');
    }
}

// ==========================================
// Toast notification
// ==========================================
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.innerHTML = `<div class="alert alert-${type}">${escapeHtml(message)}</div>`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ==========================================
// Utils
// ==========================================
function escapeHtml(text) {
    const el = document.createElement('span');
    el.textContent = text;
    return el.innerHTML;
}

function debounce(fn, delay = 300) {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return String(num);
}
