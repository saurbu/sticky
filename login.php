<?php
// 1. Using your registration config file to ensure we are on the exact same database
require_once "database.php";
session_start();

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

$loginError = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $userInput = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = $_POST['password']; // Raw password input from user

    if (!empty($userInput) && !empty($password)) {

        // 2. Switched target table to 'login3' to match your registration page perfectly
        // This query allows them to login using either their username OR their email string!
        $query = "SELECT * FROM login3 WHERE username='$userInput' OR email='$userInput' LIMIT 1";
        $result = mysqli_query($conn, $query);
        

        if ($result && mysqli_num_rows($result) > 0) {

            $user_data = mysqli_fetch_assoc($result);

            // 3. Since register.php uses secure password_hash(), we MUST use password_verify()
            if (password_verify($password, $user_data['password'])) {

                // Save the actual username into the session so the dashboard can display it
                $_SESSION['username'] = $user_data['username'];

                echo "<script>
                        alert('Successfully Logged In');
                        window.location.href='index.php';
                      </script>";
                exit();
            }
        }
        $loginError = "Wrong Username or Password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sticky Login</title>
    <link rel="icon" type="image/png" href="img/title.png">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<div class="page-container">
    <div class="main-content">
        <div class="sticky-side">
            <div class="quote-box">
                <div class="post-card active" id="post-container">
                    <div class="post-header">
                        <div class="post-profile">
                            <img src="https://i.pravatar.cc/150?img=68" class="profile-img" alt="Profile">
                            <div class="profile-info">
                                Saurav0802 <span class="post-time">• 2h</span>
                            </div>
                        </div>
                        <div class="post-options">•••</div>
                    </div>
                    <div class="post-body">
                        <p class="quote-text" id="quote-display">Believe you can and you're halfway there.</p>
                    </div>
                    <div class="post-footer">
                        <i class="fa-regular fa-heart"></i>
                        <i class="fa-regular fa-comment"></i>
                        <i class="fa-regular fa-paper-plane"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-side">
            <div class="form-card">
                <img src="img/stickylogo.png" class="logo" alt="Sticky Logo">
                
                <?php if(!empty($loginError)): ?>
                    <div class="alert alert-danger"><?php echo $loginError; ?></div>
                <?php endif; ?>
                
                <form action="login.php" method="POST">
                    <div class="form-group">
                        <input type="text" id="username" name="username" placeholder="Username or Email" required>
                    </div>

                    <div class="form-group">
                        <input type="password" name="password" id="password" placeholder="Password" required>
                    </div>

                    <button type="submit" class="primary-btn">
                        <span>Login</span>
                    </button>
                </form>

                <div class="switch-page">
                    <p>Don't have an account? <a href="register.php">Create Account</a></p>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-socials">
            <a href="https://instagram.com" target="_blank"><i class="fa-brands fa-instagram"></i></a>
            <a href="https://facebook.com" target="_blank"><i class="fa-brands fa-facebook"></i></a>
            <a href="https://x.com" target="_blank"><i class="fa-brands fa-x-twitter"></i></a>
            <a href="https://linkedin.com" target="_blank"><i class="fa-brands fa-linkedin-in"></i></a>
            <a href="https://github.com" target="_blank"><i class="fa-brands fa-github"></i></a>
        </div>
        <p class="footer-text">&copy; <?php echo date('Y'); ?> Sticky. All rights reserved.</p>
    </footer>
</div>

<script>
    const quotes = [
        "Believe you can and you're halfway there.",
        "Your limitation—it's only your imagination.",
        "Push yourself, because no one else is going to do it for you.",
        "Great things never come from comfort zones.",
        "Dream it. Wish it. Do it."
    ];
    let currentIdx = 0;
    const postContainer = document.getElementById('post-container');
    const quoteEl = document.getElementById('quote-display');

    setInterval(() => {
        postContainer.classList.remove('active');
        setTimeout(() => {
            currentIdx = (currentIdx + 1) % quotes.length;
            quoteEl.textContent = quotes[currentIdx];
            postContainer.classList.add('active');
        }, 400);
    }, 4000);
</script>

</body>
</html>