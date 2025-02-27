<?php
// สร้างรหัสผ่านที่เข้ารหัส
$password_plain = "123"; // ใส่รหัสผ่านที่ต้องการ
$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

// แสดงผลรหัสผ่านที่เข้ารหัส
echo "รหัสผ่านที่เข้ารหัส: " . $password_hashed;
?>
