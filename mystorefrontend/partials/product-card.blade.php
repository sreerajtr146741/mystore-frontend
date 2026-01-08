@php
    $p = (object) ($p ?? []);
    $id = $p->id ?? 1;
    $name = $p->name ?? 'Unknown Product';
    $price = (float)($p->price ?? 0);
    $discounted = (float)($p->discounted_price ?? $p->final_price ?? $price);
    $stock = $p->stock ?? 0;
    $image = backend_img($p->image ?? null);
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
                
                {{-- Button removed as requested --}}
            </div>
        </div>
    </div>
</div>
