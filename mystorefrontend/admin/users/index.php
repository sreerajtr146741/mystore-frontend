<?php
include __DIR__ . '/../../partials/premium-styles.php';
// $users is available from index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>User Management • Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-dark bg-dark shadow-sm mb-4">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold">MyStore Admin</span>
        <form method="POST" action="<?= route('logout') ?>" class="m-0">
            <?= csrf_field() ?>
            <button class="btn btn-warning btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </button>
        </form>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-people me-2"></i>User Management</h2>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= session('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card bg-dark border-secondary mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="q" class="form-control" placeholder="Search by name or email" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="buyer" <?php if(($_GET['role'] ?? '') == 'buyer') echo 'selected'; ?>>Buyer</option>
                        <option value="seller" <?php if(($_GET['role'] ?? '') == 'seller') echo 'selected'; ?>>Seller</option>
                        <option value="admin" <?php if(($_GET['role'] ?? '') == 'admin') echo 'selected'; ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" <?php if(($_GET['status'] ?? '') == 'active') echo 'selected'; ?>>Active</option>
                        <option value="suspended" <?php if(($_GET['status'] ?? '') == 'suspended') echo 'selected'; ?>>Suspended</option>
                        <option value="blocked" <?php if(($_GET['status'] ?? '') == 'blocked') echo 'selected'; ?>>Blocked</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card bg-dark border-secondary">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="user-rows">
                    <?php include __DIR__ . '/partials/row.php'; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary text-white shadow-lg">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-lines-fill me-2"></i>User Profile</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="user-modal-body">
                <!-- Content loaded via JS -->
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Basic modal logic for View User could be added here if needed
    // Currently removed the complex AJAX fetch for simplicity unless requested
});
</script>

</body>
</html>
