<?php
require_once __DIR__ . '/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo isset($page_title) ? $page_title . ' • ' : ''; ?><?php echo APP_NAME; ?></title>
  <link rel="stylesheet" href="http://localhost/E-Comm/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="container header-inner">
    <a class="logo" href="/E-Comm/">Nova<span>Shop</span></a>
    <nav class="nav">
      <a href="/E-Comm/products">Products</a>
      <?php if (is_logged_in()): ?>
        <a href="/E-Comm/wishlist">Wishlist</a>
        <a href="/E-Comm/cart">Cart</a>
        <a href="/E-Comm/profile">Profile</a>
        <?php if (is_admin()): ?>
          <a href="/E-Comm/admin">Admin Panel</a>
        <?php endif; ?>
        <a href="/E-Comm/logout" class="btn btn-ghost">Logout</a>
      <?php else: ?>
        <a href="/E-Comm/login" class="btn btn-ghost">Login</a>
        <a href="/E-Comm/register" class="btn btn-primary">Create Account</a>
      <?php endif; ?>
    </nav>
  </div>
  <?php if (isset($show_search) && $show_search): ?>
  <form class="searchbar container" action="/E-Comm/products" method="get">
    <input type="text" name="q" placeholder="Search for products, brands and more…" value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" />
    <button type="submit" class="btn btn-dark">Search</button>
  </form>
  <?php endif; ?>
</header>
<main class="page">
