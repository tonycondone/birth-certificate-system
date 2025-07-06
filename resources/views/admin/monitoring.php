<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>System Monitoring - Admin Portal</title>
    <link href="/public/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>System Monitoring</h1>
        <p>Monitor system health, performance, and logs to ensure smooth operation of the birth certificate system.</p>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        System Uptime & Performance
                    </div>
                    <div class="card-body">
                        <p>Uptime: <strong id="uptime">Loading...</strong></p>
                        <p>Average Response Time: <strong id="responseTime">Loading...</strong></p>
                        <p>Active Sessions: <strong id="activeSessions">Loading...</strong></p>
                        <p>Database Connections: <strong id="dbConnections">Loading...</strong></p>
                        <button id="refreshStats" class="btn btn-success">Refresh Data</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        Recent Error Logs
                    </div>
                    <div class="card-body" style="max-height: 300px; overflow-y: auto; font-family: monospace; background-color: #f8d7da; color: #721c24;">
                        <pre id="errorLogs">Loading...</pre>
                        <button id="clearLogs" class="btn btn-danger mt-2">Clear Logs</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <h2>Database Statistics</h2>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Table Name</th>
                        <th>Rows</th>
                        <th>Size</th>
                        <th>Index Size</th>
                    </tr>
                </thead>
                <tbody id="dbStatsBody">
                    <tr><td colspan="4">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

<script>
$(document).ready(function() {
    function loadStats() {
        $.ajax({
            url: '/api/admin/monitoring/stats',
            method: 'GET',
            success: function(data) {
                $('#uptime').text(data.uptime);
                $('#responseTime').text(data.response_time);
                $('#activeSessions').text(data.active_sessions);
                $('#dbConnections').text(data.db_connections);
            },
            error: function() {
                alert('Failed to load system stats.');
            }
        });
    }

    function loadErrorLogs() {
        $.ajax({
            url: '/api/admin/monitoring/error-logs',
            method: 'GET',
            success: function(data) {
                $('#errorLogs').text(data.logs.join("\n"));
            },
            error: function() {
                alert('Failed to load error logs.');
            }
        });
    }

    function loadDbStats() {
        $.ajax({
            url: '/api/admin/monitoring/db-stats',
            method: 'GET',
            success: function(data) {
                const tbody = $('#dbStatsBody');
                tbody.empty();
                data.tables.forEach(table => {
                    tbody.append(
                        `<tr>
                            <td>${table.name}</td>
                            <td>${table.rows}</td>
                            <td>${table.size}</td>
                            <td>${table.index_size}</td>
                        </tr>`
                    );
                });
            },
            error: function() {
                alert('Failed to load database statistics.');
            }
        });
    }

    $('#refreshStats').click(function() {
        loadStats();
        loadErrorLogs();
        loadDbStats();
    });

    $('#clearLogs').click(function() {
        $.ajax({
            url: '/api/admin/monitoring/clear-logs',
            method: 'POST',
            success: function() {
                $('#errorLogs').text('');
            },
            error: function() {
                alert('Failed to clear logs.');
            }
        });
    });

    // Initial load
    loadStats();
    loadErrorLogs();
    loadDbStats();
});
</script>
</body>
</html>
