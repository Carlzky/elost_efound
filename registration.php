<?php
session_start();
include 'db.php'; // Keeps your database credentials separate and secure

$message = "";
$active_form = "login"; // Default state on page load
$registration_success = false; // Flag to tell JS to run the cool transition

// Check if there is a redirect message waiting in the session from a previous form submission
if (isset($_SESSION['redirect_message'])) {
    $message = $_SESSION['redirect_message'];
    $active_form = $_SESSION['redirect_active_form'] ?? 'login';
    
    // If the last action was a successful registration, trip our animation flag
    if (str_contains($message, 'successful')) {
        $registration_success = true;
    }
    
    unset($_SESSION['redirect_message']);
    unset($_SESSION['redirect_active_form']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ---- REGISTRATION PROCESSING ----
    if (isset($_POST['action']) && $_POST['action'] == 'register') {
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // 1. Backend Validation: Enforce minimum password length of 6 characters
        if (strlen($password) < 6) {
            $_SESSION['redirect_message'] = "<div class='msg error'>Password must be at least 6 characters long!</div>";
            $_SESSION['redirect_active_form'] = "register";
        }
        // 2. Backend Validation: Match passwords
        elseif ($password !== $confirm_password) {
            $_SESSION['redirect_message'] = "<div class='msg error'>Passwords do not match!</div>";
            $_SESSION['redirect_active_form'] = "register";
        } 
        // 3. Backend Validation: Restrict to official @cvsu.edu.ph institutional emails
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !str_ends_with(strtolower($email), '@cvsu.edu.ph')) {
            $_SESSION['redirect_message'] = "<div class='msg error'>Registration restricted! Only official @cvsu.edu.ph emails are allowed.</div>";
            $_SESSION['redirect_active_form'] = "register";
        } 
        else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $check->bind_param("ss", $username, $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $_SESSION['redirect_message'] = "<div class='msg error'>Username or Email already exists!</div>";
                $_SESSION['redirect_active_form'] = "register";
            } else {
                // REMOVED: full_name from database columns insertion layout logic
                $stmt = $conn->prepare("INSERT INTO users (email, username, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $email, $username, $hashed_password);
                
                if ($stmt->execute()) {
                    $_SESSION['redirect_message'] = "<div class='msg success'>Registration successful! Preparing your workspace...</div>";
                    $_SESSION['redirect_active_form'] = "register"; 
                } else {
                    $_SESSION['redirect_message'] = "<div class='msg error'>Registration failed. Try again.</div>";
                    $_SESSION['redirect_active_form'] = "register";
                }
                $stmt->close();
            }
            $check->close();
        }
        
        // PRG Pattern Redirect back to clean page
        header("Location: registration.php");
        exit();
    }
    
    // ---- LOGIN PROCESSING ----
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                header("Location: loading.html");
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
    <title>E-LOST KOH, E-FOUND MOH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1F5D4A;      
            --primary-dark: #143F32;
            --bg-green-start: #3D4434; 
            --bg-green-end: #6B735C;   
            --card-white: #FFFFFF;   
            --text-dark: #1A202C;
            --text-muted: #718096;
            --accent-green: #9CD83B;   
            --gold: #F1B846;          
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bg-green-start), var(--bg-green-end));
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
            overflow-x: hidden;
            transition: background 1s ease; 
        }

        .page-wrapper {
            display: flex;
            align-items: center; 
            justify-content: center;
            width: 100%;
            min-height: 100vh;
            transition: opacity 0.8s cubic-bezier(0.25, 1, 0.5, 1), transform 0.8s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .page-wrapper.page-exit {
            opacity: 0;
            transform: scale(1.06) translateY(-15px);
            filter: blur(4px);
        }

        .container {
            display: flex;
            align-items: center; 
            width: 1240px;       
            justify-content: space-between;
            gap: 60px;
        }

        .brand-side {
            text-align: center;
            color: #FFFFFF;
            flex: 1;
            max-width: 550px;   
            padding: 20px;
            animation: fadeInAndScale 1.2s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .brand-side h1 {
            font-family: 'Poppins', sans-serif;
            font-weight: 900;   
            font-size: 56px;    
            margin: 30px 0 0 0;
            line-height: 1.05;
            letter-spacing: 1.5px;
            text-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .brand-side h1 span {
            color: var(--accent-green);
            display: inline-block;
        }

        .logo-box {
            width: 240px;       
            height: 240px;      
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: 6px solid var(--gold); 
            border-radius: 48px;       
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 105px;    
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45), inset 0 4px 8px rgba(255, 255, 255, 0.25);
            transition: transform 0.7s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 0.7s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .logo-box:hover {
            transform: scale(1.08) translateY(-10px) rotate(4deg);
            box-shadow: 0 35px 80px rgba(0, 0, 0, 0.55), inset 0 4px 8px rgba(255, 255, 255, 0.3);
        }

        .form-card {
            background-color: var(--card-white);
            border-radius: 36px; 
            box-shadow: 0 35px 75px -15px rgba(0, 0, 0, 0.45);
            position: relative;
            overflow: hidden;
            animation: slideUpCard 1.2s cubic-bezier(0.16, 1, 0.3, 1) both;
            width: 500px;      
            min-height: 560px; 
            display: flex;
            flex-direction: column;
            transition: width 0.85s cubic-bezier(0.25, 1, 0.5, 1), min-height 0.85s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .form-card.register-mode {
            width: 530px;      
            min-height: 720px; /* Adjusted slightly smaller now that full name is gone */
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 7px;
            background: linear-gradient(90deg, var(--primary), var(--gold), var(--accent-green));
            z-index: 10;
        }

        .form-padding-wrapper {
            padding: 60px 55px; 
            box-sizing: border-box;
            width: 100%;
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .form-card h2 {
            font-family: 'Poppins', sans-serif;
            color: var(--primary);
            text-align: center;
            margin-top: 0;
            margin-bottom: 40px;
            font-size: 36px; 
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .input-group {
            margin-bottom: 26px; 
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 18px 55px 18px 24px; 
            border: 1.5px solid #E2E8F0;
            border-radius: 18px; 
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
            font-size: 16px; 
            background-color: #F8FAFC;
            color: var(--text-dark);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary);
            background-color: #FFFFFF;
            box-shadow: 0 0 0 5px rgba(31, 93, 74, 0.15);
            transform: translateY(-1px);
        }

        .password-toggle {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px; 
            padding: 4px;
            color: var(--text-muted);
            user-select: none;
            transition: color 0.2s ease, transform 0.2s ease;
            z-index: 5;
        }

        .password-toggle:hover {
            color: var(--primary);
            transform: translateY(-50%) scale(1.1);
        }

        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 15px; 
            color: var(--text-muted);
            margin-bottom: 35px;
        }

        .options-row label {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }

        .options-row input[type="checkbox"] {
            accent-color: var(--primary);
            width: 20px; 
            height: 20px;
            cursor: pointer;
        }

        .options-row a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .options-row a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #FFFFFF;
            border: none;
            padding: 18px; 
            border-radius: 18px;
            font-family: 'Poppins', sans-serif;
            font-size: 17px; 
            font-weight: 600;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            box-shadow: 0 6px 18px rgba(31, 93, 74, 0.25);
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(31, 93, 74, 0.4);
        }

        .footer-text {
            text-align: center;
            font-size: 15.5px; 
            color: var(--text-muted);
            margin-top: auto; 
            padding-top: 30px;
        }

        .footer-text button {
            background: none;
            border: none;
            color: var(--primary);
            font-weight: 700;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 15.5px;
            padding: 0 4px;
            text-decoration: none;
            position: relative;
            transition: color 0.3s ease;
        }

        .footer-text button:hover {
            color: var(--primary-dark);
        }

        .footer-text button::after {
            content: '';
            position: absolute;
            width: 100%;
            transform: scaleX(0);
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--primary-dark);
            transform-origin: bottom right;
            transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .footer-text button:hover::after {
            transform: scaleX(1);
            transform-origin: bottom left;
        }

        .form-section {
            opacity: 1;
            transform: translateY(0) scale(1);
            transition: opacity 0.6s cubic-bezier(0.25, 1, 0.5, 1), transform 0.6s cubic-bezier(0.25, 1, 0.5, 1), visibility 0.6s;
            width: 100%;
            display: flex;
            flex-direction: column;
        }

        .form-section.hidden {
            opacity: 0;
            position: absolute;
            transform: translateY(20px) scale(0.97); 
            visibility: hidden;
            pointer-events: none;
            width: calc(100% - 110px); 
        }

        @keyframes fadeInAndScale {
            from { opacity: 0; transform: scale(0.88); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes slideUpCard {
            from { opacity: 0; transform: translateY(60px); }
            to { opacity: 1; transform: translateY(0); }
        }

        #message-container {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transform: translateY(-8px);
            transition: max-height 0.5s cubic-bezier(0.25, 1, 0.5, 1), opacity 0.4s cubic-bezier(0.25, 1, 0.5, 1), transform 0.5s cubic-bezier(0.25, 1, 0.5, 1), margin-bottom 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        }

        #message-container.reveal-smooth {
            max-height: 120px; 
            opacity: 1;
            transform: translateY(0);
            margin-bottom: 24px;
        }

        .msg {
            text-align: center;
            font-size: 15px;
            font-weight: 500;
            padding: 14px;
            border-radius: 14px;
        }
        .msg.error { color: #C53030; background-color: #FFF5F5; border: 1px solid #FED7D7; }
        .msg.success { color: var(--primary); background-color: #F0FDF4; border: 1px solid #DCFCE7; }

        @media (max-width: 1024px) {
            .container { flex-direction: column; width: 100%; gap: 50px; padding: 25px 0; }
            .brand-side { max-width: 100%; }
            .form-card, .form-card.register-mode { width: 100%; max-width: 500px; min-height: auto; }
            .form-section.hidden { position: absolute; width: calc(100% - 110px); }
        }
    </style>
</head>
<body>

<div class="page-wrapper" id="masterWrapper">
    <div class="container">
        <div class="brand-side">
            <div class="logo-box">🔍</div>
            <h1>E-LOST <span>KOH</span><br>E-FOUND <span>MOH</span></h1>
        </div>

        <div id="dynamic-card" class="form-card <?php echo ($active_form == 'register') ? 'register-mode' : ''; ?>">
            <div class="form-padding-wrapper">
                
                <div id="message-container">
                    <?php echo $message; ?>
                </div>

                <div id="login-section" class="form-section <?php echo ($active_form == 'login') ? '' : 'hidden'; ?>">
                    <h2>Welcome Back</h2>
                    <form action="registration.php" method="POST">
                        <input type="hidden" name="action" value="login">
                        <div class="input-group">
                            <input type="text" name="username" placeholder="Username" required>
                        </div>
                        <div class="input-group">
                            <input type="password" name="password" id="login-password" placeholder="Password" required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('login-password', this)">👁️</button>
                        </div>
                        <div class="options-row">
                            <label><input type="checkbox" name="remember"> Remember me</label>
                            <a href="#">Forgot Password?</a>
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
                            <input type="password" name="password" id="register-password" placeholder="Password (Min 6 characters)" minlength="6" required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('register-password', this)">👁️</button>
                        </div>
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="register-confirm-password" placeholder="Confirm Password" minlength="6" required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('register-confirm-password', this)">👁️</button>
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
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleButton.textContent = '🙈'; 
    } else {
        passwordField.type = 'password';
        toggleButton.textContent = '👁️'; 
    }
}

function hideMessageSmoothly() {
    const msgContainer = document.getElementById('message-container');
    if (msgContainer && msgContainer.classList.contains('reveal-smooth')) {
        msgContainer.classList.remove('reveal-smooth');
        setTimeout(() => {
            msgContainer.innerHTML = '';
        }, 500);
    }
}

function toggleForm(formType) {
    const card = document.getElementById('dynamic-card');
    const loginSection = document.getElementById('login-section');
    const registerSection = document.getElementById('register-section');
    
    hideMessageSmoothly();

    if (formType === 'register') {
        loginSection.classList.add('hidden');
        card.classList.add('register-mode');
        setTimeout(() => {
            registerSection.classList.remove('hidden');
        }, 200); 
    } else {
        registerSection.classList.add('hidden');
        card.classList.remove('register-mode');
        setTimeout(() => {
            loginSection.classList.remove('hidden');
        }, 200);
    }
}

window.addEventListener('DOMContentLoaded', () => {
    const msgContainer = document.getElementById('message-container');
    const masterWrapper = document.getElementById('masterWrapper');
    
    const hasSuccess = <?php echo $registration_success ? 'true' : 'false'; ?>;

    if (msgContainer && msgContainer.innerHTML.trim() !== "") {
        setTimeout(() => {
            msgContainer.classList.add('reveal-smooth');
        }, 50);

        if (hasSuccess) {
            setTimeout(() => {
                masterWrapper.classList.add('page-exit');
                setTimeout(() => {
                    window.location.href = 'loading.html';
                }, 800);
            }, 1800); 
        } else {
            setTimeout(() => {
                hideMessageSmoothly();
            }, 3050); 
        }
    }
});
</script>

</body>
</html>