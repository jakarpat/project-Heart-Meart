<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

session_start();
require_once 'config.php';

try {
    if (empty($_SESSION['user_id'])) {
        throw new Exception("กรุณาเข้าสู่ระบบก่อน");
    }

    $conn = getDatabaseConnection();
    $user_id = $_SESSION['user_id'];

    $updateFields = [
        'goal' => $_POST['goal'] ?? null,
        'zodiac' => $_POST['zodiac'] ?? null,
        'languages' => $_POST['languages'] ?? null,
        'education' => $_POST['education'] ?? null,
        'family_plan' => $_POST['family_plan'] ?? null,
        'covid_vaccine' => $_POST['covid_vaccine'] ?? null,
        'love_expression' => $_POST['love_expression'] ?? null,
        'blood_type' => $_POST['blood_type'] ?? null,
        'pet' => $_POST['pet'] ?? null,
        'drink' => $_POST['drink'] ?? null,
        'exercise' => $_POST['exercise'] ?? null
    ];

    // **ลบค่าที่เป็น NULL ออกจากอาร์เรย์**
    $updateFields = array_filter($updateFields, fn($value) => $value !== null);

    if (empty($updateFields)) {
        throw new Exception("ไม่มีข้อมูลที่ต้องอัปเดต");
    }

    // **สร้าง SQL Query**
    $columns = implode(", ", array_keys($updateFields));
    $placeholders = implode(", ", array_fill(0, count($updateFields), "?"));
    $updateString = implode(", ", array_map(fn($col) => "$col = VALUES($col)", array_keys($updateFields)));

    $sql_update = "INSERT INTO `match` (user_id, $columns) 
                   VALUES (?, $placeholders) 
                   ON DUPLICATE KEY UPDATE $updateString";

    $stmt_update = $conn->prepare($sql_update);
    
    if ($stmt_update) {
        // **สร้าง Data Type String ("i" สำหรับ user_id และ "s" สำหรับ String)**
        $types = "i" . str_repeat("s", count($updateFields));
        $values = array_merge([$user_id], array_values($updateFields));

        // **Bind Parameters**
        $stmt_update->bind_param($types, ...$values);

        if (!$stmt_update->execute()) {
            throw new Exception("❌ เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $stmt_update->error);
        }
        $stmt_update->close();
    } else {
        throw new Exception("❌ การเตรียมคำสั่ง SQL ผิดพลาด: " . $conn->error);
    }

    $conn->close();
    echo json_encode(['success' => true, 'message' => 'บันทึกข้อมูลสำเร็จ!']);
    exit();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}
?>
