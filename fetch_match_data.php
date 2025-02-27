<?php
require_once 'config.php';

$conn = getDatabaseConnection();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

$user_id = $_SESSION['user_id'];

$sql_match = "SELECT * FROM `match` WHERE user_id = ?";
$stmt_match = $conn->prepare($sql_match);
$stmt_match->bind_param("i", $user_id);
$stmt_match->execute();
$result_match = $stmt_match->get_result();
$match_data = $result_match->fetch_assoc();

// ถ้าไม่มีข้อมูลในฐานข้อมูล
if (!$match_data) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลในฐานข้อมูล']);
    exit;
}

// ป้องกัน NULL โดยแปลงเป็น "ไม่มีข้อมูล"
foreach ($match_data as $key => $value) {
    $match_data[$key] = $value ?? 'ไม่มีข้อมูล';
}

// Debugging: ตรวจสอบค่าก่อนส่ง JSON
error_log("🔍 ดึงข้อมูลจากฐานข้อมูล: " . json_encode($match_data, JSON_UNESCAPED_UNICODE));

// ส่ง JSON ออกไป
header('Content-Type: application/json; charset=UTF-8');
echo json_encode(['success' => true, 'data' => $match_data], JSON_UNESCAPED_UNICODE);
exit;
?>
