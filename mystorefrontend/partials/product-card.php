<?php
// Ensure $p is available and is an object
if (!isset($p)) { 
    return; 
}
if (is_array($p)) {
    $p = (object) $p;
}
// Strict check: must be object and have an ID
if (!is_object($p) || !isset($p->id)) {
    return; 
}

// define local helper for image if not exists
$imgHelper = function($path) {
    if (!$path) return null;
    if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
    return '/storage/' . ltrim($path, '/');
};

$photo = $imgHelper($p->image ?? '');
$price = (float)($p->price ?? 0);
$finalPrice = (float)($p->discounted_price ?? $p->final_price ?? $price);

$hasDiscount = $finalPrice < $price;
$saveAmt = $price - $finalPrice;
$savePct = $price > 0 ? round(($saveAmt / $price) * 100) : 0;
$stock = $p->stock ?? null;
// Use route helper if available, else manual
$href = function_exists('route') ? route('products.show', ['id' => $p->id]) : '/products/show?id=' . $p->id;
?>

<div class="card card-prod h-100" data-href="<?= $href ?>">
    <!-- Discount Ribbon -->
    <?php if($hasDiscount): ?>
        <div class="ribbon"><?= $savePct ?>% OFF</div>
    <?php endif; ?>

    <!-- Image -->
    <?php if($photo): ?>
        <img class="img-fit" src="<?= $photo ?>" alt="<?= htmlspecialchars($p->name ?? 'Product') ?>">
    <?php else: ?>
        <div class="bg-light d-flex align-items-center justify-content-center" style="aspect-ratio:4/3;">
            <i class="bi bi-image fs-1 text-muted"></i>
        </div>
    <?php endif; ?>

    <div class="card-body d-flex flex-column">
        <h5 class="fw-bold mb-1 text-truncate" title="<?= htmlspecialchars($p->name ?? 'Product') ?>"><?= htmlspecialchars($p->name ?? 'Product') ?></h5>

        <div class="mb-2">
            <?php if(!empty($p->category)): ?>
                <span class="badge badge-cat"><?= htmlspecialchars($p->category) ?></span>
            <?php endif; ?>
        </div>

        <div class="mt-auto">
            <?php if($stock !== null): ?>
                <div class="small <?= $stock > 0 ? 'text-success' : 'text-danger' ?> mb-2">
                    <span class="stock-dot" style="background:<?= $stock > 0 ? '#22c55e' : '#ef4444' ?>"></span>
                    <?= $stock > 0 ? $stock . ' in stock' : 'Out of stock' ?>
                </div>
            <?php endif; ?>

            <div>
                <?php if($hasDiscount): ?>
                    <div>
                        <span class="strike muted">₹<?= number_format($price, 2) ?></span>
                        <span class="price text-success">₹<?= number_format($finalPrice, 2) ?></span>
                    </div>
                <?php else: ?>
                    <span class="price">₹<?= number_format($price, 2) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
