<?php
if (!session_id()) {
    session_start();
}
require_once('functions.php');

prettyPrint("video proccessing loaded");
$loadedVideoProcessing = true;

$notTesting = true;

ini_set('display_errors', 0);
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
require_once __DIR__ . '/vendor/autoload.php';
echo "<pre>";
//date_default_timezone_set("GMT");//why is iths here
echo "The time is " . date("Y-m-d H:i:s").'   '.   time();


$currentTime = time();

$fb = new Facebook\Facebook([
  'app_id' => 'get ur own',//'',
  'app_secret' => 'poop',//'',
  'default_graph_version' => 'v2.12',
  ]);



$helper = $fb->getRedirectLoginHelper();

//$permissions = ['manage_pages', 'publish_pages', 'publish_actions', 'read_page_mailboxes', 'pages_messaging'];
$permissions = [];

try {
    if (isset($_SESSION['facebook_access_token'])) {
        $accessToken = $_SESSION['facebook_access_token'];
    } else {
        $accessToken = $helper->getAccessToken();
    }
} catch (Facebook\Exceptions\FacebookResponseException $e) {
    // When Graph returns an error
    echo 'Graph returned an error: ' . $e->getMessage();

    exit;
} catch (Facebook\Exceptions\FacebookSDKException $e) {
    // When validation fails or other local issues
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    if ($e->getMessage() =='Cross-site request forgery validation failed. Required param "state" missing from persistent data.') {
        echo 'trying to unset 1';
        // session_unset();
        // session_destroy();
        //header("refresh:0;url=index.php");
        //  echo("<meta http-equiv='refresh' content='1'>");
    }
    exit;
}

if (isset($accessToken)) {
    if (isset($_SESSION['facebook_access_token'])) {
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    } else {
        // getting short-lived access token
        $_SESSION['facebook_access_token'] = (string) $accessToken;

        // OAuth 2.0 client handler
        $oAuth2Client = $fb->getOAuth2Client();

        // Exchanges a short-lived access token for a long-lived one
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);

        $_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;

        // setting default access token to be used in script
        $fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
    }

    // redirect the user back to the same page if it has "code" GET variable
    if (isset($_GET['code'])) {
        header('Location: ./');
    }

    // getting basic info about user
    try {
        $profile_request = $fb->get('/me?fields=name,first_name,last_name,email');
        $profile = $profile_request->getGraphNode()->asArray();
    } catch (Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        echo 'Graph returned an error: ' . $e->getMessage();
        //session_destroy();
        // redirecting user back to app login page
        ///header("Location: ./");
        exit;
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        if ($e->getMessage() =='Cross-site request forgery validation failed. Required param "state" missing from persistent data.') {
            echo 'trying to unset 2';
            session_unset();
            session_destroy();
            //header("refresh:0;url= index.php");
            //echo("<meta http-equiv='refresh' content='1'>");
        }
        exit;
    }
    //•••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••••
    try {
        //require "graphapifunctions.php";
        file_put_contents("temparray.txt", "");

        prettyPrint("starting graph api stry block");
        removeIDsThatLeadToDuplicateVideos();// remove all ids that lead to duplicate memes eg 1644383829203805 equals 1630069933968528_1644383829203805
        prettyPrint("removed duplicate ids");


        //these pages have shit memes - REMOVÉ THEM
        $arrayOfPagesToDeleteMemesFrom = array(//should have done all these by page id to begin with ugh
          "mememangIG",
        //  "seewaymore",//doesn't exist
        //  "1242917139163973", //doesn't exist
        //"therealironicstolenmemes",//doesnt exisst
        //  "DankusMaximusMemes",//doesn't exist
          "TheMemeArmy",
          "DankMemesFromOuterSpace",
          //"rottenmemes420",//sadly this is a good page but has too much inappropriate content that could easily get us zucced on youtube such as sexual content //doesn't exist
          "bonzibuddysinternetcollections" //too much nudity and pornography :l

        );//these will auto be removed from $arrayOfPagesForYT
        //might have to de lete 690807961112365 univer


        //lmaooo now i have to set this manually coz i cant get it from graph any more w/o permissions
        $arrayOfPagesToDeleteMemesFromActualID = array(//will be in same order as $arrayOfPagesToDeleteMemesFrom
          "198669200507559",//mememange
          "1630069933968528",//thememearmy
          "602276443221815", //DankMemesFromOuterSpace
          "222043204954108"//bonzibuddysinternetcollections
        );

        //this is for getting memes for the youtube bot
        //------------------------------------------------------------------------------------------------
        $arrayOfPagesForYT = array( //facebook page ids

        "toodank89",
        "TheMemeArmy",
        //"135636106970222",//this id expired hmmm
        "imakememesforliving",
        "imakememes69",
        "TheDankLads",
        "lymphoma420",
        "OfficialBardockObama",
        "1590697684292763",
        "DankusMaximusMemes",
        "grandayy",
        "1242917139163973",
        //these are from the old video memes for bot absorbent teens:
        "memeglomeratebot",
        "Dankpedia",
        "succmymeme",
        "Handoveryourmancard3",
        "IhavenomemesImustshitpost",
        "DankMemesFromOuterSpace",
        "seewaymore",
        "ericscreamymeme",
        "DankMemesGang",
        "mememangIG",
        "supremezestymemes",
        "WindowsMemes69",
        "ayydelalmao3",
        "cuminassbro2",
        "robustgourmetmemes",
        "NEEMEMES",
        "zestysupreme",
        "TrolleyProblemMemes",
        "Edgyteensanonymous",
        "WATinTheFuWorld",
        "883146658474341",
        "Shitposting2006",
        "mochadepressotogoplease",
        "1165949343424004",
        "amphetameme2",
        "delakillyourself",
        "1008892339157119",
        "Succpedia",
        "260604947659228",
        "therealironicstolenmemes",
        "memewave4",
        "PerhapsSweet",
        "DankestMemesNoice",
        "TheMemeAesthetic",
        "MemeArchaeologist",
        "190297124777434",//handoevrurmancard
        "bonzibuddysinternetcollections",
        "garrettgifts2",
        "Memespapi",
        "MemesCycles",
        "DankMemesYK",
        "690807961112365", //university of memes - miggt have to delete as some 'sexual' things we'll see
        "ocfreshstolenmemes1",
        "rottenmemes420",
        "mtmydiii",
        "1977277725932338", // i have no gf or aspirations


    );
        $arrayOfPagesForYT = array_diff($arrayOfPagesForYT, $arrayOfPagesToDeleteMemesFrom);

        printArray($arrayOfPagesForYT, "array of pages for yt");

        // //------------------------------get videos-----------------------------------------------------------
        // $newVideosToAddToList = array();
        // $newVideosToAddToListOldVideos = array(); //ebcause we don't want them to be appended, we want them randomly inserted.
        // $IDsWithErrors = makeTextFileUniqueAndReturnIt('invalidIDs.txt');
        // $pagesAlreadyPaginated = makeTextFileUniqueAndReturnIt('pagesalreadypaginated.txt');
        //
        // if (!isset($lengthOfFBVideoAllowed)) {
        //     $lengthOfFBVideoAllowed = 420;
        // }
        // if (!isset($minAgeOfFBVideoAllowed)) {
        //     $minAgeOfFBVideoAllowed = 43200;
        // }
        // if (!isset($maximumAgeForAVideoToBeConsideredNewEnoughToAppendNotInsert)) {
        //     $maximumAgeForAVideoToBeConsideredNewEnoughToAppendNotInsert = 604800;//one week
        //     prettyPrint("defaulting maximumAgeForAVideoToBeConsideredNewEnoughToAppendNotInsert");
        // }
        //
        // foreach ($arrayOfPagesForYT as $keypageforyt => $valuepageforyt) {
        //     $pageIDCurrentlyLoopingThrough = $valuepageforyt;
        //     if (in_array($valuepageforyt, $IDsWithErrors)) {
        //         prettyPrint("found errors with this page before so continuing");
        //         continue;
        //     }
        //     $getPageID = $fb->get('/'.$valuepageforyt.'?fields=id');
        //     $getPageID = $getPageID->getGraphNode()->asArray()['id'];
        //
        //     prettyPrint("page id $getPageID");
        //
        //
        //     prettyPrint("looping through this page: $valuepageforyt");
        //     $videomemestxt = file('videomemestopost.txt', FILE_IGNORE_NEW_LINES);
        //     prettyPrint("number of elements in videomemestxt array before for loop for all pages: ". count($videomemestxt));
        //
        //     //now requires retarded review to get permissions. just use current videomemes.txt, cba to get more. rip fb.
        //     $getPageVideos = $fb->get('/'.$getPageID.'/videos?fields=id,created_time,from,length&limit=100');
        //     $getPageVideos = $getPageVideos->getGraphEdge();
        //
        //     $firstPageVideosArray = $getPageVideos->asArray();
        //     foreach ($firstPageVideosArray as $keyPagination => $valuePagination) {
        //         prettyPrint($valuePagination['id']);
        //         $ageOfVideo = time() - $valuePagination['created_time']->getTimestamp();
        //
        //         if ($valuePagination['length'] < $lengthOfFBVideoAllowed && $ageOfVideo > $minAgeOfFBVideoAllowed) { //&& $valuePagination['from']['id'] == $getPageID['id'] ) {//we don't want user posted videos but we do want shared videos and most videos are shared so guess we'll sacrifice it
        //             //array_unshift($videomemestxt, $valuePagination['id']);
        //             echo 'video is long enough and old enoguh to be used';
        //             prettyprint("the age of this video is", $ageOfVideo);
        //             if ($ageOfVideo < $maximumAgeForAVideoToBeConsideredNewEnoughToAppendNotInsert) {
        //                 prettyPrint("$ageOfVideo - age is young enough to be appended   "   .$valuePagination['id']);
        //                 array_push($newVideosToAddToList, $valuePagination);
        //                 prettyPrint("count newVideosToAddToList".count($newVideosToAddToList));
        //             } else {
        //                 prettyPrint("$ageOfVideo - age is too old to be appended  ". $valuePagination['id']);
        //
        //                 array_push($newVideosToAddToListOldVideos, $valuePagination);
        //                 prettyPrint("count newVideosToAddToListOldVideos".count($newVideosToAddToListOldVideos));
        //
        //             }
        //         } else {
        //             prettyPrint("video is too long or too young to be used yet " .  $valuePagination['id']);
        //             if ($ageOfVideo < time() - $minAgeOfFBVideoAllowed) {
        //                 prettyPrint("video is too young rip");
        //             }
        //         }
        //     }
        //     printArray($firstPageVideosArray, "first page videos array");
        //
        //     //we do NOT want it to do this, otherwise it wont get the recent video urls.
        //     //    if (in_array($valuepageforyt, $pagesAlreadyPaginated))continue;
        //     if (!in_array($valuepageforyt, $pagesAlreadyPaginated) && count($firstPageVideosArray) >= 100) {
        //         prettyPrint($valuepageforyt.' is about to be paginated ');
        //         $nextFeed = $getPageVideos;//delet
        //         //these memes will be old, but for the new memes, we need to put them at the top of the txt file because of the limiter we will place in the for loop
        //             for ($i=0; $i < 100 ; $i++) {//i know looping through more pages that existst will break the script, but since it is always a one off, at least it will kind of work.
        //               echo 'loop count '.$i;
        //                 appendToTxtFile('pagesalreadypaginated.txt', $valuepageforyt);
        //
        //                 $nextFeed = $fb->next($nextFeed);
        //                 $nextFeedVideoArray = $nextFeed->asArray();
        //                 printArray($nextFeedVideoArray, "$i th page videos array");
        //
        //
        //
        //                 foreach ($nextFeedVideoArray as $keyPagination => $valuePagination) {
        //                     $ageOfVideo = time() - $valuePagination['created_time']->getTimestamp();
        //                     if ($valuePagination['length'] < $lengthOfFBVideoAllowed && $ageOfVideo > $minAgeOfFBVideoAllowed) { //&& $valuePagination['from']['id'] == $getPageID['id'] ) {//we don't want user posted videos but we do want shared videos and most videos are shared so guess we'll sacrifice it
        //                         //array_unshift($videomemestxt, $valuePagination['id']);
        //                         array_push($newVideosToAddToListOldVideos, $valuePagination);
        //                     }
        //                 }
        //
        //                 if (count($nextFeedVideoArray) < 100) {//this means there won't be any more pages after this so stop tryna get the next page
        //                     break;
        //                 }
        //             }
        //         makeTextFileUniqueAndReturnIt("pagesalreadypaginated.txt");
        //
        //         echo 'end of loop';
        //     }
        // }//for loop through all fb pages
        //
        // //-------------------------------
        // echo ' getting videos complete ';
        // printArray($newVideosToAddToList, "newvideostoadd to file");
        // printArray($newVideosToAddToListOldVideos, "newVideosToAddToListPaginated to file");
        //
        // $arrayToAdd = array();
        // foreach ($newVideosToAddToList as $key => $value) {
        //     $arrayToAdd[$value['id']] = $value;
        // }
        // foreach ($newVideosToAddToListOldVideos as $key => $value) {
        //     $arrayToAdd[$value['id']] = $value;
        // }
        // //printArray($arrayToAdd, "array to add");
        // prettyPrint("array to add count ".count($arrayToAdd));
        // addArrayToJsonFile("allVideoMetadata.json", $arrayToAdd);//am i retarded? the reason there are so many lines in the json is coz it is pretty printing so each id takes multiple lines FUCKNIG THICKO DOOHHHHH
        //
        // foreach ($newVideosToAddToList as $key => $value) {
        //     appendToTxtFile("videomemestopost.txt", $value['id']);
        // }
        //
        // $arrayOfOldVideoIDs = array();
        // foreach ($newVideosToAddToListOldVideos as $key => $value) {
        //     array_push($arrayOfOldVideoIDs, $value['id']);
        // }
        //
        // insertArrayAtStartOfTxtFile("videomemestopost.txt", $arrayOfOldVideoIDs);//paginated videos should go at top so they aren't used first. we want new trendy memes not old ones
        // //----------------
        //

        $videoIDsFromFile = array_reverse(makeTextFileUniqueAndReturnIt("videomemestopost.txt"));//reverse so newest are first
        $IDsWithErrors = makeTextFileUniqueAndReturnIt('invalidIDs.txt');
        $memesAlreadyPosted = makeTextFileUniqueAndReturnIt('memesalreadyposted.txt');


        //
        // //--------set array of pages to delete
        // $arrayOfPagesToDeleteMemesFromActualID = array();
        // foreach ($arrayOfPagesToDeleteMemesFrom as $key => $value) {
        //     $pageIDCurrentlyLoopingThrough = $value;
        //     if (in_array($value, $IDsWithErrors)) {
        //         continue;
        //     }
        //     $getPageID = $fb->get('/'.$value.'?fields=id');
        //     $getPageID = $getPageID->getGraphNode()->asArray();
        //
        //     echo("<pre>");
        //     echo 'this page id for deleting: ';
        //     print_r($getPageID['id']);
        //     echo("</pre>");
        //     array_push($arrayOfPagesToDeleteMemesFromActualID, $getPageID['id']);
        // }
        // echo("<pre>");
        // echo 'delete array: ';
        // print_r($arrayOfPagesToDeleteMemesFromActualID);
        // echo("</pre>");
        //
        // //--------------------------------------------

        if (!isset($useRandomMemes)) {
            echo "random memes not set so defaulting to true";
            $useRandomMemes = false;
        }
        if ($useRandomMemes) {
            echo 'shuffling videoIDs for random order...';
            shuffle($videoIDsFromFile);
            shuffle($videoIDsFromFile);
            shuffle($videoIDsFromFile);
            shuffle($videoIDsFromFile);
        }

        //--------------set memesUsedInLastNsets
        //make it delete blocks from last 5 sets once there are more than 5 sets in the txt.
        $memesUsedInLastNsets = file('memesUsedInLastNsets.txt', FILE_IGNORE_NEW_LINES);//there are some lines that are labels so they will be the same so can't be unique

        if (!isset($memesUsedInLastNsetsNValue)) {
            echo '$memesUsedInLastNsetsNValue not set so defaulting';
            $memesUsedInLastNsetsNValue = count($videoIDsFromFile)/500;
            //default value
        }


        $keepingCountOfSets = 0;
        foreach ($memesUsedInLastNsets as $key => $value) {
            if ($value == '*end_of_compilation*') {
                $keepingCountOfSets++;
            }
        }
        prettyPrint("count of sets: $keepingCountOfSets memesUsedInLastNsetsNValue $memesUsedInLastNsetsNValue");


        while ($keepingCountOfSets > $memesUsedInLastNsetsNValue) {
            foreach ($memesUsedInLastNsets as $key => $value) {
                unset($memesUsedInLastNsets[$key]);
                if ($value == '*end_of_compilation*') {
                    break;
                }
            }
            $memesUsedInLastNsets = array_values($memesUsedInLastNsets);
            $keepingCountOfSets--;
        }

        replaceTextFileWithArray("memesUsedInLastNsets.txt", $memesUsedInLastNsets);
        //-------------------------------

        $arrayOfVideosWithFullInformation = array();
        $limiter = 0;
        if (!isset($limiterMax)) {
            echo 'setting $limiterMax default';
            $limiterMax = 400;
        }
        if (!isset($minAgeOfVideoToUseJsonMetadata)){
          echo 'defaulting $minAgeOfVideoToUseJsonMetadata';
          $minAgeOfVideoToUseJsonMetadata = 604800;
        }
        $arrayOfVideoMetadata = getArrayFromJsonFile("allVideoMetadata.json");

        foreach ($videoIDsFromFile as $key => $value) {
            $pageIDCurrentlyLoopingThrough = $value;
            if (strpos($value, '*') !== false) {//not an Id so continue
                continue;
            }
            if (in_array($value, $IDsWithErrors)) {//this id has thrown an erro before so skip
                continue;
            }
            // if (in_array($value, $memesAlreadyPosted) && $useRandomMemes == false) {//this id was already used and we don't wanna use random memes
            //     continue;
            // }//this is all a bit bs to me lol we should always include random memes when wil we ever have new memes only.
            if (in_array($value, $memesUsedInLastNsets)) {//don't use a meme used in the last 5 (N) sets so that videos differ from each other
                continue;
            }

            echo 'limiter: '.$limiter;
            //limiter so it does not spend for ever getting every single one of the thousands of memes available. I know the timing will be wrong but what can we do.
            //we could make it so that it only has to get all the memes once, and stores the time each one was uploaded. The reason I havent been doing this is because the source url keeps changing, but the time will never change.
            //i think a less complex solution would be to tell the videomemes script to get some more memes once the memes have run out, ie when it has to delete the final vid coz it is not long enough.
            if ($limiter>$limiterMax) {//we should not need the limiter now that we cache the data it wont be as api intensive in the long term
                break;
            }//NEVER TURN OFF LIMITER. IT IS VERY CRUCIAL TO MANY THINGS. IT ALLOWS RANDOMISATION WITHIN A SUBSET OF ALL THE POSSIBLE VIDEOS.
            prettyPrint("getting video metadata for this video $value");

            //we cannot use the json at all. unfortunately, we need to always check if the videos are still up on fbto ensure they are not zuccworthy. if not, then we would use the already downloaded video and it might be bad!
            //we can use it actually if the video is old enough because then it won't bezuccworthy and anyway it would have been deleted so it won't be downloaded
            if (is_array($arrayOfVideoMetadata[$value]['created_time'])){
              printArray($arrayOfVideoMetadata[$value],  'metadata array');
              $timeString = $arrayOfVideoMetadata[$value]['created_time']['date'];
              $ageFoundFromJson = DateTime::createFromFormat('Y-m-d H:i:s',substr($timeString, 0, strpos($timeString, ".")), timezone_open($arrayOfVideoMetadata[$value]['created_time']['timezone']) );
              $ageFoundFromJson = time() - $ageFoundFromJson->getTimestamp();
              prettyPrint("age from json $ageFoundFromJson");
              echo ($ageFoundFromJson > $minAgeOfVideoToUseJsonMetadata);
            }
            if ($ageFoundFromJson > $minAgeOfVideoToUseJsonMetadata && isset($arrayOfVideoMetadata[$value]['created_time']) && isset($arrayOfVideoMetadata[$value]['from'])) {
                $getThisVideoData = $arrayOfVideoMetadata[$value];
                echo "video metadata already found in saved json";
            } else {
              try {
                  //echo "video metadata not already found so trying to get from graph";
                  $getThisVideoData = $fb->get('/'.$value.'/?fields=id,created_time,from');//we don't even need to get length here. it is calculated later, and if it was too long it would have been flitered out before it could even get in the videomemestopost file.
                  $getThisVideoData = $getThisVideoData->getGraphNode()->asArray();
              } catch (Facebook\Exceptions\FacebookResponseException $e) {
                  $exceptionMessage = print_r('Graph returned an error: ' . $e->getMessage(), true);

                  preg_match_all('/".*?"|\'.*?\'/', $e->getMessage(), $pageFromErrorMessage);
                  $pageID = trim($pageFromErrorMessage[0][0], "'");

                  echo("<pre>");
                  var_dump($exceptionMessage);
                  echo("</pre>");

                  echo("<pre>");
                  print_r($pageFromErrorMessage);
                  echo("</pre>");

                  if (strpos($exceptionMessage, 'Graph returned an error: Unsupported get request. Object with ID') !== false || strpos($exceptionMessage, 'Some of the aliases you requested do not exist') !== false) {
                      echo("<pre>");
                      print_r("this id no longer exists: ". $pageIDCurrentlyLoopingThrough);
                      echo("</pre>");
                      //$pageIDCurrentlyLoopingThrough can also be the id of a meme - i know i named it badly
                  file_put_contents("invalidIDs.txt", $pageIDCurrentlyLoopingThrough . "\n", FILE_APPEND);//$pageIDCurrentlyLoopingThrough is misleadingly also used for video ids
                  //header("refresh:0;url=index.php");
                  //echo("<meta http-equiv='refresh' content='1'>");
                      continue;
                      //exit; //could we delete this exit so it doesnt end termination ?
                  }
              }
            }


            echo 'tryna get video timestamp';
            if (is_array($getThisVideoData['created_time'])){
              $timeString = $getThisVideoData['created_time']['date'];
              $getThisVideoData['created_time'] = DateTime::createFromFormat('Y-m-d H:i:s',substr($timeString, 0, strpos($timeString, ".")), timezone_open($getThisVideoData['created_time']['timezone']) );

            }
            printArray($getThisVideoData, "video metadata");

            $getThisVideoData['timestamp'] = $getThisVideoData['created_time']->getTimestamp();

            echo("<pre>");
            echo ' got video timestamp: ';
            print_r($getThisVideoData);
            echo("</pre>");

            if (in_array($getThisVideoData['from']['id'], $arrayOfPagesToDeleteMemesFromActualID)) {
                echo("<pre>");
                echo 'found a page i want deleted so skipping '.$getThisVideoData['from']['id'];
                echo("</pre>");
                file_put_contents("invalidIDs.txt", $getThisVideoData['id'] . "\n", FILE_APPEND);

                continue;
            }

            // foreach ($arrayOfPagesToDeleteMemesFromActualID as $key => $value) {
            //   if (stripos($getThisVideoData['from']['id'],$value) !== false){
            //     echo ("<pre>");
            //     echo 'found a page i want deleted so skipping *not actually this is a test* '.$getThisVideoData['from']['id'];
            //     echo ("</pre>");
            //   }
            // }



            array_push($arrayOfVideosWithFullInformation, $getThisVideoData);
            $limiter++;
        }
        $arrayToAdd = array();
        foreach ($arrayOfVideosWithFullInformation as $key => $value) {
            $arrayToAdd[$value['id']] = $value;
        }

        //printArray($arrayToAdd, "array to add");
        prettyPrint("array to add count ".count($arrayToAdd));
        addArrayToJsonFile("allVideoMetadata.json", $arrayToAdd);
        //at the end of the file place something that can confirm whether all the memes have been verified successfully
        //doing this because all ids were successful a no error was caught

        echo 'limiter reach: '.$limiter;

        function sortByTime($x, $y)
        {
            return $y['timestamp'] - $x['timestamp'];
        }
        //sort so most recent come first
        usort($arrayOfVideosWithFullInformation, 'sortByTime');//we can no longer sort by time because we have no limiter, and therefore the video order will be the same every time if we unrandomise every possible ideo essentially. no, because it won't make a difference coz the videos are sotred by duration later. this is actually good keep it.
        //shuffle($arrayOfVideosWithFullInformation);//waoh wait a sec. limiter is acutally a lot more important than i thought. it allows for randomness within a given subset. even if we shuffle here, it will still reorder so only the shortest ones are used. we need to turn on limiter
        //i repeat, we must make sure we never turn off limiter.

        //
        echo("<pre>");
        echo 'videoprocessing rray of video with full information';
        print_r($arrayOfVideosWithFullInformation);
        echo("</pre>");

        if (empty($arrayOfVideosWithFullInformation)) {
            exit("arrayOfVideosWithFullInformation empty");
        }

        $encoded = json_encode($arrayOfVideosWithFullInformation, JSON_FORCE_OBJECT);
        file_put_contents("temparray.txt", $encoded);
    } catch (Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        echo 'outside error';
        $exceptionMessage = print_r('Graph returned an error: ' . $e->getMessage(), true);

        preg_match_all('/".*?"|\'.*?\'/', $e->getMessage(), $pageFromErrorMessage);
        $pageID = trim($pageFromErrorMessage[0][0], "'");

        echo("<pre>");
        var_dump($exceptionMessage);
        echo("</pre>");

        echo("<pre>");
        print_r($pageFromErrorMessage);
        echo("</pre>");

        if (strpos($exceptionMessage, 'Graph returned an error: Unsupported get request. Object with ID') !== false || strpos($exceptionMessage, 'Some of the aliases you requested do not exist') !== false) {
            echo("<pre>");
            print_r("this id no longer exists: ". $pageIDCurrentlyLoopingThrough);
            echo("</pre>");
            //$pageIDCurrentlyLoopingThrough can also be the id of a meme - i know i named it badly
            file_put_contents("invalidIDs.txt", $pageIDCurrentlyLoopingThrough . "\n", FILE_APPEND);//$pageIDCurrentlyLoopingThrough is misleadingly also used for video ids
            //header("refresh:0;url=index.php");
            ////echo("<meta http-equiv='refresh' content='1'>");

            exit; //could we delete this exit so it doesnt end termination ?
        }

        session_unset();
        session_destroy();
        //header("refresh:0;url= index.php");
    } catch (Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error: ' . $e->getMessage();
        echo 'Facebook SDK returned an error: ' . $e->getLine();
        if ($e->getMessage() =='Cross-site request forgery validation failed. Required param "state" missing from persistent data.') {
            echo 'trying to unset 3';
            session_unset();
            session_destroy();
            //header("refresh:0;url= index.php");
            ////echo("<meta http-equiv='refresh' content='1'>");
        }

        exit;
    }
    // Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']
} else {
    // replace your website URL same as added in the developers.facebook.com/apps e.g. if you used http instead of https and you used non-www version or www version of your website then you must add the same here
    $loginUrl = $helper->getLoginUrl('https://www.youtubebot.com/youtubebotphp/index.php', $permissions);
    echo '<a id = "login" href="   ' . $loginUrl . '   ">Log in with Facebook!</a>';
}
echo "loaded the youtube bot videoprocessing";


function removeIDsThatLeadToDuplicateVideos()
{
    //firstly, remove all ids that lead to duplicate memes eg 1644383829203805 equals 1630069933968528_1644383829203805
    $videomemestxt = makeTextFileUniqueAndReturnIt("videomemestopost.txt");
    $arrayWithNoUnderscoreMemeIDs = array();

    foreach ($videomemestxt as $key => $value) {
        if (stripos($value, '_') === false) {
            array_push($arrayWithNoUnderscoreMemeIDs, $value);
        }
    }

    foreach ($videomemestxt as $key => $value) {
        if (in_array(explode('_', $value)[1], $arrayWithNoUnderscoreMemeIDs)) {
            prettyPrint("unsetting video memes line as same as one with underscore");
            unset($videomemestxt[$key]);
        }
    }

    replaceTextFileWithArray("/Applications/XAMPP/xamppfiles/htdocs/youtubebotphp/videomemestopost.txt", $videomemestxt);
}



 ?>

 <script>


     var elm=document.getElementById('login');
       document.location.href = elm.href;

 </script>
