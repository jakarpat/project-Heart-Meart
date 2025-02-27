<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// เชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydatabase";

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("❌ การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// ตรวจสอบการล็อกอิน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // ✅ ค้นหาข้อมูลจากตาราง users
    $sql = "SELECT id, username, email, password FROM users WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($user_id, $username, $email, $hashed_password);
        $stmt->fetch();

        if ($user_id) {
            // ✅ ตรวจสอบรหัสผ่าน
            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;

                // ✅ ตรวจสอบว่ามีโปรไฟล์ใน `profile1` หรือยัง
                $stmt->close();
                $sql_check_profile = "SELECT id FROM profile1 WHERE email = ?";
                $stmt = $conn->prepare($sql_check_profile);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    // ถ้ามีโปรไฟล์แล้วให้ไปที่ MatchPage
                    header("Location: MatchPage.php");
                } else {
                    // ถ้ายังไม่มีโปรไฟล์ ให้ไปสร้างโปรไฟล์ก่อน
                    header("Location: profile1.php");
                }
                exit();
            } else {
                header("Location: register_index.php?error=รหัสผ่านไม่ถูกต้อง");
                exit();
            }
        } else {
            header("Location: register_index.php?error=อีเมลนี้ไม่มีอยู่ในระบบ");
            exit();
        }
        $stmt->close();
    }
}

$conn->close();
?>
