<?php
session_start();
require_once 'php/config.php';

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
            </ul>
            <div class="links">
                <a href="#">Privacy Policy</a>
                <a href="#">User agreement</a>
            </div>
        </div>
        <div class="page">
            <div class="user-profile">
                <div class="profile-header">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['Username']) ?>&background=random" alt="avatar" class="avatar" />
                    <div class="profile-info">
                        <h1><?= htmlspecialchars($user['Username']) ?></h1>
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
                  <p>
                    <?= !empty($user['Description'])
                        ? nl2br(htmlspecialchars($user['Description']))
                        : "This user hasn't written a description yet." ?>
                  </p>
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
</body>
</html>
