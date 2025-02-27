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
    <title>Share Location</title>
    <script>
        function askForLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(saveLocation, showError);
            } else {
                alert("❌ เบราว์เซอร์ของคุณไม่รองรับการแชร์ตำแหน่ง");
                redirectToMatchPage();
            }
        }

        function saveLocation(position) {
            let latitude = position.coords.latitude;
            let longitude = position.coords.longitude;

            fetch("update_location.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `latitude=${latitude}&longitude=${longitude}`
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log("✅ พิกัดถูกบันทึกแล้ว:", data.message);
                } else {
                    console.log("❌ ไม่สามารถบันทึกพิกัดได้:", data.error);
                }
                redirectToMatchPage();
            }).catch(() => redirectToMatchPage());
        }

        function showError(error) {
            alert("⛔ ไม่สามารถดึงตำแหน่งของคุณได้");
            redirectToMatchPage();
        }

        function redirectToMatchPage() {
            window.location.href = "MatchPage.php"; // ไปยังหน้าคู่แมตช์
        }
    </script>
</head>
<body onload="askForLocation()">
    <h2>📍 กำลังขออนุญาตใช้ตำแหน่ง...</h2>
</body>
</html>
