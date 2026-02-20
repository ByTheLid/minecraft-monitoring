/**
 * Chart initialization for server detail page
 */

let playersChart = null;

async function loadChart(serverId, period = '24h', tabEl = null) {
    // Update active tab
    if (tabEl) {
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        tabEl.classList.add('active');
    }

    try {
        const res = await api.get(`/api/servers/${serverId}/stats?period=${period}`);

        if (!res.success || !res.data.length) {
            renderEmptyChart();
            return;
        }

        const data = res.data;
        const labels = data.map(d => {
            const date = new Date(d.checked_at || d.hour);
            if (period === '24h') {
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
            return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
        });

        const players = data.map(d => d.players_online ?? d.avg_players ?? 0);

        renderChart(labels, players, period);
    } catch (err) {
        console.error('Chart load error:', err);
        renderEmptyChart();
    }
}

function renderChart(labels, players, period) {
    const ctx = document.getElementById('playersChart');
    if (!ctx) return;

    if (playersChart) {
        playersChart.destroy();
    }

    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(0, 255, 136, 0.3)');
    gradient.addColorStop(1, 'rgba(0, 255, 136, 0.0)');

    playersChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Players Online',
                data: players,
                borderColor: '#00ff88',
                backgroundColor: gradient,
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: period === '24h' ? 0 : 3,
                pointHoverRadius: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f3460',
                    titleColor: '#e0e0e0',
                    bodyColor: '#00ff88',
                    borderColor: '#00ff88',
                    borderWidth: 1,
                    padding: 10,
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.03)' },
                    ticks: {
                        color: '#a0a0a0',
                        font: { size: 10 },
                        maxTicksLimit: 12,
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.03)' },
                    ticks: {
                        color: '#a0a0a0',
                        font: { size: 10 },
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

function renderEmptyChart() {
    const ctx = document.getElementById('playersChart');
    if (!ctx) return;

    if (playersChart) {
        playersChart.destroy();
    }

    playersChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['No data'],
            datasets: [{
                label: 'Players',
                data: [0],
                borderColor: '#444',
                borderWidth: 1,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
}

// Auto-load on page ready
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('playersChart');
    if (canvas) {
        const serverIdBtn = document.querySelector('[data-server-id]');
        if (serverIdBtn) {
            loadChart(serverIdBtn.dataset.serverId, '24h');
        }
    }
});
