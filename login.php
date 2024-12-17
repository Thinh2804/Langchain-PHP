<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="css/logcss.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="" class="logo">
            <h2>Account Log In</h2>
        </div>
        <form class="login-form" action="loginpro.php" method="POST">
            <input type="text" id="username" name="username" placeholder="Username/Email" required>
            <div class="password-input">
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit">Log In</button>
        </form>
        <div class="login-footer">
            <a href="#" class="help-link">Having Problems?</a>
        </div>
    </div>
</body>
</html>