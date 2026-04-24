<?php
// Get cart count for badge
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_count = (int)($stmt->fetchColumn() ?? 0);
}
?>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="<?= $base ?>pages/home.php">🌱 GreenLife</a>
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link <?= $active=='home'?'active':'' ?>"
                       href="<?= $base ?>pages/home.php">
                        <i class="bi bi-house"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active=='about'?'active':'' ?>"
                       href="<?= $base ?>pages/about.php">
                        <i class="bi bi-info-circle"></i> About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active=='products'?'active':'' ?>"
                       href="<?= $base ?>pages/products.php">
                        <i class="bi bi-grid"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active=='cart'?'active':'' ?>"
                       href="<?= $base ?>pages/cart.php">
                        <i class="bi bi-cart3"></i> Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="badge bg-warning text-dark"
                                  style="font-size:10px;">
                                <?= $cart_count ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active=='wishlist'?'active':'' ?>"
                       href="<?= $base ?>pages/wishlist.php">
                        <i class="bi bi-heart"></i> Wishlist
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active=='orders'?'active':'' ?>"
                       href="<?= $base ?>pages/order_details.php">
                        <i class="bi bi-bag"></i> My Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $active=='profile'?'active':'' ?>"
                       href="<?= $base ?>pages/profile.php">
                        <i class="bi bi-person"></i> Profile
                    </a>
                </li>
                <li class="nav-item ms-lg-2">
                    <a class="nav-link" href="<?= $base ?>logout.php"
                       style="background:#2e7d32; border-radius:20px; padding:6px 16px;">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>