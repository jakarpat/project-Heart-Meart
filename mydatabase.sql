-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 27, 2025 at 08:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mydatabase`
--

-- --------------------------------------------------------

--
-- Table structure for table `match`
--

CREATE TABLE `match` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `goal` text DEFAULT NULL,
  `zodiac` varchar(255) DEFAULT NULL,
  `language` varchar(50) DEFAULT NULL,
  `languages` text DEFAULT NULL,
  `education` varchar(255) DEFAULT NULL,
  `family_plan` varchar(255) DEFAULT NULL,
  `covid_vaccine` varchar(255) DEFAULT NULL,
  `love_expression` varchar(255) DEFAULT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `drink` varchar(255) DEFAULT NULL,
  `pets` varchar(255) DEFAULT NULL,
  `drinking` varchar(255) DEFAULT NULL,
  `exercise` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `nearby_only` tinyint(1) DEFAULT 0,
  `interest` enum('ผู้หญิง','ผู้ชาย','ทุกคน') DEFAULT 'ทุกคน',
  `age_range_min` int(11) DEFAULT 0,
  `age_range_max` int(11) DEFAULT 60,
  `travel_world` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pet` varchar(255) NOT NULL DEFAULT '',
  `age_range` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `match`
--

INSERT INTO `match` (`id`, `user_id`, `email`, `goal`, `zodiac`, `language`, `languages`, `education`, `family_plan`, `covid_vaccine`, `love_expression`, `blood_type`, `drink`, `pets`, `drinking`, `exercise`, `location`, `nearby_only`, `interest`, `age_range_min`, `age_range_max`, `travel_world`, `created_at`, `updated_at`, `pet`, `age_range`) VALUES
(1, 45, 'camel@gmail.com', 'หาคนคบกันยาวๆ 😍', '♉ พฤษภ', NULL, NULL, '🎓 ปริญญาโท', 'ขยายครอบครัว 👨‍👩‍👧‍👦', NULL, '🌹 สร้างความโรแมนติก', '🆎 AB', 'ไม่ระบุ', NULL, '💧 น้ำ', '🏋️‍♀️ ฟิตเนส', NULL, 0, '', 0, 60, 0, '2025-02-01 11:09:21', '2025-02-13 09:02:40', 'ไม่ระบุ', NULL),
(2, 59, 'saifon1987@gmail.com', 'ไม่มีข้อมูล', '♈ เมษ', NULL, 'อังกฤษ', '🛠️ สายอาชีพ', 'ขยายครอบครัว 👨‍👩‍👧‍👦', 'ยังไม่ได้รับวัคซีน', '❤️ วิธีการแสดงความรักในสถานการณ์ต่าง ๆ', '🆎 กรุ', '💧 น้ำ', NULL, '☕ กาแฟ', '🚴‍♀️ ปั่นจักรยาน', '36 กม.', 0, 'ผู้หญิง', 29, 60, 0, '2025-02-01 12:06:29', '2025-02-26 05:02:05', '🐇 กระต่าย', 24),
(3, 50, 'wow00@gmail.com', 'ไม่มีข้อมูล', '♉ พฤษภ', NULL, NULL, NULL, NULL, NULL, '👂 การฟังและเข้าใจความต้องการ', '🆎 AB', '💧 น้ำ', NULL, NULL, '❓ อื่นๆ', '24', 0, 'ผู้หญิง', 37, 60, 0, '2025-02-01 12:15:09', '2025-02-03 21:16:48', '🐦 นก', NULL),
(18, 46, '66309010001@kbtc.ac.th', 'ไม่มีข้อมูล', '♈ เมษ', NULL, 'ฝรั่งเศส', '📜 การศึกษานอกระบบ', 'ยังไม่แน่ใจ 🤔', 'ไม่มีข้อมูล', '❤️ วิธีการแสดงความรักในสถานการณ์ต่าง ๆ', '🆎 กรุ', '💧 น้ำ', NULL, '💧 น้ำ', '🧘‍♀️ โยคะ', '54 กม.', 0, 'ผู้หญิง', 27, 60, 0, '2025-02-04 09:00:10', '2025-02-26 03:43:25', '🐇 กระต่าย', 22),
(20, 37, 'jakarpat3276@gmail.com', 'ไม่ระบุ', '♉ พฤษภ', NULL, NULL, 'ไม่ระบุ', 'ไม่ระบุ', NULL, '❤️ วิธีการแสดงความรักในสถานการณ์ต่าง ๆ', '🅾️ O', '❓ อื่นๆ', NULL, 'ไม่ระบุ', '🏃‍♂️ วิ่ง', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-04 09:03:04', '2025-02-04 09:03:04', '🐕 สุนัข', NULL),
(24, 55, '63207710003@kbtc.ac.th', 'ไม่ระบุ', '♉ พฤษภ', NULL, NULL, 'ไม่ระบุ', 'ไม่ระบุ', NULL, '❤️ วิธีการแสดงความรักในสถานการณ์ต่าง ๆ', '🅱️ B', '🍹 น้ำผลไม้', NULL, 'ไม่ระบุ', '🧘‍♀️ โยคะ', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-10 14:51:48', '2025-02-10 14:51:48', '😸 แมว', NULL),
(60, 53, 'hok@gmail.com', 'ไม่ระบุ', '♊ เมถุน', NULL, NULL, 'ไม่ระบุ', 'ไม่ระบุ', NULL, '👂 การฟังและเข้าใจความต้องการ', '🆎 AB', '💧 น้ำ', NULL, 'ไม่ระบุ', '🏋️‍♀️ ฟิตเนส', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-10 19:04:47', '2025-02-10 19:04:47', '🐇 กระต่าย', NULL),
(112, 38, '63209010003@kbtc.ac.th', 'หาคนรักจริงหวังแต่ง ❤️', '♊ เมถุน', NULL, NULL, 'ประถมศึกษา', 'ขยายครอบครัว 👨‍👩‍👧‍👦', NULL, '❤️ วิธีการแสดงความรักในสถานการณ์ต่าง ๆ', '🅾️ O', 'ไม่ระบุ', NULL, '💧 น้ำ', '🚴‍♀️ ปั่นจักรยาน', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:26:40', '2025-02-18 21:26:40', 'ไม่ระบุ', NULL),
(113, 44, 'wow555@gmail.com', 'หาคนคบกันยาวๆ 😍', '♊ เมถุน', NULL, NULL, '🎓 ปริญญาตรี', 'ยังไม่แน่ใจ 🤔', NULL, '💑 เคล็ดลับการดูแลความสัมพันธ์', '🆎 AB', 'ไม่ระบุ', NULL, '☕ กาแฟ', '🏋️‍♀️ ฟิตเนส', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:27:53', '2025-02-18 21:27:53', 'ไม่ระบุ', NULL),
(114, 51, 'oho@gmail.com', 'หาคนคบสบายๆ เพื่อคลิกจริงจัง 🥂', '♎ ตุลย์', NULL, NULL, '🎓 ปริญญาโท', 'ยังไม่แน่ใจ 🤔', NULL, '🌹 สร้างความโรแมนติก', '🅰️ A', 'ไม่ระบุ', NULL, '❓ อื่นๆ', '🏋️‍♀️ ฟิตเนส', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:30:15', '2025-02-18 21:30:15', 'ไม่ระบุ', NULL),
(115, 52, '63209010012@kbtc.ac.th', 'หาคนคุยแบบสนุกๆ 🎉', '♈ เมษ', NULL, NULL, '🎓 ปริญญาโท', 'ขยายครอบครัว 👨‍👩‍👧‍👦', NULL, '❤️ วิธีการแสดงความรักในสถานการณ์ต่าง ๆ', '🆎 AB', 'ไม่ระบุ', NULL, '🍵 ชา', '🚴‍♀️ ปั่นจักรยาน', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:31:11', '2025-02-18 21:31:11', 'ไม่ระบุ', NULL),
(116, 56, 'jakarpat7276@gmail.com', 'หาคนคบกันยาวๆ 😍', '♉ พฤษภ', NULL, NULL, '📚 มัธยมศึกษาตอนปลาย', 'มีลูกคนแรก 👶', NULL, '🌹 สร้างความโรแมนติก', '🆎 AB', 'ไม่ระบุ', NULL, '☕ กาแฟ', '🏃‍♂️ วิ่ง', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:33:39', '2025-02-18 21:33:39', 'ไม่ระบุ', NULL),
(117, 57, 'ca00@gmail.com', 'หาเพื่อนใหม่ 👋', '♋ กรกฎ', NULL, NULL, '📚 มัธยมศึกษาตอนปลาย', 'ยังไม่แน่ใจ 🤔', NULL, '🌹 สร้างความโรแมนติก', '🅰️ A', 'ไม่ระบุ', NULL, '🍹 น้ำผลไม้', '🏋️‍♀️ ฟิตเนส', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:34:30', '2025-02-18 21:34:30', 'ไม่ระบุ', NULL),
(118, 58, 'test9@gmail.com', 'หาคนรักจริงหวังแต่ง ❤️', '♉ พฤษภ', NULL, NULL, '🏫 สายสามัญ', 'ขยายครอบครัว 👨‍👩‍👧‍👦', NULL, '💑 เคล็ดลับการดูแลความสัมพันธ์', '🆎 AB', 'ไม่ระบุ', NULL, '❓ อื่นๆ', '🚴‍♀️ ปั่นจักรยาน', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:35:30', '2025-02-18 21:35:30', 'ไม่ระบุ', NULL),
(120, 61, 'surasak@gmail.com', 'หาคนคุยแบบสนุกๆ 🎉', '♋ กรกฎ', NULL, NULL, '🛠️ สายอาชีพ', 'ขยายครอบครัว 👨‍👩‍👧‍👦', NULL, '🌍 การแสดงความรักในวัฒนธรรมต่าง ๆ', '🆎 AB', 'ไม่ระบุ', NULL, '❓ อื่นๆ', 'ไม่ระบุ', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:37:13', '2025-02-18 21:37:13', 'ไม่ระบุ', NULL),
(121, 62, 'kitichai.j@gmail.com', 'หาคนคบกันยาวๆ 😍', '♉ พฤษภ', NULL, NULL, '🛠️ สายอาชีพ', 'มีลูกคนแรก 👶', NULL, '👂 การฟังและเข้าใจความต้องการ', '🅾️ O', 'ไม่ระบุ', NULL, '💧 น้ำ', '🏃‍♂️ วิ่ง', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:38:02', '2025-02-18 21:38:02', 'ไม่ระบุ', NULL),
(122, 63, 'kitichai.t@gmail.com', 'หาคนรักจริงหวังแต่ง ❤️', '♏ พิจิก', NULL, NULL, '🎓 ปริญญาโท', 'ยังไม่แน่ใจ 🤔', NULL, '💬 ภาษาแห่งความรัก', '🆎 AB', 'ไม่ระบุ', NULL, '☕ กาแฟ', 'ไม่ระบุ', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:38:50', '2025-02-18 21:38:50', 'ไม่ระบุ', NULL),
(123, 64, 'somchai.r@gmail.com', 'หาคนคบสบายๆ เพื่อคลิกจริงจัง 🥂', '♈ เมษ', NULL, NULL, '📚 มัธยมศึกษาตอนปลาย', 'มีลูกคนแรก 👶', NULL, '❤️ วิธีการแสดงความรักในสถานการณ์ต่าง ๆ', '🅰️ A', 'ไม่ระบุ', NULL, '☕ กาแฟ', '🚴‍♀️ ปั่นจักรยาน', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:39:46', '2025-02-18 21:39:46', 'ไม่ระบุ', NULL),
(124, 65, 'sompong.w@gmail.com', 'หาเพื่อนใหม่ 👋', '♌ สิงห์', NULL, NULL, '🏫 สายสามัญ', 'มีลูกคนแรก 👶', NULL, '🌹 สร้างความโรแมนติก', '🅱️ B', 'ไม่ระบุ', NULL, '🍹 น้ำผลไม้', '🚴‍♀️ ปั่นจักรยาน', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:40:45', '2025-02-18 21:40:45', 'ไม่ระบุ', NULL),
(125, 66, 'surasak.w@gmail.com', 'หาคนคบสบายๆ เพื่อคลิกจริงจัง 🥂', '♋ กรกฎ', NULL, NULL, '🎓 ปริญญาตรี', 'ขยายครอบครัว 👨‍👩‍👧‍👦', NULL, '❤️ วิธีการแสดงความรักในสถานการณ์ต่าง ๆ', '🆎 AB', 'ไม่ระบุ', NULL, '☕ กาแฟ', '🧘‍♀️ โยคะ', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:41:25', '2025-02-18 21:41:25', 'ไม่ระบุ', NULL),
(126, 67, 'prawit.w@gmail.com', 'หาคนคบกันยาวๆ 😍', '♎ ตุลย์', NULL, NULL, '🎓 ปริญญาโท', 'ไม่ระบุ', NULL, '👂 การฟังและเข้าใจความต้องการ', '🆎 AB', 'ไม่ระบุ', NULL, '🍵 ชา', '🏃‍♂️ วิ่ง', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:42:02', '2025-02-18 21:42:02', 'ไม่ระบุ', NULL),
(127, 68, 'rungroj.r@gmail.com', 'หาคนคบสบายๆ เพื่อคลิกจริงจัง 🥂', '♉ พฤษภ', NULL, NULL, '🎓 ปริญญาตรี', 'ขยายครอบครัว 👨‍👩‍👧‍👦', NULL, '💑 เคล็ดลับการดูแลความสัมพันธ์', '🆎 AB', 'ไม่ระบุ', NULL, '🍹 น้ำผลไม้', '🧘‍♀️ โยคะ', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:42:46', '2025-02-18 21:42:46', 'ไม่ระบุ', NULL),
(128, 69, 'narong.w@gmail.com', 'หาคนคบกันยาวๆ 😍', '♊ เมถุน', NULL, NULL, '🏫 สายสามัญ', 'ยังไม่แน่ใจ 🤔', NULL, '🌹 สร้างความโรแมนติก', '🅰️ A', 'ไม่ระบุ', NULL, '🍹 น้ำผลไม้', '🏃‍♂️ วิ่ง', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:47:05', '2025-02-18 21:47:05', 'ไม่ระบุ', NULL),
(129, 70, 'anucha.t@gmail.com', 'หาคนคบกันยาวๆ 😍', '♊ เมถุน', NULL, NULL, '🎓 ปริญญาโท', 'ขยายครอบครัว 👨‍👩‍👧‍👦', NULL, '❤️ วิธีการแสดงความรักในสถานการณ์ต่าง ๆ', '🅾️ O', 'ไม่ระบุ', NULL, '🍹 น้ำผลไม้', '🚴‍♀️ ปั่นจักรยาน', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:47:42', '2025-02-18 21:47:42', 'ไม่ระบุ', NULL),
(130, 71, 'waraporn.t@gmail.com', 'หาคนรักจริงหวังแต่ง ❤️', '♊ เมถุน', NULL, NULL, '🎓 ปริญญาตรี', 'ยังไม่แน่ใจ 🤔', NULL, '❤️ วิธีการแสดงความรักในสถานการณ์ต่าง ๆ', '🅱️ B', 'ไม่ระบุ', NULL, '☕ กาแฟ', '🧘‍♀️ โยคะ', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:48:36', '2025-02-18 21:48:36', 'ไม่ระบุ', NULL),
(131, 81, 'qq@kbtc.ac.th', 'หาคนคบกันยาวๆ 😍', '♋ กรกฎ', NULL, NULL, '📚 มัธยมศึกษาตอนปลาย', 'ยังไม่แน่ใจ 🤔', NULL, '💑 เคล็ดลับการดูแลความสัมพันธ์', '🅰️ A', 'ไม่ระบุ', NULL, '💧 น้ำ', '🧘‍♀️ โยคะ', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:49:20', '2025-02-18 21:49:20', 'ไม่ระบุ', NULL),
(132, 82, 'tot@gmail.com', 'หาคนคบสบายๆ เพื่อคลิกจริงจัง 🥂', '♊ เมถุน', NULL, NULL, '🎓 ปริญญาตรี', 'ขยายครอบครัว 👨‍👩‍👧‍👦', NULL, '🌹 สร้างความโรแมนติก', '🅾️ O', 'ไม่ระบุ', NULL, '🍹 น้ำผลไม้', '🏃‍♂️ วิ่ง', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:50:01', '2025-02-18 21:50:01', 'ไม่ระบุ', NULL),
(133, 83, '66309990001@kbtc.ac.th', 'หาคนคุยแบบสนุกๆ 🎉', '♋ กรกฎ', NULL, NULL, '📚 มัธยมศึกษาตอนปลาย', 'ยังไม่แน่ใจ 🤔', NULL, '❤️ วิธีการแสดงความรักในสถานการณ์ต่าง ๆ', '🅾️ O', 'ไม่ระบุ', NULL, '💧 น้ำ', '🏋️‍♀️ ฟิตเนส', NULL, 0, 'ทุกคน', 0, 60, 0, '2025-02-18 21:50:51', '2025-02-18 21:50:51', 'ไม่ระบุ', NULL),
(161, 0, 'ไม่มีข้อมูล', '♉ พฤษภ', 'อังกฤษ', NULL, '🛠️ สายอาชีพ', 'ขยายครอบครัว 👨‍👩‍👧‍👦', 'ได้รับวัคซีนเข็มเดียว', '🌹 สร้างความโรแมนติก', '🅾️ กรุ๊ปเลือด O', '🐇 กระ', '🧘‍♀️ โยคะ', NULL, NULL, '40 กม.', '24 ปี', 0, 'ทุกคน', 0, 60, 0, '2025-02-26 05:11:35', '2025-02-26 05:11:35', '☕ กาแฟ', 0);

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `user1` int(11) NOT NULL,
  `user2` int(11) NOT NULL,
  `matched_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`id`, `user1`, `user2`, `matched_at`) VALUES
(1, 73, 87, '2025-02-23 20:37:28'),
(98, 73, 87, '2025-02-26 04:59:29'),
(99, 53, 116, '2025-02-26 11:50:31'),
(100, 79, 116, '2025-02-26 12:10:19');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `match_id`, `sender_id`, `user_id`, `message`, `created_at`, `is_read`) VALUES
(1, 1, 0, 87, '123', '2025-02-25 06:21:34', 0),
(2, 46, 0, 87, 'สวัสดีครับ', '2025-02-25 06:23:48', 0),
(3, 1, 0, 87, 'ชื่ออะไร', '2025-02-25 06:23:55', 0),
(4, 50, 0, 87, '123', '2025-02-25 07:18:55', 0),
(5, 52, 0, 87, '123', '2025-02-25 07:30:18', 0),
(6, 52, 0, 87, 'สวัสดี', '2025-02-25 07:32:16', 0),
(7, 52, 0, 87, 'ชิตังเม', '2025-02-25 07:39:39', 0),
(8, 54, 59, 0, 'ooo', '2025-02-25 07:45:45', 0),
(9, 55, 59, 0, '123', '2025-02-25 07:48:24', 0),
(10, 1, 59, 0, '123', '2025-02-25 07:56:16', 0),
(11, 74, 59, 0, '123', '2025-02-25 09:03:08', 0),
(12, 78, 59, 0, 'สวัสดี', '2025-02-25 09:15:15', 0),
(13, 81, 86, 0, '123', '2025-02-25 10:18:56', 0),
(14, 0, 59, 0, '123', '2025-02-25 18:25:56', 0),
(15, 97, 59, 0, 'สวัสดี', '2025-02-25 18:50:28', 0),
(16, 97, 59, 0, '123', '2025-02-25 18:54:31', 0),
(17, 0, 46, 0, '455', '2025-02-25 20:16:03', 0),
(18, 0, 46, 0, 'ชื่ิอไร', '2025-02-25 20:37:53', 0),
(19, 0, 46, 0, '1234', '2025-02-25 20:42:25', 0),
(20, 0, 59, 0, 'สวัสดีคับ', '2025-02-25 21:04:05', 0),
(21, 0, 59, 0, '133333', '2025-02-26 03:00:49', 0),
(22, 0, 59, 0, '123', '2025-02-26 03:30:41', 0),
(23, 98, 59, 0, 'สวัสดี', '2025-02-26 03:43:44', 0),
(24, 98, 59, 0, 'ชื่อไรหรออ', '2025-02-26 04:25:10', 0),
(25, 0, 46, 0, 'กกก', '2025-02-27 06:57:29', 0);

-- --------------------------------------------------------

--
-- Table structure for table `profile1`
--

CREATE TABLE `profile1` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `interest` enum('male','female','any') NOT NULL,
  `profile_pictures` text DEFAULT NULL,
  `profile_main` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profile1`
--

INSERT INTO `profile1` (`id`, `name`, `email`, `dob`, `gender`, `interest`, `profile_pictures`, `profile_main`, `created_at`, `password`, `phone`) VALUES
(46, 'oot', '', '0000-00-00', 'male', 'male', NULL, NULL, '2025-02-25 16:51:50', '', NULL),
(68, 'admin', 'jakarpat3276@gmail.com', '2004-05-24', 'male', 'female', 'uploads/673ec529a33cd_ดด.jpg', 'uploads/673ec529a33cd_ดด.jpg', '2024-11-21 05:29:13', '', NULL),
(69, 'Camel', '63209010003@kbtc.ac.th', '2017-05-24', 'male', 'any', 'uploads/673f215397117_DSCN7463.JPG', 'uploads/673f215397117_DSCN7463.JPG', '2024-11-21 12:02:27', '', NULL),
(72, 'oop', 'wow555@gmail.com', '2024-11-24', 'male', 'male', 'uploads/673f4d4c2f592_jo.jpg', 'uploads/673f4d4c2f592_jo.jpg', '2024-11-21 15:10:04', '', NULL),
(73, 'Camel', 'camel@gmail.com', '2004-05-24', 'male', 'female', 'uploads/673f4dec3c150_LINE_ALBUM_บาสชาย_240630_1.jpg', 'uploads/673f4dec3c150_LINE_ALBUM_บาสชาย_240630_1.jpg', '2024-11-21 15:12:44', '', NULL),
(74, 'MelShi', '66309010001@kbtc.ac.th', '2003-05-24', 'male', 'female', 'uploads/67446097929f8_b5.jpg', 'uploads/67446097929f8_b5.jpg', '2024-11-25 11:33:43', '', NULL),
(75, 'จักรภัทร', 'wow00@gmail.com', '1999-05-25', 'male', 'female', 'uploads/67473317b376b_th.jfif', 'uploads/67473317b376b_th.jfif', '2024-11-27 14:56:23', '', NULL),
(76, 'oho', 'oho@gmail.com', '2024-11-07', 'male', 'female', 'uploads/67474c45365b9_loao.png', 'uploads/67474c45365b9_loao.png', '2024-11-27 16:43:49', '', NULL),
(77, 'KBTC', '63209010012@kbtc.ac.th', '2004-05-24', 'male', 'any', 'uploads/6747d3ee86d7b_33.png', 'uploads/6747d3ee86d7b_33.png', '2024-11-28 02:22:38', '', NULL),
(79, 'HOK', 'hok@gmail.com', '2004-05-24', 'male', 'female', 'uploads/6747d74a34a60_444.png', 'uploads/6747d74a34a60_444.png', '2024-11-28 02:36:58', '', NULL),
(81, 'Jakarpat', '632000010003@kbtc.ac.th', '2004-05-24', 'male', 'female', 'uploads/675f0544497a1_Screenshot 2024-12-12 190130.png', 'uploads/675f0544497a1_Screenshot 2024-12-12 190130.png', '2024-12-15 16:35:16', '', NULL),
(83, 'Camel', '63207710003@kbtc.ac.th', '2004-05-24', 'male', 'female', 'uploads/675f07111ad75_Screenshot 2024-12-12 190130.png', 'uploads/675f07111ad75_Screenshot 2024-12-12 190130.png', '2024-12-15 16:42:57', '', NULL),
(84, 'gamel', 'jakarpat7276@gmail.com', '2009-09-15', 'male', 'female', 'uploads/677bc0bd828e5_292415446_440562261409371_6239923424576652428_n.jpg', 'uploads/677bc0bd828e5_292415446_440562261409371_6239923424576652428_n.jpg', '2025-01-06 11:38:37', '', NULL),
(85, 'ca00', 'ca00@gmail.com', '2024-05-16', 'female', 'male', 'uploads/67850cde8b301_หัวใจจ.png', 'uploads/67850cde8b301_หัวใจจ.png', '2025-01-13 12:53:50', '', NULL),
(86, 'admin', 'test9@gmail.com', '2004-05-24', 'male', 'any', 'uploads/67853ad3de051_Idle (6).png', 'uploads/67853ad3de051_Idle (6).png', '2025-01-13 16:09:55', '', NULL),
(87, 'สายฝน ทองคำ', 'saifon1987@gmail.com', '2008-07-21', 'female', 'male', 'uploads/678bd54189f34_สายฝน.jpg', 'uploads/678bd54189f34_สายฝน.jpg', '2025-01-18 16:22:25', '', NULL),
(89, 'บอล', 'surasak@gmail.com', '1995-06-21', 'male', 'female', '', '', '2025-01-19 04:24:34', '', NULL),
(90, 'กิต', 'kitichai.j@gmail.com', '2007-10-03', 'male', 'female', '', '', '2025-01-19 04:31:20', '', NULL),
(91, ' Kay', 'kitichai.t@gmail.com', '2002-05-14', 'male', 'female', '', '', '2025-01-19 04:35:37', '', NULL),
(92, 'chai', 'somchai.r@gmail.com', '2005-01-11', 'male', 'female', '', '', '2025-01-19 04:41:23', '', NULL),
(93, 'Pong', 'sompong.w@gmail.com', '1999-12-16', 'male', 'female', '', '', '2025-01-19 04:44:37', '', NULL),
(94, 'Sak', 'surasak.w@gmail.com', '2010-05-17', 'male', 'any', '', '', '2025-01-19 04:45:58', '', NULL),
(95, 'Wit', 'prawit.w@gmail.com', '2003-04-27', 'male', 'female', '', '', '2025-01-19 04:47:48', '', NULL),
(96, 'Roj', 'rungroj.r@gmail.com', '1997-09-15', 'male', 'female', '', '', '2025-01-19 04:50:04', '', NULL),
(97, 'Rong', 'narong.w@gmail.com', '1999-03-31', 'male', 'female', '', '', '2025-01-19 04:51:45', '', NULL),
(98, 'Cha', 'anucha.t@gmail.com', '2000-11-06', 'female', 'male', '', '', '2025-01-19 04:56:04', '', NULL),
(99, 'Pae', 'waraporn.t@gmail.com', '2004-07-11', 'female', 'male', '', '', '2025-01-19 04:59:09', '', NULL),
(101, 'Cha', '&lt;?php echo htmlspecialchars($email); ?&gt;', '2006-10-04', 'male', 'female', '', '', '2025-01-19 05:16:54', '', NULL),
(107, 'Jakarpat', 'qq@kbtc.ac.th', '2025-02-03', 'male', 'female', 'uploads/67a42f67b30d3_Heart Meart (1).png', 'uploads/67a42f67b30d3_Heart Meart (1).png', '2025-02-06 03:41:27', '', NULL),
(109, 'TOT', 'tot@gmail.com', '2004-05-24', 'male', 'female', 'uploads/67a4b3c005b9b_1325913.png', 'uploads/67a4b3c005b9b_1325913.png', '2025-02-06 13:06:08', '', NULL),
(110, 'โอริโอ้', '66309990001@kbtc.ac.th', '2025-02-28', 'male', 'female', 'uploads/67b4096456e1c_Yellow and Black Modern Typography Tshirt Design A4 Document (1).png,uploads/67b40964571f9_475461756_2650035428524155_8657811638620404937_n-Photoroom.png,uploads/67b40964576ff_DSCN7463.JPG', 'uploads/67b4096456e1c_Yellow and Black Modern Typography Tshirt Design A4 Document (1).png', '2025-02-18 04:15:32', '', NULL),
(111, 'test111', 'saifon333@gmail.com', '2019-01-24', 'male', 'female', '', '', '2025-02-24 04:19:35', '', NULL),
(112, 'ryushi', 'ruy@kbtc.ac.th', '2013-05-24', 'male', 'female', 'uploads/67bd97995bdb5_666.jpg,uploads/67bd97995bf62_Blender_logo_no_text.svg.png', 'uploads/67bd97995bdb5_666.jpg', '2025-02-25 10:12:41', '', NULL),
(113, 'คาเมล', 'camel1@gmail.com', '2009-09-11', 'female', 'male', 'uploads/67bd99c10c1d0_475461756_2650035428524155_8657811638620404937_n-Photoroom.png', 'uploads/67bd99c10c1d0_475461756_2650035428524155_8657811638620404937_n-Photoroom.png', '2025-02-25 10:21:53', '', NULL),
(114, 'kbtc11', 'kbtc@kbtc.ac.th', '2025-02-25', 'male', 'female', 'uploads/67bdbc95332ec_779.gif', 'uploads/67bdbc95332ec_779.gif', '2025-02-25 12:50:29', '', NULL),
(115, 'riw3', 'riw@gmail.com', '2007-06-13', 'male', 'female', 'uploads/67bdc0554cf89_999.jpg', 'uploads/67bdc0554cf89_999.jpg', '2025-02-25 13:06:29', '', NULL),
(116, 'dddd', 'wow@gmail.com', '2018-05-14', 'female', 'male', 'uploads/67be19e690a82_enhanced_image.png', 'uploads/67be19e690a82_enhanced_image.png', '2025-02-25 19:28:38', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `review` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `name`, `review`, `created_at`) VALUES
(1, 'คาเมล', 'เว็บนี้ใช้ง่ายย สดวก สวย', '2025-02-01 13:40:33'),
(2, 'ชิตังเม', 'สวยมาก ใช่งานง่ายกว่าทุกๆแอพที่เล่นมา แถบฟรีด้วย ', '2025-02-01 13:41:28'),
(3, 'Camel', 'สวยมาก', '2025-02-25 10:06:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(37, 'admin2', '$2y$10$G9KQ6VJVjFjZ8cV.HH3WE.ZtjMT4eMYHL9tdQZyApD5IC1VZJail2', 'jakarpat3276@gmail.com', '2024-11-21 05:28:03'),
(38, 'came', '$2y$10$Xm95sSpoGkrb0PGaAOgC2.ybIJdYuwuckTmKU1BN.lB5r8fq94Ct6', '63209010003@kbtc.ac.th', '2024-11-21 12:00:58'),
(43, 'CEO', '$2y$10$lctWEbHqOMFPn.HYcfTkT.wmoY49LpYyKDvxRGXMHnFNiqEtfUORK', 'wow@gmail.com', '2024-11-21 14:53:10'),
(44, 'riw', '$2y$10$YyR3A7Vbpu2Ah7GxP3mquOSAnbfj/Y7smaaIBqfZpnrPxmtouQgLu', 'wow555@gmail.com', '2024-11-21 14:54:20'),
(45, 'camel', '$2y$10$eJtZFag41ND6enUinLRSleR/xsiKwhLP/ZIO07axXKFN3nGGyuUEq', 'camel@gmail.com', '2024-11-21 15:11:37'),
(46, 'คาเมร่า', '$2y$10$orERFQCoL6MSMXOAmKn63u1tm1owzk1zVDN/zRk/uhHDrAFga6IDG', '66309010001@kbtc.ac.th', '2024-11-25 11:32:17'),
(50, 'jakarpat', '$2y$10$Qmfwqy3GqImqXy0e05i35etzPTIcmtgG0ww1m7CZadZfk9XwSXt.S', 'wow00@gmail.com', '2024-11-27 14:55:41'),
(51, 'oho', '$2y$10$cetstLtaQwNk6C6PmMKxJe3K5EhIKCvKbmnM0Bpq0hqTQ1Tv7CGM6', 'oho@gmail.com', '2024-11-27 16:43:32'),
(52, 'kbtc', '$2y$10$rs3UienTTWON0QYGjUzOq.XqmZwC.lSdWGpEMG4kdzxBzdaAnwuce', '63209010012@kbtc.ac.th', '2024-11-28 02:21:58'),
(53, 'hok', '$2y$10$Z9VWa5xDePkKuukgP8MXY.Hoz8AZKSIhFptzAuwcQkvfu9dokeBP6', 'hok@gmail.com', '2024-11-28 02:36:10'),
(55, 'IT', '$2y$10$pNC3akFXcLjwbG4t89It4eMbm9JB/4e2M7xBm1uiImHQyLo4zp6by', '63207710003@kbtc.ac.th', '2024-12-15 16:41:24'),
(56, 'gamel', '$2y$10$qET5eI2tIC37t2xBkEmfVeSe49VkN0mOVq2F0MBqE6i8VDfP20TCe', 'jakarpat7276@gmail.com', '2025-01-06 11:38:06'),
(57, 'ca00', '$2y$10$eunQnb7c9BlOg01h.yDrPePEzmGbG5lCWK2BpADWynh4TkDfNp3XK', 'ca00@gmail.com', '2025-01-13 12:53:09'),
(58, 'adminmel', '$2y$10$E8xZnMAm59NHmPsreZvl4.zQ3xbeREI82d0i6DgvMtnYobN1kFmEW', 'test9@gmail.com', '2025-01-13 16:08:14'),
(59, 'สายฝน ทองคำ', '$2y$10$QEHxIxUxbvTtAZcHP9wiqOpTqY3LeAMeQlFUQlCuDV5zJZbFPakW6', 'saifon1987@gmail.com', '2025-01-18 16:13:34'),
(61, 'นายสุรศักดิ์ ชาญชัย', '$2y$10$N4/WwmKSYWJRd6kATV2vDu2HrLtVlH41iMaunoA9rLP4xX7Tc9smG', 'surasak@gmail.com', '2025-01-19 04:22:07'),
(62, 'กิติชัย ใจดี', '$2y$10$ruQthbVfTiV54mAbcDxcZuRBPhCCPk1G4E32oiGZQ0YgEkVwQkod6', 'kitichai.j@gmail.com', '2025-01-19 04:30:49'),
(63, 'Kitichai Tangtrong', '$2y$10$O268SoXwLDrmPYAYtyU5t.WsrM/lgNledipmsNCyNnbo3UtHTRnKu', 'kitichai.t@gmail.com', '2025-01-19 04:35:10'),
(64, 'Somchai Rungreung', '$2y$10$L58SpGNaK.b.nxIMfev9dOEefBS.q2o3kJ9q8tKRnGH8zcMT.2H7O', 'somchai.r@gmail.com', '2025-01-19 04:40:52'),
(65, 'Sompong Wongwittaya', '$2y$10$T/Sf4eM0VnLSg1BcB16jpuxuvgRSxrkXuB/5uP80Vrf2soz/Mh/xe', 'sompong.w@gmail.com', '2025-01-19 04:43:53'),
(66, 'Surasak Wiset', '$2y$10$qqyAmCo/tojXPD1ogjK8hedJCgfVlcbBAFLMFjLhhdN8RHLcSgf8u', 'surasak.w@gmail.com', '2025-01-19 04:45:34'),
(67, 'Prawit Wongwittaya', '$2y$10$UmYi9ZRhEVLq4bX2JMNQ5.dARvHGEoDohrFNGv.YJYnFcwD6LAuoi', 'prawit.w@gmail.com', '2025-01-19 04:47:18'),
(68, 'Rungroj Rungreung', '$2y$10$w3KzfagsDd4YXMnXgsSQ9uAWqAeSQgVgoJ4xImOiCoC33wcDRbq.y', 'rungroj.r@gmail.com', '2025-01-19 04:49:21'),
(69, 'Narong Wiset', '$2y$10$yhrT04/hGSsyI7ZQ0898Te11h0W7wrVboINWcjMp2lm9xxJAYOR3.', 'narong.w@gmail.com', '2025-01-19 04:51:07'),
(70, 'Anucha Thammakun', '$2y$10$an.UXa0Fk01KS/JKLsY4a.HdjeG7Wq13susCXB3WLefMYU5hi7yF2', 'anucha.t@gmail.com', '2025-01-19 04:55:32'),
(71, 'Waraporn Tangtrong', '$2y$10$qB.2pd1ZmLgTZm2yLM294eEr.NXGaB7aJ1T6HJfSabJM5QfeUSMZe', 'waraporn.t@gmail.com', '2025-01-19 04:58:27'),
(75, 'riw4', '$2y$10$A95C2ry/YDeIilVGhe0cPOcm1f8cGRoYQzg.Ce97H0HmduCtht9TS', 'riwkong093663@gmail.com', '2025-02-06 03:03:53'),
(79, 'ceo', '$2y$10$FjU7Ca0u5Qfa2ObzoVKIZ.Syjt54Dmy.lOyfzsmegfdNGyllzkuJ.', 'ceo@gmail.com', '2025-02-06 03:28:19'),
(81, 'qq', '$2y$10$Nz98U/u7yj2bjoFmYBjlL.HAJSuHbl4zVAIywEpC1jzmcRBdwO69e', 'qq@kbtc.ac.th', '2025-02-06 03:38:27'),
(82, 'tot', '$2y$10$goLN7EoTFBeZto4/6I47X.jBq8D4M6CjBlNg5qVjP3UlpnvJA/8ye', 'tot@gmail.com', '2025-02-06 13:01:12'),
(83, 'กำดำ', '$2y$10$8YJKqesH0M2BffaMcPS3TOm6Cxzfl0jNU9JDL6EUdcSB9WQ9Ngdie', '66309990001@kbtc.ac.th', '2025-02-18 04:09:29'),
(84, 'test3', '$2y$10$Iv6NI6mioBYPUqOD4E7qIOSWHVAOhAiKbz/tRgicSMfNRnch96u/i', 'saifon333@gmail.com', '2025-02-24 04:19:02'),
(85, 'ryu', '$2y$10$RqSchnd4HOQl05W6NQd3KOvVYWc6RapuSPKMp0sWukqWgEGW1ZvcK', 'ryu@gmail.com', '2025-02-25 10:08:50'),
(86, 'ruy1', '$2y$10$9pFmoE7k/jraEFyA3auBGOJycYoSmh6dIFopZ.UL2cc3Q/6ML6euy', 'ruy@kbtc.ac.th', '2025-02-25 10:11:43'),
(87, 'mel22', '$2y$10$4.7ZtxycUB6i5Rh.hLu7u.jEOZgmQ3XBsyNNDpOaeybEBPV9se6/G', 'camel1@gmail.com', '2025-02-25 10:21:17'),
(90, 'kbtc11', '$2y$10$Rx1G1i6z4licqw5tPOXzzu090l3z4TyvDglXXM7Cez/kz3QbYIkge', 'kbtc@kbtc.ac.th', '2025-02-25 12:48:14'),
(91, 'riw3', '$2y$10$mdyJGaaPnqs6/dEc2D/.iOjb8yU13xzJXjJSZNiz.lV756J3b56v.', 'riw@gmail.com', '2025-02-25 12:55:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `match`
--
ALTER TABLE `match`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `user_id_2` (`user_id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `profile1`
--
ALTER TABLE `profile1`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `match`
--
ALTER TABLE `match`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `profile1`
--
ALTER TABLE `profile1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
