<?php
// api/signup.php
declare(strict_types=1);
require __DIR__ . '/config.php';

$in = json_input();
$full = trim($in['full_name'] ?? '');
$user = trim($in['username'] ?? '');
$pass = (string)($in['password'] ?? '');

if ($full === '' || $user === '' || $pass === '') {
  respond(['error' => 'All fields are required.'], 400);
}
if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $user)) {
  respond(['error' => 'Username must be 3–20 chars (letters, numbers, underscore).'], 400);
}
if (strlen($pass) < 6) {
  respond(['error' => 'Password must be at least 6 characters.'], 400);
}

$hash = password_hash($pass, PASSWORD_DEFAULT);

try {
  $pdo = db();
  $stmt = $pdo->prepare('INSERT INTO users (full_name, username, password_hash) VALUES (?, ?, ?)');
  $stmt->execute([$full, $user, $hash]);
  respond(['ok' => true, 'message' => 'Account created.']);
} catch (PDOException $e) {
  if ($e->getCode() === '23000') { // duplicate username
    respond(['error' => 'Username already exists.'], 409);
  }
  respond(['error' => 'Server error while creating account.'], 500);
}
