<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userEmail = $_POST['email']; // อีเมลของผู้ใช้ที่ส่งข้อความ
    $userName = $_POST['name']; // ชื่อผู้ใช้
    $userMessage = $_POST['message']; // ข้อความที่ผู้ใช้พิมพ์

    $adminEmail = "jakarpat3276@gmail.com"; // ✅ อีเมลของแอดมิน (คุณ)

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username   = 'jakarpat3276@gmail.com'; // ✅ อีเมลของคุณ
        $mail->Password   = 'qums jyib ovfw cmib'; // ✅ ใช้ App Password ของ Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // ✅ ให้ FROM เป็นอีเมลของผู้ใช้
        $mail->setFrom($userEmail, $userName);
        
        // ✅ ส่งหาแอดมิน (คุณ)
        $mail->addAddress($adminEmail, 'Admin');

        $mail->isHTML(true);
        $mail->Subject = "New Contact Form Message from $userName";
        $mail->Body    = "<h2>ข้อความจาก: $userName ($userEmail)</h2><p>$userMessage</p>";

        if (!$mail->send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        } 
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: " . $mail->ErrorInfo;
    }
} 
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดต่อแอดมิน | Heart Meart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* ✅ ตั้งค่าพื้นฐาน */
body {
    margin: 0;
    font-family: 'Arial', sans-serif;
    background: url('uploads/96.webp') no-repeat center center fixed;
    background-size: cover;
    animation: moveBackground 10s infinite alternate ease-in-out, zoomEffect 15s infinite alternate ease-in-out;
    color: white;
    overflow-x: hidden; /* ✅ ป้องกันการเลื่อนแนวนอน */
}

/* ✅ ทำให้พื้นหลังเลื่อนขึ้น-ลงแบบ Smooth */
@keyframes moveBackground {
    0% { background-position: center top; }
    50% { background-position: center center; }
    100% { background-position: center bottom; }
}

/* ✅ ทำให้พื้นหลังซูมเข้า-ออกเบาๆ */
@keyframes zoomEffect {
    0% { background-size: 100%; }
    100% { background-size: 105%; }
}

        /* ✅ ปรับพื้นหลังของ Header ให้เข้ากับธีม */
header {
  
  padding: 12px 0;
  position: fixed;
  width: 100%;
  top: 0;
  z-index: 1000;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 10px 30px rgba(255, 0, 255, 0.4); /* ✅ เพิ่มเงาแบบม่วงนีออน */
  border-bottom: 4px solid rgba(255, 105, 180, 0.5); /* ✅ ขอบล่างสีชมพู */
}

        
/* ✅ จัดทุกอย่างให้อยู่กลาง */
.header-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 95%;
    max-width: 1300px;
    margin: 0 auto;
}

/* ✅ ปรับขนาดและตำแหน่งของโลโก้ */
.logo-container {
    display: flex;
    align-items: center;
    gap: 5px;
}

.logo-container img {
    width: 100px;
    height: auto;
}

.logo-container h1 {
    font-size: 30px;
    color: #ff6b81;
    white-space: nowrap;
}

/* ✅ ปรับเมนูให้เลื่อนไปทางซ้ายอีกนิด */
nav {
    flex-grow: 1;
    text-align: center; /* ✅ จัดให้อยู่ตรงกลาง */
    margin-left: -220px; /* ✅ เลื่อนเมนูไปทางซ้าย */
}

nav ul {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center; /* ✅ เมนูอยู่กึ่งกลาง */
    align-items: center;
    gap: 20px;
}

/* ✅ บังคับให้ตัวอักษรเป็นสีขาว */
nav ul li a {
    color: white !important; /* ✅ บังคับให้เป็นสีขาว */
    text-decoration: none;
    font-size: 30px;
    font-weight: bold;
    padding: 10px 14px;
    border-radius: 10px;
    transition: all 0.3s ease;
    white-space: nowrap;
    text-shadow: 2px 2px 10px rgba(255, 255, 255, 0.8); /* ✅ เพิ่มเงาให้มองเห็นชัดขึ้น */
    opacity: 1 !important; /* ✅ ป้องกันความโปร่งแสง */
    filter: none !important; /* ✅ ปิดการใช้ filter ที่อาจมีผลต่อสี */
}


/* ✅ เพิ่มเส้นไฮไลท์ด้านล่างเมื่อโฮเวอร์ */
nav ul li a {
    position: relative; /* ✅ ตั้งค่า relative เพื่อให้ pseudo-element อยู่ภายใน */
}

/* ✅ เส้นไฮไลท์ */
nav ul li a::after {
    content: "";
    display: block;
    height: 3px;
    background: linear-gradient(90deg, #ff416c, #ff6b81);
    width: 0;
    position: absolute;
    bottom: -5px; /* ✅ ควบคุมให้เส้นอยู่ด้านล่างของตัวอักษร */
    left: 50%;
    transform: translateX(-50%); /* ✅ จัดให้อยู่กึ่งกลางของตัวอักษร */
    transition: width 0.3s ease-in-out;
}

/* ✅ ทำให้เส้นขยายเมื่อโฮเวอร์ */
nav ul li a:hover::after {
    width: 100%;
}

/* ✅ ทำให้ปุ่มภาษาและ Login อยู่ขวาสุด */
.auth {
    display: flex;
    align-items: center;
    gap: 16px; /* ✅ เพิ่มระยะห่างระหว่างปุ่ม */
    white-space: nowrap;
    position: absolute;
    right: 24px; /* ✅ ให้ปุ่มอยู่ขวาสุด */
}

/* ✅ ปรับขนาดปุ่ม "ภาษา" และ "Login" ให้ใหญ่ขึ้น */
.auth button {
    background: linear-gradient(45deg, #ff416c, #ff6b81);
    color: white;
    border: none;
    padding: 14px 40px; /* ✅ เพิ่มขนาด Padding */
    border-radius: 30px; /* ✅ ปรับความโค้งมน */
    font-size: 26px; /* ✅ เพิ่มขนาดตัวอักษร */
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px rgba(255, 88, 100, 0.5);
}

/* ✅ เอฟเฟกต์เมื่อโฮเวอร์ */
.auth button:hover {
    background: linear-gradient(45deg, #ff6b81, #ff416c);
    transform: scale(1.1); /* ✅ เพิ่มขนาดตอนโฮเวอร์ */
    box-shadow: 0 10px 25px rgba(255, 88, 100, 0.6);
}


/* ✅ ทำให้ปุ่มภาษาและ Login อยู่ขวาสุด */
.auth {
    display: flex;
    align-items: center;
    gap: 16px; /* ✅ เพิ่มระยะห่างระหว่างปุ่ม */
    white-space: nowrap;
    position: absolute;
    right: 24px; /* ✅ ให้ปุ่มอยู่ขวาสุด */
}

/* ✅ ปรับขนาดปุ่ม "ภาษา" และ "Login" ให้ใหญ่ขึ้น */
.auth button {
    background: linear-gradient(45deg, #ff416c, #ff6b81);
    color: white;
    border: none;
    padding: 14px 40px; /* ✅ เพิ่มขนาด Padding */
    border-radius: 30px; /* ✅ ปรับความโค้งมน */
    font-size: 26px; /* ✅ เพิ่มขนาดตัวอักษร */
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 20px rgba(255, 88, 100, 0.5);
}

/* ✅ เอฟเฟกต์เมื่อโฮเวอร์ */
.auth button:hover {
    background: linear-gradient(45deg, #ff6b81, #ff416c);
    transform: scale(1.1); /* ✅ เพิ่มขนาดตอนโฮเวอร์ */
    box-shadow: 0 10px 25px rgba(255, 88, 100, 0.6);
}

html, body {
    overflow: hidden;
    height: 100%;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;

}

.contact-form {
    background: rgba(255, 255, 255, 0.1);
    padding: 60px;
    border-radius: 15px;
    text-align: center;
    max-width: 450px;
    width: 100%;
    box-shadow: 0 8px 25px rgba(255, 105, 180, 0.4);
    backdrop-filter: blur(16px);
    border: 2px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease-in-out;

    /* จัดให้อยู่ตรงกลางแนวตั้ง และอยู่ใต้แถบเมนู */
    position: relative;
    top: 70px; /* ปรับค่าตามความสูงของเมนู */
}

.contact-form:hover {
    transform: scale(1.02);
    box-shadow: 0 10px 30px rgba(255, 105, 180, 0.6);
}


.contact-form h2 {
    color: #ff6a6a;
    font-size: 26px;
    text-shadow: 2px 2px 10px rgba(255, 106, 106, 0.6);
    margin-bottom: 20px;
}

/* ✅ กล่องป้อนข้อมูล */
.input-group {
    margin: 18px 0;
    position: relative;
    display: flex;
    align-items: center;
}

.input-group i {
    position: absolute;
    left: 18px;
    color: rgba(255, 255, 255, 0.6);
    font-size: 18px;
}

.input-group input,
.input-group textarea {
    width: 100%;
    padding: 12px 12px 12px 45px;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.1);
    color: white;
    font-size: 16px;
    outline: none;
    transition: all 0.3s ease-in-out;
}

.input-group textarea {
    resize: none;
    height: 120px;
}

.input-group input:focus,
.input-group textarea:focus {
    border-color: #ff6a6a;
    background: rgba(255, 255, 255, 0.15);
}

.input-group input::placeholder,
.input-group textarea::placeholder {
    color: rgba(255, 255, 255, 0.6);
}

/* ✅ ปุ่มกด */
.send-btn {
    background: linear-gradient(45deg, #ff416c, #ff6b81);
    color: white;
    border: none;
    padding: 14px;
    border-radius: 12px;
    font-size: 18px;
    cursor: pointer;
    width: 100%;
    margin-top: 15px;
    transition: all 0.3s ease-in-out;
    box-shadow: 0 5px 15px rgba(255, 65, 108, 0.4);
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
}

.send-btn:hover {
    background: linear-gradient(45deg, #ff6b81, #ff416c);
    transform: scale(1.05);
}


    </style>
</head>
<body>

<!-- ✅ HEADER -->
<header>
    <div class="header-container">
        <!-- โลโก้และชื่อ -->
        <div class="logo-container">
            <a href="index.html">
            <img src="/Project/uploads/1234.png" alt="User Profile Image" class="profile-image"></a>
            <h1>Heart Meart</h1>
        </div>

        <!-- เมนูอยู่ตรงกลาง -->
        <nav>
            <ul>
                <li><a href="view.php" data-english="Review" data-thai="รีวิว">รีวิว</a></li>
                <li><a href="Page.html" data-english="Learn More" data-thai="เรียนรู้เพิ่มเติม">เรียนรู้เพิ่มเติม</a></li>
                <li><a href="contact.html" data-english="Contact" data-thai="ติดต่อ">ติดต่อ</a></li>
                <li><a href="Dating.html" data-english="Safety" data-thai="ความปลอดภัย">ความปลอดภัย</a></li>
            </ul>
        </nav>

        <!-- ปุ่มภาษาและ Login อยู่ขวาสุด -->
        <div class="auth">
            <button class="language-btn" onclick="changeLanguage()">ภาษา</button>
            <button data-english="Login" data-thai="เข้าสู่ระบบ" onclick="window.location.href='register_index.php'">Login</button>
        </div>
    </div>
</header>

<div class="contact-form">
    <h2>ติดต่อแอดมิน</h2>
    <form action="/project/send_email.php" method="POST">

        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text" name="name" placeholder="ชื่อของคุณ" required>
        </div>

        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="อีเมลของคุณ" required>
        </div>

        <div class="input-group">
            <i class="fas fa-comment"></i>
            <textarea name="message" placeholder="ข้อความของคุณ" required></textarea>
        </div>

        <button type="submit" class="send-btn">
            <i class="fas fa-paper-plane"></i> ส่งข้อความ
        </button>

    </form>
</div>
<script>
        function changeLanguage() {
            const currentLang = document.body.classList.contains('english') ? 'thai' : 'english';
            document.body.classList.toggle('english');
            document.body.classList.toggle('thai');
            
            const elements = document.querySelectorAll('[data-english], [data-thai]');
            elements.forEach(el => {
                const text = el.getAttribute('data-' + currentLang);
                if (text) {
                    el.textContent = text;
                }
            });
        }
    </script>
</body>
</html>
