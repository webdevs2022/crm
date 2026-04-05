<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Already logged in
if (Auth::check()) {
    header('Location: /crm/');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        $error = 'Please enter both email and password.';
    } else {
        $result = Auth::login($email, $password);
        if ($result['success']) {
            header('Location: /crm/');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Enterprise CRM</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root{--bg:#0d0f14;--bg2:#13161e;--surface:#1e2230;--border:#2a2f42;--accent:#4f7cff;--accent2:#7c5cfc;--green:#22d3a0;--red:#f0516c;--text:#e8ecf5;--text2:#8d95b0;--text3:#5a6180;--r:12px;--font:'DM Sans',sans-serif}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;background:var(--bg);color:var(--text);font-family:var(--font);display:flex;align-items:center;justify-content:center;padding:20px}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(ellipse 60% 50% at 50% 0%,rgba(79,124,255,.12),transparent);pointer-events:none}
.card{width:100%;max-width:420px;background:var(--bg2);border:1px solid var(--border);border-radius:18px;padding:40px;box-shadow:0 24px 80px rgba(0,0,0,.5)}
.logo{display:flex;align-items:center;gap:12px;margin-bottom:32px;justify-content:center}
.logo-mark{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:grid;place-items:center;font-size:18px;font-weight:700;color:#fff;box-shadow:0 0 24px rgba(79,124,255,.35)}
.logo-text{font-size:18px;font-weight:700}
.logo-sub{font-size:11px;color:var(--text3)}
h2{font-size:22px;font-weight:700;margin-bottom:6px;text-align:center}
.sub{color:var(--text3);font-size:13px;text-align:center;margin-bottom:28px}
.fg{margin-bottom:16px}
.fl{display:block;font-size:12px;font-weight:550;color:var(--text2);margin-bottom:6px;letter-spacing:.02em}
.fi-wrap{position:relative}
.fi-wrap i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:var(--text3);font-size:15px}
.fi{width:100%;padding:10px 13px 10px 40px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r);color:var(--text);font-family:var(--font);font-size:14px;outline:none;transition:border-color .15s,box-shadow .15s}
.fi:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(79,124,255,.12)}
.fi::placeholder{color:var(--text3)}
.eye{position:absolute;right:40px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text3);cursor:pointer;font-size:16px;transition:color .15s}
.eye:hover{color:var(--text)}
.error{background:rgba(240,81,108,.1);border:1px solid rgba(240,81,108,.3);border-radius:8px;padding:10px 14px;font-size:13px;color:var(--red);margin-bottom:16px;display:flex;align-items:center;gap:8px}
.btn-login{width:100%;padding:12px;background:var(--accent);color:#fff;border:none;border-radius:var(--r);font-family:var(--font);font-size:14px;font-weight:600;cursor:pointer;transition:all .15s;margin-top:4px;letter-spacing:.02em}
.btn-login:hover{background:#3d6de8;box-shadow:0 6px 20px rgba(79,124,255,.35)}
.divider{border:none;border-top:1px solid var(--border);margin:24px 0}
.demo-creds{background:var(--surface);border-radius:10px;padding:14px;font-size:12px}
.demo-creds h4{color:var(--text2);font-size:11px;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px}
.cred-row{display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid var(--border);gap:8px}
.cred-row:last-child{border-bottom:none;padding-bottom:0}
.cred-role{color:var(--accent);font-size:11.5px;font-weight:600;min-width:90px}
.cred-email{color:var(--text3);font-size:11px;font-family:monospace}
.cred-btn{background:none;border:none;color:var(--text3);font-size:10px;cursor:pointer;padding:2px 6px;border-radius:4px;border:1px solid var(--border);transition:all .15s;white-space:nowrap}
.cred-btn:hover{background:var(--accent);color:#fff;border-color:var(--accent)}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-mark">E</div>
    <div>
      <div class="logo-text">Enterprise CRM</div>
      <div class="logo-sub">Education Management Platform</div>
    </div>
  </div>

  <h2>Welcome Back</h2>
  <p class="sub">Sign in to your account to continue</p>

  <?php if ($error): ?>
  <div class="error"><i class="bi bi-exclamation-circle-fill"></i><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="./login.php">
    <div class="fg">
      <label class="fl" for="email">Email Address</label>
      <div class="fi-wrap">
        <i class="bi bi-envelope"></i>
        <input class="fi" type="email" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autocomplete="email">
      </div>
    </div>
    <div class="fg">
      <label class="fl" for="password">Password</label>
      <div class="fi-wrap">
        <i class="bi bi-lock"></i>
        <input class="fi" type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
        <button type="button" class="eye" onclick="togglePw()"><i class="bi bi-eye" id="eye-icon"></i></button>
      </div>
    </div>
    <button type="submit" class="btn-login">
      <i class="bi bi-box-arrow-in-right"></i> Sign In
    </button>
  </form>
</div>

<script>
function togglePw(){
  const f=document.getElementById('password');
  const i=document.getElementById('eye-icon');
  f.type=f.type==='password'?'text':'password';
  i.className=f.type==='password'?'bi bi-eye':'bi bi-eye-slash';
}
</script>
</body>
</html>
