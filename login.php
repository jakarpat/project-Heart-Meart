<?php
session_start(); // เริ่มต้น Session

// เชื่อมต่อฐานข้อมูล
function getDatabaseConnection() {
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "mydatabase";

    $conn = new mysqli($host, $user, $password, $database);
    if ($conn->connect_error) {
        die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
    }
    return $conn;
}

$conn = getDatabaseConnection();

// ตรวจสอบว่ามีการส่งค่า login หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // ตรวจสอบข้อมูลผู้ใช้
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];

            // ตั้งค่าให้ Session คงอยู่ในทุกแท็บ
            session_set_cookie_params([
                'lifetime' => 0, // คงอยู่จนกว่าปิด Browser
                'path' => '/',
                'domain' => '', // สามารถใช้ Session ได้ทุกโดเมนย่อย
                'secure' => isset($_SERVER["HTTPS"]), // ใช้ HTTPS เท่านั้น
                'httponly' => true,
                'samesite' => 'Lax' // ป้องกัน CSRF Attack
            ]);
            session_regenerate_id(true); // ป้องกัน Session Hijacking

            echo json_encode(["success" => true, "message" => "ล็อกอินสำเร็จ"]);
        } else {
            echo json_encode(["success" => false, "error" => "รหัสผ่านไม่ถูกต้อง"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "ไม่พบอีเมลนี้ในระบบ"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "error" => "คำขอไม่ถูกต้อง"]);
}
?>
