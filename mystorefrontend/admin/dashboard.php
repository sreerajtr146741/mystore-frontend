<?php
ob_start();

$user = auth()->user();
$role = is_object($user) ? ($user->role ?? '') : ($user['role'] ?? '');
$isAdmin = ($role === 'admin');
$isSeller = ($role === 'seller');
?>

<div class="row mb-4 animate__animated animate__fadeInDown">
    <div class="col-12">
        <h1 class="h3 fw-bold text-white mb-1">
            Dashboard
        </h1>
        <p class="text-white-50 mb-0 small">Welcome back, <?= is_object($user) ? $user->name : ($user['name'] ?? 'User') ?></p>
    </div>
</div>

<?php if($isAdmin): ?>
    <div class="row g-4 animate__animated animate__fadeInUp">
        <!-- KPI Cards -->
        <div class="col-md-3">
             <div class="card border-0 shadow-sm h-100">
                 <div class="card-body">
                     <h6 class="text-muted text-uppercase small fw-bold">Total Accounts</h6>
                     <h2 class="mb-0 fw-bold"><?= $stats['total_users'] ?? 0 ?></h2>
                     <small class="text-success"><i class="bi bi-arrow-up"></i> User Network</small>
                 </div>
             </div>
        </div>
        
        <div class="col-md-3">
             <div class="card border-0 shadow-sm h-100">
                 <div class="card-body">
                     <h6 class="text-muted text-uppercase small fw-bold">Inventory</h6>
                     <h2 class="mb-0 fw-bold"><?= $stats['total_products'] ?? 0 ?></h2>
                     <small class="text-info"><?= $stats['new_today'] ?? 0 ?> new today</small>
                 </div>
             </div>
        </div>
        
        <div class="col-md-3">
             <div class="card border-0 shadow-sm h-100">
                 <div class="card-body">
                     <h6 class="text-muted text-uppercase small fw-bold">Gross Revenue</h6>
                     <h2 class="mb-0 fw-bold">₹<?= number_format($stats['total_revenue'] ?? 0) ?></h2>
                     <small class="text-muted">Lifetime Performance</small>
                 </div>
             </div>
        </div>
        
        <div class="col-md-3">
             <div class="card border-0 shadow-sm h-100">
                 <div class="card-body">
                     <h6 class="text-muted text-uppercase small fw-bold">Pending Orders</h6>
                     <h2 class="mb-0 fw-bold"><?= $adminExtras['pending_orders'] ?? 0 ?></h2>
                     <small class="<?= ($adminExtras['pending_orders'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                        <?= ($adminExtras['pending_orders'] ?? 0) > 0 ? 'Requires attention' : 'No items pending' ?>
                     </small>
                 </div>
             </div>
        </div>
        
        <!-- Detailed Stats Row -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="fw-bold m-0">User Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4 border-end">
                            <i class="bi bi-people fs-1 text-primary"></i>
                            <h4 class="mt-2 fw-bold"><?= $userStats['buyers'] ?? 0 ?></h4>
                            <span class="text-muted small">Active Buyers</span>
                        </div>
                        <div class="col-4 border-end">
                            <i class="bi bi-person-x fs-1 text-danger"></i>
                            <h4 class="mt-2 fw-bold"><?= $stats['suspended_users'] ?? 0 ?></h4>
                            <span class="text-muted small">Suspended</span>
                        </div>
                        <div class="col-4">
                            <i class="bi bi-person-check fs-1 text-success"></i>
                            <h4 class="mt-2 fw-bold"><?= $userStats['active_30d'] ?? 0 ?></h4>
                            <span class="text-muted small">Active (30d)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                <div class="card-body">
                     <h5 class="fw-bold">Messages</h5>
                     <div class="d-flex align-items-center justify-content-between mt-4">
                         <div>
                             <h2 class="fw-bold mb-0"><?= $adminExtras['pending_messages'] ?? 0 ?></h2>
                             <span class="opacity-75">New Inquiries</span>
                         </div>
                         <i class="bi bi-chat-dots fs-1 opacity-50"></i>
                     </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if($isSeller): ?>
    <div class="alert alert-info shadow-sm mt-4">
        <h5 class="alert-heading fw-bold"><i class="bi bi-shop me-2"></i>Seller Panel</h5>
        <p class="mb-0">Access your store management tools from the navigation menu. Manage products, view orders, and more.</p>
    </div>
<?php endif; ?>

<footer class="mt-5 text-center text-white-50 small">
    Updated: <?= date('d M Y • h:i A') ?> • MyStore Admin v2.3 (Latest)
</footer>

<?php
$content = ob_get_clean();
$title = 'Dashboard • MyStore Admin';
include __DIR__ . '/../../layouts/admin.php';
?>
