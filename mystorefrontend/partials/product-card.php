@php
    $p = (object) $p; // Ensure it is an object to prevent array access errors
    
    // Safely resolve image
    $photo = $p->image ?? null;
    
    // Price logic with safe defaults
    $price = (float)($p->price ?? 0);
    $finalPrice = (float)($p->discounted_price ?? $p->final_price ?? $price);
    $hasDiscount = $finalPrice < $price;
    $stock = $p->stock ?? 0;
    
    // Calculate save pct safely
    $savePct = 0;
    if ($hasDiscount && $price > 0) {
        $savePct = round((($price - $finalPrice) / $price) * 100);
    }

    // Determine URLs safely
    $id = $p->id ?? 0;
    $href = route('product.show', ['id' => $id]);
@endphp

<div class="card card-prod h-100" onclick="location.href='{{ $href }}'">
    {{-- Discount Ribbon --}}
    @if($hasDiscount)
        <div class="ribbon">{{ $savePct }}% OFF</div>
    @endif

    {{-- Image --}}
    @if($photo)
        <img class="img-fit" src="{{ $photo }}" alt="{{ $p->name ?? 'Product' }}">
    @else
        <div class="bg-light d-flex align-items-center justify-content-center h-100" style="min-height: 200px;">
            <i class="bi bi-image fs-1 text-muted"></i>
        </div>
    @endif

    <div class="card-body d-flex flex-column p-3">
        <h6 class="fw-bold mb-1 text-truncate" title="{{ $p->name ?? '' }}">{{ $p->name ?? 'Unnamed Product' }}</h6>

        <div class="mb-2">
            @if(isset($p->category))
                <span class="badge badge-cat text-truncate" style="max-width: 100%;">{{ $p->category }}</span>
            @endif
        </div>

        <div class="mt-auto">
            @if(isset($stock))
                <div class="small {{ $stock > 0 ? 'text-success' : 'text-danger' }} mb-2">
                    <span class="stock-dot" style="background:{{ $stock > 0 ? '#22c55e' : '#ef4444' }}"></span>
                    {{ $stock > 0 ? $stock.' in stock' : 'Out of stock' }}
                </div>
            @endif

            <div>
                @if($hasDiscount)
                    <div>
                        <span class="text-decoration-line-through text-muted me-2 small">₹{{ number_format($price) }}</span>
                        <span class="fw-bold text-success">₹{{ number_format($finalPrice) }}</span>
                        <span class="small text-danger ms-1">({{ $savePct }}% OFF)</span>
                    </div>
                @else
                    <span class="fw-bold">₹{{ number_format($price) }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
