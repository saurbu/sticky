<?php
require_once "database.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$user_bio = mysqli_real_escape_string($conn, $_POST['user_bio']);

$update_meta_query = "UPDATE login3 SET user_bio='$user_bio' WHERE username='$username'";
mysqli_query($conn, $update_meta_query);

if (isset($_FILES['cover_banner']) && $_FILES['cover_banner']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['cover_banner']['tmp_tmp_name'] ?? $_FILES['cover_banner']['tmp_name'];
    $fileName = $_FILES['cover_banner']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    $validExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($fileExtension, $validExtensions)) {
        $uploadFileDir = 'uploads/';
        
        if(!is_dir($uploadFileDir)){
            mkdir($uploadFileDir, 0755, true);
        }
        
        $newFileName = 'banner_' . md5($username . time()) . '.' . $fileExtension;
        $dest_path = $uploadFileDir . $newFileName;
        
        if(move_uploaded_file($fileTmpPath, $dest_path)) {
            $update_banner_query = "UPDATE login3 SET cover_banner='$dest_path' WHERE username='$username'";
            mysqli_query($conn, $update_banner_query);
        }
    }
}

header("Location: index.php");
exit();
?>