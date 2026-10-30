<?php
header('Content-Type: text/plain');

$url = 'http://localhost/metalsindo/assets/css/styles.css';
echo "Testing URL: $url\n\n";

$ctx = stream_context_create([
    'http' => [
        'timeout' => 5,
        'ignore_errors' => true
    ]
]);

$content = @file_get_contents($url, false, $ctx);
if ($content === false) {
    echo "ERROR: Gagal membaca $url\n";
    $err = error_get_last();
    echo "Detail: " . ($err['message'] ?? 'Unknown error') . "\n";
} else {
    echo "HTTP Response Headers:\n";
    print_r($http_response_header ?? []);
    echo "\nContent preview (first 200 chars):\n";
    echo substr($content, 0, 200) . "\n";
}

