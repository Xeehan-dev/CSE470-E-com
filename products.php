<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/session.php';

// Get search query
$search = isset($_GET['q']) ? escape_string($_GET['q']) : '';

// Build query
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'active'";

if (!empty($search)) {
    $sql .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%' OR c.name LIKE '%$search%')";
}

$sql .= " ORDER BY p.created_at DESC";

$products = get_multiple_rows($sql);

$page_title = 'Products';
$show_search = true;
include __DIR__ . '/../helpers/header.php';
?>

<section class="container section">
  <div class="section-head">
    <h2>All Products</h2>
    <p class="muted">
      <?php if (!empty($search)): ?>
        Search results for "<?php echo htmlspecialchars($search); ?>"
      <?php else: ?>
        Browse our latest collection.
      <?php endif; ?>
    </p>
  </div>
  
  <?php if (empty($products)): ?>
    <div class="card">
      <p class="muted">No products found.</p>
    </div>
  <?php else: ?>
    <div class="grid products">
      <?php foreach ($products as $product): ?>
        <article class="card product">
          <div class="media placeholder"></div>
          <?php if (!empty($product['badge'])): ?>
            <div class="badge <?php echo $product['badge']; ?>"><?php echo ucfirst($product['badge']); ?></div>
          <?php endif; ?>
          <div class="card-body">
            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
            <p class="desc"><?php echo htmlspecialchars($product['description']); ?></p>
            <p class="price">$<?php echo number_format($product['price'], 2); ?></p>
            <div class="actions">
              <?php if (is_logged_in()): ?>
                <a class="btn btn-dark" href="/cart?action=add&product_id=<?php echo $product['id']; ?>">Add to Cart</a>
                <a class="btn btn-ghost" href="/wishlist?action=add&product_id=<?php echo $product['id']; ?>">Wishlist</a>
              <?php else: ?>
                <a class="btn btn-dark" href="/login">Login to Buy</a>
                <a class="btn btn-ghost" href="/login">Login for Wishlist</a>
              <?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/../helpers/footer.php'; ?>
