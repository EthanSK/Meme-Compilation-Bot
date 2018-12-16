
 <?php
 //make it ignore invalid post ids and also chegk to nee if i can get from the resizelist if it hoesnt alreaiy exist but needs to be made in thin cycle
 echo 'start';
 exec('unset DYLD_LIBRARY_PATH ;');
 putenv('DYLD_LIBRARY_PATH');
 putenv('DYLD_LIBRARY_PATH=/usr/bin');
 //this is so it can add download the new memes during the day while the video is not being made
 $shouldMakeTheVideo = true;
 $ffmpeg = './ffmpeg';
 $rand = mt_rand(0,1000);
 $order = substr(time(), -5);
 $videolist = file('videolist.txt', FILE_IGNORE_NEW_LINES);
 $arrayOfOutputVideoNames = array();
 $totalVideoDurationInTempDownloadedMemesFolderInSeconds = 0;

 $limiter = 0;

 $refreshRate = 3600;
 echo "</pre>";
 header( "refresh:$refreshRate;url= index.php" );
 //echo shell_exec("/usr/local/bin/mp4box -add meme.mp4 -cat Tmpfile.mp4 -cat meme.mp4 output.mp4  2>&1");

 //$memesalreadyintempdownloadfile = file('memesintempdownloadfile.txt', FILE_IGNORE_NEW_LINES);
 $videosActuallyInTheTempDownloadFile = scandir("tempDownloadedMemes/");
 $toDecode = file_get_contents('temparray.txt');
 //$encoded = json_encode($arrayOfVideosWithFullInformation,JSON_FORCE_OBJECT);
 //file_put_contents("temparray.txt",$encoded);

 $decodedArray = json_decode($toDecode,true);
 $arrayOfVideosWithFullInformation = $decodedArray;
 //require 'videoprocessing.php';


 //need to remove memes in tempdownaoal file when they are delet. actually no, the file is used for jack shit.
 foreach ($arrayOfVideosWithFullInformation as $key => $value) {
   if (!in_array($value['id'].".mp4",$videosActuallyInTheTempDownloadFile)){
   file_put_contents("tempDownloadedMemes/".$value['id'].".mp4", fopen($value['source'], 'r'));
   //file_put_contents("memesintempdownloadfile.txt", $value['id']."\n", FILE_APPEND);
 }
 }


 $videosActuallyInTheTempDownloadFile = scandir("tempDownloadedMemes/");
 file_put_contents("videolist.txt","");

 $arrayOfIdsWithTheirDuration=array();
 foreach ($arrayOfVideosWithFullInformation as $key => $value) {
 if(in_array($value['id'].".mp4",$videosActuallyInTheTempDownloadFile)){
   echo 'checking for errors: ';
   //there will be the 'error' that one output file must be specified but that's fine it can be ignored it affects nothing
   $checkForErrors =  shell_exec("$ffmpeg -i ./tempDownloadedMemes/".$value['id'].".mp4 2>&1" );


       if (stripos($checkForErrors, "Invalid data found when processing input") !== false){
         unlink("./tempDownloadedMemes/".$value['id'].".mp4");
         continue;
       }
     $currentVideoDuration =  shell_exec("$ffmpeg -i ./tempDownloadedMemes/".$value['id'].".mp4 2>&1 | grep Duration");
     $actualDuration = substr($currentVideoDuration, 11, 12);
     $arrayWithHoursMinsAndSecs = explode(":", $actualDuration);
     $thisVideoDurationInSeconds = $arrayWithHoursMinsAndSecs[2] + $arrayWithHoursMinsAndSecs[1]*60;
         $totalVideoDurationInTempDownloadedMemesFolderInSeconds += $arrayWithHoursMinsAndSecs[2] + $arrayWithHoursMinsAndSecs[1]*60;

       $arrayOfIdsWithTheirDuration[$value['id']] = $thisVideoDurationInSeconds;
       //place these memes in a txt to be concatenated
       file_put_contents("videolist.txt","file '". $value['id'].".mp4\n", FILE_APPEND);


       if($totalVideoDurationInTempDownloadedMemesFolderInSeconds >= 600)break;

 }
 }

 echo ("<pre>");
 echo 'total video duration: ';
   print_r($totalVideoDurationInTempDownloadedMemesFolderInSeconds);
    echo ("</pre>");
    $videolist = file('videolist.txt', FILE_IGNORE_NEW_LINES);
    //this is to sort the videolist array based on duration
   asort($arrayOfIdsWithTheirDuration);

  echo ("<pre>");
    print_r($arrayOfIdsWithTheirDuration);
     echo ("</pre>");

 $arrayOfIDsThatWillBePostd = array();
 //this is to go through each value in the sorted array and resize it to 1080x1080
 foreach ($arrayOfIdsWithTheirDuration as $key => $value) {
   // $startOfVideoFileName = stripos($value, "'");
   // $videoFileName = substr($value, $startOfVideoFileName+1);
   // $videoFileName = str_replace("'","",$videoFileName);
 // $order++;
 $videoFileName = $key.".mp4";
     echo ("<pre>");
      print_r($videoFileName);
       echo ("</pre>");
 array_push($arrayOfIDsThatWillBePostd, $key);

   //not this one
   //echo shell_exec("$ffmpeg -i $videoFileName -vf 'scale=:1080:force_original_aspect_ratio=decrease,pad=1280:720:(ow-iw)/2:(oh-ih)/2' -video_track_timescale 90000 resized$order.mp4 2>&1");
 //resize:
  //echo shell_exec("$ffmpeg  -i ./tempDownloadedMemes/$videoFileName -filter_complex 'scale=1080:-1,pad=1080:1080:(ow-iw)/2:(oh-ih)/2:0x2F2F2F,setpts=PTS-STARTPTS' -video_track_timescale 100000 ./resizedVideos/resized_$videoFileName 2>&1");
 //this one works yay
  echo shell_exec("$ffmpeg -i ./tempDownloadedMemes/$videoFileName -filter_complex 'setpts=PTS-STARTPTS,scale=1080:-1,pad=1080:1080:(ow-iw)/2:(oh-ih)/2:0x2F2F2F,setsar=sar=1/1,fps=fps=30' -video_track_timescale 15360 ./resizedVideos/resized_$videoFileName 2>&1");

 array_push($arrayOfOutputVideoNames, "resized_$videoFileName");
 }


 file_put_contents('resizedvideolist.txt',  "");
 foreach ($arrayOfOutputVideoNames as $key => $value) {
   //$limiter++;
   //if ($limiter >3 )break;
   //file_put_contents('resizedvideolist.txt',  "file 'resizedVideos/$value'\n", FILE_APPEND);
   file_put_contents('resizedvideolist.txt',  "resizedVideos/$value\n", FILE_APPEND);

 }
 $time = time();
  echo ("<pre>");
    print_r($time);
     echo ("</pre>");

 echo ("<pre>");
 //concatenate videos:
 //echo shell_exec("$ffmpeg -auto_convert 1 -f concat -i resizedvideolist.txt -qscale 0 -vcodec mpeg4 concatenated_$time.mp4 2>&1");
 // echo shell_exec("$ffmpeg -auto_convert 1 -f concat -i resizedvideolist.txt -qscale 0 -vcodec mpeg4 concatenated_$time.mp4 2>&1");

    echo ("</pre>");

 //well this failed
 //if using this, remember to change the file put contents into resized video list to the first one where it places the word file in front of each line
 $resizedVideoList = file('resizedVideoList.txt', FILE_IGNORE_NEW_LINES);
 $inputStringForConcat = "";
 $concatString = "";
 $concateNvalue = 0;
  foreach ($resizedVideoList as $key => $value) {
    //$limiter++;
    //if ($limiter > 2)break;
    $inputStringForConcat .= " -i ".$value;
    $concatString .= "[$key:0][$key:1]";
    $concateNvalue++;
  }
  $concatString .= " concat=n=$concateNvalue:v=1:a=1[v][a]";
   echo ("<pre>");
     print_r($inputStringForConcat);
     print_r($concatString);

      echo ("</pre>");
      $idWereTalkingAboutTemp = '221259188291562_311560232594790';

  echo ("<pre>");
  echo shell_exec("$ffmpeg $inputStringForConcat -filter_complex '$concatString' -map '[v]' -map '[a]' concatenated_$time.mp4 2>&1");
  //echo shell_exec("$ffmpeg -i resizedVideos/resized_221259188291562_312388849178595.mp4 -i resizedVideos/resized_221259188291562_311560232594790.mp4 -filter_complex 'concat=n=2:v=1:a=1[v][a]' -map '[v]' -map '[a]' concatenated_$time.mp4 2>&1");
  //echo shell_exec("/usr/local/bin/mp4box -add resizedVideos/resized_221259188291562_312444612506352.mp4 -cat resizedVideos/resized_1238578182896580_1305089302912134.mp4 -cat resizedVideos/resized_1238578182896580_1305089302912134.mp4 concatenated_$time.mp4  2>&1");

     echo ("</pre>");

     echo ("<pre>");
     echo 'background addition: ';
     //working but old for testing :
       //echo shell_exec("$ffmpeg -loop 1 -i background.jpg -i meme.mp4 -i 1.png -filter_complex '[1:v]scale=-1:1080[scaledvid]; [0:v][scaledvid]overlay=(main_w-overlay_w)/2:(main_h-overlay_h)/2:shortest=1[overlay1];[overlay1][2:v]overlay=(main_w-overlay_w)/2:(main_h-overlay_h)/2[output]' -map '[output]' background$order.mp4 2>&1");
       //make the actual video
     //  echo shell_exec("$ffmpeg  -i background.mov -i concatenated_$time.mp4 -i memestreamwatermark.png -i subscribe.png -filter_complex '[1:v]scale=1080:-1[scaledvid]; [0:v][scaledvid]overlay=(main_w-overlay_w)/2:(main_h-overlay_h)/2:shortest=1[overlay1];[overlay1][2:v]overlay=((main_w-overlay_w)/2)+400:((main_h-overlay_h)/2)+450[overlay2]; [overlay2][3:v]overlay=5:(main_h-overlay_h)/2[output]; [1:a] apad [outputaudio]' -map '[output]' -map '[outputaudio]' ./finalVidsNoIntroOrOutro/finished_$time.mp4 2>&1");

     //overlay items
       echo shell_exec("$ffmpeg -i background.mov -i concatenated_$time.mp4 -i memestreamwatermark.png -i subscribetext.png -i mslogo.png -i dankmemecompilationtext.png -i memestream.png -y -filter_complex '[1:v]scale=1080:-1[scaledvid]; [0:v][scaledvid]overlay=(main_w-overlay_w)/2:(main_h-overlay_h)/2:shortest=1[overlay1];[overlay1][2:v]overlay=((main_w-overlay_w)/2)+450:((main_h-overlay_h)/2)+465[overlay2]; [overlay2][3:v]overlay=1495:(main_h-overlay_h)/2+200[overlay3];[overlay3][4:v]overlay=0:main_h-overlay_h[overlay4];[overlay4][5:v]overlay=5:50[overlay5];[overlay5][6:v]overlay=1530:30[output]; [1:a] apad [outputaudio]' -map '[output]' -map '[outputaudio]' ./finalVidsNoIntroOrOutro/finished_$time.mp4 2>&1");
     //remember to convert intro and outro to correct formats ffmpeg before concating them
 // use this ffmpeg command to do so  : ffmpeg -i memestreamintro.mov -filter_complex 'setpts=PTS-STARTPTS,setsar=sar=1/1,fps=fps=60' -video_track_timescale 15360 -y memestreamintro.mp4 2>&1
       echo shell_exec("$ffmpeg -i memestreamintro.mp4 -i ./finalVidsNoIntroOrOutro/finished_$time.mp4 -i memestreamoutro.mp4 -filter_complex 'concat=n=3:v=1:a=1[v][a]' -map '[v]' -map '[a]' /finalVids/finished_$time.mp4 2>&1");


       unlink("concatenated_$time.mp4");
       unlink("./finalVidsNoIntroOrOutro/finished_$time.mp4");

       file_put_contents('memestoupload.txt',"finished_$time.mp4"."\n", FILE_APPEND);

       //reenable this
       //only delete the files if a video was output successfully
       if (file_exists("./finalVids/finished_$time.mp4")){

         $finalVideoDuration =  shell_exec("$ffmpeg -i ./finalVids/finished_$time.mp4 2>&1 | grep Duration");
         $actualDuration = substr($finalVideoDuration, 11, 12);
         $arrayWithHoursMinsAndSecs = explode(":", $actualDuration);
         $thisVideoDurationInSeconds = $arrayWithHoursMinsAndSecs[2] + $arrayWithHoursMinsAndSecs[1]*60;

         if ($thisVideoDurationInSeconds < 600){
           unlink("./finalVids/finished_$time.mp4");
 }

       foreach ($arrayOfIDsThatWillBePostd as $key => $value) {
         if ($thisVideoDurationInSeconds < 600){
         file_put_contents('memesalreadyposted.txt',$value."\n", FILE_APPEND);

       }
         unlink("./tempDownloadedMemes/$value.mp4");

       }

     $resizedVideos = scandir('./resizedVideos');

     foreach ($resizedVideos as $key => $value) {
       if ($key >= 3){
         //we do this because it does not overwrite by default, so if the resized videos are kept in the file, then it will not bother overwriting and doing the ffmpeg resize
         if ($thisVideoDurationInSeconds < 600){
       unlink("resizedVideos/$value");
     }
       }
     }


 }
        echo ("</pre>");



 echo 'end of index.php';

 //require 'youtubeupload.php';

  ?>
