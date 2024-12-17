<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trường Đại Học Kĩ Thuật - Công Nghệ</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Thêm style cho logo */
        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            width: auto;
            height: 40px; /* Điều chỉnh chiều cao logo */
            max-width: 100%;
            object-fit: contain;
        }

        /* Style cho modal */
        .modal {
            display: none; /* Ẩn modal mặc định */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4); /* Màu nền mờ */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px; /* Chiều rộng tối đa của modal */
            border-radius: 8px; /* Bo góc cho modal */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Đổ bóng cho modal */
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Style cho form đăng nhập */
        .modal-content h2 {
            text-align: center; /* Căn giữa tiêu đề */
            margin-bottom: 20px; /* Khoảng cách dưới tiêu đề */
        }

        .modal-content input[type="text"],
        .modal-content input[type="password"] {
            width: 100%; /* Chiều rộng 100% */
            padding: 10px; /* Padding cho input */
            margin: 10px 0; /* Khoảng cách trên/dưới cho input */
            border: 1px solid #ccc; /* Đường viền cho input */
            border-radius: 4px; /* Bo góc cho input */
        }

        .modal-content button {
            width: 100%; /* Chiều rộng 100% */
            padding: 10px; /* Padding cho button */
            background-color: #4CAF50; /* Màu nền cho button */
            color: white; /* Màu chữ cho button */
            border: none; /* Không có đường viền */
            border-radius: 4px; /* Bo góc cho button */
            cursor: pointer; /* Con trỏ khi hover */
        }

        .modal-content button:hover {
            background-color: #45a049; /* Màu nền khi hover */
        }
    </style>
</head>
<body>
<header>
    <div class="logo">
        <img src="img/logo.png">
    </div>
    <nav>
        <button id="loginBtn" class="login-btn">Đăng Nhập →</button>
    </nav>
</header>

<main>
    <h1 class="highlight">Trường đại học kĩ thuật công nghệ <br> Cần Thơ</br></h1>
    
    <!-- Thêm nút Đăng Nhập trong phần main -->
    <button id="mainLoginBtn" class="login-btn">Đăng Nhập →</button>

    <div class="features">
        <div class="feature">
            <img src="img/ueh.png" alt="Thông Dụng">
            <h3>Thông Dụng</h3>
            <p>Có thể sử dụng mọi lúc, bất cứ khi nào.</p>
        </div>
        <div class="feature">
            <img src="img/dhct.png" alt="Quản Lý">
            <h3>Quản Lý</h3>
            <p>Có thể quản lý được trên các hệ thống cùng chuỗi hoạt động</p>
        </div>
        <div class="feature">
            <img src="img/logo.png" alt="Liên Kết">
            <h3>Liên Kết</h3>
            <p>Có liên kết với các trường đại học như: Đại Học Cần Thơ, Đại Học Kinh Tế TP.HCM...</p>
        </div>
    </div>
</main>

<!-- Modal Đăng Nhập -->
<div id="loginModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Đăng Nhập</h2>
        <form id="loginForm" action="loginpro.php" method="POST">
            <input type="text" id="username" name="username" placeholder="Username/Email" required>
            <div class="password-input">
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit">Log In</button>
        </form>
    </div>
</div>

<footer>
    <p></p>
</footer>

<script>
    // Hiển thị modal khi nhấn nút đăng nhập từ header
    document.getElementById('loginBtn').onclick = function() {
        document.getElementById('loginModal').style.display = 'block';
    }

    // Hiển thị modal khi nhấn nút đăng nhập từ main
    document.getElementById('mainLoginBtn').onclick = function() {
        document.getElementById('loginModal').style.display = 'block';
    }

    // Đóng modal
    function closeModal() {
        document.getElementById('loginModal').style.display = 'none';
    }

    // Đóng modal khi nhấn ra ngoài modal
    window.onclick = function(event) {
        if (event.target == document.getElementById('loginModal')) {
            closeModal();
        }
    }
</script>
</body>
</html>