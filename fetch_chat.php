<?php
session_start();
require_once 'connect.php';

if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

$email = $_SESSION['email'];
$match_id = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 0;

$conn = getDatabaseConnection();

// ดึง ID ของผู้ใช้ปัจจุบัน
$stmt = $conn->prepare("SELECT id FROM profile1 WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit();
}
$current_user = $result->fetch_assoc();
$user_id = $current_user['id'];
$stmt->close();

// ตรวจสอบว่าผู้ใช้เป็นส่วนหนึ่งของ match_id นี้หรือไม่
$stmt = $conn->prepare("SELECT * FROM matches WHERE id = ? AND (user1 = ? OR user2 = ?)");
$stmt->bind_param("iii", $match_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid match']);
    exit();
}
$stmt->close();

// ดึงข้อความแชทของ match_id นี้
$stmt = $conn->prepare("SELECT sender_id, message, created_at FROM messages WHERE match_id = ? ORDER BY created_at ASC");
$stmt->bind_param("i", $match_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'sender' => ($row['sender_id'] == $user_id) ? 'me' : 'them',
        'message' => $row['message'],
        'time' => $row['created_at']
    ];
}
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'messages' => $messages]);
?>
