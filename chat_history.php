<?php
// เริ่มต้น Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบว่ามีการล็อกอินหรือไม่
if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "error" => "คุณต้องเข้าสู่ระบบ"]);
    exit();
}

// เชื่อมต่อฐานข้อมูล
function getDatabaseConnection() {
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "mydatabase";

    $conn = new mysqli($host, $user, $password, $database);
    if ($conn->connect_error) {
        die(json_encode(["success" => false, "error" => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error]));
    }
    return $conn;
}

$conn = getDatabaseConnection();

// รับค่า Match ID ที่ต้องการดึงประวัติแชท
if (!isset($_GET['match_id'])) {
    echo json_encode(["success" => false, "error" => "ไม่พบ match_id"]);
    exit();
}

$match_id = intval($_GET['match_id']);
$email = $_SESSION['email'];

// ดึง ID ของผู้ใช้
$sql_user = "SELECT id FROM profile1 WHERE email = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $email);
$stmt_user->execute();
$res_user = $stmt_user->get_result();
if ($res_user->num_rows === 0) {
    echo json_encode(["success" => false, "error" => "ไม่พบข้อมูลผู้ใช้"]);
    exit();
}
$user = $res_user->fetch_assoc();
$user_id = $user['id'];
$stmt_user->close();

// ดึงข้อมูลแชทระหว่างผู้ใช้กับคู่แมตช์
$sql_chat = "SELECT sender_id, receiver_id, message, timestamp FROM messages 
             WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
             ORDER BY timestamp ASC";

$stmt_chat = $conn->prepare($sql_chat);
$stmt_chat->bind_param("iiii", $user_id, $match_id, $match_id, $user_id);
$stmt_chat->execute();
$result_chat = $stmt_chat->get_result();

$messages = [];
while ($row = $result_chat->fetch_assoc()) {
    $messages[] = [
        "sender" => ($row['sender_id'] == $user_id) ? "me" : "match",
        "message" => $row['message'],
        "timestamp" => $row['timestamp']
    ];
}

$stmt_chat->close();
$conn->close();

echo json_encode(["success" => true, "messages" => $messages]);
?>
