<?php
header('Content-Type: text/plain');
echo shell_exec('git show HEAD:application/config/config.php | head -n 35 2>&1');

