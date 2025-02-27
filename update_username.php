<?php
session_start();
require_once 'config.php';

header("Content-Type: application/json");

// ✅ ตรวจสอบว่า Session มีค่า user_id หรือไม่
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "กรุณาเข้าสู่ระบบ"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ ตรวจสอบว่ามีค่าถูกส่งมาหรือไม่
if (!isset($_POST['username']) || trim($_POST['username']) === '') {
    echo json_encode(["success" => false, "message" => "ชื่อใหม่ไม่สามารถเว้นว่างได้"]);
    exit();
}

$newUsername = trim($_POST['username']);
$conn = getDatabaseConnection();

if (!$conn) {
    echo json_encode(["success" => false, "message" => "❌ เชื่อมต่อฐานข้อมูลล้มเหลว"]);
    exit();
}

// ✅ SQL อัปเดตชื่อ
$sql = "UPDATE users SET username = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("si", $newUsername, $user_id);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "บันทึกชื่อใหม่สำเร็จ"]);
    } else {
        echo json_encode(["success" => false, "message" => "เกิดข้อผิดพลาดในการบันทึก: " . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "SQL Error: " . $conn->error]);
}

$conn->close();
?>
