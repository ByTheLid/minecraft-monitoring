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

    // Design Version Toggle (Modern vs Pixel)
    const designToggle = document.getElementById('designToggle');
    if (designToggle) {
        designToggle.addEventListener('click', async () => {
            designToggle.disabled = true;
            // Determine current from icon
            const isPixel = designToggle.querySelector('i').classList.contains('fa-gamepad');
            const nextDesign = isPixel ? 'modern' : 'pixel';
            
            try {
                const res = await api.post('/design/toggle', { design: nextDesign });
                if (res.success) {
                    window.location.reload();
                } else {
                    showToast('Failed to switch design', 'error');
                    designToggle.disabled = false;
                }
            } catch (err) {
                showToast('Network error', 'error');
                designToggle.disabled = false;
            }
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
        const orig = el.textContent;
        el.textContent = '✓ Copied!';
        setTimeout(() => {
            el.textContent = orig;
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
// ==========================================
// Vote Modal Logic
// ==========================================
let currentVoteServerId = null;
let currentVoteBtn = null;

const voteModal = {
    backdrop: document.getElementById('voteModalBackdrop'),
    input: document.getElementById('voteUsername'),
    card: document.querySelector('#voteModalBackdrop .modal'),
    closeBtn: document.getElementById('closeVoteModal'),
    confirmBtn: document.getElementById('confirmVoteBtn'),

    init() {
        if (!this.backdrop) return;

        // Close events
        this.closeBtn?.addEventListener('click', () => this.close());
        this.backdrop.addEventListener('click', (e) => {
            if (e.target === this.backdrop) this.close();
        });

        // Confirm events
        this.confirmBtn?.addEventListener('click', () => this.submit());
        this.input?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') this.submit();
        });
    },

    open(serverId, btn) {
        currentVoteServerId = serverId;
        currentVoteBtn = btn;
        this.input.value = localStorage.getItem('mc_username') || '';
        this.backdrop.classList.add('active');
        setTimeout(() => this.input.focus(), 100);
    },

    close() {
        this.backdrop.classList.remove('active');
        currentVoteServerId = null;
        currentVoteBtn = null;
    },

    async submit() {
        const username = this.input.value.trim();
        if (!username) {
            showToast('Please enter a username', 'error');
            return;
        }
        
        // Save username for next time
        localStorage.setItem('mc_username', username);

        const serverId = currentVoteServerId;
        const btn = currentVoteBtn;

        this.close();

        if (btn) {
            btn.disabled = true;
            btn.dataset.originalText = btn.textContent;
            btn.textContent = '...';
        }

        try {
            const res = await api.post(`/api/servers/${serverId}/vote`, { username: username });

            if (res.success) {
                if (btn) {
                    // Fix: Use innerHTML to preserve icon
                    btn.innerHTML = `<i class="fas fa-caret-up"></i> ${res.data.vote_count}`;
                    btn.classList.add('voted');
                }
                
                // Reward notification logic
                let msg = 'Vote recorded!';
                let type = 'success';
                
                if (res.data.reward_status === 'sent') {
                    msg += ' Reward sent to server.';
                } else if (res.data.reward_status === 'not_configured') {
                    msg += ' (No reward configured by owner)';
                    type = 'info';
                } else if (res.data.reward_status === 'failed') {
                    msg += ' but Reward delivery failed.';
                    type = 'error'; // Partial success
                }

                showToast(msg, type);
            } else {
                if (btn) {
                    btn.innerHTML = btn.dataset.originalText || '<i class="fas fa-caret-up"></i> Vote';
                    btn.disabled = false;
                }
                showToast(res.error?.message || 'Vote failed', 'error');
            }
        } catch (err) {
            if (btn) {
                btn.innerHTML = btn.dataset.originalText || '<i class="fas fa-caret-up"></i> Vote';
                btn.disabled = false;
            }
            showToast('Network error', 'error');
        }
    }
};

// Initialize modal
document.addEventListener('DOMContentLoaded', () => {
    voteModal.init();
});

// Trigger function called by buttons
function voteServer(serverId, btn) {
    if (btn.classList.contains('voted') || btn.disabled) return;
    voteModal.open(serverId, btn);
}

// ==========================================
// Toast notification
// ==========================================
function showToast(message, type = 'info') {
    let container = document.getElementById('toastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    let icon = 'fa-info-circle';
    if(type === 'success') icon = 'fa-check-circle';
    if(type === 'error') icon = 'fa-exclamation-circle';

    toast.innerHTML = `<i class="fas ${icon}"></i><span>${escapeHtml(message)}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Cleanup server-rendered toasts after animation
document.addEventListener('DOMContentLoaded', () => {
    const serverToasts = document.querySelectorAll('.toast');
    serverToasts.forEach(toast => {
        setTimeout(() => {
            toast.style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    });
});

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
