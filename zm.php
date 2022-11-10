<?php 
define("ZM_USER", "admin");
define("ZM_PASS", "mellamoandres"); 

define("ZM_MODE_ON", "Modect");     // Modect Mocord
define("ZM_MODE_OFF", "Monitor");    // Monitor Record

include_once("dbSQLs.php");

function zm_getToken() {
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost/zm/api/host/login.json',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => 'user='.ZM_USER.'&pass='.ZM_PASS.'&stateful=1',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded'
    ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true)['access_token'];
}


//2015-05-15%2018:43:56/   EndTime%20<=:208:43:56
function zm_getEventsBetween($access_token, $start, $stop, $getNotAlarms=false) { 
    $filter = '/MonitorId%20=:1' . '/AlarmFrames%20>:0';
    if ( $getNotAlarms ) $filter = '';

    $curl = curl_init();
    $url = 'http://localhost/zm/api/events/index/'.
    'StartTime%20>:' . str_replace(' ', '%20', $start).
    '/EndTime%20<=:' . str_replace(' ', '%20', $stop).
    $filter.
    '.json?sort=StartTime&direction=asc'.
    '&token=' . $access_token;
    curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    $response = curl_exec($curl); 
    return json_decode($response, true);
}


function zm_getAllEvents_NotArchived($access_token) { 
    $salida = [];
    $url = 'http://localhost/zm/api/events/index/'.
        'archived%20=:0'.
        '.json?sort=StartTime&direction=asc'.
        '&token='.$access_token;

    // initial request
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    $response = curl_exec($curl); 
    $json = json_decode($response, true);
    $salida = array_merge($salida, $json['events']);
    $pageCount = $json['pagination']['pageCount'];
    curl_close($curl);

    //concurrent requests
    $curl_arr = array();
    $master = curl_multi_init();
    for ( $page = 2 ; $page <= $pageCount ; $page++ ) {
        $curl_arr[] = curl_init();
        curl_setopt_array($curl_arr[$page-2], array(
            CURLOPT_URL => $url . '&page='.$page,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        array_push($curl_arr, $curl_arr[$page-2]);
        curl_multi_add_handle($master, $curl_arr[$page-2]);
    }
    do {
        curl_multi_exec($master, $running);
    } while($running > 0);
    for ( $page = 2 ; $page <= $pageCount ; $page++ ) {
        $json = json_decode( curl_multi_getcontent($curl_arr[$page-2]), true);
        $salida = array_merge($salida, $json['events']);
    }

    return $salida;
}


function zm_zipEventsSnapshots($events, $zipname) {
    $zip = new ZipArchive;
    $zip->open($zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    foreach ($events as $index=>$e) {
        $snap = $e['Event']['FileSystemPath'] .'/snapshot.jpg';
        if ( file_exists($snap) ) {
            $dirs = explode('/', $e['Event']['FileSystemPath']);
            $zip->addFile($snap, $dirs[count($dirs)-2].'/'.$dirs[count($dirs)-1].'.jpg');
        }
    }
    $zip->close();
}


function zm_getMonitor($access_token) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost/zm/api/monitors/1.json?token=' . $access_token,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true)['monitor'];
}


function zm_createEventVideo($access_token, $event_id) {
    // shell_exec('ffmpeg -f image2 -r 60 -i '.$eventPath.'/%5d-capture.jpg -vcodec libx264 -crf 18  -pix_fmt yuv420p Video.mp4');
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost/zm/index.php?view=request&request=event&action=video&videoFormat=avi&rate=100&scale=100'
        .'&id='.$event_id.'&token='.$access_token,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true)['response'];
}


function zm_getEvent($access_token, $event_id) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost/zm/api/events/'.$event_id.'.json?token='.$access_token,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true)['event'];

}


function zm_eventVideo($access_token, $event_id) {
    $event = zm_getEvent($access_token, $event_id);
    $eventPath = $event['Event']['FileSystemPath'];
    foreach(scandir($eventPath) as $f) {
        $ext = pathinfo($f)['extension'];
        if ( $ext == 'mp4' ) return $eventPath.'/'.$f;
        if ( $ext == 'avi' ) return $eventPath.'/'.$f;
    }
    return false;
}


function zm_changeMonitorStatus($access_token, $function) {   
    db_setGlobal('monitor_state', $function); return;
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost/zm/api/monitors/1.json?token=' . $access_token,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => 'Monitor%5BFunction%5D='.$function.'&Monitor%5BEnabled%5D=1',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded'
    ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
}


function zm_archiveEvent($access_token, $event_id) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost/zm/index.php?view=request&request=event&action=archive'
        .'&id='.$event_id.'&token='.$access_token,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
}


function zm_renameEvent($access_token, $event_id, $event_new_name) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://localhost/zm/index.php?view=request&request=event&action=rename'
        .'&eventName='.$event_new_name.'&id='.$event_id.'&token='.$access_token,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
}