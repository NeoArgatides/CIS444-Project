<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Récupère le terme de recherche
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($search)) {
    echo json_encode([]);
    exit();
}

// Prépare la requête avec LIKE
$searchTerm = '%' . $search . '%';
$stmt = $DBConnect->prepare("
    SELECT Users.UserID, Users.Username, COUNT(Posts.PostID) AS PostCount
    FROM Users
    LEFT JOIN Posts ON Users.UserID = Posts.UserID
    WHERE Users.Username LIKE ?
    GROUP BY Users.UserID
    LIMIT 10
");
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = [
        'UserID' => $row['UserID'],
        'Username' => $row['Username'],
        'PostCount' => (int) $row['PostCount']
    ];
}

echo json_encode($users);
exit();
