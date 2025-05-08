<?php
session_start();
require_once 'php/config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: index.php');
    exit();
}

$type = isset($_GET['type']) ? $_GET['type'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Initialize variables
$item = [];
$errors = [];
$success = false;

// Check if the type and action are valid
if (!in_array($type, ['user', 'post', 'reply']) || !in_array($action, ['edit', 'create'])) {
    header('Location: admin.php');
    exit();
}

// If editing, fetch the existing item
if ($action === 'edit' && $id > 0) {
    switch ($type) {
        case 'user':
            $query = "SELECT * FROM Users WHERE UserID = ?";
            break;
        case 'post':
            $query = "SELECT * FROM Posts WHERE PostID = ?";
            break;
        case 'reply':
            $query = "SELECT * FROM Replies WHERE ReplyID = ?";
            break;
    }

    $stmt = $DBConnect->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header('Location: admin.php');
        exit();
    }

    $item = $result->fetch_assoc();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($type) {
        case 'user':
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $role = $_POST['role'] ?? 'User';
            $description = $_POST['description'] ?? '';
            $password = $_POST['password'] ?? '';

            // Validate input
            if (empty($username)) $errors[] = "Username is required";
            if (empty($email)) $errors[] = "Email is required";
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";

            if (empty($errors)) {
                if ($action === 'create') {
                    if (empty($password)) {
                        $errors[] = "Password is required for new users";
                    } else {
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);

                        $query = "INSERT INTO Users (Username, Email, PasswordHash, Role, Description) VALUES (?, ?, ?, ?, ?)";
                        $stmt = $DBConnect->prepare($query);
                        $stmt->bind_param("sssss", $username, $email, $password_hash, $role, $description);

                        if ($stmt->execute()) {
                            $success = true;
                            $id = $DBConnect->insert_id;
                        } else {
                            $errors[] = "Database error: " . $stmt->error;
                        }
                    }
                } else {
                    // Update query
                    if (!empty($password)) {
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $query = "UPDATE Users SET Username = ?, Email = ?, PasswordHash = ?, Role = ?, Description = ? WHERE UserID = ?";
                        $stmt = $DBConnect->prepare($query);
                        $stmt->bind_param("sssssi", $username, $email, $password_hash, $role, $description, $id);
                    } else {
                        $query = "UPDATE Users SET Username = ?, Email = ?, Role = ?, Description = ? WHERE UserID = ?";
                        $stmt = $DBConnect->prepare($query);
                        $stmt->bind_param("ssssi", $username, $email, $role, $description, $id);
                    }

                    if ($stmt->execute()) {
                        $success = true;
                    } else {
                        $errors[] = "Database error: " . $stmt->error;
                    }
                }
            }
            break;

        case 'post':
            $title = $_POST['title'] ?? '';
            $content = $_POST['content'] ?? '';
            $tags = $_POST['tags'] ?? '';
            $user_id = $_POST['user_id'] ?? '';

            // Validate input
            if (empty($title)) $errors[] = "Title is required";
            if (empty($content)) $errors[] = "Content is required";
            if (empty($user_id)) $errors[] = "User ID is required";

            if (empty($errors)) {
                if ($action === 'create') {
                    $query = "INSERT INTO Posts (UserID, Title, Content, Tags) VALUES (?, ?, ?, ?)";
                    $stmt = $DBConnect->prepare($query);
                    $stmt->bind_param("isss", $user_id, $title, $content, $tags);

                    if ($stmt->execute()) {
                        $success = true;
                        $id = $DBConnect->insert_id;
                    } else {
                        $errors[] = "Database error: " . $stmt->error;
                    }
                } else {
                    $query = "UPDATE Posts SET UserID = ?, Title = ?, Content = ?, Tags = ? WHERE PostID = ?";
                    $stmt = $DBConnect->prepare($query);
                    $stmt->bind_param("isssi", $user_id, $title, $content, $tags, $id);

                    if ($stmt->execute()) {
                        $success = true;
                    } else {
                        $errors[] = "Database error: " . $stmt->error;
                    }
                }
            }
            break;

        case 'reply':
            $content = $_POST['content'] ?? '';
            $post_id = $_POST['post_id'] ?? '';
            $user_id = $_POST['user_id'] ?? '';

            // Validate input
            if (empty($content)) $errors[] = "Content is required";
            if (empty($post_id)) $errors[] = "Post ID is required";
            if (empty($user_id)) $errors[] = "User ID is required";

            if (empty($errors)) {
                if ($action === 'create') {
                    $query = "INSERT INTO Replies (PostID, UserID, Content) VALUES (?, ?, ?)";
                    $stmt = $DBConnect->prepare($query);
                    $stmt->bind_param("iis", $post_id, $user_id, $content);

                    if ($stmt->execute()) {
                        $success = true;
                        $id = $DBConnect->insert_id;
                    } else {
                        $errors[] = "Database error: " . $stmt->error;
                    }
                } else {
                    $query = "UPDATE Replies SET PostID = ?, UserID = ?, Content = ? WHERE ReplyID = ?";
                    $stmt = $DBConnect->prepare($query);
                    $stmt->bind_param("iisi", $post_id, $user_id, $content, $id);

                    if ($stmt->execute()) {
                        $success = true;
                    } else {
                        $errors[] = "Database error: " . $stmt->error;
                    }
                }
            }
            break;
    }

    // Record admin action if successful
    if ($success) {
        $admin_id = $_SESSION['user_id'];
        $target_user_id = ($type === 'user') ? $id : ($user_id ?? null);
        $target_post_id = ($type === 'post') ? $id : ($post_id ?? null);
        $target_reply_id = ($type === 'reply') ? $id : null;

        $action_type = ($action === 'create') ? 'Create' : 'Edit';
        $action_query = "INSERT INTO AdminActions (AdminID, TargetUserID, ActionType, TargetPostID, TargetReplyID, Notes)
                        VALUES (?, ?, ?, ?, ?, ?)";
        $notes = "$action_type " . ucfirst($type) . " by admin";

        $action_stmt = $DBConnect->prepare($action_query);
        $action_stmt->bind_param("iisiss", $admin_id, $target_user_id, $action_type, $target_post_id, $target_reply_id, $notes);
        $action_stmt->execute();

        // Redirect to admin page if successful
        if (empty($errors)) {
            header("Location: admin.php?type=$type");
            exit();
        }
    }
}

// Fetch users for dropdowns
$users_query = "SELECT UserID, Username FROM Users ORDER BY Username";
$users_result = $DBConnect->query($users_query);
$users = [];

if ($users_result && $users_result->num_rows > 0) {
    while ($row = $users_result->fetch_assoc()) {
        $users[$row['UserID']] = $row['Username'];
    }
}

// Fetch posts for reply dropdown
$posts_query = "SELECT PostID, Title FROM Posts ORDER BY Title";
$posts_result = $DBConnect->query($posts_query);
$posts = [];

if ($posts_result && $posts_result->num_rows > 0) {
    while ($row = $posts_result->fetch_assoc()) {
        $posts[$row['PostID']] = $row['Title'];
    }
}

// Get form title
$form_title = ucfirst($action) . ' ' . ucfirst($type);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $form_title ?> - Admin Panel</title>
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
            overflow: auto; /* Allow scrolling for the page content */
        }

        .admin-container {
            padding: 24px;
            width: 100%;
            height: 100%;
            box-sizing: border-box;
        }

        .admin-header {
            margin-bottom: 24px;
            border-bottom: 1px solid #e3e6e8;
            padding-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .admin-header h1 {
            margin: 0;
            font-size: 24px;
        }

        .admin-form {
            width: 100%;
            max-width: 100%;
            margin: 0;
            background-color: white;
            padding: 24px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            box-sizing: border-box;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 15px;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #e3e6e8;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }

        .form-group select {
            height: 42px;
        }

        .form-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e3e6e8;
        }

        .cancel-button {
            padding: 12px 24px;
            background-color: #f8f9f9;
            color: #6a737c;
            border: 1px solid #e3e6e8;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }

        .cancel-button:hover {
            background-color: #e3e6e8;
        }

        .submit-button {
            padding: 12px 24px;
            background-color: #0a95ff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
        }

        .submit-button:hover {
            background-color: #0077cc;
        }

        .errors {
            padding: 16px;
            margin-bottom: 24px;
            border-radius: 4px;
            background-color: #f9e1e1;
            color: #c91010;
            border: 1px solid #e8c4c4;
        }

        .errors ul {
            margin: 0;
            padding-left: 20px;
        }

        /* Responsive adjustments */
        @media (min-width: 768px) {
            .admin-form {
                padding: 32px;
            }
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
                    <h1><?= $form_title ?></h1>
                    <a href="admin.php?type=<?= $type ?>" class="cancel-button">Back to List</a>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="POST" class="admin-form">
                    <?php if ($type === 'user'): ?>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" value="<?= htmlspecialchars($item['Username'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($item['Email'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password <?= ($action === 'edit') ? '(leave blank to keep unchanged)' : '' ?></label>
                            <input type="password" id="password" name="password" <?= ($action === 'create') ? 'required' : '' ?>>
                        </div>

                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role">
                                <option value="User" <?= (isset($item['Role']) && $item['Role'] === 'User') ? 'selected' : '' ?>>User</option>
                                <option value="Admin" <?= (isset($item['Role']) && $item['Role'] === 'Admin') ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"><?= htmlspecialchars($item['Description'] ?? '') ?></textarea>
                        </div>
                    <?php elseif ($type === 'post'): ?>
                        <div class="form-group">
                            <label for="user_id">Author</label>
                            <select id="user_id" name="user_id" required>
                                <option value="">Select Author</option>
                                <?php foreach ($users as $uid => $uname): ?>
                                    <option value="<?= $uid ?>" <?= (isset($item['UserID']) && $item['UserID'] == $uid) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($uname) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" id="title" name="title" value="<?= htmlspecialchars($item['Title'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="content">Content</label>
                            <textarea id="content" name="content" required><?= htmlspecialchars($item['Content'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="tags">Tags (comma separated)</label>
                            <input type="text" id="tags" name="tags" value="<?= htmlspecialchars($item['Tags'] ?? '') ?>">
                        </div>
                    <?php elseif ($type === 'reply'): ?>
                        <div class="form-group">
                            <label for="post_id">Post</label>
                            <select id="post_id" name="post_id" required>
                                <option value="">Select Post</option>
                                <?php foreach ($posts as $pid => $ptitle): ?>
                                    <option value="<?= $pid ?>" <?= (isset($item['PostID']) && $item['PostID'] == $pid) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ptitle) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="user_id">Author</label>
                            <select id="user_id" name="user_id" required>
                                <option value="">Select Author</option>
                                <?php foreach ($users as $uid => $uname): ?>
                                    <option value="<?= $uid ?>" <?= (isset($item['UserID']) && $item['UserID'] == $uid) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($uname) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="content">Content</label>
                            <textarea id="content" name="content" required><?= htmlspecialchars($item['Content'] ?? '') ?></textarea>
                        </div>
                    <?php endif; ?>

                    <div class="form-buttons">
                        <a href="admin.php?type=<?= $type ?>" class="cancel-button">Cancel</a>
                        <button type="submit" class="submit-button">Save <?= ucfirst($type) ?></button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
