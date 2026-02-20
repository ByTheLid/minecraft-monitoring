/**
 * Admin Dashboard — Charts & Activity Feed
 */
document.addEventListener('DOMContentLoaded', async function() {
    try {
        var res = await api.get('/api/admin/dashboard-stats');
        if (!res.success) return;

        var data = res.data;

        renderAdminChart('registrationsChart', data.registrations, 'Registrations', '#3b82f6', 'rgba(59,130,246,0.15)');
        renderAdminChart('votesChart', data.votes, 'Votes', '#22c55e', 'rgba(34,197,94,0.15)');
        renderActivityFeed(data.activity);
    } catch (err) {
        console.error('Admin dashboard load error:', err);
    }
});

function renderAdminChart(canvasId, dataset, label, borderColor, bgColor) {
    var ctx = document.getElementById(canvasId);
    if (!ctx) return;

    var days = [];
    var counts = [];
    var dataMap = {};
    (dataset || []).forEach(function(d) { dataMap[d.date] = parseInt(d.count); });

    for (var i = 6; i >= 0; i--) {
        var date = new Date();
        date.setDate(date.getDate() - i);
        var key = date.toISOString().split('T')[0];
        days.push(date.toLocaleDateString([], { month: 'short', day: 'numeric' }));
        counts.push(dataMap[key] || 0);
    }

    var context2d = ctx.getContext('2d');
    var gradient = context2d.createLinearGradient(0, 0, 0, 220);
    gradient.addColorStop(0, bgColor);
    gradient.addColorStop(1, 'transparent');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: days,
            datasets: [{
                label: label,
                data: counts,
                borderColor: borderColor,
                backgroundColor: gradient,
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: borderColor,
                pointBorderColor: '#1a2235',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: { color: '#94a3b8', font: { size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(255,255,255,0.04)' },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 },
                        stepSize: 1,
                        callback: function(v) { return Number.isInteger(v) ? v : ''; }
                    }
                }
            }
        }
    });
}

function renderActivityFeed(activity) {
    var feed = document.getElementById('activityFeed');
    if (!feed) return;

    if (!activity || !activity.length) {
        feed.innerHTML = '<li class="text-muted">No recent activity.</li>';
        return;
    }

    var iconMap = {
        registration: { icon: 'fa-user-plus', color: 'blue' },
        server_added: { icon: 'fa-server', color: 'green' },
        vote: { icon: 'fa-thumbs-up', color: 'purple' }
    };
    var labelMap = {
        registration: 'New user',
        server_added: 'Server added',
        vote: 'Vote for'
    };

    feed.innerHTML = activity.map(function(item) {
        var cfg = iconMap[item.type] || { icon: 'fa-circle', color: 'muted' };
        var prefix = labelMap[item.type] || item.type;
        return '<li>' +
            '<div class="activity-icon admin-stat-icon ' + cfg.color + '">' +
                '<i class="fas ' + cfg.icon + '"></i>' +
            '</div>' +
            '<span><strong>' + prefix + ':</strong> ' + escapeHtml(item.label) + '</span>' +
            '<span class="activity-time">' + adminTimeAgo(item.time) + '</span>' +
        '</li>';
    }).join('');
}

function adminTimeAgo(datetime) {
    if (!datetime) return '';
    var diff = Math.floor((Date.now() - new Date(datetime.replace(' ', 'T')).getTime()) / 1000);
    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return Math.floor(diff / 86400) + 'd ago';
}
