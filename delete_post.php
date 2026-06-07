<?php
require_once "database.php";
session_start();

if (!isset($_SESSION['username'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

$current_user = $_SESSION['username'];

if (!isset($_POST['post_id']) || empty($_POST['post_id'])) {
    echo json_encode(["status" => "error", "message" => "Missing target identification parameter."]);
    exit();
}

$post_id = intval($_POST['post_id']);

$check_query = "SELECT username FROM posts WHERE id = '$post_id' LIMIT 1";
$check_result = mysqli_query($conn, $check_query);

if ($check_result && mysqli_num_rows($check_result) > 0) {
    $post_data = mysqli_fetch_assoc($check_result);
    
    if ($post_data['username'] === $current_user) {
        $delete_query = "DELETE FROM posts WHERE id = '$post_id'";
        if (mysqli_query($conn, $delete_query)) {
            echo json_encode(["status" => "success", "message" => "Post successfully deleted."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database processing failure."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Permission denied. You can only delete your own notes."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Target note records not found."]);
}
exit();
?>