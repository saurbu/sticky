<?php

$hostName = "localhost";
$dbUser = "root";
$dbPassword = "";
$dbName = "sticky_notes_db";

$conn = mysqli_connect($hostName, $dbUser, $dbPassword, $dbName);
if (!$conn){
    die("Something went wrong; " . mysqli_connect_error());
}  
?>