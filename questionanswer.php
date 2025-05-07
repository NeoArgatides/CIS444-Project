<?php
session_start();
require_once 'php/config.php'; // Ensure this file connects to DB as $DBConnect

function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;

    if ($difference < 60) return "$difference second(s) ago";
    elseif ($difference < 3600) return floor($difference / 60) . " minute(s) ago";
    elseif ($difference < 86400) return floor($difference / 3600) . " hour(s) ago";
    elseif ($difference < 604800) return floor($difference / 86400) . " day(s) ago";
    else return date('F j, Y', $timestamp);
}

$search = $_GET['search'] ?? '';
$tag = $_GET['tag'] ?? '';
$sort = $_GET['sort'] ?? 'newest'; // Default sort by newest

// Corrected SQL query based on the actual table structure
$sql = "SELECT p.PostID, p.Title, p.Content, p.Timestamp, p.Tags, u.Username, u.UserID,
               (SELECT COUNT(*) FROM Likes l WHERE l.PostID = p.PostID) AS Likes,
               (SELECT COUNT(*) FROM Replies r WHERE r.PostID = p.PostID) AS Answers
        FROM Posts p
        JOIN Users u ON p.UserID = u.UserID";

// Add filters for search and tag if provided
if (!empty($search)) {
    $search = $DBConnect->real_escape_string($search);
    $sql .= " AND (p.Title LIKE '%$search%' OR p.Content LIKE '%$search%')";
}
if (!empty($tag)) {
    $tag = $DBConnect->real_escape_string($tag);
    $sql .= " AND p.Tags LIKE '%$tag%'";
}

// Order by the selected sort option
switch ($sort) {
    case 'likes':
        $sql .= " ORDER BY Likes DESC, p.Timestamp DESC";
        break;
    case 'answered':
        $sql .= " ORDER BY Answers DESC, p.Timestamp DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY p.Timestamp ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY p.Timestamp DESC";
        break;
}

// Execute the query
$result = $DBConnect->query($sql);

// Fetch all tags for the tag filters
$tags_query = "SELECT DISTINCT Tags FROM Posts";
$tags_result = $DBConnect->query($tags_query);

$all_tags = [];
if ($tags_result && $tags_result->num_rows > 0) {
    while ($row = $tags_result->fetch_assoc()) {
        $question_tags = explode(',', $row['Tags']);
        foreach ($question_tags as $single_tag) {
            $single_tag = trim($single_tag);
            if (!empty($single_tag) && !in_array($single_tag, $all_tags)) {
                $all_tags[] = $single_tag;
            }
        }
    }
}
sort($all_tags);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Q&A Page</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="./assets/css/qa.css" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    body {
      overflow: hidden;
      height: 100vh;
    }

    main {
      height: calc(100vh - 60px); /* Adjust based on your header height */
      display: flex;
    }

    .main-content {
      flex: 1;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .content {
      flex: 1;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }

    .questions-list {
      flex: 1;
      overflow-y: auto;
      padding-right: 10px;
    }

    .sidebar {
      height: 100%;
      overflow-y: auto;
    }

    /* Sort options styling */
    .sort-options {
      display: flex;
      margin: 10px 0;
      border-bottom: 1px solid #e3e6e8;
      padding-bottom: 12px;
    }

    .sort-label {
      margin-right: 8px;
      color: #6a737c;
      font-size: 13px;
      font-weight: 500;
      display: flex;
      align-items: center;
    }

    .sort-buttons {
      display: flex;
    }

    .sort-button {
      background: transparent;
      border: none;
      color: #6a737c;
      font-size: 13px;
      padding: 4px 10px;
      cursor: pointer;
      border-radius: 3px;
      margin-right: 4px;
    }

    .sort-button.active {
      background-color: #e1ecf4;
      color: #39739d;
      font-weight: 500;
    }

    .sort-button:hover:not(.active) {
      background-color: #f8f9f9;
    }
  </style>
</head>
<body>
<?php include_once 'header.php'; ?>
<main>
  <div class="sidebar">
    <ul>
      <li><a href="index.php"><img src="https://img.icons8.com/?size=100&id=67881&format=png&color=000000" alt="Ask" /><span>Ask</span></a></li>
      <li><a href="users.php"><img src="https://img.icons8.com/?size=100&id=98957&format=png&color=000000" alt="users" /><span>Users</span></a></li>
      <li><a href="questionanswer.php" class="active"><img src="https://img.icons8.com/?size=100&id=2908&format=png&color=000000" alt="q&a" /><span>Q&A</span></a></li>
    </ul>
    <div class="links">
      <a href="#">Privacy Policy</a>
      <a href="#">User agreement</a>
    </div>
  </div>

  <div class="main-content">
    <div class="content">
      <div class="page-header">
        <h1>Newest Questions</h1>
        <a href="index.php" class="ask-button">Ask Question</a>
        <form method="GET" class="filter-form">
          <input type="text" name="search" placeholder="Search questions..." value="<?= htmlspecialchars($search) ?>" />
          <button type="submit">Search</button>
          <?php if (!empty($tag)): ?>
            <input type="hidden" name="tag" value="<?= htmlspecialchars($tag) ?>" />
          <?php endif; ?>
          <?php if (!empty($sort)): ?>
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>" />
          <?php endif; ?>
        </form>

        <!-- Tag Filter Buttons -->
        <div class="tag-filter-buttons">
          <?php if (!empty($tag)): ?>
            <div class="active-filter">
              <span>Filtered by:</span>
              <a href="questionanswer.php<?= !empty($search) ? '?search='.urlencode($search) : '' ?><?= !empty($sort) ? (!empty($search) ? '&' : '?').'sort='.urlencode($sort) : '' ?>" class="active-tag">
                <?= htmlspecialchars($tag) ?>
                <span class="remove-tag">×</span>
              </a>
            </div>
          <?php endif; ?>

          <div class="popular-tags">
            <span>Popular Tags:</span>
            <div class="tag-buttons">
              <?php foreach ($all_tags as $single_tag): ?>
                <a href="?tag=<?= urlencode($single_tag) ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?><?= !empty($sort) ? '&sort='.urlencode($sort) : '' ?>"
                   class="tag-button <?= $tag === $single_tag ? 'active' : '' ?>">
                  <?= htmlspecialchars($single_tag) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Sort options -->
        <div class="sort-options">
          <div class="sort-label">Sort by:</div>
          <div class="sort-buttons">
            <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'newest'])) ?>"
               class="sort-button <?= $sort === 'newest' ? 'active' : '' ?>">
              Newest
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'oldest'])) ?>"
               class="sort-button <?= $sort === 'oldest' ? 'active' : '' ?>">
              Oldest
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'likes'])) ?>"
               class="sort-button <?= $sort === 'likes' ? 'active' : '' ?>">
              Most likes
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'answered'])) ?>"
               class="sort-button <?= $sort === 'answered' ? 'active' : '' ?>">
              Most answered
            </a>
          </div>
        </div>
      </div>

      <div class="questions-list">
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($question = $result->fetch_assoc()): ?>
            <?php $is_own_question = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $question['UserID']; ?>
            <div class="question-card">
              <h3><a href="question.php?id=<?= $question['PostID'] ?>"><?= htmlspecialchars($question['Title']) ?></a>
              <?php if ($is_own_question): ?>
                <div class="own-question-badge">Your Question</div>
              <?php endif; ?>
            </h3>
              <p class="question-excerpt"><?= htmlspecialchars(substr(str_replace(['\r\n', '\r', '\n'], PHP_EOL, $question['Content']), 0, 200)) ?>...</p>
              <div class="question-tags">
                <?php foreach (explode(',', $question['Tags']) as $question_tag): ?>
                  <?php $question_tag = trim($question_tag); ?>
                  <?php if (!empty($question_tag)): ?>
                    <a href="?tag=<?= urlencode($question_tag) ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?><?= !empty($sort) ? '&sort='.urlencode($sort) : '' ?>" class="tag"><?= htmlspecialchars($question_tag) ?></a>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
              <div class="question-meta">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($question['Username']) ?>&background=random" alt="avatar" class="user-avatar" />
                <a href="user.php?id=<?= $question['UserID'] ?>" class="user-name"><?= htmlspecialchars($question['Username']) ?></a>
                <span class="timestamp"><?= time_ago($question['Timestamp']) ?></span>
              </div>
              <div class="question-stats">
                <div class="stat-group">
                  <div class="stat-value"><?= $question['Likes'] ?? 0 ?></div>
                  <div class="stat-label">likes</div>
                  <?php if (isset($_SESSION['user_id']) && !$is_own_question): ?>
                    <button class="like-button" data-post-id="<?= $question['PostID'] ?>" data-liked="false">
                      <span class="like-icon">❤</span>
                    </button>
                  <?php else: ?>
                    <div class="static-heart">❤</div>
                  <?php endif; ?>
                </div>
                <div class="stat-group">
                  <div class="stat-value"><?= $question['Answers'] ?? 0 ?></div>
                  <div class="stat-label">answers</div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="no-questions">
            <p>No questions found. Be the first to ask a question!</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const likeButtons = document.querySelectorAll('.like-button');

  // First, check which posts the current user has already liked
  fetch('php/get_liked_posts.php')
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Mark the buttons of posts the user has already liked
        data.liked_posts.forEach(postId => {
          const button = document.querySelector(`.like-button[data-post-id="${postId}"]`);
          if (button) {
            button.setAttribute('data-liked', 'true');
          }
        });
      }
    })
    .catch(error => console.error('Error fetching liked posts:', error));

  likeButtons.forEach(button => {
    button.addEventListener('click', function() {
      const postId = this.getAttribute('data-post-id');
      const liked = this.getAttribute('data-liked') === 'true';
      const likeIcon = this.querySelector('.like-icon');
      const statValue = this.parentElement.querySelector('.stat-value');

      // Toggle like state visually first for immediate feedback
      this.setAttribute('data-liked', !liked);
      const currentLikes = parseInt(statValue.textContent);
      statValue.textContent = liked ? (currentLikes - 1) : (currentLikes + 1);

      if (!liked) {
        likeIcon.classList.add('liked-animation');
        setTimeout(() => {
          likeIcon.classList.remove('liked-animation');
        }, 800);
      }

      // Send like action to server
      fetch('php/like_post.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `post_id=${postId}&action=${liked ? 'unlike' : 'like'}`
      })
      .then(response => response.json())
      .then(data => {
        if (!data.success) {
          // Revert the visual change if the server request failed
          this.setAttribute('data-liked', liked);
          statValue.textContent = currentLikes;
          console.error('Like action failed:', data.message);
        }
      })
      .catch(error => {
        // Revert the visual change if there was an error
        this.setAttribute('data-liked', liked);
        statValue.textContent = currentLikes;
        console.error('Error:', error);
      });
    });
  });
});
</script>
</body>
</html>
