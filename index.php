<?php
ini_set('memory_limit','2648M');
echo("<pre>");
echo 'start';
exec('unset DYLD_LIBRARY_PATH ;');
putenv('DYLD_LIBRARY_PATH');
putenv('DYLD_LIBRARY_PATH=/usr/bin');
if (!session_id()) {
    session_start();
}
date_default_timezone_set('Europe/London');
require_once('functions.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);
$time = time();
$date = new DateTime(null, new DateTimeZone('Europe/London'));
$timeLocal =  $date->getTimestamp() + $date->getOffset();
echo 'time: ';
 echo("<pre>");
 echo date("Y-m-d H:i:s\n");
   print_r($date->getTimestamp() + $date->getOffset());
    echo("</pre>");
// if (!session_id()) {
//     session_start();
// }

//settings----------------------------------------------------------------------------------
$shouldMakeTheVideo = true;//this is so it can add download the new memes during the day while the video is not being made
$lengthOfVideo = 600;
$lengthOfFBVideoAllowed = 600;//10 mins for indiviudal memes
$timeAllowedForMaxClipInCompilationToBe = 150;//it will allow a few videos over 20 seconds near the end. also i should make i have to use remaining videos if there are none remaining under this tim elimit.
$timeLimitOfEachVideo = 25;
$timeBeforeLongVideosCanStartKickingIn = 250;//the higher this is the higher the audience retetnion and therefore more views
$ffmpeg = './ffmpeg';
$youtubedl = './youtube-dl';//for downloading the fb vids since now they b l o c c source param in api req
$timeAllowedToStartRunning = '02:00';//we set time coz this bot is cpu intensive and i don't want it runnig while im using the computer.
$timeToStopStartingRun = '12:00';//MUST BE 24 HOUR TIME. 11PM IS 23 00
$numberOfVidsToTryAndMakeADay = 3;//THIS IS JUST NUMBER OF NORMAL VIDS TO MAKE ECAH DAY....FOR NOW 0 COZ MEME STREAM NEEDS A BREAK
$memesUsedInLastNsetsNValue = count(makeTextFileUniqueAndReturnIt("videomemestopost.txt"))/750;//this means a video will guaranteed not contain any id that appeard in the last N videos
$limiterMax = 600;//the number of videos to be considered for one round of compliation making
$useRandomMemes = false;//this ishould always be true. we will never have enoug to use only new memes. wrong - if it used the memes in the last n sets it will not use them again.
$minAgeOfFBVideoAllowed = 86400;//24 hours to make sure zuccworthy ivideos have already been zucced by the time it comes to posting so we don't get striked
$maximumAgeForAVideoToBeConsideredNewEnoughToAppendNotInsert = 604800;//one week
$shouldCreateIndividualMemes = true;
$shouldGoInsaneWithVideoCreation = false;
$minAgeOfVideoToUseJsonMetadata = 604800;//1 week
$nameOfBackgroundFile = "background2_correctproperties.mov";
$nameOfWatermarkFile = "memestreamwatermark.png";
$nameOfSubscribeTextFile = "subscribetext2.png";
$nameOfLogoToDisplayInBottomLeft = "mslogo2.png";
$nameOfLogoToDisplayInTopLeft = "memestreamtriangles.png";
$finalOutputFolder = "./finalVids";
$nameOfIntroFile = "memeCompIntro.mp4";
$nameOfOutroFile = "outroUniversal.mp4";
//----------------------------------------------------------------------------------------
echo 'memes used in last n sets value'. $memesUsedInLastNsetsNValue;
date_default_timezone_set('Europe/London');
$currentTime = date('H:i');

$isThisCompilationInsane = false;
$finalVidsArray = scandir_only_wanted_files("./finalVids");
$numberOfFinalVidsCreatedInCurrentSetTime = 0;
$timeBotWillBeRunning = max(strtotime("today $timeToStopStartingRun")-strtotime("today $timeAllowedToStartRunning"), strtotime("tomorrow $timeToStopStartingRun")-strtotime("today $timeAllowedToStartRunning"));
prettyPrint("time bot will be running: $timeBotWillBeRunning  ". gmdate('H:i:s', $timeBotWillBeRunning));
//$startTimeStampThisSet = max(strtotime("today $timeToStopStartingRun") - $timeBotWillBeRunning,strtotime("tomorrow $timeToStopStartingRun") - $timeBotWillBeRunning );

if (strtotime("today $timeAllowedToStartRunning") > strtotime("now")){
  $startTimeStampThisSet = strtotime("yesterday $timeAllowedToStartRunning");
}else{
  $startTimeStampThisSet = strtotime("today $timeAllowedToStartRunning");

}

prettyPrint("start time today: $startTimeStampThisSet  ".date('D-m H:i:s', $startTimeStampThisSet));

foreach ($finalVidsArray as $key => $value) {
  $videoCreationTime = filemtime("./finalVids/" . $value);
  prettyPrint("creationtime $value $videoCreationTime");


  if ($videoCreationTime >= $startTimeStampThisSet){
    prettyPrint("$value is between time limits at $videoCreationTime");
    $numberOfFinalVidsCreatedInCurrentSetTime++;
  }
}
prettyPrint("number of videos created 'today' : $numberOfFinalVidsCreatedInCurrentSetTime");
if ($numberOfFinalVidsCreatedInCurrentSetTime >= $numberOfVidsToTryAndMakeADay && $shouldGoInsaneWithVideoCreation){
  prettyPrint("going insane.");
  $isThisCompilationInsane = true;//ie no longer create for meme stream but create for insane channel
}else{
  prettyPrint("not going insane");
}


if ($isThisCompilationInsane){ //this is just for anothre channel, so it creates for two different channnels
  $nameOfBackgroundFile = "background.mov";
  $nameOfWatermarkFile = "insanewatermark.png";
  $nameOfSubscribeTextFile = "empty.png";
  $nameOfLogoToDisplayInBottomLeft = "empty.png";
  $nameOfLogoToDisplayInTopLeft = "empty.png";
  $finalOutputFolder = "./finalVidsInsane";
  $nameOfIntroFile = "insaneintro.mp4";
  $nameOfOutroFile = "insaneintro.mp4";

}

//we wanna check for if there were N final videos already created in this current set's time, because if so, we need not make any more and can go insane.

$rand = mt_rand(0, 1000);
$order = substr(time(), -5);
$videolist = file('videolist.txt', FILE_IGNORE_NEW_LINES);
$memesAlreadyPosted = file('memesalreadyposted.txt', FILE_IGNORE_NEW_LINES);
$arrayOfOutputVideoNames = array();
$totalVideoDurationInTempDownloadedMemesFolderInSeconds = 0;//not a setting
$counterVideoIndex = 1;
// $lastTimeWeRanOutOfNewMemes = file('ranOutOfMemesTracker.txt', FILE_IGNORE_NEW_LINES);
// $lastTimeWeRanOutOfNewMemes = end($lastTimeWeRanOutOfNewMemes);
// if ($lastTimeWeRanOutOfNewMemes + 604800 > time()) {
//     //ie it hasnt been a week
//     $useRandomMemes = true;
// } else {
//     $useRandomMemes = false;
// }

prettyPrint("use random memes? $useRandomMemes");
$limiter = 0;








$isBetweenTimeLimits = isBetweenTimeLimits($timeAllowedToStartRunning, $timeToStopStartingRun, $currentTime);

echo 'is between time limits: '. $isBetweenTimeLimits;
$refreshRate;
if ($isBetweenTimeLimits) {
    $timeBotWillBeRunning = max(strtotime("today $timeToStopStartingRun")-strtotime("today $timeAllowedToStartRunning"), strtotime("tomorrow $timeToStopStartingRun")-strtotime("today $timeAllowedToStartRunning"));
    prettyPrint("time bot will be running: $timeBotWillBeRunning");
    $refreshRate = round($timeBotWillBeRunning/$numberOfVidsToTryAndMakeADay);
    if ($numberOfVidsToTryAndMakeADay ==0){
      $refreshRate = 0;//it means we are on insane mode. we can't divide by 0
    }
    echo 'refresh rate', gmdate("H:i:s", $refreshRate). $refreshRate;
    echo "should reload at: ". gmdate("H:i:s", $refreshRate +  $date->getTimestamp() + $date->getOffset());
    header("refresh:$refreshRate;url=index.php");//keep this non commented coz it always uses the latter header
    //appendToTxtFile("lastReloaded.txt", "current time: $timeLocal time to reload" . ($refreshRate + $timeLocal));//at bottom of script;
    $remainingTimeTillTimeLimitStop = 0;//needed for bottom
  }
 else {
    //$remainingTimeTillTimeLimitStop =  min(abs(strtotime("today $timeAllowedToStartRunning")-strtotime('now')), abs(strtotime("tomorrow $timeAllowedToStartRunning")-strtotime('now')));//this is wrong lol
    $remainingTimeTillTimeLimitStop = max(strtotime("today $timeAllowedToStartRunning")-strtotime('now'), strtotime("tomorrow $timeAllowedToStartRunning")-strtotime('now'));
    $refreshRate = $remainingTimeTillTimeLimitStop;
    echo 'gonna wait this long before reloading again:'. date("H:i:s", $remainingTimeTillTimeLimitStop);
    header("refresh:$refreshRate;url=index.php");
}
echo "</pre>";
//echo shell_exec("/usr/local/bin/mp4box -add meme.mp4 -cat Tmpfile.mp4 -cat meme.mp4 output.mp4  2>&1");

//$memesalreadyintempdownloadfile = file('memesintempdownloadfile.txt', FILE_IGNORE_NEW_LINES);
$videosActuallyInTheTempDownloadFile = scandir_only_wanted_files("tempDownloadedMemes/");
$toDecode = file_get_contents('temparray.txt');

//printArray($videosActuallyInTheTempDownloadFile, "videos in temp dowload file");

$decodedArray = json_decode($toDecode, true);
$arrayOfVideosWithFullInformation = $decodedArray;
require 'videoprocessing.php';
echo 'done video processing file';
//delet files that still havent loaded in from last reload (ie corrupted)
foreach ($arrayOfVideosWithFullInformation as $key => $value) {

    //this is for if we just loop through the videos we are concerned about for this compilation.
    if (!in_array($value['id'].".mp4", $videosActuallyInTheTempDownloadFile)) {
        continue;
    }



    $value = $value['id'] . ".mp4";

    //-------
    $checkForErrors =  shell_exec("$ffmpeg -i ./tempDownloadedMemes/$value 2>&1");
    echo("<pre>");
    print_r($checkForErrors);
    echo("</pre>");
    echo $value;
    if (stripos($checkForErrors, "Invalid data found when processing input") !== false) {
        echo ' deleting an actually bad file ';
        unlink("./tempDownloadedMemes/$value");
    }
    $numberOfStreams = substr_count($checkForErrors, "Stream");
    echo("<pre>");
    echo $key.' numberr of streams: ';
    print_r($numberOfStreams);
    echo("</pre>");
    if ($numberOfStreams != 2) {
        echo 'not enough streams so unlinking '. $value;
        file_put_contents("invalidIDs.txt", substr($value, 0, -4) . "\n", FILE_APPEND);
        unlink("./tempDownloadedMemes/".$value);
    }

    //   $currentVideoDuration =  shell_exec("$ffmpeg -i ./tempDownloadedMemes/$value 2>&1 | grep Duration");
    //   $actualDuration = substr($currentVideoDuration, 11, 12);
    //   $arrayWithHoursMinsAndSecs = explode(":", $actualDuration);
    //   $thisVideoDurationInSeconds = $arrayWithHoursMinsAndSecs[2] + $arrayWithHoursMinsAndSecs[1]*60 + $arrayWithHoursMinsAndSecs[0]*3600;
    // echo ("<pre>");
    // echo'array duration';
    //  print_r($arrayWithHoursMinsAndSecs);
    //   echo ("</pre>");
    // echo ("<pre>");
    // echo  'this vid Duration:';
    //  print_r($thisVideoDurationInSeconds);
    //   echo ("</pre>");
    //


        // if ($thisVideoDurationInSeconds > $timeAllowedForMaxClipInCompilationToBe){
        //   //actually we dont want this because of the new individual meme uplaoder. we want the memes to stay in the temp download file
        //   echo 'video too long so deleting from temp';
        //   file_put_contents("invalidIDs.txt", substr($value, 0, -4) . "\n", FILE_APPEND );
        //   unlink("./tempDownloadedMemes/".$value);
        //
        // }
}
echo 'done doing the number of streams checking';
$arrayOfTempDownloadedFilesizes =  array();


    //$badVideoURLs = file("badVideoURLs.txt", FILE_IGNORE_NEW_LINES);

//need to remove memes in tempdownloaded file when they are delet. actually no, the file is used for jack shit.
foreach ($arrayOfVideosWithFullInformation as $key => $value) {
    ob_flush();
    flush();
    if (in_array($value['id'], $badVideoURLs)) {
        continue;
    }

    if (!in_array($value['id'].".mp4", $videosActuallyInTheTempDownloadFile)) {
        try {
            //  file_put_contents("badVideoURLs.txt",$value['id']."\n", FILE_APPEND);

            echo $key. 'trying to download '. $value['id'];
            //file_put_contents("tempDownloadedMemes/".$value['id'].".mp4", fopen($value['source'], 'r'));//we have no source key any more rip
            shell_exec("$youtubedl -o './tempDownloadedMemes/$value[id].mp4' https://facebook.com/$value[id]");
            echo("<pre>");
            echo $key. 'downloaded: '. $value['id'];
            echo("</pre>");
        } catch (Exception $e) {
            echo $e->getMessage();
            echo 'caught, now sholud continue loop';
        }


        //file_put_contents("memesintempdownloadfile.txt", $value['id']."\n", FILE_APPEND);
    } else {
        echo("<pre>");
        print_r($key.'foundin temp downloads so not downloadding again '.$value['id']);
        echo("</pre>");
    }
}
echo 'done downloading';
//ah, it is actually good we did this in the end, because unlike ffmpeg operations where the script won't continue until the operation is complete, here it adds the video to the temp downlaoded file, but does not fully load it it until after a while, but the script continues while it is loading it, so we cannot just assume that it is loaded and try and perform an ffmpeg on it
//we must delete that actually bad files BEFORE the new ones are loaded in (ie from last load)
//actually, just realised it doesnt do this, but actually was just loadidng in bad urls really quiclky so it loodked like that was happening fukc.
$videosActuallyInTheTempDownloadFile = scandir_only_wanted_files("tempDownloadedMemes/");
file_put_contents("videolist.txt", "");

$arrayOfIdsWithTheirDuration=array();
 echo("<pre>");
 echo 'array of videos with full information ';
   print_r($arrayOfVideosWithFullInformation);
    echo("</pre>");

foreach ($arrayOfVideosWithFullInformation as $key => $value) {
    echo 'going through a video';
    if (in_array($value['id'].".mp4", $videosActuallyInTheTempDownloadFile)) {
        //i dunno why we need this but apparently my method of relying on reading the temp downloads dir didnt work as i got 2 duplicate videos hmm
        if (in_array($value['id'], $memesAlreadyPosted)) {
            //turning these off since now im making it randomly select videos
  //  unlink("./tempDownloadedMemes/".$value['id'].".mp4");
  //  continue;
        }

        echo 'checking for errors: ';
        //there will be the 'error' that one output file must be specified but that's fine it can be ignored it affects nothing
        $checkForErrors =  shell_exec("$ffmpeg -i ./tempDownloadedMemes/".$value['id'].".mp4 2>&1");

        //it may look as if we are sorting an empty fucking array but actually with ever loop the array gets bigger, and this is in a loop
        echo("<pre>");
        print_r($checkForErrors);
        echo("</pre>");
        asort($arrayOfIdsWithTheirDuration);


        if (stripos($checkForErrors, "Invalid data found when processing input") !== false) {
            //this causes it to delete all the files that havent loaded fully. Therefore, do not delete them, just continue
            echo 'invalid data found so continuing';
            //unlink("./tempDownloadedMemes/".$value['id'].".mp4");
            continue;
        }
        $numberOfStreams = substr_count($checkForErrors, "Stream");
        echo("<pre>");
        //  echo 'number of streams (2nd loop): ';
        //print_r($numberOfStreams);
        echo("</pre>");
        if ($numberOfStreams != 2) {
            //unlink("./tempDownloadedMemes/".$value['id'].".mp4");
            echo 'not enough streams so continuing '  ;
            continue;
        }

        $currentVideoDuration =  shell_exec("$ffmpeg -i ./tempDownloadedMemes/".$value['id'].".mp4 2>&1 | grep Duration");
        $actualDuration = substr($currentVideoDuration, 11, 12);
        $arrayWithHoursMinsAndSecs = explode(":", $actualDuration);
        //$arrayWithHoursMinsAndSecs = array_map('trim',$arrayWithHoursMinsAndSecs);
        $thisVideoDurationInSeconds = $arrayWithHoursMinsAndSecs[2] + $arrayWithHoursMinsAndSecs[1]*60 + $arrayWithHoursMinsAndSecs[0]*3600;

        if ($totalVideoDurationInTempDownloadedMemesFolderInSeconds < $timeBeforeLongVideosCanStartKickingIn && $thisVideoDurationInSeconds >$timeLimitOfEachVideo) {
          prettyprint("total video duration in temp downloaded less than time before long videos can start kiking in and this vid duration is greater than the time limit of each video");
            continue;
        }
        echo("<pre>");
        echo'array duration';
        print_r($arrayWithHoursMinsAndSecs);
        echo("</pre>");
        echo("<pre>");
        echo  'this vid Duration:';
        print_r($thisVideoDurationInSeconds);
        echo("</pre>");

        $previousIDDuration = end($arrayOfIdsWithTheirDuration);
        //rather than stop videos based on duration, stop them based on filesize
        //actuatlly, lets do both for good measure
        //    if ($previousIDDuration == $thisVideoDurationInSeconds){
        //so basically this duration check should almost defo ensure duplicates dont come up but the downside is that loads of videos are actually the same length but the videos themselves are different so we have to scan a LOT of videos
        if (in_array($thisVideoDurationInSeconds, $arrayOfIdsWithTheirDuration)) {
            echo 'video durations were the same';

            continue;
        }

        if (in_array(filesize("./tempDownloadedMemes/".$value['id'].".mp4"), $arrayOfTempDownloadedFilesizes)) {
          prettyPrint("found video of same file size so continuing");
            continue;
        }
        array_push($arrayOfTempDownloadedFilesizes, filesize("./tempDownloadedMemes/".$value['id'].".mp4"));
        if ($thisVideoDurationInSeconds > $timeAllowedForMaxClipInCompilationToBe) {
          prettyPrint("this vid duration is higher than timeAllowedForMaxClipInCompilationToBe");
            continue;
        }
        if ($thisVideoDurationInSeconds > $timeAllowedForMaxClipInCompilationToBe && $shouldCreateIndividualMemes){
          //place in individual upload file
          copy("./tempDownloadedMemes/".$value['id'].".mp4" ,"./longIndividualMemes/tempname_".$value['id'].".mp4");
        }
        $arrayOfIdsWithTheirDuration[$value['id']] = $thisVideoDurationInSeconds;

        $totalVideoDurationInTempDownloadedMemesFolderInSeconds += $thisVideoDurationInSeconds;

        //place these memes in a txt to be concatenated
        file_put_contents("videolist.txt", "file '". $value['id'].".mp4'\n", FILE_APPEND);


        if ($totalVideoDurationInTempDownloadedMemesFolderInSeconds >= 620) {
            echo 'max video length reached';
            break;
        }
    }

    asort($arrayOfIdsWithTheirDuration);
}


echo("<pre>");
echo 'total video duration: ';
  print_r($totalVideoDurationInTempDownloadedMemesFolderInSeconds);
   echo("</pre>");
   $videolist = file('videolist.txt', FILE_IGNORE_NEW_LINES);
   //this is to sort the videolist array based on duration
  //asort($arrayOfIdsWithTheirDuration);

 echo("<pre>");
 echo 'array of ids with their duration: ';
   print_r($arrayOfIdsWithTheirDuration);
    echo("</pre>");

$arrayOfIDsThatWillBePostd = array();
//this is to go through each value in the sorted array and resize it to 1080x1080
function random_color_part()
{
    return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
}

function random_color()
{
    return random_color_part() . random_color_part() . random_color_part();
}
foreach ($arrayOfIdsWithTheirDuration as $key => $value) {
    // if ($notTesting) {
    //     $getPageName = $fb->get('/'.$key.'?fields=from');
    //     $getPageName = $getPageName->getGraphNode()->asArray();
    // }
    foreach ($arrayOfVideosWithFullInformation as $key2 => $value2) {
      if ($value2['id'] == $key){
        $getPageName = $value2;
        break;
      }
    }
    echo("<pre>");
    echo 'page this was from: ';
    print_r($getPageName['from']['name']);
    echo("</pre>");
    $watermarkString = $counterVideoIndex." - ".$getPageName['from']['name'];//this needs to have : removed
    $watermarkString = str_replace("'", "", $watermarkString);//apparenlty one backslash is not enough to escape so nvm we just replace it with nothing
    $watermarkString = str_replace(":", "", $watermarkString);
    $watermarkString = str_replace("%", " ", $watermarkString);
    $watermarkString = str_replace("]", " ", $watermarkString);
    $watermarkString = str_replace("[", " ", $watermarkString);

    //  $watermarkString = preg_replace("/[^A-Za-z0-9?!\s. -]/", "", $watermarkString);
    // $videoFileName = substr($value, $startOfVideoFileName+1);
    // $videoFileName = str_replace("'","",$videoFileName);
    // $order++;
    $videoFileName = $key.".mp4";
    echo("<pre>");
    print_r($videoFileName);
    echo("</pre>");
    array_push($arrayOfIDsThatWillBePostd, $key);
    //not this one
    //echo shell_exec("$ffmpeg -i $videoFileName -vf 'scale=:1080:force_original_aspect_ratio=decrease,pad=1280:720:(ow-iw)/2:(oh-ih)/2' -video_track_timescale 90000 resized$order.mp4 2>&1");
    //resize:
    //echo shell_exec("$ffmpeg  -i ./tempDownloadedMemes/$videoFileName -filter_complex 'scale=1080:-1,pad=1080:1080:(ow-iw)/2:(oh-ih)/2:0x2F2F2F,setpts=PTS-STARTPTS' -video_track_timescale 100000 ./resizedVideos/resized_$videoFileName 2>&1");

    //get width and height from a decoded array


    echo "this top one is to get dimensions: \n";
    $videoInfo = shell_exec("$ffmpeg -i ./tempDownloadedMemes/$videoFileName 2>&1 ");
    $videoInfoArray = explode(",", $videoInfo);
    $dimensions = explode("x", $videoInfoArray[11]);
    $width = $dimensions[0];
    $height = explode(" ", $dimensions[1])[0];
    echo("<pre>");
    echo 'width: ';
    echo $width. "\n";
    echo 'height: ';
    echo $height . "\n";
    echo("</pre>");

    echo 'videoinfo: ';
    print_r($videoInfo);

    // $numberOfStreams = substr_count($videoInfo,"Stream");
    //  echo ("<pre>");
    //   echo 'number of streams: ';
    //    print_r($numberOfStreams);
    //     echo ("</pre>");
    //     //if there is one stream and it is a video stream, then add weed engine thomas
    // if ($numberOfStreams = 1 ){
    //   continue;
    //   //actuall I decided it is best to not have quiet memes. Just skip them.
    // //  echo 'adding quiet overlay: ';
    //  //echo shell_exec("$ffmpeg -f lavfi -i aevalsrc=0 -i ./tempDownloadedMemes/$videoFileName -shortest -c:v copy -c:a aac -strict -2 ./tempDownloadedMemes/audiotest.mp4 2>&1");
    //
    // }



    echo random_color();

    $randomeHexColour = random_color();
    //this one works yay - it only works for square or horizontal vids. need to make an if else for vertical vids
    if ($width >= $height) {
        echo shell_exec("$ffmpeg -i ./tempDownloadedMemes/$videoFileName -filter_complex 'setpts=PTS-STARTPTS,scale=1080:-1,pad=1080:1080:(ow-iw)/2:(oh-ih)/2:0x$randomeHexColour,setsar=sar=1/1,fps=fps=30' -qscale 0 -video_track_timescale 15360 ./resizedVideos/resizedNoCounter_$videoFileName 2>&1");
    } else {
        echo 'vertical video ';
        echo shell_exec("$ffmpeg -i ./tempDownloadedMemes/$videoFileName -filter_complex 'setpts=PTS-STARTPTS,scale=-1:1080,pad=1080:1080:(ow-iw)/2:(oh-ih)/2:0x$randomeHexColour,setsar=sar=1/1,fps=fps=30' -qscale 0 -video_track_timescale 15360 ./resizedVideos/resizedNoCounter_$videoFileName 2>&1");
    }
    //watermark index of video
    echo shell_exec("$ffmpeg -i ./resizedVideos/resizedNoCounter_$videoFileName -filter_complex drawtext=fontfile='Champagne & Limousines.ttf:text=''$watermarkString'': fontcolor=F8E0F7: fontsize=35' -qscale 0 -codec:a copy ./resizedVideos/resized_$videoFileName 2>&1");

    array_push($arrayOfOutputVideoNames, "resized_$videoFileName");


    $counterVideoIndex++;
    echo("<pre>");
    echo 'counter video index: ';
    print_r($counterVideoIndex);
    echo("</pre>");

    echo("<pre>");
    echo 'watermark string: ';
    print_r($watermarkString);
    echo("</pre>");

}


file_put_contents('resizedvideolist.txt', "");
foreach ($arrayOfOutputVideoNames as $key => $value) {
    //$limiter++;
    //if ($limiter >3 )break;
    //file_put_contents('resizedvideolist.txt',  "file 'resizedVideos/$value'\n", FILE_APPEND);
    file_put_contents('resizedvideolist.txt', "resizedVideos/$value\n", FILE_APPEND);
}
echo 'resized videos';
echo("<pre>");
//concatenate videos:
//echo shell_exec("$ffmpeg -auto_convert 1 -f concat -i resizedvideolist.txt -qscale 0 -vcodec mpeg4 concatenated_$time.mp4 2>&1");
// echo shell_exec("$ffmpeg -auto_convert 1 -f concat -i resizedvideolist.txt -qscale 0 -vcodec mpeg4 concatenated_$time.mp4 2>&1");

   echo("</pre>");

//well this failed
//if using this, remember to change the file put contents into resized video list to the first one where it places the word file in front of each line
$resizedVideoList = file('resizedVideoList.txt', FILE_IGNORE_NEW_LINES);

$inputStringForConcat = "";
$concatString = "";
$concateNvalue = 0;
 foreach ($resizedVideoList as $key => $value) {
     $checkForErrors =  shell_exec("$ffmpeg -i $value 2>&1");
     $numberOfStreams = substr_count($checkForErrors, "Stream");
     echo("<pre>");
     echo $key.' number of streams: ';
     print_r($numberOfStreams." ". $value);
     echo("</pre>");

     if ($numberOfStreams != 2) {
         echo 'not enough streams so invalidating ';
         file_put_contents("invalidIDs.txt", substr($value, 22, -4) . "\n", FILE_APPEND);

         unlink("./tempDownloadedMemes/".$value);
         $videCreationStoppedCozNotEnoughStreamsInOneOfTheConcats = true;
     }
     //  $checkForErrors =  shell_exec("$ffmpeg -i ./tempDownloadedMemes/".$value['id'].".mp4 2>&1" );
     //   echo ("<pre>");
     //   echo $checkForErrors;
     //      echo ("</pre>");
     //
     //      if (stripos($checkForErrors, "Invalid data found when processing input") !== false){
     //        unlink("./tempDownloadedMemes/".$value['id'].".mp4");
     //        continue;
     //      }
     //$limiter++;
     //if ($limiter > 2)break;
     $inputStringForConcat .= " -i ".$value;
     $concatString .= "[$key:0][$key:1]";
     $concateNvalue++;
 }
 $concatString .= " concat=n=$concateNvalue:v=1:a=1[v][a]";
  echo("<pre>");
    print_r($inputStringForConcat);
    print_r($concatString);

     echo("</pre>");
    // $idWereTalkingAboutTemp = '221259188291562_311560232594790';

 echo("<pre>");
 echo shell_exec("$ffmpeg $inputStringForConcat -filter_complex '$concatString' -map '[v]' -map '[a]' -qscale 0 concatenated_$time.mp4 2>&1");
 //echo shell_exec("$ffmpeg -i resizedVideos/resized_221259188291562_312388849178595.mp4 -i resizedVideos/resized_221259188291562_311560232594790.mp4 -filter_complex 'concat=n=2:v=1:a=1[v][a]' -map '[v]' -map '[a]' concatenated_$time.mp4 2>&1");
 //echo shell_exec("/usr/local/bin/mp4box -add resizedVideos/resized_221259188291562_312444612506352.mp4 -cat resizedVideos/resized_1238578182896580_1305089302912134.mp4 -cat resizedVideos/resized_1238578182896580_1305089302912134.mp4 concatenated_$time.mp4  2>&1");

    echo("</pre>");

    echo("<pre>");
    echo 'background addition: ';
    //working but old for testing :
      //echo shell_exec("$ffmpeg -loop 1 -i background.jpg -i meme.mp4 -i 1.png -filter_complex '[1:v]scale=-1:1080[scaledvid]; [0:v][scaledvid]overlay=(main_w-overlay_w)/2:(main_h-overlay_h)/2:shortest=1[overlay1];[overlay1][2:v]overlay=(main_w-overlay_w)/2:(main_h-overlay_h)/2[output]' -map '[output]' background$order.mp4 2>&1");
      //make the actual video
    //  echo shell_exec("$ffmpeg  -i background.mov -i concatenated_$time.mp4 -i memestreamwatermark.png -i subscribe.png -filter_complex '[1:v]scale=1080:-1[scaledvid]; [0:v][scaledvid]overlay=(main_w-overlay_w)/2:(main_h-overlay_h)/2:shortest=1[overlay1];[overlay1][2:v]overlay=((main_w-overlay_w)/2)+400:((main_h-overlay_h)/2)+450[overlay2]; [overlay2][3:v]overlay=5:(main_h-overlay_h)/2[output]; [1:a] apad [outputaudio]' -map '[output]' -map '[outputaudio]' ./finalVidsNoIntroOrOutro/finished_$time.mp4 2>&1");

    //overlay items
    //see if we can make a copy of background that is the length of concat, then use that, then wedont need shorteste.
    $concatVideoDuration =  shell_exec("$ffmpeg -i testoutput.mp4 2>&1 | grep Duration");
    $actualDuration = substr($concatVideoDuration, 11, 12);
    $arrayWithHoursMinsAndSecs = explode(":", $actualDuration);
    $thisVideoDurationInSeconds = $arrayWithHoursMinsAndSecs[2] + $arrayWithHoursMinsAndSecs[1]*60 + $arrayWithHoursMinsAndSecs[0]*3600;
    $modifiedthisVideoDurationInSeconds = gmdate("H:i:s", $thisVideoDurationInSeconds).".".explode('.', $thisVideoDurationInSeconds)[1];

//echo shell_exec("$ffmpeg -i background.mov -y -t $modifiedthisVideoDurationInSeconds -video_track_timescale 15360 -acodec copy -vcodec copy cut_background.mov 2>&1");
//ffmpeg -i background.mov -y -t 00:00:06 -video_track_timescale 15360 -acodec copy -vcodec copy cut_background.mov 2>&1
//command to make the background the same properties as the rest:
//ffmpeg -i background2.mov -y  -video_track_timescale 15360 -acodec copy -vcodec copy background2_correctproperties.mov 2>&1

    // echo shell_exec("$ffmpeg -i background2_correctproperties.mov -i concatenated_$time.mp4 -i memestreamwatermark.png -i subscribetext.png -i mslogo.png -i dankmemecompilationtext.png -i memestream.png -y -filter_complex '[1:v]scale=1080:-1[scaledvid]; [0:v][scaledvid]overlay=(main_w-overlay_w)/2:(main_h-overlay_h)/2[overlay1];[overlay1][2:v]overlay=((main_w-overlay_w)/2)+450:((main_h-overlay_h)/2)+465[overlay2]; [overlay2][3:v]overlay=1495:(main_h-overlay_h)/2+200[overlay3];[overlay3][4:v]overlay=0:main_h-overlay_h[overlay4];[overlay4][5:v]overlay=5:50[overlay5];[overlay5][6:v]overlay=1530:30[output]' -map '[output]' -map 1:1 -qscale 0 -shortest ./finalVidsNoIntroOrOutro/finished_$time.mp4 2>&1");

      echo shell_exec("$ffmpeg -i $nameOfBackgroundFile -i concatenated_$time.mp4 -i $nameOfWatermarkFile -i $nameOfSubscribeTextFile -i $nameOfLogoToDisplayInBottomLeft -i $nameOfLogoToDisplayInTopLeft -y -filter_complex '[1:v]scale=1080:-1[scaledvid]; [0:v][scaledvid]overlay=(main_w-overlay_w)/2:(main_h-overlay_h)/2[overlay1];[overlay1][2:v]overlay=((main_w-overlay_w)/2)+450:((main_h-overlay_h)/2)+465[overlay2]; [overlay2][3:v]overlay=1495:(main_h-overlay_h)/2[overlay3];[overlay3][4:v]overlay=0:main_h-overlay_h[overlay4];[overlay4][5:v]overlay=60:100[output]' -map '[output]' -map 1:1 -qscale 0 -shortest ./finalVidsNoIntroOrOutro/finished_$time.mp4 2>&1");
// ffmpeg -i background.mp4 -i meme.mp4 -i memestreamwatermark.png -i subscribetext.png -i mslogo.png -i dankmemecompilationtext.png -i memestream.png -y -filter_complex '[1:v]scale=1080:-1[scaledvid]; [0:v][scaledvid]overlay=(main_w-overlay_w)/2:(main_h-overlay_h)/2:shortest=1[overlay1];[overlay1][2:v]overlay=((main_w-overlay_w)/2)+450:((main_h-overlay_h)/2)+465[overlay2]; [overlay2][3:v]overlay=1495:(main_h-overlay_h)/2+200[overlay3];[overlay3][4:v]overlay=0:main_h-overlay_h[overlay4];[overlay4][5:v]overlay=5:50[overlay5];[overlay5][6:v]overlay=1530:30[output]' -map '[output]' -map 1:1 ./finalVidsNoIntroOrOutro/finished_$time.mp4 2>&1

    //remember to convert intro and outro to correct formats ffmpeg before concating them
// use this ffmpeg command to do so  : ffmpeg -i memestreamintro.mov -filter_complex 'setpts=PTS-STARTPTS,setsar=sar=1/1,fps=fps=60' -video_track_timescale 15360 -y memestreamintro.mp4 2>&1
// or this for outro: ffmpeg -i memestreamoutro.mov -filter_complex 'setpts=PTS-STARTPTS,setsar=sar=1/1,fps=fps=60' -video_track_timescale 15360 -y memestreamoutro.mp4 2>&1
      echo shell_exec("$ffmpeg -i $nameOfIntroFile -i ./finalVidsNoIntroOrOutro/finished_$time.mp4 -i $nameOfOutroFile -filter_complex 'concat=n=3:v=1:a=1[v][a]' -map '[v]' -map '[a]' $finalOutputFolder/unfinished_$time.mp4 2>&1");
      rename("$finalOutputFolder/unfinished_$time.mp4", "$finalOutputFolder/finished_$time.mp4");



      echo 'done making video';
      //resized_1630069933968528_1647254285583426
     unlink("concatenated_$time.mp4");//reenable this and below
     unlink("./finalVidsNoIntroOrOutro/finished_$time.mp4");


      //reenable this
      //only delete the files if a video was output successfully
      if (file_exists("$finalOutputFolder/finished_$time.mp4")) {
          echo 'file exists '."$finalOutputFolder/finished_$time.mp4" ;

          $finalVideoDuration =  shell_exec("$ffmpeg -i $finalOutputFolder/finished_$time.mp4 2>&1 | grep Duration");
          $actualDuration = substr($finalVideoDuration, 11, 12);
          $arrayWithHoursMinsAndSecs = explode(":", $actualDuration);
          $thisVideoDurationInSeconds = $arrayWithHoursMinsAndSecs[2] + $arrayWithHoursMinsAndSecs[1]*60 + $arrayWithHoursMinsAndSecs[0]*3600;
          echo("<pre>");
          echo 'final video duration: ';
          print_r($thisVideoDurationInSeconds);
          echo("</pre>");
          file_put_contents('lengthOfLastCreatedVideo.txt', "finished_$time.mp4:  "."$thisVideoDurationInSeconds"."\n", FILE_APPEND);

          if ($thisVideoDurationInSeconds < $lengthOfVideo) {
              unlink("$finalOutputFolder/finished_$time.mp4");
              //it should put in a txt the last time we have run out of new memes with the timestamp, then after a week it will stop using random memes and try to start using new ones
              file_put_contents('ranOutOfMemesTracker.txt', time()."\n", FILE_APPEND);
              //when we run out of memes, we need to distribute just used memes because otherwise we will keepon getting the same erro
              $arrayToRedistribute = array();
              foreach ($arrayOfVideosWithFullInformation as $key => $value) {
                array_push($arrayToRedistribute, $value['id']);
              }
              prettyPrint("not long enough so redistributing full info array vids");
              distributeJustUsedMemesRandomlyInVideoMemesToPost($arrayToRedistribute);


          } else {
            if ($isThisCompilationInsane){
              file_put_contents('memestouploadInsane.txt', "finished_$time.mp4"."\n", FILE_APPEND);

            }else{
              file_put_contents('memestoupload.txt', "finished_$time.mp4"."\n", FILE_APPEND);
            }
          }

          foreach ($arrayOfIDsThatWillBePostd as $key => $value) {
              echo "\n".' value: '. $value;
              if ($thisVideoDurationInSeconds >= $lengthOfVideo) {
                  //i think the reason this hasn't been used it because it deletes the temp downloaded memes once it has finished. However, what stops it from reloading the same memes in the next round?
                  //it is meant to be unlinking the memes in temp downloaded so they do not get used again, and the same memes wont be loaded in because they are 'continued' in the for loop in the graph query, i'm not sure why this isnt working fully
                  //oh wait, all along in video proccessing it ignores them and does not query graph for these memes
                  //i think techniaclly memes already posted is actually memes already made ,they have not been posted yet.
                  //memesalreadyposted is not the same as memestoupload because it is a txt with every meme inside the meme compilation, whereas memes to upload is just essentially a scandir of the final vids file, or at least it better be.
                  file_put_contents('memesalreadyposted.txt', $value."\n", FILE_APPEND);

                  file_put_contents("./textFilesWithMemeIDs/finished_$time.txt", $value."\n", FILE_APPEND);

                  file_put_contents('memesUsedInLastNsets.txt', $value."\n", FILE_APPEND);

                  //to be on the safe side i think its best we ignore all the memes in memesalreadyposted.txt as well.
              }
              //just in case graph dies or i need the videos for another purpose, ima stop deleting memes already made into compilations...
      //  unlink("./tempDownloadedMemes/$value.mp4");
          }
          distributeJustUsedMemesRandomlyInVideoMemesToPost($arrayOfIDsThatWillBePostd);
          if ($thisVideoDurationInSeconds >= $lengthOfVideo) {
            appendToTxtFile("memesUsedInLastNsets.txt", '*end_of_compilation*');
          }
          $resizedVideos = scandir_only_wanted_files('./resizedVideos');

          foreach ($resizedVideos as $key => $value) {
              //we do this because it does not overwrite by default, so if the resized videos are kept in the file, then it will not bother overwriting and doing the ffmpeg resize
              if ($thisVideoDurationInSeconds >= $lengthOfVideo) {
                  unlink("resizedVideos/$value");
              }
          }
      } else {
          echo("<pre>");
          print_r('apparently the file doesnt exist');
          echo("</pre>");
      }
       echo("</pre>");

if ($videCreationStoppedCozNotEnoughStreamsInOneOfTheConcats) {
    echo '$videCreationStoppedCozNotEnoughStreamsInOneOfTheConcats';
    echo("<meta http-equiv='refresh' content='1'>");
}
$timeTakenToRunBot = time() - $time;
$refreshRate = max($refreshRate - $timeTakenToRunBot, 0);
if ($shouldGoInsaneWithVideoCreation && $remainingTimeTillTimeLimitStop <= 0){
  $refreshRate = 0;
}
print("actual refresh rate: $refreshRate");
$date = new DateTime(null, new DateTimeZone('Europe/London'));
$timeLocal =  $date->getTimestamp() + $date->getOffset();
$timetoreload = $refreshRate + $timeLocal;
prettyPrint("time to reload $timetoreload");
appendToTxtFile("lastReloaded.txt", "end of script current time: $timeLocal time to reload $timetoreload");
header("refresh:$refreshRate;url=index.php");
if ($refreshRate <= 0){
  echo("<meta http-equiv='refresh' content='1'>");

}
echo 'end of index.php';


//gonna make this into a separate app
//require 'youtubeupload.php';

?>
<meta http-equiv="refresh" content="86400; url=https://www.youtubebot.com/youtubebotphp/index.php">
