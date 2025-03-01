<?php
session_start();
require_once 'connect.php';
header("Content-Type: application/json");

// 🛠 Debug: ตรวจสอบค่าที่ถูกส่งมา
error_log("🟡 POST Data: " . json_encode($_POST));

if (!isset($_SESSION['email'])) {
    error_log("❌ Session หมดอายุ หรือไม่ได้เข้าสู่ระบบ");
    echo json_encode(["success" => false, "error" => "Session หมดอายุ กรุณาเข้าสู่ระบบใหม่"]);
    exit();
}

$conn = getDatabaseConnection();

// ✅ ดึง user_id จาก Session
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

// ✅ รับค่า match_id
$match_id = $_POST['match_id'] ?? null;

if (!$match_id || !is_numeric($match_id)) {
    error_log("⚠️ match_id ไม่ถูกต้อง: " . json_encode($match_id));
    echo json_encode(["success" => false, "error" => "match_id ไม่ถูกต้อง"]);
    exit();
}

// ✅ ตรวจสอบว่ามีแมตช์อยู่แล้วหรือไม่
$sql_check = "SELECT * FROM matches WHERE (user1 = ? AND user2 = ?) OR (user1 = ? AND user2 = ?)";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("iiii", $user_id, $match_id, $match_id, $user_id);
$stmt_check->execute();
$res_check = $stmt_check->get_result();
if ($res_check->num_rows > 0) {
    error_log("❌ คุณแมตช์กับผู้ใช้นี้แล้ว!");
    echo json_encode(["success" => false, "error" => "คุณแมตช์กับผู้ใช้นี้แล้ว!"]);
    exit();
}

// ✅ บันทึกแมตช์
$sql_insert = "INSERT INTO matches (user1, user2) VALUES (?, ?)";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("ii", $user_id, $match_id);

if ($stmt_insert->execute()) {
    error_log("✅ บันทึกแมตช์สำเร็จ!");
    echo json_encode(["success" => true, "message" => "บันทึกแมตช์สำเร็จ!"]);
} else {
    error_log("❌ SQL Error: " . $stmt_insert->error);
    echo json_encode(["success" => false, "error" => "❌ บันทึกข้อมูลไม่สำเร็จ: " . $conn->error]);
}

$stmt_insert->close();
$conn->close();
?>
