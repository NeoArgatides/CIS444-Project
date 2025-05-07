<?php
session_start();
?>
<header>
      <a href="index.php">
        <img src="./assets/img/logo-no-bg.png" alt="logo" />
      </a>

      <?php if (isset($_SESSION['user_id'])): ?>
  <input type="text" name="search" placeholder="Search users" />
  <div class="profile">
    <img
      src="https://cdn-icons-png.flaticon.com/512/219/219983.png"
      alt="profile"
    />
    <span><?= htmlspecialchars($_SESSION['username']) ?></span>
    <a href="/php/logout.php">Logout</a>
  </div>
<?php endif; ?>
    </header>
