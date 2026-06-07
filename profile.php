<?php
require_once "database.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ./login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch the logged-in user's profile metadata
$query = "SELECT profile_pic, cover_banner, user_bio FROM login3 WHERE username='$username' LIMIT 1";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);

$profile_img = (!empty($user_data['profile_pic'])) ? $user_data['profile_pic'] : 'img/network.png';
$cover_banner = (!empty($user_data['cover_banner'])) ? $user_data['cover_banner'] : 'img/pp1.png';
$user_bio = (!empty($user_data['user_bio'])) ? $user_data['user_bio'] : 'Web developer';

// Fetch user metrics
$views_query = "SELECT COUNT(*) as total_posts, SUM(likes) as total_likes FROM posts WHERE username='$username'";
$views_result = mysqli_query($conn, $views_query);
$metrics = mysqli_fetch_assoc($views_result);
$total_posts = $metrics['total_posts'] ?? 0;
$total_likes = $metrics['total_likes'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sticky Profile</title>
    <link rel="icon" type="image/png" href="img/title.png">

    <link rel="stylesheet" href="style1.css">
</head>
<body>
    
    <nav class="navbar">
        <div class="nav-left">
            <a href="index.php" class="logo"><img src="img/stickylogo.png" alt="Logo"></a>
        </div>
        <div class="nav-center">
            <ul>
                <li><a href="index.php" id="nav-home-btn"><img src="/img/pngwing.com (1).png" alt="">Home</a></li>
                <li><a href="profile.php" id="nav-mynotes-btn" class="active-link"><img src="/img/note.png" class="nav-icon-small" alt="">My Notes</a></li>
                <li><a href="#"><img src="/img/pngwing.com (2).png" alt="">Notification</a></li>
                <li><a href="#"><img src="/img/pngwing.com (4).png" alt="">Explore</a></li>
            </ul>
        </div>
        <div class="nav-right">
            <div class="online"></div>
            <img src="<?php echo $profile_img; ?>" class="nav-profile-img" onclick="toggleDrawer(true)" alt="Nav Profile">
        </div>
    </nav>

    <div class="drawer-backdrop" id="drawerBackdrop" onclick="toggleDrawer(false)"></div>
    <div class="profile-drawer" id="profileDrawer">
        <button class="close-drawer-btn" onclick="toggleDrawer(false)">&times;</button>
        <div class="drawer-main-content">
            <img src="<?php echo $profile_img; ?>" class="drawer-avatar" alt="Current Profile Picture">
            <div class="drawer-username">@<?php echo htmlspecialchars($username); ?></div>
            
            <div class="upload-form-wrapper">
                <form action="upload_profile.php" method="POST" enctype="multipart/form-data">
                    <label for="fileInput" class="file-select-label">Choose New Image</label>
                    <input type="file" name="profile_image" id="fileInput" accept="image/*" class="hide-element" onchange="previewSelectionName(this)">
                    <div id="file-name-preview" class="preview-text"></div>
                    <button type="submit" class="upload-submit-btn">Upload Profile Picture</button>
                </form>
            </div>
        </div>
        <div class="drawer-footer-wrapper">
            <hr class="drawer-divider">
            <a href="logout.php" class="logout-action-link">Logout Account</a>
        </div>
    </div>

    <div class="container layout-profile-view">
        
        <div class="left-sidebar">
            <div class="sidebar-profile-box">
                <img src="<?php echo $cover_banner; ?>" class="sidebar-cover-banner" alt="User Cover Image">
                <div class="sidebar-profile-info">
                    <img src="<?php echo $profile_img; ?>" alt="Profile Picture">
                    <h1><?php echo htmlspecialchars($username); ?></h1>
                    <h3><?php echo htmlspecialchars($user_bio); ?></h3>
                    <ul>
                        <li>Your Active Posts<span><?php echo $total_posts; ?></span></li>
                        <li>Total Earned Likes<span><?php echo $total_likes; ?></span></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="main-content layout-grid-stream">
            <!-- <h2 class="grid-section-title">Your Sticky Notes Workspace</h2> -->
            <div class="profile-notes-grid">
                <div id="post-container"></div> 
            </div>
        </div>

    </div>

    <script>
        const currentSessionUser = <?php echo json_encode($username); ?>;
        const currentSessionAvatar = <?php echo json_encode($profile_img); ?>;
        window.activeFeedMode = 'mine'; // Explicit window level initialization context

        function toggleDrawer(open) {
            const drawer = document.getElementById('profileDrawer');
            const backdrop = document.getElementById('drawerBackdrop');
            if (drawer && backdrop) {
                if (open) {
                    drawer.classList.add('open');
                    backdrop.classList.add('show');
                } else {
                    drawer.classList.remove('open');
                    backdrop.classList.remove('show');
                }
            }
        }

        function previewSelectionName(input) {
            const output = document.getElementById('file-name-preview');
            if(input.files && input.files[0]) {
                output.textContent = "Selected: " + input.files[0].name;
            }
        }

        window.addEventListener('focus', function() {
            fetch('posts_api.php?action=fetch&mode=mine')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'error') {
                    alert("Session updated in another tab. Reloading dashboard...");
                    window.location.reload();
                }
            }).catch(err => console.log("Session identity tracker running..."));
        });
    </script>
    <script src="./addPost.js"></script>
</body>
</html>