<?php
session_start();
require_once 'connect.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['email'])) {
    header("Location: login.php?error=คุณต้องเข้าสู่ระบบ");
    exit();
}

$email = $_SESSION['email'];
$conn = getDatabaseConnection();

// ✅ ดึงข้อมูลผู้ใช้
$sql_user = "SELECT id, name, profile_pictures FROM profile1 WHERE email = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $email);
$stmt_user->execute();
$res_user = $stmt_user->get_result();
$user = $res_user->fetch_assoc();
$user_id = $user['id'];
$user_name = $user['name'];
$userProfileImage = !empty($user['profile_pictures']) ? "uploads/" . $user['profile_pictures'] : "uploads/default_profile.jpg";
$stmt_user->close();

// ✅ ดึงคู่แมตช์ทั้งหมด พร้อมจำนวนข้อความที่ยังไม่อ่าน
$sql_matches = "SELECT m.id AS match_id, 
                       CASE WHEN m.user1 = ? THEN m.user2 ELSE m.user1 END AS partner_id, 
                       p.name, 
                       p.profile_pictures,
                       (SELECT COUNT(*) FROM messages WHERE match_id = m.id AND sender_id != ? AND is_read = 0) AS unread_count
                FROM matches m
                JOIN profile1 p ON (p.id = CASE WHEN m.user1 = ? THEN m.user2 ELSE m.user1 END)
                WHERE (m.user1 = ? OR m.user2 = ?)";

$stmt_matches = $conn->prepare($sql_matches);
$stmt_matches->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
$stmt_matches->execute();
$res_matches = $stmt_matches->get_result();

$matches = [];
while ($row = $res_matches->fetch_assoc()) {
    $matches[] = [
        'match_id' => $row['match_id'],
        'partner_id' => $row['partner_id'],
        'name' => $row['name'],
        'profile_pictures' => !empty($row['profile_pictures']) ? "uploads/" . $row['profile_pictures'] : "uploads/default_profile.jpg",
        'unread_count' => $row['unread_count']
    ];
}

$stmt_matches->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
    font-family: 'Arial', sans-serif;
    background: #121212;
    margin: 0;
    padding: 0;
    color: white;
}

.chat-container {
    display: flex;
    height: 100vh;
}
.chat-sidebar {
    width: 320px;
    background: #1c1c1c;
    padding: 20px;
    overflow-y: auto;
    color: white;
}

.chat-sidebar h2 {
    font-size: 18px;
    color: #ff5a60;
    margin-bottom: 10px;
}

.match-item {
    padding: 10px;
    border-bottom: 1px solid #444;
    cursor: pointer;
    display: flex;
    align-items: center;
    position: relative;
}

.match-item img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
    object-fit: cover;
    border: 2px solid #ff5a60;
}

.match-item .unread-badge {
    background: red;
    color: white;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 12px;
    position: absolute;
    right: 10px;
    top: 10px;
}

.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #252525;
}

.chat-header {
    padding: 15px;
    background: #ff5a60;
    display: flex;
    align-items: center;
    font-size: 18px;
    font-weight: bold;
}

.chat-box {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
}

.chat-input {
    display: flex;
    padding: 15px;
    background: #333;
    border-top: 1px solid #444;
}

.chat-input input {
    flex: 1;
    padding: 10px;
    border-radius: 20px;
    border: none;
    font-size: 16px;
    background: #444;
    color: white;
}

.chat-input input:focus {
    outline: none;
    background: #555;
}

.chat-input button {
    margin-left: 10px;
    padding: 10px 20px;
    border-radius: 20px;
    background: #ff5a60;
    color: white;
    border: none;
    font-weight: bold;
    cursor: pointer;
}

.chat-input button:hover {
    background: #e04850;
}

.message {
    padding: 12px 16px;
    margin: 5px;
    border-radius: 18px;
    max-width: 60%;
    word-wrap: break-word;
    font-size: 16px;
    line-height: 1.4;
    display: inline-block;
    position: relative;
}

.sent {
    background-color: #0084ff;
    color: white;
    align-self: flex-end;
    margin-left: auto;
    text-align: right;
}


.received {
    background-color: #e4e6eb;
    color: black;
    align-self: flex-start;
    margin-right: auto;
}

.match-item {
    padding: 12px;
    border-bottom: 1px solid #444;
    cursor: pointer;
    display: flex;
    align-items: center;
    transition: background 0.3s ease;
}

.match-item:hover {
    background: #333;
}

.match-item img {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    margin-right: 10px;
    border: 2px solid #ff5a60;
}

    </style>
</head>
<body>

<div class="chat-container">
<div class="chat-sidebar">
    <h2>คู่แมตช์</h2>
    <?php foreach ($matches as $match): ?>
    <div class="match-item" 
         data-id="<?= $match['match_id'] ?>" 
         data-name="<?= htmlspecialchars($match['name']) ?>">
        <img src="<?= htmlspecialchars($match['profile_pictures']) ?>" alt="<?= htmlspecialchars($match['name']) ?>" class="profile-img">
        <span><?= htmlspecialchars($match['name']) ?></span>
        <span class="unread-badge" style="<?= $match['unread_count'] > 0 ? '' : 'display:none;' ?>">
            <?= $match['unread_count'] ?>
        </span>
    </div>
<?php endforeach; ?>



</div>

    <div class="chat-main">
        <div class="chat-header">
            <span id="chat-partner-name">เลือกคู่แมตช์</span>
        </div>
        <div class="chat-box" id="chat-box"></div>
        <div class="chat-input">
            <input type="text" id="message" placeholder="พิมพ์ข้อความ...">
            <button id="send">ส่ง</button>
        </div>
    </div>
</div>

<script>
var match_id = 0;
var currentUserId = <?= (int)$user_id ?>;

// ✅ ฟังก์ชันโหลดข้อความแชท
function loadMessages() {
    if (match_id === 0) {
        console.warn("⚠️ ยังไม่มี match_id");
        return;
    }

    console.log("📡 กำลังโหลดข้อความสำหรับ match_id:", match_id);

    $.getJSON('load_messages.php', { match_id: match_id }, function(response) {
        console.log("🔍 Response จาก load_messages.php:", response);

        if (response.success) {
            let html = "";
            response.messages.forEach(function(msg) {
                let senderClass = (parseInt(msg.sender_id) === currentUserId) ? "sent" : "received";
                html += `<div class="message ${senderClass}">
                            <span>${msg.message}</span><br>
                            <small>${msg.created_at}</small>
                         </div>`;
            });

            $('#chat-box').html(html);
            $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
        } else {
            console.error("❌ Error จาก load_messages.php:", response.error);
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error("❌ AJAX Error:", textStatus, errorThrown);
    });
}function loadMessages() {
    if (match_id === 0) {
        console.warn("⚠️ ไม่มี match_id ไม่สามารถโหลดข้อความได้");
        return;
    }

    console.log("📡 กำลังโหลดข้อความสำหรับ match_id:", match_id);

    $.getJSON('load_messages.php', { match_id: match_id }, function(response) {
        console.log("🔍 Response จาก load_messages.php:", response);

        if (response.success) {
            let html = "";
            response.messages.forEach(function(msg) {
                let senderClass = (parseInt(msg.sender_id) === currentUserId) ? "sent" : "received";
                html += `<div class="message ${senderClass}">
                            <span>${msg.message}</span><br>
                            <small>${msg.created_at}</small>
                         </div>`;
            });

            $('#chat-box').html(html);
            $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
        } else {
            console.error("❌ Error จาก load_messages.php:", response.error);
        }
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error("❌ AJAX Error:", textStatus, errorThrown);
    });
}

$('.match-item').click(function() {
    match_id = $(this).data('id');
    let partnerName = $(this).data('name');

    console.log("เลือกคู่แชท match_id:", match_id);

    $('#chat-partner-name').text(partnerName);
    loadMessages();
});



$('#send').click(function() {
    let message = $('#message').val().trim();
    if (message === "") return;

    console.log("📤 กำลังส่งข้อความ:", message);

    $.post('send_message.php', { match_id: match_id, message: message }, function(response) {
        console.log("✅ ส่งข้อความสำเร็จ:", response);
        $('#message').val('');
        loadMessages();
    });
});

// ✅ ฟังก์ชันเลือกคู่แชท
function openChat(id, partnerName) {
    match_id = id;
    console.log("🟢 เปิดแชท match_id:", match_id);

    $('#chat-partner-name').text(partnerName);
    loadMessages();

    // ✅ อัปเดตสถานะว่าอ่านข้อความแล้ว
    $.post('update_read_status.php', { match_id: match_id }, function(response) {
        if (response.success) {
            console.log("✅ อัปเดตสถานะอ่านแล้ว");
        }
    });

    // ✅ ซ่อน badge unread
    $(`.match-item[data-id="${match_id}"] .unread-badge`).hide();
}

// ✅ โหลดแชททุก 3 วินาที
setInterval(loadMessages, 3000);


setInterval(function () {
    if (match_id > 0) {
        loadMessages();
    }
}, 3000);



</body>
</html> 