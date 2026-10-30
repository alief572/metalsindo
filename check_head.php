<?php
// Cek apa yang dirender oleh header.php
define('BASEPATH', __DIR__ . '/system/');
define('APPPATH', __DIR__ . '/application/');
define('VIEWPATH', __DIR__ . '/application/views/');

require_once APPPATH . 'config/config.php';
echo "config base_url: " . $config['base_url'] . "\n";
echo "SERVER HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "\n";
echo "SERVER SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET') . "\n";

