<?php
$mysqli = new mysqli("206.84.97.106", "alief", "sdk*^HkfJKjkefJFJLEJLE", "metalsindo_dev", 3306);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}
$result = $mysqli->query("DESCRIBE tr_spk_marketing;");
while ($row = $result->fetch_assoc()) {
    print_r($row);
}
$mysqli->close();
?>



