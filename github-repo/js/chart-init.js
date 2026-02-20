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

    const container = document.querySelector('.chart-container');
    if (container) {
        container.classList.add('loading');
    }

    try {
        const res = await api.get(`/api/servers/${serverId}/stats?period=${period}`);

        if (!res.success || !res.data || !res.data.length) {
            renderEmptyChart('No data available for this period');
            return;
        }

        const data = res.data;
        const labels = data.map(d => {
            const raw = d.checked_at || d.hour;
            if (!raw) return '';
            const date = new Date(raw.replace(' ', 'T'));
            if (isNaN(date.getTime())) return raw;
            if (period === '24h') {
                return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }
            return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
        });

        const players = data.map(d => {
            const val = d.players_online ?? d.avg_players ?? 0;
            return Number(val) || 0;
        });

        renderChart(labels, players, period);
    } catch (err) {
        console.error('Chart load error:', err);
        renderEmptyChart('Failed to load chart data');
    } finally {
        if (container) {
            container.classList.remove('loading');
        }
    }
}

function renderChart(labels, players, period) {
    const ctx = document.getElementById('playersChart');
    if (!ctx) return;

    if (playersChart) {
        playersChart.destroy();
    }

    const context2d = ctx.getContext('2d');
    const gradient = context2d.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(0, 255, 136, 0.3)');
    gradient.addColorStop(1, 'rgba(0, 255, 136, 0.0)');

    const showPoints = labels.length <= 48;

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
                pointRadius: showPoints ? 3 : 0,
                pointHoverRadius: 6,
                pointBackgroundColor: '#00ff88',
                pointBorderColor: '#0a0e27',
                pointBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 600,
                easing: 'easeOutQuart'
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 52, 96, 0.95)',
                    titleColor: '#e0e0e0',
                    bodyColor: '#00ff88',
                    borderColor: '#00ff88',
                    borderWidth: 1,
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        title: function(items) {
                            return items[0]?.label || '';
                        },
                        label: function(item) {
                            return 'Players: ' + formatNumber(item.raw);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: {
                        color: '#a0a0a0',
                        font: { size: 10 },
                        maxTicksLimit: 12,
                        maxRotation: 0,
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: {
                        color: '#a0a0a0',
                        font: { size: 10 },
                        callback: function(val) {
                            return formatNumber(val);
                        }
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

function renderEmptyChart(message) {
    const ctx = document.getElementById('playersChart');
    if (!ctx) return;

    if (playersChart) {
        playersChart.destroy();
    }

    message = message || 'No data available';

    playersChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [''],
            datasets: [{
                label: 'Players',
                data: [0],
                borderColor: 'rgba(255,255,255,0.1)',
                borderWidth: 1,
                pointRadius: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: message,
                    color: '#666',
                    font: { size: 14, weight: 'normal' }
                }
            },
            scales: {
                x: { display: false },
                y: { display: false }
            }
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
