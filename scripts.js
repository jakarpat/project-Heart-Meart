// เพิ่ม event listeners สำหรับปุ่มต่างๆ
document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('คลิกปุ่ม: ' + this.textContent);
            // เพิ่มโค้ดสำหรับการจัดการการคลิกปุ่มแต่ละปุ่มที่นี่
        });
    });

    const helpText = document.querySelector('.help-text');
    helpText.addEventListener('click', function() {
        console.log('คลิกข้อความช่วยเหลือ');
        // เพิ่มโค้ดสำหรับการแสดงหน้าช่วยเหลือที่นี่
    });
});