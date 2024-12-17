<?php
session_start();

// Đường dẫn tới thư mục lưu file
$target_dir = "../data/";  // Sửa lại đường dẫn để trỏ đến thư mục data ở ngoài

// Kiểm tra và tạo thư mục nếu chưa tồn tại
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

try {
    // Kiểm tra xem có file được upload không
    if (!isset($_FILES["file"]) || $_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
        throw new Exception("Không có file được upload hoặc có lỗi trong quá trình upload.");
    }

    $file = $_FILES["file"];
    $fileName = basename($file["name"]);
    $target_file = $target_dir . $fileName;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Kiểm tra định dạng file
    if ($fileType !== "pdf") {
        throw new Exception("Lỗi khi tải.");
    }

    // Kiểm tra kích thước file (giới hạn 10MB)
    $maxFileSize = 10 * 1024 * 1024; // 10MB in bytes
    if ($file["size"] > $maxFileSize) {
        throw new Exception("File quá lớn. Giới hạn 10MB.");
    }

    // Kiểm tra file đã tồn tại
    if (file_exists($target_file)) {
        // Tạo tên file mới bằng cách thêm timestamp
        $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '_' . time() . '.pdf';
        $target_file = $target_dir . $fileName;
    }

    // Upload file
    if (!move_uploaded_file($file["tmp_name"], $target_file)) {
        throw new Exception("Có lỗi xảy ra khi upload file.");
    }

    // Cập nhật quyền truy cập cho file
    chmod($target_file, 0644);

    $_SESSION['success'] = "File $fileName đã được upload thành công.";

} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

// Chuyển hướng về trang chính
header("Location: home.php");
exit();

function sanitizeFileName($fileName) {
    // Loại bỏ ký tự đặc biệt và khoảng trắng
    $fileName = preg_replace("/[^a-zA-Z0-9._-]/", "", $fileName);
    return $fileName;
}
?>
