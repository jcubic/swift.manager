<?php
/**
 *  This file is part of Swift Manager
 *  Copyright (C) 2018  Jakub Jankiewicz <http://jcubic.pl/me>
 *
 *  Released under the MIT license
 *
 */

require('../apps/terminal/leash/lib/Service.php');
$leash = new Leash('config.json', getcwd() . '/..');
if ($leash->valid_token($_GET['token']) && isset($_GET['filename']) && file_exists($_GET['filename'])) {
    $size = filesize($_GET['filename']);
    header('Content-Type: ' . mime_content_type($_GET['filename']));
    header('Content-Transfer-Encoding: Binary');
    header('Content-disposition: attachment; filename="' . basename($_GET['filename']) . '"');
    header('Content-Length: ' . $size);
    header('Content-Range: 0-' . ($size-1) . '/' . $size);
    $file = fopen($_GET['filename'], 'r');
    while (!feof($file)) {
        $buffer = fread($file, 1048576);
        echo $buffer;
        ob_flush();
        flush();
    }
    fclose($file);
} else {
    echo 'wrong args';
}

?>
