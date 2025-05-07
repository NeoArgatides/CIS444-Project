<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    http_response_code(401);
    exit();
}

// Récupère les utilisateurs avec le nombre de posts créés
$query = "
    SELECT
        Users.UserID,
        Users.Username,
        Users.Email,
        Users.Role,
        Users.DateJoined,
        COUNT(Posts.PostID) AS PostCount
    FROM Users
    LEFT JOIN Posts ON Users.UserID = Posts.UserID
    GROUP BY Users.UserID
";

$stmt = $DBConnect->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
exit();
