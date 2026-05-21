<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
require_once '../includes/db.php';

// Add category
if (isset($_POST['add_category'])) {
    $name     = trim($_POST['cat_name']);
    $type     = $_POST['cat_type'];
    $season   = $_POST['cat_season'];
    $location = $_POST['cat_location'];
    $stmt = $pdo->prepare("INSERT INTO categories (name, type, season, location) 
                            VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $type, $season, $location]);
    header("Location: manage_products.php?success=category_added");
    exit();
}

// Add product
if (isset($_POST['add_product'])) {
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price       = (float)$_POST['price'];
    $stock       = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    $image       = null;

    // Handle image upload
    if (!empty($_FILES['product_image']['name'])) {
        $cat = $pdo->prepare("SELECT type FROM categories WHERE id = ?");
        $cat->execute([$category_id]);
        $cat_type  = $cat->fetchColumn();
        $ext       = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $filename  = strtolower(str_replace(' ', '_', $name)) . '.' . $ext;
        $upload_dir = "../assets/images/products/$cat_type/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $filename);
        $image = "$cat_type/$filename";
    }

    $stmt = $pdo->prepare("INSERT INTO products 
                            (name, description, price, stock, category_id, image)
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $description, $price, $stock, $category_id, $image]);
    header("Location: manage_products.php?success=added");
    exit();
}

// Delete product
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    try {
        // Get image path before deleting
        $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        // Step 1 - Remove from wishlists
        $pdo->prepare("DELETE FROM wishlist WHERE product_id = ?")->execute([$id]);

        // Step 2 - Remove from carts
        $pdo->prepare("DELETE FROM cart WHERE product_id = ?")->execute([$id]);

        // Step 3 - Remove reviews
        $pdo->prepare("DELETE FROM reviews WHERE product_id = ?")->execute([$id]);

        // Step 4 - Get all order_items for this product
        $stmt = $pdo->prepare("SELECT DISTINCT order_id FROM order_items WHERE product_id = ?");
        $stmt->execute([$id]);
        $affected_orders = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Step 5 - Delete order items for this product
        $pdo->prepare("DELETE FROM order_items WHERE product_id = ?")->execute([$id]);

        // Step 6 - Delete orders that now have NO items
        if (!empty($affected_orders)) {
            foreach ($affected_orders as $order_id) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM order_items WHERE order_id = ?");
                $stmt->execute([$order_id]);
                $remaining = $stmt->fetchColumn();
                if ($remaining == 0) {
                    $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$order_id]);
                }
            }
        }

        // Step 7 - Delete the product itself
        $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);

        // Step 8 - Delete image file if exists
        if ($product && $product['image']) {
            $imgFile = "../assets/images/products/" . $product['image'];
            if (file_exists($imgFile)) {
                unlink($imgFile);
            }
        }

        header("Location: manage_products.php?success=deleted");

    } catch (Exception $e) {
        header("Location: manage_products.php?error=" . urlencode($e->getMessage()));
    }
    exit();
}

// Update product
if (isset($_POST['update_product'])) {
    $id          = (int)$_POST['product_id'];
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price       = (float)$_POST['price'];
    $stock       = (int)$_POST['stock'];

    // Get current image
    $stmt = $pdo->prepare("SELECT image, category_id FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $current = $stmt->fetch();
    $image = $current['image'];

    // Handle new image upload
    if (!empty($_FILES['edit_image']['name'])) {
        // Get category type for folder
        $stmt = $pdo->prepare("SELECT type FROM categories WHERE id = ?");
        $stmt->execute([$current['category_id']]);
        $cat_type = $stmt->fetchColumn();

        $ext      = pathinfo($_FILES['edit_image']['name'], PATHINFO_EXTENSION);
        $filename = strtolower(str_replace(' ', '_', $name)) . '.' . $ext;
        $upload_dir = "../assets/images/products/$cat_type/";

        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        // Delete old image if exists
        if ($image && file_exists("../assets/images/products/" . $image)) {
            unlink("../assets/images/products/" . $image);
        }

        move_uploaded_file($_FILES['edit_image']['tmp_name'], $upload_dir . $filename);
        $image = "$cat_type/$filename";
    }

    // Remove image if checkbox checked
    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        if ($image && file_exists("../assets/images/products/" . $image)) {
            unlink("../assets/images/products/" . $image);
        }
        $image = null;
    }

    $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, image=? WHERE id=?");
    $stmt->execute([$name, $description, $price, $stock, $image, $id]);
    header("Location: manage_products.php?success=updated");
    exit();
}

$products   = $pdo->query("SELECT p.*, c.name as cat_name FROM products p
                            JOIN categories c ON p.category_id = c.id
                            ORDER BY p.id DESC")->fetchAll();
$categories = $pdo->query("SELECT * FROM categories ORDER BY type, name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - GreenLife Admin</title>
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
            padding: 15px 20px; border-bottom: 1px solid #2e7d32; margin-bottom: 10px;
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
        .form-control:focus, .form-select:focus {
            border-color: #66bb6a;
            box-shadow: 0 0 0 0.2rem rgba(102,187,106,0.25);
        }
        .product-thumb {
            width: 45px; height: 45px; border-radius: 8px;
            object-fit: cover; background: #e8f5e9;
            display: flex; align-items: center;
            justify-content: center; font-size: 22px;
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">🌱 Green Life Admin</div>
    <a href="dashboard.php" class="sidebar-link">
        <i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="manage_orders.php" class="sidebar-link">
        <i class="bi bi-bag"></i> Manage Orders</a>
    <a href="manage_products.php" class="sidebar-link active">
        <i class="bi bi-grid"></i> Manage Products</a>
    <a href="manage_users.php" class="sidebar-link">
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
        <h5 style="margin:0; color:#1b5e20; font-weight:700;">🌱 Manage Products</h5>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-success btn-sm"
                    data-bs-toggle="modal" data-bs-target="#addCatModal">
                + Add Category
            </button>
            <button class="btn btn-success btn-sm"
                    data-bs-toggle="modal" data-bs-target="#addModal">
                + Add Product
            </button>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php
        if ($_GET['success'] == 'added')          echo '✅ Product added successfully!';
        elseif ($_GET['success'] == 'updated')    echo '✅ Product updated successfully!';
        elseif ($_GET['success'] == 'deleted')    echo '✅ Product and all related data deleted successfully!';
        elseif ($_GET['success'] == 'deactivated') echo '⚠️ Product has existing orders — stock set to 0.';
        elseif ($_GET['success'] == 'category_added') echo '✅ Category added successfully!';
        ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger">
        ❌ Error: <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

    <div class="table-box">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead style="background:#e8f5e9;">
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $p):
                   $icon = '🌱';
if (strpos(strtolower($p['cat_name']), 'flower') !== false) $icon = '🌸';
elseif (strpos(strtolower($p['cat_name']), 'vegetable') !== false ||
        strpos(strtolower($p['cat_name']), 'leafy') !== false ||
        strpos(strtolower($p['cat_name']), 'creeper') !== false ||
        strpos(strtolower($p['cat_name']), 'root') !== false) $icon = '🥦';
elseif (strpos(strtolower($p['cat_name']), 'fruit') !== false) $icon = '🍎';
elseif (strpos(strtolower($p['cat_name']), 'plant') !== false ||
        strpos(strtolower($p['cat_name']), 'succulent') !== false ||
        strpos(strtolower($p['cat_name']), 'bonsai') !== false ||
        strpos(strtolower($p['cat_name']), 'herb') !== false) $icon = '🪴';
                ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td>
                        <?php if ($p['image'] && file_exists('../assets/images/products/' . $p['image'])): ?>
                            <img src="../assets/images/products/<?= htmlspecialchars($p['image']) ?>"
                                 style="width:45px; height:45px; border-radius:8px; object-fit:cover;">
                        <?php else: ?>
                            <div class="product-thumb"><?= $icon ?></div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($p['name']) ?></strong><br>
                        <small style="color:#999; font-size:11px;">
                            <?= substr(htmlspecialchars($p['description']), 0, 40) ?>...
                        </small>
                    </td>
                    <td><span class="badge bg-success"><?= htmlspecialchars($p['cat_name']) ?></span></td>
                    <td style="color:#e65100; font-weight:600;">
                        ₹<?= number_format($p['price'], 2) ?>
                    </td>
                    <td>
                        <span class="badge <?= $p['stock'] < 10 ? 'bg-danger' : 'bg-success' ?>">
                            <?= $p['stock'] ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-outline-primary btn-sm"
                                onclick="editProduct(<?= htmlspecialchars(json_encode($p)) ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="manage_products.php?delete=<?= $p['id'] ?>"
                           class="btn btn-outline-danger btn-sm"
                           onclick="return confirm('Delete this product?')">
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

<!-- ADD PRODUCT MODAL -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#1b5e20; color:white;">
                <h5 class="modal-title">➕ Add New Product</h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Price (₹)</label>
                            <input type="number" name="price" class="form-control"
                                   step="0.01" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3 mt-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>">
                                    <?= htmlspecialchars($cat['name']) ?>
                                    (<?= $cat['type'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="product_image" class="form-control"
                               accept="image/*">
                        <small class="text-muted">
                            JPG/PNG only. Leave empty to use default icon.
                        </small>
                    </div>
                    <button type="submit" name="add_product" class="btn btn-success w-100">
                        Add Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- EDIT PRODUCT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#1565c0; color:white;">
                <h5 class="modal-title">✏️ Edit Product</h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" id="edit_id">

                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" name="name" id="edit_name"
                               class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_desc"
                                  class="form-control" rows="3"></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Price (₹)</label>
                            <input type="number" name="price" id="edit_price"
                                   class="form-control" step="0.01" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" id="edit_stock"
                                   class="form-control" required>
                        </div>
                    </div>

                    <!-- Current Image Preview -->
                    <div class="mb-3 mt-3" id="current_img_box">
                        <label class="form-label">Current Image</label>
                        <div id="current_img_preview"
                             style="background:#f5f5f5; border-radius:8px;
                                    padding:10px; text-align:center;">
                        </div>
                        <div class="form-check mt-2">
                            <input type="checkbox" name="remove_image"
                                   value="1" class="form-check-input"
                                   id="remove_img_check">
                            <label class="form-check-label text-danger"
                                   for="remove_img_check">
                                🗑️ Remove current image
                            </label>
                        </div>
                    </div>

                    <!-- New Image Upload -->
                    <div class="mb-3">
                        <label class="form-label">Upload New Image</label>
                        <input type="file" name="edit_image"
                               class="form-control" accept="image/*"
                               onchange="previewNewImage(this)">
                        <small class="text-muted">
                            Leave empty to keep current image
                        </small>
                    </div>

                    <!-- New image preview -->
                    <div id="new_img_preview" style="display:none;
                         text-align:center; margin-bottom:10px;">
                        <img id="new_img_tag"
                             style="max-height:120px; border-radius:8px;
                                    object-fit:cover;">
                        <div style="font-size:12px; color:#777; margin-top:5px;">
                            New image preview
                        </div>
                    </div>

                    <button type="submit" name="update_product"
                            class="btn btn-primary w-100">
                        Update Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- ADD CATEGORY MODAL -->
<div class="modal fade" id="addCatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#2e7d32; color:white;">
                <h5 class="modal-title">➕ Add New Category</h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="cat_name" class="form-control"
                               placeholder="e.g. Monsoon Flowers" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="cat_type" class="form-select" required>
                            <option value="flowers">Flowers</option>
                            <option value="vegetables">Vegetables</option>
                            <option value="fruits">Fruits</option>
                            
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Season</label>
                        <select name="cat_season" class="form-select">
                            <option value="all">All Season</option>
                            <option value="summer">Summer</option>
                            <option value="winter">Winter</option>
                            <option value="rainy">Rainy</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <select name="cat_location" class="form-select">
                            <option value="both">Both</option>
                            <option value="indoor">Indoor</option>
                            <option value="outdoor">Outdoor</option>
                        </select>
                    </div>
                    <button type="submit" name="add_category"
                            class="btn btn-success w-100">
                        Add Category
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editProduct(p) {
    document.getElementById('edit_id').value    = p.id;
    document.getElementById('edit_name').value  = p.name;
    document.getElementById('edit_desc').value  = p.description;
    document.getElementById('edit_price').value = p.price;
    document.getElementById('edit_stock').value = p.stock;

    // Reset checkboxes and previews
    document.getElementById('remove_img_check').checked = false;
    document.getElementById('new_img_preview').style.display = 'none';

    // Show current image preview
    var preview = document.getElementById('current_img_preview');
    if (p.image) {
        preview.innerHTML = '<img src="../assets/images/products/' + p.image + '"' +
            ' style="max-height:100px; border-radius:8px; object-fit:cover;">' +
            '<div style="font-size:12px;color:#777;margin-top:5px;">' + p.image + '</div>';
    } else {
        preview.innerHTML = '<div style="color:#999; font-size:13px; padding:10px;">No image — using default icon</div>';
    }

    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function previewNewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('new_img_tag').src = e.target.result;
            document.getElementById('new_img_preview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>