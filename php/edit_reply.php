<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in to edit a reply']);
    exit();
}

// Check if necessary data is provided
if (!isset($_POST['reply_id']) || !isset($_POST['content']) || empty($_POST['content'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$reply_id = intval($_POST['reply_id']);
$content = trim($_POST['content']);
$user_id = $_SESSION['user_id'];

// Verify the reply exists and belongs to the current user
$check_reply = $DBConnect->prepare("
    SELECT UserID FROM Replies
    WHERE ReplyID = ?
");
$check_reply->bind_param("i", $reply_id);
$check_reply->execute();
$result = $check_reply->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Reply not found']);
    exit();
}

$reply_data = $result->fetch_assoc();
if ($reply_data['UserID'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'You can only edit your own replies']);
    exit();
}

// Update the reply
$update_reply = $DBConnect->prepare("
    UPDATE Replies
    SET Content = ?, Timestamp = NOW()
    WHERE ReplyID = ? AND UserID = ?
");
$update_reply->bind_param("sii", $content, $reply_id, $user_id);

if ($update_reply->execute()) {
    echo json_encode(['success' => true, 'message' => 'Reply updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $DBConnect->error]);
}
?>
