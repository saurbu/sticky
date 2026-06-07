<?php
require_once "database.php";
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

$query = "SELECT profile_pic, cover_banner, user_bio FROM login3 WHERE username='$username' LIMIT 1";
$result = mysqli_query($conn, $query);
$user_data = mysqli_fetch_assoc($result);

$profile_img = (!empty($user_data['profile_pic'])) ? $user_data['profile_pic'] : 'img/network.png';
$cover_banner = (!empty($user_data['cover_banner'])) ? $user_data['cover_banner'] : 'img/pp1.png';
$user_bio = (!empty($user_data['user_bio'])) ? $user_data['user_bio'] : 'Web developer';

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
    <title>Sticky</title>
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
                <li><a href="index.php" id="nav-home-btn" class="active-link"><img src="/img/pngwing1.png" alt="">Home</a></li>
                <li><a href="profile.php" id="nav-mynotes-btn"><img src="/img/note.png" class="nav-icon-small" alt="">My Notes</a></li>
                <li><a href="#"><img src="/img/pngwing2.png" alt="">Notification</a></li>
                <!-- <li><a href="#"><img src="/img/pngwing.com (4).png" alt="">Explore</a></li> -->
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

    <div class="custom-edit-modal" id="profileEditModal">
        <div class="modal-body-card">
            <h2>Update Profile Details</h2>
            <form action="update_profile_meta.php" method="POST" enctype="multipart/form-data">
                <div class="modal-field-group">
                    <label>Profile Bio Status Tag</label>
                    <input type="text" name="user_bio" value="<?php echo htmlspecialchars($user_bio); ?>" maxlength="100" required>
                </div>
                <div class="modal-field-group">
                    <label>Backside Banner Image File</label>
                    <input type="file" name="cover_banner" accept="image/*">
                </div>
                <div class="modal-action-row">
                    <button type="button" class="modal-btn-cancel" onclick="toggleEditModal(false)">Cancel</button>
                    <button type="submit" class="modal-btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="left-sidebar">
            <div class="sidebar-profile-box">
                <button class="edit-profile-trigger-btn" onclick="toggleEditModal(true)" title="Edit Banner and Bio Description">
                    <svg viewBox="0 0 24 24"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                </button>
                
                <img src="<?php echo $cover_banner; ?>" class="sidebar-cover-banner" alt="User Backside Cover Image Canvas">
                <div class="sidebar-profile-info">
                    <img src="<?php echo $profile_img; ?>" alt="Sidebar Profile Picture Render layout">
                    <h1><?php echo htmlspecialchars($username); ?></h1>
                    <h3><?php echo htmlspecialchars($user_bio); ?></h3>
                    <ul>
                        <li>Your Active Posts<span><?php echo $total_posts; ?></span></li>
                        <li>Total Earned Likes<span><?php echo $total_likes; ?></span></li>
                    </ul>
                </div>
            </div>
            
            <div class="sidebar-activity">
                <h3>Workspace Tools</h3>
                <a href="index.php" class="active-activity"><img src="img/recent.png" alt="">Global Streams</a>
                <a href="profile.php"><img src="img/note.png" class="sidebar-icon-inline" alt="">Personal Notes</a>
                <a href="#" onclick="toggleDrawer(true)"><img src="img/has.png" alt="">Account Manager</a>
                
                <div class="discover-more-links">
                    <!-- <a href="logout.php" class="signout-trigger-text">Sign Out Session</a> -->
                </div>
            </div>
        </div>
        
        <div class="main-content">
            <div class="create-post">
                <div class="create-post-input">
                    <img src="<?php echo $profile_img; ?>" class="creator-avatar-preview" alt="Creator Avatar Profile Asset">
                    <div class="notes-input-wrapper">
                        <textarea rows="2" cols="30" id="newPostContent" placeholder="Write a post" maxlength="300"></textarea>
                        <div id="charCounter" class="character-counter-node">0 / 300</div>
                    </div>
                </div>
                
                <div class="create-post-links">
                    <li onclick="triggerBackgroundUpload()"><img src="img/photo.png">Photo</li>
                    <input type="file" id="bgImageUpload" accept="image/*" class="hide-element" onchange="handleBackgroundSelect(event)">
                    
                    <li>
                        <label class="picker-inline-label">
                            <input type="color" id="templateColorPicker" value="#ffffff"> Template
                        </label>
                    </li>
                    
                    <li>
                        <label class="picker-inline-label">
                            <input type="color" id="textColorPicker" value="#111111"> Text Color
                        </label>
                    </li>
                    
                    <li><button onclick="addPost()" class="composer-submit-btn">Post</button></li>
                </div>
            </div>
            
            <div id="scroll-feed-viewport">
                <div id="post-container"></div> 
            </div>
        </div>

        <div class="right-sidebar">
            <div class="sidebar-news">
                <img src="img/note.png" alt="" class="info-icon">
                <h3>Trending Notes</h3>
                <?php
                $trending_query = "SELECT id, content, username, likes FROM posts ORDER BY likes DESC, id DESC LIMIT 5";
                $trending_result = mysqli_query($conn, $trending_query);
                
                if (mysqli_num_rows($trending_result) > 0) {
                    while ($trend = mysqli_fetch_assoc($trending_result)) {
                        $snippet = (strlen($trend['content']) > 28) ? substr($trend['content'], 0, 25) . "..." : $trend['content'];
                        echo '<a href="#postKey' . $trend['id'] . '">' . htmlspecialchars($snippet) . '</a>';
                        echo '<span>By @' . htmlspecialchars($trend['username']) . ' &middot; ' . intval($trend['likes']) . ' likes</span>';
                    }
                } else {
                    echo '<p class="empty-feed-fallback">No trends logged yet.</p>';
                }
                ?>
            </div>
            
            <div class="right-sidebar-follow">
                <h3 class="sidebar-heading-small">Active Network</h3>
                <div class="follow-people">
                    <?php
                    $users_directory_query = "SELECT username, profile_pic FROM login3 ORDER BY id DESC LIMIT 3";
                    $users_directory_result = mysqli_query($conn, $users_directory_query);
                    
                    while ($member = mysqli_fetch_assoc($users_directory_result)) {
                        $member_avatar = (!empty($member['profile_pic'])) ? $member['profile_pic'] : 'img/network.png';
                        echo '<li>';
                        echo '<img src="' . $member_avatar . '" class="member-directory-avatar">';
                        echo '<span>' . htmlspecialchars($member['username']) . '</span>';
                        echo '</li>';
                    }
                    ?>
                </div>
                <a href="index.php" style="text-decoration:none;"><button class="find-more-btn">Find More</button></a>
            </div>
        </div>
    </div>
    

    <script>
        const currentSessionUser = <?php echo json_encode($username); ?>;
        const currentSessionAvatar = <?php echo json_encode($profile_img); ?>;
        window.activeFeedMode = 'home'; // Explicit window level initialization context

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

        function toggleEditModal(open) {
            const modal = document.getElementById('profileEditModal');
            if (modal) {
                if (open) modal.classList.add('active');
                else modal.classList.remove('active');
            }
        }

        function previewSelectionName(input) {
            const output = document.getElementById('file-name-preview');
            if(input.files && input.files[0]) {
                output.textContent = "Selected: " + input.files[0].name;
            }
        }

        window.addEventListener('focus', function() {
            fetch('posts_api.php?action=fetch&mode=home')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'error') {
                    alert("Session updated in another tab. Reloading your dashboard...");
                    window.location.reload();
                }
            }).catch(err => console.log("Session validation running..."));
        });
    </script>
    <script src="./addPost.js"></script>
</body>
</html>