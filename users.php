<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Search users page</title>
    <link rel="stylesheet" href="./assets/css/users.css" />
  </head>
  <body>
    <?php
    include_once 'header.php';

    // Check if user is logged in
    $is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    ?>
    <main>
      <div class="sidebar">
        <ul>
          <li>
            <a href="index.php">
              <img
                src="https://img.icons8.com/?size=100&id=67881&format=png&color=000000"
                alt="Ask"
              />
              <span>Ask</span>
            </a>
          </li>
          <li>
            <a href="users.php">
              <img
                src="https://img.icons8.com/?size=100&id=98957&format=png&color=000000"
                alt="users"
              />
              <span>Users</span>
            </a>
          </li>
          <li>
            <a href="questionanswer.php">
              <img
                src="https://img.icons8.com/?size=100&id=2908&format=png&color=000000"
                alt="q&a"
              />
              <span>Q&A</span>
            </a>
          </li>
          <?php if ($is_logged_in && isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
          <li><a href="admin.php"><img src="https://img.icons8.com/?size=100&id=12599&format=png&color=000000" alt="admin" /><span>Admin</span></a></li>
          <?php endif; ?>
        </ul>
        <div class="links">
          <a href="#">Privacy Policy</a>
          <a href="#">User agreement</a>
        </div>
      </div>

      <?php if (!$is_logged_in): ?>
      <div class="page">
        <div style="display: flex; justify-content: center; align-items: center; height: 100%; padding: 20px;">
          <div style="max-width: 450px; width: 100%; background-color: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); text-align: center; padding: 40px 30px;">
            <h1 style="font-size: 24px; margin-bottom: 15px; color: #0c0d0e;">Please Log In</h1>
            <p style="color: #3c4146; margin-bottom: 30px; font-size: 16px;">You need to be logged in to view and search users.</p>

            <a href="login.php" class="login-button" style="display: flex; justify-content: center; align-items: center; gap: 10px; width: 100%; padding: 12px 0; border: 1px solid #d6d9dc; border-radius: 4px; text-decoration: none; font-weight: 500; background-color: #f8f9f9; color: #3c4146; transition: all 0.2s; margin-bottom: 15px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                <polyline points="10 17 15 12 10 7"></polyline>
                <line x1="15" y1="12" x2="3" y2="12"></line>
              </svg>
              Log in
            </a>

            <div style="display: flex; align-items: center; margin: 20px 0;">
              <div style="flex-grow: 1; height: 1px; background-color: #e3e6e8;"></div>
              <span style="padding: 0 10px; color: #6a737c; font-size: 14px;">or</span>
              <div style="flex-grow: 1; height: 1px; background-color: #e3e6e8;"></div>
            </div>

            <a href="signup.php" class="signup-button" style="display: flex; justify-content: center; align-items: center; gap: 10px; width: 100%; padding: 12px 0; border: none; border-radius: 4px; text-decoration: none; font-weight: 500; background-color: #0a95ff; color: white; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,123,255,0.2);">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <line x1="20" y1="8" x2="20" y2="14"></line>
                <line x1="23" y1="11" x2="17" y2="11"></line>
              </svg>
              Sign up
            </a>
          </div>
        </div>
      </div>
      <?php else: ?>
      <div class="page">
        <div class="page-header">
          <h1>Users</h1>
          <div class="filters">
            <div class="search-box">
              <img
                src="https://img.icons8.com/?size=100&id=132&format=png"
                alt="search"
              />
              <input type="text" placeholder="Filter by user" />
            </div>
            <div class="tabs">
              <button class="active">Users</button>
              <button>Admin</button>
              <button>Most Posts</button>
            </div>
          </div>
        </div>
        <div class="users-grid">
          <!-- User cards will be populated by JavaScript -->
        </div>
      </div>
      <?php endif; ?>
    </main>
    <?php if ($is_logged_in): ?>
    <script type="module" src="./assets/js/users.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // If the users.js script already handles creating user cards,
        // this function will ensure the correct avatar URLs are used
        window.createAvatar = function(username) {
          return `https://ui-avatars.com/api/?name=${encodeURIComponent(username)}&background=random`;
        };
      });
    </script>
    <?php endif; ?>
  </body>
</html>
