<?php
// Product Detail Page (Plain PHP)

// 1. Data Setup (Variables passed from Controller/Index)
$p = $product ?? null;
if (!$p) {
    echo "Product not found.";
    return;
}

// Image Helper Logic (Local equivalent of Blade @php block)
$img = function($path) {
    if(!$path) return '/images/placeholder.png'; // Fallback
    if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
    // Assuming 'storage' symlink exists and maps to backend storage
    return '/storage/' . ltrim($path, '/');
};

// Pricing Logic
$price = (float)($p->price ?? 0);
$final = (float)($p->discounted_price ?? $p->final_price ?? $price);
$hasDisc = $final < $price;
$saveAmt = max(0, $price - $final);
$savePct = $price > 0 ? round(($saveAmt / $price) * 100) : 0;

// Gallery
$gallery = [];
if (!empty($p->image)) $gallery[] = $img($p->image);
if (empty($gallery)) $gallery[] = '/images/placeholder.png';
$mainImage = $gallery[0];

// Similarity
$simProducts = isset($similarProducts) ? (is_array($similarProducts) ? $similarProducts : $similarProducts->all()) : [];
$randProducts = isset($randomProducts) ? (is_array($randomProducts) ? $randomProducts : $randomProducts->all()) : [];

// 2. Capture Styles
ob_start();
?>
<style>
    body { background-color: #f1f3f6; font-family: Roboto, Arial, sans-serif; }
    .product-container { background: #fff; padding: 16px; box-shadow: 0 1px 1px 0 rgba(0,0,0,.16); }
    
    /* Left Column */
    .left-col { position: sticky; top: 80px; align-self: flex-start; }
    .gallery-wrapper { display: flex; flex-direction: row; gap: 10px; }
    .thumbnails { display: flex; flex-direction: column; gap: 5px; width: 64px; }
    .thumb-box { 
        width: 64px; height: 64px; border: 1px solid #f0f0f0; 
        cursor: pointer; overflow: hidden; padding: 2px;
        transition: border-color .2s;
    }
    .thumb-box:hover, .thumb-box.active { border-color: #2874f0; }
    .thumb-box img { width: 100%; height: 100%; object-fit: contain; }
    
    .main-image-box { 
        flex-grow: 1; height: 500px; display: flex; align-items: center; justify-content: center; 
        border: 1px solid #f0f0f0; position: relative;
    }
    .main-image-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
    
    .action-btns { margin-top: 0; display: flex; gap: 10px; }
    .btn-fk { 
        flex: 1; padding: 18px 8px; border: none; color: #fff; 
        font-weight: 600; font-size: 16px; border-radius: 2px; 
        text-transform: uppercase; box-shadow: 0 1px 2px 0 rgba(0,0,0,.2);
        display: flex; align-items: center; justify-content: center; gap: 8px;
        transition: box-shadow .2s;
    }
    .btn-fk:hover { box-shadow: 0 4px 8px 0 rgba(0,0,0,.2); color: #fff !important; opacity: 0.95; }
    .btn-cart { background: #ff9f00; }
    .btn-buy { background: #fb641b; }
    .btn-cart:hover { background: #f29700; }
    .btn-buy:hover { background: #ee5b16; }

    /* Right Column */
    .breadcrumb { font-size: 12px; color: #878787; margin-bottom: 5px; }
    .breadcrumb a { color: #878787; text-decoration: none; }
    .breadcrumb a:hover { color: #2874f0; }
    
    .product-title { font-size: 18px; color: #212121; margin-bottom: 5px; }
    
    .price-block { display: flex; align-items: baseline; gap: 10px; margin: 10px 0; }
    .final-price { font-size: 28px; font-weight: 500; color: #212121; }
    .original-price { font-size: 16px; color: #878787; text-decoration: line-through; }
    .discount-pct { font-size: 16px; color: #388e3c; font-weight: 500; }
    
    .offers-list { list-style: none; padding: 0; font-size: 14px; margin-top: 10px; }
    .offers-list li { margin-bottom: 8px; display: flex; gap: 8px; color: #212121; }
    .tag-icon { color: #16bd49; flex-shrink: 0; margin-top: 2px; }
    
    .section-head { font-size: 16px; font-weight: 500; color: #212121; display: flex; width: 110px; flex-shrink: 0; }
    .row-section { display: flex; margin-top: 24px; }
    .row-content { flex-grow: 1; }
    
    .product-card-hover { transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; }
    .product-card-hover:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-color: #2874f0 !important; }
    
    .specs-table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .specs-table td { padding: 8px 0; vertical-align: top; }
    .col-key { color: #878787; width: 33%; }
    .col-val { color: #212121; }
    .spec-cat-title { font-size: 18px; color: #000; margin-top: 20px; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 10px; }
    
    .desc-text { font-size: 14px; color: #212121; line-height: 1.5; white-space: pre-wrap; }
    .text-truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* Responsive */
    @media(max-width: 768px){
        .left-col { position: static; }
        .gallery-wrapper { flex-direction: column-reverse; }
        .thumbnails { flex-direction: row; width: 100%; overflow-x: auto; }
        .section-head { width: 100%; margin-bottom: 10px; }
        .row-section { flex-direction: column; }
    }
</style>
<?php
$styles = ob_get_clean();

// 3. Capture Content
ob_start();
?>
<div class="container-fluid mt-2 mb-4" style="max-width: 1400px;">
    <div class="product-container row g-0">
        
        <!-- LEFT COLUMN: IMAGES & BUTTONS -->
        <div class="col-md-5 col-lg-4 p-3 left-col">
            <div class="position-relative">
                <div class="gallery-wrapper">
                    <!-- Thumbnails -->
                    <?php if(count($gallery) > 1): ?>
                    <div class="thumbnails">
                        <?php foreach($gallery as $i => $src): ?>
                            <div class="thumb-box <?= $i===0 ? 'active':'' ?>" onclick="changeImage('<?= $src ?>', this)">
                                <img src="<?= $src ?>" alt="Thumb">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Main Image -->
                    <div class="main-image-box">
                        <img id="mainImage" src="<?= $mainImage ?>" alt="<?= htmlspecialchars($p->name) ?>">
                    </div>
                </div>
            
                <div class="action-btns">
                    <?php if(auth()->check()): ?>
                        <form action="<?= route('cart.add', ['id' => $p->id]) ?>" method="GET" class="flex-fill d-flexInput">
                            <!-- Note: Using GET for cart add based on index.php route logic (Line 198) which expects GET -->
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="id" value="<?= $p->id ?>">
                            <button class="btn btn-fk btn-cart flex-fill">
                                <i class="bi bi-cart-fill"></i> ADD TO CART
                            </button>
                        </form>
                        <a href="<?= route('checkout.index') ?>" class="btn btn-fk btn-buy flex-fill text-decoration-none">
                            <i class="bi bi-lightning-fill"></i> BUY NOW
                        </a>
                    <?php else: ?>
                        <a href="<?= route('login') ?>" class="btn btn-fk btn-cart text-decoration-none"><i class="bi bi-cart-fill"></i> ADD TO CART</a>
                        <a href="<?= route('login') ?>" class="btn btn-fk btn-buy text-decoration-none"><i class="bi bi-lightning-fill"></i> BUY NOW</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: DETAILS -->
        <div class="col-md-7 col-lg-8 p-3 ps-md-4">
            
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="<?= url('/') ?>">Home</a> &nbsp;›&nbsp; 
                <a href="<?= route('products.index') ?>">Products</a> &nbsp;›&nbsp; 
                <?php if(!empty($p->category)): ?>
                    <a href="<?= route('products.index', ['category'=>$p->category]) ?>"><?= htmlspecialchars($p->category) ?></a> &nbsp;›&nbsp; 
                <?php endif; ?>
                <span><?= htmlspecialchars($p->name) ?></span>
            </div>

            <h1 class="product-title fw-normal"><?= htmlspecialchars($p->name) ?></h1>
            
            <div class="price-block">
                <span class="final-price">₹<?= number_format($final, 0) ?></span>
                <?php if($hasDisc): ?>
                    <span class="original-price">₹<?= number_format($price, 0) ?></span>
                    <span class="discount-pct"><?= $savePct ?>% off</span>
                <?php endif; ?>
            </div>

            <!-- Offers -->
            <div class="mb-3">
                <div class="fw-bold fs-6 mb-2">Available offers</div>
                <ul class="offers-list">
                    <li><i class="bi bi-tag-fill tag-icon"></i> <span><strong>Bank Offer</strong> 5% Unlimited Cashback on Axis Bank Credit Card</span></li>
                    <li><i class="bi bi-tag-fill tag-icon"></i> <span><strong>Bank Offer</strong> 10% off on SBI Credit Card, up to ₹1,500. On orders of ₹5,000</span></li>
                </ul>
            </div>

            <!-- Delivery -->
            <div class="row-section">
                <div class="section-head text-secondary">Delivery</div>
                <div class="row-content">
                    <div class="fw-medium mb-1">Standard Delivery <span class="text-secondary">|</span> <span class="text-success">Free</span></div>
                </div>
            </div>

            <!-- Description -->
            <?php if(!empty($p->description)): ?>
            <div class="mt-4 border p-3 rounded">
                <div class="fs-5 fw-bold mb-3">Description</div>
                <div class="desc-text text-secondary"><?= htmlspecialchars($p->description) ?></div>
            </div>
            <?php endif; ?>

            <!-- Specifications -->
            <?php if(!empty($p->specifications)): ?>
            <div class="mt-4 border rounded p-3">
                <div class="fs-5 fw-bold mb-3">Specifications</div>
                <?php
                    // Adjust spec handling if it's an array or object
                    $specs = $p->specifications;
                    if (is_string($specs)) $specs = json_decode($specs, true);
                    // If simple key-value, wrap in 'General'
                    if (is_array($specs) && !isset($specs[0]['key']) && !isset($specs['General'])) {
                         $specs = ['General' => $specs];
                    }
                ?>
                
                <?php foreach($specs as $cat => $items): ?>
                    <div class="mb-3">
                        <div class="spec-cat-title fs-6"><?= htmlspecialchars(is_string($cat) ? $cat : 'General') ?></div>
                        <table class="specs-table">
                            <?php if(is_array($items)): foreach($items as $k => $v): ?>
                                <?php 
                                    $key = is_array($v) ? ($v['key'] ?? $k) : $k;
                                    $val = is_array($v) ? ($v['value'] ?? '') : $v;
                                ?>
                                <tr>
                                    <td class="col-key"><?= htmlspecialchars($key) ?></td>
                                    <td class="col-val"><?= htmlspecialchars($val) ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
    
    <!-- Similar Products -->
    <?php if(!empty($simProducts)): ?>
        <div class="card mt-2 border-0 shadow-sm p-3">
            <h5 class="fw-bold mb-3">Similar Products</h5>
            <div class="row g-3">
            <?php foreach($simProducts as $sp): ?>
                <?php $spObj = is_array($sp) ? (object)$sp : $sp; ?>
                <div class="col-6 col-md-3">
                     <div class="p-2 border rounded text-center h-100 product-card-hover d-flex flex-column">
                         <a href="<?= route('product.show', ['id'=>$spObj->id]) ?>" class="text-decoration-none text-dark flex-grow-1">
                             <img src="<?= $img($spObj->image) ?>" class="img-fluid mb-2" style="max-height: 150px; object-fit: contain;">
                             <div class="text-truncate fw-medium"><?= htmlspecialchars($spObj->name) ?></div>
                             <div class="text-success fw-bold">₹<?= number_format($spObj->final_price ?? $spObj->price, 0) ?></div>
                         </a>
                     </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>
<?php
$content = ob_get_clean();

// 4. Client Scripts
ob_start();
?>
<script>
    function changeImage(src, el) {
        document.getElementById('mainImage').src = src;
        document.querySelectorAll('.thumb-box').forEach(function(b){ b.classList.remove('active') });
        el.classList.add('active');
    }
</script>
<?php
$scripts = ob_get_clean();

$title = htmlspecialchars($p->name ?? 'Product') . ' • MyStore';
include __DIR__ . '/../layouts/master.php';
?>