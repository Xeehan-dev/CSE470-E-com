<?php
// Homepage Controller
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/session.php';

// Get featured products (products with badges or newest)
$featured_sql = "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.status = 'active' 
                 ORDER BY p.badge DESC, p.created_at DESC 
                 LIMIT 3";

$featured_products = get_multiple_rows($featured_sql);

// Get all products for the main grid
$products_sql = "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 WHERE p.status = 'active' 
                 ORDER BY p.created_at DESC 
                 LIMIT 6";

$products = get_multiple_rows($products_sql);

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $name = is_logged_in() ? $_SESSION['user_name'] : 'Anonymous';
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 5;
    $comment = escape_string($_POST['comment']);
    $sql = "INSERT INTO comments (name, rating, comment) VALUES ('$name', $rating, '$comment')";
    try {
        execute_query($sql);
        // Set a success message in session
        $_SESSION['success_msg'] = "Comment submitted!";
    } catch (Exception $e) {
        $_SESSION['error_msg'] = "Failed to submit comment.";
    }
    // Redirect to avoid duplicate submission
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Show messages if set
$success_msg = isset($_SESSION['success_msg']) ? $_SESSION['success_msg'] : '';
$error_msg = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// Fetch comments
$comments = get_multiple_rows("SELECT * FROM comments ORDER BY id DESC LIMIT 10");

$page_title = 'Home';
$show_search = true;
include __DIR__ . '/../helpers/header.php';
?>

<section class="hero container">
  <div class="hero-copy">
    <h1>Discover your next favorite thing</h1>
    <p>Hand‑picked products, clean design, and a smooth, distraction‑free experience.</p>
    <div class="cta">
      <a class="btn btn-primary" href="/products">Shop New Arrivals</a>
      <?php if (is_logged_in()): ?>
        <a class="btn btn-ghost" href="/wishlist">View Wishlist</a>
      <?php else: ?>
        <a class="btn btn-ghost" href="/login">Login to Wishlist</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="hero-art" aria-hidden="true"></div>
</section>

<section class="container section">
  <div class="section-head">
    <h2>Featured Picks</h2>
    <a href="/products" class="link">See all →</a>
  </div>
  <div class="grid products">
    <?php foreach ($featured_products as $product): ?>
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
</section>

<section class="container section">
  <div class="section-head">
    <h2>Latest Products</h2>
    <a href="/products" class="link">Browse all →</a>
  </div>
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
</section>

<section class="container section comments">
  <h2>Customer Comments</h2>
  <?php if (!empty($success_msg)): ?>
    <div class="alert alert-success"><?php echo $success_msg; ?></div>
  <?php elseif (!empty($error_msg)): ?>
    <div class="alert alert-danger"><?php echo $error_msg; ?></div>
  <?php endif; ?>
  <form class="comment-form" action="" method="POST">
    <label for="cmt">Leave a comment</label>
    <textarea id="cmt" name="comment" rows="4" placeholder="Share your experience…" required></textarea>
    <label for="rating">Rating:</label>
    <select name="rating" id="rating">
      <option value="5">5★</option>
      <option value="4">4★</option>
      <option value="3">3★</option>
      <option value="2">2★</option>
      <option value="1">1★</option>
    </select>
    <button class="btn btn-primary" type="submit">Submit</button>
  </form>
  <div class="comment-list">
    <?php foreach ($comments as $cmt): ?>
      <div class="comment">
        <strong><?php echo htmlspecialchars($cmt['name']); ?></strong>
        <span>• <?php echo $cmt['rating']; ?>★</span>
        <p><?php echo htmlspecialchars($cmt['comment']); ?></p>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php include __DIR__ . '/../helpers/footer.php'; ?>
