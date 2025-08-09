<?php
// resources/views/registrar/batch-process-diagnostic.php
$pageTitle = $pageTitle ?? 'Batch Process Diagnostics';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <style>
        .diagnostic-header {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .diagnostic-card {
            margin-bottom: 20px;
        }
        .status-success {
            color: #198754;
        }
        .status-error {
            color: #dc3545;
        }
        .code-block {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <!-- Diagnostic Header -->
        <div class="diagnostic-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="mb-1">Batch Process Diagnostics</h1>
                    <p class="text-muted mb-0">
                        <i class="fa fa-wrench me-1"></i>
                        System diagnostic tool to help identify database issues
                    </p>
                </div>
                <div>
                    <a href="/registrar/batch-process" class="btn btn-outline-primary">
                        <i class="fa fa-arrow-left me-1"></i> Back to Batch Process
                    </a>
                </div>
            </div>
        </div>

        <!-- Database Connection Status -->
        <div class="card diagnostic-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Database Connection Status</h5>
                <?php if ($diagnostic['database_connection']): ?>
                    <span class="badge bg-success">Connected</span>
                <?php else: ?>
                    <span class="badge bg-danger">Not Connected</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if ($diagnostic['database_connection']): ?>
                    <p class="status-success"><i class="fa fa-check-circle me-1"></i> Database connection is active and working correctly.</p>
                    <?php if (isset($diagnostic['test_query']) && $diagnostic['test_query']): ?>
                        <p class="status-success"><i class="fa fa-check-circle me-1"></i> Test query executed successfully.</p>
                    <?php else: ?>
                        <p class="status-error"><i class="fa fa-times-circle me-1"></i> Test query failed. Check database permissions.</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="status-error"><i class="fa fa-times-circle me-1"></i> Database connection failed. Please check your configuration.</p>
                    <?php if (isset($diagnostic['error'])): ?>
                        <div class="alert alert-danger">
                            <strong>Error:</strong> <?= htmlspecialchars($diagnostic['error']) ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Table Status -->
        <div class="card diagnostic-card">
            <div class="card-header">
                <h5 class="mb-0">Required Tables Status</h5>
            </div>
            <div class="card-body">
                <?php if (isset($diagnostic['tables']) && !empty($diagnostic['tables'])): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Table Name</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($diagnostic['tables'] as $table => $exists): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($table) ?></code></td>
                                        <td>
                                            <?php if ($exists): ?>
                                                <span class="status-success"><i class="fa fa-check-circle me-1"></i> Exists</span>
                                            <?php else: ?>
                                                <span class="status-error"><i class="fa fa-times-circle me-1"></i> Missing</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!$exists): ?>
                                                <?php if ($table === 'certificates'): ?>
                                                    <button class="btn btn-sm btn-outline-primary create-table-btn" data-table="certificates">
                                                        Create Table
                                                    </button>
                                                <?php elseif ($table === 'activity_log'): ?>
                                                    <button class="btn btn-sm btn-outline-primary create-table-btn" data-table="activity_log">
                                                        Create Table
                                                    </button>
                                                <?php elseif ($table === 'notifications'): ?>
                                                    <button class="btn btn-sm btn-outline-primary create-table-btn" data-table="notifications">
                                                        Create Table
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">Contact administrator</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-secondary view-schema-btn" data-table="<?= $table ?>">
                                                    View Schema
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No table information available.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Test Applications -->
        <div class="card diagnostic-card">
            <div class="card-header">
                <h5 class="mb-0">Test Applications</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($applications)): ?>
                    <p class="mb-3">The following applications can be used for testing the approval process:</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Child Name</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($app['id'] ?? '') ?></td>
                                        <td><?= htmlspecialchars(($app['child_first_name'] ?? '') . ' ' . ($app['child_last_name'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars($app['status'] ?? '') ?></td>
                                        <td><?= isset($app['submitted_at']) ? date('Y-m-d H:i', strtotime($app['submitted_at'])) : '' ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary test-approve-btn" data-id="<?= $app['id'] ?? '' ?>">
                                                Test Approve
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle me-1"></i> No test applications available.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Schema Information -->
        <?php foreach (($diagnostic['schema'] ?? []) as $table => $schema): ?>
            <div class="card diagnostic-card schema-card" id="schema-<?= $table ?>" style="display: none;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Schema: <?= htmlspecialchars($table) ?></h5>
                    <button type="button" class="btn-close" aria-label="Close"></button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Column</th>
                                    <th>Type</th>
                                    <th>Null</th>
                                    <th>Key</th>
                                    <th>Default</th>
                                    <th>Extra</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schema as $column => $details): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($column) ?></code></td>
                                        <td><code><?= htmlspecialchars($details['type']) ?></code></td>
                                        <td><?= htmlspecialchars($details['null']) ?></td>
                                        <td><?= htmlspecialchars($details['key']) ?></td>
                                        <td><?= htmlspecialchars($details['default'] ?? 'NULL') ?></td>
                                        <td><?= htmlspecialchars($details['extra']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Test Results -->
        <div class="card diagnostic-card" id="test-result-card" style="display: none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Test Results</h5>
                <button type="button" class="btn-close close-test-results" aria-label="Close"></button>
            </div>
            <div class="card-body">
                <div id="test-results-content"></div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center mt-4">
            <a href="/registrar/batch-process" class="btn btn-primary">
                <i class="fa fa-arrow-left me-1"></i> Back to Batch Process
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // View schema button
            document.querySelectorAll('.view-schema-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const table = this.getAttribute('data-table');
                    hideAllSchemaTables();
                    document.getElementById(`schema-${table}`).style.display = 'block';
                });
            });
            
            // Close schema view
            document.querySelectorAll('.schema-card .btn-close').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    hideAllSchemaTables();
                });
            });
            
            // Close test results
            document.querySelectorAll('.close-test-results').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    document.getElementById('test-result-card').style.display = 'none';
                });
            });
            
            // Create table buttons
            document.querySelectorAll('.create-table-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const table = this.getAttribute('data-table');
                    fetch(`/registrar/create-table?table=${table}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.success ? 'Table created successfully!' : 'Error creating table: ' + data.message);
                        if (data.success) {
                            location.reload();
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error);
                    });
                });
            });
            
            // Test approve buttons
            document.querySelectorAll('.test-approve-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    document.getElementById('test-result-card').style.display = 'block';
                    document.getElementById('test-results-content').innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Testing approval for application #' + id + '...</p></div>';
                    
                    fetch(`/registrar/batch-process`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: `application_ids[]=${id}&action=approve&comments=Test approval&debug=true`
                    })
                    .then(response => response.json())
                    .then(data => {
                        let resultHtml = `
                            <h6>Approval Test Results:</h6>
                            <div class="alert alert-${data.success ? 'success' : 'danger'}">
                                ${data.message}
                            </div>
                        `;
                        
                        if (data.errors && data.errors.length > 0) {
                            resultHtml += '<div class="mb-3"><strong>Errors:</strong><ul class="mb-0">';
                            data.errors.forEach(error => {
                                resultHtml += `<li>${error}</li>`;
                            });
                            resultHtml += '</ul></div>';
                        }
                        
                        if (data.diagnostic) {
                            resultHtml += '<h6 class="mt-3">Diagnostic Information:</h6>';
                            resultHtml += `<div class="code-block">${JSON.stringify(data.diagnostic, null, 2)}</div>`;
                        }
                        
                        document.getElementById('test-results-content').innerHTML = resultHtml;
                    })
                    .catch(error => {
                        document.getElementById('test-results-content').innerHTML = `
                            <div class="alert alert-danger">
                                <strong>Error:</strong> ${error}
                            </div>
                        `;
                    });
                });
            });
            
            // Hide all schema tables
            function hideAllSchemaTables() {
                document.querySelectorAll('.schema-card').forEach(function(card) {
                    card.style.display = 'none';
                });
            }
        });
    </script>
</body>
</html> 