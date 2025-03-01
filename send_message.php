<?php
session_start();
require_once 'connect.php';
$conn = getDatabaseConnection();

if (!isset($_SESSION['user_id']) || !isset($_POST['match_id']) || !isset($_POST['message'])) {
    echo json_encode(["success" => false, "error" => "Invalid Request"]);
    exit();
}
$sql_user = "SELECT id FROM profile1 WHERE email = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $_SESSION['email']);
$stmt_user->execute();
$res_user = $stmt_user->get_result();
if ($res_user->num_rows === 0) {
    error_log("❌ ไม่พบข้อมูลผู้ใช้");
    echo json_encode(["success" => false, "error" => "ไม่พบข้อมูลผู้ใช้"]);
    exit();
}
$user = $res_user->fetch_assoc();
$user_id = $user['id'];
$stmt_user->close();
echo $user_id;
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
