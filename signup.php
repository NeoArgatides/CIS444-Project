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
        <h2>Join DevSphere</h2>
        <p style="margin-bottom: 2rem">
          By signing up, you agree to our Terms and Privacy Policy.
        </p>
        <form id="signup-form" action="php/register.php" method="POST">
        <label for="signup-email">Email</label>
          <input
            type="email"
            id="signup-email"
            name="email"
            value="<?= htmlspecialchars($old['email'] ?? '') ?>"
            placeholder="Enter your email"
            required
          />
          <label for="signup-username">Username</label>
          <input
            type="text"
            value="<?= htmlspecialchars($old['username'] ?? '') ?>"
            id="signup-username"
            name="username"
            placeholder="Enter your username"
            required
          />
          <label for="signup-password">Password</label>
          <input
            type="password"
            id="signup-password"
            name="password"
            placeholder="Enter your password"
            required
          />
          <label for="confirm-signup-password">Confirm Password</label>
          <input
            type="password"
            id="confirm-signup-password"
            name="confirm-password"
            placeholder="Enter your password"
            required
          />
          <?php

            if (!empty($_SESSION['register_errors'])) {
                foreach ($_SESSION['register_errors'] as $error) {
                    echo "<p style='color:red;margin-bottom:10px;'>$error</p>";
                }
                unset($_SESSION['register_errors']);
            }

            $old = $_SESSION['register_old'] ?? [];
            unset($_SESSION['register_old']);
          ?>
          <button type="submit" class="primary-btn">Sign Up</button>
        </form>
        <p class="already-member">
          Already have an account ? <a href="login.php">Log in</a>
        </p>
      </div>
    </main>
    <footer class="site-footer">
      <p>&copy; 2025 DevSphere. All rights reserved.</p>
    </footer>
    <script src="./assets/js/login.js"></script>
  </body>
</html>
