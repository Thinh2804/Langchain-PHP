<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <title>Hỏi đáp AI</title>
    
</head>

<body>
<div class="header">
    <div class="subheader">
        <div class="logo">
            <img src="img/logo.png" alt="Logo">
        </div>
        <div class="welcome">
            Xin chào người dùng
        </div>
        <div class="info">
                <a href="info.php">Thông tin tài khoản</a>
        </div>
        <div class="logout">
            <a href="dashboard.php">Đăng xuất</a>
        </div>
    </div>
</div>


<div class="container">
    <div class="chat-container">
        <div class="chat-header">
            Hỏi đáp AI
        </div>
        <div class="chat-messages" id="chatMessages">
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $question = $_POST["question"];
                
                echo "<div class='message user-message'>" . htmlspecialchars($question) . "</div>";

                $url = "http://localhost:5000/answer";
                $data = json_encode(array("question" => $question));

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data)
                ));

                $result = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode == 200) {
                    $response = json_decode($result, true);
                    if (isset($response["answer"]) && isset($response["sources"])) {
                        echo "<div class='message ai-message'>";
                        echo htmlspecialchars($response["answer"]);
                        echo "<div class='sources'>";
                        foreach ($response["sources"] as $source) {
                            echo "Nguồn: " . htmlspecialchars($source["source"]) . ", Trang: " . htmlspecialchars(implode(", ", $source["page"])) . "<br>";
                        }
                        echo "</div></div>";
                    } else {
                        echo "<div class='message ai-message'>Không nhận được câu trả lời hợp lệ từ server.</div>";
                    }
                } else {
                    echo "<div class='message ai-message'>Có lỗi xảy ra khi gửi yêu cầu. Mã lỗi HTTP: " . $httpCode . "</div>";
                }
            }
            ?>
        </div>
        <form method="post" class="chat-input">
            <textarea id="question" name="question" rows="2" placeholder="Nhập câu hỏi của bạn..."></textarea>
            <button type="submit" value ="ask">Gửi</button>
        </form>
    </div>
</div>    
    <script>
        // Cuộn xuống cuối cùng của khung chat sau khi tải trang
        window.onload = function() {
            var chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        };
    </script>
</body>
</html>