<?php
// api/login.php
declare(strict_types=1);
require __DIR__ . '/config.php';

$in = json_input();
$user = trim($in['username'] ?? '');
$pass = (string)($in['password'] ?? '');

if ($user === '' || $pass === '') {
  respond(['error' => 'Username and password are required.'], 400);
}

try {
  $pdo = db();
  $stmt = $pdo->prepare('SELECT id, full_name, username, password_hash FROM users WHERE username = ? LIMIT 1');
  $stmt->execute([$user]);
  $row = $stmt->fetch();

  if (!$row || !password_verify($pass, $row['password_hash'])) {
    respond(['error' => 'Invalid username or password.'], 401);
  }

  // Auth OK — store session
  $_SESSION['user_id'] = (int)$row['id'];
  $_SESSION['full_name'] = $row['full_name'];
  $_SESSION['username'] = $row['username'];

  respond(['ok' => true, 'user' => ['id' => (int)$row['id'], 'full_name' => $row['full_name'], 'username' => $row['username']]]);
} catch (Throwable $e) {
  respond(['error' => 'Server error while logging in.'], 500);
}
