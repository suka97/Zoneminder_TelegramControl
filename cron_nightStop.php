<?php
include("telegram.php");
include("zm.php");
include("dbSQLs.php");

$zm_token = zm_getToken();
$time = strtotime('+420 minute');
db_setGlobal( 'zm_schedule_start', strval($time) );
if ( zm_getMonitor($zm_token)['Monitor']['Function'] != ZM_MODE_OFF ) {
    zm_changeMonitorStatus($zm_token, ZM_MODE_OFF);
}