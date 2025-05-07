<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>Login - DevSphere</title>
    <link rel="stylesheet" href="./assets/css/login.css" />
  </head>
  <body>
    <?php include_once 'header.php'; ?>
    <main class="auth-container">
      <div>
        <h2>Log In to DevSphere</h2>
        <p style="margin-bottom: 2rem">
          By logging in, you agree to our Terms and Privacy Policy.
        </p>
        <form id="login-form" action="php/login.php" method="POST">
          <label for="login-email">Username</label>
          <input
            type="text"
            id="login-username"
            name="username"
            placeholder="Enter your username"
            required
          />
          <label for="login-password">Password</label>
          <input
            type="password"
            id="login-password"
            name="password"
            placeholder="Enter your password"
            required
          />
          <?php
          if (!empty($_SESSION['login_errors'])) {
              foreach ($_SESSION['login_errors'] as $error) {
                  echo "<p style='color:red;'>$error</p>";
              }
              unset($_SESSION['login_errors']);
          }
          if (!empty($_SESSION['message'])) {
              echo "<p style='color:green;margin-bottom:10px;'>".$_SESSION['message']."</p>";
              unset($_SESSION['message']);
          }

          $old = $_SESSION['login_old'] ?? [];
          unset($_SESSION['login_old']);
          ?>
          <button type="submit" class="primary-btn">Log In</button>
        </form>
        <p class="already-member">
          Don’t have an account? <a href="signup.php">Sign Up</a>
        </p>
      </div>
    </main>
    <footer class="site-footer">
      <p>&copy; 2025 DevSphere. All rights reserved.</p>
    </footer>
    <script src="./assets/js/login.js"></script>
  </body>
</html>
