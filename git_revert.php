<?php
header('Content-Type: text/plain');

$output = shell_exec('git checkout -- application/modules/ application/models/ application/core/ application/controllers/ application/helpers/ 2>&1');
echo "Checkout modules/models/core/controllers/helpers:\n" . $output . "\n\n";

$status = shell_exec('git status --short 2>&1');
echo "Git Status:\n" . $status;
