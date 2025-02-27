<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydatabase";

// เชื่อมต่อฐานข้อมูล
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// ตรวจสอบว่าอีเมลอยู่ใน Session หรือไม่
if (!isset($_SESSION['email'])) {
    die("❌ ไม่พบอีเมลใน Session กรุณาสมัครสมาชิกใหม่!");
}

$email = $_SESSION['email']; // ดึงอีเมลจาก Session

// ตั้งค่าโฟลเดอร์อัปโหลด
$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ตรวจสอบว่ามีการส่งฟอร์มหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $dob = htmlspecialchars(trim($_POST['dob']));
    $gender = htmlspecialchars(trim($_POST['gender']));
    $interest = htmlspecialchars(trim($_POST['interest']));

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $uploadedImages = [];

    // ตรวจสอบและอัปโหลดไฟล์
    if (!empty($_FILES['profile_pictures']['name'][0])) {
        foreach ($_FILES['profile_pictures']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['profile_pictures']['error'][$key] === UPLOAD_ERR_OK) {
                $fileType = mime_content_type($tmpName);
                if (in_array($fileType, $allowedTypes)) {
                    $fileName = uniqid() . "_" . basename($_FILES['profile_pictures']['name'][$key]);
                    $targetFile = $uploadDir . $fileName;

                    if (move_uploaded_file($tmpName, $targetFile)) {
                        $uploadedImages[] = $targetFile;
                    } else {
                        echo "❌ อัปโหลดรูปภาพล้มเหลว: " . $_FILES['profile_pictures']['name'][$key] . "<br>";
                    }
                } else {
                    echo "❌ ประเภทไฟล์ไม่รองรับ: " . $_FILES['profile_pictures']['name'][$key] . "<br>";
                }
            } else {
                echo "❌ เกิดข้อผิดพลาดในการอัปโหลดไฟล์: " . $_FILES['profile_pictures']['name'][$key] . "<br>";
            }
        }
    }

    // กำหนดรูปแรกเป็นรูปโปรไฟล์หลัก
    $profileMainImage = $uploadedImages[0] ?? ''; // ใช้รูปแรก หรือเว้นว่างถ้าไม่มีรูป

    // แปลงรูปภาพที่อัปโหลดให้เป็นสตริงที่คั่นด้วยเครื่องหมายจุลภาค
    $profilePictures = !empty($uploadedImages) ? implode(',', $uploadedImages) : '';

    // ตรวจสอบก่อนบันทึกข้อมูล
    if (!empty($name) && !empty($dob) && !empty($gender) && !empty($interest)) {
        try {
            $stmt = $conn->prepare("INSERT INTO profile1 (name, email, dob, gender, interest, profile_pictures, profile_main, created_at) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssssss", $name, $email, $dob, $gender, $interest, $profilePictures, $profileMainImage);

            if ($stmt->execute()) {
                $_SESSION['profile'] = [
                    'username' => $name,
                    'email' => $email,
                    'dob' => $dob,
                    'gender' => $gender,
                    'interest' => $interest,
                    'profile_pictures' => $profilePictures,
                    'profile_main' => $profileMainImage
                ];
                header("Location: register_index.php"); // ✅ เปลี่ยนเป็นหน้าหลักหลังสร้างโปรไฟล์
                exit();
            } else {
                echo "❌ เกิดข้อผิดพลาด: " . $stmt->error;
            }
        } catch (Exception $e) {
            echo "❌ ข้อผิดพลาดในการบันทึกข้อมูล: " . $e->getMessage();
        }

        $stmt->close();
    } else {
        echo "❌ กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}

$conn->close();
?>
