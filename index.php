<?php
session_start();
require_once 'php/config.php';

// Check if user is logged in using the correct session variable names
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$username = $is_logged_in ? $_SESSION['username'] : '';
$user_id = $is_logged_in ? $_SESSION['user_id'] : 0;

// Process form submission if user is logged in
if ($is_logged_in && isset($_POST['submit_question'])) {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $tags = $_POST['tags'] ?? '';

    // Validate input
    $errors = [];
    if (empty($title)) $errors[] = "Title is required";
    if (empty($content)) $errors[] = "Content is required";
    if (empty($tags)) $errors[] = "At least one tag is required";

    // If no errors, process the submission
    if (empty($errors)) {
        // Sanitize inputs
        $title = $DBConnect->real_escape_string($title);
        $content = $DBConnect->real_escape_string($content);
        $tags = $DBConnect->real_escape_string($tags);

        // Create SQL query for insertion
        $sql = "INSERT INTO Posts (UserID, Title, Content, Timestamp, Tags)
                VALUES (?, ?, ?, NOW(), ?)";

        // Prepare and execute the statement
        $stmt = $DBConnect->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("isss", $user_id, $title, $content, $tags);

            if ($stmt->execute()) {
                // Success! Get the new post ID
                $post_id = $DBConnect->insert_id;
                $stmt->close();

                // Redirect to the newly created question
                header("Location: question.php?id=" . $post_id . "&created=1");
                exit();
            } else {
                // SQL execution failed
                $errors[] = "Database error: " . $stmt->error;
                $stmt->close();
            }
        } else {
            // Statement preparation failed
            $errors[] = "Database error: " . $DBConnect->error;
        }
    }
}

// Get a list of popular tags for the tag suggestions
// Try to get them from the database first
$popular_tags = [];
$tags_query = "SELECT Tags FROM Posts ORDER BY Timestamp DESC LIMIT 20";
$tags_result = $DBConnect->query($tags_query);

if ($tags_result && $tags_result->num_rows > 0) {
    $all_tags = [];
    while ($row = $tags_result->fetch_assoc()) {
        $post_tags = explode(',', $row['Tags']);
        foreach ($post_tags as $tag) {
            $tag = trim($tag);
            if (!empty($tag)) {
                if (!isset($all_tags[$tag])) {
                    $all_tags[$tag] = 1;
                } else {
                    $all_tags[$tag]++;
                }
            }
        }
    }

    // Sort by popularity
    arsort($all_tags);
    // Get the top 10 tags
    $popular_tags = array_slice(array_keys($all_tags), 0, 10);
}

// If we couldn't get tags from the database, use fallback list
if (empty($popular_tags)) {
    $popular_tags = ['php', 'javascript', 'css', 'html', 'mysql', 'python', 'react', 'node.js', 'jquery', 'angular'];
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DevSphere</title>
    <link rel="stylesheet" href="./assets/css/styles.css" />
    <?php if ($is_logged_in): ?>
    <link rel="stylesheet" href="./assets/css/question-form.css" />
    <?php endif; ?>
  </head>
  <body>
    <?php include_once 'header.php'; ?>
    <main>
      <div class="sidebar">
        <ul>
          <li>
            <a href="index.php" class="active">
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
        <?php if ($is_logged_in): ?>
          <!-- Question Posting Form for logged-in users -->
          <div class="question-form-container">
            <h1>Ask a Question</h1>
            <p class="subtitle">Get help from the community by asking a clear, concise question</p>

            <?php if (!empty($errors)): ?>
              <div class="error-container">
                <ul>
                  <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form method="POST" action="" class="question-form">
              <div class="form-group">
                <label for="title">Title</label>
                <input
                  type="text"
                  id="title"
                  name="title"
                  placeholder="e.g. How to implement authentication in PHP?"
                  value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                  required
                />
                <p class="form-help">Be specific and imagine you're asking a question to another person</p>
              </div>

              <div class="form-group">
                <label for="content">Question Details</label>
                <textarea
                  id="content"
                  name="content"
                  placeholder="Explain your problem in detail. Include what you've tried and your expected results."
                  required
                ><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                <p class="form-help">Include all relevant details that would help someone answer your question</p>
              </div>

              <div class="form-group">
                <label for="tags">Tags</label>
                <input
                  type="text"
                  id="tags"
                  name="tags"
                  placeholder="e.g. php,mysql,authentication"
                  value="<?= htmlspecialchars($_POST['tags'] ?? '') ?>"
                  required
                />
                <p class="form-help">Add up to 5 tags to describe what your question is about</p>

                <div class="tag-suggestions">
                  <span>Popular tags:</span>
                  <div class="tag-buttons">
                    <?php foreach ($popular_tags as $tag): ?>
                      <button type="button" class="tag-suggestion" onclick="addTag('<?= $tag ?>')"><?= htmlspecialchars($tag) ?></button>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>

              <div class="form-group form-actions">
                <button type="submit" name="submit_question" class="submit-button">Post Your Question</button>
              </div>
            </form>

            <div class="question-form-tips">
              <h2>Writing a good question</h2>
              <ul>
                <li><strong>Be specific</strong> about your problem</li>
                <li><strong>Describe what you've tried</strong> and what didn't work</li>
                <li><strong>Include code</strong> or error messages if applicable</li>
                <li><strong>Use proper formatting</strong> to make your question readable</li>
                <li><strong>Check for similar questions</strong> before posting</li>
              </ul>
            </div>
          </div>
        <?php else: ?>
          <!-- Login/Register Container for guests -->
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
        <?php endif; ?>
      </div>
    </main>
    <script type="module">
      import { setActive } from "./assets/js/navigation.js"
      setActive()
    </script>

    <?php if ($is_logged_in): ?>
    <script>
      function addTag(tag) {
        const tagsInput = document.getElementById('tags');
        const currentTags = tagsInput.value.split(',').filter(t => t.trim() !== '');

        // Check if tag already exists
        if (!currentTags.includes(tag)) {
          // Add the tag if we have less than 5 tags
          if (currentTags.length < 5) {
            if (currentTags.length === 0 || currentTags[0] === '') {
              tagsInput.value = tag;
            } else {
              tagsInput.value = currentTags.join(',') + ',' + tag;
            }
          } else {
            alert('You can only add up to 5 tags');
          }
        }
      }
    </script>
    <?php endif; ?>
  </body>
</html>
