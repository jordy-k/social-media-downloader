<?php
date_default_timezone_set("Asia/Jakarta");
set_time_limit(0);
require '../vendor/simple_html_dom/simple_html_dom.php';

function postValue($key)
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    } else {
        throw new Exception('Please provide a ' . $key . ' key!');
    }
}

function detectDomain($url)
{
    if (strpos($url, 'linevoom.line.me') !== false) {
        $res = 'line-video';
    } else if (strpos($url, 'instagram') !== false) {
        $res = 'line-video';
    }
    return $res;
}

function prt($string)
{
    echo $string . PHP_EOL;
}

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    $GLOBALS['currentError'] = $errstr;
});

$GLOBALS['result'] = new stdClass();
$GLOBALS['result']->status = 500;
$GLOBALS['result']->message = 'Internal server error';
$GLOBALS['result']->elapsedTime = microtime(true);
$GLOBALS['result']->url = '';
$GLOBALS['result']->source = '';
$GLOBALS['result']->poster = '';
$GLOBALS['result']->filename = '';

try {
    $url = postValue('url');
    $GLOBALS['result']->url = $url;
    $document = file_get_html($url);
    // echo $document;
    // die;

    //video
    $videoMeta = $document->find('meta[property="og:video"]', 0)->content . '/mp4';
    $GLOBALS['result']->source = $videoMeta;

    //poster
    $posterMeta = $document->find('meta[property="og:image"]', 0)->content;
    $GLOBALS['result']->poster = $posterMeta;

    $title = $document->find('meta[property="og:title"]', 0)->content;
    $caption = $document->find('div.textLayout-module_text_inner__3L9Wn', 0)->text();
    $GLOBALS['result']->filename = $title . ' - ' . $caption . '.mp4';

    $GLOBALS['result']->status = 200;
    $GLOBALS['result']->message = 'Success';
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
