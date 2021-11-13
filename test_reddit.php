<?php
require_once("reddit.php");
$reddit = new reddit();

$response = $reddit->getCaptchaReqs();
echo $response;

$link = "http://makezine.com/2014/07/01/makerbot-realeases-ipad-app-for-easy-3d-printing/";
$subreddit = "KissAnimeRu";
$title = "testing";
$response = $reddit->createStory($title, $link, $subreddit);

echo $response;

?>
