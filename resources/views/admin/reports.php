<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Reports - Admin Portal</title>
    <link href="/public/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>Reports Dashboard</h1>
        <p>View and generate reports related to birth certificate applications, user registrations, and system activity.</p>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        Application Reports
                    </div>
                    <div class="card-body">
                        <ul>
                            <li>Pending Applications</li>
                            <li>Approved Certificates</li>
                            <li>Rejected Applications</li>
                            <li>Average Processing Time</li>
                        </ul>
                        <button id="generateAppReport" class="btn btn-primary">Generate Report</button>
                        <div id="appReportResult" class="mt-3"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        User Reports
                    </div>
                    <div class="card-body">
                        <ul>
                            <li>New Registrations</li>
                            <li>Active Users</li>
                            <li>User Roles Distribution</li>
                            <li>Login Activity</li>
                        </ul>
                        <button id="generateUserReport" class="btn btn-success">Generate Report</button>
                        <div id="userReportResult" class="mt-3"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-info">
                    <div class="card-header bg-info text-white">
                        System Activity
                    </div>
                    <div class="card-body">
                        <ul>
                            <li>System Uptime</li>
                            <li>Error Logs</li>
                            <li>Performance Metrics</li>
                            <li>Audit Logs</li>
                        </ul>
                        <button id="generateSystemReport" class="btn btn-info">Generate Report</button>
                        <div id="systemReportResult" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <nav aria-label="Reports pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled"><a class="page-link" href="#" tabindex="-1">Previous</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul>
        </nav>
    </div>

<script>
$(document).ready(function() {
    $('#generateAppReport').click(function() {
        $.ajax({
            url: '/api/admin/reports/applications',
            method: 'GET',
            success: function(data) {
                $('#appReportResult').html('<pre>' + JSON.stringify(data, null, 2) + '</pre>');
            },
            error: function() {
                alert('Failed to generate application report.');
            }
        });
    });

    $('#generateUserReport').click(function() {
        $.ajax({
            url: '/api/admin/reports/users',
            method: 'GET',
            success: function(data) {
                $('#userReportResult').html('<pre>' + JSON.stringify(data, null, 2) + '</pre>');
            },
            error: function() {
                alert('Failed to generate user report.');
            }
        });
    });

    $('#generateSystemReport').click(function() {
        $.ajax({
            url: '/api/admin/reports/system',
            method: 'GET',
            success: function(data) {
                $('#systemReportResult').html('<pre>' + JSON.stringify(data, null, 2) + '</pre>');
            },
            error: function() {
                alert('Failed to generate system report.');
            }
        });
    });
});
</script>
</body>
</html>
