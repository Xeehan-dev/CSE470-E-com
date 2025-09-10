<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/session.php';

// Require admin access
require_admin();

$message = '';
$message_type = '';

// Handle admin actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'delete_product' && isset($_GET['id'])) {
        $product_id = (int)$_GET['id'];
        $delete_sql = "DELETE FROM products WHERE id = $product_id";
        execute_query($delete_sql);
        $message = "✅ Product deleted successfully!";
        $message_type = 'success';
    } elseif ($action === 'update_order_status' && isset($_GET['order_id']) && isset($_GET['status'])) {
        $order_id = (int)$_GET['order_id'];
        $status = escape_string($_GET['status']);
        $update_sql = "UPDATE orders SET order_status = '$status' WHERE id = $order_id";
        execute_query($update_sql);
        $message = "✅ Order status updated!";
        $message_type = 'success';
    }
}

// Get products for admin
$products_sql = "SELECT p.*, c.name as category_name 
                 FROM products p 
                 LEFT JOIN categories c ON p.category_id = c.id 
                 ORDER BY p.created_at DESC";

$products = get_multiple_rows($products_sql);

// Get orders for admin
$orders_sql = "SELECT o.*, u.name as customer_name 
               FROM orders o 
               JOIN users u ON o.user_id = u.id 
               ORDER BY o.created_at DESC 
               LIMIT 10";

$orders = get_multiple_rows($orders_sql);

// Get statistics
$stats_sql = "SELECT 
                COUNT(*) as total_products,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_products,
                COUNT(CASE WHEN status = 'out_of_stock' THEN 1 END) as out_of_stock
              FROM products";
$stats = get_single_row($stats_sql);

$page_title = 'Admin Panel';
$show_search = false;
include __DIR__ . '/../helpers/header.php';
?>

<section class="container section">
  <div class="admin card">
    <h2>Admin Panel</h2>
    
    <?php if (!empty($message)): ?>
      <div class="message <?php echo $message_type; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>
    
    <div class="grid two">
      <div class="tile">
        <h4>Statistics</h4>
        <p><strong>Total Products:</strong> <?php echo $stats['total_products']; ?></p>
        <p><strong>Active Products:</strong> <?php echo $stats['active_products']; ?></p>
        <p><strong>Out of Stock:</strong> <?php echo $stats['out_of_stock']; ?></p>
      </div>
      <div class="tile">
        <h4>Quick Actions</h4>
        <a href="admin_products.php" class="btn btn-dark">Manage Products</a>
        <a href="admin_orders.php" class="btn btn-dark">View All Orders</a>
        <a href="admin_users.php" class="btn btn-dark">Manage Users</a>
      </div>
    </div>
    
    <div class="grid two mt">
      <section>
        <h3>Products</h3>
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Price</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (array_slice($products, 0, 5) as $product): ?>
              <tr>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td>$<?php echo number_format($product['price'], 2); ?></td>
                <td><?php echo ucfirst($product['status']); ?></td>
                <td>
                  <a href="/admin/products?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-ghost">Edit</a>
                  <a href="/admin?action=delete_product&id=<?php echo $product['id']; ?>" class="btn btn-ghost danger" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
      <section>
        <h3>Recent Orders</h3>
        <table class="table">
          <thead>
            <tr>
              <th>#</th>
              <th>Customer</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                <td><?php echo ucfirst($order['order_status']); ?></td>
                <td>
                  <a href="/admin/orders?action=view&id=<?php echo $order['id']; ?>" class="btn btn-ghost">View</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    </div>
  </div>
</section>

<?php include __DIR__ . '/../helpers/footer.php'; ?>
