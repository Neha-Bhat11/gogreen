<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
require_once '../includes/db.php';

// Delete user
if (isset($_GET['delete_user'])) {
    $id = (int)$_GET['delete_user'];

    // Check if user has orders
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $stmt->execute([$id]);
    $order_count = $stmt->fetchColumn();

    if ($order_count > 0) {
        // Has orders - cannot delete fully
        // Just clear cart, wishlist and OTPs only
        $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM wishlist WHERE user_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM otp_codes WHERE user_id = ?")->execute([$id]);
        header("Location: manage_users.php?success=has_orders");
    } else {
        // No orders - safe to delete completely
        $pdo->prepare("DELETE FROM reviews WHERE user_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM wishlist WHERE user_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM otp_codes WHERE user_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        header("Location: manage_users.php?success=deleted");
    }
    exit();
}


// Edit user
if (isset($_POST['update_user'])) {
    $id     = (int)$_POST['user_id'];
    $name   = trim($_POST['name']);
    $email  = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $place  = trim($_POST['place']);
    $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, mobile=?, place=? WHERE id=?");
    $stmt->execute([$name, $email, $mobile, $place, $id]);
    header("Location: manage_users.php?success=updated");
    exit();
}

$users = $pdo->query("SELECT u.*,
                       COUNT(DISTINCT o.id) as total_orders
                       FROM users u
                       LEFT JOIN orders o ON u.id = o.user_id
                       GROUP BY u.id
                       ORDER BY u.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - GreenLife Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f7f0; font-family: 'Segoe UI', sans-serif; }
        .sidebar {
            background: #1b5e20; min-height: 100vh; padding: 20px 0;
            position: fixed; width: 240px; top: 0; left: 0; z-index: 100;
        }
        .sidebar-brand {
            color: white; font-size: 20px; font-weight: 700;
            padding: 15px 20px; border-bottom: 1px solid #2e7d32;
            margin-bottom: 10px;
        }
        .sidebar-link {
            display: block; color: #c8e6c9; padding: 12px 20px;
            text-decoration: none; font-size: 14px; transition: all 0.2s;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: #2e7d32; color: white; padding-left: 28px;
        }
        .sidebar-link i { margin-right: 8px; }
        .main-content { margin-left: 240px; padding: 25px; }
        .top-bar {
            background: white; border-radius: 12px; padding: 15px 20px;
            margin-bottom: 25px; display: flex;
            justify-content: space-between; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .table-box {
            background: white; border-radius: 15px;
            padding: 25px; box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }
        .form-control:focus {
            border-color: #66bb6a;
            box-shadow: 0 0 0 0.2rem rgba(102,187,106,0.25);
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">🌱 GreenLife Admin</div>
    <a href="dashboard.php" class="sidebar-link">
        <i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="manage_orders.php" class="sidebar-link">
        <i class="bi bi-bag"></i> Manage Orders</a>
    <a href="manage_products.php" class="sidebar-link">
        <i class="bi bi-grid"></i> Manage Products</a>
    <a href="manage_users.php" class="sidebar-link active">
        <i class="bi bi-people"></i> Manage Users</a>
    <a href="manage_reviews.php" class="sidebar-link">
        <i class="bi bi-star"></i> Manage Reviews</a>
    <a href="../index.php" class="sidebar-link">
        <i class="bi bi-globe"></i> View Website</a>
    <a href="admin_logout.php" class="sidebar-link" style="color:#ef9a9a;">
        <i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <div class="top-bar">
        <h5 style="margin:0; color:#1b5e20; font-weight:700;">👥 Manage Users</h5>
        <span style="color:#777;"><?= count($users) ?> registered users</span>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert <?= $_GET['success'] == 'has_orders' ? 'alert-warning' : 'alert-success' ?>">
        <?php
        if ($_GET['success'] == 'deleted')   echo '✅ User deleted successfully!';
        if ($_GET['success'] == 'updated')   echo '✅ User updated successfully!';
        if ($_GET['success'] == 'has_orders') echo '⚠️ User has existing orders so cannot be fully deleted. Cart and wishlist cleared only.';
        ?>
    </div>
<?php endif; ?>

    <div class="table-box">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead style="background:#e8f5e9;">
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Place</th>
                        <th>Orders</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                    <td style="font-size:13px;"><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['mobile']) ?></td>
                    <td><?= htmlspecialchars($u['place']) ?></td>
                    <td>
                        <span class="badge bg-success"><?= $u['total_orders'] ?></span>
                    </td>
                    <td style="font-size:12px; color:#777;">
                        <?= date('d M Y', strtotime($u['created_at'])) ?>
                    </td>
                    <td>
                        <button class="btn btn-outline-primary btn-sm"
                                onclick="editUser(<?= htmlspecialchars(json_encode($u)) ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="manage_users.php?delete_user=<?= $u['id'] ?>"
                           class="btn btn-outline-danger btn-sm"
                           onclick="return confirm('Delete this user? Their orders will remain.')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- EDIT USER MODAL -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#1565c0; color:white;">
                <h5 class="modal-title">✏️ Edit User</h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" id="edit_user_name"
                               class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit_user_email"
                               class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mobile</label>
                        <input type="text" name="mobile" id="edit_user_mobile"
                               class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Place</label>
                        <input type="text" name="place" id="edit_user_place"
                               class="form-control" required>
                    </div>
                    <button type="submit" name="update_user"
                            class="btn btn-primary w-100">
                        Update User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editUser(u) {
    document.getElementById('edit_user_id').value     = u.id;
    document.getElementById('edit_user_name').value   = u.name;
    document.getElementById('edit_user_email').value  = u.email;
    document.getElementById('edit_user_mobile').value = u.mobile;
    document.getElementById('edit_user_place').value  = u.place;
    new bootstrap.Modal(document.getElementById('editUserModal')).show();
}
</script>
</body>
</html>