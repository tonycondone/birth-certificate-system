<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>System Settings - Admin Portal</title>
    <link href="/public/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>System Settings</h1>
        <p>Configure system-wide settings and preferences for the birth certificate system.</p>

        <form>
            <div class="mb-3">
                <label for="siteTitle" class="form-label">Site Title</label>
                <input type="text" class="form-control" id="siteTitle" value="Digital Birth Certificate System" />
            </div>
            <div class="mb-3">
                <label for="defaultUserRole" class="form-label">Default User Role</label>
                <select class="form-select" id="defaultUserRole">
                    <option value="parent" selected>Parent/Guardian</option>
                    <option value="hospital">Hospital Staff</option>
                    <option value="registrar">Registrar</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="emailNotifications" class="form-label">Email Notifications</label>
                <select class="form-select" id="emailNotifications">
                    <option value="enabled" selected>Enabled</option>
                    <option value="disabled">Disabled</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="maintenanceMode" class="form-label">Maintenance Mode</label>
                <select class="form-select" id="maintenanceMode">
                    <option value="off" selected>Off</option>
                    <option value="on">On</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</body>
</html>
