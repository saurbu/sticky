<?php
require_once "database.php";

$statusMessage = "";

if (isset($_POST["submit"])) {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $passwordRepeat = $_POST["repeat_password"];

    $errors = array();

    if (empty($username) || empty($email) || empty($password) || empty($passwordRepeat)) {
        array_push($errors, "All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        array_push($errors, "Email is not valid.");
    }

    if ($password !== $passwordRepeat) {
        array_push($errors, "Passwords do not match.");
    }

    //Validate both username and email available in db
    $checkUser = "SELECT * FROM login3 WHERE username = ? OR email = ?";
    $stmt = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmt, $checkUser)) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['username'] === $username) {
                array_push($errors, "Username already taken.");
            }
            if ($row['email'] === $email) {
                array_push($errors, "Email already exists.");
            }
        }
    }

    if (count($errors) > 0) {
        foreach ($errors as $error) {
            $statusMessage .= "<div class='alert alert-danger'>$error</div>";
        }
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO login3 (username, email, password) VALUES (?, ?, ?)";
        $stmt = mysqli_stmt_init($conn);

        if (mysqli_stmt_prepare($stmt, $sql)) {
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $passwordHash);
            mysqli_stmt_execute($stmt);
            $statusMessage = "<div class='alert alert-success'>Registered Successfully! <a href='login.php' style='font-weight:600; text-decoration:underline;'>Login here</a></div>";
        } else {
            die("Something went wrong with the database engine setup.");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sticky Signup</title>
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
                        <p class="quote-text" id="quote-display">Action is the foundational key to all success.</p>
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
                
                <?php if(!empty($statusMessage)) echo $statusMessage; ?>
                
                <form action="register.php" method="post">
                    <div class="form-group">
                        <input type="text" name="username" placeholder="Username" required>
                    </div>

                    <div class="form-group">
                        <input type="email" name="email" placeholder="Email" required>
                    </div>

                    <div class="form-group">
                        <input type="password" name="password" placeholder="Password" required>
                    </div>

                    <div class="form-group">
                        <input type="password" name="repeat_password" placeholder="Repeat Password" required>
                    </div>

                    <button type="submit" name="submit" class="primary-btn">Register</button>
                </form>

                <div class="switch-page">
                    <p>Already have an account? <a href="login.php">Login</a></p>
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
        "Success isn't just about what you accomplish.",
        "It's about what you inspire others to do.",
        "Focus on being productive instead of busy.",
        "Action is the foundational key to all success.",
        "Mistakes are proof that you are trying."
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