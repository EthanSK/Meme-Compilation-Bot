<?php
$time = time();
echo("<pre>");
exec('unset DYLD_LIBRARY_PATH ;');
putenv('DYLD_LIBRARY_PATH');
putenv('DYLD_LIBRARY_PATH=/usr/bin');
error_reporting(E_ALL);
$ffmpeg = './ffmpeg';

require_once "functions.php";
date_default_timezone_set('Europe/London');
$currentTime = date('H:i');

$timeAllowedToStartRunning = '12:31';//we set time coz this bot is cpu intensive and i don't want it runnig while im using the computer.
$timeToStopStartingRun = '12:30';
$lengthOfRunningTimeALlowed = 48600;

$timestamp =  strtotime("today $timeToStopStartingRun") - strtotime('now');

function isBetween($from, $till, $input)
{
    $f = DateTime::createFromFormat('!H:i', $from);
    $t = DateTime::createFromFormat('!H:i', $till);
    $i = DateTime::createFromFormat('!H:i', $input);
    if ($f > $t) {
        $t->modify('+1 day');
    }
    return ($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);
}

$isBetweenTimeLimits = isBetween($timeAllowedToStartRunning, $timeToStopStartingRun, $currentTime);
echo $isBetweenTimeLimits;
if (!$isBetweenTimeLimits) {
    $remainingTimeTillTimeLimitStop = max(strtotime("today $timeAllowedToStartRunning")-strtotime('now'), strtotime("tomorrow $timeAllowedToStartRunning")-strtotime('now'));
}

$timeBotWillBeRunning = max(strtotime("today $timeToStopStartingRun")-strtotime("today $timeAllowedToStartRunning"), strtotime("tomorrow $timeToStopStartingRun")-strtotime("today $timeAllowedToStartRunning"));

echo "time bot will be running" .  gmdate("H:i:s", $timeBotWillBeRunning) ."\n";
echo '               gonna wait this long before reloading again:'. $remainingTimeTillTimeLimitStop;
echo "\n in hours". gmdate("H:i:s", $remainingTimeTillTimeLimitStop);
echo("<pre>");
echo "   \n";
print_r($timeAllowedToStartRunning);
echo("</pre>");
$watermarkString ="5 - poo.aeu - the ice screate '' :";//this needs to have : removed
$watermarkString = preg_replace("/[^A-Za-z0-9?!\s., -]/", "", $watermarkString);

echo $watermarkString;

$date = new DateTime(null, new DateTimeZone('Europe/London'));
//$time = time();
$time = $date->getTimestamp() + $date->getOffset();
echo 'time: ';
 echo("<pre>");
 echo date("Y-m-d H:i:s\n");
   print_r($time);
   echo "\noffset".$date->getOffset();
    echo("</pre>");



    $timeAllowedToStartRunning = '20:31';//we set time coz this bot is cpu intensive and i don't want it runnig while im using the computer.
    $timeToStopStartingRun = '12:30';//MUST BE 24 HOUR TIME. 11PM IS 23 00


    $finalVidsArray = scandir_only_wanted_files("./finalVids");
    $numberOfFinalVidsCreatedInCurrentSetTime = 0;
    $timeBotWillBeRunning = max(strtotime("today $timeToStopStartingRun")-strtotime("today $timeAllowedToStartRunning"), strtotime("tomorrow $timeToStopStartingRun")-strtotime("today $timeAllowedToStartRunning"));
    prettyPrint("time bot will be running: $timeBotWillBeRunning  ". gmdate('H:i:s', $timeBotWillBeRunning));
    //$startTimeStampThisSet = max(strtotime("today $timeToStopStartingRun") - $timeBotWillBeRunning ,strtotime("tomorrow $timeToStopStartingRun") - $timeAllowedToStartRunning );
    //$startTimeStampThisSet = min(strtotime("today $timeToStopStartingRun") - (strtotime("today $timeToStopStartingRun") - strtotime("today $timeAllowedToStartRunning")),strtotime("today $timeToStopStartingRun") - (strtotime("today $timeAllowedToStartRunning") - strtotime("today $timeToStopStartingRun") ) );
    //why don't we just if we've reached the start time today, use todays time, else use yesterdays start time?


    if (strtotime("today $timeAllowedToStartRunning") > strtotime("now")){
      $startTimeStampThisSet = strtotime("yesterday $timeAllowedToStartRunning");
    }else{
      $startTimeStampThisSet = strtotime("today $timeAllowedToStartRunning");

    }


    prettyPrint("start time today: $startTimeStampThisSet  ".date('D-m-y H:i:s', $startTimeStampThisSet));

    foreach ($finalVidsArray as $key => $value) {
      $videoCreationTime = filemtime("./finalVids/" . $value);
      prettyPrint("creationtime $value $videoCreationTime");


      if ($videoCreationTime >= $startTimeStampThisSet){
        prettyPrint("$value is between time limits at $videoCreationTime");
        $numberOfFinalVidsCreatedInCurrentSetTime++;
      }
    }
    prettyPrint("number of videos created 'today' : $numberOfFinalVidsCreatedInCurrentSetTime");














echo 'end of testing ';
