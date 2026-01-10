<?php if(!empty($users) && count($users) > 0): ?>
    <?php foreach($users as $user): ?>
    <?php 
        if(is_array($user)) $user = (object)$user; 
        $role = $user->role ?? 'buyer';
        $status = $user->status ?? 'active';
        $email = $user->email ?? '';
        $id = $user->id ?? 0;
        $name = $user->name ?? 'User';
        // Handle created_at formatting safely
        $joined = 'N/A';
        if(isset($user->created_at)) {
            // If it's a Carbon object or string
            if(is_string($user->created_at)) $joined = date('M d, Y', strtotime($user->created_at));
            else $joined = 'Date Err';
        }
    ?>
    <tr>
        <td><?= $id ?></td>
        <td><?= htmlspecialchars($name) ?></td>
        <td><?= htmlspecialchars($email) ?></td>
        <td><span class="badge bg-info"><?= ucfirst($role) ?></span></td>
        <td>
            <?php if($status == 'active'): ?>
                <span class="badge badge-active">Active</span>
            <?php elseif($status == 'suspended'): ?>
                <span class="badge badge-suspended">Suspended</span>
            <?php else: ?>
                <span class="badge badge-blocked">Blocked</span>
            <?php endif; ?>
        </td>
        <td><?= $joined ?></td>
        <td class="text-end">
            <?php if($email !== 'admin@store.com'): ?>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-info btn-sm view-user-btn" 
                            data-id="<?= $id ?>" 
                            title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>

                    <form method="POST" action="<?= route('admin.users.toggle', ['id'=>$id]) ?>" class="d-inline">
                        <?= csrf_field() ?>
                        <?= method_field('PATCH') ?>
                        <button class="btn <?= $status == 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?> btn-sm" 
                                title="<?= $status == 'active' ? 'Deactivate User' : 'Activate User' ?>">
                            <i class="bi bi-power"></i>
                        </button>
                    </form>

                    <form method="POST" action="<?= route('admin.users.destroy', ['id'=>$id]) ?>" class="d-inline">
                        <?= csrf_field() ?>
                        <?= method_field('DELETE') ?>
                        <button class="btn btn-dark btn-sm" title="Delete" onclick="return confirm('Delete this user permanently?')">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <span class="text-muted">Protected</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="7" class="text-center text-muted py-4">No users found</td>
    </tr>
<?php endif; ?>
