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

// ตรวจสอบว่า $_SESSION['email'] มีค่าหรือไม่
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email']; // ดึงค่าจาก Session

    // ดึงข้อมูล username จากฐานข้อมูล
    $sql = "SELECT username FROM users WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($username);
        $stmt->fetch();
        $stmt->close();
    }
} else {
    $email = "";
    $username = ""; // ตั้งค่าเริ่มต้นหากไม่มีข้อมูล
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างบัญชี</title>
    <style>




        body {
    background: linear-gradient(to right, #2c003e, #6e0e65, #9b196f);
    color: #fff;
    font-family: 'Poppins', sans-serif;
    padding: 20px;
    text-align: center;
}

.container {
    display: flex;
    justify-content: space-between;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    max-width: 900px;
    margin: auto;
    padding: 40px;
    box-shadow: 0 10px 30px rgba(255, 0, 255, 0.2);
    backdrop-filter: blur(10px);
}

.form-container {
    flex: 1;
    margin-right: 20px;
}

h1 {
    font-size: 2.5em;
    font-weight: bold;
    color: #ff73b3;
    text-shadow: 2px 2px 10px rgba(255, 255, 255, 0.3);
}

.form-group {
    margin-bottom: 25px;
    display: flex;
    align-items: center;
}

.form-group label {
    flex: 0 0 150px;
    margin-right: 10px;
    font-size: 1.2em;
    font-weight: bold;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="date"] {
    flex: 1;
    padding: 12px;
    border: 2px solid #ff73b3;
    border-radius: 10px;
    background-color: rgba(255, 255, 255, 0.1);
    color: #fff;
    font-size: 1em;
    transition: 0.3s;
}

.form-group input:focus {
    border-color: #ff73b3;
    background-color: rgba(255, 255, 255, 0.2);
}

.submit-button-container {
    display: flex;
    justify-content: center;
    margin-top: 30px;
}

.submit-button-container button {
    width: 30%;
    padding: 12px;
    font-size: 1.5em;
    border-radius: 8px;
    background: linear-gradient(to right, #ff73b3, #ff1493);
    color: #fff;
    border: none;
    cursor: pointer;
    transition: background 0.3s, transform 0.2s;
}

.submit-button-container button:hover {
    background: linear-gradient(to right, #ff1493, #ff007f);
    transform: translateY(-3px);
    box-shadow: 0px 5px 20px rgba(255, 0, 255, 0.3);
}

.image-upload-container {
    flex-basis: 300px;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 20px;
}

.image-upload-container label {
    margin-bottom: 15px;
    display: block;
    font-size: 1.5em;
    font-weight: bold;
    color: #ff73b3;
}

.image-upload {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    width: 100%;
}

.image-upload input[type="file"] {
    display: none;
}

.image-upload label {
    border: 2px dashed #ff73b3;
    height: 150px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #ff73b3;
    cursor: pointer;
    border-radius: 10px;
    transition: 0.3s;
}

.image-upload label:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: #ff1493;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 10px;
}
.image-upload {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* จัดเรียง 3 รูปต่อแถว */
    gap: 30px; /* เพิ่มระยะห่างระหว่างช่อง */
    width: 100%;
    max-width: 1200px; /* ปรับขนาดให้เหมาะกับหน้าจอใหญ่ */
    margin: auto; /* จัดให้อยู่ตรงกลาง */
}

.image-upload label {
    width: 220px; /* ขยายขนาดช่อง */
    height: 220px; /* ขยายขนาดช่อง */
    display: flex;
    justify-content: center;
    align-items: center;
    border: 3px dashed #ff73b3; /* ขยายเส้นขอบ */
    border-radius: 15px;
    font-size: 1.2em; /* ขยายตัวอักษร */
    color: #ff73b3;
    cursor: pointer;
    transition: 0.3s;
    text-align: center;
    position: relative;
    overflow: hidden;
    background-color: rgba(255, 255, 255, 0.1);
}

.image-upload label:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: #ff1493;
}

/* ปรับขนาดตัวอย่างรูปภาพให้ใหญ่ขึ้น */
.image-preview {
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    color: #ff73b3;
    font-size: 1.2em;
    transition: 0.3s;
    background-size: cover;
    background-position: center;
    min-height: 220px; /* ป้องกันการหดของช่อง */
    max-width: 100%;
    border-radius: 10px;
}


    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>สร้างบัญชี</h1>
            <form action="profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                    <label for="name">ชื่อ</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($username); ?>" readonly>
                </div>
                <div class="form-group">
    <label for="email">อีเมล</label>
    <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
</div>

                <div class="form-group">
                    <label for="dob">วันเกิด</label>
                    <input type="date" id="dob" name="dob" required>
                </div>
                <div class="form-group">
                    <label>เพศ</label>
                    <div>
                        <input type="radio" id="male" name="gender" value="male" required>
                        <label for="male">ผู้ชาย</label>
                        <input type="radio" id="female" name="gender" value="female">
                        <label for="female">ผู้หญิง</label>
                        <input type="radio" id="other" name="gender" value="other">
                        <label for="other">อื่นๆ</label>
                    </div>
                </div>
                <div class="form-group">
                    <label>ฉันสนใจ</label>
                    <div>
                        <input type="radio" id="interest_male" name="interest" value="male" required>
                        <label for="interest_male">ผู้ชาย</label>
                        <input type="radio" id="interest_female" name="interest" value="female">
                        <label for="interest_female">ผู้หญิง</label>
                        <input type="radio" id="interest_any" name="interest" value="any">
                        <label for="interest_any">ทุกคน</label>
                    </div>
                </div>
                <div class="image-upload-container">
                    <label>รูปภาพโปรไฟล์</label>
                    <div class="image-upload">
                        <label for="profile_pic1">
                            <input type="file" id="profile_pic1" name="profile_pictures[]" accept="image/*" onchange="previewImage(event, 'preview1')">
                            <div class="image-preview" id="preview1">เพิ่มรูปภาพ 1</div>
                        </label>
                        <label for="profile_pic2">
                            <input type="file" id="profile_pic2" name="profile_pictures[]" accept="image/*" onchange="previewImage(event, 'preview2')">
                            <div class="image-preview" id="preview2">เพิ่มรูปภาพ 2</div>
                        </label>
                        <label for="profile_pic3">
                            <input type="file" id="profile_pic3" name="profile_pictures[]" accept="image/*" onchange="previewImage(event, 'preview3')">
                            <div class="image-preview" id="preview3">เพิ่มรูปภาพ 3</div>
                        </label>
                        <label for="profile_pic4">
                            <input type="file" id="profile_pic4" name="profile_pictures[]" accept="image/*" onchange="previewImage(event, 'preview4')">
                            <div class="image-preview" id="preview4">เพิ่มรูปภาพ 4</div>
                        </label>
                        <label for="profile_pic5">
                            <input type="file" id="profile_pic5" name="profile_pictures[]" accept="image/*" onchange="previewImage(event, 'preview5')">
                            <div class="image-preview" id="preview5">เพิ่มรูปภาพ 5</div>
                        </label>
                        <label for="profile_pic6">
                            <input type="file" id="profile_pic6" name="profile_pictures[]" accept="image/*" onchange="previewImage(event, 'preview6')">
                            <div class="image-preview" id="preview6">เพิ่มรูปภาพ 6</div>
                        </label>
                    </div>
                </div>
                
                <script>
                function previewImage(event, previewId) {
                    const input = event.target;
                    const preview = document.getElementById(previewId);
                
                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                
                        reader.onload = function(e) {
                            preview.style.backgroundImage = `url(${e.target.result})`;
                            preview.textContent = ""; // ลบข้อความ "เพิ่มรูปภาพ"
                            preview.style.border = "none"; // ซ่อนเส้นขอบเดิม
                        };
                
                        reader.readAsDataURL(input.files[0]);
                    }
                }
                </script>
                
                <div class="submit-button-container">
                    <button type="submit">สร้างบัญชี</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
