<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to delete a reply']);
    exit();
}

// Check if reply_id is provided
if (!isset($_POST['reply_id']) || empty($_POST['reply_id'])) {
    echo json_encode(['success' => false, 'message' => 'No reply specified']);
    exit();
}

$reply_id = intval($_POST['reply_id']);
$user_id = $_SESSION['user_id'];

// Check reply exists and belongs to the current user
$check_query = $DBConnect->prepare("SELECT UserID FROM Replies WHERE ReplyID = ?");
$check_query->bind_param("i", $reply_id);
$check_query->execute();
$result = $check_query->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Reply not found']);
    exit();
}

$reply = $result->fetch_assoc();
if ($reply['UserID'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'You can only delete your own replies']);
    exit();
}

// Delete the reply
$delete_reply = $DBConnect->prepare("DELETE FROM Replies WHERE ReplyID = ? AND UserID = ?");
$delete_reply->bind_param("ii", $reply_id, $user_id);
$delete_reply->execute();

if ($delete_reply->affected_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete reply']);
    exit();
}

echo json_encode(['success' => true]);
?>
