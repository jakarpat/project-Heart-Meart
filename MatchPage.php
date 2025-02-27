<?php
// เริ่มต้น Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['email'])) {
    header("Location: login.php?error=You must login to access this page.");
    exit();
}

// ดึงข้อมูลผู้ใช้จาก Session
$email = $_SESSION['email'];
$username = $_SESSION['username'] ?? '';

// ฟังก์ชันสำหรับเชื่อมต่อฐานข้อมูล
function getDatabaseConnection() {
    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "mydatabase";

    $conn = new mysqli($host, $user, $password, $database);
    if ($conn->connect_error) {
        throw new Exception("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
    }
    return $conn;
}

// ฟังก์ชันคำนวณเปอร์เซ็นต์แมตช์ (อายุอย่างเดียว)
function calculateMatchPercentage($user_age, $match_age) {
    $age_diff = abs($user_age - $match_age);
    $percentage = max(100 - ($age_diff * 5), 0);
    return $percentage;
}

// ฟังก์ชันคำนวณเปอร์เซ็นต์แมตช์แบบมีโบนัสถ้าเพศตรงกัน
function calculateMatchPercentageWithGender($user_age, $match_age, $user_interest, $match_gender) {
    $age_diff = abs($user_age - $match_age);
    $percentage = max(100 - ($age_diff * 5), 0);
    // ถ้าคู่แมตช์ตรงกับความสนใจ ให้เพิ่มโบนัส 20 คะแนน
    if ($user_interest === $match_gender) {
        $percentage += 20;
    }
    return min($percentage, 100);
}

try {
    $conn = getDatabaseConnection();

    // ดึงข้อมูลผู้ใช้ปัจจุบัน
    $sql_user = "SELECT profile_pictures, gender, interest, dob FROM profile1 WHERE email = ?";
    $stmt_user = $conn->prepare($sql_user);
    if (!$stmt_user) {
        throw new Exception("การเตรียมคำสั่ง SQL ล้มเหลว: " . $conn->error);
    }
    $stmt_user->bind_param("s", $email);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows > 0) {
        $current_user = $result_user->fetch_assoc();
        $user_picture = $current_user['profile_pictures'];
        $user_gender = $current_user['gender'];
        $user_interest = $current_user['interest'];

        // หากมีรูปโปรไฟล์
        $upload_path = "";
        $default_picture = $upload_path . "default_profile.jpg";
        $profile_picture_path = (!empty($user_picture) && file_exists(__DIR__ . "/" . $upload_path . $user_picture))
    ? $upload_path . $user_picture
    : $default_picture;

        // คำนวณอายุของผู้ใช้
        if (!empty($current_user['dob'])) {
            $dob = new DateTime($current_user['dob']);
            $now = new DateTime();
            $user_age = $now->diff($dob)->y;
        } else {
            $user_age = 0;
        }
    } else {
        echo "<p style='color: red;'>ไม่พบข้อมูลผู้ใช้งาน</p>";
        exit();
    }
    $stmt_user->close();

    // ดึงข้อมูล Matches
    // ใช้ LIKE ? เพื่อรองรับ wildcard
    $sql_matches = "SELECT id, name, TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age, gender, profile_pictures, interest
                    FROM profile1
                    WHERE gender LIKE ? AND email != ?";
    $stmt_matches = $conn->prepare($sql_matches);
    if (!$stmt_matches) {
        throw new Exception("การเตรียมคำสั่ง SQL ล้มเหลว: " . $conn->error);
    }

    // ถ้า interest เป็น male/female ให้ใช้ค่านั้น ไม่งั้นใช้ '%'
    $desired_gender = ($user_interest === 'male' || $user_interest === 'female') ? $user_interest : '%';

    $stmt_matches->bind_param("ss", $desired_gender, $email);
    $stmt_matches->execute();
    $result_matches = $stmt_matches->get_result();

    $matches = [];
    while ($row = $result_matches->fetch_assoc()) {
        $user_pictures = explode(',', $row['profile_pictures']);
        $match_picture = trim($user_pictures[0]);

        // คำนวณ match_percentage แบบมีโบนัส
        $match_percentage = calculateMatchPercentageWithGender($user_age, $row['age'], $user_interest, $row['gender']);

        $matches[] = [
            'id' => $row['id'], // สำคัญ: เอาไว้ใช้ในระบบแชท
            'name' => $row['name'],
            'age' => $row['age'],
            'gender' => $row['gender'],
            'image' => (!empty($match_picture) && file_exists($upload_path . $match_picture))
                        ? $upload_path . $match_picture
                        : $default_picture,
            'interest' => $row['interest'],
            'match_percentage' => $match_percentage
        ];
    }

    $stmt_matches->close();
    $conn->close();

} catch (Exception $e) {
    echo "<p style='color: red;'>เกิดข้อผิดพลาด: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit();
}

?>


  <script>
    // กำหนดตัวแปร global สำหรับรูปเจ้าของยูสเซอร์
    let myProfilePic = "<?= htmlspecialchars($profile_picture_path) ?>";
  </script>

  <script>
      function askForLocation() {
          if (navigator.geolocation) {
              navigator.geolocation.getCurrentPosition(saveLocation, showError);
          } else {
              alert("❌ เบราว์เซอร์ของคุณไม่รองรับการแชร์ตำแหน่ง");
          }
      }

      function saveLocation(position) {
          let latitude = position.coords.latitude;
          let longitude = position.coords.longitude;

          // ส่งพิกัดไปบันทึกในฐานข้อมูลโดยใช้ fetch
          fetch("update_location.php", {
              method: "POST",
              headers: { "Content-Type": "application/x-www-form-urlencoded" },
              body: `latitude=${latitude}&longitude=${longitude}`
          })
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  alert("✅ พิกัดถูกบันทึกเรียบร้อยแล้ว!");
              } else {
                  alert("❌ ไม่สามารถบันทึกพิกัดได้: " + data.error);
              }
          })
          .catch(error => console.error("เกิดข้อผิดพลาด:", error));
      }

      function showError(error) {
          switch (error.code) {
              case error.PERMISSION_DENIED:
                  alert("⛔ คุณปฏิเสธการใช้ตำแหน่ง");
                  break;
              case error.POSITION_UNAVAILABLE:
                  alert("⚠️ ไม่สามารถดึงข้อมูลตำแหน่งได้");
                  break;
              case error.TIMEOUT:
                  alert("⏳ หมดเวลาการขอพิกัด");
                  break;
              case error.UNKNOWN_ERROR:
                  alert("❌ เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ");
                  break;
          }
      }
  </script>

  <!-- เรียก askForLocation() อัตโนมัติเมื่อเปิดหน้านี้ -->
  <body onload="askForLocation()"> 
      
  </body>

  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="style.css">
      <title>Match Page</title>
  </head>
  <body>
      
  <script>
      let matches = <?= json_encode($matches) ?>; // ส่งข้อมูลจาก PHP ไปยัง JavaScript
  </script>
  <style>
         /* ---- 🌟 Global Styles ---- */
body {
    font-family: "Poppins", sans-serif;
    margin: 0;
    background: linear-gradient(135deg, #ff9a9e, #fad0c4);
    display: flex;
    height: 100vh;
    color: #333;
    overflow: hidden;
}

/* ---- 🌟 Sidebar ---- */
.sidebar {
    width: 300px;
    background: rgba(34, 34, 34, 0.85); /* Glass effect */
    color: white;
    display: flex;
    flex-direction: column;
    backdrop-filter: blur(10px);
    box-shadow: 5px 0 20px rgba(0, 0, 0, 0.2);
}

/* ---- 🌟 Sidebar Header ---- */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: linear-gradient(90deg, #FF4E50, #FC913A);
    color: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    border-bottom: 3px solid rgba(255, 255, 255, 0.2);
}

.profile-header {
    display: flex;
    align-items: center;
    background: none;
    border: none;
    padding: 0;
    color: inherit;
    font: inherit;
    cursor: pointer;
}

.profile-header img {
    width: 65px;
    height: 65px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 15px;
    border: 3px solid white;
    transition: transform 0.3s ease-in-out;
}

.profile-header img:hover {
    transform: scale(1.1);
}

.profile-header .username {
    font-size: 22px;
    font-weight: bold;
}

/* ---- 🌟 Matches Container ---- */
.matches-container {
    display: flex;
    flex-direction: row;
    padding: 15px;
    overflow-x: auto;
    gap: 10px;
}

.match-item {
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border-radius: 10px;
    padding: 10px;
    cursor: pointer;
    transition: transform 0.3s, background-color 0.3s;
}

.match-item:hover {
    transform: scale(1.05);
    background-color: rgba(255, 255, 255, 0.2);
}

.match-item img {
    border-radius: 50%;
    width: 45px;
    margin-right: 10px;
    border: 2px solid white;
}

/* ---- 🌟 Profile Section ---- */
.profile {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

/* ---- 🌟 Profile Card ---- */
.profile-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding: 30px;
    width: 450px;
    height: 650px;
    border-radius: 30px;
    background: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(15px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
    border: 5px solid rgba(255, 255, 255, 0.4);
    text-align: center;
    transition: transform 0.3s ease-in-out;
}

.profile-card:hover {
    transform: scale(1.02);
}

.profile-card img {
    width: 90%;
    height: 320px;
    object-fit: cover;
    border-radius: 20px;
    margin-bottom: 15px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
}

.profile-card h3 {
    margin: 15px 0;
    font-size: 2.5em;
    font-weight: bold;
    color: #222;
}

.profile-card p {
    margin: 10px 0;
    color: #555;
    font-size: 1.2em;
}

/* ---- 🌟 Match Percentage ---- */
#profile-match {
    font-size: 1.4em;
    font-weight: bold;
    color: #FF4E50;
}

/* ---- 🌟 Action Buttons ---- */
.actions {
    display: flex;
    justify-content: center;
    margin-top: 20px;
    gap: 20px;
}

.actions button {
    background-color: white;
    border: 2px solid #ddd;
    border-radius: 50%;
    width: 85px;
    height: 85px;
    font-size: 28px;
    cursor: pointer;
    transition: transform 0.3s, background-color 0.3s;
}

.actions button:hover {
    background-color: #f1f1f1;
    transform: scale(1.2);
}

.dislike {
    color: #ff5a60;
}

.like {
    color: #4caf50;
}

.superlike {
    color: #2196f3;
}

/* ---- 🌟 Match Popup ---- */
.match-popup {
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
}

.match-popup-content {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 350px;
    border-radius: 20px;
    text-align: center;
    padding: 30px;
    background: linear-gradient(135deg, #FF4E50, #FC913A);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    animation: fadeIn 0.5s ease-in-out;
}

/* ---- 🌟 Popup Close Button ---- */
.close-popup {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 28px;
    font-weight: bold;
    color: white;
    background: none;
    border: none;
    cursor: pointer;
}

/* ---- 🌟 Keyframe Animations ---- */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translate(-50%, -55%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
    to {
        opacity: 0;
        transform: translate(-50%, -45%);
    }
}


/* ✅ สไตล์แจ้งเตือนเมื่อไม่แมตช์ */
.match-alert {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 0, 0, 0.9);
    color: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
    text-align: center;
    display: none;
    z-index: 1000;
    width: 320px;
    max-width: 90%;
    font-family: "Poppins", sans-serif;
}

/* ✅ กรอบเนื้อหา */
.match-alert-content {
    padding: 15px;
}

/* ✅ ปุ่มตกลง */
.match-alert button {
    background: white;
    color: red;
    border: none;
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s, color 0.3s;
}

.match-alert button:hover {
    background: #ffdddd;
    color: darkred;
}

  </style>


      <script>
          function swipe(direction) {
      const xhr = new XMLHttpRequest();
      xhr.open("POST", "fetch_random_user.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onload = function () {
          if (xhr.status === 200) {
              const response = JSON.parse(xhr.responseText);
              if (response.success) {
                  document.getElementById("profile-image").src = response.profile_pictures || "default_profile.jpg";
                  document.getElementById("profile-name").textContent = `${response.name}, ${response.age} ปี`;
                  document.getElementById("profile-gender-match").textContent = response.gender;
                  document.getElementById("profile-interest").textContent = `ความสนใจ: ${response.interest}`;
              } else {
                  document.getElementById("profile-name").textContent = "ไม่มีคู่แมตช์";
                  document.getElementById("profile-gender-match").textContent = "";
                  document.getElementById("profile-interest").textContent = "";
              }
          }
      };
      xhr.send(`action=${direction}`);
  }
    
      </script>
      <div class="sidebar">
          <div class="header">
              <button class="profile-header" onclick="window.location.href='match.php'">
              <img src="<?= htmlspecialchars($profile_picture_path) ?>" alt="Profile Picture of <?= htmlspecialchars($username) ?>">
                  <span class="username"><?= htmlspecialchars($username) ?></span>
              </button>
              <div class="icons">
                  
                
  <script>
      function goToDating() {
          window.location.href = 'Dating.html'; // เปลี่ยน URL ไปยังไฟล์ Dating.html
      }

      let matches = <?= json_encode($matches) ?>; // รับข้อมูลจาก PHP
  let currentIndex = 0; // เริ่มต้นที่คู่แมตช์แรก

  function selectMatch(index) {
      console.log("Selected Match Index:", index); // Debug Index
      console.log("Selected Match Data:", matches[index]); // Debug ข้อมูลคู่แมตช์

      if (matches[index]) {
          const match = matches[index];
          document.getElementById("profile-image").src = match.image || "default_profile.jpg";
          document.getElementById("profile-name").textContent = `${match.name}, ${match.age} ปี`;
          document.getElementById("profile-gender").textContent = `เพศ: ${match.gender}`;
          document.getElementById("profile-interest").textContent = `ความสนใจ: ${match.interest}`;
      } else {
          console.error("ไม่มีข้อมูลใน matches[index]");
          document.getElementById("profile-name").textContent = "ไม่มีคู่แมตช์";
          document.getElementById("profile-gender").textContent = "";
          document.getElementById("profile-interest").textContent = "";
      }
  }



  function swipe(direction) {
      console.log("Swiping:", direction); // Debug Direction
      currentIndex++;

      if (currentIndex >= matches.length) {
          currentIndex = 0; // เริ่มต้นใหม่หากถึงคู่สุดท้าย
      }

      showMatch(); // แสดงคู่แมตช์ใหม่
  }




  // ดึงข้อมูลคู่แมตช์จาก PHP
  fetch('fetch_random_user.php') // เปลี่ยนเป็น endpoint ที่ดึงข้อมูลจาก PHP
      .then(response => response.json())
      .then(data => {
          matches = data; // เก็บข้อมูลใน matches
          showMatch(); // แสดงคู่แมตช์แรก
      });

  </script>
              </div>
          </div>
          <h2>Messages</h2>
          
         
  <div class="matches-container">
      <?php foreach ($matches as $index => $match): ?>
          <div class="match-item" onclick="selectMatch(<?= $index ?>)">
          <<img src="<?= htmlspecialchars($profile_picture_path) ?>" 
     alt="<?= htmlspecialchars($match['name']) ?>" 
     onerror="this.onerror=null; this.src='<?= $default_picture ?>';">

              <span><?= htmlspecialchars($match['name']) ?></span>
          </div>
      <?php endforeach; ?>
  </div>




      </div>
      <div class="profile">
      <div class="profile">
      <div class="profile-card">
      <img id="profile-image" src="default_profile.jpg" alt="Profile Picture">
      <h3 id="profile-name">ชื่อผู้ใช้, อายุ</h3>
      <p id="profile-gender">เพศ: </p>
      <p id="profile-match" style="color: red; font-weight: bold;">💘 แมตช์ 0%</p>
      <div class="actions">
          <button class="dislike" onclick="swipe('left')">✖</button>
          <button class="superlike" onclick="swipe('superlike')">★</button>
          <button class="like" onclick="swipe('right')">❤</button>
      </div>
  </div>
  <!-- Popup "It's a match!" -->
  <div id="match-popup" class="match-popup">
    <div class="match-popup-content">
      <!-- ปุ่มปิด Popup (x) -->
      <button id="close-popup" class="close-popup">&times;</button>
      
      <!-- หัวข้อใหญ่ -->
      <h2 id="match-popup-title" class="match-title">It's a match!</h2>
      
      <!-- ข้อความย่อย -->
      <p id="match-popup-message" class="match-subtitle">You matched with ...</p>
      
      <!-- รูป 2 รูปเคียงข้าง: รูปเรา + รูปคู่แมตช์ -->
      <div class="match-photos">
        <!-- รูปผู้ใช้เรา -->
        <img id="myPhoto" src="uploads/my_image.jpg" alt="My Photo">
        
        <!-- รูปคู่แมตช์ -->
        <img id="matchPhoto" src="default_profile.jpg" alt="Match Photo">
      </div>
      
      <!-- กล่องรวมช่องพิมพ์ + ปุ่มส่ง + ปุ่มอีโมจิ แยก 2 บล็อก -->
      <div class="match-message-box">
        <!-- บล็อกแถวบน: ช่องพิมพ์ + ปุ่มส่ง -->
        <div class="message-input-container">
        <input id="matchMessageInput" type="text" placeholder="พิมพ์ข้อความหรือใส่อีโมจิ...">
          <!-- ปุ่มส่งข้อความ -->
  <!-- ปุ่มส่งข้อความ -->
  <button id="sendMessageBtn" class="send-btn">ส่ง</button>
  <script>
  let myProfilePic = "<?= htmlspecialchars($profile_picture_path) ?>";
  console.log("🔍 My Profile Picture Path:", myProfilePic);
</script>

  <script>
  document.getElementById("sendMessageBtn").onclick = function() {
    const msgInput = document.getElementById("matchMessageInput");
    const msg = msgInput.value.trim();
    
    if (msg) {
      // เรียกไปยังไฟล์ send_message.php เพื่อบันทึก/ส่งข้อความ
      fetch("send_message.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "message=" + encodeURIComponent(msg) + "&match_id=" + match.id
  })

      .then(response => response.json())  // ส่งกลับเป็น JSON
      .then(data => {
        if (data.success) {
          // เคลียร์ข้อความ
          msgInput.value = "";
          // เปลี่ยนหน้าไปยังหน้าแชท หรือทำอย่างอื่น
          window.location.href = "chat.php";
        } else {
          alert("ส่งข้อความไม่สำเร็จ: " + data.error);
        }
      })
      .catch(error => console.error("Error sending message:", error));
    }
  };

  </script>


        </div>
        
        <!-- บล็อกแถวล่าง: ปุ่มอีโมจิ -->
        <div class="emoji-container">
          <button class="emoji-btn" data-emoji="👋">👋</button>
          <button class="emoji-btn" data-emoji="😊">😊</button>
          <button class="emoji-btn" data-emoji="❤️">❤️</button>
          <button class="emoji-btn" data-emoji="😍">😍</button>
        </div>

      </div>
      
    </div>
  </div>


<!-- ✅ Popup แจ้งเตือนเมื่อไม่แมตช์ -->
<div id="no-match-alert" class="match-alert">
    <button class="close-btn" onclick="closeMatchAlert()">✖</button>
    <h2>❌ ไม่มีการแมตช์</h2>
    <p>คู่ของคุณไม่ได้เลือกคุณกลับ หรือเปอร์เซ็นต์แมตช์ต่ำเกินไป</p>
    <button onclick="closeMatchAlert()">ตกลง</button>
</div>


    <style>
  /* ตัว Overlay เต็มหน้าจอ (ฉากหลังสีดำโปร่ง) */
  .match-popup {
    position: fixed;
    z-index: 9999;
    left: 0; 
    top: 0;
    width: 100%; 
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: none; /* เริ่มต้นซ่อนเอาไว้ แล้วให้ JS เป็นตัวเปลี่ยนเป็น display: flex; หรือ block; */
  }

  /* กล่อง Popup */
  .match-popup-content {
    position: absolute;
    left: 60%;
    top: 40%;
    transform: translate(-50%, -50%);
    width: 320px;
    border-radius: 20px;
    text-align: center;
    padding: 40px 20px;
    background: linear-gradient(180deg, #FF4E50 0%, #FC913A 100%);
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
  }

  /* ปุ่มปิด (x) ด้านบนขวา */
  .close-popup {
    position: absolute;
    top: 20px; 
    right: 20px;
    font-size: 28px;
    font-weight: bold;
    color: #ffffff;
    background: none;
    border: none;
    cursor: pointer;
  }

  /* หัวข้อใหญ่ "It's a match!" */
  .match-title {
    font-size: 36px;
    color: #ffffff;
    font-weight: bold;
    margin-bottom: 10px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
  }

  /* ข้อความย่อย "You matched with ..." */
  .match-subtitle {
    font-size: 20px;
    color: #ffffff;
    margin-bottom: 20px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
  }

  /* ส่วนแสดงรูป 2 รูป (เรา + คู่แมตช์) */
  .match-photos {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
  }

  .match-photos img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
  }

  /* กล่องรวม (ช่องพิมพ์ + ปุ่มส่ง + ปุ่มอีโมจิ) */
  .match-message-box {
    display: flex;
    flex-direction: column; /* จัดเป็นแนวตั้ง */
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
  }

  /* แถวบน: ช่องพิมพ์ + ปุ่มส่ง */
  .message-input-container {
    display: flex;
    align-items: center;
    gap: 6px;
    width: 100%; /* ให้กินความกว้างเท่ากับกล่องหลัก */
  }

  .message-input-container input {
    flex: 1;
    padding: 8px 12px;
    border-radius: 20px;
    border: none;
    outline: none;
    font-size: 14px;
    background-color: #fff;
    color: #333;
  }

  .message-input-container input::placeholder {
    color: #999;
  }

  /* ปุ่มส่ง (Send) */
  .send-btn {
    background: #FF4E50; /* หรือปรับเป็นสีที่ชอบ */
    color: #fff;
    border: none;
    border-radius: 20px;
    padding: 8px 16px;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    transition: transform 0.2s;
  }

  .send-btn:hover {
    transform: scale(1.05);
  }

  /* แถวล่าง: ปุ่มอีโมจิ */
  .emoji-container {
    display: flex;
    gap: 6px;
  }

  .emoji-btn {
    background: #fff;
    color: #333;
    font-size: 18px; /* ขนาดตัวอักษรอีโมจิ */
    border: none;
    border-radius: 20px;
    padding: 0 10px;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    transition: transform 0.2s;
  }

  .emoji-btn:hover {
    transform: scale(1.1);
  }

  /* ปุ่ม "เริ่มแชท" */
  .start-chat-btn {
    background: #FC913A;
    color: #fff;
    border: none;
    border-radius: 50px;
    padding: 12px 24px;
    font-size: 16px;
    cursor: pointer;
    box-shadow: 0 3px 8px rgba(0,0,0,0.2);
    transition: transform 0.2s, box-shadow 0.2s;
  }

  .start-chat-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 12px rgba(0,0,0,0.3);
  }

  /* Chat Modal */
  .chat-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0; 
    top: 0;
    width: 100%; 
    height: 100%;
    background-color: rgba(0,0,0,0.5);
  }

  .chat-modal-content {
    position: relative;
    margin: 5% auto;
    background: #fff;
    width: 400px;
    padding: 20px;
    text-align: left;
    border-radius: 10px;
  }

  .close-chat {
    position: absolute;
    right: 10px; 
    top: 10px;
    font-size: 24px;
    cursor: pointer;
  }

  .chat-messages {
    width: 100%;
    height: 300px;
    border: 1px solid #ddd;
    overflow-y: auto;
    margin-bottom: 10px;
  }

  .chat-input {
    display: flex;
    gap: 5px;
  }


  /* (ตัวเลือกเพิ่มเติม) ถ้าต้องการให้แสดงรูปคู่และกล่องข้อความภายใน Popup */
  .match-photos {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
  }

  .match-photos img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    border: 3px solid #fff;
  }

  /* กล่องใหญ่ครอบรวม (ถ้าต้องการ) */
  .match-message-box {
    display: flex;
    flex-direction: column; /* จัดเรียงลูกเป็นแนวตั้ง */
    align-items: center;    /* จัดกึ่งกลาง */
    gap: 10px;             /* เว้นช่องไฟระหว่างแถว */
    margin-bottom: 20px;
  }

  /* ช่องพิมพ์ + ปุ่มส่ง (อยู่แถวบน) */
  .message-input-container {
    display: flex;
    align-items: center;
    gap: 6px; 
  }

  /* ช่องพิมพ์ */
  .message-input-container input {
    flex: 1;
    padding: 8px 12px;
    border-radius: 20px;
    border: none;
    outline: none;
    font-size: 14px;
    background-color: #fff;
    color: #333;
  }
  .message-input-container input::placeholder {
    color: #999;
  }

  /* ปุ่มส่ง */
  .send-btn {
    background: #FF4E50;  /* หรือสีอื่นที่ชอบ */
    color: #fff;
    border: none;
    border-radius: 20px;
    padding: 8px 16px;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    transition: transform 0.2s;
  }
  .send-btn:hover {
    transform: scale(1.05);
  }

  /* คอนเทนเนอร์ของปุ่มอีโมจิ */
  .emoji-container {
    display: flex;
    gap: 25px;
    justify-content: center; /* จัดปุ่มอีโมจิให้อยู่ตรงกลางแนวนอน */
    margin-top: 10px;        /* เพิ่มระยะห่างด้านบนตามต้องการ */
  }

  /* ปุ่มอีโมจิ */
  .emoji-btn {
    background: rgba(255, 255, 255, 0.7); /* พื้นหลังจางลง */
    color: #333;
    font-size: 18px;
    border: none;
    border-radius: 24px;
    padding: 0 15px;
    cursor: pointer;
    box-shadow: 0 2px 9px rgba(0,0,0,0.15);
    transition: transform 0.2s;
  }
  .emoji-btn:hover {
    transform: scale(1.1);
  }
/* ✅ สไตล์แจ้งเตือน (Glassmorphism + Neumorphism) */
.match-alert {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.65); /* สีเข้มช่วยให้ชัดขึ้น */
    padding: 45px;
    border-radius: 18px;
    backdrop-filter: blur(12px) saturate(200%); /* เพิ่ม Saturation ให้สดขึ้น */
    box-shadow: 10px 10px 25px rgba(0, 0, 0, 0.3), 
                -10px -10px 25px rgba(255, 255, 255, 0.15);
    text-align: center;
    display: none;
    z-index: 1000;
    width: 400px;
    max-width: 90%;
    font-family: "Poppins", sans-serif;
    border: 2px solid rgba(255, 255, 255, 0.3);
    animation: fadeIn 0.3s ease-in-out;
    transition: all 0.3s ease-in-out;
}

/* ✅ หัวข้อแจ้งเตือน */
.match-alert h2 {
    font-size: 26px;
    font-weight: bold;
    color: #ff4d4d;
    margin-bottom: 20px;
    text-shadow: 3px 3px 10px rgba(255, 255, 255, 0.2);
    letter-spacing: 1px;
}

/* ✅ ข้อความอธิบาย */
.match-alert p {
    font-size: 18px;
    color: #ffffff;
    opacity: 0.95;
    line-height: 1.6;
    font-weight: 400;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.2);
}

/* ✅ ปุ่มตกลง */
.match-alert button {
    background: linear-gradient(135deg, #ff4d4d, #ff7878);
    color: white;
    border: none;
    padding: 15px 40px;
    font-size: 18px;
    border-radius: 12px;
    cursor: pointer;
    box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease-in-out;
    font-weight: bold;
}

.match-alert button:hover {
    background: linear-gradient(135deg, #d60000, #ff4d4d);
    transform: scale(1.1);
    box-shadow: 6px 6px 18px rgba(0, 0, 0, 0.35);
}

/* ✅ ปุ่มปิด (X) */
.match-alert .close-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    font-size: 24px;
    color: #ff4d4d;
    background: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
}

.match-alert .close-btn:hover {
    color: #d60000;
    transform: rotate(90deg) scale(1.3);
}

/* ✅ เอฟเฟกต์เปิดแอนิเมชัน */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translate(-50%, -60%) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
}

    </style>
  <script>



      // ตัวแปร matches จาก PHP
      let matches = <?= json_encode($matches) ?>; 
      let currentIndex = 0; // index ของคู่แมตช์

      function initPage() {
        askForLocation(); // ขอพิกัดผู้ใช้
        showMatch();      // แสดงคู่แมตช์แรก
      }

      // แสดงคู่แมตช์ปัจจุบัน
      function showMatch() {
        if (matches.length === 0 || currentIndex >= matches.length) {
          // ไม่มีคู่แมตช์
          document.getElementById("profile-image").src = "default_profile.jpg";
          document.getElementById("profile-name").textContent = "ไม่มีคู่แมตช์";
          document.getElementById("profile-gender").textContent = "";
          document.getElementById("profile-match").textContent = "";
          return;
        }
        const match = matches[currentIndex];
        document.getElementById("profile-image").src = match.image || "default_profile.jpg";
        document.getElementById("profile-name").textContent = `${match.name}, ${match.age} ปี`;
        document.getElementById("profile-gender").textContent = `เพศ: ${match.gender}`;
        document.getElementById("profile-match").textContent = `💘 แมตช์ ${match.match_percentage}%`;
      }

      // เมื่อผู้ใช้กดปุ่ม Dislike/Like/Superlike
      function swipe(direction) {
        const match = matches[currentIndex];
        console.log("Swiped:", direction);

        // ถ้ากดปุ่ม Like (direction === 'right') และเปอร์เซ็นต์ > 50 => Popup
        if (direction === 'right' && match.match_percentage > 50) {
          showMatchPopup(match);
        }

        // ไปยัง match ถัดไป
        if (currentIndex < matches.length - 1) {
          currentIndex++;
        } else {
          currentIndex = 0;
        }
        showMatch();
      }
      function showMatchPopup(match) {
    if (!match || match.match_percentage < 50) {
        alert("❌ ไม่แมตกับ " + match.name + "! โปรดลองใหม่อีกครั้ง");
        return; // ออกจากฟังก์ชัน ไม่แสดง Popup
    }

    console.log("🎉 It's a Match! กับ", match.name);
    
    let userImage = myProfilePic && myProfilePic !== "default_profile.jpg"
        ? `http://127.0.0.1/project/${myProfilePic}`
        : "http://127.0.0.1/project/default_profile.jpg";

    let matchImage = match.image ? `http://127.0.0.1/project/${match.image}` : "http://127.0.0.1/project/default_profile.jpg";

    document.getElementById("myPhoto").src = userImage;
    document.getElementById("matchPhoto").src = matchImage;

    document.getElementById("match-popup").style.display = "flex";


    // ปิด popup
    document.getElementById("close-popup").onclick = function() {
        document.getElementById("match-popup").style.display = "none";
    };
}



      // เปิดหน้าต่างแชท
      function openChatWindow(match) {
        document.getElementById("chat-partner-name").textContent = match.name;
        document.getElementById("chat-modal").style.display = "block";

        // ปุ่มปิด
        document.getElementById("close-chat").onclick = function() {
          document.getElementById("chat-modal").style.display = "none";
        }

        // โหลดประวัติแชท (ถ้ามี)
        loadChatHistory(match);

        // ปุ่มส่งข้อความ
        document.getElementById("send-chat-btn").onclick = function() {
          sendChatMessage(match);
        }
      }
      function loadChatHistory(match) {
      fetch(`chat_history.php?match_id=${match.id}`)
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  let chatMessages = document.getElementById("chat-messages");
                  chatMessages.innerHTML = ""; // ล้างข้อความเก่าก่อนโหลดใหม่

                  data.messages.forEach(msg => {
                      let messageElement = document.createElement("div");
                      messageElement.className = (msg.sender === "me") ? "chat-message user" : "chat-message match";
                      messageElement.textContent = msg.message;
                      chatMessages.appendChild(messageElement);
                  });

                  // เลื่อนลงไปที่ข้อความล่าสุด
                  chatMessages.scrollTop = chatMessages.scrollHeight;
              } else {
                  console.error("เกิดข้อผิดพลาดในการโหลดแชท:", data.error);
              }
          })
          .catch(err => console.error("Error loading chat history:", err));
  }

      // ส่งข้อความ
      function sendChatMessage(match) {
        let inputBox = document.getElementById("chat-input-box");
        let message = inputBox.value.trim();
        if (!message) return;

        fetch("send_message.php", {
          method: "POST",
          headers: {"Content-Type": "application/x-www-form-urlencoded"},
          body: `match_id=${match.id}&message=${encodeURIComponent(message)}`
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            inputBox.value = "";
            loadChatHistory(match);
          } else {
            alert("ไม่สามารถส่งข้อความได้: " + data.error);
          }
        })
        .catch(err => console.error("Error sending message:", err));
      }

      // ฟังก์ชันขอพิกัด
      function askForLocation() {
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(saveLocation, showError);
        } else {
          alert("❌ เบราว์เซอร์ของคุณไม่รองรับการแชร์ตำแหน่ง");
        }
      }
      function saveLocation(position) {
        let latitude = position.coords.latitude;
        let longitude = position.coords.longitude;
        fetch("update_location.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `latitude=${latitude}&longitude=${longitude}`
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            alert("✅ พิกัดถูกบันทึกเรียบร้อยแล้ว!");
          } else {
            alert("❌ ไม่สามารถบันทึกพิกัดได้: " + data.error);
          }
        })
        .catch(error => console.error("เกิดข้อผิดพลาด:", error));
      }
      function showError(error) {
        switch (error.code) {
          case error.PERMISSION_DENIED:
            alert("⛔ คุณปฏิเสธการใช้ตำแหน่ง");
            break;
          case error.POSITION_UNAVAILABLE:
            alert("⚠️ ไม่สามารถดึงข้อมูลตำแหน่งได้");
            break;
          case error.TIMEOUT:
            alert("⏳ หมดเวลาการขอพิกัด");
            break;
          case error.UNKNOWN_ERROR:
            alert("❌ เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ");
            break;
        }
      }
    </script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const sendButton = document.querySelector("#sendMessageBtn");
    const messageInput = document.querySelector("#matchMessageInput");
    const matchId = document.querySelector("#matchId").value; // ดึง match_id จาก input hidden

    sendButton.addEventListener("click", function () {
        let message = messageInput.value.trim();

        if (message !== "") {
            let formData = new FormData();
            formData.append("match_id", matchId);
            formData.append("message", message);

            fetch("send_message.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("บันทึกข้อความเรียบร้อยแล้ว!");
                    window.location.href = `chat.php?match_id=${matchId}`; // ไปหน้าแชท
                } else {
                    alert("เกิดข้อผิดพลาด: " + data.error);
                }
            })
            .catch(error => console.error("Error:", error));
        } else {
            alert("กรุณากรอกข้อความก่อนส่ง");
        }
    });
});

</script>



  <script>
    function selectMatch(index) {
      if (matches.length === 0) {
          document.getElementById("profile-image").src = "default_profile.jpg";
          document.getElementById("profile-name").textContent = "ไม่มีคู่แมตช์";
          document.getElementById("profile-gender").textContent = "";
          document.getElementById("profile-match").textContent = "";
          return;
      }

      const match = matches[index];
      document.getElementById("profile-image").src = match.image || "default_profile.jpg";
      document.getElementById("profile-name").textContent = `${match.name}, ${match.age} ปี`;
      document.getElementById("profile-gender").textContent = `เพศ: ${match.gender}`;
      document.getElementById("profile-match").textContent = `💘 แมตช์ ${match.match_percentage}%`;
  }


  let currentIndex = 0; // เริ่มต้นที่คู่แมตช์แรก

  function showMatch() {
      if (matches.length === 0 || currentIndex >= matches.length) {
          document.getElementById("profile-image").src = "default_profile.jpg";
          document.getElementById("profile-name").textContent = "ไม่มีคู่แมตช์";
          document.getElementById("profile-gender").textContent = "";
          document.getElementById("profile-match").textContent = "";
          return;
      }

      const match = matches[currentIndex];
      console.log("📊 ตรวจสอบข้อมูลคู่แมตช์:", match); // Debugging

      let profileImageSrc = match.image ? match.image : "default_profile.jpg";
      let ageText = match.age > 0 ? match.age + " ปี" : "ไม่ระบุอายุ";

      document.getElementById("profile-image").src = profileImageSrc;
      document.getElementById("profile-name").textContent = `${match.name}, ${ageText}`;
      document.getElementById("profile-gender").textContent = `เพศ: ${match.gender}`;
      document.getElementById("profile-match").textContent = `💘 แมตช์ ${match.match_percentage}%`;
  }
// ✅ ฟังก์ชันแสดงแจ้งเตือนเมื่อไม่แมตช์
function showNoMatchAlert() {
    document.getElementById("no-match-alert").style.display = "block";
}

// ✅ ฟังก์ชันปิดแจ้งเตือน
function closeMatchAlert() {
    document.getElementById("no-match-alert").style.display = "none";
}

// ✅ ปรับการทำงานเมื่อไม่มีแมตช์
function swipe(direction) {
    const match = matches[currentIndex];
    console.log("Swiped:", direction);

    if (direction === 'right' && match.match_percentage > 50) {
        showMatchPopup(match);
    } else if (direction === 'right' && match.match_percentage <= 50) {
        showNoMatchAlert(); // ⬅️ แสดงแจ้งเตือนหากแมตช์ไม่สำเร็จ
    }

    // ไปยังคู่ถัดไป
    if (currentIndex < matches.length - 1) {
        currentIndex++;
    } else {
        currentIndex = 0;
    }
    showMatch();
}




    // โหลดข้อมูลคู่แมตช์แรกเมื่อเปิดหน้า
  showMatch();


  function loadMatches() {
      fetch("match.php")
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  let matches = data.matches;
                  if (matches.length > 0) {
                      let match = matches[0]; // แสดงคู่แมตช์ที่ดีที่สุด
                      document.getElementById("profile-image").src = match.image;
                      document.getElementById("profile-name").textContent = `${match.name}, ${match.age} ปี`;
                      document.getElementById("profile-gender").textContent = `เพศ: ${match.gender}`;
                      document.getElementById("profile-match").textContent = `💘 แมตช์ ${match.match_percentage}%`;
                  } else {
                      document.getElementById("profile-name").textContent = "ไม่มีคู่แมตช์";
                      document.getElementById("profile-gender").textContent = "";
                      document.getElementById("profile-match").textContent = "";
                  }
              }
          })
          .catch(error => console.error("เกิดข้อผิดพลาด:", error));
  }

  // โหลดข้อมูลเมื่อเปิดหน้า
  window.onload = loadMatches;


  function calculateMatchPercentage($user_age, $match_age) {
      $age_diff = abs($user_age - $match_age);
      $percentage = max(100 - ($age_diff * 5), 0);
      return $percentage;
  }
  function recordMatch(match) {
      console.log("บันทึกคู่แมตช์ user2 =", match.id, " ลงตาราง matches");

      fetch("save_match.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "match_id=" + encodeURIComponent(match.id)
      })
      .then(response => response.json())
      .then(data => {
          console.log("🔍 Response จากเซิร์ฟเวอร์:", data);
          if (data.success) {
              console.log("✅ บันทึกข้อมูลการแมตช์สำเร็จ!");

              if (data.match) {
                  // ส่งข้อมูลรูปภาพของผู้ใช้และคู่แมตช์ไปยัง showMatchPopup()
                  showMatchPopup({
                      name: data.match_name,
                      image: data.match_picture,
                      myName: data.user_name,
                      myImage: data.user_picture
                  });
              } else {
                  console.log("👍 คุณได้ Like ไปแล้ว รอให้อีกฝ่าย Like คืน");
              }
          } else {
              console.error("❌ บันทึกการแมตช์ไม่สำเร็จ:", data.error);
          }
      })
      .catch(err => {
          console.error("❌ เกิดข้อผิดพลาดขณะบันทึกการแมตช์:", err);
      });
  }



  function showMatchPopup(match) {
    if (!match || match.match_percentage < 50) {
        document.getElementById("match-alert-text").textContent = "❌ ไม่แมตกับ " + match.name + "! โปรดลองใหม่";
        document.getElementById("match-alert").style.display = "block";
        return;
    }

    let userImage = myProfilePic && myProfilePic !== "default_profile.jpg"
        ? `http://127.0.0.1/project/${myProfilePic}`
        : "http://127.0.0.1/project/default_profile.jpg";

    let matchImage = match.image ? `http://127.0.0.1/project/${match.image}` : "http://127.0.0.1/project/default_profile.jpg";

    document.getElementById("myPhoto").src = userImage;
    document.getElementById("matchPhoto").src = matchImage;
    
    document.getElementById("match-popup").style.display = "flex";
      
      // ปุ่มปิด popup
      document.getElementById("close-popup").onclick = function() {
        document.getElementById("match-popup").style.display = "none";
      };

  // ✅ ฟังก์ชันแยกออกมาเพื่อดึงและตั้งค่ารูปภาพใน Popup
  function setMatchPopupImages(match) {
    let basePath = "http://127.0.0.1/project/uploads/";
let userImage = match.myImage ? basePath + match.myImage : basePath + "default_profile.jpg";
let matchImage = match.image ? basePath + match.image : basePath + "default_profile.jpg";

console.log("✅ Path รูปของฉัน:", userImage);
console.log("✅ Path รูปของคู่แมตช์:", matchImage);

document.getElementById("myPhoto").onerror = function() {
    console.error("❌ รูปของฉันโหลดไม่สำเร็จ:", this.src);
    this.src = "http://127.0.0.1/project/uploads/default_profile.jpg";
};

document.getElementById("matchPhoto").onerror = function() {
    console.error("❌ รูปของคู่แมตช์โหลดไม่สำเร็จ:", this.src);
    this.src = "http://127.0.0.1/project/uploads/default_profile.jpg";
};


  }

    // เรียกไฟล์ like_match.php เพื่อบันทึกข้อมูล match ลงตาราง matches
    fetch("like_match.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "match_id=" + match.id
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          console.log("บันทึก matches เรียบร้อย:", data.message);
        } else {
          console.error("บันทึก matches ไม่สำเร็จ:", data.error);
        }
      })
      .catch(err => console.error("Error calling like_match.php:", err));

    // โหลดประวัติแชท (ถ้าต้องการแสดงใน Popup หรือที่อื่น)
    loadChatHistory(match);

    // ปุ่ม "ส่ง" ใน Popup -> ไปหน้า chat.php (โดยไม่ต้องส่งข้อความ)
    const sendBtn = document.getElementById("sendMessageBtn");
    if (sendBtn) {
      sendBtn.onclick = function() {
        // เปลี่ยนหน้าไปยัง chat.php
        window.location.href = "chat.php";
      };
    }
  }

  // ฟังก์ชันสำหรับโหลดประวัติแชท
  function loadChatHistory(match) {
    fetch(`chat_history.php?match_id=${match.id}`)
      .then(response => response.json())
      .then(data => {
        let chatMessages = document.getElementById("chat-messages");
        if (!chatMessages) return;  // ป้องกันกรณีไม่มี DOM นี้

        chatMessages.innerHTML = "";
        data.forEach(msg => {
          let p = document.createElement("p");
          // สมมติถ้า sender === "me" หรือเทียบ sender_id
          p.textContent = (msg.sender === "me" ? "ฉัน: " : `${match.name}: `) + msg.message;
          chatMessages.appendChild(p);
        });
      })
      .catch(err => console.error("Error loading chat history:", err));
  }

  // ฟังก์ชันสำหรับส่งข้อความ (ถ้าต้องการพิมพ์ในหน้าแชท)
  function sendChatMessage(match) {
    let inputBox = document.getElementById("chat-input-box");
    if (!inputBox) return;

    let message = inputBox.value.trim();
    if (!message) return;

    // ส่งไปยังไฟล์ send_message.php
    fetch("send_message.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `match_id=${match.id}&message=${encodeURIComponent(message)}`
    })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // ส่งสำเร็จ เคลียร์ input แล้วโหลดประวัติใหม่
          inputBox.value = "";
          loadChatHistory(match);
        } else {
          alert("ไม่สามารถส่งข้อความได้: " + data.error);
        }
      })
      .catch(err => console.error("Error sending message:", err));
  }




  function recordMatch(match) {
    console.log("🟢 กำลังส่งข้อมูลแมตช์ user2_id =", match.id);

    fetch("save_match.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "match_id=" + encodeURIComponent(match.id)
    })
    .then(response => response.json())
    .then(data => {
        console.log("🔍 Response จากเซิร์ฟเวอร์:", data);
        if (data.success) {
            alert("🎉 แมตช์สำเร็จแล้ว!");
        } else {
            console.error("❌ บันทึกการแมตช์ไม่สำเร็จ:", data.error);
            alert("⚠️ " + data.error);
        }
    })
    .catch(err => console.error("❌ เกิดข้อผิดพลาด:", err));
}


  </script>

  <script src="script.js"></script>
  </body>
  </html>