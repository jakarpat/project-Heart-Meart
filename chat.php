<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat System</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #121212;
            color: white;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
        }
        .chat-container {
            display: flex;
            width: 100%;
            height: 100%;
        }
        .chat-sidebar {
            width: 300px;
            background: #1c1c1c;
            padding: 20px;
            overflow-y: auto;
            border-right: 1px solid #444;
        }
        .chat-sidebar h2 {
            color: #ff5a60;
        }
        .match-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #444;
            cursor: pointer;
            transition: background 0.3s;
        }
        .match-item:hover {
            background: #333;
        }
        .match-item img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin-right: 10px;
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
        
        .message {
            padding: 12px;
            border-radius: 18px;
            max-width: 60%;
            word-wrap: break-word;
            font-size: 16px;
            margin: 5px;
            background-color: #e4e6eb;
            color: black;
        }

        .sent {
            background-color: #0084ff;
            color: white;
            align-self: flex-end;
        }
        .received {
            background-color: #e4e6eb;
            color: black;
            align-self: flex-start;
        }
        .chat-input {
            display: flex;
            padding: 10px;
            background: #333;
            border-top: 1px solid #444;
        }
        .chat-input input {
            flex: 1;
            padding: 10px;
            border-radius: 20px;
            border: none;
            background: #444;
            color: white;
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

        .message--owner {
            display: flex;
            justify-content: flex-end; /* Ensures alignment to the right */
        }
    </style>
</head>
<body>
<div class="chat-container">
    <div class="chat-sidebar">
        <h2>คู่แมตช์</h2>
        <div id="match-list"></div>
    </div>
    <div class="chat-main">
        <div class="chat-header" id="chat-partner-name">เลือกคู่แชท</div>
        <div class="chat-box" id="chat-box"></div>
        <div class="chat-input">
            <input type="text" id="message" placeholder="พิมพ์ข้อความ...">
            <button id="send">ส่ง</button>
        </div>
    </div>
</div>

<?php
require_once 'connect.php';
session_start();

$conn = getDatabaseConnection();

// ✅ ดึง user_id จาก Session
$sql_user = "SELECT id FROM profile1 WHERE email = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $_SESSION['email']);
$stmt_user->execute();
$res_user = $stmt_user->get_result();
if ($res_user->num_rows === 0) {
error_log("❌ ไม่พบข้อมูลผู้ใช้");
echo json_encode(["success" => false, "error" => "ไม่พบข้อมูลผู้ใช้"]);
exit();
}
$user = $res_user->fetch_assoc();
$user_id = $user['id'];
?>
<script>
let match_id = 0;
let userId = <?php echo $user_id ?>;

function loadMatches() {
    $.getJSON('load_matches.php',  { user_id: match_id }, function(response) {
        const filteredMatcheUser1 = response.body.filter(match => match.user1 === userId);
        const filteredMatchesUser2 = response.body.filter(match => match.user2 === userId);

        console.log(filteredMatcheUser1);
        console.log(filteredMatchesUser2);
        let html = '';
        filteredMatcheUser1.forEach(function(match) {
            html += `<div class="match-item" data-id="${match.match_id}" data-name="${match.user2_name}">
                        <img src="${match.picture_user2}" alt="${match.user2_name}">
                        <span>${match.user2_name}</span>
                    </div>`;
        });
        filteredMatchesUser2.forEach(function(match) {
            html += `<div class="match-item" data-id="${match.match_id}" data-name="${match.user1_name}">
                        <img src="${match.picture_user1}" alt="${match.picture_user1}">
                        <span>${match.user1_name}</span>
                    </div>`;
        });
        $('#match-list').html(html);
    });
}

function loadMessages() {
    if (match_id === 0) return;
    
    $.getJSON('load_messages.php', { match_id: match_id }, function(response) {
        let html = '';
        response.messages.forEach(function(msg) {
            console.log(msg);
            
            if (userId === msg.sender_id) {
                let senderClass = (msg.sender_id == response.currentUserId) ? "sent" : "received";
                html += `
                <div class="message--owner">
                    <div class="message">${msg.message}</div>
                </div>
                `;
            } else {
                let senderClass = (msg.sender_id == response.currentUserId) ? "sent" : "received";
                html += `<div class="message ${senderClass}">${msg.message}</div>`;
            }
        });
        $('#chat-box').html(html);
        $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
    });
}

$(document).on('click', '.match-item', function() {
    match_id = $(this).data('id');
    console.log(match_id);
    
    $('#chat-partner-name').text($(this).data('name'));
    loadMessages();
});
$('#send').click(function() {
    let message = $('#message').val().trim();
    if (!message) return;
    $.post('send_message.php', { match_id: match_id, message: message }, function() {
        $('#message').val('');
        loadMessages();
    });
});
setInterval(loadMessages, 3000);
loadMatches();
</script>
</body>
</html>
