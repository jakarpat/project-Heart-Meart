<?php
// ตรวจสอบว่ามีการเปิด session แล้วหรือยัง
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// เปิดการแสดงข้อผิดพลาด (ปิดเมื่อขึ้นเซิร์ฟจริง)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'connect.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

$conn = getDatabaseConnection();
if (!$conn) {
    die("Database connection failed!");
}

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['email'])) {
    header("Location: login.php?error=You must login first.");
    exit();
}

// ดึง user_id ของผู้ใช้ที่ล็อกอิน
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($user_id <= 0) {
    die("No user_id found in session. กรุณาตรวจสอบค่า \$_SESSION['user_id']");
}
?>
