<?php
session_start();
require_once __DIR__ . '/includes/auth.php';
Auth::logout();
header('Location: /crm/login.php');
exit;
