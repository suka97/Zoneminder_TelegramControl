<?php
include("telegram.php");
include("zm.php");
include("dbSQLs.php");

$zm_token = zm_getToken();
$events = zm_getAllEvents_NotArchived($zm_token);
zm_zipEventsSnapshots($events, 'events.zip');
