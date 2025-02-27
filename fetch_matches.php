<?php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['email'])) {
    echo json_encode(["success" => false, "message" => "คุณต้องเข้าสู่ระบบก่อน"]);
    exit();
}

$email = $_SESSION['email'];

function getDatabaseConnection() {
    $conn = new mysqli("localhost", "root", "", "mydatabase");
    if ($conn->connect_error) {
        throw new Exception("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
    }
    return $conn;
}

try {
    $conn = getDatabaseConnection();

    // ดึงข้อมูลผู้ใช้ปัจจุบัน
    $stmt_user = $conn->prepare("SELECT * FROM profile1 WHERE email = ?");
    $stmt_user->bind_param("s", $email);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($result_user->num_rows === 0) {
        throw new Exception("ไม่พบข้อมูลผู้ใช้งาน");
    }
    $current_user = $result_user->fetch_assoc();
    $stmt_user->close();

    // ดึงข้อมูลผู้ใช้ทั้งหมด (ยกเว้นตัวเอง)
    $stmt_matches = $conn->prepare("SELECT * FROM profile1 WHERE email != ?");
    $stmt_matches->bind_param("s", $email);
    $stmt_matches->execute();
    $result_matches = $stmt_matches->get_result();

    // กำหนดค่าน้ำหนักสำหรับฟิลด์ต่าง ๆ (ปรับได้ตามที่ต้องการ)
    $weights = [
        'goal'            => 30,
        'zodiac'          => 10,
        'languages'       => 15,
        'education'       => 10,
        'family_plan'     => 10,
        'love_expression' => 10,
        'blood_type'      => 5,
        'drink'           => 5,
        'pets'            => 5,
        'exercise'        => 10
    ];
    $max_score = array_sum($weights);

    $matches = [];
    while ($row = $result_matches->fetch_assoc()) {
        $score = 0;
        foreach ($weights as $field => $weight) {
            if (!empty($current_user[$field]) && !empty($row[$field]) && $current_user[$field] == $row[$field]) {
                $score += $weight;
            }
        }
        $match_percentage = round(($score / $max_score) * 100, 2);

        // คำนวณอายุ
        $age = "ไม่ระบุ";
        if (!empty($row['dob'])) {
            $dob_obj = date_create($row['dob']);
            if ($dob_obj) {
                $age = date_diff($dob_obj, date_create('today'))->y;
            }
        }

        // ตรวจสอบรูปภาพ (ถ้ามีให้ใช้ในโฟลเดอร์ uploads)
        $profile_pic = $row['profile_pictures'] ?? '';
        $image_path = "uploads/default_profile.jpg";
        if (!empty($profile_pic) && file_exists("uploads/" . $profile_pic)) {
            $image_path = "uploads/" . $profile_pic;
        }

        // ถ้าต้องการแสดงเฉพาะผู้ที่มี match_percentage > 0
        if ($match_percentage > 0) {
            $matches[] = [
                'id'               => $row['id'],  // ไอดีของคู่แมตช์
                'name'             => $row['name'],
                'age'              => $age,
                'gender'           => $row['gender'],
                'image'            => $image_path,
                'match_percentage' => $match_percentage
            ];
        }
    }

    $stmt_matches->close();
    $conn->close();

    echo json_encode(["success" => true, "matches" => $matches], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
