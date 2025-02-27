<?php
// ตั้งค่าการเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydatabase"; // แก้เป็นฐานข้อมูลที่คุณใช้

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตรวจสอบว่ามีการส่งข้อมูลจากฟอร์มหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $review = $_POST['review'];

    // ป้องกัน SQL Injection
    $stmt = $conn->prepare("INSERT INTO reviews (name, review) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $review);
    $stmt->execute();
    $stmt->close();

    // รีเฟรชหน้าเพื่อแสดงรีวิวใหม่
    header("Location: view.php");
    exit();
}

// ดึงข้อมูลรีวิวจากฐานข้อมูล
$result = $conn->query("SELECT * FROM reviews ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รีวิวเว็บไซต์</title>
    <style>
        /* ✅ ตั้งค่าพื้นหลัง */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
            padding: 40px;
        }

        /* ✅ หัวข้อ */
        h2 {
            font-size: 32px;
            color: #333;
            margin-bottom: 20px;
        }

        /* ✅ ปุ่มเมนูด้านบน */
        .top-buttons {
            margin-bottom: 30px;
        }

        .top-buttons a {
            display: inline-block;
            padding: 12px 24px;
            margin: 5px;
            border-radius: 30px;
            font-size: 16px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .btn-guide { background: #ff416c; }
        .btn-troubleshoot { background: #ff6600; }
        .btn-security { background: #cc0033; }

        .top-buttons a:hover {
            transform: scale(1.05);
        }

        /* ✅ ฟอร์มรีวิว */
        form {
    background: white;
    max-width: 500px;
    width: 90%; /* ปรับให้ยืดหยุ่นตามหน้าจอ */
    margin: 20px auto;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    text-align: left;
}


        input, textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 14px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    max-width: 100%; /* ป้องกันขยายเกินกรอบ */
}

textarea {
    resize: none; /* ปิดการขยายเอง */
}


        /* ✅ ปุ่มส่งรีวิว */
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(45deg, #ff416c, #ff4b2b);
            border: none;
            color: white;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(255, 65, 108, 0.3);
        }

        button:hover {
            background: linear-gradient(45deg, #ff4b2b, #ff416c);
            transform: scale(1.05);
            box-shadow: 0 5px 12px rgba(255, 65, 108, 0.4);
        }

        /* ✅ ส่วนแสดงรีวิว */
        .reviews {
            max-width: 600px;
            margin: 40px auto;
            text-align: left;
        }

        .review {
            background: white;
            padding: 20px;
            margin: 15px 0;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .review p {
            margin: 5px 0;
            font-size: 16px;
        }

        .review strong {
            font-size: 18px;
            color: #ff416c;
        }

        .review small {
            color: #777;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <!-- ✅ หัวข้อ -->
    <h2>รีวิวเว็บไซต์</h2>

    <!-- ✅ ปุ่มเมนู -->
    <div class="top-buttons">
        <a href="index.html" class="btn-guide">หน้าแรก</a>
        <a href="contact.html" class="btn-troubleshoot">ติดต่อเรา</a>
        <a href="Dating.html" class="btn-security">ความปลอดภัย</a>
    </div>

    <!-- ✅ ฟอร์มรีวิว -->
    <form action="" method="post">
        <label for="name">ชื่อของคุณ</label>
        <input type="text" name="name" placeholder="ชื่อของคุณ" required>
        
        <label for="review">เขียนรีวิวของคุณ</label>
        <textarea name="review" placeholder="เขียนรีวิวของคุณ" required></textarea>
        
        <button type="submit">ส่งรีวิว</button>
    </form>

    <!-- ✅ แสดงรีวิวล่าสุด -->
    <h3>รีวิวล่าสุด</h3>
    <div class="reviews">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="review">
                <p><strong><?= htmlspecialchars($row['name']) ?></strong></p>
                <p><?= nl2br(htmlspecialchars($row['review'])) ?></p>
                <small><?= $row['created_at'] ?></small>
            </div>
        <?php endwhile; ?>
    </div>

</body>
</html>
