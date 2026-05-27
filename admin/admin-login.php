<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include 'config/db.php';

$message = "";

// Already logged in as admin
if(isset($_SESSION['admin_id'])){
    header("Location: admin-dashboard.php");
    exit();
}

// Pull redirect messages
if(isset($_SESSION['redirect_message'])){
    $message = $_SESSION['redirect_message'];
    unset($_SESSION['redirect_message']);
}

// ── LOGIN PROCESSING ────────────────────────────────────────────
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT admin_id, admin_name, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $admin = $result->fetch_assoc();
        if(password_verify($password, $admin['password'])){
            $_SESSION['admin_id']   = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['admin_name'];
            header("Location: admin-dashboard.php");
            exit();
        } else {
            $_SESSION['redirect_message'] = "<div class='msg error'>Incorrect password.</div>";
        }
    } else {
        $_SESSION['redirect_message'] = "<div class='msg error'>Admin account not found.</div>";
    }
    $stmt->close();
    header("Location: admin-login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - E-LOST KOH, E-FOUND MOH</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">
<style>
/* ─── RESET ──────────────────────────────────────────────────── */
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

:root {
    --pg:   #1F5D4A;
    --pgd:  #143F32;
    --gold: #F1B846;
    --lg:   #BBC34A;
    --bg:   #6B7C5C;   /* same olive-green background from screenshot */
    --white:#FFFFFF;
    --dark: #1A1A1A;
    --muted:#7A7A7A;
    --border:#E0E0E0;
    --radius:14px;
}

body {
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    background: var(--bg);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}

/* Subtle texture overlay — same vibe as the screenshot */
body::before {
    content: '';
    position: fixed;
    inset: 0;
    background:
        radial-gradient(ellipse at 20% 50%, rgba(31,93,74,.35) 0%, transparent 60%),
        radial-gradient(ellipse at 80% 20%, rgba(20,63,50,.25) 0%, transparent 55%);
    pointer-events: none;
    z-index: 0;
}

/* ─── OUTER CARD (matches registration's .container) ─────────── */
.container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 780px;
    background: rgba(31, 93, 74, 0.72);
    border-radius: 22px;
    padding: 52px 48px;
    display: flex;
    align-items: center;
    gap: 52px;
    box-shadow: 0 28px 70px rgba(0,0,0,0.28);
    border: 1px solid rgba(255,255,255,0.08);
    backdrop-filter: blur(6px);
}

/* ─── BRAND SIDE ─────────────────────────────────────────────── */
.brand-side {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    flex-shrink: 0;
}

.logo-box {
    width: 96px; height: 96px;
    background: linear-gradient(135deg, var(--pg), var(--pgd));
    border: 3px solid var(--gold);
    border-radius: 22px;
    display: flex; align-items: center; justify-content: center;
    font-size: 42px;
    box-shadow: 0 14px 36px rgba(0,0,0,0.35), inset 0 3px 7px rgba(255,255,255,0.15);
    transition: transform .7s cubic-bezier(.2,.8,.2,1);
}
.logo-box:hover { transform: scale(1.06) rotate(4deg); }

.brand-side h1 {
    font-family: 'Poppins', sans-serif;
    font-size: 22px; font-weight: 800;
    color: #fff;
    text-align: center;
    line-height: 1.3;
    letter-spacing: .5px;
}

.brand-side h1 span { color: var(--lg); }

.admin-badge {
    margin-top: 4px;
    background: rgba(241,184,70,.18);
    border: 1px solid rgba(241,184,70,.45);
    color: var(--gold);
    font-size: 11px; font-weight: 700;
    letter-spacing: 1.6px;
    text-transform: uppercase;
    padding: 5px 14px;
    border-radius: 999px;
}

/* ─── FORM CARD ──────────────────────────────────────────────── */
.form-card {
    flex: 1;
    background: var(--white);
    border-radius: var(--radius);
    padding: 38px 36px 34px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.14);
}

.form-card h2 {
    font-family: 'Poppins', sans-serif;
    font-size: 22px; font-weight: 700;
    color: var(--pg);
    margin-bottom: 24px;
    text-align: center;
}

/* ─── MESSAGES ───────────────────────────────────────────────── */
#message-container { margin-bottom: 0; }

.msg {
    padding: 11px 16px;
    border-radius: 9px;
    font-size: 13.5px; font-weight: 500;
    margin-bottom: 18px;
    opacity: 0;
    transform: translateY(-6px);
    transition: opacity .4s ease, transform .4s ease;
}

.msg.reveal-smooth { opacity: 1; transform: translateY(0); }

.msg.error   { background: #FEE8E8; color: #C0392B; border: 1px solid #f5c6c6; }
.msg.success { background: #E8F5EA; color: #1E7E34; border: 1px solid #c3e6cb; }

/* ─── INPUTS ─────────────────────────────────────────────────── */
.input-group {
    position: relative;
    margin-bottom: 14px;
}

.input-group input {
    width: 100%;
    padding: 12px 44px 12px 16px;
    border: 1.5px solid var(--border);
    border-radius: 9px;
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    color: var(--dark);
    background: #FAFAFA;
    outline: none;
    transition: border .2s, background .2s;
}

.input-group input:focus {
    border-color: var(--pg);
    background: #fff;
}

.input-group input::placeholder { color: #ADADAD; }

/* password toggle */
.password-toggle {
    position: absolute;
    right: 12px; top: 50%;
    transform: translateY(-50%);
    background: none; border: none;
    cursor: pointer; padding: 4px;
    color: #ADADAD;
    display: flex; align-items: center;
    transition: color .2s;
}
.password-toggle:hover { color: var(--pg); }
.password-toggle svg {
    width: 18px; height: 18px;
    fill: none; stroke: currentColor;
    stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
}

/* ─── OPTIONS ROW ────────────────────────────────────────────── */
.options-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    font-size: 13px;
}

.options-row label {
    display: flex; align-items: center; gap: 7px;
    color: var(--muted); cursor: pointer;
    font-weight: 500;
}

.options-row label input[type="checkbox"] {
    accent-color: var(--pg);
    width: 15px; height: 15px;
}

.options-row a {
    color: var(--pg); font-weight: 600;
    text-decoration: none;
    transition: opacity .15s;
}
.options-row a:hover { opacity: .75; }

/* ─── BUTTON ─────────────────────────────────────────────────── */
.btn-primary {
    width: 100%;
    padding: 13px;
    background: var(--pg);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-family: 'Poppins', sans-serif;
    font-size: 15px; font-weight: 700;
    letter-spacing: .4px;
    cursor: pointer;
    transition: background .2s, transform .15s;
}
.btn-primary:hover {
    background: var(--pgd);
    transform: translateY(-1px);
}
.btn-primary:active { transform: translateY(0); }

/* ─── DIVIDER ────────────────────────────────────────────────── */
.divider {
    display: flex; align-items: center; gap: 10px;
    margin: 20px 0 16px;
    color: #CCC; font-size: 12px;
}
.divider::before, .divider::after {
    content: ''; flex: 1;
    height: 1px; background: #EAEAEA;
}

/* ─── BACK LINK ──────────────────────────────────────────────── */
.back-link {
    display: flex; align-items: center; justify-content: center;
    gap: 6px;
    font-size: 13px; font-weight: 600;
    color: var(--muted);
    text-decoration: none;
    transition: color .2s;
    margin-top: 4px;
}
.back-link:hover { color: var(--pg); }
.back-link svg { width: 14px; height: 14px; }

/* ─── RESPONSIVE ─────────────────────────────────────────────── */
@media (max-width: 640px) {
    .container {
        flex-direction: column;
        gap: 28px;
        padding: 36px 24px;
    }
    .brand-side h1 { font-size: 20px; }
    .form-card { padding: 28px 20px; }
}
</style>
</head>
<body>

<div class="container">

    <!-- BRAND SIDE -->
    <div class="brand-side">
        <div class="logo-box">🔍</div>
        <h1>E-LOST <span>MOH</span><br>E-FOUND <span>KOH</span></h1>
        <div class="admin-badge">Admin Portal</div>
    </div>

    <!-- FORM CARD -->
    <div class="form-card">
        <h2>Admin Login</h2>

        <div id="message-container"><?php echo $message; ?></div>

        <form action="admin-login.php" method="POST">

            <div class="input-group">
                <input
                    type="text"
                    name="username"
                    placeholder="Admin Username"
                    required
                    autocomplete="username"
                >
            </div>

            <div class="input-group">
                <input
                    type="password"
                    name="password"
                    id="admin-password"
                    placeholder="Password"
                    required
                    autocomplete="current-password"
                >
                <button type="button" class="password-toggle" onclick="togglePass()">
                    <svg viewBox="0 0 24 24" id="eye-icon">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>

            <div class="options-row">
                <label>
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="admin-forgot-password.php">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-primary">LOG IN</button>

        </form>

        <div class="divider">or</div>

        <a href="registration.php" class="back-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            Back to User Login
        </a>
    </div>

</div>

<script>
function togglePass() {
    const field = document.getElementById('admin-password');
    const icon  = document.getElementById('eye-icon');
    const isHidden = field.type === 'password';
    field.type = isHidden ? 'text' : 'password';
    icon.innerHTML = isHidden
        ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>`
        : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>`;
}

window.addEventListener('DOMContentLoaded', () => {
    const msg = document.getElementById('message-container');
    if(msg && msg.innerHTML.trim() !== ''){
        setTimeout(() => msg.querySelector('.msg')?.classList.add('reveal-smooth'), 50);
        setTimeout(() => {
            const m = msg.querySelector('.msg');
            if(m){ m.classList.remove('reveal-smooth'); setTimeout(() => msg.innerHTML = '', 500); }
        }, 4000);
    }
});
</script>

</body>
</html>