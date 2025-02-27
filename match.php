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
    if (empty($_SESSION['user_id']) || !is_numeric($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
        throw new Exception("เกิดข้อผิดพลาด: user_id ไม่ถูกต้อง (ค่า: " . ($_SESSION['user_id'] ?? 'ไม่ได้กำหนด') . ")");
    }

    $user_id = intval($_SESSION['user_id']); // แปลงให้แน่ใจว่าเป็น int

    // ตรวจสอบว่ามีการส่งค่าจากฟอร์มหรือไม่
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $updateFields = [];

        // รับค่าจากฟอร์มและบันทึก
        if (!empty($_POST['goal'])) {
            $updateFields['goal'] = $_POST['goal'];
        }

        if (!empty($_POST['zodiac'])) {
            $updateFields['zodiac'] = $_POST['zodiac'];
        }

        if (!empty($_POST['love_expression'])) {
            $updateFields['love_expression'] = $_POST['love_expression'];
        }

        // ตรวจสอบว่ามี `user_id` อยู่ในตาราง match หรือไม่
        $sql_check = "SELECT COUNT(*) FROM `match` WHERE user_id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $user_id);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();

        if ($count == 0) {
            $sql_insert = "INSERT INTO `match` (user_id, email) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("is", $user_id, $email);
            $stmt_insert->execute();
            
            $stmt_insert->close();
            
        }

        // อัปเดตข้อมูล
        $sql_update = "UPDATE `match` SET goal = ?, zodiac = ?, love_expression = ? WHERE user_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update) {
            $stmt_update->bind_param(
                "sssi",
                $updateFields['goal'] ?? null,
                $updateFields['zodiac'] ?? null,
                $updateFields['love_expression'] ?? null,
                $user_id
            );
            if (!$stmt_update->execute()) {
                throw new Exception("เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . $stmt_update->error);
            }
            $stmt_update->close();
        } else {
            throw new Exception("การเตรียมคำสั่ง SQL ผิดพลาด: " . $conn->error);
        }
    }

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
            <a href="MatchPage.php">
    <img src="<?= htmlspecialchars($profile_picture_url) ?>" alt="User Profile Image" class="profile-image">
</a>

            </div>
            <div class="name">โปรไฟล์ผู้ใช้</div>
        </div>
        <h2>การตั้งค่าบัญชี</h2>
    <!-- Add scroller class to enable scrolling in the sidebar -->
    <div class="scroller">
        <div class="sidebar-item">อีเมล: <?= htmlspecialchars($profile1['user_email'] ?? 'ไม่มีข้อมูล') ?></div>
        <div class="sidebar-item">ชื่อผู้ใช้: <?= htmlspecialchars($profile1['username'] ?? 'ไม่มีข้อมูล') ?></div>
        <div class="sidebar-item">อายุ: <?= htmlspecialchars($age) ?> ปี</div>

    <!-- ปุ่ม Dating Goal -->
   

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
        <li><button><i class="fas fa-book"></i> ประถมศึกษา</button></li>
        
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

<title>โปรไฟล์ผู้ใช้</title>
            <style>
                /* พื้นหลังเว็บไซต์ */
                body {
                    font-family: Arial, sans-serif;
                    background-color: #121212;  
                    color: white;
                    margin: 0;
                    padding: 0;
                }

                /* กล่องโปรไฟล์ (มี Scrollbar) */
                .profile-container {
                    background-color: rgb(24, 24, 24);
                    border-radius: 12px;
                    padding: 15px;
                    text-align: center;
                    width: 350px;
                    max-height: 630px;
                    box-shadow: 0 1px 30px rgb(255, 255, 255);
                    position: fixed;
                    left: 35%;
                    top: 50%;
                    transform: translate(-50%, -50%);
                    overflow-y: auto; /* ให้เลื่อนดูข้อมูลได้ */
                    scrollbar-width: thin;
                    scrollbar-color: #FFA726 #333;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-between;
                }

                /* ปรับ Scrollbar */
                .profile-container::-webkit-scrollbar {
                    width: 8px;
                }

                .profile-container::-webkit-scrollbar-thumb {
                    background: #FFA726;
                    border-radius: 5px;
                }

                .profile-container::-webkit-scrollbar-track {
                    background: rgba(255, 255, 255, 0.1);
                }

            /* ปรับรูปโปรไฟล์ให้เป็นสี่เหลี่ยมและใหญ่ขึ้น */
        .profile-picture {
            width: 300px;  /* ขยายขนาด */
            height: 320px; /* ขยายขนาด */
            border-radius: 20px; /* ให้มุมโค้งเล็กน้อย แต่ยังเป็นกรอบเหลี่ยม */
            border: 6px solid #FFA726; /* เพิ่มเส้นขอบสีส้ม */
            object-fit: cover;
            margin-bottom: 12px;

            /* เพิ่ม Glow แบบ Gradient */
            box-shadow: 
                0 0 10px rgba(255, 165, 0, 0.8), 
                0 0 20px rgba(255, 140, 0, 0.6), 
                0 0 30px rgba(255, 120, 0, 0.4);

            /* เพิ่ม Animation ให้เงาเหลื่อมสั่นเบาๆ */
            animation: glowEffect 2s infinite alternate;
        }

        /* เอฟเฟกต์เงาเหลื่อมให้มีการเปลี่ยนสีแบบนุ่มๆ */
        @keyframes glowEffect {
            0% {
                box-shadow: 
                    0 0 10px rgba(255, 165, 0, 0.8), 
                    0 0 20px rgba(255, 140, 0, 0.6), 
                    0 0 30px rgba(255, 120, 0, 0.4);
            }
            100% {
                box-shadow: 
                    0 0 15px rgba(255, 180, 0, 0.9), 
                    0 0 25px rgba(255, 160, 0, 0.7), 
                    0 0 35px rgba(255, 140, 0, 0.5);
            }
        }

                /* ชื่อโปรไฟล์ */
                .profile-name {
                    font-size: 22px;
                    font-weight: bold;
                    color: #FFA726;
                    margin-bottom: 10px;
                }

                /* ข้อมูลบัญชี */
                .profile-age, .profile-section p {
                    font-size: 18px;
                    color: #B3B3B3;
                    margin-bottom: 8px;
                }

                /* ปุ่มแก้ไข */
                .edit-button {
                    background: linear-gradient(90deg, #FF5A60, #E04854);
                    color: white;
                    border: none;
                    padding: 14px 20px;
                    border-radius: 10px;
                    cursor: pointer;
                    font-size: 18px;
                    font-weight: bold;
                    transition: all 0.3s;
                    margin-top: 10px;
                    display: inline-block;
                    box-shadow: 0 5px 10px rgba(255, 90, 96, 0.5);
                }

                .edit-button:hover {
                    background: linear-gradient(90deg, #E04854, #C73741);
                    transform: scale(1.05);
                    box-shadow: 0 6px 12px rgba(255, 90, 96, 0.6);
                }

            /* กล่องข้อมูลที่เลื่อนขึ้นมา */
        #matchDataDisplay {
            background: rgba(255, 255, 255, 0.08); /* ปรับให้จางลงอีกนิด */
            padding: 25px;
            border-radius: 20px; /* ทำให้ขอบโค้งน้อยลง */
            text-align: left;
            margin-top: 20px; /* เพิ่มระยะห่างจากปุ่มแก้ไขข้อมูล */
            max-height: 500px; /* กำหนดความสูงสูงสุด */
            
            backdrop-filter: blur(6px); /* เพิ่มเบลอพื้นหลัง */
            box-shadow: 0 4px 10px rgba(255, 255, 255, 0.1); /* เพิ่มเงาให้นุ่มขึ้น */
            transition: background 0.3s ease-in-out; /* ทำให้เปลี่ยนพื้นหลังนุ่มขึ้น */
        }

        /* ปรับปุ่มแก้ไขให้มีระยะห่างที่เหมาะสม */
        .edit-button {
            margin-bottom: 20px; /* เพิ่มระยะห่างด้านล่างก่อนถึง #matchDataDisplay */
        }

            </style>
        </head>
        <body>

        <div class="profile-container">
            <div>
                <h1>โปรไฟล์ผู้ใช้</h1>
                <img src="<?= htmlspecialchars($profile_picture_url) ?>" class="profile-picture" alt="รูปโปรไฟล์">
                <div class="profile-name">ชื่อผู้ใช้: <?= htmlspecialchars($profile1['username'] ?? 'ไม่มีข้อมูล') ?></div>
                <div class="profile-age">อายุ <?= htmlspecialchars($age ?? 'ไม่ระบุ') ?> ปี</div>
                
                <div class="profile-section">
                
                    <p>อีเมล: <?= htmlspecialchars($profile1['user_email'] ?? 'ไม่มีข้อมูล') ?></p>
                    <p>วันเกิด: <?= htmlspecialchars($profile1['dob'] ?? 'ไม่ระบุ') ?></p>
                </div>

                <button class="edit-button" onclick="openEditPage()">แก้ไขข้อมูล</button>
            </div>

            <!-- ข้อมูลที่สามารถเลื่อนขึ้นมา -->
            <div id="matchDataDisplay">
                <p>⏳ กำลังโหลดข้อมูล...</p>
            </div>
        </div>

        <script>
            function openEditPage() {
                window.location.href = "match_edit.php";
            }

        // ฟังก์ชันบันทึกข้อมูลลงฐานข้อมูลก่อน แล้วดึงข้อมูลใหม่มาแสดง
        function saveAndFetchMatchData() {
            const matchData = {
                goal: document.getElementById("goalInput")?.value || "ไม่มีข้อมูล",
                zodiac: document.getElementById("zodiacInput")?.value || "ไม่มีข้อมูล",
                languages: document.getElementById("languagesInput")?.value || "ไม่มีข้อมูล",
                education: document.getElementById("educationInput")?.value || "ไม่มีข้อมูล",
                family_plan: document.getElementById("familyPlanInput")?.value || "ไม่มีข้อมูล",
                covid_vaccine: document.getElementById("covidVaccineInput")?.value || "ไม่มีข้อมูล",
                love_expression: document.getElementById("loveExpressionInput")?.value || "ไม่มีข้อมูล",
                blood_type: document.getElementById("bloodTypeInput")?.value || "ไม่มีข้อมูล",
                pet: document.getElementById("petInput")?.value || "ไม่มีข้อมูล",
                drink: document.getElementById("drinkInput")?.value || "ไม่มีข้อมูล",
                exercise: document.getElementById("exerciseInput")?.value || "ไม่มีข้อมูล"
            };

            console.log("📤 ส่งข้อมูลไปยังเซิร์ฟเวอร์:", matchData);

            fetch("save_match_data.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(matchData)
            })
            .then(response => response.json())
            .then(data => {
                console.log("✅ ผลลัพธ์จากเซิร์ฟเวอร์:", data);
                if (data.success) {
                    fetchMatchData(); // ดึงข้อมูลใหม่มาแสดงหลังจากบันทึกสำเร็จ
                } else {
                    alert("❌ " + data.message);
                }
            })
            .catch(error => console.error("🚨 เกิดข้อผิดพลาด:", error));
        }

        function fetchMatchData() {
            console.log("🚀 กำลังดึงข้อมูลจาก fetch_match_data.php...");
            
            fetch("fetch_match_data.php")
                .then(response => response.text())  // เปลี่ยนเป็น text() เพื่อตรวจสอบข้อมูลที่เซิร์ฟเวอร์ส่งมา
                .then(text => {
                    console.log("📩 ข้อมูลที่ได้รับจากเซิร์ฟเวอร์ (Raw Data):", text);

                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (error) {
                        console.error("❌ JSON Parsing Error:", error);
                        document.getElementById("matchDataDisplay").innerHTML = `<p>❌ โหลดข้อมูลล้มเหลว: JSON ไม่ถูกต้อง</p>`;
                        return;
                    }

                    console.log("✅ JSON Parsed Data:", data);

                    if (data.success) {
                        const match = data.data;
                        const getValue = (value) => value && value !== "ไม่มีข้อมูล" ? value : "ไม่มีข้อมูล";

                        document.getElementById("matchDataDisplay").innerHTML = `
    <p><strong>📍 โลเคชั่น:</strong> ${getValue(match.location)}</p>
    <p><strong>👶 ช่วงอายุที่สนใจ:</strong> ${getValue(match.age_range)}</p>
    <p><strong>♈ ราศี:</strong> ${getValue(match.zodiac)}</p>
    <p><strong>💬 ภาษา:</strong> ${getValue(match.languages)}</p>
    <p><strong>🎓 การศึกษา:</strong> ${getValue(match.education)}</p>
    <p><strong>👪 แผนการมีครอบครัว:</strong> ${getValue(match.family_plan)}</p>
    <p><strong>🧩 วัคซีน COVID-19:</strong> ${getValue(match.covid_vaccine)}</p>
    <p><strong>💖 การแสดงความรัก:</strong> ${getValue(match.love_expression)}</p>
    <p><strong>💧 กรุ๊ปเลือด:</strong> ${getValue(match.blood_type)}</p>
    <p><strong>🐶 สัตว์เลี้ยง:</strong> ${getValue(match.pet)}</p>
    <p><strong>🍇 การดื่ม:</strong> ${getValue(match.drink)}</p>
    <p><strong>🎉 การออกกำลังกาย:</strong> ${getValue(match.exercise)}</p>
`;

                    } else {
                        console.error("⚠️ ไม่สามารถโหลดข้อมูลได้:", data.message);
                        document.getElementById("matchDataDisplay").innerHTML = `<p>❌ ไม่พบข้อมูล</p>`;
                    }
                })
                .catch(error => {
                    console.error("🚨 เกิดข้อผิดพลาด:", error);
                    document.getElementById("matchDataDisplay").innerHTML = `<p>❌ โหลดข้อมูลล้มเหลว</p>`;
                });
        }

        // โหลดข้อมูลเมื่อหน้าเว็บโหลดเสร็จ
        document.addEventListener("DOMContentLoaded", fetchMatchData);

     





// ✅ ฟังก์ชันอัปเดตข้อความแสดงผลสำหรับหลายตัวเลือก
function updateMultipleDisplay(buttonId, displayId, selectedValue) {
    const button = document.getElementById(buttonId);
    let display = document.getElementById(displayId);

    // ถ้ายังไม่มี Element แสดงผล ให้สร้างขึ้นมา
    if (!display) {
        display = document.createElement("div");
        display.id = displayId;
        display.style.marginTop = "10px";
        display.style.fontSize = "16px";
        display.style.color = "#FFA726";
        display.style.textAlign = "center";
        display.setAttribute("data-values", ""); // ใช้ attribute แทน dataset
        button.parentNode.insertBefore(display, button.nextSibling);
    }

    // ดึงค่าปัจจุบันจาก attribute
    let selectedValues = display.getAttribute("data-values") ? display.getAttribute("data-values").split(", ") : [];

    // ถ้ายังไม่มีค่านี้ ให้เพิ่มเข้าไป
    if (!selectedValues.includes(selectedValue)) {
        selectedValues.push(selectedValue);
        display.setAttribute("data-values", selectedValues.join(", ")); // อัปเดต attribute

        // อัปเดตข้อความใน `<div>` พร้อมปุ่มลบ
        display.innerHTML = selectedValues
          
            .join("");
    }
}

// ✅ ฟังก์ชันลบค่าที่เลือกออกจาก `<div>`
function removeSelectedValue(displayId, valueToRemove) {
    let display = document.getElementById(displayId);
    if (!display) return;

    let selectedValues = display.getAttribute("data-values") ? display.getAttribute("data-values").split(", ") : [];
    
    // กรองค่าที่ไม่ต้องการออก
    selectedValues = selectedValues.filter(value => value !== valueToRemove);
    display.setAttribute("data-values", selectedValues.join(", ")); // อัปเดต attribute

    // อัปเดตข้อความใหม่ หรือซ่อน `<div>` ถ้าไม่มีค่าเหลือ
    if (selectedValues.length > 0) {
        display.innerHTML = selectedValues
            
            .join("");
    } else {
        display.remove(); // ถ้าไม่มีค่าเหลือให้ลบ `<div>` ออก
    }
}

// ✅ ฟังก์ชันอัปเดตค่าโลเคชั่นและอายุจาก `range slider`
function updateLocationValue() {
    requestAnimationFrame(() => {
        const locationRange = document.getElementById("locationRange");
        const locationValue = document.getElementById("locationValue");

        if (locationRange && locationValue) {
            locationValue.textContent = locationRange.value + " กม.";
        }
    });
}

function updateAgeValue() {
    requestAnimationFrame(() => {
        const ageRange = document.getElementById("ageRange");
        const ageValue = document.getElementById("ageValue");

        if (ageRange && ageValue) {
            ageValue.textContent = ageRange.value + " ปี";
        }
    });
}

// ✅ ตั้งค่าการฟังอีเวนต์เลื่อนค่า (ทำให้เลื่อนได้)
document.addEventListener("DOMContentLoaded", () => {
    const locationSlider = document.getElementById("locationRange");
    const ageSlider = document.getElementById("ageRange");

    if (locationSlider) {
        locationSlider.addEventListener("input", updateLocationValue);
        updateLocationValue(); // อัปเดตค่าเริ่มต้น
    }

    if (ageSlider) {
        ageSlider.addEventListener("input", updateAgeValue);
        updateAgeValue(); // อัปเดตค่าเริ่มต้น
    }
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


document.getElementById("saveButton").addEventListener("click", function () {
    const matchData = {
        goal: document.querySelector("#goalDisplay")?.textContent.trim() || "ไม่มีข้อมูล",
        zodiac: document.querySelector("#zodiacDisplay")?.textContent.trim() || "ไม่มีข้อมูล",
        languages: document.querySelector("#languageDisplay")?.textContent.trim() || "ไม่มีข้อมูล",
        education: document.querySelector("#educationDisplay")?.textContent.trim() || "ไม่มีข้อมูล",
        family_plan: document.querySelector("#familyPlanDisplay")?.textContent.trim() || "ไม่มีข้อมูล",
        covid_vaccine: document.querySelector("#vaccineDisplay")?.textContent.trim() || "ไม่มีข้อมูล",
        love_expression: document.querySelector("#loveExpressionDisplay")?.textContent.trim() || "ไม่มีข้อมูล",
        blood_type: document.querySelector("#bloodTypeDisplay")?.textContent.trim() || "ไม่มีข้อมูล",
        pet: document.querySelector("#petDisplay")?.textContent.trim() || "ไม่มีข้อมูล",
        drink: document.querySelector("#drinkDisplay")?.textContent.trim() || "ไม่มีข้อมูล",
        exercise: document.querySelector("#exerciseDisplay")?.textContent.trim() || "ไม่มีข้อมูล",
        location: document.getElementById("locationRange").value + " กม.",
        age_range: document.getElementById("ageRange").value + " ปี"
    };

    console.log("📤 ข้อมูลที่ส่งไปเซิร์ฟเวอร์:", matchData);

    fetch("save_data.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(matchData),
    })
    .then(response => response.json())
    .then(data => {
        console.log("✅ ตอบกลับจากเซิร์ฟเวอร์:", data);
        if (data.success) {
            alert("✅ บันทึกข้อมูลสำเร็จ!");
            location.reload();
        } else {
            alert("❌ เกิดข้อผิดพลาด: " + data.message);
        }
    })
    .catch(error => {
        console.error("🚨 เกิดข้อผิดพลาด:", error);
        alert("❌ มีข้อผิดพลาดในการบันทึกข้อมูล");
    });
});





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
