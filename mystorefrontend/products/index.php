<?php
// User Request: Fetch products directly in view
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../api.php';

// Fetch products
$response = callAPI("GET", "products"); // Removed leading slash
$products = $response['data'] ?? [];

// Helper class to mimic basic pagination if needed, or just handle as array
// For now, we will treat $products as a simple array and handle pagination manually if provided by API
$products_data = is_array($products) ? $products : [];
// If the API returns pagination metadata, it usually wraps 'data'. 
// If $response['data'] is the array, we are good.

// Mocking the 'count' and 'hasMorePages' check for the view logic below
$products_count = count($products_data);
$hasMorePages = false; // Implement logic if API provides 'next_page_url'
$nextPageUrl = '#'; 
if (isset($response['next_page_url'])) {
    $hasMorePages = true;
    $nextPageUrl = $response['next_page_url'];
}

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
            <div class="bg-white p-3 shadow-sm rounded-3" style="min-height: 80vh;">
                <!-- Filters Header -->
                <div class="border-bottom pb-2 mb-3">
                    <h5 class="fw-bold m-0">Filters</h5>
                </div>

                <!-- Search Section -->
                <div class="mb-4">
                    <h6 class="text-uppercase text-secondary fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">SEARCH</h6>
                    <form action="" method="GET" class="d-flex mt-2">
                        <!-- Preserve other query params -->
                        <?php foreach($_GET as $k => $v): if($k=='search' || $k=='page') continue; ?>
                            <input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($v) ?>">
                        <?php endforeach; ?>
                        
                        <div class="input-group input-group-sm">
                             <input type="text" name="search" class="form-control bg-light border-0" placeholder="Search products..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                             <button class="btn btn-primary text-white border-0"><i class="bi bi-search"></i></button>
                        </div>
                    </form>
                </div>

                <!-- Categories Section -->
                <div class="mb-2">
                    <h6 class="text-uppercase text-secondary fw-bold mb-2" style="font-size: 0.75rem; letter-spacing: 0.5px;">CATEGORIES</h6>
                    <div class="d-flex flex-column gap-1">
                        <?php
                        $cats = $categories ?? [];
                        if(is_array($cats) || $cats instanceof \Traversable) {
                            $cats = collect($cats)->map(function($c){ return is_object($c) ? ($c->name ?? 'Unknown') : $c; })->toArray();
                        } else {
                            $cats = [];
                        }
                        
                        foreach($cats as $cat):
                            $isActive = (request('category') == $cat);
                        ?>
                            <a href="<?= route('products.index', array_merge(request()->all(), ['category' => $cat])) ?>" 
                               class="d-flex align-items-center text-decoration-none py-1 px-2 rounded transition-all <?= $isActive ? 'bg-blue-50 text-primary fw-bold' : 'text-dark hover-bg-light' ?>"
                               style="font-size: 0.9rem;">
                               <i class="bi bi-chevron-right me-2 text-secondary" style="font-size: 0.7rem;"></i>
                               <span><?= $cat ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <style>
                .hover-bg-light:hover { background-color: #f8f9fa; color: #2563eb !important; }
            </style>
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
                <div class="card border-0 shadow-sm py-5 text-center mt-3">
                    <div class="card-body">
                        <img src="https://static-assets-web.flixcart.com/fk-p-linchpin-web/fk-cp-zion/img/error-no-search-results_2353c5.png" alt="No Results" class="mb-4" style="max-height: 160px;">
                        <h4 class="fw-bold fs-4 mb-2">Sorry, no results found!</h4>
                        <p class="text-secondary fs-6">We couldn't find any products matching your criteria.</p>
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