<?php
// Mật khẩu cần hash
$password = "ntna2001007";

// Hash mật khẩu sử dụng PASSWORD_DEFAULT (mặc định là bcrypt)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Hiển thị mật khẩu đã hash
echo $hashed_password;
?>
