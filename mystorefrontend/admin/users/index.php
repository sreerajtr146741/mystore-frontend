<?php
ob_start();

$q = $_GET['q'] ?? '';
$roleFilter = $_GET['role'] ?? '';
$statusFilter = $_GET['status'] ?? '';
?>

<div class="row mb-4 animate__animated animate__fadeInDown">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 fw-bold text-white mb-1">
                <i class="bi bi-people me-2"></i>User <span class="text-secondary opacity-75">Management</span>
            </h1>
            <p class="text-white-50 mb-0 small">Manage user accounts and permissions.</p>
        </div>
    </div>
</div>

<div class="card border-0 shadow-lg rounded-4 overflow-hidden animate__animated animate__fadeInUp" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px);">
    <div class="card-body p-0">
        <?php if(session('success')): ?>
            <div class="alert alert-success m-3 rounded-3 d-flex align-items-center gap-2 border-0 shadow-sm">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div class="fw-medium"><?= session('success') ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="alert alert-danger m-3 rounded-3 d-flex align-items-center gap-2 border-0 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div class="fw-medium"><?= session('error') ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="p-3 bg-light border-bottom">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                     <div class="input-group">
                         <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                         <input type="text" name="q" class="form-control" placeholder="Search by name or email" value="<?= htmlspecialchars($q) ?>">
                     </div>
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="buyer" <?= $roleFilter == 'buyer' ? 'selected' : '' ?>>Buyer</option>
                        <option value="seller" <?= $roleFilter == 'seller' ? 'selected' : '' ?>>Seller</option>
                        <option value="admin" <?= $roleFilter == 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" <?= $statusFilter == 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="suspended" <?= $statusFilter == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                        <option value="blocked" <?= $statusFilter == 'blocked' ? 'selected' : '' ?>>Blocked</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Filter</button>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="user-rows" class="border-top-0">
                    <?php include __DIR__ . '/partials/row.php'; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>

<?php 
// User Details Modal logic
?>
<div class="modal fade" id="userDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-lines-fill me-2"></i>User Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="user-modal-body">
                <!-- Content loaded via JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'User Management • MyStore Admin';
include __DIR__ . '/../../layouts/admin.php';
?>
