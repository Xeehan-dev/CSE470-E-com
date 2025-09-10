</main>
<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <div class="logo small">Nova<span>Shop</span></div>
      <p>Beautiful products. Simple checkout. Complete e-commerce solution.</p>
    </div>
    <div>
      <h4>Shop</h4>
      <a href="/products">All Products</a>
      <?php if (is_logged_in()): ?>
        <a href="/wishlist">Wishlist</a>
        <a href="/cart">Cart</a>
      <?php endif; ?>
    </div>
    <div>
      <h4>Account</h4>
      <?php if (is_logged_in()): ?>
        <a href="/profile">Profile</a>
        <?php if (is_admin()): ?>
          <a href="/admin">Admin Panel</a>
        <?php endif; ?>
        <a href="/logout">Logout</a>
      <?php else: ?>
        <a href="/login">Login</a>
        <a href="/register">Create Account</a>
      <?php endif; ?>
    </div>
  </div>
  <p class="copyright">© 2025 NovaShop. Built with PHP & MySQL.</p>
</footer>
</body>
</html>
