<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../helpers/session.php';

$message = '';
$message_type = '';

if (isset($_POST['register'])) {
    $name = escape_string($_POST['name']);
    $email = escape_string($_POST['email']);
    $password = $_POST['pass'];
    
    // Validate input
    if (empty($name) || empty($email) || empty($password)) {
        $message = "❌ All fields are required.";
        $message_type = 'error';
    } elseif (strlen($password) < 6) {
        $message = "❌ Password must be at least 6 characters long.";
        $message_type = 'error';
    } else {
        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = '$email'";
        if (get_row_count($check_sql) > 0) {
            $message = "❌ This email already exists. Please use another.";
            $message_type = 'error';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";
            
            try {
                execute_query($sql);
                $message = "✅ Account created successfully! You can now login.";
                $message_type = 'success';
                
                // Clear form data
                $_POST = array();
            } catch (Exception $e) {
                $message = "❌ Error: " . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

$page_title = 'Create Account';
$show_search = false;
include __DIR__ . '/../helpers/header.php';
?>

<section class="container section">
  <div class="auth card">
    <h2>Create Account</h2>
    
    <?php if (!empty($message)): ?>
      <div class="message <?php echo $message_type; ?>">
        <?php echo $message; ?>
      </div>
    <?php endif; ?>
    
    <form class="form" action="" method="POST">
      <label>Full Name</label>
      <input type="text" placeholder="Your name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
      
      <label>Email</label>
      <input type="email" placeholder="you@email.com" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
      
      <label>Password</label>
      <input type="password" placeholder="Choose a password (min 6 characters)" name="pass" required>
      
      <button class="btn btn-primary" type="submit" name="register" value="register">Create Account</button>
      
      <p class="muted">Already have an account? <a class="link" href="/login">Login here</a>.</p>
    </form>
  </div>
</section>

<?php include __DIR__ . '/../helpers/footer.php'; ?>
