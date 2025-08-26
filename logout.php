<?php
// api/logout.php
declare(strict_types=1);
require __DIR__ . '/config.php';

// Destroy all session data
$_SESSION = [];
session_destroy();

// Redirect back to login/signup form
header("Location: ../index.html");
exit;
