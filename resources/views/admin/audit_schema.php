<?php
$pageTitle = $pageTitle ?? 'Audit Schema Inspector';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Audit Schema Inspector</h3>
    <a href="/admin" class="btn btn-outline-secondary">Back to Admin</a>
  </div>
  <?php foreach (($schema ?? []) as $table => $info): ?>
    <div class="card mb-3">
      <div class="card-header d-flex justify-content-between align-items-center">
        <strong><?= htmlspecialchars($table) ?></strong>
        <?php if ($info['exists']): ?>
          <span class="badge bg-success">Exists</span>
        <?php else: ?>
          <span class="badge bg-danger">Missing</span>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <?php if ($info['exists']): ?>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
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
                <?php foreach ($info['columns'] as $name => $col): ?>
                  <tr>
                    <td><code><?= htmlspecialchars($name) ?></code></td>
                    <td><code><?= htmlspecialchars($col['type']) ?></code></td>
                    <td><?= htmlspecialchars($col['null']) ?></td>
                    <td><?= htmlspecialchars($col['key']) ?></td>
                    <td><?= htmlspecialchars((string)($col['default'] ?? 'NULL')) ?></td>
                    <td><?= htmlspecialchars($col['extra']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="p-3 text-muted">Table is missing.</div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
</body>
</html> 