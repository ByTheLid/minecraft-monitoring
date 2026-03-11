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
        // Use standard fetch if api.get is not universally returning {success, data}
        const response = await fetch(`/api/server/${serverId}/analytics?range=${period}`);
        const text = await response.text();
        
        let res;
        try { res = JSON.parse(text); } catch(e) { res = { success: false }; }

        const apiData = res.data || res; // depending on ApiResponse wrapper
        
        if (!apiData || !apiData.labels || apiData.labels.length === 0) {
            renderEmptyChart();
            return;
        }

        renderChart(apiData.labels, apiData.datasets, period);
    } catch (err) {
        console.error('Chart load error:', err);
        renderEmptyChart();
    }
}

function renderChart(labels, datasets, period) {
    const ctx = document.getElementById('playersChart');
    if (!ctx) return;

    if (playersChart) {
        playersChart.destroy();
    }

    // Enhance the first dataset (players) with a beautiful gradient
    if (datasets && datasets[0]) {
        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

        datasets[0].backgroundColor = gradient;
        datasets[0].fill = true;
        datasets[0].tension = 0.4;
        datasets[0].borderWidth = 2;
        datasets[0].pointRadius = period === '24h' ? 0 : 4;
        datasets[0].pointHoverRadius = 6;
    }
    
    // Ensure the second dataset (uptime) is tied to a secondary Y axis
    if (datasets && datasets[1]) {
        datasets[1].yAxisID = 'y1';
        datasets[1].borderRadius = 4;
    }

    playersChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    display: true,
                    labels: { color: '#94a3b8', usePointStyle: true, boxWidth: 8 }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: '#f8fafc',
                    bodyColor: '#cbd5e1',
                    borderColor: 'rgba(148, 163, 184, 0.2)',
                    borderWidth: 1,
                    padding: 12,
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(148, 163, 184, 0.1)', drawBorder: false },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 },
                        maxTicksLimit: 12,
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    grid: { color: 'rgba(148, 163, 184, 0.1)', drawBorder: false },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 },
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    max: 100,
                    grid: { drawOnChartArea: false },
                    ticks: {
                        color: '#10b981',
                        font: { size: 11 },
                        callback: function(value) { return value + '%' }
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
