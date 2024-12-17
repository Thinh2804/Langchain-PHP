<?php
session_start();

//Kiểm tra xem người dùng đã đăng nhập chưa
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
//     header('Location: login.php');
//     exit;
// } 
$pdf_files = glob("../data/*.pdf");

// Đọc lịch sử chat từ file JSON
$chat_history_file = '../data/chat_history.json'; // Đường dẫn đến file chat history
$chat_history = [];

if (file_exists($chat_history_file)) {
    $chat_history = json_decode(file_get_contents($chat_history_file), true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="/hc2.00/css/bohome.css">
    <link rel="stylesheet" href="/hc2.00/css/chat.css">
    <link rel="stylesheet" href="/hc2.00/css/dashboard.css">
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            width: 300px;
        }

        .popup-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #d4edda; /* Màu nền cho thông báo thành công */
            color: green; /* Màu chữ cho thông báo thành công */
            border: 1px solid #c3e6cb;
            padding: 20px;
            border-radius: 5px;
            display: none; /* Ẩn mặc định */
            z-index: 1001; /* Đảm bảo thông báo nổi trên các phần tử khác */
        }

        .error-message {
            background-color: #f8d7da; /* Màu nền cho thông báo lỗi */
            color: red; /* Màu chữ cho thông báo lỗi */
        }
    </style>
</head>
<body>
<div class="header">
    <div class="subheader">
        <div class="logo">
            <img src="../img/logo.png" alt="Logo">
        </div>
        <div class="welcome">
            Xin chào Administrator
        </div>
        <div class="info">
            <a href="settinguser.php">Quản lý người dùng</a>
        </div>
        <div class="logout">
            <a href="../dashboard.php">Đăng xuất</a>
        </div>
    </div>
</div> 
<div class="container">
    <!-- Phần thông báo -->
    <div class="popup-message" id="popupMessage">
        <?php
        if (isset($_SESSION['success'])) {
            echo htmlspecialchars($_SESSION['success']);
            unset($_SESSION['success']); // Xóa thông báo sau khi hiển thị
        }
        if (isset($_SESSION['error'])) {
            echo htmlspecialchars($_SESSION['error']);
            unset($_SESSION['error']); // Xóa thông báo sau khi hiển thị
        }
        ?>
    </div>

    <!-- Phần danh sách PDF -->
    <div class="pdf-section">
        <div class="pdf-upload">
            <h2>Upload PDF File</h2>
            <form action="uploadpdf.php" method="post" enctype="multipart/form-data">
                <label for="file">Chọn file PDF:</label>
                <input type="file" name="file" id="file" accept="application/pdf" required>
                <input type="submit" value="Upload">
            </form>
        </div>
        
        <h2>Danh sách file PDF:</h2>
        <div class="pdf-list">
            <ul>
            <?php foreach ($pdf_files as $file): ?>
                <li class="file-item">
                    <span class="file-name"><?php echo basename($file); ?></span>
                    <form method="POST" action="deletefile.php" class="delete-form" style="display: inline;">
                        <input type="hidden" name="filename" value="<?php echo basename($file); ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa file này?');">Xóa</button>
                    </form>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Phần chat -->
    <div class="chat-section">
        <div class="chat">
            <h1>Hỏi đáp AI</h1>
            <div class="chat-container">
                <div class="chat-messages" id="chatMessages">
                    <!-- Hiển thị lịch sử chat -->
                    <?php foreach ($chat_history as $entry): ?>
                        <div class="message <?php echo $entry['role'] === 'user' ? 'user-message' : 'ai-message'; ?>">
                            <?php echo htmlspecialchars($entry['content']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="chat-input">
                    <form id="chatForm" method="post">
                        <textarea id="question" name="question" rows="3" placeholder="Nhập câu hỏi của bạn..." onkeydown="handleEnter(event)"></textarea>
                        <button type="submit" class="gradient-button" id="sendButton">
                            <span>Gửi</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('chatForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const question = document.getElementById('question').value;
    const chatMessages = document.getElementById('chatMessages');

    if (question.trim() !== '') {
        // Hiển thị câu hỏi của người dùng
        const userMessage = document.createElement('div');
        userMessage.className = 'message user-message';
        userMessage.textContent = question;
        chatMessages.appendChild(userMessage);

        // Gửi câu hỏi đến Python server
        const response = await fetch('http://localhost:5000/answer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ question: question })
        });

        if (response.ok) {
            const data = await response.json();
            const aiMessage = document.createElement('div');
            aiMessage.className = 'message ai-message';
            aiMessage.textContent = data.answer;
            chatMessages.appendChild(aiMessage);

            // Hiển thị nguồn tham khảo nếu có
            if (data.sources && data.sources.length > 0) {
                let sourcesText = "Nguồn tham khảo:\n";
                data.sources.forEach((source, index) => {
                    sourcesText += `${index + 1}. ${source.source} (Trang ${source.page})\n`;
                });
                const sourcesMessage = document.createElement('div');
                sourcesMessage.className = 'message ai-message';
                sourcesMessage.textContent = sourcesText;
                chatMessages.appendChild(sourcesMessage);
            }
        } else {
            const errorMessage = document.createElement('div');
            errorMessage.className = 'message ai-message';
            errorMessage.textContent = 'Có lỗi xảy ra khi gửi yêu cầu.';
            chatMessages.appendChild(errorMessage);
        }

        // Xóa nội dung textarea
        document.getElementById('question').value = '';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const popupMessage = document.getElementById('popupMessage');

    if (popupMessage.innerHTML.trim() !== '') {
        popupMessage.style.display = 'block'; // Hiển thị popup
        setTimeout(() => {
            popupMessage.style.display = 'none'; // Ẩn popup sau 3 giây
        }, 3000); // Ẩn sau 3 giây
    }
});
</script>

<div id="messagePopup" class="message-popup">
    <div class="popup-content">
        <span class="popup-message"></span>
        <button class="close-popup">&times;</button>
    </div>
</div>
</body>
</html>
