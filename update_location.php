<?php
session_start();
if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "error" => "User not logged in"]);
    exit();
}

$email = $_SESSION['email'];
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;

if (!$latitude || !$longitude) {
    echo json_encode(["success" => false, "error" => "Invalid location data"]);
    exit();
}

$conn = new mysqli("localhost", "root", "", "mydatabase");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit();
}

// บันทึกพิกัดลง column 'location' ของตาราง
$stmt = $conn->prepare("UPDATE profile1 SET location = CONCAT(?, ',', ?) WHERE email = ?");
$stmt->bind_param("dds", $latitude, $longitude, $email);
$success = $stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(["success" => $success, "message" => "Location updated"]);
?>
