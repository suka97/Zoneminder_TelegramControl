<?php 
define("TEL_TOKEN", "1727993307:AAGsVtpU3XgswOxGBWNUfSbWRsH_KcQeST8");

function tel_sendPhoto($img_path, $chat_id, $caption='', $no_upload=false) {
    $postfields = array('chat_id' => $chat_id);
    $postfields['photo'] = ($no_upload) ? $img_path : (new CURLFILE($img_path));
    if ( strlen($caption) > 0 ) $postfields['caption'] = $caption;
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.telegram.org/bot'.TEL_TOKEN.'/sendPhoto',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $postfields,
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true)['result']['photo'][0]['file_id'];
}


function tel_sendMessage($msg, $chat_id) {
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.telegram.org/bot'.TEL_TOKEN.'/sendMessage?chat_id='.$chat_id.'&text='.$msg,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
    ));
    $response = curl_exec($curl);
    curl_close($curl);
}


function tel_sendVideo($video_path, $chat_id, $caption='', $no_upload=false) {
    $postfields = array('chat_id' => $chat_id);
    $postfields['video'] = ($no_upload) ? $video_path : new CURLFILE($video_path);
    if ( strlen($caption) > 0 ) $postfields['caption'] = $caption;
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.telegram.org/bot'.TEL_TOKEN.'/sendVideo',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $postfields,
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $json = json_decode($response, true)['result'];
    if ( array_key_exists('video', $json) ) return $json['video']['file_id'];
    else return $json['document']['file_id'];
}


function tel_sendPhotoGroup($photoGroup, $chat_id, $captions=null) {
    $postfields = array('chat_id' => $chat_id, 'media' => []);
    foreach ( $photoGroup as $index=>$p ) {
        $media = $p;
        if ( file_exists($p) ) {
            $postfields['photo'.$index] = new CURLFILE($p);
            $media = 'attach://photo'.$index;
        }
        $item = [
            'type' => 'photo',
            'media' => $media,
        ];
        if ( is_array($captions) ) $item['caption'] = $captions[$index];
        $postfields['media'][] = $item;
    }
    $postfields['media'] = json_encode($postfields['media']);
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.telegram.org/bot'.TEL_TOKEN.'/sendMediaGroup',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $postfields,
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true)['result'];
}