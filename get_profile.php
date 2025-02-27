<?php
session_start();
require_once 'connect.php';

// เปิดการแสดงข้อผิดพลาด (แนะนำให้ปิดเมื่อขึ้นเซิร์ฟจริง)
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// ตั้งค่าภาพโปรไฟล์เริ่มต้น
$defaultImage = "uploads/default_profile.jpg";
$userProfileImage = $defaultImage;
$chatPartnerImage = $defaultImage;
$chatPartnerName = "คู่แมตช์";

// ฟังก์ชันช่วยตรวจสอบไฟล์และ URL
function getProfileImage($profileImage) {
    global $defaultImage;
    if (!empty($profileImage) && filter_var($profileImage, FILTER_VALIDATE_URL)) {
        return $profileImage;
    }
    $imagePath = "uploads/" . trim($profileImage);
    return (!empty($profileImage) && file_exists($imagePath)) ? $imagePath : $defaultImage;
}

// ตรวจสอบว่าตาราง `profile1` มีคอลัมน์ `profile_image` หรือไม่
$hasProfileImageColumn = false;
$columnCheckQuery = "SHOW COLUMNS FROM profile1 LIKE 'profile_image'";
$columnCheckResult = $conn->query($columnCheckQuery);
$hasProfileImageColumn = $columnCheckResult && $columnCheckResult->num_rows > 0;

// ดึงข้อมูลรูปโปรไฟล์ของผู้ใช้ที่ล็อกอิน
if ($hasProfileImageColumn) {
    $sql_user = "SELECT profile_image FROM profile1 WHERE id = ?";
    $stmt = $conn->prepare($sql_user);
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res_user = $stmt->get_result();
        if ($res_user->num_rows > 0) {
            $row_user = $res_user->fetch_assoc();
            $userProfileImage = getProfileImage($row_user['profile_image'] ?? '');
        }
        $stmt->close();
    }
}

// ดึงข้อมูลคู่สนทนา
$match_id = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 0;
if ($match_id > 0 && $hasProfileImageColumn) {
    $sql_partner = "SELECT name, profile_image FROM profile1 WHERE id = ?";
    $stmt = $conn->prepare($sql_partner);
    if ($stmt) {
        $stmt->bind_param("i", $match_id);
        $stmt->execute();
        $res_partner = $stmt->get_result();
        if ($res_partner->num_rows > 0) {
            $row_partner = $res_partner->fetch_assoc();
            $chatPartnerName = $row_partner['name'] ?? "คู่แมตช์";
            $chatPartnerImage = getProfileImage($row_partner['profile_image'] ?? '');
        }
        $stmt->close();
    }
}

$conn->close();
?>
