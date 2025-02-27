const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');
const axios = require('axios'); // ใช้ axios สำหรับ Infobip
require('dotenv').config();

const app = express();
app.use(cors());
app.use(bodyParser.json());

// ตรวจสอบว่าคีย์ .env ถูกตั้งค่าหรือไม่
if (!process.env.INFOBIP_API_KEY || !process.env.INFOBIP_BASE_URL) {
    console.error('ERROR: โปรดตั้งค่า INFOBIP_API_KEY และ INFOBIP_BASE_URL ในไฟล์ .env');
    process.exit(1);
}

// Infobip credentials
const infobipApiKey = process.env.INFOBIP_API_KEY;
const infobipBaseUrl = process.env.INFOBIP_BASE_URL;

// Store verification codes temporarily
const verificationCodes = {};

// Route to send OTP via SMS
app.post('/send-otp', async (req, res) => {
  console.log('Received request:', req.body);
  // ส่วนที่เหลือของโค้ด
});

    // ตรวจสอบว่า phone และ country ถูกส่งมาหรือไม่
    if (!phone || !country) {
        return res.status(400).json({
            success: false,
            message: 'กรุณากรอกหมายเลขโทรศัพท์และประเทศให้ครบถ้วน',
        });
    }

    const fullPhone = `${country}${phone}`;
    const otpCode = Math.floor(100000 + Math.random() * 900000); // สร้างรหัส OTP
    verificationCodes[fullPhone] = otpCode;

    try {
        // ขั้นตอนที่ 1: ตรวจสอบเบอร์โทรศัพท์ผ่าน Infobip Lookup API
        console.log(`กำลังตรวจสอบหมายเลขโทรศัพท์: ${fullPhone}`);
        const lookupResponse = await axios.get(
            `${infobipBaseUrl}/number/1/lookup?phoneNumber=${encodeURIComponent(fullPhone)}`,
            {
                headers: {
                    Authorization: `App ${infobipApiKey}`,
                    'Content-Type': 'application/json',
                },
            }
        );

        // ตรวจสอบสถานะของเบอร์
        const lookupStatus = lookupResponse.data.status?.groupName || 'UNKNOWN';
        console.log(`สถานะหมายเลขโทรศัพท์: ${lookupStatus}`);
        if (lookupStatus !== 'VALID') {
            return res.status(400).json({
                success: false,
                message: 'หมายเลขโทรศัพท์นี้ไม่มีอยู่จริง',
            });
        }

        // ขั้นตอนที่ 2: ส่ง OTP หากเบอร์โทรศัพท์มีอยู่จริง
        console.log(`หมายเลข ${fullPhone} ถูกต้อง กำลังส่ง OTP`);
        const response = await axios.post(
            `${infobipBaseUrl}/sms/2/text/advanced`,
            {
                messages: [
                    {
                        destinations: [{ to: fullPhone }],
                        from: 'InfoSMS',
                        text: `Your OTP code is ${otpCode}`,
                    },
                ],
            },
            {
                headers: {
                    Authorization: `App ${infobipApiKey}`,
                    'Content-Type': 'application/json',
                },
            }
        );

        console.log('Response จาก Infobip:', response.data);
        res.json({
            success: true,
            message: 'ส่ง OTP สำเร็จ',
            response: response.data,
        });
    } catch (error) {
        console.error('Error:', error.response?.data || error.message);
        res.status(500).json({
            success: false,
            message: error.response?.data?.message || 'เกิดข้อผิดพลาดระหว่างการส่ง OTP',
        });
    }
});

// ตรวจสอบว่าเซิร์ฟเวอร์เริ่มทำงานหรือไม่
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log(`Server is running on http://localhost:${PORT}`));
