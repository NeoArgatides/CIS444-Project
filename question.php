<?php
session_start();
require_once 'php/config.php';

// Check if question ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: questionanswer.php");
    exit();
}

$question_id = intval($_GET['id']);
$logged_in = isset($_SESSION['user_id']);
$current_user_id = $logged_in ? $_SESSION['user_id'] : 0;

// Function to calculate time ago
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;

    if ($difference < 60) return "$difference second(s) ago";
    elseif ($difference < 3600) return floor($difference / 60) . " minute(s) ago";
    elseif ($difference < 86400) return floor($difference / 3600) . " hour(s) ago";
    elseif ($difference < 604800) return floor($difference / 86400) . " day(s) ago";
    else return date('F j, Y', $timestamp);
}

// Get question details
$stmt = $DBConnect->prepare("
    SELECT p.PostID, p.Title, p.Content, p.Timestamp, p.Tags, p.UserID,
           u.Username,
           (SELECT COUNT(*) FROM Replies r WHERE r.PostID = p.PostID) AS AnswerCount,
           (SELECT COUNT(*) FROM Likes l WHERE l.PostID = p.PostID) AS LikeCount
    FROM Posts p
    JOIN Users u ON p.UserID = u.UserID
    WHERE p.PostID = ?
");
$stmt->bind_param("i", $question_id);
$stmt->execute();
$question_result = $stmt->get_result();

if ($question_result->num_rows === 0) {
    header("Location: questionanswer.php");
    exit();
}

$question = $question_result->fetch_assoc();

// Check if the current user has liked this post
$user_liked = false;
if ($logged_in) {
    $like_check = $DBConnect->prepare("SELECT LikeID FROM Likes WHERE PostID = ? AND UserID = ?");
    $like_check->bind_param("ii", $question_id, $current_user_id);
    $like_check->execute();
    $user_liked = $like_check->get_result()->num_rows > 0;
}

// Get replies
$reply_stmt = $DBConnect->prepare("
    SELECT r.ReplyID, r.Content, r.Timestamp, r.UserID, u.Username
    FROM Replies r
    JOIN Users u ON r.UserID = u.UserID
    WHERE r.PostID = ?
    ORDER BY r.Timestamp ASC
");
$reply_stmt->bind_param("i", $question_id);
$reply_stmt->execute();
$replies_result = $reply_stmt->get_result();
$replies = [];
while ($reply = $replies_result->fetch_assoc()) {
    $replies[] = $reply;
}

// Handle reply submission
$reply_error = "";
$reply_success = false;

if ($logged_in && isset($_POST['submit_reply']) && !empty($_POST['reply_content'])) {
    $reply_content = $_POST['reply_content'];

    // Insert the reply
    $insert_reply = $DBConnect->prepare("
        INSERT INTO Replies (PostID, UserID, Content, Timestamp)
        VALUES (?, ?, ?, NOW())
    ");
    $insert_reply->bind_param("iis", $question_id, $current_user_id, $reply_content);

    if ($insert_reply->execute()) {
        $reply_success = true;

        // Redirect to avoid form resubmission
        header("Location: question.php?id=" . $question_id . "&replied=1");
        exit();
    } else {
        $reply_error = "Failed to post your reply. Please try again.";
    }
}

// Increment view count (could be implemented with a separate table for more accurate tracking)
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($question['Title']) ?> - DevSphere</title>
    <link rel="stylesheet" href="./assets/css/question.css" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
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
            <a href="questionanswer.php" class="active">
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
      <div class="main-content">
        <div class="content">
          <div class="question-header">
            <h1><?= htmlspecialchars($question['Title']) ?></h1>
            <div class="question-stats">
              <span>Asked <?= time_ago($question['Timestamp']) ?></span>
              <span>Viewed <?= rand(10, 100) ?> times</span>
            </div>
            <a href="index.php" class="ask-button">Ask Question</a>
          </div>

          <div class="post-layout">
            <div class="vote-cell">
              <?php if ($logged_in && $current_user_id != $question['UserID']): ?>
                <button class="vote-button <?= $user_liked ? 'liked' : '' ?>"
                        id="like-button"
                        data-post-id="<?= $question['PostID'] ?>"
                        data-liked="<?= $user_liked ? 'true' : 'false' ?>">
                  <svg width="24" height="24" viewBox="0 0 24 24" class="like-icon">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                </svg>
              </button>
              <?php else: ?>
                <div class="static-heart">
                  <svg width="24" height="24" viewBox="0 0 24 24" class="like-icon-static">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                </svg>
                </div>
              <?php endif; ?>
              <span class="vote-count" id="like-count"><?= $question['LikeCount'] ?></span>
              <span class="stat-label">likes</span>
            </div>

            <div class="post-content">
              <div class="post-body">
                <?= nl2br(htmlspecialchars(str_replace(['\r\n', '\r', '\n'], PHP_EOL, $question['Content']))) ?>

                <?php if ($logged_in && $current_user_id == $question['UserID']): ?>
                <div id="question-edit-form" class="edit-form" style="display: none;">
                  <form id="edit-question-form">
                    <input type="hidden" name="post_id" value="<?= $question['PostID'] ?>">
                    <div class="form-group">
                      <label for="edit-title">Title</label>
                      <input type="text" id="edit-title" name="title" value="<?= htmlspecialchars($question['Title']) ?>" required>
                    </div>
                    <div class="form-group">
                      <label for="edit-content">Content</label>
                      <textarea id="edit-content" name="content" required><?= htmlspecialchars($question['Content']) ?></textarea>
                    </div>
                    <div class="form-group">
                      <label for="edit-tags">Tags</label>
                      <input type="text" id="edit-tags" name="tags" value="<?= htmlspecialchars($question['Tags']) ?>">
                      <small>Separate tags with commas</small>
                    </div>
                    <div class="form-actions">
                      <button type="button" class="cancel-button" onclick="toggleQuestionEdit()">Cancel</button>
                      <button type="button" class="save-button" onclick="saveQuestionEdit()">Save Edits</button>
                    </div>
                  </form>
                </div>
                <?php endif; ?>

                <div class="post-tags">
                  <?php foreach (explode(',', $question['Tags']) as $tag): ?>
                    <?php $tag = trim($tag); ?>
                    <?php if (!empty($tag)): ?>
                      <a href="questionanswer.php?tag=<?= urlencode($tag) ?>" class="tag"><?= htmlspecialchars($tag) ?></a>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              </div>

              <div class="post-footer">
                <div class="post-actions">
                  <button class="share-button">Share</button>
                  <?php if ($logged_in && $current_user_id == $question['UserID']): ?>
                    <button type="button" class="edit-button" id="question-edit-btn" onclick="toggleQuestionEdit()">Edit</button>
                    <button type="button" class="delete-button" onclick="confirmDelete('post', <?= $question['PostID'] ?>)">Delete</button>
                  <?php endif; ?>
                </div>
                <div class="post-author">
                  <div class="author-info">
                    <span class="asked">asked <?= time_ago($question['Timestamp']) ?></span>
                    <div class="user-card">
                      <img
                        src="https://ui-avatars.com/api/?name=<?= urlencode($question['Username']) ?>&background=random"
                        alt="author avatar"
                        class="user-avatar"
                      />
                      <a href="user.php?id=<?= $question['UserID'] ?>" class="username"><?= htmlspecialchars($question['Username']) ?></a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <?php if (count($replies) > 0): ?>
            <div class="answers-section">
              <h2><?= count($replies) ?> Answer<?= count($replies) > 1 ? 's' : '' ?></h2>

              <?php foreach ($replies as $reply): ?>
                <div class="answer" id="reply-<?= $reply['ReplyID'] ?>">
                  <div class="post-layout">
                    <div class="post-content">
                      <div class="post-body">
                        <?= nl2br(htmlspecialchars(str_replace(['\r\n', '\r', '\n'], PHP_EOL, $reply['Content']))) ?>
                      </div>
                      <div class="post-footer">
                        <div class="post-actions">
                          <?php if ($logged_in && $current_user_id == $reply['UserID']): ?>
                            <button type="button" class="edit-button" id="reply-edit-btn-<?= $reply['ReplyID'] ?>" onclick="toggleReplyEdit(<?= $reply['ReplyID'] ?>)">Edit</button>
                            <button type="button" class="delete-button" onclick="confirmDelete('reply', <?= $reply['ReplyID'] ?>)">Delete</button>
                          <?php endif; ?>
                        </div>
                        <div class="post-author">
                          <div class="author-info">
                            <span class="answered">answered <?= time_ago($reply['Timestamp']) ?></span>
                            <div class="user-card">
                              <img
                                src="https://ui-avatars.com/api/?name=<?= urlencode($reply['Username']) ?>&background=random"
                                alt="author avatar"
                                class="user-avatar"
                              />
                              <a href="user.php?id=<?= $reply['UserID'] ?>" class="username"><?= htmlspecialchars($reply['Username']) ?></a>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <?php if ($logged_in && $current_user_id == $reply['UserID']): ?>
                  <div id="reply-edit-form-<?= $reply['ReplyID'] ?>" class="edit-form" style="display: none;">
                    <form id="edit-reply-form-<?= $reply['ReplyID'] ?>">
                      <input type="hidden" name="reply_id" value="<?= $reply['ReplyID'] ?>">
                      <div class="form-group">
                        <label for="edit-reply-content-<?= $reply['ReplyID'] ?>">Content</label>
                        <textarea id="edit-reply-content-<?= $reply['ReplyID'] ?>" name="content" required><?= htmlspecialchars($reply['Content']) ?></textarea>
                      </div>
                      <div class="form-actions">
                        <button type="button" class="cancel-button" onclick="toggleReplyEdit(<?= $reply['ReplyID'] ?>)">Cancel</button>
                        <button type="button" class="save-button" onclick="saveReplyEdit(<?= $reply['ReplyID'] ?>)">Save Edits</button>
                      </div>
                    </form>
                  </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <?php if ($logged_in): ?>
          <div class="your-answer">
            <h2>Your Answer</h2>
              <?php if (!empty($reply_error)): ?>
                <div class="error-message"><?= $reply_error ?></div>
              <?php endif; ?>
              <?php if (isset($_GET['replied']) && $_GET['replied'] == '1'): ?>
                <div class="success-message">Your answer has been posted successfully!</div>
              <?php endif; ?>
              <form method="POST" action="">

                <textarea name="reply_content" id="reply_content" required placeholder="Write your answer here..."></textarea>
                <button type="submit" name="submit_reply" class="post-answer" id="post-answer-btn" disabled>Post Your Answer</button>
              </form>
            </div>
          <?php else: ?>
            <div class="login-prompt">
              <p>You must <a href="login.php">log in</a> to answer this question.</p>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </main>

    <div id="toast-container"></div>

    <div id="delete-modal" class="modal">
      <div class="modal-content">
        <h3>Confirm Deletion</h3>
        <p id="delete-message">Are you sure you want to delete this?</p>
        <div class="modal-actions">
          <button id="cancel-delete" class="cancel-button">Cancel</button>
          <button id="confirm-delete" class="delete-button">Delete</button>
        </div>
      </div>
    </div>

    <script>
      // Function for text editor buttons
      function insertText(before, after) {
        const textarea = document.getElementById('reply_content');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selectedText = textarea.value.substring(start, end);
        const newText = before + selectedText + after;
        textarea.value = textarea.value.substring(0, start) + newText + textarea.value.substring(end);

        // Set cursor position after the inserted text
        const newCursorPos = start + newText.length;
        textarea.focus();
        textarea.setSelectionRange(newCursorPos, newCursorPos);
      }

      // Like functionality
      document.addEventListener('DOMContentLoaded', function() {
        const likeButton = document.getElementById('like-button');
        if (likeButton) {
          likeButton.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const liked = this.getAttribute('data-liked') === 'true';
            const likeCount = document.getElementById('like-count');

            // Toggle visually first for immediate feedback
            this.setAttribute('data-liked', !liked);
            this.classList.toggle('liked');
            const currentCount = parseInt(likeCount.textContent);
            likeCount.textContent = liked ? (currentCount - 1) : (currentCount + 1);

            // Send like/unlike request to server
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
                // Revert if there was an error
                this.setAttribute('data-liked', liked);
                this.classList.toggle('liked');
                likeCount.textContent = currentCount;
                console.error('Like action failed:', data.message);
              }
            })
            .catch(error => {
              // Revert if there was an error
              this.setAttribute('data-liked', liked);
              this.classList.toggle('liked');
              likeCount.textContent = currentCount;
              console.error('Error:', error);
            });
          });
        }

        // Share button functionality
        const shareButtons = document.querySelectorAll('.share-button');
        shareButtons.forEach(button => {
          button.addEventListener('click', function() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
              showToast('Link copied to clipboard!');
            }).catch(err => {
              console.error('Failed to copy: ', err);
              showToast('Failed to copy link', 3000);
            });
          });
        });
      });

      // Add form validation to enable/disable the submit button
      document.addEventListener('DOMContentLoaded', function() {
        const replyTextarea = document.getElementById('reply_content');
        const submitButton = document.getElementById('post-answer-btn');

        if (replyTextarea && submitButton) {
          // Initial check on page load
          updateButtonState();

          // Add input event listener to textarea
          replyTextarea.addEventListener('input', updateButtonState);

          function updateButtonState() {
            // Enable button only if textarea has content
            if (replyTextarea.value.trim() !== '') {
              submitButton.disabled = false;
              submitButton.classList.add('active');
            } else {
              submitButton.disabled = true;
              submitButton.classList.remove('active');
            }
          }
        }
      });

      // Fix the toggleQuestionEdit function to hide the button when editing
      function toggleQuestionEdit() {
        const postBody = document.querySelector('.post-content .post-body');
        const editForm = document.getElementById('question-edit-form');
        const postTags = document.querySelector('.post-tags');
        const editButton = document.getElementById('question-edit-btn');
        const actionsContainer = editButton.parentElement;

        if (editForm.style.display === 'none') {
          // Hide content but keep the edit form visible
          postTags.style.display = 'none';
          // We need to hide the content but not the edit form which is inside post-body
          const contentElements = postBody.childNodes;
          for (let i = 0; i < contentElements.length; i++) {
            if (contentElements[i].nodeType === 1 &&
                contentElements[i].id !== 'question-edit-form') {
              contentElements[i].style.display = 'none';
            }
          }
          editForm.style.display = 'block';
          // Hide the edit button completely instead of changing text
          editButton.style.display = 'none';
        } else {
          // Show content and hide the edit form
          postTags.style.display = 'flex';
          const contentElements = postBody.childNodes;
          for (let i = 0; i < contentElements.length; i++) {
            if (contentElements[i].nodeType === 1 &&
                contentElements[i].id !== 'question-edit-form') {
              contentElements[i].style.display = '';
            }
          }
          editForm.style.display = 'none';
          // Show the edit button again
          editButton.style.display = 'inline-flex';
        }
      }

      // Fix the toggleReplyEdit function to hide the button when editing
      function toggleReplyEdit(replyId) {
        const replyElement = document.getElementById(`reply-${replyId}`);
        if (!replyElement) {
          console.error(`Reply element with ID reply-${replyId} not found`);
          return;
        }

        const replyBody = replyElement.querySelector('.post-body');
        const editForm = document.getElementById(`reply-edit-form-${replyId}`);
        const editButton = document.getElementById(`reply-edit-btn-${replyId}`);

        if (editForm.style.display === 'none') {
          replyBody.style.display = 'none';
          editForm.style.display = 'block';
          // Hide the edit button completely
          editButton.style.display = 'none';
        } else {
          replyBody.style.display = 'block';
          editForm.style.display = 'none';
          // Show the edit button again
          editButton.style.display = 'inline-flex';
        }
      }

      // Save question edits
      function saveQuestionEdit() {
        const form = document.getElementById('edit-question-form');
        const formData = new FormData(form);

        fetch('php/edit_post.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Reload the page to show updated content
            window.location.reload();
          } else {
            alert('Failed to save changes: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while saving changes');
        });
      }

      // Save reply edits
      function saveReplyEdit(replyId) {
        const form = document.getElementById(`edit-reply-form-${replyId}`);
        const formData = new FormData(form);

        fetch('php/edit_reply.php', {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Reload the page to show updated content
            window.location.reload();
          } else {
            alert('Failed to save changes: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred while saving changes');
        });
      }

      // Add this toast function
      function showToast(message, duration = 3000) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerText = message;

        const container = document.getElementById('toast-container');
        container.appendChild(toast);

        // Make the toast visible
        setTimeout(() => {
          toast.classList.add('show');
        }, 10);

        // Remove the toast after duration
        setTimeout(() => {
          toast.classList.remove('show');
          setTimeout(() => {
            container.removeChild(toast);
          }, 300);
        }, duration);
      }

      // Deletion functionality
      let deleteType = '';
      let deleteId = 0;

      function confirmDelete(type, id) {
        deleteType = type;
        deleteId = id;

        const modal = document.getElementById('delete-modal');
        const message = document.getElementById('delete-message');

        if (type === 'post') {
          message.textContent = 'Are you sure you want to delete this question? This cannot be undone.';
        } else {
          message.textContent = 'Are you sure you want to delete this answer? This cannot be undone.';
        }

        modal.style.display = 'flex';
      }

      document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('delete-modal');
        const cancelBtn = document.getElementById('cancel-delete');
        const confirmBtn = document.getElementById('confirm-delete');

        cancelBtn.addEventListener('click', function() {
          modal.style.display = 'none';
        });

        confirmBtn.addEventListener('click', function() {
          // Handle the delete action
          const endpoint = deleteType === 'post' ? 'php/delete_post.php' : 'php/delete_reply.php';
          const idParam = deleteType === 'post' ? 'post_id' : 'reply_id';

          fetch(endpoint, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `${idParam}=${deleteId}`
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // If deleting a question, redirect to the questions list
              if (deleteType === 'post') {
                window.location.href = 'questionanswer.php';
              } else {
                // If deleting a reply, reload the current page
                window.location.reload();
              }
            } else {
              showToast('Error: ' + data.message);
              modal.style.display = 'none';
            }
          })
          .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while deleting');
            modal.style.display = 'none';
          });
        });

        // Close the modal if clicked outside
        window.addEventListener('click', function(event) {
          if (event.target === modal) {
            modal.style.display = 'none';
          }
        });
      });
    </script>
    <style>
      /* Improved button styles with better contrast */
      .post-actions button,
      .post-actions a,
      .form-actions button {
        background-color: #f0f2f4;
        color: #3c4146;
        border: 1px solid #c9cdd3;
        border-radius: 4px;
        padding: 8px 14px;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        margin: 4px 0;
      }

      /* Regular button hover/focus states */
      .post-actions button:hover,
      .post-actions a:hover,
      .form-actions .cancel-button:hover {
        background-color: #e3e6ea;
        border-color: #adb5bd;
        box-shadow: 0 2px 6px rgba(0,0,0,0.12);
      }

      /* Share button specific styling */
      .share-button {
        background-color: #e6f0fa !important;
        color: #0366d6 !important;
        border-color: #a8c7e5 !important;
      }

      .share-button:hover {
        background-color: #d1e5f8 !important;
        border-color: #7fb0e0 !important;
      }

      /* Edit button specific styling */
      .edit-button {
        background-color: #e7f5ee !important;
        color: #28a745 !important;
        border-color: #a3d9b1 !important;
      }

      .edit-button:hover {
        background-color: #d4eddf !important;
        border-color: #86c798 !important;
      }

      /* Update cancel button to red */
      .cancel-button {
        background-color: #fce8e6 !important;
        color: #d32f2f !important;
        border: 1px solid #f5c2c7 !important;
        padding: 8px 16px;
        font-weight: 500;
        transition: all 0.2s ease;
      }

      .cancel-button:hover {
        background-color: #f8d7da !important;
        border-color: #f1aeb5 !important;
        color: #b71c1c !important;
        box-shadow: 0 2px 4px rgba(244,67,54,0.15) !important;
      }

      /* Save button */
      .save-button {
        background-color: #0a95ff !important;
        color: white !important;
        border: none !important;
        padding: 8px 16px;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(10,149,255,0.2) !important;
      }

      .save-button:hover {
        background-color: #0074cc !important;
        box-shadow: 0 3px 6px rgba(10,149,255,0.3) !important;
      }

      /* Like button refinements */
      .vote-button {
        background: none !important;
        border: none !important;
        border-radius: 0;
        width: auto;
        height: auto;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: all 0.2s ease;
        box-shadow: none !important;
        padding: 5px;
        margin: 5px 0;
        cursor: pointer;
      }

      .vote-button svg {
        fill: none;
        stroke: #666;
        stroke-width: 2px;
        transition: all 0.2s;
      }

      .vote-button:hover svg {
        transform: scale(1.1);
        stroke: #999;
      }

      .vote-button.liked svg {
        fill: #ff4d4d;
        stroke: #ff4d4d;
      }

      .vote-count {
        font-size: 18px;
        font-weight: bold;
        margin: 5px 0;
        text-align: center;
        color: #3c4146;
      }

      /* Post Answer button improvements */
      .post-answer {
        background-color: #0a95ff;
        color: white;
        border: none;
        padding: 10px 18px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        margin-top: 16px;
        box-shadow: 0 2px 4px rgba(10,149,255,0.2);
      }

      .post-answer.active:hover {
        background-color: #0074cc;
        box-shadow: 0 3px 6px rgba(10,149,255,0.3);
      }

      /* Editor toolbar buttons */
      .editor-toolbar button {
        background-color: #f0f2f4;
        border: 1px solid #c9cdd3;
        border-radius: 3px;
        padding: 6px 10px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.2s ease;
      }

      .editor-toolbar button:hover {
        background-color: #e3e6ea;
        border-color: #adb5bd;
      }

      /* Additional styles for the question page */
      .post-layout {
        display: flex;
        position: relative;
      }

      .vote-cell {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding-right: 16px;
        min-width: 40px;
      }

      .post-content {
        flex-grow: 1;
        display: flex;
        flex-direction: row;
      }

      .post-body {
        line-height: 1.6;
        font-size: 15px;
        color: #242729;
        flex-grow: 1;
        padding-bottom: 16px;
      }

      .post-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin: 16px 0;
      }

      .tag {
        background-color: #e1ecf4;
        color: #39739d;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 12px;
        text-decoration: none;
      }

      .post-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
      }

      .post-actions {
        display: flex;
        gap: 8px;
        align-items: center;
      }

      .post-actions button, .post-actions a {
        background: none;
        border: none;
        color: #6a737c;
        cursor: pointer;
        font-size: 13px;
        text-decoration: none;
      }

      .author-info {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        max-width: 180px;
      }

      .asked, .answered {
        color: #6a737c;
        font-size: 12px;
        margin-bottom: 4px;
      }

      .user-card {
        display: flex;
        align-items: center;
        gap: 8px;
        background-color: #f8f9f9;
        padding: 4px 6px;
        border-radius: 3px;
        max-width: fit-content;
      }

      .user-avatar {
        width: 24px;
        height: 24px;
        border-radius: 3px;
      }

      .username {
        color: #0074cc;
        text-decoration: none;
        font-size: 12px;
      }

      .answers-section {
        margin-top: 30px;
      }

      .answers-section h2 {
        font-size: 19px;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e3e6e8;
      }

      .answer {
        margin-bottom: 30px;
        border-bottom: 1px solid #e3e6e8;
        padding-bottom: 20px;
      }

      .answer:last-child {
        border-bottom: none;
      }

      .your-answer {
        margin-top: 30px;
      }

      .your-answer h2 {
        font-size: 19px;
        margin-bottom: 20px;
      }

      .editor-toolbar {
        display: flex;
        gap: 4px;
        margin-bottom: 8px;
        flex-wrap: wrap;
        background-color: #f8f9f9;
        padding: 8px;
        border: 1px solid #e3e6e8;
        border-bottom: none;
        border-top-left-radius: 3px;
        border-top-right-radius: 3px;
      }

      .editor-toolbar button {
        background: none;
        border: 1px solid #d6d9dc;
        border-radius: 3px;
        padding: 6px 10px;
        cursor: pointer;
        font-size: 14px;
      }

      .separator {
        width: 1px;
        background-color: #d6d9dc;
        margin: 0 8px;
      }

      textarea {
        width: 100%;
        min-height: 200px;
        padding: 10px;
        border: 1px solid #e3e6e8;
        border-radius: 3px;
        font-family: inherit;
        font-size: 15px;
        line-height: 1.5;
        resize: vertical;
      }

      .post-answer {
        background-color: #0a95ff;
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 4px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        margin-top: 16px;
      }

      .post-answer:hover {
        background-color: #0074cc;
      }

      .login-prompt {
        margin-top: 30px;
        background-color: #fdf7e3;
        padding: 16px;
        border: 1px solid #f1e5bc;
        border-radius: 3px;
      }

      .login-prompt p {
        margin: 0;
        color: #6a737c;
      }

      .login-prompt a {
        color: #0074cc;
        text-decoration: none;
      }

      .error-message {
        background-color: #fdf3f3;
        color: #c92a2a;
        padding: 10px;
        border: 1px solid #e8c7c7;
        border-radius: 3px;
        margin-bottom: 16px;
      }

      .success-message {
        background-color: #e9f6ea;
        color: #2e7d32;
        padding: 10px;
        border: 1px solid #c8e6c9;
        border-radius: 3px;
        margin-bottom: 16px;
      }

      /* Make special adjustments for the answer sections */
      .answer .post-footer {
        top: 16px;
        right: 16px;
      }

      /* Style for disabled button */
      .post-answer:disabled {
        background-color: #a0a0a0;
        cursor: not-allowed;
        opacity: 0.7;
      }

      .post-answer.active {
        background-color: #0a95ff;
        cursor: pointer;
        opacity: 1;
      }

      .post-answer.active:hover {
        background-color: #0074cc;
      }

      /* Edit form styles */
      .edit-form {
        background-color: #f8f9fa;
        padding: 15px;
        border: 1px solid #e3e6e8;
        border-radius: 4px;
        margin-bottom: 20px;
      }

      .form-group {
        margin-bottom: 15px;
      }

      .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        font-size: 14px;
      }

      .form-group input[type="text"],
      .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #d6d9dc;
        border-radius: 3px;
        font-family: inherit;
        font-size: 15px;
      }

      .form-group textarea {
        min-height: 150px;
      }

      .form-group small {
        display: block;
        margin-top: 5px;
        color: #6a737c;
        font-size: 12px;
      }

      /* Toast notification styles */
      #toast-container {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 9999;
      }

      .toast {
        background-color: #323232;
        color: white;
        padding: 12px 24px;
        border-radius: 4px;
        font-size: 14px;
        margin-top: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        transform: translateY(100px);
        opacity: 0;
        transition: transform 0.3s, opacity 0.3s;
        max-width: 280px;
      }

      .toast.show {
        transform: translateY(0);
        opacity: 1;
      }

      /* Delete button styling */
      .delete-button {
        background-color: #fce8e6 !important;
        color: #d32f2f !important;
        border-color: #f5c2c7 !important;
      }

      .delete-button:hover {
        background-color: #f8d7da !important;
        border-color: #f1aeb5 !important;
        color: #b71c1c !important;
        box-shadow: 0 2px 4px rgba(244,67,54,0.15) !important;
      }

      /* Modal styles */
      .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.4);
        z-index: 10000;
        justify-content: center;
        align-items: center;
      }

      .modal-content {
        background-color: white;
        padding: 24px;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
      }

      .modal h3 {
        margin-top: 0;
        color: #d32f2f;
      }

      .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
      }

      /* Add these styles */
      .static-heart {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 5px;
        margin: 5px 0;
      }

      .like-icon-static {
        fill: #ddd;
        stroke: #aaa;
        stroke-width: 1px;
      }

      .stat-label {
        font-size: 12px;
        color: #6a737c;
        text-align: center;
      }
    </style>
  </body>
</html>
