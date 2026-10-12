<?php
$mysqli = new mysqli("206.84.97.106", "alief", "sdk*^HkfJKjkefJFJLEJLE", "metalsindo_dev", 3306);
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}
$result = $mysqli->query("ALTER TABLE tr_spk_marketing ADD COLUMN ppn DOUBLE(11,2) DEFAULT 0.00, ADD COLUMN nilai_ppn DOUBLE(11,2) DEFAULT 0.00;");
if ($result) {
    echo "Columns added successfully.\n";
} else {
    echo "Error adding columns: " . $mysqli->error . "\n";
}
$mysqli->close();
?>
