@if(request()->ajax())
    @include('partials.product-list', ['products' => $products])
@else
    @extends('layouts.master')

    @section('title', 'Shop • MyStore')

    @push('styles')
    <style>
        /* Page Layout & Colors */
        .bg-page { background-color: #f1f3f6; }
        
        /* Sidebar Styling */
        .sidebar { position: sticky; top: 80px; height: fit-content; z-index: 10; }
        .filter-link { font-size: 0.95rem; text-decoration: none; transition: all 0.2s; }
        .filter-link:hover { color: #0d6efd !important; }
        .filter-active { font-weight: 700; color: #0d6efd; }

        /* Banner & Carousel */
        .banner-container {
            width: 100%;
            aspect-ratio: 5 / 1;
            overflow: hidden;
            position: relative;
        }
        .standard-banner {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top center;
        }
        @media (max-width: 768px) {
            .banner-container { aspect-ratio: 2.5 / 1; }
        }

        /* Product Cards & Interactions */
        .card-prod {
            position: relative; border: 0; border-radius: 16px; 
            overflow: hidden; transition: .25s; background: #fff;
            box-shadow: 0 6px 20px rgba(2,6,23,.06); cursor: pointer;
        }
        .card-prod:hover { transform: translateY(-6px); box-shadow: 0 16px 32px rgba(2,6,23,.12); }
        .price { font-weight: 800; }
        .badge-cat { background: #fff3c4; color: #7c2d12; border: 1px solid #fde68a; }
        .ribbon {
            --c: #22c55e;
            position: absolute; top: 12px; left: -40px; background: var(--c); color: #fff;
            padding: 6px 60px; transform: rotate(-35deg); font-weight: 700; 
            box-shadow: 0 6px 16px rgba(34,197,94,.3); letter-spacing: .5px; font-size: .85rem;
        }
    </style>
    @endpush

    @section('content')
    @php
        $categories = [
            'Mobile Phones','Laptops','Tablets','Smart Watches','Headphones','Cameras',
            'TVs','Gaming','Fashion','Shoes','Bags','Watches','Furniture','Home Decor',
            'Kitchen','Sports','Gym & Fitness','Vehicles','Cars','Bikes','Accessories',
            'Fruits','Vegetables','Groceries','Books','Toys','Other'
        ];
    @endphp

    <div class="container-fluid py-4 bg-page">
        <div class="row g-3">
            {{-- Sidebar Filters --}}
            <div class="col-lg-3 col-xl-2 sidebar">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="mb-0 fw-bold">Filters</h5>
                    </div>
                    <div class="card-body p-0">
                        {{-- Search Filter --}}
                        <div class="p-3 border-bottom">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3">Search</h6>
                            <form action="{{ route('products.index') }}" method="GET">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control form-control-sm" 
                                           placeholder="Search products..." value="{{ request('search') }}">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                                </div>
                            </form>
                        </div>

                        {{-- Categories Filter --}}
                        <div class="p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="text-uppercase text-muted small fw-bold mb-0">Categories</h6>
                                @if(request('category'))
                                    <a href="{{ route('products.index') }}" class="small text-decoration-none text-danger">Clear</a>
                                @endif
                            </div>
                            <div class="d-flex flex-column gap-2">
                                @foreach($categories as $cat)
                                    @php $isActive = request('category') === $cat; @endphp
                                    <a href="{{ route('products.index', array_merge(request()->except('page'), ['category' => $cat])) }}" 
                                       class="filter-link {{ $isActive ? 'filter-active' : 'text-dark' }}">
                                       <div class="d-flex align-items-center">
                                            @if($isActive)
                                                <i class="bi bi-check2 me-2"></i>
                                            @else
                                                <i class="bi bi-chevron-right me-2 text-muted" style="font-size: 0.75rem;"></i>
                                            @endif
                                            {{ $cat }}
                                       </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main Content --}}
            <div class="col-lg-9 col-xl-10">
                
                {{-- Carousel Slider --}}
                <div id="homeCarousel" class="carousel slide mb-4 shadow-sm rounded-4 overflow-hidden" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="0" class="active"></button>
                        @isset($carouselSlides)
                            @foreach($carouselSlides as $idx => $slide)
                                <button type="button" data-bs-target="#homeCarousel" data-bs-slide-to="{{ $idx + 1 }}"></button>
                            @endforeach
                        @endisset
                    </div>
                    <div class="carousel-inner">
                        {{-- Static Default Slide --}}
                        <div class="carousel-item active">
                            <div class="banner-container">
                                <img src="https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1200&q=80" 
                                     class="d-block standard-banner" alt="Super Sale">
                            </div>
                        </div>

                        {{-- Dynamic Slides --}}
                        @if(isset($carouselSlides))
                            @foreach($carouselSlides as $slide)
                                <div class="carousel-item">
                                    <a href="{{ $slide['link'] }}" class="d-block position-relative banner-container">
                                        <img src="{{ $slide['image'] }}" class="d-block standard-banner" alt="{{ $slide['title'] }}">
                                    </a>
                                </div>
                            @endforeach
                        @endif
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

                {{-- Sort Header --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body py-2 d-flex align-items-center justify-content-between">
                        <div><span class="fw-bold fs-5">MyStore</span></div>
                        <div class="d-flex align-items-center">
                            <span class="me-2 small fw-bold text-muted">Sort By</span>
                            <select class="form-select form-select-sm border-0" onchange="updateSort(this.value)" 
                                    style="width: auto; font-weight: 500; cursor: pointer;">
                                <option value="relevance" {{ request('sort') == 'relevance' ? 'selected' : '' }}>Relevance</option>
                                <option value="popularity" {{ request('sort') == 'popularity' ? 'selected' : '' }}>Popularity</option>
                                <option value="price_low_high" {{ request('sort') == 'price_low_high' ? 'selected' : '' }}>Price -- Low to High</option>
                                <option value="price_high_low" {{ request('sort') == 'price_high_low' ? 'selected' : '' }}>Price -- High to Low</option>
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest First</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Product Grid --}}
                @if($products->count())
                    <div class="row g-2" id="product-grid">
                        @include('partials.product-list', ['products' => $products])
                    </div>

                    {{-- Infinite Scroll Elements --}}
                    <div id="loading-spinner" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div id="sentinel" style="height: 10px;"></div>

                    @if($products->hasMorePages())
                        <div id="pagination-data" data-next-url="{{ $products->nextPageUrl() }}" style="display:none;"></div>
                    @endif
                @else
                    <div class="card border-0 shadow-sm py-5 text-center">
                        <div class="card-body">
                            <img src="https://static-assets-web.flixcart.com/fk-p-linchpin-web/fk-cp-zion/img/error-no-search-results_2353c5.png" 
                                 alt="No Results" class="mb-4" style="max-width: 200px;">
                            <h4>Sorry, no results found!</h4>
                            <p class="text-muted">Please check the spelling or try searching for something else</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endsection

    @push('scripts')
    <script>
        // Sort Handler
        function updateSort(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', value);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Card Click Event
            document.body.addEventListener('click', function(e) {
                const card = e.target.closest('.card-prod');
                if (card && !e.target.closest('.stop-click, button, input, a')) {
                    const href = card.getAttribute('data-href');
                    if (href) window.location.href = href;
                }
            });

            // Infinite Scroll
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
                    .then(r => r.text())
                    .then(html => {
                        spinner.classList.add('d-none');
                        if (html.trim().length > 0) {
                            grid.insertAdjacentHTML('beforeend', html);
                            
                            // Update Next URL for subsequent pages
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
        });
    </script>
    @endpush
@endif