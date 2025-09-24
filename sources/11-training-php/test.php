<?php
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');

echo "<h3>🔍 Thử kết nối MySQL...</h3>";
echo "Host: $host<br>";
echo "User: $user<br>";
echo "DB: $db<br>";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_errno) {
    die("❌ Kết nối thất bại: " . $conn->connect_error);
} else {
    echo "✅ Kết nối MySQL thành công!";
}

$conn->close();
