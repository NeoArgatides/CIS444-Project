<?php
if (!session_id()) {
    session_start();
}
?>
<header>
  <a href="index.php">
    <img src="./assets/img/logo-no-bg.png" alt="logo" />
  </a>

  <?php if (isset($_SESSION['user_id'])): ?>
    <div class="search-container">
      <input type="text" id="user-search" name="search" placeholder="Search users" autocomplete="off" />
      <div id="search-results" style="display: none;" class="search-results"></div>
    </div>
    <div class="profile">
      <a href="user.php?id=<?= $_SESSION['user_id'] ?>" class="profile-link">
        <img
          src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['username']) ?>&background=random"
          alt="profile"
        />
        <span><?= htmlspecialchars($_SESSION['username']) ?></span>
      </a>
      <a href="/php/logout.php">Logout</a>
    </div>

    <script src="./assets/js/header.js" ></script>
  <?php endif; ?>
</header>

<style>
  .profile-link {
    display: flex;
    align-items: center;
    text-decoration: none;
    color: inherit;
    margin-right: 15px;
    gap: 5px;
  }

  .profile-link:hover {
    opacity: 0.8;
  }

  .profile {
    display: flex;
    align-items: center;
  }
</style>
