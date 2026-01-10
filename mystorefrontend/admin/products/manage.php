<?php
include __DIR__ . '/../../partials/premium-styles.php';

// Auth checks
$user = auth()->user();
$isAdmin = ($user->role ?? '') === 'admin';
$isSeller = ($user->role ?? '') === 'seller';
$canEdit = $isAdmin || $isSeller;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Products • Admin • MyStore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css">
    <style>
        .control-h { height:42px; }
        .rounded-14 { border-radius:14px; }
        .toolbar { gap:.5rem; flex-wrap:nowrap; }
        @media (max-width: 768px){
            .toolbar { flex-wrap:wrap; }
            .search-wrap, #categorySelect, .choices { width:100%!important; }
        }
        .search-input{
            height:42px; padding-left:38px; padding-right:38px;
            background:#0f182b!important; border-radius:14px; color:#e8eefb!important;
            border:1px solid #21314a; width:360px; max-width:100%;
        }
        .search-input:focus{
            background:#0f182b!important; color:#ffffff!important;
            border-color:var(--brand);
            box-shadow:0 0 0 0.25rem rgba(96,165,250,0.25);
        }
        #categorySelect {
            color:#e8eefb!important; background:#0d1526!important;
            border:1px solid #21314a; border-radius:14px; height:42px;
            min-width:220px; max-width:100%;
        }
        #categorySelect option{ background:#0d1526; color:#e8eefb; }
        .choices__inner{
            background:#0f182b!important; color:#e8eefb!important;
            border:1px solid #21314a!important; border-radius:14px!important; min-height:42px!important;
            padding-top:6px!important; padding-bottom:6px!important;
        }
        .choices[data-type*=select-one] .choices__input{ background:#0f182b!important; color:#e8eefb!important; }
        .choices__item--selectable{ color:#e8eefb!important; }
        .choices__list--dropdown{ background:#0d1526!important; border-color:#21314a!important; }
        /* Fix Constant Size */
        .choices { width: 260px !important; min-width: 260px !important; max-width: 260px !important; margin-bottom: 0 !important; }
        .table-wrap{ background:var(--table); border-radius:16px; border:1px solid #22304a; }
        .table thead th{ color:white; background:var(--table); border-color:#22304a; }
        .img-thumb{ width:56px; height:56px; border-radius:12px; object-fit:cover; }
        .btn-act{ padding:.3rem .55rem; border-radius:10px; }
        .btn-edit{ background:rgba(59,130,246,.18); border:1px solid rgba(59,130,246,.35); color:#dbeafe; }
        .btn-del{ background:rgba(239,68,68,.16); border:1px solid rgba(239,68,68,.35); color:#fecaca; }
        .btn-add{
            height:42px; padding:.45rem .8rem; border-radius:12px;
            display:inline-flex; align-items:center; gap:.45rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark">
    <div class="container d-flex justify-content-between">
        <a class="navbar-brand fw-bold" href="<?= url('/dashboard') ?>"><i class="bi bi-bag-fill me-2"></i>MyStore</a>
        <div class="d-flex gap-2">
            <a href="<?= url('/dashboard') ?>" class="btn btn-outline-light btn-sm">Dashboard</a>
            <form action="<?= route('logout') ?>" method="POST">
                <?= csrf_field() ?>
                <button class="btn btn-warning btn-sm">Logout</button>
            </form>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if($data['errors']->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <ul class="mb-0 ps-3">
                <li><?= $data['errors']->first() ?></li>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="row-gap:.5rem;">
        <?php if($canEdit): ?>
        <div class="d-flex w-100 justify-content-between gap-2">
            <form method="GET" class="d-flex align-items-center toolbar flex-grow-1" id="searchForm">
                <select id="categorySelect" name="category" class="form-select">
                    <option value="">All Categories</option>
                    <?php if(isset($categories)): ?>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= htmlspecialchars($c) ?>" <?php if(isset($_GET['category']) && $_GET['category'] == $c) echo 'selected'; ?>><?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <button class="btn btn-primary btn-sm control-h rounded-14 px-3" type="submit">
                    Filter
                </button>
            </form>
            <div>
                 <button class="btn btn-success btn-sm control-h rounded-14 px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#categoryDiscountModal">
                    <i class="bi bi-percent me-1"></i> Category Discount
                 </button>
                 <button class="btn btn-warning btn-sm control-h rounded-14 px-3 fw-bold text-dark" onclick="openBannerModal()">
                    <i class="bi bi-image me-1"></i> Manage Banner
                 </button>
                 <a href="<?= route('admin.products.create') ?>" class="btn btn-add btn-primary text-white text-decoration-none rounded-14 px-3 fw-bold">
                    <i class="bi bi-plus-lg"></i> Add Product
                 </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- PRODUCTS TABLE -->
    <div class="table-wrap">
        <div class="table-responsive">
            <table class="table table-dark table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Stock</th>
                        <th>Status</th>
                        <th>Seller</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="product-rows">
                    <?php 
                        // Include the row partial
                        // We rely on variables already being in scope or defining them.
                        // $products is available from controller/index.php
                        include __DIR__ . '/partials/row.php'; 
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const catSelect = document.getElementById('categorySelect');
    if (catSelect) {
        new Choices(catSelect, {
            searchEnabled: true,
            itemSelectText: '',
            shouldSort: false,
            removeItemButton: false,
            placeholder: true
        });
    }
});

// Auto-dismiss alerts
setTimeout(() => {
  document.querySelectorAll('.alert').forEach(el => {
    el.style.transition = 'opacity 0.5s ease';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 500);
  });
}, 5000);

function openBannerModal() {
    alert("Banner modal functionality is currently being updated.");
}
</script>

<!-- Category Discount Modal (Simplified) -->
<div class="modal fade" id="categoryDiscountModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bg-dark text-white border border-secondary shadow-lg">
      <div class="modal-header border-bottom border-secondary">
        <h5 class="modal-title fw-bold">Discount Manager</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
         <p>Discount feature coming soon.</p>
      </div>
    </div>
  </div>
</div>

</body>
</html>