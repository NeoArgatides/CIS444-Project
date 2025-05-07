<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DevSphere</title>
    <link rel="stylesheet" href="./assets/css/styles.css" />
  </head>
  <body>
    <?php include_once 'header.php'; ?>
    <main>
      <div class="sidebar">
        <ul>
          <li>
            <a href="index.php">
              <img
                src="https://img.icons8.com/?size=100&id=67881&format=png&color=000000"
                alt="home"
              />
              <span>Home</span>
            </a>
          </li>
          <li>
            <a href="users.html">
              <img
                src="https://img.icons8.com/?size=100&id=98957&format=png&color=000000"
                alt="users"
              />
              <span>Users</span>
            </a>
          </li>
          <li>
            <a href="questionanswer.html">
              <img
                src="https://img.icons8.com/?size=100&id=2908&format=png&color=000000"
                alt="q&a"
              />
              <span>Q&A</span>
            </a>
          </li>
        </ul>
        <div class="links">
          <a href="#">Privacy Policy</a>
          <a href="#">User agreement</a>
        </div>
      </div>
      <div class="page">
        <div class="auth-container">
          <h1>Welcome to Our Community</h1>
          <p class="subtitle">
            Join our community to ask questions, share knowledge, and connect
            with other developers.
          </p>

          <div class="auth-buttons">
            <a class="login-button" href="login.php">
              <img
                src="https://img.icons8.com/?size=100&id=61027&format=png"
                alt="login"
              />
              Log in
            </a>
            <div class="divider">
              <span>or</span>
            </div>
            <a class="register-button" href="signup.php">
              <img
                src="https://img.icons8.com/?size=100&id=43942&format=png"
                alt="register"
              />
              Sign up
            </a>
          </div>

          <div class="auth-footer">
            <p>
              By signing up, you agree to our
              <a href="#">Terms of Service</a> and
              <a href="#">Privacy Policy</a>.
            </p>
          </div>
        </div>
      </div>
    </main>
    <script type="module">
      import { setActive } from "./assets/js/navigation.js"
      setActive()
    </script>
  </body>
</html>
