<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get all posts that the user has liked
$stmt = $DBConnect->prepare("SELECT PostID FROM Likes WHERE UserID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$liked_posts = [];
while ($row = $result->fetch_assoc()) {
    $liked_posts[] = $row['PostID'];
}

echo json_encode(['success' => true, 'liked_posts' => $liked_posts]);
?>
