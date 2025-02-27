<?php
function getDatabaseConnection() {
    $servername = "localhost";  // หรือใช้ที่อยู่เซิร์ฟเวอร์ของคุณ
    $username = "root";         // ค่าเริ่มต้นของ XAMPP คือ 'root'
    $password = "";             // ค่าเริ่มต้นของ XAMPP ไม่มีรหัสผ่าน
    $dbname = "mydatabase";    // ⚠ เปลี่ยนเป็น "mydatabase" ตามภาพที่ส่งมา

    // เชื่อมต่อ MySQL
    $conn = new mysqli($servername, $username, $password, $dbname);

    // ตรวจสอบข้อผิดพลาดในการเชื่อมต่อ
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}
?>
