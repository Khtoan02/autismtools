<?php
/**
 * Template Name: Trỏ Link
 *
 * @package AutismTools
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đang chuyển hướng...</title>

    <!-- Cách 1: Chuyển hướng bằng thẻ Meta (Dành cho trình duyệt tắt JS) -->
    <!-- Content="0" nghĩa là 0 giây, chuyển ngay lập tức -->
    <meta http-equiv="refresh" content="0; url=https://tools.dawnbridge.vn/check-list-tieu-hoa">

    <!-- Cách 2: Chuyển hướng bằng JavaScript (Nhanh và mượt hơn) -->
    <script type="text/javascript">
        // Hàm thực hiện chuyển hướng
        function redirectPage() {
            var newUrl = "https://tools.dawnbridge.vn/check-list-tieu-hoa";
            
            // Sử dụng replace để không lưu lịch sử trang cũ, 
            // giúp người dùng bấm nút Back không bị kẹt lại trang này.
            window.location.replace(newUrl);
        }

        // Gọi hàm ngay khi tải xong
        redirectPage();
    </script>
    
    <!-- CSS để trang nhìn gọn gàng trong lúc chờ (nếu mạng chậm) -->
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f9f9f9;
            text-align: center;
        }
        .message {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="message">
        <p>Trang này đã được chuyển đến địa chỉ mới.</p>
        <p>Đang tự động chuyển hướng...</p>
        <p>Nếu trình duyệt không tự chuyển, vui lòng <a href="https://tools.dawnbridge.vn/check-list-tieu-hoa">BẤM VÀO ĐÂY</a>.</p>
    </div>
</body>
</html>