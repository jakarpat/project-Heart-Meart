<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบและสมัครสมาชิก</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
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


/* ✅ ตั้งค่าพื้นฐานของฟอร์ม */
.container {
    display: flex;
    justify-content: center;  /* ✅ จัดกึ่งกลางแนวนอน */
    align-items: center;  /* ✅ จัดกึ่งกลางแนวตั้ง */
    height: 100vh;  /* ✅ ทำให้ฟอร์มอยู่ตรงกลางจอ */
    width: 100%;
    position: relative;
}

/* ✅ ฟอร์มเข้าสู่ระบบ */
.form-container {
    background: rgba(255, 255, 255, 0.95); /* ✅ เพิ่มความโปร่งใส */
    padding: 40px;
    border-radius: 20px;
    text-align: center;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(10px); /* ✅ เอฟเฟกต์ความโปร่งใส */
}

/* ✅ หัวข้อฟอร์ม */
.form-container h1 {
    color: #ff416c;
    margin-bottom: 20px;
    font-size: 28px;
    text-shadow: 2px 2px 10px rgba(255, 65, 108, 0.5);
}

/* ✅ กล่องป้อนข้อมูล */
.input-group {
    display: flex;
    align-items: center;
    background: white;
    border-radius: 30px;
    padding: 12px;
    margin: 12px 0;
    border: 1px solid #ccc;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

.input-group i {
    margin-right: 12px;
    color: #666;
    font-size: 18px;
}

.input-group input {
    border: none;
    outline: none;
    width: 100%;
    font-size: 16px;
    padding: 8px;
}

/* ✅ ปุ่มกด */
button[type="submit"] {
    background: linear-gradient(45deg, #ff416c, #ff6b81);
    color: white;
    border: none;
    padding: 14px;
    border-radius: 30px;
    font-size: 20px;
    cursor: pointer;
    width: 100%;
    margin-top: 20px;
    transition: all 0.3s ease-in-out;
    box-shadow: 0 5px 15px rgba(255, 65, 108, 0.4);
}

button[type="submit"]:hover {
    background: linear-gradient(45deg, #ff6b81, #ff416c);
    transform: scale(1.05);
    box-shadow: 0 8px 20px rgba(255, 65, 108, 0.6);
}

/* ✅ ข้อความแจ้งเตือน */
.message {
    color: red;
    font-size: 14px;
    margin-top: 5px;
}

/* ✅ ลิงก์สมัครสมาชิก */
.form-container p {
    margin-top: 15px;
    font-size: 16px;
    color: #333;
}

.form-container p a {
    color: #ff416c;
    font-weight: bold;
    text-decoration: none;
    transition: color 0.3s ease-in-out;
}

.form-container p a:hover {
    color: #ff6b81;
}

/* ✅ รองรับ Responsive */
@media screen and (max-width: 768px) {
    .form-container {
        max-width: 350px; /* ✅ ปรับให้พอดีกับหน้าจอ */
    }
}

</style>
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


    <div class="container">
        <!-- Login Form -->
        <div class="form-container" id="login-container">
            <h1>เข้าสู่ระบบ</h1>
            <?php if (isset($_GET['error'])): ?>
                <p style="color:red;"><?= htmlspecialchars($_GET['error']) ?></p>
            <?php endif; ?>
            <form id="loginForm" action="register.php" method="post">
    <input type="hidden" name="action" value="login">
    <div class="input-group">
        <i class="fas fa-envelope"></i>
        <input type="email" id="login-email" name="email" placeholder="อีเมล" required maxlength="50">
    </div>
    <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" id="login-password" name="password" placeholder="รหัสผ่าน" required maxlength="50">
    </div>
     
    <button type="submit">เข้าสู่ระบบ</button>
</form>

            <p>ยังไม่มีบัญชี? <a href="register_2.html">สมัครสมาชิก</a></p>
        </div>
    </div>
</body>

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

 // ✅ ทำให้ปุ่มภาษาและปุ่มเข้าสู่ระบบมีขนาดเท่ากันเสมอ
 const langButton = document.querySelector('.language-btn');
 const loginButton = document.querySelector('.login-btn');

 if (langButton && loginButton) {
     langButton.style.width = '140px';
     loginButton.style.width = '140px';
 }
}

 </script>
</html>
