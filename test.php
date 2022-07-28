<?php
include("telegram.php");
include("zm.php");
include("dbSQLs.php");
$video_id = 36692;
$chat_id = 1507660693;

$zm_token = zm_getToken();
$event_video = zm_eventVideo($zm_token, $video_id);
if ( $event_video == false ) {
    $event_video = zm_createEventVideo($zm_token, $video_id);
}
$tl_video_id = tel_sendVideo($event_video, $chat_id, 'Event '.$video_id);
echo var_export($event_video)."\n";
echo var_export($tl_video_id)."\n";
