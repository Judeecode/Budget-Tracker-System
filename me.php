<?php
// api/me.php
declare(strict_types=1);
require __DIR__ . '/config.php';

if (!empty($_SESSION['user_id'])) {
  respond([
    'ok' => true,
    'user' => [
      'id' => (int)$_SESSION['user_id'],
      'full_name' => $_SESSION['full_name'],
      'username' => $_SESSION['username']
    ]
  ]);
}
respond(['error' => 'Not authenticated'], 401);
