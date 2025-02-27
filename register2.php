<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// การตั้งค่าฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydatabase";

// สร้างการเชื่อมต่อฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("❌ การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// ตรวจสอบว่ามีข้อมูลจากฟอร์มส่งมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $pass = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_pass = isset($_POST['confirm-password']) ? trim($_POST['confirm-password']) : '';

    // ✅ ตรวจสอบว่าฟิลด์ไม่ว่างเปล่า
    if (empty($user) || empty($email) || empty($pass) || empty($confirm_pass)) {
        header("Location: register_2.html?error=กรุณากรอกข้อมูลให้ครบทุกช่อง");
        exit();
    }

    // ✅ ตรวจสอบรูปแบบอีเมล
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register_2.html?error=รูปแบบอีเมลไม่ถูกต้อง");
        exit();
    }

    // ✅ ตรวจสอบว่ารหัสผ่านตรงกันหรือไม่
    if ($pass !== $confirm_pass) {
        header("Location: register_2.html?error=รหัสผ่านไม่ตรงกัน");
        exit();
    }

    // ✅ แฮชรหัสผ่าน
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

    // ✅ ตรวจสอบว่ามีผู้ใช้ซ้ำหรือไม่
    if ($stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?")) {
        $stmt->bind_param("ss", $user, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            header("Location: register_2.html?error=ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้งานแล้ว");
            $stmt->close();
            exit();
        }
        $stmt->close();
    }

    // ✅ เพิ่มข้อมูลลงฐานข้อมูล (ลบ `phone` ออก)
    if ($stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)")) {
        $stmt->bind_param("sss", $user, $hashed_password, $email);

        if ($stmt->execute()) {
            $_SESSION['email'] = $email;
            header("Location: profile1.php");
            exit();
        } else {
            header("Location: register_2.html?error=เกิดข้อผิดพลาด กรุณาลองใหม่");
        }

        $stmt->close();
    } else {
        header("Location: register_2.html?error=ไม่สามารถเพิ่มข้อมูลได้");
    }
}

$conn->close();
?>
