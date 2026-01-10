<?php foreach($products as $p): 
    $img = $p->image ? (filter_var($p->image, FILTER_VALIDATE_URL) ? $p->image : '/storage/'.ltrim($p->image, '/')) : null;
    $isActive = (isset($p->status) && $p->status === 'active') || (isset($p->is_active) && $p->is_active == 1);
?>
<tr>
    <td class="ps-4">#<?= $p->id ?></td>
    <td>
        <?php if($img): ?>
            <img src="<?= $img ?>" class="rounded-2 border" width="48" height="48" style="object-fit: cover;" alt="Product">
        <?php else: ?>
            <div class="rounded-2 border bg-light d-flex align-items-center justify-content-center text-secondary small" style="width:48px; height:48px;">N/A</div>
        <?php endif; ?>
    </td>
    <td class="fw-medium"><?= $p->name ?></td>
    <td><span class="badge bg-light text-dark border fw-normal"><?= $p->category ?></span></td>
    <td class="text-nowrap">₹<?= number_format($p->price, 2) ?></td>
    <td><?= $p->stock ?></td>
    <td>
        <?php if($isActive): ?>
            <span class="badge bg-success bg-opacity-10 text-success px-3 rounded-pill">Active</span>
        <?php else: ?>
            <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 rounded-pill">Hidden</span>
        <?php endif; ?>
    </td>
    <td><small class="text-muted"><?= $p->seller_name ?? ($p->user->name ?? '—') ?></small></td>
    <td class="text-end pe-4">
        <a href="<?= route('admin.products.edit', ['id' => $p->id]) ?>" class="btn btn-sm btn-outline-primary rounded-pill me-1"><i class="bi bi-pencil"></i></a>
        <form method="POST" action="<?= route('admin.products.destroy', ['id' => $p->id]) ?>" class="d-inline">
             <?= csrf_field() ?>
             <?= method_field('DELETE') ?>
            <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Delete this product?')">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
<?php if($products->isEmpty()): ?>
<tr><td colspan="9" class="text-center py-4 text-muted">No products found.</td></tr>
<?php endif; ?>
