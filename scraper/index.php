<?php
date_default_timezone_set("Asia/Jakarta");
set_time_limit(0);
require '../vendor/simple_html_dom/simple_html_dom.php';

use Phpfastcache\Helper\Psr16Adapter;

require __DIR__ . '/../vendor/autoload.php';

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
    $url = postValue('url');
    $GLOBALS['result']->url = $url;
    $document = file_get_html($url);
    // echo $document;
    // die;
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
        // $source = "https://media.sf-converter.com/get?__sig=AUV-uDIY9H5otgKTCRXYCA&__expires=1645173849&uri=https%3A%2F%2Finstagram.fdnk3-2.fna.fbcdn.net%2Fv%2Ft50.2886-16%2F273902630_1039064320009795_7013114122342133466_n.mp4%3F_nc_ht%3Dinstagram.fdnk3-2.fna.fbcdn.net%26_nc_cat%3D107%26_nc_ohc%3D3_BZR-ScwbEAX-uYfYg%26edm%3DAABBvjUBAAAA%26ccb%3D7-4%26oe%3D62116CAD%26oh%3D00_AT9E9DnHNZN5Sje1ex0LXqoTEljl6ELJwaa0O0by0Eq7ZQ%26_nc_sid%3D83d603%26dl%3D1&filename=Don't%20worry%20hooman%20we%20got%20the%20mail%20for%20you!%20-%20Follow%20%40barked%20for%20more%20funny%20dog%20videos!-%20%C2%A0%40sydg32-%23barked%C2%A0%23dog%C2%A0%23doggo%C2%A0%23Dachshund%20%239gag.mp4&ua=-&referer=https%3A%2F%2Fwww.instagram.com%2F";
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
