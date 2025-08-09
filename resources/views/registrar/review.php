<?php
$pageTitle = $pageTitle ?? 'Review Application';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Digital Birth Certificate System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        .page-header{background:#f8f9fa;border-radius:8px;padding:16px 20px;margin:16px 0}
        .section-card{margin-bottom:16px}
        .sticky-actions{position:sticky;bottom:0;background:#fff;border-top:1px solid #e9ecef;padding:12px 0;z-index:10}
        .muted{color:#6c757d}
        .kv{display:flex;gap:8px}
        .kv .k{min-width:140px;color:#6c757d}
    </style>
</head>
<body class="bg-light">
<div class="container py-3">
    <div class="d-flex align-items-center justify-content-between page-header">
        <div>
            <h2 class="h4 mb-1">Review Application</h2>
            <div class="muted">Reference: <?= htmlspecialchars($application['application_number'] ?? ($application['id'] ?? '')) ?></div>
        </div>
        <div>
            <a href="/registrar/pending" class="btn btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i>Back to Pending</a>
        </div>
    </div>

    <div id="alertHost"></div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card section-card">
                <div class="card-header"><strong>Child Information</strong></div>
                <div class="card-body">
                    <div class="kv"><div class="k">Full Name</div><div class="v"><?= htmlspecialchars(($application['child_first_name'] ?? '').' '.($application['child_middle_name'] ?? '').' '.($application['child_last_name'] ?? '')) ?></div></div>
                    <div class="kv"><div class="k">Date of Birth</div><div class="v"><?= !empty($application['date_of_birth']) ? date('M j, Y', strtotime($application['date_of_birth'])) : '—' ?></div></div>
                    <div class="kv"><div class="k">Time of Birth</div><div class="v"><?= !empty($application['time_of_birth']) ? date('g:i A', strtotime($application['time_of_birth'])) : '—' ?></div></div>
                    <div class="kv"><div class="k">Gender</div><div class="v"><?= htmlspecialchars(ucfirst($application['gender'] ?? '')) ?></div></div>
                    <div class="kv"><div class="k">Place of Birth</div><div class="v"><?= htmlspecialchars($application['place_of_birth'] ?? '') ?></div></div>
                </div>
            </div>

            <div class="card section-card">
                <div class="card-header"><strong>Parents</strong></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="kv"><div class="k">Father</div><div class="v"><?= htmlspecialchars($application['father_name'] ?? (($application['father_first_name'] ?? '').' '.($application['father_last_name'] ?? ''))) ?></div></div>
                            <div class="kv"><div class="k">Phone</div><div class="v"><?= htmlspecialchars($application['father_phone'] ?? '') ?></div></div>
                            <div class="kv"><div class="k">ID</div><div class="v"><?= htmlspecialchars($application['father_national_id'] ?? '') ?></div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="kv"><div class="k">Mother</div><div class="v"><?= htmlspecialchars($application['mother_name'] ?? (($application['mother_first_name'] ?? '').' '.($application['mother_last_name'] ?? ''))) ?></div></div>
                            <div class="kv"><div class="k">Phone</div><div class="v"><?= htmlspecialchars($application['mother_phone'] ?? '') ?></div></div>
                            <div class="kv"><div class="k">ID</div><div class="v"><?= htmlspecialchars($application['mother_national_id'] ?? '') ?></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card section-card">
                <div class="card-header"><strong>Supporting Documents</strong></div>
                <div class="card-body">
                    <?php if (!empty($documents)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($documents as $doc): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fa fa-file-text text-secondary me-2"></i>
                                        <?= htmlspecialchars($doc['document_type'] ?? 'Document') ?>
                                        <small class="text-muted ms-2"><?= !empty($doc['uploaded_at']) ? date('M j, Y', strtotime($doc['uploaded_at'])) : '' ?></small>
                                    </span>
                                    <span>
                                        <?php if (!empty($doc['id'])): ?>
                                            <a class="btn btn-sm btn-outline-primary" href="/documents/view/<?= (int)$doc['id'] ?>" target="_blank">View</a>
                                        <?php endif; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-muted">No documents uploaded.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card section-card">
                <div class="card-header"><strong>History</strong></div>
                <div class="card-body">
                    <?php if (!empty($history)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($history as $h): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div><strong><?= htmlspecialchars($h['action'] ?? 'Update') ?></strong></div>
                                        <div class="text-muted small"><?= !empty($h['created_at']) ? date('M j, Y g:i A', strtotime($h['created_at'])) : '' ?></div>
                                    </div>
                                    <div class="mt-1 text-muted"><?= nl2br(htmlspecialchars($h['description'] ?? '')) ?></div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-muted">No history yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card section-card">
                <div class="card-header"><strong>Applicant</strong></div>
                <div class="card-body">
                    <div class="kv"><div class="k">Name</div><div class="v"><?= htmlspecialchars(($application['applicant_first_name'] ?? '').' '.($application['applicant_last_name'] ?? '')) ?></div></div>
                    <div class="kv"><div class="k">Email</div><div class="v"><?= htmlspecialchars($application['applicant_email'] ?? '') ?></div></div>
                    <div class="kv"><div class="k">Phone</div><div class="v"><?= htmlspecialchars($application['applicant_phone'] ?? '') ?></div></div>
                    <div class="kv"><div class="k">Submitted</div><div class="v"><?= !empty($application['submitted_at']) ? date('M j, Y g:i A', strtotime($application['submitted_at'])) : '—' ?></div></div>
                    <div class="kv"><div class="k">Status</div><div class="v"><span class="badge bg-info"><?= htmlspecialchars(ucwords(str_replace('_',' ',$application['status'] ?? ''))) ?></span></div></div>
                </div>
            </div>

            <div class="card section-card">
                <div class="card-header"><strong>Registrar Decision</strong></div>
                <div class="card-body">
                    <form id="reviewForm">
                        <input type="hidden" name="application_id" value="<?= (int)($application['id'] ?? 0) ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <div class="mb-3">
                            <label for="comments" class="form-label">Comments</label>
                            <textarea class="form-control" id="comments" name="comments" rows="3" placeholder="Add comments (required for rejection)"></textarea>
                            <div id="commentsFeedback" class="invalid-feedback"></div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success" data-action="approve"><i class="fa fa-check me-1"></i>Approve</button>
                            <button type="submit" class="btn btn-danger" data-action="reject"><i class="fa fa-times me-1"></i>Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="sticky-actions">
        <div class="container">
            <div class="d-flex justify-content-end gap-2">
                <a href="/registrar/pending" class="btn btn-outline-secondary"><i class="fa fa-list me-1"></i>Back to List</a>
                <a href="#" class="btn btn-outline-primary" onclick="window.scrollTo({top:0,behavior:'smooth'});return false;"><i class="fa fa-arrow-up me-1"></i>Top</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
  const form = document.getElementById('reviewForm');
  const alertHost = document.getElementById('alertHost');
  function showAlert(kind, msg){
    alertHost.innerHTML = `<div class="alert alert-${kind} alert-dismissible fade show" role="alert">${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`;
  }
  form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const action = e.submitter?.dataset?.action || 'approve';
    const fd = new FormData(form);
    fd.append('action', action);
    const comments = (fd.get('comments')||'').toString().trim();
    if(action==='reject' && !comments){
      const fb = document.getElementById('commentsFeedback');
      fb.textContent = 'Please provide a reason for rejection.';
      document.getElementById('comments').classList.add('is-invalid');
      return;
    }
    document.getElementById('comments').classList.remove('is-invalid');
    try{
      const res = await fetch('/registrar/process', {
        method: 'POST',
        headers: { 'X-Requested-With':'XMLHttpRequest' },
        body: fd
      });
      const data = await res.json();
      if(data && data.success){
        showAlert('success', data.message || 'Action completed. Redirecting...');
        setTimeout(()=>{ window.location.href = '/registrar/pending'; }, 800);
      }else{
        showAlert('danger', (data && (data.error||data.message)) || 'Action failed.');
      }
    }catch(err){
      showAlert('danger', 'Network error. Please try again.');
    }
  });
})();
</script>
</body>
</html> 