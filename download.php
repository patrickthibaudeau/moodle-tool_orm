<?php
require_once('config.php');

$filename = required_param('file', PARAM_TEXT);
// Force file download
header("Content-Description: File Transfer");
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"". basename($filename) ."\"");

readfile ($filename);
exit();