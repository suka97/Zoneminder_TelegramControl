<?php
// sshpass -p "mellamoandres" rsync -v -t -r telegram/*.{php,py} pilar_rasp:~/telegram/

include_once("telegram.php");
include_once("zm.php");
include_once("dbSQLs.php");

function sendOkAndContinue() {
    // Buffer all upcoming output...
    ob_start();
    // Get the size of the output.
    $size = ob_get_length();
    // Disable compression (in case content length is compressed).
    header("Content-Encoding: none");
    // Set the content length of the response.
    header("Content-Length: {$size}");
    // Close the connection.
    header("Connection: close");
    // Flush all output.
    ob_end_flush();
    @ob_flush();
    flush();
    // Close current session (if it exists).
    if(session_id()) session_write_close();
}

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}


$hook = json_decode(file_get_contents('php://input'), true);
// file_put_contents('/var/www/html/telegram/hook.json', json_encode($hook)."\n", FILE_APPEND);

if ( !is_null($hook['message']) ) {
    $chat_id = $hook['message']['chat']['id'];
    $user_id = $hook['message']['from']['id'];

    if ( is_null($hook['message']['entities'][0]) ) return;
    if ( $hook['message']['entities'][0]['type'] != 'bot_command' ) return;

    if ( is_null($hook['message']['from']) ) return;
    if ( $hook['message']['text'] == '/mi_id' ) {
        tel_sendMessage($user_id, $chat_id);
        return;
    }
    if ( !db_userExists($user_id) ) {
        tel_sendMessage('Usuario no habilitado', $chat_id);
        return;
    }
    // get reply event id
    $reply_event_id = null;
    if ( array_key_exists('reply_to_message', $hook['message']) ) {
        if ( strpos($hook['message']['reply_to_message']['caption'], 'New event ') !== false ) {
            $reply_event_id = get_string_between($hook['message']['reply_to_message']['caption'], 'New event ', ' ');
        }
    }
    
    $args = explode(" ", $hook['message']['text']);
    switch($args[0]) {
        case '/ver':
            shell_exec('zmu -m 2 -i -U admin -P mellamoandres');   // guarda en Monitor1.jpg
            shell_exec('zmu -m 3 -i -U admin -P mellamoandres');   // guarda en Monitor1.jpg
            tel_sendPhotoGroup(['Monitor2.jpg','Monitor3.jpg'], $chat_id);
            break;
        case '/activar':
            $zm_token = zm_getToken();
            if ( zm_getMonitor($zm_token)['Monitor']['Function'] != ZM_MODE_ON ) {
                zm_changeMonitorStatus($zm_token, ZM_MODE_ON);
                tel_sendMessage('Alarma activada', $chat_id);
                db_setGlobal('zm_schedule_start', '');
                db_setGlobal('zm_last_time', date('Y-m-d H:i:s', time()));
            }
            else {
                tel_sendMessage('Alarma ya activa', $chat_id);
            }
            break;
        case '/desactivar':
            $zm_token = zm_getToken();
            $time = strtotime('+720 minute');
            if ( count($args) == 2 ) {
                $time = strtotime($args[1].' minute');
                if ( $time === false ) {
                    tel_sendMessage('Fecha invalida', $chat_id);
                    return;
                }
            }
            db_setGlobal( 'zm_schedule_start', strval($time) );
            tel_sendMessage('Schedule Start '.date('m/d/Y H:i:s',$time), $chat_id);
            if ( zm_getMonitor($zm_token)['Monitor']['Function'] != ZM_MODE_OFF ) {
                tel_sendMessage('Alarma desactivada', $chat_id);
                zm_changeMonitorStatus($zm_token, ZM_MODE_OFF);
            }
            else {
                tel_sendMessage('Alarma ya inactiva', $chat_id);
            }
            break;
        case '/agregar_user':
            if ( count($args) != 2 ) {
                tel_sendMessage('Wrong usage', $chat_id);
                return;
            }
            if ( !db_userExists($args[1]) ) {
                db_addUser($args[1]);
                tel_sendMessage('User '.$args[1].' added', $chat_id);
            }
            else
                tel_sendMessage('Usuario ya existente', $chat_id);
            break;
        case '/agregar_chat':
            if ( !db_chatExists($chat_id) ) {
                db_addChat($chat_id, $user_id);
                tel_sendMessage('Chat agregado', $chat_id);
            }
            else 
                tel_sendMessage('Chat ya existente', $chat_id);
            break;
        case '/borrar_chat':
            if ( db_chatExists($chat_id) ) {
                db_delChat($chat_id);
                tel_sendMessage('Chat eliminado', $chat_id);
            }
            else
                tel_sendMessage('Chat inexistente', $chat_id);
            break;
        case '/video':
            if ( (is_null($reply_event_id) && count($args)!=2) || (!is_null($reply_event_id) && count($args)!=1) ) {
                tel_sendMessage('Wrong usage', $chat_id);
                return;
            }
            $event_id = is_null($reply_event_id) ? $args[1] : $reply_event_id;
            $tl_video_id = db_getZmEventVideo($event_id);
            if ( empty($tl_video_id) ) {
                sendOkAndContinue();
                tel_sendMessage('Procesando Video', $chat_id); 
                $zm_token = zm_getToken();
                $event_video = zm_eventVideo($zm_token, $event_id);
                if ( $event_video == false ) {
                    $event_video = zm_createEventVideo($zm_token, $event_id);
                }
                $tl_video_id = tel_sendVideo($event_video, $chat_id, 'Event '.$event_id);
                db_addZmEventVideo($event_id, $tl_video_id);
            }
            else {
                tel_sendVideo($tl_video_id, $chat_id, 'Event '.$event_id, true);
            }
            break;
        case '/archivar':   // event_id, event_name
            if ( count($args) != 3 ) {
                tel_sendMessage('Wrong usage', $chat_id);
                return;
            }
            $zm_token = zm_getToken();
            zm_renameEvent($zm_token, $args[1], $args[2]);
            zm_archiveEvent($zm_token, $args[1]);
            tel_sendMessage('Evento Archivado', $chat_id);
            break;
        case '/alarma':
            if ( count($args) > 2 ) {
                tel_sendMessage('Wrong usage', $chat_id);
                return;
            }
            if ( count($args) == 2 ) {
                if ( !in_array($args[1], ['on', 'off']) ) {
                    tel_sendMessage('Wrong usage', $chat_id);
                    return;
                }
                $state = $args[1];
            }
            else {
                $state = (db_getGlobal('alarma_state')=='on') ? 'off' : 'on';
            }
            db_setGlobal('alarma_state', $state);
            tel_sendMessage('Alarma State Changed to '.$state, $chat_id);
            // $command = escapeshellcmd('./alarma.py');
            // shell_exec($command);
            break;
        case '/zip_all_events':
            $zm_token = zm_getToken();
            $events = zm_getAllEvents_NotArchived($zm_token);
            tel_sendMessage('Ziping events...', $chat_id);
            sendOkAndContinue();
            zm_zipEventsSnapshots($events, '/home/ubuntu/telegram/events.zip');
            tel_sendMessage('Events ziped OK', $chat_id);
            break;
        case '/eventos_entre':
            if ( count($args) != 3 ) {
                tel_sendMessage('Wrong usage', $chat_id);
                return;
            }
            $date_start = str_replace('_', ' ', $args[1]);
            $date_end = str_replace('_', ' ', $args[2]);
            if ( $date_start==false || $date_end==false ) {
                tel_sendMessage('Invalid times', $chat_id);
                return;
            }
            $zm_token = zm_getToken();
            $events = zm_getEventsBetween($zm_token, $date_start, $date_end, true)['events'];
            if ( count($events) > 10 ) {
                tel_sendMessage('Too many events: '.count($events), $chat_id);
                return;
            }
            // foreach ( $events as $e ) { 
            //     $db_event = db_getZmEvent($e['Event']['Id']);
            //     if ( $db_event == false ) {
            //         $tel_video_id = '';
            //         $tel_snap_id = tel_sendPhoto($e['Event']['FileSystemPath'].'/snapshot.jpg', $chat_id, 'Event '.$e['Event']['Id'].' '.$e['Event']['StartTime']);
            //         db_addZmEvent(array(
            //             'id' => $e['Event']['Id'],
            //             'start_time' => $e['Event']['StartTime'],
            //             'end_time' => $e['Event']['EndTime'],
            //             'tl_video_id' => $tel_video_id,
            //             'tl_snap_id' => $tel_snap_id
            //         ));
            //     }
            //     else {
            //         tel_sendPhoto($db_event['tl_snap_id'], $chat_id, 'Event '.$e['Event']['Id'].' '.$e['Event']['StartTime'], true);
            //     }
            // }
            $photoGroup = []; $captions = [];
            foreach ( $events as $e ) { 
                $photoGroup[] = $e['Event']['FileSystemPath'].'/snapshot.jpg';
                $captions[] = 'Event '.$e['Event']['Id'].' '.$e['Event']['StartTime'];
            }
            tel_sendPhotoGroup($photoGroup, $chat_id, $captions);
            break;
        default:
            tel_sendMessage('Comando invalido', $chat_id);
    }
}