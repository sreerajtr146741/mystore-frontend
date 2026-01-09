<?php
// Output Buffer for Content
ob_start();

// Ensure order object is safe
$order = isset($order) ? $order : null;
if (!$order) {
    echo "<div class='container py-5 text-center'><h3>Order not found</h3><a href='/orders' class='btn btn-primary'>Back to Orders</a></div>";
    $content = ob_get_clean();
    include __DIR__ . '/../layouts/master.php';
    exit;
}
?>
<div class="container py-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="fw-bold mb-0">Order Details</h2>
        <div class="d-flex align-items-center gap-3">
             <span class="text-muted me-2">Order ID: #<?= $order->id ?></span>
             <?php if(in_array($order->status, ['placed', 'processing'])): ?>
                <form action="<?= route('orders.cancel', ['id' => $order->id]) ?>" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">Cancel Order</button>
                </form>
            <?php endif; ?>
            <?php if($order->status == 'delivered'): ?>
                <form action="<?= route('orders.return', ['id' => $order->id]) ?>" method="POST" onsubmit="return confirm('Request a return for this order?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-warning btn-sm rounded-pill px-3 fw-bold">Return Order</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tracker -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 p-4">
        <h5 class="fw-bold mb-4">Delivery Status</h5>
        
        <?php if($order->status == 'cancelled'): ?>
            <div class="alert alert-danger d-flex align-items-center mb-0">
                <i class="bi bi-x-circle fs-4 me-3"></i>
                <div>
                    <div class="fw-bold">Order Cancelled</div>
                    <div class="small">This order has been cancelled. Please contact support for help.</div>
                </div>
            </div>
        <?php elseif($order->status == 'returned'): ?>
            <div class="alert alert-secondary d-flex align-items-center mb-0">
                <i class="bi bi-arrow-counterclockwise fs-4 me-3"></i>
                <div>
                    <div class="fw-bold">Order Returned</div>
                    <div class="small">This order has been returned and refunded.</div>
                </div>
            </div>
        <?php else: ?>
            <div class="position-relative mx-3 my-4">
                <?php 
                    $width = match($order->status) {
                        'placed' => '0%',
                        'processing' => '33%',
                        'shipped' => '66%',
                        'delivered', 'return_requested' => '100%',
                        default => '0%'
                    };
                ?>
                <!-- Progress Background -->
                <div class="progress position-absolute top-50 start-0 w-100 translate-middle-y" style="height: 4px; z-index: 1;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $width ?>;"></div>
                </div>

                <div class="d-flex justify-content-between position-relative" style="z-index: 2;">
                    <?php 
                    $steps = [
                        ['status' => 'placed', 'label' => 'Placed', 'icon' => 'bi-clipboard'],
                        ['status' => 'processing', 'label' => 'Processing', 'icon' => 'bi-gear'],
                        ['status' => 'shipped', 'label' => 'Shipped', 'icon' => 'bi-truck'],
                        ['status' => 'delivered', 'label' => 'Delivered', 'icon' => 'bi-check-circle']
                    ];
                    // Mapping for active check. 
                    // Logic: If status is 'shipped', then placed, processing and shipped are active.
                    $statusOrder = ['placed'=>1, 'processing'=>2, 'shipped'=>3, 'out_for_delivery'=>3, 'delivered'=>4, 'return_requested'=>4];
                    $currentVal = $statusOrder[$order->status] ?? 0;
                    
                    foreach($steps as $idx => $step): 
                        $stepVal = $idx + 1;
                        $isActive = $stepVal <= $currentVal;
                    ?>
                        <div class="text-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 text-white <?= $isActive ? 'bg-success' : 'bg-secondary' ?>" 
                                 style="width: 40px; height: 40px; border: 4px solid #fff; box-shadow: 0 0 0 1px #dee2e6;">
                                <i class="bi <?= $step['icon'] ?>"></i>
                            </div>
                            <div class="small fw-bold <?= $isActive ? 'text-dark' : 'text-muted' ?>"><?= $step['label'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if($order->status == 'processing'): ?>
                <div class="alert alert-info py-2 small d-inline-block mt-3"><i class="bi bi-info-circle me-1"></i> We are packing your order.</div>
            <?php elseif($order->status == 'shipped'): ?>
                <div class="alert alert-primary py-2 small d-inline-block mt-3"><i class="bi bi-truck me-1"></i> Your order is on the way!</div>
            <?php elseif($order->status == 'return_requested'): ?>
                <div class="alert alert-warning py-2 small d-inline-block mt-3"><i class="bi bi-clock me-1"></i> You have requested a return. Waiting for approval.</div>
            <?php endif; ?>
        <?php endif; ?>
        

        <?php if(!empty($order->delivery_date) && !in_array($order->status, ['cancelled', 'return_requested', 'returned', 'delivered'])): ?>
            <?php $delDate = $order->delivery_date instanceof \Carbon\Carbon ? $order->delivery_date : \Carbon\Carbon::parse($order->delivery_date); ?>
            <div class="mt-4 pt-3 border-top d-flex align-items-center text-muted">
                <i class="bi bi-calendar-event me-2 fs-5"></i>
                <div>
                    <span class="small text-uppercase fw-bold">Expected Delivery</span><br>
                    <span class="text-dark fw-bold"><?= $delDate->format('D, d M Y') ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Items & Address -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 fw-bold">Items Ordered</div>
                <div class="card-body p-0">
                    <?php 
                    $items = is_array($order->items) ? $order->items : ($order->items instanceof \Illuminate\Support\Collection ? $order->items->all() : []);
                    foreach($items as $item): 
                        // Safely cast
                        $item = (object)$item;
                        if(isset($item->product) && is_array($item->product)) $item->product = (object)$item->product;
                        $pImg = $item->product->image ?? '';
                        $image = (strpos($pImg, 'http') === 0) ? $pImg : asset('storage/'.$pImg);
                    ?>
                        <div class="d-flex p-3 border-bottom">
                            <div style="width: 80px; height: 80px;" class="flex-shrink-0 bg-light rounded overflow-hidden">
                                <img src="<?= $image ?>" class="w-100 h-100 object-fit-cover" alt="Product">
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <h6 class="fw-bold mb-1"><?= $item->product->name ?? 'Product' ?></h6>
                                <div class="small text-muted"><?= $item->qty ?> x ₹<?= number_format($item->price, 2) ?></div>
                            </div>
                            <div class="fw-bold text-end">
                                <div>₹<?= number_format($item->price * $item->qty, 2) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer bg-light p-3 text-end fw-bold">
                    Total: ₹<?= number_format($order->total, 2) ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 fw-bold">Shipping Details</div>
                <div class="card-body">
                    <?php $user = auth()->user(); ?>
                    <h6 class="fw-bold"><?= $user->name ?></h6>
                    <p class="mb-0 text-muted small"><?= $order->shipping_address ?? 'Address not found' ?></p>
                    <hr>
                    <div class="small text-muted">Phone: <?= $user->phone ?? 'N/A' ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
// Include Master
include __DIR__ . '/../layouts/master.php';
?>
