<?php 
$idx = 0;
foreach($orders as $order): 
    $idx++; 
?>
    <div class="card border-0 shadow-lg mb-4 rounded-4 overflow-hidden hover:shadow-2xl transition-all duration-300">
        <div class="card-header bg-white border-bottom-0 p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-indigo-100 text-indigo-700 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="fas fa-box-open fs-5"></i>
                </div>
                <div>
                    <div class="text-xs text-uppercase text-muted fw-bold tracking-wider">Order ID</div>
                    <div class="fw-bold fs-5 text-dark">#<?= $order->id ?></div>
                </div>
            </div>
            <div>
               <div class="text-end">
                    <?php if($order->status == 'delivered'): ?>
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-bold border border-success-subtle">
                            <i class="fas fa-check-circle me-1"></i> Delivered
                        </span>
                    <?php elseif($order->status == 'shipped'): ?>
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill fw-bold border border-primary-subtle">
                            <i class="fas fa-truck me-1"></i> Shipped
                        </span>
                    <?php elseif($order->status == 'processing'): ?>
                        <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill fw-bold border border-info-subtle">
                            <i class="fas fa-cog fa-spin me-1"></i> Processing
                        </span>
                    <?php elseif($order->status == 'out_for_delivery'): ?>
                        <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill fw-bold border border-warning-subtle">
                            <i class="fas fa-truck-moving me-1"></i> Out for Delivery
                        </span>
                    <?php elseif($order->status == 'return_requested'): ?>
                        <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2 rounded-pill fw-bold border border-warning-subtle">
                            <i class="fas fa-undo me-1"></i> Return Requested
                        </span>
                    <?php elseif($order->status == 'returned'): ?>
                        <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fw-bold border border-secondary-subtle">
                            <i class="fas fa-check-double me-1"></i> Returned
                        </span>
                    <?php else: ?>
                        <span class="badge bg-light text-dark px-3 py-2 rounded-pill fw-bold border">
                            <i class="fas fa-clock me-1"></i> <?= ucfirst(str_replace('_', ' ', $order->status)) ?>
                        </span>
                    <?php endif; ?>
               </div>
               <div class="text-xs text-muted text-end mt-1"><?= $order->created_at instanceof \Carbon\Carbon ? $order->created_at->format('D, d M Y • h:i A') : date('D, d M Y • h:i A', strtotime($order->created_at)) ?></div>
            </div>
        </div>
        
        <div class="card-body p-4 bg-light bg-opacity-25">
            <div class="row align-items-center g-4">
                <!-- Product Previews -->
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-3 overflow-auto pb-2" style="scrollbar-width: thin;">
                        <?php 
                        // Mock Items collection if array
                        $items = is_array($order->items) ? collect($order->items) : $order->items;
                        $take4 = $items->take(4);
                        $itemIdx = 0;
                        foreach($take4 as $item): 
                            $itemIdx++;
                            // Ensure item is object
                            $item = (object)$item;
                            // Ensure nested product is object
                            if(isset($item->product) && is_array($item->product)) $item->product = (object)$item->product;
                            
                            $pImg = $item->product->image ?? '';
                            $imgSrc = (strpos($pImg, 'http') === 0) ? $pImg : asset('storage/'.$pImg);
                        ?>
                            <div class="position-relative" style="min-width: 70px;">
                                <div class="ratio ratio-1x1 rounded-3 overflow-hidden border bg-white">
                                    <img src="<?= $imgSrc ?>" class="object-fit-cover" alt="Product">
                                </div>
                                <?php if($itemIdx == 4 && $items->count() > 4): ?>
                                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex align-items-center justify-content-center text-white fw-bold rounded-3">
                                        +<?= $items->count() - 3 ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php 
                            // Handle single item display text
                            $firstItem = $items->first();
                            if($firstItem) {
                                $firstItem = (object)$firstItem;
                                if(isset($firstItem->product) && is_array($firstItem->product)) $firstItem->product = (object)$firstItem->product;
                            }
                        ?>
                        <?php if($items->count() == 1 && $firstItem): ?>
                            <div class="nav flex-column">
                                <div class="fw-semibold text-dark"><?= $firstItem->product->name ?? 'Product' ?></div>
                                <div class="small text-muted">Qnt: <?= $firstItem->qty ?? 1 ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Meta & Actions -->
                <div class="col-md-5">
                    <div class="d-flex justify-content-between align-items-center h-100">
                        <div>
                            <div class="small text-muted text-uppercase fw-bold">Total Amount</div>
                            <div class="fs-4 fw-bold text-dark">₹<?= number_format($order->total, 2) ?></div>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <?php if(in_array($order->status, ['placed', 'processing'])): ?>
                                <form action="<?= route('orders.cancel', ['id' => $order->id]) ?>" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2 fw-bold shadow-sm hover-scale" title="Cancel Order">
                                        <i class="fas fa-times-circle"></i> Cancel
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php
                                // Handle date parsing safely
                                $updatedAt = $order->updated_at instanceof \Carbon\Carbon ? $order->updated_at : \Carbon\Carbon::parse($order->updated_at);
                                $daysSinceDelivery = $updatedAt->diffInDays(now());
                                $canReturn = $daysSinceDelivery <= 7;
                            ?>

                            <?php if($order->status == 'delivered'): ?>
                                <?php if($canReturn): ?>
                                    <form action="<?= route('orders.return', ['id' => $order->id]) ?>" method="POST" onsubmit="return confirm('Request a return for this order?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-2 fw-bold shadow-sm hover-scale" title="Return Order (<?= 7 - $daysSinceDelivery ?> days remaining)">
                                            <i class="fas fa-undo"></i> Return
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <a href="<?= route('orders.download', ['id' => $order->id]) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 fw-bold shadow-sm hover-scale" title="Download Invoice">
                                    <i class="bi bi-download"></i>
                                </a>
                            <?php endif; ?>
                            <a href="<?= route('orders.show', ['id' => $order->id]) ?>" class="btn btn-sm btn-primary rounded-pill px-3 py-2 fw-bold shadow-sm hover-scale">
                                View <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
