<?php
require_once 'connect.php';
session_start();

if (!isset($_GET['match_id'])) {
    echo json_encode(['success' => false, 'error' => 'match_id missing']);
    exit();
}

$match_id = intval($_GET['match_id']);
$conn = getDatabaseConnection();

$sql = "SELECT * FROM messages WHERE match_id = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $match_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        'message' => $row['message'],
        'sender_id' => $row['sender_id'],
        'created_at' => $row['created_at']
    ];
}

echo json_encode(['success' => true, 'messages' => $messages]);
$stmt->close();
$conn->close();

?>
