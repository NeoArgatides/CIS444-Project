<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Search users page</title>
    <link rel="stylesheet" href="./assets/css/users.css" />
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
        </ul>
        <div class="links">
          <a href="#">Privacy Policy</a>
          <a href="#">User agreement</a>
        </div>
      </div>
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
              <button>Posts</button>
            </div>
          </div>
        </div>
        <div class="users-grid">
          <!-- User cards will be populated by JavaScript -->
        </div>

      </div>
    </main>
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
  </body>
</html>
