<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
require_once 'config.php';

try {
    // ✅ สร้างการเชื่อมต่อฐานข้อมูล
    $conn = getDatabaseConnection();

    // ✅ ตรวจสอบว่าเข้าสู่ระบบหรือยัง
    if (empty($_SESSION['user_id'])) {
        throw new Exception("❌ กรุณาเข้าสู่ระบบก่อน");
    }

    $user_id = $_SESSION['user_id'];

    // ✅ ดึงอีเมลของผู้ใช้
    $sql_user = "SELECT email FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user = $result_user->fetch_assoc();
    $stmt_user->close();

    if (!$user || empty($user['email'])) {
        throw new Exception("❌ ไม่พบอีเมลของผู้ใช้! กรุณาเข้าสู่ระบบใหม่");
    }

    $email = $user['email'];

    // ✅ รับ JSON จาก Frontend
    $json_data = file_get_contents('php://input');
    file_put_contents("debug_input.txt", $json_data . PHP_EOL, FILE_APPEND); // 🟢 LOG JSON ที่รับมา

    $decoded_data = json_decode($json_data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        die(json_encode(["success" => false, "message" => "❌ JSON Decode Error: " . json_last_error_msg()]));
    }

    // ✅ Debug ค่า JSON ที่รับมา
    file_put_contents("debug_input.txt", json_encode($decoded_data, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);

    // ✅ ฟิลด์ที่ต้องการบันทึก (เพิ่ม location & age_range)
    $fields = [
        'goal', 'zodiac', 'languages', 'education', 'family_plan',
        'covid_vaccine', 'love_expression', 'blood_type', 'pet', 'drink', 'exercise',
        'location', 'age_range' // 🎯 เพิ่มโลเคชั่นและอายุ
    ];

    // ✅ ตรวจสอบค่าที่จะบันทึก
    $updateFields = [];
    foreach ($fields as $field) {
        $updateFields[$field] = isset($decoded_data[$field]) && trim($decoded_data[$field]) !== "" ? trim($decoded_data[$field]) : "ไม่มีข้อมูล";
    }

    // ✅ Debug ค่าที่จะบันทึกลงฐานข้อมูล
    file_put_contents("debug_sql.txt", json_encode($updateFields, JSON_PRETTY_PRINT) . PHP_EOL, FILE_APPEND);

    // ✅ ตรวจสอบว่ามีข้อมูล `match` หรือไม่
    $sql_check = "SELECT 1 FROM `match` WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $exists = $result_check->num_rows > 0;
    $stmt_check->close();

    if ($exists) {
        // ✅ UPDATE ข้อมูล
        $update_set = implode(", ", array_map(fn($f) => "`$f` = ?", array_keys($updateFields)));
        $sql_update = "UPDATE `match` SET $update_set WHERE email = ?";

        // ✅ เตรียม SQL Statement
        $stmt_update = $conn->prepare($sql_update);
        if (!$stmt_update) {
            throw new Exception("❌ SQL Prepare Error: " . $conn->error);
        }

        // ✅ Bind ค่าและ Execute
        $types = str_repeat("s", count($updateFields)) . "s";
        $params = [...array_values($updateFields), $email];
        $stmt_update->bind_param($types, ...$params);
        $stmt_update->execute();
        $stmt_update->close();
    } else {
        // ✅ INSERT ข้อมูลใหม่
        $columns = "`email`, `" . implode("`, `", array_keys($updateFields)) . "`";
        $placeholders = implode(", ", array_fill(0, count($updateFields) + 1, "?"));
        $sql_insert = "INSERT INTO `match` ($columns) VALUES ($placeholders)";

        // ✅ เตรียม SQL Statement
        $stmt_insert = $conn->prepare($sql_insert);
        if (!$stmt_insert) {
            throw new Exception("❌ SQL Prepare Error: " . $conn->error);
        }

        // ✅ Bind ค่าและ Execute
        $types = str_repeat("s", count($updateFields)) . "s";
        $params = [...array_values($updateFields), $email];
        $stmt_insert->bind_param($types, ...$params);
        $stmt_insert->execute();
        $stmt_insert->close();
    }

    $conn->close();

    echo json_encode(["success" => true, "message" => "✅ บันทึกข้อมูลสำเร็จ!"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
