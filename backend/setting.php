<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];

    // Kết nối database
    $conn = new mysqli('localhost', 'root', '', 'db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Cập nhật vai trò
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $user_id);

    if ($stmt->execute()) {
        echo "Cập nhật thành công!";
    } else {
        echo "Cập nhật thất bại: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

    // Reload lại trang để hiển thị thay đổi
    header("Location: settinguser.php");
    exit();
}
?>