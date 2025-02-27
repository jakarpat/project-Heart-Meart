document.addEventListener("DOMContentLoaded", function () {
    loadMatches(); // โหลดคู่แมตช์เมื่อเปิดหน้า
});

/**
 * โหลดรายชื่อคู่แมตช์และแสดงในแถบซ้าย
 */
function loadMatches() {
    fetch("fetch_matches.php")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let matchesContainer = document.querySelector(".matches-container");
                matchesContainer.innerHTML = ""; // ล้างรายการก่อน

                data.matches.forEach(match => {
                    let matchItem = document.createElement("div");
                    matchItem.className = "match-item";
                    matchItem.onclick = function () {
                        openChatWindow(match);
                    };

                    let img = document.createElement("img");
                    img.src = match.image;
                    img.alt = match.name;

                    let span = document.createElement("span");
                    span.textContent = match.name;

                    matchItem.appendChild(img);
                    matchItem.appendChild(span);
                    matchesContainer.appendChild(matchItem);
                });
            }
        })
        .catch(error => console.error("Error loading matches:", error));
}

/**
 * เปิดหน้าต่างแชทของคู่แมตช์ที่เลือก
 */
function openChatWindow(match) {
    document.getElementById("chat-partner-name").textContent = match.name;
    document.getElementById("chat-modal").style.display = "block";

    // ปุ่มปิดแชท
    document.getElementById("close-chat").onclick = function () {
        document.getElementById("chat-modal").style.display = "none";
    };

    // โหลดประวัติแชท
    loadChatHistory(match.id);

    // ปุ่มส่งข้อความ
    document.getElementById("send-chat-btn").onclick = function () {
        sendChatMessage(match.id);
    };
}

/**
 * โหลดประวัติแชทของคู่แมตช์
 */
function loadChatHistory(match_id) {
    fetch(`fetch_chat.php?match_id=${match_id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let chatMessages = document.getElementById("chat-messages");
                chatMessages.innerHTML = "";
                data.messages.forEach(msg => {
                    let messageElem = document.createElement("p");
                    messageElem.textContent = (msg.sender === "me" ? "ฉัน: " : "คู่แมตช์: ") + msg.message;
                    chatMessages.appendChild(messageElem);
                });
            }
        })
        .catch(err => console.error("Error loading chat history:", err));
}

/**
 * ส่งข้อความแชทไปยังคู่แมตช์
 */
function sendChatMessage(match_id) {
    let inputBox = document.getElementById("matchMessageInput");
    let message = inputBox.value.trim();
    if (!message) return;

    fetch("send_message.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `match_id=${match_id}&message=${encodeURIComponent(message)}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                inputBox.value = "";
                loadChatHistory(match_id); // โหลดประวัติแชทใหม่
            } else {
                alert("ไม่สามารถส่งข้อความได้: " + data.error);
            }
        })
        .catch(err => console.error("Error sending message:", err));
}
