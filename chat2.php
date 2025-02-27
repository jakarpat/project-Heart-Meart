<?php
require_once 'connect.php';
$conn = getDatabaseConnection();

$user1 = 73; // เจ้าของบัญชี
$user2 = 87; // คู่แมตช์

$sql = "SELECT messages.*, matches.user1, matches.user2 
        FROM matches 
        LEFT JOIN messages ON matches.id = messages.match_id 
        WHERE (matches.user1 = ? AND matches.user2 = ?) 
           OR (matches.user1 = ? AND matches.user2 = ?) 
        ORDER BY messages.created_at ASC"; // เรียงตามเวลาส่ง

$stmt_match = $conn->prepare($sql);
$stmt_match->bind_param("iiii", $user1, $user2, $user2, $user1); 
$stmt_match->execute();
$res_match = $stmt_match->get_result();

while ($data = $res_match->fetch_assoc()) {
    $sender = ($data['user1'] == $user1) ? "คุณ" : "คู่แมตช์"; // เช็คว่าฝั่งไหนเป็นคนส่ง

    echo "<div style='text-align:" . ($sender == "คุณ" ? "right" : "left") . ";'>";
    echo "<strong>$sender:</strong> " . htmlspecialchars($data['message']);
    echo "</div><br>";
}
?>
