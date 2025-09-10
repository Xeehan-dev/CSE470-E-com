<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/session.php';

// Require login for payment
require_login();

$user_id = get_current_user_id();
$message = '';
$message_type = '';

// Get cart items for checkout
$cart_sql = "SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.stock_quantity 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.user_id = $user_id";

$cart_items = get_multiple_rows($cart_sql);

// Calculate totals
$subtotal = 0;
$shipping = 3.00;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$total = $subtotal + $shipping;

// Handle payment submission
if (isset($_POST['process_payment'])) {
    if (empty($cart_items)) {
        $message = "❌ Your cart is empty.";
        $message_type = 'error';
    } else {
        // Validate payment form
        $cardholder = escape_string($_POST['cardholder']);
        $card_number = escape_string($_POST['card_number']);
        $expiry = escape_string($_POST['expiry']);
        $cvv = escape_string($_POST['cvv']);
        $billing_address = escape_string($_POST['billing_address']);
        
        if (empty($cardholder) || empty($card_number) || empty($expiry) || empty($cvv) || empty($billing_address)) {
            $message = "❌ All payment fields are required.";
            $message_type = 'error';
        } else {
            try {
                // Generate order number
                $order_number = 'ORD' . date('Ymd') . rand(1000, 9999);
                
                // Create order
                $order_sql = "INSERT INTO orders (user_id, order_number, total_amount, billing_address, payment_status, order_status) 
                             VALUES ($user_id, '$order_number', $total, '$billing_address', 'paid', 'processing')";
                execute_query($order_sql);
                
                $order_id = get_last_insert_id();
                
                // Add order items
                foreach ($cart_items as $item) {
                    $item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                                VALUES ($order_id, " . $item['product_id'] . ", " . $item['quantity'] . ", " . $item['price'] . ")";
                    execute_query($item_sql);
                    
                    // Update product stock
                    $new_stock = $item['stock_quantity'] - $item['quantity'];
                    $update_stock_sql = "UPDATE products SET stock_quantity = $new_stock WHERE id = " . $item['product_id'];
                    execute_query($update_stock_sql);
                }
                
                // Clear cart
                $clear_cart_sql = "DELETE FROM cart WHERE user_id = $user_id";
                execute_query($clear_cart_sql);
                
                $message = "✅ Payment processed successfully! Order #$order_number has been placed.";
                $message_type = 'success';
                
                // Redirect to order confirmation
                header("Location: order_confirmation.php?order_id=$order_id");
                exit();
                
            } catch (Exception $e) {
                $message = "❌ Error processing payment: " . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

$page_title = 'Payment';
$show_search = false;
include __DIR__ . '/../helpers/header.php';
?>

<section class="container section">
  <div class="section-head">
    <h2>Payment</h2>
    <p class="muted">Secure checkout (demo form).</p>
  </div>
  
  <?php if (!empty($message)): ?>
    <div class="message <?php echo $message_type; ?>">
      <?php echo $message; ?>
    </div>
  <?php endif; ?>
  
  <?php if (empty($cart_items)): ?>
    <div class="card">
      <p class="muted">Your cart is empty. <a href="/products" class="link">Continue shopping</a></p>
    </div>
  <?php else: ?>
    <div class="grid two">
      <div>
        <div class="card">
          <h3>Order Summary</h3>
          <div class="rows">
            <div><span>Subtotal</span><span>$<?php echo number_format($subtotal, 2); ?></span></div>
            <div><span>Shipping</span><span>$<?php echo number_format($shipping, 2); ?></span></div>
            <div class="total"><span>Total</span><span>$<?php echo number_format($total, 2); ?></span></div>
          </div>
        </div>
      </div>
      
      <div>
        <form class="card form payment-form" action="" method="POST">
          <h3>Payment Information</h3>
          <div class="grid two">
            <div>
              <label>Cardholder Name</label>
              <input type="text" placeholder="Full Name" name="cardholder" required>
            </div>
            <div>
              <label>Card Number</label>
              <input type="text" placeholder="1234 5678 9012 3456" name="card_number" required>
            </div>
            <div>
              <label>Expiry</label>
              <input type="text" placeholder="MM/YY" name="expiry" required>
            </div>
            <div>
              <label>CVV</label>
              <input type="password" placeholder="123" name="cvv" required>
            </div>
            <div class="full">
              <label>Billing Address</label>
              <input type="text" placeholder="Street, City, ZIP" name="billing_address" required>
            </div>
          </div>
          <button class="btn btn-dark" type="submit" name="process_payment">Pay Now</button>
        </form>
      </div>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/../helpers/footer.php'; ?>
