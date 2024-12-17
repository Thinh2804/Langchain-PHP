<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng</title>
    <link rel="stylesheet" href="set.css"> <!-- Liên kết đến file CSS -->
</head>
<body>
    <h1>Quản Lý Người Dùng</h1>
    <a href="home.php">Quay lại</a>
    <!-- Mã PHP để xử lý và hiển thị bảng người dùng -->
    <?php
    // Kết nối database
    $conn = new mysqli('localhost', 'root', '', 'db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Kiểm tra nếu có yêu cầu POST để cập nhật vai trò
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
        $user_id = $_POST['user_id'];
        $new_role = $_POST['role'];

        // Cập nhật vai trò
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $new_role, $user_id);

        if ($stmt->execute()) {
            echo "<p>Cập nhật thành công!</p>";
        } else {
            echo "<p>Cập nhật thất bại: " . $conn->error . "</p>";
        }

        $stmt->close();
    }

    // Lấy danh sách người dùng
    $sql = "SELECT id, username, email, role FROM users";
    $result = $conn->query($sql);
    ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['username']}</td>
                        <td>{$row['email']}</td>
                        <td>
                            <form method='POST' action='settinguser.php'>
                                <select name='role'>
                                    <option value='admin' " . ($row['role'] == 'admin' ? 'selected' : '') . ">Admin</option>
                                    <option value='gv' " . ($row['role'] == 'gv' ? 'selected' : '') . ">Giảng viên</option>
                                    <option value='user' " . ($row['role'] == 'user' ? 'selected' : '') . ">Sinh viên</option>
                                </select>
                                <input type='hidden' name='user_id' value='{$row['id']}'>
                                <button type='submit' name='update_role'>Cập nhật</button>
                            </form>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No users found</td></tr>";
        }
        $conn->close();
        ?>
    </table>
</body>
</html>