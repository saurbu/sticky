<?php
require_once "database.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_image'])) {
    $username = $_SESSION['username'];
    $file = $_FILES['profile_image'];

    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif');

    if (in_array($fileExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 5000000) { 
                $newFileName = "profile_" . time() . "_" . uniqid('', true) . "." . $fileExt;
                $fileDestination = 'img/' . $newFileName;

                if (move_uploaded_file($fileTmpName, $fileDestination)) {
    
                    $stmt = $conn->prepare("UPDATE login3 SET profile_pic = ? WHERE username = ?");
                    $stmt->bind_param("ss", $fileDestination, $username);
                    
                    if ($stmt->execute()) {
                        echo "<script>alert('Profile image updated successfully!'); window.location.href='index.php';</script>";
                    } else {
                        echo "<script>alert('Database update failed.'); window.location.href='index.php';</script>";
                    }
                    $stmt->close();
                } else {
                    echo "<script>alert('Failed to upload image to destination folder.'); window.location.href='index.php';</script>";
                }
            } else {
                echo "<script>alert('File size is too large (Max 5MB).'); window.location.href='index.php';</script>";
            }
        } else {
            echo "<script>alert('Error uploading file.'); window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid file type! Use JPG, JPEG, PNG, or GIF.'); window.location.href='index.php';</script>";
    }
} else {
    header("Location: index.php");
    exit();
}
?>