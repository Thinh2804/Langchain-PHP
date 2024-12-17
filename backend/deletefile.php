<?php
session_start();

// Kiểm tra phương thức request
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: home.php");
    exit();
}

// Lấy tên file từ form
$filename = isset($_POST['filename']) ? trim($_POST['filename']) : '';

// Kiểm tra tên file có rỗng không
if (empty($filename)) {
    $_SESSION['error'] = "Tên file không được để trống!";
    header("Location: home.php");
    exit();
}

// Đường dẫn đến file cần xóa
$filepath = "../data/" . $filename;

// Kiểm tra file có tồn tại không
if (!file_exists($filepath)) {
    $_SESSION['error'] = "File không tồn tại!";
    header("Location: home.php");
    exit();
}

// Thực hiện xóa file
try {
    if (unlink($filepath)) {
        $_SESSION['success'] = "Đã xóa file thành công!";
    } else {
        $_SESSION['error'] = "Không thể xóa file!";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Có lỗi xảy ra khi xóa file: " . $e->getMessage();
}

// Chuyển hướng về trang chủ
header("Location: home.php");
exit();
?>