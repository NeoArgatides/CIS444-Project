<?php
session_start();
require_once 'php/config.php';

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// If not logged in, show login message
if (!$is_logged_in) {
    // Still include header for consistency
    include_once 'header.php';

    echo '
    <main>
        <div class="sidebar">
            <ul>
                <li><a href="index.php"><img src="https://img.icons8.com/?size=100&id=67881&format=png&color=000000" alt="Ask" /><span>Ask</span></a></li>
                <li><a href="users.php"><img src="https://img.icons8.com/?size=100&id=98957&format=png&color=000000" alt="users" /><span>Users</span></a></li>
                <li><a href="questionanswer.php"><img src="https://img.icons8.com/?size=100&id=2908&format=png&color=000000" alt="q&a" /><span>Q&A</span></a></li>
            </ul>
            <div class="links">
                <a href="#">Privacy Policy</a>
                <a href="#">User agreement</a>
            </div>
        </div>
        <div class="page">
            <div class="auth-container" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 70vh; text-align: center; padding: 20px;">
                <h1>Please Log In</h1>
                <p class="subtitle">You need to be logged in to view user profiles.</p>

                <div class="auth-buttons" style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px; width: 250px;">
                    <a class="login-button" href="login.php" style="display: flex; align-items: center; gap: 10px; padding: 12px 20px; background-color: #e1ecf4; color: #39739d; text-decoration: none; border-radius: 4px; font-weight: 500; border: 1px solid #39739d;">
                        <img src="https://img.icons8.com/?size=100&id=61027&format=png" alt="login" style="width: 24px; height: 24px;" />
                        Log in
                    </a>
                    <div class="divider" style="display: flex; align-items: center; margin: 10px 0;"><span style="background: #f8f9fa; padding: 0 10px; color: #6a737c;">or</span></div>
                    <a class="register-button" href="signup.php" style="display: flex; align-items: center; gap: 10px; padding: 12px 20px; background-color: #0a95ff; color: white; text-decoration: none; border-radius: 4px; font-weight: 500; border: none;">
                        <img src="https://img.icons8.com/?size=100&id=43942&format=png" alt="register" style="width: 24px; height: 24px;" />
                        Sign up
                    </a>
                </div>
            </div>
        </div>
    </main>';

    exit();
}

// Handle profile updates
if (isset($_POST['update_profile']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $username = trim($_POST['username']);
    $description = trim($_POST['description']);

    // Basic validation
    if (!empty($username)) {
        // Update the user's profile
        $update_stmt = $DBConnect->prepare("UPDATE Users SET Username = ?, Description = ? WHERE UserID = ?");
        $update_stmt->bind_param("ssi", $username, $description, $user_id);

        if ($update_stmt->execute()) {
            // Redirect to avoid form resubmission
            header("Location: user.php?id=" . $user_id . "&updated=1");
            exit();
        }
    }
}

function time_ago($datetime) {

  $timestamp = strtotime($datetime);
  $difference = time() - $timestamp;

  $periods = ['second', 'minute', 'hour', 'day', 'week', 'month', 'year', 'decade'];
  $lengths = ['60','60','24','7','4.35','12','10'];

  for ($j = 0; $difference >= $lengths[$j]; $j++) {
      $difference /= $lengths[$j];
  }

  $difference = round($difference);

  return "$difference " . $periods[$j] . "(s) ago";
}


// Vérifie si un user_id est passé en paramètre GET
if (!isset($_GET['id'])) {
    die('User not found');
}

$user_id = $_GET['id'];

// Check if the user is viewing their own profile
$is_own_profile = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user_id;

// Prépare la requête pour récupérer les informations de l'utilisateur
$stmt = $DBConnect->prepare("
    SELECT
        Users.UserID,
        Users.Username,
        Users.Email,
        Users.DateJoined,
        Users.Description,
        Users.Role,
        Users.LastConnection,
        COUNT(Posts.PostID) AS PostCount
    FROM Users
    LEFT JOIN Posts ON Users.UserID = Posts.UserID
    WHERE Users.UserID = ?
    GROUP BY Users.UserID
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Si l'utilisateur n'existe pas, afficher une erreur
if ($result->num_rows === 0) {
    die('User not found');
}

$user = $result->fetch_assoc();

// Formater la date d'inscription pour un affichage plus lisible
$date_joined = new DateTime($user['DateJoined']);
$formatted_date_joined = $date_joined->format('F j, Y');

// Charger les posts de l'utilisateur
$stmt_posts = $DBConnect->prepare("
    SELECT PostID, Title, Timestamp
    FROM Posts
    WHERE UserID = ?
    ORDER BY Timestamp DESC
    LIMIT 5
");

$stmt_posts->bind_param("i", $user_id);
$stmt_posts->execute();
$posts_result = $stmt_posts->get_result();

// Fetcher les posts de l'utilisateur
$posts = [];
while ($post = $posts_result->fetch_assoc()) {
    $posts[] = $post;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($user['Username']) ?>'s Profile</title>
    <link rel="stylesheet" href="./assets/css/user.css" />
</head>
<body>
    <?php include_once 'header.php'; ?>
    <main>
        <div class="sidebar">
            <ul>
                <li><a href="index.php"><img src="https://img.icons8.com/?size=100&id=67881&format=png&color=000000" alt="Ask" /><span>Ask</span></a></li>
                <li><a href="users.php"><img src="https://img.icons8.com/?size=100&id=98957&format=png&color=000000" alt="users" /><span>Users</span></a></li>
                <li><a href="questionanswer.php"><img src="https://img.icons8.com/?size=100&id=2908&format=png&color=000000" alt="q&a" /><span>Q&A</span></a></li>
                <?php if ($is_logged_in && isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
      <li><a href="admin.php"><img src="https://img.icons8.com/?size=100&id=12599&format=png&color=000000" alt="admin" /><span>Admin</span></a></li>
      <?php endif; ?>
            </ul>
            <div class="links">
                <a href="#">Privacy Policy</a>
                <a href="#">User agreement</a>
            </div>
        </div>
        <div class="page">
            <div class="user-profile">
                <?php if (isset($_GET['updated']) && $_GET['updated'] == 1 && $is_own_profile): ?>
                    <div class="alert alert-success">
                        Your profile has been updated successfully.
                    </div>
                <?php endif; ?>

                <div class="profile-header">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['Username']) ?>&background=random" alt="avatar" class="avatar" />
                    <div class="profile-info">
                        <?php if ($is_own_profile): ?>
                            <div class="edit-controls">
                                <button id="edit-profile-btn" class="edit-btn">Edit Profile</button>
                            </div>

                            <!-- Username display (visible by default) -->
                            <h1 id="username-display"><?= htmlspecialchars($user['Username']) ?></h1>

                            <!-- Form for editing (hidden by default) -->
                            <form id="profile-form" method="post" action="" style="display: none;">
                                <div class="form-group">
                                    <label for="username">Username:</label>
                                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['Username']) ?>" required>
                                </div>

                                <!-- Hidden description field that will be filled via JavaScript -->
                                <input type="hidden" id="hidden-description" name="description" value="<?= htmlspecialchars($user['Description'] ?? '') ?>">
                                <input type="hidden" name="update_profile" value="1">
                            </form>
                        <?php else: ?>
                            <h1><?= htmlspecialchars($user['Username']) ?></h1>
                        <?php endif; ?>

                        <!-- These are now outside the form so they're always visible -->
                        <div class="member-info">
                            <span>Member since <?= $formatted_date_joined ?></span>
                            <span>Last seen: <?= time_ago($user['LastConnection']) ?></span>
                        </div>
                        <div class="stats">
                            <div class="stat">
                                <div class="number"><?= $user['PostCount'] ?></div>
                                <div class="label">Posts</div>
                            </div>
                            <div class="stat">
                                <div class="number"><?= htmlspecialchars($user['Role']) ?></div>
                                <div class="label">Role</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="about-section">
                    <h2>About</h2>
                    <?php if ($is_own_profile): ?>
                        <div id="description-display">
                            <p>
                                <?= !empty($user['Description'])
                                    ? nl2br(htmlspecialchars($user['Description']))
                                    : "This user hasn't written a description yet." ?>
                            </p>
                        </div>
                        <div class="form-group" id="description-edit" style="display: none;">
                            <textarea id="description-textarea" rows="5"><?= htmlspecialchars($user['Description'] ?? '') ?></textarea>
                            <div class="form-actions">
                                <button type="button" id="save-btn" class="save-btn">Save Changes</button>
                                <button type="button" id="cancel-edit-btn" class="cancel-btn">Cancel</button>
                            </div>
                        </div>
                    <?php else: ?>
                        <p>
                            <?= !empty($user['Description'])
                                ? nl2br(htmlspecialchars($user['Description']))
                                : "This user hasn't written a description yet." ?>
                        </p>
                    <?php endif; ?>
                </div>

                <div class="posts-section">
                    <div class="posts-header">
                        <h2>Top Posts</h2>
                        <?php if ($user['PostCount'] > 5): ?>
                            <a href="questionanswer.php?user=<?= $user['UserID'] ?>" class="view-all-link">View all posts</a>
                        <?php endif; ?>
                    </div>

                    <div class="posts-list">
                        <?php if (empty($posts)): ?>
                            <div class="no-posts">
                                <img src="https://img.icons8.com/?size=100&id=55494&format=png&color=000000" alt="No posts" class="no-posts-icon">
                                <p>No posts available yet.</p>
                                <?php if ($is_own_profile): ?>
                                    <a href="index.php" class="create-post-btn">Create your first post</a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                                <a href="question.php?id=<?= $post['PostID'] ?>" class="post-card-link">
                                    <div class="post-card">
                                        <div class="post-stats">
                                            <?php
                                            // Get post stats if available
                                            $likes_query = $DBConnect->prepare("SELECT COUNT(*) as count FROM Likes WHERE PostID = ?");
                                            $likes_query->bind_param("i", $post['PostID']);
                                            $likes_query->execute();
                                            $likes_result = $likes_query->get_result();
                                            $likes = $likes_result->fetch_assoc()['count'] ?? 0;

                                            $replies = $DBConnect->query("SELECT COUNT(*) as count FROM Replies WHERE PostID = " . $post['PostID'])->fetch_assoc()['count'] ?? 0;
                                            ?>
                                            <div class="stat-item">
                                                <span class="stat-value"><?= $likes ?></span>
                                                <span class="stat-label">likes</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-value"><?= $replies ?></span>
                                                <span class="stat-label">replies</span>
                                            </div>
                                        </div>

                                        <div class="post-content">
                                            <span class="post-title">
                                                <?= htmlspecialchars($post['Title']) ?>
                                            </span>

                                            <div class="post-meta">
                                                <span class="post-date">Posted <?= time_ago($post['Timestamp']) ?></span>

                                                <?php
                                                // Get post tags if available
                                                $tags_result = $DBConnect->query("SELECT Tags FROM Posts WHERE PostID = " . $post['PostID'])->fetch_assoc();
                                                if ($tags_result && !empty($tags_result['Tags'])):
                                                    $tags = explode(',', $tags_result['Tags']);
                                                ?>
                                                    <div class="post-tags">
                                                        <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                                                            <span class="post-tag" data-tag="<?= urlencode(trim($tag)) ?>">
                                                                <?= htmlspecialchars(trim($tag)) ?>
                                                            </span>
                                                        <?php endforeach; ?>

                                                        <?php if (count($tags) > 3): ?>
                                                            <span class="more-tags">+<?= count($tags) - 3 ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script type="module" src="./assets/js/user.js"></script>

    <?php if ($is_own_profile): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const editBtn = document.getElementById('edit-profile-btn');
        const cancelBtn = document.getElementById('cancel-edit-btn');
        const profileForm = document.getElementById('profile-form');
        const usernameDisplay = document.getElementById('username-display');
        const descriptionDisplay = document.getElementById('description-display');
        const descriptionEdit = document.getElementById('description-edit');

        // Show edit form
        editBtn.addEventListener('click', function() {
            profileForm.style.display = 'block';
            usernameDisplay.style.display = 'none';
            descriptionDisplay.style.display = 'none';
            descriptionEdit.style.display = 'block';
            editBtn.style.display = 'none';
        });

        // Cancel edit
        cancelBtn.addEventListener('click', function() {
            profileForm.style.display = 'none';
            usernameDisplay.style.display = 'block';
            descriptionDisplay.style.display = 'block';
            descriptionEdit.style.display = 'none';
            editBtn.style.display = 'inline-block';
        });

        // Save changes
        document.getElementById('save-btn').addEventListener('click', function() {
            document.getElementById('hidden-description').value = document.getElementById('description-textarea').value;
            document.getElementById('profile-form').submit();
        });
    });
    </script>

    <style>
    .edit-controls {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 10px;
        position: absolute;
        right: 20px;
        top: 20px;
    }

    .edit-btn {
        background-color: #0a95ff;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 8px 12px;
        cursor: pointer;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .form-group input, .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }

    .save-btn {
        background-color: #2ecc71;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 8px 12px;
        cursor: pointer;
    }

    .cancel-btn {
        background-color: #e74c3c;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 8px 12px;
        cursor: pointer;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .profile-header {
        position: relative;
    }
    </style>
    <?php endif; ?>
</body>
</html>
