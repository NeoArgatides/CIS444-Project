<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to edit a post']);
    exit();
}

// Check if necessary data is provided
if (!isset($_POST['post_id']) || !isset($_POST['title']) || !isset($_POST['content']) ||
    empty($_POST['title']) || empty($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$post_id = intval($_POST['post_id']);
$title = trim($_POST['title']);
$content = trim($_POST['content']);
$tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';
$user_id = $_SESSION['user_id'];

// Verify the post exists and belongs to the current user
$check_post = $DBConnect->prepare("
    SELECT UserID FROM Posts
    WHERE PostID = ?
");
$check_post->bind_param("i", $post_id);
$check_post->execute();
$result = $check_post->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit();
}

$post_data = $result->fetch_assoc();
if ($post_data['UserID'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'You can only edit your own posts']);
    exit();
}

// Update the post
$update_post = $DBConnect->prepare("
    UPDATE Posts
    SET Title = ?, Content = ?, Tags = ?, Timestamp = NOW()
    WHERE PostID = ? AND UserID = ?
");
$update_post->bind_param("sssii", $title, $content, $tags, $post_id, $user_id);

if ($update_post->execute()) {
    echo json_encode(['success' => true, 'message' => 'Post updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $DBConnect->error]);
}
?>
