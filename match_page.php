<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php?error=You must login to access this page.");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Page</title>
    <link rel="stylesheet" href="style.css">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            fetch("fetch_match.php")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById("username").textContent = data.user.name;
                        document.getElementById("profile-pic").src = data.user.profile_pictures || "default_profile.jpg";
                        updateMatchList(data.matches);
                    } else {
                        console.error("⚠️ ไม่พบข้อมูล:", data.message);
                    }
                })
                .catch(error => console.error("❌ โหลดข้อมูลล้มเหลว:", error));
        });

        function updateMatchList(matches) {
            let matchContainer = document.getElementById("match-container");
            matchContainer.innerHTML = ""; // เคลียร์ข้อมูลเดิม

            if (matches.length === 0) {
                matchContainer.innerHTML = "<p>ไม่มีคู่แมตช์ในขณะนี้</p>";
                return;
            }

            matches.forEach(match => {
                let matchDiv = document.createElement("div");
                matchDiv.className = "match-item";
                matchDiv.innerHTML = `
                    <img src="${match.image}" alt="Profile Image">
                    <div>
                        <h3>${match.name}, ${match.age} ปี</h3>
                        <p>เพศ: ${match.gender}</p>
                        <p style="color: red; font-weight: bold;">💘 แมตช์ ${match.match_percentage}%</p>
                    </div>
                `;
                matchContainer.appendChild(matchDiv);
            });
        }
    </script>
</head>
<body>
    <h1>ยินดีต้อนรับ <span id="username">...</span></h1>
    <img id="profile-pic" src="default_profile.jpg" alt="Profile Picture" width="100">

    <h2>คู่แมตช์ของคุณ</h2>
    <div id="match-container">กำลังโหลด...</div>
</body>
</html>
