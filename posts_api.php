<?php
require_once "database.php";
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized Access"]);
    exit();
}

$action = $_GET['action'] ?? '';

// 1. FETCH POSTS (Now includes the 'likes' column data)
if ($action === 'fetch') {
    $mode = $_GET['mode'] ?? 'home';
    $currentUser = $_SESSION['username'];
    
    if ($mode === 'mine') {
        $stmt = $conn->prepare("SELECT * FROM posts WHERE username = ? ORDER BY id DESC");
        $stmt->bind_param("s", $currentUser);
    } else {
        $stmt = $conn->prepare("SELECT * FROM posts ORDER BY id DESC");
    }
    
    if ($stmt && $stmt->execute()) {
        $result = $stmt->get_result();
        $posts = [];
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
        echo json_encode($posts);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
    exit();
}

// 2. CREATE POST
if ($action === 'create') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!empty($data['content'])) {
        $stmt = $conn->prepare("INSERT INTO posts (username, profile_pic, content, bg_image, bg_color, text_color, template_class) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $user = $_SESSION['username'];
        $pic = $data['profile_pic'] ?? 'img/network.png';
        $content = $data['content'];
        $bg_img = $data['bg_image'] ?? null;
        $bg_col = $data['bg_color'] ?? null;
        $text_col = $data['text_color'] ?? '#111111';
        $tpl = $data['template_class'] ?? 'tpl-yellow';

        $stmt->bind_param("sssssss", $user, $pic, $content, $bg_img, $bg_col, $text_col, $tpl);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "id" => $stmt->insert_id]);
        } else {
            echo json_encode(["status" => "error"]);
        }
    }
    exit();
}

// 3. NEW ACTION: UPDATE LIKE COUNT IN DATABASE
if ($action === 'like') {
    $data = json_decode(file_get_contents("php://input"), true);
    $postId = intval($data['id'] ?? 0);
    $voteType = $data['type'] ?? 'increment'; // 'increment' or 'decrement'

    if ($postId > 0) {
        if ($voteType === 'increment') {
            $stmt = $conn->prepare("UPDATE posts SET likes = likes + 1 WHERE id = ?");
        } else {
            $stmt = $conn->prepare("UPDATE posts SET likes = GREATEST(0, likes - 1) WHERE id = ?");
        }
        
        $stmt->bind_param("i", $postId);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
            exit();
        }
    }
    echo json_encode(["status" => "error"]);
    exit();
}

// 4. DELETE POST
if ($action === 'delete') {
    $id = intval($_GET['id']);
    $user = $_SESSION['username'];
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND username = ?");
    $stmt->bind_param("is", $id, $user);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error"]);
    }
    exit();
}
?>