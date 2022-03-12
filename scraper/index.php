<?php
date_default_timezone_set("Asia/Jakarta");
set_time_limit(0);
require '../vendor/simple_html_dom/simple_html_dom.php';
require '../vendor/autoload.php';

use Phpfastcache\Helper\Psr16Adapter;

use Browser\Casper;

function postValue($key)
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    } else {
        throw new Exception('Please provide a ' . $key . ' key!');
    }
}

function detectMedia($url)
{
    if (strpos($url, 'linevoom.line.me') !== false) {
        $res = 'line-video';
    } else if (strpos($url, 'instagram') !== false) {
        $res = 'instagram-video';
    }
    return $res;
}

function removeParam($url, $param)
{
    $url = preg_replace('/(&|\?)' . preg_quote($param) . '=[^&]*$/', '', $url);
    $url = preg_replace('/(&|\?)' . preg_quote($param) . '=[^&]*&/', '$1', $url);
    return $url;
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
$GLOBALS['result']->media = '';

try {
    // $url = postValue('url');
    $url = 'https://linevoom.line.me/post/1164595693501078522/';
    $GLOBALS['result']->url = $url;

    $casper = new Casper();
    $casper->setOptions(array(
        'ignore-ssl-errors' => 'yes'
    ));
    $casper->start($url);
    $casper->wait(5000);
    $output = $casper->getOutput();
    $casper->run();
    $html = $casper->getHtml();
    // $document = file_get_html($url);
    $document = str_get_html($html);
    echo $document;
    die;
    //media
    $media = detectMedia($url);
    $GLOBALS['result']->media = $media;
    if ($media == 'line-video') {
        $source = $document->find('meta[property="og:video"]', 0)->content . '/mp4';
        $poster = $document->find('meta[property="og:image"]', 0)->content;
        $title = $document->find('meta[property="og:title"]', 0)->content;
        $filename = 'Line - ' . $title;
    } else if ($media == 'instagram-video') {
        $instagram = new \InstagramScraper\Instagram(new \GuzzleHttp\Client());
        $instagram = \InstagramScraper\Instagram::withCredentials(new \GuzzleHttp\Client(), 'habisya123', '123habisya', new Psr16Adapter('Files'));
        $instagram->login();
        $media = $instagram->getMediaByUrl(explode("?", $url)[0]);
        // echo "Caption: {$media->getCaption()}\n";
        // echo "High resolution image: {$media->getImageHighResolutionUrl()}\n\n";
        // echo "Media type (video or image): {$media->getType()}\n\n";
        // echo "Video Low Resolution: {$media->getVideoLowResolutionUrl()}\n\n";
        // echo "Video Standard Resolution: {$media->getVideoStandardResolutionUrl()}\n\n";
        $source = $media->getVideoStandardResolutionUrl();
        $poster = $media->getImageHighResolutionUrl();
        $title = $media->getCaption();
        $filename = 'Instagram - ' . $title;
    }
    $GLOBALS['result']->source = $source;
    $GLOBALS['result']->poster = $poster;
    $GLOBALS['result']->filename = $filename;

    //status
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
