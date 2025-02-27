<?php
session_start();
require_once 'connect.php';

header("Content-Type: application/json");

if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "error" => "คุณต้องเข้าสู่ระบบ"]);
    exit();
}

$conn = getDatabaseConnection();

// ✅ ดึง user_id
$sql_user = "SELECT id FROM profile1 WHERE email = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $_SESSION['email']);
$stmt_user->execute();
$res_user = $stmt_user->get_result();
$user = $res_user->fetch_assoc();
$user_id = $user['id'];
$stmt_user->close();

// ✅ รับค่าจาก AJAX
$match_id = $_POST['match_id'] ?? null;
$message = trim($_POST['message'] ?? "");

error_log("📩 รับข้อความจาก user_id: $user_id | match_id: $match_id | message: " . json_encode($message));

// ✅ ตรวจสอบค่า
if (!$match_id || !is_numeric($match_id) || empty($message)) {
    echo json_encode(["success" => false, "error" => "ข้อมูลไม่ถูกต้อง"]);
    exit();
}

// ✅ บันทึกข้อความลงฐานข้อมูล
$sql_insert = "INSERT INTO messages (match_id, sender_id, message, created_at, is_read) VALUES (?, ?, ?, NOW(), 0)";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("iis", $match_id, $user_id, $message);

if ($stmt_insert->execute()) {
    echo json_encode(["success" => true, "message" => "ส่งข้อความสำเร็จ!"]);
} else {
    error_log("❌ บันทึกข้อความไม่สำเร็จ: " . $conn->error);
    echo json_encode(["success" => false, "error" => "บันทึกข้อความไม่สำเร็จ"]);
}

$stmt_insert->close();
$conn->close();
?>
