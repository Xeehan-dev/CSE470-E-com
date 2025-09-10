<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/session.php';

// Require login for wishlist
require_login();

$user_id = get_current_user_id();
$message = '';
$message_type = '';

// Handle wishlist actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
    
    if ($action === 'add' && $product_id > 0) {
        // Check if product exists and is active
        $product_sql = "SELECT id, name FROM products WHERE id = $product_id AND status = 'active'";
        $product = get_single_row($product_sql);
        
        if ($product) {
            // Check if already in wishlist
            $check_sql = "SELECT id FROM wishlist WHERE user_id = $user_id AND product_id = $product_id";
            if (get_row_count($check_sql) == 0) {
                // Add to wishlist
                $insert_sql = "INSERT INTO wishlist (user_id, product_id) VALUES ($user_id, $product_id)";
                execute_query($insert_sql);
                $message = "✅ " . htmlspecialchars($product['name']) . " added to wishlist!";
                $message_type = 'success';
            } else {
                $message = "⚠️ " . htmlspecialchars($product['name']) . " is already in your wishlist!";
                $message_type = 'warning';
            }
        }
    } elseif ($action === 'remove' && $product_id > 0) {
        $delete_sql = "DELETE FROM wishlist WHERE user_id = $user_id AND product_id = $product_id";
        execute_query($delete_sql);
        $message = "✅ Item removed from wishlist!";
        $message_type = 'success';
    } elseif ($action === 'move_to_cart' && $product_id > 0) {
        // Move from wishlist to cart
        $delete_sql = "DELETE FROM wishlist WHERE user_id = $user_id AND product_id = $product_id";
        execute_query($delete_sql);
        
        // Add to cart
        $check_cart_sql = "SELECT id, quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id";
        $cart_item = get_single_row($check_cart_sql);
        
        if ($cart_item) {
            $new_quantity = $cart_item['quantity'] + 1;
            $update_sql = "UPDATE cart SET quantity = $new_quantity WHERE id = " . $cart_item['id'];
            execute_query($update_sql);
        } else {
            $insert_sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, 1)";
            execute_query($insert_sql);
        }
        
        $message = "✅ Item moved to cart!";
        $message_type = 'success';
    }
}

// Get wishlist items
$wishlist_sql = "SELECT w.id, p.id as product_id, p.name, p.price, p.description, p.badge 
                 FROM wishlist w 
                 JOIN products p ON w.product_id = p.id 
                 WHERE w.user_id = $user_id 
                 ORDER BY w.created_at DESC";

$wishlist_items = get_multiple_rows($wishlist_sql);

$page_title = 'Wishlist';
$show_search = true;
include __DIR__ . '/../helpers/header.php';
?>

<section class="container section">
  <div class="section-head">
    <h2>Your Wishlist</h2>
    <p class="muted">Items you're keeping an eye on.</p>
  </div>
  
  <?php if (!empty($message)): ?>
    <div class="message <?php echo $message_type; ?>">
      <?php echo $message; ?>
    </div>
  <?php endif; ?>
  
  <?php if (empty($wishlist_items)): ?>
    <div class="card">
      <p class="muted">Your wishlist is empty. <a href="/products" class="link">Start shopping</a></p>
    </div>
  <?php else: ?>
    <div class="stack list-items">
      <?php foreach ($wishlist_items as $item): ?>
        <article class="list-item card">
          <div class="media small placeholder"></div>
          <div class="li-body">
            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
            <p class="desc"><?php echo htmlspecialchars($item['description']); ?></p>
            <p class="price">$<?php echo number_format($item['price'], 2); ?></p>
          </div>
          <div class="li-actions">
            <a class="btn btn-dark" href="/cart?action=add&product_id=<?php echo $item['product_id']; ?>">Move to Cart</a>
            <a class="btn btn-ghost danger" href="/wishlist?action=remove&product_id=<?php echo $item['product_id']; ?>">Remove</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/../helpers/footer.php'; ?>
