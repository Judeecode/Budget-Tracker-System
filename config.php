<?php
// api/config.php
declare(strict_types=1);

session_start();

/**
 * Database connection via PDO (stable & strict).
 * Adjust $DB_USER/$DB_PASS if you set a MySQL password.
 */
function db(): PDO {
  static $pdo = null;
  if ($pdo) return $pdo;

  $DB_HOST = '127.0.0.1';
  $DB_NAME = 'auth_demo';
  $DB_USER = 'root';
  $DB_PASS = ''; // set if you created one
  $DSN = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

  $options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    // You can enable persistent connection for performance in dev:
    // PDO::ATTR_PERSISTENT => true,
  ];
  try {
    $pdo = new PDO($DSN, $DB_USER, $DB_PASS, $options);
    return $pdo;
  } catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed."]);
    exit;
  }
}

function json_input(): array {
  $raw = file_get_contents('php://input') ?: '';
  $data = json_decode($raw, true);
  if (is_array($data)) return $data;
  // Fallback to form-encoded
  return $_POST ?: [];
}

function respond($data, int $code = 200): void {
  header('Content-Type: application/json; charset=utf-8');
  http_response_code($code);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}
