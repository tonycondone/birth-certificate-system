<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>User Management - Admin Portal</title>
    <link href="/public/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>User Management</h1>
        <p>Manage registered users, their roles, and statuses within the birth certificate system.</p>

        <div class="mb-3">
            <input type="search" id="searchInput" class="form-control" placeholder="Search users by name or email" aria-label="Search users" />
        </div>

        <table class="table table-bordered table-hover align-middle" id="usersTable">
            <thead class="table-light">
                <tr>
                    <th scope="col">User ID</th>
                    <th scope="col">Full Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Role</th>
                    <th scope="col">Status</th>
                    <th scope="col" style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- User rows will be loaded here dynamically -->
            </tbody>
        </table>

        <nav aria-label="User pagination">
            <ul class="pagination justify-content-center" id="pagination">
                <!-- Pagination buttons will be loaded here dynamically -->
            </ul>
        </nav>
    </div>

<script>
$(document).ready(function() {
    const usersPerPage = 10;
    let currentPage = 1;
    let usersData = [];

    function fetchUsers() {
        $.ajax({
            url: '/api/admin/users',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                usersData = data.users;
                renderTable();
                renderPagination();
            },
            error: function() {
                alert('Failed to load users data.');
            }
        });
    }

    function renderTable() {
        const tbody = $('#usersTable tbody');
        tbody.empty();

        const filteredUsers = usersData.filter(user => {
            const searchTerm = $('#searchInput').val().toLowerCase();
            return user.full_name.toLowerCase().includes(searchTerm) || user.email.toLowerCase().includes(searchTerm);
        });

        const start = (currentPage - 1) * usersPerPage;
        const end = start + usersPerPage;
        const usersToShow = filteredUsers.slice(start, end);

        usersToShow.forEach(user => {
            const statusBadge = user.status === 'active' ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
            const row = `
                <tr>
                    <td>${user.id}</td>
                    <td>${user.full_name}</td>
                    <td>${user.email}</td>
                    <td>${user.role}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-primary me-1 edit-btn" data-id="${user.id}">Edit</button>
                        <button class="btn btn-sm btn-warning me-1 toggle-status-btn" data-id="${user.id}">${user.status === 'active' ? 'Deactivate' : 'Activate'}</button>
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${user.id}">Delete</button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    function renderPagination() {
        const pagination = $('#pagination');
        pagination.empty();

        const filteredUsers = usersData.filter(user => {
            const searchTerm = $('#searchInput').val().toLowerCase();
            return user.full_name.toLowerCase().includes(searchTerm) || user.email.toLowerCase().includes(searchTerm);
        });

        const totalPages = Math.ceil(filteredUsers.length / usersPerPage);

        for (let i = 1; i <= totalPages; i++) {
            const activeClass = i === currentPage ? 'active' : '';
            pagination.append(`<li class="page-item ${activeClass}"><a class="page-link" href="#">${i}</a></li>`);
        }
    }

    $('#searchInput').on('input', function() {
        currentPage = 1;
        renderTable();
        renderPagination();
    });

    $('#pagination').on('click', 'a', function(e) {
        e.preventDefault();
        currentPage = parseInt($(this).text());
        renderTable();
        renderPagination();
    });

    $('#usersTable').on('click', '.toggle-status-btn', function() {
        const userId = $(this).data('id');
        $.ajax({
            url: `/api/admin/users/${userId}/toggle-status`,
            method: 'POST',
            success: function() {
                fetchUsers();
            },
            error: function() {
                alert('Failed to update user status.');
            }
        });
    });

    $('#usersTable').on('click', '.delete-btn', function() {
        if (!confirm('Are you sure you want to delete this user?')) return;
        const userId = $(this).data('id');
        $.ajax({
            url: `/api/admin/users/${userId}`,
            method: 'DELETE',
            success: function() {
                fetchUsers();
            },
            error: function() {
                alert('Failed to delete user.');
            }
        });
    });

    // Initial fetch
    fetchUsers();
});
</script>
</body>
</html>
