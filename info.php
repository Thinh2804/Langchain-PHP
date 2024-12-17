<?php
session_start();

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Chuyển hướng đến trang đăng nhập nếu chưa đăng nhập
    exit;
}

// Kết nối đến cơ sở dữ liệu
$mysqli = new mysqli('localhost', 'root', '', 'db'); // Thay đổi thông tin kết nối nếu cần

// Kiểm tra kết nối
if ($mysqli->connect_error) {
    die('Kết nối thất bại: ' . $mysqli->connect_error);
}

// Lấy thông tin người dùng từ cơ sở dữ liệu
$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT fullname, username, email, dob, profile_image FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc(); // Lấy dữ liệu người dùng
} else {
    echo "Không tìm thấy thông tin người dùng.";
    exit;
}

// Xử lý tải lên hình ảnh
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_image'])) {
    $target_dir = "uploads/"; // Thư mục lưu hình ảnh
    $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Kiểm tra xem file có phải là hình ảnh không
    $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
    if ($check === false) {
        echo "File không phải là hình ảnh.";
        $uploadOk = 0;
    }

    // Kiểm tra kích thước file
    if ($_FILES["profile_image"]["size"] > 500000) {
        echo "Xin lỗi, hình ảnh của bạn quá lớn.";
        $uploadOk = 0;
    }

    // Cho phép các định dạng hình ảnh cụ thể
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Xin lỗi, chỉ cho phép các định dạng JPG, JPEG, PNG & GIF.";
        $uploadOk = 0;
    }

    // Kiểm tra xem $uploadOk có được thiết lập thành 0 không do lỗi
    if ($uploadOk == 0) {
        echo "Xin lỗi, hình ảnh của bạn không được tải lên.";
    } else {
        // Xóa hình ảnh cũ nếu có
        if (!empty($user['profile_image']) && file_exists($user['profile_image'])) {
            unlink($user['profile_image']);
        }

        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            // Cập nhật đường dẫn hình ảnh trong cơ sở dữ liệu
            $stmt = $mysqli->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
            $stmt->bind_param('si', $target_file, $user_id);
            $stmt->execute();
            echo "Hình ảnh ". htmlspecialchars(basename($_FILES["profile_image"]["name"])). " đã được tải lên.";
        } else {
            echo "Xin lỗi, đã xảy ra lỗi khi tải lên hình ảnh.";
        }
    }
}

// Xử lý cập nhật thông tin người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $dob = $_POST['dob'];

    // Cập nhật thông tin người dùng trong cơ sở dữ liệu
    $stmt = $mysqli->prepare("UPDATE users SET fullname = ?, email = ?, dob = ? WHERE id = ?");
    $stmt->bind_param('sssi', $fullname, $email, $dob, $user_id);
    $stmt->execute();
    echo "Thông tin người dùng đã được cập nhật.";
}

// Đóng kết nối
$stmt->close();
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/info.css">
    <title>Thông Tin Người Dùng</title>
</head>
<body>
    <div class="container">
        <h1>Thông Tin Người Dùng</h1>
        <form method="post">
            <p><strong>Họ và tên:</strong> <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required></p>
            <p><strong>Tên đăng nhập:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></p>
            <p><strong>Ngày sinh:</strong> <input type="date" name="dob" value="<?php echo htmlspecialchars($user['dob']); ?>" required></p>
            <button type="submit" name="update_info">Cập nhật thông tin</button>
        </form>

        <!-- Hiển thị hình ảnh người dùng từ thư mục uploads -->
        <div class="profile-image-container">
            <h2>Hình Ảnh Hồ Sơ</h2>
            <img src="uploads/<?php echo htmlspecialchars($user['profile_image'] ?: 'default.jpg'); ?>" alt="Hình ảnh hồ sơ" style="max-width: 200px; height: auto;">
            <form method="post" enctype="multipart/form-data">
                <input type="file" name="profile_image" accept="image/*" required>
                <button type="submit">Thay đổi hình ảnh</button>
            </form>
        </div>

        <!-- Thêm mục chứa hình ảnh từ thư mục uploads -->
        <div class="uploaded-images-container">
            <h2>Hình Ảnh Tải Lên</h2>
            <div class="uploaded-images">
                <?php
                // Lấy danh sách hình ảnh từ thư mục uploads
                $images = glob("uploads/*.{jpg,jpeg,png,gif}", GLOB_BRACE);
                foreach ($images as $image) {
                    echo '<img src="' . htmlspecialchars($image) . '" alt="Hình ảnh tải lên" style="max-width: 100px; height: auto; margin: 5px;">';
                }
                ?>
            </div>
        </div>

        <a href="index2.php">Quay lại</a> <!-- Liên kết quay lại trang index -->
    </div>
</body>
</html>
