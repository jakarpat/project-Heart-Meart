<?php
// เปิดการรายงานข้อผิดพลาด
error_reporting(E_ALL);
ini_set('display_errors', 1);

// เริ่มต้น Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// โหลดการตั้งค่าฐานข้อมูล
require_once 'config.php';

try {
    // สร้างการเชื่อมต่อฐานข้อมูล
    $conn = getDatabaseConnection();

    // ตรวจสอบว่าเข้าสู่ระบบหรือยัง
    if (empty($_SESSION['user_id'])) {
        throw new Exception("กรุณาเข้าสู่ระบบก่อน");
    }

    // ดึง user_id จาก Session
    $user_id = $_SESSION['user_id'];

    // ตรวจสอบว่ามีการส่งค่าจากฟอร์มหรือไม่
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_images'])) {
        $uploadDir = 'uploads/';
        $uploadedFiles = [];

        foreach ($_FILES['profile_images']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['profile_images']['error'][$index] === UPLOAD_ERR_OK) {
                $fileName = basename($_FILES['profile_images']['name'][$index]);
                $targetFilePath = $uploadDir . $fileName;

                if (move_uploaded_file($tmpName, $targetFilePath)) {
                    $uploadedFiles[] = $targetFilePath; // เก็บเส้นทางไฟล์ที่อัปโหลด
                }
            }
        }

        echo json_encode(['success' => true, 'files' => $uploadedFiles]);
        exit();
    }

    $updateFields = [];

    // ตรวจสอบและบันทึกค่า goal
    if (isset($_POST['goal'])) {
        $goal = $_POST['goal'];
        $updateFields['goal'] = $goal;
    }

    // ตรวจสอบและบันทึกค่า zodiac
    if (isset($_POST['zodiac'])) {
        $zodiac = $_POST['zodiac'];
        $updateFields['zodiac'] = $zodiac;
    }

    // ตรวจสอบและบันทึกค่า love expression
    if (isset($_POST['love_expression'])) {
        $love_expression = $_POST['love_expression'];
        $updateFields['love_expression'] = $love_expression;
    }

    // บันทึกค่าที่อัปเดตลงในตาราง match
    foreach ($updateFields as $field => $value) {
        $sql_update = "INSERT INTO `match` (user_id, $field) VALUES (?, ?)
                       ON DUPLICATE KEY UPDATE $field = VALUES($field)";
        $stmt_update = $conn->prepare($sql_update);

        if ($stmt_update) {
            $stmt_update->bind_param("is", $user_id, $value);
            if (!$stmt_update->execute()) {
                throw new Exception("เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $stmt_update->error);
            }
            $stmt_update->close();
        } else {
            throw new Exception("การเตรียมคำสั่ง SQL ผิดพลาด: " . $conn->error);
        }
    }

    $success_message = "บันทึกข้อมูลสำเร็จ!";

    // ดึงข้อมูลผู้ใช้จากตาราง users และ profile1
    $sql = "
        SELECT 
            u.username AS username, 
            u.email AS user_email,
            p.dob, 
            p.gender, 
            p.interest, 
            p.profile_pictures
        FROM users u
        LEFT JOIN profile1 p ON u.email = p.email
        WHERE u.id = ?
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("การเตรียมคำสั่ง SQL ผิดพลาด: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $profile1 = $result->num_rows > 0 ? $result->fetch_assoc() : [
        'username' => 'ไม่ระบุ',
        'user_email' => 'ไม่ระบุ',
        'dob' => null,
        'gender' => 'ไม่ระบุ',
        'interest' => 'ไม่ระบุ',
        'profile_pictures' => 'default_profile.jpg',
    ];
    $stmt->close();

    // ดึงข้อมูล match สำหรับ user_id นี้
    $sql_match = "SELECT * FROM `match` WHERE user_id = ?";
    $stmt_match = $conn->prepare($sql_match);
    if (!$stmt_match) {
        throw new Exception("การเตรียมคำสั่ง SQL สำหรับ match ผิดพลาด: " . $conn->error);
    }
    $stmt_match->bind_param("i", $user_id);
    $stmt_match->execute();
    $result_match = $stmt_match->get_result();
    $match_data = $result_match->num_rows > 0 ? $result_match->fetch_assoc() : [];
    $stmt_match->close();

    $conn->close();

    // จัดการรูปโปรไฟล์
    $upload_path = "";
    $profile_picture = $profile1['profile_pictures'] ?? 'default_profile.jpg';
    $profile_picture_url = file_exists(__DIR__ . '/' . $upload_path . $profile_picture)
        ? $upload_path . $profile_picture
        : $upload_path . 'default_profile.jpg';

    // คำนวณอายุ
    $age = isset($profile1['dob']) && $profile1['dob'] !== null 
        ? (date('Y') - date('Y', strtotime($profile1['dob']))) 
        : 'ไม่ทราบอายุ';

} catch (Exception $e) {
    echo "<p style='color: red; text-align: center;'>เกิดข้อผิดพลาด: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit();
}
?>




<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>โปรไฟล์ผู้ใช้</title>
    <style>
        /* พื้นฐานสำหรับตัวเว็บไซต์ */
body {
    font-family: Arial, sans-serif;
    background-color: #121212;
    color: white;
    margin: 0;
    padding: 0;
    overflow-x: hidden; /* Prevent horizontal scrolling */
}

/* Header */
.header {
    display: flex;
    align-items: center;
    padding: 20px;
    background: linear-gradient(90deg, #FF512F, #F09819);
    color: white;
    font-size: 24px;
    width: 100%;
    justify-content: space-between;
    box-sizing: border-box;
    border-bottom: 3px solid #FFA726;
}

/* โปรไฟล์ไอคอนใน Header */
.profile-icon {
    display: flex;
    align-items: center;
    gap: 15px;
}

.profile-icon img {
    width: 80px;
    height: 80px;
    border-radius: 0; /* เอาขอบวงกลมออก */
    object-fit: cover;
}

/* ชื่อผู้ใช้ */
.name {
    font-size: 28px;
    font-weight: bold;
    text-shadow: 1px 1px 2px #333;
}

/* Sidebar */
.sidebar {
    width: 350px;
    background-color: #1c1c1e;
    padding: 25px;
    display: flex;
    flex-direction: column;
    box-sizing: border-box;
    border-right: 2px solid #333;
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    height: 100vh; /* ให้ Sidebar สูงเต็มหน้าจอ */
    overflow-y: auto; /* Enable scrolling for sidebar */
    scrollbar-color: #000 #121212;
    scrollbar-width: thin;
}

.sidebar-item {
    display: flex;
    align-items: center;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 8px;
    background-color: #2b2b2d;
    color: #ddd;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s;
}

.sidebar-item:hover {
    background-color: #333;
    color: #fff;
    transform: translateX(5px);
}

.sidebar-icon {
    margin-right: 10px;
}

/* Content Container */
.profile-container {
    flex-grow: 1;
    padding: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    margin-left: 370px; /* Account for the fixed sidebar */
    height: 100vh; /* Ensure the profile container stays full height */
    overflow-y: auto; /* Allow scrolling in profile container */
}

/* Profile Box */
.profile-box {
    background-color: #242424;
    border-radius: 15px;
    padding: 50px;
    max-width: 600px;
    width: 100%;
    text-align: center;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
    overflow: hidden; /* Prevent scrolling within the profile box */
}

.profile-picture {
    max-width: 300px; /* Smaller profile picture */
    width: 100%;
    border: 5px solid #FFA726;
    border-radius: 15px;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
    margin-bottom: 30px;
}

.profile-name {
    font-size: 30px; /* Smaller font for name */
    font-weight: bold;
    margin-bottom: 16px;
    color: #FFA726;
}

.profile-age {
    font-size: 22px; /* Smaller font for age */
    color: #B3B3B3;
    margin-bottom: 35px;
}

.edit-button {
    background-color: #FF5A60;
    color: white;
    border: none;
    padding: 20px;
    border-radius: 25px;
    cursor: pointer;
    font-size: 22px; /* Slightly smaller font for the button */
    max-width: 400px;
    margin-top: 30px;
    transition: all 0.3s;
}

.edit-button:hover {
    background-color: #e04b54;
    transform: scale(1.05);
    box-shadow: 0 6px 12px rgba(255, 90, 96, 0.4);
}

/* ปรับสไตล์ของไอคอนและข้อความสำหรับแผนการมีครอบครัวใน Sidebar */
#openFamilyPlanMenu {
    display: flex;
    align-items: center;
    padding: 10px;
    cursor: pointer;
    transition: background-color 0.3s;
}

#openFamilyPlanMenu:hover {
    background-color: #444;
}

.sidebar-icon {
    margin-right: 10px;
    font-size: 20px;
}

.sidebar-label {
    font-size: 16px;
}

/* ปรับสไตล์สำหรับเมนู Popup */
.popup-menu {
    display: none;
    position: fixed;
    left: 20%; /* ขยับเมนูไปทางขวามากขึ้น */
    top: 30%; /* ขยับเมนูลงมาจากตำแหน่งเดิม */
    background-color: #333; /* สีพื้นหลังของเมนู */
    color: white; /* สีข้อความในเมนู */
    padding: 15px;
    border-radius: 8px; /* ขอบมน */
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.5); /* เงาของเมนู */
    z-index: 1000; /* ให้เมนูอยู่บนสุด */
    width: 270px; /* กำหนดความกว้าง */
    text-align: center; /* จัดข้อความให้อยู่ตรงกลาง */
    animation: fadeIn 0.3s ease; /* เพิ่มการทำงานของ fade-in */
}

/* เพิ่มฟอนต์ขนาดของหัวข้อในเมนู */
.popup-menu h3 {
    margin-bottom: 15px;
    font-size: 18px; /* ขนาดฟอนต์ของหัวข้อ */
    font-weight: bold;
    color: #FFA726; /* สีของหัวข้อ */
}

/* สไตล์ของลิสต์ในเมนู */
.popup-menu ul {
    list-style: none; /* เอาเครื่องหมายจุดออก */
    padding: 0;
    margin: 0;
}

/* ปรับช่องว่างของรายการในเมนู */
.popup-menu li {
    margin-bottom: 12px; /* ช่องว่างระหว่างรายการ */
}

/* ปรับสไตล์ของปุ่มในเมนู */
.popup-menu button {
    width: 100%; /* ให้ปุ่มเต็มความกว้าง */
    padding: 12px;
    background-color: #444; /* สีพื้นหลังของปุ่ม */
    color: white; /* สีข้อความในปุ่ม */
    border: none;
    border-radius: 8px; /* ขอบมนของปุ่ม */
    font-size: 16px; /* ขนาดฟอนต์ในปุ่ม */
    cursor: pointer; /* เปลี่ยนเคอร์เซอร์เมื่อวางเมาส์ */
    transition: all 0.3s ease; /* เพิ่มการเคลื่อนไหวของปุ่ม */
    margin-bottom: 10px; /* เพิ่มช่องว่างระหว่างปุ่ม */
}

/* เพิ่มสีของปุ่มเมื่อ hover */
.popup-menu button:hover {
    background-color: #ffa726; /* สีพื้นหลังเมื่อ hover */
    color: black; /* สีข้อความเมื่อ hover */
    transform: scale(1.05); /* ทำให้ปุ่มขยายเมื่อ hover */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* เพิ่มเงาให้กับปุ่มเมื่อ hover */
}

/* เอฟเฟกต์ Fade-in */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px); /* ขยับเมนูขึ้นจากด้านล่าง */
    }
    to {
        opacity: 1;
        transform: translateY(0); /* กลับสู่ตำแหน่งเดิม */
    }
}

/* เพิ่มสไตล์สำหรับ scrollbar */
::-webkit-scrollbar {
    width: 8px; /* ขนาดของแถบเลื่อน */
}

::-webkit-scrollbar-track {
    background: #121212; /* สีพื้นหลังของแทร็กแถบเลื่อน */
}

::-webkit-scrollbar-thumb {
    background: #444; /* สีของแถบเลื่อน */
}

::-webkit-scrollbar-thumb:hover {
    background: #ffa726; /* สีของแถบเลื่อนเมื่อ hover */
}


/* เพิ่มแอนิเมชั่น Fade-in */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ปรับสไตล์ของแถบเลื่อน (Scrollbar) */
::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: #121212;
}

::-webkit-scrollbar-thumb {
    background: #444;
}

::-webkit-scrollbar-thumb:hover {
    background: #ffa726;
}

/* เอฟเฟกต์ Fade-in */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* เพิ่มสไตล์สำหรับ scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #121212;
}

::-webkit-scrollbar-thumb {
    background: #444;
}

::-webkit-scrollbar-thumb:hover {
    background: #ffa726;
}

/* เพิ่มแอนิเมชั่น Fade-in */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ปรับสไตล์ของแถบเลื่อน (Scrollbar) */
::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: #121212;
}

::-webkit-scrollbar-thumb {
    background: #333;
}

::-webkit-scrollbar-thumb:hover {
    background: #ffa726;
}

/* Fade-in animation for the popup */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Scrollbar Style */
::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: #121212;
}

::-webkit-scrollbar-thumb {
    background: #000;
}

::-webkit-scrollbar-thumb:hover {
    background: #333;
}  

/* การตั้งค่าการค้นหา */
.search-settings {
    background-color: #1f1f1f;
    border-radius: 12px;
    padding: 20px;
    margin-top: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    color: #ffffff;
}

.search-settings h2 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #ffa726;
    text-align: center;
    font-weight: bold;
}

.setting-item {
    margin-bottom: 20px;
}

.setting-item label {
    display: block;
    margin-bottom: 8px;
    font-size: 16px;
    color: #b3b3b3;
}

.setting-slider {
    display: flex;
    align-items: center;
    gap: 10px;
}

input[type="range"] {
    -webkit-appearance: none;
    appearance: none;
    width: 100%;
    height: 6px;
    background: #444;
    border-radius: 5px;
    outline: none;
    transition: background 0.3s;
}

input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 20px;
    height: 20px;
    background: #ffa726;
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.3s;
}

input[type="range"]::-webkit-slider-thumb:hover {
    background: #ff8c00;
}

input[type="checkbox"] {
    accent-color: #ffa726;
    transform: scale(1.2);
}

.setting-item select {
    width: 100%;
    padding: 8px 10px;
    font-size: 16px;
    color: #ffffff;
    background-color: #333333;
    border: 1px solid #444444;
    border-radius: 8px;
    outline: none;
    transition: border 0.3s;
}

.setting-item select:hover {
    border-color: #ffa726;
}

.setting-item span {
    font-size: 14px;
    color: #b3b3b3;
}

.setting-slider span {
    font-size: 16px;
    color: #ffa726;
    font-weight: bold;
}

.selected-display {
    margin-top: 20px;
    font-size: 20px;
    color: #FFA726;
}
/* เพิ่ม margin ให้กับ .setting-item */
.setting-item {
    margin-bottom: 30px; /* ปรับระยะห่างระหว่างแต่ละรายการ */
}

/* เพิ่ม margin ให้กับ .sidebar-item */
.sidebar-item {
    margin-bottom: 20px; /* ปรับระยะห่างระหว่างเมนู */
}
.result-display {
    margin-top: 8px;
    font-size: 16px;
    color: #ffa726;
    text-align: left;
}
/* สไตล์หน้าต่างแก้ไข */
.modal {
    display: none; /* ซ่อนเริ่มต้น */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 10px;
    width: 80%;
    max-width: 600px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #ddd;
    padding-bottom: 10px;
}

.modal-header h2 {
    margin: 0;
    font-size: 20px;
}

.modal-header .close {
    font-size: 24px;
    cursor: pointer;
}

.modal-body {
    padding: 20px 0;
}

.image-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.image-item {
    position: relative;
    width: 100px;
    height: 100px;
}

.image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #ddd;
}

.image-item .remove-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    background: red;
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 12px;
    cursor: pointer;
}

.add-image {
    width: 100px;
    height: 100px;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 2px dashed #ddd;
    border-radius: 8px;
    color: #888;
    cursor: pointer;
    transition: border-color 0.3s ease, color 0.3s ease;
}

.add-image:hover {
    border-color: #ff5a60;
    color: #ff5a60;
}

.modal-footer {
    text-align: right;
    margin-top: 10px;
}

.save-button {
    background: #ff5a60;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    box-shadow: 0 4px 10px rgba(255, 90, 96, 0.3);
    transition: all 0.3s ease;
}

.save-button:hover {
    background: #e04854;
}


    </style>
</head>
<body>
<div class="main-container">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="header">
            <div class="profile-icon">
                <img src="<?= htmlspecialchars($profile_picture_url) ?>" alt="User Profile Image" class="profile-image">
            </div>
            <div class="name">โปรไฟล์ผู้ใช้</div>
        </div>
        <h2>การตั้งค่าบัญชี</h2>
    <!-- Add scroller class to enable scrolling in the sidebar -->
    <div class="scroller">
        <div class="sidebar-item">อีเมล: <?= htmlspecialchars($profile1['user_email'] ?? 'ไม่มีข้อมูล') ?></div>
        <div class="sidebar-item">ชื่อผู้ใช้: <?= htmlspecialchars($profile1['username'] ?? 'ไม่มีข้อมูล') ?></div>
        <div class="sidebar-item">อายุ: <?= htmlspecialchars($age) ?> ปี</div>
       

    
<!-- ปุ่ม ราศี -->
<div class="sidebar-item" id="openZodiacBtn">
    <span class="sidebar-icon">♈</span>
    <span class="sidebar-label">ราศี</span>
</div>



<div class="sidebar-item" id="openLanguageMenu">
    <span class="sidebar-icon">💬</span>
    <span class="sidebar-label">เพิ่มภาษา</span>
</div>

<div class="sidebar-item" id="openEducationMenu">
    <span class="sidebar-icon">🎓</span>
    <span class="sidebar-label">การศึกษา</span>
</div>

   <!-- แผนการมีครอบครัว -->
<div class="sidebar-item" id="openFamilyPlanMenu">
    <span class="sidebar-icon">👪</span>
    <span class="sidebar-label">แผนการมีครอบครัว</span>
</div>

<!-- วัคซีน COVID-19 -->
<div class="sidebar-item" id="openVaccineMenu">
    <span class="sidebar-icon">🧩</span>
    <span class="sidebar-label">วัคซีน COVID-19</span>
</div>

<div class="sidebar-item" id="openLoveExpressionMenu">
    <span class="sidebar-icon">💖</span>
    <span class="sidebar-label">การแสดงความรัก</span>
</div>

<div class="sidebar-item" id="openBloodTypeMenu">
    <span class="sidebar-icon">💧</span>
    <span class="sidebar-label">กรุ๊ปเลือด</span>
</div>

<div class="sidebar-item" id="openPetMenu">
    <span class="sidebar-icon">🐶</span>
    <span class="sidebar-label">สัตว์เลี้ยง</span>
</div>

<div class="sidebar-item" id="openDrinkMenu">
    <span class="sidebar-icon">🍇</span>
    <span class="sidebar-label">การดื่ม</span>
</div>

<div class="sidebar-item" id="openExerciseMenu">
    <span class="sidebar-icon">🎉</span>
    <span class="sidebar-label">การออกกำลังกาย</span>
</div>

<div class="search-settings">
    <h2>การตั้งค่าการค้นหา</h2>

    <div class="setting-item">
    <label>โลเคชั่น</label>
    <div class="setting-slider">
        <input type="range" min="0" max="100" value="0" id="locationRange" oninput="updateLocationValue()">
        <span id="locationValue">0 กม.</span> <!-- แสดงค่าเริ่มต้นเป็น 0 กม. -->
    </div>
</div>

    <div class="setting-item">
        <label>
            <span>แสดงเฉพาะคนที่อยู่ในช่วงนี้เท่านั้น</span>
            <input type="checkbox" id="showNearbyOnly">
        </label>
    </div>

    <div class="setting-item">
        <label>สนใจ</label>
        <select id="interest">
            <option value="ผู้หญิง">ผู้หญิง</option>
            <option value="ผู้ชาย">ผู้ชาย</option>
            <option value="ทุกคน">ทุกคน</option>
        </select>
    </div>

    <div class="setting-item">
    <label>ตั้งค่าอายุ</label>
    <div class="setting-slider">
        <input type="range" min="0" max="60" value="0" id="ageRange" oninput="updateAgeValue()">
        <span id="ageValue">0</span> <!-- แสดงค่าเริ่มต้นเป็น 0 -->
    </div>
</div>

    <div class="setting-item">
        <label>
            <span>แสดงเฉพาะคนที่อยู่ในช่วงนี้เท่านั้น</span>
            <input type="checkbox" id="ageNearbyOnly">
        </label>
    </div>

    <div class="setting-item">
        <label>
            <span>ไปท่องโลก</span>
            <input type="checkbox" id="travelWorld">
        </label>
    </div>
</div>

<!-- ปุ่มบันทึก -->
<div style="text-align: center; margin-top: 20px;">
<div class="sidebar-item" id="saveButton" style="cursor: pointer; text-align: center;">💾 บันทึกข้อมูล</div>

</div>

        <div class="sidebar-item" onclick="location.href='index.html'" style="cursor: pointer;">🔒 ออกจากระบบ</div>
    </div>



</div>
   <!-- Popup Menus -->



<div id="zodiacMenu" class="popup-menu">
    <h3>ราศีของคุณคือ?</h3>
    <ul>
        <li><button>♈ เมษ</button></li>
        <li><button>♉ พฤษภ</button></li>
        <li><button>♊ เมถุน</button></li>
        <li><button>♋ กรกฎ</button></li>
        <li><button>♌ สิงห์</button></li>
        <li><button>♍ กันย์</button></li>
        <li><button>♎ ตุลย์</button></li>
        <li><button>♏ พิจิก</button></li>
        <li><button>♐ ธนู</button></li>
        <li><button>♑ มังกร</button></li>
        <li><button>♒ กุมภ์</button></li>
        <li><button>♓ มีน</button></li>
    </ul>
</div>


<div id="languageMenu" class="popup-menu">
    <h3>เพิ่มภาษาที่คุณสนใจ:</h3>
    <ul>
        <li><button>อังกฤษ</button></li>
        <li><button>ญี่ปุ่น</button></li>
        <li><button>ฝรั่งเศส</button></li>
        <li><button>สเปน</button></li>
        <li><button>จีน</button></li>
        <li><button>อื่น ๆ</button></li>
    </ul>
</div>

<!-- เพิ่มเมนูการศึกษา -->
<div id="educationMenu" class="popup-menu">
    <h3>เลือกข้อมูลการศึกษา</h3>
    <ul>
       
        <li><button>🏫 สายสามัญ</button></li>
        <li><button>🛠️ สายอาชีพ</button></li>
        <li><button>📜 การศึกษานอกระบบ</button></li>
        <li><button>💻 การศึกษาทางไกล</button></li>
        <li><button>✅ สำเร็จการศึกษาแล้ว</button></li>
    </ul>
</div>
<div id="familyPlanMenu" class="popup-menu">
    <h3>คุณมีแผนการอะไรเกี่ยวกับครอบครัว?</h3>
    <ul>
        <li><button>มีลูกคนแรก 👶</button></li>
        <li><button>ขยายครอบครัว 👨‍👩‍👧‍👦</button></li>
        <li><button>ยังไม่แน่ใจ 🤔</button></li>
        <li><button>ไม่มีแผนการตอนนี้ ❌</button></li>
    </ul>
</div>
<div id="vaccineMenu" class="popup-menu">
    <h3>วัคซีน COVID-19 ของคุณ:</h3>
    <ul>
        <li><button>ได้รับวัคซีนครบ 2 เข็ม</button></li>
        <li><button>ได้รับวัคซีนเข็มเดียว</button></li>
        <li><button>ยังไม่ได้รับวัคซีน</button></li>
        <li><button>ต้องการข้อมูลเพิ่มเติม</button></li>
    </ul>
</div>
<div id="loveExpressionMenu" class="popup-menu">
    <h3>การแสดงความรัก</h3>
    <ul>
    <li><button>💬 ภาษาแห่งความรัก</button></li>
        <li><button>❤️ วิธีการแสดงความรักในสถานการณ์ต่าง ๆ</button></li>
        <li><button>🌹 สร้างความโรแมนติก</button></li>
        <li><button>👂 การฟังและเข้าใจความต้องการ</button></li>
        <li><button>💑 เคล็ดลับการดูแลความสัมพันธ์</button></li>
        <li><button>🤫 การแสดงความรักแบบไม่ใช้คำพูด</button></li>
        <li><button>🌍 การแสดงความรักในวัฒนธรรมต่าง ๆ</button></li>
        <li><button>📝 แบบทดสอบและคำแนะนำ</button></li>
    </ul>
</div>
<div id="bloodTypeMenu" class="popup-menu">
    <h3>เลือกกรุ๊ปเลือดของคุณ</h3>
    <ul>
        <li><button>🅰️ กรุ๊ปเลือด A</button></li>
        <li><button>🅱️ กรุ๊ปเลือด B</button></li>
        <li><button>🅾️ กรุ๊ปเลือด O</button></li>
        <li><button>🆎 กรุ๊ปเลือด AB</button></li>
    </ul>
</div>
<div id="petMenu" class="popup-menu">
    <h3>สัตว์เลี้ยงของคุณคือ?</h3>
    <ul>
    <li><button>🐕 สุนัข</button></li>
    <li><button>😸 แมว</button></li>
    <li><button>🐇 กระต่าย</button></li>
    <li><button>🐦 นก</button></li>
    <li><button>❓ อื่นๆ</button></li>
    </ul>
</div>
<div id="drinkMenu" class="popup-menu">
    <h3>เครื่องดื่มที่คุณชอบ?</h3>
    <ul>
    <li><button>🍹 น้ำผลไม้</button></li>
    <li><button>☕ กาแฟ</button></li>
    <li><button>🍵 ชา</button></li>
    <li><button>💧 น้ำ</button></li>
    <li><button>❓ อื่นๆ</button></li>
    </ul>
</div>
<div id="exerciseMenu" class="popup-menu">
    <h3>คุณชอบการออกกำลังกายแบบไหน?</h3>
    <ul>
    <li><button>🏃‍♂️ วิ่ง</button></li>
    <li><button>🚴‍♀️ ปั่นจักรยาน</button></li>
    <li><button>🧘‍♀️ โยคะ</button></li>
    <li><button>🏋️‍♀️ ฟิตเนส</button></li>
    <li><button>❓ อื่นๆ</button></li>
    </ul>
</div>

<script>

// ฟังก์ชันสำหรับตั้งค่า Popup Menu
function setupPopupMenu(menuId, buttonId) {
    const menu = document.getElementById(menuId);
    const button = document.getElementById(buttonId);

    if (!menu || !button) return;

    button.addEventListener('click', (e) => {
        e.stopPropagation(); // ป้องกันเหตุการณ์คลิกนอกเมนู
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });

    window.addEventListener('click', (event) => {
        if (!menu.contains(event.target) && event.target !== button) {
            menu.style.display = 'none';
        }
    });
}

// ตั้งค่า Popup Menu
setupPopupMenu('datingGoalMenu', 'openMenuBtn');
setupPopupMenu('zodiacMenu', 'openZodiacBtn');
setupPopupMenu('languageMenu', 'openLanguageMenu');
setupPopupMenu('educationMenu', 'openEducationMenu'); 
setupPopupMenu('familyPlanMenu', 'openFamilyPlanMenu');
setupPopupMenu('vaccineMenu', 'openVaccineMenu');
setupPopupMenu('loveExpressionMenu', 'openLoveExpressionMenu');
setupPopupMenu('bloodTypeMenu', 'openBloodTypeMenu');
setupPopupMenu('petMenu', 'openPetMenu');
setupPopupMenu('drinkMenu', 'openDrinkMenu');
setupPopupMenu('exerciseMenu', 'openExerciseMenu');

</script>

<!-- Content Area -->
<div class="profile-container">
<style>
        .profile-box {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
            font-family: Arial, sans-serif;
        }

        .profile-box h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .profile-pictures-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .picture-slot {
            position: relative;
            width: 100px;
            height: 100px;
            border: 2px dashed #ccc;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .picture-slot img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .picture-slot .remove-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 14px;
            cursor: pointer;
        }

        .add-picture {
            font-size: 30px;
            color: #aaa;
            cursor: pointer;
        }

        .save-button {
            background-color: #FF6B6B;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
        }

        
    </style>
</head>
<body>
<div class="profile-box">
    <h1>โปรไฟล์ผู้ใช้</h1>
        <div class="edit-username-container">
            <label for="newUsername">ชื่อผู้ใช้:</label>
            <input type="text" id="newUsername" class="username-input" value="<?= htmlspecialchars($profile1['username'] ?? '') ?>">
            <button class="save-username-btn" onclick="saveNewUsername()">
                <i class="fas fa-save"></i> บันทึก
            </button>
        </div>
    <div class="image-upload-container">
    <!-- ช่องแรกสำหรับอัปโหลดรูปโปรไฟล์ -->
    <div class="image-slot" id="imageSlot1">
        <img src="<?= htmlspecialchars($profile_picture_url) ?>" alt="รูปโปรไฟล์" class="uploaded-image" id="uploadedImage1" style="display: block;">
        <label for="fileInput1" class="add-image-label">+</label>
        <input type="file" id="fileInput1" class="file-input" accept="image/*" style="display: none;">
        <button type="button" class="remove-image" id="removeImage1" style="display: none;">x</button>
    </div>

    <!-- ช่องอื่นๆ ใช้ PHP Loop -->
    <?php for ($i = 2; $i <= 6; $i++): ?>
        <div class="image-slot" id="imageSlot<?= $i ?>">
            <img src="default-profile.jpg" alt="รูปโปรไฟล์" class="uploaded-image" id="uploadedImage<?= $i ?>" style="display: none;">
            <label for="fileInput<?= $i ?>" class="add-image-label">+</label>
            <input type="file" id="fileInput<?= $i ?>" class="file-input" accept="image/*" style="display: block;">
            <button type="button" class="remove-image" id="removeImage<?= $i ?>" style="display: none;">x</button>
        </div>
    <?php endfor; ?>


   

<!-- ปุ่มแก้ไขข้อมูล -->
<button class="edit-button" onclick="openEditPage()">บันทึกข้อมูล</button>
</div>
    <!-- ใส่กล่องข้อมูลโปรไฟล์ใต้ภาพ -->
    <div class="profile-info-container">
        <h2>ข้อมูลโปรไฟล์ของคุณ</h2>
        <ul class="profile-info-list">
            <li>📍 <strong>โลเคชั่น:</strong> <?= htmlspecialchars($match_data['location'] ?? 'ไม่ระบุ') ?> กม.</li>
            <li>👶 <strong>ช่วงอายุที่สนใจ:</strong> <?= htmlspecialchars($match_data['age_range'] ?? 'ไม่ระบุ') ?></li>
            <li>♈ <strong>ราศี:</strong> <?= htmlspecialchars($match_data['zodiac'] ?? 'ไม่ระบุ') ?></li>
            <li>💬 <strong>ภาษา:</strong> <?= htmlspecialchars($match_data['languages'] ?? 'ไม่ระบุ') ?></li>
            <li>🎓 <strong>การศึกษา:</strong> <?= htmlspecialchars($match_data['education'] ?? 'ไม่ระบุ') ?></li>
            <li>👪 <strong>แผนการมีครอบครัว:</strong> <?= htmlspecialchars($match_data['family_plan'] ?? 'ไม่ระบุ') ?></li>
            <li>🧩 <strong>วัคซีน COVID-19:</strong> <?= htmlspecialchars($match_data['covid_vaccine'] ?? 'ไม่ระบุ') ?></li>
            <li>💖 <strong>การแสดงความรัก:</strong> <?= htmlspecialchars($match_data['love_expression'] ?? 'ไม่ระบุ') ?></li>
            <li>💧 <strong>กรุ๊ปเลือด:</strong> <?= htmlspecialchars($match_data['blood_type'] ?? 'ไม่ระบุ') ?></li>
            <li>🐶 <strong>สัตว์เลี้ยง:</strong> <?= htmlspecialchars($match_data['pet'] ?? 'ไม่ระบุ') ?></li>
            <li>🍇 <strong>การดื่ม:</strong> <?= htmlspecialchars($match_data['drink'] ?? 'ไม่ระบุ') ?></li>
            <li>🎉 <strong>การออกกำลังกาย:</strong> <?= htmlspecialchars($match_data['exercise'] ?? 'ไม่ระบุ') ?></li>
        </ul>
    </div>
<style>/* ---------- กล่องโปรไฟล์ (ปรับขนาดและเงา) ---------- */
.profile-box {
    background: #222;
    border-radius: 15px;
    padding: 30px;
    max-width: 700px;
    width: 100%;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
    margin: auto;
}

/* ---------- หัวข้อโปรไฟล์ ---------- */
h1 {
    color: #ffa726;
    font-size: 26px;
    font-weight: bold;
    margin-bottom: 20px;
}

/* ---------- ฟอร์มแก้ไขชื่อ ---------- */
.edit-username-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.edit-username-container label {
    color: white;
    font-size: 18px;
    font-weight: bold;
}

.username-input {
    padding: 8px;
    font-size: 16px;
    border: 2px solid #ffa726;
    border-radius: 8px;
    background-color: #333;
    color: white;
    width: 60%;
    text-align: center;
}

/* ---------- ปุ่มบันทึกชื่อ ---------- */
.save-username-btn {
    background: #ffa726;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s;
}

.save-username-btn:hover {
    background: #ff8c00;
}

/* ---------- กล่องอัปโหลดรูป ---------- */
.image-upload-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    padding: 20px;
    justify-content: center;
    background: #1e1e1e;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
    max-width: 420px;
    margin: auto;
}

/* ---------- ช่องใส่รูป ---------- */
.image-slot {
    position: relative;
    width: 120px;
    height: 120px;
    border: 2px dashed #555;
    border-radius: 12px;
    background: #2a2a2a;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.3s ease, border-color 0.3s ease;
}

.image-slot:hover {
    transform: scale(1.05);
    border-color: #888;
}

/* ---------- รูปที่อัปโหลด ---------- */
.uploaded-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
}

/* ---------- ปุ่มบันทึกข้อมูล ---------- */
.save-button {
    background: linear-gradient(90deg, #ff6b6b, #ff4a4a);
    color: #fff;
    font-size: 1.1em;
    border: none;
    border-radius: 25px;
    padding: 14px 30px;
    cursor: pointer;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    transition: transform 0.3s ease, box-shadow 0.3s ease;

    /* ✅ ปรับตำแหน่งให้ไปทางขวา */
    display: flex;
    justify-content: flex-end;
    align-items: center;
    width: fit-content;
    margin: 20px auto 0 auto;
}

.save-button:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
}

/* ---------- กล่องข้อมูลโปรไฟล์ ---------- */
.profile-info-container {
    background-color: #242424;
    border-radius: 15px;
    padding: 25px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    color: white;
    text-align: left;
    font-family: 'Arial', sans-serif;
    margin: 30px auto;
}

/* ---------- หัวข้อข้อมูลโปรไฟล์ ---------- */
.profile-info-container h2 {
    font-size: 22px;
    color: #ffa726;
    text-align: center;
    margin-bottom: 15px;
    font-weight: bold;
    text-transform: uppercase;
}

/* ---------- รายการข้อมูล ---------- */
.profile-info-list {
    list-style: none;
    padding: 0;
}

/* ---------- สไตล์ของรายการแต่ละข้อ ---------- */
.profile-info-list li {
    font-size: 16px;
    margin-bottom: 10px;
    color: #ddd;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    background: rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    transition: background 0.3s, transform 0.2s;
}

.profile-info-list li:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: scale(1.02);
}

/* ---------- สไตล์ตัวอักษรที่เป็นหัวข้อ (strong) ---------- */
.profile-info-list strong {
    color: #ffa726;
    font-weight: bold;
}

/* ---------- Responsive สำหรับมือถือ ---------- */
@media (max-width: 768px) {
    .profile-box {
        max-width: 95%;
    }

    .image-upload-container {
        max-width: 100%;
        grid-template-columns: repeat(2, 1fr);
    }

    .profile-info-container {
        max-width: 100%;
        padding: 20px;
    }

    .profile-info-list li {
        font-size: 14px;
        padding: 8px 12px;
    }

    .save-button {
        width: 100%;
        justify-content: center;
    }
}


</style>



<script>

function enableEditUsername() {
    document.getElementById("usernameDisplay").style.display = "none";
    document.querySelector(".edit-username-btn").style.display = "none";
    document.getElementById("editUsernameForm").style.display = "block";
}

function cancelEditUsername() {
    document.getElementById("editUsernameForm").style.display = "none";
    document.getElementById("usernameDisplay").style.display = "inline";
    document.querySelector(".edit-username-btn").style.display = "inline";
}

function saveNewUsername() {
    const newName = document.getElementById("newUsername").value.trim();
    console.log("📌 กำลังส่งชื่อใหม่ไปที่เซิร์ฟเวอร์:", newName);

    if (newName === "") {
        alert("กรุณากรอกชื่อใหม่!");
        return;
    }

    fetch("update_username.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ username: newName }) // ✅ ตรวจสอบว่าคีย์ตรงกัน
    })
    .then(response => response.json())
    .then(data => {
        console.log("📩 คำตอบจากเซิร์ฟเวอร์:", data);
        if (data.success) {
            alert("✅ " + data.message);
            location.reload(); // รีเฟรชหน้าเพื่อแสดงชื่อใหม่
        } else {
            alert("❌ " + data.message);
        }
    })
    .catch(error => {
        console.error("🚨 เกิดข้อผิดพลาด:", error);
    });
}




function openEditPage() {
    // เปลี่ยนเส้นทางไปยังหน้าใหม่
    window.location.href = "match.php"; // ใส่ URL ของหน้าที่ต้องการ
}
</script>

    </div>
</div>

<style>


/* ส่วนหัวโปรไฟล์ */
.profile-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 20px;
    font-weight: bold;
}

.profile-header .profile-name {
    color: #333;
}

.profile-header .profile-age {
    color: #888;
}

/* ส่วนของข้อมูล */
.profile-section {
    background-color: #f5f5f5;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.profile-section h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
    font-weight: bold;
    color: #555;
}

.profile-section p {
    margin: 5px 0;
    font-size: 14px;
    color: #666;
    display: flex;
    align-items: center;
    gap: 10px;
}

.profile-section i {
    color: #ff5a60;
}

/* ปุ่มแก้ไข */
.edit-button {
    background-color: #ff5a60;
    color: white;
    border: none;
    border-radius: 20px;
    padding: 15px;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s;
}

.edit-button:hover {
    background-color: #e04854;
    transform: scale(1.05);
}

</style>
<!-- หน้าต่างแก้ไข -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Profile Pictures</h2>
            <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="image-grid" id="imageGrid">
                <!-- ที่ว่างสำหรับรูปภาพจะถูกเพิ่มผ่าน JavaScript -->
            </div>
        </div>
        <div class="modal-footer">
            <button class="save-button" onclick="saveChanges()">Save</button>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // รายการรูปภาพเริ่มต้น
    const existingImages = [
        "uploads/image1.jpg",
        "uploads/image2.jpg"
    ];
    
    function renderImages() {
        const imageGrid = document.getElementById("imageGrid");
        imageGrid.innerHTML = ""; // ล้างก่อนโหลดใหม่

        existingImages.forEach((imageSrc, index) => {
            const imageItem = document.createElement("div");
            imageItem.classList.add("image-item");

            imageItem.innerHTML = `
                <img src="${imageSrc}" alt="Image ${index + 1}">
                <button class="remove-btn" onclick="removeImage(${index})">&#10005;</button>
            `;
            imageGrid.appendChild(imageItem);
        });

        // เพิ่มปุ่มอัปโหลดถ้ายังไม่ครบ 6 รูป
        if (existingImages.length < 6) {
            const addImage = document.createElement("div");
            addImage.classList.add("add-image");
            addImage.innerHTML = `<span>+</span>`;
            addImage.onclick = uploadNewImage;
            imageGrid.appendChild(addImage);
        }
    }

    window.uploadNewImage = function () {
        if (existingImages.length >= 6) {
            alert("สามารถอัปโหลดได้สูงสุด 6 รูปเท่านั้น");
            return;
        }
        
        // จำลองการอัปโหลด (สามารถแทนที่ด้วยฟังก์ชันอัปโหลดจริง)
        const newImageSrc = `uploads/image${existingImages.length + 1}.jpg`;
        existingImages.push(newImageSrc);
        renderImages();
    };

    window.removeImage = function (index) {
        existingImages.splice(index, 1); // ลบรูปจากอาร์เรย์
        renderImages();
    };

    renderImages();
});
</script>

    </div>
</div>
<script>

document.getElementById("saveButton").addEventListener("click", function () {
    const goal = document.querySelector("#goalDisplay")?.textContent || "ไม่มีข้อมูล";
    const zodiac = document.querySelector("#zodiacDisplay")?.textContent || "ไม่มีข้อมูล";
    const loveExpression = document.querySelector("#loveExpressionDisplay")?.textContent || "ไม่มีข้อมูล";
    const bloodType = document.querySelector("#bloodTypeDisplay")?.textContent || "ไม่มีข้อมูล";
    const pet = document.querySelector("#petDisplay")?.textContent || "ไม่มีข้อมูล";
    const drink = document.querySelector("#drinkDisplay")?.textContent || "ไม่มีข้อมูล";
    const exercise = document.querySelector("#exerciseDisplay")?.textContent || "ไม่มีข้อมูล";

    fetch("save_data.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
            goal,
            zodiac,
            love_expression: loveExpression,
            blood_type: bloodType,
            pet,
            drink,
            exercise,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert("บันทึกข้อมูลสำเร็จ!");
                location.reload(); // โหลดหน้าใหม่เพื่อแสดงผลข้อมูลล่าสุด
            } else {
                alert("เกิดข้อผิดพลาด: " + data.message);
            }
        })
        .catch((error) => {
            console.error("เกิดข้อผิดพลาด:", error);
        });
});



function openEditModal() {
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function removeImage(imageId) {
    alert('Remove image ID: ' + imageId);
    // ที่นี่สามารถส่งคำขอ AJAX เพื่อลบรูปออกจากฐานข้อมูล
}

function uploadNewImage() {
    alert('Open file picker to upload a new image');
    // ที่นี่สามารถเปิดตัวเลือกไฟล์เพื่ออัปโหลดรูปภาพใหม่
}

function saveChanges() {
    alert('Saving changes...');
    // ที่นี่สามารถส่งคำขอ AJAX เพื่อบันทึกการเปลี่ยนแปลง
    closeEditModal();
}


function updateLocationValue() {
    const locationRange = document.getElementById('locationRange');
    const locationValue = document.getElementById('locationValue');
    locationValue.textContent = `${locationRange.value} กม.`;
}

function updateAgeValue() {
    const ageRange = document.getElementById('ageRange');
    const ageValue = document.getElementById('ageValue');
    ageValue.textContent = `${ageRange.value}`;
}

/**
 * ฟังก์ชันสำหรับบันทึกค่าที่เลือกจากเมนู
 * @param {string} value - ค่าที่ผู้ใช้เลือกจากเมนู
 */
function saveSelectedOption(value) {
    // แสดงค่าที่เลือกใน console
    console.log("ค่าที่เลือก: ", value);

    // ส่งค่าที่เลือกไปยังเซิร์ฟเวอร์ผ่าน AJAX (ตัวอย่าง)
    fetch('save_option.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ selectedOption: value }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert("บันทึกข้อมูลสำเร็จ!");
                // อัปเดตผลลัพธ์ที่แสดง
                document.getElementById("selectedDisplay").textContent = `คุณเลือก: ${value}`;
            } else {
                alert("เกิดข้อผิดพลาดในการบันทึกข้อมูล");
            }
        })
        .catch((error) => {
            console.error("เกิดข้อผิดพลาด: ", error);
        });
}

function handleSelection(button) {
    const selectedValue = button.textContent.trim(); // ดึงค่าจากปุ่ม
    const display = document.getElementById("selectedDisplay"); // ส่วนแสดงผล

    // แสดงข้อความที่เลือก
    display.textContent = `คุณเลือก: ${selectedValue}`;

    // ซ่อนเมนู Popup
    const popupMenu = button.closest('.popup-menu'); // ค้นหาเมนูที่ปุ่มนี้อยู่
    if (popupMenu) {
        popupMenu.style.display = 'none'; // ซ่อนเมนู
    }

    // ตรวจสอบว่ามีค่า selectedValue ก่อนส่งข้อมูล
    if (selectedValue) {
        // ส่งข้อมูลไปยังเซิร์ฟเวอร์
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `selected_option=${encodeURIComponent(selectedValue)}`,
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then((data) => {
                if (data.success) {
                    alert(data.message); // แสดงข้อความสำเร็จ
                } else {
                    alert(`ข้อผิดพลาด: ${data.message}`); // แสดงข้อความข้อผิดพลาด
                }
            })
            .catch((error) => {
                console.error("เกิดข้อผิดพลาด:", error);
                alert("เกิดข้อผิดพลาดในการส่งข้อมูล");
            });
    } else {
        console.error("ไม่มีค่า selectedValue");
    }
}

// เพิ่ม Event Listener ให้กับปุ่มใน Popup Menu
document.querySelectorAll(".popup-menu button").forEach((button) => {
    button.addEventListener("click", () => handleSelection(button));
});








// ฟังก์ชันอัปเดตข้อความแสดงผลสำหรับหลายตัวเลือก
function updateMultipleDisplay(buttonId, displayId, selectedValue) {
    const button = document.getElementById(buttonId);

    // ตรวจสอบว่ามีข้อความแสดงผลอยู่แล้วหรือไม่
    let existingDisplay = document.getElementById(displayId);
    if (!existingDisplay) {
        // ถ้าไม่มี ให้สร้างข้อความใหม่
        let display = document.createElement("div");
        display.id = displayId;
        display.style.marginTop = "10px";
        display.style.fontSize = "16px";
        display.style.color = "#FFA726";
        display.style.textAlign = "center";
        display.textContent = selectedValue; // เพิ่มค่าที่เลือกครั้งแรก

        // แทรกข้อความใต้ปุ่ม
        button.parentNode.insertBefore(display, button.nextSibling);
    } else {
        // ถ้ามีอยู่แล้ว ตรวจสอบว่าค่าใหม่มีในข้อความหรือไม่
        const currentText = existingDisplay.textContent.split(", ");
        if (!currentText.includes(selectedValue)) {
            currentText.push(selectedValue);
            existingDisplay.textContent = currentText.join(", "); // อัปเดตข้อความใหม่
        }
    }
}

// จัดการคลิกตัวเลือกในเมนู "Dating Goal"
document.querySelectorAll("#datingGoalMenu button").forEach((button) => {
    button.addEventListener("click", function () {
        const selectedValue = this.textContent.trim();
        updateMultipleDisplay("openMenuBtn", "menuDisplay", selectedValue);
        document.getElementById("datingGoalMenu").style.display = "none"; // ซ่อนเมนู
    });
});

// จัดการคลิกตัวเลือกในเมนู "ราศี"
document.querySelectorAll("#zodiacMenu button").forEach((button) => {
    button.addEventListener("click", function () {
        const selectedValue = this.textContent.trim();
        updateMultipleDisplay("openZodiacBtn", "zodiacDisplay", selectedValue);
        document.getElementById("zodiacMenu").style.display = "none"; // ซ่อนเมนู
    });
});

// จัดการคลิกตัวเลือกในเมนู "เพิ่มภาษา"
document.querySelectorAll("#languageMenu button").forEach((button) => {
    button.addEventListener("click", function () {
        const selectedValue = this.textContent.trim();
        updateMultipleDisplay("openLanguageMenu", "languageDisplay", selectedValue);
        document.getElementById("languageMenu").style.display = "none"; // ซ่อนเมนู
    });
});

// จัดการคลิกตัวเลือกในเมนู "การศึกษา"
document.querySelectorAll("#educationMenu button").forEach((button) => {
    button.addEventListener("click", function () {
        const selectedValue = this.textContent.trim();
        updateMultipleDisplay("openEducationMenu", "educationDisplay", selectedValue);
        document.getElementById("educationMenu").style.display = "none"; // ซ่อนเมนู
    });
});

// จัดการคลิกตัวเลือกในเมนู "แผนการมีครอบครัว"
document.querySelectorAll("#familyPlanMenu button").forEach((button) => {
    button.addEventListener("click", function () {
        const selectedValue = this.textContent.trim();
        updateMultipleDisplay("openFamilyPlanMenu", "familyPlanDisplay", selectedValue);
        document.getElementById("familyPlanMenu").style.display = "none"; // ซ่อนเมนู
    });
});

// จัดการคลิกตัวเลือกในเมนู "วัคซีน COVID-19"
document.querySelectorAll("#vaccineMenu button").forEach((button) => {
    button.addEventListener("click", function () {
        const selectedValue = this.textContent.trim();
        updateMultipleDisplay("openVaccineMenu", "vaccineDisplay", selectedValue);
        document.getElementById("vaccineMenu").style.display = "none"; // ซ่อนเมนู
    });
});

// จัดการคลิกตัวเลือกในเมนู "การแสดงความรัก"
document.querySelectorAll("#loveExpressionMenu button").forEach((button) => {
    button.addEventListener("click", function () {
        const selectedValue = this.textContent.trim();
        updateMultipleDisplay("openLoveExpressionMenu", "loveExpressionDisplay", selectedValue);
        document.getElementById("loveExpressionMenu").style.display = "none"; // ซ่อนเมนู
    });
});

// จัดการคลิกตัวเลือกในเมนู "กรุ๊ปเลือด"
document.querySelectorAll("#bloodTypeMenu button").forEach((button) => {
    button.addEventListener("click", function () {
        const selectedValue = this.textContent.trim();
        updateMultipleDisplay("openBloodTypeMenu", "bloodTypeDisplay", selectedValue);
        document.getElementById("bloodTypeMenu").style.display = "none"; // ซ่อนเมนู
    });
});

// จัดการคลิกตัวเลือกในเมนู "สัตว์เลี้ยง"
document.querySelectorAll("#petMenu button").forEach((button) => {
    button.addEventListener("click", function () {
        const selectedValue = this.textContent.trim();
        updateMultipleDisplay("openPetMenu", "petDisplay", selectedValue);
        document.getElementById("petMenu").style.display = "none"; // ซ่อนเมนู
    });
});

// จัดการคลิกตัวเลือกในเมนู "การดื่ม"
document.querySelectorAll("#drinkMenu button").forEach((button) => {
    button.addEventListener("click", function () {
        const selectedValue = this.textContent.trim();
        updateMultipleDisplay("openDrinkMenu", "drinkDisplay", selectedValue);
        document.getElementById("drinkMenu").style.display = "none"; // ซ่อนเมนู
    });
});

// จัดการคลิกตัวเลือกในเมนู "การออกกำลังกาย"
document.querySelectorAll("#exerciseMenu button").forEach((button) => {
    button.addEventListener("click", function () {
        const selectedValue = this.textContent.trim();
        updateMultipleDisplay("openExerciseMenu", "exerciseDisplay", selectedValue);
        document.getElementById("exerciseMenu").style.display = "none"; // ซ่อนเมนู
    });
});

// เพิ่มเมนูอื่น ๆ ด้วยฟังก์ชัน updateMultipleDisplay ได้ตามตัวอย่างข้างต้น





document.getElementById('saveButton').addEventListener('click', function () {
    const formData = new FormData();
    const imageInputs = document.querySelectorAll('.file-input');

    imageInputs.forEach(input => {
        if (input.files[0]) {
            formData.append('profile_images[]', input.files[0]);
        }
    });

    fetch('save_profile.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log("📩 ตอบกลับจากเซิร์ฟเวอร์:", data);
        if (data.success) {
            alert("✅ " + data.message);
            updateProfilePictures(data.files); // เปลี่ยนรูปใน UI
        } else {
            alert("❌ " + data.message);
        }
    })
    .catch(error => console.error("🚨 เกิดข้อผิดพลาด:", error));
});

function updateProfilePictures(imagePaths) {
    const imageContainers = document.querySelectorAll('.uploaded-image');
    imagePaths.forEach((path, index) => {
        if (imageContainers[index]) {
            imageContainers[index].src = path;
            imageContainers[index].style.display = 'block';
        }
    });
}


document.querySelectorAll('.file-input').forEach(input => {
    input.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            const imageSlot = e.target.closest('.image-slot');
            const uploadedImage = imageSlot.querySelector('.uploaded-image');
            const removeButton = imageSlot.querySelector('.remove-image');

            reader.onload = function (event) {
                uploadedImage.src = event.target.result;
                uploadedImage.style.display = 'block';
                removeButton.style.display = 'flex';
                e.target.style.display = 'none'; // ซ่อน input file
            };

            reader.readAsDataURL(file);
        }
    });
});

document.querySelectorAll('.remove-image').forEach(button => {
    button.addEventListener('click', function () {
        const imageSlot = button.closest('.image-slot');
        const uploadedImage = imageSlot.querySelector('.uploaded-image');
        const fileInput = imageSlot.querySelector('.file-input');

        uploadedImage.src = '';
        uploadedImage.style.display = 'none'; // ซ่อนรูปภาพ
        button.style.display = 'none'; // ซ่อนปุ่มลบ
        fileInput.style.display = 'block'; // แสดง input file อีกครั้ง
        fileInput.value = ''; // รีเซ็ตค่า input file
    });
});
    
</script>
</body>
</html>
