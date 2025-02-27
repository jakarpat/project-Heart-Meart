<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['match_id']) || !isset($_POST['message'])) {
    echo json_encode(["success" => false, "error" => "Invalid Request"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$match_id = (int)$_POST['match_id'];
$message = trim($_POST['message']);

if (empty($message)) {
    echo json_encode(["success" => false, "error" => "Message cannot be empty"]);
    exit();
}

$conn = getDatabaseConnection();
$sql = "INSERT INTO messages (match_id, sender_id, message, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $match_id, $user_id, $message);
$success = $stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(["success" => $success]);
?>
