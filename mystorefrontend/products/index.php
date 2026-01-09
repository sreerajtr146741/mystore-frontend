<?php
// 1. Capture Styles
ob_start();
?>
<style>
    /* Page specific styles */
    .hero{
        background: linear-gradient(120deg,#6d28d9 0%, #4c1d95 45%, #3b82f6 100%);
        color:#fff; border-radius:16px; padding:28px 22px;
        box-shadow:0 18px 40px rgba(76,29,149,.25);
    }
    .search-card{ margin-top:-22px; border:0; border-radius:16px; }
    .search-input-page, .search-select{ height:52px; border-radius:14px; }
    .btn-go{
        height:52px; border-radius:14px; font-weight:700; border:0; color:#07101a;
        background:linear-gradient(135deg,#22d3ee,#60a5fa);
        box-shadow:0 10px 22px rgba(96,165,250,.25);
    }
    .card-prod{
        position:relative; border:0; border-radius:16px; overflow:hidden; transition:.25s; background:#fff;
        box-shadow:0 6px 20px rgba(2,6,23,.06); cursor:pointer;
    }
    .card-prod:hover{ transform:translateY(-6px); box-shadow:0 16px 32px rgba(2,6,23,.12); }
    .img-fit{ width:100%; aspect-ratio:4/3; object-fit:cover; display:block; }
    .price{ font-weight:800; }
    .strike{ text-decoration: line-through; opacity:.7; margin-right:.4rem; }
    .badge-cat{ background:#fff3c4; color:#7c2d12; border:1px solid #fde68a; }
    .muted{ color:#64748b; }
    .ribbon{
        --c:#22c55e;
        position:absolute; top:12px; left:-40px; background:var(--c); color:#fff;
        padding:6px 60px; transform:rotate(-35deg); font-weight:700; box-shadow:0 6px 16px rgba(34,197,94,.3);
        letter-spacing:.5px; font-size:.85rem;
    }
    .stock-dot{ width:10px; height:10px; border-radius:50%; display:inline-block; margin-right:.4rem; }
    .pagination-container{ display:none; }
    
    /* Standard Banner Size */
    .banner-container {
        width: 100%;
        aspect-ratio: 5/1; /* Further reduced height (very thin banner) */
        overflow: hidden;
        position: relative;
    }
    
    .standard-banner {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: top center; /* Changed from center to top to handle top-heavy content in short banner */
    }
    
    /* Mobile optimization for banners */
    @media (max-width: 768px) {
        .banner-container {
            aspect-ratio: 2.5/1; /* Adjusted for mobile to not be too thin */
        }
    }
</style>
<?php
$styles = ob_get_clean();

// 2. Capture Main Content
ob_start();
?>
<div class="container-fluid py-4" style="background-color: #f1f3f6;">
    <div class="row g-3">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 col-xl-2" style="position: sticky; top: 80px; height: fit-content; z-index: 1;">
            <div class="p-0 bg-white" style="border-right: 1px solid #eee; min-height: 80vh;"> <!-- Simulated full height look -->
                
                <!-- Categories Header -->
                <div class="pt-2 pb-2 ps-1">
                    <h6 class="text-uppercase fw-bold text-dark mb-3" style="font-size: 0.9rem; letter-spacing: 0.5px; opacity: 0.8;">CATEGORIES</h6>
                </div>

                <!-- Category List -->
                <div class="d-flex flex-column">
                    <?php
                    $cats = $categories ?? [];
                    if(is_array($cats) || $cats instanceof \Traversable) {
                        $cats = collect($cats)->map(function($c){ return is_object($c) ? ($c->name ?? 'Unknown') : $c; })->toArray();
                    } else {
                        $cats = [];
                    }
                    
                    foreach($cats as $cat):
                    ?>
                        <a href="<?= route('products.index', array_merge(request()->all(), ['category' => $cat])) ?>" 
                           class="d-flex align-items-center text-decoration-none py-2 ps-1 pe-2 rounded transition-all <?= request('category') === $cat ? 'text-primary' : 'text-dark default-cat-link' ?>"
                           style="font-size: 1rem; margin-bottom: 2px;">
                           
                           <span class="me-2 text-secondary d-flex align-items-center justify-content-center" style="width: 20px; height: 20px;">
                                <i class="bi bi-chevron-right" style="font-size: 0.75rem; -webkit-text-stroke: 1px;"></i>
                           </span>
                           <span class="<?= request('category') === $cat ? 'fw-bold' : '' ?>" style="font-weight: 400;"><?= $cat ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <style>
            .default-cat-link:hover { color: #2563eb !important; }
            .default-cat-link:hover i { color: #2563eb !important; }
        </style>

        <!-- Product Grid -->
        <div class="col-lg-9 col-xl-10">
            
            <!-- Carousel Slider (Always Visible) -->
            <div id="homeCarousel" class="carousel slide mb-4 shadow-sm rounded-4 overflow-hidden" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="0" class="active"></button>
                    <?php if(isset($carouselSlides)): ?>
                        <?php foreach($carouselSlides as $idx => $slide): ?>
                            <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="<?= $idx + 1 ?>"></button>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="carousel-inner">
                    <!-- Static Slide 1: Home Banner -->
                    <div class="carousel-item active">
                        <div class="banner-container">
                            <img src="https://placehold.co/1200x250/6d28d9/ffffff?text=Super+Sale+Starts+Now" class="d-block standard-banner" alt="Super Sale">
                        </div>
                    </div>

                    <!-- Dynamic Slides (New Arrivals Style) -->
                    <?php if(isset($carouselSlides)): ?>
                        <?php foreach($carouselSlides as $slide): ?>
                            <div class="carousel-item">
                                <a href="<?= $slide['link'] ?>" class="d-block position-relative banner-container">
                                    <img src="<?= $slide['image'] ?>" class="d-block standard-banner" alt="<?= $slide['title'] ?>">
                                </a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#homeCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#homeCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>

            <!-- Sort Header -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body py-2 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="fw-bold fs-5">MyStore</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="me-2 small fw-bold text-muted">Sort By</span>
                        <select class="form-select form-select-sm border-0" style="width: auto; font-weight: 500; cursor: pointer;" onchange="updateSort(this.value)">
                            <option value="relevance" <?= request('sort') == 'relevance' ? 'selected' : '' ?>>Relevance</option>
                            <option value="popularity" <?= request('sort') == 'popularity' ? 'selected' : '' ?>>Popularity</option>
                            <option value="price_low_high" <?= request('sort') == 'price_low_high' ? 'selected' : '' ?>>Price -- Low to High</option>
                            <option value="price_high_low" <?= request('sort') == 'price_high_low' ? 'selected' : '' ?>>Price -- High to Low</option>
                            <option value="newest" <?= request('sort') == 'newest' ? 'selected' : '' ?>>Newest First</option>
                        </select>
                    </div>
                </div>
            </div>

            <script>
                function updateSort(value) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('sort', value);
                    url.searchParams.delete('page'); // Reset to page 1
                    window.location.href = url.toString();
                }
            </script>

            <?php if($products->count()): ?>
                <div class="row g-2" id="product-grid">
                     <!-- Using standard card layout, slightly compact for 'shop' feel -->
                    <?php include __DIR__ . '/../partials/product-list.php'; ?>
                </div>

                <!-- Loader for Infinite Scroll -->
                <div id="loading-spinner" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                
                <!-- Sentinel -->
                <div id="sentinel" style="height: 10px;"></div>

                <!-- Hidden Pagination Data -->
                <?php if($products->hasMorePages()): ?>
                    <div id="pagination-data" data-next-url="<?= $products->nextPageUrl() ?>" style="display:none;"></div>
                <?php endif; ?>
            <?php else: ?>
                <div class="card border-0 shadow-sm py-5 text-center">
                    <div class="card-body">
                        <img src="https://static-assets-web.flixcart.com/fk-p-linchpin-web/fk-cp-zion/img/error-no-search-results_2353c5.png" alt="No Results" class="mb-4" style="max-width: 200px;">
                        <h4>No products found</h4>
                        <p class="text-muted">We couldn't find any products matching your criteria.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();

// 3. Capture Scripts
ob_start();
?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Infinite Scroll Logic
        let nextUrl = document.getElementById('pagination-data')?.dataset.nextUrl;
        const sentinel = document.getElementById('sentinel');
        const spinner = document.getElementById('loading-spinner');
        const grid = document.getElementById('product-grid');
        let isLoading = false;

        if (sentinel && nextUrl) {
            const observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting && !isLoading && nextUrl) {
                    loadMoreProducts();
                }
            }, { rootMargin: '200px' });

            observer.observe(sentinel);

            function loadMoreProducts() {
                isLoading = true;
                spinner.classList.remove('d-none');

                fetch(nextUrl, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    spinner.classList.add('d-none');
                    if (html.trim().length > 0) {
                        grid.insertAdjacentHTML('beforeend', html);
                        
                        const currentUrl = new URL(nextUrl);
                        const currentPage = parseInt(currentUrl.searchParams.get('page') || 1);
                        currentUrl.searchParams.set('page', currentPage + 1);
                        nextUrl = currentUrl.toString();

                        isLoading = false;
                    } else {
                        observer.disconnect();
                        sentinel.remove();
                    }
                })
                .catch(err => {
                    console.error('Scroll Error:', err);
                    spinner.classList.add('d-none');
                    isLoading = false;
                });
            }
        }

        // 2. Card Click Logic (Global handler or specific)
        document.body.addEventListener('click', function(e) {
            const card = e.target.closest('.card-prod');
            if (card) {
                if (e.target.closest('.stop-click, button, input, a')) return;
                const href = card.getAttribute('data-href');
                if (href) window.location.href = href;
            }
        });
    });
</script>
<?php
$scripts = ob_get_clean();

// 4. Include Master Layout
include __DIR__ . '/../layouts/master.php';
?>