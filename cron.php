<?php
include("zm.php");
include("dbSQLs.php");
include("telegram.php");

$zm_schedule_start = db_getGlobal('zm_schedule_start');
$zm_last_monitor_status = db_getGlobal('zm_last_monitor_status');
$alert_chats = db_getChats();

$zm_access_token = zm_getToken();
$zm_monitor = zm_getMonitor($zm_access_token);

if( $zm_monitor['Monitor_Status']['Status'] != $zm_last_monitor_status ) {
    db_setGlobal('zm_last_monitor_status', $zm_monitor['Monitor_Status']['Status']);
    foreach ( $alert_chats as $chat ) {
        tel_sendMessage('Status Changed to: '.$zm_monitor['Monitor_Status']['Status'], $chat);
    }
}

// schedule start
if ( $zm_schedule_start != '' ) {
    if ( strtotime('now') >= $zm_schedule_start ) {
        zm_changeMonitorStatus($zm_access_token, ZM_MODE_ON);
        for ( $i=0 ; $i<count($alert_chats) ; $i++ ) tel_sendMessage('Alarma activada', $alert_chats[$i]);
        db_setGlobal('zm_schedule_start', '');
    }
}

// new events
if ( zm_getMonitor($zm_access_token)['Monitor']['Function'] == ZM_MODE_ON ) {
    $events_15m = zm_getEventsBetween($zm_access_token, date('Y-m-d H:i:s', strtotime('-15 minute')), date('Y-m-d H:i:s', time()))['events'];
    if ( db_getGlobal('zm_false_event') == 'on' ) {
        if ( count($events_15m) == 0 ) {
            db_setGlobal('zm_false_event', 'off');
            for ( $i=0 ; $i<count($alert_chats) ; $i++ ) tel_sendMessage('False Alarm Passed', $alert_chats[$i]);
            db_setGlobal('zm_last_time', date('Y-m-d H:i:s', time()));
        }
    }
    else {
        // $last_ev_time = db_getLastEvent(); is_array($last_ev_time)?$last_ev_time['start_time']:'2021-06-14 19:51:55'
        $new_events = zm_getEventsBetween($zm_access_token, db_getGlobal('zm_last_time'), date('Y-m-d H:i:s', time()))['events'];
        foreach ( $new_events as $e ) { 
            db_setGlobal('zm_last_time', $e['Event']['StartTime']);
            if ( $e['Event']['AlarmFrames'] < 3 ) continue;

            // $tel_video_id = tel_sendVideo($e['Event']['FileSystemPath'], $alert_chats[0]);
            $tel_video_id = '';
            $tel_snap_id = tel_sendPhoto($e['Event']['FileSystemPath'].'/snapshot.jpg', $alert_chats[0], 'New event '.$e['Event']['Id'].' '.$e['Event']['Length'].'seg');

            for ( $i=1 ; $i<count($alert_chats) ; $i++ ) {
                // tel_sendMessage('New event '.$e['Event']['Id'], $chat);
                tel_sendPhoto($tel_snap_id, $alert_chats[$i], 'New event '.$e['Event']['Id'].' '.$e['Event']['Length'].'seg', true);
            }
            db_addZmEvent(array(
                'id' => $e['Event']['Id'],
                'start_time' => $e['Event']['StartTime'],
                'end_time' => $e['Event']['EndTime'],
                'tl_video_id' => $tel_video_id,
                'tl_snap_id' => $tel_snap_id
            ));
        }

        if ( count($events_15m) >= 5 ) {
            db_setGlobal('zm_false_event', 'on');
            for ( $i=0 ; $i<count($alert_chats) ; $i++ ) tel_sendMessage('False Alarm Triggered', $alert_chats[$i]);
        }
    }
}