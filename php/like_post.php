<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get the post ID and action from the request
$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($post_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

if ($action !== 'like' && $action !== 'unlike') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// Get the current user ID
$user_id = $_SESSION['user_id'];

// Check if the post exists and is not from the current user
$check_post = $DBConnect->prepare("SELECT UserID FROM Posts WHERE PostID = ?");
$check_post->bind_param("i", $post_id);
$check_post->execute();
$result = $check_post->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit;
}

$post = $result->fetch_assoc();
if ($post['UserID'] == $user_id) {
    echo json_encode(['success' => false, 'message' => 'Cannot like your own post']);
    exit;
}

// Like or unlike action
if ($action === 'like') {
    // Check if the user already liked this post
    $check_like = $DBConnect->prepare("SELECT LikeID FROM Likes WHERE PostID = ? AND UserID = ?");
    $check_like->bind_param("ii", $post_id, $user_id);
    $check_like->execute();
    $like_result = $check_like->get_result();

    if ($like_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'You already liked this post']);
        exit;
    }

    // Add the like
    $add_like = $DBConnect->prepare("INSERT INTO Likes (PostID, UserID, Timestamp) VALUES (?, ?, NOW())");
    $add_like->bind_param("ii", $post_id, $user_id);
    $success = $add_like->execute();

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Post liked successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to like post']);
    }
} else {
    // Unlike action - remove the like
    $remove_like = $DBConnect->prepare("DELETE FROM Likes WHERE PostID = ? AND UserID = ?");
    $remove_like->bind_param("ii", $post_id, $user_id);
    $success = $remove_like->execute();

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Post unliked successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to unlike post']);
    }
}
?>
