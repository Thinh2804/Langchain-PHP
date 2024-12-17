<?php
session_start();

// Kết nối tới cơ sở dữ liệu bằng MySQLi
$mysqli = new mysqli('localhost', 'root', '', 'db');

// Kiểm tra kết nối
if ($mysqli->connect_error) {
    die('Kết nối thất bại: ' . $mysqli->connect_error);
}

// Kiểm tra nếu form đã submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Chuẩn bị câu truy vấn với MySQLi
    $stmt = $mysqli->prepare("SELECT id, fullname, username, email, dob, password, role FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $username);  // 's' là kiểu dữ liệu string cho username
    $stmt->execute();  // Thực thi câu truy vấn
    $result = $stmt->get_result();  // Lấy kết quả

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();  // Lấy dữ liệu người dùng

        // Kiểm tra mật khẩu
        if (password_verify($password, $user['password'])) {
            // Lưu thông tin người dùng vào session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Chuyển hướng dựa trên vai trò
            if ($user['role'] === 'admin') {
                header("Location: backend/home.php");
            } else if ($user['role'] === 'gv') {
                header("Location: index2.php");
            } else {
                header("Location: index2.php");
            }
            exit; // Không cần chuyển hướng ở đây
        } else {
            echo "Mật khẩu không chính xác!";
        }
    } else {
        echo "Tài khoản không tồn tại!";
    }

    // Đóng statement
    $stmt->close();
}

// Đóng kết nối
$mysqli->close();
?>
