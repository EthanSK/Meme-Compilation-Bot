<?php
$enablePosting = true;
$refreshToken = '1/yXkm9lBrNRjXnkmaxBKuzGj6uoJ1ppWsFZMxx7ZEZok';
/**
 * Library Requirements
 *
 * 1. Install composer (https://getcomposer.org)
 * 2. On the command line, change to this directory (api-samples/php)
 * 3. Require the google/apiclient library
 *    $ composer require google/apiclient:~2.0
 */
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}

require_once __DIR__ . '/vendor/autoload.php';

session_start();
//session_unset();
//session_destroy();
/*
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */
$OAUTH2_CLIENT_ID = '739568280829-8h7iok4ok4tp3qmvf0hgkgrcr88beb5e.apps.googleusercontent.com';
$OAUTH2_CLIENT_SECRET = 'NPKBieXrqvK2wLau901k-cSP';

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setScopes('https://www.googleapis.com/auth/youtube');
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
    FILTER_SANITIZE_URL);
//$redirect = "http://youtubebot.com/youtubebotphp/youtubeupload.php";
$client->setRedirectUri($redirect);
$client->refreshToken($refreshToken);
// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);

// Check if an auth token exists for the required scopes
$tokenSessionKey = 'token-' . $client->prepareScopes();
if (isset($_GET['code'])) {
  if (strval($_SESSION['state']) !== strval($_GET['state'])) {
    die('The session state did not match.');
  }

  $client->authenticate($_GET['code']);
  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
  header('Location: ' . $redirect);
}

if (isset($_SESSION[$tokenSessionKey])) {
  $client->setAccessToken($_SESSION[$tokenSessionKey]);
}
 echo ("<pre>");
 echo 'access token';
   print_r($client->getAccessToken());
    echo ("</pre>");



    // REPLACE this value with the path to the file you are uploading.
    $videoPath = "./finalVids/".$memesToUpload[0];
    //$videoPath  = 'meme.mp4';
     echo ("<pre>");
       print_r($videoPath);
        echo ("</pre>");

        $shouldStopMeme= false;
        $memesToUpload = file('memesToUpload.txt', FILE_IGNORE_NEW_LINES);
        if (empty($memesToUpload)){
          $shouldStopMeme = true;
        }
$description = "Thanks for watching!

Check out and subscribe to my personal Youtube channel:
https://goo.gl/3Nznco

Make sure you check out our amazing Facebook meme page:
https://goo.gl/ji90qL

Follow us on Twitter for news and updates:
 https://goo.gl/uNAfzY

Play some dank meme games and other games on your mobile phone:
https://goo.gl/GH9e3l

Here are some Facebook meme page bots we have created:

https://www.facebook.com/memewarsincomments/
https://www.facebook.com/videomemesbot/
https://www.facebook.com/sosigmemes/
https://www.facebook.com/memesdelafresh/
https://www.facebook.com/thenextdigitofpieveryhour2/
https://www.facebook.com/myfirmbelief/
https://www.facebook.com/heckinbamboozledbot/
https://www.facebook.com/weneedtobuildawallV2/
https://www.facebook.com/The-same-inspirational-video-of-Jim-Carrey-every-day-1302880266453784/
https://www.facebook.com/It-is-Wednesday-my-dudes-every-day-639670602886547/
https://www.facebook.com/TheSamePhotoofHughMungusEveryDay/
https://www.facebook.com/youareuglyandhavenofriends/";

$volume = 1;

    $memesAlreadyUploaded = file('memesactuallyuploaded.txt', FILE_IGNORE_NEW_LINES);
    foreach ($memesAlreadyUploaded as $keyuploaded => $valueuploaded) {
    if ($valueuploaded == $videoPath){
      $shouldStopMeme= true;
    }
    }
     echo ("<pre>");
     echo 'should stop meme: ';
       print_r($shouldStopMeme);
        echo ("</pre>");

// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) {
  $htmlBody = '';
  //put the if meme already upldoade thing here
try{
        // Call the channels.list method to retrieve information about the
        // currently authenticated user's channel.
        $channelsResponse = $youtube->channels->listChannels('contentDetails', array(
          'mine' => 'true',
        ));

        $htmlBody = '';
        foreach ($channelsResponse['items'] as $channel) {
          // Extract the unique playlist ID that identifies the list of videos
          // uploaded to the channel, and then call the playlistItems.list method
          // to retrieve that list.
          $uploadsListId = $channel['contentDetails']['relatedPlaylists']['uploads'];

          $playlistItemsResponse = $youtube->playlistItems->listPlaylistItems('snippet', array(
            'playlistId' => $uploadsListId,
            'maxResults' => 50
          ));
          $arrayOfVideos = (array) $playlistItemsResponse;
        //$jsonOfVideos = json_encode($playlistItemsResponse);
        //$arrayOfVideos = json_decode($jsonOfVideos, true);
 echo ("<pre>");
   print_r($playlistItemsResponse);
    echo ("</pre>");

$latestVideoTimestamp = 0;
 echo ("<pre>");
 echo 'current timestamp: ';
   print_r(time());
    echo ("</pre>");

foreach ($playlistItemsResponse['items'] as $keyVideo => $valueVideo) {
  if (strtotime($valueVideo['snippet']['publishedAt'])> $latestVideoTimestamp){
$latestVideoTimestamp = strtotime($valueVideo['snippet']['publishedAt']);

}
}

 echo ("<pre>");
 echo 'most recent video: ';
   print_r($latestVideoTimestamp);
    echo ("</pre>");
    if ($latestVideoTimestamp + 8 > time()){
      $shouldStopMeme = true;
      echo 'not enough time has passed';
      $stopBecauseOfTime = true;
    }else{
      $stopBecauseOfTime = false;
    }
    foreach ($playlistItemsResponse['items'] as $keyVideo => $valueVideo) {
      if (strtotime($valueVideo['snippet']['publishedAt']) == $latestVideoTimestamp){
        $titleArray = explode(" ", $valueVideo['snippet']['title']);
        $previousVolume = substr(end($titleArray), 1);
        $volume = $previousVolume +1;

    }
    }


          $htmlBody .= "<h3>Videos in list $uploadsListId</h3><ul>";
          foreach ($playlistItemsResponse['items'] as $playlistItem) {
            $htmlBody .= sprintf('<li>%s (%s)</li>', $playlistItem['snippet']['title'],
              $playlistItem['snippet']['resourceId']['videoId']);

          }
          $htmlBody .= '</ul>';
        }





        if (!$shouldStopMeme){

    // Create a snippet with title, description, tags and category ID
    // Create an asset resource and set its snippet metadata and type.
    // This example sets the video's title, description, keyword tags, and
    // video category.
    $snippet = new Google_Service_YouTube_VideoSnippet();
    $snippet->setTitle("HILARIOUS Dank Memes Compilation #$volume");
    $snippet->setDescription($description);
    $snippet->setTags(array(
	"memes",
	"funny memes",
	"hilarious memes",
	"dank memes",
	"hilarious dank memes",
	"memes compilation",
	"funny memes compilation",
	"funny videos",
	"funny vines",
	"best vines",
	"best memes",
	"try not to laugh",
	"try not to laugh challenge",
	"laugh",
	"mlg memes",
	"edgy memes",
	"ironic memes",
	"4chan memes",
	"facebook memes",
	"memes for teens",
	"meme compilation",
	"compilation",
	"meme",
	"funny meme",
	"pyrocynical",
	"you laugh you lose",
	"meme vine compilations",
	"meme vine",
	"trump",
	"mlg",
	"meme stream",
	"postpartum depression",
	"lol",
	"bant",
	"haha"));
    // Numeric video category. See
    // https://developers.google.com/youtube/v3/docs/videoCategories/list
    $snippet->setCategoryId("23");

    // Set the video's status to "public". Valid statuses are "public",
    // "private" and "unlisted".
    $status = new Google_Service_YouTube_VideoStatus();
    $status->privacyStatus = "public";

    // Associate the snippet and status objects with a new video resource.
    $video = new Google_Service_YouTube_Video();
    $video->setSnippet($snippet);
    $video->setStatus($status);

    // Specify the size of each chunk of data, in bytes. Set a higher value for
    // reliable connection as fewer chunks lead to faster uploads. Set a lower
    // value for better recovery on less reliable connections.
    $chunkSizeBytes = 1 * 1024 * 1024;

    // Setting the defer flag to true tells the client to return a request which can be called
    // with ->execute(); instead of making the API call immediately.
    $client->setDefer(true);

    // Create a request for the API's videos.insert method to create and upload the video.
    $insertRequest = $youtube->videos->insert("status,snippet", $video);

    // Create a MediaFileUpload object for resumable uploads.
    $media = new Google_Http_MediaFileUpload(
        $client,
        $insertRequest,
        'video/*',
        null,
        true,
        $chunkSizeBytes
    );
    $media->setFileSize(filesize($videoPath));


    // Read the media file and upload it chunk by chunk.
    $status = false;
    $handle = fopen($videoPath, "rb");
    while (!$status && !feof($handle)) {
      $chunk = fread($handle, $chunkSizeBytes);
      $status = $media->nextChunk($chunk);
    }

    fclose($handle);

    // If you want to make other calls after the file upload, set setDefer back to false
    $client->setDefer(false);

    file_put_contents('memesactuallyuploaded.txt', $videoPath."\n", FILE_APPEND);

    // ihave no idea wtf this block does
    // foreach ($memesToUpload as $keytoupload => $valuetoupload) {
    //   if ($keytoupload>=1){
    //   file_put_contents('memesToUpload.txt', $valuetoupload."\n", FILE_APPEND);
    // }
    // }

    $htmlBody .= "<h3>Video Uploaded</h3><ul>";
    $htmlBody .= sprintf('<li>%s (%s)</li>',
        $status['snippet']['title'],
        $status['id']);

    $htmlBody .= '</ul>';


  }else{
    if ($stopBecauseOfTime){
      echo '  stopped because not enough time has passed  ';
    }else{
    echo '  stopped duplicate  ';
  }
  }} catch (Google_Service_Exception $e) {
    $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
    $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  }

  $_SESSION[$tokenSessionKey] = $client->getAccessToken();
} elseif ($OAUTH2_CLIENT_ID == 'REPLACE_ME') {
  $htmlBody = <<<END
  <h3>Client Credentials Required</h3>
  <p>
    You need to set <code>\$OAUTH2_CLIENT_ID</code> and
    <code>\$OAUTH2_CLIENT_ID</code> before proceeding.
  <p>
END;
} else {
  // If the user hasn't authorized the app, initiate the OAuth flow
  $state = mt_rand();
  $client->setState($state);
  $_SESSION['state'] = $state;

  $authUrl = $client->createAuthUrl();
  $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
}
?>

<!doctype html>
<html>
<head>
<title>Video Uploaded</title>
</head>
<body>
  <?=$htmlBody?>
</body>
</html>
