<?php
session_start();
include 'config/db.php'; 

$message = "";
$active_form = "login"; 
$registration_success = false; 

// 1. Defaults to "Welcome" for first-time visitors, "Welcome Back" for returning users (not yet working) --Gumagana pala
$greeting = "Welcome"; 
if (isset($_COOKIE['has_visited'])) {
    $greeting = "Welcome Back";
}

// ---- AUTOMATIC "REMEMBER ME" COOKIE CHECK (STILL IN PROGRESS) ----
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    if (strpos($_COOKIE['remember_me'], ':') !== false) {
        list($selector, $validator) = explode(':', $_COOKIE['remember_me']);
        
        $stmt = $conn->prepare("SELECT ut.user_id, ut.hashed_validator, u.username FROM user_tokens ut JOIN users u ON ut.user_id = u.id WHERE ut.selector = ? AND ut.expiry > NOW()");
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hashed_validator, $username);
            $stmt->fetch();
            
            if (hash_equals($hashed_validator, hash('sha256', $validator))) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                
                // Set the visit cookie during auto-login (valid for 1 year)
                setcookie('has_visited', '1', time() + (86400 * 365), "/");
                
                header("Location: loading.html?redirect=dashboard");
                exit();
            }
        }
        $stmt->close();
    }
}

if (isset($_SESSION['redirect_message'])) {
    $message = $_SESSION['redirect_message'];
    $active_form = $_SESSION['redirect_active_form'] ?? 'login';
    
    if (str_contains($message, 'successful')) {
        $registration_success = true;
    }
    
    unset($_SESSION['redirect_message']);
    unset($_SESSION['redirect_active_form']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ---- REGISTRATION PROCESSING ----
    if (isset($_POST['action']) && $_POST['action'] == 'register') {
        $email             = trim($_POST['email']);
        $username          = trim($_POST['username']);
        $password          = $_POST['password'];
        $confirm_password  = $_POST['confirm_password'];
        $security_question = $_POST['security_question'];
        $security_answer   = trim($_POST['security_answer']);

        if (strlen($password) < 6) {
            $_SESSION['redirect_message'] = "<div class='msg error'>Password must be at least 6 characters long!</div>";
            $_SESSION['redirect_active_form'] = "register";
        }
        elseif ($password !== $confirm_password) {
            $_SESSION['redirect_message'] = "<div class='msg error'>Passwords do not match!</div>";
            $_SESSION['redirect_active_form'] = "register";
        } 
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@cvsu.edu.ph')) {
            $_SESSION['redirect_message'] = "<div class='msg error'>Registration restricted! Only @cvsu.edu.ph emails are allowed.</div>";
            $_SESSION['redirect_active_form'] = "register";
        } 
        elseif (empty($security_question) || empty($security_answer)) {
            $_SESSION['redirect_message'] = "<div class='msg error'>Please select a security question and provide an answer!</div>";
            $_SESSION['redirect_active_form'] = "register";
        }
        else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $hashed_answer = password_hash(strtolower($security_answer), PASSWORD_BCRYPT);

            $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $check->bind_param("ss", $username, $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $_SESSION['redirect_message'] = "<div class='msg error'>Username or Email already exists!</div>";
                $_SESSION['redirect_active_form'] = "register";
            } else {
                $stmt = $conn->prepare("INSERT INTO users (email, username, password, security_question, security_answer) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $email, $username, $hashed_password, $security_question, $hashed_answer);
                
                if ($stmt->execute()) {
                    $_SESSION['redirect_message'] = "<div class='msg success'>Registration successful! Please log in below.</div>";
                    $_SESSION['redirect_active_form'] = "login"; 
                } else {
                    $_SESSION['redirect_message'] = "<div class='msg error'>Registration failed. Try again.</div>";
                    $_SESSION['redirect_active_form'] = "register";
                }
                $stmt->close();
            }
            $check->close();
        }
        
        header("Location: registration.php");
        exit();
    }
    
    // ---- LOGIN PROCESSING ----
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']);

        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id']  = $user_id;
                $_SESSION['username'] = $username;
                
                // Set the visit cookie during manual form login (valid for 1 year)
                setcookie('has_visited', '1', time() + (86400 * 365), "/");
                
                if ($remember) {
                    $selector  = bin2hex(random_bytes(6)); 
                    $validator = bin2hex(random_bytes(32)); 
                    $expiry    = date('Y-m-d H:i:s', time() + (86400 * 30)); 
                    
                    $hashed_validator = hash('sha256', $validator);
                    
                    $tokStmt = $conn->prepare("INSERT INTO user_tokens (user_id, selector, hashed_validator, expiry) VALUES (?, ?, ?, ?)");
                    $tokStmt->bind_param("isss", $user_id, $selector, $hashed_validator, $expiry);
                    $tokStmt->execute();
                    $tokStmt->close();
                    
                    setcookie('remember_me', $selector . ':' . $validator, time() + (86400 * 30), "/", "", false, true);
                }
                
                header("Location: loading.html?redirect=dashboard");
                exit();
            } else {
                $_SESSION['redirect_message'] = "<div class='msg error'>Invalid password!</div>";
            }
        } else {
            $_SESSION['redirect_message'] = "<div class='msg error'>Username not found!</div>";
        }
        $stmt->close();
        
        $_SESSION['redirect_active_form'] = "login";
        header("Location: registration.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-LOST MOH, E-FOUND KOH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/registrationstyle.css">
</head>
<body>

<div class="page-wrapper" id="masterWrapper">
    <div class="container">
        
        <div class="brand-side">
            <div class="logo-box">🔍</div>
            <h1>E-LOST <span>MOH</span><br>E-FOUND <span>KOH</span></h1>
        </div>

        <div id="dynamic-card" class="form-card <?php echo ($active_form == 'register') ? 'register-mode' : ''; ?>">
            <div class="form-padding-wrapper">
                
                <div id="message-container"><?php echo $message; ?></div>

                <div id="login-section" class="form-section <?php echo ($active_form == 'login') ? '' : 'hidden'; ?>">
                    <h2><?php echo htmlspecialchars($greeting); ?></h2>
                    
                    <form action="registration.php" method="POST">
                        <input type="hidden" name="action" value="login">
                        <div class="input-group">
                            <input type="text" name="username" placeholder="Username" required>
                        </div>
                        <div class="input-group">
                            <input type="password" name="password" id="login-password" placeholder="Password" required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('login-password', this)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                        <div class="options-row">
                            <label><input type="checkbox" name="remember"> Remember me</label>
                            <a href="forgot_password.php">Forgot Password?</a>
                        </div>
                        <button type="submit" class="btn-primary">Log In</button>
                    </form>
                    <div class="footer-text">
                        Found something? <button type="button" onclick="toggleForm('register')">Create Account</button>
                    </div>
                </div>

                <div id="register-section" class="form-section <?php echo ($active_form == 'register') ? '' : 'hidden'; ?>">
                    <h2>Create Account</h2>
                    <form action="registration.php" method="POST">
                        <input type="hidden" name="action" value="register">
                        <div class="input-group">
                            <input type="email" name="email" placeholder="Email (must be @cvsu.edu.ph)" pattern="[a-zA-Z0-9._%+-]+@cvsu\.edu\.ph$" title="Please use your official university account ending in @cvsu.edu.ph" required>
                        </div>
                        <div class="input-group">
                            <input type="text" name="username" placeholder="Username" required>
                        </div>
                        <div class="input-group">
                            <select name="security_question" class="custom-select" required>
                                <option value="" disabled selected>Select a Security Question</option>
                                <option value="What is your elementary school name?">What is your elementary school name?</option>
                                <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                                <option value="In what city or town were you born?">In what city or town were you born?</option>
                                <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <input type="text" name="security_answer" placeholder="Your Security Answer" required>
                        </div>
                        <div class="input-group">
                            <input type="password" name="password" id="register-password" placeholder="Password (Min 6 characters)" minlength="6" required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('register-password', this)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="register-confirm-password" placeholder="Confirm Password" minlength="6" required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('register-confirm-password', this)">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </button>
                        </div>
                        <button type="submit" class="btn-primary">Register</button>
                    </form>
                    <div class="footer-text">
                        Already have an account? <button type="button" onclick="toggleForm('login')">Log In</button>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
function togglePasswordVisibility(fieldId, toggleButton) {
    const passwordField = document.getElementById(fieldId);
    const svgIcon = toggleButton.querySelector('svg');
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        svgIcon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>`;
    } else {
        passwordField.type = 'password';
        svgIcon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>`;
    }
}

function hideMessageSmoothly() {
    const msgContainer = document.getElementById('message-container');
    if (msgContainer && msgContainer.classList.contains('reveal-smooth')) {
        msgContainer.classList.remove('reveal-smooth');
        setTimeout(() => { msgContainer.innerHTML = ''; }, 500);
    }
}

function toggleForm(formType) {
    const card = document.getElementById('dynamic-card');
    const loginSection = document.getElementById('login-section');
    const registerSection = document.getElementById('register-section');
    hideMessageSmoothly();

    if (formType === 'register') {
        loginSection.classList.add('hidden');
        registerSection.classList.remove('hidden');
        card.classList.add('register-mode');
    } else {
        registerSection.classList.add('hidden');
        loginSection.classList.remove('hidden');
        card.classList.remove('register-mode');
    }
}

window.addEventListener('DOMContentLoaded', () => {
    const msgContainer = document.getElementById('message-container');
    if (msgContainer && msgContainer.innerHTML.trim() !== "") {
        setTimeout(() => { msgContainer.classList.add('reveal-smooth'); }, 50);
        setTimeout(() => { hideMessageSmoothly(); }, 4000); 
    }
});
</script>
</body>
</html>