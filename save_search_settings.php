<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

try {
    $conn = getDatabaseConnection();

    if (empty($_SESSION['user_id'])) {
        throw new Exception("❌ กรุณาเข้าสู่ระบบก่อน");
    }

    $user_id = $_SESSION['user_id'];
    $json_data = file_get_contents("php://input");
    $decoded_data = json_decode($json_data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die(json_encode(["success" => false, "message" => "❌ JSON Decode Error: " . json_last_error_msg()]));
    }

    $fields = [
        'location', 'nearby_only', 'interest', 'age_range', 'age_nearby_only', 'travel_world'
    ];

    $updateFields = [];
    foreach ($fields as $field) {
        $updateFields[$field] = isset($decoded_data[$field]) ? trim($decoded_data[$field]) : "0";
    }

    $sql_check = "SELECT 1 FROM `search_settings` WHERE user_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $user_id);
    $stmt_check->execute();
    $exists = $stmt_check->get_result()->num_rows > 0;
    $stmt_check->close();

    if ($exists) {
        $update_set = implode(", ", array_map(fn($f) => "`$f` = ?", array_keys($updateFields)));
        $sql_update = "UPDATE `search_settings` SET $update_set WHERE user_id = ?";
    } else {
        $columns = "`user_id`, `" . implode("`, `", array_keys($updateFields)) . "`";
        $placeholders = implode(", ", array_fill(0, count($updateFields) + 1, "?"));
        $sql_update = "INSERT INTO `search_settings` ($columns) VALUES ($placeholders)";
    }

    $stmt_update = $conn->prepare($sql_update);
    if (!$stmt_update) {
        throw new Exception("❌ SQL Prepare Error: " . $conn->error);
    }

    $types = str_repeat("s", count($updateFields)) . "i";
    $params = [...array_values($updateFields), $user_id];
    $stmt_update->bind_param($types, ...$params);
    $stmt_update->execute();
    $stmt_update->close();
    $conn->close();

    echo json_encode(["success" => true, "message" => "✅ บันทึกข้อมูลสำเร็จ!"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
