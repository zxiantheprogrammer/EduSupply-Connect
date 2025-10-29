<?php
include 'db.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';

if (!$username || !$password || !$role) {
  echo "All fields are required.";
  exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

try {
  // If role is teacher, insert into teachers table first
  if ($role === 'teacher') {
    $stmt = $conn->prepare("INSERT INTO teachers (full_name) VALUES (?)");
    $stmt->execute([$username]); // assuming username is the teacher's name
    $teacher_id = $conn->insert_id; // ✅ MySQLi method
  } else {
    $teacher_id = null;
  }

  // Insert into users table
  $stmt = $conn->prepare("INSERT INTO users (username, teacher_id, password, role) VALUES (?, ?, ?, ?)");
  $stmt->execute([$username, $teacher_id, $hashed, $role]);

  echo "Account created successfully!";
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
}
?>