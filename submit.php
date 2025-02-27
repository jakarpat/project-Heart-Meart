<?php
// Database connection
$connection = mysqli_connect("localhost", "root", "", "profile");

if (!$connection) {
    die("Database connection error: " . mysqli_connect_error());
}

// Getting form data
$name = mysqli_real_escape_string($connection, $_POST['name']);
$email = mysqli_real_escape_string($connection, $_POST['email']);
$dob = mysqli_real_escape_string($connection, $_POST['dob']);
$gender = mysqli_real_escape_string($connection, $_POST['gender']);
$interest = mysqli_real_escape_string($connection, $_POST['interest']);

// Handle image upload
$profilePictures = $_FILES['profile_pictures'];
$imagePaths = [];

for ($i = 0; $i < count($profilePictures['name']); $i++) {
    if ($profilePictures['error'][$i] === 0) {
        $tmpName = $profilePictures['tmp_name'][$i];
        $imageName = basename($profilePictures['name'][$i]);
        $uploadDir = "uploads/"; // Make sure this directory exists
        $imagePath = $uploadDir . uniqid() . "_" . $imageName;

        if (move_uploaded_file($tmpName, $imagePath)) {
            $imagePaths[] = $imagePath;
        }
    }
}

// Save user data to database
$sql = "INSERT INTO users (name, email, dob, gender, interest, profile_pictures) VALUES ('$name', '$email', '$dob', '$gender', '$interest', '" . implode(',', $imagePaths) . "')";
if (mysqli_query($connection, $sql)) {
    echo "บัญชีถูกสร้างเรียบร้อยแล้ว!";
} else {
    echo "Error: " . mysqli_error($connection);
}

// Close connection
mysqli_close($connection);
?>
