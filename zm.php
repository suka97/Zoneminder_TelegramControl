<?php 
define("ZM_USER", "admin");
define("ZM_PASS", "mellamoandres"); 

define("ZM_MODE_ON", "Modect");     // Modect Mocord
define("ZM_MODE_OFF", "Monitor");    // Monitor Record

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
function zm_getEventsBetween($access_token, $start, $stop) { 
    $curl = curl_init();
    $url = 'http://localhost/zm/api/events/index/'.
    'StartTime%20>:' . str_replace(' ', '%20', $start).
    '/EndTime%20<=:' . str_replace(' ', '%20', $stop).
    '/MonitorId%20=:1'.
    '/AlarmFrames%20>:0'.
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
        if ( pathinfo($f)['extension'] == 'avi' ) 
            return $eventPath.'/'.$f;
    }
    return false;
}


function zm_changeMonitorStatus($access_token, $function) {   
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