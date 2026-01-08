@php
    $p = (object) ($p ?? []);
    $id = $p->id ?? 1;
    $name = $p->name ?? 'Unknown Product';
    $price = (float)($p->price ?? 0);
    $discounted = (float)($p->discounted_price ?? $p->final_price ?? $price);
    $stock = $p->stock ?? 0;
    $image = $p->image ?? 'https://via.placeholder.com/400?text=No+Image';
    $category = $p->category ?? 'General';
    $isNew = $p->is_new ?? false;
    
    $hasDiscount = $price > $discounted;
    $discount = 0;
    if($hasDiscount && $price > 0) {
        $discount = round((($price - $discounted) / $price) * 100);
    }
    
    // Check if $p is a "Mock" object or just stdClass for route generation
    // If it's a simple stdClass, route('products.show', $p) might fail if it expects an ID. 
    // We safe-guard by passing ID explicitly.
    $link = route('product.show', ['id' => $id]);
@endphp

<div class="col-6 col-md-4 col-lg-3">
    <div class="card card-prod h-100" onclick="location.href='{{ $link }}'">
        <div class="position-relative">
            <img src="{{ $image }}" class="img-fit" alt="{{ $name }}">
            
            @if($discount > 0)
                <div class="ribbon">{{ $discount }}% OFF</div>
            @endif
            
            @if($isNew)
                <span class="position-absolute top-0 end-0 m-2 badge bg-primary">NEW</span>
            @endif
        </div>
        
        <div class="card-body p-3 d-flex flex-column">
            <div class="small text-muted mb-1">{{ $category }}</div>
            <h6 class="card-title fw-bold text-truncate mb-1" title="{{ $name }}">{{ $name }}</h6>
            
            <div class="mt-auto pt-2">
                <div class="d-flex align-items-center mb-2">
                    <span class="price h5 mb-0">₹{{ number_format($discounted) }}</span>
                    @if($discount > 0)
                        <span class="text-muted text-decoration-line-through ms-2 small">₹{{ number_format($price) }}</span>
                    @endif
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    @if($stock > 0)
                        <small class="text-success fw-bold"><i class="bi bi-circle-fill stock-dot"></i> In Stock</small>
                    @else
                        <small class="text-danger fw-bold"><i class="bi bi-x-circle-fill me-1"></i> Out of Stock</small>
                    @endif
                    
                    <button class="btn btn-sm btn-outline-primary rounded-circle shadow-sm p-2 lh-1 stop-click" 
                            onclick="event.stopPropagation(); location.href='{{ route('cart.add', ['id' => $id]) }}'"
                            title="Add to Cart">
                        <i class="bi bi-bag-plus-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
