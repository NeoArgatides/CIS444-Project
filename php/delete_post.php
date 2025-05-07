<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to delete a post']);
    exit();
}

// Check if post_id is provided
if (!isset($_POST['post_id']) || empty($_POST['post_id'])) {
    echo json_encode(['success' => false, 'message' => 'No post specified']);
    exit();
}

$post_id = intval($_POST['post_id']);
$user_id = $_SESSION['user_id'];

// Check post exists and belongs to the current user
$check_query = $DBConnect->prepare("SELECT UserID FROM Posts WHERE PostID = ?");
$check_query->bind_param("i", $post_id);
$check_query->execute();
$result = $check_query->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit();
}

$post = $result->fetch_assoc();
if ($post['UserID'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'You can only delete your own posts']);
    exit();
}

// Start transaction
$DBConnect->begin_transaction();

try {
    // Delete all replies to this post
    $delete_replies = $DBConnect->prepare("DELETE FROM Replies WHERE PostID = ?");
    $delete_replies->bind_param("i", $post_id);
    $delete_replies->execute();

    // Delete all likes for this post
    $delete_likes = $DBConnect->prepare("DELETE FROM Likes WHERE PostID = ?");
    $delete_likes->bind_param("i", $post_id);
    $delete_likes->execute();

    // Delete the post
    $delete_post = $DBConnect->prepare("DELETE FROM Posts WHERE PostID = ? AND UserID = ?");
    $delete_post->bind_param("ii", $post_id, $user_id);
    $delete_post->execute();

    if ($delete_post->affected_rows === 0) {
        throw new Exception("Failed to delete post");
    }

    // Commit transaction
    $DBConnect->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback on error
    $DBConnect->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
