<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
logoutUser();
$_SESSION['flash_success'] = 'You have been logged out.';
redirect('/admin/login.php');
