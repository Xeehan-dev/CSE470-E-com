<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/session.php';

$message = '';
$message_type = '';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit();
}

if (isset($_POST['login'])) {
    $email = escape_string($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $message = "❌ Email and password are required.";
        $message_type = 'error';
    } else {
        // Get user by email
        $sql = "SELECT id, name, email, password, role FROM users WHERE email = '$email'";
        $user = get_single_row($sql);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: /');
            } else {
                header('Location: /');
            }
            exit();
        } else {
            $message = "❌ Invalid email or password.";
            $message_type = 'error';
        }
    }
}

$page_title = 'Login';
$show_search = false;
include __DIR__ . '/../helpers/header.php';
?>

<section class="container section">
  <div class="auth card">
    <h2>Login</h2>
    
    <?php if (!empty($message)): ?>
      <div class="message <?php echo $message_type; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>
    
    <form class="form" action="" method="POST">
      <label>Email</label>
      <input type="email" placeholder="you@email.com" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
      
      <label>Password</label>
      <input type="password" placeholder="••••••••" name="password" required>
      
      <button class="btn btn-primary" type="submit" name="login" value="login">Login</button>
      
      <p class="muted">No account? <a class="link" href="/register">Create one</a>.</p>
    </form>
  </div>
</section>

<?php include __DIR__ . '/../helpers/footer.php'; ?>