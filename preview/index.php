<?php
date_default_timezone_set("Asia/Jakarta");
set_time_limit(0);

function getValue($param)
{
    if (isset($_GET[$param])) {
        if ($_GET[$param]) {
            return $_GET[$param];
        } else {
            throw new Exception('Please provide a url value!');
        }
    } else {
        throw new Exception('Please provide a ' . $param . ' parameter!');
    }
}

function clean($string)
{
    $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    $GLOBALS['currentError'] = $errstr;
});
$GLOBALS['result'] = new stdClass();
$GLOBALS['result']->status = 500;
$GLOBALS['result']->message = 'Internal server error';
$GLOBALS['result']->elapsedTime = microtime(true);

try {
    $url = getValue('url');
    $type = getValue('type');
    $filename = getValue('filename');
    ob_start();
    $save = $type == 'video' ?  __DIR__ . '/movie.mp4' : __DIR__ . '/image.jpg';
    $nameNew = $type == 'video' ? clean($filename) . ".mp4" : clean($filename) . ".jpg";
    file_put_contents($save, fopen($url, 'r'));
    header('Connection: Keep-Alive');
    header('Content-Description: File Transfer');
    header('Content-Type: application/force-download');
    header("Content-Disposition: attachment; filename=$nameNew");
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($save));
    while (ob_get_level()) {
        ob_end_clean();
    }
    readfile($save);
    exit;
    unlink($save);
} catch (Exception $e) {
    $GLOBALS['result']->message = $e->getMessage();
    if (isset($GLOBALS['currentError'])) {
        $GLOBALS['result']->message .= ', ' . $GLOBALS['currentError'];
    }
} catch (Error $e) {
    $GLOBALS['result']->message = $e->getMessage();
} finally {
    restore_error_handler();
    $GLOBALS['result']->elapsedTime = round(microtime(true) - $GLOBALS['result']->elapsedTime, 3) . ' s';
    header('Content-Type: application/json');
    echo json_encode($GLOBALS['result'], JSON_PRETTY_PRINT);
}
