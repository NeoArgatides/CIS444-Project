<?php
session_start();
require_once 'php/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    // Display access denied message instead of redirecting, but include sidebar
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Access Denied - DevSphere</title>
        <link rel="stylesheet" href="./assets/css/styles.css" />
        <style>
            .access-denied {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 80vh;
                text-align: center;
                padding: 20px;
            }
            .access-denied h1 {
                color: #c91010;
                margin-bottom: 10px;
            }
            .access-denied p {
                font-size: 18px;
                margin-bottom: 20px;
            }
            .access-denied a {
                padding: 10px 20px;
                background-color: #0074cc;
                color: white;
                text-decoration: none;
                border-radius: 4px;
            }
        </style>
    </head>
    <body>
        ';
    include_once 'header.php';
    echo '
        <main>
            <div class="sidebar">
                <ul>
                    <li><a href="index.php"><img src="https://img.icons8.com/?size=100&id=67881&format=png&color=000000" alt="Ask" /><span>Ask</span></a></li>
                    <li><a href="users.php"><img src="https://img.icons8.com/?size=100&id=98957&format=png&color=000000" alt="users" /><span>Users</span></a></li>
                    <li><a href="questionanswer.php"><img src="https://img.icons8.com/?size=100&id=2908&format=png&color=000000" alt="q&a" /><span>Q&A</span></a></li>
                    <li><a href="admin.php" class="active"><img src="https://img.icons8.com/?size=100&id=12599&format=png&color=000000" alt="admin" /><span>Admin</span></a></li>
                </ul>
                <div class="links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">User agreement</a>
                </div>
            </div>
            <div class="page">
                <div class="access-denied">
                    <h1>Access Denied</h1>
                    <p>You must be an administrator to access this page.</p>
                    <a href="index.php">Return to Homepage</a>
                </div>
            </div>
        </main>
    </body>
    </html>';
    exit();
}

// Handle delete operations
if (isset($_GET['delete']) && isset($_GET['type']) && isset($_GET['id'])) {
    $type = $_GET['type'];
    $id = (int)$_GET['id'];
    $table = '';
    $id_column = '';

    switch ($type) {
        case 'user':
            $table = 'Users';
            $id_column = 'UserID';
            break;
        case 'post':
            $table = 'Posts';
            $id_column = 'PostID';
            break;
        case 'reply':
            $table = 'Replies';
            $id_column = 'ReplyID';
            break;
        case 'like':
            $table = 'Likes';
            $id_column = 'LikeID';
            break;
    }

    if (!empty($table) && !empty($id_column)) {
        // Record admin action
        $admin_id = $_SESSION['user_id'];
        $target_user_id = null;
        $target_post_id = null;
        $target_reply_id = null;

        // Get target user ID for logging
        if ($type === 'user') {
            $target_user_id = $id;
        } else {
            $query = "SELECT UserID FROM $table WHERE $id_column = ?";
            $stmt = $DBConnect->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $target_user_id = $row['UserID'];
            }

            if ($type === 'post') {
                $target_post_id = $id;
            } else if ($type === 'reply') {
                $target_reply_id = $id;
            }
        }

        // Log admin action
        $action_query = "INSERT INTO AdminActions (AdminID, TargetUserID, ActionType, TargetPostID, TargetReplyID, Notes)
                        VALUES (?, ?, 'Delete', ?, ?, 'Deleted by admin')";
        $action_stmt = $DBConnect->prepare($action_query);
        $action_stmt->bind_param("iiis", $admin_id, $target_user_id, $target_post_id, $target_reply_id);
        $action_stmt->execute();

        // Delete the record
        $delete_query = "DELETE FROM $table WHERE $id_column = ?";
        $delete_stmt = $DBConnect->prepare($delete_query);
        $delete_stmt->bind_param("i", $id);
        $delete_stmt->execute();

        header("Location: admin.php?type=$type&deleted=1");
        exit();
    }
}

// Default view is users
$type = isset($_GET['type']) ? $_GET['type'] : 'user';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare query based on content type
$query = '';
switch ($type) {
    case 'user':
        $query = "SELECT * FROM Users";
        if (!empty($search)) {
            $search = $DBConnect->real_escape_string($search);
            $query .= " WHERE Username LIKE '%$search%' OR Email LIKE '%$search%'";
        }
        $query .= " ORDER BY DateJoined DESC";
        break;
    case 'post':
        $query = "SELECT p.*, u.Username FROM Posts p JOIN Users u ON p.UserID = u.UserID";
        if (!empty($search)) {
            $search = $DBConnect->real_escape_string($search);
            $query .= " WHERE p.Title LIKE '%$search%' OR p.Content LIKE '%$search%' OR p.Tags LIKE '%$search%'";
        }
        $query .= " ORDER BY p.Timestamp DESC";
        break;
    case 'reply':
        $query = "SELECT r.*, u.Username, p.Title as PostTitle FROM Replies r
                 JOIN Users u ON r.UserID = u.UserID
                 JOIN Posts p ON r.PostID = p.PostID";
        if (!empty($search)) {
            $search = $DBConnect->real_escape_string($search);
            $query .= " WHERE r.Content LIKE '%$search%'";
        }
        $query .= " ORDER BY r.Timestamp DESC";
        break;
    case 'like':
        $query = "SELECT l.*, u.Username, p.Title as PostTitle FROM Likes l
                 JOIN Users u ON l.UserID = u.UserID
                 JOIN Posts p ON l.PostID = p.PostID";
        if (!empty($search)) {
            $search = $DBConnect->real_escape_string($search);
            $query .= " WHERE p.Title LIKE '%$search%' OR u.Username LIKE '%$search%'";
        }
        $query .= " ORDER BY l.Timestamp DESC";
        break;
    case 'adminaction':
        $query = "SELECT a.*, admin.Username as AdminUsername, target.Username as TargetUsername,
                 p.Title as PostTitle FROM AdminActions a
                 LEFT JOIN Users admin ON a.AdminID = admin.UserID
                 LEFT JOIN Users target ON a.TargetUserID = target.UserID
                 LEFT JOIN Posts p ON a.TargetPostID = p.PostID";
        if (!empty($search)) {
            $search = $DBConnect->real_escape_string($search);
            $query .= " WHERE a.ActionType LIKE '%$search%' OR admin.Username LIKE '%$search%'
                     OR target.Username LIKE '%$search%' OR a.Notes LIKE '%$search%'";
        }
        $query .= " ORDER BY a.Timestamp DESC";
        break;
}

$result = $DBConnect->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Panel - DevSphere</title>
    <link rel="stylesheet" href="./assets/css/styles.css" />
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            overflow: hidden;
        }

        main {
            display: flex;
            height: calc(100vh - 60px);
            width: 100%;
            overflow: hidden;
        }

        .sidebar {
            height: 100%;
            overflow-y: auto;
            flex-shrink: 0;
        }

        .page {
            flex: 1;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .admin-container {
            display: flex;
            flex-direction: column;
            padding: 20px;
            height: 100%;
            width: 100%;
            box-sizing: border-box;
            overflow: hidden; /* Prevent container from scrolling */
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #e3e6e8;
            padding-bottom: 15px;
            flex-shrink: 0;
            width: 100%;
        }

        .tab-navigation {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #e3e6e8;
            flex-shrink: 0;
            width: 100%;
        }

        .tab-button {
            padding: 10px 20px;
            background: none;
            border: none;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            color: #6a737c;
        }

        .tab-button.active {
            color: #0c0d0e;
            border-bottom: 2px solid #0074cc;
        }

        .search-bar {
            display: flex;
            margin-bottom: 20px;
            width: 100%;
            max-width: 600px;
            flex-shrink: 0;
        }

        .search-bar input {
            flex: 1;
            padding: 8px 12px;
            border: 1px solid #e3e6e8;
            border-radius: 3px 0 0 3px;
        }

        .search-bar button {
            padding: 8px 16px;
            background-color: #0074cc;
            color: white;
            border: none;
            border-radius: 0 3px 3px 0;
            cursor: pointer;
        }

        .table-container {
            height: 400px;
            max-height: 550px;
            overflow-y: auto;
            overflow-x: hidden;
            width: 100%;
            border: 1px solid #e3e6e8;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            flex-shrink: 0;
            flex-grow: 1;
            background-color: white;
        }

        .admin-table {
            width: 100%;
            min-width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .admin-table th {
            background-color: #f8f9f9;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #e3e6e8;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .admin-table td {
            padding: 10px;
            border-bottom: 1px solid #e3e6e8;
            word-wrap: break-word;
            vertical-align: middle;
            height: 45px;
            box-sizing: border-box;
        }

        .admin-table tr:hover {
            background-color: #f8f9f9;
        }

        .action-buttons {
            height: 100%;
        }

        .action-buttons a, .action-buttons button {
            padding: 6px 12px;
            border-radius: 3px;
            text-decoration: none;
            font-size: 12px;
            cursor: pointer;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 28px;
            box-sizing: border-box;
        }

        .edit-button {
            background-color: #e1ecf4;
            color: #39739d;
            border: 1px solid #39739d;
        }

        .delete-button {
            background-color: #f9e1e1;
            color: #c91010;
            border: 1px solid #c91010;
        }

        .view-button {
            background-color: #e8f0fe;
            color: #1967d2;
            border: 1px solid #1967d2;
        }

        .create-button {
            padding: 8px 16px;
            background-color: #0a95ff;
            color: white;
            border-radius: 3px;
            text-decoration: none;
            font-weight: 500;
            white-space: nowrap;
        }

        .notification {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 3px;
            background-color: #e1ecf4;
            color: #39739d;
            flex-shrink: 0;
            width: 100%;
        }

        .truncate {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Table column widths based on content type */
        <?php if ($type === 'user'): ?>
        .admin-table th:nth-child(1) { width: 5%; }
        .admin-table th:nth-child(2) { width: 15%; }
        .admin-table th:nth-child(3) { width: 20%; }
        .admin-table th:nth-child(4) { width: 10%; }
        .admin-table th:nth-child(5) { width: 15%; }
        .admin-table th:nth-child(6) { width: 15%; }
        .admin-table th:nth-child(7) { width: 20%; }
        <?php elseif ($type === 'post'): ?>
        .admin-table th:nth-child(1) { width: 5%; }
        .admin-table th:nth-child(2) { width: 20%; }
        .admin-table th:nth-child(3) { width: 25%; }
        .admin-table th:nth-child(4) { width: 10%; }
        .admin-table th:nth-child(5) { width: 15%; }
        .admin-table th:nth-child(6) { width: 10%; }
        .admin-table th:nth-child(7) { width: 15%; }
        <?php elseif ($type === 'reply'): ?>
        .admin-table th:nth-child(1) { width: 5%; }
        .admin-table th:nth-child(2) { width: 20%; }
        .admin-table th:nth-child(3) { width: 30%; }
        .admin-table th:nth-child(4) { width: 15%; }
        .admin-table th:nth-child(5) { width: 15%; }
        .admin-table th:nth-child(6) { width: 15%; }
        <?php elseif ($type === 'like'): ?>
        .admin-table th:nth-child(1) { width: 10%; }
        .admin-table th:nth-child(2) { width: 25%; }
        .admin-table th:nth-child(3) { width: 30%; }
        .admin-table th:nth-child(4) { width: 20%; }
        .admin-table th:nth-child(5) { width: 15%; }
        <?php elseif ($type === 'adminaction'): ?>
        .admin-table th:nth-child(1) { width: 5%; }
        .admin-table th:nth-child(2) { width: 15%; }
        .admin-table th:nth-child(3) { width: 15%; }
        .admin-table th:nth-child(4) { width: 10%; }
        .admin-table th:nth-child(5) { width: 20%; }
        .admin-table th:nth-child(6) { width: 15%; }
        .admin-table th:nth-child(7) { width: 20%; }
        <?php endif; ?>

        /* Modal Styles */
        .modal-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 100;
            justify-content: center;
            align-items: center;
        }

        .modal {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            width: 400px;
            max-width: 90%;
            padding: 24px;
        }

        .modal-header {
            margin-bottom: 16px;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 18px;
            color: #c91010;
        }

        .modal-body {
            margin-bottom: 24px;
            color: #333;
            line-height: 1.5;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .modal-button {
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            border: none;
        }

        .cancel-modal-button {
            background-color: #f8f9f9;
            color: #6a737c;
            border: 1px solid #e3e6e8;
        }

        .delete-modal-button {
            background-color: #c91010;
            color: white;
        }
    </style>
</head>
<body>
    <?php include_once 'header.php'; ?>
    <main>
        <div class="sidebar">
            <ul>
                <li><a href="index.php"><img src="https://img.icons8.com/?size=100&id=67881&format=png&color=000000" alt="Ask" /><span>Ask</span></a></li>
                <li><a href="users.php"><img src="https://img.icons8.com/?size=100&id=98957&format=png&color=000000" alt="users" /><span>Users</span></a></li>
                <li><a href="questionanswer.php"><img src="https://img.icons8.com/?size=100&id=2908&format=png&color=000000" alt="q&a" /><span>Q&A</span></a></li>
                <li><a href="admin.php" class="active"><img src="https://img.icons8.com/?size=100&id=12599&format=png&color=000000" alt="admin" /><span>Admin</span></a></li>
            </ul>
            <div class="links">
                <a href="#">Privacy Policy</a>
                <a href="#">User agreement</a>
            </div>
        </div>

        <div class="page">
            <div class="admin-container">
                <div class="admin-header">
                    <h1>Admin Panel</h1>
                    <?php if ($type !== 'adminaction'): ?>
                    <a href="admin_edit.php?type=<?= $type ?>&action=create" class="create-button">Create New <?= ucfirst($type) ?></a>
                    <?php endif; ?>
                </div>

                <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
                <div class="notification">
                    Item has been successfully deleted.
                </div>
                <?php endif; ?>

                <div class="tab-navigation">
                    <a href="admin.php?type=user" class="tab-button <?= $type === 'user' ? 'active' : '' ?>">Users</a>
                    <a href="admin.php?type=post" class="tab-button <?= $type === 'post' ? 'active' : '' ?>">Posts</a>
                    <a href="admin.php?type=reply" class="tab-button <?= $type === 'reply' ? 'active' : '' ?>">Replies</a>
                    <a href="admin.php?type=like" class="tab-button <?= $type === 'like' ? 'active' : '' ?>">Likes</a>
                    <a href="admin.php?type=adminaction" class="tab-button <?= $type === 'adminaction' ? 'active' : '' ?>">Admin Actions</a>
                </div>

                <form method="GET" action="admin.php" class="search-bar">
                    <input type="hidden" name="type" value="<?= $type ?>">
                    <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit">Search</button>
                </form>

                <div class="table-container">
                    <table class="admin-table">
                        <?php if ($type === 'user'): ?>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Date Joined</th>
                                <th>Last Connection</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['UserID'] ?></td>
                                    <td><?= htmlspecialchars($row['Username']) ?></td>
                                    <td><?= htmlspecialchars($row['Email']) ?></td>
                                    <td><?= $row['Role'] ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($row['DateJoined'])) ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($row['LastConnection'])) ?></td>
                                    <td class="action-buttons">
                                        <a href="user.php?id=<?= $row['UserID'] ?>" class="view-button">View</a>
                                        <a href="admin_edit.php?type=user&id=<?= $row['UserID'] ?>&action=edit" class="edit-button">Edit</a>
                                        <a href="#" class="delete-button" data-id="<?= $row['UserID'] ?>" data-type="user" data-name="<?= htmlspecialchars($row['Username']) ?>">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7">No users found</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php elseif ($type === 'post'): ?>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Content</th>
                                <th>Author</th>
                                <th>Tags</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['PostID'] ?></td>
                                    <td><?= htmlspecialchars($row['Title']) ?></td>
                                    <td class="truncate"><?= htmlspecialchars($row['Content']) ?></td>
                                    <td><?= htmlspecialchars($row['Username']) ?></td>
                                    <td><?= htmlspecialchars($row['Tags']) ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($row['Timestamp'])) ?></td>
                                    <td class="action-buttons">
                                        <a href="question.php?id=<?= $row['PostID'] ?>" class="view-button">View</a>
                                        <a href="admin_edit.php?type=post&id=<?= $row['PostID'] ?>&action=edit" class="edit-button">Edit</a>
                                        <a href="#" class="delete-button" data-id="<?= $row['PostID'] ?>" data-type="post" data-name="<?= htmlspecialchars($row['Title']) ?>">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7">No posts found</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php elseif ($type === 'reply'): ?>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>On Post</th>
                                <th>Content</th>
                                <th>Author</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['ReplyID'] ?></td>
                                    <td><?= htmlspecialchars($row['PostTitle']) ?></td>
                                    <td class="truncate"><?= htmlspecialchars($row['Content']) ?></td>
                                    <td><?= htmlspecialchars($row['Username']) ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($row['Timestamp'])) ?></td>
                                    <td class="action-buttons">
                                        <a href="question.php?id=<?= $row['PostID'] ?>" class="view-button">View</a>
                                        <a href="admin_edit.php?type=reply&id=<?= $row['ReplyID'] ?>&action=edit" class="edit-button">Edit</a>
                                        <a href="#" class="delete-button" data-id="<?= $row['ReplyID'] ?>" data-type="reply" data-name="reply to '<?= htmlspecialchars($row['PostTitle']) ?>'">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="6">No replies found</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php elseif ($type === 'like'): ?>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Post</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['LikeID'] ?></td>
                                    <td><?= htmlspecialchars($row['Username']) ?></td>
                                    <td><?= htmlspecialchars($row['PostTitle']) ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($row['Timestamp'])) ?></td>
                                    <td class="action-buttons">
                                        <a href="question.php?id=<?= $row['PostID'] ?>" class="view-button">View</a>
                                        <a href="#" class="delete-button" data-id="<?= $row['LikeID'] ?>" data-type="like" data-name="like by <?= htmlspecialchars($row['Username']) ?>">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5">No likes found</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php elseif ($type === 'adminaction'): ?>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Admin</th>
                                <th>Target User</th>
                                <th>Action Type</th>
                                <th>Target Item</th>
                                <th>Date</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['ActionID'] ?></td>
                                    <td><?= htmlspecialchars($row['AdminUsername']) ?></td>
                                    <td><?= htmlspecialchars($row['TargetUsername']) ?></td>
                                    <td><?= htmlspecialchars($row['ActionType']) ?></td>
                                    <td>
                                        <?php if (!empty($row['TargetPostID'])): ?>
                                            Post: <?= htmlspecialchars($row['PostTitle']) ?>
                                        <?php elseif (!empty($row['TargetReplyID'])): ?>
                                            Reply ID: <?= $row['TargetReplyID'] ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('Y-m-d H:i', strtotime($row['Timestamp'])) ?></td>
                                    <td><?= htmlspecialchars($row['Notes']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="7">No admin actions found</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Confirmation Modal -->
    <div class="modal-backdrop" id="deleteModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Confirm Deletion</h3>
            </div>
            <div class="modal-body">
                <p id="deleteMessage">Are you sure you want to delete this item? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="modal-button cancel-modal-button" id="cancelDelete">Cancel</button>
                <button class="modal-button delete-modal-button" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('deleteModal');
            const deleteMessage = document.getElementById('deleteMessage');
            const confirmDeleteBtn = document.getElementById('confirmDelete');
            const cancelDeleteBtn = document.getElementById('cancelDelete');
            let deleteUrl = '';

            // Get all delete buttons
            const deleteButtons = document.querySelectorAll('.delete-button');

            // Add click event to all delete buttons
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    const id = this.getAttribute('data-id');
                    const type = this.getAttribute('data-type');
                    const name = this.getAttribute('data-name');

                    // Set delete URL
                    deleteUrl = `admin.php?type=${type}&id=${id}&delete=1`;

                    // Set confirmation message
                    deleteMessage.textContent = `Are you sure you want to delete ${type} "${name}"? This action cannot be undone.`;

                    // Show modal
                    modal.style.display = 'flex';
                });
            });

            // Handle cancel button click
            cancelDeleteBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });

            // Handle confirm button click
            confirmDeleteBtn.addEventListener('click', function() {
                window.location.href = deleteUrl;
            });

            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
