<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "auth_demo");

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$user_id = $_SESSION['user_id'];

// Handle Add Expense
if (isset($_POST['add_expense'])) {
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $date = $_POST['date'];

    $stmt = $mysqli->prepare("INSERT INTO expenses (user_id, amount, category, date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $user_id, $amount, $category, $date);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

// Handle Edit Expense
if (isset($_POST['edit_expense'])) {
    $id = $_POST['id'];
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $date = $_POST['date'];

    $stmt = $mysqli->prepare("UPDATE expenses SET amount=?, category=?, date=? WHERE id=? AND user_id=?");
    $stmt->bind_param("dssii", $amount, $category, $date, $id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

// Fetch Expenses
$expenses = $mysqli->query("SELECT * FROM expenses WHERE user_id=$user_id ORDER BY date DESC");

// Grouped Views
$daily = $mysqli->query("SELECT DATE(date) as period, SUM(amount) as total FROM expenses WHERE user_id=$user_id GROUP BY DATE(date)");
$monthly = $mysqli->query("SELECT DATE_FORMAT(date, '%Y-%m') as period, SUM(amount) as total FROM expenses WHERE user_id=$user_id GROUP BY DATE_FORMAT(date, '%Y-%m')");
$yearly = $mysqli->query("SELECT YEAR(date) as period, SUM(amount) as total FROM expenses WHERE user_id=$user_id GROUP BY YEAR(date)");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Budget Tracker</title>
  <style>
    body { font-family: Arial, sans-serif; background: #5F0F40; margin:0; padding:20px; }
    .header { display:flex; justify-content:space-between; align-items:center; }
    h1 { color: #FB8B24; }
    .logout { padding:10px 20px; background:#A04747; color:white; border:none; cursor:pointer; border-radius:5px; }
    .card1 { background:#FFF8DC; padding:20px; margin-top:20px; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,0.1); }
    .card2 { background:#FFF8DC; padding:20px; margin-top:20px; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,0.1); }
    .card3 { background:#FFF8DC; padding:20px; margin-top:20px; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,0.1); }
    .card4 { background:#FFF8DC; padding:20px; margin-top:20px; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,0.1); }
    .card5 { background:#FFF8DC; padding:20px; margin-top:20px; border-radius:8px; box-shadow:0 4px 6px rgba(0,0,0,0.1); }
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    table, th, td { border:1px solid #ddd; }
    th, td { padding:10px; text-align:center; }
    form { margin-top:10px; }
    input, select { padding:8px; margin:5px; }
    .btn { background:#D8A25E; padding:8px 15px; border:none; border-radius:5px; cursor:pointer; }
    .btn:hover { background:#9A031E; }
  </style>
</head>
<body>
  <div class="header">
    <h1>💰 Budget Tracker</h1>
    <form action="api/logout.php" method="POST">
        <button class="logout">Logout</button>
    </form>
  </div>

  <div class="card1">
    <h2>Add Expense</h2>
    <form method="POST">
      <input type="number" name="amount" step="0.01" placeholder="Amount (₱)" required>
      <input type="text" name="category" placeholder="Category" required>
      <input type="date" name="date" required>
      <button class="btn" type="submit" name="add_expense">Add</button>
    </form>
  </div>

  <div class="card2">
    <h2>Your Expenses</h2>
    <table>
      <tr><th>Amount (₱)</th><th>Category</th><th>Date</th><th>Actions</th></tr>
      <?php while($row = $expenses->fetch_assoc()): ?>
        <tr>
          <td>₱<?= number_format($row['amount'], 2) ?></td>
          <td><?= $row['category'] ?></td>
          <td><?= $row['date'] ?></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <input type="number" name="amount" step="0.01" value="<?= $row['amount'] ?>" required>
              <input type="text" name="category" value="<?= $row['category'] ?>" required>
              <input type="date" name="date" value="<?= $row['date'] ?>" required>
              <button class="btn" type="submit" name="edit_expense">Update</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </table>
  </div>

  <div class="card3">
    <h2>Daily Expenses</h2>
    <table><tr><th>Date</th><th>Total (₱)</th></tr>
      <?php while($row = $daily->fetch_assoc()): ?>
        <tr><td><?= $row['period'] ?></td><td>₱<?= number_format($row['total'], 2) ?></td></tr>
      <?php endwhile; ?>
    </table>
  </div>

  <div class="card4">
    <h2>Monthly Expenses</h2>
    <table><tr><th>Month</th><th>Total (₱)</th></tr>
      <?php while($row = $monthly->fetch_assoc()): ?>
        <tr><td><?= $row['period'] ?></td><td>₱<?= number_format($row['total'], 2) ?></td></tr>
      <?php endwhile; ?>
    </table>
  </div>

  <div class="card5">
    <h2>Yearly Expenses</h2>
    <table><tr><th>Year</th><th>Total (₱)</th></tr>
      <?php while($row = $yearly->fetch_assoc()): ?>
        <tr><td><?= $row['period'] ?></td><td>₱<?= number_format($row['total'], 2) ?></td></tr>
      <?php endwhile; ?>
    </table>
  </div>
</body>
</html>
