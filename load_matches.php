<?php
require_once 'connect.php';
session_start();

try {
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

    $sql = "SELECT m.id AS match_id ,m.user1, u1.name AS user1_name, 
                m.user2, u2.name AS user2_name, u2.profile_pictures AS picture_user2, 
                u1.name AS user1_name, u1.profile_pictures AS picture_user1, 
                m.matched_at 
        FROM matches m
        LEFT JOIN profile1 u1 ON m.user1 = u1.id
        LEFT JOIN profile1 u2 ON m.user2 = u2.id
        WHERE m.user1 = ? OR m.user2 = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

    echo json_encode(["success" => true, "body" => $result->fetch_all(MYSQLI_ASSOC)]);

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}

?>
