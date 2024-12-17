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
    <h1>Hỏi đáp AI</h1>
    <form method="post">
        <label for="question">Câu hỏi:</label><br>
        <textarea id="question" name="question" rows="4" cols="50"></textarea><br>
        <input type="submit" value="Gửi câu hỏi">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $question = $_POST["question"];
        
        function get_content($url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        }
        
        $content = get_content('http://localhost:5000/answer');
        #$url = "http://localhost:5000/answer";
        $data = json_encode(array("question" => $question));

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => $data
            )
        );
        if (ini_get('allow_url_fopen')) {
            echo "file_get_contents() có thể làm việc với URL.";
        } else {
            echo "file_get_contents() không thể làm việc với URL.";
        }    

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        if ($result === FALSE) {
            echo "<p>Có lỗi xảy ra khi gửi yêu cầu.</p>";
        } else {
            $response = json_decode($result, true);
            if (isset($response["answer"]) && isset($response["sources"])) {
                echo "<h2>Câu trả lời:</h2>";
                echo "<p>" . htmlspecialchars($response["answer"]) . "</p>";
                
                echo "<h2>Nguồn tham khảo:</h2>";
                foreach ($response["sources"] as $index => $source) {
                    echo "<p>Nguồn " . ($index + 1) . ": " . 
                         htmlspecialchars($source["source"]) . ", Trang " . 
                         htmlspecialchars($source["page"]) . "</p>";
                }
            } else {
                echo "<p>Không nhận được câu trả lời hợp lệ từ server.</p>";
            }
        }
    }
    ?>
</body>
</html>